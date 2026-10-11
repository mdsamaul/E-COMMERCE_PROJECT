@extends('layouts.admin')

@section('title', 'Manage Brands')
@section('page_title', 'Product Brands')
@section('page_subtitle', 'Manage manufacturers, designers, and partner brands')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h6 class="text-muted mb-0">Total: {{ $brands->total() }} brands</h6>
    </div>
    <!-- STRICT NO-MODAL RULE: Dedicated full-screen creation view -->
    <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Brand
    </a>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 70px;">Logo</th>
                    <th>Brand Name</th>
                    <th>Slug</th>
                    <th>Products Assigned</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($brands as $brand)
                    <tr>
                        <td>
                            @if($brand->logo)
                                <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="rounded border object-fit-contain p-1 bg-white" width="48" height="48">
                            @else
                                <div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                    <i class="bi bi-tag text-secondary"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $brand->name }}</div>
                            @if($brand->description)
                                <small class="text-muted d-block text-truncate" style="max-width: 300px;">{{ $brand->description }}</small>
                            @endif
                        </td>
                        <td><code class="text-secondary small">{{ $brand->slug }}</code></td>
                        <td>
                            <span class="badge bg-info-subtle text-info fw-semibold">{{ $brand->products_count }} products</span>
                        </td>
                        <td>
                            @if($brand->is_active)
                                <span class="badge bg-success-subtle text-success badge-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger badge-subtle">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit Brand">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete brand [{{ $brand->name }}]?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Brand">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-tag fs-1 d-block mb-2 text-secondary"></i>
                            No brands found. Click <strong>Add New Brand</strong> to create one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($brands->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $brands->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
