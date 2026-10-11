@extends('layouts.app')

@section('title', $category->name . ' — ShopBD')
@section('meta_description', $category->description ?? 'Browse our selection of ' . $category->name . ' at ShopBD with fast delivery across Bangladesh.')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none text-muted">Categories</a></li>
            @if($category->parent)
                <li class="breadcrumb-item"><a href="{{ route('products.category', $category->parent->slug) }}" class="text-decoration-none text-muted">{{ $category->parent->name }}</a></li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
        </ol>
    </nav>

    <!-- Category Header Card -->
    <div class="card border-0 shadow-sm p-4 mb-4 bg-white">
        <div class="row align-items-center">
            <div class="col-md-9">
                <h3 class="fw-bold mb-2">{{ $category->name }}</h3>
                <p class="text-muted small mb-0">{{ $category->description ?? 'Explore authentic products under ' . $category->name . '.' }}</p>

                <!-- Subcategories pills -->
                @if($category->children->count() > 0)
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="small fw-bold text-muted align-self-center me-1">Subcategories:</span>
                        @foreach($category->children as $sub)
                            <a href="{{ route('products.category', $sub->slug) }}" class="badge bg-light text-dark border text-decoration-none py-2 px-3">
                                {{ $sub->name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            @if($category->image)
                <div class="col-md-3 text-center text-md-end mt-3 mt-md-0">
                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="rounded shadow-sm object-fit-cover" style="width: 100px; height: 100px;">
                </div>
            @endif
        </div>
    </div>

    <!-- Products Grid -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold text-muted mb-0">Total {{ $products->total() }} items available</h6>
    </div>

    @if($products->count() > 0)
        <div class="row g-3 g-md-4 mb-4">
            @foreach($products as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    <x-product-card :product="$product" />
                </div>
            @endforeach
        </div>

        @if($products->hasPages())
            <div class="d-flex justify-content-center">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @else
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="py-4">
                <i class="bi bi-box-seam fs-1 text-muted d-block mb-3"></i>
                <h5 class="fw-bold">No Products in this Category</h5>
                <p class="text-muted small mb-3">We are currently restocking products in this category.</p>
                <a href="{{ route('products.index') }}" class="btn btn-shop-primary">
                    Browse Other Products
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
