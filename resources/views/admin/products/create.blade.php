@extends('layouts.admin')

@section('title', 'Add New Product')
@section('page_title', 'Create Product')
@section('page_subtitle', 'Add a new product with prices, SKU, multiple images, and inventory records')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-11 col-xxl-10">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Products
            </a>
            <span class="text-muted small">* Indicates required fields</span>
        </div>

        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf

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
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Premium Cotton Panjabi, Wireless Bluetooth Earbuds" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="slug" class="form-label fw-bold">Product Slug</label>
                                <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="auto-generated from title if blank">
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="sku" class="form-label fw-bold">SKU (Stock Keeping Unit) <span class="text-danger">*</span></label>
                                <input type="text" name="sku" id="sku" class="form-control text-uppercase @error('sku') is-invalid @enderror" value="{{ old('sku') }}" placeholder="e.g. PANJ-001, EARB-BLU-02" required>
                                <small class="text-muted">Must be globally unique.</small>
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="short_description" class="form-label fw-bold">Short Summary</label>
                                <textarea name="short_description" id="short_description" rows="2" class="form-control @error('short_description') is-invalid @enderror" placeholder="Quick 1-2 sentence overview shown in product lists">{{ old('short_description') }}</textarea>
                                @error('short_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label fw-bold">Full Product Description</label>
                                <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror" placeholder="Detailed product specifications, materials, features, washing instructions, warranty info...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Inventory Card -->
                    <div class="table-card p-4 mb-4">
                        <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">
                            <i class="bi bi-tag me-2 text-primary"></i>Pricing & Stock Management
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="regular_price" class="form-label fw-bold">Regular Price (৳) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" min="0" name="regular_price" id="regular_price" class="form-control @error('regular_price') is-invalid @enderror" value="{{ old('regular_price') }}" required>
                                </div>
                                @error('regular_price')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="sale_price" class="form-label fw-bold">Sale Price (৳)</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" min="0" name="sale_price" id="sale_price" class="form-control @error('sale_price') is-invalid @enderror" value="{{ old('sale_price') }}" placeholder="Leave blank if no discount">
                                </div>
                                <small class="text-muted">Must be less than regular price.</small>
                                @error('sale_price')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="purchase_price" class="form-label fw-bold">Cost / Purchase Price (৳)</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" min="0" name="purchase_price" id="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror" value="{{ old('purchase_price') }}" placeholder="Internal cost">
                                </div>
                                <small class="text-muted">For profit calculation only.</small>
                                @error('purchase_price')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="stock_quantity" class="form-label fw-bold">Initial Stock Quantity <span class="text-danger">*</span></label>
                                <input type="number" min="0" name="stock_quantity" id="stock_quantity" class="form-control @error('stock_quantity') is-invalid @enderror" value="{{ old('stock_quantity', 10) }}" required>
                                <small class="text-muted">Initial inventory will be logged in Inventory Movements.</small>
                                @error('stock_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="low_stock_threshold" class="form-label fw-bold">Low Stock Warning Threshold</label>
                                <input type="number" min="0" name="low_stock_threshold" id="low_stock_threshold" class="form-control @error('low_stock_threshold') is-invalid @enderror" value="{{ old('low_stock_threshold', 5) }}">
                                <small class="text-muted">Dashboard alerts when stock drops below this.</small>
                                @error('low_stock_threshold')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="stock_remarks" class="form-label fw-bold">Inventory Movement Remarks</label>
                                <input type="text" name="stock_remarks" id="stock_remarks" class="form-control" value="{{ old('stock_remarks', 'Initial stock entry upon product creation') }}" placeholder="e.g. Warehouse receipt batch #1">
                            </div>
                        </div>
                    </div>

                    <!-- Images Card -->
                    <div class="table-card p-4">
                        <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">
                            <i class="bi bi-images me-2 text-primary"></i>Product Images
                        </h6>

                        <div class="mb-3">
                            <label for="images" class="form-label fw-bold">Upload Product Photos</label>
                            <input type="file" name="images[]" id="images" class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror" multiple accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">Select one or multiple images (JPG, PNG, WEBP, max 2MB each). First image becomes primary automatically.</small>
                            @error('images')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('images.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                <option value="">No Specific Brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
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
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_active">
                                Active & Visible in Catalog
                            </label>
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_featured">
                                Featured Product (Homepage Highlights)
                            </label>
                        </div>
                    </div>

                    <!-- Submission buttons -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check2-circle me-1"></i> Save & Publish Product
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                            Discard Changes
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
