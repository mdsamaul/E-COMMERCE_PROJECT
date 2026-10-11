<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /**
     * Generate dynamic sitemap.xml for search engines.
     */
    public function sitemap(): Response
    {
        $products = Product::active()
            ->select('slug', 'updated_at')
            ->latest('updated_at')
            ->get();

        $categories = Category::active()
            ->select('slug', 'updated_at')
            ->orderBy('sort_order')
            ->get();

        $brands = Brand::active()
            ->select('slug', 'updated_at')
            ->orderBy('name')
            ->get();

        $staticRoutes = [
            ['url' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => route('products.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => route('pages.about'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['url' => route('pages.contact'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => route('pages.faq'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['url' => route('pages.shipping'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['url' => route('pages.return'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['url' => route('pages.privacy'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['url' => route('pages.terms'), 'priority' => '0.5', 'changefreq' => 'monthly'],
        ];

        return response()
            ->view('seo.sitemap', compact('products', 'categories', 'brands', 'staticRoutes'))
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    /**
     * Generate robots.txt directives.
     */
    public function robots(): Response
    {
        $sitemapUrl = url('/sitemap.xml');

        $content = implode("\n", [
            'User-agent: *',
            'Disallow: /admin/',
            'Disallow: /checkout/',
            'Disallow: /customer/',
            'Disallow: /cart/',
            'Disallow: /payment/',
            'Allow: /',
            '',
            "Sitemap: {$sitemapUrl}",
        ]);

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
