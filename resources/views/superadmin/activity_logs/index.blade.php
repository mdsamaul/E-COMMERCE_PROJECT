@extends('layouts.superadmin')

@section('title', 'Security & Audit Logs')
@section('page_title', 'Security & Audit Trail')
@section('page_subtitle', 'Immutable audit log of administrative actions, role assignments, logins, and configurations.')

@section('content')
<!-- Filter Bar -->
<div class="sa-table-card mb-4">
    <div class="p-3">
        <form method="GET" action="{{ route('superadmin.activity-logs.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by description or IP address..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="action" class="form-select">
                    <option value="">All Audit Actions</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ $action === $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
                @if($search || $action)
                    <a href="{{ route('superadmin.activity-logs.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Timestamp</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-muted small text-nowrap">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td>
                            @if($log->user)
                                <div class="fw-bold text-dark small">{{ $log->user->name }}</div>
                                <small class="text-muted" style="font-size: 0.75rem;">{{ $log->user->email }}</small>
                            @else
                                <span class="badge bg-light text-muted border">System</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $log->action }}</span>
                        </td>
                        <td>
                            <div class="small text-dark">{{ $log->description }}</div>
                        </td>
                        <td>
                            <code class="text-muted small">{{ $log->ip_address ?? '—' }}</code>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-clock-history fs-1 d-block mb-2 text-secondary"></i>
                            No activity log entries found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="p-3 border-top">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
