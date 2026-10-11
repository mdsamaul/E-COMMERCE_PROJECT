# ShopBD — Master Project Plan & Architecture Roadmap

**Project Name**: ShopBD  
**Business Model**: Single-Vendor Multi-Category E-Commerce Platform for Bangladesh  
**Platform Target**: Modern Web (Responsive: Mobile, Tablet, Desktop) & Affordable Shared Hosting Compatible  
**Last Updated**: October 11, 2026  

---

## 1. System Architecture Overview

### Architectural Philosophy
ShopBD adheres to a robust, clean, and maintainable Laravel MVC + Service Layer architecture. It prioritizes simplicity, security, and low hosting overhead without sacrificing modern UX and enterprise-grade software standards.

```
┌─────────────────────────────────────────────────────────────┐
│                       Client Layer                          │
│     Bootstrap 5 UI + Blade Templates + Responsive UX        │
│          (Optimized CSS/JS Bundles + SEO Meta)              │
└──────────────────────────────┬──────────────────────────────┘
                               │ HTTP / HTTPS
┌──────────────────────────────▼──────────────────────────────┐
│                    Routing & Middleware                     │
│  - Web Routes (Customer)       - Admin Routes (/admin)      │
│  - Rate Limiting (Throttle)    - CSRF Protection            │
│  - Session Management          - Role Authorization (Admin) │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                     Controller Layer                        │
│   Thin Controllers responsible only for Request/Response    │
│   - Customer: HomeController, ProductController, Cart...   │
│   - Admin: DashboardController, ProductManager...          │
└───────────────┬─────────────────────────────┬───────────────┘
                │ Form Requests (Validation)  │
┌───────────────▼─────────────────────────────▼───────────────┐
│                       Service Layer                         │
│   Core Business Logic & Transactions:                       │
│   - OrderService: Recalculates totals, locks inventory     │
│   - CartService: Guest session + Authenticated sync         │
│   - InventoryService: Prevents overselling & logs audit     │
│   - CouponService: Validates limits & discounts             │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                  Eloquent ORM & Data Layer                  │
│   - Normalized Models, Casting, Query Scopes, Relationships │
│   - MySQL 8.x / MariaDB (DECIMAL 12,2 for Currency)         │
│   - Transactions (DB::transaction) & Row Locks              │
└─────────────────────────────────────────────────────────────┘
```

### Architectural Principles
1. **Thin Controllers, Rich Services**: Business-critical workflows (e.g. checkout, stock deduction, coupon validation) reside in service classes with atomic database transactions.
2. **Never Trust the Client**: Cart calculations, prices, discounts, and inventory availability are recomputed strictly on the server during checkout.
3. **Historical Data Preservation**: Orders store product snapshots (title, SKU, purchase price, selling price) so historical accounting remains immutable even if products are edited or deleted.
4. **Bangladesh Native**: BDT (৳) currency handling, division/district-based shipping calculations (Inside Dhaka vs. Outside Dhaka), local phone number format validation (`+8801...` / `01...`), and Cash on Delivery (COD) as default payment.
5. **Dedicated Full-Page Creation Views (Strict No-Modal Rule for Major Forms)**:
   Per client requirement (*"amar kono boro create page modal a na diye full screen a create korio"*), all major entity creation and editing screens (Products, Categories, Brands, Coupons, Banners, Orders, Settings) MUST be designed as dedicated, full-width responsive pages (`create.blade.php`, `edit.blade.php`). Never use cramped modal dialogs for entity creation or multi-field forms. Modals are strictly reserved for minor confirmations (e.g. Delete Confirmation Dialog).

---

## 2. Technology Stack & Environment Confirmation

| Component | Verified Local Version | Production Requirement | Hosting Compatibility |
|---|---|---|---|
| **PHP** | 8.2.12 (CLI) | PHP >= 8.2.0 | Apache / LiteSpeed Shared Hosting (cPanel) |
| **Framework** | Laravel 11.57.0 | Laravel 11.x | Standard PHP hosting with public_html docroot |
| **Database** | MySQL / MariaDB (XAMPP installed) | MySQL 5.7+ / 8.0+ / MariaDB 10.3+ | Standard cPanel MySQL database |
| **Testing DB**| SQLite `:memory:` | Automated CI/CD & Local testing | Zero external dependency for test suite |
| **Frontend** | Bootstrap 5.3 + Bootstrap Icons | HTML5, CSS3, Bootstrap 5 | Zero runtime Node requirement on shared hosting |
| **Asset Pipeline**| Vite 6.4 + npm 10.9 | Precompiled static assets in `/public/build` | Deploy prebuilt assets directly to shared host |
| **Version Control**| Git 2.51.0 | Git + GitHub | Standard Git workflow |

