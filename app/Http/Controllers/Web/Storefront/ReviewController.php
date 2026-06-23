<?php

namespace App\Http\Controllers\Web\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;

class ReviewController extends Controller
{
    public function create(string $storeSlug, Order $order)
    {
        $store = Store::where('slug', $storeSlug)->where('is_active', true)->firstOrFail();
        
        if ($order->store_id !== $store->id) {
            abort(404);
        }
        
        return view('store.reviews.new', compact('store', 'order'));
    }
}
