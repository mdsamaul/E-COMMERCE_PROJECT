<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Rules\BangladeshPhone;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        protected CartService $cartService,
        protected AuthService $authService
    ) {}

    /**
     * Process checkout atomically with row-locking, inventory validation,
     * total recalculation, and guest auto-account provisioning.
     *
     * @param array<string, mixed> $data Validated checkout form data
     * @param User|null $user Authenticated user or null for guest
     * @return Order
     *
     * @throws ValidationException|\Throwable
     */
    public function processCheckout(array $data, ?User $user = null): Order
    {
        // 1. Idempotency Check (prevent duplicate orders on rapid double-clicks)
        $idempotencyKey = $data['idempotency_key'] ?? null;
        if ($idempotencyKey) {
            $cacheKey = "checkout_idempotency_" . md5($idempotencyKey);
            if (Cache::has($cacheKey)) {
                $cachedOrderId = Cache::get($cacheKey);
                $existingOrder = Order::find($cachedOrderId);
                if ($existingOrder) {
                    return $existingOrder;
                }
            }
        }

        // 2. Fetch current cart
        $cart = $this->cartService->getCart($user);
        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Your shopping cart is empty. Please add items before checking out.',
            ]);
        }

        // 3. Execute atomic transaction with pessimistic locking
        $order = DB::transaction(function () use ($data, $user, $cart) {
            $productIds = $cart->items->pluck('product_id')->all();

            // Lock all relevant product rows to prevent overselling race conditions
            $lockedProducts = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validate availability and stock for every cart item
            $subtotal = 0.00;
            $itemsToProcess = [];

            foreach ($cart->items as $cartItem) {
                $product = $lockedProducts->get($cartItem->product_id);

                if (!$product || !$product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => "Product '{$cartItem->product_id}' is no longer available. Please review your cart.",
                    ]);
                }

                if ($product->stock_quantity < $cartItem->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient stock for '{$product->name}'. Available: {$product->stock_quantity}, requested: {$cartItem->quantity}.",
                    ]);
                }

                $effectiveUnitPrice = (float) $product->effective_price;
                $lineSubtotal = round($effectiveUnitPrice * $cartItem->quantity, 2);
                $subtotal += $lineSubtotal;

                $itemsToProcess[] = [
                    'product' => $product,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $effectiveUnitPrice,
                    'unit_purchase_price' => (float) ($product->purchase_price ?? $product->purchase_cost ?? 0.00),
                    'subtotal' => $lineSubtotal,
                ];
            }

            // 4. Calculate Shipping Cost on Server
            $district = trim($data['district']);
            $isDhaka = strcasecmp($district, 'Dhaka') === 0;
            $shippingZone = $isDhaka ? 'inside_dhaka' : 'outside_dhaka';

            $insideFee = (float) Setting::get('inside_dhaka_delivery_fee', 60.00);
            $outsideFee = (float) Setting::get('outside_dhaka_delivery_fee', 120.00);
            $freeThreshold = (float) Setting::get('free_shipping_threshold', 2500.00);

            $isFreeShipping = ($freeThreshold > 0 && $subtotal >= $freeThreshold);
            $shippingCost = $isFreeShipping ? 0.00 : ($isDhaka ? $insideFee : $outsideFee);

            // 5. Calculate Coupon Discount on Server
            $coupon = null;
            $discountAmount = 0.00;
            $couponId = Session::get('applied_coupon_id');

            if ($couponId) {
                $candidateCoupon = Coupon::active()->lockForUpdate()->find($couponId);
                $userId = $user ? $user->id : null;

                if ($candidateCoupon && $candidateCoupon->isValidForAmount($subtotal, $userId)) {
                    $coupon = $candidateCoupon;
                    $discountAmount = round($coupon->calculateDiscount($subtotal), 2);
                }
            }

            // Final Total
            $totalAmount = max(0.00, round($subtotal - $discountAmount + $shippingCost, 2));

            // 6. Handle User Assignment / Auto-Account Creation for Guest
            $orderUser = $user;
            $phone = BangladeshPhone::normalize($data['phone']);
            $wasAutoCreated = false;

            if (!$orderUser) {
                $provisionResult = $this->authService->findOrCreateFromPurchase(
                    phone: $phone,
                    name: $data['recipient_name'],
                    email: $data['email'] ?? null
                );
                $orderUser = $provisionResult['user'];
                $wasAutoCreated = $provisionResult['was_created'];

                // Auto-save delivery address for the user profile if none exists
                if ($orderUser->addresses()->count() === 0) {
                    $orderUser->addresses()->create([
                        'recipient_name' => $data['recipient_name'],
                        'phone' => $phone,
                        'division' => $data['division'] ?? 'Dhaka',
                        'district' => $district,
                        'address_line' => $data['delivery_address'],
                        'delivery_notes' => $data['delivery_notes'] ?? null,
                        'is_default' => true,
                    ]);
                }
            }

            // 7. Create the Order
            $orderNumber = $this->generateOrderNumber();

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $orderUser ? $orderUser->id : null,
                'recipient_name' => trim($data['recipient_name']),
                'phone' => $phone,
                'alternative_phone' => !empty($data['alternative_phone']) ? BangladeshPhone::normalize($data['alternative_phone']) : null,
                'delivery_address' => trim($data['delivery_address']),
                'division' => trim($data['division'] ?? 'Dhaka'),
                'district' => $district,
                'shipping_zone' => $shippingZone,
                'shipping_cost' => $shippingCost,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'coupon_id' => $coupon ? $coupon->id : null,
                'total_amount' => $totalAmount,
                'payment_method' => 'cod',
                'payment_status' => 'unpaid', // Cash on Delivery is strictly unpaid until delivery
                'order_status' => 'pending',
                'delivery_notes' => !empty($data['delivery_notes']) ? trim($data['delivery_notes']) : null,
            ]);

            // 8. Create OrderItem Snapshots & Deduct Inventory
            foreach ($itemsToProcess as $item) {
                /** @var Product $product */
                $product = $item['product'];
                $qty = $item['quantity'];

                // Snapshot creation
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_purchase_price' => $item['unit_purchase_price'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $qty,
                    'subtotal' => $item['subtotal'],
                ]);

                // Inventory reduction
                $previousStock = $product->stock_quantity;
                $product->decrement('stock_quantity', $qty);
                $newStock = $previousStock - $qty;

                // Inventory Movement Audit Log
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'order_id' => $order->id,
                    'type' => 'sale',
                    'quantity_change' => -$qty,
                    'previous_stock' => $previousStock,
                    'current_stock' => $newStock,
                    'remarks' => "Order #{$order->order_number} checkout",
                    'created_by_user_id' => $orderUser ? $orderUser->id : null,
                ]);
            }

            // 9. Record Coupon Usage
            if ($coupon) {
                $coupon->increment('total_used');
                CouponUsage::create([
                    'coupon_id' => $coupon->id,
                    'user_id' => $orderUser ? $orderUser->id : null,
                    'order_id' => $order->id,
                    'discount_amount' => $discountAmount,
                ]);
            }

            // 10. Initial Order Status History
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => 'pending',
                'remarks' => 'Order placed successfully with Cash on Delivery.',
                'changed_by_user_id' => $orderUser ? $orderUser->id : null,
            ]);

            // 11. Empty Cart & Session Coupon
            $cart->items()->delete();
            Session::forget('applied_coupon_id');

            // Flash info for auto-created account notice
            if ($wasAutoCreated) {
                Session::flash('account_created_notice', "An account has been created for {$phone}. You can log in using your phone number.");
            }

            return $order;
        });

        // 12. Record Idempotency Cache
        if ($idempotencyKey) {
            $cacheKey = "checkout_idempotency_" . md5($idempotencyKey);
            Cache::put($cacheKey, $order->id, now()->addMinutes(30));
        }

        return $order->load(['items', 'coupon', 'statusHistories']);
    }

    /**
     * Generate a professional, unique order number for Bangladesh retail.
     * Format: ORD-YYYYMMDD-XXXXXX
     */
    protected function generateOrderNumber(): string
    {
        $prefix = 'ORD-' . date('Ymd') . '-';
        do {
            $orderNumber = $prefix . strtoupper(Str::random(6));
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
