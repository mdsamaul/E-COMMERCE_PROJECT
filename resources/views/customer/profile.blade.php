<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account — ShopBD</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --shopbd-primary: #0f766e;
            --shopbd-primary-hover: #115e59;
            --shopbd-accent: #f97316;
            --shopbd-bg: #f8fafc;
        }
        body {
            background-color: var(--shopbd-bg);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        .btn-brand {
            background-color: var(--shopbd-primary);
            color: #ffffff;
            border-color: var(--shopbd-primary);
        }
        .btn-brand:hover {
            background-color: var(--shopbd-primary-hover);
            color: #ffffff;
            border-color: var(--shopbd-primary-hover);
        }
    </style>
</head>
<body>
    <header class="bg-white border-bottom py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="/" class="fw-bold text-decoration-none fs-4" style="color: var(--shopbd-primary);">
                <i class="bi bi-bag-check-fill me-1"></i>Shop<span style="color: var(--shopbd-accent);">BD</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small">Signed in as <strong>{{ $user->name }}</strong> ({{ $user->phone }})</span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-box-arrow-right me-1"></i> Log Out
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="py-5">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row g-4">
                <!-- 1. Personal Information -->
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-person-circle me-2 text-primary"></i>Personal Information</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" action="{{ route('customer.profile.update') }}">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Full Name</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Mobile Number</label>
                                    <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" required>
                                    <div class="form-text small">Used for SMS order tracking and courier dispatch.</div>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Email Address</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-brand">Save Profile Changes</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 2. Change Password -->
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-lock me-2 text-primary"></i>Change Password</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" action="{{ route('customer.password.update') }}">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Current Password</label>
                                    <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">New Password</label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Min 8 characters" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" class="form-control" placeholder="Re-type new password" required>
                                </div>

                                <button type="submit" class="btn btn-brand">Update Password</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 3. Saved Delivery Addresses & Dedicated Full-Page Add Address Form -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-geo-alt me-2 text-primary"></i>Delivery Address Book</h5>
                            <span class="badge bg-secondary">{{ $user->addresses->count() }} Addresses</span>
                        </div>
                        <div class="card-body p-4">
                            <!-- Existing Addresses -->
                            @if($user->addresses->isNotEmpty())
                                <div class="row g-3 mb-4">
                                    @foreach($user->addresses as $address)
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="border rounded-3 p-3 h-100 position-relative bg-light">
                                                @if($address->is_default)
                                                    <span class="badge bg-success position-absolute top-0 end-0 m-3">Default</span>
                                                @endif
                                                <h6 class="fw-bold mb-1">{{ $address->recipient_name }}</h6>
                                                <div class="small text-muted mb-2"><i class="bi bi-telephone me-1"></i>{{ $address->phone }}</div>
                                                <p class="small text-dark mb-2">{{ $address->full_address }}</p>
                                                @if($address->delivery_notes)
                                                    <p class="small text-muted mb-3 fst-italic">Note: {{ $address->delivery_notes }}</p>
                                                @endif
                                                <form method="POST" action="{{ route('customer.address.delete', $address) }}" onsubmit="return confirm('Remove this address?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-light border mb-4 text-center py-4">
                                    <i class="bi bi-geo-alt fs-2 d-block text-muted mb-2"></i>
                                    No saved addresses found. Add your primary Bangladesh delivery address below.
                                </div>
                            @endif

                            <!-- Dedicated Full-Page Form: Add Delivery Address (No Modals) -->
                            <div class="p-4 border rounded-3 bg-white mt-3">
                                <h5 class="fw-bold text-dark mb-3">
                                    <i class="bi bi-plus-circle-fill text-success me-2"></i>Add New Delivery Address
                                </h5>
                                <form method="POST" action="{{ route('customer.address.store') }}">
                                    @csrf

                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-semibold">Recipient Name</label>
                                            <input type="text" name="recipient_name" class="form-control @error('recipient_name') is-invalid @enderror" placeholder="Full name" value="{{ old('recipient_name') }}" required>
                                            @error('recipient_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-semibold">Recipient Mobile Number</label>
                                            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="017XXXXXXXX" value="{{ old('phone') }}" required>
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold">Division</label>
                                            <select name="division" class="form-select @error('division') is-invalid @enderror" required>
                                                <option value="">Select Division</option>
                                                <option value="Dhaka" {{ old('division') == 'Dhaka' ? 'selected' : '' }}>Dhaka</option>
                                                <option value="Chittagong" {{ old('division') == 'Chittagong' ? 'selected' : '' }}>Chittagong</option>
                                                <option value="Rajshahi" {{ old('division') == 'Rajshahi' ? 'selected' : '' }}>Rajshahi</option>
                                                <option value="Khulna" {{ old('division') == 'Khulna' ? 'selected' : '' }}>Khulna</option>
                                                <option value="Barisal" {{ old('division') == 'Barisal' ? 'selected' : '' }}>Barisal</option>
                                                <option value="Sylhet" {{ old('division') == 'Sylhet' ? 'selected' : '' }}>Sylhet</option>
                                                <option value="Rangpur" {{ old('division') == 'Rangpur' ? 'selected' : '' }}>Rangpur</option>
                                                <option value="Mymensingh" {{ old('division') == 'Mymensingh' ? 'selected' : '' }}>Mymensingh</option>
                                            </select>
                                            @error('division')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold">District</label>
                                            <input type="text" name="district" class="form-control @error('district') is-invalid @enderror" placeholder="e.g. Dhaka, Gazipur, Bogura" value="{{ old('district') }}" required>
                                            @error('district')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold">Thana / Upazila</label>
                                            <input type="text" name="upazila_thana" class="form-control @error('upazila_thana') is-invalid @enderror" placeholder="e.g. Dhanmondi, Mirpur" value="{{ old('upazila_thana') }}">
                                            @error('upazila_thana')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Detailed Street Address</label>
                                            <textarea name="address_line" class="form-control @error('address_line') is-invalid @enderror" rows="2" placeholder="House number, road number, flat/floor, sector/village" required>{{ old('address_line') }}</textarea>
                                            @error('address_line')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Delivery Instructions (Optional)</label>
                                            <input type="text" name="delivery_notes" class="form-control @error('delivery_notes') is-invalid @enderror" placeholder="e.g. Please leave with security guard, call upon arrival" value="{{ old('delivery_notes') }}">
                                            @error('delivery_notes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <div class="form-check">
                                                <input type="checkbox" name="is_default" value="1" class="form-check-input" id="is_default" {{ old('is_default') ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold text-muted small" for="is_default">Set as default delivery address</label>
                                            </div>
                                        </div>

                                        <div class="col-12 mt-3">
                                            <button type="submit" class="btn btn-brand">
                                                <i class="bi bi-check-lg me-1"></i> Save Address
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
