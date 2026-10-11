<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') — ShopBD</title>
    <!-- Bootstrap 5 & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --admin-sidebar-bg: #0f172a;
            --admin-sidebar-hover: #1e293b;
            --admin-sidebar-active: #0ea5e9;
            --admin-accent: #0f766e;
            --admin-bg: #f8fafc;
        }
        body {
            background-color: var(--admin-bg);
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            color: #334155;
        }
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        .admin-sidebar {
            width: 260px;
            background-color: var(--admin-sidebar-bg);
            color: #cbd5e1;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            border-right: 1px solid #1e293b;
        }
        .admin-sidebar-brand {
            padding: 1.25rem 1.5rem;
            font-size: 1.25rem;
            font-weight: 800;
            color: #ffffff;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #1e293b;
            text-decoration: none;
        }
        .admin-nav {
            padding: 1rem 0.75rem;
            flex-grow: 1;
        }
        .admin-nav-heading {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: #64748b;
            padding: 0.75rem 0.75rem 0.25rem;
        }
        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.85rem;
            color: #94a3b8;
            border-radius: 0.5rem;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.15s ease-in-out;
            margin-bottom: 0.15rem;
        }
        .admin-nav-link:hover {
            color: #ffffff;
            background-color: var(--admin-sidebar-hover);
        }
        .admin-nav-link.active {
            color: #ffffff;
            background-color: #0284c7;
            font-weight: 600;
        }
        .admin-nav-link i {
            font-size: 1.1rem;
        }
        .admin-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .admin-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .admin-body {
            padding: 2rem;
            flex-grow: 1;
        }
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.85rem;
            padding: 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07);
        }
        .table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.85rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            overflow: hidden;
        }
        .table-card .card-header {
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            padding: 1rem 1.5rem;
        }
        .table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.85rem 1rem;
        }
        .table tbody td {
            padding: 0.85rem 1rem;
            vertical-align: middle;
            font-size: 0.9rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .badge-subtle {
            font-weight: 600;
            padding: 0.35em 0.65em;
            border-radius: 0.375rem;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand">
                <i class="bi bi-shield-shaded text-warning me-2 fs-4"></i>
                <span>ShopBD <span class="badge bg-warning text-dark ms-1 font-monospace fs-6">ADMIN</span></span>
            </a>

            <div class="admin-nav">
                @if(auth()->user()->isSuperAdmin())
                    <div class="mb-3 px-2">
                        <a href="{{ route('superadmin.dashboard') }}" class="btn btn-warning text-dark btn-sm w-100 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-shield-lock-fill"></i> Super Admin Portal
                        </a>
                    </div>
                @endif

                <div class="admin-nav-heading">Main Overview</div>
                <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                @if(auth()->user()->hasPermission('orders.view'))
                    <a href="{{ route('admin.orders.index') }}" class="admin-nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <i class="bi bi-bag-check"></i> Orders & Sales
                    </a>
                @endif

                <div class="admin-nav-heading">Catalog & Stock</div>
                @if(auth()->user()->hasPermission('products.view'))
                    <a href="{{ route('admin.products.index') }}" class="admin-nav-link {{ request()->routeIs('admin.products.*') && !request()->routeIs('admin.products.stock*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam"></i> Products
                    </a>
                @endif
                @if(auth()->user()->hasPermission('products.stock'))
                    <a href="{{ route('admin.products.stock') }}" class="admin-nav-link {{ request()->routeIs('admin.products.stock*') ? 'active' : '' }}">
                        <i class="bi bi-boxes"></i> Inventory & Stock
                    </a>
                @endif
                @if(auth()->user()->hasPermission('categories.manage'))
                    <a href="{{ route('admin.categories.index') }}" class="admin-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i class="bi bi-grid-3x3-gap"></i> Categories
                    </a>
                @endif
                @if(auth()->user()->hasPermission('brands.manage'))
                    <a href="{{ route('admin.brands.index') }}" class="admin-nav-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">
                        <i class="bi bi-tag"></i> Brands
                    </a>
                @endif

                <div class="admin-nav-heading">Marketing & Content</div>
                @if(auth()->user()->hasPermission('coupons.manage'))
                    <a href="{{ route('admin.coupons.index') }}" class="admin-nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                        <i class="bi bi-ticket-perforated"></i> Coupons
                    </a>
                @endif
                @if(auth()->user()->hasPermission('banners.manage'))
                    <a href="{{ route('admin.banners.index') }}" class="admin-nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                        <i class="bi bi-images"></i> Banners
                    </a>
                @endif
                @if(auth()->user()->hasPermission('reviews.manage'))
                    <a href="{{ route('admin.reviews.index') }}" class="admin-nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                        <i class="bi bi-chat-square-quote"></i> Reviews
                    </a>
                @endif

                <div class="admin-nav-heading">Users & Reports</div>
                @if(auth()->user()->hasPermission('customers.view'))
                    <a href="{{ route('admin.customers.index') }}" class="admin-nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> Customers
                    </a>
                @endif
                @if(auth()->user()->hasPermission('reports.sales'))
                    <a href="{{ route('admin.reports.sales') }}" class="admin-nav-link {{ request()->routeIs('admin.reports.sales') ? 'active' : '' }}">
                        <i class="bi bi-graph-up-arrow"></i> Sales Reports
                    </a>
                @endif

                <div class="admin-nav-heading">Account & System</div>
                <a href="{{ route('admin.profile') }}" class="admin-nav-link {{ request()->routeIs('admin.profile*') ? 'active' : '' }}">
                    <i class="bi bi-person-gear"></i> My Profile
                </a>
                @if(auth()->user()->hasPermission('settings.manage'))
                    <a href="{{ route('admin.settings.index') }}" class="admin-nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                @endif
            </div>

            <div class="p-3 border-top border-secondary">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="text-truncate me-2">
                        <small class="d-block text-secondary">Logged in as</small>
                        <span class="small fw-semibold text-light text-truncate d-block">{{ auth()->user()->name }}</span>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Sign Out">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="admin-content">
            <!-- Topbar -->
            <header class="admin-header">
                <div>
                    <h5 class="mb-0 fw-bold text-dark">@yield('page_title', 'Dashboard')</h5>
                    @hasSection('page_subtitle')
                        <small class="text-muted">@yield('page_subtitle')</small>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-3">
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('superadmin.dashboard') }}" class="btn btn-sm btn-warning text-dark fw-bold">
                            <i class="bi bi-shield-lock-fill me-1"></i> Super Admin Center
                        </a>
                    @endif
                    <a href="{{ route('home') }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-box-arrow-up-right me-1"></i> View Storefront
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1 text-primary"></i> {{ auth()->user()->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->email }}</span></li>
                            <li><a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="bi bi-person-gear me-2"></i> My Profile</a></li>
                            @if(auth()->user()->isSuperAdmin())
                                <li><a class="dropdown-item text-warning-emphasis" href="{{ route('superadmin.dashboard') }}"><i class="bi bi-shield-shaded me-2"></i> Super Admin Center</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Log Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Body Area -->
            <main class="admin-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i>
                        <span>{{ session('success') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="bi bi-exclamation-octagon-fill me-2 fs-5 align-middle"></i>
                        <span>{{ session('error') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Please fix the following errors:</div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
