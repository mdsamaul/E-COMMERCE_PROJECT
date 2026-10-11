<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }} - ShopBD</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            font-size: 14px;
        }
        .invoice-card {
            max-width: 850px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 40px;
            border: 1px solid #e2e8f0;
        }
        .invoice-logo {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }
        .invoice-logo span {
            color: #2563eb;
        }
        .invoice-badge {
            font-size: 12px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table-invoice th {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 14px;
            border: none;
        }
        .table-invoice td {
            padding: 14px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .total-row {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            border-top: 2px solid #e2e8f0;
        }
        .watermark {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 80px;
            font-weight: 900;
            color: rgba(148, 163, 184, 0.12);
            pointer-events: none;
            text-transform: uppercase;
            letter-spacing: 4px;
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
    <div class="d-flex justify-content-between align-items-center mt-4 mb-2 no-print" style="max-width: 850px; margin: 0 auto;">
        <a href="{{ route('customer.orders.show', $order->order_number) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Order Details
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm px-3 shadow-sm">
                <i class="bi bi-printer me-1"></i> Print Invoice
            </button>
        </div>
    </div>

    <!-- Main Printable Invoice Card -->
    <div class="invoice-card position-relative">
        @if($order->order_status === 'cancelled')
            <div class="watermark">CANCELLED</div>
        @elseif($order->order_status === 'returned')
            <div class="watermark">RETURNED</div>
        @elseif($order->payment_status === 'paid')
            <div class="watermark" style="color: rgba(34, 197, 94, 0.1);">PAID</div>
        @endif

        <!-- Invoice Header -->
        <div class="row align-items-center mb-4 pb-3 border-bottom">
            <div class="col-sm-6">
                <div class="invoice-logo mb-1">
                    Shop<span>BD</span>
                </div>
                <div class="text-muted small">Single-Vendor Multi-Category E-Commerce</div>
                <div class="text-muted small">Dhaka, Bangladesh | Support: 01700-000000</div>
                <div class="text-muted small">Email: support@shopbd.com | Web: www.shopbd.com</div>
            </div>
            <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                <h4 class="fw-bold mb-1 text-uppercase text-secondary">INVOICE</h4>
                <div class="fw-bold text-dark fs-5">#{{ $order->order_number }}</div>
                <div class="text-muted small">Date: {{ $order->created_at->format('d M, Y h:i A') }}</div>
                <div class="mt-2">
                    <span class="invoice-badge bg-{{ $order->order_status === 'delivered' ? 'success' : ($order->order_status === 'cancelled' ? 'danger' : 'primary') }} text-white">
                        Order: {{ ucfirst($order->order_status) }}
                    </span>
                    <span class="invoice-badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }} text-{{ $order->payment_status === 'paid' ? 'white' : 'dark' }} ms-1">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Customer & Order Meta Information -->
        <div class="row mb-4">
            <div class="col-sm-6">
                <h6 class="text-uppercase fw-bold text-muted small mb-2">Billed & Shipped To:</h6>
                <div class="fw-bold text-dark">{{ $order->recipient_name }}</div>
                <div class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $order->phone }}</div>
                @if($order->email)
                    <div class="text-muted small"><i class="bi bi-envelope me-1"></i>{{ $order->email }}</div>
                @endif
                <div class="text-muted small mt-1">
                    <i class="bi bi-geo-alt me-1"></i>{{ $order->delivery_address }}<br>
                    {{ $order->district }} - {{ $order->shipping_zone === 'dhaka' ? 'Inside Dhaka City' : 'Outside Dhaka' }}
                </div>
            </div>
            <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                <h6 class="text-uppercase fw-bold text-muted small mb-2">Payment Details:</h6>
                <div class="small">
                    <strong>Payment Method:</strong> 
                    {{ $order->payment_method === 'cod' ? 'Cash on Delivery (COD)' : strtoupper($order->payment_method) }}
                </div>
                <div class="small">
                    <strong>Payment Status:</strong> 
                    <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </div>
                @if($order->delivery_instructions)
                    <div class="mt-2 text-start p-2 bg-light rounded small">
                        <strong>Delivery Instructions:</strong><br>
                        <span class="text-muted fst-italic">{{ $order->delivery_instructions }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Order Items Table -->
        <div class="table-responsive mb-4">
            <table class="table table-invoice mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Product Description</th>
                        <th class="text-center" style="width: 120px;">Unit Price</th>
                        <th class="text-center" style="width: 80px;">Qty</th>
                        <th class="text-end" style="width: 130px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                                <div class="text-muted small">SKU: {{ $item->sku }}</div>
                            </td>
                            <td class="text-center text-nowrap">৳{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end fw-semibold text-nowrap">৳{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary & Totals -->
        <div class="row justify-content-end mb-4">
            <div class="col-sm-6 col-md-5">
                <table class="table table-borderless small mb-0">
                    <tr>
                        <td class="text-muted text-end">Subtotal:</td>
                        <td class="text-end fw-semibold" style="width: 120px;">৳{{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    @if($order->discount_amount > 0)
                        <tr>
                            <td class="text-danger text-end">
                                Discount @if($order->coupon) ({{ $order->coupon->code }}) @endif:
                            </td>
                            <td class="text-end text-danger fw-semibold">-৳{{ number_format($order->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="text-muted text-end">Delivery Charge ({{ $order->shipping_zone === 'dhaka' ? 'Inside Dhaka' : 'Outside Dhaka' }}):</td>
                        <td class="text-end fw-semibold">৳{{ number_format($order->shipping_cost, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td class="text-end fw-bold">Grand Total:</td>
                        <td class="text-end fw-bold text-primary fs-5">৳{{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Footer / Terms Note -->
        <div class="pt-3 border-top text-center text-muted small">
            <p class="mb-1 fw-semibold text-dark">Thank you for shopping with ShopBD!</p>
            <p class="mb-0">
                Please check products upon delivery. For queries, warranty or return claims within 7 days, 
                contact support@shopbd.com or call our hotline: 01700-000000.
            </p>
        </div>
    </div>
</div>

</body>
</html>
