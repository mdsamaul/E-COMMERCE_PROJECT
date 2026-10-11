@extends('layouts.app')

@section('title', 'My Orders — ShopBD')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customer.profile') }}" class="text-decoration-none">My Account</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Orders</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom gap-2">
        <div>
            <h1 class="h3 fw-bold mb-0">My Orders</h1>
            <p class="text-muted small mb-0">Track shipments, view receipts, and review purchased products</p>
        </div>
        <div>
            <a href="{{ route('customer.wishlist.index') }}" class="btn btn-outline-danger btn-sm me-2">
                <i class="bi bi-heart me-1"></i> My Wishlist
            </a>
            <a href="{{ route('customer.profile') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-person me-1"></i> Profile & Addresses
            </a>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('customer.orders.index') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Search by order number (e.g. ORD-2026...)" value="{{ request('q') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Order Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Confirmation</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing / Packing</option>
                        <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped / On the Way</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                    </select>
                </div>
                <div class="col-md-3 text-md-end">
                    <button type="submit" class="btn btn-sm btn-shop-primary me-1">Filter</button>
                    @if(request()->hasAny(['q', 'status']))
                        <a href="{{ route('customer.orders.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if($orders->isEmpty())
        <!-- Empty State -->
        <div class="card border-0 shadow-sm text-center py-5 my-3">
            <div class="card-body py-5">
                <i class="bi bi-bag-x text-muted" style="font-size: 4rem;"></i>
                <h4 class="fw-bold mt-3 text-dark">No Orders Found</h4>
                <p class="text-muted mx-auto" style="max-width: 420px;">
                    @if(request()->hasAny(['q', 'status']))
                        No orders matched your search criteria. Try resetting the filters.
                    @else
                        You haven't placed any orders yet. Discover our premium collections and place your first Cash on Delivery order!
                    @endif
                </p>
                <a href="{{ route('products.index') }}" class="btn btn-shop-primary px-4 mt-2">
                    <i class="bi bi-bag-plus me-1"></i> Start Shopping
                </a>
            </div>
        </div>
    @else
        <!-- Orders List -->
        <div class="space-y-3">
            @foreach($orders as $order)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <span class="text-muted small d-block">Order Placed</span>
                                <strong class="text-dark small">{{ $order->created_at->format('M d, Y') }}</strong>
                            </div>
                            <div class="border-start ps-3">
                                <span class="text-muted small d-block">Total Amount</span>
                                <strong class="text-shop-primary small">৳{{ number_format($order->total_amount, 2) }}</strong>
                            </div>
                            <div class="border-start ps-3 d-none d-sm-block">
                                <span class="text-muted small d-block">Recipient</span>
                                <span class="text-dark small fw-semibold">{{ $order->recipient_name }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small d-none d-md-inline">Order #:</span>
                            <span class="font-monospace fw-bold small text-secondary">{{ $order->order_number }}</span>
                        </div>
                    </div>

                    <div class="card-body p-3 p-md-4">
                        <div class="row align-items-center g-3">
                            <!-- Items Thumbnail Strip & Titles -->
                            <div class="col-md-7">
                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    @foreach($order->items->take(3) as $item)
                                        <div class="d-flex align-items-center gap-2" style="max-width: 200px;">
                                            <div style="width: 50px; height: 50px; background: #f8fafc; border-radius: 6px; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                @if($item->product && $item->product->primaryImage)
                                                    <img src="{{ $item->product->primaryImage->url }}" alt="{{ $item->product_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                @else
                                                    <i class="bi bi-card-image text-muted"></i>
                                                @endif
                                            </div>
                                            <div class="small">
                                                <span class="text-truncate d-block fw-semibold text-dark">{{ $item->product_name }}</span>
                                                <span class="text-muted">Qty: {{ $item->quantity }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($order->items->count() > 3)
                                        <span class="badge bg-light text-secondary border">+{{ $order->items->count() - 3 }} more</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Order Status Badge & Payment Status -->
                            <div class="col-md-2 text-md-center">
                                @php
                                    $statusBadge = match($order->order_status) {
                                        'pending' => 'bg-warning text-dark',
                                        'confirmed' => 'bg-info text-dark',
                                        'processing' => 'bg-primary text-white',
                                        'shipped' => 'bg-indigo text-white bg-opacity-75',
                                        'delivered' => 'bg-success text-white',
                                        'cancelled' => 'bg-danger text-white',
                                        'returned' => 'bg-secondary text-white',
                                        default => 'bg-light text-dark'
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge }} text-uppercase px-2 py-1 mb-1 d-inline-block">
                                    {{ $order->order_status }}
                                </span>
                                <small class="d-block text-muted">
                                    {{ $order->payment_method === 'cod' ? 'COD' : strtoupper($order->payment_method) }}
                                    ({{ ucfirst($order->payment_status) }})
                                </small>
                            </div>

                            <!-- Actions -->
                            <div class="col-md-3 text-md-end">
                                <div class="d-flex flex-column flex-sm-row justify-content-md-end gap-2">
                                    <a href="{{ route('customer.orders.show', $order->order_number) }}" class="btn btn-sm btn-shop-primary fw-semibold">
                                        View Details
                                    </a>
                                    <a href="{{ route('customer.orders.invoice', $order->order_number) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print Invoice">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $orders->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
