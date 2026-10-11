@extends('layouts.admin')

@section('title', 'Edit Coupon — ' . $coupon->code)
@section('page_title', 'Edit Coupon')
@section('page_subtitle', 'Update discount values and validity constraints')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Coupons
            </a>
            <span class="text-muted small">Coupon ID: #{{ $coupon->id }} | Used: {{ $coupon->total_used }} times</span>
        </div>

        <div class="table-card p-4">
            <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <!-- Coupon Code -->
                    <div class="col-md-6">
                        <label for="code" class="form-label fw-bold">Coupon Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="code" class="form-control text-uppercase @error('code') is-invalid @enderror" value="{{ old('code', $coupon->code) }}" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Discount Type -->
                    <div class="col-md-6">
                        <label for="type" class="form-label fw-bold">Discount Type <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="fixed" {{ old('type', $coupon->type) === 'fixed' ? 'selected' : '' }}>Fixed Amount (৳)</option>
                            <option value="percentage" {{ old('type', $coupon->type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Discount Value -->
                    <div class="col-md-6">
                        <label for="value" class="form-label fw-bold">Discount Value <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="value" id="value" class="form-control @error('value') is-invalid @enderror" value="{{ old('value', $coupon->value) }}" required>
                        @error('value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Min Order Amount -->
                    <div class="col-md-6">
                        <label for="min_order_amount" class="form-label fw-bold">Minimum Order Subtotal (৳)</label>
                        <input type="number" step="0.01" min="0" name="min_order_amount" id="min_order_amount" class="form-control @error('min_order_amount') is-invalid @enderror" value="{{ old('min_order_amount', $coupon->min_order_amount) }}">
                        @error('min_order_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Max Discount Cap -->
                    <div class="col-md-6">
                        <label for="max_discount_amount" class="form-label fw-bold">Maximum Discount Cap (৳)</label>
                        <input type="number" step="0.01" min="0" name="max_discount_amount" id="max_discount_amount" class="form-control @error('max_discount_amount') is-invalid @enderror" value="{{ old('max_discount_amount', $coupon->max_discount_amount) }}">
                        @error('max_discount_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Usage Limit Per Coupon -->
                    <div class="col-md-3">
                        <label for="usage_limit_per_coupon" class="form-label fw-bold">Total Max Uses</label>
                        <input type="number" min="1" name="usage_limit_per_coupon" id="usage_limit_per_coupon" class="form-control @error('usage_limit_per_coupon') is-invalid @enderror" value="{{ old('usage_limit_per_coupon', $coupon->usage_limit_per_coupon) }}">
                        @error('usage_limit_per_coupon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Usage Limit Per User -->
                    <div class="col-md-3">
                        <label for="usage_limit_per_user" class="form-label fw-bold">Uses Per Customer</label>
                        <input type="number" min="1" name="usage_limit_per_user" id="usage_limit_per_user" class="form-control @error('usage_limit_per_user') is-invalid @enderror" value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user) }}">
                        @error('usage_limit_per_user')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Starts At -->
                    <div class="col-md-6">
                        <label for="starts_at" class="form-label fw-bold">Start Date & Time</label>
                        <input type="datetime-local" name="starts_at" id="starts_at" class="form-control @error('starts_at') is-invalid @enderror" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}">
                        @error('starts_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Expires At -->
                    <div class="col-md-6">
                        <label for="expires_at" class="form-label fw-bold">Expiry Date & Time</label>
                        <input type="datetime-local" name="expires_at" id="expires_at" class="form-control @error('expires_at') is-invalid @enderror" value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i')) }}">
                        @error('expires_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Active Toggle -->
                    <div class="col-12">
                        <div class="border rounded p-3 bg-light">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Active Status
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Update Coupon
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
