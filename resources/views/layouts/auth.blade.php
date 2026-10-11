<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Authentication') — ShopBD</title>
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            background: #ffffff;
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
        .brand-logo {
            font-weight: 800;
            color: var(--shopbd-primary);
            text-decoration: none;
            font-size: 1.75rem;
        }
        .brand-logo span {
            color: var(--shopbd-accent);
        }
    </style>
</head>
<body class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="text-center mb-4">
                    <a href="/" class="brand-logo">
                        <i class="bi bi-bag-check-fill me-1"></i>Shop<span>BD</span>
                    </a>
                    <p class="text-muted small mt-1">Single-Vendor Multi-Category Store for Bangladesh</p>
                </div>

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

                @if(session('status'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>{{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="auth-card p-4 p-md-5">
                    @yield('content')
                </div>

                <div class="text-center mt-4 text-muted small">
                    &copy; {{ date('Y') }} ShopBD. Cash on Delivery across all 64 districts in Bangladesh.
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
