<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'user_id',
        'recipient_name',
        'phone',
        'alternative_phone',
        'delivery_address',
        'division',
        'district',
        'shipping_zone',
        'shipping_cost',
        'subtotal',
        'discount_amount',
        'coupon_id',
        'total_amount',
        'payment_method',
        'payment_status',
        'order_status',
        'delivery_notes',
        'admin_notes',
        'courier_provider',
        'courier_tracking_id',
        'courier_consignment_id',
        'courier_status',
        'courier_error',
        'courier_response',
        'courier_dispatched_at',
        'gateway_transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'shipping_cost' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'courier_response' => 'array',
            'courier_dispatched_at' => 'datetime',
        ];
    }

    /* -------------------------------------------------------------------------
     | Relationships
     | ------------------------------------------------------------------------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /* -------------------------------------------------------------------------
     | Scopes
     | ------------------------------------------------------------------------- */

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_RETURNED = 'returned';

    /**
     * Allowed state transitions map
     *
     * @var array<string, list<string>>
     */
    public const ALLOWED_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
        self::STATUS_PROCESSING => [self::STATUS_SHIPPED, self::STATUS_CANCELLED],
        self::STATUS_SHIPPED => [self::STATUS_DELIVERED, self::STATUS_RETURNED],
        self::STATUS_DELIVERED => [self::STATUS_RETURNED],
        self::STATUS_CANCELLED => [],
        self::STATUS_RETURNED => [],
    ];

    /**
     * Verify whether a status transition is permitted from current state.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        if ($this->order_status === $newStatus) {
            return false;
        }

        $allowed = self::ALLOWED_TRANSITIONS[$this->order_status] ?? [];
        return in_array($newStatus, $allowed, true);
    }

    /**
     * Check if a customer may cancel this order.
     * Customers are permitted to cancel only while order is pending or confirmed (prior to processing/packing).
     */
    public function canBeCancelledByCustomer(): bool
    {
        return in_array($this->order_status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true);
    }

    /**
     * Determine if order has been booked/dispatched with a courier provider.
     */
    public function isDispatchedToCourier(): bool
    {
        return !empty($this->courier_tracking_id) || !empty($this->courier_consignment_id);
    }

    /**
     * Determine if order is eligible for courier dispatch.
     * Order must not already be dispatched and must be in confirmed or processing state.
     */
    public function canDispatchToCourier(): bool
    {
        return !$this->isDispatchedToCourier() 
            && in_array($this->order_status, [self::STATUS_CONFIRMED, self::STATUS_PROCESSING], true);
    }

    /**
     * Determine whether inventory has already been restored for this order.
     * Prevents duplicate stock incrementation during multiple cancellation or refund calls.
     */
    public function hasRestockedInventory(): bool
    {
        return \App\Models\InventoryMovement::where('order_id', $this->id)
            ->where('type', 'return')
            ->exists();
    }

    /**
     * Safely restore inventory for this order with an audit trail.
     * Returns true if restocked, false if already restocked previously.
     */
    public function restockInventorySafely(?User $changedByUser = null, ?string $reason = null): bool
    {
        if ($this->hasRestockedInventory()) {
            return false;
        }

        $this->loadMissing('items');

        foreach ($this->items as $item) {
            if (!$item->product_id) {
                continue;
            }

            $product = Product::lockForUpdate()->find($item->product_id);
            if (!$product) {
                continue;
            }

            $previousStock = $product->stock_quantity;
            $qtyToRestock = $item->quantity;
            $product->increment('stock_quantity', $qtyToRestock);
            $newStock = $previousStock + $qtyToRestock;

            \App\Models\InventoryMovement::create([
                'product_id' => $product->id,
                'order_id' => $this->id,
                'type' => 'return',
                'quantity_change' => $qtyToRestock,
                'previous_stock' => $previousStock,
                'current_stock' => $newStock,
                'remarks' => $reason ?? "Stock restored for order #{$this->order_number}",
                'created_by_user_id' => $changedByUser?->id,
            ]);
        }

        return true;
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('order_status', self::STATUS_PENDING);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeDelivered(Builder $query): Builder
    {
        return $query->where('order_status', self::STATUS_DELIVERED);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('order_status', self::STATUS_CANCELLED);
    }
}
