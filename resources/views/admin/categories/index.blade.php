@extends('layouts.admin')

@section('title', 'Manage Categories')
@section('page_title', 'Product Categories')
@section('page_subtitle', 'Organize catalog hierarchy, subcategories, and navigation menus')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h6 class="text-muted mb-0">Total: {{ $categories->total() }} categories</h6>
    </div>
    <!-- STRICT NO-MODAL RULE: Dedicated full-screen creation view -->
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Category
    </a>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 70px;">Image</th>
                    <th>Category Name</th>
                    <th>Parent Category</th>
                    <th>Slug</th>
                    <th>Products</th>
                    <th>Featured</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>
                            @if($category->image)
                                <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="rounded border object-fit-cover" width="48" height="48">
                            @else
                                <div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                    <i class="bi bi-folder2 text-secondary"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $category->name }}</div>
                            @if($category->description)
                                <small class="text-muted d-block text-truncate" style="max-width: 250px;">{{ $category->description }}</small>
                            @endif
                        </td>
                        <td>
                            @if($category->parent)
                                <span class="badge bg-secondary-subtle text-secondary">{{ $category->parent->name }}</span>
                            @else
                                <span class="text-muted small">None (Top-Level)</span>
                            @endif
                        </td>
                        <td><code class="text-secondary small">{{ $category->slug }}</code></td>
                        <td>
                            <span class="badge bg-info-subtle text-info fw-semibold">{{ $category->products_count }} items</span>
                        </td>
                        <td>
                            @if($category->is_featured)
                                <span class="badge bg-warning-subtle text-warning badge-subtle">Yes</span>
                            @else
                                <span class="text-muted small">No</span>
                            @endif
                        </td>
                        <td>
                            @if($category->is_active)
                                <span class="badge bg-success-subtle text-success badge-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger badge-subtle">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit Category">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete category [{{ $category->name }}]?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Category">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                            No categories found. Click <strong>Add New Category</strong> to create your first category.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $categories->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
