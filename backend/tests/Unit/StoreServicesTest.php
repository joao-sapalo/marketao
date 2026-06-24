<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreReview;
use App\Services\Store\CancelOrderService;
use App\Services\Store\CheckoutService;
use App\Services\Store\ConfirmOrderService;
use App\Services\Store\TrustScoreCalculatorService;
use App\Services\Store\VerifyPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreServicesTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = Store::factory()->create();
        $this->product = Product::factory()->create([
            'store_id' => $this->store->id,
            'sale_price' => 1000,
            'quantity' => 50,
            'is_active' => true,
        ]);
    }

    public function test_checkout_creates_order(): void
    {
        $service = new CheckoutService($this->store, [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2],
            ],
            'guest_name' => 'Maria',
            'guest_phone' => '244900000000',
            'payment_method' => Order::CASH,
        ]);

        $result = $service->call();

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['order']);
        $this->assertEquals(2000, $result['order']->total);
        $this->assertEquals(Order::PENDING, $result['order']->status);

        $this->assertDatabaseHas('orders', ['id' => $result['order']->id]);
        $this->assertDatabaseHas('order_items', ['order_id' => $result['order']->id, 'quantity' => 2]);
    }

    public function test_checkout_raises_error_for_insufficient_stock(): void
    {
        $service = new CheckoutService($this->store, [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 999],
            ],
            'guest_name' => 'Maria',
            'guest_phone' => '244900000000',
        ]);

        $result = $service->call();

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('stock', $result['errors'][0]);
    }

    public function test_checkout_raises_error_for_inactive_store(): void
    {
        $this->store->update(['is_active' => false]);

        $service = new CheckoutService($this->store, [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
            'guest_name' => 'Maria',
            'guest_phone' => '244900000000',
        ]);

        $result = $service->call();

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('inativa', $result['errors'][0]);
    }

    public function test_checkout_raises_error_for_unknown_product(): void
    {
        $this->expectException(\RuntimeException::class);

        $service = new CheckoutService($this->store, [
            'items' => [
                ['product_id' => 99999, 'quantity' => 1],
            ],
            'guest_name' => 'Maria',
            'guest_phone' => '244900000000',
        ]);

        $service->call();
    }

    public function test_confirm_order_creates_sale_and_stock_movement(): void
    {
        $order = Order::factory()
            ->has(OrderItem::factory()->state([
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'product_code' => $this->product->code,
                'unit_price' => $this->product->sale_price,
                'quantity' => 3,
                'total' => 3000,
            ]), 'items')
            ->create(['store_id' => $this->store->id]);

        $service = new ConfirmOrderService($order);
        $result = $service->call();

        $this->assertTrue($result['success']);
        $this->assertEquals(Order::CONFIRMED, $result['order']->status);

        // Stock was decremented
        $this->assertEquals(47, $this->product->fresh()->quantity);
        $this->assertDatabaseHas('stock_movements', ['quantity' => 3]);
    }

    public function test_confirm_order_fails_for_non_pending(): void
    {
        $order = Order::factory()->confirmed()->create(['store_id' => $this->store->id]);

        $service = new ConfirmOrderService($order);
        $result = $service->call();

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('pendente', $result['errors'][0]);
    }

    public function test_verify_payment(): void
    {
        $order = Order::factory()->create([
            'store_id' => $this->store->id,
            'payment_status' => Order::PENDING_VERIFICATION,
            'status' => Order::CONFIRMED,
        ]);

        $service = new VerifyPaymentService($order);
        $result = $service->call();

        $this->assertTrue($result['success']);
        $this->assertEquals(Order::PAID, $result['order']->payment_status);
    }

    public function test_verify_payment_fails_for_wrong_status(): void
    {
        $order = Order::factory()->create([
            'store_id' => $this->store->id,
            'payment_status' => Order::UNPAID,
        ]);

        $service = new VerifyPaymentService($order);
        $result = $service->call();

        $this->assertFalse($result['success']);
    }

    public function test_cancel_pending_order(): void
    {
        $order = Order::factory()->create(['store_id' => $this->store->id]);

        $service = new CancelOrderService($order, 'Teste cancelamento');
        $result = $service->call();

        $this->assertTrue($result['success']);
        $this->assertEquals(Order::CANCELLED, $result['order']->status);
        $this->assertEquals('Teste cancelamento', $result['order']->cancel_reason);
    }

    public function test_cancel_confirmed_order_restores_stock(): void
    {
        $order = Order::factory()
            ->has(OrderItem::factory()->state([
                'product_id' => $this->product->id,
                'quantity' => 3,
            ]), 'items')
            ->confirmed()
            ->create(['store_id' => $this->store->id]);
        // Deduct stock as ConfirmOrderService would
        $this->product->decrement('quantity', 3);

        $service = new CancelOrderService($order, 'Reembolso');
        $result = $service->call();

        $this->assertTrue($result['success']);
        $this->assertEquals(Order::CANCELLED, $result['order']->status);
        $this->assertEquals(50, $this->product->fresh()->quantity);
    }

    public function test_cannot_cancel_delivered_order(): void
    {
        $order = Order::factory()->delivered()->create(['store_id' => $this->store->id]);

        $service = new CancelOrderService($order, 'Tentativa');
        $result = $service->call();

        $this->assertFalse($result['success']);
    }

    public function test_trust_score_starts_at_zero(): void
    {
        $service = new TrustScoreCalculatorService($this->store);
        $service->call();

        $this->assertEquals(0, $this->store->fresh()->trust_score);
    }

    public function test_trust_score_increases_with_reviews(): void
    {
        $order = Order::factory()->delivered()->create(['store_id' => $this->store->id]);
        StoreReview::factory()->create([
            'store_id' => $this->store->id,
            'order_id' => $order->id,
            'rating' => 5,
        ]);

        $service = new TrustScoreCalculatorService($this->store);
        $service->call();

        $this->assertGreaterThan(0, $this->store->fresh()->trust_score);
    }

    public function test_trust_score_updates_store_stats(): void
    {
        Order::factory()->count(3)->create(['store_id' => $this->store->id]);
        Order::factory()->confirmed()->create(['store_id' => $this->store->id]);

        $service = new TrustScoreCalculatorService($this->store);
        $service->call();

        $fresh = $this->store->fresh();
        $this->assertEquals(4, $fresh->total_orders);
        $this->assertGreaterThan(0, $fresh->confirmed_orders);
    }
}
