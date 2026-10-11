<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use Illuminate\Http\Request;

class CashOnDeliveryGateway implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return 'cod';
    }

    public function getDisplayName(): string
    {
        return 'Cash on Delivery (COD)';
    }

    /**
     * Cash on Delivery is always available without external credentials.
     */
    public function isConfigured(): bool
    {
        return true;
    }

    /**
     * Initiate payment for Cash on Delivery.
     * Directs customer directly to order confirmation page.
     */
    public function initiatePayment(Order $order, array $customParams = []): array
    {
        return [
            'success' => true,
            'redirect_url' => route('checkout.confirmation', $order->order_number),
            'transaction_id' => null,
            'message' => 'Order placed with Cash on Delivery.',
            'raw_response' => [
                'method' => 'cod',
                'order_number' => $order->order_number,
                'amount' => $order->total_amount,
                'currency' => 'BDT',
            ],
        ];
    }

    /**
     * Verify COD payment upon physical delivery collection.
     */
    public function verifyPayment(Request $request): array
    {
        $orderNumber = $request->input('order_number') ?? $request->input('tran_id');
        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return [
                'success' => false,
                'status' => 'failed',
                'amount' => 0.00,
                'currency' => 'BDT',
                'transaction_id' => $orderNumber ?: 'unknown',
                'gateway_transaction_id' => null,
                'gateway_response' => $request->all(),
                'message' => 'Order not found.',
            ];
        }

        return [
            'success' => true,
            'status' => 'completed',
            'amount' => (float) $order->total_amount,
            'currency' => 'BDT',
            'transaction_id' => $order->order_number,
            'gateway_transaction_id' => 'COD-' . $order->order_number,
            'gateway_response' => ['method' => 'cod', 'collected' => true],
            'message' => 'Cash on delivery payment verified.',
        ];
    }

    public function refundPayment(Order $order, float $amount, string $reason): array
    {
        return [
            'success' => true,
            'refund_id' => 'COD-REF-' . strtoupper(bin2hex(random_bytes(4))),
            'amount' => $amount,
            'message' => 'Manual COD cash refund recorded.',
        ];
    }
}
