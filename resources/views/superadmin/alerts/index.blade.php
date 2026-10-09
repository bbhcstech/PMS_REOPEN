@extends('layouts.superadmin')

@section('title', 'Platform Alert & Notification Center')

@push('styles')
<style>
    /* CSS Tokens & Root Architecture */
    :root {
        --bg-surface: #ffffff;
        --bg-subtle: #f8fafc;
        --border-color: #e2e8f0;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --text-subtle: #94a3b8;
        --primary: #2563eb;
        --primary-light: #eff6ff;
        --success: #10b981;
        --success-light: #EEF2FF;
        --warning: #f59e0b;
        --warning-light: #fffbeb;
        --danger: #ef4444;
        --danger-light: #fef2f2;
        --info: #06b6d4;
        --info-light: #ecfeff;
        --purple: #8b5cf6;
        --purple-light: #f5f3ff;
        --radius-lg: 12px;
        --radius-md: 8px;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
        --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.07);
        --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
    }

    /* Container & Layout */
    .alerts-container {
        padding: 24px;
        max-width: 1600px;
        margin: 0 auto;
        font-family: inherit;
        color: var(--text-main);
    }

    /* KPI Summary Cards */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .kpi-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 16px 20px;
        box-shadow: var(--shadow-sm);
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary);
    }

    .kpi-icon-box {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .kpi-val {
        font-size: 24px;
        font-weight: 800;
        line-height: 1.2;
    }

    .kpi-label {
        font-size: 12.5px;
        color: var(--text-muted);
        font-weight: 600;
    }

    /* Filter Toolbar */
    .filter-toolbar {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 16px 20px;
        box-shadow: var(--shadow-sm);
        margin-bottom: 24px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-select, .search-input {
        padding: 7px 12px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 12.5px;
        color: var(--text-main);
        background: var(--bg-surface);
        outline: none;
        transition: border 0.15s;
    }

    .filter-select:focus, .search-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(37,99,235,0.1);
    }

    /* Severity Badges & Pills */
    .severity-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .sev-critical { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .sev-warning { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
    .sev-info { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
    .sev-success { background: #EEF2FF; color: #2F6BFF; border: 1px solid #C7D2FE; }

    .category-pill {
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 6px;
        text-transform: uppercase;
    }

    /* Alert Feed List */
    .alert-feed {
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-bottom: 28px;
    }

    .alert-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 18px 22px;
        box-shadow: var(--shadow-sm);
        transition: all 0.2s ease;
        display: flex;
        align-items: flex-start;
        gap: 16px;
        position: relative;
    }

    .alert-card:hover {
        box-shadow: var(--shadow-md);
        border-color: #cbd5e1;
    }

    .alert-card.is-unread {
        background: #f8fafc;
        border-left: 4px solid var(--primary);
    }

    .alert-card.is-critical {
        border-left: 4px solid var(--danger);
    }

    .alert-card.is-warning {
        border-left: 4px solid var(--warning);
    }

    .alert-card.is-resolved {
        opacity: 0.75;
        background: #fafafa;
    }

    .unread-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--primary);
        position: absolute;
        top: 20px;
        right: 20px;
    }

    /* Subscription Intel Highlight Box */
    .sub-intel-box {
        background: #f8fafc;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 12px 16px;
        margin-top: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    /* Action Buttons */
    .btn-action-primary {
        background: var(--primary);
        color: #ffffff;
        border: none;
        padding: 6px 14px;
        border-radius: var(--radius-md);
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-action-primary:hover { background: #1d4ed8; }

    .btn-action-secondary {
        background: #ffffff;
        color: var(--text-main);
        border: 1px solid var(--border-color);
        padding: 6px 14px;
        border-radius: var(--radius-md);
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-action-secondary:hover { background: #f1f5f9; border-color: #cbd5e1; }

    /* Slide-Over Drawer Overlay */
    .drawer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(3px);
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.25s ease;
        display: flex;
        justify-content: flex-end;
    }

    .drawer-overlay.open {
        opacity: 1;
        visibility: visible;
    }

    .drawer-panel {
        width: 520px;
        max-width: 90vw;
        height: 100vh;
        background: var(--bg-surface);
        box-shadow: var(--shadow-lg);
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
    }

    .drawer-panel.open {
        transform: translateX(0);
    }

    .drawer-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .drawer-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
    }

    /* Modal Backdrop */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(3px);
        z-index: 1050;
        display: none;
        align-items: center;
        justify-content: center;
    }

    /* ============================================================
       DARK THEME ARCHITECTURE FOR ALERTS CENTER
       ============================================================ */
    html[data-pms-theme="dark"],
    html[data-theme="dark"],
    html[data-bs-theme="dark"] {
        --bg-surface: #0F1530;
        --bg-subtle: #141B3D;
        --border-color: rgba(238, 241, 251, 0.09);
        --text-main: #EEF1FB;
        --text-muted: #CBD5E1;
        --text-subtle: #9AA3C7;
        --primary: #2F6BFF;
        --primary-light: rgba(47, 107, 255, 0.15);
    }

    html[data-pms-theme="dark"] .kpi-card,
    html[data-theme="dark"] .kpi-card,
    html[data-bs-theme="dark"] .kpi-card {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25) !important;
    }

    html[data-pms-theme="dark"] .filter-toolbar,
    html[data-theme="dark"] .filter-toolbar,
    html[data-bs-theme="dark"] .filter-toolbar {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25) !important;
    }
    html[data-pms-theme="dark"] .filter-select,
    html[data-theme="dark"] .filter-select,
    html[data-bs-theme="dark"] .filter-select {
        background: #141B3D !important;
        color: #EEF1FB !important;
        border-color: rgba(238, 241, 251, 0.12) !important;
    }
    html[data-pms-theme="dark"] .filter-select option,
    html[data-theme="dark"] .filter-select option {
        background: #0F1530 !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .search-box input,
    html[data-theme="dark"] .search-box input,
    html[data-bs-theme="dark"] .search-box input {
        background: #141B3D !important;
        color: #EEF1FB !important;
        border-color: rgba(238, 241, 251, 0.12) !important;
    }

    /* Alert Cards (Screenshot 5 & 6) */
    html[data-pms-theme="dark"] .alert-card,
    html[data-theme="dark"] .alert-card,
    html[data-bs-theme="dark"] .alert-card {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25) !important;
    }
    html[data-pms-theme="dark"] .alert-card:hover,
    html[data-theme="dark"] .alert-card:hover,
    html[data-bs-theme="dark"] .alert-card:hover {
        border-color: rgba(47, 107, 255, 0.4) !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4) !important;
    }
    html[data-pms-theme="dark"] .alert-card.is-unread,
    html[data-theme="dark"] .alert-card.is-unread,
    html[data-bs-theme="dark"] .alert-card.is-unread {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.12) !important;
        border-left-color: var(--primary) !important;
    }
    html[data-pms-theme="dark"] .alert-card.is-resolved,
    html[data-theme="dark"] .alert-card.is-resolved,
    html[data-bs-theme="dark"] .alert-card.is-resolved {
        background: #0D1333 !important;
        border-color: rgba(238, 241, 251, 0.08) !important;
        opacity: 0.88 !important;
    }
    html[data-pms-theme="dark"] .alert-card.is-resolved h3,
    html[data-theme="dark"] .alert-card.is-resolved h3,
    html[data-bs-theme="dark"] .alert-card.is-resolved h3 {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .alert-card.is-resolved p,
    html[data-theme="dark"] .alert-card.is-resolved p,
    html[data-bs-theme="dark"] .alert-card.is-resolved p {
        color: #9AA3C7 !important;
    }
    html[data-pms-theme="dark"] .sub-intel-box,
    html[data-theme="dark"] .sub-intel-box,
    html[data-bs-theme="dark"] .sub-intel-box {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
    }

    /* Alert Icon Badges (Screenshot 5 - Bottom Left) */
    .alert-icon-critical { background: #fef2f2; color: #dc2626; }
    .alert-icon-warning { background: #fffbeb; color: #d97706; }
    .alert-icon-success { background: #EEF2FF; color: #2F6BFF; }
    .alert-icon-info { background: #eff6ff; color: #2563eb; }

    html[data-pms-theme="dark"] .alert-icon-critical,
    html[data-theme="dark"] .alert-icon-critical,
    html[data-bs-theme="dark"] .alert-icon-critical {
        background: rgba(239, 68, 68, 0.15) !important;
        color: #f87171 !important;
        border: 1px solid rgba(248, 113, 113, 0.3) !important;
    }
    html[data-pms-theme="dark"] .alert-icon-warning,
    html[data-theme="dark"] .alert-icon-warning,
    html[data-bs-theme="dark"] .alert-icon-warning {
        background: rgba(245, 158, 11, 0.15) !important;
        color: #fbbf24 !important;
        border: 1px solid rgba(251, 191, 36, 0.3) !important;
    }
    html[data-pms-theme="dark"] .alert-icon-success,
    html[data-theme="dark"] .alert-icon-success,
    html[data-bs-theme="dark"] .alert-icon-success {
        background: rgba(47, 107, 255, 0.15) !important;
        color: #60A5FA !important;
        border: 1px solid rgba(79, 131, 255, 0.3) !important;
    }
    html[data-pms-theme="dark"] .alert-icon-info,
    html[data-theme="dark"] .alert-icon-info,
    html[data-bs-theme="dark"] .alert-icon-info {
        background: rgba(37, 99, 235, 0.15) !important;
        color: #60a5fa !important;
        border: 1px solid rgba(96, 165, 250, 0.3) !important;
    }

    html[data-pms-theme="dark"] .category-pill,
    html[data-theme="dark"] .category-pill,
    html[data-bs-theme="dark"] .category-pill {
        background: #141B3D !important;
        color: #9AA3C7 !important;
        border: 1px solid rgba(238, 241, 251, 0.09) !important;
    }

    /* Action Buttons (Screenshot 5 - Bottom Right) */
    html[data-pms-theme="dark"] .btn-action-secondary,
    html[data-theme="dark"] .btn-action-secondary,
    html[data-bs-theme="dark"] .btn-action-secondary {
        background: #141B3D !important;
        color: #EEF1FB !important;
        border: 1px solid rgba(238, 241, 251, 0.12) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
    }
    html[data-pms-theme="dark"] .btn-action-secondary:hover,
    html[data-theme="dark"] .btn-action-secondary:hover,
    html[data-bs-theme="dark"] .btn-action-secondary:hover {
        background: #1A2247 !important;
        border-color: rgba(47, 107, 255, 0.4) !important;
        color: #38bdf8 !important;
    }

    /* Drawer */
    html[data-pms-theme="dark"] .drawer-panel,
    html[data-theme="dark"] .drawer-panel,
    html[data-bs-theme="dark"] .drawer-panel {
        background: #0F1530 !important;
        color: #EEF1FB !important;
        border-left: 1px solid rgba(238, 241, 251, 0.09);
    }
    html[data-pms-theme="dark"] .drawer-header,
    html[data-theme="dark"] .drawer-header,
    html[data-bs-theme="dark"] .drawer-header {
        background: #141B3D !important;
        border-bottom-color: rgba(238, 241, 251, 0.09) !important;
    }
</style>
@endpush

@section('content')
<div class="alerts-container">

    <!-- PAGE HEADER -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <!-- Breadcrumb -->
            <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text-muted); font-weight: 600; margin-bottom: 6px;">
                <span>Platform</span>
                <i class="fas fa-chevron-right" style="font-size: 9px; color: var(--text-subtle);"></i>
                <span>Operations</span>
                <i class="fas fa-chevron-right" style="font-size: 9px; color: var(--text-subtle);"></i>
                <span style="color: var(--primary);">Alert Center</span>
            </div>

            <!-- Page Title -->
            <div style="display: flex; align-items: center; gap: 12px;">
                <h1 style="font-size: 24px; font-weight: 800; color: var(--text-main); margin: 0; letter-spacing: -0.5px;">
                    Alert &amp; Notification Center
                </h1>
                <span style="background: #fef2f2; color: #dc2626; font-size: 11.5px; font-weight: 800; padding: 3px 10px; border-radius: 20px; border: 1px solid #fecaca; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-circle" style="font-size: 7px; animation: pulse 1.5s infinite;"></i>
                    <span id="headerUnreadBadgeCount">{{ $kpis['unread'] }} Active Alerts</span>
                </span>
            </div>

            <!-- Subtitle & Last Updated -->
            <div style="display: flex; align-items: center; gap: 14px; margin-top: 4px; font-size: 13px; color: var(--text-muted);">
                <span>Monitor critical events, subscription risks, infrastructure issues, and tenant activity across the platform.</span>
                <span style="color: var(--text-subtle);">•</span>
                <span style="font-size: 12px; color: var(--text-subtle); display: flex; align-items: center; gap: 5px;">
                    <i class="far fa-clock"></i> Last updated: <strong id="lastUpdatedTimestamp">{{ now()->format('H:i:s') }}</strong>
                </span>
            </div>
        </div>

        <!-- Right Header Action Controls -->
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <button class="btn-action-secondary" id="markAllReadHeaderBtn">
                <i class="fas fa-check-double" style="color: var(--primary);"></i> Mark All as Read
            </button>
            <button class="btn-action-secondary" id="refreshAlertsBtn">
                <i class="fas fa-sync-alt"></i> Refresh Alerts
            </button>
            <button class="btn-action-primary" id="openSettingsModalBtn">
                <i class="fas fa-sliders-h"></i> Preferences
            </button>
        </div>
    </div>

    <!-- 1. KPI SUMMARY CARDS -->
    <div class="kpi-grid">
        <!-- Critical -->
        <div class="kpi-card" data-filter-type="severity" data-filter-val="critical">
            <div class="kpi-icon-box" style="background: #fef2f2; color: #dc2626;">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div>
                <div class="kpi-val" style="color: #dc2626;">{{ $kpis['critical'] }}</div>
                <div class="kpi-label">Critical Alerts</div>
            </div>
        </div>

        <!-- Warnings -->
        <div class="kpi-card" data-filter-type="severity" data-filter-val="warning">
            <div class="kpi-icon-box" style="background: #fffbeb; color: #d97706;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <div class="kpi-val" style="color: #d97706;">{{ $kpis['warnings'] }}</div>
                <div class="kpi-label">System Warnings</div>
            </div>
        </div>

        <!-- Unread -->
        <div class="kpi-card" data-filter-type="status" data-filter-val="unread">
            <div class="kpi-icon-box" style="background: #eff6ff; color: #2563eb;">
                <i class="fas fa-bell"></i>
            </div>
            <div>
                <div class="kpi-val" style="color: #2563eb;">{{ $kpis['unread'] }}</div>
                <div class="kpi-label">Unread Notifications</div>
            </div>
        </div>

        <!-- Subscription Expiring -->
        <div class="kpi-card" data-filter-type="category" data-filter-val="subscription">
            <div class="kpi-icon-box" style="background: #f5f3ff; color: #8b5cf6;">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <div class="kpi-val" style="color: #8b5cf6;">{{ $kpis['subscription_expiring'] }}</div>
                <div class="kpi-label">Subscriptions Expiring</div>
            </div>
        </div>

        <!-- Infrastructure Issues -->
        <div class="kpi-card" data-filter-type="category" data-filter-val="database">
            <div class="kpi-icon-box" style="background: #f1f5f9; color: #334155;">
                <i class="fas fa-server"></i>
            </div>
            <div>
                <div class="kpi-val" style="color: #334155;">{{ $kpis['infrastructure_issues'] }}</div>
                <div class="kpi-label">Infrastructure Issues</div>
            </div>
        </div>

        <!-- Resolved Today -->
        <div class="kpi-card" data-filter-type="status" data-filter-val="resolved">
            <div class="kpi-icon-box" style="background: #EEF2FF; color: #2F6BFF;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="kpi-val" style="color: #2F6BFF;">{{ $kpis['resolved_today'] }}</div>
                <div class="kpi-label">Resolved Today</div>
            </div>
        </div>
    </div>

    <!-- 2. FILTER TOOLBAR BAR -->
    <div class="filter-toolbar">
        <div class="filter-group">
            <!-- Search -->
            <div style="position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 11px; color: var(--text-subtle);"></i>
                <input type="text" class="search-input" id="alertSearchInput" placeholder="Search alert title, company, code..." style="padding-left: 28px; width: 220px;" />
            </div>

            <!-- Severity Filter -->
            <select class="filter-select" id="severityFilterSelect">
                <option value="all">All Severities</option>
                <option value="critical">🔴 Critical</option>
                <option value="warning">⚠️ Warning</option>
                <option value="info">ℹ️ Info</option>
                <option value="success">✓ Success</option>
            </select>

            <!-- Category Filter -->
            <select class="filter-select" id="categoryFilterSelect">
                <option value="all">All Categories</option>
                <option value="subscription">Subscription</option>
                <option value="company">Company</option>
                <option value="database">Database</option>
                <option value="backup">Backup</option>
                <option value="migration">Migration</option>
                <option value="usage">Usage</option>
                <option value="security">Security</option>
                <option value="system">System</option>
            </select>

            <!-- Status Filter -->
            <select class="filter-select" id="statusFilterSelect">
                <option value="all">All Statuses</option>
                <option value="unread">Unread</option>
                <option value="read">Read</option>
                <option value="resolved">Resolved</option>
                <option value="action_required">Action Required</option>
            </select>

            <!-- Company Filter -->
            <select class="filter-select" id="companyFilterSelect">
                <option value="all">All Companies</option>
                @foreach($companies as $comp)
                    <option value="{{ $comp->id }}">{{ $comp->name }} ({{ $comp->company_code ?? 'TEN' }})</option>
                @endforeach
            </select>

            <!-- Time Range Filter -->
            <select class="filter-select" id="timeFilterSelect">
                <option value="all">All Time</option>
                <option value="today">Today</option>
                <option value="7days">Last 7 Days</option>
                <option value="30days">Last 30 Days</option>
            </select>
        </div>

        <button class="btn-action-secondary" id="clearFiltersBtn" style="padding: 6px 12px; font-size: 12px;">
            <i class="fas fa-times-circle"></i> Clear Filters
        </button>
    </div>

    <!-- 3. MAIN ALERT FEED LIST -->
    <div class="alert-feed" id="alertFeedList">
        @foreach($allAlerts as $alert)
            <div class="alert-card {{ $alert['status'] === 'unread' ? 'is-unread' : '' }} {{ $alert['status'] === 'resolved' ? 'is-resolved' : '' }} {{ $alert['severity'] === 'critical' ? 'is-critical' : ($alert['severity'] === 'warning' ? 'is-warning' : '') }}"
                 data-id="{{ $alert['id'] }}"
                 data-live-key="alert-{{ $alert['alert_key'] }}"
                 data-alert-key="{{ $alert['alert_key'] }}"
                 data-timestamp="{{ $alert['timestamp'] ?? '' }}"
                 data-severity="{{ $alert['severity'] }}"
                 data-category="{{ $alert['category'] }}"
                 data-status="{{ $alert['status'] }}"
                 data-company-id="{{ $alert['company_id'] ?? '' }}"
                 data-action-required="{{ !empty($alert['action_required']) ? 'true' : 'false' }}"
                 data-search="{{ strtolower($alert['title'] . ' ' . $alert['description'] . ' ' . $alert['company_name'] . ' ' . ($alert['tenant_code'] ?? '')) }}">

                @if($alert['status'] === 'unread')
                    <span class="unread-indicator" title="Unread Alert"></span>
                @endif

                <!-- Severity Icon Badge -->
                <div class="alert-icon-box alert-icon-{{ $alert['severity'] }}" style="width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                    @if($alert['severity'] === 'critical')
                        <i class="fas fa-exclamation-circle"></i>
                    @elseif($alert['severity'] === 'warning')
                        <i class="fas fa-exclamation-triangle"></i>
                    @elseif($alert['severity'] === 'success')
                        <i class="fas fa-check-circle"></i>
                    @else
                        <i class="fas fa-info-circle"></i>
                    @endif
                </div>

                <!-- Alert Body Content -->
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; margin-bottom: 6px;">
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span class="category-pill">{{ strtoupper($alert['category']) }}</span>
                            <span class="severity-badge sev-{{ $alert['severity'] }}">
                                ● {{ strtoupper($alert['severity']) }}
                            </span>
                            @if(!empty($alert['action_required']))
                                <span style="background: #fef2f2; color: #dc2626; font-size: 10.5px; font-weight: 800; padding: 2px 7px; border-radius: 4px; border: 1px solid #fecaca;">
                                    <i class="fas fa-hand-paper"></i> Action Required
                                </span>
                            @endif
                        </div>
                        <span style="font-size: 12px; color: var(--text-subtle); font-weight: 500;">
                            <i class="far fa-clock"></i> {{ $alert['created_at'] }}
                        </span>
                    </div>

                    <!-- Title -->
                    <h3 style="font-size: 15px; font-weight: 800; color: var(--text-main); margin: 0 0 6px 0; display: flex; align-items: center; gap: 8px;">
                        {{ $alert['title'] }}
                    </h3>

                    <!-- Description -->
                    <p style="font-size: 13px; color: var(--text-muted); margin: 0 0 10px 0; line-height: 1.5;">
                        {{ $alert['description'] }}
                    </p>

                    <!-- Affected Company Bar -->
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 12.5px; color: var(--text-muted); font-weight: 600;">
                        <div style="width: 24px; height: 24px; border-radius: 6px; background: var(--bg-subtle); color: var(--text-main); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 10.5px; overflow: hidden; border: 1px solid var(--border-color); flex-shrink: 0;">
                            @if(!empty($alert['logo_url']))
                                <img src="{{ $alert['logo_url'] }}" alt="{{ $alert['company_name'] }}" style="width: 100%; height: 100%; object-fit: cover;" />
                            @else
                                {{ strtoupper(substr($alert['company_name'], 0, 2)) }}
                            @endif
                        </div>
                        <span>Company: <strong style="color: var(--text-main);">{{ $alert['company_name'] }}</strong></span>
                        <span style="font-family: monospace; font-size: 11px; background: var(--bg-subtle); color: var(--text-muted); padding: 1px 6px; border-radius: 4px;">
                            {{ $alert['tenant_code'] ?? 'CENTRAL' }}
                        </span>
                    </div>

                    <!-- Subscription Expiration Intelligence Highlight Panel -->
                    @if($alert['category'] === 'subscription')
                        <div class="sub-intel-box">
                            <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                                <div>
                                    <span style="font-size: 11px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Current Plan</span>
                                    <div style="font-size: 13px; font-weight: 800; color: var(--primary);">{{ $alert['plan_name'] ?? 'DIAMOND PLAN' }}</div>
                                </div>
                                <div style="height: 24px; width: 1px; background: var(--border-color);"></div>
                                <div>
                                    <span style="font-size: 11px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Expiry Date</span>
                                    <div style="font-size: 13px; font-weight: 800; color: var(--text-main);">{{ $alert['expiry_date'] ?? 'N/A' }}</div>
                                </div>
                                <div style="height: 24px; width: 1px; background: var(--border-color);"></div>
                                <div>
                                    <span style="font-size: 11px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Days Remaining</span>
                                    <div style="font-size: 13px; font-weight: 800; color: {{ ($alert['days_remaining'] ?? 30) <= 7 ? '#dc2626' : '#d97706' }};">
                                        {{ $alert['days_remaining'] ?? 0 }} Days
                                    </div>
                                </div>
                            </div>

                            <!-- Subscription Intelligence Quick Actions -->
                            <div style="display: flex; gap: 8px;">
                                <a href="{{ Route::has('super-admin.companies.show') ? route('super-admin.companies.show', $alert['company_id']) : (Route::has('superadmin.companies.show') ? route('superadmin.companies.show', $alert['company_id']) : url('/super-admin/companies/'.$alert['company_id'])) }}" class="btn-action-secondary" style="padding: 4px 10px; font-size: 11.5px;">
                                    <i class="fas fa-building"></i> View Company
                                </a>
                                <a href="{{ Route::has('super-admin.subscriptions.index') ? route('super-admin.subscriptions.index') : (Route::has('superadmin.subscriptions.index') ? route('superadmin.subscriptions.index') : url('/super-admin/subscriptions')) }}" class="btn-action-primary" style="padding: 4px 10px; font-size: 11.5px;">
                                    <i class="fas fa-credit-card"></i> Manage Subscription
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Card Actions -->
                <div style="display: flex; flex-direction: column; gap: 6px; align-items: flex-end; flex-shrink: 0;">
                    <button type="button" class="btn-action-secondary inspect-alert-btn"
                            data-id="{{ $alert['id'] }}"
                            data-alert-key="{{ $alert['alert_key'] }}"
                            data-status="{{ $alert['status'] }}"
                            data-title="{{ $alert['title'] }}"
                            data-company-id="{{ $alert['company_id'] ?? '' }}"
                            data-company-name="{{ $alert['company_name'] }}"
                            data-tenant-code="{{ $alert['tenant_code'] ?? 'CENTRAL' }}"
                            data-domain="{{ $alert['domain'] ?? ($alert['company_name'] . '.pms.com') }}"
                            data-plan="{{ $alert['plan_name'] ?? 'DIAMOND PLAN' }}"
                            data-expiry="{{ $alert['expiry_date'] ?? 'N/A' }}"
                            data-days="{{ $alert['days_remaining'] ?? 0 }}"
                            data-db="{{ $alert['db_name'] ?? 'pms_central' }}"
                            data-severity="{{ $alert['severity'] }}"
                            data-category="{{ $alert['category'] }}"
                            style="padding: 5px 10px; font-size: 12px;">
                        <i class="fas fa-eye" style="color: var(--primary);"></i> Inspect
                    </button>
                    @if($alert['status'] === 'unread')
                        <button type="button" class="btn-action-secondary mark-read-btn" data-id="{{ $alert['id'] }}" data-alert-key="{{ $alert['alert_key'] }}" style="padding: 4px 8px; font-size: 11.5px;">
                            <i class="fas fa-check"></i> Read
                        </button>
                    @endif
                    @if($alert['status'] !== 'resolved')
                        <button type="button" class="btn-action-secondary resolve-alert-btn" data-id="{{ $alert['id'] }}" data-alert-key="{{ $alert['alert_key'] }}" style="padding: 4px 8px; font-size: 11.5px; color: var(--success);">
                            <i class="fas fa-check-circle"></i> Resolve
                        </button>
                    @else
                        <span class="resolved-alert-label" style="font-size: 11px; color: var(--success); font-weight: 700;"><i class="fas fa-check"></i> Resolved</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- EMPTY STATES -->
    <!-- Filtered Empty State -->
    <div id="filteredEmptyState" style="display: none; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 48px; text-align: center; box-shadow: var(--shadow-sm); margin-bottom: 28px;">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: #f1f5f9; color: var(--text-subtle); display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 16px;">
            <i class="fas fa-search"></i>
        </div>
        <h3 style="font-size: 17px; font-weight: 800; color: var(--text-main); margin: 0 0 6px 0;">No alerts match your current filters</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin: 0 0 20px 0;">Try adjusting your search criteria, severity level, or category selector.</p>
        <button class="btn-action-primary" id="resetEmptyFiltersBtn">
            <i class="fas fa-undo"></i> Clear All Filters
        </button>
    </div>

</div>

<!-- 4. SLIDE-OVER ALERT DETAIL DRAWER -->
<div class="drawer-overlay" id="alertDetailDrawer">
    <div class="drawer-panel" id="alertDrawerPanel">
        <div class="drawer-header">
            <div>
                <span class="severity-badge sev-warning" id="drawerSevBadge">● WARNING</span>
                <h3 style="font-size: 16px; font-weight: 800; color: var(--text-main); margin: 6px 0 0 0;" id="drawerAlertTitle">
                    Subscription Expiring in 7 Days
                </h3>
            </div>
            <button class="btn-action-secondary" id="closeAlertDrawerBtn" style="padding: 6px 10px;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="drawer-body">
            <!-- Affected Company Profile -->
            <h4 style="font-size: 11.5px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; margin: 0 0 10px 0;">Affected Tenant Company</h4>
            <div style="background: var(--bg-subtle); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <strong style="font-size: 14px; color: var(--text-main); display: block;" id="drawerCompanyName">Original Company</strong>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                        Tenant ID: <code style="font-family: monospace; font-weight: 700; color: #475569;" id="drawerTenantCode">MAIN</code> • Domain: <span id="drawerCompanyDomain">original.pms.com</span>
                    </div>
                </div>
                <a href="#" id="drawerCompanyLink" class="btn-action-secondary" style="padding: 4px 10px; font-size: 11.5px;">
                    <i class="fas fa-external-link-alt"></i> Profile
                </a>
            </div>

            <!-- Subscription Intel Grid -->
            <h4 style="font-size: 11.5px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; margin: 0 0 10px 0;">Subscription Specifications</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
                <div style="background: var(--bg-subtle); padding: 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <span style="font-size: 11px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Current Package</span>
                    <div style="font-size: 15px; font-weight: 800; color: var(--primary);" id="drawerPlanName">DIAMOND PLAN</div>
                </div>
                <div style="background: var(--bg-subtle); padding: 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <span style="font-size: 11px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Days Remaining</span>
                    <div style="font-size: 15px; font-weight: 800; color: #dc2626;" id="drawerDaysLeft">7 Days</div>
                </div>
            </div>

            <!-- Infrastructure Status -->
            <h4 style="font-size: 11.5px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; margin: 0 0 10px 0;">Infrastructure Metrics</h4>
            <div style="background: var(--bg-subtle); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 20px; font-size: 12.5px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: var(--text-muted);">Tenant Database:</span>
                    <code style="font-family: monospace; font-weight: 700; color: var(--text-main);" id="drawerDbName">pms_last</code>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: var(--text-muted);">Database Health:</span>
                    <strong style="color: var(--success);" id="drawerDbHealth">Healthy (21ms)</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Storage Usage:</span>
                    <strong style="color: #d97706;" id="drawerStorageUsage">84% Capacity</strong>
                </div>
            </div>

            <!-- Audit Timeline -->
            <h4 style="font-size: 11.5px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; margin: 0 0 10px 0;">Alert Life-Cycle Timeline</h4>
            <div style="display: flex; flex-direction: column; gap: 8px; font-size: 12px; margin-bottom: 24px;" id="drawerTimelineList">
                <div style="display: flex; justify-content: space-between; padding: 8px 10px; background: var(--bg-subtle); border-radius: 6px; border: 1px solid var(--border-color);">
                    <span><strong style="color: var(--text-main);">Subscription expiry alert generated</strong></span>
                    <span style="color: var(--text-subtle);">42 mins ago</span>
                </div>
            </div>
        </div>

        <!-- Drawer Footer Actions -->
        <div style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
            <button class="btn-action-secondary" id="drawerMarkReadBtn">
                <i class="fas fa-check"></i> Mark as Read
            </button>
            <button class="btn-action-primary" id="drawerResolveBtn">
                <i class="fas fa-check-circle"></i> Mark as Resolved
            </button>
        </div>
    </div>
</div>

<!-- 5. NOTIFICATION SETTINGS MODAL -->
<div class="modal-backdrop" id="notifSettingsModal">
    <div style="background: var(--bg-surface); color: var(--text-main); border-radius: var(--radius-lg); width: 540px; max-width: 92vw; overflow: hidden; box-shadow: var(--shadow-lg); border: 1px solid var(--border-color);">
        <div style="padding: 18px 24px; background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--text-main); margin: 0;">
                <i class="fas fa-sliders-h" style="color: var(--primary);"></i> Alert &amp; Notification Preferences
            </h3>
            <button class="btn-action-secondary" id="closeSettingsModalBtn" style="padding: 6px 10px;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div style="padding: 24px; display: flex; flex-direction: column; gap: 16px; font-size: 13px;">
            <!-- Email Alert Dispatch -->
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: var(--bg-subtle); border-radius: 8px; border: 1px solid var(--border-color);">
                <div>
                    <strong style="color: var(--text-main); display: block;">Super Admin Email Alerts</strong>
                    <span style="font-size: 11.5px; color: var(--text-muted);">Receive immediate emails for Critical &amp; Action Required alerts.</span>
                </div>
                <input type="checkbox" checked style="width: 18px; height: 18px; cursor: pointer;" />
            </div>

            <!-- Subscription Threshold Warnings -->
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: var(--bg-subtle); border-radius: 8px; border: 1px solid var(--border-color);">
                <div>
                    <strong style="color: var(--text-main); display: block;">Subscription Expiry Warnings</strong>
                    <span style="font-size: 11.5px; color: var(--text-muted);">Auto-trigger warnings at 30, 15, 7, 3, and 1-day thresholds.</span>
                </div>
                <input type="checkbox" checked style="width: 18px; height: 18px; cursor: pointer;" />
            </div>

            <!-- Database & Storage Alerts -->
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: var(--bg-subtle); border-radius: 8px; border: 1px solid var(--border-color);">
                <div>
                    <strong style="color: var(--text-main); display: block;">Storage &amp; Database Thresholds</strong>
                    <span style="font-size: 11.5px; color: var(--text-muted);">Notify when tenant DB storage exceeds 80% or connections drop.</span>
                </div>
                <input type="checkbox" checked style="width: 18px; height: 18px; cursor: pointer;" />
            </div>
        </div>

        <div style="padding: 14px 24px; background: var(--bg-subtle); border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
            <button class="btn-action-secondary" id="cancelSettingsBtn">Cancel</button>
            <button class="btn-action-primary" id="saveSettingsBtn">Save Preferences</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // 1. Filtering Engine (Severity, Category, Status, Company, Time, Search)
    // Cards are looked up on every pass because the live-records sync can re-insert them.
    const searchInput = document.getElementById('alertSearchInput');
    const severitySelect = document.getElementById('severityFilterSelect');
    const categorySelect = document.getElementById('categoryFilterSelect');
    const statusSelect = document.getElementById('statusFilterSelect');
    const companySelect = document.getElementById('companyFilterSelect');
    const timeSelect = document.getElementById('timeFilterSelect');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    const resetEmptyFiltersBtn = document.getElementById('resetEmptyFiltersBtn');

    const filteredEmptyState = document.getElementById('filteredEmptyState');
    const kpiCards = document.querySelectorAll('.kpi-card');

    function currentAlertCards() {
        return document.querySelectorAll('#alertFeedList .alert-card');
    }

    function withinTimeRange(card, range) {
        if (range === 'all') return true;
        const raw = card.getAttribute('data-timestamp');
        if (!raw) return true;
        const created = new Date(raw.replace(' ', 'T'));
        if (isNaN(created.getTime())) return true;
        const now = new Date();
        if (range === 'today') return created.toDateString() === now.toDateString();
        const days = range === '7days' ? 7 : 30;
        return (now - created) <= days * 86400000;
    }

    function applyFilters() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const severity = severitySelect ? severitySelect.value : 'all';
        const category = categorySelect ? categorySelect.value : 'all';
        const status = statusSelect ? statusSelect.value : 'all';
        const company = companySelect ? companySelect.value : 'all';
        const timeRange = timeSelect ? timeSelect.value : 'all';

        let visibleCount = 0;

        currentAlertCards().forEach(card => {
            const cardSearch = card.getAttribute('data-search') || '';
            const cardSev = card.getAttribute('data-severity') || '';
            const cardCat = card.getAttribute('data-category') || '';
            const cardStat = card.getAttribute('data-status') || '';
            const cardComp = card.getAttribute('data-company-id') || '';
            const cardActionReq = card.getAttribute('data-action-required') === 'true';

            // Every search word must appear somewhere in the alert (title, description, company, code).
            const matchSearch = !query || query.split(/\s+/).every(word => cardSearch.includes(word));
            const matchSev = (severity === 'all') || (cardSev === severity);
            const matchCat = (category === 'all') || (cardCat === category);
            const matchComp = (company === 'all') || (cardComp === company);
            const matchTime = withinTimeRange(card, timeRange);

            let matchStat = true;
            if (status !== 'all') {
                if (status === 'action_required') matchStat = cardActionReq && cardStat !== 'resolved';
                else matchStat = (cardStat === status);
            }

            if (matchSearch && matchSev && matchCat && matchStat && matchComp && matchTime) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (filteredEmptyState) {
            filteredEmptyState.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
        searchInput.addEventListener('search', applyFilters);
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); applyFilters(); }
        });
    }
    [severitySelect, categorySelect, statusSelect, companySelect, timeSelect].forEach(select => {
        if (select) select.addEventListener('change', applyFilters);
    });

    function resetFilters() {
        if (searchInput) searchInput.value = '';
        if (severitySelect) severitySelect.value = 'all';
        if (categorySelect) categorySelect.value = 'all';
        if (statusSelect) statusSelect.value = 'all';
        if (companySelect) companySelect.value = 'all';
        if (timeSelect) timeSelect.value = 'all';
        applyFilters();
    }

    if (clearFiltersBtn) clearFiltersBtn.addEventListener('click', resetFilters);
    if (resetEmptyFiltersBtn) resetEmptyFiltersBtn.addEventListener('click', resetFilters);

    // KPI Cards Click-to-Filter Interaction
    kpiCards.forEach(card => {
        card.addEventListener('click', function() {
            const filterType = this.getAttribute('data-filter-type');
            const filterVal = this.getAttribute('data-filter-val');

            resetFilters();

            if (filterType === 'severity' && severitySelect) severitySelect.value = filterVal;
            if (filterType === 'category' && categorySelect) categorySelect.value = filterVal;
            if (filterType === 'status' && statusSelect) statusSelect.value = filterVal;

            applyFilters();
        });
    });

    // 2. Mark Single Read & Resolve Actions
    // Delegated from the document so buttons keep working after live-records refreshes the feed.
    const markAllReadHeaderBtn = document.getElementById('markAllReadHeaderBtn');
    const alertCsrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const alertsBaseUrl = @json(url('/super-admin/alerts'));

    function postAlertAction(alertKey, action) {
        return fetch(`${alertsBaseUrl}/${encodeURIComponent(alertKey)}/${action}`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': alertCsrfToken
            }
        }).then(res => res.json().catch(() => ({})).then(data => {
            if (!res.ok || !data.success) throw new Error(data.message || 'Action failed. Please try again.');
            return data;
        }));
    }

    function findAlertCard(alertKey) {
        return Array.from(currentAlertCards()).find(card => card.getAttribute('data-alert-key') === alertKey) || null;
    }

    function markCardRead(card) {
        if (!card) return;
        card.classList.remove('is-unread');
        if (card.getAttribute('data-status') === 'unread') card.setAttribute('data-status', 'read');
        card.querySelectorAll('.unread-indicator, .mark-read-btn').forEach(el => el.remove());
    }

    function markCardResolved(card) {
        if (!card) return;
        markCardRead(card);
        card.classList.add('is-resolved');
        card.setAttribute('data-status', 'resolved');
        const resolveBtns = card.querySelectorAll('.resolve-alert-btn');
        if (resolveBtns.length && !card.querySelector('.resolved-alert-label')) {
            const label = document.createElement('span');
            label.className = 'resolved-alert-label';
            label.style.cssText = 'font-size: 11px; color: var(--success); font-weight: 700;';
            label.innerHTML = '<i class="fas fa-check"></i> Resolved';
            resolveBtns[0].replaceWith(label);
        }
        card.querySelectorAll('.resolve-alert-btn').forEach(el => el.remove());
    }

    // Live refresh keeps old buttons and adds fresh ones; drop duplicates and buttons that no longer apply.
    function normalizeAlertCards() {
        currentAlertCards().forEach(card => {
            ['.inspect-alert-btn', '.mark-read-btn', '.resolve-alert-btn', '.resolved-alert-label'].forEach(selector => {
                card.querySelectorAll(selector).forEach((el, index) => { if (index > 0) el.remove(); });
            });
            const status = card.getAttribute('data-status');
            if (status !== 'unread') markCardRead(card);
            if (status === 'resolved') markCardResolved(card);
            else card.classList.remove('is-resolved');
        });
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('#alertFeedList .mark-read-btn, #alertFeedList .resolve-alert-btn');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        if (btn.disabled) return;

        const card = btn.closest('.alert-card');
        const alertKey = btn.getAttribute('data-alert-key') || card?.getAttribute('data-alert-key');
        if (!alertKey) return;

        const isResolve = btn.classList.contains('resolve-alert-btn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (isResolve ? 'Resolving...' : 'Saving...');

        postAlertAction(alertKey, isResolve ? 'resolve' : 'read')
            .then(() => {
                if (isResolve) markCardResolved(card); else markCardRead(card);
                applyFilters();
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                alert(error.message);
            });
    });

    document.addEventListener('pms:records-updated', function () {
        normalizeAlertCards();
        applyFilters();
    });
    normalizeAlertCards();

    if (markAllReadHeaderBtn) {
        markAllReadHeaderBtn.addEventListener('click', function() {
            this.disabled = true;
            fetch("{{ Route::has('super-admin.alerts.mark-all-read') ? route('super-admin.alerts.mark-all-read') : (Route::has('superadmin.alerts.mark-all-read') ? route('superadmin.alerts.mark-all-read') : url('/super-admin/alerts/mark-all-read')) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Could not mark alerts as read. Please try again.');
                return res.json();
            })
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Could not mark alerts as read.');
                window.location.reload();
            })
            .catch(error => {
                window.alert(error.message);
                markAllReadHeaderBtn.disabled = false;
            });
        });
    }

    // Refresh Button
    const refreshAlertsBtn = document.getElementById('refreshAlertsBtn');
    if (refreshAlertsBtn) {
        refreshAlertsBtn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
            setTimeout(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Alerts';
                document.getElementById('lastUpdatedTimestamp').textContent = new Date().toTimeString().slice(0,8);
            }, 500);
        });
    }

    // 3. Slide-Over Alert Detail Drawer Handler (delegated, see section 2)
    const drawerOverlay = document.getElementById('alertDetailDrawer');
    const drawerPanel = document.getElementById('alertDrawerPanel');
    const closeAlertDrawerBtn = document.getElementById('closeAlertDrawerBtn');
    const drawerMarkReadBtn = document.getElementById('drawerMarkReadBtn');
    const drawerResolveBtn = document.getElementById('drawerResolveBtn');
    let drawerAlertKey = null;

    function setDrawerText(id, value) {
        const el = document.getElementById(id);
        if (el && value !== undefined && value !== null && value !== '') el.textContent = value;
    }

    function syncDrawerActions() {
        const card = drawerAlertKey ? findAlertCard(drawerAlertKey) : null;
        const status = card ? card.getAttribute('data-status') : 'resolved';
        if (drawerMarkReadBtn) drawerMarkReadBtn.style.display = status === 'unread' ? '' : 'none';
        if (drawerResolveBtn) drawerResolveBtn.style.display = status === 'resolved' ? 'none' : '';
    }

    function closeAlertDrawer() {
        if (drawerOverlay) drawerOverlay.classList.remove('open');
        if (drawerPanel) drawerPanel.classList.remove('open');
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('#alertFeedList .inspect-alert-btn');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();

        const card = btn.closest('.alert-card');
        const alertId = btn.getAttribute('data-id');
        const attrSev = btn.getAttribute('data-severity');
        const attrDays = btn.getAttribute('data-days');
        drawerAlertKey = btn.getAttribute('data-alert-key') || card?.getAttribute('data-alert-key');

        // Fill the drawer from this alert's own data
        setDrawerText('drawerAlertTitle', btn.getAttribute('data-title'));
        setDrawerText('drawerCompanyName', btn.getAttribute('data-company-name'));
        setDrawerText('drawerTenantCode', btn.getAttribute('data-tenant-code'));
        setDrawerText('drawerCompanyDomain', btn.getAttribute('data-domain'));
        setDrawerText('drawerPlanName', btn.getAttribute('data-plan'));
        if (attrDays !== null) setDrawerText('drawerDaysLeft', attrDays + ' Days');
        setDrawerText('drawerDbName', btn.getAttribute('data-db'));

        const drawerSevBadge = document.getElementById('drawerSevBadge');
        if (drawerSevBadge && attrSev) {
            drawerSevBadge.className = 'severity-badge sev-' + attrSev;
            drawerSevBadge.textContent = '● ' + attrSev.toUpperCase();
        }

        const timeline = document.getElementById('drawerTimelineList');
        if (timeline && card) {
            const createdText = (card.querySelector('.fa-clock')?.parentElement?.textContent || '').trim();
            const row = document.createElement('div');
            row.style.cssText = 'display: flex; justify-content: space-between; gap: 12px; padding: 8px 10px; background: var(--bg-subtle); border-radius: 6px; border: 1px solid var(--border-color);';
            const label = document.createElement('strong');
            label.style.color = 'var(--text-main)';
            label.textContent = (btn.getAttribute('data-title') || 'Alert') + ' — alert generated';
            const when = document.createElement('span');
            when.style.cssText = 'color: var(--text-subtle); flex-shrink: 0;';
            when.textContent = createdText;
            row.append(label, when);
            timeline.replaceChildren(row);
        }

        const compId = btn.getAttribute('data-company-id') || '';
        const link = document.getElementById('drawerCompanyLink');
        if (link) {
            link.href = compId ? `{{ url('/super-admin/companies') }}/${encodeURIComponent(compId)}` : '#';
            link.style.display = compId ? '' : 'none';
        }

        syncDrawerActions();
        if (drawerOverlay) drawerOverlay.classList.add('open');
        if (drawerPanel) drawerPanel.classList.add('open');

        // Live tenant infrastructure details (title and severity stay those of the inspected alert)
        if (!compId) return;
        fetch(`${alertsBaseUrl}/details/${encodeURIComponent(alertId)}?company_id=${encodeURIComponent(compId)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(res => res.ok ? res.json() : null)
            .then(data => {
                if (!data || !data.alert) return;
                const a = data.alert;
                setDrawerText('drawerCompanyName', a.company_name);
                setDrawerText('drawerTenantCode', a.tenant_code);
                setDrawerText('drawerCompanyDomain', a.domain);
                setDrawerText('drawerPlanName', a.plan_name);
                if (a.days_remaining !== undefined) setDrawerText('drawerDaysLeft', a.days_remaining + ' Days');
                setDrawerText('drawerDbName', a.db_name);
                setDrawerText('drawerDbHealth', a.db_health);
                setDrawerText('drawerStorageUsage', a.storage_usage);
            })
            .catch(() => {});
    });

    [[drawerMarkReadBtn, 'read'], [drawerResolveBtn, 'resolve']].forEach(([button, action]) => {
        if (!button) return;
        button.addEventListener('click', function () {
            if (!drawerAlertKey || button.disabled) return;
            const originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            postAlertAction(drawerAlertKey, action)
                .then(() => {
                    const card = findAlertCard(drawerAlertKey);
                    if (action === 'resolve') markCardResolved(card); else markCardRead(card);
                    applyFilters();
                    syncDrawerActions();
                    if (action === 'resolve') closeAlertDrawer();
                })
                .catch(error => alert(error.message))
                .finally(() => {
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                });
        });
    });

    if (closeAlertDrawerBtn) closeAlertDrawerBtn.addEventListener('click', closeAlertDrawer);

    if (drawerOverlay) {
        drawerOverlay.addEventListener('click', function(e) {
            if (e.target === drawerOverlay) closeAlertDrawer();
        });
    }

    // 4. Notification Preferences Settings Modal Handler
    const settingsModal = document.getElementById('notifSettingsModal');
    const openSettingsModalBtn = document.getElementById('openSettingsModalBtn');
    const closeSettingsModalBtn = document.getElementById('closeSettingsModalBtn');
    const cancelSettingsBtn = document.getElementById('cancelSettingsBtn');
    const saveSettingsBtn = document.getElementById('saveSettingsBtn');

    if (openSettingsModalBtn) {
        openSettingsModalBtn.addEventListener('click', function() {
            settingsModal.style.display = 'flex';
        });
    }

    function hideSettingsModal() {
        if (settingsModal) settingsModal.style.display = 'none';
    }

    if (closeSettingsModalBtn) closeSettingsModalBtn.addEventListener('click', hideSettingsModal);
    if (cancelSettingsBtn) cancelSettingsBtn.addEventListener('click', hideSettingsModal);
    if (saveSettingsBtn) {
        saveSettingsBtn.addEventListener('click', function() {
            hideSettingsModal();
        });
    }
});
</script>
@endpush
