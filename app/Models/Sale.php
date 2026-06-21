<?php

namespace App\Models;

use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['customer_id', 'user_id', 'total', 'discount', 'notes', 'status', 'store_id', 'source', 'customer_name', 'customer_phone', 'customer_email', 'delivery_address', 'payment_method'])]
class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use HasFactory;

    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function accountReceivable(): HasOne
    {
        return $this->hasOne(AccountReceivable::class);
    }

    public function getProfitAttribute(): float
    {
        $this->loadMissing('items.product');

        return $this->items->sum(function (SaleItem $item) {
            $cost = $item->product?->purchase_price ?? 0;
            return ($item->price - $cost) * $item->quantity;
        });
    }
}
