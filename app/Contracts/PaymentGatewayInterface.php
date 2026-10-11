<?php

namespace App\Contracts;

use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Unique machine identifier for this gateway (e.g. 'cod', 'sslcommerz', 'bkash').
     */
    public function getName(): string;

    /**
     * Human-friendly display title for customer checkout.
     */
    public function getDisplayName(): string;

    /**
     * Determine whether the gateway has all required merchant credentials configured.
     */
    public function isConfigured(): bool;

    /**
     * Initiate payment for an order.
     * Returns array with:
     * - bool 'success'
     * - ?string 'redirect_url' (or confirmation route)
     * - ?string 'transaction_id'
     * - ?string 'message'
     * - ?array 'raw_response'
     */
    public function initiatePayment(Order $order, array $customParams = []): array;

    /**
     * Verify payment response / webhook payload from gateway.
     * Must be idempotent and validate transaction ID, amount, and currency.
     * Returns array with:
     * - bool 'success'
     * - string 'status' ('completed', 'failed', 'cancelled')
     * - float 'amount'
     * - string 'currency'
     * - string 'transaction_id'
     * - ?string 'gateway_transaction_id'
     * - ?array 'gateway_response'
     * - ?string 'message'
     */
    public function verifyPayment(Request $request): array;

    /**
     * Refund payment (if supported by gateway).
     */
    public function refundPayment(Order $order, float $amount, string $reason): array;
}
