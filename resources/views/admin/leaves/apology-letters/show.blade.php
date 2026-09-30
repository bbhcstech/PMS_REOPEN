@extends('admin.layout.app')

@section('title', 'Apology Letter')

@section('content')
@php $isAdmin = in_array(strtolower((string) auth()->user()?->role), ['admin', 'hr'], true); @endphp
<div class="leave-show-page">
    <div class="show-head">
        <div>
            <h1>{{ $letter->subject }}</h1>
            <p>{{ $letter->user?->name ?? 'Employee' }} - {{ $letter->created_at?->format('d M Y h:i A') }}</p>
        </div>
        <div class="show-actions">
            <a href="{{ $letter->archived_at ? route('leaves.apology-letters.archive') : route('leaves.apology-letters.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back</a>
            @if($letter->archived_at)
                <form method="POST" action="{{ route('leaves.apology-letters.restore', $letter->id) }}" onsubmit="return confirm('Restore this apology letter?');">
                    @csrf
                    <button class="btn btn-primary" type="submit"><i class="fas fa-rotate-left"></i> Restore</button>
                </form>
            @else
                <form method="POST" action="{{ route('leaves.apology-letters.archive.action', $letter->id) }}" onsubmit="return confirm('Archive this apology letter?');">
                    @csrf
                    <button class="btn btn-light" type="submit"><i class="fas fa-box-archive"></i> Archive</button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="detail-grid">
        <section class="detail-card">
            <h2>Letter Details</h2>
            <dl>
                <dt>Employee</dt><dd>{{ $letter->user?->name ?? 'N/A' }}</dd>
                <dt>Status</dt><dd><span class="badge status-{{ $letter->status === 'submitted' ? 'pending' : ($letter->archived_at ? 'archived' : 'approved') }}">{{ ucfirst($letter->status) }}</span></dd>
                <dt>HR Email</dt><dd>{{ $letter->recipient_email ?: 'Not provided' }}</dd>
                <dt>Related Leave</dt><dd>{{ $letter->leave ? ($letter->leave->type_label . ' from ' . optional($letter->leave->start_date)->format('d M Y') . ' to ' . optional($letter->leave->end_date)->format('d M Y')) : 'Not linked' }}</dd>
                <dt>Reviewed By</dt><dd>{{ $letter->reviewer?->name ?? 'Not reviewed' }}</dd>
                <dt>Archived At</dt><dd>{{ $letter->archived_at?->format('d M Y h:i A') ?? 'Not archived' }}</dd>
            </dl>
        </section>

        @if($isAdmin)
            <section class="detail-card">
                <h2>HR Review</h2>
                <form method="POST" action="{{ route('leaves.apology-letters.review', $letter->id) }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="reviewed" {{ $letter->status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                            <option value="archived" {{ $letter->status === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Admin Note</label>
                        <textarea name="admin_note" class="form-control" rows="4">{{ old('admin_note', $letter->admin_note) }}</textarea>
                    </div>
                    <button class="btn btn-primary"><i class="fas fa-check"></i> Save Review</button>
                </form>
            </section>
        @endif
    </div>

    <section class="detail-card mt-3">
        <h2>Apology Letter</h2>
        <pre class="letter-body">{{ $letter->body }}</pre>
    </section>
</div>

