<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // 1. Hero Sliders & Promo Cards
        $heroBanners = Banner::active()
            ->placement('hero_slider')
            ->orderBy('sort_order')
            ->get();

        $promoCards = Banner::active()
            ->placement('promo_card')
            ->orderBy('sort_order')
            ->take(3)
            ->get();

        // 2. Featured Categories
        $featuredCategories = Category::active()
            ->featured()
            ->withCount(['products' => fn ($q) => $q->active()])
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        // 3. Flash Deals (active products with discounted sale price)
        $flashDeals = Product::active()
            ->inStock()
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'regular_price')
            ->with(['primaryImage', 'category', 'brand'])
            ->latest('id')
            ->take(8)
            ->get();

        // 4. Featured Products
        $featuredProducts = Product::active()
            ->featured()
            ->with(['primaryImage', 'category', 'brand'])
            ->latest('id')
            ->take(8)
            ->get();

        // 5. New Arrivals
        $newArrivals = Product::active()
            ->with(['primaryImage', 'category', 'brand'])
            ->latest('id')
            ->take(8)
            ->get();

        // 6. Active Brands
        $brands = Brand::active()
            ->withCount(['products' => fn ($q) => $q->active()])
            ->orderBy('name')
            ->take(12)
            ->get();

        return view('frontend.home', compact(
            'heroBanners',
            'promoCards',
            'featuredCategories',
            'flashDeals',
            'featuredProducts',
            'newArrivals',
            'brands'
        ));
    }
}
