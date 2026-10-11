@extends('layouts.app')

@section('title', $product->name . ' — Buy Online in Bangladesh | ShopBD')
@section('meta_description', Str::limit(strip_tags($product->short_description ?? $product->description), 160))
@section('meta_image', $product->primaryImage?->url ?? asset('images/default.jpg'))

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            @if($product->category)
                <li class="breadcrumb-item"><a href="{{ route('products.category', $product->category->slug) }}" class="text-decoration-none text-muted">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active text-truncate" style="max-width: 300px;" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-4 g-lg-5 mb-5">
        <!-- Left: Image Gallery -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <!-- Large Active Image Display -->
                <div class="position-relative bg-light rounded text-center mb-3" style="height: 420px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                    @if($product->has_discount)
                        <span class="badge-discount">-{{ $product->discount_percentage }}% OFF</span>
                    @endif

                    @if($product->primaryImage)
                        <img id="mainProductImage" src="{{ $product->primaryImage->url }}" alt="{{ $product->name }}" class="img-fluid object-fit-contain" style="max-height: 400px; max-width: 100%; transition: opacity 0.2s;">
                    @else
                        <div class="text-muted"><i class="bi bi-card-image fs-1"></i></div>
                    @endif
                </div>

                <!-- Gallery Thumbnails -->
                @if($product->images->count() > 1)
                    <div class="d-flex gap-2 overflow-auto pb-1" id="thumbnailStrip">
                        @foreach($product->images as $index => $img)
                            <div class="border rounded p-1 cursor-pointer thumbnail-item {{ $img->is_primary ? 'border-primary border-2' : '' }}" style="width: 76px; height: 76px; flex-shrink: 0; cursor: pointer;" onclick="changeMainImage('{{ $img->url }}', this)">
                                <img src="{{ $img->url }}" alt="{{ $product->name }} thumbnail {{ $index }}" class="w-100 h-100 object-fit-cover rounded">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Right: Product Information & Purchase Controls -->
        <div class="col-lg-6">
            <!-- Taxonomy Badges -->
            <div class="d-flex align-items-center gap-2 mb-2">
                @if($product->category)
                    <a href="{{ route('products.category', $product->category->slug) }}" class="badge bg-light text-shop-primary border text-decoration-none">
                        {{ $product->category->name }}
                    </a>
                @endif
                @if($product->brand)
                    <a href="{{ route('products.brand', $product->brand->slug) }}" class="badge bg-light text-secondary border text-decoration-none">
                        {{ $product->brand->name }}
                    </a>
                @endif
                <span class="badge bg-secondary-subtle text-secondary font-monospace">SKU: {{ $product->sku }}</span>
            </div>

            <!-- Title -->
            <h2 class="fw-bold text-dark mb-2">{{ $product->name }}</h2>

            <!-- Rating & Reviews -->
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="text-warning">
                    @php $rating = (int) round($product->average_rating); @endphp
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi {{ $i <= $rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                    @endfor
                </div>
                <span class="fw-bold small">{{ $product->average_rating }}</span>
                <span class="text-muted small">({{ $product->reviews_count }} Verified Reviews)</span>
            </div>

            <!-- Price Box -->
            <div class="p-3 bg-light rounded-3 mb-3 border">
                <div class="d-flex align-items-baseline gap-3">
                    @if($product->has_discount)
                        <span class="fw-bold fs-2 text-danger">৳{{ number_format($product->sale_price, 2) }}</span>
                        <span class="text-muted text-decoration-line-through fs-5">৳{{ number_format($product->regular_price, 2) }}</span>
                        <span class="badge bg-danger">Save ৳{{ number_format($product->regular_price - $product->sale_price, 2) }}</span>
                    @else
                        <span class="fw-bold fs-2 text-dark">৳{{ number_format($product->regular_price, 2) }}</span>
                    @endif
                </div>
                <small class="text-muted d-block mt-1">Inclusive of all taxes. Delivery calculated at checkout.</small>
            </div>

            <!-- Stock Availability -->
            <div class="mb-3">
                @if(!$product->in_stock)
                    <span class="badge bg-danger fs-6 py-2 px-3">
                        <i class="bi bi-x-circle me-1"></i> Out of Stock
                    </span>
                @elseif($product->is_low_stock)
                    <span class="badge bg-warning text-dark fs-6 py-2 px-3">
                        <i class="bi bi-exclamation-triangle me-1"></i> Hurry! Only {{ $product->stock_quantity }} units left in stock
                    </span>
                @else
                    <span class="badge bg-success fs-6 py-2 px-3">
                        <i class="bi bi-check-circle me-1"></i> In Stock & Ready to Dispatch
                    </span>
                @endif
            </div>

            <!-- Short Description -->
            @if($product->short_description)
                <p class="text-secondary mb-4">{{ $product->short_description }}</p>
            @endif

            <!-- Purchase Controls (Qty + Buttons) -->
            @if($product->in_stock)
                <div class="row g-3 align-items-center mb-4">
                    <div class="col-auto">
                        <label class="form-label small fw-bold d-block mb-1">Quantity</label>
                        <div class="input-group" style="width: 130px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="decrementQty()"><i class="bi bi-dash"></i></button>
                            <input type="number" id="purchaseQuantity" class="form-control text-center" value="1" min="1" max="{{ $product->stock_quantity }}">
                            <button class="btn btn-outline-secondary" type="button" onclick="incrementQty()"><i class="bi bi-plus"></i></button>
                        </div>
                    </div>

                    <div class="col">
                        <label class="form-label small fw-bold d-block mb-1">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button class="btn btn-shop-primary flex-grow-1 py-2 fw-bold" onclick="alert('Product added to cart! Complete checkout via cart in Phase 6.')">
                                <i class="bi bi-cart-plus me-1"></i> Add to Cart
                            </button>
                            <button class="btn btn-warning flex-grow-1 py-2 fw-bold text-dark" onclick="alert('Proceeding directly to instant checkout! (Phase 7)')">
                                <i class="bi bi-bag-check me-1"></i> Buy Now
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Delivery & Return Trust Box -->
            <div class="card border border-info-subtle bg-info-subtle p-3 rounded-3 small">
                <div class="row g-2">
                    <div class="col-12 d-flex align-items-center gap-2">
                        <i class="bi bi-truck text-primary fs-5"></i>
                        <div>
                            <strong>Inside Dhaka:</strong> ৳{{ number_format(\App\Models\Setting::get('inside_dhaka_delivery_fee', 60), 2) }} (Delivery in 24-48 Hours)
                        </div>
                    </div>
                    <div class="col-12 d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt text-primary fs-5"></i>
                        <div>
                            <strong>Outside Dhaka:</strong> ৳{{ number_format(\App\Models\Setting::get('outside_dhaka_delivery_fee', 120), 2) }} (Nationwide Courier in 3-5 Days)
                        </div>
                    </div>
                    <div class="col-12 d-flex align-items-center gap-2">
                        <i class="bi bi-cash-coin text-success fs-5"></i>
                        <div>
                            <strong>Cash on Delivery (COD)</strong> available at doorstep.
                        </div>
                    </div>
                    <div class="col-12 d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-counterclockwise text-warning fs-5"></i>
                        <div>
                            <strong>7-Day Returns:</strong> Easy refund or replacement policy.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Description, Specs & Reviews Tabs -->
    <div class="card border-0 shadow-sm p-4 mb-5 bg-white">
        <ul class="nav nav-tabs mb-4" id="productTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-dark" id="desc-tab" data-bs-toggle="tab" data-bs-target="#descTab" type="button" role="tab">
                    Product Description
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-dark" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specsTab" type="button" role="tab">
                    Specifications
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-dark" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviewsTab" type="button" role="tab">
                    Reviews ({{ $product->approvedReviews->count() }})
                </button>
            </li>
        </ul>

        <div class="tab-content" id="productTabsContent">
            <!-- Description Tab -->
            <div class="tab-pane fade show active" id="descTab" role="tabpanel">
                <div class="text-secondary" style="white-space: pre-line; line-height: 1.8;">
                    {{ $product->description ?? $product->short_description ?? 'No detailed description provided for this product.' }}
                </div>
            </div>

            <!-- Specifications Tab -->
            <div class="tab-pane fade" id="specsTab" role="tabpanel">
                <div class="table-responsive" style="max-width: 600px;">
                    <table class="table table-bordered table-sm small">
                        <tbody>
                            <tr>
                                <th class="bg-light text-muted" style="width: 200px;">Product Name</th>
                                <td>{{ $product->name }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-muted">SKU Code</th>
                                <td class="font-monospace">{{ $product->sku }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-muted">Category</th>
                                <td>{{ $product->category?->name ?? 'General' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-muted">Brand / Manufacturer</th>
                                <td>{{ $product->brand?->name ?? 'Authentic Supplier' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-muted">Stock Status</th>
                                <td>{{ $product->in_stock ? 'In Stock' : 'Out of Stock' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-muted">Country of Origin</th>
                                <td>Bangladesh / Imported</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reviews Tab -->
            <div class="tab-pane fade" id="reviewsTab" role="tabpanel">
                @if($product->approvedReviews->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($product->approvedReviews as $review)
                            <div class="list-group-item px-0 py-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="fw-bold">{{ $review->user?->name ?? 'Verified Customer' }}</div>
                                    <small class="text-muted">{{ $review->created_at->format('d M, Y') }}</small>
                                </div>
                                <div class="text-warning small mb-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    @endfor
                                </div>
                                <p class="text-secondary small mb-0">{{ $review->comment }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-chat-square-quote fs-1 d-block mb-2"></i>
                        <p class="mb-0">There are no customer reviews for this product yet.</p>
                        <small>Customers who purchase this item can submit verified feedback.</small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="mb-5">
            <h4 class="fw-bold mb-4"><i class="bi bi-grid-fill text-shop-primary me-2"></i>Related Products You May Like</h4>
            <div class="row g-3 g-md-4">
                @foreach($relatedProducts as $related)
                    <div class="col-6 col-md-3">
                        <x-product-card :product="$related" />
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function changeMainImage(url, element) {
        const mainImg = document.getElementById('mainProductImage');
        if (mainImg) {
            mainImg.style.opacity = '0.3';
            setTimeout(() => {
                mainImg.src = url;
                mainImg.style.opacity = '1';
            }, 100);
        }
        document.querySelectorAll('#thumbnailStrip .thumbnail-item').forEach(el => {
            el.classList.remove('border-primary', 'border-2');
        });
        element.classList.add('border-primary', 'border-2');
    }

    function incrementQty() {
        const input = document.getElementById('purchaseQuantity');
        const max = parseInt(input.getAttribute('max')) || 999;
        let val = parseInt(input.value) || 1;
        if (val < max) input.value = val + 1;
    }

    function decrementQty() {
        const input = document.getElementById('purchaseQuantity');
        let val = parseInt(input.value) || 1;
        if (val > 1) input.value = val - 1;
    }
</script>
@endpush
