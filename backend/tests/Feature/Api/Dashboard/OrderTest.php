<?php

namespace Tests\Feature\Api\Dashboard;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Store $store;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        $this->store = Store::factory()->create(['user_id' => $this->user->id]);
        $this->order = Order::factory()->create(['store_id' => $this->store->id]);
    }

    public function test_can_list_orders(): void
    {
        Order::factory()->count(2)->create(['store_id' => $this->store->id]);

        $response = $this->getJson('/api/dashboard/orders');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_can_filter_orders_by_status(): void
    {
        Order::factory()->create(['store_id' => $this->store->id, 'status' => Order::PENDING]);
        Order::factory()->confirmed()->create(['store_id' => $this->store->id]);

        $response = $this->getJson('/api/dashboard/orders?status=' . Order::PENDING);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_show_order(): void
    {
        $order = Order::factory()
            ->has(\App\Models\OrderItem::factory()->count(2), 'items')
            ->create(['store_id' => $this->store->id]);

        $response = $this->getJson("/api/dashboard/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'reference', 'status', 'items']]);
    }

    public function test_can_confirm_order(): void
    {
        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'quantity' => 10,
        ]);
        $order = Order::factory()
            ->has(\App\Models\OrderItem::factory()->state([
                'product_id' => $product->id,
                'quantity' => 2,
                'product_name' => $product->name,
                'product_code' => $product->code,
                'unit_price' => $product->sale_price,
            ]), 'items')
            ->create(['store_id' => $this->store->id]);

        $response = $this->postJson("/api/dashboard/orders/{$order->id}/confirm");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Pedido confirmado com sucesso!');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::CONFIRMED,
        ]);

        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'quantity' => 2]);
        $this->assertNotNull($order->fresh()->sale_id);
    }

    public function test_cannot_confirm_non_pending_order(): void
    {
        $order = Order::factory()->confirmed()->create(['store_id' => $this->store->id]);

        $response = $this->postJson("/api/dashboard/orders/{$order->id}/confirm");

        $response->assertStatus(422);
    }

    public function test_can_mark_processing(): void
    {
        $order = Order::factory()->confirmed()->create(['store_id' => $this->store->id]);

        $response = $this->postJson("/api/dashboard/orders/{$order->id}/mark-processing");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Pedido marcado como Em preparação.');
    }

    public function test_can_mark_shipped(): void
    {
        $order = Order::factory()->confirmed()->create(['store_id' => $this->store->id]);

        $response = $this->postJson("/api/dashboard/orders/{$order->id}/mark-shipped");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Pedido marcado como Enviado.');
    }

    public function test_can_mark_delivered(): void
    {
        $order = Order::factory()->confirmed()->create(['store_id' => $this->store->id]);

        $response = $this->postJson("/api/dashboard/orders/{$order->id}/mark-delivered");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Pedido marcado como Entregue.');

        $this->assertNotNull($order->fresh()->delivered_at);
    }

    public function test_can_cancel_pending_order(): void
    {
        $response = $this->postJson("/api/dashboard/orders/{$this->order->id}/cancel", [
            'reason' => 'Cliente desistiu.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Pedido cancelado.');

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => Order::CANCELLED,
        ]);
    }

    public function test_cannot_cancel_delivered_order(): void
    {
        $order = Order::factory()->delivered()->create(['store_id' => $this->store->id]);

        $response = $this->postJson("/api/dashboard/orders/{$order->id}/cancel");

        $response->assertStatus(422);
    }

    public function test_returns_404_for_wrong_store_order(): void
    {
        $otherOrder = Order::factory()->create();

        $response = $this->getJson("/api/dashboard/orders/{$otherOrder->id}");

        $response->assertStatus(404);
    }
}
