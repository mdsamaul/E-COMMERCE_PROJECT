# ShopBD — Payment Gateway & Courier Logistics Integration Plan (Phase 8)

## 1. Architectural Overview & Design Philosophy

ShopBD implements an **adapter and strategy pattern** for both online payment gateways and third-party courier logistics.
All external integrations are strictly **optional**, **modular**, and **non-blocking**:

1. **Cash on Delivery (COD)** is always active out of the box and requires zero external credentials or third-party network access.
2. **Payment Gateways** (SSLCOMMERZ, bKash) and **Courier Services** (Steadfast, Pathao) remain in an inert, disabled state until merchant credentials are explicitly supplied via environment variables (`.env`).
3. **No Fake Confirmations**: If an external API is not configured or unavailable, ShopBD never claims live operations succeeded. For courier logistics, a tested, isolated `MockCourierService` is provided for end-to-end development, manual testing, and automated integration tests.
4. **Zero Card Storage (PCI-DSS Compliance)**: ShopBD never handles, transmits, or stores credit/debit card numbers, CVVs, or bank PINs on its application servers. Payments are handled via hosted redirection or secure vendor iframes.
5. **Strict Callback Idempotency**: All webhook and callback listeners verify order payment status, currency (`BDT`), and expected monetary amounts before applying state transitions. Repeated callbacks or replayed requests will never duplicate payment ledger records or alter settled orders.

---

## 2. Core Interfaces

### 2.1 Payment Gateway Contract (`PaymentGatewayInterface`)
Location: `app/Contracts/PaymentGatewayInterface.php`

```php
interface PaymentGatewayInterface
{
    public function getName(): string;
    public function getDisplayName(): string;
    public function isConfigured(): bool;
    public function initiatePayment(Order $order, Request $request): array;
    public function verifyPayment(Request $request): array;
    public function refundPayment(Order $order, float $amount, ?string $reason = null): array;
}
```

- **Registered Implementations**:
  - `CashOnDeliveryGateway`: Default local handler (`isConfigured() === true`).
  - `SslCommerzPaymentGateway`: Official SSLCOMMERZ v4 integration.
  - `BkashPaymentGateway`: Official bKash Tokenized Checkout v1.2.0-beta.

### 2.2 Courier Service Contract (`CourierServiceInterface`)
Location: `app/Contracts/CourierServiceInterface.php`

```php
interface CourierServiceInterface
{
    public function getName(): string;
    public function getDisplayName(): string;
    public function isConfigured(): bool;
    public function createConsignment(Order $order): array;
    public function trackConsignment(string $trackingId): array;
    public function cancelConsignment(string $trackingId, ?string $reason = null): array;
}
```

- **Registered Implementations**:
  - `MockCourierService`: Sandbox/Mock adapter, generating deterministic consignment IDs (`CSG-XXXXXX`) and tracking codes (`TRK-BD-XXXX`). Always functional for testing.
  - `SteadfastCourierService`: Official Steadfast Courier API v1 integration.
  - `PathaoCourierService`: Official Pathao Courier Aladdin API v1 integration.

---

## 3. Bangladesh Payment Gateways — Official Specification & Plan

### 3.1 SSLCOMMERZ (v4 Hosted Checkout)

#### Official API Specifications
- **Documentation Reference**: SSLCOMMERZ Official Developer Documentation (v4.00)
- **Session Initiation URL (Sandbox)**: `https://sandbox.sslcommerz.com/gwprocess/v4/api.php`
- **Session Initiation URL (Live)**: `https://securepay.sslcommerz.com/gwprocess/v4/api.php`
- **Validation URL (Sandbox)**: `https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php`
- **Validation URL (Live)**: `https://securepay.sslcommerz.com/validator/api/validationserverAPI.php`

#### Merchant Credentials & Environment Variables
```env
# SSLCOMMERZ Configuration
SSLCOMMERZ_STORE_ID=your_store_id
SSLCOMMERZ_STORE_PASSWORD=your_store_passwd
SSLCOMMERZ_SANDBOX=true
SSLCOMMERZ_SUCCESS_URL=/payment/callback/sslcommerz
SSLCOMMERZ_FAIL_URL=/payment/callback/sslcommerz
SSLCOMMERZ_CANCEL_URL=/payment/callback/sslcommerz
SSLCOMMERZ_IPN_URL=/payment/ipn/sslcommerz
```

