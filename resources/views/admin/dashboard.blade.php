@extends('layouts.admin')

@section('title', 'Control Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-0">ShopBD Executive Dashboard</h2>
        <p class="text-muted small mb-0">Store performance overview and real-time operations in Bangladesh.</p>
    </div>
    <div>
        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
            <i class="bi bi-clock-history me-1"></i> Timezone: Asia/Dhaka
        </span>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="stat-card p-3">
            <div class="text-muted small fw-semibold">Paid Sales</div>
            <div class="fs-4 fw-bold text-success mt-1">৳{{ number_format($stats['total_sales'], 2) }}</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="stat-card p-3">
            <div class="text-muted small fw-semibold">Total Orders</div>
            <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_orders'] }}</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="stat-card p-3">
            <div class="text-muted small fw-semibold">Pending Orders</div>
            <div class="fs-4 fw-bold text-warning mt-1">{{ $stats['pending_orders'] }}</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="stat-card p-3">
            <div class="text-muted small fw-semibold">Catalog Items</div>
            <div class="fs-4 fw-bold text-dark mt-1">{{ $stats['total_products'] }}</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="stat-card p-3">
            <div class="text-muted small fw-semibold">Customers</div>
            <div class="fs-4 fw-bold text-primary mt-1">{{ $stats['total_customers'] }}</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="stat-card p-3">
            <div class="text-muted small fw-semibold">Low Stock Alert</div>
            <div class="fs-4 fw-bold text-danger mt-1">{{ $stats['low_stock_count'] }}</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-cart-check me-2 text-primary"></i>Recent Orders</h5>
                <span class="badge bg-secondary">{{ $recentOrders->count() }} Orders</span>
            </div>
            <div class="card-body p-0">
                @if($recentOrders->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                        No customer orders recorded yet.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Recipient</th>
                                    <th>District</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td class="fw-semibold">{{ $order->order_number }}</td>
                                        <td>{{ $order->recipient_name }}</td>
                                        <td>{{ $order->district }}</td>
                                        <td>৳{{ number_format($order->total_amount, 2) }}</td>
                                        <td><span class="badge bg-info-subtle text-info text-uppercase">{{ $order->payment_method }}</span></td>
                                        <td><span class="badge bg-warning-subtle text-warning">{{ ucfirst($order->order_status) }}</span></td>
                                        <td class="small text-muted">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
