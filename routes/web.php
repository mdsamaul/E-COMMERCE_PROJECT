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
use App\Http\Controllers\Customer\ProfileController;
use Illuminate\Support\Facades\Route;

/* -----------------------------------------------------------------------------
 | Public Routes
 | ----------------------------------------------------------------------------- */
Route::get('/', function () {
    return view('welcome');
})->name('home');

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

