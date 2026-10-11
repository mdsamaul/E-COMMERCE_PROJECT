@extends('layouts.app')

@section('title', $brand->name . ' Products — ShopBD')
@section('meta_description', $brand->description ?? 'Browse original ' . $brand->name . ' products on ShopBD with Cash on Delivery in Bangladesh.')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none text-muted">Brands</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $brand->name }}</li>
        </ol>
    </nav>

    <!-- Brand Header Card -->
    <div class="card border-0 shadow-sm p-4 mb-4 bg-white">
        <div class="d-flex align-items-center gap-4">
            @if($brand->logo)
                <div class="rounded border p-2 bg-white shadow-sm flex-shrink-0" style="width: 90px; height: 90px; display: flex; align-items: center; justify-content: center;">
                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="img-fluid object-fit-contain" style="max-height: 70px;">
                </div>
            @endif
            <div>
                <h3 class="fw-bold mb-1">{{ $brand->name }}</h3>
                <p class="text-muted small mb-0">{{ $brand->description ?? 'Official ' . $brand->name . ' catalog on ShopBD.' }}</p>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold text-muted mb-0">Total {{ $products->total() }} items from {{ $brand->name }}</h6>
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
                <i class="bi bi-tag fs-1 text-muted d-block mb-3"></i>
                <h5 class="fw-bold">No Products Found for {{ $brand->name }}</h5>
                <p class="text-muted small mb-3">Check back soon for new arrivals from this brand.</p>
                <a href="{{ route('products.index') }}" class="btn btn-shop-primary">
                    Browse All Brands & Products
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
