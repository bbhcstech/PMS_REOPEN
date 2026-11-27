@extends('admin.layout.app')

@section('title', 'Create Attendance')

@section('content')
<style>
    /* ===== CLEAN CREATE ATTENDANCE UI ===== */
    :root {
        --primary-blue: #2563eb;
        --primary-hover: #1d4ed8;
        --primary-teal: #2563eb;
        --primary-accent: #3b82f6;
        --primary-green: #10b981;
        --bg-light: #f5f7fb;
        --glass-border: #e2e8f0;
        --card-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        --card-shadow-hover: 0 6px 20px rgba(15, 23, 42, 0.06);
        --transition-smooth: all 0.2s ease;
        --spring-transition: all 0.2s ease;
    }

    .attendance-create-container {
        background: var(--bg-light);
        min-height: calc(100vh - 90px);
        padding: 1.5rem 1.75rem 2.5rem;
        position: relative;
    }

    .ambient-orb {
        display: none;
    }

    .content-wrapper {
        position: relative;
        z-index: 10;
        max-width: 1180px;
        margin: 0 auto;
    }

    /* ===== HEADER ===== */
    .header-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 1.25rem 1.75rem;
        margin-bottom: 1.25rem;
        border: 1px solid #e2e8f0;
        box-shadow: var(--card-shadow);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        transition: var(--transition-smooth);
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .header-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: #eef4ff;
        border: 1px solid #dbeafe;
        color: var(--primary-blue);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .header-title h1 {
        font-size: 1.4rem;
        font-weight: 750;
        color: #0f172a;
        margin: 0 0 0.2rem;
        letter-spacing: -0.02em;
    }

    .header-title p {
        color: #64748b;
        font-size: 0.84rem;
        font-weight: 500;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .header-title p i {
        color: #94a3b8;
        font-size: 0.8rem;
    }

    .header-year-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.42rem 0.9rem;
        border-radius: 999px;
        background: #eff6ff;
        border: 1px solid #dbeafe;
        color: #1e40af;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .header-year-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #2563eb;
    }

    /* ===== ALERT ===== */
    .alert-premium {
        border-radius: 12px;
        border: 1px solid #fecaca;
        border-left: 4px solid #ef4444;
        padding: 0.9rem 1.25rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 500;
        font-size: 0.88rem;
    }

    .alert-danger {
        background: #fef2f2;
        color: #991b1b;
    }

    .alert-premium i {
        font-size: 1.1rem;
    }

    /* ===== FORM CARD ===== */
    .form-card {
        background: #ffffff;
        border-radius: 16px;
        overflow: visible;
        border: 1px solid #e2e8f0;
        box-shadow: var(--card-shadow);
    }

    .form-body {
        padding: 1.5rem 1.75rem 1.75rem;
    }

    .form-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 0.9rem;
        margin-bottom: 1.4rem;
        border-bottom: 1px solid #eef2f7;
    }

    .form-section-title {
        font-size: 0.72rem;
        font-weight: 750;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #64748b;
    }

    .form-required-note {
        font-size: 0.75rem;
        font-weight: 600;
        color: #dc2626;
    }

    /* ===== FORM GRID ===== */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem 1.75rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group.hidden {
        display: none;
    }

    .form-label {
        font-size: 0.72rem;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: flex;
        align-items: center;
        gap: 0.45rem;
        margin: 0;
    }

    .form-label i {
        color: #64748b;
        font-size: 0.78rem;
    }

    .form-label .required-star {
        color: #dc2626;
        font-size: 0.88rem;
        line-height: 1;
    }

    .form-control,
    .form-select {
        width: 100%;
        padding: 0.55rem 0.9rem;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 500;
        color: #0f172a;
        background-color: #ffffff;
        transition: var(--transition-smooth);
        outline: none;
        min-height: 44px;
        box-shadow: none;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .form-control::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }

    .date-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .date-input-wrap i {
        position: absolute;
        left: 0.95rem;
        color: #64748b;
        font-size: 0.9rem;
        pointer-events: none;
    }

    .date-input-wrap .form-control {
        padding-left: 2.5rem;
    }

    .form-control[type="time"] {
        appearance: none;
        -webkit-appearance: none;
    }

    .form-control[type="time"]::-webkit-calendar-picker-indicator {
        filter: invert(0.4);
        cursor: pointer;
    }

    .form-text {
        font-size: 0.74rem;
        color: #64748b;
        margin-top: 0.15rem;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .text-danger {
        color: #dc2626 !important;
        font-size: 0.75rem;
        font-weight: 600;
        margin-top: 0.15rem;
    }

    /* ===== RADIO & CHECKBOX CARDS ===== */
    .radio-group {
        display: flex;
        gap: 0.75rem;
        align-items: stretch;
        flex-wrap: wrap;
    }

    .radio-option {
        flex: 1 1 120px;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 0.55rem;
        cursor: pointer;
        padding: 0.55rem 0.95rem;
        border-radius: 10px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        transition: var(--transition-smooth);
        margin: 0;
    }

    .radio-option:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }

    .radio-option input[type="radio"] {
        accent-color: var(--primary-blue);
        width: 16px;
        height: 16px;
        margin: 0;
        cursor: pointer;
        flex-shrink: 0;
    }

    .radio-option input[type="radio"]:checked + .radio-label {
        color: #1d4ed8;
    }

    .radio-option:has(input:checked) {
        background: #f0f6ff;
        border-color: var(--primary-blue);
        box-shadow: inset 0 0 0 1px var(--primary-blue);
    }

    .radio-label {
        font-size: 0.85rem;
        font-weight: 650;
        color: #475569;
        transition: var(--transition-smooth);
        cursor: pointer;
    }

    .checkbox-option {
        min-height: 44px;
        display: flex;
        align-items: center;
        gap: 0.65rem;
        cursor: pointer;
        padding: 0.55rem 0.95rem;
        border-radius: 10px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        transition: var(--transition-smooth);
        margin: 0;
    }

    .checkbox-option:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }

    .checkbox-option input[type="checkbox"] {
        width: 17px;
        height: 17px;
        border-radius: 5px;
        accent-color: var(--primary-blue);
        cursor: pointer;
        margin: 0;
        flex-shrink: 0;
    }

    .checkbox-option:has(input:checked) {
        border-color: var(--primary-blue);
        background: #f0f6ff;
    }

    .checkbox-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
    }

    /* ===== SELECT2 / BOOTSTRAP SELECT OVERRIDES ===== */
    .bootstrap-select {
        width: 100% !important;
    }

    .bootstrap-select .dropdown-toggle {
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px !important;
        padding: 0.55rem 0.9rem !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        background: #ffffff !important;
        min-height: 44px !important;
        color: #0f172a !important;
        box-shadow: none !important;
        transition: var(--transition-smooth) !important;
    }

    .bootstrap-select .dropdown-toggle:focus,
    .bootstrap-select .dropdown-toggle[aria-expanded="true"] {
        border-color: var(--primary-blue) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
    }

    .bootstrap-select .dropdown-menu {
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        padding: 0.45rem !important;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.1) !important;
    }

    .bootstrap-select .dropdown-menu .dropdown-item {
        padding: 0.48rem 0.85rem !important;
        border-radius: 8px !important;
        font-size: 0.85rem !important;
        font-weight: 500 !important;
    }

    .bootstrap-select .dropdown-menu .dropdown-item:hover {
        background: #eff6ff !important;
        color: #1d4ed8 !important;
    }

    .bootstrap-select .dropdown-menu .dropdown-item.active {
        background: var(--primary-blue) !important;
        color: #ffffff !important;
    }

    .bootstrap-select .dropdown-menu .bs-actionsbox {
        padding: 0.45rem !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }

    .bootstrap-select .dropdown-menu .bs-actionsbox .btn {
        border-radius: 8px !important;
        font-weight: 600 !important;
        font-size: 0.78rem !important;
        padding: 0.3rem 0.75rem !important;
    }

    /* ===== DATERANGEPICKER OVERRIDES ===== */
    .daterangepicker {
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.12) !important;
        font-family: inherit !important;
        z-index: 1060 !important;
    }

    .daterangepicker .drp-buttons .btn {
        border-radius: 8px !important;
        font-weight: 600 !important;
        padding: 0.4rem 1.1rem !important;
    }

    .daterangepicker .drp-buttons .btn-primary {
        background: var(--primary-blue) !important;
        border-color: var(--primary-blue) !important;
    }

    .daterangepicker td.active,
    .daterangepicker td.active:hover {
        background: var(--primary-blue) !important;
    }

    .daterangepicker td.in-range {
        background: #eff6ff !important;
        color: #1d4ed8 !important;
    }

    /* ===== ACTION BUTTONS ===== */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 0.75rem;
        padding-top: 1.35rem;
        margin-top: 1.5rem;
        border-top: 1px solid #eef2f7;
        flex-wrap: wrap;
    }

    .btn-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.68rem 1.4rem;
        background: var(--primary-blue);
        color: #ffffff;
        border: 1px solid var(--primary-blue);
        border-radius: 10px;
        font-weight: 650;
        font-size: 0.875rem;
        cursor: pointer;
        transition: var(--transition-smooth);
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.22);
        text-decoration: none;
    }

    .btn-submit:hover {
        background: var(--primary-hover);
        border-color: var(--primary-hover);
        color: #ffffff;
    }

    .btn-cancel {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.68rem 1.3rem;
        background: #ffffff;
        color: #334155;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-weight: 650;
        font-size: 0.875rem;
        cursor: pointer;
        transition: var(--transition-smooth);
        text-decoration: none;
    }

    .btn-cancel:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
        .attendance-create-container {
            padding: 1.25rem;
        }

        .form-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }

    @media (max-width: 768px) {
        .attendance-create-container {
            padding: 1rem;
        }

        .header-card,
        .form-body {
            padding: 1.25rem;
        }

        .form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .btn-submit,
        .btn-cancel {
            width: 100%;
        }
    }

    /* ===== DARK MODE ===== */
    html[data-pms-theme="dark"] .attendance-create-container {
        background: #090f20;
    }

    html[data-pms-theme="dark"] .header-card,
    html[data-pms-theme="dark"] .form-card {
        background: #111a32;
        border-color: #2a3856;
    }

    html[data-pms-theme="dark"] .header-icon {
        background: rgba(59, 130, 246, 0.16);
        border-color: rgba(96, 165, 250, 0.32);
        color: #93c5fd;
    }

    html[data-pms-theme="dark"] .header-title h1 {
        color: #f1f5f9;
    }

    html[data-pms-theme="dark"] .header-title p,
    html[data-pms-theme="dark"] .form-section-title,
    html[data-pms-theme="dark"] .form-text {
        color: #94a3b8;
    }

    html[data-pms-theme="dark"] .header-year-pill {
        background: rgba(37, 99, 235, 0.18);
        border-color: rgba(96, 165, 250, 0.35);
        color: #bfdbfe;
    }

    html[data-pms-theme="dark"] .form-section-head,
    html[data-pms-theme="dark"] .form-actions {
        border-color: #24314d;
    }

    html[data-pms-theme="dark"] .form-label {
        color: #cbd5e1;
    }

    html[data-pms-theme="dark"] .form-control,
    html[data-pms-theme="dark"] .form-select,
    html[data-pms-theme="dark"] .radio-option,
    html[data-pms-theme="dark"] .checkbox-option,
    html[data-pms-theme="dark"] .btn-cancel {
        background: #16213e;
        border-color: #2d3d61;
        color: #f1f5f9;
    }

    html[data-pms-theme="dark"] .form-control:focus,
    html[data-pms-theme="dark"] .form-select:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.16);
    }

    html[data-pms-theme="dark"] .radio-option:has(input:checked),
    html[data-pms-theme="dark"] .checkbox-option:has(input:checked) {
        background: rgba(37, 99, 235, 0.2);
        border-color: #60a5fa;
    }

    html[data-pms-theme="dark"] .radio-option input[type="radio"]:checked + .radio-label,
    html[data-pms-theme="dark"] .radio-label,
    html[data-pms-theme="dark"] .checkbox-label {
        color: #e2e8f0;
    }

    html[data-pms-theme="dark"] .bootstrap-select .dropdown-toggle {
        background: #16213e !important;
        border-color: #2d3d61 !important;
        color: #f1f5f9 !important;
    }

    html[data-pms-theme="dark"] .bootstrap-select .dropdown-menu {
        background: #16213e !important;
        border-color: #2d3d61 !important;
    }

    html[data-pms-theme="dark"] .bootstrap-select .dropdown-menu .dropdown-item {
        color: #e2e8f0 !important;
    }

    html[data-pms-theme="dark"] .bootstrap-select .dropdown-menu .dropdown-item:hover {
        background: rgba(59, 130, 246, 0.18) !important;
        color: #93c5fd !important;
    }

    html[data-pms-theme="dark"] .bootstrap-select .dropdown-menu .dropdown-item.active {
        background: var(--primary-blue) !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .daterangepicker {
        background: #16213e !important;
        border-color: #2d3d61 !important;
    }

    html[data-pms-theme="dark"] .daterangepicker .calendar-table {
        background: #16213e !important;
        color: #e2e8f0 !important;
    }

    html[data-pms-theme="dark"] .daterangepicker td.off {
        color: #64748b !important;
    }

    html[data-pms-theme="dark"] .daterangepicker td.in-range {
        background: rgba(59, 130, 246, 0.22) !important;
        color: #93c5fd !important;
    }

    html[data-pms-theme="dark"] .daterangepicker td.active,
    html[data-pms-theme="dark"] .daterangepicker td.active:hover {
        background: var(--primary-blue) !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .daterangepicker .drp-buttons {
        border-color: #2d3d61 !important;
    }

    html[data-pms-theme="dark"] .daterangepicker .drp-buttons .btn-default {
        background: #1e293b !important;
        color: #e2e8f0 !important;
        border-color: #334155 !important;
    }

    html[data-pms-theme="dark"] .form-control[type="time"]::-webkit-calendar-picker-indicator {
        filter: invert(0.75);
    }
</style>

<div class="attendance-create-container" data-live-preserve data-live-records-off>
    <div class="content-wrapper">
        <!-- ===== HEADER ===== -->
        <div class="header-card">
            <div class="header-left">
                <div class="header-icon" aria-hidden="true">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="header-title">
                    <h1>Add Attendance</h1>
                    <p><i class="fas fa-info-circle"></i> Mark single or batch attendance for employees with custom work location and schedules</p>
                </div>
            </div>
            <div class="header-year-pill">
                <span class="header-year-dot" aria-hidden="true"></span>
                <span>Year: {{ $year }}</span>
            </div>
        </div>

        <!-- ===== ALERT ===== -->
        @if(session('error'))
            <div class="alert-premium alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        <!-- ===== FORM CARD ===== -->
        <div class="form-card">
            <div class="form-body">
                <div class="form-section-head">
                    <span class="form-section-title">Attendance Configuration</span>
                    <span class="form-required-note">* Required fields</span>
                </div>
                <form method="POST" action="{{ route('attendance.store') }}">
                    @csrf
                    <input type="hidden" name="staff_category" value="{{ request('staff_category', 'employee') }}">

                    <!-- ===== FORM GRID ===== -->
                    <div class="form-grid">

                        <!-- Department -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-building"></i> Department <span class="required-star">*</span>
                            </label>
                            <select name="department_id" class="form-select" required>
                                <option value="0">-- Select Department --</option>
                                @foreach ($departments as $team)
                                    <option value="{{ $team->id }}" @selected(old('department_id', $users->firstWhere('id', (int) request('user_id'))?->employeeDetail?->department_id) == $team->id)>{{ $team->dpt_name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Employees -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-users"></i> Employees <span class="required-star">*</span>
                            </label>
                            <select class="form-control multiple-users" multiple name="user_id[]"
                                    id="selectEmployee" data-live-search="true" data-size="8">
                                <option value="">-- Select Employees --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(in_array($user->id, (array) old('user_id', request('user_id') ? [request('user_id')] : [])))>{{ $user->name }} ({{ $user->designation ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Location -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Location <span class="required-star">*</span>
                            </label>
                            <select name="location_id" id="location_id" class="form-select">
                                <option value="">Custom location / select a saved location</option>
                                @foreach ($location as $locations)
                                    <option @selected(old('location_id', $locations->is_default == 1 ? $locations->id : null) == $locations->id) value="{{ $locations->id }}">
                                        {{ $locations->location }}
                                    </option>
                                @endforeach
                            </select>
                            @error('location_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Mark Attendance By -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar-check"></i> Mark Attendance By <span class="required-star">*</span>
                            </label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input class="form-check-input" type="radio" name="mark_attendance_by" value="month" @checked(!request('date'))>
                                    <span class="radio-label">Month</span>
                                </label>
                                <label class="radio-option">
                                    <input class="form-check-input" type="radio" name="mark_attendance_by" value="date" @checked(request('date'))>
                                    <span class="radio-label">Date Range</span>
                                </label>
                            </div>
                        </div>

                        <!-- Custom Location / Address -->
                        <div class="form-group">
                            <label for="attendance_location" class="form-label">
                                <i class="fas fa-location-arrow"></i> Custom Location / Address
                            </label>
                            <input type="text" name="clock_in_address" id="attendance_location" class="form-control" maxlength="1000" value="{{ old('clock_in_address') }}" placeholder="Enter the employee's attendance location">
                            @error('clock_in_address')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>

                        <!-- Working From -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-laptop-house"></i> Working From <span class="required-star">*</span>
                            </label>
                            <select name="work_from_type" id="work_from_type" class="form-select" required>
                                <option value="office">Office</option>
                                <option value="home">Home</option>
                                <option value="other">Other</option>
                            </select>
                            @error('work_from_type')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Other Location -->
                        <div class="form-group full-width d-none" id="other_location_div">
                            <label class="form-label">
                                <i class="fas fa-map-pin"></i> Other Location
                            </label>
                            <input type="text" name="working_from" id="other_location" class="form-control" placeholder="Enter location...">
                            @error('other_location')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Year -->
                        <div class="form-group" id="year_section">
                            <label class="form-label">
                                <i class="fas fa-calendar"></i> Year <span class="required-star">*</span>
                            </label>
                            <select name="year" id="year" class="form-select">
                                <option value="">-- Select Year --</option>
                                @for ($i = $year; $i >= $year - 4; $i--)
                                    <option @if ($i == $year) selected @endif value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Month -->
                        <div class="form-group" id="month_section">
                            <label class="form-label">
                                <i class="fas fa-calendar-alt"></i> Month <span class="required-star">*</span>
                            </label>
                            <select id="month" name="month" class="form-select">
                                <option value="">-- Select Month --</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div class="form-group full-width" id="date_section" style="display: none;">
                            <label class="form-label">
                                <i class="fas fa-calendar-week"></i> Date Range <span class="required-star">*</span>
                            </label>
                            <div class="date-input-wrap">
                                <i class="far fa-calendar"></i>
                                <input type="text" class="form-control" id="date_range" name="date_range" placeholder="MM/DD/YYYY - MM/DD/YYYY" value="{{ old('date_range', request('date') ? \Carbon\Carbon::parse(request('date'))->format('m/d/Y') . ' - ' . \Carbon\Carbon::parse(request('date'))->format('m/d/Y') : '') }}">
                            </div>
                            <span class="form-text"><i class="fas fa-info-circle"></i> Select start and end dates</span>
                            @error('date_range')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Clock In -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-sign-in-alt"></i> Clock In <span class="required-star">*</span>
                            </label>
                            <input type="time" name="clock_in" class="form-control"
                                   value="10:30" required max="23:59">
                            @error('clock_in')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Clock Out -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-sign-out-alt"></i> Clock Out
                            </label>
                            <input type="time" name="clock_out" class="form-control"
                                   value="19:30" max="23:59">
                            @error('clock_out')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Hidden Status -->
                        <input type="hidden" name="status" id="status" value="absent">

                        <!-- Late -->
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-clock"></i> Late</label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input class="form-check-input" type="radio" id="late_yes" name="late" value="yes">
                                    <span class="radio-label">Yes</span>
                                </label>
                                <label class="radio-option">
                                    <input class="form-check-input" type="radio" id="late_no" name="late" value="no" checked>
                                    <span class="radio-label">No</span>
                                </label>
                            </div>
                        </div>

                        <!-- Half Day -->
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-star-half-alt"></i> Half Day</label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input class="form-check-input" type="radio" id="half_day_yes" name="half_day" value="yes">
                                    <span class="radio-label">Yes</span>
                                </label>
                                <label class="radio-option">
                                    <input class="form-check-input" type="radio" id="half_day_no" name="half_day" value="no" checked>
                                    <span class="radio-label">No</span>
                                </label>
                            </div>
                        </div>

                        <!-- Half Day Duration -->
                        <div class="form-group full-width" id="half_day_duration_div" style="display: none;">
                            <label class="form-label"><i class="fas fa-clock"></i> Half Day Duration</label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input class="form-check-input" type="radio" id="first_half_day_yes" name="half_day_duration" value="first_half" checked>
                                    <span class="radio-label">First Half</span>
                                </label>
                                <label class="radio-option">
                                    <input class="form-check-input" type="radio" id="first_half_day_no" name="half_day_duration" value="second_half">
                                    <span class="radio-label">Second Half</span>
                                </label>
                            </div>
                        </div>

                        <!-- Overwrite Attendance -->
                        <div class="form-group full-width">
                            <label class="form-label"><i class="fas fa-exchange-alt"></i> Options</label>
                            <label class="checkbox-option">
                                <input type="checkbox" name="overwrite_attendance" id="overwrite_attendance" class="form-check-input" value="yes">
                                <span class="checkbox-label">Overwrite Existing Attendance</span>
                            </label>
                        </div>

                    </div>

                    <!-- ===== ACTION BUTTONS ===== -->
                    <div class="form-actions">
                        <a href="{{ route('leaves.index') }}" class="btn-cancel">
                            Cancel / Reset
                        </a>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-check"></i> Save Attendance
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // ===== Date Range Toggle =====
        const monthRadio = document.querySelector('input[name="mark_attendance_by"][value="month"]');
        const dateRadio = document.querySelector('input[name="mark_attendance_by"][value="date"]');
        const yearSection = document.getElementById("year_section");
        const monthSection = document.getElementById("month_section");
        const dateSection = document.getElementById("date_section");

        function toggleSections() {
            if (monthRadio.checked) {
                yearSection.style.display = "block";
                monthSection.style.display = "block";
                dateSection.style.display = "none";
            } else {
                yearSection.style.display = "none";
                monthSection.style.display = "none";
                dateSection.style.display = "block";
            }
        }

        toggleSections();
        monthRadio.addEventListener("change", toggleSections);
        dateRadio.addEventListener("change", toggleSections);

        // ===== Date Range Picker =====
        const dateRangeInput = document.getElementById("date_range");
        if (dateRangeInput) {
            $(dateRangeInput).daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'MM/DD/YYYY'
                }
            });

            $(dateRangeInput).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            });

            $(dateRangeInput).on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        }

        // ===== Half Day Duration Toggle =====
        const halfDayYes = document.getElementById("half_day_yes");
        const halfDayNo = document.getElementById("half_day_no");
        const durationDiv = document.getElementById("half_day_duration_div");

        function toggleDuration() {
            durationDiv.style.display = halfDayYes.checked ? "block" : "none";
        }

        toggleDuration();
        halfDayYes.addEventListener("change", toggleDuration);
        halfDayNo.addEventListener("change", toggleDuration);

        // ===== Other Location Toggle =====
        const workFromType = document.getElementById("work_from_type");
        const otherDiv = document.getElementById("other_location_div");
        const otherInput = document.getElementById("other_location");

        workFromType.addEventListener("change", function() {
            if (this.value === "other") {
                otherDiv.classList.remove("d-none");
                otherInput.setAttribute("required", "required");
            } else {
                otherDiv.classList.add("d-none");
                otherInput.removeAttribute("required");
                otherInput.value = "";
            }
        });

        // ===== Status Update =====
        const statusInput = document.getElementById("status");

        function updateStatus() {
            let status = "present";

            if (document.getElementById("late_yes").checked) {
                status = "late";
            }

            if (document.getElementById("half_day_yes").checked) {
                status = "half_day";
            }

            statusInput.value = status;
        }

        document.querySelectorAll("input[name='late'], input[name='half_day']").forEach(input => {
            input.addEventListener("change", updateStatus);
        });

        updateStatus();

        // ===== Initialize SelectPicker =====
        $("#selectEmployee").selectpicker({
            actionsBox: true,
            selectAllText: "Select All",
            deselectAllText: "Deselect All",
            multipleSeparator: ", ",
            selectedTextFormat: "count > 3",
            countSelectedText: function(selected, total) {
                return selected + " selected";
            }
        });
    });
</script>
@endpush
