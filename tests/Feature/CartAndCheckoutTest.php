<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Tests\TestCase;

class CartAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed delivery settings
        Setting::set('inside_dhaka_delivery_fee', '60.00', 'shipping', 'number');
        Setting::set('outside_dhaka_delivery_fee', '120.00', 'shipping', 'number');
        Setting::set('free_shipping_threshold', '2500.00', 'shipping', 'number');
        Setting::set('store_phone', '01700-000000', 'general', 'string');
    }

    protected function createProduct(array $attributes = []): Product
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        return Product::create(array_merge([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Premium Panjabi',
            'slug' => 'premium-panjabi-' . Str::random(5),
            'sku' => 'SKU-' . strtoupper(Str::random(6)),
            'regular_price' => 1200.00,
            'sale_price' => 1000.00,
            'purchase_price' => 700.00,
            'stock_quantity' => 10,
            'low_stock_threshold' => 3,
            'is_active' => true,
            'is_featured' => true,
        ], $attributes));
    }

    public function test_guest_can_add_item_to_cart_and_view_cart(): void
    {
        $product = $this->createProduct(['stock_quantity' => 5]);

        $response = $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $viewResponse = $this->get(route('cart.index'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee($product->name);
        $viewResponse->assertSee('1,000.00'); // Effective sale price
    }

    public function test_cannot_add_more_than_available_stock_to_cart(): void
    {
        $product = $this->createProduct(['stock_quantity' => 3]);

        $response = $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseMissing('cart_items', [
            'product_id' => $product->id,
        ]);
    }

    public function test_cannot_add_inactive_or_out_of_stock_product(): void
    {
        $inactiveProduct = $this->createProduct(['is_active' => false]);
        $outOfStockProduct = $this->createProduct(['stock_quantity' => 0]);

        // Inactive product
        $response1 = $this->post(route('cart.add'), [
            'product_id' => $inactiveProduct->id,
            'quantity' => 1,
        ]);
        $response1->assertSessionHasErrors('product_id');

        // Out of stock product
        $response2 = $this->post(route('cart.add'), [
            'product_id' => $outOfStockProduct->id,
            'quantity' => 1,
        ]);
        $response2->assertSessionHasErrors('quantity');
    }

    public function test_can_update_quantity_and_recalculate_subtotal(): void
    {
        $product = $this->createProduct([
            'regular_price' => 500.00,
            'sale_price' => null,
            'stock_quantity' => 10,
        ]);

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $cartItem = CartItem::where('product_id', $product->id)->firstOrFail();

        $updateResponse = $this->put(route('cart.update', $cartItem->id), [
            'quantity' => 3,
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 3,
        ]);

        $totals = app(CartService::class)->calculateTotals();
        $this->assertEquals(1500.00, $totals['subtotal']);
    }

    public function test_can_remove_item_and_clear_cart(): void
    {
        $product1 = $this->createProduct();
        $product2 = $this->createProduct();

        $this->post(route('cart.add'), ['product_id' => $product1->id, 'quantity' => 1]);
        $this->post(route('cart.add'), ['product_id' => $product2->id, 'quantity' => 1]);

        $item1 = CartItem::where('product_id', $product1->id)->firstOrFail();

        // Remove single item
        $this->delete(route('cart.destroy', $item1->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('cart_items', ['id' => $item1->id]);
        $this->assertDatabaseHas('cart_items', ['product_id' => $product2->id]);

        // Clear cart
        $this->post(route('cart.clear'))
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseMissing('cart_items', ['product_id' => $product2->id]);
    }

    public function test_guest_cart_merges_into_customer_cart_upon_login(): void
    {
        $product1 = $this->createProduct(['name' => 'Guest Item']);
        $product2 = $this->createProduct(['name' => 'User Existing Item']);

        $customer = User::factory()->create([
            'email' => 'customer@example.com',
            'phone' => '+8801711112233',
            'password' => bcrypt('password123'),
            'role' => 'customer',
        ]);

        // Customer already had product2 in their cart
        $userCart = Cart::create(['user_id' => $customer->id]);
        $userCart->items()->create([
            'product_id' => $product2->id,
            'quantity' => 1,
            'unit_price' => $product2->effective_price,
        ]);

        // Guest adds product1 to session cart
        $this->post(route('cart.add'), [
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);

        // Guest logs in
        $response = $this->post(route('login'), [
            'login' => 'customer@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect();

        // Customer's cart should now have both products
        $updatedUserCart = Cart::where('user_id', $customer->id)->with('items')->first();
        $this->assertCount(2, $updatedUserCart->items);
        $this->assertTrue($updatedUserCart->items->pluck('product_id')->contains($product1->id));
        $this->assertTrue($updatedUserCart->items->pluck('product_id')->contains($product2->id));
    }

    public function test_coupon_validation_and_calculation(): void
    {
        $coupon = Coupon::create([
            'code' => 'EID10',
            'type' => 'percentage',
            'value' => 10.00,
            'min_order_amount' => 1500.00,
            'max_discount_amount' => 500.00,
            'is_active' => true,
        ]);

        $product = $this->createProduct(['regular_price' => 1000.00, 'sale_price' => null]);
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

        // Below minimum order amount (1000 < 1500)
        $failResponse = $this->post(route('cart.coupon.apply'), [
            'coupon_code' => 'EID10',
        ]);
        $failResponse->assertSessionHasErrors('coupon_code');

        // Increase quantity so subtotal = 2000 >= 1500
        $cartItem = CartItem::where('product_id', $product->id)->firstOrFail();
        $this->put(route('cart.update', $cartItem->id), ['quantity' => 2]);

        $successResponse = $this->post(route('cart.coupon.apply'), [
            'coupon_code' => 'EID10',
        ]);
        $successResponse->assertSessionHas('success');

        $totals = app(CartService::class)->calculateTotals();
        $this->assertEquals(200.00, $totals['discount_amount']); // 10% of 2000 = 200

        // Remove coupon
        $this->delete(route('cart.coupon.remove'))
            ->assertSessionHas('info');

        $totalsWithoutCoupon = app(CartService::class)->calculateTotals();
        $this->assertEquals(0.00, $totalsWithoutCoupon['discount_amount']);
    }

    public function test_shipping_calculation_for_inside_dhaka_and_outside_dhaka(): void
    {
        $product = $this->createProduct(['regular_price' => 500.00, 'sale_price' => null]);
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

        // Inside Dhaka
        $insideTotals = app(CartService::class)->calculateTotals(null, 'Dhaka');
        $this->assertEquals(60.00, $insideTotals['shipping_cost']);
        $this->assertEquals(560.00, $insideTotals['total_amount']);

        // Outside Dhaka
        $outsideTotals = app(CartService::class)->calculateTotals(null, 'Chattogram');
        $this->assertEquals(120.00, $outsideTotals['shipping_cost']);
        $this->assertEquals(620.00, $outsideTotals['total_amount']);

        // Free Shipping threshold (>= 2500)
        $cartItem = CartItem::where('product_id', $product->id)->firstOrFail();
        $this->put(route('cart.update', $cartItem->id), ['quantity' => 5]); // 5 * 500 = 2500

        $freeTotals = app(CartService::class)->calculateTotals(null, 'Chattogram');
        $this->assertEquals(0.00, $freeTotals['shipping_cost']);
        $this->assertTrue($freeTotals['free_shipping_eligible']);
        $this->assertEquals(2500.00, $freeTotals['total_amount']);
    }

    public function test_checkout_validation_errors(): void
    {
        $product = $this->createProduct();
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

        $response = $this->post(route('checkout.store'), [
            'recipient_name' => '',
            'phone' => '12345', // Invalid phone
            'district' => '',
            'delivery_address' => '',
            'payment_method' => 'cod',
        ]);

        $response->assertSessionHasErrors(['recipient_name', 'phone', 'district', 'delivery_address']);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_successful_cash_on_delivery_checkout_with_inventory_locking_and_snapshots(): void
    {
        $product = $this->createProduct([
            'name' => 'Cotton Shirt',
            'sku' => 'SHIRT-001',
            'purchase_price' => 450.00,
            'regular_price' => 800.00,
            'sale_price' => 700.00,
            'stock_quantity' => 10,
        ]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 2]);

        $response = $this->post(route('checkout.store'), [
            'recipient_name' => 'Sajjad Hossain',
            'phone' => '01712345678',
            'district' => 'Dhaka',
            'division' => 'Dhaka',
            'delivery_address' => 'House 14, Road 2, Banani, Dhaka',
            'delivery_notes' => 'Deliver between 2 PM - 5 PM',
            'payment_method' => 'cod',
            'idempotency_key' => Str::uuid()->toString(),
        ]);

        $order = Order::first();
        $this->assertNotNull($order);

        $response->assertRedirect(route('checkout.confirmation', $order->order_number));

        // Order assertions
        $this->assertEquals('Sajjad Hossain', $order->recipient_name);
        $this->assertEquals('01712345678', $order->phone);
        $this->assertEquals('Dhaka', $order->district);
        $this->assertEquals('inside_dhaka', $order->shipping_zone);
        $this->assertEquals(60.00, (float) $order->shipping_cost);
        $this->assertEquals(1400.00, (float) $order->subtotal); // 2 * 700
        $this->assertEquals(1460.00, (float) $order->total_amount); // 1400 + 60
        $this->assertEquals('cod', $order->payment_method);
        $this->assertEquals('unpaid', $order->payment_status); // COD is unpaid at checkout
        $this->assertEquals('pending', $order->order_status);

        // Snapshot assertions
        $this->assertCount(1, $order->items);
        $orderItem = $order->items->first();
        $this->assertEquals('Cotton Shirt', $orderItem->product_name);
        $this->assertEquals('SHIRT-001', $orderItem->product_sku);
        $this->assertEquals(450.00, (float) $orderItem->unit_purchase_price);
        $this->assertEquals(700.00, (float) $orderItem->unit_price);
        $this->assertEquals(2, $orderItem->quantity);
        $this->assertEquals(1400.00, (float) $orderItem->subtotal);

        // Stock deduction
        $product->refresh();
        $this->assertEquals(8, $product->stock_quantity); // 10 - 2

        // Inventory movement audit log
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'order_id' => $order->id,
            'type' => 'sale',
            'quantity_change' => -2,
            'previous_stock' => 10,
            'current_stock' => 8,
        ]);

        // Order Status History
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'to_status' => 'pending',
        ]);

        // Cart should be empty
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_guest_checkout_auto_provisions_customer_account(): void
    {
        $product = $this->createProduct(['stock_quantity' => 5]);
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

        $phone = '01899887766';
        $this->assertDatabaseMissing('users', ['phone' => '01899887766']);

        $this->post(route('checkout.store'), [
            'recipient_name' => 'Fahim Rahman',
            'phone' => $phone,
            'district' => 'Chattogram',
            'delivery_address' => 'GEC Circle, Nasirabad, Chattogram',
            'payment_method' => 'cod',
        ]);

        // User should now exist
        $user = User::where('phone', '01899887766')->first();
        $this->assertNotNull($user);
        $this->assertEquals('customer', $user->role);
        $this->assertEquals('Fahim Rahman', $user->name);

        // Address should be saved
        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'district' => 'Chattogram',
        ]);

        // Order linked to this user
        $order = Order::first();
        $this->assertEquals($user->id, $order->user_id);
    }

    public function test_stock_race_prevents_overselling_and_rolls_back_transaction(): void
    {
        $product = $this->createProduct(['stock_quantity' => 1]);

        // Add 1 to cart
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

        // Meanwhile, another customer buys the last unit
        $product->update(['stock_quantity' => 0]);

        $response = $this->post(route('checkout.store'), [
            'recipient_name' => 'Late Customer',
            'phone' => '01711223344',
            'district' => 'Dhaka',
            'delivery_address' => 'Mirpur 10, Dhaka',
            'payment_method' => 'cod',
        ]);

        $response->assertSessionHasErrors('stock');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_idempotency_prevents_duplicate_orders(): void
    {
        $product = $this->createProduct(['stock_quantity' => 10]);
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

        $idempotencyKey = 'unique-key-12345';

        $data = [
            'recipient_name' => 'Double Clicker',
            'phone' => '01711000000',
            'district' => 'Dhaka',
            'delivery_address' => 'Gulshan 2, Dhaka',
            'payment_method' => 'cod',
            'idempotency_key' => $idempotencyKey,
        ];

        // First click
        $response1 = $this->post(route('checkout.store'), $data);
        $order1 = Order::first();
        $this->assertNotNull($order1);
        $response1->assertRedirect(route('checkout.confirmation', $order1->order_number));

        // Re-add product to simulate rapid duplicate POST
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

        // Second click with identical idempotency key
        $response2 = $this->post(route('checkout.store'), $data);
        $response2->assertRedirect(route('checkout.confirmation', $order1->order_number));

        // No new order should have been created
        $this->assertDatabaseCount('orders', 1);
    }
}
