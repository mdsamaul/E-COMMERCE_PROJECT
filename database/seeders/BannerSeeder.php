<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Festive Mega Sale 2026',
                'subtitle' => 'Save up to 40% on Panjabis, Jamdani Sarees & Fashion accessories with Cash on Delivery across Bangladesh.',
                'image_path' => 'banners/hero-fashion-eid.jpg',
                'button_text' => 'Shop Clothing',
                'button_url' => '/category/clothing',
                'placement' => 'hero_slider',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Original Electronics & Gadgets',
                'subtitle' => 'Official Walton & Samsung smartphones and smartwatches with 1-year brand warranty.',
                'image_path' => 'banners/hero-electronics.jpg',
                'button_text' => 'Explore Gadgets',
                'button_url' => '/category/electronics',
                'placement' => 'hero_slider',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Pure Groceries Delivered in 2 Hours',
                'subtitle' => 'Chinigura Aromatic Rice, Pure Mustard Oil, Spices & Tea at wholesale rates in Dhaka.',
                'image_path' => 'banners/hero-groceries.jpg',
                'button_text' => 'Order Groceries',
                'button_url' => '/category/groceries',
                'placement' => 'hero_slider',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Home & Kitchen Appliances',
                'subtitle' => 'Heavy duty blenders, rice cookers & electric kettles with instant service warranty.',
                'image_path' => 'banners/promo-appliances.jpg',
                'button_text' => 'View Deals',
                'button_url' => '/category/home-appliances',
                'placement' => 'promo_card',
                'sort_order' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::updateOrCreate(
                [
                    'title' => $banner['title'],
                    'placement' => $banner['placement'],
                ],
                $banner
            );
        }
    }
}
