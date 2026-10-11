@extends('layouts.auth')

@section('title', 'Set New Password')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold text-dark mb-1">Set New Password</h3>
    <p class="text-muted small">Choose a secure password for your ShopBD account.</p>
</div>

<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="mb-3">
        <label for="email" class="form-label fw-semibold">Email Address</label>
        <input type="email" 
               id="email" 
               name="email" 
               class="form-control @error('email') is-invalid @enderror" 
               value="{{ $email ?? old('email') }}" 
               required 
               autofocus>
        @error('email')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label fw-semibold">New Password</label>
        <input type="password" 
               id="password" 
               name="password" 
               class="form-control @error('password') is-invalid @enderror" 
               placeholder="Minimum 8 characters" 
               required>
        @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label fw-semibold">Confirm New Password</label>
        <input type="password" 
               id="password_confirmation" 
               name="password_confirmation" 
               class="form-control" 
               placeholder="Re-type new password" 
               required>
    </div>

    <button type="submit" class="btn btn-brand w-100 py-2 fw-semibold">
        <i class="bi bi-shield-check me-1"></i> Reset Password
    </button>
</form>
@endsection
