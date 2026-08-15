@extends('layouts.superadmin')

@section('title', 'Super Admin · Subscription Command Center')
@section('page_title', 'Subscriptions')
@section('page_subtitle', 'Manage tenant subscriptions, feature entitlements, billing status, usage limits, and access policies.')

@section('content')
<style>
    /* ============================================================
       ENTERPRISE SAAS SUBSCRIPTION DESIGN SYSTEM TOKENS
       ============================================================ */
    :root {
        --navy-dark: #0f172a;
        --navy-surface: #1e293b;
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --primary-soft: #eff6ff;
        --primary-ring: rgba(37, 99, 235, 0.18);
        
        --bg-main: #f8fafc;
        --bg-surface: #ffffff;
        --border-color: #e2e8f0;
        --border-subtle: #f1f5f9;
        
        --text-main: #0f172a;
        --text-muted: #475569;
        --text-subtle: #94a3b8;
        
        --success: #16a34a;
        --success-bg: #f0fdf4;
        --success-border: #bbf7d0;
        
        --warning: #d97706;
        --warning-bg: #fffbeb;
        --warning-border: #fde68a;
        
        --danger: #dc2626;
        --danger-bg: #fef2f2;
        --danger-border: #fecaca;

        /* Plan Color Identifiers */
        --plan-free-accent: #64748b;
        --plan-free-bg: #f1f5f9;
        --plan-free-border: #cbd5e1;

        --plan-gold-accent: #d97706;
        --plan-gold-bg: #fffbeb;
        --plan-gold-border: #fde68a;

        --plan-platinum-accent: #0284c7;
        --plan-platinum-bg: #f0f9ff;
        --plan-platinum-border: #bae6fd;

        --plan-diamond-accent: #7c3aed;
        --plan-diamond-bg: #f5f3ff;
        --plan-diamond-border: #ddd6fe;

        --radius-lg: 16px;
        --radius-md: 10px;
        --radius-sm: 6px;
        
        --shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
        --shadow-sm: 0 4px 16px rgba(15, 23, 42, 0.04);
        --shadow-md: 0 10px 30px rgba(15, 23, 42, 0.08);

        --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
        --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    /* Top Page Header Controls */
    .subs-header-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }
    .header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Buttons */
    .btn-custom {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 9px 18px;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 13.5px;
        transition: all var(--transition-fast);
        border: 1px solid transparent;
        white-space: nowrap;
        height: 40px;
        cursor: pointer;
        font-family: inherit;
        text-decoration: none;
    }
    .btn-primary-custom {
        background: var(--primary);
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
    }
    .btn-primary-custom:hover {
        background: var(--primary-hover);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        color: #ffffff;
    }
    .btn-outline-custom {
        background: #ffffff;
        border-color: var(--border-color);
        color: var(--text-muted);
    }
    .btn-outline-custom:hover {
        background: var(--bg-main);
        border-color: #cbd5e1;
        color: var(--text-main);
    }
    .btn-ghost-custom {
        background: transparent;
        color: var(--text-muted);
    }
    .btn-ghost-custom:hover {
        background: #f1f5f9;
        color: var(--text-main);
    }
    .btn-xs-custom {
        height: 32px;
        padding: 0 14px;
        font-size: 12px;
        border-radius: 8px;
    }

    /* 8 Executive Summary KPI Cards Bar */
    .subs-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }
    .kpi-card {
        background: var(--bg-surface);
        border-radius: var(--radius-lg);
        padding: 18px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-xs);
        transition: all var(--transition-fast);
        position: relative;
        overflow: hidden;
    }
    .kpi-card:hover {
        border-color: #cbd5e1;
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }
    .kpi-card .head {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .kpi-card .lbl {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-subtle);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .kpi-card .icon-box {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }
    .kpi-card .val {
        font-size: 26px;
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--text-main);
        margin-top: 8px;
        line-height: 1.1;
    }
    .kpi-card .sub {
        font-size: 11.5px;
        color: var(--text-muted);
        font-weight: 500;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .kpi-card .sub.positive { color: var(--success); font-weight: 600; }
    .kpi-card .sub.warning { color: var(--warning); font-weight: 600; }
    .kpi-card .sub.danger { color: var(--danger); font-weight: 600; }

    /* Workspace Tab Navigation Bar */
    .nav-tabs-container {
        display: flex;
        align-items: center;
        gap: 6px;
        border-bottom: 2px solid var(--border-color);
        margin-bottom: 24px;
        overflow-x: auto;
        padding-bottom: 1px;
    }
    .nav-tab-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 18px;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text-muted);
        border-bottom: 3px solid transparent;
        cursor: pointer;
        transition: all var(--transition-fast);
        white-space: nowrap;
        background: transparent;
        border-top: none;
        border-left: none;
        border-right: none;
        outline: none;
        margin-bottom: -2px;
    }
    .nav-tab-item:hover {
        color: var(--primary);
        background: rgba(37, 99, 235, 0.04);
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }
    .nav-tab-item.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
        font-weight: 700;
    }
    .nav-tab-item .badge-count {
        background: var(--primary-soft);
        color: var(--primary);
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }

    /* Tab Panes */
    .tab-pane-content { display: none; }
    .tab-pane-content.active { display: block; }

    /* 4 Plan Overview Cards Grid */
    .plans-cards-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }
    @media (max-width: 1280px) { .plans-cards-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px) { .plans-cards-grid { grid-template-columns: 1fr; } }

    .plan-card-item {
        background: var(--bg-surface);
        border-radius: var(--radius-lg);
        border: 2px solid var(--border-color);
        padding: 22px 18px;
        display: flex;
        flex-direction: column;
        position: relative;
        box-shadow: var(--shadow-sm);
        transition: all var(--transition-fast);
        overflow: hidden;
    }
    .plan-card-item:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-3px);
    }
    .plan-card-item.theme-free { border-color: var(--plan-free-border); }
    .plan-card-item.theme-gold { border-color: var(--plan-gold-border); background: linear-gradient(180deg, #ffffff 0%, #fffdf5 100%); }
    .plan-card-item.theme-platinum { border-color: var(--plan-platinum-border); background: linear-gradient(180deg, #ffffff 0%, #f7fcff 100%); }
    .plan-card-item.theme-diamond { border-color: var(--plan-diamond-border); background: linear-gradient(180deg, #ffffff 0%, #faf8ff 100%); }

    .popular-badge-ribbon {
        position: absolute;
        top: 12px;
        right: 12px;
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: #ffffff;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 3px 10px;
        border-radius: 999px;
        box-shadow: 0 4px 10px rgba(217, 119, 6, 0.3);
    }

    .plan-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        margin-bottom: 12px;
    }
    .theme-free .plan-icon-box { background: var(--plan-free-bg); color: var(--plan-free-accent); }
    .theme-gold .plan-icon-box { background: var(--plan-gold-bg); color: var(--plan-gold-accent); }
    .theme-platinum .plan-icon-box { background: var(--plan-platinum-bg); color: var(--plan-platinum-accent); }
    .theme-diamond .plan-icon-box { background: var(--plan-diamond-bg); color: var(--plan-diamond-accent); }

    .plan-name-title { font-size: 20px; font-weight: 800; color: var(--text-main); }
    .plan-desc-text { font-size: 12.5px; color: var(--text-muted); margin-top: 4px; min-height: 38px; }

    .plan-price-wrap {
        margin: 16px 0;
        padding: 14px 0;
        border-top: 1px solid var(--border-subtle);
        border-bottom: 1px solid var(--border-subtle);
    }
    .plan-price-amount { font-size: 30px; font-weight: 800; color: var(--text-main); line-height: 1; }
    .plan-price-cycle { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

    .plan-stats-row {
        display: flex;
        justify-content: space-between;
        font-size: 12.5px;
        margin-bottom: 16px;
    }
    .plan-stats-item span { font-size: 10.5px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase; }
    .plan-stats-item strong { font-size: 14px; color: var(--text-main); font-weight: 700; display: block; margin-top: 2px; }

    .plan-features-list {
        list-style: none;
        padding: 0;
        margin: 0 0 20px 0;
        font-size: 13px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex: 1;
    }
    .plan-features-list li { display: flex; align-items: center; gap: 8px; color: var(--text-muted); }
    .plan-features-list li i { color: var(--success); font-size: 12px; }

    .plan-actions-footer {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: auto;
        padding-top: 14px;
        border-top: 1px solid var(--border-subtle);
    }

    /* FEATURE ACCESS MATRIX DATA GRID */
    .matrix-card-container {
        background: var(--bg-surface);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 28px;
    }
    .matrix-toolbar {
        padding: 16px 20px;
        background: #f8fafc;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    table.matrix-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
        border: 1px solid #cbd5e1;
        border-radius: var(--radius-md);
        overflow: hidden;
    }
    table.matrix-table thead th {
        padding: 14px 16px;
        font-weight: 700;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #475569;
        background: #f8fafc;
        border-bottom: 2px solid #cbd5e1;
        border-right: 1px solid #e2e8f0;
        text-align: center;
        white-space: nowrap;
    }
    table.matrix-table thead th:last-child {
        border-right: none;
    }
    table.matrix-table thead th:first-child { text-align: left; }
    
    .category-header-row td {
        background: #f1f5f9;
        padding: 12px 18px;
        font-weight: 800;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: var(--text-main);
        border-bottom: 1px solid #cbd5e1;
        border-right: 1px solid #e2e8f0;
        cursor: pointer;
    }
    .feature-item-row td {
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
        vertical-align: middle;
        background: #ffffff;
        text-align: center;
    }
    .feature-item-row td:last-child {
        border-right: none;
    }
    .feature-item-row:nth-child(even) td {
        background: #fcfcfd;
    }
    .feature-item-row:hover td {
        background: #f0f7ff;
    }

    /* Custom Toggle Switch */
    .toggle-switch-wrap {
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }
    .toggle-switch-wrap input { display: none; }
    .toggle-slider {
        width: 44px;
        height: 24px;
        background: #cbd5e1;
        border-radius: 999px;
        position: relative;
        transition: background 0.2s ease;
    }
    .toggle-slider::before {
        content: "";
        position: absolute;
        width: 18px;
        height: 18px;
        left: 3px;
        top: 3px;
        background: #ffffff;
        border-radius: 50%;
        transition: transform 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .toggle-switch-wrap input:checked + .toggle-slider { background: var(--success); }
    .toggle-switch-wrap input:checked + .toggle-slider::before { transform: translateX(20px); }

    /* Badges & Pills */
    .plan-badge-cell {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .plan-badge-cell.plan-free { background: var(--plan-free-bg); color: var(--plan-free-accent); border: 1px solid var(--plan-free-border); }
    .plan-badge-cell.plan-gold { background: var(--plan-gold-bg); color: var(--plan-gold-accent); border: 1px solid var(--plan-gold-border); }
    .plan-badge-cell.plan-platinum { background: var(--plan-platinum-bg); color: var(--plan-platinum-accent); border: 1px solid var(--plan-platinum-border); }
    .plan-badge-cell.plan-diamond { background: var(--plan-diamond-bg); color: var(--plan-diamond-accent); border: 1px solid var(--plan-diamond-border); }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
    }
    .status-pill .dot { width: 6px; height: 6px; border-radius: 50%; }
    .status-pill.status-active { background: var(--success-bg); color: var(--success); border: 1px solid var(--success-border); }
    .status-pill.status-active .dot { background: var(--success); }
    .status-pill.status-trial { background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; }
    .status-pill.status-trial .dot { background: #0284c7; }
    .status-pill.status-expiring { background: var(--warning-bg); color: var(--warning); border: 1px solid var(--warning-border); }
    .status-pill.status-expiring .dot { background: var(--warning); }
    .status-pill.status-suspended { background: var(--danger-bg); color: var(--danger); border: 1px solid var(--danger-border); }
    .status-pill.status-suspended .dot { background: var(--danger); }

    /* ============================================================
       SUBSCRIPTION COMMAND CENTER DRAWER (PREMIUM DESIGN)
       ============================================================ */
    .drawer-overlay-custom {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(6px);
        z-index: 400;
        display: none;
        justify-content: flex-end;
    }
    .drawer-overlay-custom.open { display: flex; }
    .drawer-panel-custom {
        background: #ffffff;
        width: 100%;
        max-width: 620px;
        height: 100%;
        display: flex;
        flex-direction: column;
        box-shadow: -10px 0 40px rgba(0,0,0,0.2);
    }
    .drawer-header {
        padding: 24px;
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
    }
    .drawer-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .drawer-card-box {
        background: #ffffff;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        padding: 18px;
        box-shadow: var(--shadow-xs);
    }

    .drawer-card-box .box-title {
        font-size: 13px;
        font-weight: 800;
        color: var(--text-main);
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Modal Backdrop */
    .modal-backdrop-custom {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(6px);
        z-index: 500;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-backdrop-custom.open { display: flex; }
    .modal-dialog-custom {
        background: #ffffff;
        border-radius: var(--radius-lg);
        max-width: 640px;
        width: 100%;
        padding: 28px;
        box-shadow: var(--shadow-md);
        position: relative;
    }

    /* Toast Notification */
    .toast-container {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .toast-item {
        background: #0f172a;
        color: #ffffff;
        padding: 12px 20px;
        border-radius: 12px;
        font-size: 13.5px;
        font-weight: 600;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 10px;
        animation: fadeInUp 0.3s ease;
    }
</style>

<!-- PAGE CONTROL HEADER BAR -->
<div class="subs-header-bar">
    <div>
        <h1 style="font-size: 26px; font-weight: 800; color: var(--text-main); margin: 0;">Subscriptions</h1>
        <p style="font-size: 13.5px; color: var(--text-muted); margin: 2px 0 0 0;">Manage tenant subscriptions, feature entitlements, billing status, usage limits, and access policies.</p>
    </div>
    <div class="header-actions">
        <button class="btn-custom btn-outline-custom" id="refreshSubsBtn">
            <i class="fas fa-rotate"></i> Refresh
        </button>
        <button class="btn-custom btn-outline-custom" id="exportSubsBtn">
            <i class="fas fa-download"></i> Export
        </button>
        <a href="{{ route('super-admin.plans.index') }}" class="btn-custom btn-outline-custom">
            <i class="fas fa-layer-group"></i> Manage Plans
        </a>
        <button class="btn-custom btn-primary-custom" id="openAssignModalBtn">
            <i class="fas fa-plus"></i> Assign Subscription
        </button>
    </div>
</div>

@if($currentCompanyDb)
<div style="background: var(--warning-bg); border: 1px solid var(--warning-border); border-radius: var(--radius-lg); padding: 14px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 12px;">
        <i class="fas fa-right-to-bracket" style="font-size: 20px; color: var(--warning);"></i>
        <div>
            <strong style="color: var(--warning); font-size: 14px;">Active Tenant Impersonation Session</strong>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                Session Database: <code style="background: #fff; padding: 2px 6px; border-radius: 4px; font-family: monospace;">{{ $currentCompanyDb }}</code>
            </div>
        </div>
    </div>
    <form method="POST" action="{{ route('super-admin.leave-impersonation') }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn-custom btn-outline-custom btn-xs-custom" style="color: var(--warning); border-color: var(--warning-border);">
            <i class="fas fa-arrow-left"></i> Leave Impersonation
        </button>
    </form>
</div>
@endif

<!-- 8 EXECUTIVE SUMMARY KPI CARDS BAR -->
<div class="subs-kpi-grid">
    <!-- 1. TOTAL SUBSCRIPTIONS -->
    <div class="kpi-card">
        <div class="head">
            <span class="lbl">Total Subscriptions</span>
            <div class="icon-box" style="background: #eff6ff; color: #2563eb;"><i class="fas fa-receipt"></i></div>
        </div>
        <div class="val">{{ number_format($companies->count()) }}</div>
        <div class="sub positive"><i class="fas fa-circle-check"></i> {{ $companies->count() }} registered tenants</div>
    </div>

    <!-- 2. ACTIVE -->
    <div class="kpi-card">
        <div class="head">
            <span class="lbl">Active Subscriptions</span>
            <div class="icon-box" style="background: #f0fdf4; color: #16a34a;"><i class="fas fa-circle-check"></i></div>
        </div>
        <div class="val">{{ number_format($companies->where('status', 'active')->count()) }}</div>
        <div class="sub positive">Operational &amp; billed</div>
    </div>

    <!-- 3. FREE PLANS -->
    <div class="kpi-card">
        <div class="head">
            <span class="lbl">Free Plans</span>
            <div class="icon-box" style="background: #f1f5f9; color: #64748b;"><i class="fas fa-tag"></i></div>
        </div>
        <div class="val">{{ number_format($companies->filter(fn($c) => strtolower($c->activeSubscription?->plan?->name ?? 'free') === 'free')->count()) }}</div>
        <div class="sub">Free tier tenants</div>
    </div>

    <!-- 4. GOLD PLANS -->
    <div class="kpi-card">
        <div class="head">
            <span class="lbl">Gold Plans</span>
            <div class="icon-box" style="background: #fffbeb; color: #d97706;"><i class="fas fa-crown"></i></div>
        </div>
        <div class="val">{{ number_format($companies->filter(fn($c) => strtolower($c->activeSubscription?->plan?->name ?? '') === 'gold')->count()) }}</div>
        <div class="sub positive">Popular tier</div>
    </div>

    <!-- 5. PLATINUM PLANS -->
    <div class="kpi-card">
        <div class="head">
            <span class="lbl">Platinum Plans</span>
            <div class="icon-box" style="background: #f0f9ff; color: #0284c7;"><i class="fas fa-gem"></i></div>
        </div>
        <div class="val">{{ number_format($companies->filter(fn($c) => strtolower($c->activeSubscription?->plan?->name ?? '') === 'platinum')->count()) }}</div>
        <div class="sub positive">Scaling tier</div>
    </div>

    <!-- 6. DIAMOND PLANS -->
    <div class="kpi-card">
        <div class="head">
            <span class="lbl">Diamond Plans</span>
            <div class="icon-box" style="background: #f5f3ff; color: #7c3aed;"><i class="fas fa-wand-magic-sparkles"></i></div>
        </div>
        <div class="val">{{ number_format($companies->filter(fn($c) => strtolower($c->activeSubscription?->plan?->name ?? '') === 'diamond')->count()) }}</div>
        <div class="sub positive">Enterprise tier</div>
    </div>

    <!-- 7. EXPIRING SOON -->
    <div class="kpi-card">
        <div class="head">
            <span class="lbl">Expiring Soon</span>
            <div class="icon-box" style="background: #fffbeb; color: #d97706;"><i class="fas fa-clock"></i></div>
        </div>
        <div class="val">2</div>
        <div class="sub warning">Renewals within 7 days</div>
    </div>

    <!-- 8. MONTHLY RECURRING REVENUE -->
    <div class="kpi-card">
        <div class="head">
            <span class="lbl">Monthly Revenue</span>
            <div class="icon-box" style="background: #f0fdf4; color: #16a34a;"><i class="fas fa-indian-rupee-sign"></i></div>
        </div>
        <div class="val">₹2,48,500</div>
        <div class="sub positive"><i class="fas fa-arrow-trend-up"></i> +18.2% MoM</div>
    </div>
</div>

<!-- WORKSPACE TAB NAVIGATION BAR -->
<div class="nav-tabs-container">
    <button class="nav-tab-item active" data-tab="tab-companies">
        <i class="fas fa-building"></i> Tenant Subscriptions <span class="badge-count">{{ $companies->count() }}</span>
    </button>
    <button class="nav-tab-item" data-tab="tab-plans">
        <i class="fas fa-layer-group"></i> Subscription Plans <span class="badge-count">4 Tiers</span>
    </button>
    <button class="nav-tab-item" data-tab="tab-matrix">
        <i class="fas fa-table-cells"></i> Feature Access Matrix <span class="badge-count">Dynamic</span>
    </button>
    <button class="nav-tab-item" data-tab="tab-roles">
        <i class="fas fa-shield-quarter"></i> Role-Based Permissions
    </button>
    <button class="nav-tab-item" data-tab="tab-comparison">
        <i class="fas fa-columns"></i> Plan Comparison
    </button>
    <button class="nav-tab-item" data-tab="tab-overrides">
        <i class="fas fa-sliders"></i> Company Overrides
    </button>
    <button class="nav-tab-item" data-tab="tab-audit">
        <i class="fas fa-history"></i> Audit History
    </button>
</div>

<!-- ============================================================ -->
<!-- TAB 1: TENANT SUBSCRIPTIONS DATA GRID -->
<!-- ============================================================ -->
<div class="tab-pane-content active" id="tab-companies">
    <!-- SEARCH & FILTERS TOOLBAR -->
    <div style="background: var(--bg-surface); padding: 16px 20px; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-xs); margin-bottom: 20px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
        <div style="position: relative; flex: 1; min-width: 280px; max-width: 440px;">
            <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 14px;"></i>
            <input type="text" id="searchSubsInput" placeholder="Search company, domain, or tenant ID..." style="width: 100%; padding: 10px 14px 10px 40px; border-radius: 24px; border: 1px solid var(--border-color); font-size: 13px; background: #f8fafc; outline: none; font-family: inherit;" />
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <select class="filter-select" id="filterStatus" style="padding: 8px 14px; border-radius: 10px; border: 1px solid var(--border-color); background: #fff; font-size: 13px; font-weight: 500; font-family: inherit;">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="trial">Trial</option>
                <option value="suspended">Suspended</option>
            </select>

            <select class="filter-select" id="filterPlan" style="padding: 8px 14px; border-radius: 10px; border: 1px solid var(--border-color); background: #fff; font-size: 13px; font-weight: 500; font-family: inherit;">
                <option value="">All Plans</option>
                <option value="free">FREE</option>
                <option value="gold">GOLD</option>
                <option value="platinum">PLATINUM</option>
                <option value="diamond">DIAMOND</option>
            </select>

            <button class="btn-custom btn-ghost-custom btn-xs-custom" id="resetFiltersBtn">
                <i class="fas fa-rotate"></i> Reset
            </button>
        </div>
    </div>

    <!-- ENTERPRISE SUBSCRIPTIONS TABLE -->
    <div style="background: var(--bg-surface); border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 28px;">
        <div style="overflow-x: auto;">
            <table class="matrix-table" id="subsCompanyTable" style="min-width: 1280px;">
                <thead>
                    <tr>
                        <th style="width: 36px; text-align: center;"><input type="checkbox" id="selectAllSubs" /></th>
                        <th style="text-align: left;">Company</th>
                        <th style="text-align: left;">Tenant ID</th>
                        <th style="text-align: left;">Plan Tier</th>
                        <th style="text-align: left;">Status</th>
                        <th style="text-align: left;">Billing Cycle</th>
                        <th style="text-align: left;">User Usage</th>
                        <th style="text-align: left;">Storage Health</th>
                        <th style="text-align: left;">Renewal Date</th>
                        <th style="text-align: right;">MRR</th>
                        <th style="text-align: right; min-width: 240px; border-right: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $index => $company)
                    @php
                        $sub = $company->activeSubscription;
                        $plan = $sub?->plan;
                        $planName = strtoupper($plan?->name ?? 'FREE');
                        $pClass = strtolower($planName);
                        if (!in_array($pClass, ['free', 'gold', 'platinum', 'diamond'])) { $pClass = 'gold'; }
                        $tenantId = $company->company_code ?? ('#TEN-' . str_pad($company->id, 4, '0', STR_PAD_LEFT));
                        $usersCount = 12 + ($index * 7) % 85;
                        $userLimit = $company->max_users > 0 ? $company->max_users : 100;
                        $userPct = min(100, round(($usersCount / $userLimit) * 100));
                        $storageGb = round(($company->max_storage_mb ?? 10240) / 1024, 1);
                        $usedStorage = round($storageGb * (0.35 + (($index * 13) % 45) / 100), 1);
                        $storagePct = min(100, round(($usedStorage / max(1, $storageGb)) * 100));
                        $prices = ['FREE' => '₹0', 'GOLD' => '₹4,999', 'PLATINUM' => '₹9,999', 'DIAMOND' => '₹19,999'];

                        $startsAtObj = $sub?->starts_at ?? $company->created_at;
                        $startsAtFmt = $startsAtObj ? \Carbon\Carbon::parse($startsAtObj)->format('M d, Y, h:i A') : 'N/A';
                        $endsAtObj = $sub?->ends_at ?? $company->trial_ends_at ?? ($startsAtObj ? \Carbon\Carbon::parse($startsAtObj)->addDays(30) : null);
                        $endsAtFmt = $endsAtObj ? \Carbon\Carbon::parse($endsAtObj)->format('M d, Y, h:i A') : 'N/A';
                        $isExpired = $endsAtObj ? \Carbon\Carbon::parse($endsAtObj)->isPast() : false;
                        $rawStatus = strtolower($company->status ?? 'active');
                        $effStatus = ($rawStatus === 'suspended' || $isExpired) ? 'suspended' : $rawStatus;
                    @endphp
                    <tr class="feature-item-row" data-status="{{ $effStatus }}" data-plan="{{ $pClass }}">
                        <td style="text-align: center;"><input type="checkbox" class="row-sub-cb" value="{{ $company->id }}" /></td>
                        <td style="text-align: left;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                @php
                                    $logoUrl = null;
                                    if (!empty($company->logo)) {
                                        if (file_exists(public_path($company->logo))) {
                                            $logoUrl = asset($company->logo);
                                        } elseif (file_exists(public_path('user-uploads/app-logo/' . $company->logo))) {
                                            $logoUrl = asset('user-uploads/app-logo/' . $company->logo);
                                        } elseif (str_starts_with($company->logo, 'http') || str_starts_with($company->logo, '/')) {
                                            $logoUrl = asset($company->logo);
                                        }
                                    }
                                @endphp
                                <div style="width: 38px; height: 38px; border-radius: 10px; background: #0f172a; color: #60a5fa; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; overflow: hidden; flex-shrink: 0; border: 1px solid rgba(226, 232, 240, 0.8);">
                                    @if($logoUrl)
                                        <img src="{{ $logoUrl }}" alt="{{ $company->name }}" style="width: 100%; height: 100%; object-fit: cover;" />
                                    @else
                                        {{ strtoupper(substr($company->name, 0, 2)) }}
                                    @endif
                                </div>
                                <div>
                                    <strong style="color: var(--text-main); font-size: 13.5px;">{{ $company->name }}</strong>
                                    <div style="font-size: 11.5px; color: var(--text-subtle);">{{ $company->domain ?? ($company->subdomain . '.bbhpms.io') }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="text-align: left;"><code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 11.5px; color: #0284c7; font-weight: 700;">{{ $tenantId }}</code></td>
                        <td style="text-align: left;"><span class="plan-badge-cell plan-{{ $pClass }}">{{ $planName }}</span></td>
                        <td style="text-align: left;">
                            @php
                                $stClass = match($effStatus) {
                                    'active' => 'status-active',
                                    'trial' => 'status-trial',
                                    'suspended' => 'status-suspended',
                                    default => 'status-expiring',
                                };
                            @endphp
                            <span class="status-pill {{ $stClass }}"><span class="dot"></span> {{ $isExpired && $rawStatus !== 'suspended' ? 'Expired (Suspended)' : ucfirst($company->status ?? 'Active') }}</span>
                        </td>
                        <td style="text-align: left;"><span style="font-weight: 600; color: var(--text-muted);">{{ ucfirst($sub->billing_cycle ?? 'monthly') }}</span></td>
                        <td style="text-align: left;">
                            <div style="font-size: 12px; font-weight: 700; color: var(--text-main);">{{ $usersCount }} / {{ $userLimit }}</div>
                            <div style="width: 80px; height: 5px; background: #e2e8f0; border-radius: 3px; overflow: hidden; margin-top: 3px;">
                                <div style="height: 100%; width: {{ $userPct }}%; background: {{ $userPct >= 90 ? 'var(--danger)' : ($userPct >= 70 ? 'var(--warning)' : 'var(--primary)') }};"></div>
                            </div>
                        </td>
                        <td style="text-align: left;">
                            <div style="font-size: 12px; font-weight: 700; color: var(--text-main);">{{ $usedStorage }} GB / {{ $storageGb }} GB</div>
                            <div style="width: 80px; height: 5px; background: #e2e8f0; border-radius: 3px; overflow: hidden; margin-top: 3px;">
                                <div style="height: 100%; width: {{ $storagePct }}%; background: {{ $storagePct >= 90 ? 'var(--danger)' : ($storagePct >= 70 ? 'var(--warning)' : 'var(--success)') }};"></div>
                            </div>
                        </td>
                        <td style="text-align: left;">
                            <div style="font-size: 12px; font-weight: 700; color: {{ $isExpired ? 'var(--danger)' : 'var(--text-main)' }};">
                                {{ $endsAtFmt }}
                            </div>
                            <div style="font-size: 10.5px; color: var(--text-subtle); margin-top: 2px;">
                                <i class="fas fa-clock-rotate-left" style="font-size: 9.5px;"></i> Upgraded: {{ $startsAtFmt }}
                            </div>
                        </td>
                        <td style="text-align: right;"><strong style="color: var(--text-main);">{{ $prices[$planName] ?? '₹4,999' }}</strong></td>
                        <td style="text-align: right;">
                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 6px;">
                                <button class="btn-custom btn-primary-custom btn-xs-custom open-company-drawer" data-id="{{ $company->id }}" data-name="{{ $company->name }}" data-tenant="{{ $tenantId }}" data-plan="{{ $planName }}" data-status="{{ ucfirst($effStatus) }}" data-users="{{ $usersCount }}" data-userlimit="{{ $userLimit }}" data-storage="{{ $usedStorage }}" data-storagelimit="{{ $storageGb }}" data-domain="{{ $company->domain ?? ($company->subdomain . '.bbhpms.io') }}" data-logo="{{ $logoUrl }}" data-startsat="{{ $startsAtFmt }}" data-endsat="{{ $endsAtFmt }}">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                                <button class="btn-custom btn-outline-custom btn-xs-custom open-change-modal" data-id="{{ $company->id }}" data-name="{{ $company->name }}" data-currentplan="{{ $planName }}">
                                    Change Plan
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" style="text-align: center; padding: 40px; color: var(--text-subtle);">No tenant subscriptions registered</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- TAB 2: SUBSCRIPTION PLAN CARDS OVERVIEW -->
<!-- ============================================================ -->
<div class="tab-pane-content" id="tab-plans">
    <div class="plans-cards-grid">
        @foreach(['FREE', 'GOLD', 'PLATINUM', 'DIAMOND'] as $idx => $pName)
        @php
            $pClass = strtolower($pName);
            $planObj = $plans->first(fn($p) => strtoupper($p->name) === $pName);
            $mPrice = $planObj?->monthly_price ?? match($pName) { 'FREE' => 0, 'GOLD' => 4999, 'PLATINUM' => 9999, 'DIAMOND' => 19999 };
            $yPrice = $planObj?->yearly_price ?? ($mPrice * 10);
            $uLimit = $planObj?->max_users ?? match($pName) { 'FREE' => 5, 'GOLD' => 25, 'PLATINUM' => 100, 'DIAMOND' => 0 };
            $sGb = round(($planObj?->max_storage_mb ?? match($pName) { 'FREE' => 5120, 'GOLD' => 25600, 'PLATINUM' => 102400, 'DIAMOND' => 512000 }) / 1024);
            $subCount = $companies->filter(fn($c) => strtoupper($c->activeSubscription?->plan?->name ?? ($idx === 0 ? 'FREE' : '')) === $pName)->count();
            $enabledModCount = match($pName) { 'FREE' => 7, 'GOLD' => 16, 'PLATINUM' => 23, 'DIAMOND' => 28 };
        @endphp
        <div class="plan-card-item theme-{{ $pClass }}">
            @if($pName === 'GOLD')
                <div class="popular-badge-ribbon">Most Popular</div>
            @endif
            <div class="plan-icon-box">
                @if($pName === 'FREE') <i class="fas fa-tag"></i>
                @elseif($pName === 'GOLD') <i class="fas fa-crown"></i>
                @elseif($pName === 'PLATINUM') <i class="fas fa-gem"></i>
                @else <i class="fas fa-wand-magic-sparkles"></i> @endif
            </div>

            <div class="plan-name-title">{{ $pName }} Plan</div>
            <div class="plan-desc-text">{{ $planObj->description ?? 'Subscription tier providing tailored capabilities for organization scale.' }}</div>

            <div class="plan-price-wrap">
                <div class="plan-price-amount">₹{{ number_format($mPrice) }}</div>
                <div class="plan-price-cycle">per company / month (billed ₹{{ number_format($yPrice) }}/yr)</div>
            </div>

            <div class="plan-stats-row">
                <div class="plan-stats-item">
                    <span>Active Tenants</span>
                    <strong>{{ $subCount }} Companies</strong>
                </div>
                <div class="plan-stats-item" style="text-align: right;">
                    <span>Enabled Modules</span>
                    <strong>{{ $enabledModCount }} / 28 Modules</strong>
                </div>
            </div>

            <div class="plan-stats-row" style="margin-bottom: 20px;">
                <div class="plan-stats-item">
                    <span>User Limit</span>
                    <strong>{{ $uLimit > 0 ? $uLimit . ' Users' : 'Unlimited' }}</strong>
                </div>
                <div class="plan-stats-item" style="text-align: right;">
                    <span>Storage Limit</span>
                    <strong>{{ $sGb }} GB</strong>
                </div>
            </div>

            <ul class="plan-features-list">
                <li><i class="fas fa-check"></i> {{ $uLimit > 0 ? 'Up to ' . $uLimit . ' User Accounts' : 'Unlimited User Accounts' }}</li>
                <li><i class="fas fa-check"></i> {{ $sGb }} GB Dedicated Tenant Storage</li>
                <li><i class="fas fa-check"></i> Dedicated Multi-Tenant Database</li>
                @if($pName !== 'FREE') <li><i class="fas fa-check"></i> CRM Deals &amp; Sales Pipelines</li> @endif
                @if(in_array($pName, ['PLATINUM', 'DIAMOND'])) <li><i class="fas fa-check"></i> Full Payroll &amp; Tax Rules Engine</li> @endif
                @if($pName === 'DIAMOND') <li><i class="fas fa-check"></i> Priority Cluster &amp; Enterprise Support</li> @endif
            </ul>

            <div class="plan-actions-footer">
                <button type="button" class="btn-custom btn-outline-custom btn-xs-custom switch-to-matrix" data-plan="{{ $pClass }}" style="flex: 1;">
                    <i class="fas fa-table-cells"></i> View Features
                </button>
                <button type="button" class="btn-custom btn-primary-custom btn-xs-custom open-assign-for-plan" data-planid="{{ $planObj?->id ?? 1 }}" data-planname="{{ $pName }}" style="flex: 1;">
                    <i class="fas fa-user-plus"></i> Assign
                </button>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- ============================================================ -->
<!-- TAB 3: FEATURE ACCESS MATRIX (DYNAMIC PERMISSION MATRIX) -->
<!-- ============================================================ -->
<div class="tab-pane-content" id="tab-matrix">
    <div class="matrix-card-container">
        <div class="matrix-toolbar">
            <div>
                <strong style="font-size: 15px; color: var(--text-main);"><i class="fas fa-table-cells" style="color: var(--primary);"></i> Plan Feature Access Matrix</strong>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Control which modules and capabilities are available across subscription tiers.</div>
            </div>
            <div style="display: flex; gap: 8px;">
                <button class="btn-custom btn-outline-custom btn-xs-custom" id="expandAllCategoriesBtn"><i class="fas fa-angles-down"></i> Expand All</button>
                <button class="btn-custom btn-outline-custom btn-xs-custom" id="collapseAllCategoriesBtn"><i class="fas fa-angles-up"></i> Collapse All</button>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="matrix-table">
                <thead>
                    <tr>
                        <th>MODULE / FEATURE NAME</th>
                        <th>
                            <span class="plan-badge-cell plan-free"><i class="fas fa-tag"></i> FREE</span>
                            <div style="font-size: 10px; color: var(--text-subtle); margin-top: 4px;">₹0 / mo</div>
                        </th>
                        <th>
                            <span class="plan-badge-cell plan-gold"><i class="fas fa-crown"></i> GOLD</span>
                            <div style="font-size: 10px; color: var(--text-subtle); margin-top: 4px;">₹4,999 / mo</div>
                        </th>
                        <th>
                            <span class="plan-badge-cell plan-platinum"><i class="fas fa-gem"></i> PLATINUM</span>
                            <div style="font-size: 10px; color: var(--text-subtle); margin-top: 4px;">₹9,999 / mo</div>
                        </th>
                        <th>
                            <span class="plan-badge-cell plan-diamond"><i class="fas fa-wand-magic-sparkles"></i> DIAMOND</span>
                            <div style="font-size: 10px; color: var(--text-subtle); margin-top: 4px;">₹19,999 / mo</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $groupedModules = $modules->groupBy(fn($m) => $m->category ?? 'CORE PLATFORM');
                    @endphp

                    @foreach($groupedModules as $categoryName => $catModules)
                    <tr class="category-header-row" data-cat-target="cat-{{ Str::slug($categoryName) }}">
                        <td colspan="5">
                            <i class="fas fa-chevron-down" style="font-size: 11px; margin-right: 8px;"></i> {{ $categoryName }}
                            <span style="font-size: 11px; font-weight: 600; color: var(--text-muted); margin-left: 8px;">({{ $catModules->count() }} Features)</span>
                        </td>
                    </tr>

                    @foreach($catModules as $mod)
                    <tr class="feature-item-row cat-group-cat-{{ Str::slug($categoryName) }}">
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 30px; height: 30px; border-radius: 8px; background: #f1f5f9; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 14px;">
                                    <i class="bx {{ $mod->icon ?? 'bx-cube' }}"></i>
                                </div>
                                <div>
                                    <strong style="color: var(--text-main); font-size: 13.5px;">{{ $mod->name }}</strong>
                                    <div style="font-size: 11.5px; color: var(--text-subtle);">{{ $mod->description ?? 'Module functionality.' }}</div>
                                </div>
                            </div>
                        </td>

                        @foreach(['FREE', 'GOLD', 'PLATINUM', 'DIAMOND'] as $pIndex => $pName)
                        @php
                            $planObj = $plans->first(fn($p) => strtoupper($p->name) === $pName);
                            $planId = $planObj?->id ?? ($pIndex + 1);
                            
                            // Default availability rules
                            $isDefaultEnabled = match($pName) {
                                'FREE' => in_array($mod->slug, ['dashboard', 'notifications', 'organization', 'hr', 'employees', 'attendance', 'leave-management']),
                                'GOLD' => !in_array($mod->slug, ['payroll', 'analytics', 'advanced-reports']),
                                'PLATINUM' => true,
                                'DIAMOND' => true,
                            };
                            
                            $isEnabledInDb = false;
                            if ($planObj && method_exists($planObj, 'modules')) {
                                $isEnabledInDb = $planObj->modules->contains('id', $mod->id);
                            }
                            $checked = $isEnabledInDb || $isDefaultEnabled;
                        @endphp
                        <td>
                            <label class="toggle-switch-wrap">
                                <input type="checkbox" class="matrix-toggle-input" data-planid="{{ $planId }}" data-moduleid="{{ $mod->id }}" {{ $checked ? 'checked' : '' }} />
                                <span class="toggle-slider"></span>
                            </label>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- TAB 4: ROLE-BASED PERMISSIONS LAYER -->
<!-- ============================================================ -->
<div class="tab-pane-content" id="tab-roles">
    <div class="matrix-card-container">
        <div class="matrix-toolbar">
            <div>
                <strong style="font-size: 15px; color: var(--text-main);"><i class="fas fa-shield-quarter" style="color: var(--primary);"></i> Role-Based Module Permissions</strong>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                    Subscription determines module inclusion. Roles control actions inside enabled modules.
                </div>
            </div>
            <select id="roleSelector" style="padding: 8px 14px; border-radius: 10px; border: 1px solid var(--border-color); font-size: 13px; font-weight: 700; font-family: inherit;">
                <option value="Admin">Role: Admin (Full Access)</option>
                <option value="Manager">Role: Manager</option>
                <option value="HR">Role: HR Specialist</option>
                <option value="Employee" selected>Role: Employee (Standard)</option>
            </select>
        </div>

        <div style="overflow-x: auto;">
            <table class="matrix-table">
                <thead>
                    <tr>
                        <th style="text-align: left;">MODULE NAME</th>
                        <th>VIEW</th>
                        <th>CREATE</th>
                        <th>EDIT</th>
                        <th>DELETE</th>
                        <th>APPROVE</th>
                        <th>EXPORT</th>
                        <th>ASSIGN</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modules->take(12) as $mod)
                    @php
                        $includedInPlan = !in_array($mod->slug, ['payroll', 'analytics', 'advanced-reports']);
                    @endphp
                    <tr class="feature-item-row">
                        <td style="text-align: left;">
                            <strong style="color: var(--text-main);">{{ $mod->name }}</strong>
                            @if(!$includedInPlan)
                                <span style="display: block; font-size: 10.5px; color: var(--danger); font-weight: 700;">● Not included in plan</span>
                            @endif
                        </td>
                        @foreach(['VIEW', 'CREATE', 'EDIT', 'DELETE', 'APPROVE', 'EXPORT', 'ASSIGN'] as $actIdx => $act)
                        <td>
                            @if($includedInPlan)
                                <label class="toggle-switch-wrap">
                                    <input type="checkbox" {{ ($actIdx === 0 || ($actIdx < 3 && $mod->slug !== 'payroll')) ? 'checked' : '' }} />
                                    <span class="toggle-slider"></span>
                                </label>
                            @else
                                <span style="font-size: 11px; color: var(--text-subtle); font-weight: 600;">Disabled</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- TAB 5: PLAN COMPARISON -->
<!-- ============================================================ -->
<div class="tab-pane-content" id="tab-comparison">
    <div class="matrix-card-container">
        <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
            <h3 style="font-size: 18px; font-weight: 800; margin: 0; color: var(--text-main);">Plan Tier Capabilities Comparison</h3>
            <p style="font-size: 13px; color: var(--text-muted); margin: 4px 0 0 0;">Side-by-side entitlement comparison across FREE, GOLD, PLATINUM, and DIAMOND tiers.</p>
        </div>
        <div style="overflow-x: auto;">
            <table class="matrix-table" style="min-width: 800px;">
                <thead>
                    <tr>
                        <th style="text-align: left; width: 32%;">CAPABILITY / FEATURE</th>
                        <th>FREE (₹0)</th>
                        <th>GOLD (₹4,999)</th>
                        <th>PLATINUM (₹9,999)</th>
                        <th>DIAMOND (₹19,999)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td style="text-align: left;"><strong>Max User Accounts</strong></td><td>5 Users</td><td>25 Users</td><td>100 Users</td><td>Unlimited Users</td></tr>
                    <tr><td style="text-align: left;"><strong>Tenant Storage</strong></td><td>5 GB</td><td>25 GB</td><td>100 GB</td><td>500 GB</td></tr>
                    <tr><td style="text-align: left;"><strong>Core HR &amp; Attendance</strong></td><td><i class="fas fa-check" style="color: var(--success);"></i> Included</td><td><i class="fas fa-check" style="color: var(--success);"></i> Included</td><td><i class="fas fa-check" style="color: var(--success);"></i> Included</td><td><i class="fas fa-check" style="color: var(--success);"></i> Included</td></tr>
                    <tr><td style="text-align: left;"><strong>Projects &amp; Tasks</strong></td><td><i class="fas fa-xmark" style="color: var(--text-subtle);"></i> —</td><td><i class="fas fa-check" style="color: var(--success);"></i> Included</td><td><i class="fas fa-check" style="color: var(--success);"></i> Included</td><td><i class="fas fa-check" style="color: var(--success);"></i> Included</td></tr>
                    <tr><td style="text-align: left;"><strong>CRM Deals &amp; Pipelines</strong></td><td><i class="fas fa-xmark" style="color: var(--text-subtle);"></i> —</td><td><i class="fas fa-check" style="color: var(--success);"></i> Included</td><td><i class="fas fa-check" style="color: var(--success);"></i> Included</td><td><i class="fas fa-check" style="color: var(--success);"></i> Included</td></tr>
                    <tr><td style="text-align: left;"><strong>Payroll &amp; Tax Engine</strong></td><td><i class="fas fa-xmark" style="color: var(--text-subtle);"></i> —</td><td><i class="fas fa-xmark" style="color: var(--text-subtle);"></i> —</td><td><i class="fas fa-check" style="color: var(--success);"></i> Included</td><td><i class="fas fa-check" style="color: var(--success);"></i> Included</td></tr>
                    <tr><td style="text-align: left;"><strong>Advanced Intelligence Analytics</strong></td><td><i class="fas fa-xmark" style="color: var(--text-subtle);"></i> —</td><td><i class="fas fa-xmark" style="color: var(--text-subtle);"></i> —</td><td><i class="fas fa-check" style="color: var(--success);"></i> Included</td><td><i class="fas fa-check" style="color: var(--success);"></i> Included</td></tr>
                    <tr><td style="text-align: left;"><strong>Dedicated Cluster Priority</strong></td><td><i class="fas fa-xmark" style="color: var(--text-subtle);"></i> —</td><td><i class="fas fa-xmark" style="color: var(--text-subtle);"></i> —</td><td><i class="fas fa-xmark" style="color: var(--text-subtle);"></i> —</td><td><i class="fas fa-check" style="color: var(--success);"></i> Dedicated</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- TAB 6: COMPANY FEATURE OVERRIDES -->
<!-- ============================================================ -->
<!-- ============================================================ -->
<!-- TAB 6: COMPANY FEATURE OVERRIDES -->
<!-- ============================================================ -->
<div class="tab-pane-content" id="tab-overrides">
    <div class="matrix-card-container">
        <div style="padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 style="font-size: 18px; font-weight: 800; margin: 0; color: var(--text-main);">Company-Specific Feature Overrides</h3>
                <p style="font-size: 13px; color: var(--text-muted); margin: 4px 0 0 0;">Grant special enterprise custom module overrides to specific companies without modifying the base plan tier.</p>
            </div>
            <div id="overrideCompanyBanner" style="font-size: 13px; font-weight: 700; color: var(--primary); background: var(--primary-soft); padding: 8px 16px; border-radius: 20px; border: 1px solid var(--primary-ring);">
                <i class="fas fa-building" style="margin-right: 6px;"></i> <span id="overrideSelectedCompanyName">Select a company</span>
            </div>
        </div>
        <div style="padding: 20px;">
            <div style="margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="flex: 1; min-width: 280px; max-width: 440px;">
                    <label style="font-size: 12px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase;">Select Tenant Company:</label>
                    <select id="overrideCompanySelect" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border-color); font-size: 14px; font-weight: 700; font-family: inherit; margin-top: 4px; display: block; background: #fff;">
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} (Current Plan: {{ strtoupper($c->activeSubscription?->plan?->name ?? 'FREE') }})</option>
                        @endforeach
                    </select>
                </div>
                <div style="font-size: 12px; color: var(--text-muted); background: #f8fafc; padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border-color);">
                    <i class="fas fa-circle-info" style="color: var(--primary); margin-right: 6px;"></i> Toggle switches directly update feature access for the selected company in real-time.
                </div>
            </div>

            <table class="matrix-table" id="overrideMatrixTable">
                <thead>
                    <tr>
                        <th style="text-align: left;">MODULE / FEATURE NAME</th>
                        <th style="text-align: left;">CATEGORY</th>
                        <th style="text-align: left;">BASE PLAN INCLUSION</th>
                        <th style="text-align: center;">SUPER ADMIN OVERRIDE TOGGLE</th>
                        <th style="text-align: right;">OVERRIDE STATUS</th>
                    </tr>
                </thead>
                <tbody id="companyOverrideTableBody">
                    @php
                        $groupedModules = $modules->groupBy(fn($m) => $m->category ?? 'CORE PLATFORM');
                    @endphp
                    @foreach($groupedModules as $categoryName => $catModules)
                    <tr class="category-header-row" style="background: #f8fafc; font-weight: 700;">
                        <td colspan="5" style="padding: 10px 16px; font-size: 12px; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-layer-group" style="color: var(--primary); margin-right: 8px;"></i> {{ $categoryName }}
                            <span style="font-size: 11px; font-weight: 600; color: var(--text-muted); margin-left: 8px;">({{ $catModules->count() }} Features)</span>
                        </td>
                    </tr>
                    @foreach($catModules as $mod)
                    <tr class="feature-item-row override-module-row" data-moduleid="{{ $mod->id }}" data-moduleslug="{{ $mod->slug }}">
                        <td style="text-align: left;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 28px; height: 28px; border-radius: 6px; background: #f1f5f9; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                                    <i class="fas {{ $mod->icon ?? 'fa-cube' }}"></i>
                                </div>
                                <div>
                                    <strong style="color: var(--text-main); font-size: 13.5px;">{{ $mod->name }}</strong>
                                    <div style="font-size: 11.5px; color: var(--text-subtle);">{{ $mod->description ?? 'Module functionality' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="text-align: left;"><span style="font-size: 11px; font-weight: 600; color: var(--text-muted); background: #f1f5f9; padding: 2px 8px; border-radius: 4px;">{{ $mod->category ?? 'CORE' }}</span></td>
                        <td style="text-align: left;" class="base-plan-status-cell">
                            <span class="plan-badge-cell plan-free">Plan Default</span>
                        </td>
                        <td style="text-align: center;">
                            <label class="toggle-switch-wrap">
                                <input type="checkbox" class="company-override-input" data-moduleid="{{ $mod->id }}" />
                                <span class="toggle-slider"></span>
                            </label>
                        </td>
                        <td style="text-align: right;" class="override-status-cell">
                            <span style="font-size: 11px; font-weight: 700; color: var(--text-subtle); background: #f1f5f9; padding: 3px 8px; border-radius: 4px;">Plan Default</span>
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- TAB 7: AUDIT HISTORY TIMELINE -->
<!-- ============================================================ -->
<div class="tab-pane-content" id="tab-audit">
    <div class="matrix-card-container" style="padding: 24px;">
        <h3 style="font-size: 18px; font-weight: 800; margin: 0 0 16px 0; color: var(--text-main);">Subscription Audit Log Timeline</h3>
        <div style="display: flex; flex-direction: column; gap: 16px;">
            @forelse($auditLogs as $log)
            <div style="display: flex; gap: 14px; align-items: flex-start; padding-bottom: 14px; border-bottom: 1px solid var(--border-subtle);">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <strong style="color: var(--text-main); font-size: 14px;">{{ ucfirst(str_replace('.', ' ', $log->action)) }}</strong>
                    <div style="font-size: 12.5px; color: var(--text-muted); margin-top: 2px;">
                        Triggered by: Super Admin • IP: {{ $log->ip_address ?? '127.0.0.1' }}
                    </div>
                    <div style="font-size: 11px; color: var(--text-subtle); margin-top: 4px;">
                        {{ $log->created_at?->format('M d, Y · H:i A') ?? now()->format('M d, Y · H:i A') }}
                    </div>
                </div>
            </div>
            @empty
            <div style="color: var(--text-subtle); padding: 20px 0;">No recent subscription audit events logged.</div>
            @endforelse
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- SUBSCRIPTION COMMAND CENTER DRAWER (PREMIUM DESIGN) -->
<!-- ============================================================ -->
<div class="drawer-overlay-custom" id="companyDetailDrawer">
    <div class="drawer-panel-custom">
        <!-- DRAWER HEADER -->
        <div class="drawer-header">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div id="drawerCompanyLogoBox" style="width: 48px; height: 48px; border-radius: 14px; background: #0f172a; color: #60a5fa; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                    CO
                </div>
                <div>
                    <h3 style="font-size: 20px; font-weight: 800; margin: 0; color: var(--text-main);" id="drawerCompanyName">Company Name</h3>
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                        <code style="font-family: monospace; font-size: 11.5px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 4px;" id="drawerTenantId">#TEN-0001</code>
                        <span style="font-size: 12px; color: var(--text-muted);" id="drawerCompanyDomain">company.bbhpms.io</span>
                    </div>
                </div>
            </div>
            <button class="btn-custom btn-ghost-custom btn-xs-custom" id="closeDrawerBtn" style="font-size: 22px;">&times;</button>
        </div>

        <div class="drawer-body">
            <!-- HEALTH & EXPIRATION COUNTDOWN BANNER (LUXURY EMERALD SLATE THEMING) -->
            <div style="background: linear-gradient(135deg, #073a26 0%, #0f744c 100%); padding: 20px; border-radius: var(--radius-lg); color: #ffffff; box-shadow: 0 12px 32px rgba(15, 116, 76, 0.22); border: 1px solid rgba(255, 255, 255, 0.18);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="plan-badge-cell plan-gold" id="drawerPlanBadge" style="font-size: 12px; padding: 5px 12px; font-weight: 800;">GOLD</span>
                        <span class="status-pill status-active" id="drawerStatusPill"><span class="dot"></span> Active</span>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.9px; color: #a7f3d0;">COUNTDOWN</div>
                        <div style="font-size: 22px; font-weight: 900; color: #34d399; margin-top: 2px;" id="drawerCountdownText">29 DAYS LEFT</div>
                    </div>
                </div>

                <!-- TIMELINE DATA TABLE FORMAT -->
                <table style="width: 100%; border-collapse: collapse; background: rgba(0, 0, 0, 0.25); border-radius: 10px; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.12);">
                    <tbody>
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.12);">
                            <td style="padding: 8px 12px; font-size: 11.5px; color: #cbd5e1; font-weight: 600;">
                                <i class="fas fa-clock-rotate-left" style="color: #38bdf8; margin-right: 6px;"></i> Assigned Date &amp; Time
                            </td>
                            <td style="padding: 8px 12px; font-size: 12px; font-weight: 700; color: #ffffff; text-align: right;" id="drawerAssignedTimeCell">
                                Aug 13, 2026 12:00 AM
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 12px; font-size: 11.5px; color: #cbd5e1; font-weight: 600;">
                                <i class="fas fa-calendar-xmark" style="color: #fb7185; margin-right: 6px;"></i> Expiration Date &amp; Time
                            </td>
                            <td style="padding: 8px 12px; font-size: 12px; font-weight: 700; color: #fecdd3; text-align: right;" id="drawerExpiryTimeCell">
                                Sep 12, 2026 12:00 AM
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- COMPACT SUBSCRIPTION OVERVIEW CARDS -->
            <div class="drawer-card-box">
                <div class="box-title">
                    <span><i class="fas fa-circle-info" style="color: var(--primary);"></i> Subscription Overview</span>
                    <span style="font-size: 11px; color: var(--success); font-weight: 700;"><i class="fas fa-shield-check"></i> Verified Status</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                    <div style="background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px solid var(--border-subtle);">
                        <div style="font-size: 10.5px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Billing Cycle</div>
                        <strong style="font-size: 13.5px; color: var(--text-main);" id="drawerCycleText">Monthly Auto-Renew</strong>
                    </div>
                    <div style="background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px solid var(--border-subtle);">
                        <div style="font-size: 10.5px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Payment Status</div>
                        <strong style="font-size: 13.5px; color: var(--success);"><i class="fas fa-circle-check"></i> Paid &amp; Current</strong>
                    </div>
                    <div style="background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px solid var(--border-subtle);">
                        <div style="font-size: 10.5px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Monthly Price</div>
                        <strong style="font-size: 15px; color: var(--text-main);" id="drawerPriceText">₹4,999 / mo</strong>
                    </div>
                    <div style="background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px solid var(--border-subtle);">
                        <div style="font-size: 10.5px; color: var(--text-subtle); font-weight: 700; text-transform: uppercase;">Annual Price</div>
                        <strong style="font-size: 15px; color: var(--text-main);">₹49,990 / yr</strong>
                    </div>
                </div>
            </div>

            <!-- USAGE & LIMITS HEALTH -->
            <div class="drawer-card-box">
                <div class="box-title">
                    <span><i class="fas fa-chart-pie" style="color: var(--primary);"></i> Capacity &amp; Resource Usage</span>
                </div>
                
                <div style="margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 4px;">
                        <span style="font-weight: 600; color: var(--text-main);">User Accounts Allocation</span>
                        <strong id="drawerUserUsageText">18 / 25 Users</strong>
                    </div>
                    <div style="height: 6px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                        <div style="height: 100%; width: 72%; background: var(--primary);"></div>
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 4px;">
                        <span style="font-weight: 600; color: var(--text-main);">Tenant Storage Allocation</span>
                        <strong id="drawerStorageUsageText">18 GB / 25 GB</strong>
                    </div>
                    <div style="height: 6px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                        <div style="height: 100%; width: 72%; background: var(--success);"></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 4px;">
                        <span style="font-weight: 600; color: var(--text-main);">Active Projects Allocation</span>
                        <strong>18 / 50 Projects</strong>
                    </div>
                    <div style="height: 6px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                        <div style="height: 100%; width: 36%; background: #0284c7;"></div>
                    </div>
                </div>
            </div>

            <!-- FEATURE ACCESS LIST -->
            <div class="drawer-card-box">
                <div class="box-title">
                    <span><i class="fas fa-lock-open" style="color: var(--primary);"></i> Entitlement &amp; Feature Access</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border-subtle);">
                        <span><i class="fas fa-check" style="color: var(--success); margin-right: 8px;"></i> HR &amp; Employee Database</span>
                        <span style="font-size: 11px; font-weight: 700; color: var(--text-subtle);">Plan Default</span>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border-subtle);">
                        <span><i class="fas fa-check" style="color: var(--success); margin-right: 8px;"></i> Attendance &amp; Geolocation Clock-in</span>
                        <span style="font-size: 11px; font-weight: 700; color: var(--text-subtle);">Plan Default</span>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border-subtle);">
                        <span><i class="fas fa-check" style="color: var(--success); margin-right: 8px;"></i> CRM Deals &amp; Sales Pipelines</span>
                        <span style="font-size: 11px; font-weight: 700; color: var(--text-subtle);">Plan Default</span>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border-subtle);">
                        <span><i class="fas fa-bolt" style="color: #d97706; margin-right: 8px;"></i> Payroll Rules &amp; Payslips</span>
                        <span style="font-size: 10.5px; font-weight: 800; color: #d97706; background: #fffbeb; padding: 2px 6px; border-radius: 4px;">⚡ Custom Override</span>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 0;">
                        <span style="color: var(--text-subtle);"><i class="fas fa-lock" style="margin-right: 8px;"></i> Advanced Intelligence Analytics</span>
                        <span style="font-size: 11px; font-weight: 700; color: var(--danger);">🔒 Restricted</span>
                    </div>
                </div>
            </div>

            <!-- VERTICAL SUBSCRIPTION TIMELINE -->
            <div class="drawer-card-box">
                <div class="box-title">
                    <span><i class="fas fa-history" style="color: var(--primary);"></i> Subscription History</span>
                </div>
                <div style="position: relative; padding-left: 18px; border-left: 2px solid var(--border-color); display: flex; flex-direction: column; gap: 16px;">
                    <div style="position: relative;">
                        <div style="position: absolute; left: -24px; top: 2px; width: 10px; height: 10px; border-radius: 50%; background: var(--success);"></div>
                        <strong style="font-size: 13px; color: var(--text-main);">Subscription Active &amp; Verified</strong>
                        <div style="font-size: 11.5px; color: var(--text-muted);">Aug 12, 2026 · 10:30 AM</div>
                    </div>
                    <div style="position: relative;">
                        <div style="position: absolute; left: -24px; top: 2px; width: 10px; height: 10px; border-radius: 50%; background: var(--primary);"></div>
                        <strong style="font-size: 13px; color: var(--text-main);">Plan Changed (FREE → GOLD)</strong>
                        <div style="font-size: 11.5px; color: var(--text-muted);">Jul 01, 2026 · 14:15 PM</div>
                    </div>
                    <div style="position: relative;">
                        <div style="position: absolute; left: -24px; top: 2px; width: 10px; height: 10px; border-radius: 50%; background: #94a3b8;"></div>
                        <strong style="font-size: 13px; color: var(--text-main);">Tenant Company Provisioned</strong>
                        <div style="font-size: 11.5px; color: var(--text-muted);">Jun 01, 2026 · 09:00 AM</div>
                    </div>
                </div>
            </div>

            <!-- ACTION CENTER BUTTONS -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 8px;">
                <button class="btn-custom btn-primary-custom" id="drawerChangePlanTrigger">
                    <i class="fas fa-arrow-right-arrow-left"></i> Change Plan
                </button>
                <button class="btn-custom btn-outline-custom" id="drawerExtendSubBtn">
                    <i class="fas fa-calendar-plus"></i> Extend Subscription
                </button>
                <button class="btn-custom btn-outline-custom" id="drawerReduceSubBtn">
                    <i class="fas fa-calendar-minus"></i> Reduce Plan
                </button>
                <button class="btn-custom btn-outline-custom" id="drawerManageFeaturesBtn">
                    <i class="fas fa-sliders"></i> Manage Features
                </button>
                <button class="btn-custom btn-primary-custom" id="drawerActivateTrigger" style="background: var(--success); border-color: var(--success); display: none;">
                    <i class="fas fa-circle-check"></i> Activate Access
                </button>
                <button class="btn-custom btn-outline-custom" id="drawerSuspendTrigger" style="color: var(--danger); border-color: var(--danger-border);">
                    <i class="fas fa-ban"></i> Suspend Access
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- ASSIGN / CHANGE PLAN MODAL (WITH COMPARISON DIFF) -->
<!-- ============================================================ -->
<div class="modal-backdrop-custom" id="assignPlanModal">
    <div class="modal-dialog-custom">
        <h3 style="font-size: 20px; font-weight: 800; margin: 0 0 6px 0; color: var(--text-main);">Assign / Change Subscription Plan</h3>
        <p style="font-size: 13.5px; color: var(--text-muted); margin: 0 0 20px 0;">Select a target company and choose a subscription plan tier.</p>

        <form id="assignPlanForm">
            @csrf
            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase;">Select Tenant Company:</label>
                <select name="company_id" id="modalCompanySelect" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border-color); font-size: 14px; font-weight: 700; font-family: inherit; margin-top: 4px;">
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} (Current: {{ strtoupper($c->activeSubscription?->plan?->name ?? 'FREE') }})</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase;">Select Target Plan Tier:</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 6px;">
                    @foreach(['FREE', 'GOLD', 'PLATINUM', 'DIAMOND'] as $pIdx => $pName)
                    @php
                        $planObj = $plans->first(fn($p) => strtoupper($p->name) === $pName);
                        $pId = $planObj?->id ?? $pName;
                        $pPrice = $planObj?->monthly_price ?? match($pName) { 'FREE' => 0, 'GOLD' => 4999, 'PLATINUM' => 9999, 'DIAMOND' => 19999 };
                    @endphp
                    <label style="padding: 14px; border: 2px solid var(--border-color); border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: space-between;" class="plan-card-option">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="radio" name="plan_id" value="{{ $pId }}" data-planname="{{ $pName }}" {{ $pName === 'GOLD' ? 'checked' : '' }} />
                            <span class="plan-badge-cell plan-{{ strtolower($pName) }}">{{ $pName }}</span>
                        </div>
                        <strong style="font-size: 13.5px;">₹{{ number_format($pPrice) }}/mo</strong>
                    </label>
                    @endforeach
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border-color); padding-top: 16px;">
                <button type="button" class="btn-custom btn-outline-custom" id="closeAssignModalBtn">Cancel</button>
                <button type="submit" class="btn-custom btn-primary-custom" id="submitAssignPlanBtn">Confirm &amp; Assign Plan</button>
            </div>
        </form>
    </div>
</div>

<!-- CONFIRMATION MODAL FOR SUSPENSION -->
<div class="modal-backdrop-custom" id="suspendConfirmModal">
    <div class="modal-dialog-custom" style="max-width: 460px;">
        <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--danger-bg); color: var(--danger); display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 14px;">
            <i class="fas fa-triangle-exclamation"></i>
        </div>
        <h3 style="font-size: 18px; font-weight: 800; margin: 0 0 8px 0; color: var(--text-main);">Suspend Tenant Subscription?</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin: 0 0 20px 0;">This will immediately restrict the company's access to subscription-based features and modules. Are you sure you want to proceed?</p>
        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button class="btn-custom btn-outline-custom" id="closeSuspendModalBtn">Cancel</button>
            <button class="btn-custom btn-primary-custom" style="background: var(--danger); border-color: var(--danger);" id="confirmSuspendActionBtn">Confirm Suspension</button>
        </div>
    </div>
</div>

<!-- TOAST NOTIFICATION CONTAINER -->
<div class="toast-container" id="toastContainer"></div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Workspace Tab Switching
    const tabButtons = document.querySelectorAll('.nav-tab-item');
    const tabPanes = document.querySelectorAll('.tab-pane-content');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-tab');
            tabButtons.forEach(b => b.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));

            this.classList.add('active');
            const targetPane = document.getElementById(targetId);
            if (targetPane) targetPane.classList.add('active');
        });
    });

    // 2. Switch to Matrix Tab from Plan Card View Features
    document.querySelectorAll('.switch-to-matrix').forEach(btn => {
        btn.addEventListener('click', function() {
            const matrixTab = document.querySelector('[data-tab="tab-matrix"]');
            if (matrixTab) matrixTab.click();
        });
    });

    // 3. Category Collapse / Expand in Matrix
    document.querySelectorAll('.category-header-row').forEach(row => {
        row.addEventListener('click', function() {
            const targetCat = this.getAttribute('data-cat-target');
            const childRows = document.querySelectorAll('.cat-group-' + targetCat);
            const icon = this.querySelector('i');

            childRows.forEach(r => {
                r.style.display = (r.style.display === 'none') ? '' : 'none';
            });
            if (icon) {
                icon.classList.toggle('fa-chevron-down');
                icon.classList.toggle('fa-chevron-right');
            }
        });
    });

    const expandBtn = document.getElementById('expandAllCategoriesBtn');
    const collapseBtn = document.getElementById('collapseAllCategoriesBtn');
    if (expandBtn) {
        expandBtn.addEventListener('click', () => {
            document.querySelectorAll('.feature-item-row').forEach(r => r.style.display = '');
        });
    }
    if (collapseBtn) {
        collapseBtn.addEventListener('click', () => {
            document.querySelectorAll('.feature-item-row').forEach(r => r.style.display = 'none');
        });
    }

    // 4. Feature Matrix AJAX Toggle
    document.querySelectorAll('.matrix-toggle-input').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const planId = this.getAttribute('data-planid');
            const moduleId = this.getAttribute('data-moduleid');
            const enabled = this.checked ? 1 : 0;

            fetch("{{ Route::has('super-admin.plans.toggle-module') ? route('super-admin.plans.toggle-module') : route('superadmin.plans.toggle-module') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ plan_id: planId, module_id: moduleId, enabled: enabled })
            })
            .then(res => res.json())
            .then(data => {
                showToast(data.message || 'Plan feature access updated successfully.');
            })
            .catch(() => showToast('Feature access updated.'));
        });
    });

    // 5. Drawer Handling
    const drawer = document.getElementById('companyDetailDrawer');
    const closeDrawerBtn = document.getElementById('closeDrawerBtn');

    document.querySelectorAll('.open-company-drawer').forEach(btn => {
        btn.addEventListener('click', function() {
            const compId = this.getAttribute('data-id');
            const compName = this.getAttribute('data-name');
            const tenantId = this.getAttribute('data-tenant');
            const plan = (this.getAttribute('data-plan') || 'FREE').toUpperCase();
            const domain = this.getAttribute('data-domain');
            const users = parseFloat(this.getAttribute('data-users') || 0);
            const userLimit = parseFloat(this.getAttribute('data-userlimit') || 100);
            const storage = parseFloat(this.getAttribute('data-storage') || 0);
            const storageLimit = parseFloat(this.getAttribute('data-storagelimit') || 10);
            const logoUrl = this.getAttribute('data-logo');

            document.getElementById('drawerCompanyName').innerText = compName;
            document.getElementById('drawerTenantId').innerText = tenantId;
            document.getElementById('drawerCompanyDomain').innerText = domain;
            
            // Plan badge styling & text
            const planBadge = document.getElementById('drawerPlanBadge');
            if (planBadge) {
                const pClass = plan.toLowerCase();
                planBadge.className = 'plan-badge-cell plan-' + (['free','gold','platinum','diamond'].includes(pClass) ? pClass : 'free');
                planBadge.innerText = plan;
                planBadge.style.fontSize = '11.5px';
                planBadge.style.padding = '5px 12px';
            }

            // Price mapping matching Plan Tier
            const prices = {
                'FREE': { m: '₹0 / mo', a: '₹0 / yr' },
                'GOLD': { m: '₹4,999 / mo', a: '₹49,990 / yr' },
                'PLATINUM': { m: '₹9,999 / mo', a: '₹99,990 / yr' },
                'DIAMOND': { m: '₹19,999 / mo', a: '₹1,99,990 / yr' }
            };
            const pInfo = prices[plan] || prices['FREE'];
            const priceEl = document.getElementById('drawerPriceText');
            if (priceEl) priceEl.innerText = pInfo.m;
            const annualPriceEl = document.getElementById('drawerAnnualPriceText');
            if (annualPriceEl) annualPriceEl.innerText = pInfo.a;

            // Usage & Progress Bars
            document.getElementById('drawerUserUsageText').innerText = users + ' / ' + userLimit + ' Users';
            document.getElementById('drawerStorageUsageText').innerText = storage + ' GB / ' + storageLimit + ' GB';
            
            const userPct = Math.min(100, Math.round((users / Math.max(1, userLimit)) * 100));
            const userBar = document.getElementById('drawerUserProgressBar');
            if (userBar) userBar.style.width = userPct + '%';

            const storagePct = Math.min(100, Math.round((storage / Math.max(1, storageLimit)) * 100));
            const storageBar = document.getElementById('drawerStorageProgressBar');
            if (storageBar) storageBar.style.width = storagePct + '%';

            // Logo image handling
            const logoBox = document.getElementById('drawerCompanyLogoBox');
            if (logoBox) {
                if (logoUrl && logoUrl.trim() !== '') {
                    logoBox.innerHTML = '<img src="' + logoUrl + '" alt="' + compName + '" style="width: 100%; height: 100%; object-fit: cover;" />';
                } else {
                    logoBox.innerText = compName.substring(0, 2).toUpperCase();
                }
            }

            // Timestamps table cells handling
            const startsAtStr = this.getAttribute('data-startsat') || 'N/A';
            const endsAtStr = this.getAttribute('data-endsat') || 'N/A';

            const assignedCell = document.getElementById('drawerAssignedTimeCell');
            if (assignedCell) assignedCell.innerText = startsAtStr;

            const expiryCell = document.getElementById('drawerExpiryTimeCell');
            if (expiryCell) expiryCell.innerText = endsAtStr;

            // Countdown calculation
            const countdownEl = document.getElementById('drawerCountdownText');
            if (countdownEl && endsAtStr !== 'N/A') {
                const expDate = new Date(endsAtStr);
                const now = new Date();
                const diffMs = expDate - now;

                if (diffMs <= 0) {
                    countdownEl.innerText = 'EXPIRED';
                    countdownEl.style.color = '#ef4444';
                } else {
                    const daysLeft = Math.floor(diffMs / (1000 * 60 * 60 * 24));
                    const hoursLeft = Math.floor((diffMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    countdownEl.innerText = daysLeft + 'd ' + hoursLeft + 'h LEFT';
                    countdownEl.style.color = daysLeft <= 5 ? '#f59e0b' : '#34d399';
                }
            }

            // Attach data attributes & update status pill inside drawer hero banner
            const statusStr = (this.getAttribute('data-status') || 'Active').toLowerCase();
            const drawerStatusPill = document.getElementById('drawerStatusPill');

            if (drawerStatusPill) {
                if (statusStr === 'suspended') {
                    drawerStatusPill.className = 'status-pill status-suspended';
                    drawerStatusPill.innerHTML = '<span class="dot"></span> Suspended';
                } else if (statusStr === 'trial') {
                    drawerStatusPill.className = 'status-pill status-trial';
                    drawerStatusPill.innerHTML = '<span class="dot"></span> Trial';
                } else {
                    drawerStatusPill.className = 'status-pill status-active';
                    drawerStatusPill.innerHTML = '<span class="dot"></span> Active';
                }
            }

            const changeTrigger = document.getElementById('drawerChangePlanTrigger');
            if (changeTrigger) {
                changeTrigger.setAttribute('data-id', compId);
                changeTrigger.setAttribute('data-currentplan', plan);
                changeTrigger.setAttribute('data-status', statusStr);
            }

            const manageFeaturesBtn = document.getElementById('drawerManageFeaturesBtn');
            if (manageFeaturesBtn) {
                manageFeaturesBtn.setAttribute('data-companyid', compId);
            }

            const suspendTrigger = document.getElementById('drawerSuspendTrigger');
            const activateTrigger = document.getElementById('drawerActivateTrigger');

            if (statusStr === 'suspended') {
                if (suspendTrigger) suspendTrigger.style.display = 'none';
                if (activateTrigger) {
                    activateTrigger.style.display = 'inline-flex';
                    activateTrigger.setAttribute('data-id', compId);
                }

                const featuresContainer = document.getElementById('drawerFeaturesContainer');
                if (featuresContainer) {
                    featuresContainer.innerHTML = '<div style="background: #fef2f2; color: #991b1b; padding: 12px; border-radius: 8px; border: 1px solid #fecaca; font-weight: 600; text-align: center; font-size: 12px;"><i class="fas fa-lock" style="margin-right: 6px;"></i> All module and feature access is LOCKED while tenant is SUSPENDED. Activate to restore access.</div>';
                }
            } else {
                if (suspendTrigger) {
                    suspendTrigger.style.display = 'inline-flex';
                    suspendTrigger.setAttribute('data-id', compId);
                }
                if (activateTrigger) activateTrigger.style.display = 'none';
            }

            if (drawer) drawer.classList.add('open');
        });
    });

    if (closeDrawerBtn && drawer) {
        closeDrawerBtn.addEventListener('click', () => drawer.classList.remove('open'));
    }

    // 6. Suspension Modal & Activation Handling
    const suspendModal = document.getElementById('suspendConfirmModal');
    const suspendTrigger = document.getElementById('drawerSuspendTrigger');
    const closeSuspendBtn = document.getElementById('closeSuspendModalBtn');
    const confirmSuspendBtn = document.getElementById('confirmSuspendActionBtn');

    if (suspendTrigger && suspendModal) {
        suspendTrigger.addEventListener('click', function() {
            const compId = this.getAttribute('data-id');
            if (confirmSuspendBtn && compId) {
                confirmSuspendBtn.setAttribute('data-id', compId);
            }
            suspendModal.classList.add('open');
        });
    }

    if (closeSuspendBtn && suspendModal) {
        closeSuspendBtn.addEventListener('click', () => suspendModal.classList.remove('open'));
    }

    if (confirmSuspendBtn && suspendModal) {
        confirmSuspendBtn.addEventListener('click', function() {
            const compId = this.getAttribute('data-id');
            if (!compId) {
                suspendModal.classList.remove('open');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Suspending...';

            const url = "/super-admin/companies/" + compId + "/suspend";

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                this.innerHTML = 'Confirm Suspension';
                suspendModal.classList.remove('open');
                if (drawer) drawer.classList.remove('open');

                showToast(data.message || 'Subscription access suspended successfully.');

                const row = document.querySelector('tr[data-company-id="' + compId + '"]') || document.querySelector('input.row-sub-cb[value="' + compId + '"]')?.closest('tr');
                if (row) {
                    row.setAttribute('data-status', 'suspended');
                    const statusPill = row.querySelector('.status-pill');
                    if (statusPill) {
                        statusPill.className = 'status-pill status-suspended';
                        statusPill.innerHTML = '<span class="dot"></span> Suspended';
                    }
                }

                setTimeout(() => window.location.reload(), 500);
            })
            .catch(() => {
                this.disabled = false;
                this.innerHTML = 'Confirm Suspension';
                suspendModal.classList.remove('open');
                if (drawer) drawer.classList.remove('open');
                showToast('Subscription access suspended.');
                setTimeout(() => window.location.reload(), 500);
            });
        });
    }

    // Activate Tenant Company Access
    const activateTrigger = document.getElementById('drawerActivateTrigger');
    if (activateTrigger) {
        activateTrigger.addEventListener('click', function() {
            const compId = this.getAttribute('data-id');
            if (!compId) return;

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Activating...';

            fetch("/super-admin/companies/" + compId + "/activate", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-circle-check"></i> Activate Access';
                if (drawer) drawer.classList.remove('open');

                showToast(data.message || 'Tenant company activated! Feature access restored.');

                const row = document.querySelector('tr[data-company-id="' + compId + '"]') || document.querySelector('input.row-sub-cb[value="' + compId + '"]')?.closest('tr');
                if (row) {
                    row.setAttribute('data-status', 'active');
                    const statusPill = row.querySelector('.status-pill');
                    if (statusPill) {
                        statusPill.className = 'status-pill status-active';
                        statusPill.innerHTML = '<span class="dot"></span> Active';
                    }
                }

                setTimeout(() => window.location.reload(), 500);
            })
            .catch(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-circle-check"></i> Activate Access';
                if (drawer) drawer.classList.remove('open');
                showToast('Tenant company activated.');
                setTimeout(() => window.location.reload(), 500);
            });
        });
    }

    // Extend Subscription Access by 30 Days (Blocked if Suspended)
    const extendSubBtn = document.getElementById('drawerExtendSubBtn');
    if (extendSubBtn) {
        extendSubBtn.addEventListener('click', function() {
            const changeTrigger = document.getElementById('drawerChangePlanTrigger');
            const compId = changeTrigger ? changeTrigger.getAttribute('data-id') : null;
            const status = changeTrigger ? changeTrigger.getAttribute('data-status') : null;

            if (status === 'suspended') {
                showToast('Cannot extend subscription for a SUSPENDED company. Please activate the company first.', 'danger');
                return;
            }

            if (!compId) return;

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Extending...';

            fetch("/super-admin/subscriptions/" + compId + "/extend", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ days: 30 })
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-calendar-plus"></i> Extend Subscription';
                if (!data.success) {
                    showToast(data.message || 'Error extending subscription', 'danger');
                    return;
                }

                if (drawer) drawer.classList.remove('open');
                showToast(data.message || 'Subscription extended by 30 days!');
                setTimeout(() => window.location.reload(), 500);
            })
            .catch(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-calendar-plus"></i> Extend Subscription';
                if (drawer) drawer.classList.remove('open');
                showToast('Subscription extended.');
                setTimeout(() => window.location.reload(), 500);
            });
        });
    }

    // Reduce Subscription Duration by 7 Days (Blocked if Suspended)
    const reduceSubBtn = document.getElementById('drawerReduceSubBtn');
    if (reduceSubBtn) {
        reduceSubBtn.addEventListener('click', function() {
            const changeTrigger = document.getElementById('drawerChangePlanTrigger');
            const compId = changeTrigger ? changeTrigger.getAttribute('data-id') : null;
            const status = changeTrigger ? changeTrigger.getAttribute('data-status') : null;

            if (status === 'suspended') {
                showToast('Cannot reduce subscription for a SUSPENDED company. Please activate the company first.', 'danger');
                return;
            }

            if (!compId) return;

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Reducing...';

            fetch("/super-admin/subscriptions/" + compId + "/reduce", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ days: 7 })
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-calendar-minus"></i> Reduce Plan';
                if (!data.success) {
                    showToast(data.message || 'Error reducing subscription', 'danger');
                    return;
                }

                if (drawer) drawer.classList.remove('open');
                showToast(data.message || 'Subscription duration reduced by 7 days!');
                setTimeout(() => window.location.reload(), 500);
            })
            .catch(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-calendar-minus"></i> Reduce Plan';
                if (drawer) drawer.classList.remove('open');
                showToast('Subscription duration reduced.');
                setTimeout(() => window.location.reload(), 500);
            });
        });
    }

    // 7. Assign / Change Plan Modal
    const modal = document.getElementById('assignPlanModal');
    const openModalBtn = document.getElementById('openAssignModalBtn');
    const closeModalBtn = document.getElementById('closeAssignModalBtn');
    const form = document.getElementById('assignPlanForm');

    if (openModalBtn && modal) openModalBtn.addEventListener('click', () => modal.classList.add('open'));
    if (closeModalBtn && modal) closeModalBtn.addEventListener('click', () => modal.classList.remove('open'));

    document.querySelectorAll('.open-change-modal, #drawerChangePlanTrigger').forEach(btn => {
        btn.addEventListener('click', function() {
            const compId = this.getAttribute('data-id');
            const row = document.querySelector('tr[data-company-id="' + compId + '"]');
            const status = row ? row.getAttribute('data-status') : this.getAttribute('data-status');

            if (status === 'suspended') {
                showToast('Cannot change subscription plan for a SUSPENDED company. Please activate the company first.', 'danger');
                return;
            }

            const currentPlan = (this.getAttribute('data-currentplan') || this.getAttribute('data-plan') || 'GOLD').toUpperCase();
            
            const compSelect = document.getElementById('modalCompanySelect');
            if (compSelect && compId) compSelect.value = compId;

            // Pre-select matching plan radio button in modal
            const targetRadio = document.querySelector(`input[name="plan_id"][data-planname="${currentPlan}"]`) || document.querySelector(`input[name="plan_id"][value="${currentPlan}"]`);
            if (targetRadio) {
                targetRadio.checked = true;
            }

            if (drawer) drawer.classList.remove('open');
            if (modal) modal.classList.add('open');
        });
    });

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('submitAssignPlanBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating Plan...';
            }

            const formData = new FormData(this);

            fetch("{{ Route::has('super-admin.subscriptions.assign') ? route('super-admin.subscriptions.assign') : (Route::has('superadmin.subscriptions.assign') ? route('superadmin.subscriptions.assign') : url('/super-admin/subscriptions/assign')) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Confirm &amp; Assign Plan';
                }
                if (modal) modal.classList.remove('open');

                if (data.success) {
                    showToast(data.message || 'Subscription plan assigned successfully.');

                    // Update row in table DOM immediately
                    if (data.company_id && data.plan_name) {
                        const row = document.querySelector(`tr[data-company-id="${data.company_id}"]`) || document.querySelector(`input.row-sub-cb[value="${data.company_id}"]`)?.closest('tr');
                        if (row) {
                            row.setAttribute('data-plan', data.plan_class);
                            const badgeCell = row.querySelector('.plan-badge-cell');
                            if (badgeCell) {
                                badgeCell.className = 'plan-badge-cell plan-' + data.plan_class;
                                badgeCell.innerText = data.plan_name;
                            }
                            const changeBtn = row.querySelector('.open-change-modal');
                            if (changeBtn) {
                                changeBtn.setAttribute('data-currentplan', data.plan_name);
                            }
                        }
                    }

                    setTimeout(() => window.location.reload(), 500);
                } else {
                    showToast(data.message || 'Could not assign subscription plan.', 'danger');
                }
            })
            .catch(err => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Confirm &amp; Assign Plan';
                }
                if (modal) modal.classList.remove('open');
                showToast('Plan update submitted.');
                setTimeout(() => window.location.reload(), 500);
            });
        });
    }

    // 8. Search & Filtering in Table
    const searchInput = document.getElementById('searchSubsInput');
    const statusFilter = document.getElementById('filterStatus');
    const planFilter = document.getElementById('filterPlan');
    const resetBtn = document.getElementById('resetFiltersBtn');

    function applyTableFilters() {
        const query = (searchInput?.value || '').toLowerCase().trim();
        const statusVal = (statusFilter?.value || '').toLowerCase();
        const planVal = (planFilter?.value || '').toLowerCase();

        document.querySelectorAll('#subsCompanyTable tbody tr.feature-item-row').forEach(row => {
            const text = row.textContent.toLowerCase();
            const rowStatus = row.getAttribute('data-status') || '';
            const rowPlan = row.getAttribute('data-plan') || '';

            const matchesSearch = query === '' || text.includes(query);
            const matchesStatus = statusVal === '' || rowStatus === statusVal;
            const matchesPlan = planVal === '' || rowPlan === planVal;

            row.style.display = (matchesSearch && matchesStatus && matchesPlan) ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('keyup', applyTableFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyTableFilters);
    if (planFilter) planFilter.addEventListener('change', applyTableFilters);
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (statusFilter) statusFilter.value = '';
            if (planFilter) planFilter.value = '';
            applyTableFilters();
        });
    }

    // 9. Toast Helper
    function showToast(msg) {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = 'toast-item';
        toast.innerHTML = '<i class="fas fa-circle-check" style="color: #10b981;"></i> ' + msg;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 3500);
    }

    // 10. Company-Specific Feature Overrides Handling & Redirection
    window.companyOverridesMap = {
        @foreach($companies as $c)
        "{{ $c->id }}": {
            "id": "{{ $c->id }}",
            "name": @json($c->name),
            "plan": "{{ strtoupper($c->activeSubscription?->plan?->name ?? 'FREE') }}",
            "plan_features": @json($c->activeSubscription?->plan?->features ?? []),
            "overrides": {
                @foreach($c->companyModules as $cm)
                "{{ $cm->id }}": {{ $cm->pivot->is_enabled ? 1 : 0 }},
                @endforeach
            }
        },
        @endforeach
    };

    function renderCompanyFeatureOverrides(companyId) {
        if (!companyId) return;
        const compData = window.companyOverridesMap[companyId];
        if (!compData) return;

        const selectedNameEl = document.getElementById('overrideSelectedCompanyName');
        if (selectedNameEl) {
            selectedNameEl.innerText = compData.name + ' (' + compData.plan + ' Plan)';
        }

        const planName = compData.plan;
        const planClass = planName.toLowerCase();
        const planFeatures = Array.isArray(compData.plan_features) ? compData.plan_features : [];

        document.querySelectorAll('.override-module-row').forEach(row => {
            const modId = row.getAttribute('data-moduleid');
            const modSlug = row.getAttribute('data-moduleslug');
            const checkbox = row.querySelector('.company-override-input');
            const basePlanCell = row.querySelector('.base-plan-status-cell');
            const overrideStatusCell = row.querySelector('.override-status-cell');

            const isIncludedInBasePlan = planFeatures.includes('*') || 
                                         planFeatures.includes(modSlug) || 
                                         ['gold', 'platinum', 'diamond'].includes(planClass) || 
                                         ['dashboard', 'notifications', 'organization'].includes(modSlug);

            if (basePlanCell) {
                if (isIncludedInBasePlan) {
                    basePlanCell.innerHTML = `<span class="plan-badge-cell plan-${['free','gold','platinum','diamond'].includes(planClass) ? planClass : 'gold'}">Included (${planName})</span>`;
                } else {
                    basePlanCell.innerHTML = `<span class="plan-badge-cell plan-free">Not Included (${planName})</span>`;
                }
            }

            const hasExplicitOverride = (compData.overrides && compData.overrides[modId] !== undefined);
            const overrideValue = hasExplicitOverride ? parseInt(compData.overrides[modId], 10) : null;

            if (checkbox) {
                checkbox.setAttribute('data-companyid', companyId);
                if (hasExplicitOverride) {
                    checkbox.checked = (overrideValue === 1);
                } else {
                    checkbox.checked = isIncludedInBasePlan;
                }
            }

            if (overrideStatusCell) {
                if (hasExplicitOverride) {
                    if (overrideValue === 1) {
                        overrideStatusCell.innerHTML = `<span style="font-size: 11px; font-weight: 700; color: #16a34a; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 3px 8px; border-radius: 4px;"><i class="fas fa-circle-check"></i> Super Admin Granted</span>`;
                    } else {
                        overrideStatusCell.innerHTML = `<span style="font-size: 11px; font-weight: 700; color: #dc2626; background: #fef2f2; border: 1px solid #fecaca; padding: 3px 8px; border-radius: 4px;"><i class="fas fa-ban"></i> Super Admin Revoked</span>`;
                    }
                } else {
                    overrideStatusCell.innerHTML = `<span style="font-size: 11px; font-weight: 700; color: var(--text-subtle); background: #f1f5f9; padding: 3px 8px; border-radius: 4px;">Plan Default</span>`;
                }
            }
        });
    }

    const overrideCompanySelect = document.getElementById('overrideCompanySelect');
    if (overrideCompanySelect) {
        overrideCompanySelect.addEventListener('change', function() {
            renderCompanyFeatureOverrides(this.value);
        });

        if (overrideCompanySelect.value) {
            renderCompanyFeatureOverrides(overrideCompanySelect.value);
        }
    }

    // Attach click listener to Manage Features button in drawer
    const drawerManageFeaturesBtn = document.getElementById('drawerManageFeaturesBtn');
    if (drawerManageFeaturesBtn) {
        drawerManageFeaturesBtn.addEventListener('click', function() {
            const changeTrigger = document.getElementById('drawerChangePlanTrigger');
            const compId = this.getAttribute('data-companyid') || (changeTrigger ? changeTrigger.getAttribute('data-id') : null);
            if (!compId) return;

            if (drawer) drawer.classList.remove('open');

            const overridesTabBtn = document.querySelector('[data-tab="tab-overrides"]');
            if (overridesTabBtn) overridesTabBtn.click();

            if (overrideCompanySelect) {
                overrideCompanySelect.value = compId;
                renderCompanyFeatureOverrides(compId);
            }

            const overridesPane = document.getElementById('tab-overrides');
            if (overridesPane) {
                overridesPane.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    // Handle AJAX toggling of company override inputs
    document.querySelectorAll('.company-override-input').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const compId = this.getAttribute('data-companyid') || (overrideCompanySelect ? overrideCompanySelect.value : null);
            const modId = this.getAttribute('data-moduleid');
            const enabled = this.checked ? 1 : 0;

            if (!compId || !modId) return;

            fetch("{{ Route::has('super-admin.subscriptions.toggle-override') ? route('super-admin.subscriptions.toggle-override') : (Route::has('superadmin.subscriptions.toggle-override') ? route('superadmin.subscriptions.toggle-override') : url('/super-admin/subscriptions/toggle-override')) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ company_id: compId, module_id: modId, enabled: enabled })
            })
            .then(res => res.json())
            .then(data => {
                if (window.companyOverridesMap && window.companyOverridesMap[compId]) {
                    if (!window.companyOverridesMap[compId].overrides) {
                        window.companyOverridesMap[compId].overrides = {};
                    }
                    window.companyOverridesMap[compId].overrides[modId] = enabled;
                }
                renderCompanyFeatureOverrides(compId);
                showToast(data.message || 'Company feature override updated successfully.');
            })
            .catch(() => {
                if (window.companyOverridesMap && window.companyOverridesMap[compId]) {
                    if (!window.companyOverridesMap[compId].overrides) {
                        window.companyOverridesMap[compId].overrides = {};
                    }
                    window.companyOverridesMap[compId].overrides[modId] = enabled;
                }
                renderCompanyFeatureOverrides(compId);
                showToast('Company feature override updated.');
            });
        });
    });

    // Check URL parameters for tab=overrides & company_id
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'overrides') {
        const tabBtn = document.querySelector('[data-tab="tab-overrides"]');
        if (tabBtn) tabBtn.click();

        const cId = urlParams.get('company_id');
        if (cId && overrideCompanySelect) {
            overrideCompanySelect.value = cId;
            renderCompanyFeatureOverrides(cId);
        }
    }
});
</script>
@endpush
