<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->word(),
            'product_code' => fake()->unique()->bothify('PROD-####'),
            'unit_price' => 500,
            'quantity' => fake()->numberBetween(1, 5),
            'discount' => 0,
            'total' => fn(array $attrs) => $attrs['unit_price'] * $attrs['quantity'],
        ];
    }

    public function forOrder(Order $order): static
    {
        return $this->state(fn() => ['order_id' => $order->id]);
    }
}
