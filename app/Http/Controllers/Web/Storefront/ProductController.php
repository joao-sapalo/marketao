<?php

namespace App\Http\Controllers\Web\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(string $storeSlug, Request $request)
    {
        $store = Store::where('slug', $storeSlug)->where('is_active', true)->firstOrFail();
        
        $query = Product::where('store_id', $store->id)->where('is_active', true);
        
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        
        $products = $query->paginate(12);
        
        return view('store.products.index', compact('store', 'products'));
    }

    public function show(string $storeSlug, Product $product)
    {
        $store = Store::where('slug', $storeSlug)->where('is_active', true)->firstOrFail();
        
        if ($product->store_id !== $store->id || !$product->is_active) {
            abort(404);
        }
        
        return view('store.products.show', compact('store', 'product'));
    }
}
