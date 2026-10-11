@extends('layouts.app')

@section('title', 'About Us — ShopBD')
@section('meta_description', 'Learn about ShopBD, Bangladesh\'s trusted single-vendor e-commerce store dedicated to genuine products and fast Cash on Delivery.')

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">About Us</li>
        </ol>
    </nav>

    <div class="row align-items-center g-5 mb-5">
        <div class="col-lg-6">
            <h6 class="text-uppercase text-shop-primary fw-bold tracking-wider mb-2">Our Mission</h6>
            <h2 class="fw-bold mb-3">Empowering Online Shopping in Bangladesh with Integrity & Speed</h2>
            <p class="text-secondary leading-relaxed mb-3">
                Founded with a mission to deliver authentic products to every corner of Bangladesh, <strong>ShopBD</strong> is your trusted single-vendor destination for clothing, electronics, home essentials, and lifestyle accessories.
            </p>
            <p class="text-secondary leading-relaxed mb-4">
                We believe online shopping should be seamless, transparent, and dependable. By managing our inventory directly rather than operating as an unverified marketplace, we guarantee genuine quality, strict packaging standards, and swift deliveries.
            </p>
            <div class="row g-3">
                <div class="col-6">
                    <div class="border rounded p-3 bg-white shadow-sm text-center">
                        <h3 class="fw-bold text-shop-primary mb-1">64</h3>
                        <small class="text-muted">Districts Covered</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border rounded p-3 bg-white shadow-sm text-center">
                        <h3 class="fw-bold text-shop-primary mb-1">100%</h3>
                        <small class="text-muted">Genuine Goods</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4 bg-white">
                <h5 class="fw-bold mb-3"><i class="bi bi-patch-check-fill text-warning me-2"></i>Why Choose ShopBD?</h5>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex align-items-start mb-3">
                        <i class="bi bi-truck text-shop-primary fs-4 me-3"></i>
                        <div>
                            <strong class="d-block text-dark">Speedy Nationwide Shipping</strong>
                            <span class="text-muted small">Deliveries within 24-48 hours in Dhaka metro, and 3-5 days across all 64 districts in Bangladesh.</span>
                        </div>
                    </li>
                    <li class="d-flex align-items-start mb-3">
                        <i class="bi bi-cash-stack text-shop-primary fs-4 me-3"></i>
                        <div>
                            <strong class="d-block text-dark">No-Risk Cash on Delivery</strong>
                            <span class="text-muted small">Check your parcel upon delivery and pay conveniently in cash or mobile banking (bKash/Nagad).</span>
                        </div>
                    </li>
                    <li class="d-flex align-items-start mb-3">
                        <i class="bi bi-shield-lock text-shop-primary fs-4 me-3"></i>
                        <div>
                            <strong class="d-block text-dark">Direct Quality Control</strong>
                            <span class="text-muted small">Every item is checked, inspected, and safely packaged in our central fulfillment warehouse before dispatch.</span>
                        </div>
                    </li>
                    <li class="d-flex align-items-start">
                        <i class="bi bi-headset text-shop-primary fs-4 me-3"></i>
                        <div>
                            <strong class="d-block text-dark">Dedicated Local Support</strong>
                            <span class="text-muted small">Reach our helpline directly at {{ \App\Models\Setting::get('store_phone', '01700-000000') }} for prompt order assistance.</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
