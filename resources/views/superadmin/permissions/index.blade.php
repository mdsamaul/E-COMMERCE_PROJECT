@extends('layouts.superadmin')

@section('title', 'Permission Catalog')
@section('page_title', 'System Permission Catalog')
@section('page_subtitle', 'Master registry of granular permissions governing application modules.')

@section('content')
<div class="mb-4">
    <p class="text-muted small">Permissions are evaluated by middleware, controller gates, and Blade directives to guard administrative routes.</p>
</div>

<div class="row g-4">
    @foreach($permissions as $module => $modulePermissions)
        <div class="col-12 col-lg-6">
            <div class="sa-table-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center py-3 bg-light">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-folder2-open text-primary me-2"></i>{{ $module }} Module
                    </h6>
                    <span class="badge bg-secondary font-monospace">{{ count($modulePermissions) }} Rights</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Permission</th>
                                <th>Slug</th>
                                <th>Assigned Roles</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modulePermissions as $p)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark small">{{ $p->name }}</div>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">{{ $p->description }}</small>
                                    </td>
                                    <td>
                                        <code class="text-primary small font-monospace">{{ $p->slug }}</code>
                                    </td>
                                    <td>
                                        @forelse($p->roles as $role)
                                            <span class="badge bg-light text-dark border font-monospace mb-1" style="font-size: 0.7rem;">{{ $role->name }}</span>
                                        @empty
                                            <span class="text-muted small">—</span>
                                        @endforelse
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
