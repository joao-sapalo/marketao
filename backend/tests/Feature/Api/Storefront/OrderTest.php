<?php

namespace Tests\Feature\Api\Storefront;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = Store::factory()->create(['slug' => 'minha-loja']);
        $this->product = Product::factory()->create([
            'store_id' => $this->store->id,
            'is_active' => true,
            'sale_price' => 500,
            'quantity' => 10,
        ]);
    }

    public function test_can_create_order(): void
    {
        $response = $this->postJson("/api/s/minha-loja/orders", [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2],
            ],
            'guest_name' => 'João Cliente',
            'guest_phone' => '244900000000',
            'payment_method' => Order::CASH,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data' => ['id', 'reference', 'total', 'status']])
            ->assertJsonPath('data.status', Order::PENDING);

        $this->assertDatabaseHas('orders', ['guest_name' => 'João Cliente']);
        $this->assertDatabaseHas('order_items', ['quantity' => 2]);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->postJson("/api/s/minha-loja/orders", []);

        $response->assertStatus(422);
    }

    public function test_returns_error_when_insufficient_stock(): void
    {
        $response = $this->postJson("/api/s/minha-loja/orders", [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 999],
            ],
            'guest_name' => 'João Cliente',
            'guest_phone' => '244900000000',
            'payment_method' => Order::CASH,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_returns_error_when_store_inactive(): void
    {
        $this->store->update(['is_active' => false]);

        $response = $this->postJson("/api/s/minha-loja/orders", [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
            'guest_name' => 'João Cliente',
            'guest_phone' => '244900000000',
        ]);

        $response->assertStatus(422);
    }

    public function test_can_list_orders(): void
    {
        Order::factory()->count(3)->create(['store_id' => $this->store->id]);
        // Order from another store
        Order::factory()->create();

        $response = $this->getJson("/api/s/minha-loja/orders");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_show_order(): void
    {
        $order = Order::factory()
            ->has(\App\Models\OrderItem::factory()->count(2), 'items')
            ->create(['store_id' => $this->store->id]);

        $response = $this->getJson("/api/s/minha-loja/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'reference', 'status', 'items']])
            ->assertJsonCount(2, 'data.items');
    }

    public function test_returns_404_for_wrong_store_order(): void
    {
        $order = Order::factory()->create();

        $response = $this->getJson("/api/s/minha-loja/orders/{$order->id}");

        $response->assertStatus(404);
    }

    public function test_can_find_order_by_reference(): void
    {
        $order = Order::factory()->create([
            'store_id' => $this->store->id,
            'reference' => 'ORD-2026-99999',
        ]);

        $response = $this->getJson("/api/s/minha-loja/orders/by-reference/ORD-2026-99999");

        $response->assertStatus(200)
            ->assertJsonPath('data.reference', 'ORD-2026-99999');
    }
}
