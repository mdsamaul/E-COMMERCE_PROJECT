<?php

namespace App\Http\Controllers;

use App\Http\Requests\Frontend\CheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService
    ) {}

    /**
     * Show the full-page checkout form.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        $cart = $this->cartService->getCart($user);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', 'Your shopping cart is empty. Please add items to checkout.');
        }

        // Selected or default district
        $defaultDistrict = 'Dhaka';
        if ($user && $user->defaultAddress) {
            $defaultDistrict = $user->defaultAddress->district;
        }

        $district = $request->input('district', $defaultDistrict);
        $totals = $this->cartService->calculateTotals($user, $district);

        // Prepopulate form data if user is logged in
        $prefill = [
            'name' => $user ? $user->name : old('recipient_name', ''),
            'phone' => $user ? $user->phone : old('phone', ''),
            'email' => $user ? $user->email : old('email', ''),
            'district' => old('district', $district),
            'division' => old('division', $user?->defaultAddress?->division ?? 'Dhaka'),
            'delivery_address' => old('delivery_address', $user?->defaultAddress?->address_line ?? ''),
            'delivery_notes' => old('delivery_notes', ''),
        ];

        $districts = $this->getBangladeshDistricts();
        $idempotencyKey = Str::uuid()->toString();

        return view('frontend.checkout.index', [
            'totals' => $totals,
            'cart' => $cart,
            'items' => $totals['items'],
            'prefill' => $prefill,
            'districts' => $districts,
            'idempotencyKey' => $idempotencyKey,
            'warnings' => $totals['warnings'],
        ]);
    }

    /**
     * AJAX endpoint to recalculate totals when delivery district changes.
     */
    public function calculate(Request $request): JsonResponse
    {
        $district = $request->input('district', 'Dhaka');
        $totals = $this->cartService->calculateTotals(auth()->user(), $district);

        return response()->json([
            'success' => true,
            'shipping_zone' => $totals['shipping_zone'],
            'shipping_cost' => $totals['shipping_cost'],
            'discount_amount' => $totals['discount_amount'],
            'subtotal' => $totals['subtotal'],
            'total_amount' => $totals['total_amount'],
            'free_shipping_eligible' => $totals['free_shipping_eligible'],
        ]);
    }

    /**
     * Process checkout form submission atomically.
     */
    public function store(CheckoutRequest $request): RedirectResponse
    {
        try {
            $order = $this->orderService->processCheckout(
                data: $request->validated(),
                user: auth()->user()
            );

            return redirect()->route('checkout.confirmation', $order->order_number)
                ->with('success', "Order #{$order->order_number} has been placed successfully!");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            return back()->withErrors([
                'checkout_error' => 'An unexpected error occurred while processing your order: ' . $e->getMessage(),
            ])->withInput();
        }
    }

    /**
     * Show order confirmation page.
     */
    public function confirmation(string $orderNumber): View
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items.product.primaryImage', 'coupon', 'statusHistories'])
            ->firstOrFail();

        return view('frontend.checkout.confirmation', [
            'order' => $order,
        ]);
    }

    /**
     * List of Bangladesh administrative districts.
     */
    protected function getBangladeshDistricts(): array
    {
        return [
            'Dhaka', 'Gazipur', 'Narayanganj', 'Tangail', 'Faridpur', 'Manikganj', 'Munshiganj',
            'Narsingdi', 'Kishoreganj', 'Gopalganj', 'Madaripur', 'Rajbari', 'Shariatpur',
            'Chattogram', 'Cox\'s Bazar', 'Cumilla', 'Feni', 'Brahmanbaria', 'Noakhali',
            'Chandpur', 'Lakshmipur', 'Khagrachhari', 'Rangamati', 'Bandarban',
            'Sylhet', 'Moulvibazar', 'Habiganj', 'Sunamganj',
            'Rajshahi', 'Bogura', 'Pabna', 'Sirajganj', 'Naogaon', 'Natore', 'Chapai Nawabganj', 'Joypurhat',
            'Khulna', 'Jashore', 'Kushtia', 'Satkhira', 'Bagerhat', 'Jhenaidah', 'Chuadanga', 'Magura', 'Narail', 'Meherpur',
            'Barishal', 'Bhola', 'Patuakhali', 'Pirojpur', 'Jhalokati', 'Barguna',
            'Rangpur', 'Dinajpur', 'Kurigram', 'Gaibandha', 'Nilphamari', 'Thakurgaon', 'Lalmonirhat', 'Panchagarh',
            'Mymensingh', 'Jamalpur', 'Netrokona', 'Sherpur'
        ];
    }
}
