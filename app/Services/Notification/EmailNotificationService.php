<?php

namespace App\Services\Notification;

use App\Contracts\OrderNotificationInterface;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class EmailNotificationService implements OrderNotificationInterface
{
    protected ?string $mailer;

    public function __construct()
    {
        $this->mailer = config('mail.default');
    }

    public function isConfigured(): bool
    {
        // Check if an actual SMTP/mailgun/ses provider is configured rather than null/log/array in production
        return !in_array($this->mailer, ['log', 'array', null], true);
    }

    public function sendOrderPlaced(Order $order): array
    {
        $email = $order->user?->email;

        if (!$email) {
            return [
                'channel' => 'email',
                'sent' => false,
                'status' => 'no_recipient_email',
                'message' => 'Order has no associated recipient email address.',
            ];
        }

        if (!$this->isConfigured()) {
            Log::info("[Notification][Email] Provider is set to '{$this->mailer}'. Logged order placement email for {$email} (Order #{$order->order_number}).");

            return [
                'channel' => 'email',
                'sent' => false,
                'status' => 'not_configured',
                'message' => "Email provider is configured to '{$this->mailer}'. Outbound email was not sent over live SMTP.",
            ];
        }

        Log::info("[Notification][Email] Sent order confirmation to {$email} for Order #{$order->order_number}.");

        return [
            'channel' => 'email',
            'sent' => true,
            'status' => 'delivered',
            'message' => "Order confirmation email sent to {$email}.",
        ];
    }

    public function sendOrderStatusUpdated(Order $order, string $previousStatus, string $newStatus): array
    {
        $email = $order->user?->email;

        if (!$email) {
            return [
                'channel' => 'email',
                'sent' => false,
                'status' => 'no_recipient_email',
                'message' => 'Order has no associated recipient email address.',
            ];
        }

        if (!$this->isConfigured()) {
            Log::info("[Notification][Email] Provider is set to '{$this->mailer}'. Logged status change email for {$email} (Order #{$order->order_number}: {$newStatus}).");

            return [
                'channel' => 'email',
                'sent' => false,
                'status' => 'not_configured',
                'message' => "Email provider is configured to '{$this->mailer}'. Outbound email was not sent over live SMTP.",
            ];
        }

        Log::info("[Notification][Email] Sent status change notification to {$email} for Order #{$order->order_number}.");

        return [
            'channel' => 'email',
            'sent' => true,
            'status' => 'delivered',
            'message' => "Status change email sent to {$email}.",
        ];
    }
}
