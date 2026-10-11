@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold text-dark mb-1">Reset Password</h3>
    <p class="text-muted small">Enter your email address and we will send you a password reset link.</p>
</div>

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="mb-4">
        <label for="email" class="form-label fw-semibold">Email Address</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
            <input type="email" 
                   id="email" 
                   name="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   value="{{ old('email') }}" 
                   placeholder="you@example.com" 
                   required 
                   autofocus>
        </div>
        @error('email')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-brand w-100 py-2 fw-semibold">
        <i class="bi bi-send me-1"></i> Send Password Reset Link
    </button>

    <div class="text-center mt-4 pt-3 border-top">
        <a href="{{ route('login') }}" class="small text-decoration-none" style="color: var(--shopbd-primary);">
            <i class="bi bi-arrow-left me-1"></i> Back to Login
        </a>
    </div>
</form>
@endsection
