<?php

namespace App\Services\Courier;

use App\Contracts\CourierServiceInterface;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use App\Services\OrderTransitionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class CourierManager
{
    /** @var array<string, CourierServiceInterface> */
    protected array $drivers = [];

    public function __construct(
        MockCourierService $mockService,
        SteadfastCourierService $steadfastService,
        PathaoCourierService $pathaoService,
        protected OrderTransitionService $transitionService
    ) {
        $this->drivers['mock'] = $mockService;
        $this->drivers['steadfast'] = $steadfastService;
        $this->drivers['pathao'] = $pathaoService;
    }

    /**
     * Resolve courier driver by name or return configured default.
     */
    public function driver(?string $name = null): CourierServiceInterface
    {
        $name = strtolower($name ?: config('shopbd.courier.default', 'mock'));

        if (!isset($this->drivers[$name])) {
            throw new InvalidArgumentException("Courier provider [{$name}] is not supported.");
        }

        return $this->drivers[$name];
    }

    /**
     * Get list of active and configured couriers for dispatch selection.
     */
    public function getAvailableCouriers(): array
    {
        $available = [];

        foreach ($this->drivers as $name => $driver) {
            if ($driver->isConfigured()) {
                $available[$name] = [
                    'name' => $driver->getName(),
                    'display_name' => $driver->getDisplayName(),
                    'is_configured' => true,
                ];
            }
        }

        return $available;
    }

    /**
     * Get status overview of all supported courier providers.
     */
    public function getAllCouriersStatus(): array
    {
        $status = [];

        foreach ($this->drivers as $name => $driver) {
            $status[$name] = [
                'name' => $driver->getName(),
                'display_name' => $driver->getDisplayName(),
                'is_configured' => $driver->isConfigured(),
            ];
        }

        return $status;
    }

    /**
     * Dispatch an order with the selected courier service.
     * Updates order consignment fields and transitions order to 'shipped' if currently processing.
     */
    public function dispatchOrder(Order $order, ?string $provider = null, ?User $dispatchedByUser = null): array
    {
        $driver = $this->driver($provider);

        if (!$driver->isConfigured() && $driver->getName() !== 'mock') {
            $errorMsg = "Courier provider [{$driver->getDisplayName()}] is not configured with merchant API credentials.";
            $order->courier_error = $errorMsg;
            $order->save();

            return [
                'success' => false,
                'error' => $errorMsg,
                'order' => $order,
            ];
        }

        $result = $driver->createConsignment($order);

        if ($result['success']) {
            return DB::transaction(function () use ($order, $driver, $result, $dispatchedByUser) {
                $order->courier_provider = $driver->getName();
                $order->courier_tracking_id = $result['tracking_code'];
                $order->courier_consignment_id = $result['consignment_id'];
                $order->courier_status = $result['status'] ?? 'in_transit';
                $order->courier_error = null;
                $order->courier_response = $result['raw_response'];
                $order->courier_dispatched_at = Carbon::now();
                $order->save();

                // If order was pending or confirmed or processing, advance to shipped
                if (in_array($order->order_status, [Order::STATUS_PENDING, Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING], true)) {
                    // Advance through transition service to maintain valid transitions
                    if ($order->order_status === Order::STATUS_PENDING) {
                        $this->transitionService->transition($order, Order::STATUS_CONFIRMED, "Auto-confirmed for dispatch", $dispatchedByUser);
                    }
                    if ($order->order_status === Order::STATUS_CONFIRMED) {
                        $this->transitionService->transition($order, Order::STATUS_PROCESSING, "Processing parcel dispatch", $dispatchedByUser);
                    }
                    if ($order->order_status === Order::STATUS_PROCESSING) {
                        $this->transitionService->transition(
                            $order,
                            Order::STATUS_SHIPPED,
                            "Parcel booked with {$driver->getDisplayName()} (Tracking: {$result['tracking_code']})",
                            $dispatchedByUser
                        );
                    }
                } else {
                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'from_status' => $order->order_status,
                        'to_status' => $order->order_status,
                        'remarks' => "Parcel booked with {$driver->getDisplayName()} (Tracking: {$result['tracking_code']})",
                        'changed_by_user_id' => $dispatchedByUser?->id,
                    ]);
                }

                Log::info("[Courier] Order #{$order->order_number} successfully dispatched with {$driver->getName()} (Tracking: {$result['tracking_code']}).");

                return [
                    'success' => true,
                    'order' => $order->fresh(),
                    'tracking_code' => $result['tracking_code'],
                    'consignment_id' => $result['consignment_id'],
                    'error' => null,
                ];
            });
        }

        // Store error details safely without crashing
        $order->courier_error = $result['error'];
        $order->courier_response = $result['raw_response'];
        $order->save();

        Log::error("[Courier] Order #{$order->order_number} failed dispatch with {$driver->getName()}: " . $result['error']);

        return [
            'success' => false,
            'order' => $order,
            'error' => $result['error'] ?? 'Booking consignment failed.',
        ];
    }

    /**
     * Check live tracking status from the assigned courier provider.
     */
    public function trackOrder(Order $order): array
    {
        if (!$order->isDispatchedToCourier()) {
            return [
                'success' => false,
                'status' => 'not_dispatched',
                'message' => 'Order has not been dispatched with any courier yet.',
            ];
        }

        $provider = $order->courier_provider ?: 'mock';
        $driver = $this->driver($provider);

        $lookupKey = $order->courier_tracking_id ?: $order->order_number;
        $trackingResult = $driver->trackConsignment($lookupKey);

        if ($trackingResult['success'] && !empty($trackingResult['status'])) {
            $order->courier_status = $trackingResult['status'];
            $order->save();
        }

        return $trackingResult;
    }
}
