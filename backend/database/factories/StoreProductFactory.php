<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use App\Models\StoreProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreProductFactory extends Factory
{
    protected $model = StoreProduct::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'product_id' => Product::factory(),
            'is_visible' => true,
            'featured' => false,
            'display_order' => 0,
        ];
    }

    public function visible(): static
    {
        return $this->state(fn() => ['is_visible' => true]);
    }

    public function hidden(): static
    {
        return $this->state(fn() => ['is_visible' => false]);
    }

    public function featured(): static
    {
        return $this->state(fn() => ['featured' => true, 'display_order' => 1]);
    }
}
