@extends('admin.layout.app')

@section('title', 'Leave Details')

@section('content')
<div class="leave-show-page">
    <div class="show-head">
        <div>
            <span><i class="fas fa-calendar-check"></i> Leave Request</span>
            <h1>{{ $leave->user?->name ?? 'Employee' }}</h1>
            <p>{{ $leave->type_label }} from {{ optional($leave->start_date)->format('d M Y') }} to {{ optional($leave->end_date)->format('d M Y') }}</p>
        </div>
        <a href="{{ route('leaves.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="detail-grid">
        <section class="detail-card">
            <h2>Request Details</h2>
            <dl>
                <dt>Status</dt><dd><span class="badge status-{{ $leave->status }}">{{ ucfirst($leave->status) }}</span></dd>
                <dt>Payment</dt><dd>{{ $leave->is_unpaid ? 'Unpaid Leave / Payroll Deduction' : 'Paid Leave' }}</dd>
                <dt>Total Days</dt><dd>{{ number_format((float) $leave->total_days, 1) }}</dd>
                <dt>Emergency</dt><dd>{{ $leave->emergency_flag ? 'Yes' : 'No' }}</dd>
                <dt>Half Day</dt><dd>{{ $leave->half_day_flag ? 'Yes' : 'No' }}</dd>
                <dt>Contact</dt><dd>{{ $leave->contact_during_leave ?: 'Not provided' }}</dd>
                <dt>Attachment</dt><dd>@if($leave->attachment_path)<a href="{{ asset($leave->attachment_path) }}" target="_blank">Open document</a>@else None @endif</dd>
            </dl>
        </section>

        <section class="detail-card">
            <h2>Reason</h2>
            <p>{{ $leave->reason }}</p>
            @if($leave->apology_note)
                <h2>Apology / Regularization</h2>
                <p>{{ $leave->apology_note }}</p>
            @endif
            @if($leave->rejection_reason)
                <h2>Rejection Reason</h2>
                <p>{{ $leave->rejection_reason }}</p>
            @endif
        </section>
    </div>

    <section class="detail-card mt-3">
        <h2>Approval Audit</h2>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Date</th><th>By</th><th>Action</th><th>Note</th></tr></thead>
                <tbody>
                    @forelse($leave->approvals as $approval)
                        <tr>
                            <td>{{ $approval->created_at->format('d M Y h:i A') }}</td>
                            <td>{{ $approval->user?->name ?? 'System' }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $approval->action)) }}</td>
                            <td>{{ $approval->note ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">No audit records yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<style>
    .leave-show-page { padding: 30px 35px; min-height: 100vh; background: linear-gradient(135deg, #f0f9f4, #f7fbff); }
    .show-head, .detail-card { border: 1px solid rgba(16,185,129,.12); background: rgba(255,255,255,.96); box-shadow: 0 16px 36px -20px rgba(15,23,42,.22); }
    .show-head { display: flex; justify-content: space-between; gap: 18px; align-items: center; padding: 28px; border-radius: 24px; margin-bottom: 18px; }
    .show-head span { color: #0f744c; font-weight: 900; }
    .show-head h1 { margin: 8px 0 6px; font-size: 34px; font-weight: 900; }
    .show-head p { margin: 0; color: #667085; font-weight: 650; }
    .leave-show-page .btn { display: inline-flex; align-items: center; gap: 8px; border-radius: 12px; font-weight: 900; }
    .leave-show-page .btn-light { background: #f0f9f4; color: #0f744c; border: 1px solid rgba(16,185,129,.18); }
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .detail-card { padding: 22px; border-radius: 20px; }
    .detail-card h2 { font-size: 18px; font-weight: 900; margin-bottom: 14px; }
    .detail-card dl { display: grid; grid-template-columns: 150px 1fr; gap: 10px 16px; margin: 0; }
    .detail-card dt { color: #667085; text-transform: uppercase; font-size: .75rem; }
    .detail-card dd { margin: 0; font-weight: 750; }
    .badge { padding: 7px 11px; border-radius: 999px; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-approved { background: #d1fae5; color: #047857; }
    .status-rejected { background: #fee2e2; color: #b91c1c; }
    @media (max-width: 768px) { .leave-show-page { padding: 18px; } .show-head, .detail-grid { grid-template-columns: 1fr; flex-direction: column; align-items: flex-start; } .detail-card dl { grid-template-columns: 1fr; } }
</style>
@endsection
