@extends('layouts.admin')

@section('title', 'Edit Brand — ' . $brand->name)
@section('page_title', 'Edit Brand')
@section('page_subtitle', 'Update manufacturer or brand details')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Brands
            </a>
            <span class="text-muted small">Brand ID: #{{ $brand->id }}</span>
        </div>

        <div class="table-card p-4">
            <form method="POST" action="{{ route('admin.brands.update', $brand) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <!-- Brand Name -->
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $brand->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Brand Slug -->
                    <div class="col-md-6">
                        <label for="slug" class="form-label fw-bold">Slug</label>
                        <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $brand->slug) }}">
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Logo Upload -->
                    <div class="col-12">
                        <label for="logo" class="form-label fw-bold">Brand Logo</label>
                        @if($brand->logo)
                            <div class="mb-2 d-flex align-items-center gap-3">
                                <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="rounded border p-1 object-fit-contain bg-white" width="64" height="64">
                                <small class="text-muted">Current logo. Uploading a new file will replace it.</small>
                            </div>
                        @endif
                        <input type="file" name="logo" id="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">Leave empty to keep existing logo. JPG, PNG, WEBP (Max 2MB).</small>
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label for="description" class="form-label fw-bold">Brand Bio / Description</label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $brand->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Active Toggle -->
                    <div class="col-12">
                        <div class="border rounded p-3 bg-light">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $brand->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Active Status
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Update Brand
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
