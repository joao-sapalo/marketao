<?php

namespace App\Services\Store;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Events\OrderCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(
        private Store $store,
        private array $data // guest_name, guest_phone, guest_whatsapp, guest_email, notes, items[{product_id, quantity}], payment_method
    ) {}

    public function call(): array
    {
        if (!$this->store->is_active) {
            return ['success' => false, 'errors' => ['Loja inativa.'], 'order' => null];
        }

        return DB::transaction(function () {
            $items = [];
            $subtotal = 0;

            foreach ($this->data['items'] as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('store_id', $this->store->id)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    throw new \RuntimeException("Produto #{$item['product_id']} não encontrado.");
                }

                if ($product->quantity < $item['quantity']) {
                    return ['success' => false, 'errors' => ["{$product->name} sem stock suficiente."], 'order' => null];
                }

                $unitPrice = $product->sale_price;
                $total = $unitPrice * $item['quantity'];
                $subtotal += $total;

                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'unit_price' => $unitPrice,
                    'quantity' => $item['quantity'],
                    'discount' => 0,
                    'total' => $total,
                ];
            }

            $order = Order::create([
                'store_id' => $this->store->id,
                'guest_name' => $this->data['guest_name'] ?? null,
                'guest_phone' => $this->data['guest_phone'] ?? null,
                'guest_whatsapp' => $this->data['guest_whatsapp'] ?? $this->data['guest_phone'] ?? null,
                'guest_email' => $this->data['guest_email'] ?? null,
                'notes' => $this->data['notes'] ?? null,
                'status' => Order::PENDING,
                'payment_method' => $this->data['payment_method'] ?? Order::CASH,
                'payment_status' => Order::UNPAID,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total' => $subtotal,
                'reference' => $this->generateOrderReference(),
                'payment_reference' => $this->generatePaymentReference(),
            ]);

            foreach ($items as $item) {
                $order->items()->create($item);
            }

            // Broadcast event
            event(new OrderCreated($order));

            return ['success' => true, 'order' => $order, 'errors' => null];
        });
    }

    private function generateOrderReference(): string
    {
        $year = now()->year;
        $seq = Order::whereYear('created_at', $year)->count() + 1;
        return "ORD-{$year}-" . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    private function generatePaymentReference(): string
    {
        return "PAG-" . now()->year . "-" . strtoupper(Str::random(6));
    }
}
