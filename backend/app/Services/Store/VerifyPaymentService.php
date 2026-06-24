<?php

namespace App\Services\Store;

use App\Models\AccountReceivable;
use App\Models\AuditLog;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class VerifyPaymentService
{
    public function __construct(
        private Order $order,
        private ?int $userId = null
    ) {}

    public function call(): array
    {
        if ($this->order->payment_status !== Order::PENDING_VERIFICATION) {
            return ['success' => false, 'errors' => ['Pagamento não está pendente de verificação.'], 'order' => null];
        }

        return DB::transaction(function () {
            $this->order->update([
                'payment_status' => Order::PAID,
                'paid_at' => now(),
            ]);

            if ($this->order->status === Order::CONFIRMED) {
                $this->order->update(['status' => Order::PROCESSING]);
            }

            // Mark AccountReceivable as paid if exists
            if ($this->order->sale && $this->order->sale->accountReceivable) {
                $this->order->sale->accountReceivable->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            AuditLog::create([
                'user_id' => $this->userId,
                'action' => 'payment_verified',
                'entity_type' => 'order',
                'entity_id' => $this->order->id,
                'description' => "Pagamento verificado para pedido {$this->order->reference}.",
                'ip_address' => request()->ip(),
            ]);

            return ['success' => true, 'order' => $this->order->fresh(), 'errors' => null];
        });
    }
}
