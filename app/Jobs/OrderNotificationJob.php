<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Notifications\WhatsappService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class OrderNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $orderId
    ) {}

    public function handle(): void
    {
        $order = Order::with('store')->find($this->orderId);
        if (!$order) return;

        $store = $order->store;

        (new WhatsappService(
            phone: $order->guest_whatsapp ?? $order->guest_phone,
            event: 'order_created',
            variables: ['reference' => $order->reference, 'total' => (string)$order->total]
        ))->call();

        (new WhatsappService(
            phone: $store->whatsapp,
            event: 'new_order_merchant',
            variables: ['reference' => $order->reference, 'customer' => $order->guest_name ?? 'Cliente']
        ))->call();
    }
}
