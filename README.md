# ShopBD — Single-Vendor E-Commerce Platform for Bangladesh

[![Laravel 11](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP 8.2](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![Bootstrap 5](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![Tests](https://img.shields.io/badge/Tests-131%20Passed%20(608%20Assertions)-2ea44f?style=flat&logo=githubactions&logoColor=white)](#automated-testing)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

**ShopBD** is a fast, secure, single-vendor multi-category e-commerce platform built on **Laravel 11** and **Bootstrap 5**, tailored specifically for the Bangladeshi market. It includes native BDT (৳) currency handling, inside/outside Dhaka dynamic delivery rates, dual-credential authentication (Email or BD Mobile Number), Cash on Delivery (COD), optional payment gateway support (SSLCOMMERZ, bKash), and courier logistics integration (Steadfast, Pathao, Mock).

---

## Key Highlights & Architectural Features

- **Bangladesh-Native Commerce Engine**:
  - Currency: Bangladeshi Taka (`৳` / BDT) with strict `DECIMAL(12,2)` precision.
  - Delivery Fees: Dynamic Dhaka City (৳60) vs. Nationwide Outside Dhaka (৳120), with configurable free shipping thresholds.
  - Dual Login: Customers and administrators can authenticate with either **Email Address** OR **Bangladeshi Mobile Number** (`01XXXXXXXXX`).
  - Strict No-Modal Rule: All major administrative creation and edit screens (Products, Categories, Brands, Coupons, Banners, Settings) are dedicated full-page responsive views.
- **Robust Order Lifecycle & Concurrency**:
  - Atomic checkout with pessimistic row locking (`lockForUpdate()`) preventing inventory overselling race conditions.
  - Idempotency token enforcement preventing duplicate orders from rapid repeated clicks.
  - Immutable historical snapshots (`OrderItem`) preserving purchased product names, SKUs, purchase costs, and sale prices.
  - Order state machine with automated, double-restock-safe inventory restoration upon order cancellation or return.
- **Bangladesh Payment Gateways & Courier Logistics**:
  - **Cash on Delivery (COD)**: 100% autonomous, zero external credentials required.
  - **SSLCOMMERZ v4 & bKash Tokenized Checkout**: Decoupled strategy adapters with strict callback verification and PCI-DSS compliance (zero card details stored).
  - **Courier Logistics**: Steadfast REST API, Pathao Aladdin API, and a built-in Mock Courier for offline/sandbox testing.
- **Shared Hosting & cPanel Ready**:
  - Precompiled static assets (`npm run build`) eliminating Node.js runtime requirement on shared hosting.
  - Root and public `.htaccess` security hardening with web root isolation preventing access to `.env` or application source code.
  - Built-in deployment smoke test (`php artisan shopbd:smoke-test`), automated backup scripts, and cPanel deployment guide.

---

## System Requirements

- **PHP**: >= 8.2 with extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `pdo_sqlite`, `tokenizer`, `xml`, `zip`.
- **Database**: MySQL 5.7+ / 8.0+ or MariaDB 10.3+. (SQLite in-memory used for fast automated testing).
- **Composer**: 2.x
- **Node.js / npm**: Node 18+ (for local asset compilation only; shared hosting does not require Node).

---

## Quick Start & Installation

### 1. Clone & Install Dependencies
```bash
git clone https://github.com/mdsamaul/E-COMMERCE_PROJECT.git shopbd
cd shopbd
composer install
npm install
npm run build
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

Configure your `.env` database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shopbd
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Database Migration & Seeding
```bash
php artisan migrate --seed
php artisan storage:link
```

### 4. Run Development Server
```bash
php artisan serve
```
Visit `http://localhost:8000` in your web browser.

---

## Default Demo Credentials

| Role | Email / Mobile | Password | Access Path |
|---|---|---|---|
| **Super Administrator** | `admin@shopbd.com` / `01711000000` | `password123` | `/admin/login` |
| **Demo Customer** | `customer@shopbd.com` / `01712000000` | `password123` | `/login` |

*To create a new administrator safely from CLI without exposing credentials in seeds:*
```bash
php artisan shopbd:create-admin
```

---

## Automated Testing & Health Diagnostics

### Running the Test Suite
The complete test suite runs against in-memory SQLite and requires zero external database dependencies:
```bash
php artisan test
```
*Current test suite status: **131 tests passed (608 assertions), 0 failures**.*

### Running the Production Smoke Test
Verify deployment readiness, file permissions, database state, and security boundaries:
```bash
php artisan shopbd:smoke-test
```

---

## Documentation & Architecture Guides

Detailed documentation is available in the repository:
- [PROJECT_PLAN.md](PROJECT_PLAN.md) — Complete 10-phase master architecture roadmap and feature checklist.
- [DATABASE_DESIGN.md](DATABASE_DESIGN.md) — Schema definitions for all 21 tables, foreign keys, and indexes.
- [DEVELOPMENT_LOG.md](DEVELOPMENT_LOG.md) — Comprehensive phase-by-phase implementation and audit log.
- [cPanel Shared Hosting Deployment Guide](docs/CPANEL_SHARED_HOSTING_DEPLOYMENT_GUIDE.md) — Step-by-step cPanel production deployment runbook.
- [Payment & Courier Integration Plan](docs/PAYMENT_AND_COURIER_INTEGRATION_PLAN.md) — Official gateway & logistics API specs.

---

## Shared Hosting Deployment Automation

Scripts for one-step packaging, deployment, and backups are provided in `scripts/`:
- `scripts/build-production-package.ps1` — Builds Vite assets and creates a production `.zip` ready for cPanel File Manager upload.
- `scripts/deploy-cpanel.sh` — Run via cPanel Terminal/SSH to set permissions, optimize autoloader, migrate, and cache routes.
- `scripts/backup-cpanel.sh` — Nightly cron-compatible database and uploaded media backup script with 14-day rotation.

---

## License

This software is released under the [MIT License](LICENSE).
