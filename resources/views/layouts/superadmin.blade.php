<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin Control Center') — ShopBD</title>
    <!-- Bootstrap 5 & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --sa-sidebar-bg: #090d16;
            --sa-sidebar-hover: #131d31;
            --sa-sidebar-active: #d97706;
            --sa-accent: #f59e0b;
            --sa-accent-glow: rgba(245, 158, 11, 0.15);
            --sa-bg: #f8fafc;
        }
        body {
            background-color: var(--sa-bg);
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            color: #1e293b;
        }
        .sa-wrapper {
            display: flex;
            min-height: 100vh;
        }
        .sa-sidebar {
            width: 270px;
            background-color: var(--sa-sidebar-bg);
            color: #94a3b8;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            border-right: 1px solid #1e293b;
        }
        .sa-sidebar-brand {
            padding: 1.35rem 1.5rem;
            font-size: 1.2rem;
            font-weight: 800;
            color: #ffffff;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #1e293b;
            text-decoration: none;
            letter-spacing: -0.02em;
        }
        .sa-badge {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #000;
            font-weight: 800;
            font-size: 0.65rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            letter-spacing: 0.05em;
        }
        .sa-nav {
            padding: 1.25rem 0.85rem;
            flex-grow: 1;
        }
        .sa-nav-heading {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            color: #475569;
            padding: 0.85rem 0.75rem 0.35rem;
        }
        .sa-nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.85rem;
            color: #94a3b8;
            border-radius: 0.5rem;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            transition: all 0.15s ease-in-out;
            margin-bottom: 0.15rem;
        }
        .sa-nav-link:hover {
            color: #ffffff;
            background-color: var(--sa-sidebar-hover);
        }
        .sa-nav-link.active {
            color: #ffffff;
            background-color: rgba(245, 158, 11, 0.15);
            border-left: 3px solid var(--sa-accent);
            font-weight: 600;
        }
        .sa-nav-link i {
            font-size: 1.15rem;
            color: #64748b;
        }
        .sa-nav-link.active i, .sa-nav-link:hover i {
            color: var(--sa-accent);
        }
        .sa-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .sa-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.85rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .sa-body {
            padding: 2rem;
            flex-grow: 1;
        }
        .sa-stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.85rem;
            padding: 1.35rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .sa-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06);
        }
        .sa-table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.85rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            overflow: hidden;
        }
        .sa-table-card .card-header {
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            padding: 1rem 1.5rem;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="sa-wrapper">
        <!-- Super Admin Sidebar -->
        <aside class="sa-sidebar">
            <a href="{{ route('superadmin.dashboard') }}" class="sa-sidebar-brand">
                <i class="bi bi-shield-lock-fill text-warning me-2 fs-4"></i>
                <div class="d-flex flex-column">
                    <span>ShopBD</span>
                    <span class="sa-badge mt-1 align-self-start">SUPER ADMIN</span>
                </div>
            </a>

            <div class="sa-nav">
                <div class="sa-nav-heading">Overview</div>
                <a href="{{ route('superadmin.dashboard') }}" class="sa-nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-cpu"></i> Control Center
                </a>

                <div class="sa-nav-heading">Access & Administration</div>
                <a href="{{ route('superadmin.admins.index') }}" class="sa-nav-link {{ request()->routeIs('superadmin.admins.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> Administrators
                </a>
                <a href="{{ route('superadmin.roles.index') }}" class="sa-nav-link {{ request()->routeIs('superadmin.roles.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check"></i> Roles Management
                </a>
                <a href="{{ route('superadmin.permissions.index') }}" class="sa-nav-link {{ request()->routeIs('superadmin.permissions.*') ? 'active' : '' }}">
                    <i class="bi bi-key-fill"></i> Permission Catalog
                </a>

                <div class="sa-nav-heading">Platform & Operations</div>
                <a href="{{ route('admin.dashboard') }}" class="sa-nav-link" target="_blank">
                    <i class="bi bi-speedometer2"></i> Regular Admin Portal <i class="bi bi-box-arrow-up-right ms-auto small text-muted"></i>
                </a>
                <a href="{{ route('superadmin.settings.index') }}" class="sa-nav-link {{ request()->routeIs('superadmin.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-sliders"></i> Global Settings
                </a>

                <div class="sa-nav-heading">Security & Auditing</div>
                <a href="{{ route('superadmin.activity-logs.index') }}" class="sa-nav-link {{ request()->routeIs('superadmin.activity-logs.*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> Activity & Audit Logs
                </a>
            </div>

            <div class="p-3 border-top border-secondary">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="text-truncate me-2">
                        <small class="d-block text-secondary">Logged in as Super Admin</small>
                        <span class="small fw-semibold text-light text-truncate d-block">{{ auth()->user()->name }}</span>
                    </div>
                    <form method="POST" action="{{ route('superadmin.logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Sign Out">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="sa-content">
            <!-- Topbar -->
            <header class="sa-header">
                <div>
                    <h5 class="mb-0 fw-bold text-dark">@yield('page_title', 'Super Admin Control Center')</h5>
                    @hasSection('page_subtitle')
                        <small class="text-muted">@yield('page_subtitle')</small>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('home') }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-shop me-1"></i> View Storefront
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-speedometer2 me-1"></i> Admin Portal
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-dark dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-shield-fill text-warning"></i> {{ auth()->user()->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->email }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('superadmin.logout') }}">
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
            <main class="sa-body">
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
