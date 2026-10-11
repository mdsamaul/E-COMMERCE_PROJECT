@extends('layouts.admin')

@section('title', 'Add New Coupon')
@section('page_title', 'Create Coupon')
@section('page_subtitle', 'Configure a new promotional code with usage restrictions and limits')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Coupons
            </a>
            <span class="text-muted small">* Indicates required fields</span>
        </div>

        <div class="table-card p-4">
            <form method="POST" action="{{ route('admin.coupons.store') }}">
                @csrf

                <div class="row g-3">
                    <!-- Coupon Code -->
                    <div class="col-md-6">
                        <label for="code" class="form-label fw-bold">Coupon Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="code" class="form-control text-uppercase @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="e.g. EID2026, WELCOME10, SAVE500" required>
                        <small class="text-muted">Unique promotional code entered by shoppers at checkout.</small>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Discount Type -->
                    <div class="col-md-6">
                        <label for="type" class="form-label fw-bold">Discount Type <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Fixed Amount (৳)</option>
                            <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Discount Value -->
                    <div class="col-md-6">
                        <label for="value" class="form-label fw-bold">Discount Value <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="value" id="value" class="form-control @error('value') is-invalid @enderror" value="{{ old('value') }}" placeholder="e.g. 100 for ৳100 or 15 for 15%" required>
                        @error('value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Min Order Amount -->
                    <div class="col-md-6">
                        <label for="min_order_amount" class="form-label fw-bold">Minimum Order Subtotal (৳)</label>
                        <input type="number" step="0.01" min="0" name="min_order_amount" id="min_order_amount" class="form-control @error('min_order_amount') is-invalid @enderror" value="{{ old('min_order_amount') }}" placeholder="e.g. 1000">
                        <small class="text-muted">Cart subtotal required before coupon can be applied.</small>
                        @error('min_order_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Max Discount Cap (for percentage) -->
                    <div class="col-md-6">
                        <label for="max_discount_amount" class="form-label fw-bold">Maximum Discount Cap (৳)</label>
                        <input type="number" step="0.01" min="0" name="max_discount_amount" id="max_discount_amount" class="form-control @error('max_discount_amount') is-invalid @enderror" value="{{ old('max_discount_amount') }}" placeholder="e.g. 500">
                        <small class="text-muted">Maximum limit on percentage discount amount.</small>
                        @error('max_discount_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Usage Limit Per Coupon -->
                    <div class="col-md-3">
                        <label for="usage_limit_per_coupon" class="form-label fw-bold">Total Max Uses</label>
                        <input type="number" min="1" name="usage_limit_per_coupon" id="usage_limit_per_coupon" class="form-control @error('usage_limit_per_coupon') is-invalid @enderror" value="{{ old('usage_limit_per_coupon') }}" placeholder="e.g. 100">
                        <small class="text-muted">Blank for unlimited.</small>
                        @error('usage_limit_per_coupon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Usage Limit Per User -->
                    <div class="col-md-3">
                        <label for="usage_limit_per_user" class="form-label fw-bold">Uses Per Customer</label>
                        <input type="number" min="1" name="usage_limit_per_user" id="usage_limit_per_user" class="form-control @error('usage_limit_per_user') is-invalid @enderror" value="{{ old('usage_limit_per_user', 1) }}">
                        <small class="text-muted">Per customer account.</small>
                        @error('usage_limit_per_user')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Starts At -->
                    <div class="col-md-6">
                        <label for="starts_at" class="form-label fw-bold">Start Date & Time</label>
                        <input type="datetime-local" name="starts_at" id="starts_at" class="form-control @error('starts_at') is-invalid @enderror" value="{{ old('starts_at') }}">
                        @error('starts_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Expires At -->
                    <div class="col-md-6">
                        <label for="expires_at" class="form-label fw-bold">Expiry Date & Time</label>
                        <input type="datetime-local" name="expires_at" id="expires_at" class="form-control @error('expires_at') is-invalid @enderror" value="{{ old('expires_at') }}">
                        @error('expires_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Active Toggle -->
                    <div class="col-12">
                        <div class="border rounded p-3 bg-light">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Active Coupon (Eligible for checkout validation)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check2-circle me-1"></i> Save Coupon
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
