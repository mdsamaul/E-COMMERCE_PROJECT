@extends('layouts.admin')

@section('title', 'Moderate Reviews')
@section('page_title', 'Customer Reviews Moderation')
@section('page_subtitle', 'Audit, approve, and moderate product ratings and customer feedback')

@section('content')
<!-- Filter Bar -->
<div class="table-card p-3 mb-4">
    <form method="GET" action="{{ route('admin.reviews.index') }}" class="row g-2 align-items-center">
        <div class="col-md-4">
            <select name="status" class="form-select">
                <option value="">All Review Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved & Published</option>
            </select>
        </div>

        <div class="col-md-3">
            <select name="rating" class="form-select">
                <option value="">All Star Ratings</option>
                <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Stars ★★★★★</option>
                <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Stars ★★★★☆</option>
                <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Stars ★★★☆☆</option>
                <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Stars ★★☆☆☆</option>
                <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Star  ★☆☆☆☆</option>
            </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-secondary flex-grow-1">Filter Reviews</button>
            @if(request()->anyFilled(['status', 'rating']))
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th style="min-width: 250px;">Feedback & Review</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th class="text-end">Moderation</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark text-truncate" style="max-width: 220px;">
                                {{ $review->product?->name ?? 'Deleted Product' }}
                            </div>
                            <small class="text-muted font-monospace">SKU: {{ $review->product?->sku ?? '—' }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $review->user?->name ?? 'Unknown Customer' }}</div>
                            <small class="text-muted">{{ $review->user?->email }}</small>
                        </td>
                        <td>
                            <div class="text-warning">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                @endfor
                                <span class="text-dark small fw-bold ms-1">({{ $review->rating }}/5)</span>
                            </div>
                        </td>
                        <td>
                            <p class="mb-0 text-secondary small fst-italic">"{{ $review->comment ?? 'No written comment' }}"</p>
                        </td>
                        <td>
                            @if($review->is_approved)
                                <span class="badge bg-success-subtle text-success badge-subtle">
                                    <i class="bi bi-check-circle me-1"></i>Approved
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning badge-subtle">
                                    <i class="bi bi-clock me-1"></i>Pending
                                </span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ $review->created_at->format('d M, Y') }}
                        </td>
                        <td class="text-end">
                            @if(!$review->is_approved)
                                <form method="POST" action="{{ route('admin.reviews.approve', $review) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success me-1" title="Approve & Publish">
                                        <i class="bi bi-check-lg me-1"></i>Approve
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.reviews.reject', $review) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-warning me-1" title="Unapprove">
                                        <i class="bi bi-slash-circle me-1"></i>Unapprove
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" class="d-inline" onsubmit="return confirm('Delete this review permanently?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Review">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-chat-square-quote fs-1 d-block mb-2 text-secondary"></i>
                            No reviews found matching the selected filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reviews->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $reviews->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
