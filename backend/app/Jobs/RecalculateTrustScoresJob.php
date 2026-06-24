<?php

namespace App\Jobs;

use App\Models\Store;
use App\Services\Store\TrustScoreCalculatorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecalculateTrustScoresJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Store::chunk(100, function ($stores) {
            foreach ($stores as $store) {
                (new TrustScoreCalculatorService($store))->call();
            }
        });
    }
}
