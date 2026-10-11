<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CartService
{
    /**
     * Get or create active cart for the user or current session.
     */
    public function getCart(?User $user = null): Cart
    {
        $user = $user ?? auth()->user();

        if ($user) {
            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['last_active_at' => Carbon::now()]
            );
        } else {
            $token = Session::get('cart_token');
            if (!$token) {
                $token = Session::getId() ?: \Illuminate\Support\Str::random(40);
                Session::put('cart_token', $token);
            }

            $cart = Cart::firstOrCreate(
                ['session_id' => $token, 'user_id' => null],
                ['last_active_at' => Carbon::now()]
            );
        }

        $cart->update(['last_active_at' => Carbon::now()]);

        return $cart->load(['items.product.primaryImage']);
    }

    /**
     * Count total items in the current active cart.
     */
    public function getCartCount(?User $user = null): int
    {
        try {
            $cart = $this->getCart($user);
            return (int) $cart->items()->sum('quantity');
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Add a product to the cart.
     *
     * @throws ValidationException
     */
    public function addItem(int $productId, int $quantity = 1, ?User $user = null): CartItem
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be at least 1.',
            ]);
        }

        $product = Product::find($productId);

        if (!$product || !$product->is_active) {
            throw ValidationException::withMessages([
                'product_id' => 'The selected product is not available for purchase.',
            ]);
        }

        if ($product->stock_quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Sorry, this product is currently out of stock.',
            ]);
        }

        $cart = $this->getCart($user);
        $cartItem = $cart->items()->where('product_id', $productId)->first();

        $currentQtyInCart = $cartItem ? $cartItem->quantity : 0;
        $newTotalQty = $currentQtyInCart + $quantity;

        if ($newTotalQty > $product->stock_quantity) {
            $available = max(0, $product->stock_quantity - $currentQtyInCart);
            throw ValidationException::withMessages([
                'quantity' => "Cannot add {$quantity} units. Only {$available} additional unit(s) available in stock.",
            ]);
        }

        $effectivePrice = $product->effective_price;

        if ($cartItem) {
            $cartItem->update([
                'quantity' => $newTotalQty,
                'unit_price' => $effectivePrice,
            ]);
        } else {
            $cartItem = $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $effectivePrice,
            ]);
        }

        return $cartItem->load('product');
    }

    /**
     * Update an item's quantity in the cart.
     *
     * @throws ValidationException
     */
    public function updateItem(int $itemId, int $quantity, ?User $user = null): CartItem
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be at least 1.',
            ]);
        }

        $cart = $this->getCart($user);
        $cartItem = $cart->items()->where('id', $itemId)->first();

        if (!$cartItem) {
            throw ValidationException::withMessages([
                'item_id' => 'Cart item not found.',
            ]);
        }

        $product = $cartItem->product;

        if (!$product || !$product->is_active) {
            $cartItem->delete();
            throw ValidationException::withMessages([
                'item_id' => 'This product is no longer available and has been removed from your cart.',
            ]);
        }

        if ($quantity > $product->stock_quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Cannot set quantity to {$quantity}. Only {$product->stock_quantity} units available in stock.",
            ]);
        }

        $cartItem->update([
            'quantity' => $quantity,
            'unit_price' => $product->effective_price,
        ]);

        return $cartItem;
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(int $itemId, ?User $user = null): bool
    {
        $cart = $this->getCart($user);
        $cartItem = $cart->items()->where('id', $itemId)->first();

        if ($cartItem) {
            return (bool) $cartItem->delete();
        }

        return false;
    }

    /**
     * Clear all items from the cart.
     */
    public function clearCart(?User $user = null): void
    {
        $cart = $this->getCart($user);
        $cart->items()->delete();
        Session::forget('applied_coupon_id');
    }

    /**
     * Merge guest cart into authenticated user cart upon login/registration.
     */
    public function mergeGuestCartToUser(string $sessionId, User $user): void
    {
        $guestCart = Cart::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->with('items')
            ->first();

        if (!$guestCart || $guestCart->items->isEmpty()) {
            return;
        }

        $userCart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['last_active_at' => Carbon::now()]
        );

        DB::transaction(function () use ($guestCart, $userCart) {
            foreach ($guestCart->items as $guestItem) {
                $product = $guestItem->product;
                if (!$product || !$product->is_active || $product->stock_quantity <= 0) {
                    continue;
                }

                $existingUserItem = $userCart->items()
                    ->where('product_id', $guestItem->product_id)
                    ->first();

                if ($existingUserItem) {
                    $combinedQty = min(
                        $existingUserItem->quantity + $guestItem->quantity,
                        $product->stock_quantity
                    );
                    $existingUserItem->update([
                        'quantity' => $combinedQty,
                        'unit_price' => $product->effective_price,
                    ]);
                } else {
                    $qty = min($guestItem->quantity, $product->stock_quantity);
                    $userCart->items()->create([
                        'product_id' => $guestItem->product_id,
                        'quantity' => $qty,
                        'unit_price' => $product->effective_price,
                    ]);
                }
            }

            $guestCart->items()->delete();
            $guestCart->delete();
        });
    }

    /**
     * Synchronize and validate cart against current stock and active status.
     * Returns any warnings or notices.
     *
     * @return array{warnings: list<string>, cart: Cart}
     */
    public function syncAndValidate(Cart $cart): array
    {
        $warnings = [];

        foreach ($cart->items as $item) {
            $product = $item->product;

            if (!$product || !$product->is_active) {
                $warnings[] = "Product '{$item->product_name}' is no longer available and was removed from your cart.";
                $item->delete();
                continue;
            }

            if ($product->stock_quantity <= 0) {
                $warnings[] = "Product '{$product->name}' is currently out of stock and was removed from your cart.";
                $item->delete();
                continue;
            }

            if ($item->quantity > $product->stock_quantity) {
                $warnings[] = "Quantity for '{$product->name}' was adjusted from {$item->quantity} to {$product->stock_quantity} due to limited stock.";
                $item->update([
                    'quantity' => $product->stock_quantity,
                    'unit_price' => $product->effective_price,
                ]);
            } else {
                // Ensure price is current
                if ((float) $item->unit_price !== (float) $product->effective_price) {
                    $item->update(['unit_price' => $product->effective_price]);
                }
            }
        }

        return [
            'warnings' => $warnings,
            'cart' => $cart->fresh(['items.product.primaryImage']),
        ];
    }

    /**
     * Compute comprehensive financial totals for cart/checkout.
     *
     * @return array{
     *   items: Collection,
     *   items_count: int,
     *   subtotal: float,
     *   shipping_zone: string,
     *   shipping_cost: float,
     *   coupon: Coupon|null,
     *   discount_amount: float,
     *   total_amount: float,
     *   free_shipping_eligible: bool,
     *   free_shipping_threshold: float
     * }
     */
    public function calculateTotals(?User $user = null, ?string $district = 'Dhaka'): array
    {
        $cart = $this->getCart($user);
        $syncResult = $this->syncAndValidate($cart);
        $cart = $syncResult['cart'];

        $subtotal = (float) $cart->items->sum(function (CartItem $item) {
            return (float) $item->quantity * (float) $item->unit_price;
        });

        // Shipping Calculation
        $districtNormalized = trim($district ?? 'Dhaka');
        $isDhaka = strcasecmp($districtNormalized, 'Dhaka') === 0;
        $shippingZone = $isDhaka ? 'inside_dhaka' : 'outside_dhaka';

        $insideFee = (float) Setting::get('inside_dhaka_delivery_fee', 60.00);
        $outsideFee = (float) Setting::get('outside_dhaka_delivery_fee', 120.00);
        $freeThreshold = (float) Setting::get('free_shipping_threshold', 2500.00);

        $isFreeShipping = ($freeThreshold > 0 && $subtotal >= $freeThreshold && $subtotal > 0);
        $shippingCost = $isFreeShipping ? 0.00 : ($isDhaka ? $insideFee : $outsideFee);

        // Coupon Calculation
        $coupon = null;
        $discountAmount = 0.00;
        $couponId = Session::get('applied_coupon_id');

        if ($couponId && $subtotal > 0) {
            $candidateCoupon = Coupon::active()->find($couponId);
            $userId = $user ? $user->id : null;

            if ($candidateCoupon && $candidateCoupon->isValidForAmount($subtotal, $userId)) {
                $coupon = $candidateCoupon;
                $discountAmount = $coupon->calculateDiscount($subtotal);
            } else {
                Session::forget('applied_coupon_id');
            }
        }

        $totalAmount = max(0.00, $subtotal - $discountAmount) + ($subtotal > 0 ? $shippingCost : 0.00);

        return [
            'cart' => $cart,
            'items' => $cart->items,
            'items_count' => (int) $cart->items->sum('quantity'),
            'subtotal' => round($subtotal, 2),
            'shipping_zone' => $shippingZone,
            'shipping_cost' => round($subtotal > 0 ? $shippingCost : 0.00, 2),
            'coupon' => $coupon,
            'discount_amount' => round($discountAmount, 2),
            'total_amount' => round($totalAmount, 2),
            'free_shipping_eligible' => $isFreeShipping,
            'free_shipping_threshold' => $freeThreshold,
            'warnings' => $syncResult['warnings'],
        ];
    }
}
