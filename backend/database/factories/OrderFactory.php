<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'guest_name' => fake()->name(),
            'guest_phone' => '244900000000',
            'guest_whatsapp' => '244900000000',
            'status' => Order::PENDING,
            'payment_method' => Order::CASH,
            'payment_status' => Order::UNPAID,
            'subtotal' => 1000,
            'discount' => 0,
            'total' => 1000,
            'reference' => fn() => 'ORD-' . now()->year . '-' . fake()->unique()->numerify('#####'),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn() => [
            'status' => Order::CONFIRMED,
            'confirmed_at' => Carbon::now(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn() => [
            'status' => Order::CONFIRMED,
            'payment_status' => Order::PAID,
            'paid_at' => Carbon::now(),
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn() => [
            'status' => Order::DELIVERED,
            'payment_status' => Order::PAID,
            'paid_at' => Carbon::now(),
            'delivered_at' => Carbon::now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn() => [
            'status' => Order::CANCELLED,
            'cancelled_at' => Carbon::now(),
        ]);
    }
}
