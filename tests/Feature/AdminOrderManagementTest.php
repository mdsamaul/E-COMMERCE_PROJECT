<?php

namespace Tests\Feature;

use App\Contracts\OrderNotificationInterface;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\Notification\EmailNotificationService;
use App\Services\Notification\OrderNotificationManager;
use App\Services\Notification\SmsNotificationService;
use App\Services\OrderTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('inside_dhaka_delivery_fee', '60.00', 'shipping', 'number');
        Setting::set('outside_dhaka_delivery_fee', '120.00', 'shipping', 'number');
    }

    protected function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@shopbd.com',
        ]);
    }

    protected function createProduct(array $attributes = []): Product
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        return Product::create(array_merge([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Leather Wallet',
            'slug' => 'leather-wallet-' . Str::random(5),
            'sku' => 'SKU-' . strtoupper(Str::random(6)),
            'regular_price' => 1500.00,
            'sale_price' => 1200.00,
            'purchase_price' => 700.00,
            'stock_quantity' => 20,
            'low_stock_threshold' => 5,
            'is_active' => true,
            'is_featured' => true,
        ], $attributes));
    }

    protected function createOrder(array $attributes = []): Order
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();

        $subtotal = 2400.00; // 2 x 1200
        $shipping = 60.00;
        $total = 2460.00;

        $order = Order::create(array_merge([
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'user_id' => $customer->id,
            'recipient_name' => $customer->name,
            'phone' => '01811112222',
            'delivery_address' => 'Mirpur-10, Dhaka',
            'division' => 'Dhaka',
            'district' => 'Dhaka',
            'shipping_zone' => 'inside_dhaka',
            'subtotal' => $subtotal,
            'discount_amount' => 0.00,
            'shipping_cost' => $shipping,
            'total_amount' => $total,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'order_status' => Order::STATUS_PENDING,
        ], $attributes));

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'unit_price' => 1200.00,
            'unit_purchase_price' => 700.00,
            'quantity' => 2,
            'subtotal' => $subtotal,
        ]);

        return $order;
    }

    public function test_non_admin_cannot_access_admin_orders(): void
    {
        $order = $this->createOrder();

        // 1. Guest redirected to admin login
        $guestResponse = $this->get(route('admin.orders.index'));
        $guestResponse->assertRedirect(route('admin.login'));

        // 2. Regular customer gets 403 Forbidden
        $customer = User::factory()->create(['role' => 'customer']);
        $customerResponse = $this->actingAs($customer)->get(route('admin.orders.index'));
        $customerResponse->assertStatus(403);
    }

    public function test_admin_can_view_orders_index_with_filters(): void
    {
        $admin = $this->createAdmin();

        $pendingOrder = $this->createOrder(['order_status' => Order::STATUS_PENDING]);
        $deliveredOrder = $this->createOrder([
            'order_status' => Order::STATUS_DELIVERED,
            'recipient_name' => 'Special VIP Customer',
        ]);

        // General list
        $response = $this->actingAs($admin)->get(route('admin.orders.index'));
        $response->assertStatus(200);
        $response->assertSee($pendingOrder->order_number);
        $response->assertSee($deliveredOrder->order_number);

        // Status filter
        $filterResponse = $this->actingAs($admin)->get(route('admin.orders.index', ['status' => 'pending']));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee($pendingOrder->order_number);
        $filterResponse->assertDontSee($deliveredOrder->order_number);

        // Keyword search
        $searchResponse = $this->actingAs($admin)->get(route('admin.orders.index', ['q' => 'Special VIP']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee($deliveredOrder->order_number);
        $searchResponse->assertDontSee($pendingOrder->order_number);
    }

    public function test_admin_can_view_order_details_and_profit_margins(): void
    {
        $admin = $this->createAdmin();
        $order = $this->createOrder();

        $response = $this->actingAs($admin)->get(route('admin.orders.show', $order->id));

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
        $response->assertSee('Customer Revenue');
        $response->assertSee('Estimated Gross Profit');
        $response->assertSee('Purchased Items');
    }

    public function test_admin_can_advance_order_through_valid_state_transitions(): void
    {
        $admin = $this->createAdmin();
        $order = $this->createOrder(['order_status' => Order::STATUS_PENDING]);

        // 1. Pending -> Confirmed
        $res1 = $this->actingAs($admin)->post(route('admin.orders.update-status', $order->id), [
            'order_status' => Order::STATUS_CONFIRMED,
            'remarks' => 'Order verified by call',
        ]);
        $res1->assertRedirect();
        $order->refresh();
        $this->assertEquals(Order::STATUS_CONFIRMED, $order->order_status);

        // 2. Confirmed -> Processing
        $res2 = $this->actingAs($admin)->post(route('admin.orders.update-status', $order->id), [
            'order_status' => Order::STATUS_PROCESSING,
            'remarks' => 'Packing at warehouse',
        ]);
        $res2->assertRedirect();
        $order->refresh();
        $this->assertEquals(Order::STATUS_PROCESSING, $order->order_status);

        // 3. Processing -> Shipped
        $res3 = $this->actingAs($admin)->post(route('admin.orders.update-status', $order->id), [
            'order_status' => Order::STATUS_SHIPPED,
            'remarks' => 'Handed over to courier',
        ]);
        $res3->assertRedirect();
        $order->refresh();
        $this->assertEquals(Order::STATUS_SHIPPED, $order->order_status);

        // 4. Shipped -> Delivered
        $res4 = $this->actingAs($admin)->post(route('admin.orders.update-status', $order->id), [
            'order_status' => Order::STATUS_DELIVERED,
            'remarks' => 'Delivered to customer doorstep',
        ]);
        $res4->assertRedirect();
        $order->refresh();
        $this->assertEquals(Order::STATUS_DELIVERED, $order->order_status);

        // Check history logs
        $this->assertEquals(4, OrderStatusHistory::where('order_id', $order->id)->count());
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => Order::STATUS_SHIPPED,
            'to_status' => Order::STATUS_DELIVERED,
            'changed_by_user_id' => $admin->id,
        ]);
    }

    public function test_admin_cannot_execute_invalid_order_status_transition(): void
    {
        $admin = $this->createAdmin();
        $order = $this->createOrder(['order_status' => Order::STATUS_PENDING]);

        // Invalid: pending cannot jump directly to delivered
        $response = $this->actingAs($admin)->post(route('admin.orders.update-status', $order->id), [
            'order_status' => Order::STATUS_DELIVERED,
            'remarks' => 'Trying illegal jump',
        ]);

        $response->assertSessionHasErrors(['order_status']);
        $order->refresh();
        $this->assertEquals(Order::STATUS_PENDING, $order->order_status);
    }

    public function test_admin_can_reconcile_payment_status(): void
    {
        $admin = $this->createAdmin();
        $order = $this->createOrder(['payment_status' => 'unpaid']);

        $response = $this->actingAs($admin)->post(route('admin.orders.update-payment', $order->id), [
            'payment_status' => 'paid',
            'remarks' => 'Cash received from courier rider',
        ]);

        $response->assertRedirect();
        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'completed',
            'amount' => $order->total_amount,
        ]);
    }

    public function test_admin_can_view_printable_invoice(): void
    {
        $admin = $this->createAdmin();
        $order = $this->createOrder();

        $response = $this->actingAs($admin)->get(route('admin.orders.invoice', $order->id));

        $response->assertStatus(200);
        $response->assertSee('ADMIN SLIP');
        $response->assertSee($order->order_number);
    }

    public function test_notification_interfaces_report_honestly_when_unconfigured(): void
    {
        // SMS service
        $smsService = new SmsNotificationService();
        $this->assertFalse($smsService->isConfigured(), 'SMS service must NOT claim to be configured when no API key is provided');

        $order = $this->createOrder();
        $smsResult = $smsService->sendOrderPlaced($order);
        $this->assertFalse($smsResult['sent']);
        $this->assertEquals('not_configured', $smsResult['status']);

        // Email service
        $emailService = new EmailNotificationService();
        $emailResult = $emailService->sendOrderStatusUpdated($order, 'pending', 'confirmed');
        $this->assertArrayHasKey('sent', $emailResult);
        $this->assertArrayHasKey('status', $emailResult);

        // Aggregator manager
        $manager = app(OrderNotificationManager::class);
        $results = $manager->sendOrderPlaced($order);
        $this->assertArrayHasKey('sms', $results);
        $this->assertArrayHasKey('email', $results);
        $this->assertFalse($results['sms']['sent']);
    }
}
