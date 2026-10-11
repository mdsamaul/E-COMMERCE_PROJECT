@extends('layouts.app')

@section('title', 'ShopBD — Online Shopping Bangladesh | Best Deals & Cash on Delivery')
@section('meta_description', 'Shop genuine clothing, electronics, home appliances, and groceries in Bangladesh. Enjoy nationwide Cash on Delivery, easy returns, and authentic products.')

@push('structured_data')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Organization",
      "@id": "{{ url('/') }}#organization",
      "name": "ShopBD",
      "url": "{{ url('/') }}",
      "logo": "{{ asset('images/logo.png') }}",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+8801700000000",
        "contactType": "customer service",
        "areaServed": "BD",
        "availableLanguage": ["Bengali", "English"]
      }
    },
    {
      "@type": "WebSite",
      "@id": "{{ url('/') }}#website",
      "url": "{{ url('/') }}",
      "name": "ShopBD",
      "description": "Best Online Shopping Experience in Bangladesh",
      "publisher": {
        "@id": "{{ url('/') }}#organization"
      },
      "potentialAction": {
        "@type": "SearchAction",
        "target": {
          "@type": "EntryPoint",
          "urlTemplate": "{{ url('/products') }}?q={search_term_string}"
        },
        "query-input": "required name=search_term_string"
      }
    }
  ]
}
</script>
@endpush

