<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Clothing',
                'description' => 'Traditional and modern fashionable apparel for men, women, and kids in Bangladesh.',
                'is_featured' => true,
                'sort_order' => 1,
                'subcategories' => [
                    'Men\'s Panjabi & Kurta',
                    'Men\'s Casual Shirts',
                    'Women\'s Sarees & Salwar Kameez',
                    'Kids\' Fashion',
                ],
            ],
            [
                'name' => 'Electronics',
                'description' => 'Original smartphones, gadgets, audio devices, and computer accessories with official warranty.',
                'is_featured' => true,
                'sort_order' => 2,
                'subcategories' => [
                    'Smartphones & Tablets',
                    'Smart Watches & Fitness Bands',
                    'Headphones & Earbuds',
                    'Power Banks & Chargers',
                ],
            ],
            [
                'name' => 'Groceries',
                'description' => 'Daily essential grocery items, pure mustard oil, organic rice, lentils, and premium spices.',
                'is_featured' => true,
                'sort_order' => 3,
                'subcategories' => [
                    'Rice & Flours',
                    'Oil, Ghee & Spices',
                    'Tea, Coffee & Beverages',
                    'Snacks & Dairy',
                ],
            ],
            [
                'name' => 'Home Appliances',
                'description' => 'Kitchen and home appliances tailored for Bangladeshi households with energy saving efficiency.',
                'is_featured' => true,
                'sort_order' => 4,
                'subcategories' => [
                    'Blenders & Mixers',
                    'Electric Kettles',
                    'Rice Cookers',
                    'Irons & Steamers',
                ],
            ],
            [
                'name' => 'Accessories',
                'description' => 'Genuine leather goods, premium travel backpacks, belts, and fashion watches.',
                'is_featured' => true,
                'sort_order' => 5,
                'subcategories' => [
                    'Genuine Leather Wallets',
                    'Backpacks & Travel Bags',
                    'Sunglasses & Eyewear',
                ],
            ],
            [
                'name' => 'Beauty Products',
                'description' => 'Authentic skincare, herbal hair oils, personal hygiene, and fragrance products.',
                'is_featured' => true,
                'sort_order' => 6,
                'subcategories' => [
                    'Skincare & Moisturisers',
                    'Hair Oils & Shampoos',
                    'Perfumes & Attars',
                ],
            ],
            [
                'name' => 'Books',
                'description' => 'Popular Bengali literature, contemporary novels, self-development, and Islamic publications.',
                'is_featured' => false,
                'sort_order' => 7,
                'subcategories' => [
                    'Bengali Fiction & Literature',
                    'Islamic Literature',
                    'Self-Help & Business Books',
                ],
            ],
            [
                'name' => 'General Products',
                'description' => 'Everyday household sundries, office stationery, and utility lifestyle products.',
                'is_featured' => false,
                'sort_order' => 8,
                'subcategories' => [
                    'Stationery & Desk Organizers',
                    'Home Cleaning & Utility',
                ],
            ],
        ];

        foreach ($categories as $catData) {
            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($catData['name'])],
                [
                    'name' => $catData['name'],
                    'description' => $catData['description'],
                    'is_featured' => $catData['is_featured'],
                    'is_active' => true,
                    'sort_order' => $catData['sort_order'],
                ]
            );

            foreach ($catData['subcategories'] as $subOrder => $subName) {
                Category::updateOrCreate(
                    ['slug' => Str::slug($subName)],
                    [
                        'parent_id' => $parent->id,
                        'name' => $subName,
                        'description' => "Shop best {$subName} online in Bangladesh.",
                        'is_featured' => false,
                        'is_active' => true,
                        'sort_order' => $subOrder + 1,
                    ]
                );
            }
        }
    }
}
