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
    .leave-show-page { padding: 30px 35px; min-height: 100vh; background: linear-gradient(135deg, #f0f9f4, #f7fbff); }
    .show-head, .detail-card { border: 1px solid rgba(16,185,129,.12); background: rgba(255,255,255,.96); box-shadow: 0 16px 36px -20px rgba(15,23,42,.22); border-radius: 22px; }
    .show-head { display: flex; justify-content: space-between; gap: 16px; align-items: center; padding: 26px; margin-bottom: 18px; }
    .show-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
    .show-head h1 { margin: 0 0 6px; font-weight: 900; }
    .show-head p { margin: 0; color: #667085; font-weight: 700; }
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .detail-card { padding: 22px; }
    .detail-card h2 { font-size: 20px; font-weight: 900; margin-bottom: 16px; }
    .detail-card dl { display: grid; grid-template-columns: 150px 1fr; gap: 12px; }
    .detail-card dt { color: #667085; font-weight: 900; }
    .detail-card dd { margin: 0; font-weight: 750; }
    .letter-body { white-space: pre-wrap; font-family: inherit; background: #f8fffb; border: 1px solid #dbe7e1; border-radius: 14px; padding: 18px; }
    .btn { display: inline-flex; align-items: center; gap: 8px; border-radius: 12px; font-weight: 900; }
    .btn-light { background: #f0f9f4; color: #0f744c; border: 1px solid rgba(16,185,129,.18); }
    .btn-primary { background: linear-gradient(145deg, #34d399, #059669); border: 0; color: #fff; }
    .status-pending { background: #f59e0b; }
    .status-approved { background: #10b981; }
    .status-archived { background: #64748b; }
    @media (max-width: 768px) { .leave-show-page { padding: 18px; } .show-head, .detail-grid { grid-template-columns: 1fr; flex-direction: column; align-items: flex-start; } .detail-card dl { grid-template-columns: 1fr; } }
</style>
@endsection
