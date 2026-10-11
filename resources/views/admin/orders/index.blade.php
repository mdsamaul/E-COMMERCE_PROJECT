@extends('layouts.admin')

@section('title', 'Manage Orders - Admin Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Orders & Sales</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-dark mb-0">Customer Orders</h1>
            <p class="text-muted small mb-0">Track, fulfill, update order statuses, and monitor inventory movements.</p>
        </div>
    </div>

    <!-- Quick Status Tabs -->
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('admin.orders.index') }}" class="card text-decoration-none border-0 shadow-sm h-100 text-center p-3 {{ !request('status') ? 'border-primary border-bottom border-3 bg-light' : '' }}">
                <div class="text-muted small fw-semibold">All Orders</div>
                <div class="fs-4 fw-bold text-dark">{{ $counts['all'] }}</div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="card text-decoration-none border-0 shadow-sm h-100 text-center p-3 {{ request('status') === 'pending' ? 'border-warning border-bottom border-3 bg-warning-subtle' : '' }}">
                <div class="text-warning small fw-semibold">Pending</div>
                <div class="fs-4 fw-bold text-dark">{{ $counts['pending'] }}</div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}" class="card text-decoration-none border-0 shadow-sm h-100 text-center p-3 {{ request('status') === 'processing' ? 'border-primary border-bottom border-3 bg-primary-subtle' : '' }}">
                <div class="text-primary small fw-semibold">Processing</div>
                <div class="fs-4 fw-bold text-dark">{{ $counts['processing'] }}</div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}" class="card text-decoration-none border-0 shadow-sm h-100 text-center p-3 {{ request('status') === 'shipped' ? 'border-info border-bottom border-3 bg-info-subtle' : '' }}">
                <div class="text-info small fw-semibold">Shipped</div>
                <div class="fs-4 fw-bold text-dark">{{ $counts['shipped'] }}</div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}" class="card text-decoration-none border-0 shadow-sm h-100 text-center p-3 {{ request('status') === 'delivered' ? 'border-success border-bottom border-3 bg-success-subtle' : '' }}">
                <div class="text-success small fw-semibold">Delivered</div>
                <div class="fs-4 fw-bold text-dark">{{ $counts['delivered'] }}</div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" class="card text-decoration-none border-0 shadow-sm h-100 text-center p-3 {{ request('status') === 'cancelled' ? 'border-danger border-bottom border-3 bg-danger-subtle' : '' }}">
                <div class="text-danger small fw-semibold">Cancelled</div>
                <div class="fs-4 fw-bold text-dark">{{ $counts['cancelled'] }}</div>
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Search Order / Customer</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Order #, name, phone...">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Order Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Payment Status</label>
                    <select name="payment_status" class="form-select form-select-sm">
                        <option value="">All Payments</option>
                        <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Shipping Zone</label>
                    <select name="zone" class="form-select form-select-sm">
                        <option value="">All Zones</option>
                        <option value="dhaka" {{ request('zone') === 'dhaka' ? 'selected' : '' }}>Inside Dhaka</option>
                        <option value="outside_dhaka" {{ request('zone') === 'outside_dhaka' ? 'selected' : '' }}>Outside Dhaka</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Apply Filter
                    </button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table Card -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th style="width: 140px;">Order #</th>
                        <th>Customer / Destination</th>
                        <th>Items Summary</th>
                        <th class="text-center" style="width: 130px;">Amount & Payment</th>
                        <th class="text-center" style="width: 120px;">Order Status</th>
                        <th class="text-center" style="width: 120px;">Date</th>
                        <th class="text-end" style="width: 160px;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-bold text-primary text-decoration-none">
                                    #{{ $order->order_number }}
                                </a>
                                <div class="text-muted small">
                                    {{ $order->payment_method === 'cod' ? 'Cash on Delivery' : strtoupper($order->payment_method) }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $order->recipient_name }}</div>
                                <div class="text-muted small">
                                    <i class="bi bi-telephone me-1"></i>{{ $order->phone }} &bull; {{ $order->district }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                    {{ $order->items->sum('quantity') }} items ({{ $order->items->count() }} unique)
                                </span>
                                @if($order->items->isNotEmpty())
                                    <div class="text-muted small text-truncate" style="max-width: 240px;">
                                        {{ $order->items->first()->product_name }}
                                        @if($order->items->count() > 1)
                                            <span class="fst-italic">+{{ $order->items->count() - 1 }} more</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="fw-bold text-dark">৳{{ number_format($order->total_amount, 2) }}</div>
                                <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} px-2 py-0 small">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @php
                                    $statusBadge = match($order->order_status) {
                                        'pending' => 'bg-warning text-dark',
                                        'confirmed' => 'bg-info text-white',
                                        'processing' => 'bg-primary text-white',
                                        'shipped' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                        'delivered' => 'bg-success text-white',
                                        'cancelled' => 'bg-danger text-white',
                                        'returned' => 'bg-secondary text-white',
                                        default => 'bg-light text-dark',
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge }} px-2 py-1">
                                    {{ ucfirst($order->order_status) }}
                                </span>
                            </td>
                            <td class="text-center text-muted small">
                                <div>{{ $order->created_at->format('d M, Y') }}</div>
                                <div class="text-xs text-muted">{{ $order->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-primary" title="Fulfill & Manage Order">
                                        <i class="bi bi-gear-fill me-1"></i> Manage
                                    </a>
                                    <a href="{{ route('admin.orders.invoice', $order->id) }}" class="btn btn-outline-secondary" target="_blank" title="Print Invoice">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted mb-2"><i class="bi bi-inbox fs-1"></i></div>
                                <h6 class="fw-bold text-dark">No orders found</h6>
                                <p class="text-muted small mb-0">No customer orders matched your selected filter parameters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
