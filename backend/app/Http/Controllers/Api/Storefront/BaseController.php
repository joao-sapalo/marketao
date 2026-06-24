<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Store;

class BaseController extends Controller
{
    protected Store $store;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $slug = $request->route('store_slug');
            $this->store = Store::where('slug', $slug)
                ->where('is_active', true)
                ->firstOrFail();
            return $next($request);
        });
    }
}
