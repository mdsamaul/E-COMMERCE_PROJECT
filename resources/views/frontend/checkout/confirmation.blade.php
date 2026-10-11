@extends('layouts.app')

@section('title', 'Order Placed Successfully — ShopBD')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Success Header Banner -->
            <div class="card border-0 shadow-sm text-center p-4 p-md-5 mb-4 bg-white">
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success" style="width: 80px; height: 80px;">
                        <i class="bi bi-check-lg" style="font-size: 3rem;"></i>
                    </div>
                </div>
                <h2 class="fw-bold text-dark mb-1">Thank You for Your Order!</h2>
                <p class="text-secondary mb-3">Your order has been placed successfully and is being prepared for dispatch.</p>
                
                <div class="d-inline-block bg-light border rounded-pill px-4 py-2 mx-auto mb-3">
                    <span class="text-muted small me-2">Order Tracking Number:</span>
                    <strong class="font-monospace text-shop-primary fs-5">{{ $order->order_number }}</strong>
                </div>

                @if(session('account_created_notice'))
                    <div class="alert alert-info border-0 shadow-sm mx-auto text-start py-2 px-3 small my-2" style="max-width: 600px;">
                        <i class="bi bi-person-check-fill me-2 fs-6"></i>
                        <strong>Account Created:</strong> {{ session('account_created_notice') }}
                    </div>
                @endif
            </div>

            <!-- Order Details Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Order Summary</h5>
                    <span class="text-muted small">{{ $order->created_at->format('M d, Y h:i A') }}</span>
                </div>
                <div class="card-body p-4">
                    <!-- Delivery & Payment Information Row -->
                    <div class="row g-4 mb-4 pb-4 border-bottom">
                        <!-- Recipient Details -->
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-secondary small mb-2">Delivery Information</h6>
                            <p class="mb-1 fw-bold text-dark">{{ $order->recipient_name }}</p>
                            <p class="mb-1 text-secondary small">
                                <i class="bi bi-telephone me-1"></i> {{ $order->phone }}
                                @if($order->alternative_phone)
                                    <span class="text-muted">/ {{ $order->alternative_phone }}</span>
                                @endif
                            </p>
                            <p class="mb-1 text-secondary small">
                                <i class="bi bi-geo-alt me-1"></i> {{ $order->delivery_address }}
                            </p>
                            <p class="mb-1 text-secondary small">
                                <strong>District:</strong> {{ $order->district }} ({{ $order->shipping_zone === 'inside_dhaka' ? 'Inside Dhaka' : 'Outside Dhaka' }})
                            </p>
                            @if($order->delivery_notes)
                                <p class="mb-0 text-muted small mt-2">
                                    <em>Note: "{{ $order->delivery_notes }}"</em>
                                </p>
                            @endif
                        </div>

                        <!-- Payment & Status Details -->
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-secondary small mb-2">Payment & Delivery Status</h6>
                            <div class="mb-2">
                                <span class="text-muted small d-block">Payment Method:</span>
                                <strong class="text-dark">Cash on Delivery (COD)</strong>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted small d-block">Payment Status:</span>
                                <span class="badge bg-warning text-dark px-2 py-1">
                                    <i class="bi bi-hourglass-split me-1"></i> Unpaid (Pay ৳{{ number_format($order->total_amount, 2) }} upon delivery)
                                </span>
                            </div>
                            <div>
                                <span class="text-muted small d-block">Order Status:</span>
                                <span class="badge bg-primary px-2 py-1 text-uppercase">
                                    {{ $order->order_status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <h6 class="fw-bold text-uppercase text-secondary small mb-3">Purchased Items ({{ $order->items->count() }})</h6>
                    <div class="table-responsive mb-4">
                        <table class="table align-middle">
                            <thead class="table-light small text-uppercase text-secondary">
                                <tr>
                                    <th>Item</th>
                                    <th>SKU</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div style="width: 48px; height: 48px; background: #f8fafc; border-radius: 6px; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                    @if($item->product && $item->product->primaryImage)
                                                        <img src="{{ $item->product->primaryImage->url }}" alt="{{ $item->product_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                    @else
                                                        <i class="bi bi-card-image text-muted"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <span class="fw-bold text-dark d-block">{{ $item->product_name }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><code class="text-muted">{{ $item->product_sku }}</code></td>
                                        <td class="text-center">৳{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                        <td class="text-end fw-bold">৳{{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Total Breakdown -->
                    <div class="row justify-content-end">
                        <div class="col-md-5">
                            <div class="bg-light p-3 rounded-3 small">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Subtotal:</span>
                                    <span class="fw-bold">৳{{ number_format($order->subtotal, 2) }}</span>
                                </div>
                                @if($order->discount_amount > 0)
                                    <div class="d-flex justify-content-between mb-2 text-success">
                                        <span>Discount:</span>
                                        <span class="fw-bold">-৳{{ number_format($order->discount_amount, 2) }}</span>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Delivery Fee:</span>
                                    <span class="fw-bold">
                                        @if($order->shipping_cost <= 0)
                                            <span class="text-success">FREE</span>
                                        @else
                                            ৳{{ number_format($order->shipping_cost, 2) }}
                                        @endif
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between pt-2 border-top mt-2 fs-6">
                                    <span class="fw-bold text-dark">Total Amount Due:</span>
                                    <span class="fw-bold text-shop-primary">৳{{ number_format($order->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Return to Homepage
                </a>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-dark" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print Receipt
                    </button>
                    @auth
                        <a href="{{ route('customer.profile') }}" class="btn btn-shop-primary">
                            <i class="bi bi-person me-1"></i> View Order in Account
                        </a>
                    @else
                        <a href="{{ route('products.index') }}" class="btn btn-shop-primary">
                            <i class="bi bi-bag-plus me-1"></i> Continue Shopping
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
