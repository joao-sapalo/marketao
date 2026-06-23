<?php

namespace App\Http\Controllers\Web\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Store;

class HomeController extends Controller
{
    public function index(string $storeSlug)
    {
        $store = Store::where('slug', $storeSlug)->where('is_active', true)->firstOrFail();
        return view('store.home.index', compact('store'));
    }
}
