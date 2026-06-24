<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseRequest;
use App\Http\Resources\PurchaseResource;
use App\Models\AccountPayable;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Purchase::with(['supplier', 'items.product']);

        if ($request->get('supplier_id')) {
            $query->where('supplier_id', $request->get('supplier_id'));
        }

        if ($request->get('start_date')) {
            $query->whereDate('date', '>=', $request->get('start_date'));
        }

        if ($request->get('end_date')) {
            $query->whereDate('date', '<=', $request->get('end_date'));
        }

        return PurchaseResource::collection($query->latest()->paginate(15));
    }

    public function store(PurchaseRequest $request): PurchaseResource
    {
        return DB::transaction(function () use ($request) {
            $total = 0;
            foreach ($request->items as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'user_id' => $request->user()->id,
                'total' => $total,
                'date' => $request->date ?? Carbon::now(),
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $subtotal = $item['price'] * $item['quantity'];

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $subtotal,
                ]);

                $product = Product::findOrFail($item['product_id']);
                $product->increment('quantity', $item['quantity']);
            }

            if ($purchase->supplier_id) {
                AccountPayable::create([
                    'supplier_id' => $purchase->supplier_id,
                    'purchase_id' => $purchase->id,
                    'amount' => $purchase->total,
                    'due_date' => Carbon::now()->addDays(30),
                    'status' => 'pending',
                ]);
            }

            return new PurchaseResource($purchase->load(['supplier', 'items.product']));
        });
    }

    public function show(Purchase $purchase): PurchaseResource
    {
        return new PurchaseResource($purchase->load(['supplier', 'items.product']));
    }

    public function update(PurchaseRequest $request, Purchase $purchase): PurchaseResource
    {
        $purchase->update($request->only(['supplier_id', 'date', 'notes']));

        return new PurchaseResource($purchase->load(['supplier', 'items.product']));
    }

    public function destroy(Purchase $purchase): JsonResponse
    {
        $purchase->delete();

        return response()->json(['message' => 'Purchase deleted successfully']);
    }
}
