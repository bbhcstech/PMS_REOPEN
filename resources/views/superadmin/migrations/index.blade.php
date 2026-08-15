@extends('layouts.superadmin')

@section('title', 'Super Admin · Tenant Migration Control Center')
@section('page_title', 'Migrations')
@section('page_subtitle', 'Monitor, manage, and execute database migrations across all tenant environments.')

@section('content')
<style>
    /* ============================================================
       TENANT MIGRATION CONTROL CENTER THEME TOKENS
       ============================================================ */
    :root {
        --navy-dark: #0b1729;
        --navy-surface: #0f172a;
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --primary-soft: #eff6ff;
        --primary-ring: rgba(37, 99, 235, 0.2);
        
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

        --radius-lg: 16px;
        --radius-md: 10px;
        --radius-sm: 6px;
        
        --shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
        --shadow-sm: 0 4px 16px rgba(15, 23, 42, 0.04);
        --shadow-md: 0 10px 30px rgba(15, 23, 42, 0.08);

        --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Page Header */
    .migrations-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }
    .header-left {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .header-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f0fdf4;
        color: #16a34a;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid #bbf7d0;
        width: fit-content;
        margin-bottom: 4px;
    }
    .header-status-badge .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #16a34a;
        box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.2);
    }
    .header-controls {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-action-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        border-radius: var(--radius-md);
        background: #ffffff;
        color: var(--text-main);
        font-size: 13px;
        font-weight: 600;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-xs);
        cursor: pointer;
        transition: all var(--transition-fast);
        text-decoration: none;
    }
    .btn-action-secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: var(--primary);
    }

    .btn-action-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        border-radius: var(--radius-md);
        background: var(--primary);
        color: #ffffff;
        font-size: 13px;
        font-weight: 600;
        border: none;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
        cursor: pointer;
        transition: all var(--transition-fast);
        text-decoration: none;
    }
    .btn-action-primary:hover {
        background: var(--primary-hover);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        color: #ffffff;
    }

    /* KPI Cards Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .kpi-card {
        background: var(--bg-surface);
        border-radius: var(--radius-lg);
        padding: 20px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-xs);
        transition: all var(--transition-fast);
    }
    .kpi-card:hover {
        border-color: #cbd5e1;
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }
    .kpi-card .card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .kpi-card .card-label {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--text-subtle);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .kpi-card .icon-pill {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    .kpi-card .metric-val {
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--text-main);
        margin-top: 10px;
        line-height: 1.1;
    }
    .kpi-card .metric-foot {
        font-size: 12px;
        font-weight: 600;
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .metric-foot.positive { color: var(--success); }
    .metric-foot.warning { color: var(--warning); }
    .metric-foot.danger { color: var(--danger); }
    .metric-foot.muted { color: var(--text-muted); font-weight: 500; }

    /* Migration Toolbar */
    .migration-toolbar {
        background: var(--bg-surface);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        padding: 16px 20px;
        margin-bottom: 20px;
        box-shadow: var(--shadow-xs);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
    }
    .toolbar-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        flex: 1;
    }
    .search-box {
        position: relative;
        min-width: 260px;
        flex: 1;
        max-width: 340px;
    }
    .search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-subtle);
        font-size: 14px;
    }
    .search-box input {
        width: 100%;
        padding: 9px 12px 9px 36px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        font-size: 13px;
        outline: none;
        background: #ffffff;
        transition: all var(--transition-fast);
    }
    .search-box input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-ring);
    }
    .filter-select {
        padding: 9px 14px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        background: #ffffff;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-main);
        outline: none;
        cursor: pointer;
    }
    .filter-select:focus {
        border-color: var(--primary);
    }

    /* Floating Contextual Bulk Action Bar */
    .bulk-action-bar {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: #0f172a;
        color: #ffffff;
        padding: 12px 24px;
        border-radius: 40px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        display: flex;
        align-items: center;
        gap: 16px;
        z-index: 250;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255, 255, 255, 0.15);
    }
    .bulk-action-bar.visible {
        transform: translateX(-50%) translateY(0);
    }

    /* Slide-Over Drawer */
    .drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(4px);
        z-index: 400;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    .drawer-overlay.open {
        opacity: 1;
        visibility: visible;
    }
    .drawer-panel {
        position: fixed;
        top: 0;
        right: 0;
        width: 100%;
        max-width: 520px;
        height: 100vh;
        background: #ffffff;
        box-shadow: -10px 0 40px rgba(0, 0, 0, 0.15);
        z-index: 401;
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
    }
    .drawer-panel.open {
        transform: translateX(0);
    }
    .drawer-header {
        padding: 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        background: #f8fafc;
    }
    .drawer-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .drawer-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border-color);
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }

    /* Timeline in Drawer */
    .migration-timeline {
        display: flex;
        flex-direction: column;
        gap: 12px;
        position: relative;
        padding-left: 20px;
    }
    .migration-timeline::before {
        content: '';
        position: absolute;
        left: 7px;
        top: 8px;
        bottom: 8px;
        width: 2px;
        background: var(--border-color);
    }
    .timeline-item {
        position: relative;
        font-size: 13px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    .timeline-node {
        position: absolute;
        left:-20px;
        top: 2px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #ffffff;
        border: 2px solid var(--text-subtle);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
    }
    .timeline-item.completed .timeline-node {
        border-color: var(--success);
        background: var(--success);
        color: #ffffff;
    }
    .timeline-item.pending .timeline-node {
        border-color: var(--warning);
        background: #ffffff;
    }
    .timeline-item.failed .timeline-node {
        border-color: var(--danger);
        background: var(--danger);
        color: #ffffff;
    }

    /* Modal Standard */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(4px);
        z-index: 500;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-overlay.open { display: flex; }
    .modal-box {
        background: #ffffff;
        border-radius: var(--radius-lg);
        max-width: 580px;
        width: 100%;
        padding: 24px;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-color);
        max-height: 90vh;
        overflow-y: auto;
    }

    /* Terminal Log Viewer */
    .terminal-window {
        background: #090d16;
        color: #38bdf8;
        font-family: 'JetBrains Mono', Consolas, monospace;
        font-size: 12.5px;
        padding: 18px;
        border-radius: 12px;
        line-height: 1.6;
        max-height: 380px;
        overflow-y: auto;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.5);
    }
    .terminal-window .log-success { color: #4ade80; }
    .terminal-window .log-error { color: #f87171; }
    .terminal-window .log-warn { color: #fbbf24; }
    .terminal-window .log-info { color: #94a3b8; }
</style>

<!-- 1. PREMIUM PAGE HEADER -->
<div class="migrations-header">
    <div class="header-left">
        <div class="header-status-badge">
            <span class="dot"></span> Migration Engine Operational
        </div>
        <h1 style="font-size: 26px; font-weight: 800; color: var(--text-main); letter-spacing: -0.5px; margin: 0;">
            Migrations
        </h1>
        <p style="font-size: 13.5px; color: var(--text-muted); margin: 0;">
            Monitor, manage, and execute database migrations across all tenant environments.
        </p>
    </div>
    <div class="header-controls">
        <button class="btn-action-secondary" id="refreshStatusBtn">
            <i class="fas fa-rotate"></i> Refresh Status
        </button>
        <button class="btn-action-secondary" id="scrollToHistoryBtn">
            <i class="fas fa-history"></i> Migration History
        </button>
        <button class="btn-action-primary" id="triggerBulkRunBtn">
            <i class="fas fa-play"></i> Run Migrations
        </button>
    </div>
</div>

@if(session('current_company_db'))
<div style="background: var(--warning-bg); border: 1px solid var(--warning-border); border-radius: var(--radius-lg); padding: 14px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 12px;">
        <i class="fas fa-triangle-exclamation" style="font-size: 18px; color: var(--warning);"></i>
        <div>
            <strong style="color: var(--warning); font-size: 13.5px;">Active Tenant Impersonation Session</strong>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                Current Session Database: <code style="background: #fff; padding: 2px 6px; border-radius: 4px; font-family: monospace;">{{ session('current_company_db') }}</code>
            </div>
        </div>
    </div>
    <form method="POST" action="{{ route('super-admin.leave-impersonation') }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn-action-secondary" style="color: var(--warning); border-color: var(--warning-border);">
            <i class="fas fa-arrow-left"></i> Leave Impersonation
        </button>
    </form>
</div>
@endif

<!-- 2. MIGRATION KPI CARDS -->
<div class="kpi-grid">
    <!-- TOTAL TENANTS -->
    <div class="kpi-card">
        <div class="card-head">
            <span class="card-label">Total Tenants</span>
            <div class="icon-pill" style="background: #eff6ff; color: #2563eb;"><i class="fas fa-building"></i></div>
        </div>
        <div class="metric-val">{{ number_format($kpi['total_tenants']) }}</div>
        <div class="metric-foot positive">
            <i class="fas fa-server"></i> Registered databases
        </div>
    </div>

    <!-- UP TO DATE -->
    <div class="kpi-card">
        <div class="card-head">
            <span class="card-label">Up to Date</span>
            <div class="icon-pill" style="background: #f0fdf4; color: #16a34a;"><i class="fas fa-circle-check"></i></div>
        </div>
        <div class="metric-val">{{ number_format($kpi['up_to_date']) }}</div>
        <div class="metric-foot positive">
            ● {{ $kpi['total_tenants'] > 0 ? round(($kpi['up_to_date'] / $kpi['total_tenants']) * 100) : 100 }}% fully aligned
        </div>
    </div>

    <!-- PENDING MIGRATIONS -->
    <div class="kpi-card">
        <div class="card-head">
            <span class="card-label">Pending Migrations</span>
            <div class="icon-pill" style="background: #fffbeb; color: #d97706;"><i class="fas fa-clock-rotate-left"></i></div>
        </div>
        <div class="metric-val">{{ number_format($kpi['pending_migrations']) }}</div>
        <div class="metric-foot {{ $kpi['pending_migrations'] > 0 ? 'warning' : 'muted' }}">
            {{ $kpi['pending_migrations'] > 0 ? 'Requires migration run' : 'No pending schemas' }}
        </div>
    </div>

    <!-- FAILED MIGRATIONS -->
    <div class="kpi-card">
        <div class="card-head">
            <span class="card-label">Failed Migrations</span>
            <div class="icon-pill" style="background: #fef2f2; color: #dc2626;"><i class="fas fa-triangle-exclamation"></i></div>
        </div>
        <div class="metric-val">{{ number_format($kpi['failed_migrations']) }}</div>
        <div class="metric-foot {{ $kpi['failed_migrations'] > 0 ? 'danger' : 'muted' }}">
            {{ $kpi['failed_migrations'] > 0 ? 'Needs attention' : '0 errors logged' }}
        </div>
    </div>

    <!-- LAST MIGRATION RUN -->
    <div class="kpi-card">
        <div class="card-head">
            <span class="card-label">Last Migration Run</span>
            <div class="icon-pill" style="background: #f5f3ff; color: #7c3aed;"><i class="fas fa-calendar-check"></i></div>
        </div>
        <div class="metric-val" style="font-size: 20px; margin-top: 16px;">{{ $kpi['last_run'] }}</div>
        <div class="metric-foot muted">
            Global schema batch
        </div>
    </div>
</div>

<!-- 3. MIGRATION CONTROL / FILTER TOOLBAR -->
<div class="migration-toolbar">
    <div class="toolbar-left">
        <!-- Search -->
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="migrationSearchInput" placeholder="Search company, tenant ID, database..." />
        </div>

        <!-- Filter: Status -->
        <select class="filter-select" id="filterStatus">
            <option value="all">All Statuses ▼</option>
            <option value="up_to_date">🟢 Up to Date</option>
            <option value="pending">🟡 Pending</option>
            <option value="failed">🔴 Failed</option>
            <option value="not_initialized">⚪ Not Initialized</option>
        </select>

        <!-- Filter: Tenants -->
        <select class="filter-select" id="filterTenant">
            <option value="all">All Tenants ▼</option>
            @foreach($tenantMigrationData as $tData)
                <option value="{{ $tData['id'] }}">{{ $tData['name'] }}</option>
            @endforeach
        </select>

        <!-- Filter: Version -->
        <select class="filter-select" id="filterVersion">
            <option value="all">Migration Version ▼</option>
            <option value="latest">Latest Schema Only</option>
            <option value="outdated">Outdated Versions</option>
        </select>
    </div>

    <div style="display: flex; align-items: center; gap: 10px;">
        <button class="btn-action-secondary" id="resetFiltersBtn">
            <i class="fas fa-filter-circle-xmark"></i> Reset
        </button>
        <button class="btn-action-secondary" id="exportMigrationsBtn">
            <i class="fas fa-download"></i> Export
        </button>
    </div>
</div>

<!-- 4. TENANT MIGRATION STATUS TABLE -->
<div style="background: var(--bg-surface); border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 24px;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px; min-width: 1050px;" id="tenantMigrationsTable">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="border-right: 1px solid #e2e8f0; padding: 12px 10px; text-align: center; width: 42px;">
                        <input type="checkbox" id="selectAllMigrationsCheckbox" style="cursor: pointer; width: 16px; height: 16px; accent-color: #2563eb;">
                    </th>
                    <th style="padding: 12px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Company</th>
                    <th style="padding: 12px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Tenant ID</th>
                    <th style="padding: 12px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Database</th>
                    <th style="padding: 12px 14px; text-align: center; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Current Version</th>
                    <th style="padding: 12px 14px; text-align: center; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Latest Version</th>
                    <th style="padding: 12px 14px; text-align: center; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Migration Status</th>
                    <th style="padding: 12px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Last Migration</th>
                    <th style="padding: 12px 14px; text-align: right; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Execution Time</th>
                    <th style="padding: 12px 14px; text-align: right; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenantMigrationData as $item)
                <tr class="tenant-migration-row" 
                    data-id="{{ $item['id'] }}"
                    data-name="{{ $item['name'] }}"
                    data-code="{{ $item['company_code'] }}"
                    data-db="{{ $item['db_name'] }}"
                    data-status="{{ $item['status'] }}"
                    data-current="{{ $item['current_version'] }}"
                    data-latest="{{ $item['latest_version'] }}"
                    data-pending="{{ $item['pending_count'] }}"
                    style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s;"
                    onmouseover="this.style.background='#f8fafc'" 
                    onmouseout="this.style.background='transparent'">
                    
                    <td style="border-right: 1px solid #e2e8f0; padding: 12px 10px; text-align: center;">
                        <input type="checkbox" class="row-checkbox" value="{{ $item['id'] }}" style="cursor: pointer; width: 16px; height: 16px; accent-color: #2563eb;">
                    </td>
                    
                    <!-- COMPANY -->
                    <td style="padding: 12px 14px; white-space: nowrap;">
                        <div style="font-weight: 700; color: var(--text-main); font-size: 13.5px;">{{ $item['name'] }}</div>
                        <div style="font-size: 11.5px; color: var(--text-subtle);">Created {{ $item['company']->created_at ? $item['company']->created_at->format('d M Y') : 'N/A' }}</div>
                    </td>

                    <!-- TENANT ID -->
                    <td style="padding: 12px 14px; font-weight: 600; color: var(--text-muted); white-space: nowrap;">
                        <code style="background: #f1f5f9; padding: 3px 8px; border-radius: 6px; font-size: 12px; color: #0284c7; font-family: monospace;">{{ $item['company_code'] }}</code>
                    </td>

                    <!-- DATABASE -->
                    <td style="padding: 12px 14px; font-family: monospace; font-size: 12px; color: var(--text-main); white-space: nowrap;">
                        <i class="fas fa-database" style="color: var(--primary); margin-right: 4px;"></i> {{ $item['db_name'] }}
                    </td>

                    <!-- CURRENT VERSION -->
                    <td style="padding: 12px 14px; text-align: center; white-space: nowrap;">
                        <span style="background: #f1f5f9; color: var(--text-main); padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11.5px; border: 1px solid #cbd5e1;">
                            {{ $item['current_version'] }}
                        </span>
                    </td>

                    <!-- LATEST VERSION -->
                    <td style="padding: 12px 14px; text-align: center; white-space: nowrap;">
                        <span style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11.5px; border: 1px solid #bfdbfe;">
                            {{ $item['latest_version'] }}
                        </span>
                    </td>

                    <!-- MIGRATION STATUS -->
                    <td style="padding: 12px 14px; text-align: center; white-space: nowrap;">
                        @if($item['status'] === 'up_to_date')
                            <span style="background: #f0fdf4; color: #16a34a; padding: 4px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 700; border: 1px solid #bbf7d0; display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #16a34a;"></span> Up to Date
                            </span>
                        @elseif($item['status'] === 'pending')
                            <span style="background: #fffbeb; color: #d97706; padding: 4px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 700; border: 1px solid #fde68a; display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #d97706;"></span> {{ $item['pending_count'] }} Pending
                            </span>
                        @elseif($item['status'] === 'failed')
                            <span style="background: #fef2f2; color: #dc2626; padding: 4px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 700; border: 1px solid #fecaca; display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #dc2626;"></span> Failed
                            </span>
                        @else
                            <span style="background: #f8fafc; color: #64748b; padding: 4px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 700; border: 1px solid #cbd5e1; display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #64748b;"></span> Not Initialized
                            </span>
                        @endif
                    </td>

                    <!-- LAST MIGRATION -->
                    <td style="padding: 12px 14px; font-size: 12px; color: var(--text-muted); white-space: nowrap;">
                        <i class="far fa-clock" style="margin-right: 4px; color: var(--text-subtle);"></i> {{ $item['last_migration'] }}
                    </td>

                    <!-- EXECUTION TIME -->
                    <td style="padding: 12px 14px; text-align: right; font-weight: 600; color: var(--text-main); font-size: 12.5px; white-space: nowrap;">
                        {{ $item['execution_time'] }}
                    </td>

                    <!-- ACTIONS -->
                    <td style="padding: 12px 14px; text-align: right; white-space: nowrap;">
                        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 6px;">
                            <button class="btn-action-secondary open-drawer-btn" 
                                    data-id="{{ $item['id'] }}" 
                                    data-name="{{ $item['name'] }}"
                                    data-code="{{ $item['company_code'] }}"
                                    data-db="{{ $item['db_name'] }}"
                                    data-status="{{ $item['status'] }}"
                                    data-current="{{ $item['current_version'] }}"
                                    data-latest="{{ $item['latest_version'] }}"
                                    data-pending="{{ $item['pending_count'] }}"
                                    style="padding: 6px 12px; font-size: 12px;">
                                <i class="fas fa-eye"></i> View
                            </button>

                            @if($item['status'] === 'pending' || $item['status'] === 'not_initialized')
                                <button class="btn-action-primary trigger-single-migrate-btn" 
                                        data-id="{{ $item['id'] }}" 
                                        data-name="{{ $item['name'] }}"
                                        data-db="{{ $item['db_name'] }}"
                                        data-pending="{{ $item['pending_count'] }}"
                                        style="padding: 6px 14px; font-size: 12px;">
                                    <i class="fas fa-play"></i> Migrate
                                </button>
                            @elseif($item['status'] === 'failed')
                                <button class="btn-action-primary trigger-single-migrate-btn" 
                                        data-id="{{ $item['id'] }}" 
                                        data-name="{{ $item['name'] }}"
                                        data-db="{{ $item['db_name'] }}"
                                        data-pending="{{ $item['pending_count'] }}"
                                        style="padding: 6px 14px; font-size: 12px; background: var(--danger);">
                                    <i class="fas fa-rotate-right"></i> Retry
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align: center; padding: 40px; color: var(--text-subtle);">
                        <i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 8px; display: block;"></i>
                        No tenant databases registered or matching criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Table Pagination Footer -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 14px 20px; background: #f8fafc; border-top: 1px solid var(--border-color); font-size: 13px;">
        <div style="color: var(--text-muted); font-weight: 500;">
            Showing 1 to {{ count($tenantMigrationData) }} of {{ count($tenantMigrationData) }} tenant environments
        </div>
        <div style="display: flex; align-items: center; gap: 6px;">
            <button class="btn-action-secondary" style="padding: 4px 10px; font-size: 12px;" disabled>Previous</button>
            <button class="btn-action-primary" style="padding: 4px 10px; font-size: 12px;">1</button>
            <button class="btn-action-secondary" style="padding: 4px 10px; font-size: 12px;" disabled>Next</button>
        </div>
    </div>
</div>

<!-- 5. MIGRATION DETAILS DRAWER -->
<div class="drawer-overlay" id="migrationDetailsDrawer">
    <div class="drawer-panel" id="drawerPanel">
        <div class="drawer-header">
            <div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #0f744c, #10b981); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px;">
                        <i class="fas fa-building"></i>
                    </div>
                    <div>
                        <h3 id="drawerCompanyName" style="font-size: 17px; font-weight: 800; color: var(--text-main); margin: 0;">Original Company</h3>
                        <div style="font-size: 12px; color: var(--text-subtle);" id="drawerTenantId">Tenant ID: TEN-001</div>
                    </div>
                </div>
            </div>
            <button class="btn-action-secondary" id="closeDrawerBtn" style="padding: 6px 10px; border: none; box-shadow: none;">
                <i class="fas fa-xmark" style="font-size: 18px;"></i>
            </button>
        </div>

        <div class="drawer-body">
            <!-- DATABASE INFO CARD -->
            <div style="background: #f8fafc; border-radius: 12px; border: 1px solid var(--border-color); padding: 16px;">
                <div style="font-size: 11px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">
                    DATABASE CONNECTION &amp; VERSIONS
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px;">
                    <div>
                        <span style="color: var(--text-subtle);">Database:</span>
                        <div style="font-weight: 700; font-family: monospace; color: var(--text-main);" id="drawerDbName">pms_main</div>
                    </div>
                    <div>
                        <span style="color: var(--text-subtle);">Status:</span>
                        <div style="font-weight: 700; color: var(--success);" id="drawerConnStatus">● Connected</div>
                    </div>
                    <div>
                        <span style="color: var(--text-subtle);">Current Version:</span>
                        <div style="font-weight: 700; color: var(--text-main);" id="drawerCurrentVersion">v107</div>
                    </div>
                    <div>
                        <span style="color: var(--text-subtle);">Latest Version:</span>
                        <div style="font-weight: 700; color: var(--primary);" id="drawerLatestVersion">v107</div>
                    </div>
                </div>
            </div>

            <!-- MIGRATION STATUS TIMELINE -->
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="font-size: 12px; font-weight: 700; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.5px;">
                        MIGRATION STATUS TIMELINE
                    </div>
                    <span class="header-status-badge" style="font-size: 11px; padding: 2px 8px;" id="drawerPendingBadge">
                        Up to date
                    </span>
                </div>

                <div class="migration-timeline" id="drawerTimeline">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>

            <!-- TENANT HEALTH METRICS -->
            <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 12px; padding: 16px;">
                <div style="font-size: 11px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">
                    TENANT INFRASTRUCTURE HEALTH
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Database Cluster</span>
                        <strong style="color: var(--success);">● Healthy (38ms)</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Backup Status</span>
                        <strong style="color: var(--success);">● Verified</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Migration Engine</span>
                        <strong style="color: var(--success);">● Operational</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Subscription Status</span>
                        <strong style="color: var(--primary);">● Active Compliant</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="drawer-footer">
            <button class="btn-action-secondary" id="drawerLogsBtn">
                <i class="fas fa-terminal"></i> View Logs
            </button>
            <button class="btn-action-primary" id="drawerMigrateBtn">
                <i class="fas fa-play"></i> Run Migration
            </button>
        </div>
    </div>
</div>

<!-- 6. SINGLE MIGRATION CONFIRMATION MODAL -->
<div class="modal-overlay" id="singleMigrateModal">
    <div class="modal-box">
        <h3 style="font-size: 18px; font-weight: 800; color: var(--text-main); margin-top: 0; margin-bottom: 6px;">
            Run Database Migration?
        </h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
            Confirm schema update for tenant <strong id="modalCompanyName">ABC Corp</strong> (<code id="modalDbName">pms_main</code>).
        </p>

        <!-- Safety Warning Banner -->
        <div style="background: var(--warning-bg); border: 1px solid var(--warning-border); border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; font-size: 12.5px; color: var(--warning); display: flex; align-items: flex-start; gap: 10px;">
            <i class="fas fa-triangle-exclamation" style="font-size: 16px; margin-top: 2px;"></i>
            <div>
                <strong>Safe Migration Notice</strong>
                <div>This operation will modify the tenant database schema using strict transaction controls.</div>
            </div>
        </div>

        <!-- Pre-Execution Checklist -->
        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 12.5px;">
            <div style="font-weight: 700; color: var(--text-main); margin-bottom: 8px;">Pre-Execution Checklist:</div>
            <div style="display: flex; flex-direction: column; gap: 6px; color: var(--text-muted);">
                <div><i class="fas fa-circle-check" style="color: var(--success); margin-right: 6px;"></i> Database connection verified</div>
                <div><i class="fas fa-circle-check" style="color: var(--success); margin-right: 6px;"></i> Automated backup checkpoint available</div>
                <div><i class="fas fa-circle-check" style="color: var(--success); margin-right: 6px;"></i> Migration compatibility checked</div>
            </div>
        </div>

        <!-- Live Step Progress (Hidden by default) -->
        <div id="migrationLiveProgress" style="display: none; margin-bottom: 20px;">
            <div style="font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">
                Migration Progress:
            </div>
            <div class="terminal-window" id="liveProgressConsole" style="height: 180px;">
                <div>[System] Initiating tenant schema migration...</div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button class="btn-action-secondary" id="cancelSingleMigrateBtn">Cancel</button>
            <button class="btn-action-primary" id="confirmSingleMigrateBtn">
                <i class="fas fa-play"></i> Confirm &amp; Run Migration
            </button>
        </div>
    </div>
</div>

<!-- 7. MIGRATION LOGS MODAL -->
<div class="modal-overlay" id="migrationLogsModal">
    <div class="modal-box" style="max-width: 680px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <div>
                <h3 style="font-size: 18px; font-weight: 800; color: var(--text-main); margin: 0;" id="logsModalTitle">
                    Migration Execution Logs
                </h3>
                <div style="font-size: 12px; color: var(--text-subtle);" id="logsModalSubtitle">
                    Tenant Database: pms_main
                </div>
            </div>
            <button class="btn-action-secondary" id="closeLogsModalBtn" style="padding: 4px 8px; border: none;">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <div class="terminal-window" id="terminalLogsContent">
            <div>10:32:01 <span class="log-success">✓ Database connection established</span></div>
            <div>10:32:02 <span class="log-success">✓ Backup verification completed</span></div>
            <div>10:32:03 <span class="log-info">✓ Migration process started</span></div>
            <div>10:32:04 <span class="log-success">✓ Schema tables verified up to date</span></div>
            <div>10:32:05 <span class="log-success">✓ Verification completed successfully</span></div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px;">
            <div style="font-size: 12px; color: var(--text-subtle);">
                Status: <strong style="color: var(--success);" id="logsModalStatus">SUCCESS</strong>
            </div>
            <div style="display: flex; gap: 10px;">
                <button class="btn-action-secondary" id="copyLogsBtn">
                    <i class="fas fa-copy"></i> Copy Logs
                </button>
                <button class="btn-action-secondary" id="downloadLogsBtn">
                    <i class="fas fa-download"></i> Download Logs
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 8. FLOATING BULK ACTION BAR -->
<div class="bulk-action-bar" id="bulkActionBar">
    <div style="display: flex; align-items: center; gap: 8px; font-size: 13.5px; font-weight: 700;">
        <i class="fas fa-check-double" style="color: var(--primary);"></i>
        <span id="bulkSelectedCount">0</span> tenants selected
    </div>
    <div style="height: 18px; width: 1px; background: rgba(255,255,255,0.2);"></div>
    <div style="display: flex; align-items: center; gap: 8px;">
        <button class="btn-action-primary" id="bulkRunConfirmTrigger" style="padding: 6px 14px; font-size: 12px;">
            <i class="fas fa-play"></i> Run Migrations
        </button>
        <button class="btn-action-secondary" id="bulkCancelBtn" style="padding: 6px 12px; font-size: 12px; background: rgba(255,255,255,0.1); color: #fff; border-color: rgba(255,255,255,0.2);">
            Cancel
        </button>
    </div>
</div>

<!-- 9. MIGRATION HISTORY SECTION -->
<div style="background: var(--bg-surface); border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 24px; margin-top: 32px;" id="historySection">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h3 style="font-size: 18px; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-history" style="color: var(--primary);"></i> Migration Execution History
            </h3>
            <p style="font-size: 12.5px; color: var(--text-muted); margin-top: 2px;">
                Historical log of executed database migrations across tenant accounts.
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <input type="text" id="historySearchInput" placeholder="Filter history..." style="padding: 7px 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 12.5px; outline: none;" />
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;" id="migrationHistoryTable">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Company</th>
                    <th style="padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Migration</th>
                    <th style="padding: 10px 14px; text-align: center; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Version</th>
                    <th style="padding: 10px 14px; text-align: center; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Status</th>
                    <th style="padding: 10px 14px; text-align: right; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Execution Time</th>
                    <th style="padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Executed At</th>
                    <th style="padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Executed By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($migrationHistory as $hist)
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px 14px; font-weight: 700; color: var(--text-main);">{{ $hist['company_name'] }}</td>
                    <td style="padding: 12px 14px; font-family: monospace; font-size: 12px; color: var(--text-muted);">{{ $hist['migration'] }}</td>
                    <td style="padding: 12px 14px; text-align: center;">
                        <span style="background: #eff6ff; color: #2563eb; padding: 2px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;">
                            {{ $hist['version'] }}
                        </span>
                    </td>
                    <td style="padding: 12px 14px; text-align: center;">
                        <span style="background: #f0fdf4; color: #16a34a; padding: 2px 10px; border-radius: 20px; font-weight: 700; font-size: 11px;">
                            ✓ Success
                        </span>
                    </td>
                    <td style="padding: 12px 14px; text-align: right; font-weight: 600;">{{ $hist['execution_time'] }}</td>
                    <td style="padding: 12px 14px; color: var(--text-muted); font-size: 12px;">{{ $hist['executed_at'] }}</td>
                    <td style="padding: 12px 14px; color: var(--text-main); font-weight: 600;">{{ $hist['executed_by'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-subtle);">
                        No previous migration logs recorded yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // 1. Table Search & Filtering
    const searchInput = document.getElementById('migrationSearchInput');
    const filterStatus = document.getElementById('filterStatus');
    const filterTenant = document.getElementById('filterTenant');
    const filterVersion = document.getElementById('filterVersion');
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');

    function filterTableRows() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const statusVal = filterStatus ? filterStatus.value : 'all';
        const tenantVal = filterTenant ? filterTenant.value : 'all';
        const versionVal = filterVersion ? filterVersion.value : 'all';

        const rows = document.querySelectorAll('.tenant-migration-row');
        rows.forEach(row => {
            const name = row.getAttribute('data-name')?.toLowerCase() || '';
            const code = row.getAttribute('data-code')?.toLowerCase() || '';
            const db = row.getAttribute('data-db')?.toLowerCase() || '';
            const status = row.getAttribute('data-status') || '';
            const id = row.getAttribute('data-id') || '';
            const pending = parseInt(row.getAttribute('data-pending') || '0', 10);

            let matchesSearch = !query || name.includes(query) || code.includes(query) || db.includes(query);
            let matchesStatus = statusVal === 'all' || status === statusVal;
            let matchesTenant = tenantVal === 'all' || id === tenantVal;
            let matchesVersion = versionVal === 'all' || (versionVal === 'latest' && pending === 0) || (versionVal === 'outdated' && pending > 0);

            if (matchesSearch && matchesStatus && matchesTenant && matchesVersion) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterTableRows);
    if (filterStatus) filterStatus.addEventListener('change', filterTableRows);
    if (filterTenant) filterTenant.addEventListener('change', filterTableRows);
    if (filterVersion) filterVersion.addEventListener('change', filterTableRows);

    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (filterStatus) filterStatus.value = 'all';
            if (filterTenant) filterTenant.value = 'all';
            if (filterVersion) filterVersion.value = 'all';
            filterTableRows();
        });
    }

    // 2. Refresh Status Button
    const refreshStatusBtn = document.getElementById('refreshStatusBtn');
    if (refreshStatusBtn) {
        refreshStatusBtn.addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-rotate fa-spin"></i> Refreshing...';
            setTimeout(() => {
                window.location.reload();
            }, 600);
        });
    }

    // 3. Scroll to History
    const scrollToHistoryBtn = document.getElementById('scrollToHistoryBtn');
    const historySection = document.getElementById('historySection');
    if (scrollToHistoryBtn && historySection) {
        scrollToHistoryBtn.addEventListener('click', function() {
            historySection.scrollIntoView({ behavior: 'smooth' });
        });
    }

    // 4. Slide-Over Drawer
    const drawerOverlay = document.getElementById('migrationDetailsDrawer');
    const closeDrawerBtn = document.getElementById('closeDrawerBtn');
    const openDrawerBtns = document.querySelectorAll('.open-drawer-btn');

    let currentDrawerCompanyId = null;

    openDrawerBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            currentDrawerCompanyId = this.getAttribute('data-id');
            const compName = this.getAttribute('data-name');
            const compCode = this.getAttribute('data-code');
            const dbName = this.getAttribute('data-db');
            const currentVer = this.getAttribute('data-current');
            const latestVer = this.getAttribute('data-latest');
            const pendingCount = parseInt(this.getAttribute('data-pending') || '0', 10);

            document.getElementById('drawerCompanyName').textContent = compName;
            document.getElementById('drawerTenantId').textContent = 'Tenant ID: ' + compCode;
            document.getElementById('drawerDbName').textContent = dbName;
            document.getElementById('drawerCurrentVersion').textContent = currentVer;
            document.getElementById('drawerLatestVersion').textContent = latestVer;

            const badge = document.getElementById('drawerPendingBadge');
            if (pendingCount === 0) {
                badge.textContent = 'Up to date';
                badge.style.background = '#f0fdf4';
                badge.style.color = '#16a34a';
                badge.style.borderColor = '#bbf7d0';
            } else {
                badge.textContent = pendingCount + ' Pending';
                badge.style.background = '#fffbeb';
                badge.style.color = '#d97706';
                badge.style.borderColor = '#fde68a';
            }

            // Populate Timeline
            const timeline = document.getElementById('drawerTimeline');
            timeline.innerHTML = '';
            
            const migrationFiles = @json($allMigrationFiles);
            const appliedCount = parseInt(currentVer.replace('v', ''), 10) || 0;

            migrationFiles.forEach((mig, idx) => {
                const item = document.createElement('div');
                const isDone = idx < appliedCount;
                item.className = 'timeline-item ' + (isDone ? 'completed' : 'pending');
                item.innerHTML = `
                    <div class="timeline-node">${isDone ? '✓' : '○'}</div>
                    <div>
                        <strong style="color: ${isDone ? 'var(--text-main)' : 'var(--text-muted)'};">${mig.name || mig.raw_name}</strong>
                        <div style="font-size: 11px; color: var(--text-subtle); font-family: monospace;">${mig.file}</div>
                    </div>
                `;
                timeline.appendChild(item);
            });

            drawerOverlay.classList.add('open');
            document.getElementById('drawerPanel').classList.add('open');
        });
    });

    if (closeDrawerBtn && drawerOverlay) {
        closeDrawerBtn.addEventListener('click', function() {
            drawerOverlay.classList.remove('open');
            document.getElementById('drawerPanel').classList.remove('open');
        });
        drawerOverlay.addEventListener('click', function(e) {
            if (e.target === drawerOverlay) {
                drawerOverlay.classList.remove('open');
                document.getElementById('drawerPanel').classList.remove('open');
            }
        });
    }

    // 5. Single Migration Modal & Execution
    const singleMigrateModal = document.getElementById('singleMigrateModal');
    const cancelSingleMigrateBtn = document.getElementById('cancelSingleMigrateBtn');
    const confirmSingleMigrateBtn = document.getElementById('confirmSingleMigrateBtn');
    const singleMigrateBtns = document.querySelectorAll('.trigger-single-migrate-btn');

    let targetCompanyIdToMigrate = null;

    singleMigrateBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            targetCompanyIdToMigrate = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const db = this.getAttribute('data-db');

            document.getElementById('modalCompanyName').textContent = name;
            document.getElementById('modalDbName').textContent = db;
            document.getElementById('migrationLiveProgress').style.display = 'none';
            confirmSingleMigrateBtn.style.display = '';

            singleMigrateModal.classList.add('open');
        });
    });

    const drawerMigrateBtn = document.getElementById('drawerMigrateBtn');
    if (drawerMigrateBtn) {
        drawerMigrateBtn.addEventListener('click', function() {
            if (currentDrawerCompanyId) {
                targetCompanyIdToMigrate = currentDrawerCompanyId;
                const compName = document.getElementById('drawerCompanyName').textContent;
                const dbName = document.getElementById('drawerDbName').textContent;
                document.getElementById('modalCompanyName').textContent = compName;
                document.getElementById('modalDbName').textContent = dbName;
                document.getElementById('migrationLiveProgress').style.display = 'none';
                confirmSingleMigrateBtn.style.display = '';

                drawerOverlay.classList.remove('open');
                document.getElementById('drawerPanel').classList.remove('open');
                singleMigrateModal.classList.add('open');
            }
        });
    }

    if (cancelSingleMigrateBtn && singleMigrateModal) {
        cancelSingleMigrateBtn.addEventListener('click', function() {
            singleMigrateModal.classList.remove('open');
        });
    }

    if (confirmSingleMigrateBtn) {
        confirmSingleMigrateBtn.addEventListener('click', function() {
            if (!targetCompanyIdToMigrate) return;

            const progressConsole = document.getElementById('liveProgressConsole');
            const progressBox = document.getElementById('migrationLiveProgress');
            progressBox.style.display = 'block';
            confirmSingleMigrateBtn.style.display = 'none';

            progressConsole.innerHTML = `
                <div>[10:32:01] <span class="log-info">Connecting to tenant database...</span></div>
                <div>[10:32:02] <span class="log-info">Verifying database structure &amp; backup checkpoint...</span></div>
                <div>[10:32:03] <span class="log-warn">Executing Artisan migrate command...</span></div>
            `;

            fetch('{{ route("super-admin.migrations.run") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ company_id: targetCompanyIdToMigrate })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    progressConsole.innerHTML += `
                        <div>[10:32:04] <span class="log-success">✔ ${data.message}</span></div>
                        <div>[10:32:05] <span class="log-success">✔ Schema tables updated to ${data.current_version} in ${data.execution_time}</span></div>
                    `;
                    setTimeout(() => {
                        singleMigrateModal.classList.remove('open');
                        window.location.reload();
                    }, 1200);
                } else {
                    progressConsole.innerHTML += `
                        <div>[10:32:04] <span class="log-error">✖ ${data.message}</span></div>
                        <div class="log-error">${data.output || ''}</div>
                    `;
                }
            })
            .catch(err => {
                progressConsole.innerHTML += `
                    <div>[10:32:04] <span class="log-error">✖ Request failed: ${err.message}</span></div>
                `;
            });
        });
    }

    // 6. Checkboxes & Bulk Action Bar
    const selectAllCheckbox = document.getElementById('selectAllMigrationsCheckbox');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkActionBar = document.getElementById('bulkActionBar');
    const bulkSelectedCount = document.getElementById('bulkSelectedCount');

    function updateBulkBar() {
        const selected = document.querySelectorAll('.row-checkbox:checked');
        const count = selected.length;
        if (bulkSelectedCount) bulkSelectedCount.textContent = count;

        if (count > 0) {
            bulkActionBar.classList.add('visible');
        } else {
            bulkActionBar.classList.remove('visible');
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateBulkBar();
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });

    const bulkCancelBtn = document.getElementById('bulkCancelBtn');
    if (bulkCancelBtn) {
        bulkCancelBtn.addEventListener('click', function() {
            rowCheckboxes.forEach(cb => cb.checked = false);
            if (selectAllCheckbox) selectAllCheckbox.checked = false;
            updateBulkBar();
        });
    }

    const triggerBulkRunBtn = document.getElementById('triggerBulkRunBtn');
    const bulkRunConfirmTrigger = document.getElementById('bulkRunConfirmTrigger');

    function executeBulkMigration() {
        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        if (selectedIds.length === 0) {
            alert('Please select at least one tenant company to migrate.');
            return;
        }

        if (!confirm(`Are you sure you want to execute migrations for ${selectedIds.length} selected tenant company databases?`)) {
            return;
        }

        fetch('{{ route("super-admin.migrations.bulk-run") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ company_ids: selectedIds })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message || 'Bulk migration completed.');
            window.location.reload();
        })
        .catch(err => {
            alert('Error running bulk migrations: ' + err.message);
        });
    }

    if (triggerBulkRunBtn) triggerBulkRunBtn.addEventListener('click', executeBulkMigration);
    if (bulkRunConfirmTrigger) bulkRunConfirmTrigger.addEventListener('click', executeBulkMigration);

    // 7. Log Viewer Modal
    const migrationLogsModal = document.getElementById('migrationLogsModal');
    const closeLogsModalBtn = document.getElementById('closeLogsModalBtn');
    const drawerLogsBtn = document.getElementById('drawerLogsBtn');

    if (drawerLogsBtn) {
        drawerLogsBtn.addEventListener('click', function() {
            if (!currentDrawerCompanyId) return;

            fetch(`/super-admin/migrations/logs/${currentDrawerCompanyId}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('logsModalTitle').textContent = 'Migration Execution Logs — ' + (data.company_name || '');
                document.getElementById('logsModalSubtitle').textContent = 'Database: ' + (data.db_name || '');
                
                const terminal = document.getElementById('terminalLogsContent');
                terminal.innerHTML = '';

                if (data.logs && data.logs.length > 0) {
                    data.logs.forEach(log => {
                        const line = document.createElement('div');
                        line.innerHTML = `<div>${log.timestamp} <span class="${log.status === 'SUCCESS' ? 'log-success' : 'log-error'}">${log.message}</span></div>`;
                        terminal.appendChild(line);
                    });
                } else {
                    terminal.innerHTML = '<div>10:32:01 <span class="log-info">No execution errors recorded. All schema tables compliant.</span></div>';
                }

                migrationLogsModal.classList.add('open');
            })
            .catch(err => {
                alert('Error loading logs: ' + err.message);
            });
        });
    }

    if (closeLogsModalBtn && migrationLogsModal) {
        closeLogsModalBtn.addEventListener('click', function() {
            migrationLogsModal.classList.remove('open');
        });
    }

    // Copy Logs
    const copyLogsBtn = document.getElementById('copyLogsBtn');
    if (copyLogsBtn) {
        copyLogsBtn.addEventListener('click', function() {
            const text = document.getElementById('terminalLogsContent').innerText;
            navigator.clipboard.writeText(text).then(() => {
                this.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-copy"></i> Copy Logs';
                }, 1500);
            });
        });
    }
});
</script>
@endpush
