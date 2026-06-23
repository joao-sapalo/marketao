<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = $this->store->products()
            ->where('is_active', true);

        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate(20);

        return response()->json([
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(string $storeSlug, Product $product): JsonResponse
    {
        if ($product->store_id !== $this->store->id || !$product->is_active) {
            abort(404);
        }

        $stockLabel = match (true) {
            $product->quantity > 5 => 'disponivel',
            $product->quantity > 0 => 'ultimas_unidades',
            default => 'esgotado',
        };

        return response()->json([
            'data' => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'description' => $product->description,
                'sale_price' => $product->sale_price,
                'image' => $product->image,
                'stock_label' => $stockLabel,
                'category' => $product->category?->only(['id', 'name']),
            ],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $search = $request->get('q', '');
        if (strlen($search) < 2) {
            return response()->json(['data' => []]);
        }

        $products = $this->store->products()
            ->where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'sale_price', 'quantity', 'image']);

        return response()->json([
            'data' => $products->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sale_price' => $p->sale_price,
                    'image' => $p->image,
                    'stock_label' => match (true) {
                        $p->quantity > 5 => 'disponivel',
                        $p->quantity > 0 => 'ultimas_unidades',
                        default => 'esgotado',
                    },
                ];
            }),
        ]);
    }
}
