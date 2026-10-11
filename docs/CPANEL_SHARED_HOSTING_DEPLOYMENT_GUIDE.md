# ShopBD cPanel Shared Hosting Production Deployment Guide

This guide provides complete, step-by-step instructions for deploying **ShopBD** to affordable Bangladesh-compatible cPanel and DirectAdmin shared hosting providers (such as DianaHost, ExonHost, PutulHost, Dhaka Web Host, Namecheap, Hostinger, or BDIX shared hosts).

---

## 1. Architectural Overview & Security Isolation

Shared hosting environments require strict separation between private application code and public web files.

### Security Golden Rule
> **NEVER upload your entire Laravel directory directly into `public_html` without strict protection.**
> The `.env` file, database credentials, application logs, and source code must remain physically outside the public web root.

### Recommended Directory Structure (Two-Directory Strategy)
```
/home/cpanelusername/
├── shopbd_core/              <-- PRIVATE: Laravel core (outside web root)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── storage/
│   ├── vendor/
│   ├── .env                  <-- SECURED: inaccessible via browser
│   └── artisan
│
└── public_html/              <-- PUBLIC: Web Document Root
    ├── build/                <-- Compiled Vite CSS & JS
    │   ├── assets/
    │   └── manifest.json
    ├── storage/              <-- Symlink to shopbd_core/storage/app/public
    ├── .htaccess             <-- Fortified rewrite & security headers
    ├── favicon.ico
    ├── index.php             <-- Front controller pointing to shopbd_core
    └── robots.txt
```

---

## 2. Pre-requisite Hosting Inspection Checklist

Before beginning deployment, log into your cPanel dashboard and verify the following specifications:

| Requirement | Minimum Specification | Where to Check in cPanel |
|---|---|---|
| **PHP Version** | **PHP 8.2 or 8.3** | *Software > Select PHP Version* or *MultiPHP Manager* |
| **PHP Extensions** | `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `json`, `mbstring`, `openssl`, `pcre`, `pdo_mysql`, `session`, `tokenizer`, `xml` | *Software > Select PHP Version > Extensions* |
| **Memory Limit** | `256M` (Recommended: `512M`) | *Software > MultiPHP INI Editor* |
| **Max Execution Time** | `120` seconds (or higher) | *Software > MultiPHP INI Editor* |
| **Upload Max Filesize**| `32M` (or higher) | *Software > MultiPHP INI Editor* |
| **Database** | MySQL 5.7+ / 8.0+ or MariaDB 10.3+ | *Databases > phpMyAdmin* |
| **Terminal / SSH** | Enabled (preferred for commands) | *Advanced > Terminal* or *SSH Access* |
| **SSL / HTTPS** | Free Let's Encrypt / cPanel AutoSSL | *Security > SSL/TLS Status* |

> [!NOTE]
> If Terminal or SSH is disabled on your hosting plan, contact your hosting support to enable it. Alternatively, use the cPanel File Manager and phpMyAdmin methods detailed below.

---

## 3. Domain DNS & SSL Configuration

### Step 1: DNS Setup
Point your custom domain (e.g., `yourdomain.com.bd`) to your shared hosting account:
- **A Record**: Point `@` (root) and `www` to your cPanel Shared IP address.
- Alternatively, set your domain's Nameservers (NS) to your provider's nameservers.
- Allow 1-2 hours for global DNS propagation.

### Step 2: Install Free SSL (AutoSSL)
1. In cPanel, navigate to **Security > SSL/TLS Status**.
2. Select your domain (`yourdomain.com.bd` and `www.yourdomain.com.bd`).
3. Click **Run AutoSSL**.
4. Once completed, a green lock icon indicates active SSL encryption.
5. In [public/.htaccess](file:///d:/other/E-COMMERCE_PROJECT/public/.htaccess), uncomment lines 8-10 to enforce automatic HTTPS redirection:
   ```apache
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

---

## 4. cPanel Database Creation

1. In cPanel, navigate to **Databases > MySQL® Databases**.
2. **Create New Database**:
   - Enter database name: `shopbd`
   - Click **Create Database**.
   - Note the full database name: `cpaneluser_shopbd`.
