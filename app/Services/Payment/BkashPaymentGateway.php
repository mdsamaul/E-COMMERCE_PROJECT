<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BkashPaymentGateway implements PaymentGatewayInterface
{
    protected ?string $appKey;
    protected ?string $appSecret;
    protected ?string $username;
    protected ?string $password;
    protected bool $isSandbox;
    protected string $baseUrl;

    public function __construct()
    {
        $this->appKey = config('shopbd.payment.bkash.app_key') ?: env('BKASH_APP_KEY');
        $this->appSecret = config('shopbd.payment.bkash.app_secret') ?: env('BKASH_APP_SECRET');
        $this->username = config('shopbd.payment.bkash.username') ?: env('BKASH_USERNAME');
        $this->password = config('shopbd.payment.bkash.password') ?: env('BKASH_PASSWORD');
        $this->isSandbox = (bool) (config('shopbd.payment.bkash.sandbox') ?? env('BKASH_SANDBOX', true));
        $this->baseUrl = $this->isSandbox
            ? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta'
            : 'https://tokenized.pay.bka.sh/v1.2.0-beta';
    }

    public function getName(): string
    {
        return 'bkash';
    }

    public function getDisplayName(): string
    {
        return 'bKash Online Payment';
    }

    /**
     * Determine whether merchant credentials are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->appKey) 
            && !empty($this->appSecret) 
            && !empty($this->username) 
            && !empty($this->password);
    }

    /**
     * Grant access token from bKash API.
     */
    protected function grantToken(): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'username' => $this->username,
                'password' => $this->password,
            ])->timeout(30)->post("{$this->baseUrl}/tokenized/checkout/token/grant", [
                'app_key' => $this->appKey,
                'app_secret' => $this->appSecret,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['id_token'] ?? null;
            }

            Log::error("[Payment][bKash] Token grant error: " . $response->body());
            return null;
        } catch (\Throwable $e) {
            Log::error("[Payment][bKash] Token grant exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Initiate bKash payment URL.
     */
    public function initiatePayment(Order $order, array $customParams = []): array
    {
        if (!$this->isConfigured()) {
            Log::warning("[Payment][bKash] Payment initiation attempted without configured credentials.");
            return [
                'success' => false,
                'redirect_url' => null,
                'transaction_id' => $order->order_number,
                'message' => 'bKash payment gateway is not configured.',
                'raw_response' => null,
            ];
        }

        $idToken = $this->grantToken();
        if (!$idToken) {
            return [
                'success' => false,
                'redirect_url' => null,
                'transaction_id' => $order->order_number,
                'message' => 'Unable to authenticate with bKash API.',
                'raw_response' => null,
            ];
        }

        try {
            $callbackUrl = route('payment.callback', ['gateway' => 'bkash']);
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => $idToken,
                'X-APP-Key' => $this->appKey,
            ])->timeout(30)->post("{$this->baseUrl}/tokenized/checkout/create", [
                'mode' => '0011',
                'payerReference' => $order->phone,
                'callbackURL' => $callbackUrl,
                'amount' => number_format((float) $order->total_amount, 2, '.', ''),
                'currency' => 'BDT',
                'intent' => 'sale',
                'merchantInvoiceNumber' => $order->order_number,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['bkashURL'])) {
                    return [
                        'success' => true,
                        'redirect_url' => $data['bkashURL'],
                        'transaction_id' => $data['paymentID'] ?? $order->order_number,
                        'message' => 'bKash payment checkout initiated.',
                        'raw_response' => $data,
                    ];
                }

                $msg = $data['statusMessage'] ?? 'Unable to create bKash checkout session.';
                return [
                    'success' => false,
                    'redirect_url' => null,
                    'transaction_id' => $order->order_number,
                    'message' => $msg,
                    'raw_response' => $data,
                ];
            }

            return [
                'success' => false,
                'redirect_url' => null,
                'transaction_id' => $order->order_number,
                'message' => 'bKash returned error: ' . $response->body(),
                'raw_response' => null,
            ];
        } catch (\Throwable $e) {
            Log::error("[Payment][bKash] Create payment exception: " . $e->getMessage());
            return [
                'success' => false,
                'redirect_url' => null,
                'transaction_id' => $order->order_number,
                'message' => 'Exception during bKash payment: ' . $e->getMessage(),
                'raw_response' => null,
            ];
        }
    }

    /**
     * Execute and verify bKash payment via Execute API.
     */
    public function verifyPayment(Request $request): array
    {
        $paymentId = $request->input('paymentID');
        $status = strtolower($request->input('status', ''));

        if (in_array($status, ['cancel', 'cancelled', 'failure', 'failed'])) {
            return [
                'success' => false,
                'status' => 'failed',
                'amount' => 0.00,
                'currency' => 'BDT',
                'transaction_id' => $paymentId ?: 'unknown',
                'gateway_transaction_id' => $paymentId,
                'gateway_response' => $request->all(),
                'message' => "bKash payment {$status} by customer.",
            ];
        }

        if (empty($paymentId) || !$this->isConfigured()) {
            return [
                'success' => false,
                'status' => 'failed',
                'amount' => 0.00,
                'currency' => 'BDT',
                'transaction_id' => $paymentId ?: 'unknown',
                'gateway_transaction_id' => $paymentId,
                'gateway_response' => $request->all(),
                'message' => 'Missing payment ID or unconfigured bKash credentials.',
            ];
        }

        $idToken = $this->grantToken();
        if (!$idToken) {
            return [
                'success' => false,
                'status' => 'failed',
                'amount' => 0.00,
                'currency' => 'BDT',
                'transaction_id' => $paymentId,
                'gateway_transaction_id' => $paymentId,
                'gateway_response' => $request->all(),
                'message' => 'Failed to obtain bKash access token for verification.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => $idToken,
                'X-APP-Key' => $this->appKey,
            ])->timeout(30)->post("{$this->baseUrl}/tokenized/checkout/execute", [
                'paymentID' => $paymentId,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $statusCode = $data['statusCode'] ?? '';
                $trxId = $data['trxID'] ?? null;
                $amount = (float) ($data['amount'] ?? 0);
                $currency = $data['currency'] ?? 'BDT';
                $invoice = $data['merchantInvoiceNumber'] ?? '';

                if ($statusCode === '0000' && $trxId) {
                    return [
                        'success' => true,
                        'status' => 'completed',
                        'amount' => $amount,
                        'currency' => $currency,
                        'transaction_id' => $invoice ?: $paymentId,
                        'gateway_transaction_id' => $trxId,
                        'gateway_response' => $data,
                        'message' => 'bKash payment successfully executed.',
                    ];
                }

                $errorMsg = $data['statusMessage'] ?? 'bKash execution failed.';
                return [
                    'success' => false,
                    'status' => 'failed',
                    'amount' => $amount,
                    'currency' => $currency,
                    'transaction_id' => $invoice ?: $paymentId,
                    'gateway_transaction_id' => $trxId,
                    'gateway_response' => $data,
                    'message' => $errorMsg,
                ];
            }

            return [
                'success' => false,
                'status' => 'failed',
                'amount' => 0.00,
                'currency' => 'BDT',
                'transaction_id' => $paymentId,
                'gateway_transaction_id' => $paymentId,
                'gateway_response' => $response->json() ?? [],
                'message' => 'bKash execution HTTP error ' . $response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 'failed',
                'amount' => 0.00,
                'currency' => 'BDT',
                'transaction_id' => $paymentId,
                'gateway_transaction_id' => $paymentId,
                'gateway_response' => $request->all(),
                'message' => 'bKash execute exception: ' . $e->getMessage(),
            ];
        }
    }

    public function refundPayment(Order $order, float $amount, string $reason): array
    {
        return [
            'success' => false,
            'message' => 'Online bKash refund requires merchant portal processing.',
        ];
    }
}
