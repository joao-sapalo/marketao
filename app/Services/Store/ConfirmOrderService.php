<?php

namespace App\Services\Store;

use App\Models\AccountReceivable;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

class ConfirmOrderService
{
    public function __construct(
        private Order $order,
        private ?int $userId = null
    ) {}

    public function call(): array
    {
        if ($this->order->status !== Order::PENDING) {
            return ['success' => false, 'errors' => ['Pedido não está pendente.'], 'order' => null];
        }

        return DB::transaction(function () {
            $store = $this->order->store;

            // Verify stock again with lock
            foreach ($this->order->items as $item) {
                $product = $item->product;
                if (!$product) continue;

                $product = Product::lockForUpdate()->find($product->id);
                if (!$product || $product->quantity < $item->quantity) {
                    throw new \RuntimeException("Stock insuficiente para {$item->product_name}.");
                }
            }

            // Create Sale
            $sale = Sale::create([
                'store_id' => $store->id,
                'user_id' => $this->userId,
                'total' => $this->order->total,
                'status' => Sale::STATUS_PENDING,
                'source' => 'online',
                'customer_name' => $this->order->guest_name,
                'customer_phone' => $this->order->guest_phone,
                'customer_email' => $this->order->guest_email,
                'payment_method' => match ($this->order->payment_method) {
                    Order::CASH => 'cash',
                    Order::TRANSFER => 'transfer',
                    Order::MULTICAIXA => 'multicaixa',
                    default => 'cash',
                },
            ]);

            // Create SaleItems and StockMovements
            foreach ($this->order->items as $item) {
                $sale->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price,
                    'subtotal' => $item->total,
                ]);

                // Decrement stock
                if ($item->product) {
                    $item->product->decrement('quantity', $item->quantity);
                }

                // Stock movement
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'user_id' => $this->userId,
                    'type' => StockMovement::TYPE_OUT,
                    'quantity' => $item->quantity,
                    'reason' => "Venda online - Pedido {$this->order->reference}",
                    'date' => now(),
                ]);
            }

            // Create AccountReceivable if payment method is not cash and not paid
            if ($this->order->payment_method !== Order::CASH && $this->order->payment_status !== Order::PAID) {
                AccountReceivable::create([
                    'sale_id' => $sale->id,
                    'amount' => $this->order->total,
                    'due_date' => now()->addDays(3),
                    'status' => 'pending',
                ]);
            }

            // Update order
            $this->order->update([
                'status' => Order::CONFIRMED,
                'confirmed_at' => now(),
                'sale_id' => $sale->id,
            ]);

            // Audit log
            AuditLog::create([
                'user_id' => $this->userId,
                'action' => 'order_confirmed',
                'entity_type' => 'order',
                'entity_id' => $this->order->id,
                'description' => "Pedido {$this->order->reference} confirmado. Venda #{$sale->id} criada.",
                'ip_address' => request()->ip(),
            ]);

            return ['success' => true, 'order' => $this->order->fresh(), 'errors' => null];
        });
    }
}
