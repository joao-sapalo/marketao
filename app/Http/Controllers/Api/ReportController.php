<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales(Request $request): JsonResponse
    {
        $query = Sale::with(['customer', 'items.product'])
            ->where('status', Sale::STATUS_COMPLETED);

        if ($request->get('start_date')) {
            $query->whereDate('created_at', '>=', $request->get('start_date'));
        }

        if ($request->get('end_date')) {
            $query->whereDate('created_at', '<=', $request->get('end_date'));
        }

        $sales = $query->latest()->get();

        $total = $sales->sum('total');
        $totalDiscount = $sales->sum('discount');
        $totalProfit = $sales->sum(fn ($sale) => $sale->profit);

        return response()->json([
            'total_sales' => $total,
            'total_discount' => $totalDiscount,
            'total_profit' => $totalProfit,
            'sales_count' => $sales->count(),
            'sales' => $sales,
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'supplier']);

        if ($request->get('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        $products = $query->get();

        return response()->json([
            'total_products' => $products->count(),
            'low_stock_count' => $products->filter(fn ($p) => $p->quantity <= $p->min_stock)->count(),
            'total_stock_value' => $products->sum(fn ($p) => $p->purchase_price * $p->quantity),
            'products' => ProductResource::collection($products),
        ]);
    }

    public function financial(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());

        $totalSales = Sale::where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');

        $totalPurchases = Purchase::whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');

        $salesCount = Sale::where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $purchasesCount = Purchase::whereBetween('created_at', [$startDate, $endDate])
            ->count();

        return response()->json([
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'total_sales' => $totalSales,
            'total_purchases' => $totalPurchases,
            'sales_count' => $salesCount,
            'purchases_count' => $purchasesCount,
            'profit' => $totalSales - $totalPurchases,
        ]);
    }
}
