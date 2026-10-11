<?php

namespace App\Contracts;

use App\Models\Order;

interface CourierServiceInterface
{
    /**
     * Unique machine identifier for this courier provider (e.g. 'mock', 'steadfast', 'pathao').
     */
    public function getName(): string;

    /**
     * Human-friendly display title for admin dashboard.
     */
    public function getDisplayName(): string;

    /**
     * Determine whether the courier provider has required API credentials configured.
     */
    public function isConfigured(): bool;

    /**
     * Book / create parcel consignment with courier.
     * Returns array with:
     * - bool 'success'
     * - ?string 'consignment_id'
     * - ?string 'tracking_code'
     * - ?string 'status'
     * - ?string 'error'
     * - ?array 'raw_response'
     */
    public function createConsignment(Order $order): array;

    /**
     * Check current delivery tracking status of a parcel consignment.
     * Returns array with:
     * - bool 'success'
     * - ?string 'status' ('pending', 'in_transit', 'delivered', 'cancelled', 'returned')
     * - ?string 'tracking_code'
     * - ?string 'message'
     * - ?array 'raw_response'
     */
    public function trackConsignment(string $trackingCodeOrInvoice): array;

    /**
     * Cancel an active parcel consignment before pickup (if supported).
     */
    public function cancelConsignment(string $consignmentId): array;
}
