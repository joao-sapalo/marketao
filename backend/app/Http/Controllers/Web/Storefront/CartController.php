<?php

namespace App\Http\Controllers\Web\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Store;

class CartController extends Controller
{
    public function show(string $storeSlug)
    {
        $store = Store::where('slug', $storeSlug)->where('is_active', true)->firstOrFail();
        $cart = session()->get("cart_{$store->id}", []);
        
        return view('store.cart.show', compact('store', 'cart'));
    }
}
