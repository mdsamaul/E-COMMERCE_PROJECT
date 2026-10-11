@extends('layouts.admin')

@section('title', 'Edit Banner — ' . $banner->title)
@section('page_title', 'Edit Banner')
@section('page_subtitle', 'Update banner graphics, placement, and destination links')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Banners
            </a>
            <span class="text-muted small">Banner ID: #{{ $banner->id }}</span>
        </div>

        <div class="table-card p-4">
            <form method="POST" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <!-- Title -->
                    <div class="col-md-6">
                        <label for="title" class="form-label fw-bold">Banner Headline / Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $banner->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Subtitle -->
                    <div class="col-md-6">
                        <label for="subtitle" class="form-label fw-bold">Subtitle / Tagline</label>
                        <input type="text" name="subtitle" id="subtitle" class="form-control @error('subtitle') is-invalid @enderror" value="{{ old('subtitle', $banner->subtitle) }}">
                        @error('subtitle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Placement -->
                    <div class="col-md-6">
                        <label for="placement" class="form-label fw-bold">Banner Placement <span class="text-danger">*</span></label>
                        <select name="placement" id="placement" class="form-select @error('placement') is-invalid @enderror" required>
                            <option value="hero_slider" {{ old('placement', $banner->placement) === 'hero_slider' ? 'selected' : '' }}>Hero Carousel Slider (Homepage Main)</option>
                            <option value="promo_card" {{ old('placement', $banner->placement) === 'promo_card' ? 'selected' : '' }}>Promotional Grid Card</option>
                        </select>
                        @error('placement')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Sort Order -->
                    <div class="col-md-6">
                        <label for="sort_order" class="form-label fw-bold">Sort Order</label>
                        <input type="number" min="0" name="sort_order" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $banner->sort_order) }}">
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Current & New Banner Graphic -->
                    <div class="col-12">
                        <label for="image" class="form-label fw-bold">Banner Graphic</label>
                        @if($banner->image_path)
                            <div class="mb-2">
                                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="img-fluid rounded border mb-2" style="max-height: 180px;">
                                <small class="text-muted d-block">Current banner graphic. Uploading a new file will safely replace this image.</small>
                            </div>
                        @endif
                        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">Leave empty to keep existing image. JPG, PNG, WEBP (Max 3MB).</small>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Button Text -->
                    <div class="col-md-6">
                        <label for="button_text" class="form-label fw-bold">Button CTA Label</label>
                        <input type="text" name="button_text" id="button_text" class="form-control @error('button_text') is-invalid @enderror" value="{{ old('button_text', $banner->button_text) }}">
                        @error('button_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Button URL -->
                    <div class="col-md-6">
                        <label for="button_url" class="form-label fw-bold">Button Destination URL</label>
                        <input type="text" name="button_url" id="button_url" class="form-control @error('button_url') is-invalid @enderror" value="{{ old('button_url', $banner->button_url) }}">
                        @error('button_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Active Toggle -->
                    <div class="col-12">
                        <div class="border rounded p-3 bg-light">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $banner->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Active (Display on storefront)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Update Banner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
