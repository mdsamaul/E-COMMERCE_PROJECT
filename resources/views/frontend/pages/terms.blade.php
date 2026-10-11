@extends('layouts.app')

@section('title', 'Terms and Conditions — ShopBD')
@section('meta_description', 'Read the terms and conditions governing purchases, payments, and services on the ShopBD e-commerce platform.')

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Terms & Conditions</li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm p-4 p-md-5 bg-white">
        <h2 class="fw-bold mb-3">Terms and Conditions</h2>
        <p class="text-muted small mb-4">Effective Date: October 2026</p>

        <div class="text-secondary leading-relaxed small" style="line-height: 1.8;">
            <h5 class="text-dark fw-bold mt-3">1. Agreement to Terms</h5>
            <p>By accessing or purchasing from ShopBD, you agree to comply with and be bound by these Terms and Conditions. Please review them carefully prior to ordering.</p>

            <h5 class="text-dark fw-bold mt-3">2. Pricing & Currency</h5>
            <p>All prices listed on ShopBD are quoted in Bangladeshi Taka (৳ BDT). While we endeavor to ensure accurate pricing, we reserve the right to correct typographical inaccuracies or cancel orders containing erroneous price listings.</p>

            <h5 class="text-dark fw-bold mt-3">3. Order Placement & Acceptance</h5>
            <p>Placing an order constitutes an offer to purchase. ShopBD reserves the right to accept or decline orders in cases of stock unavailability, suspected fraudulent activity, or unserviceable delivery locations.</p>

            <h5 class="text-dark fw-bold mt-3">4. Cash on Delivery (COD) Obligations</h5>
            <p>When selecting Cash on Delivery, customers are obligated to provide authentic recipient contact details and accept delivery upon arrival. Malicious refusal of confirmed parcels may lead to account restriction.</p>

            <h5 class="text-dark fw-bold mt-3">5. Intellectual Property</h5>
            <p>All website content, graphics, logos, and product photographs displayed on ShopBD are the property of ShopBD and protected by applicable copyright and trademark laws.</p>
        </div>
    </div>
</div>
@endsection
