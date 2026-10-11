@extends('layouts.admin')

@section('title', 'Customers Directory')
@section('page_title', 'Customers Directory')
@section('page_subtitle', 'View registered store customers, purchase statistics, and account access')

@section('content')
<!-- Search & Status Filter -->
<div class="table-card p-3 mb-4">
    <form method="GET" action="{{ route('admin.customers.index') }}" class="row g-2 align-items-center">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search customer by name, email, or mobile..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Account Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Customers</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Suspended / Inactive</option>
            </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-secondary flex-grow-1">Search</button>
            @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary" title="Reset Filters"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Email Address</th>
                    <th>Mobile Phone</th>
                    <th>Orders Placed</th>
                    <th>Total Spent (BDT)</th>
                    <th>Status</th>
                    <th>Registered Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="text-decoration-none text-dark hover-primary">
                                    {{ $customer->name }}
                                </a>
                            </div>
                            <small class="text-muted">ID: #{{ $customer->id }}</small>
                        </td>
                        <td>
                            <span>{{ $customer->email ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="font-monospace text-secondary">{{ $customer->phone ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-info-subtle text-info fw-semibold">{{ $customer->orders_count }} orders</span>
                        </td>
                        <td class="fw-bold text-dark">
                            ৳{{ number_format($customer->total_spent ?? 0, 2) }}
                        </td>
                        <td>
                            @if($customer->is_active)
                                <span class="badge bg-success-subtle text-success badge-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger badge-subtle">Suspended</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ $customer->created_at->format('d M, Y') }}
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-outline-primary me-1" title="View Profile">
                                <i class="bi bi-eye"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.customers.toggle-status', $customer) }}" class="d-inline" onsubmit="return confirm('Change status for {{ $customer->name }}?');">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $customer->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $customer->is_active ? 'Deactivate Customer' : 'Activate Customer' }}">
                                    <i class="bi {{ $customer->is_active ? 'bi-person-slash' : 'bi-person-check' }}"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                            No registered customers found matching your search.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $customers->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
