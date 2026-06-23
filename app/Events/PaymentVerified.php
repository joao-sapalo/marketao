<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentVerified implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("store.{$this->order->store_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'payment.verified';
    }

    public function broadcastWith(): array
    {
        return [
            'event' => 'payment_verified',
            'order_id' => $this->order->id,
            'reference' => $this->order->reference,
        ];
    }
}
