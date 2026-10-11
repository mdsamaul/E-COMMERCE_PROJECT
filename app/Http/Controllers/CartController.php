<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use App\Services\CartService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Display the shopping cart page.
     */
    public function index(Request $request): View
    {
        $totals = $this->cartService->calculateTotals(
            user: auth()->user(),
            district: $request->input('district', 'Dhaka')
        );

        return view('frontend.cart.index', [
            'totals' => $totals,
            'cart' => $totals['cart'],
            'items' => $totals['items'],
            'warnings' => $totals['warnings'],
        ]);
    }

    /**
     * Add a product to the cart.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);

        try {
            $item = $this->cartService->addItem(
                productId: (int) $validated['product_id'],
                quantity: $quantity,
                user: auth()->user()
            );

            $message = "Added '{$item->product->name}' to your cart.";
            $cartCount = $this->cartService->getCartCount(auth()->user());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'cart_count' => $cartCount,
                ]);
            }

            if ($request->boolean('buy_now')) {
                return redirect()->route('checkout.index')->with('success', $message);
            }

            return back()->with('success', $message);
        } catch (ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => collect($e->errors())->flatten()->first(),
                    'errors' => $e->errors(),
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $this->cartService->updateItem(
                itemId: $id,
                quantity: (int) $validated['quantity'],
                user: auth()->user()
            );

            $message = 'Cart quantity updated successfully.';

            if ($request->wantsJson() || $request->ajax()) {
                $totals = $this->cartService->calculateTotals(auth()->user());
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'cart_count' => $this->cartService->getCartCount(auth()->user()),
                    'totals' => $totals,
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
     * Remove an item from the cart.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->cartService->removeItem($id, auth()->user());

        return back()->with('success', 'Item removed from your cart.');
    }

    /**
     * Clear all items from the cart.
     */
    public function clear(): RedirectResponse
    {
        $this->cartService->clearCart(auth()->user());

        return redirect()->route('cart.index')->with('info', 'Your shopping cart has been cleared.');
    }

    /**
     * Apply coupon code to cart.
     */
    public function applyCoupon(Request $request): RedirectResponse
    {
        $request->validate([
            'coupon_code' => ['required', 'string', 'max:50'],
        ]);

        $code = strtoupper(trim($request->input('coupon_code')));
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return back()->withErrors(['coupon_code' => "Coupon code '{$code}' does not exist."]);
        }

        if (!$coupon->is_active) {
            return back()->withErrors(['coupon_code' => "Coupon '{$code}' is inactive or has been disabled."]);
        }

        $now = Carbon::now();
        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            return back()->withErrors(['coupon_code' => "Coupon '{$code}' has not started yet."]);
        }

        if ($coupon->expires_at && $now->gt($coupon->expires_at)) {
            return back()->withErrors(['coupon_code' => "Coupon '{$code}' has expired on {$coupon->expires_at->format('M d, Y')}."]);
        }

        if ($coupon->usage_limit_per_coupon !== null && $coupon->total_used >= $coupon->usage_limit_per_coupon) {
            return back()->withErrors(['coupon_code' => "Coupon '{$code}' has reached its maximum total usage limit."]);
        }

        $totals = $this->cartService->calculateTotals(auth()->user());
        $subtotal = $totals['subtotal'];

        if ($subtotal < (float) $coupon->min_order_amount) {
            $formattedMin = number_format($coupon->min_order_amount, 2);
            return back()->withErrors(['coupon_code' => "Minimum order subtotal of ৳{$formattedMin} required to use coupon '{$code}'."]);
        }

        $userId = auth()->id();
        if ($userId && $coupon->usage_limit_per_user !== null) {
            $userUsage = $coupon->usages()->where('user_id', $userId)->count();
            if ($userUsage >= $coupon->usage_limit_per_user) {
                return back()->withErrors(['coupon_code' => "You have already reached the maximum usage limit for coupon '{$code}'."]);
            }
        }

        Session::put('applied_coupon_id', $coupon->id);

        return back()->with('success', "Coupon '{$code}' applied successfully!");
    }

    /**
     * Remove the active coupon.
     */
    public function removeCoupon(): RedirectResponse
    {
        Session::forget('applied_coupon_id');

        return back()->with('info', 'Coupon removed from your order.');
    }

    /**
     * Return live cart item count for AJAX badge polling.
     */
    public function count(): JsonResponse
    {
        return response()->json([
            'count' => $this->cartService->getCartCount(auth()->user()),
        ]);
    }
}
