<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Walton',
                'description' => 'Leading Bangladeshi electronics, smartphone, and electrical appliance brand.',
            ],
            [
                'name' => 'Aarong',
                'description' => 'Iconic Bangladeshi ethical fashion, handloom clothing, and lifestyle enterprise.',
            ],
            [
                'name' => 'Apex',
                'description' => 'Premier Bangladeshi footwear and genuine leather accessories manufacturer.',
            ],
            [
                'name' => 'Samsung',
                'description' => 'World-renowned consumer electronics, displays, and Galaxy mobile phones.',
            ],
            [
                'name' => 'Square Consumer Products',
                'description' => 'Trusted provider of Radhuni spices, Ruchi snacks, and pure food products.',
            ],
            [
                'name' => 'PRAN',
                'description' => 'Bangladesh’s largest food, confectionery, beverage, and agricultural brand.',
            ],
            [
                'name' => 'Yellow',
                'description' => 'Trendy youth apparel, casual clothing, and smart formal wear by Beximco.',
            ],
            [
                'name' => 'Singer Bangladesh',
                'description' => 'Historic household appliance and kitchen electronics brand in Bangladesh.',
            ],
            [
                'name' => 'Unilever Bangladesh',
                'description' => 'Top personal care, beauty, shampoo, and personal hygiene products.',
            ],
            [
                'name' => 'ACI Pure',
                'description' => 'Pure salt, flours, edible oils, and essential household groceries.',
            ],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['slug' => Str::slug($brand['name'])],
                [
                    'name' => $brand['name'],
                    'description' => $brand['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
