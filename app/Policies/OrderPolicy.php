<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->store?->id === $order->store_id;
    }

    public function confirm(User $user, Order $order): bool
    {
        return $user->store?->id === $order->store_id && $order->status === Order::PENDING;
    }

    public function verifyPayment(User $user, Order $order): bool
    {
        return $user->store?->id === $order->store_id && $order->payment_status === Order::PENDING_VERIFICATION;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->store?->id === $order->store_id && in_array($order->status, [Order::PENDING, Order::CONFIRMED]);
    }
}
