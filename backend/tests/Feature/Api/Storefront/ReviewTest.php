<?php

namespace Tests\Feature\Api\Storefront;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = Store::factory()->create(['slug' => 'minha-loja']);
        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'is_active' => true,
            'sale_price' => 500,
            'quantity' => 10,
        ]);
        $this->order = Order::factory()
            ->has(\App\Models\OrderItem::factory()->count(1)->state([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_code' => $product->code,
                'unit_price' => $product->sale_price,
            ]), 'items')
            ->delivered()
            ->create(['store_id' => $this->store->id]);
    }

    public function test_can_submit_review(): void
    {
        $response = $this->postJson("/api/s/minha-loja/orders/{$this->order->id}/review", [
            'rating' => 5,
            'comment' => 'Excelente produto!',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Avaliação registada com sucesso! Obrigado.');

        $this->assertDatabaseHas('store_reviews', [
            'order_id' => $this->order->id,
            'rating' => 5,
        ]);
    }

    public function test_review_validates_rating_range(): void
    {
        $response = $this->postJson("/api/s/minha-loja/orders/{$this->order->id}/review", [
            'rating' => 6,
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_review_non_delivered_order(): void
    {
        $pendingOrder = Order::factory()->create(['store_id' => $this->store->id]);

        $response = $this->postJson("/api/s/minha-loja/orders/{$pendingOrder->id}/review", [
            'rating' => 4,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Só é possível avaliar pedidos entregues.');
    }

    public function test_cannot_review_twice(): void
    {
        $this->postJson("/api/s/minha-loja/orders/{$this->order->id}/review", [
            'rating' => 5,
        ]);

        $response = $this->postJson("/api/s/minha-loja/orders/{$this->order->id}/review", [
            'rating' => 3,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Este pedido já foi avaliado.');
    }

    public function test_returns_404_for_wrong_store_order(): void
    {
        $otherOrder = Order::factory()->delivered()->create();

        $response = $this->postJson("/api/s/minha-loja/orders/{$otherOrder->id}/review", [
            'rating' => 4,
        ]);

        $response->assertStatus(404);
    }
}
