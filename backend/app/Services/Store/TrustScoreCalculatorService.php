<?php

namespace App\Services\Store;

use App\Models\Order;
use App\Models\Store;

class TrustScoreCalculatorService
{
    private const WEIGHTS = [
        'confirmation_rate' => 40,
        'delivery_speed' => 30,
        'review_average' => 20,
        'volume' => 10,
    ];

    public function __construct(
        private Store $store
    ) {}

    public function call(): void
    {
        $score = (
            ($this->confirmationRate() * self::WEIGHTS['confirmation_rate']) +
            ($this->deliverySpeed() * self::WEIGHTS['delivery_speed']) +
            ($this->reviewAverage() * self::WEIGHTS['review_average']) +
            ($this->volumeScore() * self::WEIGHTS['volume'])
        ) / 100.0;

        $this->store->updateQuietly([
            'trust_score' => round($score, 2),
            'total_orders' => $this->totalOrdersCount(),
            'confirmed_orders' => $this->confirmedOrdersCount(),
            'avg_delivery_days' => $this->avgDelivery(),
        ]);
    }

    private function confirmationRate(): float
    {
        $total = $this->totalOrdersCount();
        if ($total === 0) return 0;
        return min(($this->confirmedOrdersCount() / $total) * 100, 100);
    }

    private function deliverySpeed(): float
    {
        $avg = $this->avgDelivery();
        if ($avg === 0.0) return 50;
        return match (true) {
            $avg <= 1 => 100,
            $avg <= 2 => 80,
            $avg <= 3 => 60,
            $avg <= 5 => 40,
            default => 20,
        };
    }

    private function reviewAverage(): float
    {
        $avg = $this->store->storeReviews()->avg('rating') ?? 0;
        return min(($avg / 5.0) * 100, 100);
    }

    private function volumeScore(): float
    {
        $n = $this->confirmedOrdersCount();
        if ($n === 0) return 0;
        return min(log($n, 500) * 100, 100);
    }

    private function totalOrdersCount(): int
    {
        return $this->store->orders()->count();
    }

    private function confirmedOrdersCount(): int
    {
        return $this->store->orders()->whereNotIn('status', [0, 5])->count();
    }

    private function avgDelivery(): float
    {
        return $this->store->orders()->where('status', Order::DELIVERED)
            ->whereNotNull('confirmed_at')
            ->whereNotNull('delivered_at')
            ->selectRaw('COALESCE(AVG(EXTRACT(EPOCH FROM (delivered_at - confirmed_at)) / 86400), 0) as avg_days')
            ->value('avg_days') ?? 0.0;
    }
}
