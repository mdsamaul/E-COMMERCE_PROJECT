@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page_title', 'ShopBD Executive Dashboard')
@section('page_subtitle', 'Real-time sales, order fulfillment, and catalog status')

@section('content')
<div class="row g-4 mb-4">
    <!-- Total Revenue -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small text-uppercase fw-bold">Total Sales</span>
                <span class="badge bg-primary-subtle text-primary p-2 rounded-circle">
                    <i class="bi bi-currency-dollar fs-5"></i>
                </span>
            </div>
            <h3 class="fw-bold mb-1">৳{{ number_format($stats['total_sales'], 2) }}</h3>
            <small class="text-success"><i class="bi bi-shield-check me-1"></i>From paid orders</small>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card border-start border-4 border-info">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small text-uppercase fw-bold">Total Orders</span>
                <span class="badge bg-info-subtle text-info p-2 rounded-circle">
                    <i class="bi bi-cart-check fs-5"></i>
                </span>
            </div>
            <h3 class="fw-bold mb-1">{{ number_format($stats['total_orders']) }}</h3>
            <small class="text-muted">{{ $stats['pending_orders'] }} pending fulfillment</small>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small text-uppercase fw-bold">Low Stock Items</span>
                <span class="badge bg-warning-subtle text-warning p-2 rounded-circle">
                    <i class="bi bi-exclamation-triangle fs-5"></i>
                </span>
            </div>
            <h3 class="fw-bold mb-1">{{ number_format($stats['low_stock_count']) }}</h3>
            <a href="{{ route('admin.products.stock', ['filter' => 'low_stock']) }}" class="small text-decoration-none text-warning fw-semibold">
                View in Inventory &rarr;
            </a>
        </div>
    </div>

    <!-- Total Customers -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small text-uppercase fw-bold">Customers</span>
                <span class="badge bg-success-subtle text-success p-2 rounded-circle">
                    <i class="bi bi-people fs-5"></i>
                </span>
            </div>
            <h3 class="fw-bold mb-1">{{ number_format($stats['total_customers']) }}</h3>
            <a href="{{ route('admin.customers.index') }}" class="small text-decoration-none text-success fw-semibold">
                Manage Customers &rarr;
            </a>
        </div>
    </div>
</div>

<!-- Quick Actions & Recent Orders -->
<div class="row g-4">
    <!-- Recent Orders Table -->
    <div class="col-lg-8">
        <div class="table-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Orders</h6>
                <a href="{{ route('admin.reports.sales') }}" class="btn btn-sm btn-outline-primary">View Sales Report</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Total (BDT)</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr>
                                <td>
                                    <span class="fw-bold font-monospace">#{{ $order->order_number }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $order->user ? $order->user->name : 'Guest User' }}</div>
                                    <small class="text-muted">{{ $order->phone }}</small>
                                </td>
                                <td class="fw-bold text-dark">
                                    ৳{{ number_format($order->total_amount, 2) }}
                                </td>
                                <td>
                                    @if($order->order_status === 'delivered')
                                        <span class="badge bg-success-subtle text-success badge-subtle">Delivered</span>
                                    @elseif($order->order_status === 'shipped')
                                        <span class="badge bg-primary-subtle text-primary badge-subtle">Shipped</span>
                                    @elseif($order->order_status === 'cancelled')
                                        <span class="badge bg-danger-subtle text-danger badge-subtle">Cancelled</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning badge-subtle">Pending</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $order->created_at->format('d M, Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-1 text-secondary"></i>
                                    No orders placed yet. Orders will appear here in real-time.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Shortcuts Card -->
    <div class="col-lg-4">
        <div class="table-card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-lightning-charge text-warning me-2"></i>Quick Management</h6>
            <div class="d-grid gap-2">
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary text-start">
                    <i class="bi bi-plus-circle me-2"></i> Add New Product
                </a>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-outline-secondary text-start">
                    <i class="bi bi-folder-plus me-2"></i> Create Category
                </a>
                <a href="{{ route('admin.brands.create') }}" class="btn btn-outline-secondary text-start">
                    <i class="bi bi-tag me-2"></i> Create Brand
                </a>
                <a href="{{ route('admin.coupons.create') }}" class="btn btn-outline-secondary text-start">
                    <i class="bi bi-ticket-perforated me-2"></i> Create Promo Coupon
                </a>
                <a href="{{ route('admin.banners.create') }}" class="btn btn-outline-secondary text-start">
                    <i class="bi bi-image me-2"></i> Add Hero Banner
                </a>
                <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-dark text-start">
                    <i class="bi bi-sliders me-2"></i> Configure Delivery Charges
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
