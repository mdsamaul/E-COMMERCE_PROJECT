@extends('layouts.app')

@section('title', 'My Wishlist - ShopBD')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-transparent p-0 mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customer.profile') }}" class="text-decoration-none">My Account</a></li>
            <li class="breadcrumb-item active" aria-current="page">Wishlist</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h4 fw-bold mb-1 text-dark">
                <i class="bi bi-heart text-danger me-2"></i>My Wishlist
            </h2>
            <p class="text-muted small mb-0">Saved products you love. Move them to your cart anytime.</p>
        </div>
        <div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fs-6">
                {{ $items->count() }} {{ Str::plural('Saved Item', $items->count()) }}
            </span>
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

    @if($items->isEmpty())
        <!-- Empty Wishlist State -->
        <div class="card border-0 shadow-sm text-center py-5 my-4 rounded-3">
            <div class="card-body py-5">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                    <i class="bi bi-heart fs-1"></i>
                </div>
                <h4 class="fw-bold mb-2">Your wishlist is currently empty</h4>
                <p class="text-muted mb-4 mx-auto" style="max-width: 450px;">
                    Explore our huge collection of quality products and tap the heart icon on any product to save it here for later.
                </p>
                <a href="{{ route('products.index') }}" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                    <i class="bi bi-bag-plus me-2"></i>Start Shopping
                </a>
            </div>
        </div>
    @else
        <!-- Wishlist Items Table / Grid -->
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th style="width: 100px;">Product</th>
                            <th>Details</th>
                            <th class="text-center" style="width: 150px;">Price</th>
                            <th class="text-center" style="width: 140px;">Stock Status</th>
                            <th class="text-end" style="width: 240px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($items as $item)
                            @php
                                $product = $item->product;
                                $inStock = $product && $product->is_active && $product->stock_quantity > 0;
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ $product ? route('products.show', $product->slug) : '#' }}">
                                        @if($product && $product->primaryImage)
                                            <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="rounded object-fit-cover border" 
                                                 width="70" height="70">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted border" style="width: 70px; height: 70px;">
                                                <i class="bi bi-image fs-4"></i>
                                            </div>
                                        @endif
                                    </a>
                                </td>
                                <td>
                                    @if($product)
                                        <h6 class="mb-1">
                                            <a href="{{ route('products.show', $product->slug) }}" class="text-dark text-decoration-none fw-semibold">
                                                {{ $product->name }}
                                            </a>
                                        </h6>
                                        <div class="text-muted small">
                                            @if($product->category)
                                                <span class="me-2"><i class="bi bi-folder2 me-1"></i>{{ $product->category->name }}</span>
                                            @endif
                                            @if($product->brand)
                                                <span><i class="bi bi-tag me-1"></i>{{ $product->brand->name }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-muted fst-italic">Product no longer available</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($product)
                                        <div class="fw-bold text-dark fs-6">
                                            ৳{{ number_format($product->price, 2) }}
                                        </div>
                                        @if($product->original_price && $product->original_price > $product->price)
                                            <del class="text-muted small">৳{{ number_format($product->original_price, 2) }}</del>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($inStock)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-check2-circle me-1"></i>In Stock ({{ $product->stock_quantity }})
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                            <i class="bi bi-x-circle me-1"></i>Out of Stock
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        @if($inStock)
                                            <form action="{{ route('customer.wishlist.move-to-cart', $product->id) }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center shadow-sm">
                                                    <i class="bi bi-cart-plus me-1"></i> Move to Cart
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-secondary disabled" title="Product is currently out of stock">
                                                <i class="bi bi-cart-x me-1"></i> Out of Stock
                                            </button>
                                        @endif

                                        <form action="{{ route('customer.wishlist.destroy', $item->product_id) }}" method="POST" class="m-0" onsubmit="return confirm('Remove this product from your wishlist?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove from wishlist">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
