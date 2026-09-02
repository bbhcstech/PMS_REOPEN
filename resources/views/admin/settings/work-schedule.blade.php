@extends('admin.layout.app')

@section('title', 'Work Schedule Settings')

@push('styles')
<style>
    .work-schedule-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f4fbf7 100%);
        color: #0a2e1f;
    }

    .work-schedule-shell {
        position: relative;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Ambient Orbs */
    .ambient-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(130px);
        opacity: 0.35;
        pointer-events: none;
        z-index: 1;
    }

    .orb-1 {
        top: -100px;
        right: -100px;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(52, 211, 153, 0.12) 0%, transparent 70%);
        animation: orbFloat 20s ease-in-out infinite;
    }

    .orb-2 {
        bottom: -100px;
        left: -100px;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);
        animation: orbFloat 25s ease-in-out infinite reverse;
    }

    @keyframes orbFloat {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(40px, -30px) scale(1.05); }
        66% { transform: translate(-30px, 40px) scale(0.95); }
    }

    .content-wrapper {
        position: relative;
        z-index: 10;
    }

    /* ===== BREADCRUMB ===== */
    .breadcrumb-custom {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 12px;
    }

    .breadcrumb-custom a {
        color: #059669;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .breadcrumb-custom a:hover {
        color: #047857;
    }

    /* ===== HEADER CARD ===== */
    .branches-header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        padding: 1.75rem 2.25rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(16, 185, 129, 0.15);
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        animation: slideDown 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .header-left-box {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .header-icon-badge {
        width: 58px;
        height: 58px;
        border-radius: 20px;
        background: linear-gradient(145deg, #34d399, #059669);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        box-shadow: 0 8px 20px -4px rgba(5, 150, 105, 0.35);
        flex-shrink: 0;
    }

    .header-title h1 {
        font-size: 1.95rem;
        font-weight: 800;
        background: linear-gradient(135deg, #0a2e1f, #059669, #10b981);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        margin: 0 0 0.2rem 0;
        letter-spacing: -0.03em;
    }

    .header-title p {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
        margin: 0;
    }

    .btn-back-settings {
        background-color: #ffffff;
        border: 1px solid rgba(16, 185, 129, 0.25);
        color: #0f744c !important;
        font-weight: 700;
        font-size: 0.9rem;
        border-radius: 40px;
        padding: 0.65rem 1.4rem;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-back-settings:hover {
        background-color: #e6f3ec;
        color: #059669 !important;
        border-color: rgba(16, 185, 129, 0.4);
        transform: translateY(-2px);
    }

    .btn-back-settings:hover .back-arrow-icon {
        transform: translateX(-4px);
    }

    .back-arrow-icon {
        transition: transform 0.25s ease;
        display: inline-block;
    }

    /* ===== STATS GRID ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .stat-card,
    .work-schedule-page .stat-card,
    .work-schedule-page .stat-card:first-of-type {
        background: #ffffff !important;
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 1.5rem;
        border: 1px solid rgba(16, 185, 129, 0.14) !important;
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08) !important;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        color: #0a2e1f !important;
    }

    .work-schedule-page .stat-card:first-of-type *,
    .work-schedule-page .stat-card * {
        -webkit-text-fill-color: initial;
    }

    .work-schedule-page .stat-card h3,
    .work-schedule-page .stat-card:first-of-type h3 {
        color: #0a2e1f !important;
        -webkit-text-fill-color: #0a2e1f !important;
    }

    .work-schedule-page .stat-card h6,
    .work-schedule-page .stat-card span,
    .work-schedule-page .stat-card:first-of-type span,
    .work-schedule-page .stat-card:first-of-type h6 {
        color: #64748b !important;
        -webkit-text-fill-color: #64748b !important;
    }

    .stat-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #34d399, #059669);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .stat-card:hover::after {
        transform: scaleX(1);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 35px -12px rgba(16, 185, 129, 0.15) !important;
        border-color: rgba(16, 185, 129, 0.25) !important;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .stat-icon.days,
    .work-schedule-page .stat-card:first-of-type .stat-icon.days {
        background: linear-gradient(145deg, #d1fae5, #a7f3d0) !important;
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
    }

    .stat-icon.hours,
    .work-schedule-page .stat-card .stat-icon.hours {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
    }

    .stat-icon.break,
    .work-schedule-page .stat-card .stat-icon.break {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
    }

    .stat-icon.roster,
    .work-schedule-page .stat-card .stat-icon.roster {
        background: linear-gradient(145deg, #e0e7ff, #c7d2fe) !important;
        color: #4f46e5 !important;
        -webkit-text-fill-color: #4f46e5 !important;
    }

    .stat-info h6 {
        font-size: 0.72rem;
        color: #64748b;
        margin: 0 0 0.2rem 0;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.05em;
    }

    .stat-info h3 {
        font-size: 1.2rem;
        font-weight: 800;
        color: #0a2e1f;
        margin: 0;
        line-height: 1.2;
    }

    /* ===== FORM CARD & INPUTS ===== */
    .address-card-elevated {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        border: 1px solid rgba(16, 185, 129, 0.15);
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08);
        overflow: hidden;
    }

    .card-header-custom {
        padding: 1.5rem 2.25rem;
        border-bottom: 1px solid rgba(16, 185, 129, 0.12);
        display: flex;
        align-items: center;
        gap: 1rem;
        background: transparent;
    }

    .card-header-avatar {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }

    .section-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 1rem;
        border-radius: 40px;
        background: #ecfdf5;
        color: #059669;
        font-weight: 800;
        font-size: 0.82rem;
        letter-spacing: 0.03em;
        border: 1px solid rgba(5, 150, 105, 0.2);
        margin-bottom: 1.25rem;
    }

    /* Days Pill Grid */
    .days-pill-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.85rem;
    }

    .day-pill-item {
        position: relative;
    }

    .day-pill-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .day-pill-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.65rem 1.4rem;
        border-radius: 40px;
        background: #ffffff;
        border: 1px solid rgba(16, 185, 129, 0.2);
        color: #475569;
        font-weight: 700;
        font-size: 0.88rem;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    }

    .day-pill-label:hover {
        border-color: #34d399;
        transform: translateY(-2px);
    }

    .day-pill-input:checked + .day-pill-label {
        background: linear-gradient(145deg, #ecfdf5, #d1fae5);
        border-color: #34d399;
        color: #065f46;
        box-shadow: 0 4px 14px rgba(5, 150, 105, 0.2);
    }

    .form-label-custom {
        font-size: 0.88rem;
        font-weight: 700;
        color: #0a2e1f;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .input-group-custom {
        border-radius: 16px;
        border: 1px solid rgba(16, 185, 129, 0.2);
        background-color: #fafefb;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .input-group-custom:focus-within {
        border-color: #34d399;
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.15);
        transform: translateY(-1px);
    }

    .input-group-custom .input-group-text {
        background-color: transparent;
        border: none;
        color: #059669;
        padding-left: 18px;
        padding-right: 12px;
        font-size: 1.1rem;
    }

    .input-group-custom .form-control {
        border: none;
        background-color: transparent;
        font-size: 0.92rem;
        font-weight: 600;
        color: #0a2e1f;
        padding-right: 18px;
        height: 50px;
    }

    .input-group-custom .form-control:focus {
        box-shadow: none;
        background-color: transparent;
    }

    .switch-box-container {
        background: #f0fdf4;
        border: 1px solid rgba(16, 185, 129, 0.25);
        border-radius: 20px;
        padding: 18px 24px;
    }

    .form-check-input:checked {
        background-color: #10b981;
        border-color: #10b981;
    }

    .btn-save-address {
        height: 50px;
        border-radius: 40px;
        font-weight: 700;
        font-size: 0.95rem;
        padding: 0 32px;
        background: linear-gradient(145deg, #34d399, #059669);
        color: white !important;
        border: none;
        box-shadow: 0 6px 20px -4px rgba(5, 150, 105, 0.35);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }

    .btn-save-address:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 10px 28px -4px rgba(5, 150, 105, 0.45);
        color: white !important;
    }

    .req-asterisk {
        color: #059669;
        font-weight: 800;
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .work-schedule-page {
            padding: 1.25rem 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    .mode-radio:checked + label {
        background: linear-gradient(135deg, #10b981, #059669) !important;
        border-color: #059669 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3) !important;
    }
</style>
@endpush

@section('content')
<div class="work-schedule-page">
    <div class="work-schedule-shell">
        <div class="ambient-orb orb-1"></div>
        <div class="ambient-orb orb-2"></div>

        <div class="content-wrapper">
            <!-- Breadcrumbs -->
            <div class="breadcrumb-custom">
                <i class="fas fa-building"></i>
                <a href="{{ route('admin.settings.index') }}">Admin</a>
                <span>/</span>
                <a href="{{ route('admin.settings.index') }}">Settings</a>
                <span>/</span>
                <span>Work Schedule & Hours</span>
            </div>

            <!-- Page Header Card -->
            <div class="branches-header">
                <div class="header-left-box">
                    <div class="header-icon-badge">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="header-title">
                        <h1>Work Schedule & Hours</h1>
                        <p>Configure working days, office hours, shift policies, and break durations.</p>
                    </div>
                </div>

                <a href="{{ route('admin.settings.index') }}" class="btn-back-settings">
                    <i class="fas fa-arrow-left me-1 back-arrow-icon"></i> Back to Settings
                </a>
            </div>

            <!-- Alert Notifications -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm rounded-4 border-0" style="background: rgba(220, 252, 231, 0.95); color: #065f46; border-left: 5px solid #10b981 !important;" role="alert">
                    <i class="fas fa-check-circle fs-4 me-2"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Executive Summary Stats Grid -->
            @php
                $activeDaysCount = count($settings['working_days'] ?? []);
                $startTimeStr = !empty($settings['work_start_time']) ? date('g:i A', strtotime($settings['work_start_time'])) : '09:00 AM';
                $endTimeStr = !empty($settings['work_end_time']) ? date('g:i A', strtotime($settings['work_end_time'])) : '06:00 PM';
                $breakMins = $settings['break_duration'] ?? '60';
                $multiShiftOn = ($settings['shift_enabled'] ?? '0') == '1';
            @endphp
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon days">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Working Days</h6>
                        <h3>{{ $activeDaysCount }} Days / Wk</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon hours">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Standard Shift</h6>
                        <h3>{{ $startTimeStr }} - {{ $endTimeStr }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon break">
                        <i class="fas fa-coffee"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Break Duration</h6>
                        <h3>{{ $breakMins }} Minutes</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon roster">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Multi-Shift Roster</h6>
                        <h3>{{ $multiShiftOn ? 'Active' : 'Standard Only' }}</h3>
                    </div>
                </div>
            </div>

            <!-- Read-Only Banner for Non-Admins -->
            @if(! $isAdmin)
                <div class="alert alert-info border-0 shadow-sm rounded-4 d-flex align-items-center gap-3 mb-4" style="background: rgba(224, 242, 254, 0.95); color: #075985;" role="alert">
                    <i class="fas fa-lock fs-4 text-info"></i>
                    <div>
                        <strong>Read-Only Mode:</strong> You are viewing the official company work schedule. Configurations and updates can only be performed by system Administrators.
                    </div>
                </div>
            @endif

            <!-- Main Schedule Form Card -->
            <div class="address-card-elevated">
                <div class="card-header-custom">
                    <div class="card-header-avatar shadow-sm">
                        <i class="fas fa-business-time"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold fs-5" style="color: #0a2e1f;">Working Days & Shift Timings</h5>
                        <small class="text-muted">Set company-wide standard shift and operating hours</small>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <form method="POST" action="{{ route('admin.settings.work-schedule.update') }}">
                        @csrf

                        <!-- Section 1: Company Working Days -->
                        <div class="section-badge">
                            <i class="fas fa-calendar-week"></i> Company Operating Days
                        </div>

                        <div class="mb-5">
                            <label class="form-label-custom mb-3">
                                Select Operating Working Days <span class="req-asterisk">*</span>
                            </label>
                            <div class="days-pill-grid">
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                    @php $isChecked = in_array($day, $settings['working_days'] ?? []); @endphp
                                    <div class="day-pill-item">
                                        <input class="day-pill-input" type="checkbox" name="working_days[]" value="{{ $day }}" id="day_{{ $day }}"
                                            {{ $isChecked ? 'checked' : '' }} {{ ! $isAdmin ? 'disabled' : '' }}>
                                        <label class="day-pill-label" for="day_{{ $day }}">
                                            <i class="fas {{ $isChecked ? 'fa-check-circle' : 'fa-circle' }}" style="font-size: 0.85rem;"></i> {{ $day }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Section 2: Shift Hours & Break Duration -->
                        <div class="section-badge">
                            <i class="fas fa-clock"></i> Shift Schedule & Break Policy
                        </div>

                        <div class="row g-4">
                            <!-- Work Start Time -->
                            <div class="col-md-4">
                                <label class="form-label-custom">
                                    Standard Work Start Time <span class="req-asterisk">*</span>
                                </label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-sun"></i></span>
                                    <input type="time" name="work_start_time" class="form-control"
                                        value="{{ old('work_start_time', $settings['work_start_time'] ?? '09:00') }}" {{ ! $isAdmin ? 'disabled' : '' }} required>
                                </div>
                            </div>

                            <!-- Work End Time -->
                            <div class="col-md-4">
                                <label class="form-label-custom">
                                    Standard Work End Time <span class="req-asterisk">*</span>
                                </label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-moon"></i></span>
                                    <input type="time" name="work_end_time" class="form-control"
                                        value="{{ old('work_end_time', $settings['work_end_time'] ?? '18:00') }}" {{ ! $isAdmin ? 'disabled' : '' }} required>
                                </div>
                            </div>

                            <!-- Break Duration -->
                            <div class="col-md-4">
                                <label class="form-label-custom">
                                    Break Duration (Minutes) <span class="req-asterisk">*</span>
                                </label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-mug-hot"></i></span>
                                    <input type="number" name="break_duration" class="form-control" min="0" max="240"
                                        value="{{ old('break_duration', $settings['break_duration'] ?? '60') }}" {{ ! $isAdmin ? 'disabled' : '' }} required>
                                </div>
                            </div>

                            <!-- Enable Multiple Shift Roster -->
                            <div class="col-12 mt-4">
                                <div class="switch-box-container">
                                    <div class="form-check form-switch mb-0 d-flex align-items-center gap-3">
                                        <input class="form-check-input ms-0" type="checkbox" name="shift_enabled" value="1" id="shiftSwitch" style="width: 44px; height: 24px; cursor: pointer;"
                                            {{ ($settings['shift_enabled'] ?? '0') == '1' ? 'checked' : '' }} {{ ! $isAdmin ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="shiftSwitch" style="font-size: 0.95rem; cursor: pointer;">
                                            Enable Multiple Shift Scheduling & Rostering
                                            <small class="d-block text-muted fw-normal" style="font-size: 0.82rem;">Allows assigning different morning, evening, or night shifts per employee department.</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="mt-5 pt-4 border-top d-flex justify-content-end">
                            @if(! $isAdmin || $isSettingsReadOnly)
                                <span class="badge rounded-pill px-3.5 py-2 fw-bold" style="background: #f1f5f9; color: #64748b; font-size: 13px; border: 1px solid #cbd5e1;">
                                    <i class="fas fa-lock me-1.5 text-muted"></i> Read-Only (Admin Managed)
                                </span>
                            @else
                                <button type="submit" class="btn-save-address">
                                    <i class="fas fa-save me-1.5"></i> Save Work Schedule
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Section 2: Special Day Overrides (Global Holiday / Global WFH) -->
            <div class="address-card-elevated mt-5">
                <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="card-header-avatar shadow-sm" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                            <i class="fas fa-bullhorn text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold fs-5" style="color: #0a2e1f;">Special Day Overrides (Holiday / Work From Home)</h5>
                            <small class="text-muted">Declare any date as a Special Holiday or Global Work From Home (WFH). Notifies each Employee, HR & Manager.</small>
                        </div>
                    </div>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2 fw-semibold">
                        <i class="fas fa-bell me-1"></i> Auto-broadcasts Notifications
                    </span>
                </div>

                <div class="p-4 p-md-5">
                    <!-- Add Special Day Form (Admin Only) -->
                    @if($isAdmin)
                        <form method="POST" action="{{ route('admin.settings.work-schedule.special-day.store') }}" class="mb-4">
                            @csrf
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label-custom">Target Date <span class="req-asterisk">*</span></label>
                                    <div class="input-group input-group-custom">
                                        <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label-custom">Day Override Type <span class="req-asterisk">*</span></label>
                                    <div class="input-group input-group-custom">
                                        <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                                        <select name="type" class="form-select" required>
                                            <option value="holiday">🎉 Special Holiday (Company-wide)</option>
                                            <option value="wfh">🏠 Work From Home (Global WFH)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-custom">Reason / Announcement Title <span class="req-asterisk">*</span></label>
                                    <div class="input-group input-group-custom">
                                        <span class="input-group-text"><i class="fas fa-pen"></i></span>
                                        <input type="text" name="title" class="form-control" placeholder="e.g. Heavy Rain Alert / Founder's Day" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2" style="background: linear-gradient(135deg, #059669, #047857); border: none; box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);">
                                        <i class="fas fa-paper-plane"></i> Declare & Notify
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif

                    <!-- Scheduled Special Days Table -->
                    @php $specialDays = $settings['special_days'] ?? []; @endphp
                    @if(count($specialDays) > 0)
                        <div class="table-responsive rounded-4 border border-emerald-subtle shadow-sm mt-4">
                            <table class="table table-hover align-middle mb-0">
                                <thead style="background: linear-gradient(90deg, #ecfdf5, #f0fdf4); color: #0a2e1f;">
                                    <tr>
                                        <th class="py-3 px-4 fw-bold">Date</th>
                                        <th class="py-3 px-4 fw-bold">Override Type</th>
                                        <th class="py-3 px-4 fw-bold">Title / Event Description</th>
                                        <th class="py-3 px-4 fw-bold">Notifications Dispatched</th>
                                        @if($isAdmin)
                                            <th class="py-3 px-4 fw-bold text-end">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($specialDays as $sd)
                                        @php
                                            $isHoliday = ($sd['type'] ?? '') === 'holiday';
                                        @endphp
                                        <tr>
                                            <td class="py-3 px-4 fw-bold text-dark">
                                                <i class="fas fa-calendar-check text-emerald-600 me-2"></i>
                                                {{ date('D, M d, Y', strtotime($sd['date'])) }}
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="badge rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 {{ $isHoliday ? 'bg-warning text-dark' : 'bg-info text-white' }}">
                                                    <i class="fas {{ $isHoliday ? 'fa-gift' : 'fa-laptop-house' }}"></i>
                                                    {{ $isHoliday ? 'Special Holiday' : 'Work From Home (WFH)' }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 fw-medium text-secondary">
                                                {{ $sd['title'] }}
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 text-xs">
                                                    <i class="fas fa-check-double me-1"></i> Each Employee, HR & Manager
                                                </span>
                                            </td>
                                            @if($isAdmin)
                                                <td class="py-3 px-4 text-end">
                                                    <form method="POST" action="{{ route('admin.settings.work-schedule.special-day.destroy', $sd['id']) }}" class="d-inline" onsubmit="return confirm('Remove this special day override?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-2" title="Delete Override">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-center rounded-4 border border-dashed text-muted" style="background: rgba(240, 253, 244, 0.5);">
                            <i class="fas fa-calendar-minus fs-2 mb-2 text-emerald-400"></i>
                            <p class="mb-0 fw-medium">No special day overrides scheduled. Select a date above to announce a Holiday or Work From Home day.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Section 3: Employee Work Location Settings (Office vs. Work From Home) -->
            <div class="address-card-elevated mt-5 mb-5">
                <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="card-header-avatar shadow-sm" style="background: linear-gradient(135deg, #059669, #0d9488);">
                            <i class="fas fa-users-cog text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold fs-5" style="color: #0a2e1f;">Employee Work Location Settings (From Office vs. Work From Home)</h5>
                            <small class="text-muted">Configure individual work modes for every employee. Updating an employee dispatches alerts to HR and that employee.</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-emerald-subtle text-emerald border border-emerald-subtle rounded-pill px-3 py-2 fw-semibold">
                            <i class="fas fa-user-shield me-1"></i> Notifies HR & Employee
                        </span>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <!-- Live Filter Bar -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                        <div class="input-group style-search" style="max-width: 340px;">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="employeeSearchInput" class="form-control border-start-0" placeholder="Search employee by name or email...">
                        </div>
                        <div class="btn-group" role="group" id="modeFilterGroup">
                            <button type="button" class="btn btn-sm btn-outline-success active" data-filter="all">All Employees ({{ count($employees ?? []) }})</button>
                            <button type="button" class="btn btn-sm btn-outline-success" data-filter="office">From Office (WFO)</button>
                            <button type="button" class="btn btn-sm btn-outline-success" data-filter="wfh">Work From Home (WFH)</button>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.settings.work-schedule.employee-modes-bulk.update') }}" id="bulkEmployeeModesForm">
                        @csrf
                        <div class="table-responsive rounded-4 border border-emerald-subtle shadow-sm">
                            <table class="table table-hover align-middle mb-0" id="employeeModesTable">
                                <thead style="background: linear-gradient(90deg, #ecfdf5, #f0fdf4); color: #0a2e1f;">
                                    <tr>
                                        <th class="py-3 px-4 fw-bold">Employee Details</th>
                                        <th class="py-3 px-4 fw-bold">Role & Designation</th>
                                        <th class="py-3 px-4 fw-bold">Standard Shift</th>
                                        <th class="py-3 px-4 fw-bold text-center">Work Mode Setting (Two Options)</th>
                                        @if($isAdmin)
                                            <th class="py-3 px-4 fw-bold text-end">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $empModes = $settings['employee_modes'] ?? []; @endphp
                                    @forelse($employees ?? [] as $emp)
                                        @php
                                            $currentMode = $empModes[$emp->id] ?? 'office';
                                            $isWfh = $currentMode === 'wfh';
                                        @endphp
                                        <tr class="employee-row" data-name="{{ strtolower($emp->name) }}" data-email="{{ strtolower($emp->email) }}" data-mode="{{ $currentMode }}">
                                            <td class="py-3 px-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar-badge-circle" style="width: 42px; height: 42px; border-radius: 14px; background: linear-gradient(135deg, #10b981, #059669); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(5, 150, 105, 0.25);">
                                                        {{ strtoupper(substr($emp->name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark fs-6">{{ $emp->name }}</div>
                                                        <small class="text-muted">{{ $emp->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill fw-medium">
                                                    <i class="fas fa-briefcase text-emerald-600 me-1"></i> {{ ucfirst($emp->role ?? 'Employee') }}
                                                </span>
                                                @if($emp->designation)
                                                    <small class="d-block text-muted mt-1">{{ $emp->designation }}</small>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="small fw-semibold text-dark">
                                                    <i class="far fa-clock text-emerald-500 me-1"></i> {{ $startTimeStr }} - {{ $endTimeStr }}
                                                </div>
                                                <small class="text-muted">{{ $activeDaysCount }} Days / Wk</small>
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <div class="btn-group" role="group">
                                                    <input type="radio" class="btn-check mode-radio" name="modes[{{ $emp->id }}]" id="mode_office_{{ $emp->id }}" value="office" {{ !$isWfh ? 'checked' : '' }} {{ ! $isAdmin ? 'disabled' : '' }} data-user-id="{{ $emp->id }}">
                                                    <label class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold me-1" for="mode_office_{{ $emp->id }}">
                                                        <i class="fas fa-building me-1"></i> From Office
                                                    </label>

                                                    <input type="radio" class="btn-check mode-radio" name="modes[{{ $emp->id }}]" id="mode_wfh_{{ $emp->id }}" value="wfh" {{ $isWfh ? 'checked' : '' }} {{ ! $isAdmin ? 'disabled' : '' }} data-user-id="{{ $emp->id }}">
                                                    <label class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold" for="mode_wfh_{{ $emp->id }}">
                                                        <i class="fas fa-home me-1"></i> Work From Home
                                                    </label>
                                                </div>
                                            </td>
                                            @if($isAdmin)
                                                <td class="py-3 px-4 text-end">
                                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 single-save-btn" data-user-id="{{ $emp->id }}" style="background: linear-gradient(135deg, #059669, #047857); border: none;">
                                                        <i class="fas fa-check me-1"></i> Save & Notify
                                                    </button>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $isAdmin ? 5 : 4 }}" class="text-center py-4 text-muted">No active employees found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <small class="text-muted"><i class="fas fa-info-circle text-emerald-500 me-1"></i> Modifying an employee work mode automatically notifies HR and the assigned employee.</small>
                            @if($isAdmin)
                                <button type="submit" class="btn-save-address">
                                    <i class="fas fa-save me-1.5"></i> Save All Employee Modes
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('employeeSearchInput');
    const filterButtons = document.querySelectorAll('#modeFilterGroup button');
    const rows = document.querySelectorAll('#employeeModesTable .employee-row');

    let currentFilter = 'all';

    function filterRows() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';

        rows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const email = row.getAttribute('data-email') || '';
            const mode = row.getAttribute('data-mode') || 'office';

            const matchesSearch = name.includes(query) || email.includes(query);
            const matchesFilter = (currentFilter === 'all') || (mode === currentFilter);

            if (matchesSearch && matchesFilter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterRows);
    }

    filterButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-filter');
            filterRows();
        });
    });

    // Single Save via AJAX
    document.querySelectorAll('.single-save-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const userId = this.getAttribute('data-user-id');
            const checkedRadio = document.querySelector(`input[name="modes[${userId}]"]:checked`);
            if (!checkedRadio) return;

            const mode = checkedRadio.value;
            const originalBtnHtml = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

            fetch('{{ route("admin.settings.work-schedule.employee-mode.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    user_id: userId,
                    mode: mode
                })
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-check-circle me-1"></i> Saved!';
                setTimeout(() => { this.innerHTML = originalBtnHtml; }, 2000);

                // Update data-mode attribute
                const row = this.closest('tr');
                if (row) row.setAttribute('data-mode', mode);

                alert(data.message || 'Work mode updated and notifications dispatched!');
            })
            .catch(err => {
                this.disabled = false;
                this.innerHTML = originalBtnHtml;
                alert('Error saving work mode setting.');
            });
        });
    });

    // Live Day Pill Checkbox Icon Toggle
    document.querySelectorAll('.day-pill-input').forEach(input => {
        input.addEventListener('change', function() {
            const icon = this.nextElementSibling ? this.nextElementSibling.querySelector('i') : null;
            if (icon) {
                if (this.checked) {
                    icon.className = 'fas fa-check-circle';
                } else {
                    icon.className = 'fas fa-circle';
                }
            }
        });
    });
});
</script>
@endsection
