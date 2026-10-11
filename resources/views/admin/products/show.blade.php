@extends('layouts.admin')

@section('title', $product->name . ' — Details')
@section('page_title', 'Product Details')
@section('page_subtitle', 'SKU: ' . $product->sku . ' | Category: ' . ($product->category?->name ?? 'None'))

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Products
    </a>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit Product
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Details & Images -->
    <div class="col-lg-5">
        <div class="table-card p-4 mb-4">
            <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">Product Gallery</h6>
            @if($product->primaryImage)
                <img src="{{ $product->primaryImage->url }}" alt="{{ $product->name }}" class="img-fluid rounded mb-3 border w-100 object-fit-cover" style="max-height: 320px;">
            @else
                <div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted mb-3" style="height: 250px;">
                    <i class="bi bi-card-image fs-1"></i>
                </div>
            @endif

            @if($product->images->count() > 1)
                <div class="d-flex gap-2 overflow-auto pb-2">
                    @foreach($product->images as $img)
                        <img src="{{ $img->url }}" class="rounded border object-fit-cover {{ $img->is_primary ? 'border-primary border-2' : '' }}" width="60" height="60">
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Quick Info Box -->
        <div class="table-card p-4">
            <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">Status & Pricing</h6>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Regular Price:</span>
                    <span class="fw-bold">৳{{ number_format($product->regular_price, 2) }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Sale Price:</span>
                    <span class="fw-bold {{ $product->has_discount ? 'text-danger' : 'text-muted' }}">
                        {{ $product->has_discount ? '৳' . number_format($product->sale_price, 2) : 'No Discount' }}
                    </span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Purchase Cost:</span>
                    <span>৳{{ number_format($product->purchase_price ?? 0, 2) }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Stock On Hand:</span>
                    <span class="fw-bold {{ $product->stock_quantity <= 0 ? 'text-danger' : 'text-success' }}">
                        {{ $product->stock_quantity }} units
                    </span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Catalog Status:</span>
                    <span class="badge {{ $product->is_active ? 'bg-success-subtle text-success' : 'bg-secondary' }}">
                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Featured:</span>
                    <span>{{ $product->is_featured ? 'Yes' : 'No' }}</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Right Column: Description & Stock Movements Log -->
    <div class="col-lg-7">
        <div class="table-card p-4 mb-4">
            <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">Description</h6>
            <div class="mb-3">
                <strong class="d-block small text-muted text-uppercase mb-1">Short Summary:</strong>
                <p class="text-secondary">{{ $product->short_description ?? 'No short summary provided.' }}</p>
            </div>
            <div>
                <strong class="d-block small text-muted text-uppercase mb-1">Full Description:</strong>
                <div class="text-secondary" style="white-space: pre-line;">{{ $product->description ?? 'No full description provided.' }}</div>
            </div>
        </div>

        <!-- Inventory Movements History -->
        <div class="table-card p-4 mb-4">
            <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">
                <i class="bi bi-clock-history me-2 text-primary"></i>Inventory Movement Log
            </h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Change</th>
                            <th>Previous &rarr; New</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product->inventoryMovements as $movement)
                            <tr>
                                <td class="text-muted small">{{ $movement->created_at->format('d M, Y H:i') }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">{{ $movement->type }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold {{ $movement->quantity_change > 0 ? 'text-success' : ($movement->quantity_change < 0 ? 'text-danger' : 'text-muted') }}">
                                        {{ $movement->quantity_change > 0 ? '+' : '' }}{{ $movement->quantity_change }}
                                    </span>
                                </td>
                                <td class="small">{{ $movement->previous_stock }} &rarr; <strong>{{ $movement->current_stock }}</strong></td>
                                <td class="small text-muted">{{ $movement->remarks }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">No inventory movements recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
