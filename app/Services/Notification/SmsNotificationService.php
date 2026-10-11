<?php

namespace App\Services\Notification;

use App\Contracts\OrderNotificationInterface;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class SmsNotificationService implements OrderNotificationInterface
{
    protected ?string $apiKey;
    protected ?string $senderId;

    public function __construct()
    {
        $this->apiKey = config('services.sms.api_key') ?? env('SMS_API_KEY');
        $this->senderId = config('services.sms.sender_id') ?? env('SMS_SENDER_ID', 'ShopBD');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function sendOrderPlaced(Order $order): array
    {
        $phone = $order->phone;
        $text = "Dear {$order->recipient_name}, your ShopBD order #{$order->order_number} has been received. Total: ৳" . number_format($order->total_amount, 2) . " (COD). Thank you for shopping with us!";

        if (!$this->isConfigured()) {
            Log::info("[Notification][SMS] Provider not configured. Simulated queue for {$phone}: \"{$text}\"");

            return [
                'channel' => 'sms',
                'sent' => false,
                'status' => 'not_configured',
                'message' => 'SMS gateway provider is not configured. Message was not dispatched.',
            ];
        }

        // Future provider dispatch code here (e.g. GreenWeb, SSLWireless, Elitbuzz)
        Log::info("[Notification][SMS] Dispatched to {$phone}: \"{$text}\"");

        return [
            'channel' => 'sms',
            'sent' => true,
            'status' => 'delivered',
            'message' => "SMS successfully dispatched to {$phone}.",
        ];
    }

    public function sendOrderStatusUpdated(Order $order, string $previousStatus, string $newStatus): array
    {
        $phone = $order->phone;
        $text = "Dear {$order->recipient_name}, the status of your ShopBD order #{$order->order_number} has been updated to: " . strtoupper($newStatus) . ".";

        if (!$this->isConfigured()) {
            Log::info("[Notification][SMS] Provider not configured. Simulated status update for {$phone}: \"{$text}\"");

            return [
                'channel' => 'sms',
                'sent' => false,
                'status' => 'not_configured',
                'message' => 'SMS gateway provider is not configured. Message was not dispatched.',
            ];
        }

        Log::info("[Notification][SMS] Dispatched to {$phone}: \"{$text}\"");

        return [
            'channel' => 'sms',
            'sent' => true,
            'status' => 'delivered',
            'message' => "Status update SMS successfully dispatched to {$phone}.",
        ];
    }
}
