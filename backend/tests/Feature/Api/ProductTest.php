<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_products(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_can_create_product(): void
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $data = [
            'code' => 'PROD-001',
            'name' => 'Test Product',
            'category_id' => $category->id,
            'purchase_price' => 10.50,
            'sale_price' => 25.00,
            'quantity' => 100,
            'min_stock' => 10,
            'supplier_id' => $supplier->id,
        ];

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'code', 'name']])
            ->assertJsonPath('data.name', 'Test Product');
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'Updated Product',
            'code' => $product->code,
            'purchase_price' => $product->purchase_price,
            'sale_price' => $product->sale_price,
            'quantity' => $product->quantity,
            'min_stock' => $product->min_stock,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Product');
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Product deleted successfully']);

        $this->assertSoftDeleted($product);
    }

    public function test_can_list_low_stock_products(): void
    {
        Product::factory()->create(['quantity' => 5, 'min_stock' => 10]);
        Product::factory()->create(['quantity' => 50, 'min_stock' => 10]);

        $response = $this->getJson('/api/products/low-stock');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