---

## 3. Hosting Limitations & Solutions

Shared hosting environments have distinct constraints that ShopBD explicitly accounts for:
1. **No Node.js Runtime on Shared Host**: Production assets are precompiled locally via `npm run build` and committed/deployed into `public/build`.
2. **Single Document Root Configuration**: `.htaccess` in project root redirects traffic to `public/` or standard shared hosting directory structure (`public_html`).
3. **No Daemon / Supervisor Process**: Queue defaults to `sync` or database-driven queues runnable via cPanel cron jobs.
4. **Resource Constraints**: Optimized database indexing, eager loading (`with(...)`) to eliminate N+1 queries, and low memory consumption.
5. **Storage Symlinks**: `php artisan storage:link` handles media storage, with a fallback controller or shared-folder routing if symlinks are restricted on restrictive hosts.

---

## 4. Master Implementation Roadmap

### Phase 1: Project Foundation & Baseline Setup (Completed)
- [x] Workspace inspection and environment verification (PHP 8.2, Composer 2.8, Node 22, MySQL).
- [x] Laravel 11 project initialization in root directory.
- [x] Git repository initialization.
- [x] Base configuration: `ShopBD` application name, `Asia/Dhaka` timezone, BDT currency.
- [x] Dedicated config file: `config/shopbd.php`.
- [x] Frontend foundation: Bootstrap 5, Bootstrap Icons, custom design tokens.
- [x] Storage symlink verification (`storage:link`).
- [x] Test suite initialization (SQLite `:memory:` in `phpunit.xml`).
- [x] Master documentation: `PROJECT_PLAN.md`, `DATABASE_DESIGN.md`, `DEVELOPMENT_LOG.md`.

### Phase 2: Complete Database Architecture & Models (COMPLETED)
- [x] Create database migrations for all 21 core tables:
  - Users & Roles (`users`, `addresses`)
  - Catalog (`categories`, `brands`, `products`, `product_images`)
  - Shopping (`carts`, `cart_items`, `wishlists`, `wishlist_items`)
  - Checkout & Orders (`orders`, `order_items`, `order_status_histories`, `payments`)
  - Discounts & Promotions (`coupons`, `coupon_usages`, `banners`)
  - Engagement & Content (`reviews`, `settings`)
  - Auditing & Inventory (`inventory_movements`, `activity_logs`)
- [x] Eloquent models with relations, casts, fillable attributes, and soft deletes.
- [x] Database seeders with realistic demo data tailored for Bangladesh (Districts, categories, brands, sample products with BDT prices, admin user).
- [x] Automated test suite verifying models, relationships, and constraints (`DatabaseArchitectureTest.php`).

### Phase 3: Authentication, Authorization & Security Baseline (COMPLETED)
- [x] Customer authentication (Registration, Dual Login via Email/Mobile, Password Reset, Profile).
- [x] Admin authentication with secure route prefix (`/admin`) and dedicated server-side `AdminMiddleware`.
- [x] Bangladesh phone number validation rule and normalizer (`BangladeshPhone`).
- [x] Security hardening: Rate-limiting (`LoginRequest`), CSRF protection, session regeneration on login/logout.
- [x] Guest purchase auto-account provisioning service (`AuthService::findOrCreateFromPurchase`).
- [x] Safe initial admin creation artisan command (`shopbd:create-admin`) with interactive hidden prompts.
- [x] Automated test suite verifying customer auth, admin auth, guest access, authorization, validation, and rate limiting (`CustomerAuthTest.php`, `AdminAuthTest.php`).

### Phase 4: Complete Admin Dashboard (Completed)
- [x] Modern, professional Bootstrap 5 Admin layout with responsive sidebar navigation, sticky header, admin profile dropdown, and flash alerts (`resources/views/layouts/admin.blade.php`).
- [x] Strict No-Modal Rule enforced: All Create and Edit forms are dedicated, full-screen, responsive views.
- [x] Executive Dashboard Overview (`AdminDashboardController`):
  - Total sales in ৳ (from paid orders), Total orders, Pending orders, Total products, Total customers, Low stock count.
  - Recent orders overview and quick administrative shortcut cards.
