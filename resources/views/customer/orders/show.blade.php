@extends('layouts.app')

@section('title', 'Order #' . $order->order_number . ' — ShopBD')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customer.profile') }}" class="text-decoration-none">My Account</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customer.orders.index') }}" class="text-decoration-none">My Orders</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $order->order_number }}</li>
        </ol>
    </nav>

    <!-- Header & Action Buttons -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="h3 fw-bold mb-0">Order Details</h1>
                <span class="badge bg-secondary font-monospace">{{ $order->order_number }}</span>
            </div>
            <p class="text-muted small mb-0">Placed on {{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('customer.orders.invoice', $order->order_number) }}" target="_blank" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-printer me-1"></i> Print Invoice
            </a>
            @if($order->canBeCancelledByCustomer())
                <form method="POST" action="{{ route('customer.orders.cancel', $order->order_number) }}" onsubmit="return confirm('Are you sure you want to cancel this order? Any reserved inventory will be restocked.');">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-x-circle me-1"></i> Cancel Order
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Status Timeline Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3">Order Status</h5>
            @if(in_array($order->order_status, ['cancelled', 'returned']))
                <div class="alert alert-danger mb-0 border-0 shadow-sm d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    <div>
                        <strong>This order has been {{ strtoupper($order->order_status) }}.</strong>
                        <div class="small">If you need further assistance or wish to re-order, please contact our support hotline.</div>
                    </div>
                </div>
            @else
                <!-- Stepper -->
                @php
                    $steps = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
                    $currentIndex = array_search($order->order_status, $steps);
                    if ($currentIndex === false) $currentIndex = 0;
                @endphp
                <div class="position-relative m-4">
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($currentIndex / (count($steps) - 1)) * 100 }}%;"></div>
                    </div>
                    <div class="position-absolute top-0 start-0 translate-middle-y w-100 d-flex justify-content-between">
                        @foreach($steps as $idx => $step)
                            @php
                                $isCompleted = $idx <= $currentIndex;
                                $isCurrent = $idx === $currentIndex;
                            @endphp
                            <div class="text-center" style="width: 80px;">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm {{ $isCompleted ? 'bg-success text-white' : 'bg-light text-secondary border' }}" style="width: 34px; height: 34px;">
                                    @if($isCompleted)
                                        <i class="bi bi-check-lg"></i>
                                    @else
                                        <span class="small fw-bold">{{ $idx + 1 }}</span>
                                    @endif
                                </div>
                                <span class="d-block small mt-2 fw-semibold {{ $isCurrent ? 'text-success fw-bold' : ($isCompleted ? 'text-dark' : 'text-muted') }}">
                                    {{ ucfirst($step) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($order->courier_tracking_id)
        <!-- Courier Tracking Card -->
        <div class="card border-0 shadow-sm mb-4 border-start border-4 border-primary">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary-subtle text-primary p-3">
                            <i class="bi bi-truck fs-3"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1 text-dark">Courier Shipment in Progress</h6>
                            <div class="small text-muted">
                                Courier Partner: <strong class="text-dark">{{ strtoupper($order->courier_provider) }}</strong>
                                &bull; Tracking ID: <span class="badge bg-light text-dark font-monospace border px-2 py-1">{{ $order->courier_tracking_id }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="badge {{ $order->courier_status === 'delivered' ? 'bg-success' : 'bg-primary text-white' }} px-3 py-2 text-uppercase fs-6">
                            Status: {{ $order->courier_status ?? 'In Transit' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- Delivery Details (Left) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0"><i class="bi bi-geo-alt me-2 text-shop-primary"></i> Delivery Information</h6>
                </div>
                <div class="card-body p-4">
                    <p class="mb-1 fw-bold text-dark fs-6">{{ $order->recipient_name }}</p>
                    <p class="mb-1 text-secondary small">
                        <i class="bi bi-telephone me-1"></i> {{ $order->phone }}
                        @if($order->alternative_phone)
                            <span class="text-muted">/ {{ $order->alternative_phone }}</span>
                        @endif
                    </p>
                    <p class="mb-1 text-secondary small">
                        <i class="bi bi-house me-1"></i> {{ $order->delivery_address }}
                    </p>
                    <p class="mb-1 text-secondary small">
                        <strong>District:</strong> {{ $order->district }} ({{ $order->shipping_zone === 'inside_dhaka' ? 'Inside Dhaka' : 'Outside Dhaka' }})
                    </p>
                    @if($order->delivery_notes)
                        <div class="alert alert-light border mt-3 mb-0 small">
                            <strong>Delivery Note:</strong> "{{ $order->delivery_notes }}"
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment & Status Summary (Right) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0"><i class="bi bi-credit-card me-2 text-shop-primary"></i> Payment & Billing</h6>
                </div>
                <div class="card-body p-4">
                    @php
                        $paymentMethodName = match($order->payment_method) {
                            'cod' => 'Cash on Delivery (COD)',
                            'sslcommerz' => 'SSLCOMMERZ Online Gateway',
                            'bkash' => 'bKash Merchant Payment',
                            default => strtoupper($order->payment_method),
                        };
                    @endphp
                    <div class="row g-3">
                        <div class="col-6">
                            <span class="text-muted small d-block">Payment Method:</span>
                            <strong class="text-dark">{{ $paymentMethodName }}</strong>
                            @if($order->gateway_transaction_id)
                                <div class="text-xs font-monospace text-muted mt-1">Txn: {{ $order->gateway_transaction_id }}</div>
                            @endif
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block">Payment Status:</span>
                            <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} text-uppercase px-2 py-1">
                                {{ $order->payment_status }}
                            </span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block">Order Status:</span>
                            <span class="badge bg-primary text-uppercase px-2 py-1">
                                {{ $order->order_status }}
                            </span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block">Total Due:</span>
                            <span class="h5 fw-bold text-shop-primary mb-0">৳{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                    @if($order->payment_status === 'unpaid' && $order->payment_method === 'cod')
                        <div class="alert alert-warning-subtle text-dark border-0 py-2 px-3 small mt-3 mb-0 rounded">
                            <i class="bi bi-info-circle me-1 text-warning"></i>
                            Please have <strong>৳{{ number_format($order->total_amount, 2) }}</strong> cash ready for our delivery courier upon arrival.
                        </div>
                    @elseif($order->payment_status === 'paid')
                        <div class="alert alert-success-subtle text-success border-0 py-2 px-3 small mt-3 mb-0 rounded">
                            <i class="bi bi-check-circle me-1"></i>
                            Payment verified and settled. Thank you for your purchase!
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Items List Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="fw-bold mb-0">Purchased Items ({{ $order->items->count() }})</h5>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light small text-uppercase text-secondary">
                    <tr>
                        <th style="min-width: 280px;">Product</th>
                        <th>SKU</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-end">Subtotal</th>
                        @if($order->order_status === 'delivered')
                            <th class="text-center" style="width: 150px;">Review</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        @php $product = $item->product; @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 54px; height: 54px; background: #f8fafc; border-radius: 6px; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        @if($product && $product->primaryImage)
                                            <img src="{{ $product->primaryImage->url }}" alt="{{ $item->product_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <i class="bi bi-card-image text-muted fs-4"></i>
                                        @endif
                                    </div>
                                    <div>
                                        @if($product)
                                            <a href="{{ route('products.show', $product->slug) }}" class="text-dark fw-bold text-decoration-none hover-primary">
                                                {{ $item->product_name }}
                                            </a>
                                        @else
                                            <span class="text-dark fw-bold">{{ $item->product_name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td><code class="text-muted">{{ $item->product_sku }}</code></td>
                            <td class="text-center fw-semibold">৳{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-center fw-bold">{{ $item->quantity }}</td>
                            <td class="text-end fw-bold text-dark">৳{{ number_format($item->subtotal, 2) }}</td>
                            @if($order->order_status === 'delivered')
                                <td class="text-center">
                                    @if(in_array($item->product_id, $reviewedProductIds))
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-star-fill me-1"></i> Reviewed
                                        </span>
                                    @else
                                        <!-- Review Button triggering accordion/inline form -->
                                        <button class="btn btn-sm btn-outline-warning text-dark fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#reviewForm{{ $item->id }}">
                                            <i class="bi bi-star me-1 text-warning"></i> Review
                                        </button>
                                    @endif
                                </td>
                            @endif
                        </tr>
                        @if($order->order_status === 'delivered' && !in_array($item->product_id, $reviewedProductIds))
                            <tr class="collapse" id="reviewForm{{ $item->id }}">
                                <td colspan="6" class="bg-light p-3">
                                    <form method="POST" action="{{ route('customer.reviews.store') }}" class="card p-3 border">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                        <h6 class="fw-bold mb-2">Write a Verified Buyer Review for: {{ $item->product_name }}</h6>
                                        <div class="row g-2 align-items-center mb-2">
                                            <div class="col-auto">
                                                <label class="form-label small fw-bold mb-0">Rating:</label>
                                            </div>
                                            <div class="col-auto">
                                                <select name="rating" class="form-select form-select-sm" required style="width: 140px;">
                                                    <option value="5">★★★★★ 5 Stars</option>
                                                    <option value="4">★★★★☆ 4 Stars</option>
                                                    <option value="3">★★★☆☆ 3 Stars</option>
                                                    <option value="2">★★☆☆☆ 2 Stars</option>
                                                    <option value="1">★☆☆☆☆ 1 Star</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <textarea name="comment" rows="2" class="form-control form-control-sm" placeholder="Share your experience with this product (quality, fit, delivery speed...)" required></textarea>
                                        </div>
                                        <div>
                                            <button type="submit" class="btn btn-sm btn-shop-primary">
                                                <i class="bi bi-send me-1"></i> Submit Review for Moderation
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white p-4">
            <div class="row justify-content-end">
                <div class="col-md-5">
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-bold">৳{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                        <div class="d-flex justify-content-between mb-2 small text-success">
                            <span>Coupon Discount ({{ $order->coupon?->code ?? 'Promo' }}):</span>
                            <span class="fw-bold">-৳{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Delivery Charge:</span>
                        <span class="fw-bold">
                            @if($order->shipping_cost <= 0)
                                <span class="text-success">FREE</span>
                            @else
                                ৳{{ number_format($order->shipping_cost, 2) }}
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between pt-2 border-top mt-2 fs-5">
                        <span class="fw-bold text-dark">Total Amount:</span>
                        <span class="fw-bold text-shop-primary">৳{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status History Audit Trail -->
    @if($order->statusHistories->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-shop-primary"></i> Order Tracking Timeline</h6>
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled mb-0">
                    @foreach($order->statusHistories as $history)
                        <li class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom last-border-0">
                            <i class="bi bi-circle-fill text-shop-primary small mt-1"></i>
                            <div>
                                <strong class="text-dark text-uppercase small">{{ $history->to_status }}</strong>
                                <span class="text-muted small ms-2">{{ $history->created_at->format('M d, Y h:i A') }}</span>
                                @if($history->remarks)
                                    <div class="text-secondary small mt-1">{{ $history->remarks }}</div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>
@endsection
