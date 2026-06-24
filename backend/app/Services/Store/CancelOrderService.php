<?php

namespace App\Services\Store;

use App\Models\AuditLog;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CancelOrderService
{
    public function __construct(
        private Order $order,
        private ?string $reason = null,
        private ?int $userId = null
    ) {}

    public function call(): array
    {
        $cancellableStatuses = [Order::PENDING, Order::CONFIRMED];
        if (!in_array($this->order->status, $cancellableStatuses)) {
            return ['success' => false, 'errors' => ['Pedido não pode ser cancelado no estado atual.'], 'order' => null];
        }

        return DB::transaction(function () {
            $this->order->update([
                'status' => Order::CANCELLED,
                'cancelled_at' => now(),
                'cancel_reason' => $this->reason,
            ]);

            // Restore stock if order was confirmed (stock was already deducted)
            if ($this->order->status === Order::CONFIRMED) {
                foreach ($this->order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('quantity', $item->quantity);
                    }
                }
            }

            AuditLog::create([
                'user_id' => $this->userId,
                'action' => 'order_cancelled',
                'entity_type' => 'order',
                'entity_id' => $this->order->id,
                'description' => "Pedido {$this->order->reference} cancelado. Motivo: {$this->reason}",
                'ip_address' => request()->ip(),
            ]);

            return ['success' => true, 'order' => $this->order->fresh(), 'errors' => null];
        });
    }
}
