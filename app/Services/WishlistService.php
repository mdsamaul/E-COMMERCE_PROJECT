<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Validation\ValidationException;

class WishlistService
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Get or create wishlist for user.
     */
    public function getWishlist(User $user): Wishlist
    {
        $wishlist = Wishlist::firstOrCreate(['user_id' => $user->id]);

        return $wishlist->load(['items.product.primaryImage']);
    }

    /**
     * Get wishlist item count for user.
     */
    public function getWishlistCount(?User $user = null): int
    {
        if (!$user) {
            return 0;
        }

        $wishlist = Wishlist::where('user_id', $user->id)->first();

        return $wishlist ? $wishlist->items()->count() : 0;
    }

    /**
     * Check if product is currently in user wishlist.
     */
    public function isInWishlist(?User $user, int $productId): bool
    {
        if (!$user) {
            return false;
        }

        $wishlist = Wishlist::where('user_id', $user->id)->first();

        return $wishlist ? $wishlist->items()->where('product_id', $productId)->exists() : false;
    }

    /**
     * Add product to user wishlist.
     *
     * @throws ValidationException
     */
    public function addItem(User $user, int $productId): WishlistItem
    {
        $product = Product::find($productId);

        if (!$product || !$product->is_active) {
            throw ValidationException::withMessages([
                'product_id' => 'The selected product is not available.',
            ]);
        }

        $wishlist = $this->getWishlist($user);

        return $wishlist->items()->firstOrCreate([
            'product_id' => $productId,
        ]);
    }

    /**
     * Remove product from user wishlist.
     */
    public function removeItem(User $user, int $productId): bool
    {
        $wishlist = Wishlist::where('user_id', $user->id)->first();

        if (!$wishlist) {
            return false;
        }

        return (bool) $wishlist->items()->where('product_id', $productId)->delete();
    }

    /**
     * Move wishlist product directly into user shopping cart.
     *
     * @throws ValidationException
     */
    public function moveToCart(User $user, int $productId): void
    {
        // 1. Add to cart
        $this->cartService->addItem($productId, 1, $user);

        // 2. Remove from wishlist
        $this->removeItem($user, $productId);
    }
}
