@extends('layouts.auth')

@section('title', 'Super Admin Login')

@section('content')
<div class="mb-4 text-center">
    <div class="badge bg-warning text-dark px-3 py-2 mb-2 rounded-pill fw-bold" style="letter-spacing: 0.05em;">
        <i class="bi bi-shield-lock-fill me-1"></i> SUPER ADMINISTRATOR
    </div>
    <h3 class="fw-bold text-dark mb-1">Control Center Access</h3>
    <p class="text-muted small">Elevated credentials required. All authentication attempts are audited.</p>
</div>

<form method="POST" action="{{ route('superadmin.login.submit') }}" novalidate>
    @csrf

    <div class="mb-3">
        <label for="login" class="form-label fw-semibold">Super Admin Email or Mobile</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-shaded"></i></span>
            <input type="text" 
                   id="login" 
                   name="login" 
                   class="form-control @error('login') is-invalid @enderror" 
                   placeholder="admin@shopbd.com or 017XXXXXXXX" 
                   value="{{ old('login') }}" 
                   required 
                   autofocus>
        </div>
        @error('login')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label fw-semibold">Super Admin Password</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted"><i class="bi bi-key-fill"></i></span>
            <input type="password" 
                   id="password" 
                   name="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   placeholder="Enter master password" 
                   required>
        </div>
        @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label text-muted small" for="remember">Keep session active</label>
        </div>
        <a href="{{ route('admin.login') }}" class="small text-decoration-none text-muted">
            <i class="bi bi-arrow-left"></i> Regular Admin Login
        </a>
    </div>

    <button type="submit" class="btn btn-warning w-100 py-2 fw-bold text-dark shadow-sm">
        <i class="bi bi-shield-check me-1"></i> Authenticate Super Admin
    </button>
</form>
@endsection
