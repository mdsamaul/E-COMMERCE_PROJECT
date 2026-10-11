@extends('layouts.admin')

@section('title', 'Manage Banners')
@section('page_title', 'Homepage Banners & Sliders')
@section('page_subtitle', 'Manage hero image carousels and promotional marketing graphics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h6 class="text-muted mb-0">Total: {{ $banners->total() }} banners configured</h6>
    </div>
    <!-- STRICT NO-MODAL RULE: Dedicated full-screen creation view -->
    <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Banner
    </a>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 140px;">Banner Image</th>
                    <th>Title & Subtitle</th>
                    <th>Placement</th>
                    <th>Call to Action</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banners as $banner)
                    <tr>
                        <td>
                            @if($banner->image_path)
                                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="rounded border object-fit-cover" style="width: 120px; height: 50px;">
                            @else
                                <div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted" style="width: 120px; height: 50px;">
                                    <i class="bi bi-image"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $banner->title }}</div>
                            <small class="text-muted">{{ $banner->subtitle ?? 'No subtitle' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $banner->placement }}</span>
                        </td>
                        <td>
                            @if($banner->button_text)
                                <span class="badge bg-light text-dark border">{{ $banner->button_text }}</span>
                                <small class="text-muted d-block text-truncate" style="max-width: 150px;">{{ $banner->button_url }}</small>
                            @else
                                <span class="text-muted small">None</span>
                            @endif
                        </td>
                        <td class="font-monospace">{{ $banner->sort_order }}</td>
                        <td>
                            @if($banner->is_active)
                                <span class="badge bg-success-subtle text-success badge-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger badge-subtle">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit Banner">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" class="d-inline" onsubmit="return confirm('Delete banner [{{ $banner->title }}]?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Banner">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-images fs-1 d-block mb-2 text-secondary"></i>
                            No banners found. Click <strong>Add New Banner</strong> to create a hero slider.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($banners->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $banners->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
