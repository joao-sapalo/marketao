<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $products = Product::all();
        $users = User::all();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 15; $i++) {
            $itemsCount = rand(2, 5);
            $total = 0;
            $items = [];

            for ($j = 0; $j < $itemsCount; $j++) {
                $product = $products->random();
                $quantity = rand(1, 5);
                $price = $product->sale_price;
                $subtotal = $price * $quantity;
                $total += $subtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }

            $discount = (rand(0, 1) ? rand(100, 500) : 0);
            $finalTotal = $total - $discount;

            $saleDate = Carbon::now()->subDays(rand(0, 30));

            $sale = new Sale([
                'customer_id' => $customers->random()->id,
                'user_id' => $users->random()->id,
                'total' => max($finalTotal, 0),
                'discount' => $discount,
                'status' => Sale::STATUS_COMPLETED,
            ]);
            $sale->created_at = $saleDate;
            $sale->updated_at = $saleDate;
            $sale->save();

            foreach ($items as $itemData) {
                $item = new SaleItem($itemData);
                $item->sale()->associate($sale);
                $item->save();

                $product = $products->firstWhere('id', $itemData['product_id']);
                if ($product) {
                    $product->decrement('quantity', $itemData['quantity']);
                }
            }
        }
    }
}
