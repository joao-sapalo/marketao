<?php

namespace App\Services\Store;

use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Str;

class CreateStoreService
{
    public function __construct(
        private User $user
    ) {}

    public function call(): Store
    {
        return \DB::transaction(function () {
            $store = Store::create([
                'user_id' => $this->user->id,
                'name' => $this->defaultName(),
                'slug' => $this->generateUniqueSlug(),
                'email' => $this->user->email,
                'whatsapp' => $this->user->phone,
                'phone' => $this->user->phone,
                'is_active' => false,
            ]);
            return $store;
        });
    }

    private function defaultName(): string
    {
        return "Loja de {$this->user->name}";
    }

    private function generateUniqueSlug(): string
    {
        $base = Str::slug($this->defaultName());
        $candidate = $base;
        $n = 1;
        while (Store::where('slug', $candidate)->exists()) {
            $candidate = "{$base}-{$n}";
            $n++;
        }
        return $candidate;
    }
}
