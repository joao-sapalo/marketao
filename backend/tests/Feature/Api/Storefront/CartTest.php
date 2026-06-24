<?php

namespace Tests\Feature\Api\Storefront;

use App\Models\Product;
use App\Models\Store;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartTest extends TestCase
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

    public function test_show_empty_cart(): void
    {
        $response = $this->getJson("/api/s/minha-loja/cart");

        $response->assertStatus(200)
            ->assertJsonPath('data.items', [])
            ->assertJsonPath('data.total', 0)
            ->assertJsonPath('data.item_count', 0);
    }

    public function test_can_add_item_to_cart(): void
    {
        $response = $this->postJson("/api/s/minha-loja/cart", [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Carrinho actualizado.')
            ->assertJsonPath('item_count', 2);
    }

    public function test_can_view_cart_with_items(): void
    {
        $this->withSession(["cart_{$this->store->id}" => [$this->product->id => 3]]);

        $response = $this->getJson("/api/s/minha-loja/cart");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.name', $this->product->name)
            ->assertJsonPath('data.items.0.quantity', 3)
            ->assertJsonPath('data.items.0.subtotal', 1500)
            ->assertJsonPath('data.total', 1500);
    }

    public function test_can_remove_item_from_cart(): void
    {
        $this->withSession(["cart_{$this->store->id}" => [$this->product->id => 3]]);

        $response = $this->postJson("/api/s/minha-loja/cart", [
            'product_id' => $this->product->id,
            'quantity' => 0,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('item_count', 0);
    }

    public function test_can_clear_cart(): void
    {
        $this->withSession(["cart_{$this->store->id}" => [$this->product->id => 3]]);

        $response = $this->deleteJson("/api/s/minha-loja/cart");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Carrinho limpo.');
    }

    public function test_cannot_add_product_from_another_store(): void
    {
        $otherProduct = Product::factory()->create(['is_active' => true]);

        $response = $this->postJson("/api/s/minha-loja/cart", [
            'product_id' => $otherProduct->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(404);
    }

    public function test_cannot_add_inactive_product(): void
    {
        $inactive = Product::factory()->create([
            'store_id' => $this->store->id,
            'is_active' => false,
        ]);

        $response = $this->postJson("/api/s/minha-loja/cart", [
            'product_id' => $inactive->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(404);
    }
}
