<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentProofController extends BaseController
{
    public function store(string $storeSlug, Order $order, Request $request): JsonResponse
    {
        if ($order->store_id !== $this->store->id) {
            abort(404);
        }

        if ($order->payment_status !== Order::UNPAID) {
            return response()->json(['message' => 'Pagamento já foi processado.'], 422);
        }

        $validated = $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $path = $request->file('proof')->store("payment-proofs/{$this->store->id}/{$order->id}", 'public');

        $order->update([
            'payment_status' => Order::PENDING_VERIFICATION,
            'payment_proof_path' => $path,
        ]);

        event(new \App\Events\PaymentVerified($order));

        return response()->json([
            'message' => 'Comprovativo recebido. Aguarda verificação do comerciante.',
        ]);
    }
}
