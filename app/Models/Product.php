<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'purchase_price',
        'regular_price',
        'sale_price',
        'stock_quantity',
        'low_stock_threshold',
        'is_featured',
        'is_active',
        'price',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'regular_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /* -------------------------------------------------------------------------
     | Relationships
     | ------------------------------------------------------------------------- */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /* -------------------------------------------------------------------------
     | Accessors & Helpers
     | ------------------------------------------------------------------------- */

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->effective_price,
            set: fn ($value) => ['regular_price' => $value]
        );
    }

    public function getStockAttribute(): int
    {
        return (int) ($this->attributes['stock_quantity'] ?? 0);
    }

    public function setStockAttribute($value): void
    {
        $this->attributes['stock_quantity'] = (int) $value;
    }

    protected function effectivePrice(): Attribute
    {
        return Attribute::make(
            get: fn () => ($this->sale_price !== null && $this->sale_price > 0 && $this->sale_price < $this->regular_price)
                ? (float) $this->sale_price
                : (float) $this->regular_price
        );
    }

    protected function hasDiscount(): Attribute
    {
        return Attribute::make(
            get: fn () => ($this->sale_price !== null && $this->sale_price > 0 && $this->sale_price < $this->regular_price)
        );
    }

    protected function discountPercentage(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->has_discount) {
                    return 0;
                }
                return (int) round((($this->regular_price - $this->sale_price) / $this->regular_price) * 100);
            }
        );
    }

    protected function inStock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock_quantity > 0
        );
    }

    protected function isLowStock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock_quantity > 0 && $this->stock_quantity <= $this->low_stock_threshold
        );
    }

    protected function averageRating(): Attribute
    {
        return Attribute::make(
            get: fn () => round((float) ($this->approvedReviews()->avg('rating') ?? 0), 1)
        );
    }

    protected function reviewsCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->approvedReviews()->count()
        );
    }

    /* -------------------------------------------------------------------------
     | Scopes
     | ------------------------------------------------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0);
    }
}