- [x] Category CRUD (`CategoryController`):
  - Index with hierarchy, product count, featured/active badges, pagination.
  - Dedicated full-screen Create & Edit views (`create.blade.php`, `edit.blade.php`).
  - Slug generator, parent category selector, image upload, deletion safeguard against assigned products.
- [x] Brand CRUD (`BrandController`):
  - Index, dedicated full-screen Create & Edit views, logo upload, safe file replacement, deletion safeguard.
- [x] Product CRUD & Image Management (`ProductController`):
  - Index with search (name/SKU), category, brand, and stock level filters.
  - Dedicated full-screen Create & Edit views with comprehensive pricing (regular, sale, purchase), SKU uniqueness, multiple image uploads, and initial stock logging.
  - Image gallery manager: set primary image, delete non-primary images.
  - Product details view (`show.blade.php`) with inventory movement history audit trail.
  - Soft delete support.
- [x] Stock & Inventory Management (`ProductController@stockIndex`, `adjustStock`):
  - Dedicated full-screen stock overview (`stock.blade.php`) with low stock and out-of-stock quick filters.
  - Stock adjustment operations (+, -, set) with mandatory remarks.
  - Comprehensive `inventory_movements` table logging with audit trail (`created_by_user_id`).
- [x] Coupon Management (`CouponController`):
  - Index, dedicated full-screen Create & Edit views.
  - Uppercase code normalization, fixed & percentage discounts, min order amount, max discount cap, validity date ranges, usage limit per coupon and per user.
- [x] Banner Management (`BannerController`):
  - Index, dedicated full-screen Create & Edit views.
  - Hero slider and promotional promo card placements, image upload and safe storage cleanup.
- [x] Customer Directory (`CustomerController`):
  - Index with search (name, email, mobile) and account status filter.
  - Dedicated full-screen Customer profile (`show.blade.php`) displaying order history, lifetime spending, and delivery addresses without exposing private passwords or secrets.
  - Account status toggle (Active / Suspended).
- [x] Review Moderation (`ReviewController`):
  - Index with approval status and star rating filters.
  - Approve, unapprove, and delete customer product reviews.
- [x] Website & Delivery Settings (`SettingController`):
  - Dedicated full-screen settings dashboard (`settings/index.blade.php`).
  - Store identity (name, tagline, phone with Bangladesh phone validation, support email, office address).
  - Shipping fees (Inside Dhaka delivery charge, Outside Dhaka delivery charge, Free shipping threshold).
  - Currency symbol configuration (৳).
- [x] Date-Filtered Sales Reports (`SalesReportController`):
  - Filterable by start date, end date, order status, and payment status.
  - Period gross sales (BDT), total orders, delivered orders, average order value, total discount.
  - Payment method breakdown and top 5 selling products in period.
  - Paginated orders breakdown.
- [x] Security & Authorization:
  - Form Requests (`CategoryRequest`, `BrandRequest`, `ProductRequest`, `CouponRequest`, `BannerRequest`, `UpdateSettingsRequest`).
  - Server-side `AdminMiddleware` protects all admin routes with 403 Forbidden for non-admins.
  - Database transactions on multi-record database operations.
  - Safe file replacement and deletion on storage disks.
- [x] Automated Testing:
  - `AdminCatalogCrudTest.php` (13 tests)
  - `AdminManagementTest.php` (12 tests)
  - Full test suite passing (52 tests, 217 assertions).

### Phase 5: Complete Customer-Facing Storefront & Catalog (Completed)
- [x] Responsive Master Storefront Layout (`resources/views/layouts/app.blade.php`):
  - Top announcement bar with helpline, support email, BDT currency, and free shipping announcement.
  - Main header with store branding, interactive global search bar, user account dropdown, wishlist & cart indicators.
  - Category navigation bar with "All Categories" dropdown menu and quick navigation links.
  - Mobile responsive offcanvas drawer for smartphones and tablets.
  - Comprehensive footer with store bio, category links, customer support links, newsletter form, and Bangladeshi payment trust badges (bKash, Nagad, Rocket, Visa, COD).
