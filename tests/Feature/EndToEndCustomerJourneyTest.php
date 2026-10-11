<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderService;
use App\Services\OrderTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class EndToEndCustomerJourneyTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;
    protected Brand $brand;
    protected Product $productA;
    protected Product $productB;
    protected User $customer;
    protected User $otherCustomer;
    protected User $admin;
    protected Coupon $validCoupon;
    protected Coupon $minSpendCoupon;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed settings
        Setting::set('store_name', 'ShopBD Test Store', 'general', 'string');
        Setting::set('store_phone', '01711-000000', 'general', 'string');
        Setting::set('store_email', 'support@shopbd.com', 'general', 'string');
        Setting::set('inside_dhaka_delivery_fee', '60.00', 'shipping', 'number');
        Setting::set('outside_dhaka_delivery_fee', '120.00', 'shipping', 'number');
        Setting::set('free_shipping_threshold', '2500.00', 'shipping', 'number');
        Setting::set('currency_symbol', '৳', 'general', 'string');

        // Seed Category & Brand
        $this->category = Category::factory()->create([
            'name' => 'Men Fashion',
            'slug' => 'men-fashion',
            'is_active' => true,
        ]);

        $this->brand = Brand::factory()->create([
            'name' => 'Aarong BD',
            'slug' => 'aarong-bd',
            'is_active' => true,
        ]);

        // Seed Products
        $this->productA = Product::create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'name' => 'Handmade Cotton Panjabi',
            'slug' => 'handmade-cotton-panjabi',
            'sku' => 'PANJABI-001',
            'regular_price' => 1500.00,
            'sale_price' => 1200.00,
            'purchase_price' => 800.00,
            'stock_quantity' => 20,
            'low_stock_threshold' => 5,
            'is_active' => true,
            'is_featured' => true,
            'description' => 'Authentic Bangladeshi cotton panjabi with exquisite embroidery.',
        ]);

        $this->productB = Product::create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'name' => 'Pure Silk Kurti',
            'slug' => 'pure-silk-kurti',
            'sku' => 'KURTI-002',
            'regular_price' => 2000.00,
            'sale_price' => null,
            'purchase_price' => 1200.00,
            'stock_quantity' => 15,
            'low_stock_threshold' => 3,
            'is_active' => true,
            'is_featured' => false,
            'description' => 'Traditional silk kurti designed for festive celebrations.',
        ]);

        // Seed Users
        $this->customer = User::factory()->create([
            'name' => 'Rahim Ahmed',
            'email' => 'rahim@example.com',
            'phone' => '01712345678',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->otherCustomer = User::factory()->create([
            'name' => 'Karim Chowdhury',
            'email' => 'karim@example.com',
            'phone' => '01812345678',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Super Administrator',
            'email' => 'admin@shopbd.com',
            'phone' => '01711000000',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Seed Coupons
        $this->validCoupon = Coupon::create([
            'code' => 'EID2026',
            'type' => 'percentage',
            'value' => 10, // 10%
            'min_order_amount' => 500.00,
            'max_discount' => 500.00,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'usage_limit' => 100,
            'used_count' => 0,
        ]);

        $this->minSpendCoupon = Coupon::create([
            'code' => 'BIGSPEND',
            'type' => 'fixed',
            'value' => 300,
            'min_order_amount' => 5000.00, // higher than cart
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);
    }

    /**
     * Step 1: Browse categories and search products.
     */
    public function test_step_1_browse_categories_and_search_products(): void
    {
        // 1.1 Category listing page
        $categoryResponse = $this->get(route('products.category', $this->category->slug));
        $categoryResponse->assertStatus(200);
        $categoryResponse->assertSee('Men Fashion');
        $categoryResponse->assertSee('Handmade Cotton Panjabi');

        // 1.2 Search by keyword
        $searchResponse = $this->get(route('products.index', ['search' => 'Cotton Panjabi']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Handmade Cotton Panjabi');
        $searchResponse->assertDontSee('Pure Silk Kurti');

        // 1.3 Search by SKU
        $skuResponse = $this->get(route('products.index', ['search' => 'KURTI-002']));
        $skuResponse->assertStatus(200);
        $skuResponse->assertSee('Pure Silk Kurti');
        $skuResponse->assertDontSee('Handmade Cotton Panjabi');
    }

    /**
     * Step 2: View product details.
     */
    public function test_step_2_view_product_details(): void
    {
        $response = $this->get(route('products.show', $this->productA->slug));

        $response->assertStatus(200);
        $response->assertSee('Handmade Cotton Panjabi');
        $response->assertSee('PANJABI-001');
        $response->assertSee('৳1,200.00'); // Sale price
        $response->assertSee('৳1,500.00'); // Regular price strike-through
        $response->assertSee('In Stock');
        $response->assertSee('Men Fashion');
        $response->assertSee('Aarong BD');
        $response->assertSee('Inside Dhaka');
        $response->assertSee('Outside Dhaka');
    }

    /**
     * Step 3: Add products to the cart.
     */
    public function test_step_3_add_products_to_cart(): void
    {
        // 3.1 Guest adds product to cart
        $addResponse = $this->post(route('cart.add'), [
            'product_id' => $this->productA->id,
            'quantity' => 2,
        ]);

        $addResponse->assertRedirect();
        $cart = Cart::latest()->first();
        $this->assertNotNull($cart);
        $this->assertEquals(2, $cart->total_items);

        // 3.2 Verify cart view
        $cartViewResponse = $this->get(route('cart.index'));
        $cartViewResponse->assertStatus(200);
        $cartViewResponse->assertSee('Handmade Cotton Panjabi');
        $cartViewResponse->assertSee('PANJABI-001');
        // Subtotal: 2 * 1200 = 2400
        $cartViewResponse->assertSee('2,400.00');

        // 3.3 Verify dynamic cart count endpoint
        $countResponse = $this->get(route('cart.count'));
        $countResponse->assertStatus(200);
        $countResponse->assertJson(['count' => 2]);
    }

    /**
     * Step 4: Apply a valid or invalid coupon.
     */
    public function test_step_4_apply_valid_and_invalid_coupons(): void
    {
        // Add item to cart first (Subtotal = 1200.00)
        $this->post(route('cart.add'), [
            'product_id' => $this->productA->id,
            'quantity' => 1,
        ]);

        // 4.1 Apply invalid coupon code
        $invalidResponse = $this->post(route('cart.coupon.apply'), [
            'coupon_code' => 'FAKECODE123',
        ]);
        $invalidResponse->assertSessionHasErrors('coupon_code');
        $this->assertNull(session('applied_coupon_id'));

        // 4.2 Apply coupon that fails minimum spend threshold (needs 5000, cart has 1200)
        $minSpendResponse = $this->post(route('cart.coupon.apply'), [
            'coupon_code' => 'BIGSPEND',
        ]);
        $minSpendResponse->assertSessionHasErrors('coupon_code');
        $this->assertNull(session('applied_coupon_id'));

        // 4.3 Apply valid coupon (EID2026: 10% off min 500)
        $validResponse = $this->post(route('cart.coupon.apply'), [
            'coupon_code' => 'EID2026',
        ]);
        $validResponse->assertSessionHas('success');
        $validResponse->assertSessionHas('applied_coupon_id', $this->validCoupon->id);

        // 4.4 Verify cart page displays coupon discount
        $cartResponse = $this->get(route('cart.index'));
        $cartResponse->assertStatus(200);
        $cartResponse->assertSee('EID2026');
        $cartResponse->assertSee('120.00');
    }

    /**
     * Step 5: Complete a Cash on Delivery checkout.
     */
    public function test_step_5_complete_cash_on_delivery_checkout(): void
    {
        // Add 2 items (Subtotal: 2 * 1200 = 2400)
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->productA->id,
            'quantity' => 2,
        ]);

        // Apply coupon (10% of 2400 = 240)
        $this->actingAs($this->customer)->post(route('cart.coupon.apply'), [
            'coupon_code' => 'EID2026',
        ]);

        // 5.1 Test dynamic shipping fee calculation endpoint
        $calcResponse = $this->actingAs($this->customer)->postJson(route('checkout.calculate'), [
            'district' => 'Dhaka',
        ]);
        $calcResponse->assertStatus(200);
        $calcResponse->assertJson([
            'success' => true,
            'shipping_cost' => 60.00,
        ]);

        $calcOutside = $this->actingAs($this->customer)->postJson(route('checkout.calculate'), [
            'district' => 'Chittagong',
        ]);
        $calcOutside->assertStatus(200);
        $calcOutside->assertJson([
            'success' => true,
            'shipping_cost' => 120.00,
        ]);

        // 5.2 Submit checkout with COD
        $initialStock = $this->productA->stock_quantity; // 20

        $checkoutResponse = $this->actingAs($this->customer)->post(route('checkout.store'), [
            'recipient_name' => 'Rahim Ahmed',
            'phone' => '01712345678',
            'district' => 'Dhaka',
            'city' => 'Dhaka North',
            'delivery_address' => 'House 42, Road 11, Banani, Dhaka',
            'payment_method' => 'cod',
            'notes' => 'Please deliver between 2 PM and 5 PM',
        ]);

        $checkoutResponse->assertRedirect();
        $checkoutResponse->assertSessionHasNoErrors();

        // 5.3 Verify Order is persisted with exact calculations
        $order = Order::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals(Order::STATUS_PENDING, $order->order_status);
        $this->assertEquals(Order::PAYMENT_UNPAID, $order->payment_status);
        $this->assertEquals('cod', $order->payment_method);
        $this->assertEquals(2400.00, (float) $order->subtotal);
        $this->assertEquals(240.00, (float) $order->discount_amount);
        $this->assertEquals(60.00, (float) $order->shipping_cost);
        $this->assertEquals(2220.00, (float) $order->total_amount); // 2400 - 240 + 60

        // 5.4 Verify Stock Deduction & Inventory Movement
        $this->productA->refresh();
        $this->assertEquals($initialStock - 2, $this->productA->stock_quantity); // 18

        $movement = InventoryMovement::where('product_id', $this->productA->id)
            ->where('order_id', $order->id)
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals('sale', $movement->type);
        $this->assertEquals(-2, $movement->quantity_change);
        $this->assertEquals(18, $movement->current_stock);

        // 5.5 Verify OrderStatusHistory
        $statusHistory = OrderStatusHistory::where('order_id', $order->id)->first();
        $this->assertNotNull($statusHistory);
        $this->assertEquals(Order::STATUS_PENDING, $statusHistory->to_status);

        // 5.6 Verify Cart is cleared
        $cart = Cart::where('user_id', $this->customer->id)->first();
        $this->assertTrue(!$cart || $cart->items()->count() === 0);
    }

    /**
     * Step 6: View the created order and invoice.
     */
    public function test_step_6_view_created_order_and_printable_invoice(): void
    {
        // Create an order for customer
        $order = Order::create([
            'order_number' => 'ORD-20261011-001001',
            'user_id' => $this->customer->id,
            'recipient_name' => $this->customer->name,
            'phone' => $this->customer->phone,
            'division' => 'Dhaka',
            'district' => 'Dhaka',
            'delivery_address' => 'House 42, Road 11, Banani',
            'subtotal' => 1200.00,
            'discount_amount' => 0.00,
            'shipping_cost' => 60.00,
            'total_amount' => 1260.00,
            'payment_method' => 'cod',
            'payment_status' => Order::PAYMENT_UNPAID,
            'order_status' => Order::STATUS_PENDING,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productA->id,
            'product_name' => $this->productA->name,
            'sku' => $this->productA->sku,
            'unit_price' => 1200.00,
            'purchase_price' => 800.00,
            'quantity' => 1,
            'subtotal' => 1200.00,
        ]);

        // 6.1 Confirmation page with active session token
        $confirmationResponse = $this->actingAs($this->customer)
            ->withSession(['last_placed_order_number' => $order->order_number])
            ->get(route('checkout.confirmation', $order->order_number));
        $confirmationResponse->assertStatus(200);
        $confirmationResponse->assertSee($order->order_number);
        $confirmationResponse->assertSee('Handmade Cotton Panjabi');

        // 6.2 Customer Order History List
        $historyResponse = $this->actingAs($this->customer)->get(route('customer.orders.index'));
        $historyResponse->assertStatus(200);
        $historyResponse->assertSee($order->order_number);

        // 6.3 Customer Order Detail View
        $detailResponse = $this->actingAs($this->customer)->get(route('customer.orders.show', $order->order_number));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($order->order_number);
        $detailResponse->assertSee('৳1,260.00');
        $detailResponse->assertSee('Pending');

        // 6.4 Customer Printable Invoice View
        $invoiceResponse = $this->actingAs($this->customer)->get(route('customer.orders.invoice', $order->order_number));
        $invoiceResponse->assertStatus(200);
        $invoiceResponse->assertSee($order->order_number);
        $invoiceResponse->assertSee('INVOICE');
        $invoiceResponse->assertSee('Handmade Cotton Panjabi');
        $invoiceResponse->assertSee('৳1,260.00');
    }

    /**
     * Step 7: Log in as an administrator.
     */
    public function test_step_7_administrator_authentication(): void
    {
        // 7.1 Admin login page renders
        $loginPage = $this->get(route('admin.login'));
        $loginPage->assertStatus(200);
        $loginPage->assertSee('Admin Portal');

        // 7.2 Submit valid admin credentials
        $loginAction = $this->post(route('admin.login.submit'), [
            'login' => 'admin@shopbd.com',
            'password' => 'password123',
        ]);
        $loginAction->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);

        // 7.3 Access executive dashboard
        $dashboardResponse = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Total Sales');
        $dashboardResponse->assertSee('Total Orders');
        $dashboardResponse->assertSee('pending fulfillment');
    }

    /**
     * Step 8: Update order status and inventory.
     */
    public function test_step_8_admin_update_order_status_and_inventory(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-20261011-002002',
            'user_id' => $this->customer->id,
            'recipient_name' => $this->customer->name,
            'phone' => $this->customer->phone,
            'division' => 'Dhaka',
            'district' => 'Dhaka',
            'delivery_address' => 'Dhanmondi, Dhaka',
            'subtotal' => 1200.00,
            'discount_amount' => 0.00,
            'shipping_cost' => 60.00,
            'total_amount' => 1260.00,
            'payment_method' => 'cod',
            'payment_status' => Order::PAYMENT_UNPAID,
            'order_status' => Order::STATUS_PENDING,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productA->id,
            'product_name' => $this->productA->name,
            'sku' => $this->productA->sku,
            'unit_price' => 1200.00,
            'purchase_price' => 800.00,
            'quantity' => 2,
            'subtotal' => 2400.00,
        ]);

        // 8.1 Admin advances order from Pending -> Confirmed
        $advanceResponse = $this->actingAs($this->admin)->post(route('admin.orders.update-status', $order->id), [
            'order_status' => Order::STATUS_CONFIRMED,
            'remarks' => 'Confirmed via call with customer',
        ]);
        $advanceResponse->assertRedirect();
        $order->refresh();
        $this->assertEquals(Order::STATUS_CONFIRMED, $order->order_status);

        // 8.2 Admin advances order from Confirmed -> Processing
        $this->actingAs($this->admin)->post(route('admin.orders.update-status', $order->id), [
            'order_status' => Order::STATUS_PROCESSING,
            'remarks' => 'Picked and packed',
        ]);
        $order->refresh();
        $this->assertEquals(Order::STATUS_PROCESSING, $order->order_status);

        // 8.3 Admin manually adjusts inventory for Product A
        $stockBefore = $this->productA->stock_quantity; // 20
        $adjustResponse = $this->actingAs($this->admin)->post(route('admin.products.stock.adjust', $this->productA->id), [
            'adjustment_type' => 'add',
            'quantity' => 10,
            'remarks' => 'Received new shipment from factory',
        ]);
        $adjustResponse->assertRedirect();
        $this->productA->refresh();
        $this->assertEquals($stockBefore + 10, $this->productA->stock_quantity); // 30

        $adjMovement = InventoryMovement::where('product_id', $this->productA->id)
            ->where('type', 'adjustment')
            ->latest('id')
            ->first();
        $this->assertNotNull($adjMovement);
        $this->assertEquals(10, $adjMovement->quantity_change);
        $this->assertEquals($this->admin->id, $adjMovement->created_by_user_id);
    }

    /**
     * Step 9: Verify customers cannot access another customer's orders (IDOR).
     */
    public function test_step_9_idor_protection_customer_cannot_access_another_orders(): void
    {
        // Create an order owned strictly by Customer A
        $orderA = Order::create([
            'order_number' => 'ORD-20261011-OWNED-A',
            'user_id' => $this->customer->id,
            'recipient_name' => $this->customer->name,
            'phone' => $this->customer->phone,
            'division' => 'Dhaka',
            'district' => 'Dhaka',
            'delivery_address' => 'Private Address A',
            'subtotal' => 1000.00,
            'discount_amount' => 0.00,
            'shipping_cost' => 60.00,
            'total_amount' => 1060.00,
            'payment_method' => 'cod',
            'payment_status' => Order::PAYMENT_UNPAID,
            'order_status' => Order::STATUS_PENDING,
        ]);

        // 9.1 Other Customer tries to view Customer A's order details
        $viewResponse = $this->actingAs($this->otherCustomer)->get(route('customer.orders.show', $orderA->order_number));
        $viewResponse->assertStatus(403);

        // 9.2 Other Customer tries to view Customer A's invoice
        $invoiceResponse = $this->actingAs($this->otherCustomer)->get(route('customer.orders.invoice', $orderA->order_number));
        $invoiceResponse->assertStatus(403);

        // 9.3 Other Customer tries to cancel Customer A's order
        $cancelResponse = $this->actingAs($this->otherCustomer)->post(route('customer.orders.cancel', $orderA->order_number));
        $cancelResponse->assertStatus(403);

        // 9.4 Authenticated other customer cannot view Customer A's order confirmation (IDOR)
        $otherCustomerConfirmation = $this->actingAs($this->otherCustomer)->get(route('checkout.confirmation', $orderA->order_number));
        $otherCustomerConfirmation->assertStatus(403);

        // 9.5 Unauthenticated Guest tries to view confirmation of Customer A's order without session
        auth()->logout();
        $guestConfirmation = $this->get(route('checkout.confirmation', $orderA->order_number));
        $guestConfirmation->assertRedirect(route('login'));
    }

    /**
     * Step 10: Verify invalid prices, quantities, and payment statuses cannot bypass server rules.
     */
    public function test_step_10_server_side_defense_against_tampered_payloads(): void
    {
        // 10.1 Negative quantity in cart add is rejected
        $negativeQty = $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->productA->id,
            'quantity' => -5,
        ]);
        $negativeQty->assertSessionHasErrors('quantity');

        // 10.2 Zero quantity in cart add is rejected
        $zeroQty = $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->productA->id,
            'quantity' => 0,
        ]);
        $zeroQty->assertSessionHasErrors('quantity');

        // 10.3 Exceeding available stock quantity is rejected
        $overStock = $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->productA->id,
            'quantity' => 99999,
        ]);
        $overStock->assertSessionHasErrors();

        // 10.4 Add legitimate item to cart
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->productA->id,
            'quantity' => 1,
        ]);

        // 10.5 Tampered price in checkout payload is ignored (server computes from Product model)
        $tamperedCheckout = $this->actingAs($this->customer)->post(route('checkout.store'), [
            'recipient_name' => 'Malicious Actor',
            'phone' => '01712345678',
            'district' => 'Dhaka',
            'delivery_address' => 'Hacker Den, Dhaka',
            'payment_method' => 'cod',
            'total_amount' => 1.00, // Attempting to pay 1 BDT for a 1200 BDT item
            'subtotal' => 1.00,
            'payment_status' => Order::PAYMENT_PAID, // Attempting to mark COD order as prepaid
        ]);

        $tamperedCheckout->assertRedirect();

        $placedOrder = Order::where('recipient_name', 'Malicious Actor')->latest()->first();
        $this->assertNotNull($placedOrder);
        // Server enforced real prices: subtotal = 1200.00, shipping = 60.00, total = 1260.00
        $this->assertEquals(1200.00, (float) $placedOrder->subtotal);
        $this->assertEquals(60.00, (float) $placedOrder->shipping_cost);
        $this->assertEquals(1260.00, (float) $placedOrder->total_amount);
        // Server enforced initial payment status: strictly unpaid for COD
        $this->assertEquals(Order::PAYMENT_UNPAID, $placedOrder->payment_status);

        // 10.6 Illegal order state transition jump (e.g. Pending directly to Shipped/Delivered) is rejected
        $illegalJump = $this->actingAs($this->admin)->post(route('admin.orders.update-status', $placedOrder->id), [
            'order_status' => Order::STATUS_DELIVERED,
            'remarks' => 'Attempting illegal jump',
        ]);
        $illegalJump->assertSessionHasErrors();
        $placedOrder->refresh();
        $this->assertEquals(Order::STATUS_PENDING, $placedOrder->order_status);
    }
}
