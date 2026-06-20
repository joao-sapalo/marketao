<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_sales(): void
    {
        Sale::factory()->count(3)->create();

        $response = $this->getJson('/api/sales');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_can_create_sale(): void
    {
        $customer = Customer::factory()->create();
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create([
            'quantity' => 50,
            'sale_price' => 100,
        ]);

        $response = $this->postJson('/api/sales', [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'price' => 100,
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'total', 'items']])
            ->assertJsonPath('data.total', 200);
    }

    public function test_cannot_create_sale_without_items(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->postJson('/api/sales', [
            'customer_id' => $customer->id,
            'items' => [],
        ]);

        $response->assertStatus(422);
    }

    public function test_can_show_sale(): void
    {
        $sale = Sale::factory()->create();

        $response = $this->getJson("/api/sales/{$sale->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'total', 'status']])
            ->assertJsonPath('data.id', $sale->id);
    }
}
