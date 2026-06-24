<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => \App\Models\Store::factory(),
            'code' => fake()->unique()->ean13(),
            'name' => fake()->unique()->word(),
            'category_id' => Category::factory(),
            'description' => fake()->optional()->sentence(),
            'purchase_price' => fake()->randomFloat(2, 1, 100),
            'sale_price' => fake()->randomFloat(2, 10, 500),
            'quantity' => fake()->numberBetween(0, 100),
            'min_stock' => fake()->numberBetween(1, 20),
            'supplier_id' => Supplier::factory(),
            'image' => fake()->optional()->imageUrl(),
            'is_active' => fake()->boolean(90),
        ];
    }
}
