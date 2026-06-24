<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'store_id', 'customer_id', 'guest_name', 'guest_phone', 'guest_whatsapp', 'guest_email',
    'status', 'payment_method', 'payment_status', 'payment_reference', 'payment_proof_path',
    'subtotal', 'discount', 'total', 'notes', 'reference', 'sale_id',
    'confirmed_at', 'paid_at', 'delivered_at', 'cancelled_at', 'cancel_reason',
])]
class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    public const PENDING = 0;
    public const CONFIRMED = 1;
    public const PROCESSING = 2;
    public const SHIPPED = 3;
    public const DELIVERED = 4;
    public const CANCELLED = 5;

    public const CASH = 0;
    public const TRANSFER = 1;
    public const MULTICAIXA = 2;

    public const UNPAID = 0;
    public const PENDING_VERIFICATION = 1;
    public const PAID = 2;
    public const PARTIAL = 3;
    public const REFUNDED = 4;

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'payment_method' => 'integer',
            'payment_status' => 'integer',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'paid_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'payment_proof_path' => 'string',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function storeReview(): HasOne
    {
        return $this->hasOne(StoreReview::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::PENDING => 'Pendente',
            self::CONFIRMED => 'Confirmado',
            self::PROCESSING => 'Em Preparação',
            self::SHIPPED => 'Enviado',
            self::DELIVERED => 'Entregue',
            self::CANCELLED => 'Cancelado',
            default => 'Desconhecido',
        };
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            self::CASH => 'Dinheiro',
            self::TRANSFER => 'Transferência',
            self::MULTICAIXA => 'Multicaixa Express',
            default => 'Desconhecido',
        };
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            self::UNPAID => 'Por Pagar',
            self::PENDING_VERIFICATION => 'A Verificar',
            self::PAID => 'Pago',
            self::PARTIAL => 'Parcial',
            self::REFUNDED => 'Reembolsado',
            default => 'Desconhecido',
        };
    }

    public function scopeByStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }
}
