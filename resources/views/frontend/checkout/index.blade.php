@extends('layouts.app')

@section('title', 'Secure Checkout — ShopBD')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cart.index') }}" class="text-decoration-none">Shopping Cart</a></li>
            <li class="breadcrumb-item active" aria-current="page">Checkout</li>
        </ol>
    </nav>

    <!-- Page Title -->
    <div class="mb-4 pb-2 border-bottom">
        <h1 class="h3 fw-bold mb-0">Complete Your Order</h1>
        <p class="text-muted small mb-0">Fast Cash on Delivery across all 64 districts of Bangladesh</p>
    </div>

    @if(!empty($warnings))
        @foreach($warnings as $warning)
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $warning }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endforeach
    @endif

    @if($errors->has('checkout_error') || $errors->has('cart') || $errors->has('stock'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-octagon-fill me-2 fs-5 align-middle"></i>
            <strong>Checkout Notice:</strong>
            {{ $errors->first('checkout_error') ?: ($errors->first('stock') ?: $errors->first('cart')) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}" id="checkoutForm" onsubmit="return handleCheckoutSubmit(this);">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

        <div class="row g-4">
            <!-- Left Column: Shipping & Customer Details Form -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-shop-primary rounded-circle px-2 py-1">1</span>
                            <h5 class="fw-bold mb-0">Delivery Address & Recipient</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Recipient Name -->
                            <div class="col-md-6">
                                <label for="recipient_name" class="form-label small fw-bold">Recipient Full Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                    <input type="text" name="recipient_name" id="recipient_name" class="form-control @error('recipient_name') is-invalid @enderror" placeholder="e.g. Tanvir Ahmed" value="{{ old('recipient_name', $prefill['name']) }}" required>
                                </div>
                                @error('recipient_name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Bangladesh Phone -->
                            <div class="col-md-6">
                                <label for="phone" class="form-label small fw-bold">Mobile Number (Bangladesh) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light font-monospace fw-bold">+880</span>
                                    <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="017XXXXXXXX" value="{{ old('phone', $prefill['phone']) }}" required>
                                </div>
                                <small class="text-muted" style="font-size: 0.75rem;">Required for delivery call and SMS tracking</small>
                                @error('phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Alternative Phone -->
                            <div class="col-md-6">
                                <label for="alternative_phone" class="form-label small fw-bold">Alternative Mobile Number <span class="text-muted fw-normal">(Optional)</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                    <input type="text" name="alternative_phone" id="alternative_phone" class="form-control @error('alternative_phone') is-invalid @enderror" placeholder="018XXXXXXXX" value="{{ old('alternative_phone') }}">
                                </div>
                                @error('alternative_phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label for="email" class="form-label small fw-bold">Email Address <span class="text-muted fw-normal">(Optional for receipt)</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" placeholder="name@example.com" value="{{ old('email', $prefill['email']) }}">
                                </div>
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- District Dropdown -->
                            <div class="col-md-6">
                                <label for="districtSelect" class="form-label small fw-bold">District <span class="text-danger">*</span></label>
                                <select name="district" id="districtSelect" class="form-select @error('district') is-invalid @enderror" onchange="updateDistrictShipping(this.value)" required>
                                    <option value="" disabled>Select Delivery District</option>
                                    @foreach($districts as $dist)
                                        <option value="{{ $dist }}" {{ old('district', $prefill['district']) === $dist ? 'selected' : '' }}>
                                            {{ $dist }} {{ $dist === 'Dhaka' ? '(Inside Dhaka — ৳60)' : '(Outside Dhaka — ৳120)' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('district')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Division -->
                            <div class="col-md-6">
                                <label for="division" class="form-label small fw-bold">Division / Zone <span class="text-muted fw-normal">(Optional)</span></label>
                                <input type="text" name="division" id="division" class="form-control @error('division') is-invalid @enderror" placeholder="e.g. Dhaka Division" value="{{ old('division', $prefill['division']) }}">
                                @error('division')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Full Delivery Address -->
                            <div class="col-12">
                                <label for="delivery_address" class="form-label small fw-bold">Street / Road / House / Flat Delivery Address <span class="text-danger">*</span></label>
                                <textarea name="delivery_address" id="delivery_address" rows="3" class="form-control @error('delivery_address') is-invalid @enderror" placeholder="House # 12, Road # 4, Sector # 3, Uttara, Dhaka" required>{{ old('delivery_address', $prefill['delivery_address']) }}</textarea>
                                <small class="text-muted" style="font-size: 0.75rem;">Please provide complete address for accurate doorstep courier delivery.</small>
                                @error('delivery_address')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Delivery Notes -->
                            <div class="col-12">
                                <label for="delivery_notes" class="form-label small fw-bold">Special Delivery Instructions <span class="text-muted fw-normal">(Optional)</span></label>
                                <input type="text" name="delivery_notes" id="delivery_notes" class="form-control @error('delivery_notes') is-invalid @enderror" placeholder="e.g. Call before delivery, deliver in the afternoon, leave with security guard" value="{{ old('delivery_notes', $prefill['delivery_notes']) }}">
                                @error('delivery_notes')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method Section -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-shop-primary rounded-circle px-2 py-1">2</span>
                            <h5 class="fw-bold mb-0">Payment Method</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- COD Option Card -->
                        <div class="p-3 border rounded-3 bg-light border-success mb-3">
                            <div class="form-check d-flex align-items-start gap-3">
                                <input class="form-check-input mt-1" type="radio" name="payment_method" id="paymentCod" value="cod" {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }} required>
                                <div class="flex-grow-1">
                                    <label class="form-check-label fw-bold text-dark d-block fs-6" for="paymentCod">
                                        <i class="bi bi-cash-stack text-success me-2"></i> Cash on Delivery (COD)
                                    </label>
                                    <p class="text-muted small mb-0 mt-1">
                                        Pay with cash when your parcel is delivered to your doorstep. You can inspect the package exterior before paying our delivery partner.
                                    </p>
                                    <div class="mt-2">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-shield-check me-1"></i> No upfront payment required
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SSLCOMMERZ Gateway (Active if configured) -->
                        @if(isset($availableGateways['sslcommerz']))
                            <div class="p-3 border rounded-3 bg-white mb-3">
                                <div class="form-check d-flex align-items-start gap-3">
                                    <input class="form-check-input mt-1" type="radio" name="payment_method" id="paymentSslcz" value="sslcommerz" {{ old('payment_method') === 'sslcommerz' ? 'checked' : '' }}>
                                    <div class="flex-grow-1">
                                        <label class="form-check-label fw-bold text-dark d-block fs-6" for="paymentSslcz">
                                            <i class="bi bi-credit-card-2-front text-primary me-2"></i> Pay Online via SSLCOMMERZ
                                        </label>
                                        <p class="text-muted small mb-0 mt-1">
                                            Instant & secure payment via Visa, Mastercard, bKash, Nagad, Rocket, Upay, and 30+ Bangladeshi banks.
                                        </p>
                                        <div class="mt-2">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                <i class="bi bi-lock me-1"></i> 128-bit SSL Secured
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- bKash Gateway (Active if configured) -->
                        @if(isset($availableGateways['bkash']))
                            <div class="p-3 border rounded-3 bg-white mb-3">
                                <div class="form-check d-flex align-items-start gap-3">
                                    <input class="form-check-input mt-1" type="radio" name="payment_method" id="paymentBkash" value="bkash" {{ old('payment_method') === 'bkash' ? 'checked' : '' }}>
                                    <div class="flex-grow-1">
                                        <label class="form-check-label fw-bold text-dark d-block fs-6" for="paymentBkash">
                                            <i class="bi bi-phone text-danger me-2"></i> bKash Direct Payment
                                        </label>
                                        <p class="text-muted small mb-0 mt-1">
                                            Pay directly from your personal bKash mobile account using official bKash Tokenized Checkout.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(!isset($availableGateways['sslcommerz']) && !isset($availableGateways['bkash']))
                            <div class="p-3 bg-white border rounded-3 small text-muted">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-info-circle text-secondary"></i>
                                    <span>Online gateways (SSLCOMMERZ / bKash) are currently disabled pending merchant credentials. Cash on Delivery is 100% active.</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Review & Financial Totals -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 90px; z-index: 10;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="fw-bold mb-0">Order Review ({{ $totals['items_count'] }} items)</h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Items Preview List -->
                        <div class="mb-3" style="max-height: 240px; overflow-y: auto;">
                            @foreach($items as $item)
                                @php $product = $item->product; @endphp
                                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 48px; height: 48px; background: #f1f5f9; border-radius: 6px; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            @if($product->primaryImage)
                                                <img src="{{ $product->primaryImage->url }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                <i class="bi bi-card-image text-muted"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-dark text-truncate d-block small" style="max-width: 180px;">{{ $product->name }}</span>
                                            <span class="text-muted small">Qty: {{ $item->quantity }} × ৳{{ number_format($item->unit_price, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="fw-bold text-dark small">
                                        ৳{{ number_format($item->subtotal, 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Calculations Table -->
                        <div class="space-y-2">
                            <div class="d-flex justify-content-between mb-2 small">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-bold" id="displaySubtotal">৳{{ number_format($totals['subtotal'], 2) }}</span>
                            </div>

                            @if($totals['coupon'])
                                <div class="d-flex justify-content-between mb-2 small text-success">
                                    <span>Coupon Discount ({{ $totals['coupon']->code }})</span>
                                    <span class="fw-bold" id="displayDiscount">-৳{{ number_format($totals['discount_amount'], 2) }}</span>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between mb-2 small">
                                <div>
                                    <span class="text-muted">Delivery Charge</span>
                                    <small class="d-block text-muted" id="shippingZoneText">
                                        {{ $totals['shipping_zone'] === 'inside_dhaka' ? 'Inside Dhaka City' : 'Outside Dhaka (Nationwide)' }}
                                    </small>
                                </div>
                                <span class="fw-bold" id="displayShipping">
                                    @if($totals['free_shipping_eligible'])
                                        <span class="text-success">FREE</span>
                                    @else
                                        ৳{{ number_format($totals['shipping_cost'], 2) }}
                                    @endif
                                </span>
                            </div>

                            <div class="d-flex justify-content-between pt-3 border-top mt-3 mb-4">
                                <div>
                                    <span class="h5 fw-bold mb-0 text-dark">Total Amount</span>
                                    <small class="d-block text-muted">Cash on Delivery</small>
                                </div>
                                <span class="h4 fw-bold text-shop-primary mb-0" id="displayTotal">
                                    ৳{{ number_format($totals['total_amount'], 2) }}
                                </span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" id="submitOrderBtn" class="btn btn-shop-primary btn-lg fw-bold py-3 shadow-sm">
                                <i class="bi bi-bag-check-fill me-2"></i> Confirm Order (Cash on Delivery)
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <small class="text-muted d-block">
                                By placing this order, you agree to ShopBD's
                                <a href="{{ route('pages.terms') }}" target="_blank" class="text-decoration-none">Terms</a> and
                                <a href="{{ route('pages.return') }}" target="_blank" class="text-decoration-none">Return Policy</a>.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function updateDistrictShipping(district) {
        if (!district) return;

        fetch("{{ route('checkout.calculate') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            },
            body: JSON.stringify({ district: district })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update Zone text
                const zoneText = document.getElementById('shippingZoneText');
                if (zoneText) {
                    zoneText.innerText = data.shipping_zone === 'inside_dhaka' ? 'Inside Dhaka City' : 'Outside Dhaka (Nationwide)';
                }

                // Update Shipping cost display
                const shippingElem = document.getElementById('displayShipping');
                if (shippingElem) {
                    if (data.free_shipping_eligible) {
                        shippingElem.innerHTML = '<span class="text-success">FREE</span>';
                    } else {
                        shippingElem.innerText = '৳' + parseFloat(data.shipping_cost).toFixed(2);
                    }
                }

                // Update Total display
                const totalElem = document.getElementById('displayTotal');
                if (totalElem) {
                    totalElem.innerText = '৳' + parseFloat(data.total_amount).toFixed(2);
                }
            }
        })
        .catch(err => console.error('Error updating shipping:', err));
    }

    function handleCheckoutSubmit(form) {
        const btn = document.getElementById('submitOrderBtn');
        if (btn.disabled) {
            return false;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Placing Order...';
        return true;
    }
</script>
@endsection
