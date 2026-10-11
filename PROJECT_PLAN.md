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

### Phase 2: Complete Database Architecture & Models
- [ ] Create database migrations for all 21 core tables:
  - Users & Roles (`users`, `addresses`)
  - Catalog (`categories`, `brands`, `products`, `product_images`)
  - Shopping (`carts`, `cart_items`, `wishlists`, `wishlist_items`)
  - Checkout & Orders (`orders`, `order_items`, `order_status_histories`, `payments`)
  - Discounts & Promotions (`coupons`, `coupon_usages`, `banners`)
  - Engagement & Content (`reviews`, `settings`)
  - Auditing & Inventory (`inventory_movements`, `activity_logs`)
- [ ] Eloquent models with relations, casts, fillable attributes, and soft deletes.
- [ ] Database seeders with realistic demo data tailored for Bangladesh (Districts, categories, brands, sample products with BDT prices, admin user).

### Phase 3: Authentication, Authorization & Security Baseline
- [ ] Customer authentication (Registration, Login, Password Reset, Profile).
- [ ] Admin authentication with secure route prefix (`/admin`) and dedicated admin role middleware.
- [ ] Bangladesh phone number validation rule (`01[3-9]\d{8}`).
- [ ] Security hardening: Rate-limiting, CSRF, mass-assignment guards, security headers.

### Phase 4: Customer Frontend Layout & Homepage
- [ ] Master customer layout with top announcement bar (currency BDT, helpline), responsive navbar, search input, category dropdown, cart counter badge, and footer.
- [ ] Dynamic homepage with:
  - Hero slider / banners
  - Category showcase with icons
  - Featured products grid
  - New arrivals and best sellers
  - Value propositions (Fast delivery, COD available, Easy return)
- [ ] Static pages: About Us, Contact, FAQ, Terms, Privacy Policy, Return Policy.

### Phase 5: Product Catalog, Filters & Search
- [ ] Category and Brand browse pages.
- [ ] Product listing with sorting (price low-high, high-low, newest), price range filter, and category filter.
- [ ] Live/server-side search with sanitized keywords.
- [ ] Product detail page:
  - Image gallery / thumbnails
  - Title, SKU, Category, Brand, Stock badge
  - Regular price vs. Sale price in ৳
  - Add to Cart with quantity selector
  - Add to Wishlist toggle
  - Long description and specifications
  - Related products carousel/grid
  - Customer reviews list and submission form

### Phase 6: Shopping Cart & Wishlist System
- [ ] Dual-state cart: Guest session-based cart + authenticated persistent cart.
- [ ] Automatic cart merger upon customer login.
- [ ] Add, update quantity, remove, and clear cart actions with real-time feedback.
- [ ] Stock validation during cart manipulation.
- [ ] Customer wishlist management.

### Phase 7: Checkout & Cash on Delivery (COD) Engine
- [ ] Single-page / multi-step responsive checkout.
- [ ] Bangladesh address form: Name, Phone, Alternative Phone, Division, District, Full Address, Delivery notes.
- [ ] Dynamic shipping calculation: Inside Dhaka (৳60) vs Outside Dhaka (৳120).
- [ ] Coupon application system with server-side validity checks (min spend, expiry, usage count).
- [ ] Atomic order creation with `DB::transaction` and `lockForUpdate` stock deduction.
- [ ] Order confirmation page with printable summary and tracking code.

### Phase 8: Customer Account & Order History
- [ ] Customer dashboard.
- [ ] Order history with order status badges (Pending, Confirmed, Shipped, Delivered, Cancelled).
- [ ] Detailed order receipt view.
- [ ] Customer address book management.
- [ ] Review submission for purchased items.

### Phase 9: Admin Dashboard & Management System
- [ ] Modern, responsive Admin layout with sidebar navigation, header notifications, and admin profile.
- [ ] Executive dashboard overview:
  - Total sales in ৳, Today's orders, Total customers, Low stock alerts
  - Recent orders table
  - Monthly sales chart
- [ ] Category & Brand CRUD with image upload and SEO slug generation.
- [ ] Product Management:
  - Full CRUD with multiple image uploads, primary image selector, SKU generator
  - Pricing (regular, sale, purchase), stock levels, description, category, brand
- [ ] Inventory adjustment logs & low stock tracker.

### Phase 10: Admin Order & Operations Management
- [ ] Order management list with filters (status, date, search by customer phone or order ID).
- [ ] Order details view with printable invoice.
- [ ] Order status updater with remarks recorded in `order_status_histories`.
- [ ] Coupon manager (create discount codes, percentages, limits, expiry).
- [ ] Banner management (homepage carousel slides and promotional promos).
- [ ] Review moderation (approve/reject).
- [ ] Store settings manager (delivery charges, contact info, store status).

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
| Database Migrations (21 Tables) | Database | Phase 2 | Pending |
| Eloquent Models & Relations | Database | Phase 2 | Pending |
| Seeders (BD Districts, Demo Catalog) | Database | Phase 2 | Pending |
| Customer Auth (Login/Register) | Security | Phase 3 | Pending |
| Admin Auth & Role Middleware | Security | Phase 3 | Pending |
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
