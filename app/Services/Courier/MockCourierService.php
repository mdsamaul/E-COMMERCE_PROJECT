<?php

namespace App\Services\Courier;

use App\Contracts\CourierServiceInterface;
use App\Models\Order;

class MockCourierService implements CourierServiceInterface
{
    protected string $prefix;

    public function __construct()
    {
        $this->prefix = config('shopbd.courier.mock.prefix') ?: 'TRK-BD-';
    }

    public function getName(): string
    {
        return 'mock';
    }

    public function getDisplayName(): string
    {
        return 'Standard Delivery (Internal / Mock)';
    }

    /**
     * Mock/internal courier is always active without external credentials.
     */
    public function isConfigured(): bool
    {
        return true;
    }

    /**
     * Book parcel consignment with local/mock tracking generation.
     */
    public function createConsignment(Order $order): array
    {
        $hash = strtoupper(substr(md5($order->order_number . time()), 0, 8));
        $trackingCode = $this->prefix . $hash;
        $consignmentId = 'CSG-' . mt_rand(100000, 999999);

        return [
            'success' => true,
            'consignment_id' => $consignmentId,
            'tracking_code' => $trackingCode,
            'status' => 'in_transit',
            'error' => null,
            'raw_response' => [
                'provider' => 'mock',
                'invoice' => $order->order_number,
                'tracking_code' => $trackingCode,
                'consignment_id' => $consignmentId,
                'status' => 'in_transit',
                'cod_amount' => $order->payment_status === 'paid' ? 0 : (float) $order->total_amount,
                'created_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Check tracking status.
     */
    public function trackConsignment(string $trackingCodeOrInvoice): array
    {
        return [
            'success' => true,
            'status' => 'in_transit',
            'tracking_code' => $trackingCodeOrInvoice,
            'message' => 'Parcel is currently in transit with delivery rider.',
            'raw_response' => [
                'provider' => 'mock',
                'tracking_code' => $trackingCodeOrInvoice,
                'status' => 'in_transit',
                'checked_at' => now()->toIso8601String(),
            ],
        ];
    }

    public function cancelConsignment(string $consignmentId): array
    {
        return [
            'success' => true,
            'consignment_id' => $consignmentId,
            'message' => 'Consignment cancelled successfully.',
        ];
    }
}
