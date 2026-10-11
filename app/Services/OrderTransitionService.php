<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use App\Services\Notification\OrderNotificationManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderTransitionService
{
    public function __construct(
        protected OrderNotificationManager $notificationManager
    ) {}

    /**
     * Transition an order to a new status safely within a database transaction.
     *
     * @param Order $order The order being updated
     * @param string $newStatus Target order status
     * @param string|null $remarks Optional audit remarks
     * @param User|null $changedByUser User performing the change (admin or customer)
     * @return Order
     *
     * @throws ValidationException
     */
    public function transition(Order $order, string $newStatus, ?string $remarks = null, ?User $changedByUser = null): Order
    {
        $currentStatus = $order->order_status;

        // 1. Validate state machine transition
        if (!$order->canTransitionTo($newStatus)) {
            $allowed = implode(', ', Order::ALLOWED_TRANSITIONS[$currentStatus] ?? ['none (terminal state)']);
            throw ValidationException::withMessages([
                'order_status' => "Cannot transition order from '{$currentStatus}' to '{$newStatus}'. Allowed transitions: [{$allowed}].",
            ]);
        }

        return DB::transaction(function () use ($order, $currentStatus, $newStatus, $remarks, $changedByUser) {
            // 2. Handle Inventory Restock on Cancellation or Return
            if (in_array($newStatus, [Order::STATUS_CANCELLED, Order::STATUS_RETURNED], true)) {
                $this->restockOrderInventory($order, $newStatus, $changedByUser);
            }

            // 3. Update order record
            $order->order_status = $newStatus;
            $order->save();

            // 4. Record in order_status_histories
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $currentStatus,
                'to_status' => $newStatus,
                'remarks' => $remarks ?: "Order status changed from {$currentStatus} to {$newStatus}.",
                'changed_by_user_id' => $changedByUser?->id,
            ]);

            // 5. Send notifications via notification interface
            $this->notificationManager->sendOrderStatusUpdated($order, $currentStatus, $newStatus);

            return $order->fresh(['items', 'statusHistories', 'user']);
        });
    }

    /**
     * Safely restore inventory for cancelled or returned orders.
     * Guarantees stock is NEVER incremented multiple times.
     */
    protected function restockOrderInventory(Order $order, string $newStatus, ?User $changedByUser): void
    {
        $order->restockInventorySafely(
            $changedByUser,
            "Stock restored due to order #{$order->order_number} marked as {$newStatus}"
        );
    }
}
