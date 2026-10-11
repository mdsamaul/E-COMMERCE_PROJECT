<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General
            ['group' => 'general', 'key' => 'store_name', 'value' => 'ShopBD', 'type' => 'string', 'description' => 'E-Commerce Website Name'],
            ['group' => 'general', 'key' => 'store_tagline', 'value' => 'Best Online Shopping Experience in Bangladesh', 'type' => 'string', 'description' => 'Site slogan'],
            ['group' => 'general', 'key' => 'store_phone', 'value' => '+880 1700-000000', 'type' => 'string', 'description' => 'Customer helpline phone number'],
            ['group' => 'general', 'key' => 'store_email', 'value' => 'support@shopbd.com', 'type' => 'string', 'description' => 'Support email address'],
            ['group' => 'general', 'key' => 'store_address', 'value' => 'Motijheel Commercial Area, Dhaka-1000, Bangladesh', 'type' => 'string', 'description' => 'Office address'],

            // Shipping
            ['group' => 'shipping', 'key' => 'inside_dhaka_delivery_fee', 'value' => '60.00', 'type' => 'number', 'description' => 'Standard delivery charge inside Dhaka city in BDT (৳)'],
            ['group' => 'shipping', 'key' => 'outside_dhaka_delivery_fee', 'value' => '120.00', 'type' => 'number', 'description' => 'Standard delivery charge outside Dhaka in BDT (৳)'],
            ['group' => 'shipping', 'key' => 'free_shipping_threshold', 'value' => '2500.00', 'type' => 'number', 'description' => 'Order total above which shipping is free (0 to disable)'],

            // Localization
            ['group' => 'localization', 'key' => 'currency_code', 'value' => 'BDT', 'type' => 'string', 'description' => 'Store primary currency'],
            ['group' => 'localization', 'key' => 'currency_symbol', 'value' => '৳', 'type' => 'string', 'description' => 'Currency symbol shown in UI'],

            // Payment
            ['group' => 'payment', 'key' => 'cod_enabled', 'value' => '1', 'type' => 'boolean', 'description' => 'Cash on Delivery payment status'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
