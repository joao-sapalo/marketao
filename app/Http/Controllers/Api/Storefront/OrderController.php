<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Jobs\OrderNotificationJob;
use App\Models\Order;
use App\Models\Product;
use App\Services\Store\CheckoutService;
use App\Services\Store\CancelOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends BaseController
{
    public function byReference(string $storeSlug, string $reference): JsonResponse
    {
        $order = $this->store->orders()->where('reference', $reference)->firstOrFail();
        $order->load('items');

        return response()->json([
            'data' => $this->formatOrder($order),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $orders = $this->store->orders()
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => $orders->through(function ($order) {
                return [
                    'id' => $order->id,
                    'reference' => $order->reference,
                    'status' => $order->status,
                    'status_label' => $order->statusLabel(),
                    'total' => $order->total,
                    'item_count' => $order->items->sum('quantity'),
                    'created_at' => $order->created_at->format('d/m/Y H:i'),
                ];
            }),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    public function show(string $storeSlug, Order $order): JsonResponse
    {
        if ($order->store_id !== $this->store->id) {
            abort(404);
        }

        $order->load('items');

        return response()->json(['data' => $this->formatOrder($order)]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:20'],
            'guest_whatsapp' => ['nullable', 'string', 'max:20'],
            'guest_email' => ['nullable', 'email'],
            'payment_method' => ['required', 'integer', 'in:0,1,2'],
            'notes' => ['nullable', 'string'],
        ]);

        $service = new CheckoutService($this->store, $validated);
        $result = $service->call();

        if (!$result['success']) {
            return response()->json(['message' => implode(', ', $result['errors'])], 422);
        }

        OrderNotificationJob::dispatch($result['order']->id);

        $request->session()->forget("cart_{$this->store->id}");

        return response()->json([
            'message' => 'Pedido recebido com sucesso!',
            'data' => [
                'id' => $result['order']->id,
                'reference' => $result['order']->reference,
                'payment_reference' => $result['order']->payment_reference,
                'total' => $result['order']->total,
                'status' => $result['order']->status,
            ],
        ], 201);
    }

    private function formatOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'reference' => $order->reference,
            'payment_reference' => $order->payment_reference,
            'status' => $order->status,
            'status_label' => $order->statusLabel(),
            'payment_method' => $order->payment_method,
            'payment_method_label' => $order->paymentMethodLabel(),
            'payment_status' => $order->payment_status,
            'payment_status_label' => $order->paymentStatusLabel(),
            'guest_name' => $order->guest_name,
            'guest_phone' => $order->guest_phone,
            'subtotal' => $order->subtotal,
            'discount' => $order->discount,
            'total' => $order->total,
            'notes' => $order->notes,
            'confirmed_at' => $order->confirmed_at?->format('d/m/Y H:i'),
            'paid_at' => $order->paid_at?->format('d/m/Y H:i'),
            'delivered_at' => $order->delivered_at?->format('d/m/Y H:i'),
            'created_at' => $order->created_at->format('d/m/Y H:i'),
            'items' => $order->items->map(function ($item) {
                return [
                    'product_name' => $item->product_name,
                    'product_code' => $item->product_code,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'total' => $item->total,
                ];
            }),
            'store' => [
                'name' => $this->store->name,
                'whatsapp' => $this->store->whatsapp,
                'phone' => $this->store->phone,
                'bank_name' => $this->store->bank_name,
                'bank_holder' => $this->store->bank_holder,
                'bank_iban' => $this->store->bank_iban,
            ],
        ];
    }
}
