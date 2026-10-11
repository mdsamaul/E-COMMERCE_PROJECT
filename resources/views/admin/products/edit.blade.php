@extends('layouts.admin')

@section('title', 'Edit Product — ' . $product->name)
@section('page_title', 'Edit Product')
@section('page_subtitle', 'Update product details, pricing, images, and inventory')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-11 col-xxl-10">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Products
            </a>
            <div class="d-flex gap-2 align-items-center">
                <span class="badge bg-secondary">Product ID: #{{ $product->id }}</span>
                <span class="badge bg-dark font-monospace">SKU: {{ $product->sku }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <!-- Left Column: Core Product Info -->
                <div class="col-lg-8">
                    <!-- Basic Information Card -->
                    <div class="table-card p-4 mb-4">
                        <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">
                            <i class="bi bi-info-circle me-2 text-primary"></i>General Information
                        </h6>

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="name" class="form-label fw-bold">Product Title <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="slug" class="form-label fw-bold">Product Slug</label>
                                <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $product->slug) }}">
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="sku" class="form-label fw-bold">SKU (Stock Keeping Unit) <span class="text-danger">*</span></label>
                                <input type="text" name="sku" id="sku" class="form-control text-uppercase @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku) }}" required>
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="short_description" class="form-label fw-bold">Short Summary</label>
                                <textarea name="short_description" id="short_description" rows="2" class="form-control @error('short_description') is-invalid @enderror">{{ old('short_description', $product->short_description) }}</textarea>
                                @error('short_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label fw-bold">Full Product Description</label>
                                <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Inventory Card -->
                    <div class="table-card p-4 mb-4">
                        <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">
                            <i class="bi bi-tag me-2 text-primary"></i>Pricing & Stock
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="regular_price" class="form-label fw-bold">Regular Price (৳) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" min="0" name="regular_price" id="regular_price" class="form-control @error('regular_price') is-invalid @enderror" value="{{ old('regular_price', $product->regular_price) }}" required>
                                </div>
                                @error('regular_price')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="sale_price" class="form-label fw-bold">Sale Price (৳)</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" min="0" name="sale_price" id="sale_price" class="form-control @error('sale_price') is-invalid @enderror" value="{{ old('sale_price', $product->sale_price) }}">
                                </div>
                                <small class="text-muted">Discounted price for promotional sales.</small>
                                @error('sale_price')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="purchase_price" class="form-label fw-bold">Cost / Purchase Price (৳)</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" min="0" name="purchase_price" id="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror" value="{{ old('purchase_price', $product->purchase_price) }}">
                                </div>
                                <small class="text-muted">Internal cost for analytics.</small>
                                @error('purchase_price')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="stock_quantity" class="form-label fw-bold">Stock Quantity <span class="text-danger">*</span></label>
                                <input type="number" min="0" name="stock_quantity" id="stock_quantity" class="form-control @error('stock_quantity') is-invalid @enderror" value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                                <small class="text-muted">Modifying stock creates an automatic inventory movement log.</small>
                                @error('stock_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="low_stock_threshold" class="form-label fw-bold">Low Stock Warning Threshold</label>
                                <input type="number" min="0" name="low_stock_threshold" id="low_stock_threshold" class="form-control @error('low_stock_threshold') is-invalid @enderror" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}">
                                @error('low_stock_threshold')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="stock_remarks" class="form-label fw-bold">Stock Adjustment Remarks</label>
                                <input type="text" name="stock_remarks" id="stock_remarks" class="form-control" value="{{ old('stock_remarks') }}" placeholder="Reason for inventory change (if stock was modified)">
                            </div>
                        </div>
                    </div>

                    <!-- Existing Gallery Images -->
                    <div class="table-card p-4 mb-4">
                        <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">
                            <i class="bi bi-images me-2 text-primary"></i>Current Gallery Photos
                        </h6>

                        @if($product->images->count() > 0)
                            <div class="row g-3 mb-4">
                                @foreach($product->images as $img)
                                    <div class="col-6 col-sm-4 col-md-3">
                                        <div class="border rounded p-2 text-center bg-white shadow-sm position-relative">
                                            <img src="{{ $img->url }}" class="img-fluid rounded mb-2 object-fit-cover" style="height: 110px; width: 100%;">
                                            @if($img->is_primary)
                                                <span class="badge bg-primary mb-2 d-block"><i class="bi bi-star-fill me-1"></i>Primary</span>
                                            @else
                                                <button type="button" class="btn btn-xs btn-outline-primary mb-2 w-100" onclick="document.getElementById('set-primary-{{ $img->id }}').submit();">
                                                    Set Primary
                                                </button>
                                            @endif

                                            @if($product->images->count() > 1)
                                                <button type="button" class="btn btn-xs btn-outline-danger w-100" onclick="if(confirm('Delete this product photo?')) document.getElementById('delete-img-{{ $img->id }}').submit();">
                                                    <i class="bi bi-trash me-1"></i>Delete
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted small">No images uploaded for this product yet.</p>
                        @endif

                        <div class="pt-3 border-top">
                            <label for="images" class="form-label fw-bold">Add More Photos</label>
                            <input type="file" name="images[]" id="images" class="form-control" multiple accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">Select additional images to add to the product gallery (JPG, PNG, WEBP, max 2MB).</small>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Taxonomy & Visibility -->
                <div class="col-lg-4">
                    <div class="table-card p-4 mb-4">
                        <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">
                            <i class="bi bi-diagram-3 me-2 text-primary"></i>Category & Brand
                        </h6>

                        <div class="mb-3">
                            <label for="category_id" class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="brand_id" class="form-label fw-bold">Brand</label>
                            <select name="brand_id" id="brand_id" class="form-select @error('brand_id') is-invalid @enderror">
                                <option value="">No Brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('brand_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Visibility & Flags -->
                    <div class="table-card p-4 mb-4">
                        <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">
                            <i class="bi bi-eye me-2 text-primary"></i>Storefront Visibility
                        </h6>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_active">
                                Active & Visible in Store
                            </label>
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_featured">
                                Featured Product (Homepage Highlights)
                            </label>
                        </div>
                    </div>

                    <!-- Submission buttons -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save me-1"></i> Update Product
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- Hidden Forms for Image Management -->
        @foreach($product->images as $img)
            <form id="set-primary-{{ $img->id }}" action="{{ route('admin.products.images.primary', [$product, $img]) }}" method="POST" style="display: none;">
                @csrf
            </form>
            <form id="delete-img-{{ $img->id }}" action="{{ route('admin.products.images.delete', [$product, $img]) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    </div>
</div>
@endsection
