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
