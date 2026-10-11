<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 500, 10000);
        $shipping = 60.00;
        $discount = 0.00;

        return [
            'order_number' => 'ORD-' . strtoupper(fake()->unique()->bothify('##??####')),
            'user_id' => User::factory(),
            'recipient_name' => fake()->name(),
            'phone' => '017' . fake()->numerify('########'),
            'delivery_address' => fake()->streetAddress() . ', Dhaka',
            'division' => 'Dhaka',
            'district' => 'Dhaka',
            'shipping_zone' => 'inside_dhaka',
            'shipping_cost' => $shipping,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'total_amount' => $subtotal + $shipping - $discount,
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'order_status' => 'delivered',
        ];
    }
}
