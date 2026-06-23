<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;

class StorePolicy
{
    public function view(User $user, Store $store): bool
    {
        return $user->store?->id === $store->id || $user->role?->name === 'admin';
    }

    public function update(User $user, Store $store): bool
    {
        return $user->store?->id === $store->id;
    }

    public function manageProducts(User $user, Store $store): bool
    {
        return $user->store?->id === $store->id;
    }
}
