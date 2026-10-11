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

### Phase 1: Project Foundation & Baseline Setup (CURRENT)
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

### Phase 6: Shopping Cart & Wishlist System
- [ ] Dual-state cart: Guest session-based cart + authenticated persistent cart.
- [ ] Automatic cart merger upon customer login.
- [ ] Add, update quantity, remove, and clear cart actions with real-time feedback.
- [ ] Stock validation during cart manipulation.
- [ ] Customer wishlist management.

### Phase 7: Shopping Cart & Wishlist System
- [ ] Dual-state cart: Guest session-based cart + authenticated persistent cart.
- [ ] Automatic cart merger upon customer login.
- [ ] Add, update quantity, remove, and clear cart actions with real-time feedback.
- [ ] Stock validation during cart manipulation.
- [ ] Customer wishlist management.

### Phase 8: Checkout & Cash on Delivery (COD) Engine
- [ ] Single-page / multi-step responsive checkout.
- [ ] Bangladesh address form: Name, Phone, Alternative Phone, Division, District, Full Address, Delivery notes.
- [ ] Dynamic shipping calculation: Inside Dhaka (৳60) vs Outside Dhaka (৳120).
- [ ] Coupon application system with server-side validity checks (min spend, expiry, usage count).
- [ ] Atomic order creation with `DB::transaction` and `lockForUpdate` stock deduction.
- [ ] Order confirmation page with printable summary and tracking code.

### Phase 9: Customer Account & Order History
- [ ] Customer dashboard.
- [ ] Order history with order status badges (Pending, Confirmed, Shipped, Delivered, Cancelled).
- [ ] Detailed order receipt view.
- [ ] Customer address book management.
- [ ] Review submission for purchased items.

### Phase 10: Admin Order Fulfillment & Printable Invoice
- [ ] Order management list with filters (status, date, search by customer phone or order ID).
- [ ] Order details view with printable invoice.
- [ ] Order status updater with remarks recorded in `order_status_histories`.

### Phase 11: SEO, Polish, QA Testing & Shared Hosting Deployment Package
- [ ] Dynamic meta tags, OpenGraph tags, sitemap.xml generator, robots.txt.
- [ ] Full automated test suite (Unit & Feature tests covering auth, cart, checkout, admin).
- [ ] Performance audit, asset minification check, `.htaccess` optimization for shared hosting.
- [ ] Deployment checklist and step-by-step cPanel deployment guide.

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
| Responsive Navbar & Footer | Customer UI | Phase 4 | Pending |
| Homepage Hero & Product Grids | Customer UI | Phase 4 | Pending |
| Product Catalog & Filtering | Customer UI | Phase 5 | Pending |
| Product Details & Image Gallery | Customer UI | Phase 5 | Pending |
| Session + User Cart | Customer Logic | Phase 6 | Pending |
| Wishlist System | Customer Logic | Phase 6 | Pending |
| Checkout & BD Address Form | Order Engine | Phase 7 | Pending |
| Dhaka / Outside Dhaka Shipping | Order Engine | Phase 7 | Pending |
| Cash On Delivery Payment | Order Engine | Phase 7 | Pending |
| Coupon & Discount Validation | Order Engine | Phase 7 | Pending |
| Customer Order History & Invoice | Customer Portal | Phase 8 | Pending |
| Admin Sales & Orders Dashboard | Admin UI | Phase 9 | Pending |
| Admin Product & Inventory CRUD | Admin UI | Phase 9 | Pending |
| Admin Order Status Workflow | Admin Operations | Phase 10 | Pending |
| Admin Banner & Coupon Controls | Admin Operations | Phase 10 | Pending |
| SEO Sitemap & Meta Tags | SEO & Marketing | Phase 11 | Pending |
| Shared Hosting Production Guide | Deployment | Phase 11 | Pending |
