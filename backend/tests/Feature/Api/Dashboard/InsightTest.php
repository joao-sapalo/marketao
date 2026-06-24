<?php

namespace Tests\Feature\Api\Dashboard;

use App\Models\Store;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InsightTest extends TestCase
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

    public function test_can_get_insights(): void
    {
        $response = $this->getJson('/api/dashboard/insights');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['insights', 'trust_score', 'total_orders', 'pending_orders', 'orders_today']]);
    }

    public function test_returns_insights_array(): void
    {
        $response = $this->getJson('/api/dashboard/insights');

        $response->assertStatus(200);
        $this->assertIsArray($response->json('data.insights'));
    }
}
