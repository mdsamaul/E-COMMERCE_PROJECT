<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Services\OrderTransitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderTransitionService $transitionService
    ) {}

    /**
     * Display a listing of the customer's orders.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $query = $user->orders()->with(['items.product.primaryImage'])->latest();

        if ($request->filled('status')) {
            $query->where('order_status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where('order_number', 'like', "%{$q}%");
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('customer.orders.index', compact('orders', 'user'));
    }

    /**
     * Display the specified order details.
     */
    public function show(string $orderNumber): View
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items.product.primaryImage', 'statusHistories', 'coupon'])
            ->firstOrFail();

        // Customer permission check: only allow own orders
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        // Get reviews already submitted by customer for products in this order
        $reviewedProductIds = Review::where('user_id', Auth::id())
            ->whereIn('product_id', $order->items->pluck('product_id'))
            ->pluck('product_id')
            ->all();

        return view('customer.orders.show', compact('order', 'reviewedProductIds'));
    }

    /**
     * Display printable invoice for customer.
     */
    public function invoice(string $orderNumber): View
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items.product', 'coupon'])
            ->firstOrFail();

        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        return view('customer.orders.invoice', compact('order'));
    }

    /**
     * Allow customer to cancel order before fulfillment begins.
     */
    public function cancel(Request $request, string $orderNumber): RedirectResponse
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        if (!$order->canBeCancelledByCustomer()) {
            return back()->with('error', "Order #{$order->order_number} cannot be cancelled because it is already {$order->order_status}.");
        }

        $reason = $request->input('reason', 'Cancelled by customer.');

        try {
            $this->transitionService->transition(
                order: $order,
                newStatus: Order::STATUS_CANCELLED,
                remarks: "Customer cancellation request: " . $reason,
                changedByUser: Auth::user()
            );

            return back()->with('success', "Order #{$order->order_number} has been cancelled successfully.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Unable to cancel order: ' . $e->getMessage());
        }
    }
}
