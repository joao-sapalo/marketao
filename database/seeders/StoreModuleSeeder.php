<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreProduct;
use App\Models\StoreReview;
use App\Services\Store\TrustScoreCalculatorService;
use Illuminate\Database\Seeder;

class StoreModuleSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::first();
        if (!$store) {
            $this->command->warn('Nenhuma loja encontrada. Executa o StoreSeeder primeiro.');
            return;
        }

        // Update store with payment config
        $store->update([
            'whatsapp' => '923000001',
            'accepts_cash' => true,
            'accepts_transfer' => true,
            'accepts_multicaixa' => false,
            'bank_name' => 'BFA',
            'bank_holder' => $store->name,
            'bank_iban' => 'AO06.0040.0000.0000.1234.1013.4',
            'is_active' => true,
        ]);

        // Expose products in the store
        $products = Product::where('store_id', $store->id)->take(10)->get();
        foreach ($products as $i => $product) {
            StoreProduct::firstOrCreate(
                ['store_id' => $store->id, 'product_id' => $product->id],
                [
                    'is_visible' => true,
                    'featured' => $i < 3,
                    'display_order' => $i + 1,
                ]
            );
        }

        // Create a sample delivered order with review
        $product1 = $products->first();
        $product2 = $products->skip(1)->first();

        if ($product1 && $product2) {
            $order = Order::create([
                'store_id' => $store->id,
                'guest_name' => 'Maria da Silva',
                'guest_phone' => '924000002',
                'guest_whatsapp' => '924000002',
                'status' => Order::DELIVERED,
                'payment_method' => Order::CASH,
                'payment_status' => Order::PAID,
                'subtotal' => 15000,
                'total' => 15000,
                'reference' => 'ORD-' . now()->year . '-00001',
                'confirmed_at' => now()->subDays(2),
                'delivered_at' => now()->subDays(1),
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product1->id,
                'product_name' => $product1->name,
                'product_code' => $product1->code,
                'unit_price' => $product1->sale_price,
                'quantity' => 2,
                'total' => $product1->sale_price * 2,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product2->id,
                'product_name' => $product2->name,
                'product_code' => $product2->code,
                'unit_price' => $product2->sale_price,
                'quantity' => 1,
                'total' => $product2->sale_price,
            ]);

            StoreReview::create([
                'store_id' => $store->id,
                'order_id' => $order->id,
                'rating' => 5,
                'comment' => 'Excelente serviço, entrega rápida!',
                'guest_name' => 'Maria da Silva',
                'is_approved' => true,
            ]);
        }

        // Create a pending order
        if ($product1) {
            Order::create([
                'store_id' => $store->id,
                'guest_name' => 'João Pedro',
                'guest_phone' => '925000003',
                'guest_whatsapp' => '925000003',
                'status' => Order::PENDING,
                'payment_method' => Order::TRANSFER,
                'payment_status' => Order::UNPAID,
                'subtotal' => 5000,
                'total' => 5000,
                'reference' => 'ORD-' . now()->year . '-00002',
                'payment_reference' => 'PAG-' . now()->year . '-' . strtoupper(substr(md5(rand()), 0, 6)),
            ])->items()->create([
                'product_id' => $product1->id,
                'product_name' => $product1->name,
                'product_code' => $product1->code,
                'unit_price' => $product1->sale_price,
                'quantity' => 1,
                'total' => $product1->sale_price,
            ]);
        }

        // Recalculate trust score
        try {
            (new TrustScoreCalculatorService($store))->call();
        } catch (\Exception $e) {
            $this->command->warn('Erro ao calcular trust score: ' . $e->getMessage());
        }
    }
}
