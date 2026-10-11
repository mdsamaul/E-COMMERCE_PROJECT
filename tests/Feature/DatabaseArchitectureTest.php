<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Address;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseArchitectureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test User model, roles, and address relationship.
     */
    public function test_user_creation_roles_and_address_relationships(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin_test@shopbd.com',
            'phone' => '01711223344',
        ]);
        $customer = User::factory()->create([
            'email' => 'customer_test@shopbd.com',
            'phone' => '01799887766',
        ]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isCustomer());
        $this->assertTrue($customer->isCustomer());
        $this->assertFalse($customer->isAdmin());

        $address = Address::factory()->create([
            'user_id' => $customer->id,
            'is_default' => true,
            'district' => 'Dhaka',
        ]);

        $this->assertCount(1, $customer->addresses);
        $this->assertEquals($address->id, $customer->defaultAddress->id);
        $this->assertStringContainsString('Dhaka', $address->full_address);
    }

    /**
     * Test Category hierarchy and Brand associations.
     */
    public function test_category_hierarchy_and_brand_relationships(): void
    {
        $parent = Category::factory()->create([
            'name' => 'Clothing Test',
            'parent_id' => null,
        ]);

        $child = Category::factory()->create([
            'name' => 'Men Fashion',
            'parent_id' => $parent->id,
        ]);

        $brand = Brand::factory()->create([
            'name' => 'Aarong Test',
        ]);

        $product = Product::factory()->create([
            'category_id' => $child->id,
            'brand_id' => $brand->id,
            'name' => 'Test Panjabi',
            'regular_price' => 2000.00,
            'sale_price' => 1800.00,
            'stock_quantity' => 25,
        ]);

        $this->assertEquals($parent->id, $child->parent->id);
        $this->assertTrue($parent->children->contains($child));
        $this->assertEquals($child->id, $product->category->id);
        $this->assertEquals($brand->id, $product->brand->id);
    }

    /**
     * Test Product pricing accessors, images, and soft deletes.
     */
    public function test_product_pricing_accessors_and_soft_deletes(): void
    {
        $product = Product::factory()->create([
            'regular_price' => 1000.00,
            'sale_price' => 800.00,
            'stock_quantity' => 15,
        ]);

        $this->assertTrue($product->has_discount);
        $this->assertEquals(800.00, $product->effective_price);
        $this->assertEquals(20, $product->discount_percentage);
        $this->assertTrue($product->in_stock);

        $image = ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/test.jpg',
            'is_primary' => true,
            'sort_order' => 1,
        ]);

        $this->assertEquals($image->id, $product->primaryImage->id);

        // Soft delete test
        $product->delete();
        $this->assertSoftDeleted($product);
        $this->assertNull(Product::find($product->id));
        $this->assertNotNull(Product::withTrashed()->find($product->id));
    }

    /**
     * Test Cart and CartItem relations and calculation accessors.
     */
    public function test_cart_and_cart_item_calculations(): void
    {
        $customer = User::factory()->create();
        $cart = Cart::create([
            'user_id' => $customer->id,
            'last_active_at' => now(),
        ]);

        $product1 = Product::factory()->create(['regular_price' => 500.00]);
        $product2 = Product::factory()->create(['regular_price' => 300.00]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'unit_price' => 500.00,
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'unit_price' => 300.00,
        ]);

        $this->assertEquals(3, $cart->total_items);
        $this->assertEquals(1300.00, $cart->subtotal);
    }

    /**
     * Test Wishlist and WishlistItem relationships.
     */
    public function test_wishlist_and_wishlist_items(): void
    {
        $customer = User::factory()->create();
        $wishlist = Wishlist::create(['user_id' => $customer->id]);
        $product = Product::factory()->create();

        $item = WishlistItem::create([
            'wishlist_id' => $wishlist->id,
            'product_id' => $product->id,
        ]);

        $this->assertCount(1, $wishlist->items);
        $this->assertEquals($product->id, $wishlist->items->first()->product->id);
    }

    /**
     * Test Coupon discount calculations and limits.
     */
    public function test_coupon_calculations_and_validation(): void
    {
        $percentageCoupon = Coupon::create([
            'code' => 'TEST10',
            'type' => 'percentage',
            'value' => 10.00,
            'min_order_amount' => 1000.00,
            'max_discount_amount' => 200.00,
            'is_active' => true,
        ]);

        $this->assertFalse($percentageCoupon->isValidForAmount(500.00));
        $this->assertTrue($percentageCoupon->isValidForAmount(1500.00));

        // 10% of 1500 = 150
        $this->assertEquals(150.00, $percentageCoupon->calculateDiscount(1500.00));
        // 10% of 3000 = 300, capped at 200
        $this->assertEquals(200.00, $percentageCoupon->calculateDiscount(3000.00));

        $fixedCoupon = Coupon::create([
            'code' => 'FIXED100',
            'type' => 'fixed',
            'value' => 100.00,
            'min_order_amount' => 500.00,
            'is_active' => true,
        ]);

        $this->assertEquals(100.00, $fixedCoupon->calculateDiscount(800.00));
    }

    /**
     * Test Order, OrderItems, immutable snapshot, and status history.
     */
    public function test_order_and_immutable_item_snapshots(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Original Product Name',
            'sku' => 'TEST-SKU-001',
            'purchase_price' => 600.00,
            'regular_price' => 1000.00,
        ]);

        $order = Order::create([
            'order_number' => 'SBD-TEST-0001',
            'user_id' => $customer->id,
            'recipient_name' => 'Test Recipient',
            'phone' => '01700000000',
            'delivery_address' => 'Test Address, Dhaka',
            'division' => 'Dhaka',
            'district' => 'Dhaka',
            'shipping_zone' => 'inside_dhaka',
            'shipping_cost' => 60.00,
            'subtotal' => 1000.00,
            'discount_amount' => 0.00,
            'total_amount' => 1060.00,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'unit_purchase_price' => $product->purchase_price,
            'unit_price' => $product->regular_price,
            'quantity' => 1,
            'subtotal' => 1000.00,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => null,
            'to_status' => 'pending',
            'remarks' => 'Order placed via COD',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cod',
            'amount' => 1060.00,
            'currency' => 'BDT',
            'status' => 'pending',
        ]);

        // Modifying the original product must NOT affect the historical order item
        $product->update([
            'name' => 'Renamed Product Later',
            'regular_price' => 1500.00,
        ]);

        $freshItem = $order->fresh()->items->first();
        $this->assertEquals('Original Product Name', $freshItem->product_name);
        $this->assertEquals(1000.00, (float) $freshItem->unit_price);
        $this->assertCount(1, $order->statusHistories);
        $this->assertNotNull($order->payment);
    }

    /**
     * Test Settings, ActivityLog, InventoryMovement, and Reviews.
     */
    public function test_settings_audit_logs_and_inventory_movements(): void
    {
        // Settings
        Setting::set('test_key', 'test_value', 'general', 'string', 'Test setting');
        $this->assertEquals('test_value', Setting::get('test_key'));

        // ActivityLog
        $log = ActivityLog::record('test.action', null, ['foo' => 'bar']);
        $this->assertEquals('test.action', $log->action);
        $this->assertEquals(['foo' => 'bar'], $log->details);

        // InventoryMovement
        $product = Product::factory()->create(['stock_quantity' => 20]);
        $movement = InventoryMovement::create([
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity_change' => 5,
            'previous_stock' => 20,
            'current_stock' => 25,
            'remarks' => 'Inventory recount test',
        ]);
        $this->assertEquals(5, $movement->quantity_change);

        // Review
        $customer = User::factory()->create();
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 5,
            'is_approved' => true,
        ]);
        $this->assertCount(1, $product->approvedReviews);
    }
}
