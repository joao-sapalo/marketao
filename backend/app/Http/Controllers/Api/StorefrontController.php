<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class StorefrontController extends Controller
{
    public function store(string $slug): JsonResponse
    {
        $store = Store::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return response()->json([
            'data' => [
                'id' => $store->id,
                'name' => $store->name,
                'slug' => $store->slug,
                'description' => $store->description,
                'logo' => $store->logo,
                'cover_image' => $store->cover_image,
                'primary_color' => $store->primary_color,
                'phone' => $store->phone,
                'email' => $store->email,
                'address' => $store->address,
                'city' => $store->city,
            ],
        ]);
    }

    public function products(string $slug, Request $request): AnonymousResourceCollection
    {
        $store = Store::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $query = Product::with(['category', 'supplier'])
            ->where('store_id', $store->id)
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

        return ProductResource::collection($query->paginate(20));
    }

    public function product(string $slug, Product $product): JsonResponse
    {
        $store = Store::where('slug', $slug)->where('is_active', true)->firstOrFail();

        if ($product->store_id !== $store->id || !$product->is_active) {
            abort(404);
        }

        return response()->json([
            'data' => new ProductResource($product->load(['category', 'supplier'])),
        ]);
    }

    public function categories(string $slug): JsonResponse
    {
        $store = Store::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $categories = Category::where('store_id', $store->id)
            ->whereHas('products', function ($q) {
                $q->where('is_active', true);
            })
            ->get(['id', 'name', 'description']);

        return response()->json(['data' => $categories]);
    }

    public function checkout(string $slug, Request $request): JsonResponse
    {
        $store = Store::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'customer_email' => ['nullable', 'email'],
            'delivery_address' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($store, $validated) {
            $total = 0;
            $items = [];

            foreach ($validated['items'] as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('store_id', $store->id)
                    ->where('is_active', true)
                    ->firstOrFail();

                if ($product->quantity < $item['quantity']) {
                    return response()->json([
                        'message' => "Produto {$product->name} sem stock suficiente.",
                    ], 400);
                }

                $price = $product->sale_price;
                $subtotal = $price * $item['quantity'];
                $total += $subtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];

                $product->decrement('quantity', $item['quantity']);
            }

            $sale = Sale::create([
                'store_id' => $store->id,
                'total' => $total,
                'status' => 'pending',
                'source' => 'online',
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'delivery_address' => $validated['delivery_address'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $sale->items()->create($item);
            }

            return response()->json([
                'message' => 'Encomenda recebida com sucesso!',
                'data' => [
                    'sale_id' => $sale->id,
                    'total' => $total,
                    'status' => 'pending',
                ],
            ], 201);
        });
    }
}
