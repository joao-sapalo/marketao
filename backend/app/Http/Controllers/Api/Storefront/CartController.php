<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Models\Product;
use App\Services\Store\NaturalLanguageCartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends BaseController
{
    public function show(Request $request): JsonResponse
    {
        $cart = $request->session()->get("cart_{$this->store->id}", []);
        $items = [];
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $product = Product::find($productId);
            if (!$product) continue;
            $subtotal = $product->sale_price * $quantity;
            $total += $subtotal;
            $items[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sale_price' => $product->sale_price,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];
        }

        return response()->json([
            'data' => [
                'items' => $items,
                'total' => $total,
                'item_count' => array_sum($cart),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $product = Product::where('id', $validated['product_id'])
            ->where('store_id', $this->store->id)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Produto não encontrado.'], 404);
        }

        $cart = $request->session()->get("cart_{$this->store->id}", []);

        if ($validated['quantity'] === 0) {
            unset($cart[$validated['product_id']]);
        } else {
            $cart[$validated['product_id']] = $validated['quantity'];
        }

        $request->session()->put("cart_{$this->store->id}", $cart);

        return response()->json([
            'message' => 'Carrinho actualizado.',
            'item_count' => array_sum($cart),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->session()->forget("cart_{$this->store->id}");
        return response()->json(['message' => 'Carrinho limpo.']);
    }

    public function interpret(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2'],
        ]);

        $service = new NaturalLanguageCartService($this->store, $validated['query']);
        $result = $service->call();

        $cart = $request->session()->get("cart_{$this->store->id}", []);

        foreach ($result['items'] as $item) {
            $pid = $item['product_id'];
            $cart[$pid] = ($cart[$pid] ?? 0) + $item['quantity'];
        }

        $request->session()->put("cart_{$this->store->id}", $cart);

        return response()->json([
            'data' => [
                'items' => $result['items'],
                'unmatched' => $result['unmatched'],
                'cart_count' => array_sum($cart),
            ],
        ]);
    }
}