<style>
    .leave-show-page { padding: 30px 35px; min-height: 100vh; background: linear-gradient(135deg, #F8FAFC, #f7fbff); }
    .show-head, .detail-card { border: 1px solid rgba(47, 107, 255, 0.12); background: rgba(255,255,255,.96); box-shadow: 0 16px 36px -20px rgba(15,23,42,.22); border-radius: 22px; }
    .show-head { display: flex; justify-content: space-between; gap: 16px; align-items: center; padding: 26px; margin-bottom: 18px; }
    .show-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
    .show-head h1 { margin: 0 0 6px; font-weight: 900; }
    .show-head p { margin: 0; color: #64748B; font-weight: 700; }
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .detail-card { padding: 22px; }
    .detail-card h2 { font-size: 20px; font-weight: 900; margin-bottom: 16px; }
    .detail-card dl { display: grid; grid-template-columns: 150px 1fr; gap: 12px; }
    .detail-card dt { color: #64748B; font-weight: 900; }
    .detail-card dd { margin: 0; font-weight: 750; }
    .letter-body { white-space: pre-wrap; font-family: inherit; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 14px; padding: 18px; }
    .btn { display: inline-flex; align-items: center; gap: 8px; border-radius: 12px; font-weight: 900; }
    .btn-light { background: #F8FAFC; color: #2F6BFF; border: 1px solid rgba(47, 107, 255, 0.18); }
    .btn-primary { background: linear-gradient(145deg, #4F83FF, #2F6BFF); border: 0; color: #fff; }
    .status-pending { background: #f59e0b; }
    .status-approved { background: #10b981; }
    .status-archived { background: #64748b; }
    @media (max-width: 768px) { .leave-show-page { padding: 18px; } .show-head, .detail-grid { grid-template-columns: 1fr; flex-direction: column; align-items: flex-start; } .detail-card dl { grid-template-columns: 1fr; } }

    /* Dark mode support */
    html[data-pms-theme="dark"] .leave-show-page,
    html[data-theme="dark"] .leave-show-page,
    html[data-bs-theme="dark"] .leave-show-page,
    body[data-pms-theme="dark"] .leave-show-page,
    body.dark-mode .leave-show-page {
        background: linear-gradient(135deg, #070B1A, #0F1530) !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .show-head,
    html[data-pms-theme="dark"] .detail-card,
    html[data-theme="dark"] .show-head,
    html[data-theme="dark"] .detail-card,
    html[data-bs-theme="dark"] .show-head,
    html[data-bs-theme="dark"] .detail-card,
    body[data-pms-theme="dark"] .show-head,
    body[data-pms-theme="dark"] .detail-card,
    body.dark-mode .show-head,
    body.dark-mode .detail-card {
        background: rgba(15, 21, 48, 0.95) !important;
        border-color: rgba(79, 131, 255, 0.15) !important;
        box-shadow: 0 16px 36px -20px rgba(0, 0, 0, 0.5) !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .show-head h1,
    html[data-pms-theme="dark"] .detail-card h2,
    html[data-theme="dark"] .show-head h1,
    html[data-theme="dark"] .detail-card h2,
    html[data-bs-theme="dark"] .show-head h1,
    html[data-bs-theme="dark"] .detail-card h2,
    body[data-pms-theme="dark"] .show-head h1,
    body[data-pms-theme="dark"] .detail-card h2,
    body.dark-mode .show-head h1,
    body.dark-mode .detail-card h2 {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .show-head p,
    html[data-pms-theme="dark"] .detail-card dt,
    html[data-theme="dark"] .show-head p,
    html[data-theme="dark"] .detail-card dt,
    html[data-bs-theme="dark"] .show-head p,
    html[data-bs-theme="dark"] .detail-card dt,
    body[data-pms-theme="dark"] .show-head p,
    body[data-pms-theme="dark"] .detail-card dt,
    body.dark-mode .show-head p,
    body.dark-mode .detail-card dt {
        color: #94A3B8 !important;
    }
    html[data-pms-theme="dark"] .letter-body,
    html[data-theme="dark"] .letter-body,
    html[data-bs-theme="dark"] .letter-body,
    body[data-pms-theme="dark"] .letter-body,
    body.dark-mode .letter-body {
        background: #0F1530 !important;
        border-color: rgba(79, 131, 255, 0.2) !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .btn-light,
    html[data-theme="dark"] .btn-light,
    html[data-bs-theme="dark"] .btn-light,
    body[data-pms-theme="dark"] .btn-light,
    body.dark-mode .btn-light {
        background: #141B3D !important;
        color: #EEF1FB !important;
        border-color: rgba(79, 131, 255, 0.2) !important;
    }
</style>
@endsection
