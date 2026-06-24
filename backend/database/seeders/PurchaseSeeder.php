<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        $users = User::all();

        if ($suppliers->isEmpty() || $products->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 10; $i++) {
            $itemsCount = rand(2, 4);
            $total = 0;
            $items = [];

            for ($j = 0; $j < $itemsCount; $j++) {
                $product = $products->random();
                $quantity = rand(10, 50);
                $price = $product->purchase_price;
                $subtotal = $price * $quantity;
                $total += $subtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }

            $purchaseDate = Carbon::now()->subDays(rand(0, 30));

            $purchase = Purchase::create([
                'supplier_id' => $suppliers->random()->id,
                'user_id' => $users->random()->id,
                'total' => $total,
                'date' => $purchaseDate->format('Y-m-d'),
            ]);

            $purchase->created_at = $purchaseDate;
            $purchase->updated_at = $purchaseDate;
            $purchase->save();

            foreach ($items as $itemData) {
                $item = new PurchaseItem($itemData);
                $item->purchase()->associate($purchase);
                $item->save();

                $product = $products->firstWhere('id', $itemData['product_id']);
                if ($product) {
                    $product->increment('quantity', $itemData['quantity']);
                }
            }
        }
    }
}
