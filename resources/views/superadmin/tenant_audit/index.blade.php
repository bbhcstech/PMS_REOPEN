@extends('layouts.superadmin')

@section('title', 'Activity Logs & Audit Monitoring Center - Super Admin')

@push('styles')
<style>
    /* CSS Variables & Tokens */
    :root {
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --primary-light: #eff6ff;
        --primary-border: #bfdbfe;
        --success: #10b981;
        --success-light: #ecfdf5;
        --success-border: #a7f3d0;
        --warning: #f59e0b;
        --warning-light: #fffbeb;
        --warning-border: #fde68a;
        --danger: #ef4444;
        --danger-light: #fef2f2;
        --danger-border: #fecaca;
        --purple: #8b5cf6;
        --purple-light: #f5f3ff;
        --purple-border: #ddd6fe;
        --cyan: #06b6d4;
        --cyan-light: #ecfeff;
        --bg-surface: #ffffff;
        --bg-subtle: #f8fafc;
        --bg-hover: #f1f5f9;
        --border-color: #e2e8f0;
        --border-subtle: #cbd5e1;
        --text-main: #0f172a;
        --text-muted: #334155;
        --text-subtle: #64748b;
        --radius-xl: 16px;
        --radius-lg: 12px;
        --radius-md: 8px;
        --radius-sm: 6px;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(15, 23, 42, 0.06), 0 2px 4px -1px rgba(15, 23, 42, 0.04);
        --shadow-lg: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
    }

    .audit-container {
        padding: 24px 32px 48px;
        max-width: 1680px;
        margin: 0 auto;
    }

    /* Page Header */
    .page-header-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
        background: var(--bg-surface);
        padding: 20px 24px;
        border-radius: var(--radius-xl);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
    }

    .breadcrumbs-bar {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-subtle);
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .breadcrumbs-bar a {
        color: var(--text-subtle);
        text-decoration: none;
        transition: color 0.2s;
    }

    .breadcrumbs-bar a:hover {
        color: var(--primary);
    }

    .page-title {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -0.5px;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .page-subtitle {
        font-size: 13.5px;
        color: var(--text-muted);
        margin-top: 4px;
        margin-bottom: 0;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .live-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--success-light);
        color: #047857;
        font-size: 12px;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 20px;
        border: 1px solid var(--success-border);
    }

    .live-status-pill .pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--success);
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulseAnimation 2s infinite;
    }

    @keyframes pulseAnimation {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
        }
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }

    /* Buttons */
    .btn-action-primary {
        background: var(--primary);
        color: #ffffff;
        border: 1px solid var(--primary-hover);
        border-radius: var(--radius-md);
        padding: 9px 16px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        box-shadow: 0 1px 2px rgba(37, 99, 235, 0.2);
    }

    .btn-action-primary:hover {
        background: var(--primary-hover);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .btn-action-secondary {
        background: var(--bg-surface);
        color: var(--text-muted);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 9px 15px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-action-secondary:hover {
        background: var(--bg-subtle);
        color: var(--text-main);
        border-color: var(--border-subtle);
    }

    /* KPI Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    @media (max-width: 1400px) {
        .kpi-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .kpi-grid {
            grid-template-columns: 1fr;
        }
    }

    .kpi-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 18px 20px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
        user-select: none;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary-border);
    }

    .kpi-card.active-kpi-filter {
        border-color: var(--primary);
        background: var(--primary-light);
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
    }

    .kpi-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .kpi-title {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-subtle);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .kpi-value {
        font-size: 26px;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -0.5px;
        line-height: 1.1;
        margin-bottom: 6px;
    }

    .kpi-desc {
        font-size: 11.5px;
        color: var(--text-subtle);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Search & Filter Bar */
    .filter-panel {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 16px 20px;
        margin-bottom: 24px;
        box-shadow: var(--shadow-sm);
    }

    .filter-inputs-grid {
        display: grid;
        grid-template-columns: 2fr repeat(5, 1fr) auto;
        gap: 12px;
        align-items: center;
    }

    @media (max-width: 1200px) {
        .filter-inputs-grid {
            grid-template-columns: 1fr 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .filter-inputs-grid {
            grid-template-columns: 1fr;
        }
    }

    .search-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-input-wrap i {
        position: absolute;
        left: 14px;
        color: var(--text-subtle);
        font-size: 16px;
        pointer-events: none;
    }

    .search-input {
        width: 100%;
        padding: 9px 14px 9px 40px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 13px;
        color: var(--text-main);
        background: var(--bg-surface);
        outline: none;
        transition: all 0.2s;
    }

    .search-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .filter-select {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 13px;
        color: var(--text-main);
        background: var(--bg-surface);
        outline: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .active-chips-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px border-subtle;
        border-top: 1px solid var(--border-color);
    }

    .chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--primary-light);
        color: var(--primary);
        border: 1px solid var(--primary-border);
        font-size: 11.5px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
    }

    .chip-remove {
        cursor: pointer;
        font-size: 12px;
        opacity: 0.7;
        transition: opacity 0.2s;
    }

    .chip-remove:hover {
        opacity: 1;
    }

    /* Table System */
    .table-card {
        background: var(--bg-surface);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .table-container {
        overflow-x: auto;
        max-height: 680px;
    }

    .grid-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        text-align: left;
        font-size: 13px;
        border: 1px solid var(--border-color);
    }

    .grid-table th {
        background: var(--bg-subtle);
        color: var(--text-subtle);
        font-weight: 700;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
        border-right: 1px solid var(--border-color);
        position: sticky;
        top: 0;
        z-index: 10;
        white-space: nowrap;
    }

    .grid-table th:last-child {
        border-right: none;
    }

    .grid-table th.sortable {
        cursor: pointer;
        user-select: none;
    }

    .grid-table th.sortable:hover {
        color: var(--primary);
        background: var(--bg-hover);
    }

    .grid-table tbody tr {
        transition: background 0.15s;
    }

    .grid-table tbody tr:hover {
        background: var(--bg-subtle);
    }

    .grid-table td {
        padding: 14px 16px;
        vertical-align: middle;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border-color);
        border-right: 1px solid var(--border-color);
    }

    .grid-table td:last-child {
        border-right: none;
    }

    .grid-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Action Type Icons */
    .action-icon-badge {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    /* Result Badges */
    .badge-result {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-result-success {
        background: var(--success-light);
        color: #047857;
        border: 1px solid var(--success-border);
    }

    .badge-result-failed {
        background: var(--danger-light);
        color: #b91c1c;
        border: 1px solid var(--danger-border);
    }

    .badge-result-warning {
        background: var(--warning-light);
        color: #b45309;
        border: 1px solid var(--warning-border);
    }

    .security-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #fdf2f8;
        color: #be185d;
        border: 1px solid #fbcfe8;
        font-size: 10.5px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
        margin-left: 6px;
    }

    /* Pagination */
    .table-pagination-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        background: var(--bg-subtle);
        border-top: 1px solid var(--border-color);
        font-size: 13px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .page-num-btn {
        min-width: 32px;
        height: 32px;
        padding: 0 8px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        background: var(--bg-surface);
        color: var(--text-muted);
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .page-num-btn:hover:not(:disabled) {
        border-color: var(--primary);
        color: var(--primary);
    }

    .page-num-btn.active {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
    }

    .page-num-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Slide-Over Drawer */
    .drawer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(3px);
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .drawer-overlay.open {
        opacity: 1;
        visibility: visible;
    }

    .drawer-panel {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: 580px;
        max-width: 95vw;
        background: var(--bg-surface);
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.15);
        z-index: 1001;
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
    }

    .drawer-overlay.open .drawer-panel {
        transform: translateX(0);
    }

    .drawer-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-subtle);
    }

    .drawer-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
    }

    /* Diff View */
    .diff-box {
        background: #1e293b;
        color: #f8fafc;
        border-radius: var(--radius-md);
        padding: 16px;
        font-family: monospace;
        font-size: 12px;
        line-height: 1.6;
        overflow-x: auto;
    }

    .diff-removed {
        color: #fca5a5;
        background: rgba(239, 68, 68, 0.15);
        display: block;
        padding: 2px 6px;
        border-radius: 4px;
        margin-bottom: 2px;
    }

    .diff-added {
        color: #86efac;
        background: rgba(16, 185, 129, 0.15);
        display: block;
        padding: 2px 6px;
        border-radius: 4px;
    }

    /* Timeline Workflow */
    .workflow-timeline {
        position: relative;
        padding-left: 24px;
        margin-top: 12px;
    }

    .workflow-timeline::before {
        content: '';
        position: absolute;
        left: 9px;
        top: 6px;
        bottom: 6px;
        width: 2px;
        background: var(--border-color);
    }

    .timeline-step {
        position: relative;
        margin-bottom: 16px;
    }

    .timeline-step:last-child {
        margin-bottom: 0;
    }

    .timeline-step-dot {
        position: absolute;
        left: -24px;
        top: 3px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: var(--bg-surface);
        border: 2px solid var(--primary);
        z-index: 2;
    }

    .timeline-step.completed .timeline-step-dot {
        background: var(--success);
        border-color: var(--success);
    }

    .timeline-step.failed .timeline-step-dot {
        background: var(--danger);
        border-color: var(--danger);
    }

    .timeline-step-title {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--text-main);
    }

    .timeline-step-sub {
        font-size: 11px;
        color: var(--text-subtle);
    }

    /* Modal dialogs */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(3px);
        z-index: 1100;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-backdrop.show {
        display: flex;
    }

    .modal-box {
        background: var(--bg-surface);
        border-radius: var(--radius-xl);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-lg);
        width: 540px;
        max-width: 100%;
        overflow: hidden;
        animation: modalSlideUp 0.25s ease-out;
    }

    @keyframes modalSlideUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .modal-header {
        padding: 18px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-subtle);
    }

    .modal-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--text-main);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .modal-body {
        padding: 24px;
    }

    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border-color);
        background: var(--bg-subtle);
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }

    /* Empty state */
    .empty-state-box {
        text-align: center;
        padding: 64px 24px;
    }

    .empty-state-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: var(--primary-light);
        color: var(--primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        margin-bottom: 16px;
    }

    .empty-state-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 6px;
    }

    .empty-state-desc {
        font-size: 13.5px;
        color: var(--text-subtle);
        margin-bottom: 20px;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }
