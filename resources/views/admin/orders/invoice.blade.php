<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Invoice #{{ $order->order_number }} - ShopBD</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            font-size: 13px;
        }
        .invoice-card {
            max-width: 900px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 40px;
            border: 1px solid #e2e8f0;
        }
        .invoice-logo {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
        }
        .invoice-logo span {
            color: #2563eb;
        }
        .table-invoice th {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 12px;
            border: none;
        }
        .table-invoice td {
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .total-row {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            border-top: 2px solid #cbd5e1;
        }
        .check-box {
            width: 18px;
            height: 18px;
            border: 1.5px solid #64748b;
            display: inline-block;
            vertical-align: middle;
            border-radius: 3px;
        }
        @media print {
            body {
                background: #ffffff;
            }
            .invoice-card {
                border: none;
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
            .table-invoice th {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<div class="container position-relative">
    <!-- Action Bar (Hidden on print) -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-2 no-print" style="max-width: 900px; margin: 0 auto;">
        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Order Management
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm px-3 shadow-sm">
                <i class="bi bi-printer me-1"></i> Print Packing Slip & Invoice
            </button>
        </div>
    </div>

    <!-- Main Printable Invoice -->
    <div class="invoice-card">
        <!-- Top Bar: Brand & Order Meta -->
        <div class="row align-items-center mb-4 pb-3 border-bottom">
            <div class="col-6">
                <div class="invoice-logo mb-1">
                    Shop<span>BD</span> <span class="badge bg-secondary text-white fs-6 align-middle ms-2">ADMIN SLIP</span>
                </div>
                <div class="text-muted small">Single-Vendor Fulfillment & Dispatch Office</div>
                <div class="text-muted small">Dhaka, Bangladesh | Phone: 01700-000000</div>
            </div>
            <div class="col-6 text-end">
                <h4 class="fw-bold text-dark mb-0">ORDER #{{ $order->order_number }}</h4>
                <div class="text-muted small">Order Date: {{ $order->created_at->format('d M, Y h:i A') }}</div>
                <div class="mt-2">
                    <span class="badge bg-primary px-2 py-1 text-uppercase">{{ $order->order_status }}</span>
                    <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning text-dark' }} px-2 py-1 text-uppercase ms-1">
                        {{ $order->payment_status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Customer & Delivery Information -->
        <div class="row mb-4">
            <div class="col-6">
                <h6 class="text-uppercase fw-bold text-muted small mb-2">Ship To / Customer:</h6>
                <div class="fw-bold text-dark fs-6">{{ $order->recipient_name }}</div>
                <div><i class="bi bi-telephone me-1"></i><strong>Phone:</strong> {{ $order->phone }}</div>
                @if($order->email)
                    <div><i class="bi bi-envelope me-1"></i><strong>Email:</strong> {{ $order->email }}</div>
                @endif
                <div class="mt-1">
                    <i class="bi bi-geo-alt me-1"></i><strong>Address:</strong> {{ $order->delivery_address }}, {{ $order->district }}
                </div>
                <div class="badge bg-light text-dark border mt-1">
                    Zone: {{ $order->shipping_zone === 'dhaka' ? 'Inside Dhaka City (৳60)' : 'Outside Dhaka (৳120)' }}
                </div>
                @if($order->user)
                    <div class="text-muted small mt-1">User Account ID: #{{ $order->user_id }} ({{ $order->user->name }})</div>
                @endif
            </div>

            <div class="col-6 text-end">
                <h6 class="text-uppercase fw-bold text-muted small mb-2">Payment & Dispatch Info:</h6>
                <div><strong>Payment Method:</strong> {{ $order->payment_method === 'cod' ? 'Cash on Delivery (COD)' : strtoupper($order->payment_method) }}</div>
                <div><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</div>
                <div class="fs-6 fw-bold text-primary mt-1">Collect Amount: ৳{{ number_format($order->total_amount, 2) }}</div>
                @if($order->delivery_instructions)
                    <div class="mt-2 text-start p-2 bg-light rounded small">
                        <strong>Customer Instructions:</strong><br>
                        <span class="fst-italic text-muted">{{ $order->delivery_instructions }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Items Table with Warehouse Packing Checkbox -->
        <div class="table-responsive mb-4">
            <table class="table table-invoice mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">Check</th>
                        <th style="width: 40px;">#</th>
                        <th>Product Description & SKU</th>
                        <th class="text-center" style="width: 120px;">Unit Price</th>
                        <th class="text-center" style="width: 80px;">Qty</th>
                        <th class="text-end" style="width: 130px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $index => $item)
                        <tr>
                            <td class="text-center">
                                <span class="check-box"></span>
                            </td>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                                <div class="text-muted small">
                                    SKU: <span class="font-monospace fw-semibold">{{ $item->sku }}</span>
                                    @if($item->product)
                                        &bull; In-Stock: {{ $item->product->stock_quantity }}
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">৳{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-center fw-bold">{{ $item->quantity }}</td>
                            <td class="text-end fw-semibold">৳{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Financial Summary -->
        <div class="row justify-content-end mb-4">
            <div class="col-6 col-md-5">
                <table class="table table-borderless small mb-0">
                    <tr>
                        <td class="text-muted text-end">Subtotal:</td>
                        <td class="text-end fw-semibold" style="width: 120px;">৳{{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    @if($order->discount_amount > 0)
                        <tr>
                            <td class="text-danger text-end">Discount @if($order->coupon) ({{ $order->coupon->code }}) @endif:</td>
                            <td class="text-end text-danger fw-semibold">-৳{{ number_format($order->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="text-muted text-end">Shipping Fee:</td>
                        <td class="text-end fw-semibold">৳{{ number_format($order->shipping_cost, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td class="text-end fw-bold">Grand Total:</td>
                        <td class="text-end fw-bold text-primary fs-5">৳{{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Warehouse Verification Signatures -->
        <div class="row pt-4 border-top mt-4 text-center">
            <div class="col-4">
                <div class="border-top border-dark mx-3 pt-2 small fw-semibold">Packed By</div>
            </div>
            <div class="col-4">
                <div class="border-top border-dark mx-3 pt-2 small fw-semibold">Quality Verified By</div>
            </div>
            <div class="col-4">
                <div class="border-top border-dark mx-3 pt-2 small fw-semibold">Courier Handover Sign</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