- [x] Dynamic Homepage (`HomeController@index` & `frontend/home.blade.php`):
  - Dynamic hero slider carousel (`hero_slider` banners).
  - Trust value propositions (100% Authentic, Fast Delivery, Cash on Delivery, 7-Day Returns).
  - Featured categories showcase with product count.
  - Flash deals section (discounted items with sale badges).
  - Promotional grid card banners (`promo_card`).
  - Featured highlights and New arrivals product grids.
  - Trusted brand partners showcase.
- [x] Product Catalog, Search & Filter Engine (`ProductCatalogController@index` & `frontend/products/index.blade.php`):
  - Search by title, SKU, or description.
  - Category and Brand filtering with live product counters.
  - Price range filter (min ৳ to max ৳).
  - In-stock availability filter.
  - Sorting options: Newest, Price Low to High, Price High to Low, Popularity, Name A-Z.
  - Accessible pagination and helpful empty states.
- [x] Product Detail Page (`ProductCatalogController@show` & `frontend/products/show.blade.php`):
  - Dynamic breadcrumbs navigation.
  - Product image gallery with lightweight vanilla JavaScript image switcher.
  - Regular price vs. Sale price in ৳ with discount savings badge.
  - In stock / Low stock / Out of stock availability badges.
  - Interactive quantity selector (+ / -).
  - Delivery charge rates and timelines (Inside Dhaka ৳60 in 24-48h, Outside Dhaka ৳120 in 3-5 days).
  - Tabs for Full Description, Specifications, and Verified Customer Reviews.
  - Related products recommendation grid.
- [x] Category & Brand Browse Pages (`frontend/products/category.blade.php`, `brand.blade.php`):
  - Category header with child subcategory chips.
  - Brand header with official logo.
  - Full product grids with pagination.
- [x] Informative & Legal Pages (`PageController`):
  - About Us (`/about-us`), FAQ (`/faq`), Privacy Policy (`/privacy-policy`), Terms & Conditions (`/terms-and-conditions`), Shipping Policy (`/shipping-policy`), Return & Refund Policy (`/return-policy`).
  - Contact Us (`/contact`) with accessible message submission form and Bangladesh phone validation.
- [x] Business Integrity & SEO:
  - Strict publication status check: Only published products (`is_active = true`) shown to shoppers; unpublished items return 404.
  - Complete OpenGraph and SEO meta tags.
- [x] Automated Testing:
  - `tests/Feature/CustomerStorefrontTest.php` (11 tests).
  - Full test suite passing (63 tests, 273 assertions).

### Phase 6: Shopping Cart & Checkout System (Completed)
- [x] Dual-state cart architecture: Guest session-based cart token + authenticated persistent database cart.
- [x] Automatic cart merger upon customer login and registration (`CartService@mergeGuestCartToUser`).
- [x] Full CRUD cart operations: Add, update quantity, remove, and clear cart actions with strict server validation.
- [x] Server-side stock and availability validation:
  - Prevention of adding inactive products or out-of-stock items.
  - Strict enforcement of available stock quantities on add/update.
  - Automatic inventory synchronization on cart view.
- [x] Real-time cart calculations and live header badge with View Composer and AJAX polling endpoint (`/cart/count`).
- [x] Full-page responsive Shopping Cart view (`resources/views/frontend/cart/index.blade.php`, strict no-modal rule):
  - Line items with product thumbnail, name, SKU, unit price, quantity increment/decrement controls, subtotal, and remove button.
  - Empty cart state with shopping CTA.
  - Coupon code entry form with instant validation (min spend, expiry, usage count).
  - Estimated delivery charge and free shipping progress alert.
- [x] Full-page responsive Checkout Form (`resources/views/frontend/checkout/index.blade.php`, strict no-modal rule):
  - Recipient full name, Bangladesh mobile number (+880 validation), optional alternative phone, email.
  - Bangladesh 64-districts dropdown with real-time dynamic shipping fee recalculation:
    - Inside Dhaka City: ৳60.00
    - Outside Dhaka (Nationwide): ৳120.00
    - Free delivery for orders above threshold (৳2,500.00).
  - Detailed street/house delivery address and optional delivery instructions.
  - Order review preview with snapshot of items and financial breakdown.
