<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockMovementRequest;
use App\Http\Resources\StockMovementResource;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = StockMovement::with('product');

        if ($request->get('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }

        if ($request->get('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->get('start_date')) {
            $query->whereDate('date', '>=', $request->get('start_date'));
        }

        if ($request->get('end_date')) {
            $query->whereDate('date', '<=', $request->get('end_date'));
        }

        return StockMovementResource::collection($query->latest()->paginate(15));
    }

    public function store(StockMovementRequest $request): StockMovementResource
    {
        return DB::transaction(function () use ($request) {
            $movement = StockMovement::create([
                'product_id' => $request->product_id,
                'user_id' => $request->user()->id,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'reason' => $request->reason,
                'date' => $request->date,
            ]);

            $product = Product::findOrFail($request->product_id);

            match ($request->type) {
                'in' => $product->increment('quantity', $request->quantity),
                'out' => $product->decrement('quantity', $request->quantity),
                'adjustment' => $product->update(['quantity' => $request->quantity]),
                default => null,
            };

            return new StockMovementResource($movement->load('product'));
        });
    }

    public function show(StockMovement $stockMovement): StockMovementResource
    {
        return new StockMovementResource($stockMovement->load('product'));
    }

    public function destroy(StockMovement $stockMovement): JsonResponse
    {
        $stockMovement->delete();

        return response()->json(['message' => 'Stock movement deleted successfully']);
    }
}
