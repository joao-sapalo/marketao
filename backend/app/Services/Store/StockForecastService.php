<?php

namespace App\Services\Store;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;

class StockForecastService
{
    private const FORECAST_DAYS = 7;

    public function __construct(
        private Store $store
    ) {}

    public function atRiskProducts(): array
    {
        $result = [];
        
        foreach ($this->store->storeProducts()->visible()->with('product')->get() as $sp) {
            $product = $sp->product;
            if (!$product) continue;
            
            $dailySales = $this->avgDailySales($product->id);
            if ($dailySales <= 0) continue;
            
            $daysLeft = $product->quantity / $dailySales;
            if ($daysLeft >= self::FORECAST_DAYS) continue;
            
            $result[] = [
                'product' => $product,
                'days_left' => round($daysLeft, 1),
                'daily_avg' => round($dailySales, 1),
            ];
        }

        usort($result, fn($a, $b) => $a['days_left'] <=> $b['days_left']);
        return $result;
    }

    private function avgDailySales(int $productId): float
    {
        $sold = OrderItem::where('product_id', $productId)
            ->whereHas('order', function ($q) {
                $q->where('store_id', $this->store->id)
                  ->whereIn('status', [1, 2, 3, 4]) // confirmed, processing, shipped, delivered
                  ->where('created_at', '>=', now()->subDays(30));
            })
            ->sum('quantity');

        return $sold / 30.0;
    }
}
