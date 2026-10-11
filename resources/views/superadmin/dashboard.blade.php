@extends('layouts.superadmin')

@section('title', 'Super Admin Control Center')
@section('page_title', 'Super Administrator Control Center')
@section('page_subtitle', 'System-wide governance, administrative staff management, and security audit.')

@section('content')
<!-- Quick Action Bar -->
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('superadmin.admins.create') }}" class="btn btn-warning text-dark fw-bold">
            <i class="bi bi-person-plus-fill me-1"></i> Add Administrator
        </a>
        <a href="{{ route('superadmin.roles.create') }}" class="btn btn-dark fw-semibold">
            <i class="bi bi-shield-plus me-1"></i> Create Custom Role
        </a>
        <a href="{{ route('superadmin.settings.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-sliders me-1"></i> Global Settings
        </a>
    </div>
    <div class="text-muted small">
        <i class="bi bi-shield-check text-success me-1"></i> System RBAC Engine Active
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="sa-stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Admins</span>
                <span class="badge bg-primary-subtle text-primary rounded-pill p-2"><i class="bi bi-people-fill fs-6"></i></span>
            </div>
            <h3 class="fw-bold mb-1 text-dark">{{ $stats['total_admins'] }}</h3>
            <small class="text-muted">{{ $stats['total_superadmins'] }} Root Super Admin(s)</small>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="sa-stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Roles & RBAC</span>
                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill p-2"><i class="bi bi-shield-lock-fill fs-6"></i></span>
            </div>
            <h3 class="fw-bold mb-1 text-dark">{{ $stats['total_roles'] }}</h3>
            <small class="text-muted">{{ $stats['total_permissions'] }} Granular Permissions</small>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="sa-stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Store Customers</span>
                <span class="badge bg-success-subtle text-success rounded-pill p-2"><i class="bi bi-person-check-fill fs-6"></i></span>
            </div>
            <h3 class="fw-bold mb-1 text-dark">{{ $stats['total_customers'] }}</h3>
            <small class="text-muted">{{ $stats['total_orders'] }} Total Orders Placed</small>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="sa-stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Platform Revenue</span>
                <span class="badge bg-info-subtle text-info rounded-pill p-2"><i class="bi bi-cash-stack fs-6"></i></span>
            </div>
            <h3 class="fw-bold mb-1 text-dark">৳{{ number_format($stats['total_revenue'], 2) }}</h3>
            <small class="text-muted">{{ $stats['total_products'] }} Active Products</small>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Administrators Section -->
    <div class="col-12 col-xl-7">
        <div class="sa-table-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold"><i class="bi bi-people-fill text-primary me-2"></i>Active Administrative Staff</h6>
                    <small class="text-muted">Managed administrators holding delegatable operational roles</small>
                </div>
                <a href="{{ route('superadmin.admins.index') }}" class="btn btn-sm btn-outline-primary">
                    View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Administrator</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAdmins as $admin)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar bg-light text-primary rounded-circle p-2 text-center" style="width: 36px; height: 36px;">
                                            <i class="bi bi-person-badge"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $admin->name }}</div>
                                            <small class="text-muted">{{ $admin->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($admin->isSuperAdmin())
                                        <span class="badge bg-warning text-dark font-monospace fw-bold">Super Admin</span>
                                    @else
                                        @foreach($admin->roles as $role)
                                            <span class="badge bg-secondary font-monospace">{{ $role->name }}</span>
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    @if($admin->is_active)
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Deactivated</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('superadmin.admins.permissions', $admin->id) }}" class="btn btn-light border" title="Effective Permissions">
                                            <i class="bi bi-key"></i>
                                        </a>
                                        <a href="{{ route('superadmin.admins.edit', $admin->id) }}" class="btn btn-light border" title="Edit Admin">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No administrators registered.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- System Roles Section -->
    <div class="col-12 col-xl-5">
        <div class="sa-table-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold"><i class="bi bi-shield-check text-warning me-2"></i>Configured System Roles</h6>
                    <small class="text-muted">Roles available for delegation to operational staff</small>
                </div>
                <a href="{{ route('superadmin.roles.index') }}" class="btn btn-sm btn-outline-warning text-dark">
                    Manage Roles
                </a>
            </div>
            <div class="list-group list-group-flush">
                @foreach($systemRoles as $role)
                    <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="fw-bold text-dark">{{ $role->name }}</span>
                                @if($role->is_system)
                                    <span class="badge bg-dark-subtle text-dark small">Protected System</span>
                                @endif
                            </div>
                            <small class="text-muted d-block">{{ $role->description }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-light text-dark border">{{ $role->users_count }} users</span>
                            <small class="d-block text-muted mt-1">{{ $role->permissions_count }} perms</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Security Audit Logs -->
<div class="sa-table-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history text-danger me-2"></i>Security & Administration Audit Trail</h6>
            <small class="text-muted">Real-time log of administrator actions, logins, and permission adjustments</small>
        </div>
        <a href="{{ route('superadmin.activity-logs.index') }}" class="btn btn-sm btn-outline-secondary">
            Full Audit Logs <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Timestamp</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Details</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentLogs as $log)
                    <tr>
                        <td class="text-muted small">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $log->user?->name ?? 'System' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $log->action }}</span>
                        </td>
                        <td class="text-truncate" style="max-width: 320px;">
                            {{ $log->description }}
                        </td>
                        <td class="text-muted small font-monospace">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No activity logs recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
