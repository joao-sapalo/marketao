<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Store\CancelOrderService;
use App\Services\Store\ConfirmOrderService;
use App\Services\Store\VerifyPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        if (!$store) {
            return response()->json(['message' => 'Nenhuma loja encontrada.'], 404);
        }

        $query = $store->orders()->with('items');

        // Filters
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($paymentStatus = $request->get('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $query->orderBy($sortField, $sortDir);

        $orders = $query->paginate(20);

        return response()->json([
            'data' => $orders->through(function ($order) {
                return [
                    'id' => $order->id,
                    'reference' => $order->reference,
                    'status' => $order->status,
                    'status_label' => $order->statusLabel(),
                    'payment_status' => $order->payment_status,
                    'payment_status_label' => $order->paymentStatusLabel(),
                    'payment_method_label' => $order->paymentMethodLabel(),
                    'guest_name' => $order->guest_name,
                    'total' => $order->total,
                    'item_count' => $order->items->sum('quantity'),
                    'created_at' => $order->created_at->format('d/m/Y H:i'),
                ];
            }),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $store = $request->user()->store;
        if (!$store || $order->store_id !== $store->id) {
            return response()->json(['message' => 'Pedido não encontrado.'], 404);
        }

        $order->load(['items', 'storeReview']);

        return response()->json([
            'data' => [
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
                'guest_whatsapp' => $order->guest_whatsapp,
                'guest_email' => $order->guest_email,
                'subtotal' => $order->subtotal,
                'discount' => $order->discount,
                'total' => $order->total,
                'notes' => $order->notes,
                'confirmed_at' => $order->confirmed_at,
                'paid_at' => $order->paid_at,
                'delivered_at' => $order->delivered_at,
                'cancelled_at' => $order->cancelled_at,
                'cancel_reason' => $order->cancel_reason,
                'created_at' => $order->created_at,
                'sale_id' => $order->sale_id,
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_name' => $item->product_name,
                        'product_code' => $item->product_code,
                        'unit_price' => $item->unit_price,
                        'quantity' => $item->quantity,
                        'discount' => $item->discount,
                        'total' => $item->total,
                        'product_id' => $item->product_id,
                    ];
                }),
                'review' => $order->storeReview ? [
                    'rating' => $order->storeReview->rating,
                    'comment' => $order->storeReview->comment,
                    'guest_name' => $order->storeReview->guest_name,
                    'created_at' => $order->storeReview->created_at,
                ] : null,
            ],
        ]);
    }

    public function confirm(Request $request, Order $order): JsonResponse
    {
        $store = $request->user()->store;
        if (!$store || $order->store_id !== $store->id) {
            return response()->json(['message' => 'Pedido não encontrado.'], 404);
        }

        $service = new ConfirmOrderService($order, $request->user()->id);
        $result = $service->call();

        if (!$result['success']) {
            return response()->json(['message' => implode(', ', $result['errors'])], 422);
        }

        return response()->json([
            'message' => 'Pedido confirmado com sucesso!',
            'data' => ['status' => $result['order']->status, 'status_label' => $result['order']->statusLabel()],
        ]);
    }

    public function verifyPayment(Request $request, Order $order): JsonResponse
    {
        $store = $request->user()->store;
        if (!$store || $order->store_id !== $store->id) {
            return response()->json(['message' => 'Pedido não encontrado.'], 404);
        }

        $service = new VerifyPaymentService($order, $request->user()->id);
        $result = $service->call();

        if (!$result['success']) {
            return response()->json(['message' => implode(', ', $result['errors'])], 422);
        }

        return response()->json([
            'message' => 'Pagamento verificado com sucesso!',
            'data' => ['payment_status' => $result['order']->payment_status, 'status_label' => $result['order']->statusLabel()],
        ]);
    }

    public function markShipped(Request $request, Order $order): JsonResponse
    {
        return $this->transitionStatus($request, $order, Order::SHIPPED, 'Enviado');
    }

    public function markDelivered(Request $request, Order $order): JsonResponse
    {
        return $this->transitionStatus($request, $order, Order::DELIVERED, 'Entregue');
    }

    public function markProcessing(Request $request, Order $order): JsonResponse
    {
        return $this->transitionStatus($request, $order, Order::PROCESSING, 'Em preparação');
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        $store = $request->user()->store;
        if (!$store || $order->store_id !== $store->id) {
            return response()->json(['message' => 'Pedido não encontrado.'], 404);
        }

        $reason = $request->get('reason', 'Cancelado pelo comerciante.');
        $service = new CancelOrderService($order, $reason, $request->user()->id);
        $result = $service->call();

        if (!$result['success']) {
            return response()->json(['message' => implode(', ', $result['errors'])], 422);
        }

        return response()->json([
            'message' => 'Pedido cancelado.',
            'data' => ['status' => $result['order']->status],
        ]);
    }

    private function transitionStatus(Request $request, Order $order, int $status, string $label): JsonResponse
    {
        $store = $request->user()->store;
        if (!$store || $order->store_id !== $store->id) {
            return response()->json(['message' => 'Pedido não encontrado.'], 404);
        }

        $order->update(['status' => $status]);

        // If delivered, dispatch review request after 24h
        if ($status === Order::DELIVERED) {
            $order->update(['delivered_at' => now()]);
            \App\Jobs\SendReviewRequestJob::dispatch($order->id)->delay(now()->addHours(24));
        }

        return response()->json([
            'message' => "Pedido marcado como {$label}.",
            'data' => ['status' => $order->fresh()->status],
        ]);
    }
}
