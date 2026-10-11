@extends('layouts.admin')

@section('title', 'Add New Brand')
@section('page_title', 'Create Brand')
@section('page_subtitle', 'Add a manufacturer or brand partner to your catalog')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Brands
            </a>
            <span class="text-muted small">* Indicates required fields</span>
        </div>

        <div class="table-card p-4">
            <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <!-- Brand Name -->
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Apex, Walton, Aarong" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Brand Slug -->
                    <div class="col-md-6">
                        <label for="slug" class="form-label fw-bold">Slug</label>
                        <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="e.g. apex (auto-generated if empty)">
                        <small class="text-muted">Leave empty to auto-generate from brand name.</small>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Logo Upload -->
                    <div class="col-12">
                        <label for="logo" class="form-label fw-bold">Brand Logo</label>
                        <input type="file" name="logo" id="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">Accepted formats: JPG, PNG, WEBP (Max 2MB). Ideal size: 300x300px square or transparent PNG.</small>
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label for="description" class="form-label fw-bold">Brand Bio / Description</label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Brief background about the brand...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Active Toggle -->
                    <div class="col-12">
                        <div class="border rounded p-3 bg-light">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Active Status (Products under this brand will show brand filtering)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check2-circle me-1"></i> Save Brand
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
