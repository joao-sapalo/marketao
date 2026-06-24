<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountPayable;
use App\Models\AccountReceivable;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalSales = Sale::where('status', Sale::STATUS_COMPLETED)->sum('total');
        $totalPurchases = Purchase::sum('total');

        $monthlyProfit = Sale::where('status', Sale::STATUS_COMPLETED)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->get()
            ->sum(fn ($sale) => $sale->profit);

        $lowStockCount = Product::lowStock()->count();
        $activeCustomers = Customer::count();

        $receivablesTotal = AccountReceivable::where('status', 'pending')->sum('amount');
        $payablesTotal = AccountPayable::where('status', 'pending')->sum('amount');

        $salesChart = Sale::where('status', Sale::STATUS_COMPLETED)
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->get()
            ->groupBy(fn ($sale) => $sale->created_at->format('Y-m'))
            ->map(fn ($items) => [
                'month' => $items->first()->created_at->format('Y-m'),
                'total' => $items->sum('total'),
            ])
            ->values();

        return response()->json([
            'total_sales' => $totalSales,
            'total_purchases' => $totalPurchases,
            'monthly_profit' => $monthlyProfit,
            'low_stock_count' => $lowStockCount,
            'active_customers' => $activeCustomers,
            'accounts_receivable_total' => $receivablesTotal,
            'accounts_payable_total' => $payablesTotal,
            'sales_chart' => $salesChart,
        ]);
    }
}
