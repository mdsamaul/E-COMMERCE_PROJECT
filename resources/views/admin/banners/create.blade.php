@extends('layouts.admin')

@section('title', 'Add New Banner')
@section('page_title', 'Create Banner')
@section('page_subtitle', 'Upload a promotional hero banner or marketing card')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Banners
            </a>
            <span class="text-muted small">* Indicates required fields</span>
        </div>

        <div class="table-card p-4">
            <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <!-- Title -->
                    <div class="col-md-6">
                        <label for="title" class="form-label fw-bold">Banner Headline / Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="e.g. Eid Mega Sale, New Arrival 2026" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Subtitle -->
                    <div class="col-md-6">
                        <label for="subtitle" class="form-label fw-bold">Subtitle / Tagline</label>
                        <input type="text" name="subtitle" id="subtitle" class="form-control @error('subtitle') is-invalid @enderror" value="{{ old('subtitle') }}" placeholder="e.g. Up to 50% Off On All Traditional Attires">
                        @error('subtitle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Placement -->
                    <div class="col-md-6">
                        <label for="placement" class="form-label fw-bold">Banner Placement <span class="text-danger">*</span></label>
                        <select name="placement" id="placement" class="form-select @error('placement') is-invalid @enderror" required>
                            <option value="hero_slider" {{ old('placement') === 'hero_slider' ? 'selected' : '' }}>Hero Carousel Slider (Homepage Main)</option>
                            <option value="promo_card" {{ old('placement') === 'promo_card' ? 'selected' : '' }}>Promotional Grid Card</option>
                        </select>
                        @error('placement')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Sort Order -->
                    <div class="col-md-6">
                        <label for="sort_order" class="form-label fw-bold">Sort Order</label>
                        <input type="number" min="0" name="sort_order" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', 0) }}">
                        <small class="text-muted">Lower order appears first in slider.</small>
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Banner Graphic Upload -->
                    <div class="col-12">
                        <label for="image" class="form-label fw-bold">Banner Image Graphic <span class="text-danger">*</span></label>
                        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" required>
                        <small class="text-muted">Recommended size: 1920x600px for Hero Slider, 600x400px for Promo Card (Max 3MB).</small>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Button Text -->
                    <div class="col-md-6">
                        <label for="button_text" class="form-label fw-bold">Button CTA Label</label>
                        <input type="text" name="button_text" id="button_text" class="form-control @error('button_text') is-invalid @enderror" value="{{ old('button_text') }}" placeholder="e.g. Shop Now, Explore Collection">
                        @error('button_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Button URL -->
                    <div class="col-md-6">
                        <label for="button_url" class="form-label fw-bold">Button Destination URL</label>
                        <input type="text" name="button_url" id="button_url" class="form-control @error('button_url') is-invalid @enderror" value="{{ old('button_url') }}" placeholder="e.g. /category/clothing or /products">
                        @error('button_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Active Toggle -->
                    <div class="col-12">
                        <div class="border rounded p-3 bg-light">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Active (Display banner on storefront)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check2-circle me-1"></i> Save Banner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
