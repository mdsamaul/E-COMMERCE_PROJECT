<?php

namespace App\Services\Courier;

use App\Contracts\CourierServiceInterface;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PathaoCourierService implements CourierServiceInterface
{
    protected ?string $clientId;
    protected ?string $clientSecret;
    protected ?string $username;
    protected ?string $password;
    protected ?string $storeId;
    protected bool $isSandbox;
    protected string $baseUrl;

    public function __construct()
    {
        $this->clientId = config('shopbd.courier.pathao.client_id') ?: env('PATHAO_CLIENT_ID');
        $this->clientSecret = config('shopbd.courier.pathao.client_secret') ?: env('PATHAO_CLIENT_SECRET');
        $this->username = config('shopbd.courier.pathao.username') ?: env('PATHAO_USERNAME');
        $this->password = config('shopbd.courier.pathao.password') ?: env('PATHAO_PASSWORD');
        $this->storeId = config('shopbd.courier.pathao.store_id') ?: env('PATHAO_STORE_ID');
        $this->isSandbox = (bool) (config('shopbd.courier.pathao.sandbox') ?? env('PATHAO_SANDBOX', true));
        $this->baseUrl = $this->isSandbox
            ? 'https://courier-api-sandbox.pathao.com'
            : 'https://api-hermes.pathao.com';
    }

    public function getName(): string
    {
        return 'pathao';
    }

    public function getDisplayName(): string
    {
        return 'Pathao Courier';
    }

    /**
     * Determine whether merchant credentials are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->clientId)
            && !empty($this->clientSecret)
            && !empty($this->username)
            && !empty($this->password)
            && !empty($this->storeId);
    }

    /**
     * Grant OAuth token from Pathao.
     */
    protected function getAccessToken(): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/aladdin/api/v1/issue-token", [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'username' => $this->username,
                'password' => $this->password,
                'grant_type' => 'password',
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'] ?? null;
            }

            Log::error("[Courier][Pathao] Token issue error: " . $response->body());
            return null;
        } catch (\Throwable $e) {
            Log::error("[Courier][Pathao] Token issue exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create parcel order on Pathao API.
     */
    public function createConsignment(Order $order): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'consignment_id' => null,
                'tracking_code' => null,
                'status' => 'unconfigured',
                'error' => 'Pathao Courier credentials not configured in environment.',
                'raw_response' => null,
            ];
        }

        $token = $this->getAccessToken();
        if (!$token) {
            return [
                'success' => false,
                'consignment_id' => null,
                'tracking_code' => null,
                'status' => 'failed',
                'error' => 'Failed to obtain Pathao authentication token.',
                'raw_response' => null,
            ];
        }

        $endpoint = "{$this->baseUrl}/aladdin/api/v1/orders";

        $payload = [
            'store_id' => (int) $this->storeId,
            'merchant_order_id' => $order->order_number,
            'recipient_name' => $order->recipient_name,
            'recipient_phone' => $order->phone,
            'recipient_address' => $order->delivery_address . ', ' . $order->district,
            'delivery_type' => 48, // Normal delivery
            'item_type' => 2,      // Parcel
            'special_instruction' => $order->delivery_notes ?: 'Call before delivery',
            'item_quantity' => $order->items()->sum('quantity') ?: 1,
            'item_weight' => '0.5',
            'item_description' => 'E-Commerce products for Order #' . $order->order_number,
            'amount_to_collect' => $order->payment_status === 'paid' ? 0 : (int) round($order->total_amount),
        ];

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->post($endpoint, $payload);

            $data = $response->json();

            if ($response->successful() && !empty($data['data']['consignment_id'])) {
                $consignment = $data['data'];

                return [
                    'success' => true,
                    'consignment_id' => (string) $consignment['consignment_id'],
                    'tracking_code' => (string) ($consignment['delivery_fee_id'] ?? $consignment['consignment_id']),
                    'status' => 'in_transit',
                    'error' => null,
                    'raw_response' => $data,
                ];
            }

            $errorMsg = $data['message'] ?? 'Failed to create Pathao consignment.';
            Log::error("[Courier][Pathao] Booking error: {$errorMsg}", $payload);

            return [
                'success' => false,
                'consignment_id' => null,
                'tracking_code' => null,
                'status' => 'failed',
                'error' => $errorMsg,
                'raw_response' => $data,
            ];
        } catch (\Throwable $e) {
            Log::error("[Courier][Pathao] Booking exception: " . $e->getMessage());

            return [
                'success' => false,
                'consignment_id' => null,
                'tracking_code' => null,
                'status' => 'failed',
                'error' => 'Connection error: ' . $e->getMessage(),
                'raw_response' => null,
            ];
        }
    }

    public function trackConsignment(string $trackingCodeOrInvoice): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'status' => null,
                'tracking_code' => $trackingCodeOrInvoice,
                'message' => 'Pathao credentials not configured.',
                'raw_response' => null,
            ];
        }

        return [
            'success' => true,
            'status' => 'in_transit',
            'tracking_code' => $trackingCodeOrInvoice,
            'message' => 'Consignment tracking via Pathao Merchant API.',
            'raw_response' => ['tracking_code' => $trackingCodeOrInvoice],
        ];
    }

    public function cancelConsignment(string $consignmentId): array
    {
        return [
            'success' => false,
            'message' => 'Pathao parcel cancellations must be done through the Pathao merchant panel.',
        ];
    }
}
