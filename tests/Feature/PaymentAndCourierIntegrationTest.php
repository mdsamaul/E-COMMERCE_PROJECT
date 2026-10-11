<?php

namespace Tests\Feature;

use App\Contracts\CourierServiceInterface;
use App\Contracts\PaymentGatewayInterface;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\Courier\CourierManager;
use App\Services\Courier\MockCourierService;
use App\Services\Courier\PathaoCourierService;
use App\Services\Courier\SteadfastCourierService;
use App\Services\Payment\BkashPaymentGateway;
use App\Services\Payment\CashOnDeliveryGateway;
use App\Services\Payment\PaymentGatewayManager;
use App\Services\Payment\SslCommerzPaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentAndCourierIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('inside_dhaka_delivery_fee', '60.00', 'shipping', 'number');
        Setting::set('outside_dhaka_delivery_fee', '120.00', 'shipping', 'number');
    }

    protected function createCustomer(): User
    {
        return User::factory()->create([
            'role' => 'customer',
            'phone' => '01712345678',
        ]);
    }

    protected function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@shopbd.com',
        ]);
    }

    protected function createProduct(): Product
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        return Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Cotton Polo Shirt',
            'slug' => 'cotton-polo-shirt-' . Str::random(5),
            'sku' => 'SKU-' . strtoupper(Str::random(6)),
            'regular_price' => 1200.00,
            'sale_price' => 999.00,
            'purchase_price' => 500.00,
            'stock_quantity' => 25,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ]);
    }

    protected function createOrder(User $customer, string $status = 'processing', string $paymentMethod = 'cod', string $paymentStatus = 'unpaid'): Order
    {
        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'user_id' => $customer->id,
            'recipient_name' => $customer->name,
            'phone' => $customer->phone,
            'delivery_address' => 'House 42, Road 7, Dhanmondi, Dhaka',
            'division' => 'Dhaka',
            'district' => 'Dhaka',
            'shipping_zone' => 'inside_dhaka',
            'subtotal' => 999.00,
            'shipping_cost' => 60.00,
            'discount_amount' => 0.00,
            'total_amount' => 1059.00,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'order_status' => $status,
        ]);

        $product = $this->createProduct();

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'unit_price' => 999.00,
            'unit_purchase_price' => 500.00,
            'quantity' => 1,
            'subtotal' => 999.00,
        ]);

        return $order;
    }

    /* -------------------------------------------------------------------------
     | 1. Payment Gateway Architecture Tests
     | ------------------------------------------------------------------------- */

    public function test_cash_on_delivery_gateway_is_always_configured_without_external_credentials(): void
    {
        $cod = app(CashOnDeliveryGateway::class);

        $this->assertInstanceOf(PaymentGatewayInterface::class, $cod);
        $this->assertEquals('cod', $cod->getName());
        $this->assertTrue($cod->isConfigured());

        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 'pending', 'cod', 'unpaid');

        $result = $cod->initiatePayment($order, []);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['redirect_url']);
    }

    public function test_payment_gateway_manager_identifies_active_and_disabled_gateways(): void
    {
        $manager = app(PaymentGatewayManager::class);

        // By default without .env credentials, only COD is active
        $activeGateways = $manager->getActiveGateways();
        $this->assertArrayHasKey('cod', $activeGateways);

        $allGateways = $manager->getAllGatewaysStatus();
        $this->assertArrayHasKey('cod', $allGateways);
        $this->assertArrayHasKey('sslcommerz', $allGateways);
        $this->assertArrayHasKey('bkash', $allGateways);

        $this->assertTrue($allGateways['cod']['is_configured']);
        $this->assertFalse($allGateways['sslcommerz']['is_configured']);
        $this->assertFalse($allGateways['bkash']['is_configured']);
    }

    public function test_sslcommerz_reports_unconfigured_when_credentials_are_absent(): void
    {
        config([
            'shopbd.payment.sslcommerz.store_id' => null,
            'shopbd.payment.sslcommerz.store_password' => null,
        ]);

        $ssl = app(SslCommerzPaymentGateway::class);
        $this->assertFalse($ssl->isConfigured());

        $customer = $this->createCustomer();
        $order = $this->createOrder($customer);

        $result = $ssl->initiatePayment($order, []);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not configured', $result['message']);
    }

    public function test_sslcommerz_builds_correct_official_payload_when_configured(): void
    {
        config([
            'shopbd.payment.sslcommerz.store_id' => 'test_store_123',
            'shopbd.payment.sslcommerz.store_password' => 'secret_pass_123',
            'shopbd.payment.sslcommerz.sandbox' => true,
        ]);

        Http::fake([
            'https://sandbox.sslcommerz.com/gwprocess/v4/api.php' => Http::response([
                'status' => 'SUCCESS',
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/easycheckout.php?token=test_token_123',
            ], 200),
        ]);

        $ssl = app(SslCommerzPaymentGateway::class);
        $this->assertTrue($ssl->isConfigured());

        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 'pending', 'sslcommerz');

        $result = $ssl->initiatePayment($order, []);

        $this->assertTrue($result['success']);
        $this->assertEquals('https://sandbox.sslcommerz.com/easycheckout.php?token=test_token_123', $result['redirect_url']);

        Http::assertSent(function (\Illuminate\Http\Client\Request $request) use ($order) {
            return $request->url() === 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php'
                && $request['store_id'] === 'test_store_123'
                && $request['currency'] === 'BDT'
                && (float)$request['total_amount'] === (float)$order->total_amount;
        });
    }

    public function test_sslcommerz_validation_rejects_currency_mismatch(): void
    {
        config([
            'shopbd.payment.sslcommerz.store_id' => 'test_store_123',
            'shopbd.payment.sslcommerz.store_password' => 'secret_pass_123',
            'shopbd.payment.sslcommerz.sandbox' => true,
        ]);

        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 'pending', 'sslcommerz');

        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'amount' => '1059.00',
                'currency' => 'USD', // Mismatch!
                'val_id' => 'VAL-123456',
                'tran_id' => $order->order_number,
            ], 200),
        ]);

        $ssl = app(SslCommerzPaymentGateway::class);
        $request = Request::create('/payment/callback/sslcommerz', 'POST', [
            'val_id' => 'VAL-123456',
            'tran_id' => $order->order_number,
        ]);

        $result = $ssl->verifyPayment($request);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Currency mismatch', $result['message']);
    }

    public function test_sslcommerz_validation_rejects_amount_mismatch(): void
    {
        config([
            'shopbd.payment.sslcommerz.store_id' => 'test_store_123',
            'shopbd.payment.sslcommerz.store_password' => 'secret_pass_123',
            'shopbd.payment.sslcommerz.sandbox' => true,
        ]);

        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 'pending', 'sslcommerz');

        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'amount' => '500.00', // Expected 1059.00!
                'currency' => 'BDT',
                'val_id' => 'VAL-123456',
                'tran_id' => $order->order_number,
            ], 200),
        ]);

        $ssl = app(SslCommerzPaymentGateway::class);
        $request = Request::create('/payment/callback/sslcommerz', 'POST', [
            'val_id' => 'VAL-123456',
            'tran_id' => $order->order_number,
        ]);

        $result = $ssl->verifyPayment($request);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('does not match', $result['message']);
    }

    public function test_callback_processing_is_idempotent_and_prevents_duplicate_payments(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 'pending', 'sslcommerz', 'unpaid');

        config([
            'shopbd.payment.sslcommerz.store_id' => 'test_store_123',
            'shopbd.payment.sslcommerz.store_password' => 'secret_pass_123',
            'shopbd.payment.sslcommerz.sandbox' => true,
        ]);

        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'amount' => '1059.00',
                'currency' => 'BDT',
                'val_id' => 'VAL-SSL-999888',
                'tran_id' => $order->order_number,
            ], 200),
        ]);

        $manager = app(PaymentGatewayManager::class);
        $callbackRequest = Request::create('/payment/callback/sslcommerz', 'POST', [
            'val_id' => 'VAL-SSL-999888',
            'tran_id' => $order->order_number,
        ]);

        // First callback execution: Order should become paid, payment record created
        $firstResult = $manager->processCallback('sslcommerz', $callbackRequest);
        $this->assertTrue($firstResult['success']);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('VAL-SSL-999888', $order->gateway_transaction_id);
        $this->assertEquals(1, Payment::where('order_id', $order->id)->count());

        // Replayed / Duplicate callback: Must be idempotent and NOT create second payment
        $secondResult = $manager->processCallback('sslcommerz', $callbackRequest);
        $this->assertTrue($secondResult['success']);
        $this->assertStringContainsString('idempotent', $secondResult['message']);

        // Assert payment record count remains exactly 1
        $this->assertEquals(1, Payment::where('order_id', $order->id)->count());
    }

    /* -------------------------------------------------------------------------
     | 2. Courier Logistics Architecture Tests
     | ------------------------------------------------------------------------- */

    public function test_mock_courier_service_dispatches_and_tracks_without_external_credentials(): void
    {
        $mock = app(MockCourierService::class);

        $this->assertInstanceOf(CourierServiceInterface::class, $mock);
        $this->assertEquals('mock', $mock->getName());
        $this->assertTrue($mock->isConfigured());

        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 'processing');

        $consignment = $mock->createConsignment($order);

        $this->assertTrue($consignment['success']);
        $this->assertStringStartsWith('TRK-BD-', $consignment['tracking_code']);
        $this->assertStringStartsWith('CSG-', $consignment['consignment_id']);
        $this->assertEquals('in_transit', $consignment['status']);

        // Test Tracking
        $tracking = $mock->trackConsignment($consignment['tracking_code']);
        $this->assertTrue($tracking['success']);
        $this->assertEquals('in_transit', $tracking['status']);

        // Test Cancellation
        $cancellation = $mock->cancelConsignment($consignment['tracking_code']);
        $this->assertTrue($cancellation['success']);
        $this->assertStringContainsString('cancelled', $cancellation['message']);
    }

    public function test_steadfast_and_pathao_safely_report_unconfigured_when_keys_missing(): void
    {
        config([
            'shopbd.courier.steadfast.api_key' => null,
            'shopbd.courier.pathao.client_id' => null,
        ]);

        $steadfast = app(SteadfastCourierService::class);
        $pathao = app(PathaoCourierService::class);

        $this->assertFalse($steadfast->isConfigured());
        $this->assertFalse($pathao->isConfigured());

        $customer = $this->createCustomer();
        $order = $this->createOrder($customer);

        $steadfastResult = $steadfast->createConsignment($order);
        $this->assertFalse($steadfastResult['success']);
        $this->assertEquals('unconfigured', $steadfastResult['status']);

        $pathaoResult = $pathao->createConsignment($order);
        $this->assertFalse($pathaoResult['success']);
        $this->assertEquals('unconfigured', $pathaoResult['status']);
    }

    public function test_courier_manager_dispatches_order_and_advances_order_status_to_shipped(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 'processing');

        $this->assertTrue($order->canDispatchToCourier());
        $this->assertFalse($order->isDispatchedToCourier());

        $manager = app(CourierManager::class);
        $result = $manager->dispatchOrder($order, 'mock', $admin);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['tracking_code']);

        $order->refresh();
        $this->assertTrue($order->isDispatchedToCourier());
        $this->assertEquals('shipped', $order->order_status);
        $this->assertEquals('mock', $order->courier_provider);
        $this->assertEquals($result['tracking_code'], $order->courier_tracking_id);
        $this->assertNotNull($order->courier_dispatched_at);
        $this->assertFalse($order->canDispatchToCourier()); // Already dispatched!
    }

    public function test_admin_can_dispatch_order_via_http_endpoint(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 'processing');

        $response = $this->actingAs($admin)
            ->post(route('admin.orders.dispatch-courier', $order->id), [
                'provider' => 'mock',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('shipped', $order->order_status);
        $this->assertEquals('mock', $order->courier_provider);
        $this->assertNotNull($order->courier_tracking_id);
    }

    public function test_admin_can_refresh_courier_tracking_via_http_endpoint(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 'shipped');

        $order->update([
            'courier_provider' => 'mock',
            'courier_tracking_id' => 'TRK-BD-TEST999',
            'courier_status' => 'in_transit',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.orders.track-courier', $order->id));

        $response->assertRedirect();
        $response->assertSessionHas('info');
    }

    public function test_customer_order_details_displays_courier_tracking_id_when_dispatched(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 'shipped');

        $order->update([
            'courier_provider' => 'mock',
            'courier_tracking_id' => 'TRK-BD-LIVE12345',
            'courier_status' => 'in_transit',
        ]);

        $response = $this->actingAs($customer)
            ->get(route('customer.orders.show', $order->order_number));

        $response->assertOk();
        $response->assertSee('TRK-BD-LIVE12345');
        $response->assertSee('MOCK');
        $response->assertSee('Courier Shipment in Progress');
    }

    public function test_checkout_page_renders_payment_method_options(): void
    {
        $product = $this->createProduct();
        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->get(route('checkout.index'));

        $response->assertOk();
        $response->assertSee('Cash on Delivery (COD)');
        $response->assertSee('Online gateways (SSLCOMMERZ / bKash) are currently disabled pending merchant credentials');
    }
}
