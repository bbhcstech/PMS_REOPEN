@extends('admin.layout.app')

@section('title', 'Notification Settings')

@push('styles')
<style>
    .notification-settings-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f4fbf7 100%);
        color: #0a2e1f;
    }

    .notification-settings-shell {
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
    .notification-settings-page .stat-card,
    .notification-settings-page .stat-card:first-of-type {
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

    .notification-settings-page .stat-card:first-of-type *,
    .notification-settings-page .stat-card * {
        -webkit-text-fill-color: initial;
    }

    .notification-settings-page .stat-card h3,
    .notification-settings-page .stat-card:first-of-type h3 {
        color: #0a2e1f !important;
        -webkit-text-fill-color: #0a2e1f !important;
    }

    .notification-settings-page .stat-card h6,
    .notification-settings-page .stat-card span,
    .notification-settings-page .stat-card:first-of-type span,
    .notification-settings-page .stat-card:first-of-type h6 {
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

    .stat-icon.email,
    .notification-settings-page .stat-card:first-of-type .stat-icon.email {
        background: linear-gradient(145deg, #d1fae5, #a7f3d0) !important;
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
    }

    .stat-icon.bell,
    .notification-settings-page .stat-card .stat-icon.bell {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
    }

    .stat-icon.clock,
    .notification-settings-page .stat-card .stat-icon.clock {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
    }

    .stat-icon.digest,
    .notification-settings-page .stat-card .stat-icon.digest {
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
        font-size: 1.25rem;
        font-weight: 800;
        color: #0a2e1f;
        margin: 0;
        line-height: 1.2;
    }

    /* ===== FORM CARD & SWITCHES ===== */
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

    /* Policy Switch Box */
    .policy-switch-box {
        background: #f0fdf4;
        border: 1px solid rgba(16, 185, 129, 0.25);
        border-radius: 20px;
        padding: 1.4rem 1.6rem;
        transition: all 0.25s ease;
        height: 100%;
        display: flex;
        align-items: center;
    }

    .policy-switch-box:hover {
        border-color: #34d399;
        background: #ecfdf5;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.1);
        transform: translateY(-2px);
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

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .notification-settings-page {
            padding: 1.25rem 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="notification-settings-page">
    <div class="notification-settings-shell">
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
                <span>Notification & Alert Triggers</span>
            </div>

            <!-- Page Header Card -->
            <div class="branches-header">
                <div class="header-left-box">
                    <div class="header-icon-badge">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="header-title">
                        <h1>Notification & Alert Settings</h1>
                        <p>Configure email broadcasts, in-app alerts, task assignment notifications, and reminder triggers.</p>
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
                $emailNotif = ($settings['email_notifications'] ?? '1') == '1';
                $sysNotif = ($settings['system_notifications'] ?? '1') == '1';
                $taskAlert = ($settings['task_assignment_alert'] ?? '1') == '1';
                $leaveAlert = ($settings['leave_request_alert'] ?? '1') == '1';
                $attReminder = ($settings['attendance_reminder'] ?? '1') == '1';
                $digest = ($settings['daily_summary_digest'] ?? '0') == '1';
            @endphp
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon email">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Email Channel</h6>
                        <h3>{{ $emailNotif ? 'Active' : 'Disabled' }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bell">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="stat-info">
                        <h6>In-App Bell Alerts</h6>
                        <h3>{{ $sysNotif ? 'Active' : 'Disabled' }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon clock">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Clock Reminders</h6>
                        <h3>{{ $attReminder ? 'Active' : 'Disabled' }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon digest">
                        <i class="fas fa-file-lines"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Daily Digest Email</h6>
                        <h3>{{ $digest ? 'Active' : 'Disabled' }}</h3>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="address-card-elevated">
                <div class="card-header-custom">
                    <div class="card-header-avatar shadow-sm">
                        <i class="fas fa-sliders"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold fs-5" style="color: #0a2e1f;">Global System & Email Notification Channels</h5>
                        <small class="text-muted">Control default notification behavior, alerts, and automated email reminders across the platform</small>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <form method="POST" action="{{ route('admin.settings.notification.update') }}">
                        @csrf

                        <!-- Section 1: Core Channels & Alerts -->
                        <div class="section-badge">
                            <i class="fas fa-tower-cell"></i> Core Channels & Event Triggers
                        </div>

                        <div class="row g-4">
                            <!-- Email Notifications -->
                            <div class="col-md-6">
                                <div class="policy-switch-box">
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-3 w-100">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="email_notifications" value="1" id="emailNotifSwitch"
                                            style="width: 2.8em; height: 1.4em; cursor: pointer;"
                                            {{ $emailNotif ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="emailNotifSwitch" style="cursor: pointer;">
                                            Enable Email Notification Channel
                                            <small class="d-block text-muted fw-normal mt-0.5" style="font-size: 0.82rem;">Send automated system emails for critical updates and events.</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- In-App Bell Notifications -->
                            <div class="col-md-6">
                                <div class="policy-switch-box">
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-3 w-100">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="system_notifications" value="1" id="sysNotifSwitch"
                                            style="width: 2.8em; height: 1.4em; cursor: pointer;"
                                            {{ $sysNotif ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="sysNotifSwitch" style="cursor: pointer;">
                                            Enable In-App Bell Notifications
                                            <small class="d-block text-muted fw-normal mt-0.5" style="font-size: 0.82rem;">Display real-time notification badges in user header bar.</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Task Assignment Alerts -->
                            <div class="col-md-6">
                                <div class="policy-switch-box">
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-3 w-100">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="task_assignment_alert" value="1" id="taskAlertSwitch"
                                            style="width: 2.8em; height: 1.4em; cursor: pointer;"
                                            {{ $taskAlert ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="taskAlertSwitch" style="cursor: pointer;">
                                            Task Assignment Alerts
                                            <small class="d-block text-muted fw-normal mt-0.5" style="font-size: 0.82rem;">Notify team members immediately when assigned a new task.</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Leave Application Alerts -->
                            <div class="col-md-6">
                                <div class="policy-switch-box">
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-3 w-100">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="leave_request_alert" value="1" id="leaveAlertSwitch"
                                            style="width: 2.8em; height: 1.4em; cursor: pointer;"
                                            {{ $leaveAlert ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="leaveAlertSwitch" style="cursor: pointer;">
                                            Leave Application Alerts
                                            <small class="d-block text-muted fw-normal mt-0.5" style="font-size: 0.82rem;">Notify managers when an employee submits or cancels leave.</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Daily Clock-In Reminders -->
                            <div class="col-md-6">
                                <div class="policy-switch-box">
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-3 w-100">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="attendance_reminder" value="1" id="attReminderSwitch"
                                            style="width: 2.8em; height: 1.4em; cursor: pointer;"
                                            {{ $attReminder ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="attReminderSwitch" style="cursor: pointer;">
                                            Daily Clock-In / Out Reminders
                                            <small class="d-block text-muted fw-normal mt-0.5" style="font-size: 0.82rem;">Send automated reminders if employee forgets to clock in.</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Daily Activity Digest -->
                            <div class="col-md-6">
                                <div class="policy-switch-box">
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-3 w-100">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="daily_summary_digest" value="1" id="digestSwitch"
                                            style="width: 2.8em; height: 1.4em; cursor: pointer;"
                                            {{ $digest ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="digestSwitch" style="cursor: pointer;">
                                            Admin Daily Activity Summary Digest
                                            <small class="d-block text-muted fw-normal mt-0.5" style="font-size: 0.82rem;">Send a daily summary email of attendance and completed tasks.</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="mt-5 pt-4 border-top d-flex justify-content-end">
                            <button type="submit" class="btn-save-address">
                                <i class="fas fa-save me-1.5"></i> Save Notification Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
