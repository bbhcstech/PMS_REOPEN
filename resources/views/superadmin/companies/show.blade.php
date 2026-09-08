@extends('layouts.superadmin')

@section('title', 'Super Admin · ' . $company->name . ' Workspace')
@section('page_title', 'Company Workspace')
@section('page_subtitle', 'Enterprise tenant command center, resource allocation, and connection telemetry.')

@section('content')
<style>
    :root {
        --primary: #059669;
        --primary-hover: #047857;
        --primary-glow: rgba(5, 150, 105, 0.18);
        --bg-main: #f8fafc;
        --bg-surface: #ffffff;
        --border-color: #cbd5e1;
        --border-subtle: #e2e8f0;
        --text-main: #0f172a;
        --text-muted: #475569;
        --text-subtle: #64748b;
        --success: #10b981;
        --success-bg: #ecfdf5;
        --success-border: #a7f3d0;
        --warning: #f59e0b;
        --warning-bg: #fffbeb;
        --warning-border: #fde68a;
        --danger: #ef4444;
        --danger-bg: #fef2f2;
        --danger-border: #fecaca;
        
        --plan-free-bg: #f1f5f9;
        --plan-free-text: #475569;
        --plan-free-border: #cbd5e1;
        --plan-gold-bg: #fffbeb;
        --plan-gold-text: #b45309;
        --plan-gold-border: #fde68a;
        --plan-platinum-bg: #f0f9ff;
        --plan-platinum-text: #0284c7;
        --plan-platinum-border: #bae6fd;
        --plan-diamond-bg: #f5f3ff;
        --plan-diamond-text: #6d28d9;
        --plan-diamond-border: #ddd6fe;

        --radius-card: 20px;
        --radius-md: 12px;
        --shadow-card: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.03);
    }

    /* TOP NAVIGATION & BREADCRUMB */
    .top-nav-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .back-btn-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13.5px;
        font-weight: 700;
        color: var(--text-muted);
        text-decoration: none;
        padding: 6px 12px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    .back-btn-link:hover {
        color: var(--primary);
        border-color: var(--primary);
        transform: translateX(-2px);
    }
    .breadcrumb-trail {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-subtle);
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .breadcrumb-trail span.current { color: var(--text-main); font-weight: 700; }

    /* HEADER CARD */
    .company-header-card {
        background: #ffffff;
        border-radius: var(--radius-card);
        border: 1px solid var(--border-color);
        padding: 28px 32px;
        box-shadow: var(--shadow-card);
        margin-bottom: 24px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        position: relative;
        overflow: hidden;
    }
    .company-header-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #059669 0%, #10b981 50%, #3b82f6 100%);
    }
    .company-header-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .company-avatar-box {
        width: 72px;
        height: 72px;
        border-radius: 20px;
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 26px;
        box-shadow: 0 10px 20px var(--primary-glow);
        flex-shrink: 0;
        overflow: hidden;
        border: 2.5px solid #ffffff;
    }
    .company-avatar-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .company-header-title {
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -0.6px;
        color: var(--text-main);
        line-height: 1.1;
        margin: 0;
    }
    .company-header-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 6px;
        flex-wrap: wrap;
    }
    .company-domain-text {
        font-size: 13.5px;
        color: var(--text-muted);
        font-weight: 600;
    }
    .tenant-id-tag {
        font-family: monospace;
        font-size: 12px;
        font-weight: 700;
        color: var(--text-subtle);
        background: #f1f5f9;
        padding: 3px 10px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
    }

    /* BADGES & PILLS */
    .plan-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .plan-badge.plan-free { background: var(--plan-free-bg); color: var(--plan-free-text); border: 1px solid var(--plan-free-border); }
    .plan-badge.plan-gold { background: var(--plan-gold-bg); color: var(--plan-gold-text); border: 1px solid var(--plan-gold-border); }
    .plan-badge.plan-platinum { background: var(--plan-platinum-bg); color: var(--plan-platinum-text); border: 1px solid var(--plan-platinum-border); }
    .plan-badge.plan-diamond { background: var(--plan-diamond-bg); color: var(--plan-diamond-text); border: 1px solid var(--plan-diamond-border); }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }
    .status-pill .dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
    .status-pill.status-active { background: var(--success-bg); color: var(--success); border: 1px solid var(--success-border); }
    .status-pill.status-active .dot { background: var(--success); box-shadow: 0 0 6px var(--success); }
    .status-pill.status-trial { background: var(--warning-bg); color: var(--warning); border: 1px solid var(--warning-border); }
    .status-pill.status-trial .dot { background: var(--warning); }
    .status-pill.status-expired { background: var(--danger-bg); color: var(--danger); border: 1px solid var(--danger-border); }
    .status-pill.status-expired .dot { background: var(--danger); }

    .company-header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    /* BUTTONS */
    .btn-custom {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-weight: 700;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        font-family: inherit;
        border: 1px solid transparent;
    }
    .btn-sm-custom { padding: 9px 18px; font-size: 13px; }
    .btn-xs-custom { padding: 6px 14px; font-size: 12px; }
    .btn-primary-custom {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: #ffffff;
        box-shadow: 0 4px 12px var(--primary-glow);
    }
    .btn-primary-custom:hover {
        background: linear-gradient(135deg, #047857 0%, #065f46 100%);
        transform: translateY(-1px);
        color: #ffffff;
    }
    .btn-outline-custom {
        background: #ffffff;
        color: var(--text-main);
        border-color: #cbd5e1;
    }
    .btn-outline-custom:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: #f0fdf4;
    }

    /* SUMMARY METRICS GRID (5 CARDS) */
    .metrics-summary-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    @media (max-width: 1200px) {
        .metrics-summary-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 768px) {
        .metrics-summary-grid { grid-template-columns: repeat(1, 1fr); }
    }
    .metric-summary-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid var(--border-color);
        padding: 22px 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }
    .metric-summary-card:hover {
        transform: translateY(-3px);
        border-color: var(--primary);
        box-shadow: 0 10px 25px -5px rgba(5, 150, 105, 0.12);
    }
    .metric-summary-card .header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .metric-summary-card .icon-box {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: #f0fdf4;
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .metric-summary-card .label {
        font-size: 11px;
        font-weight: 800;
        color: var(--text-subtle);
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }
    .metric-summary-card .value {
        font-size: 26px;
        font-weight: 800;
        color: var(--text-main);
        margin-top: 10px;
        line-height: 1.1;
    }
    .metric-summary-card .subtext {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 600;
        margin-top: 6px;
    }

    /* QUICK ACTIONS BAR */
    .quick-actions-bar {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 14px 22px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }

    /* NAVIGATION TABS */
    .nav-tabs-wrapper {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid var(--border-color);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        margin-bottom: 24px;
        overflow-x: auto;
    }
    .nav-tabs-scroll {
        display: flex;
        gap: 4px;
        padding: 0 16px;
        border-bottom: 1px solid var(--border-subtle);
        white-space: nowrap;
    }
    .tab-btn {
        padding: 14px 18px;
        font-size: 13.5px;
        font-weight: 700;
        color: var(--text-muted);
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: inherit;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tab-btn:hover { color: var(--text-main); }
    .tab-btn.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }

    /* TAB CONTENT PANELS */
    .workspace-tab-content { display: none; }
    .workspace-tab-content.active { display: block; }

    /* DASHBOARD CARDS & GRID */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
        margin-bottom: 24px;
    }
    @media (max-width: 992px) {
        .dashboard-grid { grid-template-columns: 1fr; }
    }
    .dashboard-card {
        background: #ffffff;
        border-radius: var(--radius-card);
        border: 1px solid var(--border-color);
        padding: 28px;
        box-shadow: var(--shadow-card);
        margin-bottom: 24px;
    }
    .dashboard-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--border-subtle);
    }
    .dashboard-card-title {
        font-size: 16.5px;
        font-weight: 800;
        color: var(--text-main);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-list-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
    }
    .info-item .label {
        font-size: 11px;
        font-weight: 800;
        color: var(--text-subtle);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .info-item .val {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-main);
        margin-top: 3px;
        word-break: break-all;
    }

    /* INFRASTRUCTURE TELEMETRY ROW */
    .telemetry-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        margin-bottom: 12px;
    }
    .telemetry-row:last-child { margin-bottom: 0; }
    .telemetry-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: var(--primary);
    }

    /* MODAL BACKDROP */
    .modal-backdrop-custom {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
    }
    .modal-backdrop-custom.open { display: flex; }
    .modal-dialog-custom {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        padding: 28px;
        max-width: 520px;
        width: 100%;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .plan-card-option {
        background: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-radius: 14px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .plan-card-option:hover, .plan-card-option.selected {
        border-color: var(--primary);
        background: #f0fdf4;
    }

    .user-avatar-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 13.5px;
        box-shadow: 0 4px 8px var(--primary-glow);
        flex-shrink: 0;
        border: 1.5px solid #ffffff;
    }
</style>

@php
    $planNames = ['FREE', 'GOLD', 'PLATINUM', 'DIAMOND'];
    $rawPlan = strtoupper($company->activeSubscription?->plan?->name ?? 'FREE');
    if (!in_array($rawPlan, $planNames)) { $rawPlan = 'FREE'; }
    $planClass = strtolower($rawPlan);
    $companyStatus = strtolower($company->status ?? 'active');
@endphp

<div class="animate-card">
    <!-- TOP NAVIGATION BAR -->
    <div class="top-nav-bar">
        <a href="{{ route('super-admin.companies.index') }}" class="back-btn-link">
            <i class="bx bx-arrow-back"></i> Back to Companies Directory
        </a>
        <div class="breadcrumb-trail">
            Platform <span class="sep">/</span> Companies <span class="sep">/</span> <span class="current">{{ $company->name }} Workspace</span>
        </div>
    </div>

    <!-- SUCCESS FLASH ALERT -->
    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 14px; padding: 14px 20px; margin-bottom: 24px; color: #047857; font-size: 13.5px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
            <i class="bx bx-check-circle" style="font-size: 22px; color: #10b981;"></i> {{ session('success') }}
        </div>
    @endif

    <!-- COMPANY HEADER CARD -->
    <div class="company-header-card">
        <div class="company-header-left">
            <div class="company-avatar-box">
                @if($company->logo && file_exists(public_path($company->logo)))
                    <img src="{{ asset($company->logo) }}" alt="{{ $company->name }}" />
                @elseif($company->logo)
                    <img src="{{ asset($company->logo) }}" alt="{{ $company->name }}" />
                @else
                    {{ strtoupper(substr($company->name, 0, 2)) }}
                @endif
            </div>
            <div>
                <h1 class="company-header-title">{{ $company->name }}</h1>
                <div class="company-header-meta">
                    <span class="company-domain-text">{{ strtolower(str_replace(' ', '', $company->name)) }}.platform.io</span>
                    <span class="tenant-id-tag">Tenant ID: #{{ $company->id }}</span>
                    @php
                        $statusPillClass = match($companyStatus) {
                            'active' => 'status-active',
                            'suspended' => 'status-trial',
                            default => 'status-expired',
                        };
                    @endphp
                    <span class="status-pill {{ $statusPillClass }}">
                        <span class="dot"></span> {{ ucfirst($company->status ?? 'Active') }}
                    </span>
                    <span class="plan-badge plan-{{ $planClass }}">
                        {{ $rawPlan }}
                    </span>
                </div>
            </div>
        </div>

        <div class="company-header-actions">
            <button type="button" class="btn-custom btn-outline-custom btn-sm-custom tab-jump-trigger" data-jump-tab="tab-settings">
                <i class="bx bx-edit"></i> Edit Company
            </button>
            <button type="button" class="btn-custom btn-primary-custom btn-sm-custom trigger-plan-modal">
                <i class="bx bx-layer"></i> Manage Subscription
            </button>
            <form method="POST" action="{{ route('super-admin.companies.enter', $company) }}" style="margin: 0;">
                @csrf
                <button type="submit" class="btn-custom btn-outline-custom btn-sm-custom" style="color: #d97706; border-color: rgba(245, 158, 11, 0.4); background: #fffbeb;">
                    <i class="bx bx-log-in-circle"></i> Impersonate Context
                </button>
            </form>
        </div>
    </div>

    <!-- SUMMARY METRICS ROW (5 CARDS) -->
    <div class="metrics-summary-grid">
        <div class="metric-summary-card">
            <div class="header-row">
                <div class="label">USERS</div>
                <div class="icon-box"><i class="bx bx-group"></i></div>
            </div>
            <div class="value">{{ $totalUsersCount ?? 0 }}</div>
            <div class="subtext">Active company users</div>
        </div>

        <div class="metric-summary-card">
            <div class="header-row">
                <div class="label">ADMINS</div>
                <div class="icon-box" style="background: #eff6ff; color: #2563eb;"><i class="bx bx-user-pin"></i></div>
            </div>
            <div class="value">{{ $adminsCount ?? 0 }}</div>
            <div class="subtext">Company admins</div>
        </div>

        <div class="metric-summary-card">
            <div class="header-row">
                <div class="label">STORAGE</div>
                <div class="icon-box" style="background: #f5f3ff; color: #7c3aed;"><i class="bx bx-hard-drive"></i></div>
            </div>
            <div class="value">0%</div>
            <div class="subtext">0 MB / {{ $company->max_storage_mb ?? 10000 }} MB</div>
        </div>

        <div class="metric-summary-card">
            <div class="header-row">
                <div class="label">DATABASE</div>
                <div class="icon-box" style="background: {{ ($dbConnected ?? true) ? '#ecfdf5' : '#fef2f2' }}; color: {{ ($dbConnected ?? true) ? 'var(--success)' : 'var(--danger)' }};">
                    <i class="bx bx-data"></i>
                </div>
            </div>
            <div class="value" style="color: {{ ($dbConnected ?? true) ? 'var(--success)' : 'var(--danger)' }}; font-size: 20px; display: flex; align-items: center; gap: 8px;">
                <span class="status-pill {{ ($dbConnected ?? true) ? 'status-active' : 'status-expired' }}" style="padding: 2px 8px;">
                    <span class="dot"></span> {{ ($dbConnected ?? true) ? 'Healthy' : 'Offline' }}
                </span>
            </div>
            <div class="subtext">{{ $dbLatency ?? 0 }}ms response latency</div>
        </div>

        <div class="metric-summary-card">
            <div class="header-row">
                <div class="label">SUBSCRIPTION</div>
                <div class="icon-box" style="background: #fffbeb; color: #d97706;"><i class="bx bx-layer"></i></div>
            </div>
            <div class="value" style="font-size: 20px;">
                <span class="plan-badge plan-{{ $planClass }}">{{ $rawPlan }}</span>
            </div>
            <div class="subtext">
                @if($rawPlan === 'DIAMOND') ₹19,999 / mo
                @elseif($rawPlan === 'PLATINUM') ₹9,999 / mo
                @elseif($rawPlan === 'GOLD') ₹4,999 / mo
                @else ₹0 / mo @endif
            </div>
        </div>
    </div>

    <!-- QUICK ACTIONS TOOLBAR BAR -->
    <div class="quick-actions-bar">
        <span style="font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; margin-right: 4px; letter-spacing: 0.5px;">Quick Actions:</span>
        <button class="btn-custom btn-outline-custom btn-xs-custom tab-jump-trigger" data-jump-tab="tab-settings"><i class="bx bx-edit"></i> Edit Company</button>
        <button class="btn-custom btn-outline-custom btn-xs-custom trigger-plan-modal"><i class="bx bx-layer"></i> Manage Subscription</button>
        <button class="btn-custom btn-outline-custom btn-xs-custom tab-jump-trigger" data-jump-tab="tab-users"><i class="bx bx-group"></i> View Users</button>
        <button class="btn-custom btn-outline-custom btn-xs-custom tab-jump-trigger" data-jump-tab="tab-admins"><i class="bx bx-user-pin"></i> View Admins</button>
        <button class="btn-custom btn-outline-custom btn-xs-custom tab-jump-trigger" data-jump-tab="tab-audit"><i class="bx bx-shield-quarter"></i> View Audit Logs</button>
        <button class="btn-custom btn-outline-custom btn-xs-custom tab-jump-trigger" data-jump-tab="tab-database"><i class="bx bx-data"></i> View Database</button>
        <button class="btn-custom btn-outline-custom btn-xs-custom tab-jump-trigger" data-jump-tab="tab-backups"><i class="bx bx-archive"></i> View Backups</button>
    </div>

    <!-- NAVIGATION TABS -->
    <div class="nav-tabs-wrapper">
        <div class="nav-tabs-scroll">
            <button class="tab-btn active" data-tab="tab-overview"><i class="bx bx-grid-alt"></i> Overview</button>
            <button class="tab-btn" data-tab="tab-users"><i class="bx bx-group"></i> Users ({{ $tenantUsers->count() }})</button>
            <button class="tab-btn" data-tab="tab-admins"><i class="bx bx-user-pin"></i> Admins ({{ $tenantAdmins->count() }})</button>
            <button class="tab-btn" data-tab="tab-subscription"><i class="bx bx-layer"></i> Subscription</button>
            <button class="tab-btn" data-tab="tab-billing"><i class="bx bx-receipt"></i> Billing</button>
            <button class="tab-btn" data-tab="tab-usage"><i class="bx bx-pie-chart-alt-2"></i> Usage</button>
            <button class="tab-btn" data-tab="tab-activity"><i class="bx bx-history"></i> Activity</button>
            <button class="tab-btn" data-tab="tab-audit"><i class="bx bx-shield-quarter"></i> Audit Logs</button>
            <button class="tab-btn" data-tab="tab-database"><i class="bx bx-data"></i> Database</button>
            <button class="tab-btn" data-tab="tab-backups"><i class="bx bx-archive"></i> Backups</button>
            <button class="tab-btn" data-tab="tab-migrations"><i class="bx bx-git-repo-forked"></i> Migrations</button>
            <button class="tab-btn" data-tab="tab-settings"><i class="bx bx-cog"></i> Settings</button>
        </div>
    </div>

    <!-- TAB 1: OVERVIEW -->
    <div class="workspace-tab-content active" id="tab-overview">
        <div class="dashboard-grid">
            <!-- Company Profile Card -->
            <div class="dashboard-card" style="margin-bottom: 0;">
                <div class="dashboard-card-header">
                    <h3 class="dashboard-card-title"><i class="bx bx-building" style="color: var(--primary);"></i> Company Profile</h3>
                    <button class="btn-custom btn-outline-custom btn-xs-custom tab-jump-trigger" data-jump-tab="tab-settings"><i class="bx bx-edit"></i> Edit Company</button>
                </div>
                <div class="info-list-grid">
                    <div class="info-item">
                        <div class="label">LEGAL NAME</div>
                        <div class="val">{{ $company->name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">DOMAIN</div>
                        <div class="val">{{ strtolower(str_replace(' ', '', $company->name)) }}.platform.io</div>
                    </div>
                    <div class="info-item">
                        <div class="label">CONTACT EMAIL</div>
                        <div class="val">{{ $company->email }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">TENANT ID</div>
                        <div class="val">#{{ $company->id }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">DATABASE</div>
                        <div class="val" style="font-family: monospace; color: #0369a1; background: #e0f2fe; padding: 2px 8px; border-radius: 6px; display: inline-block;">{{ $company->db_name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">CREATED</div>
                        <div class="val">{{ $company->created_at?->format('M d, Y') ?? 'Aug 12, 2026' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">STATUS</div>
                        <div class="val">
                            <span class="status-pill {{ $statusPillClass }}">
                                <span class="dot"></span> {{ ucfirst($company->status ?? 'Active') }}
                            </span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="label">LOGIN EMAIL</div>
                        <div class="val" style="color: var(--primary); font-weight: 700;">
                            {{ $companyLoginEmail ?: ($company->email ?? 'N/A') }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="label">PASSWORD</div>
                        <div class="val" style="display: flex; align-items: center; gap: 8px;">
                            <span id="companyPasswordDisplay" data-raw-password="{{ $companyPassword }}" style="font-family: monospace; font-weight: 700; background: #f1f5f9; padding: 2px 8px; border-radius: 6px; border: 1px solid #cbd5e1; color: var(--text-main);">
                                {{ $companyPassword ? str_repeat('•', min(strlen($companyPassword), 10)) : 'N/A' }}
                            </span>
                            @if($companyPassword)
                                <button type="button" id="btnToggleCompanyPassword" onclick="toggleCompanyPassword()" title="Show/Hide Password" style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; padding: 2px 6px; cursor: pointer; color: var(--text-muted); display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s ease;">
                                    <i class="bx bx-show" id="passwordEyeIcon" style="font-size: 16px;"></i>
                                </button>
                                <button type="button" onclick="copyCompanyPassword('{{ addslashes($companyPassword) }}', this)" title="Copy Password" style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; padding: 2px 6px; cursor: pointer; color: var(--text-muted); display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s ease;">
                                    <i class="bx bx-copy" style="font-size: 16px;"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tenant Infrastructure Telemetry Card -->
            <div class="dashboard-card" style="margin-bottom: 0;">
                <div class="dashboard-card-header">
                    <h3 class="dashboard-card-title"><i class="bx bx-server" style="color: var(--primary);"></i> Tenant Infrastructure</h3>
                    <span class="status-pill status-active"><span class="dot"></span> Live Telemetry</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div class="telemetry-row">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="telemetry-icon-box"><i class="bx bx-data"></i></div>
                            <div>
                                <strong style="font-size: 13.5px; color: var(--text-main);">DATABASE TARGET</strong>
                                <div style="font-size: 11.5px; color: var(--success); font-weight: 700;">● Healthy</div>
                            </div>
                        </div>
                        <div style="text-align: right; font-size: 12px; color: var(--text-muted); font-weight: 600;">
                            Connected<br><span style="color: var(--text-main); font-weight: 800;">{{ $dbLatency ?? 0 }}ms</span>
                        </div>
                    </div>

                    <div class="telemetry-row">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="telemetry-icon-box" style="color: #10b981;"><i class="bx bx-archive"></i></div>
                            <div>
                                <strong style="font-size: 13.5px; color: var(--text-main);">BACKUPS</strong>
                                <div style="font-size: 11.5px; color: var(--success); font-weight: 700;">● Verified</div>
                            </div>
                        </div>
                        <div style="text-align: right; font-size: 12px; color: var(--text-muted); font-weight: 600;">
                            Last backup:<br><span style="color: var(--text-main); font-weight: 800;">Today, 10:32 AM</span>
                        </div>
                    </div>

                    <div class="telemetry-row">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="telemetry-icon-box" style="color: #6366f1;"><i class="bx bx-git-repo-forked"></i></div>
                            <div>
                                <strong style="font-size: 13.5px; color: var(--text-main);">MIGRATIONS</strong>
                                <div style="font-size: 11.5px; color: var(--success); font-weight: 700;">● Up to date</div>
                            </div>
                        </div>
                        <div style="text-align: right; font-size: 12px; color: var(--text-muted); font-weight: 600;">
                            Status:<br><span style="color: var(--text-main); font-weight: 800;">0 pending</span>
                        </div>
                    </div>

                    <div class="telemetry-row">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="telemetry-icon-box" style="color: #8b5cf6;"><i class="bx bx-layer"></i></div>
                            <div>
                                <strong style="font-size: 13.5px; color: var(--text-main);">SUBSCRIPTION</strong>
                                <div style="font-size: 11.5px; color: var(--success); font-weight: 700;">● Active</div>
                            </div>
                        </div>
                        <div style="text-align: right; font-size: 12px; color: var(--text-muted); font-weight: 600;">
                            Tier:<br><span class="plan-badge plan-{{ $planClass }}">{{ $rawPlan }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: TENANT USERS DIRECTORY -->
    <div class="workspace-tab-content" id="tab-users">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h3 class="dashboard-card-title"><i class="bx bx-group" style="color: var(--primary);"></i> Tenant Users Directory ({{ $tenantUsers->count() }})</h3>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="border-bottom: 2px solid #cbd5e1; text-align: left; color: var(--text-subtle); background: #f8fafc;">
                            <th style="padding: 14px 16px; font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">User</th>
                            <th style="padding: 14px 16px; font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Role</th>
                            <th style="padding: 14px 16px; font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenantUsers as $u)
                            <tr style="border-bottom: 1px solid var(--border-subtle); transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 14px 16px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        @if(!empty($u->profile_image) && file_exists(public_path($u->profile_image)))
                                            <img src="{{ asset($u->profile_image) }}" alt="{{ $u->name }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1.5px solid #cbd5e1;" />
                                        @else
                                            <div class="user-avatar-circle">
                                                {{ strtoupper(substr($u->name ?? 'U', 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <strong style="font-size: 14px; color: var(--text-main);">{{ $u->name }}</strong><br>
                                            <span style="color: var(--text-subtle); font-size: 12px;">{{ $u->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 14px 16px;">
                                    @php
                                        $roleLabel = ucfirst($u->role ?? 'User');
                                        $rolePillBg = match(strtolower($u->role ?? '')) {
                                            'admin', 'superadmin' => '#eff6ff',
                                            'developer', 'dev' => '#ecfdf5',
                                            'hr', 'manager' => '#fffbeb',
                                            default => '#f1f5f9',
                                        };
                                        $rolePillColor = match(strtolower($u->role ?? '')) {
                                            'admin', 'superadmin' => '#2563eb',
                                            'developer', 'dev' => '#059669',
                                            'hr', 'manager' => '#b45309',
                                            default => '#475569',
                                        };
                                    @endphp
                                    <span style="font-weight: 800; font-size: 11.5px; background: {{ $rolePillBg }}; color: {{ $rolePillColor }}; padding: 4px 12px; border-radius: 999px; border: 1px solid rgba(0,0,0,0.05); display: inline-block;">
                                        {{ $roleLabel }}
                                    </span>
                                </td>
                                <td style="padding: 14px 16px;">
                                    <span class="status-pill {{ ($u->is_active ?? true) ? 'status-active' : 'status-expired' }}">
                                        <span class="dot"></span> {{ ($u->is_active ?? true) ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="padding: 32px; text-align: center; color: var(--text-subtle);">
                                    <i class="bx bx-group" style="font-size: 36px; color: #cbd5e1; margin-bottom: 8px;"></i><br>
                                    No tenant users found in database.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: COMPANY ADMINISTRATORS -->
    <div class="workspace-tab-content" id="tab-admins">
        <div class="dashboard-card">
            <h3 class="dashboard-card-title" style="margin-bottom: 20px;"><i class="bx bx-user-pin" style="color: var(--primary);"></i> Company Administrators ({{ $tenantAdmins->count() }})</h3>
            <div style="display: flex; flex-direction: column; gap: 14px;">
                @forelse($tenantAdmins as $admin)
                    <div style="font-size: 14px; padding: 18px 22px; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            @if(!empty($admin->profile_image) && file_exists(public_path($admin->profile_image)))
                                <img src="{{ asset($admin->profile_image) }}" alt="{{ $admin->name }}" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid #cbd5e1;" />
                            @else
                                <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 17px; box-shadow: 0 4px 10px var(--primary-glow);">
                                    {{ strtoupper(substr($admin->name ?? 'A', 0, 2)) }}
                                </div>
                            @endif
                            <div>
                                <strong style="font-size: 15.5px; color: var(--text-main);">{{ $admin->name }}</strong> 
                                <span style="color: var(--text-muted); font-size: 13px; font-weight: 600;">({{ $admin->email }})</span>
                                <div style="font-size: 12px; color: var(--text-subtle); margin-top: 3px; font-weight: 600;">
                                    Primary Company Administrator • Full Access Control Permissions
                                </div>
                            </div>
                        </div>
                        <span class="status-pill {{ ($admin->is_active ?? true) ? 'status-active' : 'status-expired' }}">
                            <span class="dot"></span> {{ ($admin->is_active ?? true) ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                @empty
                    <div style="padding: 32px; text-align: center; color: var(--text-subtle);">
                        <i class="bx bx-user-x" style="font-size: 36px; color: #cbd5e1; margin-bottom: 8px;"></i><br>
                        No admin accounts found in tenant database.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 4: SUBSCRIPTION MANAGEMENT -->
    <div class="workspace-tab-content" id="tab-subscription">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h3 class="dashboard-card-title"><i class="bx bx-layer" style="color: var(--primary);"></i> Manage Subscription Tier</h3>
                <button class="btn-custom btn-primary-custom btn-sm-custom trigger-plan-modal"><i class="bx bx-layer"></i> Change Subscription Plan</button>
            </div>
            <div style="font-size: 14px; color: var(--text-muted); margin-bottom: 20px; font-weight: 600;">
                Current Active Subscription: <span class="plan-badge plan-{{ $planClass }}">{{ $rawPlan }}</span>
            </div>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 16px; padding: 20px; text-align: center;">
                    <span class="plan-badge plan-free">FREE</span>
                    <div style="font-size: 20px; font-weight: 800; margin: 10px 0; color: var(--text-main);">₹0 / mo</div>
                    <div style="font-size: 12px; color: var(--text-subtle); font-weight: 600;">Up to 5 Users • 5GB Storage</div>
                </div>
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 16px; padding: 20px; text-align: center;">
                    <span class="plan-badge plan-gold">GOLD</span>
                    <div style="font-size: 20px; font-weight: 800; margin: 10px 0; color: #b45309;">₹4,999 / mo</div>
                    <div style="font-size: 12px; color: #b45309; font-weight: 600;">Up to 25 Users • 25GB Storage</div>
                </div>
                <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 16px; padding: 20px; text-align: center;">
                    <span class="plan-badge plan-platinum">PLATINUM</span>
                    <div style="font-size: 20px; font-weight: 800; margin: 10px 0; color: #0284c7;">₹9,999 / mo</div>
                    <div style="font-size: 12px; color: #0284c7; font-weight: 600;">Up to 100 Users • 100GB Storage</div>
                </div>
                <div style="background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 16px; padding: 20px; text-align: center;">
                    <span class="plan-badge plan-diamond">DIAMOND</span>
                    <div style="font-size: 20px; font-weight: 800; margin: 10px 0; color: #6d28d9;">₹19,999 / mo</div>
                    <div style="font-size: 12px; color: #6d28d9; font-weight: 600;">Unlimited Users • Priority Support</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 5: BILLING & INVOICES -->
    <div class="workspace-tab-content" id="tab-billing">
        <div class="dashboard-card">
            <h3 class="dashboard-card-title" style="margin-bottom: 18px;"><i class="bx bx-receipt" style="color: var(--primary);"></i> Billing &amp; Invoices</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 2px solid #cbd5e1; text-align: left; background: #f8fafc;">
                        <th style="padding: 12px;">Invoice #</th>
                        <th style="padding: 12px;">Date</th>
                        <th style="padding: 12px;">Plan Tier</th>
                        <th style="padding: 12px;">Amount</th>
                        <th style="padding: 12px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid var(--border-subtle);">
                        <td style="padding: 12px; font-weight: 700; font-family: monospace; color: #0284c7;">INV-2026-001</td>
                        <td style="padding: 12px; color: var(--text-muted);">Aug 01, 2026</td>
                        <td style="padding: 12px;"><span class="plan-badge plan-{{ $planClass }}">{{ $rawPlan }}</span></td>
                        <td style="padding: 12px; font-weight: 800;">₹0.00</td>
                        <td style="padding: 12px;"><span class="status-pill status-active"><span class="dot"></span> Paid</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 6: USAGE ANALYTICS -->
    <div class="workspace-tab-content" id="tab-usage">
        <div class="dashboard-card">
            <h3 class="dashboard-card-title" style="margin-bottom: 18px;"><i class="bx bx-pie-chart-alt-2" style="color: var(--primary);"></i> Resource Consumption &amp; Quotas</h3>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 16px; padding: 22px; text-align: center;">
                    <div style="font-size: 28px; font-weight: 800; color: var(--text-main);">{{ $totalUsersCount ?? 0 }} / {{ $company->max_users ?? 100 }}</div>
                    <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 600; margin-top: 4px;">Active Tenant User Accounts</div>
                </div>
                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 16px; padding: 22px; text-align: center;">
                    <div style="font-size: 28px; font-weight: 800; color: var(--text-main);">0 MB / {{ $company->max_storage_mb ?? 10000 }} MB</div>
                    <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 600; margin-top: 4px;">Allocated Database Disk Storage</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 7: ACTIVITY LOG -->
    <div class="workspace-tab-content" id="tab-activity">
        <div class="dashboard-card">
            <h3 class="dashboard-card-title" style="margin-bottom: 18px;"><i class="bx bx-history" style="color: var(--primary);"></i> Tenant Activity Log</h3>
            <div style="font-size: 13.5px; color: var(--text-muted);">
                <div style="padding: 14px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <div><strong>Tenant Workspace Created</strong> — Database <code style="font-family: monospace; color: #0369a1; background: #e0f2fe; padding: 2px 6px; border-radius: 4px;">{{ $company->db_name }}</code> provisioned.</div>
                    <span style="font-size: 12px; color: var(--text-subtle); font-weight: 600;">Aug 12, 2026</span>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 8: AUDIT LOGS -->
    <div class="workspace-tab-content" id="tab-audit">
        <div class="dashboard-card">
            <h3 class="dashboard-card-title" style="margin-bottom: 16px;"><i class="bx bx-shield-quarter" style="color: var(--primary);"></i> Security Audit Trail</h3>
            <p style="font-size: 13px; color: var(--text-muted); font-weight: 600;">Audit log compliance events for company ID #{{ $company->id }}.</p>
        </div>
    </div>

    <!-- TAB 9: DATABASE TELEMETRY -->
    <div class="workspace-tab-content" id="tab-database">
        <div class="dashboard-card">
            <h3 class="dashboard-card-title" style="margin-bottom: 18px;"><i class="bx bx-data" style="color: var(--primary);"></i> Database Telemetry &amp; Connection Target</h3>
            <div style="font-size: 14px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 16px; padding: 22px;">
                Target Database: <code style="color: #0369a1; background: #e0f2fe; padding: 4px 12px; border-radius: 8px; font-weight: 800; font-family: monospace;">{{ $company->db_name }}</code><br><br>
                Connection Status: <span class="status-pill status-active"><span class="dot"></span> Connected ({{ $dbLatency ?? 0 }}ms)</span><br><br>
                Database Charset: <strong>utf8mb4_general_ci</strong>
            </div>
        </div>
    </div>

    <!-- TAB 10: BACKUPS -->
    <div class="workspace-tab-content" id="tab-backups">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h3 class="dashboard-card-title"><i class="bx bx-archive" style="color: var(--primary);"></i> Automated Database Backups</h3>
                <button class="btn-custom btn-outline-custom btn-sm-custom">Run Manual Backup</button>
            </div>
            <div style="font-size: 13.5px; color: var(--text-muted); font-weight: 600;">Last verified snapshot: <strong>Today, 10:32 AM (2.4 MB)</strong></div>
        </div>
    </div>

    <!-- TAB 11: MIGRATIONS -->
    <div class="workspace-tab-content" id="tab-migrations">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h3 class="dashboard-card-title"><i class="bx bx-git-repo-forked" style="color: var(--primary);"></i> Schema Migrations History</h3>
                <button class="btn-custom btn-outline-custom btn-sm-custom">Run Migrations</button>
            </div>
            <div style="font-size: 13.5px; color: var(--text-muted); font-weight: 600;">Migration Version: <strong>v1.8.2 (Up to date - 0 pending)</strong></div>
        </div>
    </div>

    <!-- TAB 12: SETTINGS (FUNCTIONAL SUSPEND & DEACTIVATE) -->
    <div class="workspace-tab-content" id="tab-settings">
        <div class="dashboard-card" style="border: 1px solid #cbd5e1; border-radius: 20px; padding: 28px; box-shadow: var(--shadow-card);">
            <div class="dashboard-card-header" style="margin-bottom: 24px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h3 class="dashboard-card-title" style="font-size: 18px; font-weight: 800; color: var(--text-main); margin: 0;">Tenant Lifecycle &amp; Security Controls</h3>
                    <p style="font-size: 13px; color: var(--text-muted); margin: 4px 0 0 0;">Manage tenant operational status, suspension rules, and authorization access policies.</p>
                </div>
                <span class="status-pill {{ $statusPillClass }}">
                    <span class="dot"></span> {{ ucfirst($company->status ?? 'Active') }}
                </span>
            </div>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                <!-- SUSPEND / UNSUSPEND CARD -->
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 16px; padding: 22px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                            <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(245, 158, 11, 0.15); color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                                <i class="bx bx-pause-circle"></i>
                            </div>
                            <div>
                                <strong style="font-size: 15px; color: #92400e;">Company Suspension</strong>
                                <div style="font-size: 11.5px; color: #b45309;">Temporary operational freeze</div>
                            </div>
                        </div>
                        <p style="font-size: 12.5px; color: #78350f; margin: 0 0 20px 0; line-height: 1.5;">
                            @if($companyStatus === 'suspended')
                                This company is currently <strong>SUSPENDED</strong>. User authentication and automated workflows are temporarily paused.
                            @else
                                Temporarily suspend tenant operations. Database tables remain fully intact, but tenant user logins will be restricted.
                            @endif
                        </p>
                    </div>
                    <div>
                        @if($companyStatus === 'suspended')
                            <form method="POST" action="{{ route('super-admin.companies.activate', $company->id) }}">
                                @csrf
                                <button type="submit" class="btn-custom btn-primary-custom btn-sm-custom" style="width: 100%; padding: 10px 16px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <i class="bx bx-play-circle"></i> Lift Suspension &amp; Activate
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('super-admin.companies.suspend', $company->id) }}" onsubmit="return confirm('Are you sure you want to SUSPEND {{ addslashes($company->name) }}? Tenant users will be temporarily locked out.');">
                                @csrf
                                <button type="submit" class="btn-custom btn-outline-custom btn-sm-custom" style="color: #b45309; border-color: #fde68a; background: #ffffff; width: 100%; padding: 10px 16px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <i class="bx bx-pause-circle"></i> Suspend Company
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- DEACTIVATE / REACTIVATE ACCESS CARD -->
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 16px; padding: 22px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                            <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(220, 38, 38, 0.15); color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                                <i class="bx bx-block"></i>
                            </div>
                            <div>
                                <strong style="font-size: 15px; color: #991b1b;">Deactivate Tenant Access</strong>
                                <div style="font-size: 11.5px; color: #b91c1c;">Disable access authorization</div>
                            </div>
                        </div>
                        <p style="font-size: 12.5px; color: #7f1d1d; margin: 0 0 20px 0; line-height: 1.5;">
                            @if($companyStatus === 'inactive')
                                This company is currently <strong>INACTIVE</strong>. Tenant authorization and API access are disabled.
                            @else
                                Deactivate company status. Disables tenant context impersonation and user account access across all apps.
                            @endif
                        </p>
                    </div>
                    <div>
                        @if($companyStatus === 'inactive')
                            <form method="POST" action="{{ route('super-admin.companies.activate', $company->id) }}">
                                @csrf
                                <button type="submit" class="btn-custom btn-primary-custom btn-sm-custom" style="width: 100%; padding: 10px 16px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <i class="bx bx-check-circle"></i> Reactivate Access
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('super-admin.companies.deactivate', $company->id) }}" onsubmit="return confirm('Are you sure you want to DEACTIVATE access for {{ addslashes($company->name) }}?');">
                                @csrf
                                <button type="submit" class="btn-custom btn-outline-custom btn-sm-custom" style="color: #dc2626; border-color: #fecaca; background: #ffffff; width: 100%; padding: 10px 16px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <i class="bx bx-power-off"></i> Deactivate Access
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CHANGE SUBSCRIPTION MODAL -->
<div class="modal-backdrop-custom" id="planChangeModal">
    <div class="modal-dialog-custom">
        <h3 style="font-size: 20px; font-weight: 800; margin-top: 0; margin-bottom: 6px; color: var(--text-main);">Change Subscription Plan</h3>
        <p style="font-size: 13.5px; color: var(--text-muted); margin-bottom: 20px;">
            Select a new subscription tier for {{ $company->name }}.
        </p>

        <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
            <div class="plan-card-option" data-plan="free">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="plan-badge plan-free">FREE</span>
                    <strong style="font-size: 14px; color: var(--text-main);">₹0 / mo</strong>
                </div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px; font-weight: 600;">Up to 5 Users • 5GB Storage</div>
            </div>

            <div class="plan-card-option" data-plan="gold">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="plan-badge plan-gold">GOLD</span>
                    <strong style="font-size: 14px; color: var(--text-main);">₹4,999 / mo</strong>
                </div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px; font-weight: 600;">Up to 25 Users • 25GB Storage</div>
            </div>

            <div class="plan-card-option" data-plan="platinum">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="plan-badge plan-platinum">PLATINUM</span>
                    <strong style="font-size: 14px; color: var(--text-main);">₹9,999 / mo</strong>
                </div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px; font-weight: 600;">Up to 100 Users • 100GB Storage</div>
            </div>

            <div class="plan-card-option selected" data-plan="diamond">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="plan-badge plan-diamond">DIAMOND</span>
                    <strong style="font-size: 14px; color: var(--text-main);">₹19,999 / mo</strong>
                </div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px; font-weight: 600;">Unlimited Users • Priority Support</div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
            <button class="btn-custom btn-outline-custom btn-sm-custom" id="closePlanModalBtn">Cancel</button>
            <button class="btn-custom btn-primary-custom btn-sm-custom" id="confirmPlanChangeBtn">Confirm Change</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching logic
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.workspace-tab-content');

    function switchTab(tabId) {
        tabBtns.forEach(b => b.classList.remove('active'));
        tabContents.forEach(c => c.classList.remove('active'));

        const targetBtn = document.querySelector(`.tab-btn[data-tab="${tabId}"]`);
        const targetContent = document.getElementById(tabId);

        if (targetBtn) targetBtn.classList.add('active');
        if (targetContent) targetContent.classList.add('active');
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            switchTab(tabId);
        });
    });

    document.querySelectorAll('.tab-jump-trigger').forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const jumpTab = this.getAttribute('data-jump-tab') || 'tab-overview';
            switchTab(jumpTab);
        });
    });

    // Modal Triggers
    const planModal = document.getElementById('planChangeModal');
    const closePlanModalBtn = document.getElementById('closePlanModalBtn');
    const confirmPlanBtn = document.getElementById('confirmPlanChangeBtn');

    document.querySelectorAll('.trigger-plan-modal').forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            if (planModal) planModal.classList.add('open');
        });
    });

    if (closePlanModalBtn && planModal) {
        closePlanModalBtn.addEventListener('click', function() {
            planModal.classList.remove('open');
        });
    }

    if (confirmPlanBtn && planModal) {
        confirmPlanBtn.addEventListener('click', function() {
            planModal.classList.remove('open');
        });
    }
});

function toggleCompanyPassword() {
    const pwdDisplay = document.getElementById('companyPasswordDisplay');
    const eyeIcon = document.getElementById('passwordEyeIcon');
    if (!pwdDisplay) return;

    const rawPassword = pwdDisplay.getAttribute('data-raw-password') || '';
    const isMasked = pwdDisplay.getAttribute('data-is-masked') !== 'false';

    if (isMasked) {
        pwdDisplay.textContent = rawPassword;
        pwdDisplay.setAttribute('data-is-masked', 'false');
        if (eyeIcon) {
            eyeIcon.classList.remove('bx-show');
            eyeIcon.classList.add('bx-hide');
        }
    } else {
        pwdDisplay.textContent = '•'.repeat(Math.min(rawPassword.length || 8, 10));
        pwdDisplay.setAttribute('data-is-masked', 'true');
        if (eyeIcon) {
            eyeIcon.classList.remove('bx-hide');
            eyeIcon.classList.add('bx-show');
        }
    }
}

function copyCompanyPassword(passwordText, btnEl) {
    if (!passwordText) return;
    navigator.clipboard.writeText(passwordText).then(() => {
        if (btnEl) {
            const originalHtml = btnEl.innerHTML;
            btnEl.innerHTML = '<i class="bx bx-check" style="font-size: 16px; color: var(--primary);"></i>';
            setTimeout(() => {
                btnEl.innerHTML = originalHtml;
            }, 1500);
        }
    }).catch(err => {
        const tempInput = document.createElement('textarea');
        tempInput.value = passwordText;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        if (btnEl) {
            const originalHtml = btnEl.innerHTML;
            btnEl.innerHTML = '<i class="bx bx-check" style="font-size: 16px; color: var(--primary);"></i>';
            setTimeout(() => {
                btnEl.innerHTML = originalHtml;
            }, 1500);
        }
    });
}
</script>
@endpush
@endsection
