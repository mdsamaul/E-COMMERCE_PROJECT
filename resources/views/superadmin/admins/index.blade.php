@extends('layouts.superadmin')

@section('title', 'Manage Administrators')
@section('page_title', 'Administrators Directory')
@section('page_subtitle', 'Manage administrator accounts, assign delegatable operational roles, and enforce account status.')

@section('content')
<!-- Filter & Action Bar -->
<div class="sa-table-card mb-4">
    <div class="p-3">
        <form method="GET" action="{{ route('superadmin.admins.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, or mobile..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="super-admin" {{ $roleFilter === 'super-admin' ? 'selected' : '' }}>Super Administrator</option>
                    @foreach($roles as $role)
                        @if($role->slug !== 'super-admin')
                            <option value="{{ $role->slug }}" {{ $roleFilter === $role->slug ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="1" {{ $statusFilter === '1' ? 'selected' : '' }}>Active Only</option>
                    <option value="0" {{ $statusFilter === '0' ? 'selected' : '' }}>Deactivated Only</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2 justify-content-md-end">
                <button type="submit" class="btn btn-dark">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
                @if($search || $roleFilter || $statusFilter !== null)
                    <a href="{{ route('superadmin.admins.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
                <a href="{{ route('superadmin.admins.create') }}" class="btn btn-warning text-dark fw-bold text-nowrap">
                    <i class="bi bi-person-plus-fill me-1"></i> Add Admin
                </a>
            </div>
        </form>
    </div>

    <!-- Admins Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Administrator</th>
                    <th>Mobile</th>
                    <th>Assigned Role(s)</th>
                    <th>Direct Permissions</th>
                    <th>Account Status</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $admin)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar bg-light text-primary rounded-circle p-2 text-center" style="width: 38px; height: 38px;">
                                    <i class="bi {{ $admin->isSuperAdmin() ? 'bi-shield-shaded text-warning' : 'bi-person-badge' }}"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $admin->name }}</div>
                                    <small class="text-muted">{{ $admin->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="font-monospace small">{{ $admin->phone ?? '—' }}</span>
                        </td>
                        <td>
                            @if($admin->isSuperAdmin())
                                <span class="badge bg-warning text-dark font-monospace fw-bold">Super Admin</span>
                            @else
                                @forelse($admin->roles as $role)
                                    <span class="badge bg-secondary font-monospace">{{ $role->name }}</span>
                                @empty
                                    <span class="badge bg-light text-muted border">No Role</span>
                                @endforelse
                            @endif
                        </td>
                        <td>
                            @if($admin->isSuperAdmin())
                                <span class="badge bg-light text-muted border">Global (All)</span>
                            @else
                                <span class="badge bg-info-subtle text-info font-monospace">{{ $admin->directPermissions->count() }} direct</span>
                            @endif
                        </td>
                        <td>
                            @if($admin->is_active)
                                <span class="badge bg-success-subtle text-success">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Deactivated</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $admin->created_at->format('M d, Y') }}</small>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('superadmin.admins.permissions', $admin->id) }}" class="btn btn-light border" title="Effective Permissions">
                                    <i class="bi bi-key"></i>
                                </a>
                                <a href="{{ route('superadmin.admins.edit', $admin->id) }}" class="btn btn-light border" title="Edit Admin">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                @if($admin->email !== 'admin@shopbd.com')
                                    <form method="POST" action="{{ route('superadmin.admins.status', $admin->id) }}" class="d-inline" onsubmit="return confirm('Toggle status for this administrator?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-light border {{ $admin->is_active ? 'text-danger' : 'text-success' }}" title="{{ $admin->is_active ? 'Deactivate Account' : 'Activate Account' }}">
                                            <i class="bi {{ $admin->is_active ? 'bi-person-slash' : 'bi-person-check' }}"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                            No administrators found matching your filter criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($admins->hasPages())
        <div class="p-3 border-top">
            {{ $admins->links() }}
        </div>
    @endif
</div>
@endsection