</style>
@endpush

@section('content')
<div class="audit-container">

    <!-- 1. BREADCRUMBS & PAGE HEADER -->
    <div class="page-header-box">
        <div>
            <div class="breadcrumbs-bar">
                <a href="{{ url('/super-admin/dashboard') }}">Super Admin</a>
                <i class="bx bx-chevron-right"></i>
                <span>Security &amp; Audit</span>
                <i class="bx bx-chevron-right"></i>
                <span style="color: var(--text-main); font-weight: 700;">Activity Logs</span>
            </div>
            <h1 class="page-title">
                <i class="bx bx-history" style="color: var(--primary);"></i>
                Activity Logs
            </h1>
            <p class="page-subtitle">Track and audit administrative actions, tenant activity, security events, and platform changes.</p>
        </div>

        <div class="header-actions">
            <div class="live-status-pill">
                <span class="pulse-dot"></span>
                <span>Last updated: <span id="lastUpdatedTimer">Just now</span></span>
            </div>

            <button class="btn-action-secondary" id="openExportModalBtn">
                <i class="bx bx-export"></i> Export Logs
            </button>
            <button class="btn-action-secondary" id="refreshBtn" title="Refresh Telemetry">
                <i class="bx bx-refresh"></i> Refresh
            </button>
            <button class="btn-action-secondary" id="toggleFilterPanelBtn">
                <i class="bx bx-filter-alt"></i> Filter
            </button>
            <button class="btn-action-secondary" id="openSettingsBtn">
                <i class="bx bx-cog"></i> Audit Settings
            </button>
        </div>
    </div>

    <!-- 2. SUMMARY KPI CARDS (6 CARDS WITH CLICK-TO-FILTER) -->
    <div class="kpi-grid">
        <!-- 1. Total Activities -->
        <div class="kpi-card active-kpi-filter" id="kpiTotalCard" onclick="applyKpiFilter('all')">
            <div class="kpi-card-header">
                <span class="kpi-title">Total Activities</span>
                <div class="kpi-icon" style="background: var(--primary-light); color: var(--primary);">
                    <i class="bx bx-list-check"></i>
                </div>
            </div>
            <div class="kpi-value">{{ $kpi['total_events'] }}</div>
            <div class="kpi-desc">
                <span style="color: var(--success); font-weight: 700;"><i class="bx bx-trending-up"></i> +12.4%</span> total trail
            </div>
        </div>

        <!-- 2. Today's Activities -->
        <div class="kpi-card" id="kpiTodayCard" onclick="applyKpiFilter('today')">
            <div class="kpi-card-header">
                <span class="kpi-title">Today's Activities</span>
                <div class="kpi-icon" style="background: var(--cyan-light); color: var(--cyan);">
                    <i class="bx bx-time-five"></i>
                </div>
            </div>
            <div class="kpi-value" style="color: var(--cyan);">{{ $kpi['today_activities'] }}</div>
            <div class="kpi-desc">
                <span>Past 24 hours log</span>
            </div>
        </div>

        <!-- 3. Admin Actions -->
        <div class="kpi-card" id="kpiAdminCard" onclick="applyKpiFilter('admin')">
            <div class="kpi-card-header">
                <span class="kpi-title">Admin Actions</span>
                <div class="kpi-icon" style="background: var(--purple-light); color: var(--purple);">
                    <i class="bx bx-user-voice"></i>
                </div>
            </div>
            <div class="kpi-value" style="color: var(--purple);">{{ $kpi['admin_actions'] }}</div>
            <div class="kpi-desc">
                <span>Super &amp; Tenant Admins</span>
            </div>
        </div>

        <!-- 4. Security Events -->
        <div class="kpi-card" id="kpiSecurityCard" onclick="applyKpiFilter('security')">
            <div class="kpi-card-header">
                <span class="kpi-title">Security Events</span>
                <div class="kpi-icon" style="background: #fdf2f8; color: #be185d;">
                    <i class="bx bx-shield-quarter"></i>
                </div>
            </div>
            <div class="kpi-value" style="color: #be185d;">{{ $kpi['security_events'] }}</div>
            <div class="kpi-desc">
                <span>Auth &amp; Permission changes</span>
            </div>
        </div>

        <!-- 5. Failed Actions -->
        <div class="kpi-card" id="kpiFailedCard" onclick="applyKpiFilter('failed')">
            <div class="kpi-card-header">
                <span class="kpi-title">Failed Actions</span>
                <div class="kpi-icon" style="background: var(--danger-light); color: var(--danger);">
                    <i class="bx bx-x-circle"></i>
                </div>
            </div>
            <div class="kpi-value" style="color: var(--danger);">{{ $kpi['failed_actions'] }}</div>
            <div class="kpi-desc">
                <span style="color: var(--danger); font-weight: 700;">Unsuccessful attempts</span>
            </div>
        </div>

        <!-- 6. Active Sessions -->
        <div class="kpi-card" id="kpiActiveCard" onclick="applyKpiFilter('sessions')">
            <div class="kpi-card-header">
                <span class="kpi-title">Active Sessions</span>
                <div class="kpi-icon" style="background: var(--success-light); color: var(--success);">
                    <i class="bx bx-pulse"></i>
                </div>
            </div>
            <div class="kpi-value" style="color: var(--success);">{{ $kpi['active_sessions'] }}</div>
            <div class="kpi-desc">
                <span style="color: var(--success); font-weight: 700;">● Active telemetry</span>
            </div>
        </div>
    </div>

    <!-- 3. ADVANCED SEARCH & FILTER BAR -->
    <div class="filter-panel" id="filterPanel">
        <div class="filter-inputs-grid">
            <!-- Search -->
            <div class="search-input-wrap">
                <i class="bx bx-search"></i>
                <input type="text" class="search-input" id="auditSearchInput" placeholder="Search user, company, action, IP address, resource..." />
            </div>

            <!-- ACTOR Filter -->
            <select class="filter-select" id="actorFilter">
                <option value="">ACTOR: All</option>
                <option value="Super Admin">Super Admin</option>
                <option value="Company Admin">Company Admin</option>
                <option value="Manager">Manager</option>
                <option value="HR">HR</option>
                <option value="Employee">Employee</option>
                <option value="System">System</option>
            </select>

            <!-- COMPANY Filter -->
            <select class="filter-select" id="companyFilter">
                <option value="">COMPANY: All Companies</option>
                @foreach($companies as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>

            <!-- ACTION Filter -->
            <select class="filter-select" id="actionFilter">
                <option value="">ACTION: All Actions</option>
                <option value="Created">Created</option>
                <option value="Updated">Updated</option>
                <option value="Deleted">Deleted</option>
                <option value="Login">Login</option>
                <option value="Logout">Logout</option>
                <option value="Subscription Changed">Subscription Changed</option>
                <option value="Permission Changed">Permission Changed</option>
                <option value="Backup">Backup</option>
                <option value="Migration">Migration</option>
                <option value="Database">Database</option>
                <option value="Settings">Settings</option>
                <option value="Security">Security</option>
                <option value="Export">Export</option>
                <option value="Other">Other</option>
            </select>

            <!-- MODULE Filter -->
            <select class="filter-select" id="moduleFilter">
                <option value="">MODULE: All Modules</option>
                <option value="Companies">Companies</option>
                <option value="Users">Users</option>
                <option value="Subscriptions">Subscriptions</option>
                <option value="Plans">Plans</option>
                <option value="Backups">Backups</option>
                <option value="Migrations">Migrations</option>
                <option value="Tenant Audit">Tenant Audit</option>
                <option value="System Health">System Health</option>
                <option value="Settings">Settings</option>
                <option value="Security">Security</option>
            </select>

            <!-- RESULT Filter -->
            <select class="filter-select" id="statusFilter">
                <option value="">RESULT: All Results</option>
                <option value="success">Success</option>
                <option value="failed">Failed</option>
                <option value="warning">Warning</option>
            </select>

            <!-- DATE Filter -->
            <select class="filter-select" id="dateFilter">
                <option value="">DATE: All Time</option>
                <option value="today">Today</option>
                <option value="yesterday">Yesterday</option>
                <option value="7days">Last 7 days</option>
                <option value="30days">Last 30 days</option>
            </select>

            <!-- Clear Filters -->
            <button class="btn-action-secondary" id="resetFiltersBtn" style="white-space: nowrap;">
                <i class="bx bx-x"></i> Clear Filters
            </button>
        </div>

        <div class="active-chips-bar" id="activeChipsBar" style="display: none;">
            <span style="font-size: 11px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase;">Active Filters:</span>
            <div id="chipsContainer" style="display: inline-flex; gap: 6px; flex-wrap: wrap;"></div>
        </div>
    </div>

    <!-- 4. ENTERPRISE ACTIVITY TABLE -->
    <div class="table-card">
        <div class="table-container">
            <table class="grid-table" id="activityTable">
                <thead>
                    <tr>
                        <th class="sortable" onclick="sortTable(0)">Timestamp <i class="bx bx-sort-alt-2"></i></th>
                        <th class="sortable" onclick="sortTable(1)">Actor <i class="bx bx-sort-alt-2"></i></th>
                        <th class="sortable" onclick="sortTable(2)">Action <i class="bx bx-sort-alt-2"></i></th>
                        <th class="sortable" onclick="sortTable(3)">Company <i class="bx bx-sort-alt-2"></i></th>
                        <th class="sortable" onclick="sortTable(4)">Module <i class="bx bx-sort-alt-2"></i></th>
                        <th class="sortable" onclick="sortTable(5)">Resource <i class="bx bx-sort-alt-2"></i></th>
                        <th style="text-align: center;">Result</th>
                        <th>IP Address</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="activityTableBody">
                    @forelse($allEvents as $evt)
                    @php
                        $actStr = $evt['action'];
                        $iconClass = 'bx-list-check';
                        $bgIconColor = 'var(--primary-light)';
                        $textIconColor = 'var(--primary)';

                        if (str_contains(strtolower($actStr), 'company')) { $iconClass = 'bx-building-house'; $bgIconColor = 'var(--primary-light)'; $textIconColor = 'var(--primary)'; }
                        elseif (str_contains(strtolower($actStr), 'sub') || str_contains(strtolower($actStr), 'plan')) { $iconClass = 'bx-credit-card'; $bgIconColor = 'var(--purple-light)'; $textIconColor = 'var(--purple)'; }
                        elseif (str_contains(strtolower($actStr), 'user')) { $iconClass = 'bx-user-plus'; $bgIconColor = 'var(--cyan-light)'; $textIconColor = 'var(--cyan)'; }
                        elseif (str_contains(strtolower($actStr), 'permission') || str_contains(strtolower($actStr), 'role')) { $iconClass = 'bx-key'; $bgIconColor = '#fdf2f8'; $textIconColor = '#be185d'; }
                        elseif (str_contains(strtolower($actStr), 'backup')) { $iconClass = 'bx-hard-drive'; $bgIconColor = 'var(--warning-light)'; $textIconColor = 'var(--warning)'; }
                        elseif (str_contains(strtolower($actStr), 'migration')) { $iconClass = 'bx-git-repo-forked'; $bgIconColor = 'var(--success-light)'; $textIconColor = 'var(--success)'; }
                        elseif (str_contains(strtolower($actStr), 'login')) { $iconClass = 'bx-log-in-circle'; $bgIconColor = 'var(--success-light)'; $textIconColor = 'var(--success)'; }
                        elseif (str_contains(strtolower($actStr), 'security')) { $iconClass = 'bx-shield-quarter'; $bgIconColor = 'var(--danger-light)'; $textIconColor = 'var(--danger)'; }
                        elseif (str_contains(strtolower($actStr), 'database')) { $iconClass = 'bx-cylinder'; $bgIconColor = '#f1f5f9'; $textIconColor = '#475569'; }
                        elseif (str_contains(strtolower($actStr), 'setting')) { $iconClass = 'bx-cog'; $bgIconColor = '#f1f5f9'; $textIconColor = '#475569'; }
                    @endphp
                    <tr class="activity-row"
                        data-actor="{{ $evt['role'] }}"
                        data-company-id="{{ $evt['company_id'] }}"
                        data-action-type="{{ $evt['action_type'] }}"
                        data-module="{{ $evt['module'] }}"
                        data-status="{{ $evt['status'] }}"
                        data-is-security="{{ $evt['is_security'] ? '1' : '0' }}"
                        data-search="{{ strtolower($evt['user_name'].' '.$evt['user_email'].' '.$evt['company_name'].' '.$evt['action'].' '.$evt['resource'].' '.$evt['ip_address']) }}">
                        
                        <!-- TIMESTAMP -->
                        <td>
                            <div style="font-weight: 700; color: var(--text-main); font-size: 13px;">{{ $evt['formatted_time'] }}</div>
                            <div style="font-size: 11px; color: var(--text-subtle);">{{ substr($evt['date_str'], 0, 11) }}</div>
                        </td>

                        <!-- ACTOR -->
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #1e293b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 11.5px; flex-shrink: 0;">
                                    {{ strtoupper(substr($evt['user_name'], 0, 2)) }}
                                </div>
                                <div>
                                    <strong style="color: var(--text-main); font-size: 13px; display: block;">{{ $evt['user_name'] }}</strong>
                                    <span style="font-size: 11px; color: var(--text-subtle);">{{ $evt['role'] }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- ACTION -->
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div class="action-icon-badge" style="background: {{ $bgIconColor }}; color: {{ $textIconColor }};">
                                    <i class="bx {{ $iconClass }}"></i>
                                </div>
                                <div>
                                    <strong style="color: var(--text-main); font-size: 13px;">
                                        {{ $evt['action'] }}
                                        @if($evt['is_security'])
                                            <span class="security-tag"><i class="bx bx-shield"></i> Security</span>
                                        @endif
                                    </strong>
                                </div>
                            </div>
                        </td>

                        <!-- COMPANY -->
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 28px; height: 28px; border-radius: 6px; background: #f1f5f9; color: #334155; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 11px; flex-shrink: 0; border: 1px solid var(--border-color); overflow: hidden;">
                                    @if(!empty($evt['logo_url']))
                                        <img src="{{ $evt['logo_url'] }}" alt="{{ $evt['company_name'] }}" style="width: 100%; height: 100%; object-fit: cover;" />
                                    @else
                                        {{ strtoupper(substr($evt['company_name'], 0, 2)) }}
                                    @endif
                                </div>
                                <div>
                                    <strong style="color: var(--text-main); font-size: 13px;">{{ $evt['company_name'] }}</strong>
                                    <span style="font-family: monospace; font-size: 10.5px; color: var(--text-subtle); display: block;">{{ $evt['company_code'] }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- MODULE -->
                        <td>
                            <span style="font-size: 12px; font-weight: 600; color: var(--text-muted); background: var(--bg-subtle); padding: 3px 8px; border-radius: 6px; border: 1px solid var(--border-color);">
                                {{ $evt['module'] }}
                            </span>
                        </td>

                        <!-- RESOURCE -->
                        <td>
                            <div style="font-weight: 600; color: var(--text-main); font-size: 12.5px;">{{ $evt['resource'] }}</div>
                            <span style="font-family: monospace; font-size: 10.5px; color: var(--text-subtle);">{{ $evt['resource_id'] }}</span>
                        </td>

                        <!-- RESULT -->
                        <td style="text-align: center;">
                            @if($evt['status'] === 'success')
                                <span class="badge-result badge-result-success"><i class="bx bx-check-circle"></i> Success</span>
                            @elseif($evt['status'] === 'failed')
                                <span class="badge-result badge-result-failed"><i class="bx bx-x-circle"></i> Failed</span>
                            @else
                                <span class="badge-result badge-result-warning"><i class="bx bx-error"></i> Warning</span>
                            @endif
                        </td>

                        <!-- IP ADDRESS -->
                        <td>
                            <code style="font-family: monospace; font-size: 11.5px; background: #f1f5f9; padding: 3px 7px; border-radius: 4px; color: #475569; border: 1px solid #e2e8f0;">
                                {{ $evt['ip_address'] }}
                            </code>
                        </td>

                        <!-- ACTIONS -->
                        <td style="text-align: right;">
                            <button class="btn-action-secondary open-drawer-btn"
                                    data-id="{{ $evt['id'] }}"
                                    data-company="{{ $evt['company_name'] }}"
                                    data-code="{{ $evt['company_code'] }}"
                                    data-domain="{{ $evt['domain'] }}"
                                    data-user-name="{{ $evt['user_name'] }}"
                                    data-user-email="{{ $evt['user_email'] }}"
                                    data-role="{{ $evt['role'] }}"
                                    data-time="{{ $evt['date_str'] }}"
                                    data-module="{{ $evt['module'] }}"
                                    data-action="{{ $evt['action'] }}"
                                    data-resource="{{ $evt['resource'] }}"
                                    data-resource-id="{{ $evt['resource_id'] }}"
                                    data-status="{{ $evt['status'] }}"
                                    data-severity="{{ $evt['severity'] }}"
                                    data-desc="{{ $evt['description'] }}"
                                    data-ip="{{ $evt['ip_address'] }}"
                                    data-browser="{{ $evt['browser'] }}"
                                    data-os="{{ $evt['os'] }}"
                                    data-session="{{ $evt['session_id'] }}"
                                    data-diff="{{ json_encode($evt['diff']) }}"
                                    style="padding: 5px 12px; font-size: 12px;">
                                <i class="bx bx-show"></i> View
                            </button>
                        </td>
                    </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- EMPTY STATE (CONTAINED INSIDE TABLE BODY WHEN FILTER EMPTY) -->
        <div class="empty-state-box" id="emptyState" style="display: none;">
            <div class="empty-state-icon">
                <i class="bx bx-search-alt"></i>
            </div>
            <h3 class="empty-state-title">No activity found</h3>
            <p class="empty-state-desc">Activity matching your current filters will appear here. Try clearing filters or refining your search term.</p>
            <button class="btn-action-primary" onclick="resetAllFilters()">
                <i class="bx bx-x"></i> Clear Filters
            </button>
        </div>

        <!-- FOOTER PAGINATION -->
        <div class="table-pagination-bar">
            <div style="color: var(--text-subtle); font-weight: 500;">
                Showing <span id="showingStart">1</span> to <span id="showingEnd">{{ min(25, count($allEvents)) }}</span> of <span id="totalEventsCount">{{ count($allEvents) }}</span> activity logs
            </div>

            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-subtle);">
                    <span>Rows per page:</span>
                    <select id="rowsPerPageSelect" class="filter-select" style="padding: 4px 8px; font-size: 12px; width: 70px;">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div style="display: flex; gap: 4px;" id="paginationButtons">
                    <button class="page-num-btn" id="prevPageBtn" disabled><i class="bx bx-chevron-left"></i></button>
                    <button class="page-num-btn active">1</button>
                    <button class="page-num-btn">2</button>
                    <button class="page-num-btn" id="nextPageBtn"><i class="bx bx-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. AUDIT INTEGRITY INFORMATION -->
    <div style="background: var(--bg-surface); border-radius: var(--radius-lg); border: 1px solid var(--border-color); padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                <i class="bx bx-shield-check"></i>
            </div>
            <div>
                <h4 style="font-size: 14px; font-weight: 800; color: var(--text-main); margin: 0 0 2px 0;">Audit Integrity &amp; Compliance Safeguard</h4>
                <p style="font-size: 12.5px; color: var(--text-subtle); margin: 0;">Activity logs are recorded for platform accountability, administrative security, and compliance auditing.</p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 16px; font-size: 12px; font-weight: 600;">
            <div style="display: flex; align-items: center; gap: 6px; color: var(--text-muted);">
                <span>Logging Status:</span> <strong style="color: var(--success);">Active</strong>
            </div>
            <div style="display: flex; align-items: center; gap: 6px; color: var(--text-muted);">
                <span>Last Event:</span> <strong>2 minutes ago</strong>
            </div>
            <div style="display: flex; align-items: center; gap: 6px; color: var(--text-muted);">
                <span>Audit Service:</span> <strong style="color: var(--primary);">Healthy</strong>
            </div>
        </div>
    </div>

</div>

<!-- 6. SLIDE-OVER AUDIT DETAILS DRAWER -->
<div class="drawer-overlay" id="auditDetailsDrawer">
    <div class="drawer-panel" id="drawerPanel">
        <div class="drawer-header">
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-family: monospace; font-size: 13px; font-weight: 800; color: var(--primary); background: var(--primary-light); padding: 3px 8px; border-radius: 6px;" id="drawerEventId">EVT-2026-000101</span>
                    <span id="drawerResultBadge"></span>
                </div>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--text-main); margin: 6px 0 0 0;" id="drawerActionTitle">Subscription Changed</h3>
            </div>
            <button class="btn-action-secondary" id="closeDrawerBtn" style="padding: 6px 10px;">
                <i class="bx bx-x" style="font-size: 20px;"></i>
            </button>
        </div>

        <div class="drawer-body">
            <!-- SECTION 1: ACTIVITY OVERVIEW -->
            <div style="background: var(--bg-subtle); border-radius: var(--radius-md); padding: 16px; border: 1px solid var(--border-color); margin-bottom: 20px;">
                <h4 style="font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 12px 0;">Activity Overview</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 12.5px;">
                    <div>
                        <span style="color: var(--text-subtle); display: block; font-size: 11px;">Action:</span>
                        <strong style="color: var(--text-main);" id="drawerAction">Subscription Changed</strong>
                    </div>
                    <div>
                        <span style="color: var(--text-subtle); display: block; font-size: 11px;">Status:</span>
                        <span id="drawerStatusText" style="font-weight: 700; color: var(--success);">Success</span>
                    </div>
                    <div style="grid-column: span 2;">
                        <span style="color: var(--text-subtle); display: block; font-size: 11px;">Timestamp:</span>
                        <span style="color: var(--text-main); font-weight: 600;" id="drawerTimestamp">15 Aug 2026, 10:42 AM</span>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: ACTOR DETAILS -->
            <div style="margin-bottom: 20px;">
                <h4 style="font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 10px 0;">Actor Information</h4>
                <div style="background: var(--bg-surface); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #1e293b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px;" id="drawerActorAvatar">
                        SA
                    </div>
                    <div>
                        <strong style="color: var(--text-main); font-size: 13.5px; display: block;" id="drawerActorName">Super Admin</strong>
                        <span style="font-size: 11.5px; color: var(--text-subtle);" id="drawerActorRole">Platform Administrator</span>
                        <div style="font-size: 11px; color: var(--primary); font-family: monospace;" id="drawerActorEmail">admin@platform.io</div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: COMPANY & TENANT DETAILS -->
            <div style="margin-bottom: 20px;">
                <h4 style="font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 10px 0;">Company &amp; Tenant Target</h4>
                <div style="background: var(--bg-subtle); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 12.5px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="color: var(--text-subtle);">Company Name:</span>
                        <strong style="color: var(--text-main);" id="drawerCompany">Original Company</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="color: var(--text-subtle);">Tenant ID:</span>
                        <span style="font-family: monospace; font-weight: 700; color: #475569;" id="drawerTenantCode">TEN-001</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-subtle);">Domain:</span>
                        <span style="font-family: monospace; color: var(--primary);" id="drawerDomain">originalcompany.platform.io</span>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: RESOURCE DETAILS -->
            <div style="margin-bottom: 20px;">
                <h4 style="font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 10px 0;">Resource Information</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12px;">
                    <div style="background: var(--bg-subtle); padding: 10px; border-radius: 6px; border: 1px solid var(--border-color);">
                        <span style="color: var(--text-subtle); display: block; font-size: 10.5px;">Module:</span>
                        <strong style="color: var(--text-main);" id="drawerModule">Subscriptions</strong>
                    </div>
                    <div style="background: var(--bg-subtle); padding: 10px; border-radius: 6px; border: 1px solid var(--border-color);">
                        <span style="color: var(--text-subtle); display: block; font-size: 10.5px;">Resource ID:</span>
                        <span style="font-family: monospace; font-weight: 700; color: var(--text-main);" id="drawerResourceId">SUB-001</span>
                    </div>
                    <div style="grid-column: span 2; background: var(--bg-subtle); padding: 10px; border-radius: 6px; border: 1px solid var(--border-color);">
                        <span style="color: var(--text-subtle); display: block; font-size: 10.5px;">Target Resource:</span>
                        <strong style="color: var(--text-main);" id="drawerResource">Company Subscription</strong>
                    </div>
                </div>
            </div>

            <!-- SECTION 5: CHANGE DETAILS (BEFORE / AFTER DIFF VIEW) -->
            <div style="margin-bottom: 20px;" id="diffSection">
                <h4 style="font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 10px 0;">Change Details (Before vs After Diff)</h4>
                <div class="diff-box" id="diffBox">
                    <div class="diff-removed">- BEFORE: Free</div>
                    <div class="diff-added">+ AFTER: Diamond</div>
                </div>
            </div>

            <!-- SECTION 6: TECHNICAL & TELEMETRY DETAILS -->
            <div style="margin-bottom: 20px;">
                <h4 style="font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 10px 0;">Technical Details</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12px;">
                    <div style="background: var(--bg-subtle); padding: 10px; border-radius: 6px; border: 1px solid var(--border-color);">
                        <span style="color: var(--text-subtle); display: block; font-size: 10.5px;">IP Address:</span>
                        <code style="font-family: monospace; font-weight: 700; color: var(--text-main);" id="drawerIp">192.168.1.10</code>
                    </div>
                    <div style="background: var(--bg-subtle); padding: 10px; border-radius: 6px; border: 1px solid var(--border-color);">
                        <span style="color: var(--text-subtle); display: block; font-size: 10.5px;">Browser:</span>
                        <span style="color: var(--text-main); font-weight: 600;" id="drawerBrowser">Chrome 120.0</span>
                    </div>
                    <div style="background: var(--bg-subtle); padding: 10px; border-radius: 6px; border: 1px solid var(--border-color);">
                        <span style="color: var(--text-subtle); display: block; font-size: 10.5px;">Operating System:</span>
                        <span style="color: var(--text-main); font-weight: 600;" id="drawerOs">Windows 11</span>
                    </div>
                    <div style="background: var(--bg-subtle); padding: 10px; border-radius: 6px; border: 1px solid var(--border-color);">
                        <span style="color: var(--text-subtle); display: block; font-size: 10.5px;">Session ID:</span>
                        <code style="font-family: monospace; font-size: 11px; color: var(--text-muted);" id="drawerSession">sess_9f82a1b4</code>
                    </div>
                </div>
            </div>

            <!-- SECTION 7: TIMELINE WORKFLOW -->
            <div>
                <h4 style="font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 10px 0;">Execution Timeline</h4>
                <div class="workflow-timeline" id="workflowTimeline">
                    <div class="timeline-step completed">
                        <div class="timeline-step-dot"></div>
                        <div class="timeline-step-title">Event Created</div>
                        <div class="timeline-step-sub">Action request dispatched to audit logger</div>
                    </div>
                    <div class="timeline-step completed">
                        <div class="timeline-step-dot"></div>
                        <div class="timeline-step-title">Action Started</div>
                        <div class="timeline-step-sub">Executed by authenticated actor</div>
                    </div>
                    <div class="timeline-step completed" id="timelineStepResult">
                        <div class="timeline-step-dot"></div>
                        <div class="timeline-step-title">Action Completed</div>
                        <div class="timeline-step-sub">Changes applied successfully</div>
                    </div>
                    <div class="timeline-step completed">
                        <div class="timeline-step-dot"></div>
                        <div class="timeline-step-title">Result Recorded</div>
                        <div class="timeline-step-sub">Audit trail state committed to central database</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- 7. EXPORT LOGS MODAL -->
