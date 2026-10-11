<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'EID2026',
                'type' => 'percentage',
                'value' => 10.00,
                'min_order_amount' => 1500.00,
                'max_discount_amount' => 500.00,
                'usage_limit_per_coupon' => 500,
                'usage_limit_per_user' => 1,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addDays(60),
                'is_active' => true,
            ],
            [
                'code' => 'WELCOME10',
                'type' => 'percentage',
                'value' => 10.00,
                'min_order_amount' => 800.00,
                'max_discount_amount' => 200.00,
                'usage_limit_per_coupon' => 1000,
                'usage_limit_per_user' => 1,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addYear(),
                'is_active' => true,
            ],
            [
                'code' => 'FREE60',
                'type' => 'fixed',
                'value' => 60.00,
                'min_order_amount' => 1000.00,
                'max_discount_amount' => null,
                'usage_limit_per_coupon' => 200,
                'usage_limit_per_user' => 2,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addDays(90),
                'is_active' => true,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(
                ['code' => $coupon['code']],
                $coupon
            );
        }
    }
}
