<?php

namespace Tests\Feature\Api\Storefront;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = Store::factory()->create(['slug' => 'minha-loja']);
    }

    public function test_can_list_products(): void
    {
        Product::factory()->count(3)->create([
            'store_id' => $this->store->id,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/s/minha-loja/products");

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta'])
            ->assertJsonCount(3, 'data');
    }

    public function test_only_returns_store_products(): void
    {
        Product::factory()->create(['store_id' => $this->store->id, 'is_active' => true]);
        Product::factory()->create(['store_id' => $this->store->id, 'is_active' => true]);
        // Product from another store
        Product::factory()->create(['is_active' => true]);

        $response = $this->getJson("/api/s/minha-loja/products");

        $response->assertJsonCount(2, 'data');
    }

    public function test_filters_by_category(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['store_id' => $this->store->id, 'category_id' => $category->id, 'is_active' => true]);
        Product::factory()->count(2)->create(['store_id' => $this->store->id, 'is_active' => true]);

        $response = $this->getJson("/api/s/minha-loja/products?category_id={$category->id}");

        $response->assertJsonCount(1, 'data');
    }

    public function test_searches_products(): void
    {
        Product::factory()->create(['store_id' => $this->store->id, 'name' => 'Arroz Agulha', 'is_active' => true]);
        Product::factory()->create(['store_id' => $this->store->id, 'name' => 'Feijão Preto', 'is_active' => true]);

        $response = $this->getJson("/api/s/minha-loja/products?search=Arroz");

        $response->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Arroz Agulha');
    }

    public function test_can_show_product_detail(): void
    {
        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'is_active' => true,
            'quantity' => 10,
        ]);

        $response = $this->getJson("/api/s/minha-loja/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name', 'sale_price', 'stock_label']])
            ->assertJsonPath('data.stock_label', 'disponivel');
    }

    public function test_returns_404_for_inactive_product(): void
    {
        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'is_active' => false,
        ]);

        $response = $this->getJson("/api/s/minha-loja/products/{$product->id}");

        $response->assertStatus(404);
    }

    public function test_returns_404_for_wrong_store_product(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        $response = $this->getJson("/api/s/minha-loja/products/{$product->id}");

        $response->assertStatus(404);
    }

    public function test_can_search(): void
    {
        Product::factory()->create([
            'store_id' => $this->store->id,
            'name' => 'Fuba de Milho',
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/s/minha-loja/search?q=Fuba");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_search_requires_min_2_chars(): void
    {
        $response = $this->getJson("/api/s/minha-loja/search?q=F");

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}
