@extends('layouts.auth')

@section('title', 'Admin Portal Login')

@section('content')
<div class="mb-4 text-center">
    <div class="badge bg-danger-subtle text-danger px-3 py-2 mb-2 rounded-pill fw-semibold">
        <i class="bi bi-shield-lock-fill me-1"></i> Admin Portal
    </div>
    <h3 class="fw-bold text-dark mb-1">Store Owner Access</h3>
    <p class="text-muted small">Sign in to manage catalog, orders, and store settings.</p>
</div>

<form method="POST" action="{{ route('admin.login.submit') }}" novalidate>
    @csrf

    <div class="mb-3">
        <label for="login" class="form-label fw-semibold">Administrator Email or Mobile</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-person"></i></span>
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
        <label for="password" class="form-label fw-semibold">Admin Password</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted"><i class="bi bi-key-fill"></i></span>
            <input type="password" 
                   id="password" 
                   name="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   placeholder="Enter admin password" 
                   required>
        </div>
        @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
        <label class="form-check-label text-muted small" for="remember">Keep admin session active</label>
    </div>

    <button type="submit" class="btn btn-dark w-100 py-2 fw-semibold">
        <i class="bi bi-box-arrow-in-right me-1"></i> Authenticate Administrator
    </button>
</form>
@endsection
