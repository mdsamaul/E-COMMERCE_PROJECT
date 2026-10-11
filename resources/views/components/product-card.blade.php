@props(['product'])

<div class="product-card">
    <div class="product-thumbnail-container">
        @if($product->has_discount)
            <span class="badge-discount">-{{ $product->discount_percentage }}% OFF</span>
        @endif

        <a href="{{ route('products.show', $product->slug) }}">
            @if($product->primaryImage)
                <img src="{{ $product->primaryImage->url }}" alt="{{ $product->name }}" loading="lazy">
            @else
                <div class="d-flex align-items-center justify-content-center bg-light text-muted w-100 h-100 position-absolute top-0 start-0">
                    <i class="bi bi-card-image fs-1"></i>
                </div>
            @endif
        </a>
    </div>

    <div class="p-3 d-flex flex-column flex-grow-1">
        <!-- Category & Brand -->
        <div class="d-flex justify-content-between align-items-center mb-1 small">
            @if($product->category)
                <a href="{{ route('products.category', $product->category->slug) }}" class="text-decoration-none text-muted fw-semibold">
                    {{ $product->category->name }}
                </a>
            @else
                <span class="text-muted">General</span>
            @endif

            @if($product->brand)
                <span class="badge bg-light text-secondary border">{{ $product->brand->name }}</span>
            @endif
        </div>

        <!-- Title -->
        <h6 class="fw-bold mb-2 flex-grow-1">
            <a href="{{ route('products.show', $product->slug) }}" class="text-dark text-decoration-none text-truncate-2 hover-primary" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 2.6rem;">
                {{ $product->name }}
            </a>
        </h6>

        <!-- Rating -->
        <div class="d-flex align-items-center mb-2 small text-warning">
            @php $rating = (int) round($product->average_rating); @endphp
            @for($i = 1; $i <= 5; $i++)
                <i class="bi {{ $i <= $rating ? 'bi-star-fill' : 'bi-star' }}"></i>
            @endfor
            <span class="text-muted ms-1">({{ $product->reviews_count }})</span>
        </div>

        <!-- Price & Availability -->
        <div class="d-flex align-items-baseline justify-content-between pt-2 border-top mt-auto">
            <div>
                @if($product->has_discount)
                    <span class="fw-bold fs-5 text-danger">৳{{ number_format($product->sale_price, 2) }}</span>
                    <small class="text-muted text-decoration-line-through ms-1">৳{{ number_format($product->regular_price, 2) }}</small>
                @else
                    <span class="fw-bold fs-5 text-dark">৳{{ number_format($product->regular_price, 2) }}</span>
                @endif
            </div>

            <div>
                @if(!$product->in_stock)
                    <span class="badge bg-secondary">Out of Stock</span>
                @elseif($product->is_low_stock)
                    <span class="badge bg-warning-subtle text-dark">Low Stock</span>
                @else
                    <span class="badge bg-success-subtle text-success">In Stock</span>
                @endif
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('products.show', $product->slug) }}" class="btn btn-sm btn-outline-shop w-100">
                <i class="bi bi-eye me-1"></i> View Details
            </a>
        </div>
    </div>
</div>
