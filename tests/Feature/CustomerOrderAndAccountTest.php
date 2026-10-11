<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Services\OrderTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerOrderAndAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('inside_dhaka_delivery_fee', '60.00', 'shipping', 'number');
        Setting::set('outside_dhaka_delivery_fee', '120.00', 'shipping', 'number');
    }

    protected function createProduct(array $attributes = []): Product
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        return Product::create(array_merge([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Cotton Polo Shirt',
            'slug' => 'cotton-polo-shirt-' . Str::random(5),
            'sku' => 'SKU-' . strtoupper(Str::random(6)),
            'regular_price' => 850.00,
            'sale_price' => 750.00,
            'purchase_price' => 450.00,
            'stock_quantity' => 15,
            'low_stock_threshold' => 3,
            'is_active' => true,
            'is_featured' => true,
        ], $attributes));
    }

    protected function createOrder(User $user, array $orderAttrs = [], ?Product $product = null, int $qty = 2): Order
    {
        $product = $product ?? $this->createProduct();

        $subtotal = (float) $product->price * $qty;
        $shipping = 60.00;
        $total = $subtotal + $shipping;

        $order = Order::create(array_merge([
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'user_id' => $user->id,
            'recipient_name' => $user->name,
            'phone' => '01711111111',
            'delivery_address' => 'House 12, Road 4, Dhanmondi',
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
        ], $orderAttrs));

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'unit_price' => $product->price,
            'unit_purchase_price' => $product->purchase_price,
            'quantity' => $qty,
            'subtotal' => $subtotal,
        ]);

        return $order;
    }

    public function test_customer_can_view_their_order_history_and_cannot_see_others_orders(): void
    {
        $customerA = User::factory()->create(['role' => 'customer']);
        $customerB = User::factory()->create(['role' => 'customer']);

        $orderA = $this->createOrder($customerA);
        $orderB = $this->createOrder($customerB);

        $response = $this->actingAs($customerA)->get(route('customer.orders.index'));

        $response->assertStatus(200);
        $response->assertSee($orderA->order_number);
        $response->assertDontSee($orderB->order_number);
    }

    public function test_customer_can_view_their_own_order_details(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = $this->createOrder($customer);

        $response = $this->actingAs($customer)->get(route('customer.orders.show', $order->order_number));

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
        $response->assertSee($order->recipient_name);
    }

    public function test_customer_cannot_view_another_customers_order_details(): void
    {
        $customerA = User::factory()->create(['role' => 'customer']);
        $customerB = User::factory()->create(['role' => 'customer']);

        $orderB = $this->createOrder($customerB);

        $response = $this->actingAs($customerA)->get(route('customer.orders.show', $orderB->order_number));

        $response->assertStatus(403);
    }

    public function test_customer_can_view_their_own_printable_invoice(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = $this->createOrder($customer);

        $response = $this->actingAs($customer)->get(route('customer.orders.invoice', $order->order_number));

        $response->assertStatus(200);
        $response->assertSee('INVOICE');
        $response->assertSee($order->order_number);
    }

    public function test_customer_cannot_view_another_customers_printable_invoice(): void
    {
        $customerA = User::factory()->create(['role' => 'customer']);
        $customerB = User::factory()->create(['role' => 'customer']);

        $orderB = $this->createOrder($customerB);

        $response = $this->actingAs($customerA)->get(route('customer.orders.invoice', $orderB->order_number));

        $response->assertStatus(403);
    }

    public function test_customer_can_cancel_pending_order_and_inventory_is_safely_restocked(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct(['stock_quantity' => 10]);
        $order = $this->createOrder($customer, ['order_status' => Order::STATUS_PENDING], $product, 3);

        $response = $this->actingAs($customer)->post(route('customer.orders.cancel', $order->order_number), [
            'reason' => 'Changed my mind',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals(Order::STATUS_CANCELLED, $order->order_status);

        // Product stock should have been restocked from 10 to 13
        $product->refresh();
        $this->assertEquals(13, $product->stock_quantity);

        // Audit trail should exist
        $this->assertDatabaseHas('inventory_movements', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'type' => 'return',
            'quantity_change' => 3,
        ]);
    }

    public function test_inventory_is_not_restocked_multiple_times_on_repeated_transitions(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct(['stock_quantity' => 10]);
        $order = $this->createOrder($customer, ['order_status' => Order::STATUS_PENDING], $product, 2);

        $transitionService = app(OrderTransitionService::class);

        // First transition to cancelled
        $transitionService->transition($order, Order::STATUS_CANCELLED, 'First cancellation', $customer);

        $product->refresh();
        $this->assertEquals(12, $product->stock_quantity);

        $this->assertTrue($order->hasRestockedInventory());

        // Call transition again or restock logic - should not increase stock again
        $order->restockInventorySafely($customer, 'Duplicate trigger check');

        $product->refresh();
        $this->assertEquals(12, $product->stock_quantity, 'Stock should NOT be incremented more than once');

        // Only one return inventory movement
        $this->assertEquals(1, InventoryMovement::where('order_id', $order->id)->where('type', 'return')->count());
    }

    public function test_customer_cannot_cancel_shipped_order(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = $this->createOrder($customer, ['order_status' => Order::STATUS_SHIPPED]);

        $response = $this->actingAs($customer)->post(route('customer.orders.cancel', $order->order_number), [
            'reason' => 'Late cancellation attempt',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $order->refresh();
        $this->assertEquals(Order::STATUS_SHIPPED, $order->order_status);
    }

    public function test_customer_can_manage_wishlist(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();

        // 1. Add to wishlist
        $addResponse = $this->actingAs($customer)->post(route('customer.wishlist.store'), [
            'product_id' => $product->id,
        ]);
        $addResponse->assertRedirect();

        $this->assertDatabaseHas('wishlist_items', [
            'product_id' => $product->id,
        ]);

        // 2. View wishlist
        $viewResponse = $this->actingAs($customer)->get(route('customer.wishlist.index'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee($product->name);

        // 3. Move to cart
        $moveResponse = $this->actingAs($customer)->post(route('customer.wishlist.move-to-cart', $product->id));
        $moveResponse->assertRedirect(route('cart.index'));

        // Wishlist should no longer have this product
        $this->assertDatabaseMissing('wishlist_items', [
            'product_id' => $product->id,
        ]);

        // 4. Add back and remove
        $this->actingAs($customer)->post(route('customer.wishlist.store'), [
            'product_id' => $product->id,
        ]);
        $delResponse = $this->actingAs($customer)->delete(route('customer.wishlist.destroy', $product->id));
        $delResponse->assertRedirect();

        $this->assertDatabaseMissing('wishlist_items', [
            'product_id' => $product->id,
        ]);
    }

    public function test_customer_cannot_review_product_without_delivered_order(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();

        // No order at all
        $response = $this->actingAs($customer)->post(route('customer.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great product without buying!',
        ]);

        $response->assertSessionHasErrors(['product_id']);
        $this->assertDatabaseMissing('reviews', ['product_id' => $product->id]);

        // Order exists but is only 'pending' (not delivered)
        $this->createOrder($customer, ['order_status' => Order::STATUS_PENDING], $product);

        $response2 = $this->actingAs($customer)->post(route('customer.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Tried reviewing before delivery',
        ]);

        $response2->assertSessionHasErrors(['product_id']);
        $this->assertDatabaseMissing('reviews', ['product_id' => $product->id]);
    }

    public function test_customer_with_delivered_order_can_submit_review_which_requires_moderation(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->createProduct();

        // Create delivered order for this product
        $this->createOrder($customer, ['order_status' => Order::STATUS_DELIVERED], $product);

        $response = $this->actingAs($customer)->post(route('customer.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Excellent quality cotton shirt, very comfortable in Bangladesh summer!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'rating' => 5,
            'is_approved' => false, // Requires moderation before public display
        ]);
    }
}
