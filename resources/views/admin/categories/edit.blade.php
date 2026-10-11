@extends('layouts.admin')

@section('title', 'Edit Category — ' . $category->name)
@section('page_title', 'Edit Category')
@section('page_subtitle', 'Update category details and taxonomy')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Categories
            </a>
            <span class="text-muted small">Category ID: #{{ $category->id }}</span>
        </div>

        <div class="table-card p-4">
            <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <!-- Category Name -->
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Category Slug -->
                    <div class="col-md-6">
                        <label for="slug" class="form-label fw-bold">Slug (URL identifier)</label>
                        <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $category->slug) }}">
                        <small class="text-muted">Unique URL-friendly slug.</small>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Parent Category -->
                    <div class="col-md-6">
                        <label for="parent_id" class="form-label fw-bold">Parent Category</label>
                        <select name="parent_id" id="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                            <option value="">None (Top-Level Category)</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Sort Order -->
                    <div class="col-md-6">
                        <label for="sort_order" class="form-label fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $category->sort_order) }}" min="0">
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Category Image -->
                    <div class="col-12">
                        <label for="image" class="form-label fw-bold">Category Image</label>
                        @if($category->image)
                            <div class="mb-2 d-flex align-items-center gap-3">
                                <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="rounded border object-fit-cover" width="64" height="64">
                                <small class="text-muted">Current image. Uploading a new file will safely replace it.</small>
                            </div>
                        @endif
                        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">Leave empty to keep existing image. JPG, PNG, WEBP (Max 2MB).</small>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Options -->
                    <div class="col-12">
                        <div class="border rounded p-3 bg-light">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured', $category->is_featured) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_featured">
                                    Feature on Homepage
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Active Status (Visible to store visitors)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