#### Verification & Idempotency Workflow
1. **Initiation**: The customer selects SSLCOMMERZ at checkout. ShopBD compiles required parameters (`store_id`, `store_passwd`, `total_amount`, `currency="BDT"`, `tran_id="TXN-ORDERNO-TIMESTAMP"`, `cus_name`, `cus_phone`, `cus_add1`, `cus_city`) and sends a server-to-server POST request to the initiation URL.
2. **Redirection**: On HTTP 200 with status `SUCCESS`, ShopBD redirects the customer's browser to the returned `GatewayPageURL`.
3. **Server-Side Validation**:
   - When the customer returns via POST to `/payment/callback/sslcommerz` or SSLCOMMERZ dispatches an Instant Payment Notification (IPN) to `/payment/ipn/sslcommerz`:
   - ShopBD extracts `val_id` and queries the **SSLCOMMERZ Order Validation API** (`validationserverAPI.php`).
   - ShopBD verifies:
     - `status === 'VALID' || status === 'VALIDATED'`
     - Returned `currency === 'BDT'`
     - Returned `amount` matches `$order->total_amount` exactly within 0.01 tolerance.
     - `tran_id` matches the recorded order transaction.
4. **Idempotent Settlement**:
   - If `$order->payment_status === 'paid'`, the transaction is already settled; ShopBD acknowledges the callback immediately without creating redundant ledger records.
   - If unpaid, ShopBD marks `$order->payment_status = 'paid'`, records `gateway_transaction_id = $val_id`, and stores a completed `Payment` audit record inside a database transaction.

---

### 3.2 bKash Merchant Gateway (Tokenized Checkout v1.2.0-beta)

#### Official API Specifications
- **Documentation Reference**: bKash Developer Portal (Tokenized Checkout API v1.2.0-beta)
- **Base URL (Sandbox)**: `https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout`
- **Base URL (Live)**: `https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout`

#### Merchant Credentials & Environment Variables
```env
# bKash Merchant Configuration
BKASH_APP_KEY=your_app_key
BKASH_APP_SECRET=your_app_secret
BKASH_USERNAME=your_merchant_username
BKASH_PASSWORD=your_merchant_password
BKASH_SANDBOX=true
BKASH_CALLBACK_URL=/payment/callback/bkash
```

#### Tokenized Lifecycle Workflow
1. **Grant Token**: ShopBD requests an authentication token using `POST /tokenized/checkout/token/grant` with `app_key` and `app_secret`. Tokens are cached within their TTL (typically 3600 seconds).
2. **Create Payment**: ShopBD requests `POST /tokenized/checkout/create` specifying:
   - `amount`: `$order->total_amount`
   - `currency`: `"BDT"`
   - `intent`: `"sale"`
   - `merchantInvoiceNumber`: `$order->order_number`
   - `callbackURL`: `url('/payment/callback/bkash')`
3. **Execution & Settlement**:
   - The customer enters their bKash PIN / OTP on the hosted checkout page.
   - Upon return to `/payment/callback/bkash`, ShopBD receives `paymentID` and `status=success`.
   - ShopBD executes `POST /tokenized/checkout/execute` using the server-side bearer token to capture the payment.
   - ShopBD verifies `transactionStatus === 'Completed'`, currency is `BDT`, and captured amount matches order total.
   - Idempotently creates payment record and updates order.

---

## 4. Courier Logistics Integrations — Official Specification & Plan

### 4.1 Steadfast Courier (API v1)

#### Official API Specifications
- **Documentation Reference**: Steadfast Courier Merchant API Docs
- **Base URL**: `https://portal.steadfast.com.bd/api/v1`
- **Headers**:
  - `Api-Key: {STEADFAST_API_KEY}`
  - `Secret-Key: {STEADFAST_SECRET_KEY}`
  - `Content-Type: application/json`

#### Merchant Credentials & Environment Variables
```env
# Steadfast Courier Configuration
STEADFAST_API_KEY=your_steadfast_api_key
STEADFAST_SECRET_KEY=your_steadfast_secret_key
STEADFAST_BASE_URL=https://portal.steadfast.com.bd/api/v1
```

#### Dispatch & Tracking Workflow
1. **Consignment Creation**:
   - Admin clicks **Create Consignment & Dispatch** on Order Management page.
   - ShopBD posts to `POST https://portal.steadfast.com.bd/api/v1/create_order`:
     ```json
     {
       "invoice": "ORD-20261011-XXXX",
       "recipient_name": "Customer Name",
       "recipient_phone": "017XXXXXXXX",
       "recipient_address": "House 12, Road 4, Banani, Dhaka",
       "cod_amount": 1450.00,
       "note": "Handle with care. Optional customer delivery notes."
     }
     ```
   - On success (`status === 200`), Steadfast returns `consignment_id` and `tracking_code`.
   - ShopBD updates the order:
     - `courier_provider = 'steadfast'`
     - `courier_tracking_id = {tracking_code}`
     - `courier_consignment_id = {consignment_id}`
     - `courier_status = 'pending'`
     - Automatically transitions order status from `processing` to `shipped`.
2. **Status Tracking**:
   - Admin or automated worker calls `GET /api/v1/status_by_invoice/{invoice}` or `status_by_cid/{consignment_id}`.
   - Maps status (`pending`, `in_review`, `delivered`, `cancelled`) to ShopBD order tracking attributes.

