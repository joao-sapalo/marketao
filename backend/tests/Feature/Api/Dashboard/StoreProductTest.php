<?php

namespace Tests\Feature\Api\Dashboard;

use App\Models\Product;
use App\Models\Store;
use App\Models\StoreProduct;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreProductTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        $this->store = Store::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_can_list_store_products(): void
    {
        Product::factory()->count(3)->create(['store_id' => $this->store->id]);

        $response = $this->getJson('/api/dashboard/store-products');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_update_product_visibility(): void
    {
        $product = Product::factory()->create(['store_id' => $this->store->id]);

        $response = $this->putJson("/api/dashboard/store-products/{$product->id}", [
            'is_visible' => true,
            'featured' => true,
            'display_order' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Produto actualizado na loja.');

        $this->assertDatabaseHas('store_product', [
            'store_id' => $this->store->id,
            'product_id' => $product->id,
            'is_visible' => true,
            'featured' => true,
        ]);
    }

    public function test_can_hide_product(): void
    {
        $product = Product::factory()->create(['store_id' => $this->store->id]);
        StoreProduct::factory()->create([
            'store_id' => $this->store->id,
            'product_id' => $product->id,
            'is_visible' => true,
        ]);

        $response = $this->putJson("/api/dashboard/store-products/{$product->id}", [
            'is_visible' => false,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('store_product', [
            'product_id' => $product->id,
            'is_visible' => false,
        ]);
    }

    public function test_returns_404_for_wrong_store_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->putJson("/api/dashboard/store-products/{$product->id}", [
            'is_visible' => true,
        ]);

        $response->assertStatus(404);
    }
}
