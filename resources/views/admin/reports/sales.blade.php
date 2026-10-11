@extends('layouts.admin')

@section('title', 'Sales Reports & Analytics')
@section('page_title', 'Sales Reports')
@section('page_subtitle', 'Filter revenue, track average order value, payment distribution, and top-selling items')

@section('content')
<!-- Date Range Filter Bar -->
<div class="table-card p-3 mb-4">
    <form method="GET" action="{{ route('admin.reports.sales') }}" class="row g-2 align-items-center">
        <div class="col-md-3">
            <label class="form-label small fw-bold text-muted mb-1">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label small fw-bold text-muted mb-1">End Date</label>
            <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
        </div>

        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted mb-1">Order Status</label>
            <select name="order_status" class="form-select">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('order_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="processing" {{ request('order_status') === 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="shipped" {{ request('order_status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ request('order_status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ request('order_status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted mb-1">Payment Status</label>
            <select name="payment_status" class="form-select">
                <option value="">All Payments</option>
                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
            </select>
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-funnel me-1"></i> Apply Filter
            </button>
        </div>
    </form>
</div>

<!-- Sales Metrics KPI Cards -->
<div class="row g-4 mb-4">
    <!-- Total Revenue -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card border-start border-4 border-success">
            <span class="text-muted small text-uppercase fw-bold">Period Gross Sales</span>
            <h3 class="fw-bold mb-1 text-success mt-2">৳{{ number_format($totalSales, 2) }}</h3>
            <small class="text-muted"><i class="bi bi-calendar-check me-1"></i>{{ $startDate->format('d M') }} – {{ $endDate->format('d M, Y') }}</small>
        </div>
    </div>

    <!-- Orders Count -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card border-start border-4 border-primary">
            <span class="text-muted small text-uppercase fw-bold">Orders Placed</span>
            <h3 class="fw-bold mb-1 mt-2">{{ number_format($totalOrders) }}</h3>
            <small class="text-muted">{{ $deliveredOrders }} delivered successfully</small>
        </div>
    </div>

    <!-- Average Order Value (AOV) -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card border-start border-4 border-info">
            <span class="text-muted small text-uppercase fw-bold">Average Order Value</span>
            <h3 class="fw-bold mb-1 mt-2">৳{{ number_format($averageOrderValue, 2) }}</h3>
            <small class="text-muted">Per completed paid order</small>
        </div>
    </div>

    <!-- Total Discounts Given -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card border-start border-4 border-warning">
            <span class="text-muted small text-uppercase fw-bold">Coupons / Discounts</span>
            <h3 class="fw-bold mb-1 mt-2 text-warning">৳{{ number_format($totalDiscount, 2) }}</h3>
            <small class="text-muted">Total promotions redeemed</small>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Top Selling Products in Period -->
    <div class="col-lg-7">
        <div class="table-card p-3">
            <h6 class="fw-bold mb-3 border-bottom pb-2">
                <i class="bi bi-trophy text-warning me-2"></i>Top Selling Products in Period
            </h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Qty Sold</th>
                            <th>Revenue (BDT)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $top)
                            <tr>
                                <td class="fw-semibold text-dark">{{ $top->product_name }}</td>
                                <td><code class="text-secondary small">{{ $top->sku }}</code></td>
                                <td class="fw-bold">{{ $top->total_qty }} units</td>
                                <td class="fw-bold text-success">৳{{ number_format($top->total_sales, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">No product sales in selected date range.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Payment Breakdown -->
    <div class="col-lg-5">
        <div class="table-card p-3">
            <h6 class="fw-bold mb-3 border-bottom pb-2">
                <i class="bi bi-credit-card me-2 text-primary"></i>Payment Method Breakdown
            </h6>
            <ul class="list-group list-group-flush">
                @forelse($paymentBreakdown as $pay)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <span class="text-uppercase fw-bold text-dark font-monospace">{{ $pay->payment_method }}</span>
                            <small class="text-muted d-block">{{ $pay->count }} orders</small>
                        </div>
                        <span class="fw-bold">৳{{ number_format($pay->total, 2) }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-center text-muted py-3">No orders placed in this period.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<!-- Detailed Orders Table -->
<div class="table-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2 text-primary"></i>Filtered Orders List</h6>
        <small class="text-muted">{{ $orders->total() }} total matching orders</small>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Payment</th>
                    <th>Order Status</th>
                    <th>Order Date</th>
                    <th class="text-end">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            <span class="fw-bold font-monospace">#{{ $order->order_number }}</span>
                        </td>
                        <td>{{ $order->user ? $order->user->name : 'Guest Customer' }}</td>
                        <td class="font-monospace small text-muted">{{ $order->phone }}</td>
                        <td>
                            <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} badge-subtle text-uppercase font-monospace">
                                {{ $order->payment_status }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary badge-subtle text-capitalize">
                                {{ $order->order_status }}
                            </span>
                        </td>
                        <td class="text-muted small">
                            {{ $order->created_at->format('d M, Y h:i A') }}
                        </td>
                        <td class="text-end fw-bold text-dark">
                            ৳{{ number_format($order->total_amount, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary"></i>
                            No orders found matching the filter range.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
