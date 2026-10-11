@extends('layouts.admin')

@section('title', 'Administrator Profile')
@section('page_title', 'My Profile')
@section('page_subtitle', 'Manage your personal administrative contact details and credentials.')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="row g-4">
            <!-- Profile Info & Roles -->
            <div class="col-12 col-lg-5">
                <div class="table-card p-4 mb-4">
                    <div class="text-center mb-4">
                        <div class="avatar bg-primary text-white rounded-circle p-3 mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                            <i class="bi bi-person-badge fs-2"></i>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark">{{ $user->name }}</h5>
                        <small class="text-muted">{{ $user->email }}</small>
                        <div class="mt-2">
                            @if($user->isSuperAdmin())
                                <span class="badge bg-warning text-dark font-monospace fw-bold">Super Admin</span>
                            @else
                                @foreach($user->roles as $role)
                                    <span class="badge bg-secondary font-monospace">{{ $role->name }}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Effective Privileges</h6>
                    @if($user->isSuperAdmin())
                        <div class="alert alert-warning small border mb-0">
                            <i class="bi bi-shield-lock-fill me-1"></i> Full system privileges active.
                        </div>
                    @else
                        <div class="small text-muted mb-2">Granted module permissions:</div>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($user->getAllPermissions() as $perm)
                                <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.72rem;">{{ $perm->slug }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Edit Details Form -->
            <div class="col-12 col-lg-7">
                <div class="table-card p-4">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-person-gear text-primary me-2"></i>Update Account Credentials
                    </h6>

                    <form method="POST" action="{{ route('admin.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="phone" class="form-label fw-bold">Mobile Number <span class="text-danger">*</span></label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="017XXXXXXXX" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-bold text-dark mb-3">Change Password (Optional)</h6>

                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-bold">Current Password</label>
                            <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Required if changing password">
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label for="password" class="form-label fw-bold">New Password</label>
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Min. 8 characters">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="password_confirmation" class="form-label fw-bold">Confirm New Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Re-type new password">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                            <i class="bi bi-save me-1"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
