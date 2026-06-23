<?php

namespace App\Models;

use Database\Factories\StoreFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'user_id', 'name', 'slug', 'description', 'logo', 'cover_image',
    'primary_color', 'phone', 'email', 'address', 'city', 'is_active',
    'whatsapp', 'accepts_cash', 'accepts_transfer', 'accepts_multicaixa',
    'bank_name', 'bank_holder', 'bank_iban',
    'trust_score', 'total_orders', 'confirmed_orders', 'avg_delivery_days',
])]
class Store extends Model
{
    /** @use HasFactory<StoreFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'accepts_cash' => 'boolean',
            'accepts_transfer' => 'boolean',
            'accepts_multicaixa' => 'boolean',
            'trust_score' => 'decimal:2',
            'total_orders' => 'integer',
            'confirmed_orders' => 'integer',
            'avg_delivery_days' => 'decimal:2',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $store) {
            if (!$store->slug) {
                $store->slug = Str::slug($store->name);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function storeProducts(): HasMany
    {
        return $this->hasMany(StoreProduct::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function storeReviews(): HasMany
    {
        return $this->hasMany(StoreReview::class);
    }
}
