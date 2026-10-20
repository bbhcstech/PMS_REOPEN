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
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if($isAdmin)
                <form method="POST" action="{{ route('leaves.updateStatus', $leave->id) }}" class="d-inline" onsubmit="return confirm('Approve this leave request?');">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="btn btn-success" title="Approve Leave"><i class="fas fa-check me-1"></i> Approve</button>
                </form>
                <form method="POST" action="{{ route('leaves.updateStatus', $leave->id) }}" class="d-inline" onsubmit="return confirm('Convert this leave request to unpaid leave?');">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="unpaid">
                    <button type="submit" class="btn btn-warning text-dark" title="Convert to Unpaid"><i class="fas fa-wallet me-1"></i> Convert to Unpaid</button>
                </form>
                <button type="button" class="btn btn-danger btn-reject-trigger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $leave->id }}" data-toggle="modal" data-target="#rejectModal{{ $leave->id }}" title="Reject Leave"><i class="fas fa-times me-1"></i> Reject</button>
            @endif
            <a href="{{ route('leaves.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Back</a>
        </div>
    </div>

    <div class="detail-grid">
        <section class="detail-card">
            <h2>Request Details</h2>
            <dl>
                <dt>Status</dt><dd><span class="badge status-{{ $leave->status }}">{{ ucfirst($leave->status) }}</span></dd>
                <dt>Payment</dt><dd>{{ $leave->status === 'rejected' ? 'N/A (Rejected)' : ($leave->status === 'pending' ? 'Pending Approval' : ($leave->is_unpaid ? 'Unpaid Leave / Payroll Deduction' : 'Paid Leave')) }}</dd>
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

        <section class="detail-card">
            <h2>Apology Letters</h2>
            @forelse($leave->apologyLetters as $letter)
                <div class="approval-row">
                    <div>
                        <strong>{{ $letter->subject }}</strong>
                        <small>{{ $letter->created_at?->format('d M Y h:i A') }} - {{ ucfirst($letter->status) }}</small>
                    </div>
                    <a href="{{ route('leaves.apology-letters.show', $letter->id) }}" class="btn btn-light btn-sm">Open</a>
                </div>
            @empty
                <p>No apology letter submitted for this leave.</p>
            @endforelse
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
    .leave-show-page {
        padding: 30px 35px;
        min-height: 100vh;
        background: var(--bx-bg, linear-gradient(135deg, #F8FAFC, #f7fbff));
        color: var(--bx-ink, #0F1530);
        transition: background 0.25s ease, color 0.25s ease;
    }
    .show-head, .detail-card {
        border: 1px solid var(--bx-border, rgba(16,185,129,.12));
        background: var(--bx-surface, rgba(255,255,255,.96));
        box-shadow: var(--bx-shadow-sm, 0 16px 36px -20px rgba(15,23,42,.12));
        transition: background 0.25s ease, border-color 0.25s ease, color 0.25s ease;
    }
    .show-head {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: center;
        padding: 28px;
        border-radius: 24px;
        margin-bottom: 18px;
    }
    .show-head span {
        color: #2F6BFF;
        font-weight: 800;
        font-size: 0.9rem;
    }
    .show-head h1 {
        margin: 8px 0 6px;
        font-size: 32px;
        font-weight: 900;
        color: var(--bx-ink, #0F1530);
    }
    .show-head p {
        margin: 0;
        color: var(--bx-ink-muted, #667085);
        font-weight: 600;
    }
    .leave-show-page .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 12px;
        font-weight: 700;
        padding: 0.5rem 1.15rem;
        min-height: 40px;
    }
    .leave-show-page .btn-light {
        background: var(--bx-surface-2, #F8FAFC);
        color: var(--bx-ink, #2F6BFF);
        border: 1px solid var(--bx-border, rgba(16,185,129,.18));
    }
    .leave-show-page .btn-light:hover {
        background: var(--bx-surface-3, #e2f4ea);
        color: var(--bx-primary, #10B981);
    }
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }
    .detail-card {
        padding: 24px;
        border-radius: 20px;
    }
    .detail-card h2 {
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 16px;
        color: var(--bx-ink, #0F1530);
    }
    .detail-card p {
        color: var(--bx-ink-body, #334155);
    }
    .detail-card dl {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 12px 16px;
        margin: 0;
    }
    .detail-card dt {
        color: var(--bx-ink-muted, #667085);
        text-transform: uppercase;
        font-size: .75rem;
        font-weight: 800;
        letter-spacing: 0.04em;
    }
    .detail-card dd {
        margin: 0;
        font-weight: 700;
        color: var(--bx-ink, #0F1530);
    }
    .approval-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        border-radius: 12px;
        background: var(--bx-surface-2, #f8fafc);
        border: 1px solid var(--bx-border, rgba(16,185,129,.1));
        margin-bottom: 10px;
    }
    .approval-row strong {
        display: block;
        color: var(--bx-ink, #0F1530);
    }
    .approval-row small {
        color: var(--bx-ink-muted, #667085);
    }
    .badge {
        padding: 7px 14px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.8rem;
    }
    .status-pending { background: rgba(245, 158, 11, 0.15); color: #b45309; }
    .status-approved { background: rgba(47, 107, 255, 0.15); color: #2F6BFF; }
    .status-rejected { background: rgba(239, 68, 68, 0.15); color: #b91c1c; }

    .detail-card table.table {
        color: var(--bx-ink, #0F1530);
        border-color: var(--bx-border, rgba(47,107,255,.12));
    }
    .detail-card table.table th {
        background: var(--bx-surface-2, #f8fafc);
        color: var(--bx-ink-muted, #667085);
        border-bottom-color: var(--bx-border, rgba(47,107,255,.12));
        font-weight: 800;
        font-size: 0.8rem;
        text-transform: uppercase;
    }
    .detail-card table.table td {
        color: var(--bx-ink, #0F1530);
        border-bottom-color: var(--bx-border, rgba(16,185,129,.08));
    }

    @media (max-width: 768px) {
        .leave-show-page { padding: 18px; }
        .show-head, .detail-grid { grid-template-columns: 1fr; flex-direction: column; align-items: flex-start; }
        .detail-card dl { grid-template-columns: 1fr; }
    }

    /* Dark Mode Overrides */
    html[data-pms-theme="dark"] .leave-show-page,
    html[data-theme="dark"] .leave-show-page {
        background: #070B1A !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .show-head,
    html[data-pms-theme="dark"] .detail-card,
    html[data-theme="dark"] .show-head,
    html[data-theme="dark"] .detail-card {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35) !important;
    }
    html[data-pms-theme="dark"] .show-head span,
    html[data-theme="dark"] .show-head span {
        color: #60A5FA !important;
    }
    html[data-pms-theme="dark"] .show-head h1,
    html[data-theme="dark"] .show-head h1,
    html[data-pms-theme="dark"] .detail-card h2,
    html[data-theme="dark"] .detail-card h2 {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .show-head p,
    html[data-pms-theme="dark"] .detail-card dt,
    html[data-pms-theme="dark"] .approval-row small,
    html[data-theme="dark"] .show-head p,
    html[data-theme="dark"] .detail-card dt,
    html[data-theme="dark"] .approval-row small {
        color: #9AA3C7 !important;
    }
    html[data-pms-theme="dark"] .detail-card dd,
    html[data-pms-theme="dark"] .detail-card p,
    html[data-pms-theme="dark"] .approval-row strong,
    html[data-theme="dark"] .detail-card dd,
    html[data-theme="dark"] .detail-card p,
    html[data-theme="dark"] .approval-row strong {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .approval-row,
    html[data-theme="dark"] .approval-row {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
    }
    html[data-pms-theme="dark"] .leave-show-page .btn-light,
    html[data-theme="dark"] .leave-show-page .btn-light {
        background: #141B3D !important;
        color: #EEF1FB !important;
        border-color: rgba(238, 241, 251, 0.14) !important;
    }
    html[data-pms-theme="dark"] .leave-show-page .btn-light:hover,
    html[data-theme="dark"] .leave-show-page .btn-light:hover {
        background: #1A2247 !important;
        color: #60A5FA !important;
        border-color: #60A5FA !important;
    }
    html[data-pms-theme="dark"] .status-pending,
    html[data-theme="dark"] .status-pending {
        background: rgba(245, 158, 11, 0.2) !important;
        color: #FBBF24 !important;
    }
    html[data-pms-theme="dark"] .status-approved,
    html[data-theme="dark"] .status-approved {
        background: rgba(47, 107, 255, 0.2) !important;
        color: #60A5FA !important;
    }
    html[data-pms-theme="dark"] .status-rejected,
    html[data-theme="dark"] .status-rejected {
        background: rgba(239, 68, 68, 0.2) !important;
        color: #F87171 !important;
    }
    html[data-pms-theme="dark"] .detail-card table.table,
    html[data-theme="dark"] .detail-card table.table {
        color: #EEF1FB !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
    }
    html[data-pms-theme="dark"] .detail-card table.table th,
    html[data-theme="dark"] .detail-card table.table th {
        background: #141B3D !important;
        color: #9AA3C7 !important;
        border-bottom-color: rgba(238, 241, 251, 0.12) !important;
    }
    html[data-pms-theme="dark"] .detail-card table.table td,
    html[data-theme="dark"] .detail-card table.table td {
        color: #EEF1FB !important;
        border-bottom-color: rgba(238, 241, 251, 0.08) !important;
    }
</style>

@if($isAdmin)
<div class="modal fade" id="rejectModal{{ $leave->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $leave->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('leaves.updateStatus', $leave->id) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="rejected">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel{{ $leave->id }}"><i class="fas fa-times-circle text-danger me-2"></i>Reject Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Are you sure you want to reject the leave request for <strong>{{ $leave->user?->name ?? 'Employee' }}</strong> ({{ number_format((float) $leave->total_days, 1) }} days)?</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Rejection Reason <small class="text-muted fw-normal">(optional)</small></label>
                    <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Provide a reason for rejecting this leave request..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-times me-1"></i> Confirm Reject</button>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
$(function () {
    $(document).on('click', '.btn-reject-trigger', function (e) {
        e.preventDefault();
        var targetId = $(this).data('bs-target') || $(this).data('target');
        if (targetId) {
            var modalEl = document.querySelector(targetId);
            if (modalEl) {
                if (window.bootstrap && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                } else if (window.jQuery && $(modalEl).modal) {
                    $(modalEl).modal('show');
                }
            }
        }
    });
});
</script>
@endpush
@endif

@endsection
