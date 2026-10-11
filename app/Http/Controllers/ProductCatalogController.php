<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::active()
            ->with(['primaryImage', 'category', 'brand']);

        // 1. Search filter
        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('sku', 'like', "%{$keyword}%")
                  ->orWhere('short_description', 'like', "%{$keyword}%");
            });
        }

        // 2. Category filter
        if ($request->filled('category')) {
            $categorySlug = $request->input('category');
            $category = Category::where('slug', $categorySlug)->first();
            if ($category) {
                $categoryIds = $category->children()->pluck('id')->push($category->id);
                $query->whereIn('category_id', $categoryIds);
            }
        } elseif ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // 3. Brand filter
        if ($request->filled('brand')) {
            $brandSlug = $request->input('brand');
            $brand = Brand::where('slug', $brandSlug)->first();
            if ($brand) {
                $query->where('brand_id', $brand->id);
            }
        } elseif ($request->filled('brand_id')) {
            $query->where('brand_id', $request->input('brand_id'));
        }

        // 4. Price range filter
        if ($request->filled('min_price')) {
            $minPrice = (float) $request->input('min_price');
            $query->whereRaw('CAST(COALESCE(NULLIF(sale_price, 0), regular_price) AS REAL) >= ?', [$minPrice]);
        }

        if ($request->filled('max_price')) {
            $maxPrice = (float) $request->input('max_price');
            $query->whereRaw('CAST(COALESCE(NULLIF(sale_price, 0), regular_price) AS REAL) <= ?', [$maxPrice]);
        }

        // 5. In-stock filter
        if ($request->boolean('in_stock')) {
            $query->where('stock_quantity', '>', 0);
        }

        // 6. Sorting
        match ($request->input('sort')) {
            'price_low' => $query->orderBy(DB::raw('COALESCE(sale_price, regular_price)'), 'asc'),
            'price_high' => $query->orderBy(DB::raw('COALESCE(sale_price, regular_price)'), 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'popular' => $query->orderBy('is_featured', 'desc')->latest('id'),
            default => $query->latest('id'),
        };

        $products = $query->paginate(16)->withQueryString();

        // Facets for sidebar filtering
        $categories = Category::active()
            ->root()
            ->with(['children' => fn ($q) => $q->active()])
            ->withCount(['products' => fn ($q) => $q->active()])
            ->orderBy('sort_order')
            ->get();

        $brands = Brand::active()
            ->withCount(['products' => fn ($q) => $q->active()])
            ->orderBy('name')
            ->get();

        return view('frontend.products.index', compact('products', 'categories', 'brands'));
    }

    public function show(string $slug): View
    {
        $product = Product::active()
            ->where('slug', $slug)
            ->with(['category', 'brand', 'images', 'approvedReviews.user'])
            ->firstOrFail();

        // 4 Related products in the same category
        $relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['primaryImage', 'category', 'brand'])
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('frontend.products.show', compact('product', 'relatedProducts'));
    }

    public function category(string $slug): View
    {
        $category = Category::active()
            ->where('slug', $slug)
            ->with(['children' => fn ($q) => $q->active()])
            ->firstOrFail();

        $categoryIds = $category->children->pluck('id')->push($category->id);

        $products = Product::active()
            ->whereIn('category_id', $categoryIds)
            ->with(['primaryImage', 'category', 'brand'])
            ->latest('id')
            ->paginate(16);

        return view('frontend.products.category', compact('category', 'products'));
    }

    public function brand(string $slug): View
    {
        $brand = Brand::active()
            ->where('slug', $slug)
            ->firstOrFail();

        $products = Product::active()
            ->where('brand_id', $brand->id)
            ->with(['primaryImage', 'category', 'brand'])
            ->latest('id')
            ->paginate(16);

        return view('frontend.products.brand', compact('brand', 'products'));
    }
}
