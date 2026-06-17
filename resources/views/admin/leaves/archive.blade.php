@extends('admin.layout.app')

@section('title', 'Archived Leaves')

@section('content')
<div class="leave-archive-page">
    <section class="archive-header">
        <div>
            <h1><i class="fas fa-box-archive me-2"></i>Archived Leaves</h1>
            <p>Restore archived leave requests whenever they need to return to the active leave section.</p>
        </div>
        <a href="{{ route('leaves.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back to Leaves</a>
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

        <form method="GET" action="{{ route('leaves.archive') }}" class="archive-search">
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by employee, type, status, or reason">
            <button class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            <a href="{{ route('leaves.archive') }}" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Reset</a>
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
                            <td><span class="pay-badge {{ $leave->is_unpaid ? 'unpaid' : 'paid' }}">{{ $leave->is_unpaid ? 'Unpaid' : 'Paid' }}</span></td>
                            <td>{{ $leave->archived_at?->format('d M Y h:i A') ?? 'Unknown' }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('leaves.restore', $leave->id) }}" onsubmit="return confirm('Restore this leave request to the active table?');">
                                    @csrf
                                    <button class="btn btn-sm btn-success"><i class="fas fa-trash-restore"></i> Restore</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
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
        background: linear-gradient(135deg, #f7fbf9, #eef8f2);
        min-height: calc(100vh - 80px);
    }

    .archive-header,
    .archive-card {
        background: #ffffff;
        border: 1px solid rgba(16, 185, 129, 0.1);
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
        color: #0a2e1f;
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
    }

    .btn-light {
        background: #f0f9f4;
        color: #0f744c;
        border: 1px solid rgba(16, 185, 129, 0.18);
    }

    .btn-primary {
        background: linear-gradient(145deg, #34d399, #059669);
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
        border-bottom: 1px solid rgba(16, 185, 129, 0.08);
    }

    .total-badge {
        border-radius: 999px;
        padding: 8px 14px;
        background: #ecfdf5;
        color: #047857;
        font-weight: 800;
        white-space: nowrap;
    }

    .archive-search {
        display: flex;
        gap: 12px;
        padding: 18px 24px;
        background: #fafefb;
        border-bottom: 1px solid rgba(16, 185, 129, 0.08);
    }

    .archive-search .form-control {
        min-height: 44px;
        border-radius: 12px;
        border: 1px solid #dbe7df;
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
        background: #d1fae5;
        color: #047857;
    }

    .status-badge.rejected {
        background: #fee2e2;
        color: #b91c1c;
    }

    .pay-badge.unpaid {
        background: #ffedd5;
        color: #c2410c;
    }

    .empty-state {
        padding: 48px 20px !important;
        text-align: center;
        color: #64748b;
    }

    .empty-state i {
        display: block;
        font-size: 44px;
        color: #a7f3d0;
        margin-bottom: 12px;
    }

    .empty-state h3 {
        margin: 0;
        font-size: 1.15rem;
        color: #0f744c;
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
</style>
@endsection
