<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcast
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
        return 'order.created';
    }

    public function broadcastWith(): array
    {
        return [
            'event' => 'new_order',
            'order_id' => $this->order->id,
            'reference' => $this->order->reference,
            'customer' => $this->order->guest_name ?? 'Anónimo',
            'total' => number_format($this->order->total, 2, ',', ' ') . ' AOA',
            'created_at' => $this->order->created_at->format('H:i'),
        ];
    }
}
