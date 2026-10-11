<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('inside_dhaka_delivery_fee', '60.00', 'shipping', 'number');
        Setting::set('outside_dhaka_delivery_fee', '120.00', 'shipping', 'number');
    }

    protected function createCustomer(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'customer',
            'phone' => '017' . rand(10000000, 99999999),
        ], $attributes));
    }

    protected function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@shopbd.com',
            'phone' => '01711111111',
        ]);
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
            'regular_price' => 1500.00,
            'sale_price' => 1200.00,
            'purchase_price' => 700.00,
            'stock_quantity' => 15,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ], $attributes));
    }

    /* -------------------------------------------------------------------------
     | 1. Authentication, Authorization & IDOR Security
     | ------------------------------------------------------------------------- */

    public function test_guest_cannot_access_admin_dashboard_and_is_redirected(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_regular_customer_is_forbidden_from_admin_dashboard(): void
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->get(route('admin.dashboard'));
        $response->assertForbidden();
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();
    }

    public function test_customer_cannot_view_or_manipulate_another_customers_order_idor(): void
    {
        $customerA = $this->createCustomer();
        $customerB = $this->createCustomer();

        $orderA = Order::create([
            'order_number' => 'ORD-TEST-AAA111',
            'user_id' => $customerA->id,
            'recipient_name' => $customerA->name,
            'phone' => $customerA->phone,
            'delivery_address' => 'Mirpur, Dhaka',
            'division' => 'Dhaka',
            'district' => 'Dhaka',
            'shipping_zone' => 'inside_dhaka',
            'subtotal' => 1200.00,
            'shipping_cost' => 60.00,
            'total_amount' => 1260.00,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
        ]);

        // Customer B attempts to view Customer A's order details
        $responseShow = $this->actingAs($customerB)->get(route('customer.orders.show', $orderA->order_number));
        $responseShow->assertForbidden();

        // Customer B attempts to view Customer A's invoice
        $responseInvoice = $this->actingAs($customerB)->get(route('customer.orders.invoice', $orderA->order_number));
        $responseInvoice->assertForbidden();

        // Customer B attempts to cancel Customer A's order
        $responseCancel = $this->actingAs($customerB)->post(route('customer.orders.cancel', $orderA->order_number));
        $responseCancel->assertForbidden();
    }

    public function test_registration_prevents_role_elevation_mass_assignment(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Malicious User',
            'email' => 'hacker@example.com',
            'phone' => '01799999999',
            'password' => 'Secret123456!',
            'password_confirmation' => 'Secret123456!',
            'role' => 'admin', // Attempted privilege escalation
        ]);

        $response->assertRedirect();

        $user = User::where('email', 'hacker@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('customer', $user->role); // Strictly customer
        $this->assertFalse($user->isAdmin());
    }

    public function test_profile_update_prevents_role_override(): void
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->put(route('customer.profile.update'), [
            'name' => 'Customer Name',
            'email' => $customer->email,
            'phone' => $customer->phone,
            'role' => 'admin', // Attempted injection
        ]);

        $response->assertRedirect();
        $customer->refresh();
        $this->assertEquals('customer', $customer->role);
    }

    public function test_product_file_upload_rejects_non_image_files(): void
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create();

        $fakeScript = UploadedFile::fake()->create('malicious.php', 10, 'application/x-php');

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Test Product',
            'sku' => 'SKU-UPLOAD-TEST',
            'category_id' => $category->id,
            'regular_price' => 500.00,
            'stock_quantity' => 10,
            'images' => [$fakeScript],
        ]);

        $response->assertSessionHasErrors(['images.0']);
    }

    /* -------------------------------------------------------------------------
     | 2. SEO, Sitemap, Robots & Structured Data
     | ------------------------------------------------------------------------- */

    public function test_sitemap_xml_returns_valid_xml_with_published_products(): void
    {
        $activeProduct = $this->createProduct(['name' => 'Active Panjabi']);
        $inactiveProduct = $this->createProduct(['name' => 'Secret Draft', 'is_active' => false]);

        $response = $this->get(route('seo.sitemap'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');

        $content = $response->getContent();
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringContainsString($activeProduct->slug, $content);
        $this->assertStringNotContainsString($inactiveProduct->slug, $content); // Hidden!
    }

    public function test_robots_txt_returns_proper_directives_and_sitemap(): void
    {
        $response = $this->get(route('seo.robots'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');

        $content = $response->getContent();
        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Disallow: /admin/', $content);
        $this->assertStringContainsString('Disallow: /checkout/', $content);
        $this->assertStringContainsString('Disallow: /customer/', $content);
        $this->assertStringContainsString('Sitemap: ' . url('/sitemap.xml'), $content);
    }

    public function test_homepage_renders_opengraph_and_json_ld_structured_data(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('<meta property="og:site_name" content="ShopBD">', false);
        $response->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
        $response->assertSee('"@type": "Organization"', false);
        $response->assertSee('"@type": "WebSite"', false);
        $response->assertSee('"@type": "SearchAction"', false);
    }

    public function test_product_detail_page_renders_product_json_ld_schema(): void
    {
        $product = $this->createProduct([
            'name' => 'Pure Silk Saree',
            'regular_price' => 5000.00,
            'sale_price' => 4500.00,
            'stock_quantity' => 12,
        ]);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertOk();
        $response->assertSee('"@type": "Product"', false);
        $response->assertSee('Pure Silk Saree', false);
        $response->assertSee('"priceCurrency": "BDT"', false);
        $response->assertSee('"price": "4500.00"', false);
        $response->assertSee('"availability": "https://schema.org/InStock"', false);
    }

    /* -------------------------------------------------------------------------
     | 3. Business Integrity: Cart Totals, Coupons & Stock Consistency
     | ------------------------------------------------------------------------- */

    public function test_product_visibility_strictly_hides_inactive_items(): void
    {
        $inactive = $this->createProduct(['is_active' => false]);

        $response = $this->get(route('products.show', $inactive->slug));
        $response->assertNotFound();
    }

    public function test_coupon_calculations_and_minimum_spend(): void
    {
        $coupon = Coupon::create([
            'code' => 'PROMO100',
            'type' => 'fixed',
            'value' => 100.00,
            'min_order_amount' => 500.00,
            'usage_limit_per_coupon' => 10,
            'is_active' => true,
        ]);

        $this->assertTrue($coupon->isValidForAmount(600.00));
        $this->assertFalse($coupon->isValidForAmount(400.00)); // Under min spend
        $this->assertEquals(100.00, $coupon->calculateDiscount(600.00));
    }

    public function test_stock_locking_and_inventory_movement_on_order_checkout(): void
    {
        $product = $this->createProduct(['stock_quantity' => 10]);

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $checkoutResponse = $this->post(route('checkout.store'), [
            'recipient_name' => 'Hasan Mahmud',
            'phone' => '01712345678',
            'delivery_address' => 'Road 11, Banani, Dhaka',
            'division' => 'Dhaka',
            'district' => 'Dhaka',
            'payment_method' => 'cod',
        ]);

        $checkoutResponse->assertRedirect();
        $checkoutResponse->assertSessionHasNoErrors();

        // Product stock must be atomically decremented from 10 to 8
        $product->refresh();
        $this->assertEquals(8, $product->stock_quantity);

        // Inventory movement audit must be recorded
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'sale',
            'previous_stock' => 10,
            'current_stock' => 8,
            'quantity_change' => -2,
        ]);
    }
}
