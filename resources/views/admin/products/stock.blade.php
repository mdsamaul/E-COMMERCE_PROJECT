@extends('layouts.admin')

@section('title', 'Inventory & Stock Management')
@section('page_title', 'Inventory & Stock Management')
@section('page_subtitle', 'Monitor stock levels, set thresholds, and adjust inventory with full audit logging')

@section('content')
<!-- Filter bar -->
<div class="table-card p-3 mb-4">
    <form method="GET" action="{{ route('admin.products.stock') }}" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name or SKU..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-md-4">
            <div class="btn-group w-100" role="group">
                <a href="{{ route('admin.products.stock') }}" class="btn btn-sm {{ !request('filter') ? 'btn-primary' : 'btn-outline-secondary' }}">
                    All Items
                </a>
                <a href="{{ route('admin.products.stock', ['filter' => 'low_stock']) }}" class="btn btn-sm {{ request('filter') === 'low_stock' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}">
                    <i class="bi bi-exclamation-triangle me-1"></i> Low Stock
                </a>
                <a href="{{ route('admin.products.stock', ['filter' => 'out_of_stock']) }}" class="btn btn-sm {{ request('filter') === 'out_of_stock' ? 'btn-danger' : 'btn-outline-secondary' }}">
                    <i class="bi bi-x-circle me-1"></i> Out of Stock
                </a>
            </div>
        </div>

        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-secondary flex-grow-1">Search</button>
            @if(request()->anyFilled(['search', 'filter']))
                <a href="{{ route('admin.products.stock') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </form>
</div>

<div class="row g-4">
    <!-- Products Stock Table -->
    <div class="col-xl-8">
        <div class="table-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-boxes me-2 text-primary"></i>Stock Adjustments</h6>
                <small class="text-muted">{{ $products->total() }} matching products</small>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Product / SKU</th>
                            <th>Current Stock</th>
                            <th>Threshold</th>
                            <th>Adjust Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark text-truncate" style="max-width: 250px;">{{ $product->name }}</div>
                                    <small class="text-muted font-monospace">SKU: {{ $product->sku }}</small>
                                </td>
                                <td>
                                    @if($product->stock_quantity <= 0)
                                        <span class="badge bg-danger text-white">Out of Stock (0)</span>
                                    @elseif($product->is_low_stock)
                                        <span class="badge bg-warning text-dark">Low: {{ $product->stock_quantity }}</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success">{{ $product->stock_quantity }} in stock</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $product->low_stock_threshold }}
                                </td>
                                <td>
                                    <!-- Quick Adjustment Form -->
                                    <form method="POST" action="{{ route('admin.products.stock.adjust', $product) }}" class="row g-1 align-items-center">
                                        @csrf
                                        <div class="col-auto">
                                            <select name="adjustment_type" class="form-select form-select-sm" required>
                                                <option value="add">+ Add</option>
                                                <option value="subtract">- Remove</option>
                                                <option value="set">= Set to</option>
                                            </select>
                                        </div>
                                        <div class="col-auto" style="width: 70px;">
                                            <input type="number" name="quantity" class="form-control form-control-sm" min="0" placeholder="Qty" required>
                                        </div>
                                        <div class="col-auto" style="width: 140px;">
                                            <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Reason (e.g. restock)" required>
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Apply Adjustment">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No products found matching stock criteria.</td>
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
    </div>

    <!-- Recent Inventory Movements Log -->
    <div class="col-xl-4">
        <div class="table-card p-3">
            <h6 class="fw-bold mb-3 border-bottom pb-2">
                <i class="bi bi-journal-text me-2 text-primary"></i>Recent Stock Movements
            </h6>
            <div class="list-group list-group-flush small">
                @forelse($recentMovements as $movement)
                    <div class="list-group-item px-0 py-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark text-truncate" style="max-width: 170px;">{{ $movement->product?->name ?? 'Deleted Product' }}</span>
                            <span class="badge {{ $movement->quantity_change > 0 ? 'bg-success' : 'bg-danger' }}">
                                {{ $movement->quantity_change > 0 ? '+' : '' }}{{ $movement->quantity_change }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small">
                            <span>{{ $movement->previous_stock }} &rarr; {{ $movement->current_stock }} units</span>
                            <span>{{ $movement->created_at->diffForHumans() }}</span>
                        </div>
                        <small class="text-secondary d-block mt-1 fst-italic">"{{ $movement->remarks }}"</small>
                    </div>
                @empty
                    <div class="text-center py-3 text-muted">No recent inventory movements recorded.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
