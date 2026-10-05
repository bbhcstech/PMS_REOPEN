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
                    <div id="endDateError" class="end-date-error-msg">
                        <i class="fas fa-exclamation-circle"></i> <span>End date can't be backdated. Please select a date on or after start date.</span>
                    </div>
                </div>
                <div>
                    <label>Total Days</label>
                    <input type="text" id="totalDays" class="form-control" value="{{ old('total_days', $leave->total_days ?? '') }}" readonly placeholder="0">
                </div>
                <div>
                    <label>Contact During Leave <span style="color:#6b7280;font-weight:600;font-size:.7rem;text-transform:none;">(with country code)</span></label>
                    {{-- hidden field submitted to server in E.164 format --}}
                    <input type="hidden" name="contact_during_leave" id="contactDuringLeaveHidden"
                           value="{{ old('contact_during_leave', $leave->contact_during_leave ?? '') }}">
                    <div class="phone-input-wrapper" style="position:relative;">
                        <input type="tel" id="contactDuringLeavePhone" class="form-control"
                               placeholder="98765 43210"
                               autocomplete="tel"
                               style="padding-left:90px;">
                        <div id="phoneValidIcon" style="display:none;position:absolute;right:12px;top:50%;transform:translateY(-50%);font-size:1.1rem;"></div>
                    </div>
                    <small id="phoneError" class="text-danger" style="display:none;font-size:.8rem;margin-top:4px;"></small>
                    <small class="text-muted" style="font-size:.72rem;">Enter number with country code, e.g. +91 98765 43210</small>
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
                <button type="submit" id="leaveSubmitBtn" class="btn btn-primary"><i class="fas fa-paper-plane"></i> {{ $isEdit ? 'Update Leave' : 'Submit Request' }}</button>
            </div>
        </form>
    </section>
</div>

