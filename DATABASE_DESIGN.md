# ShopBD — Database Schema Architecture & Specification

**Project**: ShopBD  
**Database System**: MySQL 8.x / MariaDB 10.4+ compatible (SQLite for automated testing)  
**Character Set**: `utf8mb4`  
**Collation**: `utf8mb4_unicode_ci`  
**Currency Handling**: Strict `DECIMAL(12, 2)` for monetary values (BDT ৳)  

---

## 1. Entity-Relationship Overview

```
[ users ]
   │
   ├──────< [ addresses ] (1:N)
   ├──────< [ carts ] ─────< [ cart_items ] >───── [ products ]
   ├──────< [ wishlists ] ─< [ wishlist_items ] >── [ products ]
   ├──────< [ orders ] ────┬─< [ order_items ] >──── [ products ]
   │                       ├─< [ order_status_histories ]
   │                       └─< [ payments ]
   ├──────< [ reviews ] >────────────────────────── [ products ]
   └──────< [ coupon_usages ] >──────────────────── [ coupons ]

[ categories ] ─ (self-referencing parent_id 1:N) ──< [ products ]
[ brands ] ──────────────────────────────────────────< [ products ]
[ products ] ──< [ product_images ] (1:N)
[ products ] ──< [ inventory_movements ] (1:N)
[ banners ]
[ settings ]
[ activity_logs ]
```

---

## 2. Table Specifications

### 2.1 `users`
Represents both customers and store administrators.

| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY | Unique user ID |
| `name` | VARCHAR(191) | NOT NULL | Customer or Admin full name |
| `email` | VARCHAR(191) | NOT NULL, UNIQUE | User email address |
| `phone` | VARCHAR(20) | NULL, INDEX | Bangladesh phone number (`01...`) |
| `password` | VARCHAR(255) | NOT NULL | Hashed password |
| `role` | ENUM('customer', 'admin') | DEFAULT 'customer', INDEX | Access level |
| `is_active` | BOOLEAN | DEFAULT TRUE, INDEX | Account status |
| `email_verified_at` | TIMESTAMP | NULL | Email verification timestamp |
| `remember_token` | VARCHAR(100) | NULL | "Remember me" session token |
| `created_at` | TIMESTAMP | NULL | Created timestamp |
| `updated_at` | TIMESTAMP | NULL | Updated timestamp |

---

### 2.2 `categories`
Hierarchical product categorization (Clothing, Electronics, Groceries, etc.).

| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY | Category ID |
| `parent_id` | BIGINT UNSIGNED | NULL, FOREIGN KEY (categories.id) | For subcategories (NULL = root) |
| `name` | VARCHAR(100) | NOT NULL | Category name |
| `slug` | VARCHAR(120) | NOT NULL, UNIQUE | URL slug |
| `description` | TEXT | NULL | Category description |
| `image` | VARCHAR(255) | NULL | Icon/Thumbnail path |
| `is_featured` | BOOLEAN | DEFAULT FALSE, INDEX | Display on homepage showcase |
| `is_active` | BOOLEAN | DEFAULT TRUE, INDEX | Visibility status |
| `sort_order` | INT | DEFAULT 0 | Ordering priority |
| `created_at` | TIMESTAMP | NULL | |
| `updated_at` | TIMESTAMP | NULL | |

---

### 2.3 `brands`
Product brands and manufacturers.

| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY | Brand ID |
| `name` | VARCHAR(100) | NOT NULL | Brand name |
| `slug` | VARCHAR(120) | NOT NULL, UNIQUE | URL slug |
| `logo` | VARCHAR(255) | NULL | Brand logo path |
| `description` | TEXT | NULL | Brand description |
| `is_active` | BOOLEAN | DEFAULT TRUE, INDEX | Active status |
| `created_at` | TIMESTAMP | NULL | |
| `updated_at` | TIMESTAMP | NULL | |

---

### 2.4 `products`
The core catalog item table.

| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY | Product ID |
| `category_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY (categories.id) | Linked category |
| `brand_id` | BIGINT UNSIGNED | NULL, FOREIGN KEY (brands.id) | Optional linked brand |
| `name` | VARCHAR(255) | NOT NULL | Product title |
| `slug` | VARCHAR(255) | NOT NULL, UNIQUE | URL slug |
| `sku` | VARCHAR(100) | NOT NULL, UNIQUE | Stock Keeping Unit |
| `short_description`| VARCHAR(500) | NULL | Excerpt for cards & meta |
| `description` | LONGTEXT | NULL | Detailed HTML description |
| `purchase_price` | DECIMAL(12, 2) | DEFAULT 0.00 | Cost price for profit reporting |
| `regular_price` | DECIMAL(12, 2) | NOT NULL | Standard retail price in BDT |
| `sale_price` | DECIMAL(12, 2) | NULL | Discounted selling price in BDT |
| `stock_quantity` | INT | DEFAULT 0 | Real-time physical inventory |
| `low_stock_threshold`| INT | DEFAULT 5 | Minimum threshold before alert |
| `is_featured` | BOOLEAN | DEFAULT FALSE, INDEX | Featured on homepage |
| `is_active` | BOOLEAN | DEFAULT TRUE, INDEX | Available for purchase |
| `created_at` | TIMESTAMP | NULL | |
| `updated_at` | TIMESTAMP | NULL | |
| `deleted_at` | TIMESTAMP | NULL | Soft delete column |

---

### 2.5 `product_images`
Multiple images per product.

| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY | Image ID |
| `product_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY (products.id CASCADE)| Owner product |
| `image_path` | VARCHAR(255) | NOT NULL | Storage path in `/storage/products` |
| `is_primary` | BOOLEAN | DEFAULT FALSE, INDEX | Main thumbnail / cover image |
| `sort_order` | INT | DEFAULT 0 | Display sequence in gallery |
| `created_at` | TIMESTAMP | NULL | |
| `updated_at` | TIMESTAMP | NULL | |

---

### 2.6 `carts`
Shopping cart instance (supports both guest sessions and logged-in customers).

| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY | Cart ID |
| `user_id` | BIGINT UNSIGNED | NULL, FOREIGN KEY (users.id CASCADE)| Owner if logged in |
| `session_id` | VARCHAR(100) | NULL, INDEX | Guest visitor session token |
| `last_active_at`| TIMESTAMP | NOT NULL | Used to prune abandoned carts |
| `created_at` | TIMESTAMP | NULL | |
| `updated_at` | TIMESTAMP | NULL | |

---

### 2.7 `cart_items`
Items held in a cart.

| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY | Cart item ID |
| `cart_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY (carts.id CASCADE) | Parent cart |
| `product_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY (products.id CASCADE)| Linked product |
| `quantity` | INT UNSIGNED | NOT NULL DEFAULT 1 | Item quantity |
| `unit_price` | DECIMAL(12, 2) | NOT NULL | Current price snapshot at add |
| `created_at` | TIMESTAMP | NULL | |
| `updated_at` | TIMESTAMP | NULL | |
| *Constraint* | UNIQUE (`cart_id`, `product_id`) | Prevents duplicate row per item |

---

### 2.8 `addresses`
Saved customer delivery addresses in Bangladesh.

| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY | Address ID |
| `user_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY (users.id CASCADE)| Customer |
| `recipient_name`| VARCHAR(100) | NOT NULL | Name of recipient |
| `phone` | VARCHAR(20) | NOT NULL | Mobile number (`01...`) |
| `division` | VARCHAR(50) | NOT NULL | BD Division (Dhaka, Chittagong, etc.) |
| `district` | VARCHAR(50) | NOT NULL | BD District |
| `upazila_thana` | VARCHAR(100) | NULL | Upazila or Police Station |
| `address_line` | TEXT | NOT NULL | Road, House, Flat details |
| `is_default` | BOOLEAN | DEFAULT FALSE | Default checkout address |
| `created_at` | TIMESTAMP | NULL | |
| `updated_at` | TIMESTAMP | NULL | |

---

### 2.9 `wishlists` & 2.10 `wishlist_items`
Customer saved items.

- `wishlists`: `id`, `user_id` (FOREIGN KEY), `created_at`, `updated_at`.
- `wishlist_items`: `id`, `wishlist_id` (FOREIGN KEY CASCADE), `product_id` (FOREIGN KEY CASCADE), `created_at`, `updated_at`.
- *Constraint*: UNIQUE (`wishlist_id`, `product_id`).

---

### 2.11 `orders`
The master order transaction record.

| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY | Order ID |
| `order_number` | VARCHAR(32) | NOT NULL, UNIQUE, INDEX | Customer order code (e.g., `SBD-20261011-001`) |
| `user_id` | BIGINT UNSIGNED | NULL, FOREIGN KEY (users.id SET NULL) | Customer ID (NULL for guest) |
| `recipient_name`| VARCHAR(100) | NOT NULL | Customer name |
| `phone` | VARCHAR(20) | NOT NULL, INDEX | Primary BD mobile number |
| `alternative_phone`| VARCHAR(20)| NULL | Backup contact number |
| `delivery_address`| TEXT | NOT NULL | Full delivery address |
| `division` | VARCHAR(50) | NOT NULL | Division |
| `district` | VARCHAR(50) | NOT NULL, INDEX | District (determines shipping cost) |
| `shipping_zone` | ENUM('inside_dhaka', 'outside_dhaka') | NOT NULL | Calculated shipping zone |
| `shipping_cost` | DECIMAL(12, 2) | NOT NULL DEFAULT 60.00 | Applied delivery fee |
| `subtotal` | DECIMAL(12, 2) | NOT NULL | Product total before shipping/discount |
| `discount_amount`| DECIMAL(12, 2) | NOT NULL DEFAULT 0.00 | Coupon or promo discount |
| `coupon_id` | BIGINT UNSIGNED | NULL, FOREIGN KEY (coupons.id SET NULL) | Applied coupon ID |
| `total_amount` | DECIMAL(12, 2) | NOT NULL | Final payable amount in BDT |
| `payment_method`| VARCHAR(50) | NOT NULL DEFAULT 'cod' | Default 'cod' (Cash on Delivery) |
| `payment_status`| ENUM('unpaid', 'paid', 'refunded') | NOT NULL DEFAULT 'unpaid', INDEX | Payment state |
| `order_status` | ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned') | NOT NULL DEFAULT 'pending', INDEX | Order life cycle |
| `delivery_notes`| TEXT | NULL | Special customer instructions |
| `admin_notes` | TEXT | NULL | Private internal notes |
| `created_at` | TIMESTAMP | NULL | |
| `updated_at` | TIMESTAMP | NULL | |
| `deleted_at` | TIMESTAMP | NULL | Soft delete support |

---

### 2.12 `order_items`
Immutable snapshot of line items.

| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY | Line item ID |
| `order_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY (orders.id CASCADE)| Parent order |
| `product_id` | BIGINT UNSIGNED | NULL, FOREIGN KEY (products.id SET NULL)| Original product |
| `product_name` | VARCHAR(255) | NOT NULL | Product name snapshot |
| `product_sku` | VARCHAR(100) | NOT NULL | SKU snapshot |
| `unit_purchase_price`| DECIMAL(12, 2) | DEFAULT 0.00 | Cost price snapshot at sale |
| `unit_price` | DECIMAL(12, 2) | NOT NULL | Selling price snapshot at sale |
| `quantity` | INT UNSIGNED | NOT NULL | Quantity purchased |
| `subtotal` | DECIMAL(12, 2) | NOT NULL | `unit_price * quantity` |
| `created_at` | TIMESTAMP | NULL | |
| `updated_at` | TIMESTAMP | NULL | |

