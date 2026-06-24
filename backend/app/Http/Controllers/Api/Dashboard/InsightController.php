<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Store\StockForecastService;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsightController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        if (!$store) {
            return response()->json(['message' => 'Nenhuma loja encontrada.'], 404);
        }

        $forecastService = new StockForecastService($store);
        $atRisk = $forecastService->atRiskProducts();

        // Sales pattern: best day of week
        $bestDay = $store->orders()
            ->whereIn('status', [Order::CONFIRMED, Order::PROCESSING, Order::SHIPPED, Order::DELIVERED])
            ->selectRaw("EXTRACT(DOW FROM created_at) as day_num, COUNT(*) as total")
            ->groupBy('day_num')
            ->orderBy('total', 'desc')
            ->first();

        $dayNames = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];

        // Customer churn: customers who haven't bought recently
        $lastOrderDates = $store->orders()
            ->whereIn('status', [Order::CONFIRMED, Order::PROCESSING, Order::SHIPPED, Order::DELIVERED])
            ->selectRaw('guest_phone, MAX(created_at) as last_order')
            ->whereNotNull('guest_phone')
            ->groupBy('guest_phone')
            ->having('last_order', '<', now()->subDays(15))
            ->get();

        // Accounts receivable overdue
        $overdueReceivables = $store->sales()
            ->whereHas('accountReceivable', function ($q) {
                $q->where('status', 'pending')->where('due_date', '<', now());
            })
            ->with('accountReceivable')
            ->get();

        $overdueTotal = $overdueReceivables->sum(fn($s) => $s->accountReceivable?->amount ?? 0);

        // At-risk products insights
        $stockInsights = array_map(function ($item) {
            $p = $item['product'];
            return "⚠ {$p->name} vai esgotar em ~{$item['days_left']} dias ao ritmo actual";
        }, $atRisk);

        // Build insights array
        $insights = [];

        if (!empty($stockInsights)) {
            $insights = array_merge($insights, $stockInsights);
        }

        if ($bestDay) {
            $dayName = $dayNames[(int)$bestDay->day_num] ?? 'Desconhecido';
            $insights[] = "📈 {$dayName} é o teu melhor dia — tens stock?";
        }

        foreach ($lastOrderDates as $customer) {
            $daysAgo = now()->diffInDays($customer->last_order);
            if ($daysAgo > 15) {
                $insights[] = "👤 Cliente {$customer->guest_phone} não compra há {$daysAgo} dias";
            }
        }

        if ($overdueTotal > 0) {
            $insights[] = "💰 Contas a receber em atraso: " . number_format($overdueTotal, 2, ',', ' ') . " AOA — {$overdueReceivables->count()} clientes";
        }

        // Trust score overview
        $insights[] = "⭐ Trust Score actual: {$store->trust_score}/100 — " . ($store->trust_score >= 70 ? 'Bom' : ($store->trust_score >= 40 ? 'Médio' : 'Precisa melhorar'));

        return response()->json([
            'data' => [
                'insights' => $insights,
                'trust_score' => $store->trust_score,
                'total_orders' => $store->total_orders,
                'confirmed_orders' => $store->confirmed_orders,
                'avg_delivery_days' => $store->avg_delivery_days,
                'pending_orders' => $store->orders()->where('status', Order::PENDING)->count(),
                'orders_today' => $store->orders()->whereDate('created_at', today())->count(),
            ],
        ]);
    }
}
