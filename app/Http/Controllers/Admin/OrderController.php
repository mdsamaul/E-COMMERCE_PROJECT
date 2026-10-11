<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderTransitionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Services\Courier\CourierManager;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderTransitionService $transitionService,
        protected CourierManager $courierManager
    ) {}

    /**
     * Display a paginated listing of customer orders with filters.
     */
    public function index(Request $request): View
    {
        $query = Order::with(['user', 'items'])->latest();

        // Status Filter
        if ($request->filled('status')) {
            $query->where('order_status', $request->input('status'));
        }

        // Payment Status Filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        // Shipping Zone Filter
        if ($request->filled('zone')) {
            $query->where('shipping_zone', $request->input('zone'));
        }

        // Date Range Filters
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        // Search Keyword (order number, recipient name, phone)
        if ($request->filled('q')) {
            $search = trim($request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        // Metrics for Quick Tabs
        $counts = [
            'all' => Order::count(),
            'pending' => Order::where('order_status', Order::STATUS_PENDING)->count(),
            'processing' => Order::where('order_status', Order::STATUS_PROCESSING)->count(),
            'shipped' => Order::where('order_status', Order::STATUS_SHIPPED)->count(),
            'delivered' => Order::where('order_status', Order::STATUS_DELIVERED)->count(),
            'cancelled' => Order::where('order_status', Order::STATUS_CANCELLED)->count(),
        ];

        return view('admin.orders.index', compact('orders', 'counts'));
    }

    /**
     * Display the specified order details and administrative management panel.
     */
    public function show(Order $order): View
    {
        $order->load([
            'user.addresses',
            'items.product.primaryImage',
            'statusHistories.changedByUser',
            'coupon',
            'payments',
        ]);

        // Financial & Profit Margin calculations
        $totalCost = $order->items->sum(function ($item) {
            return (float) $item->unit_purchase_price * $item->quantity;
        });

        $grossProfit = (float) $order->subtotal - $totalCost;
        $profitMargin = $order->subtotal > 0 ? ($grossProfit / $order->subtotal) * 100 : 0;

        // Next allowed state transitions
        $allowedTransitions = Order::ALLOWED_TRANSITIONS[$order->order_status] ?? [];

        // Inventory movements recorded for this order
        $inventoryMovements = InventoryMovement::where('order_id', $order->id)
            ->with(['product', 'createdByUser'])
            ->latest()
            ->get();

        return view('admin.orders.show', [
            'order' => $order,
            'totalCost' => $totalCost,
            'grossProfit' => $grossProfit,
            'profitMargin' => round($profitMargin, 1),
            'allowedTransitions' => $allowedTransitions,
            'inventoryMovements' => $inventoryMovements,
            'availableCouriers' => $this->courierManager->getAvailableCouriers(),
            'allCouriers' => $this->courierManager->getAllCouriersStatus(),
        ]);
    }

    /**
     * Update order status validating state machine rules and restocking if cancelled/returned.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'order_status' => ['required', 'string'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $newStatus = $request->input('order_status');
        $remarks = $request->input('remarks');

        try {
            $this->transitionService->transition(
                order: $order,
                newStatus: $newStatus,
                remarks: $remarks,
                changedByUser: Auth::user()
            );

            return back()->with('success', "Order #{$order->order_number} status updated to " . strtoupper($newStatus) . " successfully.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            return back()->with('error', 'Error updating order status: ' . $e->getMessage());
        }
    }

    /**
     * Update order payment status (e.g. mark COD as paid upon collection).
     */
    public function updatePayment(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'payment_status' => ['required', 'in:unpaid,paid,refunded'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $newPaymentStatus = $request->input('payment_status');
        $oldPaymentStatus = $order->payment_status;

        $order->payment_status = $newPaymentStatus;
        $order->save();

        if ($newPaymentStatus === 'paid' && $oldPaymentStatus !== 'paid') {
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $order->payment_method,
                'transaction_id' => 'TXN-' . strtoupper(bin2hex(random_bytes(4))),
                'amount' => $order->total_amount,
                'currency' => 'BDT',
                'status' => 'completed',
                'verified_at' => Carbon::now(),
            ]);
        }

        return back()->with('success', "Payment status for Order #{$order->order_number} updated to " . strtoupper($newPaymentStatus) . ".");
    }

    /**
     * Dispatch order to external or mock courier provider.
     */
    public function dispatchCourier(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'provider' => ['required', 'string', 'in:mock,steadfast,pathao'],
        ]);

        $provider = $request->input('provider');
        $result = $this->courierManager->dispatchOrder($order, $provider, Auth::user());

        if ($result['success']) {
            return back()->with('success', "Order #{$order->order_number} dispatched with {$provider}. Tracking Code: " . $result['tracking_code']);
        }

        return back()->with('error', "Courier dispatch failed: " . ($result['error'] ?? 'Unknown error.'));
    }

    /**
     * Refresh live courier tracking status.
     */
    public function trackCourier(Order $order): RedirectResponse
    {
        $result = $this->courierManager->trackOrder($order);

        if ($result['success']) {
            return back()->with('info', "Courier tracking update: Status is " . strtoupper($result['status'] ?? 'pending'));
        }

        return back()->with('warning', "Unable to update tracking: " . ($result['message'] ?? 'No response'));
    }

    /**
     * Display printable order invoice for administrative records.
     */
    public function invoice(Order $order): View
    {
        $order->load(['items.product', 'coupon', 'user']);

        return view('admin.orders.invoice', compact('order'));
    }
}
