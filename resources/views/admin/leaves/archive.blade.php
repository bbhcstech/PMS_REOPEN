@extends('admin.layout.app')

@section('title', 'Archived Leaves')

@section('content')
<div class="leave-archive-page">
    <section class="archive-header">
        <div>
            <h1><i class="fas fa-box-archive me-2"></i>Archived Leaves</h1>
            <p>Restore archived leave requests whenever they need to return to the active leave section.</p>
        </div>
        <a href="{{ route(request('staff_category') === 'authority' ? 'admin.authority-leaves' : 'leaves.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back to Leaves</a>
    </section>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <section class="archive-card">
        <div class="archive-card-head">
            <div>
                <h2>Archived Leave Requests</h2>
                <p>Search archived leaves and restore records to the active table.</p>
            </div>
            <span class="total-badge">Total: {{ method_exists($leaves, 'total') ? $leaves->total() : $leaves->count() }}</span>
        </div>

        <form method="GET" action="{{ route('leaves.archive', ['staff_category' => request('staff_category')]) }}" class="archive-search">
            <input type="hidden" name="staff_category" value="{{ request('staff_category', 'employee') }}">
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by employee, type, status, or reason">
            <button class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            <a href="{{ route('leaves.archive', ['staff_category' => request('staff_category')]) }}" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Reset</a>
        </form>

        <div class="table-responsive">
            <table class="table archive-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Archived On</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                        <tr>
                            <td>
                                <strong>{{ $leave->user?->name ?? 'N/A' }}</strong>
                                <small>{{ $leave->user?->employeeDetail?->department?->dpt_name ?? $leave->user?->designation ?? 'Employee' }}</small>
                            </td>
                            <td>{{ $leave->type_label }}</td>
                            <td>
                                <strong>{{ optional($leave->start_date)->format('d M Y') }}</strong>
                                <small>{{ optional($leave->end_date)->format('d M Y') }}</small>
                            </td>
                            <td>{{ number_format((float) $leave->total_days, 1) }}</td>
                            <td><span class="status-badge {{ $leave->status }}">{{ ucfirst($leave->status) }}</span></td>
                            <td><span class="pay-badge {{ $leave->status === 'rejected' ? 'rejected' : ($leave->is_unpaid ? 'unpaid' : 'paid') }}">{{ $leave->status === 'rejected' ? 'N/A' : ($leave->is_unpaid ? 'Unpaid' : 'Paid') }}</span></td>
                            <td>{{ $leave->archived_at?->format('d M Y h:i A') ?? 'Unknown' }}</td>
                            <td class="text-end">
                                <div class="action-buttons">
                                    <form method="POST" action="{{ route('leaves.restore', $leave->id) }}" onsubmit="return confirm('Restore this leave request to the active table?');">
                                        @csrf
                                        <button class="btn btn-sm btn-success"><i class="fas fa-trash-restore"></i> Restore</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100" class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <h3>No archived leave requests found</h3>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">{{ $leaves->links() }}</div>
    </section>
</div>

