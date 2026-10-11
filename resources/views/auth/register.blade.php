@extends('layouts.auth')

@section('title', 'Customer Registration')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold text-dark mb-1">Create Account</h3>
    <p class="text-muted small">Register to track orders, save multiple delivery addresses, and manage your wishlist.</p>
</div>

<form method="POST" action="{{ route('register') }}" novalidate>
    @csrf

    <div class="mb-3">
        <label for="name" class="form-label fw-semibold">Full Name</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
            <input type="text" 
                   id="name" 
                   name="name" 
                   class="form-control @error('name') is-invalid @enderror" 
                   placeholder="e.g. Rahim Ahmed" 
                   value="{{ old('name') }}" 
                   required 
                   autofocus>
        </div>
        @error('name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="phone" class="form-label fw-semibold">Mobile Number</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted">+88</span>
            <input type="tel" 
                   id="phone" 
                   name="phone" 
                   class="form-control @error('phone') is-invalid @enderror" 
                   placeholder="017XXXXXXXX" 
                   value="{{ old('phone') }}" 
                   required>
        </div>
        <div class="form-text small">Used for order confirmations and courier delivery updates.</div>
        @error('phone')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label fw-semibold">Email Address</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
            <input type="email" 
                   id="email" 
                   name="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   placeholder="you@example.com" 
                   value="{{ old('email') }}" 
                   required>
        </div>
        @error('email')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label fw-semibold">Password</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-lock"></i></span>
            <input type="password" 
                   id="password" 
                   name="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   placeholder="Minimum 8 characters" 
                   required>
        </div>
        @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted"><i class="bi bi-check2-circle"></i></span>
            <input type="password" 
                   id="password_confirmation" 
                   name="password_confirmation" 
                   class="form-control" 
                   placeholder="Re-type your password" 
                   required>
        </div>
    </div>

    <button type="submit" class="btn btn-brand w-100 py-2 fw-semibold">
        <i class="bi bi-person-plus me-1"></i> Register Account
    </button>

    <div class="text-center mt-4 pt-3 border-top">
        <p class="text-muted small mb-0">Already registered? 
            <a href="{{ route('login') }}" class="fw-semibold text-decoration-none" style="color: var(--shopbd-primary);">Sign In</a>
        </p>
    </div>
</form>
@endsection