- [x] Atomic Checkout Engine with Pessimistic Locking (`OrderService@processCheckout`):
  - Database transaction (`DB::transaction`) with `Product::lockForUpdate()` to prevent overselling race conditions.
  - Recalculates subtotal, discount, shipping, and total amount strictly on the server.
  - Idempotency token validation to prevent duplicate orders from rapid repeated clicks.
  - Auto-account provisioning for guest shoppers using mobile number (`AuthService::findOrCreateFromPurchase`) and automatic delivery address persistence.
  - Dedicated OrderItem snapshot persistence (capturing product name, SKU, purchase price, selling price, and quantity at purchase moment).
  - Automatic inventory deduction and audit logging to `inventory_movements` (type: 'sale').
  - Initial `OrderStatusHistory` logging (status: 'pending').
  - Cart clearing and coupon usage tracking.
  - Cash on Delivery (COD) initial implementation: Order marked as 'pending' and payment status as 'unpaid' (strictly not prepaid).
- [x] Full-page Order Confirmation & Receipt View (`resources/views/frontend/checkout/confirmation.blade.php`):
  - Unique order tracking number (`ORD-YYYYMMDD-XXXXXX`).
  - Delivery details, payment method, items summary, and printable receipt.
  - Guest auto-created account notice.
- [x] Automated Testing:
  - `tests/Feature/CartAndCheckoutTest.php` (13 comprehensive feature tests covering guest/user carts, stock races, coupon validation, delivery fee calculation, COD order creation, snapshots, auto-provisioning, and idempotency).
  - Full test suite passing (76 tests, 358 assertions).

### Phase 7: Customer Account, Order Tracking, Wishlist, Reviews & Admin Order Processing (COMPLETED)
- [x] Customer Order History & Tracking (`Customer\OrderController@index` & `customer/orders/index.blade.php`):
  - Paginated listing with search by order number and status filtering tabs.
  - Strict ownership validation: customers strictly view and access only their own orders (403 Forbidden on unauthorized access).
- [x] Customer Order Detail & Lifecycle Stepper (`Customer\OrderController@show` & `customer/orders/show.blade.php`):
  - Visual 5-stage fulfillment progress stepper (Placed -> Confirmed -> Processing -> Shipped -> Delivered).
  - Item snapshots with thumbnails, SKUs, unit prices, and quantities.
  - Delivery details, payment method, recipient phone and special instructions.
  - Order cancellation control for pending/confirmed orders prior to shipping.
  - Verified customer review submission form for delivered orders.
- [x] Customer Printable Invoice (`Customer\OrderController@invoice` & `customer/orders/invoice.blade.php`):
  - Clean, professional, printable A4 invoice layout with print media styling (`window.print()`).
  - Company letterhead, customer address, item breakdown, BDT totals, and returns policy notice.
- [x] Customer Wishlist Engine (`WishlistService`, `Customer\WishlistController` & `customer/wishlist/index.blade.php`):
  - Add to wishlist, remove from wishlist, and direct Move-to-Cart with stock validation.
  - Global view composer sharing live wishlist count in header.
  - Empty state with store navigation CTA.
- [x] Product Ratings & Reviews with Purchase Verification (`Customer\ReviewController`):
  - Documented strict purchase-verification policy: customer can only review a product if they possess an order with status `delivered` containing that product.
  - Mandatory admin moderation flag (`is_approved = false` on submission).
  - Verified Purchase badges on customer storefront reviews.
- [x] Server-Side Order State Machine & Safe Inventory Restocking (`OrderTransitionService` & `Order` model):
  - Allowed status transitions matrix strictly validated on the server (`pending` -> `confirmed` -> `processing` -> `shipped` -> `delivered`, with `cancelled` / `returned` branch).
  - Automated inventory restoration upon order cancellation or return.
  - Guard check (`hasRestockedInventory()`) strictly preventing duplicate inventory incrementation if cancellation/return is triggered multiple times.
  - Complete audit trail logged in `inventory_movements` (type `return` with previous and current stock counts).