3. **Create New Database User**:
   - Enter username: `shopbduser`
   - Use the Password Generator to create a strong password (e.g., `p#K9$vL2!mQ8#zX4`).
   - Click **Create User**.
   - Note the full username: `cpaneluser_shopbduser` and password.
4. **Add User to Database**:
   - Select User `cpaneluser_shopbduser` and Database `cpaneluser_shopbd`.
   - Click **Add**.
   - Check **ALL PRIVILEGES** and click **Make Changes**.

---

## 5. Local Packaging & Upload

### Step 1: Build Frontend Assets Locally
On your local development machine, compile the production assets using Vite:
```powershell
npm run build
```
Verify that `public/build/manifest.json` and compiled assets exist.

### Step 2: Generate Clean Production ZIP Archive
Run the provided automated packaging script:
```powershell
powershell -ExecutionPolicy Bypass -File scripts/build-production-package.ps1
```
This produces `shopbd-production-package.zip` in your project root, automatically omitting `.git`, `node_modules`, and local cache files.

### Step 3: Upload via cPanel File Manager or SFTP
1. Open **cPanel > Files > File Manager**.
2. Navigate to your root home directory `/home/cpaneluser/` (one level **above** `public_html`).
3. Click **Upload** and upload `shopbd-production-package.zip`.
4. Right-click the uploaded ZIP file and click **Extract**. Extract into a new folder named `shopbd_core`.

---

## 6. Web Document Root Configuration

Depending on your cPanel features, choose **Method A** or **Method B**:

### Method A: Pointing public_html to shopbd_core/public (Recommended)
If your cPanel allows modifying Document Root in **Domains > Domains**:
- Set the Document Root for `yourdomain.com.bd` to `/home/cpaneluser/shopbd_core/public`.

### Method B: Split Directory Setup (Compatible with all cPanel accounts)
If `public_html` is locked as the primary domain root:
1. Move the contents of `/home/cpaneluser/shopbd_core/public/*` into `/home/cpaneluser/public_html/`.
2. Edit `/home/cpaneluser/public_html/index.php`:
   ```php
   // Change line 13:
   require __DIR__.'/../shopbd_core/vendor/autoload.php';

   // Change line 17:
   $app = require_once __DIR__.'/../shopbd_core/bootstrap/app.php';
   ```
3. In this setup, `.env` and all application source code remain safe inside `/home/cpaneluser/shopbd_core/`, completely inaccessible to browsers.

---

## 7. Production Environment Configuration (`.env`)

1. In cPanel File Manager, open `/home/cpaneluser/shopbd_core/`.
2. Duplicate `.env.production.example` and rename it to `.env`.
3. Open `.env` in the cPanel Code Editor and configure:
   ```env
   APP_NAME=ShopBD
   APP_ENV=production
   APP_KEY=base64:YOUR_APP_KEY_HERE
   APP_DEBUG=false
   APP_URL=https://yourdomain.com.bd

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=cpaneluser_shopbd
   DB_USERNAME=cpaneluser_shopbduser
   DB_PASSWORD="your_database_password_here"

   SESSION_DRIVER=database
   CACHE_STORE=file
   QUEUE_CONNECTION=sync
   FILESYSTEM_DISK=public

   # Optional payment gateway credentials
   SSLCOMMERZ_STORE_ID=
   SSLCOMMERZ_STORE_PASSWORD=
   SSLCOMMERZ_IS_SANDBOX=false
   ```
4. Save the `.env` file.

---

## 8. Terminal / SSH Deployment Execution

