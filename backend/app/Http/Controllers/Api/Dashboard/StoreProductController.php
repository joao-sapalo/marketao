<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StoreProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        if (!$store) {
            return response()->json(['message' => 'Nenhuma loja encontrada.'], 404);
        }

        $products = Product::where('store_id', $store->id)
            ->orderBy('name')
            ->get();

        $storeProducts = $store->storeProducts()
            ->with('product')
            ->get()
            ->keyBy('product_id');

        return response()->json([
            'data' => $products->map(function ($product) use ($storeProducts) {
                $sp = $storeProducts->get($product->id);
                return [
                    'product_id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'sale_price' => $product->sale_price,
                    'quantity' => $product->quantity,
                    'is_visible' => $sp ? $sp->is_visible : false,
                    'featured' => $sp ? $sp->featured : false,
                    'display_order' => $sp ? $sp->display_order : 0,
                ];
            }),
        ]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $store = $request->user()->store;
        if (!$store || $product->store_id !== $store->id) {
            return response()->json(['message' => 'Produto não encontrado.'], 404);
        }

        $validated = $request->validate([
            'is_visible' => ['boolean'],
            'featured' => ['boolean'],
            'display_order' => ['integer', 'min:0'],
        ]);

        $storeProduct = StoreProduct::updateOrCreate(
            ['store_id' => $store->id, 'product_id' => $product->id],
            $validated
        );

        return response()->json([
            'message' => 'Produto actualizado na loja.',
            'data' => $storeProduct,
        ]);
    }
}
