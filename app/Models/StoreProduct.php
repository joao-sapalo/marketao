<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['store_id', 'product_id', 'is_visible', 'featured', 'display_order'])]
class StoreProduct extends Model
{
    /** @use HasFactory<\Database\Factories\StoreProductFactory> */
    use HasFactory;

    protected $table = 'store_product';

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'featured' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true)->orderBy('display_order');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
