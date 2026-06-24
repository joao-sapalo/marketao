<?php

use App\Models\Store;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('store.{storeId}', function ($user, $storeId) {
    return $user->store?->id === (int) $storeId;
});
