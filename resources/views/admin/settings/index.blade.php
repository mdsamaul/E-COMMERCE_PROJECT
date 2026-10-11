@extends('layouts.admin')

@section('title', 'Website & Delivery Settings')
@section('page_title', 'Website & Delivery Settings')
@section('page_subtitle', 'Configure store details, contact info, shipping delivery fees, and currency')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf

            <div class="row g-4">
                <!-- Store General Information -->
                <div class="col-lg-6">
                    <div class="table-card p-4 h-100">
                        <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">
                            <i class="bi bi-shop me-2 text-primary"></i>Store Identity & Contact
                        </h6>

                        <div class="mb-3">
                            <label for="store_name" class="form-label fw-bold">Store Name <span class="text-danger">*</span></label>
                            <input type="text" name="store_name" id="store_name" class="form-control @error('store_name') is-invalid @enderror" value="{{ old('store_name', $settings['store_name']) }}" required>
                            @error('store_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="store_tagline" class="form-label fw-bold">Store Tagline / Slogan</label>
                            <input type="text" name="store_tagline" id="store_tagline" class="form-control @error('store_tagline') is-invalid @enderror" value="{{ old('store_tagline', $settings['store_tagline']) }}">
                            @error('store_tagline')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="store_phone" class="form-label fw-bold">Customer Helpline Phone <span class="text-danger">*</span></label>
                            <input type="text" name="store_phone" id="store_phone" class="form-control @error('store_phone') is-invalid @enderror" value="{{ old('store_phone', $settings['store_phone']) }}" placeholder="017XXXXXXXX" required>
                            <small class="text-muted">Must be a valid Bangladesh phone number (11 digits e.g. 01712345678 or +8801712345678).</small>
                            @error('store_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="store_email" class="form-label fw-bold">Customer Support Email <span class="text-danger">*</span></label>
                            <input type="email" name="store_email" id="store_email" class="form-control @error('store_email') is-invalid @enderror" value="{{ old('store_email', $settings['store_email']) }}" required>
                            @error('store_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="store_address" class="form-label fw-bold">Office Address <span class="text-danger">*</span></label>
                            <textarea name="store_address" id="store_address" rows="3" class="form-control @error('store_address') is-invalid @enderror" required>{{ old('store_address', $settings['store_address']) }}</textarea>
                            @error('store_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Shipping & Currency Information -->
                <div class="col-lg-6">
                    <div class="table-card p-4 mb-4">
                        <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">
                            <i class="bi bi-truck me-2 text-primary"></i>Delivery & Shipping Fees
                        </h6>

                        <div class="mb-3">
                            <label for="inside_dhaka_delivery_fee" class="form-label fw-bold">Inside Dhaka Delivery Charge (৳) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" name="inside_dhaka_delivery_fee" id="inside_dhaka_delivery_fee" class="form-control @error('inside_dhaka_delivery_fee') is-invalid @enderror" value="{{ old('inside_dhaka_delivery_fee', $settings['inside_dhaka_delivery_fee']) }}" required>
                            </div>
                            <small class="text-muted">Standard delivery fee for addresses within Dhaka metro.</small>
                            @error('inside_dhaka_delivery_fee')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="outside_dhaka_delivery_fee" class="form-label fw-bold">Outside Dhaka Delivery Charge (৳) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" name="outside_dhaka_delivery_fee" id="outside_dhaka_delivery_fee" class="form-control @error('outside_dhaka_delivery_fee') is-invalid @enderror" value="{{ old('outside_dhaka_delivery_fee', $settings['outside_dhaka_delivery_fee']) }}" required>
                            </div>
                            <small class="text-muted">Courier delivery fee for rest of Bangladesh districts.</small>
                            @error('outside_dhaka_delivery_fee')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="free_shipping_threshold" class="form-label fw-bold">Free Shipping Minimum Order (৳)</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" name="free_shipping_threshold" id="free_shipping_threshold" class="form-control @error('free_shipping_threshold') is-invalid @enderror" value="{{ old('free_shipping_threshold', $settings['free_shipping_threshold']) }}">
                            </div>
                            <small class="text-muted">Orders with subtotal above this get free delivery (Set 0 to disable).</small>
                            @error('free_shipping_threshold')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Localization Card -->
                    <div class="table-card p-4">
                        <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">
                            <i class="bi bi-cash-stack me-2 text-primary"></i>Currency Format
                        </h6>

                        <div class="mb-3">
                            <label for="currency_symbol" class="form-label fw-bold">Currency Symbol <span class="text-danger">*</span></label>
                            <input type="text" name="currency_symbol" id="currency_symbol" class="form-control @error('currency_symbol') is-invalid @enderror" value="{{ old('currency_symbol', $settings['currency_symbol']) }}" required>
                            <small class="text-muted">Default: ৳ (Bangladeshi Taka symbol).</small>
                            @error('currency_symbol')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="bi bi-check2-circle me-1"></i> Save Website Settings
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
