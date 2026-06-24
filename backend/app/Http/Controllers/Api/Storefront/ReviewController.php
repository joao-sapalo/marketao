<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Models\Order;
use App\Models\StoreReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends BaseController
{
    public function store(string $storeSlug, Order $order, Request $request): JsonResponse
    {
        if ($order->store_id !== $this->store->id) {
            abort(404);
        }

        if ($order->status !== Order::DELIVERED) {
            return response()->json(['message' => 'Só é possível avaliar pedidos entregues.'], 422);
        }

        if ($order->storeReview()->exists()) {
            return response()->json(['message' => 'Este pedido já foi avaliado.'], 422);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $review = StoreReview::create([
            'store_id' => $this->store->id,
            'order_id' => $order->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'guest_name' => $order->guest_name,
            'is_approved' => true,
        ]);

        (new \App\Services\Store\TrustScoreCalculatorService($this->store))->call();

        return response()->json([
            'message' => 'Avaliação registada com sucesso! Obrigado.',
            'data' => $review,
        ], 201);
    }
}
