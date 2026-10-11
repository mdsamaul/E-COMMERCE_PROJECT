@extends('layouts.app')

@section('title', 'All Products & Shop — ' . config('app.name', 'ShopBD'))
@section('meta_description', 'Browse our full catalog of authentic clothing, electronics, groceries, and home appliances in Bangladesh.')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Products Catalog</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Sidebar Filters Column -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm p-3 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-funnel me-1 text-shop-primary"></i>Filters</h6>
                    @if(request()->anyFilled(['q', 'category', 'category_id', 'brand', 'brand_id', 'min_price', 'max_price', 'in_stock', 'sort']))
                        <a href="{{ route('products.index') }}" class="text-danger small text-decoration-none">Reset All</a>
                    @endif
                </div>

                <form method="GET" action="{{ route('products.index') }}">
                    <!-- Search Input -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Search Keywords</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="q" class="form-control" placeholder="Search..." value="{{ request('q') }}">
                            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Categories</label>
                        <div class="list-group list-group-flush small" style="max-height: 220px; overflow-y: auto;">
                            @foreach($categories as $cat)
                                <a href="{{ route('products.index', array_merge(request()->query(), ['category' => $cat->slug])) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-1 py-1 border-0 {{ request('category') === $cat->slug ? 'fw-bold text-shop-primary' : 'text-secondary' }}">
                                    <span>{{ $cat->name }}</span>
                                    <span class="badge bg-light text-muted">{{ $cat->products_count }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Brand Filter -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Brands</label>
                        <div class="list-group list-group-flush small" style="max-height: 180px; overflow-y: auto;">
                            @foreach($brands as $b)
                                <a href="{{ route('products.index', array_merge(request()->query(), ['brand' => $b->slug])) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-1 py-1 border-0 {{ request('brand') === $b->slug ? 'fw-bold text-shop-primary' : 'text-secondary' }}">
                                    <span>{{ $b->name }}</span>
                                    <span class="badge bg-light text-muted">{{ $b->products_count }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Price Range (৳)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" min="0" name="min_price" class="form-control form-control-sm" placeholder="Min ৳" value="{{ request('min_price') }}">
                            </div>
                            <div class="col-6">
                                <input type="number" min="0" name="max_price" class="form-control form-control-sm" placeholder="Max ৳" value="{{ request('max_price') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Stock Status -->
                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="in_stock" id="in_stock" value="1" {{ request()->boolean('in_stock') ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold" for="in_stock">In Stock Only</label>
                    </div>

                    <button type="submit" class="btn btn-sm btn-shop-primary w-100">Apply Filters</button>
                </form>
            </div>
        </div>

        <!-- Main Products Grid Column -->
        <div class="col-lg-9">
            <!-- Top Controls Bar -->
            <div class="card border-0 shadow-sm p-3 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h6 class="fw-bold mb-0">Products Catalog</h6>
                        <small class="text-muted">Showing {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} results</small>
                    </div>

                    <!-- Sort Selector -->
                    <form method="GET" action="{{ route('products.index') }}" class="d-flex align-items-center gap-2">
                        @foreach(request()->except(['sort', 'page']) as $key => $val)
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endforeach
                        <label class="small text-muted mb-0 text-nowrap">Sort By:</label>
                        <select name="sort" class="form-select form-select-sm" onchange="this.form.submit();">
                            <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest Items</option>
                            <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Featured & Popular</option>
                            <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name: A to Z</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Products Grid -->
            @if($products->count() > 0)
                <div class="row g-3 g-md-4 mb-4">
                    @foreach($products as $product)
                        <div class="col-6 col-md-4 col-xl-3">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($products->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $products->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="card border-0 shadow-sm text-center py-5">
                    <div class="py-4">
                        <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
                        <h5 class="fw-bold">No Products Found</h5>
                        <p class="text-muted small mb-3">We couldn't find any products matching your selected search or filters.</p>
                        <a href="{{ route('products.index') }}" class="btn btn-shop-primary">
                            Clear Filters & View All
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