---

### 2.13 `order_status_histories`
Audit log of all order status changes.

| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY | History ID |
| `order_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY (orders.id CASCADE)| Target order |
| `from_status` | VARCHAR(50) | NULL | Previous status |
| `to_status` | VARCHAR(50) | NOT NULL | New status |
| `remarks` | TEXT | NULL | Reason or courier tracking update |
| `changed_by_user_id`| BIGINT UNSIGNED | NULL, FOREIGN KEY (users.id SET NULL)| Admin user who changed |
| `created_at` | TIMESTAMP | NULL | |

---

### 2.14 `payments`
Payment transactions (designed to support COD now and optional digital gateways later).

| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY | Payment ID |
| `order_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY (orders.id CASCADE)| Linked order |
| `payment_method`| VARCHAR(50) | NOT NULL | 'cod' or future gateways |
| `transaction_id`| VARCHAR(100) | NULL, INDEX | Transaction reference |
| `amount` | DECIMAL(12, 2) | NOT NULL | Amount paid |
| `currency` | VARCHAR(10) | NOT NULL DEFAULT 'BDT' | Currency |
| `status` | ENUM('pending', 'completed', 'failed', 'refunded')| NOT NULL DEFAULT 'pending'| Payment status |
| `gateway_response`| JSON | NULL | Full gateway payload |
| `verified_at` | TIMESTAMP | NULL | Payment completion timestamp |
| `created_at` | TIMESTAMP | NULL | |
| `updated_at` | TIMESTAMP | NULL | |

---

### 2.15 `coupons` & 2.16 `coupon_usages`
Promotional discount engine.

- `coupons`:
  - `id`: BIGINT UNSIGNED PRIMARY KEY
  - `code`: VARCHAR(50) UNIQUE NOT NULL INDEX
  - `type`: ENUM('fixed', 'percentage') NOT NULL
  - `value`: DECIMAL(12, 2) NOT NULL
  - `min_order_amount`: DECIMAL(12, 2) DEFAULT 0.00
  - `max_discount_amount`: DECIMAL(12, 2) NULL
  - `usage_limit_per_coupon`: INT NULL
  - `usage_limit_per_user`: INT DEFAULT 1
  - `total_used`: INT DEFAULT 0
  - `starts_at`: TIMESTAMP NULL
  - `expires_at`: TIMESTAMP NULL
  - `is_active`: BOOLEAN DEFAULT TRUE INDEX
  - `timestamps`

- `coupon_usages`:
  - `id`: BIGINT UNSIGNED PRIMARY KEY
  - `coupon_id`: BIGINT UNSIGNED FOREIGN KEY (coupons.id CASCADE)
  - `order_id`: BIGINT UNSIGNED FOREIGN KEY (orders.id CASCADE)
  - `user_id`: BIGINT UNSIGNED NULL FOREIGN KEY (users.id SET NULL)
  - `discount_applied`: DECIMAL(12, 2) NOT NULL
  - `created_at`: TIMESTAMP NULL

---

### 2.17 `reviews`
Product feedback and ratings.

| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY | Review ID |
| `product_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY (products.id CASCADE)| Product |
| `user_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY (users.id CASCADE)| Customer |
| `order_id` | BIGINT UNSIGNED | NULL, FOREIGN KEY (orders.id SET NULL)| Verified purchase order |
| `rating` | TINYINT UNSIGNED| NOT NULL | Rating 1 to 5 |
| `comment` | TEXT | NULL | Review comment |
| `is_approved` | BOOLEAN | DEFAULT FALSE, INDEX | Admin moderation flag |
| `created_at` | TIMESTAMP | NULL | |
| `updated_at` | TIMESTAMP | NULL | |

---

### 2.18 `banners`
Homepage slider and promotional cards.

| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY | Banner ID |
| `title` | VARCHAR(191) | NOT NULL | Heading |
| `subtitle` | VARCHAR(255) | NULL | Sub-heading |
| `image_path` | VARCHAR(255) | NOT NULL | Graphic asset |
| `button_text` | VARCHAR(50) | NULL | Call-to-action text |
| `button_url` | VARCHAR(255) | NULL | Target URL |
| `placement` | VARCHAR(50) | DEFAULT 'hero_slider', INDEX | Location on site |
| `sort_order` | INT | DEFAULT 0 | Ordering |
| `is_active` | BOOLEAN | DEFAULT TRUE, INDEX | Visibility flag |
| `created_at` | TIMESTAMP | NULL | |
| `updated_at` | TIMESTAMP | NULL | |

---

### 2.19 `settings`
Key-value configuration store for runtime adjustments without code modifications.

| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY | Setting ID |
| `group` | VARCHAR(50) | NOT NULL, INDEX | 'general', 'shipping', 'store' |
| `key` | VARCHAR(100) | NOT NULL, UNIQUE | Identifier |
| `value` | LONGTEXT | NULL | Value |
| `type` | VARCHAR(30) | DEFAULT 'string' | 'string', 'number', 'boolean', 'json' |
| `description` | VARCHAR(255) | NULL | Explanatory note |
| `created_at` | TIMESTAMP | NULL | |
| `updated_at` | TIMESTAMP | NULL | |

---

### 2.20 `inventory_movements`
Double-entry inventory audit log to prevent discrepancies.

| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY | Movement ID |
| `product_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY (products.id CASCADE)| Product |
| `order_id` | BIGINT UNSIGNED | NULL, FOREIGN KEY (orders.id SET NULL)| Linked order |
| `type` | ENUM('purchase', 'sale', 'adjustment', 'return')| NOT NULL | Movement reason |
| `quantity_change`| INT | NOT NULL | Positive or negative delta |
| `previous_stock`| INT | NOT NULL | Stock count before change |
| `current_stock` | INT | NOT NULL | Stock count after change |
| `remarks` | VARCHAR(255) | NULL | Reason for manual adjustment |
| `created_by_user_id`| BIGINT UNSIGNED | NULL, FOREIGN KEY (users.id SET NULL)| Admin user |
| `created_at` | TIMESTAMP | NULL | |

---

### 2.21 `activity_logs`
Security and administrative audit trail.

| Column | Type | Modifiers | Description |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | AUTO_INCREMENT, PRIMARY KEY | Log ID |
| `user_id` | BIGINT UNSIGNED | NULL, FOREIGN KEY (users.id SET NULL)| Acting user |
| `action` | VARCHAR(100) | NOT NULL, INDEX | Action name (e.g. `order.status_update`) |
| `subject_type` | VARCHAR(100) | NULL | Eloquent model class |
| `subject_id` | BIGINT UNSIGNED | NULL | Eloquent model primary ID |
| `ip_address` | VARCHAR(45) | NULL | Client IP address |
| `user_agent` | TEXT | NULL | Client browser agent |
| `details` | JSON | NULL | Contextual details or before/after diff |
| `created_at` | TIMESTAMP | NULL | |

---

## 3. Concurrency & Data Integrity Strategy

1. **Preventing Overselling (Race Condition Safeguard)**:
   ```php
   DB::transaction(function () use ($cartItems) {
       foreach ($cartItems as $item) {
           $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
           if ($product->stock_quantity < $item->quantity) {
               throw new OutOfStockException("Item {$product->name} is out of stock.");
           }
           $product->decrement('stock_quantity', $item->quantity);
           // Log inventory movement...
       }
   });
   ```
2. **Cascading vs. Snapshot Preservations**:
   - Deleting a category or brand sets product relationships to NULL or restricts deletion if active products exist.
   - Deleting a user keeps historical orders (`user_id` set to NULL) while maintaining recipient contact details in the order row.
   - Modifying a product's price never alters existing `order_items` records.
