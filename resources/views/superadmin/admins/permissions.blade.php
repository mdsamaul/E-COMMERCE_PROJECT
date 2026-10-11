@extends('layouts.superadmin')

@section('title', 'Admin Permissions: ' . $admin->name)
@section('page_title', 'Effective Permissions: ' . $admin->name)
@section('page_subtitle', 'Comprehensive inspection of inherited role privileges and individual permission overrides.')

@section('content')
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}" class="text-decoration-none">Control Center</a></li>
        <li class="breadcrumb-item"><a href="{{ route('superadmin.admins.index') }}" class="text-decoration-none">Administrators</a></li>
        <li class="breadcrumb-item active" aria-current="page">Permissions: {{ $admin->name }}</li>
    </ol>
</nav>

<!-- Admin Summary Card -->
<div class="sa-table-card p-4 mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar bg-primary-subtle text-primary rounded-circle p-3 text-center" style="width: 52px; height: 52px;">
                <i class="bi bi-person-badge fs-4"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark">{{ $admin->name }}</h4>
                <div class="text-muted small">{{ $admin->email }} &bull; {{ $admin->phone }}</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($admin->isSuperAdmin())
                <span class="badge bg-warning text-dark font-monospace fw-bold fs-6">SUPER ADMINISTRATOR</span>
            @else
                @foreach($admin->roles as $role)
                    <span class="badge bg-secondary font-monospace fs-6">{{ $role->name }}</span>
                @endforeach
            @endif
            <a href="{{ route('superadmin.admins.edit', $admin->id) }}" class="btn btn-sm btn-outline-primary ms-2">
                <i class="bi bi-pencil me-1"></i> Edit Account
            </a>
        </div>
    </div>
</div>

@if($admin->isSuperAdmin())
    <div class="alert alert-warning border text-dark p-4 shadow-sm mb-4">
        <div class="d-flex gap-3 align-items-center">
            <i class="bi bi-shield-fill-check fs-1 text-warning"></i>
            <div>
                <h5 class="fw-bold mb-1">Global System Authority Granted</h5>
                <p class="mb-0 small">As a Super Administrator, this account possesses unconstrained global permissions across all storefront, operational, administrative, and security endpoints.</p>
            </div>
        </div>
    </div>
@endif

<!-- Effective Permission Matrix by Module -->
<div class="sa-table-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-diagram-3-fill text-primary me-2"></i>Permission Matrix by Module</h5>
            <small class="text-muted">Breakdown of active authorization rights for this administrator.</small>
        </div>
        <span class="badge bg-dark font-monospace fs-6">
            {{ $admin->isSuperAdmin() ? 'All Permissions Active' : $effectivePermissions->count() . ' Effective Rights' }}
        </span>
    </div>

    @php
        $effectiveSlugs = $effectivePermissions->pluck('slug')->toArray();
        $directSlugs = $admin->directPermissions->pluck('slug')->toArray();
        $roleSlugs = $admin->roles->flatMap(fn($r) => $r->permissions)->pluck('slug')->toArray();
    @endphp

    <div class="row g-4">
        @foreach($allPermissions as $module => $modulePermissions)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 border">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                        <span class="fw-bold text-dark">{{ $module }}</span>
                        <span class="badge bg-secondary-subtle text-secondary small">{{ count($modulePermissions) }} perms</span>
                    </div>
                    <ul class="list-group list-group-flush">
                        @foreach($modulePermissions as $p)
                            @php
                                $hasPerm = $admin->isSuperAdmin() || in_array($p->slug, $effectiveSlugs);
                                $isDirect = in_array($p->slug, $directSlugs);
                                $isRole = in_array($p->slug, $roleSlugs);
                            @endphp
                            <li class="list-group-item d-flex justify-content-between align-items-start py-2">
                                <div>
                                    <div class="small fw-semibold {{ $hasPerm ? 'text-dark' : 'text-muted' }}">
                                        <i class="bi {{ $hasPerm ? 'bi-check-circle-fill text-success' : 'bi-x-circle text-muted' }} me-1"></i>
                                        {{ $p->name }}
                                    </div>
                                    <small class="text-muted font-monospace d-block" style="font-size: 0.7rem;">{{ $p->slug }}</small>
                                </div>
                                <div>
                                    @if($admin->isSuperAdmin())
                                        <span class="badge bg-warning text-dark font-monospace" style="font-size: 0.65rem;">Super</span>
                                    @elseif($isDirect)
                                        <span class="badge bg-info text-dark font-monospace" style="font-size: 0.65rem;">Direct</span>
                                    @elseif($isRole)
                                        <span class="badge bg-secondary font-monospace" style="font-size: 0.65rem;">Role</span>
                                    @else
                                        <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.65rem;">None</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