Open **cPanel > Advanced > Terminal** (or connect via SSH):
```bash
# Navigate to the core application
cd /home/cpaneluser/shopbd_core

# 1. Set correct directory permissions
chmod -R 775 storage bootstrap/cache
chmod -R 755 public

# 2. Install production dependencies without dev packages
composer install --no-dev --prefer-dist --optimize-autoloader

# 3. Create storage symlink
php artisan storage:link

# Note for Method B (Split setup): If public_html is separate, link storage to public_html:
# ln -s /home/cpaneluser/shopbd_core/storage/app/public /home/cpaneluser/public_html/storage

# 4. Run database migrations
php artisan migrate --force

# 5. Create initial store administrator account safely
php artisan shopbd:create-admin

# 6. Optimize production caching
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Alternatively, you can run the one-command deployment script:
```bash
bash scripts/deploy-cpanel.sh
```

---

## 9. cPanel Scheduled Cron Jobs (Task Scheduling)

ShopBD requires a 1-minute cron job to process pending orders, auto-cancel uncompleted sessions, and dispatch queued notifications.

1. In cPanel, navigate to **Advanced > Cron Jobs**.
2. Under **Add New Cron Job**:
   - Common Settings: **Once Per Minute (`* * * * *`)**
   - Command:
     ```bash
     cd /home/cpaneluser/shopbd_core && php artisan schedule:run >> /dev/null 2>&1
     ```
3. Click **Add New Cron Job**.

---

## 10. Automated & Manual Smoke Testing Checklist

### Automated Smoke Test
Run the built-in diagnostic command in cPanel Terminal:
```bash
php artisan shopbd:smoke-test
```
Verify that all checks output **PASS**:
- [x] Database Connectivity & Schema
- [x] Application Encryption Key (`APP_KEY`)
- [x] Production Environment (`APP_ENV=production, APP_DEBUG=false`)
- [x] HTTPS / SSL Web Scheme
- [x] Storage & Cache Permissions (`chmod 775`)
- [x] Public Storage Symlink
- [x] Admin Authentication Account
- [x] Product Catalog & Categories
- [x] Web Root Security & Isolation (`.env` and `.git` protected)

### Manual 7-Point Live Storefront Smoke Test
1. **Homepage**: Visit `https://yourdomain.com.bd` — confirm hero slider, featured categories, and trust badges render.
2. **Catalog & Search**: Test product search keyword and category filtering.
3. **Product Detail**: Open a product page — verify image gallery, specifications, and BDT pricing.
4. **Shopping Cart**: Add item to cart, change quantity, and view cart summary.
5. **Checkout**: Place an order with Cash on Delivery (COD) using a Bangladesh phone number and district.
6. **Order Confirmation & Invoice**: Confirm receipt page loads with order tracking number (`ORD-YYYYMMDD-XXXXXX`) and printable A4 invoice.
7. **Admin Dashboard**: Log into `https://yourdomain.com.bd/admin/login` and verify orders listing and sales metrics.

---

## 11. Maintenance, Safe Updates & Rollback Plan

### Safe Website Update Procedure
When updating ShopBD with new code:
```bash
cd /home/cpaneluser/shopbd_core

# 1. Put store in maintenance mode with a secret bypass token
php artisan down --secret="shopbd-emergency-access"

# 2. Back up database and uploads before proceeding
bash scripts/backup-cpanel.sh

# 3. Pull new code or upload new release ZIP
git pull origin main

# 4. Update dependencies and database migrations
composer install --no-dev --optimize-autoloader
php artisan migrate --force

# 5. Clear and rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Run smoke test
php artisan shopbd:smoke-test

# 7. Bring store back online
php artisan up
```
*(You can access the store while in maintenance mode via `https://yourdomain.com.bd/shopbd-emergency-access`)*.

### Automated Backups
Run or schedule `scripts/backup-cpanel.sh`:
- Creates compressed MySQL dump in `/home/cpaneluser/shopbd_backups/db_*.sql.gz`.
- Archives uploaded images in `/home/cpaneluser/shopbd_backups/uploads_*.tar.gz`.
- Automatically purges backups older than 14 days.

### Emergency Rollback Procedure
If an update causes unforeseen errors:
1. Restore database from backup:
   ```bash
   gunzip < /home/cpaneluser/shopbd_backups/db_shopbd_YYYYMMDD_HHMMSS.sql.gz | mysql -u cpaneluser_shopbduser -p"your_password" cpaneluser_shopbd
   ```
2. Revert code to previous commit or previous backup archive:
   ```bash
   git checkout HEAD~1
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan up
   ```
