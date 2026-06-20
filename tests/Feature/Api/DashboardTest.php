<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_dashboard_returns_required_metrics(): void
    {
        Customer::factory()->count(5)->create();
        Product::factory()->count(3)->create(['quantity' => 2, 'min_stock' => 10]);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_sales',
                'total_purchases',
                'monthly_profit',
                'low_stock_count',
                'active_customers',
                'accounts_receivable_total',
                'accounts_payable_total',
                'sales_chart',
            ]);
    }
}
