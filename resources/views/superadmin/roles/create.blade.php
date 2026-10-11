@extends('layouts.superadmin')

@section('title', 'Create Custom Role')
@section('page_title', 'Create Custom Role')
@section('page_subtitle', 'Define a new administrative role with tailored operational permissions.')

@section('content')
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}" class="text-decoration-none">Control Center</a></li>
        <li class="breadcrumb-item"><a href="{{ route('superadmin.roles.index') }}" class="text-decoration-none">Roles</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create Role</li>
    </ol>
</nav>

<form method="POST" action="{{ route('superadmin.roles.store') }}">
    @csrf

    <div class="row g-4">
        <!-- Role Meta -->
        <div class="col-12 col-lg-5">
            <div class="sa-table-card p-4">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-shield-fill text-warning me-2"></i>Role Details</h5>

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Role Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Warehouse Dispatcher" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label fw-semibold">Role Slug (Optional)</label>
                    <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="e.g. warehouse-dispatcher">
                    <small class="text-muted">Auto-generated from name if omitted.</small>
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Describe the responsibilities of this role...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning text-dark fw-bold px-4 py-2">
                        <i class="bi bi-check2-circle me-1"></i> Save Custom Role
                    </button>
                    <a href="{{ route('superadmin.roles.index') }}" class="btn btn-outline-secondary px-3 py-2">Cancel</a>
                </div>
            </div>
        </div>

        <!-- Permissions Assignment -->
        <div class="col-12 col-lg-7">
            <div class="sa-table-card p-4">
                <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-key-fill text-primary me-2"></i>Permissions Assignment</h5>
                <p class="text-muted small mb-4">Select the specific actions and modules that members with this role are authorized to access.</p>

                <div class="row g-4">
                    @foreach($permissions as $module => $modulePermissions)
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100 bg-light">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                    <span class="fw-bold text-dark">{{ $module }}</span>
                                    <span class="badge bg-secondary font-monospace">{{ count($modulePermissions) }}</span>
                                </div>
                                @foreach($modulePermissions as $p)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $p->id }}" id="perm_{{ $p->id }}" {{ in_array($p->id, old('permissions', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="perm_{{ $p->id }}">
                                            <strong>{{ $p->name }}</strong>
                                            <span class="d-block text-muted" style="font-size: 0.75rem;">{{ $p->description }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
