@extends('admin.layout.app')

@section('title', 'Archived Attendance')

@section('content')
<style>
    .attendance-archive-page {
        background: linear-gradient(135deg, #f0f9ff 0%, #e6f7f5 50%, #f0fdf4 100%);
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
    }

    .archive-header,
    .archive-card {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.7);
        border-radius: 24px;
        box-shadow: 0 8px 40px rgba(15, 23, 42, 0.08);
    }

    .archive-header {
        padding: 1.75rem 2rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .archive-header h1 {
        margin: 0;
        color: #0f172a;
        font-size: 2rem;
        font-weight: 800;
    }

    .archive-header p {
        margin: 0.35rem 0 0;
        color: #64748b;
        font-weight: 600;
    }

    .back-button,
    .search-button,
    .reset-button {
        border: 0;
        border-radius: 16px;
        padding: 0.75rem 1.25rem;
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        font-weight: 800;
        text-decoration: none;
    }

    .back-button {
        background: #f0f9f4;
        color: #0f744c;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .search-button {
        background: linear-gradient(135deg, #1e3a8a, #0ea5a4);
        color: #ffffff;
    }

    .reset-button {
        background: #e2e8f0;
        color: #475569;
    }

    .archive-card {
        padding: 1.5rem;
    }

    .archive-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .archive-card-header h5 {
        margin: 0;
        color: #0f172a;
        font-weight: 800;
    }

    .archive-card-header p {
        margin: 0.25rem 0 0;
        color: #64748b;
    }

    .total-badge {
        background: #ecfdf5;
        color: #059669;
        border-radius: 999px;
        padding: 0.5rem 0.9rem;
        font-weight: 800;
    }

    .archive-search-bar {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .search-input {
        flex: 1;
        min-width: 240px;
        position: relative;
    }

    .search-input i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .search-input input,
    .per-page-select {
        width: 100%;
        min-height: 46px;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        padding: 0.65rem 1rem;
        font-weight: 600;
    }

    .search-input input {
        padding-left: 2.65rem;
    }

    .per-page-select {
        width: 100px;
    }

    .archive-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.55rem;
        min-width: 900px;
    }

    .archive-table th {
        color: #64748b;
        font-size: 0.82rem;
        text-transform: uppercase;
        padding: 0.75rem;
    }

    .archive-table td {
        background: #ffffff;
        color: #1e293b;
        padding: 0.85rem;
        border-top: 1px solid #eef2f7;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
    }

    .archive-table td:first-child {
        border-left: 1px solid #eef2f7;
        border-radius: 14px 0 0 14px;
    }

    .archive-table td:last-child {
        border-right: 1px solid #eef2f7;
        border-radius: 0 14px 14px 0;
    }

    .employee-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .employee-cell img {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        object-fit: cover;
    }

    .employee-name {
        color: #0f172a;
        font-weight: 800;
    }

    .employee-meta,
    .muted {
        color: #64748b;
        font-size: 0.86rem;
    }

    .status-badge,
    .archived-badge {
        border-radius: 999px;
        padding: 0.42rem 0.75rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .status-badge {
        background: #eff6ff;
        color: #2563eb;
    }

    .archived-badge {
        background: #fff7ed;
        color: #c2410c;
    }

    .btn-restore {
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 12px;
        background: #dcfce7;
        color: #15803d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #64748b;
    }

    .alert-premium {
        border-radius: 16px;
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
        font-weight: 700;
    }

    .alert-success {
        background: #dcfce7;
        color: #166534;
    }

    .alert-error {
        background: #fee2e2;
        color: #991b1b;
    }

    @media (max-width: 768px) {
        .attendance-archive-page {
            padding: 1rem;
        }

        .archive-header {
            padding: 1.25rem;
        }
    }
</style>

<div class="attendance-archive-page">
    <div class="archive-header">
        <div>
            <h1><i class="fas fa-archive me-2"></i>Archived Attendance</h1>
            <p><i class="fas fa-info-circle me-1"></i>Restore archived attendance records whenever they need to return to the active table.</p>
        </div>
        <a href="{{ route('attendance.index') }}" class="back-button">
            <i class="fas fa-arrow-left"></i>Back to Active Attendance
        </a>
    </div>

    @if(session('success'))
        <div class="alert-premium alert-success"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert-premium alert-error"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
    @endif

    @php
        $totalArchived = method_exists($attendances, 'total') ? $attendances->total() : $attendances->count();
    @endphp

    <div class="archive-card">
        <div class="archive-card-header">
            <div>
                <h5><i class="fas fa-history me-2"></i>Archived Attendance Records</h5>
                <p>Search archived attendance and restore records to the active list.</p>
            </div>
            <span class="total-badge"><i class="fas fa-database me-1"></i>Total: {{ $totalArchived }}</span>
        </div>

        <form method="GET" action="{{ route('attendance.archive') }}" class="archive-search-bar">
            <div class="search-input">
                <i class="fas fa-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search employee, status, date...">
            </div>
            <select name="per_page" class="per-page-select">
                @foreach([10, 20, 30, 40, 50, 100] as $size)
                    <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                @endforeach
            </select>
            <button type="submit" class="search-button"><i class="fas fa-search"></i>Search</button>
            @if(request()->hasAny(['search', 'per_page']))
                <a href="{{ route('attendance.archive') }}" class="reset-button"><i class="fas fa-rotate-left"></i>Reset</a>
            @endif
        </form>

        <div class="table-responsive">
            @if($attendances->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-archive fa-3x mb-3"></i>
                    <h5>No Archived Attendance Found</h5>
                    <p>There are no attendance records in the archive at the moment.</p>
                </div>
            @else
                <table class="archive-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Total</th>
                            <th>Archived On</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $index => $attendance)
                            <tr>
                                <td>{{ $attendances->firstItem() + $index }}</td>
                                <td>
                                    <div class="employee-cell">
                                        <img src="{{ $attendance->user?->profile_image ? asset($attendance->user->profile_image) : asset('images/default-avatar.png') }}"
                                             alt="{{ $attendance->user?->name ?? 'Employee' }}"
                                             onerror="this.onerror=null; this.src='{{ asset('admin/assets/img/avatars/1.png') }}';">
                                        <div>
                                            <div class="employee-name">{{ $attendance->user?->name ?? '-' }}</div>
                                            <div class="employee-meta">{{ $attendance->user?->employeeDetail?->designation?->name ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ optional($attendance->date)->format('d M Y') }}</td>
                                <td><span class="status-badge">{{ ucfirst(str_replace('_', ' ', $attendance->status ?? '-')) }}</span></td>
                                <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('h:i A') : '-' }}</td>
                                <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('h:i A') : '-' }}</td>
                                <td>{{ $attendance->total_duration }}</td>
                                <td>
                                    <span class="archived-badge">
                                        <i class="far fa-calendar-alt"></i>
                                        {{ $attendance->archived_at ? $attendance->archived_at->format('d M Y h:i A') : 'Unknown' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('attendance.restore', $attendance->id) }}" method="POST" onsubmit="return confirm('Restore this attendance record to the active table?');">
                                        @csrf
                                        <button type="submit" class="btn-restore" title="Restore">
                                            <i class="fas fa-trash-restore"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($attendances->hasPages())
                    <div class="mt-3">
                        {{ $attendances->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
