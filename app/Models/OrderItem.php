<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'unit_purchase_price',
        'unit_price',
        'quantity',
        'subtotal',
        'name',
        'sku',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'unit_purchase_price' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
            'subtotal' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function getSkuAttribute(): string
    {
        return $this->attributes['product_sku'] ?? '';
    }

    public function setSkuAttribute(?string $value): void
    {
        $this->attributes['product_sku'] = $value;
    }

    public function getNameAttribute(): string
    {
        return $this->attributes['product_name'] ?? '';
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['product_name'] = $value;
    }

    public function getPriceAttribute(): float
    {
        return (float) ($this->attributes['unit_price'] ?? 0.0);
    }

    public function setPriceAttribute($value): void
    {
        $this->attributes['unit_price'] = $value;
    }
}
