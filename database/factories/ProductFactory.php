<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $regularPrice = fake()->randomFloat(2, 300, 15000);
        $purchasePrice = round($regularPrice * 0.65, 2);
        $hasDiscount = fake()->boolean(40);
        $salePrice = $hasDiscount ? round($regularPrice * 0.85, 2) : null;

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name) . '-' . Str::random(5),
            'sku' => 'SBD-' . strtoupper(Str::random(8)),
            'short_description' => fake()->paragraph(1),
            'description' => fake()->paragraphs(3, true),
            'purchase_price' => $purchasePrice,
            'regular_price' => $regularPrice,
            'sale_price' => $salePrice,
            'stock_quantity' => fake()->numberBetween(10, 150),
            'low_stock_threshold' => 5,
            'is_featured' => fake()->boolean(25),
            'is_active' => true,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => [
            'stock_quantity' => 0,
        ]);
    }
}