<style>
    .leave-form-page {
        padding: 30px 35px;
        min-height: 100vh;
        background: var(--bx-bg, linear-gradient(135deg, #F8FAFC, #f7fbff));
        color: var(--bx-ink, #0F1530);
        transition: background 0.25s ease, color 0.25s ease;
    }
    .leave-breadcrumb, .leave-form-hero, .policy-notice, .balance-strip, .form-card {
        border: 1px solid var(--bx-border, rgba(16,185,129,.12));
        background: var(--bx-surface, rgba(255,255,255,.96));
        box-shadow: var(--bx-shadow-sm, 0 16px 36px -20px rgba(15,23,42,.12));
        transition: background 0.25s ease, border-color 0.25s ease, color 0.25s ease;
    }
    .leave-breadcrumb {
        display: inline-flex;
        gap: 8px;
        align-items: center;
        padding: 12px 18px;
        border-radius: 14px;
        color: #2F6BFF;
        font-weight: 800;
        margin-bottom: 22px;
    }
    .leave-form-hero {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: center;
        padding: 28px;
        border-radius: 24px;
        margin-bottom: 20px;
    }
    .leave-form-hero h1 {
        margin: 0 0 6px;
        font-size: 32px;
        font-weight: 900;
        color: var(--bx-ink, #0F1530);
    }
    .leave-form-hero p, .policy-notice p {
        margin: 0;
        color: var(--bx-ink-muted, #667085);
        font-weight: 600;
    }
    .leave-form-page .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 12px;
        min-height: 44px;
        font-weight: 700;
        border: 0;
        padding: 0 1.25rem;
    }
    .leave-form-page .btn-primary {
        background: linear-gradient(145deg, #4F83FF, #2F6BFF);
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(47, 107, 255, 0.3);
    }
    .leave-form-page .btn-primary:hover {
        background: linear-gradient(145deg, #1E4FCC, #2F6BFF);
        box-shadow: 0 6px 18px rgba(47, 107, 255, 0.4);
    }
    .leave-form-page .btn-light, .leave-form-page .btn-secondary {
        background: var(--bx-surface-2, #F8FAFC);
        color: var(--bx-ink, #2F6BFF);
        border: 1px solid var(--bx-border, rgba(47,107,255,.18));
    }
    .leave-form-page .btn-light:hover, .leave-form-page .btn-secondary:hover {
        background: var(--bx-surface-3, #EEF2FF);
        color: var(--bx-primary, #2F6BFF);
    }
    .policy-notice {
        display: flex;
        gap: 12px;
        align-items: center;
        padding: 16px;
        border-radius: 18px;
        margin-bottom: 20px;
    }
    .policy-notice i {
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: rgba(37, 99, 235, 0.12);
        color: #2563eb;
        flex: 0 0 auto;
    }
    .balance-strip {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
        padding: 18px;
        border-radius: 18px;
        margin-bottom: 20px;
    }
    .balance-strip div {
        padding: 12px 14px;
        border-radius: 14px;
        background: var(--bx-surface-2, #f8fafc);
        border: 1px solid var(--bx-border, rgba(47,107,255,.08));
    }
    .balance-strip span {
        display: block;
        color: var(--bx-ink-muted, #667085);
        font-size: .75rem;
        text-transform: uppercase;
        font-weight: 800;
        margin-bottom: 4px;
    }
    .balance-strip strong {
        font-size: 24px;
        font-weight: 900;
        color: var(--bx-ink, #0F172A);
    }
    .form-card {
        border-radius: 24px;
        padding: 28px;
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
        margin-bottom: 18px;
    }
    .form-grid.two {
        grid-template-columns: 1fr 1fr;
    }
    .leave-form-page label {
        display: block;
        color: var(--bx-ink-muted, #667085);
        text-transform: uppercase;
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        margin-bottom: 8px;
    }
    .leave-form-page label span {
        color: #ef4444;
    }
    .leave-form-page .form-control {
        min-height: 46px;
        border-radius: 12px;
        border: 1px solid var(--bx-border, #E2E8F0);
        background: var(--bx-surface, #ffffff);
        color: var(--bx-ink, #10142C);
        font-weight: 600;
        padding: 0.6rem 0.85rem;
        transition: border-color 0.2s ease, background 0.2s ease, color 0.2s ease;
    }
    .leave-form-page .form-control:focus {
        border-color: #2F6BFF;
        box-shadow: 0 0 0 3px rgba(47, 107, 255, 0.18);
    }
    .check-row {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        margin: 8px 0 20px;
    }
    .check-row label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border: 1px solid var(--bx-border, #E2E8F0);
        background: var(--bx-surface-2, #ffffff);
        border-radius: 12px;
        text-transform: none;
        font-size: .9rem;
        color: var(--bx-ink, #172033);
        cursor: pointer;
        font-weight: 600;
    }
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding-top: 20px;
        margin-top: 20px;
        border-top: 1px solid var(--bx-border, rgba(47,107,255,.1));
    }
    @media (max-width: 992px) {
        .leave-form-page { padding: 18px; }
        .leave-form-hero { flex-direction: column; align-items: flex-start; }
        .form-grid, .form-grid.two, .balance-strip { grid-template-columns: 1fr; }
    }

    /* Dark Mode Overrides */
    html[data-pms-theme="dark"] .leave-form-page,
    html[data-theme="dark"] .leave-form-page {
        background: #070B1A !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .leave-breadcrumb,
    html[data-pms-theme="dark"] .leave-form-hero,
    html[data-pms-theme="dark"] .policy-notice,
    html[data-pms-theme="dark"] .balance-strip,
    html[data-pms-theme="dark"] .form-card,
    html[data-theme="dark"] .leave-breadcrumb,
    html[data-theme="dark"] .leave-form-hero,
    html[data-theme="dark"] .policy-notice,
    html[data-theme="dark"] .balance-strip,
    html[data-theme="dark"] .form-card {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35) !important;
    }
    html[data-pms-theme="dark"] .leave-breadcrumb,
    html[data-theme="dark"] .leave-breadcrumb {
        color: #60A5FA !important;
    }
    html[data-pms-theme="dark"] .leave-form-hero h1,
    html[data-theme="dark"] .leave-form-hero h1 {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .leave-form-hero p,
    html[data-pms-theme="dark"] .policy-notice p,
    html[data-theme="dark"] .leave-form-hero p,
    html[data-theme="dark"] .policy-notice p,
    html[data-bs-theme="dark"] .policy-notice p,
    [data-pms-theme="dark"] .policy-notice p,
    [data-theme="dark"] .policy-notice p,
    body.dark-mode .policy-notice p {
        color: #9AA3C7 !important;
        -webkit-text-fill-color: #9AA3C7 !important;
    }
    html[data-pms-theme="dark"] .policy-notice i,
    html[data-theme="dark"] .policy-notice i,
    html[data-bs-theme="dark"] .policy-notice i,
    [data-pms-theme="dark"] .policy-notice i,
    [data-theme="dark"] .policy-notice i,
    body.dark-mode .policy-notice i {
        background: rgba(37, 99, 235, 0.25) !important;
        border: 1px solid rgba(147, 197, 253, 0.35) !important;
        color: #60a5fa !important;
        -webkit-text-fill-color: #60a5fa !important;
    }
    html[data-pms-theme="dark"] .balance-strip div,
    html[data-theme="dark"] .balance-strip div {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
    }
    html[data-pms-theme="dark"] .balance-strip span,
    html[data-theme="dark"] .balance-strip span {
        color: #9AA3C7 !important;
    }
    html[data-pms-theme="dark"] .balance-strip strong,
    html[data-theme="dark"] .balance-strip strong {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .leave-form-page label,
    html[data-theme="dark"] .leave-form-page label {
        color: #9AA3C7 !important;
    }
    html[data-pms-theme="dark"] .leave-form-page .form-control,
    html[data-theme="dark"] .leave-form-page .form-control {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.14) !important;
        color: #EEF1FB !important;
        color-scheme: dark;
    }
    html[data-pms-theme="dark"] .leave-form-page .form-control option,
    html[data-theme="dark"] .leave-form-page .form-control option {
        background: #0F1530 !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .leave-form-page .form-control:focus,
    html[data-theme="dark"] .leave-form-page .form-control:focus {
        border-color: #60A5FA !important;
        box-shadow: 0 0 0 3px rgba(79, 131, 255, 0.25) !important;
    }
    html[data-pms-theme="dark"] .check-row label,
    html[data-theme="dark"] .check-row label {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.14) !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .leave-form-page .btn-light,
    html[data-pms-theme="dark"] .leave-form-page .btn-secondary,
    html[data-theme="dark"] .leave-form-page .btn-light,
    html[data-theme="dark"] .leave-form-page .btn-secondary {
        background: #141B3D !important;
        color: #EEF1FB !important;
        border-color: rgba(238, 241, 251, 0.14) !important;
    }
    html[data-pms-theme="dark"] .leave-form-page .btn-light:hover,
    html[data-pms-theme="dark"] .leave-form-page .btn-secondary:hover,
    html[data-theme="dark"] .leave-form-page .btn-light:hover,
    html[data-theme="dark"] .leave-form-page .btn-secondary:hover {
        background: #1A2247 !important;
        color: #60A5FA !important;
        border-color: #60A5FA !important;
    }

    /* ===== INTL-TEL-INPUT OVERRIDES ===== */
    .iti { width: 100%; }
    .iti__flag-container { z-index: 10; }
    #contactDuringLeavePhone.iti__tel-input {
        padding-left: 90px !important;
    }
    #contactDuringLeavePhone.phone-valid {
        border-color: #22c55e !important;
        box-shadow: 0 0 0 3px rgba(34,197,94,0.15) !important;
    }
    #contactDuringLeavePhone.phone-invalid {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239,68,68,0.15) !important;
    }

    /* ── Dark mode: flag/dial-code button ── */
    html[data-pms-theme="dark"] .iti__selected-dial-code,
    html[data-theme="dark"] .iti__selected-dial-code,
    html[data-bs-theme="dark"] .iti__selected-dial-code,
    [data-pms-theme="dark"] .iti__selected-dial-code,
    [data-theme="dark"] .iti__selected-dial-code,
    body.dark-mode .iti__selected-dial-code {
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .iti__selected-country,
    html[data-theme="dark"] .iti__selected-country,
    html[data-bs-theme="dark"] .iti__selected-country,
    [data-pms-theme="dark"] .iti__selected-country,
    [data-theme="dark"] .iti__selected-country,
    body.dark-mode .iti__selected-country {
        background: #141B3D !important;
        border-right: 1px solid rgba(238,241,251,0.12) !important;
    }

    html[data-pms-theme="dark"] .iti__selected-country:hover,
    html[data-theme="dark"] .iti__selected-country:hover,
    body.dark-mode .iti__selected-country:hover {
        background: #1A2247 !important;
    }

    /* ── Dark mode: dropdown container ── */
    html[data-pms-theme="dark"] .iti__dropdown-content,
    html[data-theme="dark"] .iti__dropdown-content,
    html[data-bs-theme="dark"] .iti__dropdown-content,
    [data-pms-theme="dark"] .iti__dropdown-content,
    [data-theme="dark"] .iti__dropdown-content,
    body.dark-mode .iti__dropdown-content,
    html[data-pms-theme="dark"] .iti__country-list,
    html[data-theme="dark"] .iti__country-list,
    html[data-bs-theme="dark"] .iti__country-list,
    [data-pms-theme="dark"] .iti__country-list,
    [data-theme="dark"] .iti__country-list,
    body.dark-mode .iti__country-list {
        background: #0F1530 !important;
        border: 1px solid rgba(238,241,251,0.12) !important;
        box-shadow: 0 8px 30px rgba(0,0,0,0.5) !important;
    }

    /* ── Dark mode: search box ── */
    html[data-pms-theme="dark"] .iti__search-input,
    html[data-theme="dark"] .iti__search-input,
    html[data-bs-theme="dark"] .iti__search-input,
    [data-pms-theme="dark"] .iti__search-input,
    [data-theme="dark"] .iti__search-input,
    body.dark-mode .iti__search-input {
        background: #141B3D !important;
        border: 1px solid rgba(238,241,251,0.15) !important;
        color: #EEF1FB !important;
        border-radius: 8px !important;
    }
    html[data-pms-theme="dark"] .iti__search-input::placeholder,
    html[data-theme="dark"] .iti__search-input::placeholder,
    body.dark-mode .iti__search-input::placeholder {
        color: #5A6490 !important;
    }
    html[data-pms-theme="dark"] .iti__search-input:focus,
    html[data-theme="dark"] .iti__search-input:focus,
    body.dark-mode .iti__search-input:focus {
        border-color: #60A5FA !important;
        outline: none !important;
        box-shadow: 0 0 0 2px rgba(96,165,250,0.2) !important;
    }

    /* ── Dark mode: country list items ── */
    html[data-pms-theme="dark"] .iti__country,
    html[data-theme="dark"] .iti__country,
    html[data-bs-theme="dark"] .iti__country,
    [data-pms-theme="dark"] .iti__country,
    [data-theme="dark"] .iti__country,
    body.dark-mode .iti__country {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .iti__country:hover,
    html[data-theme="dark"] .iti__country:hover,
    body.dark-mode .iti__country:hover {
        background: #1A2247 !important;
    }
    html[data-pms-theme="dark"] .iti__country.iti__highlight,
    html[data-theme="dark"] .iti__country.iti__highlight,
    body.dark-mode .iti__country.iti__highlight {
        background: #1E2A52 !important;
    }
    html[data-pms-theme="dark"] .iti__dial-code,
    html[data-theme="dark"] .iti__dial-code,
    body.dark-mode .iti__dial-code {
        color: #9AA3C7 !important;
    }
    html[data-pms-theme="dark"] .iti__country-name,
    html[data-theme="dark"] .iti__country-name,
    body.dark-mode .iti__country-name {
        color: #EEF1FB !important;
    }

    /* ── Dark mode: divider ── */
    html[data-pms-theme="dark"] .iti__divider,
    html[data-theme="dark"] .iti__divider,
    body.dark-mode .iti__divider {
        border-color: rgba(238,241,251,0.1) !important;
    }

    /* ── Dark mode: the tel input itself ── */
    html[data-pms-theme="dark"] #contactDuringLeavePhone,
    html[data-theme="dark"] #contactDuringLeavePhone,
    body.dark-mode #contactDuringLeavePhone {
        background: #141B3D !important;
        color: #EEF1FB !important;
        border-color: rgba(238,241,251,0.14) !important;
        color-scheme: dark;
    }
    html[data-pms-theme="dark"] #contactDuringLeavePhone::placeholder,
    html[data-theme="dark"] #contactDuringLeavePhone::placeholder,
    body.dark-mode #contactDuringLeavePhone::placeholder {
        color: #5A6490 !important;
    }

    /* ── End Date Backdated Error Message ── */
    .end-date-error-msg {
        display: none;
        color: #ef4444 !important;
        font-size: 0.84rem;
        font-weight: 700;
        margin-top: 6px;
        padding: 7px 12px;
        background: rgba(239, 68, 68, 0.12);
        border: 1px solid rgba(239, 68, 68, 0.35);
        border-radius: 8px;
        align-items: center;
        gap: 8px;
    }
    .end-date-error-msg i {
        color: #ef4444 !important;
        font-size: 0.95rem;
        flex-shrink: 0;
    }
</style>

{{-- intl-tel-input CSS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23/build/css/intlTelInput.css">


@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const start = document.getElementById('startDate');
    const end = document.getElementById('endDate');
    const half = document.getElementById('halfDay');
    const total = document.getElementById('totalDays');
    const type = document.getElementById('leaveType');
    const attachment = document.getElementById('attachment');
    const endDateError = document.getElementById('endDateError');
    const leaveForm = document.getElementById('leaveForm');
    const submitBtn = document.getElementById('leaveSubmitBtn') || (leaveForm ? leaveForm.querySelector('button[type="submit"], button.btn-primary') : null);

    let isDateInvalid = false;

    function parseDate(val) {
        if (!val || typeof val !== 'string') return null;
        val = val.trim();
        if (!val) return null;

        // YYYY-MM-DD
        const ymd = val.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
        if (ymd) {
            return new Date(parseInt(ymd[1], 10), parseInt(ymd[2], 10) - 1, parseInt(ymd[3], 10));
        }
        // DD-MM-YYYY
        const dmy = val.match(/^(\d{1,2})[-/.](\d{1,2})[-/.](\d{4})$/);
        if (dmy) {
            return new Date(parseInt(dmy[3], 10), parseInt(dmy[2], 10) - 1, parseInt(dmy[1], 10));
        }
        const d = new Date(val);
        return isNaN(d.getTime()) ? null : d;
    }

    function getDateFromInput(el) {
        if (!el) return null;
        if (el.valueAsDate && !isNaN(el.valueAsDate.getTime())) {
            return new Date(el.valueAsDate.getUTCFullYear(), el.valueAsDate.getUTCMonth(), el.valueAsDate.getUTCDate());
        }
        return parseDate(el.value);
    }

    function showDateError(msg) {
        isDateInvalid = true;
        if (endDateError) {
            const span = endDateError.querySelector('span');
            if (span) span.textContent = msg;
            else endDateError.textContent = msg;
            endDateError.style.setProperty('display', 'flex', 'important');
        }
        if (end) {
            end.classList.add('is-invalid');
            end.style.setProperty('border-color', '#ef4444', 'important');
            end.style.setProperty('box-shadow', '0 0 0 3px rgba(239, 68, 68, 0.25)', 'important');
            end.setCustomValidity(msg);
        }
        if (total) {
            total.value = 'Invalid (Backdated)';
            total.style.setProperty('color', '#ef4444', 'important');
            total.style.setProperty('font-weight', '700', 'important');
        }
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.style.setProperty('opacity', '0.5', 'important');
            submitBtn.style.setProperty('cursor', 'not-allowed', 'important');
            submitBtn.setAttribute('title', msg);
        }
    }

    function clearDateError() {
        isDateInvalid = false;
        if (endDateError) {
            endDateError.style.setProperty('display', 'none', 'important');
        }
        if (end) {
            end.classList.remove('is-invalid');
            end.style.removeProperty('border-color');
            end.style.removeProperty('box-shadow');
            end.setCustomValidity('');
        }
        if (total) {
            total.style.removeProperty('color');
            total.style.removeProperty('font-weight');
        }
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.style.removeProperty('opacity');
            submitBtn.style.removeProperty('cursor');
            submitBtn.removeAttribute('title');
        }
    }

    function validateAndCalculate() {
        if (half && half.checked) {
            if (start && start.value) {
                end.value = start.value;
            }
            if (end) {
                end.readOnly = true;
                end.style.opacity = '0.7';
                end.style.cursor = 'not-allowed';
            }
            if (total) {
                total.value = '0.5';
                total.style.removeProperty('color');
                total.style.removeProperty('font-weight');
            }
            clearDateError();
            return true;
        } else if (end) {
            end.readOnly = false;
            end.style.opacity = '1';
            end.style.cursor = '';
        }

        const startDate = getDateFromInput(start);
        const endDate = getDateFromInput(end);

        if (!startDate || !endDate) {
            if (!endDate && total) {
                total.value = '';
            }
            clearDateError();
            return false;
        }

        // Check if End Date is before Start Date (Backdated)
        if (endDate.getTime() < startDate.getTime()) {
            showDateError("End date can't be backdated. Please select a date on or after start date.");
            return false;
        }

        // Valid date range!
        clearDateError();
        const diffMs = endDate.getTime() - startDate.getTime();
        const diffDays = Math.floor(diffMs / 86400000) + 1;
        if (total) {
            total.value = String(Math.max(1, diffDays));
        }
        return true;
    }

    function handleDatePaste(e) {
        const text = (e.clipboardData || window.clipboardData)?.getData('text')?.trim();
        if (!text) return;
        const parsed = parseDate(text);
        if (parsed) {
            e.preventDefault();
            const y = parsed.getFullYear();
            const m = String(parsed.getMonth() + 1).padStart(2, '0');
            const d = String(parsed.getDate()).padStart(2, '0');
            this.value = `${y}-${m}-${d}`;
            validateAndCalculate();
            this.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    if (start) {
        ['input', 'change', 'keyup', 'blur'].forEach(evt => {
            start.addEventListener(evt, validateAndCalculate);
        });
        start.addEventListener('paste', handleDatePaste);
    }

    if (end) {
        ['input', 'change', 'keyup', 'blur'].forEach(evt => {
            end.addEventListener(evt, validateAndCalculate);
        });
        end.addEventListener('paste', handleDatePaste);
    }

    if (half) {
        half.addEventListener('change', validateAndCalculate);
    }

    function syncDocumentRequired() {
        const option = type && type.selectedIndex >= 0 ? type.options[type.selectedIndex] : null;
        if (attachment) {
            attachment.required = option && option.dataset.document === '1';
        }
    }

    if (type) type.addEventListener('change', syncDocumentRequired);

    // Initial check on load
    validateAndCalculate();
    syncDocumentRequired();

    // STRICT BLOCKER on leaveForm submit - capture phase so it runs BEFORE any other handlers
    if (leaveForm) {
        leaveForm.addEventListener('submit', function (e) {
            const startDate = getDateFromInput(start);
            const endDate = getDateFromInput(end);

            if (startDate && endDate && endDate.getTime() < startDate.getTime()) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                showDateError("End date can't be backdated. Please select a date on or after start date.");
                end.focus();
                end.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            if (isDateInvalid) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                showDateError("End date can't be backdated. Please select a date on or after start date.");
                end.focus();
                return false;
            }
        }, true);
    }

    // Secondary click blocker on submit button
    if (submitBtn) {
        submitBtn.addEventListener('click', function (e) {
            const startDate = getDateFromInput(start);
            const endDate = getDateFromInput(end);

            if (startDate && endDate && endDate.getTime() < startDate.getTime()) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                showDateError("End date can't be backdated. Please select a date on or after start date.");
                end.focus();
                end.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            if (isDateInvalid) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                end.focus();
                return false;
            }
        }, true);
    }

    /* ===== INTL-TEL-INPUT PHONE VALIDATION ===== */
    const phoneInput   = document.getElementById('contactDuringLeavePhone');
    const hiddenInput  = document.getElementById('contactDuringLeaveHidden');
    const phoneError   = document.getElementById('phoneError');
    const phoneIcon    = document.getElementById('phoneValidIcon');

    function initPhoneValidation() {
        if (!phoneInput || !window.intlTelInput) return;

        const utilsScript = 'https://cdn.jsdelivr.net/npm/intl-tel-input@23/build/js/utils.js';

        const iti = window.intlTelInput(phoneInput, {
            utilsScript: utilsScript,
            initialCountry: 'in',           // default to India (+91)
            separateDialCode: true,
            strictMode: true,               // restrict input characters and limit digits to country maximum
            preferredCountries: ['in', 'ae', 'us', 'gb', 'sa', 'pk'],
            placeholderNumberType: 'MOBILE',
        });

        function getValidationMessage() {
            const countryData = iti.getSelectedCountryData();
            const countryName = countryData.name ? countryData.name.replace(/\s*\(.*?\)\s*/g, '').trim() : 'selected country';
            const errorCode = iti.getValidationError();

            switch (errorCode) {
                case 1:
                    return 'Invalid country code selected.';
                case 2:
                    return `Phone number is too short for ${countryName}. Please enter all required digits.`;
                case 3:
                    return `Phone number is too long for ${countryName}.`;
                case 5:
                    return `Invalid number of digits for ${countryName}.`;
                case 4:
                default:
                    return `Please enter a valid phone number for ${countryName}.`;
            }
        }

        function showPhoneState(valid, customMsg) {
            phoneInput.classList.toggle('phone-valid', valid === true);
            phoneInput.classList.toggle('phone-invalid', valid === false);

            if (valid === true) {
                if (phoneIcon) {
                    phoneIcon.style.display = 'block';
                    phoneIcon.innerHTML = '<i class="fas fa-check-circle" style="color:#22c55e;"></i>';
                }
                if (phoneError) {
                    phoneError.style.display = 'none';
                    phoneError.textContent = '';
                }
            } else if (valid === false) {
                if (phoneIcon) {
                    phoneIcon.style.display = 'block';
                    phoneIcon.innerHTML = '<i class="fas fa-times-circle" style="color:#ef4444;"></i>';
                }
                if (phoneError) {
                    phoneError.style.display = 'block';
                    phoneError.textContent = customMsg || getValidationMessage();
                }
            } else {
                // Empty / reset
                if (phoneIcon) phoneIcon.style.display = 'none';
                if (phoneError) {
                    phoneError.style.display = 'none';
                    phoneError.textContent = '';
                }
            }
        }

        function validatePhone() {
            const rawVal = phoneInput.value.trim();
            if (rawVal === '') {
                showPhoneState(null);
                if (hiddenInput) hiddenInput.value = '';
                return true;
            }

            const valid = iti.isValidNumber();
            if (valid) {
                showPhoneState(true);
                if (hiddenInput) hiddenInput.value = iti.getNumber();
                return true;
            } else {
                showPhoneState(false);
                if (hiddenInput) hiddenInput.value = '';
                return false;
            }
        }

        // Pre-fill if a value already exists (edit mode / old input)
        const existingVal = hiddenInput ? hiddenInput.value.trim() : '';
        if (existingVal) {
            iti.setNumber(existingVal);
            validatePhone();
        }

        phoneInput.addEventListener('input', validatePhone);
        phoneInput.addEventListener('countrychange', function () {
            validatePhone();
        });
        phoneInput.addEventListener('blur', function () {
            if (phoneInput.value.trim() !== '') {
                validatePhone();
            }
        });

        // Block form submit if phone is invalid
        if (leaveForm) {
            leaveForm.addEventListener('submit', function (e) {
                const rawVal = phoneInput.value.trim();
                if (rawVal !== '') {
                    if (!iti.isValidNumber()) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        showPhoneState(false);
                        phoneInput.focus();
                        phoneInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return false;
                    }
                    if (hiddenInput) hiddenInput.value = iti.getNumber(); // ensure E.164 in hidden field
                } else {
                    if (hiddenInput) hiddenInput.value = '';
                }
            }, true);
        }
    }

    // Load intl-tel-input dynamically if not already present
    if (window.intlTelInput) {
        initPhoneValidation();
    } else {
        const itiScript = document.createElement('script');
        itiScript.src = 'https://cdn.jsdelivr.net/npm/intl-tel-input@23/build/js/intlTelInput.min.js';
        itiScript.onload = initPhoneValidation;
        document.head.appendChild(itiScript);
    }
});
</script>
@endpush
@endsection
