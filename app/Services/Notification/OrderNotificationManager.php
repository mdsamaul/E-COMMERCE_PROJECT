<?php

namespace App\Services\Notification;

use App\Contracts\OrderNotificationInterface;
use App\Models\Order;

class OrderNotificationManager implements OrderNotificationInterface
{
    public function __construct(
        protected SmsNotificationService $smsService,
        protected EmailNotificationService $emailService
    ) {}

    public function sendOrderPlaced(Order $order): array
    {
        return [
            'sms' => $this->smsService->sendOrderPlaced($order),
            'email' => $this->emailService->sendOrderPlaced($order),
        ];
    }

    public function sendOrderStatusUpdated(Order $order, string $previousStatus, string $newStatus): array
    {
        return [
            'sms' => $this->smsService->sendOrderStatusUpdated($order, $previousStatus, $newStatus),
            'email' => $this->emailService->sendOrderStatusUpdated($order, $previousStatus, $newStatus),
        ];
    }
}
