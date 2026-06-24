<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Http\Resources\ProductResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class HomeController extends BaseController
{
    public function index(): JsonResponse
    {
        $featured = $this->store->storeProducts()
            ->visible()
            ->featured()
            ->with('product')
            ->get()
            ->pluck('product')
            ->filter();

        return response()->json([
            'store' => [
                'id' => $this->store->id,
                'name' => $this->store->name,
                'slug' => $this->store->slug,
                'description' => $this->store->description,
                'logo' => $this->store->logo,
                'cover_image' => $this->store->cover_image,
                'primary_color' => $this->store->primary_color,
                'phone' => $this->store->phone,
                'whatsapp' => $this->store->whatsapp,
                'email' => $this->store->email,
                'address' => $this->store->address,
                'city' => $this->store->city,
                'trust_score' => $this->store->trust_score,
                'total_orders' => $this->store->total_orders,
                'confirmed_orders' => $this->store->confirmed_orders,
                'avg_delivery_days' => $this->store->avg_delivery_days,
                'accepts_cash' => $this->store->accepts_cash,
                'accepts_transfer' => $this->store->accepts_transfer,
                'accepts_multicaixa' => $this->store->accepts_multicaixa,
            ],
            'featured_products' => ProductResource::collection($featured),
        ]);
    }

    public function categories(): JsonResponse
    {
        $featured = $this->store->storeProducts()
            ->visible()
            ->featured()
            ->with('product')
            ->get()
            ->pluck('product')
            ->filter();

        return response()->json([
            'store' => [
                'id' => $this->store->id,
                'name' => $this->store->name,
                'slug' => $this->store->slug,
                'description' => $this->store->description,
                'logo' => $this->store->logo,
                'cover_image' => $this->store->cover_image,
                'primary_color' => $this->store->primary_color,
                'phone' => $this->store->phone,
                'whatsapp' => $this->store->whatsapp,
                'email' => $this->store->email,
                'address' => $this->store->address,
                'city' => $this->store->city,
                'trust_score' => $this->store->trust_score,
                'total_orders' => $this->store->total_orders,
                'confirmed_orders' => $this->store->confirmed_orders,
                'avg_delivery_days' => $this->store->avg_delivery_days,
                'accepts_cash' => $this->store->accepts_cash,
                'accepts_transfer' => $this->store->accepts_transfer,
                'accepts_multicaixa' => $this->store->accepts_multicaixa,
            ],
            'featured_products' => ProductResource::collection($featured),
        ]);
    }
}
