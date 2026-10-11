<?php

namespace App\Contracts;

use App\Models\Order;

interface OrderNotificationInterface
{
    /**
     * Send notification for a newly placed order.
     *
     * @return array{channel: string, sent: bool, status: string, message: string}
     */
    public function sendOrderPlaced(Order $order): array;

    /**
     * Send notification for an updated order status.
     *
     * @return array{channel: string, sent: bool, status: string, message: string}
     */
    public function sendOrderStatusUpdated(Order $order, string $previousStatus, string $newStatus): array;
}
