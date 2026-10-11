<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        $isPercentage = fake()->boolean(50);
        return [
            'code' => strtoupper(Str::random(8)),
            'type' => $isPercentage ? 'percentage' : 'fixed',
            'value' => $isPercentage ? fake()->randomElement([5, 10, 15, 20]) : fake()->randomElement([100, 200, 500]),
            'min_order_amount' => fake()->randomElement([500, 1000, 2000]),
            'max_discount_amount' => $isPercentage ? 500.00 : null,
            'usage_limit_per_coupon' => 100,
            'usage_limit_per_user' => 1,
            'total_used' => 0,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(30),
            'is_active' => true,
        ];
    }
}
