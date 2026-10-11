<?php

namespace App\Http\Controllers;

use App\Services\Payment\PaymentGatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentGatewayManager $paymentManager
    ) {}

    /**
     * Handle customer redirect callbacks from payment gateways (e.g. SSLCOMMERZ, bKash).
     */
    public function callback(Request $request, string $gateway): RedirectResponse
    {
        Log::info("[Payment][Callback] Received redirect callback from gateway: {$gateway}", $request->all());

        $result = $this->paymentManager->processCallback($gateway, $request);

        $order = $result['order'] ?? null;

        if ($result['success'] && $order) {
            return redirect()
                ->route('checkout.confirmation', $order->order_number)
                ->with('success', "Payment of ৳" . number_format($order->total_amount, 2) . " verified successfully via " . strtoupper($gateway) . ".");
        }

        if ($order) {
            $targetRoute = auth()->check() ? 'customer.orders.show' : 'checkout.confirmation';
            return redirect()
                ->route($targetRoute, $order->order_number)
                ->with('error', "Online payment was not completed: " . ($result['message'] ?? 'Transaction was cancelled or rejected.'));
        }

        return redirect()
            ->route('checkout.index')
            ->with('error', "Payment processing failed: " . ($result['message'] ?? 'Unknown error.'));
    }

    /**
     * Handle server-to-server Instant Payment Notifications (IPN).
     */
    public function ipn(Request $request, string $gateway): JsonResponse
    {
        Log::info("[Payment][IPN] Received server-to-server notification from gateway: {$gateway}", $request->all());

        $result = $this->paymentManager->processCallback($gateway, $request);

        return response()->json([
            'status' => $result['success'] ? 'SUCCESS' : 'FAILED',
            'message' => $result['message'],
        ], $result['success'] ? 200 : 400);
    }
}
