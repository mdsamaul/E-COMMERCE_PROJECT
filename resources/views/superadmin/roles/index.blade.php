@extends('layouts.superadmin')

@section('title', 'Role Management')
@section('page_title', 'Role & Access Management')
@section('page_subtitle', 'Define administrative roles and bind them to granular operational permissions.')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted small mb-0">System roles provide foundational access profiles, while custom roles allow specialized delegation.</p>
    </div>
    <a href="{{ route('superadmin.roles.create') }}" class="btn btn-warning text-dark fw-bold">
        <i class="bi bi-shield-plus me-1"></i> Create Custom Role
    </a>
</div>

<div class="row g-4">
    @forelse($roles as $role)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="sa-table-card h-100 d-flex flex-column">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ $role->name }}</h5>
                        <code class="small text-muted">{{ $role->slug }}</code>
                    </div>
                    @if($role->is_system)
                        <span class="badge bg-dark font-monospace">System Role</span>
                    @else
                        <span class="badge bg-primary-subtle text-primary font-monospace">Custom Role</span>
                    @endif
                </div>
                <div class="p-3 flex-grow-1">
                    <p class="text-muted small mb-3">{{ $role->description ?? 'No description provided.' }}</p>

                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="p-2 bg-light rounded border">
                                <span class="d-block fw-bold text-dark fs-5">{{ $role->users_count }}</span>
                                <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Assigned Users</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded border">
                                <span class="d-block fw-bold text-dark fs-5">{{ $role->permissions_count }}</span>
                                <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Permissions</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-top p-3 d-flex justify-content-between align-items-center">
                    <a href="{{ route('superadmin.roles.edit', $role->id) }}" class="btn btn-sm btn-outline-dark">
                        <i class="bi bi-pencil me-1"></i> Edit Permissions
                    </a>

                    @if(!$role->is_system)
                        <form method="POST" action="{{ route('superadmin.roles.destroy', $role->id) }}" class="d-inline" onsubmit="return confirm('Delete this custom role? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" {{ $role->users_count > 0 ? 'disabled' : '' }} title="{{ $role->users_count > 0 ? 'Cannot delete role with assigned users' : 'Delete Role' }}">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary small" title="Protected system role"><i class="bi bi-lock-fill"></i> Protected</span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="sa-table-card p-5 text-center text-muted">
                No roles defined in the system.
            </div>
        </div>
    @endforelse
</div>
@endsection