<style>
    .leave-archive-page {
        padding: 24px 32px;
        background: linear-gradient(135deg, #F8FAFC, #EEF2FF);
        min-height: calc(100vh - 80px);
    }

    .archive-header,
    .archive-card {
        background: #ffffff;
        border: 1px solid rgba(47, 107, 255, 0.1);
        border-radius: 18px;
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.06);
    }

    .archive-header {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: center;
        padding: 24px;
        margin-bottom: 22px;
    }

    .archive-header h1,
    .archive-card-head h2 {
        margin: 0;
        color: #0F172A;
        font-weight: 800;
    }

    .archive-header p,
    .archive-card-head p {
        margin: 6px 0 0;
        color: #5a6e63;
        font-weight: 500;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 12px;
        border: 0;
        min-height: 42px;
        padding: 0 18px;
        font-weight: 700;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }

    .btn-light {
        background: #F8FAFC;
        color: #2F6BFF;
        border: 1px solid rgba(47, 107, 255, 0.18);
    }

    .btn-primary {
        background: linear-gradient(145deg, #4F83FF, #2F6BFF);
        color: #ffffff;
    }

    .archive-card {
        overflow: hidden;
    }

    .archive-card-head {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: center;
        padding: 22px 24px;
        border-bottom: 1px solid rgba(47, 107, 255, 0.08);
    }

    .total-badge {
        border-radius: 999px;
        padding: 8px 14px;
        background: #EEF2FF;
        color: #047857;
        font-weight: 800;
        white-space: nowrap;
    }

    .archive-search {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 24px;
        background: #fafefb;
        border-bottom: 1px solid rgba(47, 107, 255, 0.08);
    }

    .archive-search .form-control {
        flex: 1;
        min-width: 0;
        min-height: 44px;
        border-radius: 12px;
        border: 1px solid #dbe7df;
    }

    .archive-search .btn {
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }

    .archive-table {
        margin: 0;
    }

    .archive-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.78rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 14px 18px;
        border-bottom: 1px solid #e5e7eb;
    }

    .archive-table td {
        padding: 16px 18px;
        vertical-align: middle;
        border-bottom: 1px solid #eef2f7;
    }

    .archive-table td strong,
    .archive-table td small {
        display: block;
    }

    .archive-table td small {
        color: #64748b;
    }

    .status-badge,
    .pay-badge {
        display: inline-flex;
        border-radius: 999px;
        padding: 7px 12px;
        font-weight: 800;
        font-size: 0.82rem;
    }

    .status-badge.pending {
        background: #fef3c7;
        color: #b45309;
    }

    .status-badge.approved,
    .pay-badge.paid {
        background: #E0E7FF;
        color: #047857;
    }

    .status-badge.rejected,
    .pay-badge.rejected {
        background: #fee2e2;
        color: #b91c1c;
    }

    .pay-badge.unpaid {
        background: #ffedd5;
        color: #c2410c;
    }

    .archive-table td.empty-state,
    .empty-state {
        padding: 56px 20px !important;
        text-align: center !important;
        color: #64748b;
        width: 100% !important;
    }

    .empty-state i {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 44px;
        color: #C7D2FE;
        margin-bottom: 12px;
    }

    .empty-state h3 {
        margin: 0;
        font-size: 1.15rem;
        color: #2F6BFF;
        font-weight: 800;
    }
    }

    .pagination-wrap {
        padding: 18px 24px;
    }

    @media (max-width: 768px) {
        .leave-archive-page {
            padding: 16px;
        }

        .archive-header,
        .archive-card-head,
        .archive-search {
            flex-direction: column;
            align-items: stretch;
        }
    }

    /* Dark Theme Support */
    html[data-pms-theme="dark"] .leave-archive-page,
    html[data-bs-theme="dark"] .leave-archive-page,
    html[data-theme="dark"] .leave-archive-page,
    html.dark .leave-archive-page {
        background: #070B1A !important;
        color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .archive-header,
    html[data-pms-theme="dark"] .archive-card,
    html[data-bs-theme="dark"] .archive-header,
    html[data-bs-theme="dark"] .archive-card,
    html[data-theme="dark"] .archive-header,
    html[data-theme="dark"] .archive-card {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4) !important;
    }

    html[data-pms-theme="dark"] .archive-header h1,
    html[data-pms-theme="dark"] .archive-card-head h2,
    html[data-bs-theme="dark"] .archive-header h1,
    html[data-bs-theme="dark"] .archive-card-head h2 {
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .archive-header p,
    html[data-pms-theme="dark"] .archive-card-head p,
    html[data-bs-theme="dark"] .archive-header p,
    html[data-bs-theme="dark"] .archive-card-head p {
        color: #9AA3C7 !important;
    }

    html[data-pms-theme="dark"] .archive-card-head,
    html[data-bs-theme="dark"] .archive-card-head {
        border-bottom-color: rgba(238, 241, 251, 0.08) !important;
    }

    html[data-pms-theme="dark"] .total-badge,
    html[data-bs-theme="dark"] .total-badge {
        background: rgba(47, 107, 255, 0.18) !important;
        color: #60A5FA !important;
        border: 1px solid rgba(79, 131, 255, 0.3) !important;
    }

    html[data-pms-theme="dark"] .archive-search,
    html[data-bs-theme="dark"] .archive-search {
        background: #0D1326 !important;
        border-bottom-color: rgba(238, 241, 251, 0.08) !important;
    }

    html[data-pms-theme="dark"] .archive-search .form-control,
    html[data-bs-theme="dark"] .archive-search .form-control {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.16) !important;
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .archive-search .form-control::placeholder,
    html[data-bs-theme="dark"] .archive-search .form-control::placeholder {
        color: #64748B !important;
    }

    html[data-pms-theme="dark"] .archive-header .btn-light,
    html[data-pms-theme="dark"] .archive-search .btn-secondary,
    html[data-bs-theme="dark"] .archive-header .btn-light,
    html[data-bs-theme="dark"] .archive-search .btn-secondary {
        background: #141B3D !important;
        border: 1px solid rgba(238, 241, 251, 0.16) !important;
        color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .archive-header .btn-light:hover,
    html[data-pms-theme="dark"] .archive-search .btn-secondary:hover,
    html[data-bs-theme="dark"] .archive-header .btn-light:hover,
    html[data-bs-theme="dark"] .archive-search .btn-secondary:hover {
        background: #1C2652 !important;
        border-color: rgba(96, 165, 250, 0.4) !important;
        color: #FFFFFF !important;
    }

    html[data-pms-theme="dark"] .archive-table th,
    html[data-bs-theme="dark"] .archive-table th {
        background: #121938 !important;
        color: #9AA3C7 !important;
        border-bottom-color: rgba(238, 241, 251, 0.08) !important;
    }

    html[data-pms-theme="dark"] .archive-table td,
    html[data-bs-theme="dark"] .archive-table td {
        color: #CBD5E1 !important;
        border-bottom-color: rgba(238, 241, 251, 0.06) !important;
    }

    html[data-pms-theme="dark"] .archive-table td strong,
    html[data-bs-theme="dark"] .archive-table td strong {
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .archive-table td small,
    html[data-bs-theme="dark"] .archive-table td small {
        color: #9AA3C7 !important;
    }

    html[data-pms-theme="dark"] .status-badge.pending,
    html[data-bs-theme="dark"] .status-badge.pending {
        background: rgba(245, 158, 11, 0.2) !important;
        color: #fbbf24 !important;
    }

    html[data-pms-theme="dark"] .status-badge.approved,
    html[data-pms-theme="dark"] .pay-badge.paid,
    html[data-bs-theme="dark"] .status-badge.approved,
    html[data-bs-theme="dark"] .pay-badge.paid {
        background: rgba(47, 107, 255, 0.2) !important;
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .status-badge.rejected,
    html[data-bs-theme="dark"] .status-badge.rejected,
    html[data-pms-theme="dark"] .pay-badge.rejected,
    html[data-bs-theme="dark"] .pay-badge.rejected {
        background: rgba(239, 68, 68, 0.2) !important;
        color: #f87171 !important;
    }

    html[data-pms-theme="dark"] .pay-badge.unpaid,
    html[data-bs-theme="dark"] .pay-badge.unpaid {
        background: rgba(249, 115, 22, 0.2) !important;
        color: #fb923c !important;
    }

    html[data-pms-theme="dark"] .archive-table td.empty-state,
    html[data-pms-theme="dark"] .empty-state,
    html[data-bs-theme="dark"] .archive-table td.empty-state,
    html[data-bs-theme="dark"] .empty-state,
    html[data-theme="dark"] .archive-table td.empty-state,
    html[data-theme="dark"] .empty-state,
    html.dark .archive-table td.empty-state,
    html.dark .empty-state {
        color: #9AA3C7 !important;
        width: 100% !important;
        text-align: center !important;
    }

    html[data-pms-theme="dark"] .empty-state i,
    html[data-bs-theme="dark"] .empty-state i,
    html[data-theme="dark"] .empty-state i {
        color: #60A5FA !important;
        background: rgba(47, 107, 255, 0.15) !important;
        width: 60px !important;
        height: 60px !important;
        border-radius: 18px !important;
        border: 1px solid rgba(79, 131, 255, 0.25) !important;
        margin-bottom: 14px !important;
    }

    html[data-pms-theme="dark"] .empty-state h3,
    html[data-bs-theme="dark"] .empty-state h3,
    html[data-theme="dark"] .empty-state h3 {
        color: #EEF1FB !important;
    }
</style>
@endsection
