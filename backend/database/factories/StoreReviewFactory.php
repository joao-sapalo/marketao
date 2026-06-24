<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Store;
use App\Models\StoreReview;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreReviewFactory extends Factory
{
    protected $model = StoreReview::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'order_id' => Order::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->sentence(),
            'guest_name' => fake()->name(),
            'is_approved' => true,
        ];
    }

    public function unapproved(): static
    {
        return $this->state(fn() => ['is_approved' => false]);
    }

    public function withRating(int $rating): static
    {
        return $this->state(fn() => ['rating' => $rating]);
    }
}
