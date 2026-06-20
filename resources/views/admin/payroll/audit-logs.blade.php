@extends('admin.layout.app')

@section('title', 'Payroll Audit Logs')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header"><h5 class="mb-0">Payroll Audit Logs</h5></div>
        <div class="table-responsive">
            <table class="table"><thead><tr><th>Date</th><th>User</th><th>Role</th><th>Action</th><th>IP</th><th>Reason</th></tr></thead><tbody>
                @forelse($logs as $log)
                    <tr><td>{{ $log->created_at->format('d M Y h:i A') }}</td><td>{{ $log->user_id ?? '-' }}</td><td>{{ $log->role ?? '-' }}</td><td>{{ Str::headline($log->action) }}</td><td>{{ $log->ip_address ?? '-' }}</td><td>{{ $log->reason ?? '-' }}</td></tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No audit logs found.</td></tr>
                @endforelse
            </tbody></table>
        </div>
        <div class="card-footer">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
