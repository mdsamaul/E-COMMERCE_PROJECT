<?php

namespace App\Services\Courier;

use App\Contracts\CourierServiceInterface;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SteadfastCourierService implements CourierServiceInterface
{
    protected ?string $apiKey;
    protected ?string $secretKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('shopbd.courier.steadfast.api_key') ?: env('STEADFAST_API_KEY');
        $this->secretKey = config('shopbd.courier.steadfast.secret_key') ?: env('STEADFAST_SECRET_KEY');
        $this->baseUrl = rtrim(config('shopbd.courier.steadfast.base_url') ?: 'https://portal.steadfast.com.bd/api/v1', '/');
    }

    public function getName(): string
    {
        return 'steadfast';
    }

    public function getDisplayName(): string
    {
        return 'Steadfast Courier Ltd.';
    }

    /**
     * Determine whether merchant credentials are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->secretKey);
    }

    /**
     * Create shipment order with Steadfast Courier API.
     */
    public function createConsignment(Order $order): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'consignment_id' => null,
                'tracking_code' => null,
                'status' => 'unconfigured',
                'error' => 'Steadfast Courier credentials not configured in environment.',
                'raw_response' => null,
            ];
        }

        $endpoint = "{$this->baseUrl}/create_order";

        $payload = [
            'invoice' => $order->order_number,
            'recipient_name' => $order->recipient_name,
            'recipient_phone' => $order->phone,
            'recipient_address' => $order->delivery_address . ', ' . $order->district,
            'cod_amount' => $order->payment_status === 'paid' ? 0 : (float) $order->total_amount,
            'note' => $order->delivery_notes ?: 'Fragile item, deliver with care.',
        ];

        try {
            $response = Http::withHeaders([
                'Api-Key' => $this->apiKey,
                'Secret-Key' => $this->secretKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($endpoint, $payload);

            $data = $response->json();

            if ($response->successful() && ($data['status'] ?? 0) === 200 && !empty($data['consignment'])) {
                $consignment = $data['consignment'];

                return [
                    'success' => true,
                    'consignment_id' => (string) ($consignment['consignment_id'] ?? $consignment['id'] ?? ''),
                    'tracking_code' => (string) ($consignment['tracking_code'] ?? ''),
                    'status' => $consignment['status'] ?? 'pending',
                    'error' => null,
                    'raw_response' => $data,
                ];
            }

            $errorMsg = $data['message'] ?? (is_array($data['errors'] ?? null) ? json_encode($data['errors']) : 'Failed to book Steadfast consignment.');
            Log::error("[Courier][Steadfast] Booking failed: {$errorMsg}", $payload);

            return [
                'success' => false,
                'consignment_id' => null,
                'tracking_code' => null,
                'status' => 'failed',
                'error' => $errorMsg,
                'raw_response' => $data,
            ];
        } catch (\Throwable $e) {
            Log::error("[Courier][Steadfast] Booking exception: " . $e->getMessage());

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

    /**
     * Check consignment delivery status by invoice number.
     */
    public function trackConsignment(string $trackingCodeOrInvoice): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'status' => null,
                'tracking_code' => $trackingCodeOrInvoice,
                'message' => 'Steadfast Courier credentials not configured.',
                'raw_response' => null,
            ];
        }

        $endpoint = "{$this->baseUrl}/status_by_invoice/{$trackingCodeOrInvoice}";

        try {
            $response = Http::withHeaders([
                'Api-Key' => $this->apiKey,
                'Secret-Key' => $this->secretKey,
            ])->timeout(30)->get($endpoint);

            $data = $response->json();

            if ($response->successful() && ($data['status'] ?? 0) === 200) {
                return [
                    'success' => true,
                    'status' => $data['delivery_status'] ?? 'pending',
                    'tracking_code' => $trackingCodeOrInvoice,
                    'message' => 'Status retrieved: ' . ($data['delivery_status'] ?? 'pending'),
                    'raw_response' => $data,
                ];
            }

            return [
                'success' => false,
                'status' => null,
                'tracking_code' => $trackingCodeOrInvoice,
                'message' => $data['message'] ?? 'Unable to retrieve status from Steadfast.',
                'raw_response' => $data,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => null,
                'tracking_code' => $trackingCodeOrInvoice,
                'message' => 'Tracking request failed: ' . $e->getMessage(),
                'raw_response' => null,
            ];
        }
    }

    public function cancelConsignment(string $consignmentId): array
    {
        return [
            'success' => false,
            'message' => 'Steadfast cancellations must be initiated through the merchant panel or support hotline.',
        ];
    }
}