- [x] Admin Order Processing & Fulfillment Dashboard (`Admin\OrderController`, `admin/orders/index.blade.php`, `admin/orders/show.blade.php`, `admin/orders/invoice.blade.php`):
  - Orders listing with quick metric tabs, search keyword, order status, payment status, shipping zone, and date filters.
  - Full-page order management panel (Strict No-Modal Rule):
    - Financial & Profit Margin calculations (COGS vs. Customer revenue vs. Gross Profit and Margin %).
    - Allowed next transitions select box with transition remarks.
    - Payment reconciliation form with audit logging in `payments` table.
    - Ordered items table with line item profit analysis.
    - Order status history timeline stepper with changer attribution and timestamps.
    - Full inventory audit trail for the order.
  - Dedicated admin printable warehouse packing slip and invoice (`admin/orders/invoice.blade.php`) with item verification checkboxes and packer signatures.
- [x] Extensible Notification Architecture:
  - Contract: `OrderNotificationInterface` defining `sendOrderPlaced` and `sendOrderStatusUpdated`.
  - Concrete drivers: `SmsNotificationService`, `EmailNotificationService`, and `OrderNotificationManager`.
  - Honest reporting: returns `['sent' => false, 'status' => 'not_configured']` when credentials are unset, never falsely claiming messages were dispatched.
- [x] Automated Testing:
  - `tests/Feature/CustomerOrderAndAccountTest.php` (11 feature tests covering history, detail ownership, invoice ownership, cancellation, safe restock, duplicate restock prevention, wishlist CRUD, and purchase-verified reviews).
  - `tests/Feature/AdminOrderManagementTest.php` (8 feature tests covering admin filters, state machine transition validation, illegal transition rejection, payment reconciler, printable invoice, and honest notification reporting).
  - 100% test pass rate (95 tests, 443 assertions).

### Phase 8: Optional Payment Gateways & Courier Integrations (Completed)
- [x] Standardized Contracts:
  - `PaymentGatewayInterface`: Standardizing `getName()`, `getDisplayName()`, `isConfigured()`, `initiatePayment()`, `verifyPayment()`, and `refundPayment()`.
  - `CourierServiceInterface`: Standardizing `getName()`, `getDisplayName()`, `isConfigured()`, `createConsignment()`, `trackConsignment()`, and `cancelConsignment()`.
- [x] Cash on Delivery (COD) Independence:
  - 100% operational without external network calls or merchant credentials.
- [x] Bangladesh Payment Gateways Architecture:
  - `SslCommerzPaymentGateway`: Official v4 session initiation (`gwprocess/v4/api.php`) and back-office order validation API (`validationserverAPI.php`).
  - `BkashPaymentGateway`: Official Tokenized Checkout v1.2.0-beta (`grantToken`, `create`, `execute`).
  - Zero card storage policy (PCI-DSS compliance): no card numbers or CVVs ever touch ShopBD servers.
  - Strict callback idempotency & validation: server-side validation of currency (`BDT`), exact amount matching, and duplicate payment prevention.
- [x] Bangladesh Courier Logistics Architecture:
  - `MockCourierService`: Deterministic consignment IDs and `TRK-BD-XXXX` tracking codes for testing without live API keys.
  - `SteadfastCourierService`: Official Steadfast `/create_order` and `/status_by_invoice` API integration.
  - `PathaoCourierService`: Official Pathao Aladdin OAuth2 and `/orders` API integration.
  - `CourierManager`: Handles automated consignment creation, stores tracking codes, auto-advances order state from `processing` to `shipped`, and manages live tracking status refreshes.
- [x] Admin & Customer UI Enhancements:
  - Admin Order Details: dedicated Courier Logistics card with provider selector and live tracking refresh, plus recorded payments audit box (Strict No-Modal Rule).
  - Customer Order Details: displays courier partner name, tracking badge, and live delivery status.
  - Checkout UI: dynamically renders Cash on Delivery alongside configured online gateways or unconfigured notice.
- [x] Documentation & Test Suite:
  - `docs/PAYMENT_AND_COURIER_INTEGRATION_PLAN.md`: Comprehensive official specification, security matrix, environment variable setup, and testing guide.
  - `tests/Feature/PaymentAndCourierIntegrationTest.php`: 14 automated feature tests verifying gateways, callbacks, mock dispatch, admin/customer UI, and idempotency.
  - 100% overall test pass rate (109 tests, 514 assertions).

