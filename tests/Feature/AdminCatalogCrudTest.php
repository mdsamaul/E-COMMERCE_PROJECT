<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCatalogCrudTest extends TestCase
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

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get(route('admin.categories.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_regular_customer_is_forbidden_from_admin_catalog(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.categories.index'));
        $response->assertForbidden();

        $response = $this->actingAs($this->customer)->get(route('admin.products.index'));
        $response->assertForbidden();

        $response = $this->actingAs($this->customer)->get(route('admin.brands.index'));
        $response->assertForbidden();
    }

    /* -------------------------------------------------------------------------
     | Category CRUD Tests
     | ------------------------------------------------------------------------- */

    public function test_admin_can_view_category_index_and_create_pages(): void
    {
        $category = Category::factory()->create(['name' => 'Men Fashion']);

        $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));
        $response->assertOk()
            ->assertViewIs('admin.categories.index')
            ->assertSee('Men Fashion');

        $response = $this->actingAs($this->admin)->get(route('admin.categories.create'));
        $response->assertOk()
            ->assertViewIs('admin.categories.create');
    }

    public function test_admin_can_create_category_with_image_upload(): void
    {
        $file = UploadedFile::fake()->image('clothing.jpg', 400, 400);

        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Traditional Panjabi',
            'slug' => 'traditional-panjabi',
            'description' => 'Fine handcrafted attire',
            'image' => $file,
            'is_featured' => '1',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Traditional Panjabi',
            'slug' => 'traditional-panjabi',
            'is_featured' => true,
            'is_active' => true,
        ]);

        $category = Category::where('slug', 'traditional-panjabi')->first();
        $this->assertNotNull($category->image);
        Storage::disk('public')->assertExists($category->image);
    }

    public function test_admin_can_update_category(): void
    {
        $category = Category::factory()->create(['name' => 'Old Electronics']);

        $response = $this->actingAs($this->admin)->get(route('admin.categories.edit', $category));
        $response->assertOk()
            ->assertViewIs('admin.categories.edit')
            ->assertSee('Old Electronics');

        $response = $this->actingAs($this->admin)->put(route('admin.categories.update', $category), [
            'name' => 'Consumer Electronics',
            'slug' => 'consumer-electronics',
            'is_featured' => '1',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Consumer Electronics',
            'slug' => 'consumer-electronics',
        ]);
    }

    public function test_admin_cannot_delete_category_with_assigned_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_delete_empty_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));
        $response->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /* -------------------------------------------------------------------------
     | Brand CRUD Tests
     | ------------------------------------------------------------------------- */

    public function test_admin_can_create_and_update_brand(): void
    {
        $logo = UploadedFile::fake()->image('brand_logo.png', 200, 200);

        $response = $this->actingAs($this->admin)->post(route('admin.brands.store'), [
            'name' => 'Apex Footwear',
            'slug' => 'apex-footwear',
            'description' => 'Leading footwear brand in BD',
            'logo' => $logo,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.brands.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('brands', [
            'name' => 'Apex Footwear',
            'slug' => 'apex-footwear',
        ]);

        $brand = Brand::where('slug', 'apex-footwear')->first();
        Storage::disk('public')->assertExists($brand->logo);

        // Update brand
        $response = $this->actingAs($this->admin)->put(route('admin.brands.update', $brand), [
            'name' => 'Apex Footwear Ltd',
            'slug' => 'apex-footwear-ltd',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.brands.index'));
        $this->assertDatabaseHas('brands', ['name' => 'Apex Footwear Ltd']);
    }

    /* -------------------------------------------------------------------------
     | Product CRUD & Inventory Tests
     | ------------------------------------------------------------------------- */

    public function test_admin_can_create_product_with_images_and_initial_inventory_log(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $image1 = UploadedFile::fake()->image('front.jpg', 600, 600);
        $image2 = UploadedFile::fake()->image('back.jpg', 600, 600);

        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Jamdani Saree Exclusive',
            'slug' => 'jamdani-saree-exclusive',
            'sku' => 'JAM-EXCL-01',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'purchase_price' => 3500.00,
            'regular_price' => 5500.00,
            'sale_price' => 4950.00,
            'stock_quantity' => 15,
            'low_stock_threshold' => 3,
            'short_description' => 'Traditional handcrafted Jamdani saree',
            'description' => 'Authentic cotton thread work from Sonargaon artisans.',
            'images' => [$image1, $image2],
            'is_featured' => '1',
            'is_active' => '1',
            'stock_remarks' => 'Initial inventory batch from Narayanganj weavers',
        ]);

        $response->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Jamdani Saree Exclusive',
            'sku' => 'JAM-EXCL-01',
            'regular_price' => 5500.00,
            'sale_price' => 4950.00,
            'stock_quantity' => 15,
        ]);

        $product = Product::where('sku', 'JAM-EXCL-01')->first();
        $this->assertEquals(2, $product->images()->count());
        $this->assertTrue($product->primaryImage()->exists());

        // Verify InventoryMovement recorded
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'purchase',
            'quantity_change' => 15,
            'previous_stock' => 0,
            'current_stock' => 15,
            'created_by_user_id' => $this->admin->id,
        ]);
    }

    public function test_duplicate_sku_validation_is_enforced(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['sku' => 'UNIQUE-SKU-99', 'category_id' => $category->id]);

        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Another Product',
            'sku' => 'UNIQUE-SKU-99', // Duplicate SKU
            'category_id' => $category->id,
            'regular_price' => 1200.00,
            'stock_quantity' => 10,
        ]);

        $response->assertSessionHasErrors('sku');
    }

    public function test_admin_can_adjust_stock_with_audit_trail(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 10,
        ]);

        // Add 5 units
        $response = $this->actingAs($this->admin)->post(route('admin.products.stock.adjust', $product), [
            'adjustment_type' => 'add',
            'quantity' => 5,
            'remarks' => 'Supplier restock received',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(15, $product->fresh()->stock_quantity);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity_change' => 5,
            'previous_stock' => 10,
            'current_stock' => 15,
            'remarks' => 'Supplier restock received',
        ]);
    }

    public function test_admin_can_manage_product_images(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $img1 = ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/sample1.jpg',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $img2 = ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/sample2.jpg',
            'is_primary' => false,
            'sort_order' => 1,
        ]);

        // Change primary image to img2
        $response = $this->actingAs($this->admin)->post(route('admin.products.images.primary', [$product, $img2]));
        $response->assertSessionHas('success');

        $this->assertFalse($img1->fresh()->is_primary);
        $this->assertTrue($img2->fresh()->is_primary);

        // Delete img1
        $response = $this->actingAs($this->admin)->delete(route('admin.products.images.delete', [$product, $img1]));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('product_images', ['id' => $img1->id]);
    }

    public function test_admin_can_soft_delete_product(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.products.destroy', $product));
        $response->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
