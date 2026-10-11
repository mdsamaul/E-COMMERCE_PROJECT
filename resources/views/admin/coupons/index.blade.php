@extends('layouts.admin')

@section('title', 'Manage Coupons')
@section('page_title', 'Coupons & Promotions')
@section('page_subtitle', 'Create promotional discount voucher codes for campaigns and customers')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h6 class="text-muted mb-0">Total: {{ $coupons->total() }} coupon codes</h6>
    </div>
    <!-- STRICT NO-MODAL RULE: Dedicated full-screen creation view -->
    <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Coupon
    </a>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Coupon Code</th>
                    <th>Discount Type</th>
                    <th>Discount Value</th>
                    <th>Min. Order</th>
                    <th>Max. Discount</th>
                    <th>Usage / Limit</th>
                    <th>Validity Period</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $coupon)
                    <tr>
                        <td>
                            <span class="fw-bold font-monospace text-primary fs-6">{{ $coupon->code }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $coupon->type === 'percentage' ? 'bg-info-subtle text-info' : 'bg-secondary-subtle text-secondary' }} text-capitalize">
                                {{ $coupon->type }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-bold text-dark">
                                {{ $coupon->type === 'percentage' ? $coupon->value . '%' : '৳' . number_format($coupon->value, 2) }}
                            </span>
                        </td>
                        <td class="small">
                            {{ $coupon->min_order_amount ? '৳' . number_format($coupon->min_order_amount, 2) : 'No min' }}
                        </td>
                        <td class="small">
                            {{ $coupon->max_discount_amount ? '৳' . number_format($coupon->max_discount_amount, 2) : 'Unlimited' }}
                        </td>
                        <td>
                            <span class="small">{{ $coupon->total_used }}</span>
                            <span class="text-muted small">/ {{ $coupon->usage_limit_per_coupon ?? '∞' }}</span>
                        </td>
                        <td class="small text-muted">
                            @if($coupon->starts_at || $coupon->expires_at)
                                <div>{{ $coupon->starts_at ? $coupon->starts_at->format('d M y') : 'Start' }} &rarr; {{ $coupon->expires_at ? $coupon->expires_at->format('d M y') : 'Open' }}</div>
                            @else
                                <span>Always valid</span>
                            @endif
                        </td>
                        <td>
                            @if($coupon->is_active)
                                <span class="badge bg-success-subtle text-success badge-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger badge-subtle">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit Coupon">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" class="d-inline" onsubmit="return confirm('Delete coupon code [{{ $coupon->code }}]?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Coupon">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-ticket-perforated fs-1 d-block mb-2 text-secondary"></i>
                            No coupons found. Click <strong>Add New Coupon</strong> to create a discount code.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($coupons->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $coupons->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
