@extends('admin.layout.app')

@section('title', 'Payroll Audit Logs')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bx bx-list-check text-primary me-2"></i>Payroll Audit Logs</h4>
            <p class="text-muted mb-0">Complete immutable record of all payroll system actions</p>
        </div>
        <div class="d-flex gap-2">
            <input type="text" id="auditSearch" class="form-control form-control-sm" placeholder="Search action, user..." style="width:220px;">
            <select id="actionFilter" class="form-select form-select-sm" style="width:180px;">
                <option value="">All Actions</option>
                <option value="created">Created</option>
                <option value="updated">Updated</option>
                <option value="deleted">Deleted</option>
                <option value="finalized">Finalized</option>
                <option value="archived">Archived</option>
                <option value="payroll_import">Import</option>
            </select>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="auditTable">
                <thead style="background:#f8fafc;font-size:12px;">
                    <tr class="text-muted">
                        <th class="px-4 fw-semibold text-uppercase" style="min-width:140px;">Date & Time</th>
                        <th class="fw-semibold text-uppercase">User</th>
                        <th class="fw-semibold text-uppercase">Role</th>
                        <th class="fw-semibold text-uppercase">Action</th>
                        <th class="fw-semibold text-uppercase">Record</th>
                        <th class="fw-semibold text-uppercase">IP Address</th>
                        <th class="fw-semibold text-uppercase pe-4">Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $actionColor = match(true) {
                                str_contains($log->action,'created') || str_contains($log->action,'generated') => 'success',
                                str_contains($log->action,'deleted') || str_contains($log->action,'archived') => 'danger',
                                str_contains($log->action,'finalized') => 'primary',
                                str_contains($log->action,'updated') || str_contains($log->action,'status') => 'info',
                                default => 'secondary'
                            };
                        @endphp
                        <tr>
                            <td class="px-4">
                                <div class="fw-semibold small">{{ $log->created_at?->format('d M Y') }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ $log->created_at?->format('h:i A') }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-xs rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                         style="background:#4f46e5;font-size:11px;width:28px;height:28px;min-width:28px;">
                                        {{ strtoupper(substr($log->user?->name ?? $log->user_id ?? 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">{{ $log->user?->name ?? 'User #'.$log->user_id }}</div>
                                        <div class="text-muted" style="font-size:11px;">{{ $log->user?->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($log->role)
                                    <span class="badge bg-label-secondary" style="font-size:10px;">{{ ucfirst($log->role) }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-label-{{ $actionColor }}">
                                    {{ Str::headline($log->action) }}
                                </span>
                            </td>
                            <td class="small text-muted">
                                @if($log->auditable_type && $log->auditable_id)
                                    <span class="font-monospace" style="font-size:11px;">
                                        {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="small text-muted font-monospace">{{ $log->ip_address ?? '—' }}</td>
                            <td class="small text-muted pe-4">{{ Str::limit($log->reason, 50) ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bx bx-list-check fs-1 d-block mb-2 opacity-30"></i>
                                No audit log entries found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="p-3">{{ $logs->links() }}</div>
        @endif
    </div>

</div>

<script>
function filterAuditTable() {
    const q = document.getElementById('auditSearch').value.toLowerCase();
    const af = document.getElementById('actionFilter').value.toLowerCase();
    document.querySelectorAll('#auditTable tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        const matchQ = !q || text.includes(q);
        const matchA = !af || text.includes(af);
        row.style.display = (matchQ && matchA) ? '' : 'none';
    });
}
document.getElementById('auditSearch').addEventListener('input', filterAuditTable);
document.getElementById('actionFilter').addEventListener('change', filterAuditTable);
</script>
@endsection
