<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerStorefrontTest extends TestCase
{
    use RefreshDatabase;

    /* -------------------------------------------------------------------------
     | Homepage Tests
     | ------------------------------------------------------------------------- */

    public function test_homepage_renders_successfully_with_banners_and_products(): void
    {
        $banner = Banner::factory()->create([
            'title' => 'Festive Eid Collection',
            'placement' => 'hero_slider',
            'is_active' => true,
        ]);

        $category = Category::factory()->create([
            'name' => 'Men Ethnic Wear',
            'is_featured' => true,
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Royal Silk Panjabi',
            'regular_price' => 2500.00,
            'sale_price' => 2200.00,
            'is_featured' => true,
            'is_active' => true,
            'stock_quantity' => 10,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Festive Eid Collection')
            ->assertSee('Men Ethnic Wear')
            ->assertSee('Royal Silk Panjabi')
            ->assertSee('2,200.00');
    }

    public function test_inactive_products_are_hidden_from_storefront(): void
    {
        $category = Category::factory()->create(['is_active' => true]);

        $inactiveProduct = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Unpublished Secret Product',
            'slug' => 'unpublished-secret-product',
            'is_active' => false, // Inactive / Draft
        ]);

        // Home should not see it
        $this->get(route('home'))->assertDontSee('Unpublished Secret Product');

        // Catalog should not see it
        $this->get(route('products.index'))->assertDontSee('Unpublished Secret Product');

        // Detail page should return 404
        $this->get(route('products.show', $inactiveProduct->slug))->assertNotFound();
    }

    /* -------------------------------------------------------------------------
     | Catalog Search, Filters & Sorting Tests
     | ------------------------------------------------------------------------- */

    public function test_product_catalog_keyword_search(): void
    {
        $category = Category::factory()->create();

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Cotton Polo Shirt Navy',
            'sku' => 'POLO-NAVY-01',
            'is_active' => true,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Leather Formal Shoes Black',
            'sku' => 'SHOE-BLK-02',
            'is_active' => true,
        ]);

        $response = $this->get(route('products.index', ['q' => 'Polo']));
        $response->assertOk()
            ->assertSee('Cotton Polo Shirt Navy')
            ->assertDontSee('Leather Formal Shoes Black');

        $responseBySku = $this->get(route('products.index', ['q' => 'SHOE-BLK']));
        $responseBySku->assertOk()
            ->assertSee('Leather Formal Shoes Black')
            ->assertDontSee('Cotton Polo Shirt Navy');
    }

    public function test_product_catalog_filters_by_category_and_brand(): void
    {
        $cat1 = Category::factory()->create(['name' => 'Electronics', 'slug' => 'electronics']);
        $cat2 = Category::factory()->create(['name' => 'Groceries', 'slug' => 'groceries']);
        $brand = Brand::factory()->create(['name' => 'Samsung', 'slug' => 'samsung']);

        $p1 = Product::factory()->create([
            'category_id' => $cat1->id,
            'brand_id' => $brand->id,
            'name' => 'Samsung Galaxy Buds',
            'is_active' => true,
        ]);

        $p2 = Product::factory()->create([
            'category_id' => $cat2->id,
            'name' => 'Organic Mustard Oil',
            'is_active' => true,
        ]);

        // Filter by category
        $resCat = $this->get(route('products.index', ['category' => 'electronics']));
        $resCat->assertOk()
            ->assertSee('Samsung Galaxy Buds')
            ->assertDontSee('Organic Mustard Oil');

        // Filter by brand
        $resBrand = $this->get(route('products.index', ['brand' => 'samsung']));
        $resBrand->assertOk()
            ->assertSee('Samsung Galaxy Buds')
            ->assertDontSee('Organic Mustard Oil');
    }

    public function test_product_catalog_filters_by_price_and_in_stock(): void
    {
        $category = Category::factory()->create();

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Budget Earphones',
            'regular_price' => 300.00,
            'sale_price' => null,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Luxury Smartwatch',
            'regular_price' => 15000.00,
            'sale_price' => null,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Out of Stock Gadget',
            'regular_price' => 2000.00,
            'sale_price' => null,
            'stock_quantity' => 0,
            'is_active' => true,
        ]);

        // Price range 100 - 1000
        $resPrice = $this->get(route('products.index', ['min_price' => 100, 'max_price' => 1000]));
        $resPrice->assertOk()
            ->assertSee('Budget Earphones')
            ->assertDontSee('Luxury Smartwatch');

        // In-stock only
        $resStock = $this->get(route('products.index', ['in_stock' => '1']));
        $resStock->assertOk()
            ->assertDontSee('Out of Stock Gadget');
    }

    /* -------------------------------------------------------------------------
     | Product Details Tests
     | ------------------------------------------------------------------------- */

    public function test_product_detail_page_renders_with_gallery_and_reviews(): void
    {
        $category = Category::factory()->create(['name' => 'Home Appliances']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Electric Kettle 1.8L',
            'slug' => 'electric-kettle-1-8l',
            'sku' => 'KET-18L',
            'regular_price' => 1800.00,
            'sale_price' => 1500.00,
            'stock_quantity' => 8,
            'short_description' => 'Fast boiling stainless steel electric kettle.',
            'is_active' => true,
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/kettle.jpg',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $user = User::factory()->create(['name' => 'Sabbir Ahmed']);
        Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => 'Boils water very quickly, excellent build quality.',
            'is_approved' => true,
        ]);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertOk()
            ->assertSee('Electric Kettle 1.8L')
            ->assertSee('KET-18L')
            ->assertSee('1,500.00')
            ->assertSee('1,800.00')
            ->assertSee('Sabbir Ahmed')
            ->assertSee('Boils water very quickly');
    }

    /* -------------------------------------------------------------------------
     | Category and Brand Landing Pages
     | ------------------------------------------------------------------------- */

    public function test_category_page_displays_products_and_subcategories(): void
    {
        $parent = Category::factory()->create(['name' => 'Fashion', 'slug' => 'fashion', 'is_active' => true]);
        $sub = Category::factory()->create(['parent_id' => $parent->id, 'name' => 'Men Shoes', 'slug' => 'men-shoes', 'is_active' => true]);

        Product::factory()->create([
            'category_id' => $sub->id,
            'name' => 'Casual Loafer Shoes',
            'is_active' => true,
        ]);

        $response = $this->get(route('products.category', 'fashion'));
        $response->assertOk()
            ->assertSee('Fashion')
            ->assertSee('Men Shoes')
            ->assertSee('Casual Loafer Shoes');
    }

    public function test_brand_page_displays_brand_products(): void
    {
        $brand = Brand::factory()->create(['name' => 'Aarong', 'slug' => 'aarong', 'is_active' => true]);
        $category = Category::factory()->create();

        Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Aarong Handloom Kurti',
            'is_active' => true,
        ]);

        $response = $this->get(route('products.brand', 'aarong'));
        $response->assertOk()
            ->assertSee('Aarong')
            ->assertSee('Aarong Handloom Kurti');
    }

    /* -------------------------------------------------------------------------
     | Informative Static Pages Tests
     | ------------------------------------------------------------------------- */

    public function test_static_pages_render_properly(): void
    {
        $this->get(route('pages.about'))->assertOk()->assertSee('About Us');
        $this->get(route('pages.faq'))->assertOk()->assertSee('Frequently Asked Questions');
        $this->get(route('pages.privacy'))->assertOk()->assertSee('Privacy Policy');
        $this->get(route('pages.terms'))->assertOk()->assertSee('Terms and Conditions');
        $this->get(route('pages.shipping'))->assertOk()->assertSee('Shipping & Delivery Policy');
        $this->get(route('pages.return'))->assertOk()->assertSee('Return & Refund Policy');
        $this->get(route('pages.contact'))->assertOk()->assertSee('Contact Support');
    }

    public function test_contact_form_submission(): void
    {
        $response = $this->post(route('pages.contact.submit'), [
            'name' => 'Mehedi Hasan',
            'email' => 'mehedi@example.com',
            'phone' => '01812345678', // valid Bangladesh phone
            'subject' => 'Corporate Bulk Order Inquiry',
            'message' => 'We would like to order 50 gift panjabis for corporate Eid event.',
        ]);

        $response->assertSessionHas('success');
    }

    public function test_contact_form_fails_with_invalid_phone(): void
    {
        $response = $this->post(route('pages.contact.submit'), [
            'name' => 'Mehedi Hasan',
            'email' => 'mehedi@example.com',
            'phone' => '12345', // invalid phone
            'subject' => 'Test',
            'message' => 'Hello',
        ]);

        $response->assertSessionHasErrors('phone');
    }
}
