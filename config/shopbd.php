<?php

return [
    'currency' => env('APP_CURRENCY', 'BDT'),
    'currency_symbol' => env('APP_CURRENCY_SYMBOL', '৳'),

    'shipping' => [
        'inside_dhaka_cost' => (float) env('SHIPPING_INSIDE_DHAKA', 60.00),
        'outside_dhaka_cost' => (float) env('SHIPPING_OUTSIDE_DHAKA', 120.00),
    ],

    'order_statuses' => [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'returned' => 'Returned',
    ],

    'payment_methods' => [
        'cod' => 'Cash on Delivery (COD)',
        'sslcommerz' => 'SSLCOMMERZ (Cards / Mobile Banking / Net Banking)',
        'bkash' => 'bKash Merchant Payment',
    ],

    'payment' => [
        'default' => env('PAYMENT_DEFAULT_METHOD', 'cod'),

        'sslcommerz' => [
            'store_id' => env('SSLCZ_STORE_ID'),
            'store_password' => env('SSLCZ_STORE_PASSWORD'),
            'sandbox' => env('SSLCZ_TESTMODE', true),
            'api_domain' => env('SSLCZ_TESTMODE', true) 
                ? 'https://sandbox.sslcommerz.com' 
                : 'https://securepay.sslcommerz.com',
        ],

        'bkash' => [
            'app_key' => env('BKASH_APP_KEY'),
            'app_secret' => env('BKASH_APP_SECRET'),
            'username' => env('BKASH_USERNAME'),
            'password' => env('BKASH_PASSWORD'),
            'sandbox' => env('BKASH_SANDBOX', true),
            'base_url' => env('BKASH_SANDBOX', true) 
                ? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta' 
                : 'https://tokenized.pay.bka.sh/v1.2.0-beta',
        ],
    ],

    'courier' => [
        'default' => env('COURIER_DEFAULT_PROVIDER', 'mock'),

        'mock' => [
            'prefix' => 'TRK-BD-',
        ],

        'steadfast' => [
            'api_key' => env('STEADFAST_API_KEY'),
            'secret_key' => env('STEADFAST_SECRET_KEY'),
            'base_url' => env('STEADFAST_BASE_URL', 'https://portal.steadfast.com.bd/api/v1'),
        ],

        'pathao' => [
            'client_id' => env('PATHAO_CLIENT_ID'),
            'client_secret' => env('PATHAO_CLIENT_SECRET'),
            'username' => env('PATHAO_USERNAME'),
            'password' => env('PATHAO_PASSWORD'),
            'store_id' => env('PATHAO_STORE_ID'),
            'sandbox' => env('PATHAO_SANDBOX', true),
            'base_url' => env('PATHAO_SANDBOX', true)
                ? 'https://courier-api-sandbox.pathao.com'
                : 'https://api-hermes.pathao.com',
        ],
    ],

    'pagination' => [
        'products_per_page' => 12,
        'admin_table_rows' => 15,
    ],

    'store' => [
        'name' => env('APP_NAME', 'ShopBD'),
        'tagline' => 'Best Online Shopping in Bangladesh',
        'phone' => env('STORE_PHONE', '+880 1700-000000'),
        'email' => env('STORE_EMAIL', 'support@shopbd.com'),
        'address' => env('STORE_ADDRESS', 'Dhaka, Bangladesh'),
    ],
];
