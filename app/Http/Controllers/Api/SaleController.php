<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaleRequest;
use App\Http\Resources\SaleResource;
use App\Models\AccountReceivable;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Sale::with(['customer', 'items.product']);

        if ($request->get('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->get('customer_id')) {
            $query->where('customer_id', $request->get('customer_id'));
        }

        if ($request->get('start_date')) {
            $query->whereDate('created_at', '>=', $request->get('start_date'));
        }

        if ($request->get('end_date')) {
            $query->whereDate('created_at', '<=', $request->get('end_date'));
        }

        return SaleResource::collection($query->latest()->paginate(15));
    }

    public function store(SaleRequest $request): SaleResource
    {
        return DB::transaction(function () use ($request) {
            $total = 0;
            foreach ($request->items as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            $discount = $request->discount ?? 0;
            $totalAfterDiscount = $total - $discount;

            $sale = Sale::create([
                'customer_id' => $request->customer_id,
                'user_id' => $request->user()->id,
                'total' => max($totalAfterDiscount, 0),
                'discount' => $discount,
                'notes' => $request->notes,
                'status' => Sale::STATUS_COMPLETED,
            ]);

            foreach ($request->items as $item) {
                $subtotal = $item['price'] * $item['quantity'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $subtotal,
                ]);

                $product = Product::findOrFail($item['product_id']);
                $product->decrement('quantity', $item['quantity']);
            }

            if ($sale->customer_id) {
                AccountReceivable::create([
                    'customer_id' => $sale->customer_id,
                    'sale_id' => $sale->id,
                    'amount' => $sale->total,
                    'due_date' => Carbon::now()->addDays(30),
                    'status' => 'pending',
                ]);
            }

            return new SaleResource($sale->load(['customer', 'items.product']));
        });
    }

    public function update(SaleRequest $request, Sale $sale): SaleResource
    {
        $sale->update($request->only(['customer_id', 'discount', 'notes', 'status']));

        return new SaleResource($sale->load(['customer', 'items.product']));
    }

    public function show(Sale $sale): SaleResource
    {
        return new SaleResource($sale->load(['customer', 'items.product']));
    }

    public function destroy(Sale $sale): JsonResponse
    {
        if ($sale->status !== Sale::STATUS_DRAFT) {
            return response()->json(['message' => 'Only draft sales can be deleted'], 422);
        }

        $sale->delete();

        return response()->json(['message' => 'Sale deleted successfully']);
    }
}
