@extends('layouts.admin')

@section('title', 'Customer Profile — ' . $customer->name)
@section('page_title', 'Customer Profile')
@section('page_subtitle', 'Customer ID: #' . $customer->id . ' | Member since ' . $customer->created_at->format('M Y'))

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Customers Directory
    </a>
    <form method="POST" action="{{ route('admin.customers.toggle-status', $customer) }}" class="d-inline">
        @csrf
        <button type="submit" class="btn {{ $customer->is_active ? 'btn-outline-warning' : 'btn-success' }}">
            <i class="bi {{ $customer->is_active ? 'bi-person-slash' : 'bi-person-check' }} me-1"></i>
            {{ $customer->is_active ? 'Suspend Account' : 'Activate Account' }}
        </button>
    </form>
</div>

<div class="row g-4">
    <!-- Customer Info Card -->
    <div class="col-lg-4">
        <div class="table-card p-4 mb-4">
            <div class="text-center pb-3 border-bottom mb-3">
                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-2" style="width: 72px; height: 72px;">
                    <i class="bi bi-person-fill fs-1"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ $customer->name }}</h5>
                <span class="badge {{ $customer->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} badge-subtle">
                    {{ $customer->is_active ? 'Active Customer' : 'Suspended' }}
                </span>
            </div>

            <ul class="list-group list-group-flush small">
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Email:</span>
                    <span class="fw-semibold">{{ $customer->email ?? '—' }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Phone Number:</span>
                    <span class="fw-semibold font-monospace">{{ $customer->phone ?? '—' }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Registered On:</span>
                    <span>{{ $customer->created_at->format('d M, Y h:i A') }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Total Orders:</span>
                    <span class="fw-bold">{{ $ordersCount }} orders</span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Lifetime Spent:</span>
                    <span class="fw-bold text-success fs-6">৳{{ number_format($totalSpent, 2) }}</span>
                </li>
            </ul>
        </div>

        <!-- Saved Addresses -->
        <div class="table-card p-4">
            <h6 class="fw-bold mb-3 border-bottom pb-2">
                <i class="bi bi-geo-alt me-2 text-primary"></i>Delivery Addresses ({{ $customer->addresses->count() }})
            </h6>

            @forelse($customer->addresses as $address)
                <div class="border rounded p-3 mb-2 bg-light small">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-dark">{{ $address->recipient_name }}</strong>
                        @if($address->is_default)
                            <span class="badge bg-primary-subtle text-primary">Default</span>
                        @endif
                    </div>
                    <div class="text-muted">{{ $address->phone }}</div>
                    <div class="text-secondary mt-1">{{ $address->address_line }}, {{ $address->city }}, {{ $address->division }}</div>
                </div>
            @empty
                <p class="text-muted small mb-0">No saved delivery addresses on file.</p>
            @endforelse
        </div>
    </div>

    <!-- Right Column: Recent Orders -->
    <div class="col-lg-8">
        <div class="table-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-cart-check me-2 text-primary"></i>Order History</h6>
                <small class="text-muted">Last {{ $customer->orders->count() }} orders</small>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Total (BDT)</th>
                            <th>Payment</th>
                            <th>Order Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->orders as $order)
                            <tr>
                                <td>
                                    <span class="fw-bold font-monospace">#{{ $order->order_number }}</span>
                                </td>
                                <td class="fw-bold text-dark">
                                    ৳{{ number_format($order->total_amount, 2) }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border text-uppercase font-monospace small">
                                        {{ $order->payment_method }} ({{ $order->payment_status }})
                                    </span>
                                </td>
                                <td>
                                    @if($order->order_status === 'delivered')
                                        <span class="badge bg-success-subtle text-success badge-subtle">Delivered</span>
                                    @elseif($order->order_status === 'cancelled')
                                        <span class="badge bg-danger-subtle text-danger badge-subtle">Cancelled</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning badge-subtle">{{ ucfirst($order->order_status) }}</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $order->created_at->format('d M, Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">This customer has not placed any orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
