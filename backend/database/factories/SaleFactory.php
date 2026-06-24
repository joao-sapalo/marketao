<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
            'total' => fake()->randomFloat(2, 10, 5000),
            'discount' => fake()->randomFloat(2, 0, 100),
            'notes' => fake()->optional()->sentence(),
            'status' => fake()->randomElement([Sale::STATUS_COMPLETED, Sale::STATUS_CANCELLED, Sale::STATUS_DRAFT]),
        ];
    }
}
