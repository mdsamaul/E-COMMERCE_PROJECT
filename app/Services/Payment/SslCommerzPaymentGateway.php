<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SslCommerzPaymentGateway implements PaymentGatewayInterface
{
    protected ?string $storeId;
    protected ?string $storePassword;
    protected bool $isSandbox;
    protected string $apiDomain;

    public function __construct()
    {
        $this->storeId = config('shopbd.payment.sslcommerz.store_id') ?: env('SSLCZ_STORE_ID');
        $this->storePassword = config('shopbd.payment.sslcommerz.store_password') ?: env('SSLCZ_STORE_PASSWORD');
        $this->isSandbox = (bool) (config('shopbd.payment.sslcommerz.sandbox') ?? env('SSLCZ_TESTMODE', true));
        $this->apiDomain = $this->isSandbox 
            ? 'https://sandbox.sslcommerz.com' 
            : 'https://securepay.sslcommerz.com';
    }

    public function getName(): string
    {
        return 'sslcommerz';
    }

    public function getDisplayName(): string
    {
        return 'SSLCOMMERZ (Cards / Mobile Banking / Net Banking)';
    }

    /**
     * Determine whether merchant credentials are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->storeId) && !empty($this->storePassword);
    }

    /**
     * Initiate payment session with official SSLCOMMERZ v4 API.
     */
    public function initiatePayment(Order $order, array $customParams = []): array
    {
        if (!$this->isConfigured()) {
            Log::warning("[Payment][SSLCOMMERZ] Payment initiation attempted without configured credentials.");
            return [
                'success' => false,
                'redirect_url' => null,
                'transaction_id' => $order->order_number,
                'message' => 'SSLCOMMERZ payment gateway is not configured.',
                'raw_response' => null,
            ];
        }

        $endpoint = "{$this->apiDomain}/gwprocess/v4/api.php";

        $postData = [
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'total_amount' => number_format((float) $order->total_amount, 2, '.', ''),
            'currency' => 'BDT',
            'tran_id' => $order->order_number,
            'success_url' => route('payment.callback', ['gateway' => 'sslcommerz', 'status' => 'success']),
            'fail_url' => route('payment.callback', ['gateway' => 'sslcommerz', 'status' => 'fail']),
            'cancel_url' => route('payment.callback', ['gateway' => 'sslcommerz', 'status' => 'cancel']),
            'ipn_url' => route('payment.ipn', ['gateway' => 'sslcommerz']),
            'cus_name' => $order->recipient_name,
            'cus_email' => $order->email ?: ($order->user?->email ?: 'customer@shopbd.com'),
            'cus_add1' => $order->delivery_address,
            'cus_city' => $order->district,
            'cus_postcode' => '1000',
            'cus_country' => 'Bangladesh',
            'cus_phone' => $order->phone,
            'shipping_method' => 'Courier',
            'num_of_item' => $order->items()->count() ?: 1,
            'product_name' => 'ShopBD Order #' . $order->order_number,
            'product_category' => 'General Merchandise',
            'product_profile' => 'general',
        ];

        try {
            $response = Http::asForm()->timeout(30)->post($endpoint, array_merge($postData, $customParams));

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && strtoupper($data['status']) === 'SUCCESS' && !empty($data['GatewayPageURL'])) {
                    return [
                        'success' => true,
                        'redirect_url' => $data['GatewayPageURL'],
                        'transaction_id' => $order->order_number,
                        'message' => 'SSLCOMMERZ session generated successfully.',
                        'raw_response' => $data,
                    ];
                }

                $failedMsg = $data['failedreason'] ?? 'Unable to establish SSLCOMMERZ payment session.';
                Log::error("[Payment][SSLCOMMERZ] Initiation error: {$failedMsg}", $data);

                return [
                    'success' => false,
                    'redirect_url' => null,
                    'transaction_id' => $order->order_number,
                    'message' => $failedMsg,
                    'raw_response' => $data,
                ];
            }

            Log::error("[Payment][SSLCOMMERZ] HTTP error {$response->status()}: " . $response->body());
            return [
                'success' => false,
                'redirect_url' => null,
                'transaction_id' => $order->order_number,
                'message' => 'SSLCOMMERZ gateway returned HTTP error ' . $response->status(),
                'raw_response' => null,
            ];
        } catch (\Throwable $e) {
            Log::error("[Payment][SSLCOMMERZ] Connection exception: " . $e->getMessage());
            return [
                'success' => false,
                'redirect_url' => null,
                'transaction_id' => $order->order_number,
                'message' => 'Connection to payment gateway failed: ' . $e->getMessage(),
                'raw_response' => null,
            ];
        }
    }

    /**
     * Verify payment status using official SSLCOMMERZ Order Validation API.
     */
    public function verifyPayment(Request $request): array
    {
        $orderNumber = $request->input('tran_id');
        $valId = $request->input('val_id');
        $status = strtoupper($request->input('status', ''));

        if (!$orderNumber) {
            return [
                'success' => false,
                'status' => 'failed',
                'amount' => 0.00,
                'currency' => 'BDT',
                'transaction_id' => 'unknown',
                'gateway_transaction_id' => null,
                'gateway_response' => $request->all(),
                'message' => 'Missing transaction identifier from gateway callback.',
            ];
        }

        $order = Order::where('order_number', $orderNumber)->first();
        if (!$order) {
            return [
                'success' => false,
                'status' => 'failed',
                'amount' => 0.00,
                'currency' => 'BDT',
                'transaction_id' => $orderNumber,
                'gateway_transaction_id' => $valId,
                'gateway_response' => $request->all(),
                'message' => "Order #{$orderNumber} not found.",
            ];
        }

        // Handle customer cancel or fail status from redirect
        if (in_array($status, ['FAILED', 'FAIL', 'CANCEL', 'CANCELLED'])) {
            return [
                'success' => false,
                'status' => strtolower($status),
                'amount' => (float) $order->total_amount,
                'currency' => 'BDT',
                'transaction_id' => $order->order_number,
                'gateway_transaction_id' => $valId,
                'gateway_response' => $request->all(),
                'message' => "Payment {$status} by customer or issuing bank.",
            ];
        }

        if (empty($valId)) {
            return [
                'success' => false,
                'status' => 'failed',
                'amount' => (float) $order->total_amount,
                'currency' => 'BDT',
                'transaction_id' => $order->order_number,
                'gateway_transaction_id' => null,
                'gateway_response' => $request->all(),
                'message' => 'Missing validation ID (val_id) from gateway.',
            ];
        }

        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'status' => 'failed',
                'amount' => (float) $order->total_amount,
                'currency' => 'BDT',
                'transaction_id' => $order->order_number,
                'gateway_transaction_id' => $valId,
                'gateway_response' => $request->all(),
                'message' => 'SSLCOMMERZ credentials not configured on server.',
            ];
        }

        // Call official SSLCOMMERZ Order Validation API
        $validationUrl = "{$this->apiDomain}/validator/api/validationserverAPI.php";

        try {
            $valResponse = Http::timeout(30)->get($validationUrl, [
                'val_id' => $valId,
                'store_id' => $this->storeId,
                'store_passwd' => $this->storePassword,
                'v' => 1,
                'format' => 'json',
            ]);

            if (!$valResponse->successful()) {
                Log::error("[Payment][SSLCOMMERZ] Validation API failed with status {$valResponse->status()}");
                return [
                    'success' => false,
                    'status' => 'failed',
                    'amount' => (float) $order->total_amount,
                    'currency' => 'BDT',
                    'transaction_id' => $order->order_number,
                    'gateway_transaction_id' => $valId,
                    'gateway_response' => $request->all(),
                    'message' => 'Server validation API request failed.',
                ];
            }

            $validationData = $valResponse->json();
            $valStatus = strtoupper($validationData['status'] ?? '');

            // 1. Check valid status
            if (!in_array($valStatus, ['VALID', 'VALIDATED'])) {
                Log::warning("[Payment][SSLCOMMERZ] Invalid validation status for Order #{$orderNumber}: {$valStatus}", $validationData);
                return [
                    'success' => false,
                    'status' => 'failed',
                    'amount' => (float) $order->total_amount,
                    'currency' => 'BDT',
                    'transaction_id' => $order->order_number,
                    'gateway_transaction_id' => $valId,
                    'gateway_response' => $validationData,
                    'message' => "Payment verification failed: Status is {$valStatus}.",
                ];
            }

            // 2. Validate tran_id matches order number
            if (($validationData['tran_id'] ?? '') !== $order->order_number) {
                Log::error("[Payment][SSLCOMMERZ] Order ID mismatch. Expected {$order->order_number}, got {$validationData['tran_id']}");
                return [
                    'success' => false,
                    'status' => 'failed',
                    'amount' => (float) $order->total_amount,
                    'currency' => 'BDT',
                    'transaction_id' => $order->order_number,
                    'gateway_transaction_id' => $valId,
                    'gateway_response' => $validationData,
                    'message' => 'Transaction reference mismatch.',
                ];
            }

            // 3. Validate currency is BDT
            if (strtoupper($validationData['currency'] ?? '') !== 'BDT') {
                Log::error("[Payment][SSLCOMMERZ] Currency mismatch. Expected BDT, got {$validationData['currency']}");
                return [
                    'success' => false,
                    'status' => 'failed',
                    'amount' => (float) $order->total_amount,
                    'currency' => 'BDT',
                    'transaction_id' => $order->order_number,
                    'gateway_transaction_id' => $valId,
                    'gateway_response' => $validationData,
                    'message' => 'Currency mismatch.',
                ];
            }

            // 4. Validate amount matches order total
            $paidAmount = (float) ($validationData['amount'] ?? 0);
            if (abs($paidAmount - (float) $order->total_amount) > 0.05) {
                Log::error("[Payment][SSLCOMMERZ] Amount mismatch for Order #{$orderNumber}. Expected {$order->total_amount}, got {$paidAmount}");
                return [
                    'success' => false,
                    'status' => 'failed',
                    'amount' => $paidAmount,
                    'currency' => 'BDT',
                    'transaction_id' => $order->order_number,
                    'gateway_transaction_id' => $valId,
                    'gateway_response' => $validationData,
                    'message' => "Paid amount (৳{$paidAmount}) does not match order total (৳{$order->total_amount}).",
                ];
            }

            return [
                'success' => true,
                'status' => 'completed',
                'amount' => $paidAmount,
                'currency' => 'BDT',
                'transaction_id' => $order->order_number,
                'gateway_transaction_id' => $valId,
                'gateway_response' => $validationData,
                'message' => 'SSLCOMMERZ payment verified successfully.',
            ];
        } catch (\Throwable $e) {
            Log::error("[Payment][SSLCOMMERZ] Verification exception: " . $e->getMessage());
            return [
                'success' => false,
                'status' => 'failed',
                'amount' => (float) $order->total_amount,
                'currency' => 'BDT',
                'transaction_id' => $order->order_number,
                'gateway_transaction_id' => $valId,
                'gateway_response' => $request->all(),
                'message' => 'Verification exception: ' . $e->getMessage(),
            ];
        }
    }

    public function refundPayment(Order $order, float $amount, string $reason): array
    {
        return [
            'success' => false,
            'message' => 'Online automated refund requires SSLCOMMERZ Merchant Portal processing.',
        ];
    }
}
