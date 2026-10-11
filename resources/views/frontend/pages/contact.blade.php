@extends('layouts.app')

@section('title', 'Contact Us — ShopBD Customer Support')
@section('meta_description', 'Contact ShopBD customer support for order assistance, return requests, or business inquiries in Bangladesh.')

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Contact Support</li>
        </ol>
    </nav>

    <div class="row g-4 g-lg-5">
        <!-- Contact Information Column -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4 bg-white h-100">
                <h4 class="fw-bold mb-3">Get in Touch With Us</h4>
                <p class="text-secondary small mb-4">Have questions about an ongoing order, delivery timeframe, or product details? We are here to assist you.</p>

                <div class="mb-4 d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-light p-3 text-shop-primary">
                        <i class="bi bi-geo-alt-fill fs-4"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark">Office & Fulfillment Hub</strong>
                        <span class="text-secondary small">{{ \App\Models\Setting::get('store_address', 'Motijheel Commercial Area, Dhaka-1000, Bangladesh') }}</span>
                    </div>
                </div>

                <div class="mb-4 d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-light p-3 text-shop-primary">
                        <i class="bi bi-telephone-fill fs-4"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark">Customer Helpline</strong>
                        <span class="text-secondary small font-monospace">{{ \App\Models\Setting::get('store_phone', '01700-000000') }}</span>
                        <small class="d-block text-muted">Saturday – Thursday: 9:00 AM – 10:00 PM</small>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-light p-3 text-shop-primary">
                        <i class="bi bi-envelope-fill fs-4"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark">Support Email</strong>
                        <span class="text-secondary small">{{ \App\Models\Setting::get('store_email', 'support@shopbd.com') }}</span>
                        <small class="d-block text-muted">Typical reply within 24 hours</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form Column -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-4 bg-white">
                <h5 class="fw-bold mb-3">Send Us a Direct Message</h5>

                <form method="POST" action="{{ route('pages.contact.submit') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', auth()->user()?->name) }}" placeholder="e.g. Tanvir Hasan" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label small fw-bold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', auth()->user()?->email) }}" placeholder="name@example.com" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label small fw-bold">Mobile Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', auth()->user()?->phone) }}" placeholder="017XXXXXXXX" required>
                            <small class="text-muted">Bangladeshi 11-digit mobile number.</small>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="subject" class="form-label small fw-bold">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" id="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" placeholder="Order status / Delivery inquiry" required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="message" class="form-label small fw-bold">Message Details <span class="text-danger">*</span></label>
                            <textarea name="message" id="message" rows="5" class="form-control @error('message') is-invalid @enderror" placeholder="Write your message here..." required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-shop-primary px-4 py-2">
                                <i class="bi bi-send me-1"></i> Send Inquiry
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
