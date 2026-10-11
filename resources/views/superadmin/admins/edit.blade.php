@extends('layouts.superadmin')

@section('title', 'Edit Administrator')
@section('page_title', 'Edit Administrator Account')
@section('page_subtitle', 'Update administrator profile credentials, assigned operational role, and permissions.')

@section('content')
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}" class="text-decoration-none">Control Center</a></li>
        <li class="breadcrumb-item"><a href="{{ route('superadmin.admins.index') }}" class="text-decoration-none">Administrators</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit: {{ $admin->name }}</li>
    </ol>
</nav>

<form method="POST" action="{{ route('superadmin.admins.update', $admin->id) }}">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Account Details -->
        <div class="col-12 col-lg-7">
            <div class="sa-table-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-person-lines-fill text-primary me-2"></i>Account Credentials</h5>
                    @if($admin->isSuperAdmin())
                        <span class="badge bg-warning text-dark font-monospace fw-bold">ROOT SUPER ADMIN</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $admin->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $admin->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="phone" class="form-label fw-semibold">Bangladesh Mobile Number <span class="text-danger">*</span></label>
                        <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $admin->phone) }}" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label for="password" class="form-label fw-semibold">New Password (Optional)</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Leave empty to keep existing">
                        <small class="text-muted">Fill only if resetting the password (min. 8 characters).</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="password_confirmation" class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Re-type new password">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold d-block">Account Status <span class="text-danger">*</span></label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_active" id="status_active" value="1" {{ old('is_active', $admin->is_active ? '1' : '0') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label text-success fw-semibold" for="status_active">
                            <i class="bi bi-check-circle me-1"></i> Active
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="is_active" id="status_inactive" value="0" {{ old('is_active', $admin->is_active ? '1' : '0') === '0' ? 'checked' : '' }}>
                        <label class="form-check-label text-danger fw-semibold" for="status_inactive">
                            <i class="bi bi-slash-circle me-1"></i> Deactivated
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Assignment -->
        <div class="col-12 col-lg-5">
            <div class="sa-table-card p-4 mb-4">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-shield-check text-warning me-2"></i>Assigned Operational Role</h5>

                @if($admin->isSuperAdmin())
                    <div class="alert alert-warning border text-dark small mb-0">
                        <i class="bi bi-shield-lock-fill me-1"></i> <strong>Root Super Administrator:</strong> This account holds unrestricted system privileges and cannot be reassigned to standard operational roles.
                    </div>
                @else
                    <div class="mb-3">
                        <label for="role_id" class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <select name="role_id" id="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                            <option value="">-- Choose an Authorized Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id', $admin->roles->first()?->id) == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }} ({{ $role->slug }})
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning text-dark fw-bold px-4 py-2">
                    <i class="bi bi-save me-1"></i> Save Administrator Changes
                </button>
                <a href="{{ route('superadmin.admins.index') }}" class="btn btn-outline-secondary px-3 py-2">Cancel</a>
            </div>
        </div>

        <!-- Direct Permissions (If not Super Admin) -->
        @if(!$admin->isSuperAdmin())
            <div class="col-12">
                <div class="sa-table-card p-4">
                    <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-key-fill text-info me-2"></i>Direct Permission Overrides</h5>
                    <p class="text-muted small mb-3">Individual permissions explicitly attached to this account (in addition to their role permissions).</p>

                    @php
                        $userDirectPermIds = $admin->directPermissions->pluck('id')->toArray();
                    @endphp

                    <div class="row g-4">
                        @foreach($permissions as $module => $modulePermissions)
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-2 d-flex justify-content-between align-items-center">
                                        <span>{{ $module }}</span>
                                        <span class="badge bg-secondary font-monospace">{{ count($modulePermissions) }}</span>
                                    </h6>
                                    @foreach($modulePermissions as $p)
                                        <div class="form-check mb-1">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $p->id }}" id="perm_{{ $p->id }}" {{ in_array($p->id, old('permissions', $userDirectPermIds)) ? 'checked' : '' }}>
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
        @endif
    </div>
</form>
@endsection