### Phase 9: Production-Readiness Review, SEO & Performance Hardening (Completed)
- [x] Comprehensive Security Audit:
  - Authentication & Authorization: Multi-tier role authorization (`AdminMiddleware`, `auth` middleware), zero-trust access control.
  - IDOR Protection: Strict ownership validation on orders, addresses, wishlists, and reviews (403 Forbidden on foreign entity access).
  - CSRF Protection: Enabled globally across web routes; external payment gateway webhooks exempted securely in `bootstrap/app.php`.
  - Mass Assignment & Role Escalation: Guarded fillables on `User`, `Order`, `Payment`, `Review`; profile updates and registration forms strictly forbid role alteration.
  - Input Validation: Dedicated Form Requests with regex and Bangladesh telephone formatting across all mutations.
  - Safe File Uploads: Image MIME type enforcement (`image/jpeg,png,webp`), size limits, randomized hash naming, and safe unlink on replacement.
  - SQL Query Safety & XSS: 100% PDO parameterized queries via Eloquent; Blade auto-escaping `{{ }}` on all user inputs.
  - Rate Limiting & Throttling: `throttle:10,1` on checkout order submission; `throttle:5,1` on contact forms; `throttle:6,1` on authentication attempts.
  - Sensitive Log Scrubbing: Sensitive payment credentials and payload keys (`card_no`, `cvv`, `pin`, `password`, `app_secret`, `store_passwd`) masked and excluded from application logs.
- [x] Database & Performance Optimization:
  - Added compound database indexes (`2026_10_11_000018_add_performance_and_production_indexes.php`):
    - `products`: `(is_active, category_id)`, `(is_active, brand_id)`, `(is_active, is_featured)`, `(is_active, created_at)`
    - `orders`: `(user_id, created_at)`, `(order_status, created_at)`
    - `reviews`: `(product_id, is_approved)`
    - `categories`: `(is_active, parent_id)`
  - Query Optimization: Eager loading on catalog and order relationships (`with(['category', 'brand', 'images'])`, `with(['items.product', 'statusHistories', 'payments'])`) to eliminate N+1 queries.
  - Image Performance: Native browser lazy-loading (`loading="lazy"`) and responsive image attributes on all catalog and slider views.
- [x] SEO & Social Media Metadata:
  - Dynamic `sitemap.xml` (`SeoController@sitemap` & `seo/sitemap.blade.php`) outputting valid XML with priorities and change frequencies for storefront pages, categories, brands, and published products.
  - Accessible `robots.txt` (`SeoController@robots`) disallowing sensitive administrative and account pathways (`/admin`, `/checkout`, `/customer`) while pointing web crawlers to `sitemap.xml`.
  - Canonical URLs and OpenGraph / Twitter Card meta tags implemented in master layout `resources/views/layouts/app.blade.php`.
  - Schema.org JSON-LD Structured Data:
    - `Organization` & `WebSite` (with `SearchAction`) on Homepage.
    - `Product` (with `Offer`, `Brand`, `AggregateRating`, `InStock` status in `BDT`) on Product Detail Pages.
- [x] Automated Testing Suite:
  - `tests/Feature/ProductionReadinessTest.php` (14 feature tests, 50 assertions).
  - 100% test pass rate across entire application: **123 passed tests out of 123 (564 assertions), 0 failures**.

### Phase 10: Shared Hosting Deployment Package & cPanel Guide (Completed)
- [x] Shared Hosting Architecture & Web Root Hardening:
  - Fortified root `.htaccess` strictly preventing access to `.env`, `.git`, `storage/logs/`, and application source code, silently routing web requests to `public/`.
  - Updated `public/.htaccess` with HTTPS enforcement, security headers (`X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`, `Referrer-Policy`), and static asset cache expiration.
  - Documented Two-Directory Strategy (core in `/home/user/shopbd_core` outside web root, public assets in `public_html`).
- [x] Production Environment Configuration:
  - Created `.env.production.example` template optimized for Bangladesh cPanel shared hosting (prefixed database names, daily log rotation, secure session cookies, BDT currency, and disabled debug mode).
- [x] Automation & Maintenance Scripts:
  - `scripts/build-production-package.ps1`: Automated packaging script compiling Vite assets and generating a clean, lightweight upload archive without devDependencies or `.git`.
  - `scripts/deploy-cpanel.sh`: One-command cPanel Terminal/SSH deployment script setting permissions, optimizing Composer, migrating database, linking storage, and optimizing caches.
  - `scripts/backup-cpanel.sh`: Automated backup script creating compressed MySQL database dumps and media archives with 14-day automatic rotation.
