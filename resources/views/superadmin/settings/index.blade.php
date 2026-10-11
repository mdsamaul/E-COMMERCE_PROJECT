@extends('layouts.superadmin')

@section('title', 'Global Platform Settings')
@section('page_title', 'System & Store Settings')
@section('page_subtitle', 'Master configuration for store identity, delivery parameters, and checkout thresholds.')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <form method="POST" action="{{ route('superadmin.settings.update') }}">
            @csrf

            <div class="row g-4">
                <!-- Store Identity & Contact -->
                <div class="col-12 col-lg-6">
                    <div class="sa-table-card p-4 h-100">
                        <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">
                            <i class="bi bi-shop text-primary me-2"></i>Store Identity & Contact
                        </h5>

                        <div class="mb-3">
                            <label for="store_name" class="form-label fw-bold">Store Name <span class="text-danger">*</span></label>
                            <input type="text" name="store_name" id="store_name" class="form-control @error('store_name') is-invalid @enderror" value="{{ old('store_name', $settings['store_name'] ?? 'ShopBD') }}" required>
                            @error('store_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="store_phone" class="form-label fw-bold">Support Helpline Phone <span class="text-danger">*</span></label>
                            <input type="text" name="store_phone" id="store_phone" class="form-control @error('store_phone') is-invalid @enderror" value="{{ old('store_phone', $settings['store_phone'] ?? '01711000000') }}" placeholder="017XXXXXXXX" required>
                            @error('store_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="store_email" class="form-label fw-bold">Support Email <span class="text-danger">*</span></label>
                            <input type="email" name="store_email" id="store_email" class="form-control @error('store_email') is-invalid @enderror" value="{{ old('store_email', $settings['store_email'] ?? 'support@shopbd.com') }}" required>
                            @error('store_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="store_address" class="form-label fw-bold">Physical Office Address <span class="text-danger">*</span></label>
                            <textarea name="store_address" id="store_address" rows="3" class="form-control @error('store_address') is-invalid @enderror" required>{{ old('store_address', $settings['store_address'] ?? 'Dhaka, Bangladesh') }}</textarea>
                            @error('store_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Delivery & Thresholds -->
                <div class="col-12 col-lg-6">
                    <div class="sa-table-card p-4 h-100">
                        <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">
                            <i class="bi bi-truck text-warning me-2"></i>Shipping Delivery Charges
                        </h5>

                        <div class="mb-3">
                            <label for="inside_dhaka_delivery" class="form-label fw-bold">Inside Dhaka Delivery Fee (৳) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">৳</span>
                                <input type="number" step="0.01" min="0" name="inside_dhaka_delivery" id="inside_dhaka_delivery" class="form-control @error('inside_dhaka_delivery') is-invalid @enderror" value="{{ old('inside_dhaka_delivery', $settings['inside_dhaka_delivery'] ?? '60') }}" required>
                            </div>
                            @error('inside_dhaka_delivery')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="outside_dhaka_delivery" class="form-label fw-bold">Outside Dhaka Delivery Fee (৳) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">৳</span>
                                <input type="number" step="0.01" min="0" name="outside_dhaka_delivery" id="outside_dhaka_delivery" class="form-control @error('outside_dhaka_delivery') is-invalid @enderror" value="{{ old('outside_dhaka_delivery', $settings['outside_dhaka_delivery'] ?? '120') }}" required>
                            </div>
                            @error('outside_dhaka_delivery')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="free_shipping_threshold" class="form-label fw-bold">Free Shipping Threshold (৳)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">৳</span>
                                <input type="number" step="0.01" min="0" name="free_shipping_threshold" id="free_shipping_threshold" class="form-control @error('free_shipping_threshold') is-invalid @enderror" value="{{ old('free_shipping_threshold', $settings['free_shipping_threshold'] ?? '2000') }}">
                            </div>
                            <small class="text-muted">Cart orders above this amount receive free delivery.</small>
                            @error('free_shipping_threshold')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="btn btn-warning text-dark fw-bold w-100 py-2">
                                <i class="bi bi-save me-1"></i> Save System Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
