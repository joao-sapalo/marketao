<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Notifications\WhatsappService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendReviewRequestJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $orderId
    ) {}

    public function handle(): void
    {
        $order = Order::with('store')->find($this->orderId);
        if (!$order) return;
        if ($order->storeReview()->exists()) return;

        $reviewUrl = config('app.url') . "/loja/{$order->store->slug}/orders/{$order->reference}/review";

        (new WhatsappService(
            phone: $order->guest_whatsapp ?? $order->guest_phone,
            event: 'review_request',
            variables: [
                'store_name' => $order->store->name,
                'review_url' => $reviewUrl,
            ]
        ))->call();
    }
}
