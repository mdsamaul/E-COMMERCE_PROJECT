<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class PaymentGatewayManager
{
    /** @var array<string, PaymentGatewayInterface> */
    protected array $drivers = [];

    public function __construct(
        CashOnDeliveryGateway $codGateway,
        SslCommerzPaymentGateway $sslCommerzGateway,
        BkashPaymentGateway $bkashGateway
    ) {
        $this->drivers['cod'] = $codGateway;
        $this->drivers['sslcommerz'] = $sslCommerzGateway;
        $this->drivers['bkash'] = $bkashGateway;
    }

    /**
     * Get a specific payment driver instance.
     */
    public function driver(string $name = 'cod'): PaymentGatewayInterface
    {
        $name = strtolower($name);

        if (!isset($this->drivers[$name])) {
            throw new InvalidArgumentException("Payment gateway driver [{$name}] is not supported.");
        }

        return $this->drivers[$name];
    }

    /**
     * Get all active and configured payment gateways for customer checkout.
     * Cash on Delivery is always available without credentials.
     * Online gateways appear ONLY when real credentials are configured.
     *
     * @return array<string, array{name: string, display_name: string, is_configured: bool}>
     */
    public function getAvailableGateways(): array
    {
        $available = [];

        foreach ($this->drivers as $name => $driver) {
            if ($driver->isConfigured()) {
                $available[$name] = [
                    'name' => $driver->getName(),
                    'display_name' => $driver->getDisplayName(),
                    'is_configured' => true,
                ];
            }
        }

        return $available;
    }

    /**
     * Alias for getAvailableGateways.
     */
    public function getActiveGateways(): array
    {
        return $this->getAvailableGateways();
    }

    /**
     * Get status overview of all supported gateways (including disabled ones).
     */
    public function getAllGatewaysStatus(): array
    {
        $status = [];

        foreach ($this->drivers as $name => $driver) {
            $status[$name] = [
                'name' => $driver->getName(),
                'display_name' => $driver->getDisplayName(),
                'is_configured' => $driver->isConfigured(),
            ];
        }

        return $status;
    }

    /**
     * Initiate payment via chosen gateway.
     */
    public function initiatePayment(Order $order, string $gatewayName): array
    {
        $driver = $this->driver($gatewayName);

        if (!$driver->isConfigured() && $gatewayName !== 'cod') {
            return [
                'success' => false,
                'redirect_url' => null,
                'message' => "Payment gateway [{$driver->getDisplayName()}] is currently unconfigured or disabled.",
            ];
        }

        return $driver->initiatePayment($order);
    }

    /**
     * Process gateway callback / IPN notification with strict idempotency and amount validation.
     */
    public function processCallback(string $gatewayName, Request $request): array
    {
        $driver = $this->driver($gatewayName);
        $verification = $driver->verifyPayment($request);

        $orderNumber = $verification['transaction_id'] ?? null;
        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            Log::error("[Payment][Callback] Order not found for transaction: {$orderNumber}");
            return [
                'success' => false,
                'order' => null,
                'message' => "Order #{$orderNumber} not found.",
                'verification' => $verification,
            ];
        }

        // 1. If verification failed
        if (!$verification['success'] || $verification['status'] !== 'completed') {
            Log::warning("[Payment][Callback] Payment failed for Order #{$order->order_number}: " . ($verification['message'] ?? ''));

            return [
                'success' => false,
                'order' => $order,
                'message' => $verification['message'] ?? 'Payment verification failed.',
                'verification' => $verification,
            ];
        }

        // 2. Idempotency Check: Prevent duplicate payment processing
        if ($order->payment_status === 'paid') {
            Log::info("[Payment][Callback] Order #{$order->order_number} is already paid. Returning idempotent response.");

            return [
                'success' => true,
                'order' => $order,
                'message' => 'Payment already completed (idempotent duplicate request).',
                'verification' => $verification,
            ];
        }

        $existingPayment = Payment::where('order_id', $order->id)
            ->where(function ($q) use ($verification) {
                if (!empty($verification['gateway_transaction_id'])) {
                    $q->where('transaction_id', $verification['gateway_transaction_id']);
                }
            })
            ->where('status', 'completed')
            ->first();

        if ($existingPayment) {
            Log::info("[Payment][Callback] Payment transaction already registered for Order #{$order->order_number}.");

            return [
                'success' => true,
                'order' => $order,
                'message' => 'Payment transaction already registered.',
                'verification' => $verification,
            ];
        }

        // 3. Atomically record payment and mark order as paid
        return DB::transaction(function () use ($order, $gatewayName, $verification) {
            $order->payment_method = $gatewayName;
            $order->payment_status = 'paid';
            $order->gateway_transaction_id = $verification['gateway_transaction_id'] ?? null;
            $order->save();

            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $gatewayName,
                'transaction_id' => $verification['gateway_transaction_id'] ?: 'TXN-' . strtoupper(bin2hex(random_bytes(4))),
                'amount' => $verification['amount'],
                'currency' => $verification['currency'] ?? 'BDT',
                'status' => 'completed',
                'gateway_response' => $verification['gateway_response'] ?? [],
                'verified_at' => Carbon::now(),
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $order->order_status,
                'to_status' => $order->order_status,
                'remarks' => "Payment verified via " . strtoupper($gatewayName) . " (Ref: " . ($verification['gateway_transaction_id'] ?? 'N/A') . ").",
                'changed_by_user_id' => null,
            ]);

            Log::info("[Payment][Callback] Order #{$order->order_number} marked as PAID via {$gatewayName}.");

            return [
                'success' => true,
                'order' => $order->fresh(),
                'message' => 'Payment verified and order updated successfully.',
                'verification' => $verification,
            ];
        });
    }
}