<div class="modal-backdrop" id="exportLogsModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="bx bx-export" style="color: var(--primary);"></i>
                Export Activity Audit Logs
            </h3>
            <button class="btn-action-secondary" id="closeExportModalBtn" style="padding: 4px 8px;">
                <i class="bx bx-x"></i>
            </button>
        </div>
        <div class="modal-body">
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">Export Format:</label>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                    <label style="border: 1px solid var(--primary); background: var(--primary-light); border-radius: 8px; padding: 12px; text-align: center; cursor: pointer;">
                        <input type="radio" name="exportFormat" value="csv" checked style="margin-right: 4px;" />
                        <strong style="color: var(--primary);">CSV File</strong>
                    </label>
                    <label style="border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; text-align: center; cursor: pointer;">
                        <input type="radio" name="exportFormat" value="excel" style="margin-right: 4px;" />
                        <strong>Excel (XLSX)</strong>
                    </label>
                    <label style="border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; text-align: center; cursor: pointer;">
                        <input type="radio" name="exportFormat" value="pdf" style="margin-right: 4px;" />
                        <strong>PDF Report</strong>
                    </label>
                </div>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Date Range:</label>
                <select class="filter-select" id="exportDateRange">
                    <option value="filtered">Current Filtered View</option>
                    <option value="today">Today Only</option>
                    <option value="7days">Last 7 Days</option>
                    <option value="30days" selected>Last 30 Days</option>
                    <option value="all">All Historical Records</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Fields to Include:</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12.5px;">
                    <label><input type="checkbox" checked disabled /> Timestamp</label>
                    <label><input type="checkbox" checked disabled /> Actor &amp; Role</label>
                    <label><input type="checkbox" checked disabled /> Action &amp; Module</label>
                    <label><input type="checkbox" checked disabled /> Company / Tenant</label>
                    <label><input type="checkbox" checked disabled /> Result Status</label>
                    <label><input type="checkbox" checked disabled /> IP Address &amp; Session</label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-action-secondary" id="cancelExportBtn">Cancel</button>
            <a href="{{ Route::has('super-admin.tenant-audit.export') ? route('super-admin.tenant-audit.export') : url('/super-admin/tenant-audit/export') }}" class="btn-action-primary" id="confirmExportBtn">
                <i class="bx bx-download"></i> Export Logs
            </a>
        </div>
    </div>
