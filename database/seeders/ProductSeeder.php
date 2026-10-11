<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all()->keyBy('name');
        $brands = Brand::all()->keyBy('name');
        $customer = User::where('role', 'customer')->first();
        $admin = User::where('role', 'admin')->first();

        $products = [
            // 1. Clothing
            [
                'name' => 'Premium Men\'s Semi-Fitted Cotton Panjabi',
                'category' => 'Men\'s Panjabi & Kurta',
                'brand' => 'Aarong',
                'sku' => 'CLO-AAR-PAN-01',
                'short_description' => 'Fine 100% jacquard cotton Panjabi with intricate thread embroidery on collar and placket.',
                'description' => '<p>Crafted from breathable, high-grade combed cotton. Tailored for festivals, Friday prayers, and formal gatherings in Bangladesh. Features snap-button cuffs and tailored side pockets.</p>',
                'purchase_price' => 1400.00,
                'regular_price' => 2450.00,
                'sale_price' => 2190.00,
                'stock' => 45,
                'is_featured' => true,
            ],
            [
                'name' => 'Men\'s Slim Fit 100% Oxford Cotton Shirt',
                'category' => 'Men\'s Casual Shirts',
                'brand' => 'Yellow',
                'sku' => 'CLO-YEL-SHT-02',
                'short_description' => 'Smart business casual long sleeve Oxford cotton shirt with button-down collar.',
                'description' => '<p>Designed for daily office and smart casual wear in hot & humid climates. Pre-shrunk pure cotton fabric with reinforced stitching.</p>',
                'purchase_price' => 850.00,
                'regular_price' => 1550.00,
                'sale_price' => null,
                'stock' => 60,
                'is_featured' => true,
            ],
            [
                'name' => 'Traditional Handloom Pure Silk Jamdani Saree',
                'category' => 'Women\'s Sarees & Salwar Kameez',
                'brand' => 'Aarong',
                'sku' => 'CLO-AAR-JAM-03',
                'short_description' => 'Exquisite Dhakai Jamdani handloom silk saree with authentic floral motifs and golden zari border.',
                'description' => '<p>Woven by traditional artisans of Narayanganj. Ideal for weddings, cultural festivals, and Pahela Baishakh celebrations.</p>',
                'purchase_price' => 6200.00,
                'regular_price' => 9850.00,
                'sale_price' => 8900.00,
                'stock' => 15,
                'is_featured' => true,
            ],

            // 2. Electronics
            [
                'name' => 'Walton Primo S9 5G (8GB RAM / 128GB ROM)',
                'category' => 'Smartphones & Tablets',
                'brand' => 'Walton',
                'sku' => 'ELE-WLT-S9-01',
                'short_description' => '6.6" FHD+ 120Hz AMOLED display, 50MP triple AI camera, and 5000mAh battery with 33W fast charging.',
                'description' => '<p>Engineered and manufactured in Bangladesh with 1-year official brand warranty and nationwide service coverage.</p>',
                'purchase_price' => 14500.00,
                'regular_price' => 18999.00,
                'sale_price' => 17499.00,
                'stock' => 30,
                'is_featured' => true,
            ],
            [
                'name' => 'Samsung Galaxy Fit3 Smart Fitness Band',
                'category' => 'Smart Watches & Fitness Bands',
                'brand' => 'Samsung',
                'sku' => 'ELE-SAM-FIT3-02',
                'short_description' => '1.6" AMOLED color display with sleep tracking, 100+ workout modes, and 13-day battery backup.',
                'description' => '<p>Aluminum body, 5ATM water resistance, fall detection, and seamless Samsung Health integration.</p>',
                'purchase_price' => 4800.00,
                'regular_price' => 6499.00,
                'sale_price' => 5990.00,
                'stock' => 25,
                'is_featured' => true,
            ],
            [
                'name' => 'Walton Wireless Active Noise Cancelling Earbuds',
                'category' => 'Headphones & Earbuds',
                'brand' => 'Walton',
                'sku' => 'ELE-WLT-ANC-03',
                'short_description' => 'Bluetooth 5.3 true wireless earbuds with 32dB active noise cancellation and IPX4 sweat resistance.',
                'description' => '<p>Up to 30 hours total playtime with type-C fast charging case. Low latency mode for mobile gaming.</p>',
                'purchase_price' => 1200.00,
                'regular_price' => 2190.00,
                'sale_price' => 1850.00,
                'stock' => 50,
                'is_featured' => false,
            ],

            // 3. Groceries
            [
                'name' => 'Radhuni Pure Mustard Oil (1 Litre Bottle)',
                'category' => 'Oil, Ghee & Spices',
                'brand' => 'Square Consumer Products',
                'sku' => 'GRO-SQR-OIL-01',
                'short_description' => '100% natural, pungent cold-pressed mustard oil extracted from selected mustard seeds.',
                'description' => '<p>Essential for Bangladeshi traditional curries, bhortas, and pickles with strong natural aroma and pungency.</p>',
                'purchase_price' => 280.00,
                'regular_price' => 350.00,
                'sale_price' => 330.00,
                'stock' => 120,
                'is_featured' => true,
            ],
            [
                'name' => 'PRAN Premium Chinigura Aromatic Rice (5 Kg)',
                'category' => 'Rice & Flours',
                'brand' => 'PRAN',
                'sku' => 'GRO-PRN-RICE-02',
                'short_description' => 'Aged premium aromatic Chinigura rice, perfect for fragrant Polao, Biryani, and Khichuri.',
                'description' => '<p>Triple-sorted long grain aromatic rice cultivated in northern Bangladesh. Guarantees non-sticky, fragrant cooking.</p>',
                'purchase_price' => 620.00,
                'regular_price' => 750.00,
                'sale_price' => 710.00,
                'stock' => 90,
                'is_featured' => true,
            ],
            [
                'name' => 'Taaza Premium CTC Black Tea (400g Foil Pack)',
                'category' => 'Tea, Coffee & Beverages',
                'brand' => 'Unilever Bangladesh',
                'sku' => 'GRO-UNI-TEA-03',
                'short_description' => 'Rich red liquor and strong taste blended from the finest tea gardens of Sylhet & Sreemangal.',
                'description' => '<p>Packed in freshness-sealed tri-laminated foil for daily energizing milk tea (Doodh Cha).</p>',
                'purchase_price' => 190.00,
                'regular_price' => 240.00,
                'sale_price' => null,
                'stock' => 150,
                'is_featured' => false,
            ],

            // 4. Home Appliances
            [
                'name' => 'Singer 3-in-1 Heavy Duty Blender & Mixer 750W',
                'category' => 'Blenders & Mixers',
                'brand' => 'Singer Bangladesh',
                'sku' => 'APP-SNG-BLD-01',
                'short_description' => '750-watt 100% copper motor with 3 stainless steel jars for wet grinding, dry grinding, and chutneys.',
                'description' => '<p>Built to grind tough Bangladeshi spices including raw turmeric, ginger, and garlic paste without motor overheating.</p>',
                'purchase_price' => 2900.00,
                'regular_price' => 4200.00,
                'sale_price' => 3750.00,
                'stock' => 35,
                'is_featured' => true,
            ],
            [
                'name' => 'Walton 2.8L Deluxe Automatic Rice Cooker',
                'category' => 'Rice Cookers',
                'brand' => 'Walton',
                'sku' => 'APP-WLT-RC-02',
                'short_description' => 'Double non-stick pots with automatic keep-warm function and steamer tray included.',
                'description' => '<p>Cooks rice evenly for up to 10 persons. Energy-efficient 1000W heating plate with safety thermal fuse.</p>',
                'purchase_price' => 2100.00,
                'regular_price' => 2950.00,
                'sale_price' => 2690.00,
                'stock' => 40,
                'is_featured' => false,
            ],

            // 5. Accessories
            [
                'name' => 'Apex Men\'s Genuine Leather Bi-Fold Wallet',
                'category' => 'Genuine Leather Wallets',
                'brand' => 'Apex',
                'sku' => 'ACC-APX-WLT-01',
                'short_description' => 'Full-grain genuine cow leather wallet with 8 card slots, ID window, and dual currency compartments.',
                'description' => '<p>Crafted in Bangladesh from vegetable-tanned genuine leather. Slim profile designed to easily fit in front and rear pockets.</p>',
                'purchase_price' => 700.00,
                'regular_price' => 1290.00,
                'sale_price' => 1090.00,
                'stock' => 55,
                'is_featured' => true,
            ],
            [
                'name' => 'Water-Resistant College & Laptop Backpack 25L',
                'category' => 'Backpacks & Travel Bags',
                'brand' => 'Apex',
                'sku' => 'ACC-APX-BAG-02',
                'short_description' => 'Ergonomic 25L capacity backpack with padded 15.6" laptop compartment and USB pass-through port.',
                'description' => '<p>Durable 900D Oxford nylon exterior with water-repellent coating. High-density breathable mesh back panel.</p>',
                'purchase_price' => 1100.00,
                'regular_price' => 1850.00,
                'sale_price' => 1590.00,
                'stock' => 40,
                'is_featured' => false,
            ],

            // 6. Beauty Products
            [
                'name' => 'Glow & Lovely Advanced Multivitamin Daily Cream (100g)',
                'category' => 'Skincare & Moisturisers',
                'brand' => 'Unilever Bangladesh',
                'sku' => 'BEA-UNI-GLW-01',
                'short_description' => 'Clinically proven multivitamin daily brightening moisturizer with UVA/UVB sun protection.',
                'description' => '<p>Enriched with vitamins B3, C, and E. Leaves non-greasy matte finish for humid tropical climates.</p>',
                'purchase_price' => 210.00,
                'regular_price' => 275.00,
                'sale_price' => null,
                'stock' => 80,
                'is_featured' => false,
            ],

            // 7. Books
            [
                'name' => 'Shesher Kobita (The Last Poem) by Rabindranath Tagore',
                'category' => 'Bengali Fiction & Literature',
                'brand' => null,
                'sku' => 'BOK-GEN-SHK-01',
                'short_description' => 'Hardcover collector\'s edition of Rabindranath Tagore\'s celebrated tragic-romantic novel.',
                'description' => '<p>Deluxe 120-page offset paper edition with authentic original typography and timeless romantic poetry.</p>',
                'purchase_price' => 180.00,
                'regular_price' => 300.00,
                'sale_price' => 255.00,
                'stock' => 70,
                'is_featured' => false,
            ],

            // 8. General Products
            [
                'name' => 'Matador Pinpoint Ballpoint Pens (Pack of 20 - Black/Blue)',
                'category' => 'Stationery & Desk Organizers',
                'brand' => null,
                'sku' => 'GEN-MTD-PEN-01',
                'short_description' => 'Ultra-smooth 0.6mm tungsten carbide ballpoint pens for effortless everyday writing.',
                'description' => '<p>The most popular daily writing pen in schools, universities, and commercial offices across Bangladesh.</p>',
                'purchase_price' => 110.00,
                'regular_price' => 160.00,
                'sale_price' => 145.00,
                'stock' => 200,
                'is_featured' => false,
            ],
        ];

        foreach ($products as $prodData) {
            $cat = $categories->get($prodData['category']) ?? Category::first();
            $brand = isset($prodData['brand']) ? $brands->get($prodData['brand']) : null;

            $product = Product::updateOrCreate(
                ['sku' => $prodData['sku']],
                [
                    'category_id' => $cat->id,
                    'brand_id' => $brand?->id,
                    'name' => $prodData['name'],
                    'slug' => Str::slug($prodData['name']),
                    'short_description' => $prodData['short_description'],
                    'description' => $prodData['description'],
                    'purchase_price' => $prodData['purchase_price'],
                    'regular_price' => $prodData['regular_price'],
                    'sale_price' => $prodData['sale_price'],
                    'stock_quantity' => $prodData['stock'],
                    'low_stock_threshold' => 5,
                    'is_featured' => $prodData['is_featured'],
                    'is_active' => true,
                ]
            );

            // Add demo primary and secondary image
            ProductImage::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'sort_order' => 1,
                ],
                [
                    'image_path' => "products/{$product->sku}-main.jpg",
                    'is_primary' => true,
                ]
            );

            ProductImage::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'sort_order' => 2,
                ],
                [
                    'image_path' => "products/{$product->sku}-alt.jpg",
                    'is_primary' => false,
                ]
            );

            // Add initial inventory movement log
            InventoryMovement::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'type' => 'purchase',
                    'quantity_change' => $prodData['stock'],
                ],
                [
                    'order_id' => null,
                    'previous_stock' => 0,
                    'current_stock' => $prodData['stock'],
                    'remarks' => 'Initial opening inventory seed',
                    'created_by_user_id' => $admin?->id,
                ]
            );

            // Add sample review if customer exists
            if ($customer && $product->is_featured) {
                Review::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'user_id' => $customer->id,
                    ],
                    [
                        'rating' => 5,
                        'comment' => 'Excellent quality product! Fast delivery in Dhaka, highly recommended.',
                        'is_approved' => true,
                    ]
                );
            }
        }
    }
}
