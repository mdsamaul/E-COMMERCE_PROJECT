<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create or update Default Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@shopbd.com'],
            [
                'name' => 'ShopBD Super Admin',
                'phone' => '01711000001',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 2. Create or update Demo Customer
        $customer = User::updateOrCreate(
            ['email' => 'customer@shopbd.com'],
            [
                'name' => 'Rahim Ahmed',
                'phone' => '01712000002',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Add default address for demo customer
        Address::updateOrCreate(
            [
                'user_id' => $customer->id,
                'is_default' => true,
            ],
            [
                'recipient_name' => 'Rahim Ahmed',
                'phone' => '01712000002',
                'division' => 'Dhaka',
                'district' => 'Dhaka',
                'upazila_thana' => 'Dhanmondi',
                'address_line' => 'House #24, Road #7/A, Dhanmondi Residential Area',
                'delivery_notes' => 'Please call before delivery. 4th floor.',
                'is_default' => true,
            ]
        );
    }
}
