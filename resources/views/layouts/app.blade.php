<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Dynamic SEO Meta Tags -->
    <title>@yield('title', config('app.name', 'ShopBD')) — {{ \App\Models\Setting::get('store_tagline', 'Best Online Shopping Experience in Bangladesh') }}</title>
    <meta name="description" content="@yield('meta_description', 'ShopBD is Bangladesh\'s leading online store for authentic clothing, electronics, home appliances, groceries, and daily essentials with fast Cash on Delivery.')">
    <meta name="keywords" content="@yield('meta_keywords', 'ShopBD, online shopping bangladesh, buy panjabi online, electronics dhaka, cash on delivery bangladesh')">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Social Sharing -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'ShopBD — Online Shopping in Bangladesh')">
    <meta property="og:description" content="@yield('meta_description', 'Reliable online shopping with nationwide Cash on Delivery across Bangladesh.')">
    <meta property="og:image" content="@yield('meta_image', asset('images/og-shopbd.jpg'))">
    <meta property="og:url" content="{{ url()->current() }}">

    <!-- Bootstrap 5 & Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --shop-primary: #0f766e;
            --shop-primary-hover: #115e59;
            --shop-accent: #f59e0b;
            --shop-dark: #0f172a;
            --shop-light: #f8fafc;
        }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: #334155;
            background-color: #f8fafc;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .bg-shop-primary {
            background-color: var(--shop-primary) !important;
        }
        .btn-shop-primary {
            background-color: var(--shop-primary);
            border-color: var(--shop-primary);
            color: #ffffff;
            font-weight: 600;
        }
        .btn-shop-primary:hover {
            background-color: var(--shop-primary-hover);
            border-color: var(--shop-primary-hover);
            color: #ffffff;
        }
        .btn-outline-shop {
            color: var(--shop-primary);
            border-color: var(--shop-primary);
            font-weight: 600;
        }
        .btn-outline-shop:hover {
            background-color: var(--shop-primary);
            color: #ffffff;
        }
        .text-shop-primary {
            color: var(--shop-primary) !important;
        }
        .top-announcement-bar {
            background-color: #042f2e;
            color: #99f6e4;
            font-size: 0.825rem;
            padding: 0.4rem 0;
        }
        .main-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 0;
        }
        .store-logo {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--shop-dark);
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        .category-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.925rem;
        }
        .nav-link-custom {
            color: #475569;
            font-weight: 600;
            padding: 0.75rem 1rem;
            text-decoration: none;
            display: inline-block;
            transition: color 0.15s;
        }
        .nav-link-custom:hover, .nav-link-custom.active {
            color: var(--shop-primary);
        }
        .product-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
        }
        .product-thumbnail-container {
            position: relative;
            background-color: #f1f5f9;
            padding-top: 100%;
            overflow: hidden;
        }
        .product-thumbnail-container img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .product-card:hover .product-thumbnail-container img {
            transform: scale(1.05);
        }
        .badge-discount {
            position: absolute;
            top: 0.75rem;
            left: 0.75rem;
            background-color: #ef4444;
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            z-index: 2;
        }
        .site-footer {
            background-color: var(--shop-dark);
            color: #cbd5e1;
            margin-top: auto;
            padding-top: 4rem;
            padding-bottom: 2rem;
        }
        .footer-heading {
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
        }
        .footer-links {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }
        .footer-links li {
            margin-bottom: 0.6rem;
        }
        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.15s;
        }
        .footer-links a:hover {
            color: #ffffff;
        }
        .payment-badge {
            background: #ffffff;
            border-radius: 0.375rem;
            padding: 0.35rem 0.6rem;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
            color: #0f172a;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Top Announcement Bar -->
    <header class="top-announcement-bar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <span><i class="bi bi-telephone-fill me-1 text-warning"></i> Helpline: {{ \App\Models\Setting::get('store_phone', '01700-000000') }}</span>
                    <span class="d-none d-md-inline text-secondary">|</span>
                    <span class="d-none d-md-inline"><i class="bi bi-envelope-fill me-1 text-warning"></i> {{ \App\Models\Setting::get('store_email', 'support@shopbd.com') }}</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="d-none d-lg-inline"><i class="bi bi-truck me-1"></i> Cash on Delivery Available Nationwide</span>
                    <span class="badge bg-warning text-dark font-monospace fw-bold">৳ BDT</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Header -->
    <div class="main-header sticky-top shadow-sm">
        <div class="container">
            <div class="row align-items-center g-3">
                <!-- Mobile Hamburger + Brand Logo -->
                <div class="col-6 col-md-3 d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <a href="{{ route('home') }}" class="store-logo">
                        <i class="bi bi-bag-check-fill text-shop-primary me-2 fs-3"></i>
                        <span>Shop<span class="text-shop-primary">BD</span></span>
                    </a>
                </div>

                <!-- Global Search Bar -->
                <div class="col-12 col-md-6 order-3 order-md-2">
                    <form method="GET" action="{{ route('products.index') }}" class="input-group">
                        <input type="text" name="q" class="form-control" placeholder="Search for clothing, electronics, groceries..." value="{{ request('q') }}" aria-label="Search products" required>
                        <button class="btn btn-shop-primary px-4" type="submit">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </form>
                </div>

                <!-- Header Action Buttons (Account, Wishlist, Cart) -->
                <div class="col-6 col-md-3 order-2 order-md-3 text-end">
                    <div class="d-flex justify-content-end align-items-center gap-2">
                        @auth
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-warning text-dark fw-bold me-1">
                                    <i class="bi bi-shield-lock me-1"></i> Admin
                                </a>
                            @endif
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle text-shop-primary me-1"></i> {{ Str::limit(auth()->user()->name, 12) }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><a class="dropdown-item" href="{{ route('customer.profile') }}"><i class="bi bi-person me-2"></i> My Account</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-shop">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                            </a>
                            <a href="{{ route('register') }}" class="btn btn-sm btn-shop-primary d-none d-sm-inline-block">
                                Register
                            </a>
                        @endauth

                        <!-- Cart Indicator -->
                        <a href="{{ route('cart.index') }}" class="btn btn-sm btn-light border position-relative" title="Shopping Cart">
                            <i class="bi bi-cart3 fs-6"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartCountBadge">
                                {{ $headerCartCount ?? 0 }}
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Navbar (Desktop) -->
    <nav class="category-navbar d-none d-lg-block">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <!-- All Categories Dropdown -->
                <div class="dropdown me-2">
                    <button class="btn btn-shop-primary dropdown-toggle py-2 px-3 fw-bold rounded-0 rounded-top" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-grid me-2"></i> All Categories
                    </button>
                    <ul class="dropdown-menu shadow-sm py-2" style="min-width: 260px;">
                        @foreach(\App\Models\Category::active()->root()->orderBy('sort_order')->take(10)->get() as $cat)
                            <li>
                                <a class="dropdown-item py-2 fw-semibold d-flex justify-content-between align-items-center" href="{{ route('products.category', $cat->slug) }}">
                                    <span>{{ $cat->name }}</span>
                                    <i class="bi bi-chevron-right text-muted small"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <a href="{{ route('home') }}" class="nav-link-custom {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('products.index') }}" class="nav-link-custom {{ request()->routeIs('products.index') && !request('sort') ? 'active' : '' }}">Shop All</a>
                <a href="{{ route('products.index', ['sort' => 'popular']) }}" class="nav-link-custom">Flash Deals</a>
                <a href="{{ route('pages.about') }}" class="nav-link-custom {{ request()->routeIs('pages.about') ? 'active' : '' }}">About Us</a>
                <a href="{{ route('pages.contact') }}" class="nav-link-custom {{ request()->routeIs('pages.contact') ? 'active' : '' }}">Contact</a>
            </div>

            <div class="d-flex align-items-center text-muted small">
                <i class="bi bi-geo-alt me-1 text-shop-primary"></i>
                <span>Fast delivery across all 64 districts</span>
            </div>
        </div>
    </nav>

    <!-- Mobile Offcanvas Drawer -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold text-shop-primary">
                <i class="bi bi-bag-check-fill me-2"></i>ShopBD
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div class="p-3 bg-light border-bottom">
                <form method="GET" action="{{ route('products.index') }}">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control" placeholder="Search products..." required>
                        <button class="btn btn-shop-primary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
            </div>

            <div class="list-group list-group-flush">
                <a href="{{ route('home') }}" class="list-group-item list-group-item-action fw-bold"><i class="bi bi-house me-2"></i> Home</a>
                <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action fw-bold"><i class="bi bi-box-seam me-2"></i> All Products</a>
                <a href="{{ route('cart.index') }}" class="list-group-item list-group-item-action fw-bold text-shop-primary">
                    <i class="bi bi-cart3 me-2"></i> Shopping Cart
                    <span class="badge bg-danger rounded-pill float-end">{{ $headerCartCount ?? 0 }}</span>
                </a>

                <div class="p-2 px-3 text-uppercase text-muted small fw-bold mt-2">Categories</div>
                @foreach(\App\Models\Category::active()->root()->orderBy('sort_order')->take(8)->get() as $cat)
                    <a href="{{ route('products.category', $cat->slug) }}" class="list-group-item list-group-item-action ps-4">
                        {{ $cat->name }}
                    </a>
                @endforeach

                <div class="p-2 px-3 text-uppercase text-muted small fw-bold mt-2">Help & Legal</div>
                <a href="{{ route('pages.about') }}" class="list-group-item list-group-item-action"><i class="bi bi-info-circle me-2"></i> About Us</a>
                <a href="{{ route('pages.contact') }}" class="list-group-item list-group-item-action"><i class="bi bi-envelope me-2"></i> Contact Us</a>
                <a href="{{ route('pages.faq') }}" class="list-group-item list-group-item-action"><i class="bi bi-question-circle me-2"></i> FAQ</a>
                <a href="{{ route('pages.shipping') }}" class="list-group-item list-group-item-action"><i class="bi bi-truck me-2"></i> Shipping Policy</a>
                <a href="{{ route('pages.return') }}" class="list-group-item list-group-item-action"><i class="bi bi-arrow-return-left me-2"></i> Return & Refund</a>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <main class="flex-grow-1">
        @if(session('success'))
            <div class="container mt-3">
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container mt-3">
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-octagon-fill me-2 fs-5 align-middle"></i>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="container mt-3">
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5 align-middle"></i>
                    <span>{{ session('warning') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="container mt-3">
                <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-info-circle-fill me-2 fs-5 align-middle"></i>
                    <span>{{ session('info') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="row g-4 mb-5">
                <!-- Col 1: Store Bio -->
                <div class="col-lg-4 col-md-6">
                    <h5 class="fw-bold text-white mb-3">
                        <i class="bi bi-bag-check-fill text-warning me-2"></i>ShopBD
                    </h5>
                    <p class="text-secondary small mb-3">
                        {{ \App\Models\Setting::get('store_tagline', 'Best Online Shopping Experience in Bangladesh') }}.
                        We bring authentic products to your doorstep with guaranteed fast delivery and safe Cash on Delivery.
                    </p>
                    <div class="small text-secondary">
                        <div class="mb-1"><i class="bi bi-geo-alt-fill text-warning me-2"></i>{{ \App\Models\Setting::get('store_address', 'Motijheel, Dhaka-1000, Bangladesh') }}</div>
                        <div class="mb-1"><i class="bi bi-telephone-fill text-warning me-2"></i>{{ \App\Models\Setting::get('store_phone', '01700-000000') }}</div>
                        <div><i class="bi bi-envelope-fill text-warning me-2"></i>{{ \App\Models\Setting::get('store_email', 'support@shopbd.com') }}</div>
                    </div>
                </div>

                <!-- Col 2: Categories -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="footer-heading">Top Categories</h6>
                    <ul class="footer-links">
                        @foreach(\App\Models\Category::active()->featured()->take(5)->get() as $cat)
                            <li><a href="{{ route('products.category', $cat->slug) }}">{{ $cat->name }}</a></li>
                        @endforeach
                        <li><a href="{{ route('products.index') }}">View All &rarr;</a></li>
                    </ul>
                </div>

                <!-- Col 3: Customer Care & Policies -->
                <div class="col-lg-3 col-md-6 col-6">
                    <h6 class="footer-heading">Customer Care</h6>
                    <ul class="footer-links">
                        <li><a href="{{ route('pages.faq') }}">Frequently Asked Questions</a></li>
                        <li><a href="{{ route('pages.shipping') }}">Shipping & Delivery</a></li>
                        <li><a href="{{ route('pages.return') }}">Returns & Refund Policy</a></li>
                        <li><a href="{{ route('pages.privacy') }}">Privacy Policy</a></li>
                        <li><a href="{{ route('pages.terms') }}">Terms & Conditions</a></li>
                        <li><a href="{{ route('pages.contact') }}">Contact Support</a></li>
                    </ul>
                </div>

                <!-- Col 4: Newsletter & Trust -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="footer-heading">Stay Connected</h6>
                    <p class="text-secondary small mb-3">Subscribe to get seasonal promotional discounts and new arrival alerts.</p>
                    <form onsubmit="event.preventDefault(); alert('Thank you for subscribing to ShopBD newsletter!');" class="mb-3">
                        <div class="input-group">
                            <input type="email" class="form-control form-control-sm" placeholder="Your email address" required>
                            <button class="btn btn-sm btn-shop-primary" type="submit">Join</button>
                        </div>
                    </form>
                    <div class="d-flex gap-2">
                        <span class="payment-badge">bKash</span>
                        <span class="payment-badge">Nagad</span>
                        <span class="payment-badge">Rocket</span>
                        <span class="payment-badge">Visa</span>
                        <span class="payment-badge">COD</span>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="border-top border-secondary pt-3 text-center text-secondary small">
                <p class="mb-0">&copy; {{ date('Y') }} <strong>ShopBD</strong>. All Rights Reserved. Built with pride for Bangladesh.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
