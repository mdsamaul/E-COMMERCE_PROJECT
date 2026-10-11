@extends('layouts.app')

@section('title', 'Shipping & Delivery Policy — ShopBD')
@section('meta_description', 'Learn about ShopBD shipping rates, courier delivery timelines in Dhaka and all 64 districts in Bangladesh.')

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Shipping & Delivery Policy</li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm p-4 p-md-5 bg-white">
        <h2 class="fw-bold mb-3">Shipping & Delivery Policy</h2>
        <p class="text-muted small mb-4">Last Updated: October 2026</p>

        <div class="text-secondary leading-relaxed small" style="line-height: 1.8;">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 bg-light">
                        <h6 class="fw-bold text-dark mb-1"><i class="bi bi-geo-alt-fill text-shop-primary me-2"></i>Inside Dhaka Metro</h6>
                        <div class="fs-5 fw-bold text-shop-primary mb-1">৳{{ number_format(\App\Models\Setting::get('inside_dhaka_delivery_fee', 60), 2) }}</div>
                        <span class="text-muted small">Standard Delivery Time: <strong>24 to 48 Hours</strong></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 border rounded-3 bg-light">
                        <h6 class="fw-bold text-dark mb-1"><i class="bi bi-truck text-shop-primary me-2"></i>Outside Dhaka (Nationwide)</h6>
                        <div class="fs-5 fw-bold text-shop-primary mb-1">৳{{ number_format(\App\Models\Setting::get('outside_dhaka_delivery_fee', 120), 2) }}</div>
                        <span class="text-muted small">Standard Delivery Time: <strong>3 to 5 Business Days</strong></span>
                    </div>
                </div>
            </div>

            <h5 class="text-dark fw-bold mt-4">1. Free Delivery Eligibility</h5>
            <p>Orders with a total product value exceeding <strong>৳{{ number_format(\App\Models\Setting::get('free_shipping_threshold', 2500), 2) }}</strong> are eligible for automatic <strong>Free Delivery</strong> across Bangladesh.</p>

            <h5 class="text-dark fw-bold mt-3">2. Order Processing & Verification</h5>
            <p>All orders placed before 3:00 PM are processed and packed on the same business day. Our fulfillment team may contact the recipient via mobile phone to confirm delivery address details prior to dispatch.</p>

            <h5 class="text-dark fw-bold mt-3">3. Courier Partners</h5>
            <p>We work exclusively with premier nationwide courier networks (Steadfast, Pathao Courier, RedX, and eCourier) to ensure secure handling and trackable shipments.</p>

            <h5 class="text-dark fw-bold mt-3">4. Tracking Your Parcel</h5>
            <p>Once dispatched from our fulfillment hub, an SMS containing your shipment tracking link will be sent to your registered mobile phone.</p>
        </div>
    </div>
</div>
@endsection