@section('content')
<!-- Hero Slider -->
@if($heroBanners->count() > 0)
    <section class="hero-slider-section">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                @foreach($heroBanners as $index => $banner)
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}"></button>
                @endforeach
            </div>
            <div class="carousel-inner">
                @foreach($heroBanners as $index => $banner)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}" style="max-height: 480px; background-color: #042f2e;">
                        <img src="{{ $banner->image_url }}" class="d-block w-100 object-fit-cover" alt="{{ $banner->title }}" style="height: 440px; opacity: 0.85;">
                        <div class="carousel-caption d-none d-md-block text-start p-4 rounded" style="background: rgba(15, 23, 42, 0.7); max-width: 500px; bottom: 3rem; left: 8%;">
                            <h2 class="fw-bold text-white mb-2">{{ $banner->title }}</h2>
                            @if($banner->subtitle)
                                <p class="text-light mb-3">{{ $banner->subtitle }}</p>
                            @endif
                            @if($banner->button_text)
                                <a href="{{ $banner->button_url ?? route('products.index') }}" class="btn btn-warning fw-bold px-4">
                                    {{ $banner->button_text }} &rarr;
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            @if($heroBanners->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            @endif
        </div>
    </section>
@endif

<!-- Trust Features Bar -->
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="row g-3 text-center text-md-start">
            <div class="col-6 col-md-3 d-flex align-items-center gap-3">
                <i class="bi bi-shield-check text-shop-primary fs-1"></i>
                <div>
                    <h6 class="fw-bold mb-0">100% Authentic</h6>
                    <small class="text-muted">Guaranteed genuine goods</small>
                </div>
            </div>
            <div class="col-6 col-md-3 d-flex align-items-center gap-3">
                <i class="bi bi-truck text-shop-primary fs-1"></i>
                <div>
                    <h6 class="fw-bold mb-0">Fast Delivery</h6>
                    <small class="text-muted">Across all 64 districts</small>
                </div>
            </div>
            <div class="col-6 col-md-3 d-flex align-items-center gap-3">
                <i class="bi bi-cash-coin text-shop-primary fs-1"></i>
                <div>
                    <h6 class="fw-bold mb-0">Cash on Delivery</h6>
                    <small class="text-muted">Pay upon arrival</small>
                </div>
            </div>
            <div class="col-6 col-md-3 d-flex align-items-center gap-3">
                <i class="bi bi-arrow-clockwise text-shop-primary fs-1"></i>
                <div>
                    <h6 class="fw-bold mb-0">Easy 7-Day Return</h6>
                    <small class="text-muted">Hassle-free guarantee</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Categories -->
@if($featuredCategories->count() > 0)
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-grid-3x3-gap-fill text-shop-primary me-2"></i>Explore Categories</h4>
                    <p class="text-muted small mb-0">Popular shopping categories handpicked for you</p>
                </div>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-shop">View All Categories &rarr;</a>
            </div>

            <div class="row g-3">
                @foreach($featuredCategories as $category)
                    <div class="col-6 col-md-3 col-lg-3">
                        <a href="{{ route('products.category', $category->slug) }}" class="text-decoration-none">
                            <div class="card h-100 border-0 shadow-sm text-center p-3 hover-shadow transition">
                                <div class="mx-auto rounded-circle bg-light d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; overflow: hidden;">
                                    @if($category->image)
                                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="img-fluid object-fit-cover" style="width: 100%; height: 100%;">
                                    @else
                                        <i class="bi bi-folder2-open text-shop-primary fs-2"></i>
                                    @endif
                                </div>
                                <h6 class="fw-bold text-dark mb-1">{{ $category->name }}</h6>
                                <small class="text-muted">{{ $category->products_count }} Products</small>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

<!-- Flash Deals Section -->
@if($flashDeals->count() > 0)
    <section class="py-5 bg-white border-top border-bottom">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger fs-6 px-3 py-2"><i class="bi bi-lightning-charge-fill me-1"></i>Flash Deals</span>
                    <h4 class="fw-bold mb-0">Super Discount Offers</h4>
                </div>
                <a href="{{ route('products.index', ['sort' => 'popular']) }}" class="btn btn-sm btn-outline-danger">Shop All Deals &rarr;</a>
            </div>

            <div class="row g-3 g-md-4">
                @foreach($flashDeals as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

<!-- Promotional Cards Banner Grid -->
@if($promoCards->count() > 0)
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                @foreach($promoCards as $promo)
                    <div class="col-md-{{ 12 / count($promoCards) }}">
                        <div class="rounded shadow-sm overflow-hidden position-relative" style="height: 220px; background-color: #0f172a;">
                            <img src="{{ $promo->image_url }}" alt="{{ $promo->title }}" class="w-100 h-100 object-fit-cover" style="opacity: 0.75;">
                            <div class="position-absolute top-0 start-0 w-100 h-100 p-4 d-flex flex-column justify-content-center" style="background: linear-gradient(90deg, rgba(15,23,42,0.85) 0%, rgba(15,23,42,0.2) 100%);">
                                <h4 class="fw-bold text-white mb-1">{{ $promo->title }}</h4>
                                <p class="text-warning small mb-3">{{ $promo->subtitle }}</p>
                                @if($promo->button_text)
                                    <div>
                                        <a href="{{ $promo->button_url ?? route('products.index') }}" class="btn btn-sm btn-light fw-bold px-3">
                                            {{ $promo->button_text }} &rarr;
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

<!-- Featured Products Grid -->
@if($featuredProducts->count() > 0)
    <section class="py-5 bg-white border-top border-bottom">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-star-fill text-warning me-2"></i>Featured Highlights</h4>
                    <p class="text-muted small mb-0">Our editors' top recommendations this week</p>
                </div>
                <a href="{{ route('products.index', ['sort' => 'popular']) }}" class="btn btn-sm btn-outline-shop">View More &rarr;</a>
            </div>

            <div class="row g-3 g-md-4">
                @foreach($featuredProducts as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

<!-- New Arrivals Grid -->
@if($newArrivals->count() > 0)
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-sparkles text-primary me-2"></i>New Arrivals</h4>
                    <p class="text-muted small mb-0">Freshly stocked items added to our catalog</p>
                </div>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-shop">See All New &rarr;</a>
            </div>

            <div class="row g-3 g-md-4">
                @foreach($newArrivals as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

<!-- Top Brands Carousel / Grid -->
@if($brands->count() > 0)
    <section class="py-5 bg-white border-top">
        <div class="container">
            <h5 class="fw-bold text-center mb-4">Trusted Brands & Partners</h5>
            <div class="row g-3 justify-content-center align-items-center">
                @foreach($brands as $brand)
                    <div class="col-4 col-sm-3 col-md-2 text-center">
                        <a href="{{ route('products.brand', $brand->slug) }}" class="text-decoration-none text-muted">
                            <div class="border rounded p-2 bg-light shadow-sm hover-shadow transition d-flex align-items-center justify-content-center" style="height: 70px;">
                                @if($brand->logo)
                                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="img-fluid object-fit-contain" style="max-height: 48px; max-width: 90px;">
                                @else
                                    <span class="fw-bold text-dark small">{{ $brand->name }}</span>
                                @endif
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection
