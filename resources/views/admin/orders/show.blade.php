@extends('layouts.admin')

@section('title', "Order #{$order->order_number} - Admin Management")

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}" class="text-decoration-none">Orders</a></li>
                    <li class="breadcrumb-item active" aria-current="page">#{{ $order->order_number }}</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-2">
                <h1 class="h3 fw-bold text-dark mb-0">Order #{{ $order->order_number }}</h1>
                @php
                    $statusBadge = match($order->order_status) {
                        'pending' => 'bg-warning text-dark',
                        'confirmed' => 'bg-info text-white',
                        'processing' => 'bg-primary text-white',
                        'shipped' => 'bg-primary-subtle text-primary border border-primary-subtle',
                        'delivered' => 'bg-success text-white',
                        'cancelled' => 'bg-danger text-white',
                        'returned' => 'bg-secondary text-white',
                        default => 'bg-light text-dark',
                    };
                @endphp
                <span class="badge {{ $statusBadge }} px-3 py-2 rounded-pill fs-6 text-uppercase">
                    {{ $order->order_status }}
                </span>
                <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} px-3 py-2 rounded-pill fs-6 text-uppercase">
                    Payment: {{ $order->payment_status }}
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Orders
            </a>
            <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-printer me-1"></i> Print Invoice
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <ul class="mb-0 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Key Financial Metrics Bar -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="text-muted small fw-semibold">Customer Revenue</div>
                <div class="fs-4 fw-bold text-dark">৳{{ number_format($order->subtotal, 2) }}</div>
                <div class="text-muted small">Excluding shipping fees</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="text-muted small fw-semibold">Purchase Cost (COGS)</div>
                <div class="fs-4 fw-bold text-secondary">৳{{ number_format($totalCost, 2) }}</div>
                <div class="text-muted small">Cost of goods sold</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="text-muted small fw-semibold">Estimated Gross Profit</div>
                <div class="fs-4 fw-bold {{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }}">
                    ৳{{ number_format($grossProfit, 2) }}
                </div>
                <div class="small {{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }}">
                    Margin: {{ $profitMargin }}%
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <div class="text-muted small fw-semibold">Grand Total Receivable</div>
                <div class="fs-4 fw-bold text-primary">৳{{ number_format($order->total_amount, 2) }}</div>
                <div class="text-muted small">Incl. ৳{{ number_format($order->shipping_cost, 2) }} shipping</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Fulfillment & Items -->
        <div class="col-lg-8">
            <!-- Order Status & State Machine Transition Panel -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold mb-0 text-dark">
                        <i class="bi bi-arrow-repeat text-primary me-2"></i>Fulfillment & Status Transitions
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center mb-3">
                        <div class="col-md-4">
                            <span class="text-muted small fw-semibold d-block">Current Order Status:</span>
                            <span class="badge {{ $statusBadge }} px-3 py-2 text-uppercase fs-6 mt-1">
                                {{ $order->order_status }}
                            </span>
                        </div>
                        <div class="col-md-8">
                            <div class="small text-muted">
                                <strong>Allowed Next Transitions:</strong> 
                                @if(!empty($allowedTransitions))
                                    @foreach($allowedTransitions as $trans)
                                        <span class="badge bg-light text-dark border me-1">{{ ucfirst($trans) }}</span>
                                    @endforeach
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Final State Reached (No further transitions)</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if(!empty($allowedTransitions))
                        <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST" class="border-top pt-3">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label small fw-semibold text-dark">Advance To Next Status <span class="text-danger">*</span></label>
                                    <select name="order_status" class="form-select" required>
                                        <option value="">-- Choose Next Allowed Status --</option>
                                        @foreach($allowedTransitions as $statusOption)
                                            <option value="{{ $statusOption }}">
                                                {{ ucfirst($statusOption) }} 
                                                @if(in_array($statusOption, ['cancelled', 'returned']))
                                                    (Will Restock Inventory)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label small fw-semibold text-dark">Status Transition Remarks / Notes</label>
                                    <input type="text" name="remarks" class="form-control" placeholder="e.g. Dispatched via Pathao (Tracking #12345) or customer cancel reason">
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary px-4 shadow-sm" onclick="return confirm('Confirm updating order status?');">
                                        <i class="bi bi-check2-circle me-1"></i> Update Order Status
                                    </button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-light border small text-muted mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            This order is in <strong>{{ $order->order_status }}</strong> status. Order lifecycle transitions are completed.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Ordered Items & Margin Breakdown -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold mb-0 text-dark">
                        <i class="bi bi-box-seam text-primary me-2"></i>Purchased Items ({{ $order->items->count() }})
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th>Product Details</th>
                                <th class="text-center" style="width: 100px;">Unit Cost</th>
                                <th class="text-center" style="width: 110px;">Selling Price</th>
                                <th class="text-center" style="width: 70px;">Qty</th>
                                <th class="text-end" style="width: 120px;">Subtotal</th>
                                <th class="text-end" style="width: 100px;">Line Profit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($order->items as $item)
                                @php
                                    $itemCost = (float) $item->unit_purchase_price * $item->quantity;
                                    $itemRev = (float) $item->subtotal;
                                    $itemProfit = $itemRev - $itemCost;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($item->product && $item->product->primaryImage)
                                                <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" 
                                                     alt="{{ $item->product_name }}" 
                                                     class="rounded border object-fit-cover" 
                                                     width="45" height="45">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted border" style="width: 45px; height: 45px;">
                                                    <i class="bi bi-box"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                                                <div class="text-muted small">
                                                    SKU: {{ $item->sku }}
                                                    @if($item->product)
                                                        &bull; Stock Left: <span class="fw-semibold">{{ $item->product->stock_quantity }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center text-muted small">
                                        ৳{{ number_format($item->unit_purchase_price, 2) }}
                                    </td>
                                    <td class="text-center fw-semibold">
                                        ৳{{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="text-center fw-bold">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="text-end fw-semibold">
                                        ৳{{ number_format($item->subtotal, 2) }}
                                    </td>
                                    <td class="text-end small {{ $itemProfit >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                                        ৳{{ number_format($itemProfit, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-semibold">Subtotal:</td>
                                <td class="text-end fw-bold">৳{{ number_format($order->subtotal, 2) }}</td>
                                <td class="text-end fw-bold text-success">৳{{ number_format($grossProfit, 2) }}</td>
                            </tr>
                            @if($order->discount_amount > 0)
                                <tr>
                                    <td colspan="4" class="text-end text-danger fw-semibold">
                                        Coupon Discount @if($order->coupon) ({{ $order->coupon->code }}) @endif:
                                    </td>
                                    <td class="text-end text-danger fw-bold">-৳{{ number_format($order->discount_amount, 2) }}</td>
                                    <td></td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="4" class="text-end text-muted fw-semibold">
                                    Shipping Charge ({{ $order->shipping_zone === 'dhaka' ? 'Dhaka ৳60' : 'Outside Dhaka ৳120' }}):
                                </td>
                                <td class="text-end fw-bold">৳{{ number_format($order->shipping_cost, 2) }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end fw-bold text-dark fs-6">Grand Total:</td>
                                <td class="text-end fw-bold text-primary fs-5">৳{{ number_format($order->total_amount, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Inventory Movement Audit Trail -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold mb-0 text-dark">
                        <i class="bi bi-journal-text text-primary me-2"></i>Inventory Audit Trail for this Order
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 small">
                        <thead class="table-light text-uppercase text-muted">
                            <tr>
                                <th>Timestamp</th>
                                <th>Product</th>
                                <th class="text-center">Movement Type</th>
                                <th class="text-center">Qty Change</th>
                                <th class="text-center">Prev Stock</th>
                                <th class="text-center">New Stock</th>
                                <th>Remarks & User</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($inventoryMovements as $movement)
                                <tr>
                                    <td class="text-muted">{{ $movement->created_at->format('d M, Y h:i A') }}</td>
                                    <td class="fw-semibold">{{ $movement->product->name ?? 'Product #' . $movement->product_id }}</td>
                                    <td class="text-center">
                                        @if($movement->type === 'sale')
                                            <span class="badge bg-danger-subtle text-danger border">Deduction (Sale)</span>
                                        @elseif($movement->type === 'return')
                                            <span class="badge bg-success-subtle text-success border">Restock (Return/Cancel)</span>
                                        @else
                                            <span class="badge bg-light text-dark border">{{ ucfirst($movement->type) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold {{ $movement->quantity < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                    </td>
                                    <td class="text-center text-muted">{{ $movement->previous_stock }}</td>
                                    <td class="text-center fw-semibold text-dark">{{ $movement->new_stock }}</td>
                                    <td>
                                        <div class="text-muted">{{ $movement->remarks ?? 'System recorded' }}</div>
                                        <div class="text-xs text-muted">
                                            By: {{ $movement->createdByUser->name ?? 'System Automated' }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">
                                        No inventory movements logged for this order.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Customer & Timeline -->
        <div class="col-lg-4">
            <!-- Payment Reconciliation Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold mb-0 text-dark">
                        <i class="bi bi-cash-coin text-success me-2"></i>Payment Reconciliation
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <div class="text-muted small">Payment Method:</div>
                        <div class="fw-bold text-dark fs-6">
                            {{ $order->payment_method === 'cod' ? 'Cash on Delivery (COD)' : strtoupper($order->payment_method) }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Current Payment Status:</div>
                        <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} px-3 py-2 text-uppercase fs-6">
                            {{ $order->payment_status }}
                        </span>
                    </div>

                    <form action="{{ route('admin.orders.update-payment', $order->id) }}" method="POST" class="border-top pt-3">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">Update Payment Status</label>
                            <select name="payment_status" class="form-select form-select-sm" required>
                                <option value="unpaid" {{ $order->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">Payment Remarks</label>
                            <input type="text" name="remarks" class="form-control form-control-sm" placeholder="e.g. Cash collected by courier rider">
                        </div>
                        <button type="submit" class="btn btn-outline-success btn-sm w-100">
                            <i class="bi bi-wallet2 me-1"></i> Update Payment
                        </button>
                    </form>
                </div>
            </div>

            <!-- Customer & Shipping Information -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold mb-0 text-dark">
                        <i class="bi bi-person-lines-fill text-primary me-2"></i>Customer & Destination
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="mb-2">
                        <div class="text-muted small">Recipient Name:</div>
                        <div class="fw-bold text-dark">{{ $order->recipient_name }}</div>
                    </div>
                    <div class="mb-2">
                        <div class="text-muted small">Phone Number:</div>
                        <div class="fw-semibold text-dark"><i class="bi bi-telephone me-1"></i>{{ $order->phone }}</div>
                    </div>
                    @if($order->email)
                        <div class="mb-2">
                            <div class="text-muted small">Email Address:</div>
                            <div class="text-dark"><i class="bi bi-envelope me-1"></i>{{ $order->email }}</div>
                        </div>
                    @endif
                    <div class="mb-2">
                        <div class="text-muted small">Delivery Address:</div>
                        <div class="text-dark">
                            <i class="bi bi-geo-alt me-1 text-danger"></i>{{ $order->delivery_address }}<br>
                            <strong>{{ $order->district }}</strong> ({{ $order->shipping_zone === 'dhaka' ? 'Inside Dhaka City' : 'Outside Dhaka' }})
                        </div>
                    </div>
                    @if($order->delivery_instructions)
                        <div class="mt-3 p-2 bg-light rounded small">
                            <strong>Special Instructions:</strong><br>
                            <span class="text-muted fst-italic">{{ $order->delivery_instructions }}</span>
                        </div>
                    @endif

                    <div class="border-top pt-3 mt-3">
                        <div class="text-muted small">User Account:</div>
                        @if($order->user)
                            <div class="fw-semibold text-dark">
                                <i class="bi bi-person-check-fill text-success me-1"></i>Registered Customer (#{{ $order->user_id }})
                            </div>
                            <div class="text-muted small">{{ $order->user->name }} ({{ $order->user->email ?? $order->user->phone }})</div>
                        @else
                            <div class="text-muted small">Guest checkout</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order Status Timeline -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold mb-0 text-dark">
                        <i class="bi bi-clock-history text-primary me-2"></i>Status History Timeline
                    </h5>
                </div>
                <div class="card-body p-3">
                    @if($order->statusHistories->isNotEmpty())
                        <div class="timeline position-relative ps-3 border-start">
                            @foreach($order->statusHistories->sortByDesc('created_at') as $history)
                                <div class="mb-3 position-relative">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 text-uppercase small">
                                            {{ $history->new_status }}
                                        </span>
                                        @if($history->old_status)
                                            <span class="text-muted text-xs">from {{ $history->old_status }}</span>
                                        @endif
                                    </div>
                                    @if($history->remarks)
                                        <div class="small text-dark mt-1">{{ $history->remarks }}</div>
                                    @endif
                                    <div class="text-muted text-xs mt-1">
                                        <i class="bi bi-clock me-1"></i>{{ $history->created_at->format('d M, Y h:i A') }} 
                                        &bull; {{ $history->changedByUser->name ?? 'System' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted small">No history entries logged.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
