<?php

namespace App\Broadcasting;

use App\Models\Store;
use App\Models\User;

class StoreChannel
{
    public function join(User $user, Store $store): bool
    {
        return $user->store?->id === $store->id;
    }
}
