<?php

namespace Tests\Feature\Api\Dashboard;

use App\Models\Store;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_show_own_store(): void
    {
        $store = Store::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/dashboard/store');

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $store->id);
    }

    public function test_returns_404_when_no_store(): void
    {
        $response = $this->getJson('/api/dashboard/store');

        $response->assertStatus(404);
    }

    public function test_can_update_store(): void
    {
        Store::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson('/api/dashboard/store', [
            'name' => 'Loja Atualizada',
            'description' => 'Nova descrição',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Loja actualizada.')
            ->assertJsonPath('data.name', 'Loja Atualizada');
    }

    public function test_update_validates_fields(): void
    {
        Store::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson('/api/dashboard/store', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422);
    }

}
