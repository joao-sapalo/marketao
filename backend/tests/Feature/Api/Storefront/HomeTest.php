<?php

namespace Tests\Feature\Api\Storefront;

use App\Models\Product;
use App\Models\Store;
use App\Models\StoreProduct;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = Store::factory()->create(['slug' => 'minha-loja']);
    }

    public function test_can_get_store_home(): void
    {
        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'is_active' => true,
        ]);
        StoreProduct::factory()->create([
            'store_id' => $this->store->id,
            'product_id' => $product->id,
            'is_visible' => true,
            'featured' => true,
            'display_order' => 1,
        ]);

        $response = $this->getJson("/api/s/minha-loja");

        $response->assertStatus(200)
            ->assertJsonStructure(['store', 'featured_products'])
            ->assertJsonPath('store.slug', 'minha-loja')
            ->assertJsonPath('store.name', $this->store->name);
    }

    public function test_returns_404_for_inactive_store(): void
    {
        Store::factory()->create(['slug' => 'inativa', 'is_active' => false]);

        $response = $this->getJson("/api/s/inativa");

        $response->assertStatus(404);
    }

    public function test_can_get_categories(): void
    {
        $response = $this->getJson("/api/s/minha-loja/categories");

        $response->assertStatus(200)
            ->assertJsonStructure(['store', 'featured_products']);
    }
}
