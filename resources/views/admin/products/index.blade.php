@extends('layouts.admin')

@section('title', 'Manage Products')
@section('page_title', 'Product Catalog')
@section('page_subtitle', 'Browse, filter, edit, and manage all catalog products and inventory levels')

@section('content')
<!-- Filter & Search Bar -->
<div class="table-card p-3 mb-4">
    <form method="GET" action="{{ route('admin.products.index') }}" class="row g-2 align-items-center">
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name or SKU..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-md-3">
            <select name="category_id" class="form-select">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <select name="brand_id" class="form-select">
                <option value="">All Brands</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                        {{ $brand->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <select name="stock_status" class="form-select">
                <option value="">All Stock Status</option>
                <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-secondary flex-grow-1">Filter</button>
            @if(request()->anyFilled(['search', 'category_id', 'brand_id', 'stock_status']))
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary" title="Reset Filters"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </form>
</div>

<!-- Header with Action -->
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h6 class="text-muted mb-0">Showing {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products</h6>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.products.stock') }}" class="btn btn-outline-secondary">
            <i class="bi bi-boxes me-1"></i> Stock Management
        </a>
        <!-- STRICT NO-MODAL RULE: Dedicated full-screen creation view -->
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add New Product
        </a>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 70px;">Thumbnail</th>
                    <th>Product Info</th>
                    <th>Category & Brand</th>
                    <th>Regular Price</th>
                    <th>Sale Price</th>
                    <th>Stock Level</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            @if($product->primaryImage)
                                <img src="{{ $product->primaryImage->url }}" alt="{{ $product->name }}" class="rounded border object-fit-cover" width="48" height="48">
                            @else
                                <div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                    <i class="bi bi-card-image text-secondary"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-dark text-truncate" style="max-width: 280px;">
                                <a href="{{ route('admin.products.show', $product) }}" class="text-decoration-none text-dark hover-primary">
                                    {{ $product->name }}
                                </a>
                            </div>
                            <small class="text-muted font-monospace">SKU: {{ $product->sku }}</small>
                            @if($product->is_featured)
                                <span class="badge bg-warning-subtle text-warning badge-subtle ms-1">Featured</span>
                            @endif
                        </td>
                        <td>
                            <div class="small fw-semibold text-dark">{{ $product->category?->name ?? 'Uncategorized' }}</div>
                            <small class="text-muted">{{ $product->brand?->name ?? 'No Brand' }}</small>
                        </td>
                        <td class="fw-semibold text-secondary">
                            ৳{{ number_format($product->regular_price, 2) }}
                        </td>
                        <td>
                            @if($product->has_discount)
                                <span class="fw-bold text-danger">৳{{ number_format($product->sale_price, 2) }}</span>
                                <small class="badge bg-danger-subtle text-danger ms-1">-{{ $product->discount_percentage }}%</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($product->stock_quantity <= 0)
                                <span class="badge bg-danger text-white badge-subtle">Out of Stock (0)</span>
                            @elseif($product->is_low_stock)
                                <span class="badge bg-warning text-dark badge-subtle">Low: {{ $product->stock_quantity }} left</span>
                            @else
                                <span class="badge bg-success-subtle text-success badge-subtle">{{ $product->stock_quantity }} units</span>
                            @endif
                        </td>
                        <td>
                            @if($product->is_active)
                                <span class="badge bg-success-subtle text-success badge-subtle">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary badge-subtle">Draft/Hidden</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-outline-info me-1" title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit Product">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="d-inline" onsubmit="return confirm('Move product [{{ $product->name }}] to trash?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Move to Trash">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary"></i>
                            No products found matching your filter criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
