<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\WishlistController as CustomerWishlistController;
use App\Http\Controllers\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductCatalogController;
use Illuminate\Support\Facades\Route;

/* -----------------------------------------------------------------------------
 | Public Storefront Routes
 | ----------------------------------------------------------------------------- */
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductCatalogController::class, 'index'])->name('products.index');
Route::get('/product/{slug}', [ProductCatalogController::class, 'show'])->name('products.show');
Route::get('/category/{slug}', [ProductCatalogController::class, 'category'])->name('products.category');
Route::get('/brand/{slug}', [ProductCatalogController::class, 'brand'])->name('products.brand');

/* -----------------------------------------------------------------------------
 | Shopping Cart Routes (Guest & Authenticated)
 | ----------------------------------------------------------------------------- */
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/', [CartController::class, 'store'])->name('add');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
    Route::post('/coupon', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::delete('/coupon', [CartController::class, 'removeCoupon'])->name('coupon.remove');
    Route::get('/count', [CartController::class, 'count'])->name('count');
    Route::put('/{id}', [CartController::class, 'update'])->whereNumber('id')->name('update');
    Route::delete('/{id}', [CartController::class, 'destroy'])->whereNumber('id')->name('destroy');
});

/* -----------------------------------------------------------------------------
 | Checkout Routes (Guest & Authenticated)
 | ----------------------------------------------------------------------------- */
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/calculate', [CheckoutController::class, 'calculate'])->name('calculate');
    Route::post('/', [CheckoutController::class, 'store'])->name('store');
});
Route::get('/order/confirmation/{order_number}', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');

// Informative Pages
Route::get('/about-us', [PageController::class, 'about'])->name('pages.about');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact', [PageController::class, 'submitContact'])->name('pages.contact.submit');
Route::get('/faq', [PageController::class, 'faq'])->name('pages.faq');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/terms-and-conditions', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/shipping-policy', [PageController::class, 'shippingPolicy'])->name('pages.shipping');
Route::get('/return-policy', [PageController::class, 'returnPolicy'])->name('pages.return');

/* -----------------------------------------------------------------------------
 | Customer Guest Authentication Routes
 | ----------------------------------------------------------------------------- */
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

/* -----------------------------------------------------------------------------
 | Customer Authenticated Account Routes
 | ----------------------------------------------------------------------------- */
Route::middleware(['auth', 'customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::post('/addresses', [ProfileController::class, 'storeAddress'])->name('address.store');
    Route::delete('/addresses/{address}', [ProfileController::class, 'deleteAddress'])->name('address.delete');

    // Customer Orders & Invoices
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{orderNumber}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{orderNumber}/invoice', [CustomerOrderController::class, 'invoice'])->name('orders.invoice');
    Route::post('/orders/{orderNumber}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');

    // Customer Wishlist
    Route::get('/wishlist', [CustomerWishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [CustomerWishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{productId}', [CustomerWishlistController::class, 'destroy'])->whereNumber('productId')->name('wishlist.destroy');
    Route::post('/wishlist/{productId}/move-to-cart', [CustomerWishlistController::class, 'moveToCart'])->whereNumber('productId')->name('wishlist.move-to-cart');

    // Customer Reviews
    Route::post('/reviews', [CustomerReviewController::class, 'store'])->name('reviews.store');
});

// Logout for authenticated users
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/* -----------------------------------------------------------------------------
 | Admin Routes (/admin)
 | ----------------------------------------------------------------------------- */
Route::prefix('admin')->name('admin.')->group(function () {
    // Admin Guest Routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    });

    // Admin Authenticated & Authorized Routes
    Route::middleware('admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Orders Management
        Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::post('orders/{order}/payment', [AdminOrderController::class, 'updatePayment'])->name('orders.update-payment');
        Route::get('orders/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');

        // Categories CRUD
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Brands CRUD
        Route::resource('brands', BrandController::class)->except(['show']);

        // Products & Stock Management
        Route::get('products/stock/overview', [ProductController::class, 'stockIndex'])->name('products.stock');
        Route::post('products/{product}/stock/adjust', [ProductController::class, 'adjustStock'])->name('products.stock.adjust');
        Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage'])->name('products.images.delete');
        Route::post('products/{product}/images/{image}/primary', [ProductController::class, 'setPrimaryImage'])->name('products.images.primary');
        Route::resource('products', ProductController::class);

        // Coupons CRUD
        Route::resource('coupons', CouponController::class)->except(['show']);

        // Banners CRUD
        Route::resource('banners', BannerController::class)->except(['show']);

        // Customers Directory
        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::post('customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');

        // Review Moderation
        Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::post('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
        Route::post('reviews/{review}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

        // Website & Delivery Settings
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');

        // Sales Reports
        Route::get('reports/sales', [SalesReportController::class, 'index'])->name('reports.sales');
    });
});

