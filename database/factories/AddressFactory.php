<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        $districts = ['Dhaka', 'Chittagong', 'Sylhet', 'Rajshahi', 'Khulna', 'Barisal', 'Rangpur', 'Mymensingh', 'Gazipur', 'Narayanganj'];
        $divisions = ['Dhaka', 'Chittagong', 'Sylhet', 'Rajshahi', 'Khulna', 'Barisal', 'Rangpur', 'Mymensingh'];

        return [
            'user_id' => User::factory(),
            'recipient_name' => fake()->name(),
            'phone' => '01' . fake()->randomElement(['7', '8', '9', '3', '5', '6']) . fake()->numerify('########'),
            'division' => fake()->randomElement($divisions),
            'district' => fake()->randomElement($districts),
            'upazila_thana' => fake()->city(),
            'address_line' => 'House #' . fake()->buildingNumber() . ', Road #' . fake()->numberBetween(1, 40) . ', Sector #' . fake()->numberBetween(1, 15),
            'delivery_notes' => fake()->optional()->sentence(),
            'is_default' => false,
        ];
    }
}