---

### 4.2 Pathao Courier (Aladdin API v1)

#### Official API Specifications
- **Documentation Reference**: Pathao Courier Merchant Developer Documentation
- **Base URL (Sandbox)**: `https://courier-api-sandbox.pathao.com/aladdin/api/v1`
- **Base URL (Live)**: `https://api.pathao.com/aladdin/api/v1`

#### Merchant Credentials & Environment Variables
```env
# Pathao Courier Configuration
PATHAO_CLIENT_ID=your_client_id
PATHAO_CLIENT_SECRET=your_client_secret
PATHAO_USERNAME=your_merchant_email
PATHAO_PASSWORD=your_merchant_password
PATHAO_STORE_ID=your_pickup_store_id
PATHAO_SANDBOX=true
```

#### OAuth2 & Order Creation Workflow
1. **OAuth Token Grant**:
   - ShopBD authenticates via `POST /issue-token` with client credentials and merchant password. Bearer token is cached in Laravel Cache for 24 hours.
2. **Order Creation**:
   - Posts consignment payload to `POST /aladdin/api/v1/orders`:
     - `store_id`: Pickup store identifier.
     - `merchant_order_id`: `$order->order_number`
     - `recipient_name`: Customer name
     - `recipient_phone`: Customer mobile (01XXXXXXXXX)
     - `recipient_address`: Full delivery address
     - `recipient_city` / `recipient_zone` / `recipient_area`: Pathao City/Zone IDs.
     - `delivery_type`: 48 (Normal) or 12 (On-demand/Express)
     - `item_type`: 1 (Document) or 2 (Parcel)
     - `amount_to_collect`: Order grand total for COD, or 0 for prepaid orders.
3. **Consignment Response**:
   - Pathao returns `consignment_id` which is stored in ShopBD alongside tracking status.

---

## 5. Security & Risk Prevention Checklist

| Threat / Risk | ShopBD Mitigation Mechanism |
| :--- | :--- |
| **Tampered Callback Amounts** | `SslCommerzPaymentGateway` and `PaymentGatewayManager` explicitly validate returned `amount` and `currency` against database `$order->total_amount`. Any mismatch rejects the payment and logs a critical security alert. |
| **Duplicate / Replay Attacks** | Callbacks check if `$order->payment_status === 'paid'` or if an existing transaction matching `transaction_id` exists before processing. Repeated webhooks respond idempotently. |
| **Card Data Breach (PCI-DSS)** | No card numbers, expiry dates, or CVVs ever pass through or touch ShopBD servers. Payments use hosted redirects or provider iframes. |
| **CSRF in Public Webhooks** | Public IPN and callback routes (`/payment/callback/*`, `/payment/ipn/*`) are exempted from CSRF token verification in `bootstrap/app.php`, but are cryptographically verified via server-to-server query verification (`val_id` verification). |
| **Credential Leakage** | All API secrets, passwords, and tokens are stored in `.env` and accessed through `config('shopbd.*')`. No credentials are committed to version control. |
| **Third-Party Outage** | External HTTP calls use timeouts (5-10 seconds) and safe try-catch blocks. If a courier API is down, an error message is safely recorded in `courier_error` without crashing checkout or admin panels. |

---

## 6. Local Testing & Verification Guide

### 6.1 Testing Cash on Delivery (Always Functional)
1. Add products to cart and proceed to `/checkout`.
2. Select **Cash on Delivery (COD)**.
3. Submit checkout form.
4. Verify order is created with `order_status = 'pending'` and `payment_status = 'unpaid'`.

### 6.2 Testing Courier Dispatch with Mock Adapter
1. Log into admin dashboard at `/admin/orders`.
2. Open any order in `Confirmed` or `Processing` status.
3. In the **Courier Logistics** card, select **Mock Test Courier** and click **Create Consignment & Dispatch**.
4. Confirm:
   - Order transitions to `SHIPPED`.
   - A tracking code matching `TRK-BD-XXXX` is generated.
   - Click **Refresh Tracking Status** to view updated logistics status.
   - Log into the customer portal at `/customer/orders/{orderNumber}` and verify the shipment banner is displayed with the tracking ID.

### 6.3 Testing Live Sandbox Gateways (When Credentials Available)
1. Set `SSLCOMMERZ_STORE_ID=testbox` and `SSLCOMMERZ_STORE_PASSWORD=qwerty` in `.env`.
2. Set `SSLCOMMERZ_SANDBOX=true`.
3. At checkout, select **SSLCOMMERZ**.
4. Submit form and verify browser redirects to the official SSLCOMMERZ sandbox payment gateway page.
5. On the test gateway, choose a sandbox payment method (e.g. Test Card, bKash Sandbox).
6. Complete transaction and verify redirect back to `/order/confirmation/{orderNumber}` with verified payment status.
