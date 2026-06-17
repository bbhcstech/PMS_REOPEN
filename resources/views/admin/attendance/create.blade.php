@extends('admin.layout.app')

@section('title', 'Create Attendance')

@section('content')
<style>
    /* ===== PREMIUM CREATE ATTENDANCE STYLES ===== */
    :root {
        --primary-blue: #1e3a8a;
        --primary-teal: #0ea5a4;
        --primary-green: #22c55e;
        --bg-light: #f8fafc;
        --glass-border: rgba(255, 255, 255, 0.7);
        --card-shadow: 0px 4px 20px rgba(0, 0, 0, 0.02),
            0px 8px 40px rgba(0, 0, 0, 0.04),
            0px 20px 60px rgba(30, 58, 138, 0.06);
        --card-shadow-hover: 0px 20px 50px rgba(0, 0, 0, 0.08),
            0px 30px 80px rgba(30, 58, 138, 0.12);
        --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --spring-transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .attendance-create-container {
        background: linear-gradient(135deg, #f0f9ff 0%, #e6f7f5 50%, #f0fdf4 100%);
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        position: relative;
        overflow: hidden;
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
        background: radial-gradient(circle, rgba(30, 58, 138, 0.12) 0%, transparent 70%);
        animation: orbFloat 20s ease-in-out infinite;
    }

    .orb-2 {
        bottom: -100px;
        left: -100px;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(14, 165, 164, 0.1) 0%, transparent 70%);
        animation: orbFloat 25s ease-in-out infinite reverse;
    }

    .orb-3 {
        top: 50%;
        left: 50%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(34, 197, 94, 0.08) 0%, transparent 70%);
        animation: orbFloat 18s ease-in-out infinite;
        transform: translate(-50%, -50%);
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

    /* ===== HEADER ===== */
    .header-card {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        padding: 1.75rem 2.25rem;
        margin-bottom: 2rem;
        border: 1px solid var(--glass-border);
        box-shadow: var(--card-shadow);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        animation: slideDown 0.6s ease;
        transition: var(--spring-transition);
    }

    .header-card:hover {
        box-shadow: var(--card-shadow-hover);
        border-color: rgba(14, 165, 164, 0.2);
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .header-title h1 {
        font-size: 2rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-teal), var(--primary-green));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        margin-bottom: 0.25rem;
        letter-spacing: -0.03em;
    }

    .header-title p {
        color: #64748b;
        font-size: 0.95rem;
        font-weight: 500;
        margin: 0;
    }

    .header-title p i {
        color: var(--primary-teal);
    }

    /* ===== ALERT ===== */
    .alert-premium {
        border-radius: 20px;
        border-left: 6px solid;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 500;
    }

    .alert-danger {
        background: rgba(254, 226, 226, 0.95);
        border-left-color: #ef4444;
        color: #991b1b;
    }

    .alert-premium i {
        font-size: 1.25rem;
    }

    /* ===== FORM CARD ===== */
    .form-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        overflow: hidden;
        border: 1px solid var(--glass-border);
        box-shadow: var(--card-shadow);
        transition: var(--spring-transition);
    }

    .form-card:hover {
        box-shadow: var(--card-shadow-hover);
    }

    .form-body {
        padding: 2rem 2.25rem;
    }

    /* ===== FORM GRID ===== */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem 2rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group.hidden {
        display: none;
    }

    .form-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-label i {
        color: var(--primary-teal);
        font-size: 0.8rem;
    }

    .form-label .required-star {
        color: #ef4444;
        font-size: 1rem;
        line-height: 1;
    }

    .form-control,
    .form-select {
        width: 100%;
        padding: 0.65rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        font-size: 0.9rem;
        font-weight: 500;
        color: #0f172a;
        background: white;
        transition: var(--transition-smooth);
        outline: none;
        min-height: 48px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-teal);
        box-shadow: 0 0 0 4px rgba(14, 165, 164, 0.12);
    }

    .form-control::placeholder {
        color: #94a3b8;
        font-weight: 400;
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
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 0.25rem;
    }

    .form-text i {
        margin-right: 0.25rem;
    }

    .text-danger {
        color: #ef4444 !important;
        font-size: 0.75rem;
        font-weight: 600;
        margin-top: 0.25rem;
    }

    /* ===== RADIO & CHECKBOX GROUPS ===== */
    .radio-group {
        display: flex;
        gap: 1rem;
        align-items: center;
        padding-top: 0.2rem;
        flex-wrap: wrap;
    }

    .radio-option {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        cursor: pointer;
        padding: 0.3rem 0.8rem;
        border-radius: 30px;
        background: #f1f5f9;
        border: 2px solid transparent;
        transition: var(--transition-smooth);
    }

    .radio-option:hover {
        background: #e2e8f0;
    }

    .radio-option input[type="radio"] {
        accent-color: var(--primary-teal);
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .radio-option input[type="radio"]:checked+.radio-label {
        color: var(--primary-teal);
    }

    .radio-option:has(input:checked) {
        background: #d1fae5;
        border-color: var(--primary-teal);
    }

    .radio-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #475569;
        transition: var(--transition-smooth);
        cursor: pointer;
    }

    .checkbox-option {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        padding: 0.4rem 1rem;
        border-radius: 12px;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        transition: var(--transition-smooth);
    }

    .checkbox-option:hover {
        background: #f1f5f9;
    }

    .checkbox-option input[type="checkbox"] {
        width: 18px;
        height: 18px;
        border-radius: 6px;
        accent-color: var(--primary-teal);
        cursor: pointer;
        flex-shrink: 0;
    }

    .checkbox-option:has(input:checked) {
        border-color: var(--primary-teal);
        background: #d1fae5;
    }

    .checkbox-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
    }

    /* ===== SELECT2 / BOOTSTRAP SELECT OVERRIDES ===== */
    .bootstrap-select .dropdown-toggle {
        border: 2px solid #e2e8f0 !important;
        border-radius: 14px !important;
        padding: 0.65rem 1rem !important;
        font-size: 0.9rem !important;
        font-weight: 500 !important;
        background: white !important;
        min-height: 48px !important;
        color: #0f172a !important;
        transition: var(--transition-smooth) !important;
    }

    .bootstrap-select .dropdown-toggle:focus {
        border-color: var(--primary-teal) !important;
        box-shadow: 0 0 0 4px rgba(14, 165, 164, 0.12) !important;
    }

    .bootstrap-select .dropdown-menu {
        border: 1px solid #e2e8f0 !important;
        border-radius: 14px !important;
        padding: 0.5rem !important;
        box-shadow: var(--card-shadow) !important;
    }

    .bootstrap-select .dropdown-menu .dropdown-item {
        padding: 0.5rem 1rem !important;
        border-radius: 10px !important;
        transition: var(--transition-smooth) !important;
        font-weight: 500 !important;
    }

    .bootstrap-select .dropdown-menu .dropdown-item:hover {
        background: #d1fae5 !important;
        color: #065f46 !important;
    }

    .bootstrap-select .dropdown-menu .dropdown-item.active {
        background: var(--primary-teal) !important;
        color: white !important;
    }

    .bootstrap-select .dropdown-menu .bs-actionsbox {
        padding: 0.5rem !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }

    .bootstrap-select .dropdown-menu .bs-actionsbox .btn {
        border-radius: 10px !important;
        font-weight: 600 !important;
        font-size: 0.8rem !important;
        padding: 0.3rem 0.8rem !important;
    }

    /* ===== DATERANGEPICKER OVERRIDES ===== */
    .daterangepicker {
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        box-shadow: var(--card-shadow) !important;
        font-family: inherit !important;
        z-index: 1060 !important;
    }

    .daterangepicker .drp-buttons .btn {
        border-radius: 10px !important;
        font-weight: 600 !important;
        padding: 0.4rem 1.2rem !important;
    }

    .daterangepicker .drp-buttons .btn-primary {
        background: var(--primary-teal) !important;
        border-color: var(--primary-teal) !important;
    }

    .daterangepicker td.active,
    .daterangepicker td.active:hover {
        background: var(--primary-teal) !important;
    }

    .daterangepicker td.in-range {
        background: #d1fae5 !important;
        color: #065f46 !important;
    }

    /* ===== ACTION BUTTONS ===== */
    .form-actions {
        display: flex;
        gap: 1rem;
        padding-top: 1.5rem;
        margin-top: 1.5rem;
        border-top: 2px solid #e2e8f0;
        flex-wrap: wrap;
    }

    .btn-submit {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.8rem 2rem;
        background: linear-gradient(135deg, var(--primary-teal), var(--primary-green));
        color: white;
        border: none;
        border-radius: 40px;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        transition: var(--spring-transition);
        box-shadow: 0 4px 15px rgba(14, 165, 164, 0.25);
        text-decoration: none;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(14, 165, 164, 0.35);
        color: white;
    }

    .btn-cancel {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.8rem 2rem;
        background: #f1f5f9;
        color: #475569;
        border: 2px solid #e2e8f0;
        border-radius: 40px;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        transition: var(--transition-smooth);
        text-decoration: none;
    }

    .btn-cancel:hover {
        background: #e2e8f0;
        color: #0f172a;
        transform: translateY(-2px);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
        .attendance-create-container {
            padding: 1.5rem 1.25rem;
        }

        .header-card {
            padding: 1.5rem;
        }

        .header-title h1 {
            font-size: 1.6rem;
        }

        .form-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .form-body {
            padding: 1.5rem 1.5rem;
        }
    }

    @media (max-width: 768px) {
        .attendance-create-container {
            padding: 1rem;
        }

        .header-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .form-body {
            padding: 1.25rem 1rem;
        }

        .radio-group {
            gap: 0.5rem;
        }

        .radio-option {
            padding: 0.2rem 0.6rem;
            font-size: 0.8rem;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-submit,
        .btn-cancel {
            justify-content: center;
            width: 100%;
        }

        .bootstrap-select .dropdown-toggle {
            min-height: 42px !important;
            font-size: 0.85rem !important;
        }

        .form-control,
        .form-select {
            min-height: 42px;
            font-size: 0.85rem;
            padding: 0.5rem 0.8rem;
        }
    }

    @media (max-width: 480px) {
        .header-title h1 {
            font-size: 1.3rem;
        }

        .header-title p {
            font-size: 0.8rem;
        }

        .form-label {
            font-size: 0.65rem;
        }

        .radio-label,
        .checkbox-label {
            font-size: 0.75rem;
        }

        .form-control,
        .form-select {
            font-size: 0.8rem;
            min-height: 38px;
            padding: 0.4rem 0.7rem;
            border-radius: 12px;
        }

        .btn-submit,
        .btn-cancel {
            font-size: 0.85rem;
            padding: 0.6rem 1.5rem;
        }

        .bootstrap-select .dropdown-toggle {
            min-height: 38px !important;
            padding: 0.4rem 0.7rem !important;
            font-size: 0.8rem !important;
        }
    }

    /* ===== DARK MODE ===== */
    html[data-pms-theme="dark"] .attendance-create-container {
        background: linear-gradient(145deg, #07130d, #102119);
    }

    html[data-pms-theme="dark"] .header-card,
    html[data-pms-theme="dark"] .form-card {
        background: rgba(16, 33, 25, 0.95);
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .header-title h1 {
        background: linear-gradient(135deg, #60a5fa, #34d399, #22c55e);
        -webkit-background-clip: text;
        background-clip: text;
    }

    html[data-pms-theme="dark"] .header-title p {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .form-label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .form-control,
    html[data-pms-theme="dark"] .form-select {
        background: #183026;
        border-color: rgba(122, 240, 181, 0.2);
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .form-control:focus,
    html[data-pms-theme="dark"] .form-select:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.12);
    }

    html[data-pms-theme="dark"] .form-control::placeholder {
        color: #64748b;
    }

    html[data-pms-theme="dark"] .form-text {
        color: #64748b;
    }

    html[data-pms-theme="dark"] .radio-option {
        background: #183026;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .radio-option:has(input:checked) {
        background: #064e3b;
        border-color: #34d399;
    }

    html[data-pms-theme="dark"] .radio-label {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .checkbox-option {
        background: #183026;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .checkbox-option:has(input:checked) {
        background: #064e3b;
        border-color: #34d399;
    }

    html[data-pms-theme="dark"] .checkbox-label {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .form-actions {
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .btn-cancel {
        background: #183026;
        color: #d9f1e4;
        border-color: rgba(122, 240, 181, 0.2);
    }

    html[data-pms-theme="dark"] .btn-cancel:hover {
        background: #102119;
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .bootstrap-select .dropdown-toggle {
        background: #183026 !important;
        border-color: rgba(122, 240, 181, 0.2) !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .bootstrap-select .dropdown-toggle:focus {
        border-color: #34d399 !important;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.12) !important;
    }

    html[data-pms-theme="dark"] .bootstrap-select .dropdown-menu {
        background: #183026 !important;
        border-color: rgba(122, 240, 181, 0.2) !important;
    }

    html[data-pms-theme="dark"] .bootstrap-select .dropdown-menu .dropdown-item {
        color: #d9f1e4 !important;
    }

    html[data-pms-theme="dark"] .bootstrap-select .dropdown-menu .dropdown-item:hover {
        background: #064e3b !important;
        color: #34d399 !important;
    }

    html[data-pms-theme="dark"] .bootstrap-select .dropdown-menu .dropdown-item.active {
        background: #0f744c !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .bootstrap-select .dropdown-menu .bs-actionsbox {
        border-color: rgba(122, 240, 181, 0.15) !important;
    }

    html[data-pms-theme="dark"] .daterangepicker {
        background: #183026 !important;
        border-color: rgba(122, 240, 181, 0.2) !important;
    }

    html[data-pms-theme="dark"] .daterangepicker .calendar-table {
        background: #183026 !important;
        color: #d9f1e4 !important;
    }

    html[data-pms-theme="dark"] .daterangepicker td.off {
        color: #64748b !important;
    }

    html[data-pms-theme="dark"] .daterangepicker td.in-range {
        background: #064e3b !important;
        color: #34d399 !important;
    }

    html[data-pms-theme="dark"] .daterangepicker td.active,
    html[data-pms-theme="dark"] .daterangepicker td.active:hover {
        background: #0f744c !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .daterangepicker .drp-buttons {
        border-color: rgba(122, 240, 181, 0.15) !important;
    }

    html[data-pms-theme="dark"] .daterangepicker .drp-buttons .btn-primary {
        background: #0f744c !important;
        border-color: #0f744c !important;
    }

    html[data-pms-theme="dark"] .daterangepicker .drp-buttons .btn-default {
        background: #183026 !important;
        color: #d9f1e4 !important;
        border-color: rgba(122, 240, 181, 0.2) !important;
    }

    html[data-pms-theme="dark"] .form-control[type="time"]::-webkit-calendar-picker-indicator {
        filter: invert(0.7);
    }
</style>

<div class="attendance-create-container">
    <div class="ambient-orb orb-1"></div>
    <div class="ambient-orb orb-2"></div>
    <div class="ambient-orb orb-3"></div>

    <div class="content-wrapper">
        <!-- ===== HEADER ===== -->
        <div class="header-card">
            <div class="header-title">
                <h1>
                    <i class="fas fa-user-plus me-2"></i>Add Attendance
                </h1>
                <p><i class="fas fa-info-circle me-1"></i>Mark attendance for employees</p>
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
                <form method="POST" action="{{ route('attendance.store') }}">
                    @csrf

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
                                    <option value="{{ $team->id }}">{{ $team->dpt_name }}</option>
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
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->designation ?? 'N/A' }})</option>
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
                            <select name="location_id" id="location_id" class="form-select" required>
                                @foreach ($location as $locations)
                                    <option @if ($locations->is_default == 1) selected @endif value="{{ $locations->id }}">
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
                                <i class="fas fa-calendar-check"></i> Mark Attendance By
                            </label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input class="form-check-input" type="radio" name="mark_attendance_by" value="month" checked>
                                    <span class="radio-label">Month</span>
                                </label>
                                <label class="radio-option">
                                    <input class="form-check-input" type="radio" name="mark_attendance_by" value="date">
                                    <span class="radio-label">Date Range</span>
                                </label>
                            </div>
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
                            <input type="text" class="form-control" id="date_range" name="date_range" placeholder="MM/DD/YYYY - MM/DD/YYYY">
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
                        <div class="form-group d-none" id="other_location_div">
                            <label class="form-label">
                                <i class="fas fa-map-pin"></i> Other Location
                            </label>
                            <input type="text" name="working_from" id="other_location" class="form-control" placeholder="Enter location...">
                            @error('other_location')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Overwrite Attendance -->
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-exchange-alt"></i> Options</label>
                            <label class="checkbox-option">
                                <input type="checkbox" name="overwrite_attendance" id="overwrite_attendance" class="form-check-input" value="yes">
                                <span class="checkbox-label">Overwrite Existing Attendance</span>
                            </label>
                        </div>

                    </div>

                    <!-- ===== ACTION BUTTONS ===== -->
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-check-circle"></i> Save Attendance
                        </button>
                        <a href="{{ route('leaves.index') }}" class="btn-cancel">
                            <i class="fas fa-times-circle"></i> Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
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
