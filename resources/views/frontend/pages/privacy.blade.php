@extends('layouts.app')

@section('title', 'Privacy Policy — ShopBD')
@section('meta_description', 'Review the ShopBD Privacy Policy detailing how we safeguard your personal data, phone number, and delivery information.')

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Privacy Policy</li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm p-4 p-md-5 bg-white">
        <h2 class="fw-bold mb-3">Privacy Policy</h2>
        <p class="text-muted small mb-4">Last Updated: October 2026</p>

        <div class="text-secondary leading-relaxed small" style="line-height: 1.8;">
            <h5 class="text-dark fw-bold mt-3">1. Information We Collect</h5>
            <p>At ShopBD, we prioritize customer confidentiality. When you register an account, place a Cash on Delivery order, or contact customer care, we collect the necessary information to fulfill your order, including your name, delivery address, contact mobile number, and email address.</p>

            <h5 class="text-dark fw-bold mt-3">2. How Your Information Is Used</h5>
            <p>Your details are used strictly for order confirmation, dispatching packages via trusted courier partners, sending shipment tracking updates, and providing personalized customer support. We never sell, rent, or lease customer data to third-party advertisers.</p>

            <h5 class="text-dark fw-bold mt-3">3. Data Security & Storage</h5>
            <p>All passwords are encrypted with industry-standard bcrypt hashing. Sessions and administrative routes are protected with strict CSRF tokens and server-side authorization controls to prevent unauthorized access.</p>

            <h5 class="text-dark fw-bold mt-3">4. Cookies & Analytics</h5>
            <p>We utilize standard browser cookies to retain your shopping cart items across visits and provide a seamless navigation experience.</p>

            <h5 class="text-dark fw-bold mt-3">5. Inquiries & Contact</h5>
            <p>If you have any questions regarding your personal information, please reach out to our data privacy team at <strong>{{ \App\Models\Setting::get('store_email', 'support@shopbd.com') }}</strong>.</p>
        </div>
    </div>
</div>
@endsection
