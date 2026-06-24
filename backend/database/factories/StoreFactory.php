<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company(),
            'slug' => fn(array $attrs) => Str::slug($attrs['name']),
            'description' => fake()->sentence(),
            'primary_color' => fake()->hexColor(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'is_active' => true,
            'whatsapp' => '244900000000',
            'accepts_cash' => true,
            'accepts_transfer' => true,
            'accepts_multicaixa' => false,
            'bank_name' => 'Banco Angolano',
            'bank_holder' => 'Loja Teste',
            'bank_iban' => 'AO00000000000000000000000',
            'trust_score' => 4.5,
            'total_orders' => 0,
            'confirmed_orders' => 0,
            'avg_delivery_days' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn() => ['is_active' => false]);
    }
}
