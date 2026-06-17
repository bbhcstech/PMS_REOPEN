@extends('admin.layout.app')

@php
    $isEdit = isset($leave);
    $isAdmin = auth()->user()->role === 'admin';
    $oldStartDate = old('start_date', $isEdit ? optional($leave->start_date)->format('Y-m-d') : '');
    $oldEndDate = old('end_date', $isEdit ? optional($leave->end_date)->format('Y-m-d') : '');
@endphp

@section('title', $isEdit ? 'Edit Leave Request' : 'Apply Leave')

@section('content')
<div class="leave-form-page">
    <div class="leave-breadcrumb"><i class="fas fa-calendar-plus"></i> Dashboard / Leaves / {{ $isEdit ? 'Edit' : 'Apply' }}</div>

    <section class="leave-form-hero">
        <div>
            <h1>{{ $isEdit ? 'Edit Leave Request' : 'Apply for Leave' }}</h1>
            <p>Submit leave requests with policy-aware validation for SL, CL, Maternity, and unpaid leave.</p>
        </div>
        <a href="{{ route('leaves.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back to Leaves</a>
    </section>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Please fix these issues:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <section class="policy-notice">
        <i class="fas fa-info-circle"></i>
        <p>{{ $policyNotice }}</p>
    </section>

    @if($balance)
        <section class="balance-strip">
            <div><span>Total</span><strong>{{ $balance->allocated_leaves }}</strong></div>
            <div><span>Remaining</span><strong>{{ $balance->remaining_leaves }}</strong></div>
            <div><span>SL Left</span><strong>{{ $balance->sick_allocated - $balance->sick_used }}</strong></div>
            <div><span>CL Left</span><strong>{{ $balance->casual_allocated - $balance->casual_used }}</strong></div>
            <div><span>ML Left</span><strong>{{ $balance->maternity_allocated - $balance->maternity_used }}</strong></div>
        </section>
    @endif

    <section class="form-card">
        <form method="POST" action="{{ $isEdit ? route('leaves.update', $leave->id) : route('leaves.store') }}" enctype="multipart/form-data" id="leaveForm">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="form-grid">
                @if($isAdmin)
                    <div>
                        <label>Employee <span>*</span></label>
                        <select name="user_id" class="form-control" required>
                            <option value="">Select Employee</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ (int) old('user_id', $selectedUser?->id ?? $leave->user_id ?? '') === $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div>
                        <label>Employee</label>
                        <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                    </div>
                @endif

                <div>
                    <label>Leave Type <span>*</span></label>
                    <select name="leave_type_id" id="leaveType" class="form-control" required>
                        <option value="">Select Type</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}" data-code="{{ $type->code }}" data-document="{{ $type->requires_document ? 1 : 0 }}" {{ (int) old('leave_type_id', $leave->leave_type_id ?? '') === $type->id ? 'selected' : '' }}>
                                {{ $type->name }} {{ $type->annual_limit > 0 ? '(' . $type->annual_limit . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($isAdmin)
                    <div>
                        <label>Initial Status</label>
                        <select name="status" class="form-control">
                            @foreach(['pending','approved'] as $status)
                                <option value="{{ $status }}" {{ old('status', $leave->status ?? 'pending') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label>Start Date <span>*</span></label>
                    <input type="date" name="start_date" id="startDate" class="form-control" value="{{ $oldStartDate }}" required>
                </div>
                <div>
                    <label>End Date <span>*</span></label>
                    <input type="date" name="end_date" id="endDate" class="form-control" value="{{ $oldEndDate }}" required>
                </div>
                <div>
                    <label>Total Days</label>
                    <input type="text" id="totalDays" class="form-control" value="{{ old('total_days', $leave->total_days ?? '1') }}" readonly>
                </div>
                <div>
                    <label>Contact During Leave</label>
                    <input type="text" name="contact_during_leave" class="form-control" value="{{ old('contact_during_leave', $leave->contact_during_leave ?? '') }}" placeholder="Phone/email for urgent contact">
                </div>
                <div>
                    <label>Attachment</label>
                    <input type="file" name="attachment" id="attachment" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    @if($isEdit && $leave->attachment_path)
                        <small><a href="{{ asset($leave->attachment_path) }}" target="_blank">Current attachment</a></small>
                    @endif
                </div>
            </div>

            <div class="check-row">
                <label><input type="checkbox" name="half_day_flag" id="halfDay" value="1" {{ old('half_day_flag', $leave->half_day_flag ?? false) ? 'checked' : '' }}> Half-day leave</label>
                <label><input type="checkbox" name="emergency_flag" value="1" {{ old('emergency_flag', $leave->emergency_flag ?? false) ? 'checked' : '' }}> Emergency request</label>
            </div>

            <div class="form-grid two">
                <div>
                    <label>Reason <span>*</span></label>
                    <textarea name="reason" class="form-control" rows="5" required>{{ old('reason', $leave->reason ?? '') }}</textarea>
                </div>
                <div>
                    <label>Apology / Regularization Note</label>
                    <textarea name="apology_note" class="form-control" rows="5" placeholder="Required for past Sick Leave when policy allows">{{ old('apology_note', $leave->apology_note ?? '') }}</textarea>
                </div>
            </div>

            @if($isAdmin)
                <div>
                    <label>Admin Note</label>
                    <textarea name="admin_note" class="form-control" rows="3">{{ old('admin_note', $leave->admin_note ?? '') }}</textarea>
                </div>
            @endif

            <div class="form-actions">
                <a href="{{ route('leaves.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                <button class="btn btn-primary"><i class="fas fa-paper-plane"></i> {{ $isEdit ? 'Update Leave' : 'Submit Request' }}</button>
            </div>
        </form>
    </section>
</div>

<style>
    .leave-form-page { padding: 30px 35px; min-height: 100vh; background: linear-gradient(135deg, #f0f9f4, #f7fbff); color: #102119; }
    .leave-breadcrumb, .leave-form-hero, .policy-notice, .balance-strip, .form-card { border: 1px solid rgba(16,185,129,.12); background: rgba(255,255,255,.96); box-shadow: 0 16px 36px -20px rgba(15,23,42,.22); }
    .leave-breadcrumb { display: inline-flex; gap: 8px; align-items: center; padding: 12px 18px; border-radius: 14px; color: #0f744c; font-weight: 900; margin-bottom: 22px; }
    .leave-form-hero { display: flex; justify-content: space-between; gap: 18px; align-items: center; padding: 28px; border-radius: 24px; margin-bottom: 20px; }
    .leave-form-hero h1 { margin: 0 0 6px; font-size: 34px; font-weight: 900; }
    .leave-form-hero p, .policy-notice p { margin: 0; color: #667085; font-weight: 650; }
    .leave-form-page .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: 12px; min-height: 44px; font-weight: 900; border: 0; }
    .leave-form-page .btn-primary { background: linear-gradient(145deg, #34d399, #059669); color: #fff; }
    .leave-form-page .btn-light, .leave-form-page .btn-secondary { background: #f0f9f4; color: #0f744c; border: 1px solid rgba(16,185,129,.18); }
    .policy-notice { display: flex; gap: 12px; padding: 16px; border-radius: 18px; margin-bottom: 20px; }
    .policy-notice i { width: 38px; height: 38px; display: grid; place-items: center; border-radius: 12px; background: #dbeafe; color: #2563eb; flex: 0 0 auto; }
    .balance-strip { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; padding: 18px; border-radius: 18px; margin-bottom: 20px; }
    .balance-strip div { padding: 12px; border-radius: 14px; background: #f8fafc; }
    .balance-strip span { display: block; color: #667085; font-size: .75rem; text-transform: uppercase; font-weight: 900; }
    .balance-strip strong { font-size: 26px; font-weight: 900; color: #0a2e1f; }
    .form-card { border-radius: 24px; padding: 24px; }
    .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px; }
    .form-grid.two { grid-template-columns: 1fr 1fr; }
    .leave-form-page label { display: block; color: #667085; text-transform: uppercase; font-size: .76rem; font-weight: 900; margin-bottom: 6px; }
    .leave-form-page label span { color: #dc2626; }
    .leave-form-page .form-control { min-height: 46px; border-radius: 12px; border: 1px solid #dbe7e1; font-weight: 650; }
    .check-row { display: flex; gap: 16px; flex-wrap: wrap; margin: 6px 0 18px; }
    .check-row label { display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border: 1px solid #dbe7e1; border-radius: 12px; text-transform: none; font-size: .9rem; color: #172033; cursor: pointer; }
    .form-actions { display: flex; justify-content: flex-end; gap: 12px; padding-top: 18px; margin-top: 18px; border-top: 1px solid rgba(16,185,129,.1); }
    @media (max-width: 992px) { .leave-form-page { padding: 18px; } .leave-form-hero { flex-direction: column; align-items: flex-start; } .form-grid, .form-grid.two, .balance-strip { grid-template-columns: 1fr; } }
</style>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const start = document.getElementById('startDate');
    const end = document.getElementById('endDate');
    const half = document.getElementById('halfDay');
    const total = document.getElementById('totalDays');
    const type = document.getElementById('leaveType');
    const attachment = document.getElementById('attachment');

    function calculateDays() {
        if (!start.value) return;
        if (!end.value || end.value < start.value) end.value = start.value;
        const s = new Date(start.value + 'T00:00:00');
        const e = new Date(end.value + 'T00:00:00');
        const diff = Math.floor((e - s) / 86400000) + 1;
        total.value = half.checked ? '0.5' : String(Math.max(1, diff));
        end.min = start.value;
    }

    function syncDocumentRequired() {
        const option = type.options[type.selectedIndex];
        attachment.required = option && option.dataset.document === '1';
    }

    [start, end, half].forEach(el => el && el.addEventListener('change', calculateDays));
    type && type.addEventListener('change', syncDocumentRequired);
    calculateDays();
    syncDocumentRequired();
});
</script>
@endpush
@endsection