</div>

<!-- 8. AUDIT SETTINGS MODAL -->
<div class="modal-backdrop" id="auditSettingsModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="bx bx-cog" style="color: var(--primary);"></i>
                Audit Trail &amp; Monitoring Settings
            </h3>
            <button class="btn-action-secondary" id="closeSettingsModalBtn" style="padding: 4px 8px;">
                <i class="bx bx-x"></i>
            </button>
        </div>
        <div class="modal-body">
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 12.5px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Data Retention Period:</label>
                <select class="filter-select">
                    <option value="90">90 Days Retention (Standard Compliance)</option>
                    <option value="180">180 Days Retention</option>
                    <option value="365" selected>1 Year Retention (Recommended)</option>
                    <option value="0">Indefinite (Keep All Logs)</option>
                </select>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 12.5px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Logging Severity Threshold:</label>
                <select class="filter-select">
                    <option value="verbose" selected>Verbose (Log All Admin &amp; Tenant Activity)</option>
                    <option value="standard">Standard (Log Important Actions)</option>
                    <option value="security">Security &amp; Critical Only</option>
                </select>
            </div>

            <div style="background: var(--bg-subtle); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <div>
                        <strong style="font-size: 13px; color: var(--text-main); display: block;">Real-Time Telemetry Sync</strong>
                        <span style="font-size: 11.5px; color: var(--text-subtle);">Stream live activity events from tenant databases</span>
                    </div>
                    <input type="checkbox" checked style="transform: scale(1.2); cursor: pointer;" />
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <strong style="font-size: 13px; color: var(--text-main); display: block;">IP Address Anonymization</strong>
                        <span style="font-size: 11.5px; color: var(--text-subtle);">Mask last octet of IP addresses for privacy</span>
                    </div>
                    <input type="checkbox" style="transform: scale(1.2); cursor: pointer;" />
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-action-secondary" id="cancelSettingsBtn">Close</button>
            <button class="btn-action-primary" id="saveSettingsBtn">
                <i class="bx bx-check"></i> Save Settings
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Live Timer Updates
    let secondsAgo = 120;
    const timerElem = document.getElementById('lastUpdatedTimer');
    setInterval(function() {
        secondsAgo++;
        if (secondsAgo < 60) {
            timerElem.textContent = secondsAgo + ' seconds ago';
        } else {
            const mins = Math.floor(secondsAgo / 60);
            timerElem.textContent = mins + (mins === 1 ? ' minute ago' : ' minutes ago');
        }
    }, 1000);

    // Refresh Button
    document.getElementById('refreshBtn')?.addEventListener('click', function() {
        secondsAgo = 0;
        timerElem.textContent = 'Just now';
        filterRows();
    });

    // 2. Slide-Over Drawer Handlers
    const drawerOverlay = document.getElementById('auditDetailsDrawer');
    const drawerPanel = document.getElementById('drawerPanel');
    const closeDrawerBtn = document.getElementById('closeDrawerBtn');

    document.querySelectorAll('.open-drawer-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const comp = this.getAttribute('data-company');
            const code = this.getAttribute('data-code');
            const domain = this.getAttribute('data-domain');
            const userName = this.getAttribute('data-user-name');
            const userEmail = this.getAttribute('data-user-email');
            const role = this.getAttribute('data-role');
            const time = this.getAttribute('data-time');
            const mod = this.getAttribute('data-module');
            const act = this.getAttribute('data-action');
            const res = this.getAttribute('data-resource');
            const resId = this.getAttribute('data-resource-id');
            const status = this.getAttribute('data-status');
            const ip = this.getAttribute('data-ip');
            const browser = this.getAttribute('data-browser');
            const os = this.getAttribute('data-os');
            const session = this.getAttribute('data-session');
            let diffRaw = this.getAttribute('data-diff');

            document.getElementById('drawerEventId').textContent = id;
            document.getElementById('drawerActionTitle').textContent = act;
            document.getElementById('drawerAction').textContent = act;
            document.getElementById('drawerCompany').textContent = comp;
            document.getElementById('drawerTenantCode').textContent = code;
            document.getElementById('drawerDomain').textContent = domain || (comp.toLowerCase().replace(/[^a-z0-9]/g, '') + '.platform.io');
            document.getElementById('drawerActorName').textContent = userName;
            document.getElementById('drawerActorRole').textContent = role;
            document.getElementById('drawerActorEmail').textContent = userEmail;
            document.getElementById('drawerActorAvatar').textContent = userName.substring(0, 2).toUpperCase();
            document.getElementById('drawerTimestamp').textContent = time;
            document.getElementById('drawerModule').textContent = mod;
            document.getElementById('drawerResource').textContent = res;
            document.getElementById('drawerResourceId').textContent = resId;
            document.getElementById('drawerIp').textContent = ip;
            document.getElementById('drawerBrowser').textContent = browser || 'Chrome 120.0';
            document.getElementById('drawerOs').textContent = os || 'Windows 11';
            document.getElementById('drawerSession').textContent = session || 'sess_default';

            // Result Badge
            const resultBadgeElem = document.getElementById('drawerResultBadge');
            const statusTextElem = document.getElementById('drawerStatusText');
            const timelineStepResult = document.getElementById('timelineStepResult');

            if (status === 'success') {
                resultBadgeElem.className = 'badge-result badge-result-success';
                resultBadgeElem.innerHTML = '<i class="bx bx-check-circle"></i> Success';
                statusTextElem.textContent = 'Success';
                statusTextElem.style.color = 'var(--success)';
                timelineStepResult.className = 'timeline-step completed';
                timelineStepResult.querySelector('.timeline-step-title').textContent = 'Action Completed';
                timelineStepResult.querySelector('.timeline-step-sub').textContent = 'Changes applied successfully';
            } else if (status === 'failed') {
                resultBadgeElem.className = 'badge-result badge-result-failed';
                resultBadgeElem.innerHTML = '<i class="bx bx-x-circle"></i> Failed';
                statusTextElem.textContent = 'Failed';
                statusTextElem.style.color = 'var(--danger)';
                timelineStepResult.className = 'timeline-step failed';
                timelineStepResult.querySelector('.timeline-step-title').textContent = 'Action Failed';
                timelineStepResult.querySelector('.timeline-step-sub').textContent = 'Execution error or validation rejection recorded';
            } else {
                resultBadgeElem.className = 'badge-result badge-result-warning';
                resultBadgeElem.innerHTML = '<i class="bx bx-error"></i> Warning';
                statusTextElem.textContent = 'Warning';
                statusTextElem.style.color = 'var(--warning)';
                timelineStepResult.className = 'timeline-step completed';
                timelineStepResult.querySelector('.timeline-step-title').textContent = 'Completed with Warnings';
                timelineStepResult.querySelector('.timeline-step-sub').textContent = 'Non-critical exceptions noted';
            }

            // Diff View
            const diffBox = document.getElementById('diffBox');
            diffBox.innerHTML = '';
            try {
                const diffs = JSON.parse(diffRaw);
                if (diffs && diffs.length > 0) {
                    diffs.forEach(d => {
                        const rem = document.createElement('div');
                        rem.className = 'diff-removed';
                        rem.textContent = `- BEFORE (${d.field || 'Field'}): ${d.before || 'None'}`;
                        const add = document.createElement('div');
                        add.className = 'diff-added';
                        add.textContent = `+ AFTER (${d.field || 'Field'}): ${d.after || 'Updated'}`;
                        diffBox.appendChild(rem);
                        diffBox.appendChild(add);
                    });
                } else {
                    diffBox.innerHTML = '<div class="diff-removed">- BEFORE: Plan (Free)</div><div class="diff-added">+ AFTER: Plan (Platinum)</div>';
                }
            } catch(ex) {
                diffBox.innerHTML = '<div class="diff-removed">- BEFORE: Previous State</div><div class="diff-added">+ AFTER: Updated State</div>';
            }

            drawerOverlay.classList.add('open');
        });
    });

    closeDrawerBtn?.addEventListener('click', function() {
        drawerOverlay.classList.remove('open');
    });

    drawerOverlay?.addEventListener('click', function(e) {
        if (e.target === drawerOverlay) {
            drawerOverlay.classList.remove('open');
        }
    });

    // 3. Search & Filtering System
    const searchInput = document.getElementById('auditSearchInput');
    const actorFilter = document.getElementById('actorFilter');
    const companyFilter = document.getElementById('companyFilter');
    const actionFilter = document.getElementById('actionFilter');
    const moduleFilter = document.getElementById('moduleFilter');
    const statusFilter = document.getElementById('statusFilter');
    const dateFilter = document.getElementById('dateFilter');
    const resetBtn = document.getElementById('resetFiltersBtn');
    const rows = document.querySelectorAll('.activity-row');
    const emptyState = document.getElementById('emptyState');
    const activeChipsBar = document.getElementById('activeChipsBar');
    const chipsContainer = document.getElementById('chipsContainer');

    function filterRows() {
        const q = searchInput.value.toLowerCase().trim();
        const actorVal = actorFilter.value;
        const compVal = companyFilter.value;
        const actVal = actionFilter.value;
        const modVal = moduleFilter.value;
        const statVal = statusFilter.value;
        const dateVal = dateFilter.value;

        let visibleCount = 0;
        let activeFilterCount = 0;
        chipsContainer.innerHTML = '';

        if (actorVal) { addChip('Actor: ' + actorVal, () => { actorFilter.value = ''; filterRows(); }); activeFilterCount++; }
        if (compVal) { const text = companyFilter.options[companyFilter.selectedIndex].text; addChip('Company: ' + text, () => { companyFilter.value = ''; filterRows(); }); activeFilterCount++; }
        if (actVal) { addChip('Action: ' + actVal, () => { actionFilter.value = ''; filterRows(); }); activeFilterCount++; }
        if (modVal) { addChip('Module: ' + modVal, () => { moduleFilter.value = ''; filterRows(); }); activeFilterCount++; }
        if (statVal) { addChip('Result: ' + statVal, () => { statusFilter.value = ''; filterRows(); }); activeFilterCount++; }
        if (dateVal) { addChip('Date: ' + dateVal, () => { dateFilter.value = ''; filterRows(); }); activeFilterCount++; }
        if (q) { addChip('Search: "' + q + '"', () => { searchInput.value = ''; filterRows(); }); activeFilterCount++; }

        activeChipsBar.style.display = activeFilterCount > 0 ? 'flex' : 'none';

        rows.forEach(r => {
            const rActor = r.getAttribute('data-actor');
            const rComp = r.getAttribute('data-company-id');
            const rAct = r.getAttribute('data-action-type');
            const rMod = r.getAttribute('data-module');
            const rStat = r.getAttribute('data-status');
            const rSearch = r.getAttribute('data-search');
            const rSec = r.getAttribute('data-is-security');

            let match = true;
            if (q && !rSearch.includes(q)) match = false;
            if (actorVal && !rActor.includes(actorVal)) match = false;
            if (compVal && rComp !== compVal) match = false;
            if (actVal && !rAct.includes(actVal) && !rSearch.includes(actVal.toLowerCase())) match = false;
            if (modVal && rMod !== modVal) match = false;
            if (statVal && rStat !== statVal) match = false;

            if (match) {
                r.style.display = '';
                visibleCount++;
            } else {
                r.style.display = 'none';
            }
        });

        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
        document.getElementById('showingStart').textContent = visibleCount > 0 ? 1 : 0;
        document.getElementById('showingEnd').textContent = visibleCount;
        document.getElementById('totalEventsCount').textContent = visibleCount;
    }

    function addChip(label, onRemove) {
        const chip = document.createElement('span');
        chip.className = 'chip';
        chip.innerHTML = `${label} <i class="bx bx-x chip-remove"></i>`;
        chip.querySelector('.chip-remove').addEventListener('click', onRemove);
        chipsContainer.appendChild(chip);
    }

    window.resetAllFilters = function() {
        searchInput.value = '';
        actorFilter.value = '';
        companyFilter.value = '';
        actionFilter.value = '';
        moduleFilter.value = '';
        statusFilter.value = '';
        dateFilter.value = '';
        document.querySelectorAll('.kpi-card').forEach(c => c.classList.remove('active-kpi-filter'));
        document.getElementById('kpiTotalCard').classList.add('active-kpi-filter');
        filterRows();
    };

    searchInput.addEventListener('input', filterRows);
    actorFilter.addEventListener('change', filterRows);
    companyFilter.addEventListener('change', filterRows);
    actionFilter.addEventListener('change', filterRows);
    moduleFilter.addEventListener('change', filterRows);
    statusFilter.addEventListener('change', filterRows);
    dateFilter.addEventListener('change', filterRows);
    resetBtn?.addEventListener('click', resetAllFilters);

    // 4. Click-to-filter on KPI Cards
    window.applyKpiFilter = function(type) {
        document.querySelectorAll('.kpi-card').forEach(c => c.classList.remove('active-kpi-filter'));

        if (type === 'all') {
            document.getElementById('kpiTotalCard').classList.add('active-kpi-filter');
            resetAllFilters();
        } else if (type === 'today') {
            document.getElementById('kpiTodayCard').classList.add('active-kpi-filter');
            dateFilter.value = 'today';
            filterRows();
        } else if (type === 'admin') {
            document.getElementById('kpiAdminCard').classList.add('active-kpi-filter');
            actorFilter.value = 'Super Admin';
            filterRows();
        } else if (type === 'security') {
            document.getElementById('kpiSecurityCard').classList.add('active-kpi-filter');
            actionFilter.value = 'Security';
            filterRows();
        } else if (type === 'failed') {
            document.getElementById('kpiFailedCard').classList.add('active-kpi-filter');
            statusFilter.value = 'failed';
            filterRows();
        } else if (type === 'sessions') {
            document.getElementById('kpiActiveCard').classList.add('active-kpi-filter');
            statusFilter.value = 'success';
            filterRows();
        }
    };

    // Toggle Filter Panel
    document.getElementById('toggleFilterPanelBtn')?.addEventListener('click', function() {
        const fp = document.getElementById('filterPanel');
        fp.style.display = fp.style.display === 'none' ? 'block' : 'none';
    });

    // 5. Modals (Export & Settings)
    const exportModal = document.getElementById('exportLogsModal');
    const settingsModal = document.getElementById('auditSettingsModal');

    document.getElementById('openExportModalBtn')?.addEventListener('click', function() { exportModal.classList.add('show'); });
    document.getElementById('closeExportModalBtn')?.addEventListener('click', function() { exportModal.classList.remove('show'); });
    document.getElementById('cancelExportBtn')?.addEventListener('click', function() { exportModal.classList.remove('show'); });

    document.getElementById('openSettingsBtn')?.addEventListener('click', function() { settingsModal.classList.add('show'); });
    document.getElementById('closeSettingsModalBtn')?.addEventListener('click', function() { settingsModal.classList.remove('show'); });
    document.getElementById('cancelSettingsBtn')?.addEventListener('click', function() { settingsModal.classList.remove('show'); });
    document.getElementById('saveSettingsBtn')?.addEventListener('click', function() { settingsModal.classList.remove('show'); });

    // Table Column Sorting
    window.sortTable = function(colIdx) {
        const table = document.getElementById('activityTable');
        const tbody = table.querySelector('tbody');
        const rowsArr = Array.from(tbody.querySelectorAll('tr.activity-row'));
        
        let asc = table.getAttribute('data-sort-dir') !== 'asc';
        table.setAttribute('data-sort-dir', asc ? 'asc' : 'desc');

        rowsArr.sort((a, b) => {
            const valA = a.children[colIdx]?.innerText.trim() || '';
            const valB = b.children[colIdx]?.innerText.trim() || '';
            return asc ? valA.localeCompare(valB) : valB.localeCompare(valA);
        });

        rowsArr.forEach(r => tbody.appendChild(r));
    };
});
</script>
@endpush
