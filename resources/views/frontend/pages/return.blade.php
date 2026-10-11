@extends('layouts.app')

@section('title', 'Return & Refund Policy — ShopBD')
@section('meta_description', 'Learn about ShopBD 7-day hassle-free return, replacement, and refund policy in Bangladesh.')

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Return & Refund Policy</li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm p-4 p-md-5 bg-white">
        <h2 class="fw-bold mb-3">Return & Refund Policy</h2>
        <p class="text-muted small mb-4">Last Updated: October 2026</p>

        <div class="text-secondary leading-relaxed small" style="line-height: 1.8;">
            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="bi bi-shield-check fs-4 me-2 align-middle"></i>
                <strong>7-Day Replacement Guarantee:</strong> If your purchased product is defective, damaged, or does not match specifications, we offer a 100% free return or replacement within 7 days of delivery.
            </div>

            <h5 class="text-dark fw-bold mt-4">1. Eligible Reasons for Returns</h5>
            <ul>
                <li>The received product is defective, damaged, or physically broken upon arrival.</li>
                <li>The received product does not match the size, color, or specifications ordered.</li>
                <li>Missing components, accessories, or parts mentioned in the product description.</li>
            </ul>

            <h5 class="text-dark fw-bold mt-4">2. Conditions for Return</h5>
            <ul>
                <li>The product must remain unused, unwashed, and in its original condition.</li>
                <li>Original packaging, price tags, barcode stickers, and warranty cards must be intact.</li>
                <li>Return request must be initiated within 7 calendar days from the date of package delivery.</li>
            </ul>

            <h5 class="text-dark fw-bold mt-4">3. Refund Processing</h5>
            <p>Once our central warehouse receives and verifies the returned package, your refund will be issued within <strong>3 to 5 business days</strong> through your preferred mobile payment method (bKash, Nagad, Rocket) or bank transfer.</p>

            <h5 class="text-dark fw-bold mt-4">4. How to Initiate a Return</h5>
            <p>To initiate a return or exchange, simply contact our support hotline at <strong>{{ \App\Models\Setting::get('store_phone', '01700-000000') }}</strong> or email us at <strong>{{ \App\Models\Setting::get('store_email', 'support@shopbd.com') }}</strong> with your Order Number and photos of the issue.</p>
        </div>
    </div>
</div>
@endsection
