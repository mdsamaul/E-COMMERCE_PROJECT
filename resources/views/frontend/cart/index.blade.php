@extends('layouts.app')

@section('title', 'Shopping Cart — ShopBD')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
        </ol>
    </nav>

    <!-- Page Title -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 fw-bold mb-0">Shopping Cart</h1>
            <p class="text-muted small mb-0">Review your selected items before proceeding to checkout</p>
        </div>
        @if($items->isNotEmpty())
            <div>
                <form method="POST" action="{{ route('cart.clear') }}" onsubmit="return confirm('Are you sure you want to remove all items from your cart?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash3 me-1"></i> Clear Cart
                    </button>
                </form>
            </div>
        @endif
    </div>

    @if(!empty($warnings))
        @foreach($warnings as $warning)
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $warning }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endforeach
    @endif

    @if($items->isEmpty())
        <!-- Empty State -->
        <div class="card border-0 shadow-sm text-center py-5 my-4">
            <div class="card-body py-5">
                <div class="mb-4">
                    <i class="bi bi-cart-x text-muted" style="font-size: 5rem;"></i>
                </div>
                <h3 class="fw-bold text-dark">Your Shopping Cart is Empty</h3>
                <p class="text-muted mx-auto" style="max-width: 460px;">
                    Looks like you haven't added any products to your cart yet. Browse our multi-category collection of electronics, fashion, groceries, and more!
                </p>
                <div class="mt-4">
                    <a href="{{ route('products.index') }}" class="btn btn-shop-primary btn-lg px-4 shadow-sm">
                        <i class="bi bi-bag-plus me-2"></i> Start Shopping Now
                    </a>
                </div>
            </div>
        </div>
    @else
        <!-- Cart Items & Summary Layout -->
        <div class="row g-4">
            <!-- Items Column (Left) -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark">Cart Items ({{ $totals['items_count'] }})</span>
                            <span class="text-muted small">Standard Delivery Available Across Bangladesh</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light small text-uppercase text-secondary">
                                <tr>
                                    <th style="min-width: 280px;">Product</th>
                                    <th class="text-center" style="width: 120px;">Price</th>
                                    <th class="text-center" style="width: 140px;">Quantity</th>
                                    <th class="text-end" style="width: 130px;">Subtotal</th>
                                    <th class="text-center" style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    @php $product = $item->product; @endphp
                                    <tr>
                                        <!-- Product info -->
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <a href="{{ route('products.show', $product->slug) }}" class="flex-shrink-0" style="width: 64px; height: 64px; background: #f8fafc; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                                    @if($product->primaryImage)
                                                        <img src="{{ $product->primaryImage->url }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                    @else
                                                        <i class="bi bi-card-image text-muted fs-4"></i>
                                                    @endif
                                                </a>
                                                <div>
                                                    <a href="{{ route('products.show', $product->slug) }}" class="text-dark fw-bold text-decoration-none hover-primary d-block">
                                                        {{ $product->name }}
                                                    </a>
                                                    <div class="small text-muted mt-1">
                                                        <span class="badge bg-light text-secondary border me-1">SKU: {{ $product->sku }}</span>
                                                        @if($product->stock_quantity <= 5)
                                                            <span class="text-warning fw-semibold">Only {{ $product->stock_quantity }} left</span>
                                                        @else
                                                            <span class="text-success fw-semibold">In Stock</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Unit Price -->
                                        <td class="text-center fw-semibold">
                                            ৳{{ number_format($item->unit_price, 2) }}
                                        </td>

                                        <!-- Quantity update -->
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('cart.update', $item->id) }}" class="d-inline-flex align-items-center">
                                                @csrf
                                                @method('PUT')
                                                <div class="input-group input-group-sm" style="width: 110px;">
                                                    <button class="btn btn-outline-secondary" type="button" onclick="changeQty(this, -1)">-</button>
                                                    <input type="number" name="quantity" class="form-control text-center qty-input" value="{{ $item->quantity }}" min="1" max="{{ $product->stock_quantity }}" onchange="this.form.submit()">
                                                    <button class="btn btn-outline-secondary" type="button" onclick="changeQty(this, 1)">+</button>
                                                </div>
                                            </form>
                                        </td>

                                        <!-- Line Subtotal -->
                                        <td class="text-end fw-bold text-dark fs-6">
                                            ৳{{ number_format($item->subtotal, 2) }}
                                        </td>

                                        <!-- Delete item -->
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('cart.destroy', $item->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0 border-0" title="Remove item" onclick="return confirm('Remove {{ $product->name }} from cart?');">
                                                    <i class="bi bi-trash fs-5"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Continue Shopping
                    </a>
                </div>
            </div>

            <!-- Order Summary Column (Right) -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="fw-bold mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <!-- Subtotal -->
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal ({{ $totals['items_count'] }} items)</span>
                            <span class="fw-bold">৳{{ number_format($totals['subtotal'], 2) }}</span>
                        </div>

                        <!-- Coupon Form & Discount -->
                        <div class="py-3 border-top border-bottom my-3">
                            @if($totals['coupon'])
                                <div class="d-flex justify-content-between align-items-center bg-success-subtle p-2 rounded mb-2">
                                    <div>
                                        <i class="bi bi-tag-fill text-success me-1"></i>
                                        <strong class="text-success">{{ $totals['coupon']->code }}</strong>
                                        <small class="d-block text-muted">Coupon Discount Applied</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold text-success">-৳{{ number_format($totals['discount_amount'], 2) }}</span>
                                        <form method="POST" action="{{ route('cart.coupon.remove') }}" class="d-inline ms-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm text-danger p-0 border-0" title="Remove Coupon">
                                                <i class="bi bi-x-circle-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <form method="POST" action="{{ route('cart.coupon.apply') }}" class="mb-1">
                                    @csrf
                                    <label class="form-label small fw-bold mb-1">Have a Coupon Code?</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="coupon_code" class="form-control text-uppercase" placeholder="Enter coupon" required>
                                        <button class="btn btn-shop-primary" type="submit">Apply</button>
                                    </div>
                                    @error('coupon_code')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </form>
                            @endif
                        </div>

                        <!-- Shipping Estimate Note -->
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Estimated Delivery</span>
                            <span class="text-dark fw-semibold">
                                @if($totals['free_shipping_eligible'])
                                    <span class="text-success fw-bold">FREE</span>
                                @else
                                    ৳{{ number_format($totals['shipping_cost'], 2) }}
                                    <small class="text-muted d-block text-end" style="font-size: 0.75rem;">(Dhaka City: ৳60 / Outside: ৳120)</small>
                                @endif
                            </span>
                        </div>

                        @if($totals['free_shipping_threshold'] > 0 && !$totals['free_shipping_eligible'])
                            <div class="alert alert-info py-2 px-3 small border-0 mb-3 mt-2">
                                <i class="bi bi-info-circle me-1"></i> Add <strong>৳{{ number_format($totals['free_shipping_threshold'] - $totals['subtotal'], 2) }}</strong> more for <strong>FREE Delivery</strong>!
                            </div>
                        @endif

                        <!-- Grand Total -->
                        <div class="d-flex justify-content-between pt-3 border-top mt-3 mb-4">
                            <span class="h5 fw-bold mb-0">Total Amount</span>
                            <span class="h4 fw-bold text-shop-primary mb-0">৳{{ number_format($totals['total_amount'], 2) }}</span>
                        </div>

                        <!-- Proceed to Checkout Button -->
                        <div class="d-grid gap-2">
                            <a href="{{ route('checkout.index') }}" class="btn btn-shop-primary btn-lg fw-bold shadow-sm py-3">
                                Proceed to Checkout <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="bi bi-shield-check text-success me-1"></i> 100% Secure Checkout Guarantee
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Trust Badges Box -->
                <div class="card border-0 bg-light p-3 rounded-3 small">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-truck text-shop-primary fs-5"></i>
                        <span>Nationwide Delivery: 24-48 hrs in Dhaka, 3-5 days outside Dhaka</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-cash-coin text-success fs-5"></i>
                        <span>Cash on Delivery (COD) available for all districts</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-repeat text-warning fs-5"></i>
                        <span>7-Day Return & Replacement Policy</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    function changeQty(btn, delta) {
        const input = btn.closest('.input-group').querySelector('.qty-input');
        const min = parseInt(input.getAttribute('min')) || 1;
        const max = parseInt(input.getAttribute('max')) || 999;
        let current = parseInt(input.value) || 1;
        let next = current + delta;

        if (next >= min && next <= max) {
            input.value = next;
            input.form.submit();
        }
    }
</script>
@endsection
