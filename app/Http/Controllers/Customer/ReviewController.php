<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    /**
     * Submit a customer rating and review following documented purchase-verification policy.
     *
     * Purchase-Verification Policy:
     * 1. The customer must have purchased the specified product.
     * 2. The order must have reached 'delivered' status.
     * 3. Reviews require administrator moderation before being published publicly.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'rating.required' => 'Please select a star rating between 1 and 5.',
            'comment.required' => 'Please write a review comment (minimum 5 characters).',
        ]);

        $user = Auth::user();
        $productId = (int) $validated['product_id'];

        // Purchase-Verification Policy: Check for delivered order containing product
        $deliveredOrder = Order::where('user_id', $user->id)
            ->where('order_status', Order::STATUS_DELIVERED)
            ->whereHas('items', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->latest()
            ->first();

        if (!$deliveredOrder) {
            throw ValidationException::withMessages([
                'product_id' => 'Verified Buyer Policy: You can only review products from orders that have been successfully delivered to you.',
            ]);
        }

        // Check if an existing review exists to update, or create a new one
        $existingReview = Review::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existingReview) {
            $existingReview->update([
                'order_id' => $deliveredOrder->id,
                'rating' => $validated['rating'],
                'comment' => trim($validated['comment']),
                'is_approved' => false, // Always requires re-moderation after update
            ]);

            return back()->with('success', 'Your review has been updated and submitted for moderation. It will appear once approved.');
        }

        Review::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'order_id' => $deliveredOrder->id,
            'rating' => $validated['rating'],
            'comment' => trim($validated['comment']),
            'is_approved' => false, // Requires admin moderation
        ]);

        return back()->with('success', 'Thank you! Your verified purchase review has been submitted and is pending administrator moderation.');
    }
}
