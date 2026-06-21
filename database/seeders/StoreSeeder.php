<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::firstOrCreate(
            ['slug' => 'marketao'],
            [
                'name' => 'MarketAO',
                'description' => 'A sua loja de confiança. Produtos de qualidade com os melhores preços.',
                'primary_color' => '#2563eb',
                'phone' => '+244 999 000 000',
                'email' => 'loja@marketao.com',
                'address' => 'Rua da Independência, 45',
                'city' => 'Luanda',
                'is_active' => true,
            ]
        );

        // Assign all existing products and categories to this store
        Product::whereNull('store_id')->update(['store_id' => $store->id]);
        \App\Models\Category::whereNull('store_id')->update(['store_id' => $store->id]);
    }
}
