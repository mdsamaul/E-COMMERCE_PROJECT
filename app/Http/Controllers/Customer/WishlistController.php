<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function __construct(
        protected WishlistService $wishlistService
    ) {}

    /**
     * Display the customer's wishlist items.
     */
    public function index(): View
    {
        $wishlist = $this->wishlistService->getWishlist(Auth::user());

        return view('customer.wishlist.index', [
            'wishlist' => $wishlist,
            'items' => $wishlist->items,
        ]);
    }

    /**
     * Add a product to customer wishlist.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        try {
            $item = $this->wishlistService->addItem(
                user: Auth::user(),
                productId: (int) $validated['product_id']
            );

            $message = "Added '{$item->product->name}' to your wishlist.";
            $count = $this->wishlistService->getWishlistCount(Auth::user());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'wishlist_count' => $count,
                ]);
            }

            return back()->with('success', $message);
        } catch (ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => collect($e->errors())->flatten()->first(),
                ], 422);
            }

            return back()->withErrors($e->errors());
        }
    }

    /**
     * Remove a product from customer wishlist.
     */
    public function destroy(int $productId): RedirectResponse
    {
        $this->wishlistService->removeItem(Auth::user(), $productId);

        return back()->with('success', 'Product removed from your wishlist.');
    }

    /**
     * Move a wishlist product directly to customer shopping cart.
     */
    public function moveToCart(int $productId): RedirectResponse
    {
        try {
            $this->wishlistService->moveToCart(Auth::user(), $productId);

            return redirect()->route('cart.index')->with('success', 'Product moved from wishlist to your shopping cart.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Unable to move product to cart: ' . $e->getMessage());
        }
    }
}
