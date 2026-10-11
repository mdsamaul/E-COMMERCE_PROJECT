<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        Storage::fake('public');
    }

    /* -------------------------------------------------------------------------
     | Authorization Tests
     | ------------------------------------------------------------------------- */

    public function test_customer_cannot_access_coupons_banners_or_settings(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.coupons.index'));
        $response->assertForbidden();

        $response = $this->actingAs($this->customer)->get(route('admin.banners.index'));
        $response->assertForbidden();

        $response = $this->actingAs($this->customer)->get(route('admin.settings.index'));
        $response->assertForbidden();

        $response = $this->actingAs($this->customer)->get(route('admin.customers.index'));
        $response->assertForbidden();

        $response = $this->actingAs($this->customer)->get(route('admin.reports.sales'));
        $response->assertForbidden();
    }

    /* -------------------------------------------------------------------------
     | Coupon Management Tests
     | ------------------------------------------------------------------------- */

    public function test_admin_can_create_coupon_with_uppercase_normalization(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.coupons.store'), [
            'code' => 'eid2026', // lowercase input
            'type' => 'percentage',
            'value' => 15,
            'min_order_amount' => 1000,
            'max_discount_amount' => 500,
            'usage_limit_per_coupon' => 100,
            'usage_limit_per_user' => 1,
            'starts_at' => Carbon::now()->toDateTimeString(),
            'expires_at' => Carbon::now()->addDays(7)->toDateTimeString(),
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.coupons.index'))
            ->assertSessionHas('success');

        // Verified that code was converted to uppercase
        $this->assertDatabaseHas('coupons', [
            'code' => 'EID2026',
            'type' => 'percentage',
            'value' => 15.00,
        ]);
    }

    public function test_duplicate_coupon_code_fails_validation(): void
    {
        Coupon::factory()->create(['code' => 'SAVE100']);

        $response = $this->actingAs($this->admin)->post(route('admin.coupons.store'), [
            'code' => 'SAVE100',
            'type' => 'fixed',
            'value' => 100,
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_admin_can_delete_coupon(): void
    {
        $coupon = Coupon::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.coupons.destroy', $coupon));
        $response->assertRedirect(route('admin.coupons.index'));

        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }

    /* -------------------------------------------------------------------------
     | Banner Management Tests
     | ------------------------------------------------------------------------- */

    public function test_admin_can_create_banner_with_graphic_upload(): void
    {
        $file = UploadedFile::fake()->image('hero_banner.jpg', 1920, 600);

        $response = $this->actingAs($this->admin)->post(route('admin.banners.store'), [
            'title' => 'Mega Winter Clearance',
            'subtitle' => 'Save up to 60% on jackets and sweaters',
            'placement' => 'hero_slider',
            'sort_order' => 1,
            'button_text' => 'Shop Winter',
            'button_url' => '/category/winter',
            'image' => $file,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.banners.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('banners', [
            'title' => 'Mega Winter Clearance',
            'placement' => 'hero_slider',
            'button_text' => 'Shop Winter',
        ]);

        $banner = Banner::where('title', 'Mega Winter Clearance')->first();
        Storage::disk('public')->assertExists($banner->image_path);
    }

    public function test_admin_can_delete_banner_and_clean_up_storage(): void
    {
        $file = UploadedFile::fake()->image('banner_del.jpg');
        $path = $file->store('banners', 'public');

        $banner = Banner::factory()->create(['image_path' => $path]);
        Storage::disk('public')->assertExists($path);

        $response = $this->actingAs($this->admin)->delete(route('admin.banners.destroy', $banner));
        $response->assertRedirect(route('admin.banners.index'));

        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
        Storage::disk('public')->assertMissing($path);
    }

    /* -------------------------------------------------------------------------
     | Customer Directory Tests
     | ------------------------------------------------------------------------- */

    public function test_admin_can_list_and_search_customers(): void
    {
        $customer1 = User::factory()->create(['name' => 'Tanvir Hasan', 'role' => 'customer']);
        $customer2 = User::factory()->create(['name' => 'Farhana Akter', 'role' => 'customer']);

        $response = $this->actingAs($this->admin)->get(route('admin.customers.index', ['search' => 'Tanvir']));
        $response->assertOk()
            ->assertSee('Tanvir Hasan')
            ->assertDontSee('Farhana Akter');
    }

    public function test_admin_can_view_customer_profile_safely(): void
    {
        $customer = User::factory()->create([
            'name' => 'Rakib Sheikh',
            'email' => 'rakib@example.com',
            'phone' => '01712345678',
            'role' => 'customer',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.customers.show', $customer));
        $response->assertOk()
            ->assertViewIs('admin.customers.show')
            ->assertSee('Rakib Sheikh')
            ->assertSee('01712345678')
            ->assertDontSee($customer->password); // Password should never appear in view
    }

    public function test_admin_can_toggle_customer_status(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        // Suspend
        $response = $this->actingAs($this->admin)->post(route('admin.customers.toggle-status', $customer));
        $response->assertSessionHas('success');
        $this->assertFalse($customer->fresh()->is_active);

        // Reactivate
        $response = $this->actingAs($this->admin)->post(route('admin.customers.toggle-status', $customer));
        $response->assertSessionHas('success');
        $this->assertTrue($customer->fresh()->is_active);
    }

    /* -------------------------------------------------------------------------
     | Review Moderation Tests
     | ------------------------------------------------------------------------- */

    public function test_admin_can_approve_reject_and_delete_reviews(): void
    {
        $product = Product::factory()->create();
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'user_id' => $this->customer->id,
            'rating' => 5,
            'comment' => 'Excellent fabric quality!',
            'is_approved' => false,
        ]);

        // Approve review
        $response = $this->actingAs($this->admin)->post(route('admin.reviews.approve', $review));
        $response->assertSessionHas('success');
        $this->assertTrue($review->fresh()->is_approved);

        // Reject review
        $response = $this->actingAs($this->admin)->post(route('admin.reviews.reject', $review));
        $response->assertSessionHas('success');
        $this->assertFalse($review->fresh()->is_approved);

        // Delete review
        $response = $this->actingAs($this->admin)->delete(route('admin.reviews.destroy', $review));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    /* -------------------------------------------------------------------------
     | Website & Delivery Settings Tests
     | ------------------------------------------------------------------------- */

    public function test_admin_can_update_website_and_delivery_settings(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'store_name' => 'ShopBD Official',
            'store_tagline' => 'Bangladesh #1 Trusted Online Shop',
            'store_phone' => '01711112233',
            'store_email' => 'contact@shopbd.com',
            'store_address' => 'Gulshan 2, Dhaka-1212',
            'inside_dhaka_delivery_fee' => 70.00,
            'outside_dhaka_delivery_fee' => 130.00,
            'free_shipping_threshold' => 3000.00,
            'currency_symbol' => '৳',
        ]);

        $response->assertRedirect(route('admin.settings.index'))
            ->assertSessionHas('success');

        $this->assertEquals('ShopBD Official', Setting::get('store_name'));
        $this->assertEquals(70.00, Setting::get('inside_dhaka_delivery_fee'));
        $this->assertEquals(130.00, Setting::get('outside_dhaka_delivery_fee'));
        $this->assertEquals(3000.00, Setting::get('free_shipping_threshold'));
    }

    /* -------------------------------------------------------------------------
     | Sales Reports Tests
     | ------------------------------------------------------------------------- */

    public function test_admin_can_view_filtered_sales_report(): void
    {
        // Create orders
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'total_amount' => 2500.00,
            'payment_status' => 'paid',
            'order_status' => 'delivered',
            'created_at' => Carbon::now()->subDays(2),
        ]);

        Order::factory()->create([
            'user_id' => $this->customer->id,
            'total_amount' => 1500.00,
            'payment_status' => 'paid',
            'order_status' => 'delivered',
            'created_at' => Carbon::now()->subDays(3),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.sales', [
            'start_date' => Carbon::now()->subDays(7)->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
        ]));

        $response->assertOk()
            ->assertViewIs('admin.reports.sales')
            ->assertSee('4,000.00'); // Sum of 2500 + 1500
    }
}
