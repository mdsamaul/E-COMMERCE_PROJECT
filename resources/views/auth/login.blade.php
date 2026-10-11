@extends('layouts.auth')

@section('title', 'Customer Sign In')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold text-dark mb-1">Customer Sign In</h3>
    <p class="text-muted small">Log in with your registered email or Bangladeshi mobile number.</p>
</div>

<form method="POST" action="{{ route('login') }}" novalidate>
    @csrf

    <div class="mb-3">
        <label for="login" class="form-label fw-semibold">Email or Mobile Number</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
            <input type="text" 
                   id="login" 
                   name="login" 
                   class="form-control @error('login') is-invalid @enderror" 
                   placeholder="e.g. 01712000000 or you@example.com" 
                   value="{{ old('login') }}" 
                   required 
                   autofocus>
        </div>
        @error('login')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label fw-semibold mb-0">Password</label>
            <a href="{{ route('password.request') }}" class="small text-decoration-none" style="color: var(--shopbd-primary);">Forgot password?</a>
        </div>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
            <input type="password" 
                   id="password" 
                   name="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   placeholder="Enter your account password" 
                   required>
        </div>
        @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
        <label class="form-check-label text-muted small" for="remember">Remember me on this browser</label>
    </div>

    <button type="submit" class="btn btn-brand w-100 py-2 fw-semibold">
        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
    </button>

    <div class="text-center mt-4 pt-3 border-top">
        <p class="text-muted small mb-0">Don't have an account yet? 
            <a href="{{ route('register') }}" class="fw-semibold text-decoration-none" style="color: var(--shopbd-primary);">Register Now</a>
        </p>
    </div>
</form>
@endsection