- [x] Production Smoke Test & Diagnostics:
  - `ShopBdSmokeTestCommand` (`php artisan shopbd:smoke-test`): Automated 10-point deployment health check validating database connectivity, encryption keys, environment safety, SSL scheme, permissions, public storage symlink, admin account, product catalog, and web root isolation.
- [x] Comprehensive Documentation:
  - `docs/CPANEL_SHARED_HOSTING_DEPLOYMENT_GUIDE.md`: Step-by-step cPanel guide covering hosting prerequisites, domain DNS, database creation, upload methods, permissions, cron jobs, smoke tests, safe maintenance, and rollback plans.
- [x] Automated Testing:
  - `tests/Feature/SharedHostingDeploymentTest.php` (8 feature tests, 41 assertions).
  - 100% test pass rate across entire application: **141 passed tests out of 141 (721 assertions), 0 failures**.

---

## 5. Feature Status Checklist

| Feature | Category | Target Phase | Status |
|---|---|---|---|
| Laravel 11 Framework Setup | Infrastructure | Phase 1 | **Completed** |
| Bootstrap 5 + Assets Setup | Frontend | Phase 1 | **Completed** |
| Environment & BDT Config | Configuration | Phase 1 | **Completed** |
| SQLite `:memory:` Test Suite | QA / Testing | Phase 1 | **Completed** |
| Documentation Suite | Architecture | Phase 1 | **Completed** |
| Database Migrations (21 Tables) | Database | Phase 2 | **Completed** |
| Eloquent Models & Relations | Database | Phase 2 | **Completed** |
| Seeders (BD Districts, Demo Catalog) | Database | Phase 2 | **Completed** |
| Customer Auth (Login/Register) | Security | Phase 3 | **Completed** |
| Admin Auth & Role Middleware | Security | Phase 3 | **Completed** |
| Admin Catalog CRUD & Dashboard | Admin UI | Phase 4 | **Completed** |
| Responsive Navbar & Footer | Customer UI | Phase 5 | **Completed** |
| Homepage Hero & Product Grids | Customer UI | Phase 5 | **Completed** |
| Product Catalog & Filtering | Customer UI | Phase 5 | **Completed** |
| Product Details & Image Gallery | Customer UI | Phase 5 | **Completed** |
| Session + User Cart | Customer Logic | Phase 6 | **Completed** |
| Checkout & BD Address Form | Order Engine | Phase 6 | **Completed** |
| Dhaka / Outside Dhaka Shipping | Order Engine | Phase 6 | **Completed** |
| Cash On Delivery Payment | Order Engine | Phase 6 | **Completed** |
| Coupon & Discount Validation | Order Engine | Phase 6 | **Completed** |
| Customer Order History & Invoices | Customer Portal | Phase 7 | **Completed** |
| Customer Wishlist System | Customer Portal | Phase 7 | **Completed** |
| Purchase-Verified Reviews | Social Proof | Phase 7 | **Completed** |
| Order Status State Machine | Order Engine | Phase 7 | **Completed** |
| Safe Inventory Restock Audit | Inventory | Phase 7 | **Completed** |
| Admin Fulfillment & Margin Analytics | Admin Operations | Phase 7 | **Completed** |
| Notification Interfaces (SMS/Email) | Notifications | Phase 7 | **Completed** |
| Payment Gateway Interfaces & Manager | Integrations | Phase 8 | **Completed** |
| SSLCOMMERZ v4 & bKash Adapters | Integrations | Phase 8 | **Completed** |
| Callback Idempotency & Currency Checks | Security | Phase 8 | **Completed** |
| Courier Interfaces & Manager | Logistics | Phase 8 | **Completed** |
| Steadfast, Pathao & Mock Adapters | Logistics | Phase 8 | **Completed** |
| Admin Courier Dispatch & Tracking | Operations | Phase 8 | **Completed** |
| Customer Tracking Display | Customer Portal | Phase 8 | **Completed** |
| SEO Sitemap & Meta Tags | SEO & Marketing | Phase 9 | **Completed** |
| Production-Readiness & Security Hardening | Security & QA | Phase 9 | **Completed** |
| Shared Hosting Production Guide & Package | Deployment | Phase 10 | **Completed** |
