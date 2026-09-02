@extends('layouts.superadmin')

@section('title', 'Super Admin · Plans Catalog & Subscription Control Center')
@section('page_title', 'Plans Catalog')
@section('page_subtitle', 'Manage subscription plans, pricing, limits, features, and availability across the platform.')

@section('content')
<style>
    /* ============================================================
       PLANS CATALOG DESIGN SYSTEM TOKENS
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

        /* Plan Color Identifiers (STRICTLY 4 TIERS) */
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

    /* Page Header */
    .plans-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }
    .plans-breadcrumb {
        font-size: 13px;
        font-weight: 500;
        color: var(--text-subtle);
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .plans-breadcrumb span { color: var(--text-muted); }
    .plans-title {
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--text-main);
        line-height: 1.2;
    }
    .plans-subtitle {
        font-size: 14px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    /* Header Actions Bar */
    .header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .search-input-wrap {
        position: relative;
        min-width: 240px;
    }
    .search-input-wrap i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-subtle);
        font-size: 13px;
    }
    .search-input-wrap input {
        width: 100%;
        padding: 9px 12px 9px 36px;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        font-size: 13px;
        background: #ffffff;
        outline: none;
        transition: all var(--transition-fast);
        color: var(--text-main);
        font-family: inherit;
    }
    .search-input-wrap input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-ring);
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
    .btn-xs-custom {
        height: 30px;
        padding: 0 12px;
        font-size: 12px;
        border-radius: 8px;
    }

    /* Summary KPI Grid */
    .plans-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
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
    .kpi-card .head {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .kpi-card .lbl {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--text-subtle);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .kpi-card .val {
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--text-main);
        margin-top: 8px;
        line-height: 1.1;
    }
    .kpi-card .sub {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 500;
        margin-top: 6px;
    }

    /* 4 Plan Cards Grid (STRICTLY FREE, GOLD, PLATINUM, DIAMOND) */
    .plans-cards-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 32px;
        width: 100%;
    }
    @media (max-width: 1360px) {
        .plans-cards-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 640px) {
        .plans-cards-grid { grid-template-columns: 1fr; }
    }

    .plan-card-item {
        background: var(--bg-surface);
        border-radius: var(--radius-lg);
        border: 2px solid var(--border-color);
        padding: 20px 16px;
        display: flex;
        flex-direction: column;
        position: relative;
        box-shadow: var(--shadow-sm);
        transition: all var(--transition-fast);
        min-width: 0;
        overflow: hidden;
    }
    .plan-card-item:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-3px);
    }

    /* Plan Specific Theme Variations */
    .plan-card-item.theme-free { border-color: var(--plan-free-border); }
    .plan-card-item.theme-gold { border-color: var(--plan-gold-border); background: linear-gradient(180deg, #ffffff 0%, #fffdf5 100%); }
    .plan-card-item.theme-platinum { border-color: var(--plan-platinum-border); background: linear-gradient(180deg, #ffffff 0%, #f7fcff 100%); }
    .plan-card-item.theme-diamond { border-color: var(--plan-diamond-border); background: linear-gradient(180deg, #ffffff 0%, #faf8ff 100%); }

    .popular-ribbon {
        position: absolute;
        top: 10px;
        right: 10px;
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: #ffffff;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 3px 9px;
        border-radius: 999px;
        box-shadow: 0 4px 10px rgba(217, 119, 6, 0.35);
        z-index: 10;
        pointer-events: none;
    }

    .plan-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .plan-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .theme-free .plan-icon-box { background: var(--plan-free-bg); color: var(--plan-free-accent); }
    .theme-gold .plan-icon-box { background: var(--plan-gold-bg); color: var(--plan-gold-accent); }
    .theme-platinum .plan-icon-box { background: var(--plan-platinum-bg); color: var(--plan-platinum-accent); }
    .theme-diamond .plan-icon-box { background: var(--plan-diamond-bg); color: var(--plan-diamond-accent); }

    .plan-badge-tag {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 6px;
    }
    .theme-free .plan-badge-tag { background: var(--plan-free-bg); color: var(--plan-free-accent); }
    .theme-gold .plan-badge-tag { background: var(--plan-gold-bg); color: var(--plan-gold-accent); }
    .theme-platinum .plan-badge-tag { background: var(--plan-platinum-bg); color: var(--plan-platinum-accent); }
    .theme-diamond .plan-badge-tag { background: var(--plan-diamond-bg); color: var(--plan-diamond-accent); }

    .plan-name-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--text-main);
    }
    .plan-desc-text {
        font-size: 12.5px;
        color: var(--text-muted);
        margin-top: 4px;
        min-height: 38px;
    }

    .plan-price-wrap {
        margin: 16px 0;
        padding: 14px 0;
        border-top: 1px solid var(--border-subtle);
        border-bottom: 1px solid var(--border-subtle);
    }
    .plan-price-amount {
        font-size: 30px;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1;
    }
    .plan-price-cycle {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 500;
    }

    .plan-stats-row {
        display: flex;
        justify-content: space-between;
        font-size: 12.5px;
        margin-bottom: 16px;
    }
    .plan-stats-item {
        display: flex;
        flex-direction: column;
    }
    .plan-stats-item span { font-size: 11px; color: var(--text-subtle); font-weight: 600; text-transform: uppercase; }
    .plan-stats-item strong { font-size: 14px; color: var(--text-main); font-weight: 700; margin-top: 2px; }

    /* Features List */
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
    .plan-features-list li {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-muted);
    }
    .plan-features-list li i { color: var(--success); font-size: 12px; }

    .plan-actions-footer {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: auto;
        padding-top: 14px;
        border-top: 1px solid var(--border-subtle);
        width: 100%;
    }
    .plan-actions-footer .btn-custom {
        padding: 7px 8px;
        font-size: 11.5px;
        font-weight: 700;
        white-space: nowrap;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    /* 2-Column Analytics Section */
    .analytics-2col-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 32px;
    }
    @media (max-width: 1024px) {
        .analytics-2col-grid { grid-template-columns: 1fr; }
    }

    .chart-card-wrapper {
        background: var(--bg-surface);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        padding: 24px;
        box-shadow: var(--shadow-sm);
    }
    .chart-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    .chart-card-title {
        font-size: 17px;
        font-weight: 700;
        color: var(--text-main);
    }

    /* Comparison Matrix Table */
    .comparison-card {
        background: var(--bg-surface);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        padding: 24px;
        box-shadow: var(--shadow-sm);
        margin-bottom: 32px;
        overflow-x: auto;
    }
    table.matrix-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
        min-width: 700px;
    }
    table.matrix-table th {
        padding: 14px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-subtle);
        border-bottom: 2px solid var(--border-color);
        background: #f8fafc;
    }
    table.matrix-table td {
        padding: 14px;
        border-bottom: 1px solid var(--border-subtle);
        color: var(--text-muted);
    }
    table.matrix-table tr:hover { background: #f8fafc; }

    /* Ultra-Premium Modal & Entrance Keyframes */
    @keyframes modalPopIn {
        0% {
            opacity: 0;
            transform: scale(0.92) translateY(12px);
        }
        100% {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    @keyframes shimmerBtn {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }

    .modal-backdrop-custom {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        transition: all 0.3s ease;
    }
    .modal-backdrop-custom.open {
        display: flex;
    }
    .modal-backdrop-custom.open .modal-dialog-custom {
        animation: modalPopIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .modal-dialog-custom {
        background: #ffffff;
        border-radius: 24px;
        max-width: 540px;
        width: 100%;
        padding: 32px;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.3);
        border: 1px solid rgba(226, 232, 240, 0.9);
        position: relative;
    }

    .modal-input-wrap {
        position: relative;
    }
    .modal-input-wrap i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 18px;
        transition: color 0.2s ease;
        pointer-events: none;
    }
    .modal-input-field {
        width: 100%;
        padding: 11px 16px 11px 42px;
        border-radius: 14px;
        border: 1px solid #cbd5e1;
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
        outline: none;
        background: #ffffff;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    }
    .modal-input-field:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15), 0 4px 12px rgba(37, 99, 235, 0.08);
        transform: translateY(-1px);
    }
    .modal-input-wrap:focus-within i {
        color: #2563eb;
    }

    .btn-shimmer-cta {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #2563eb 100%);
        background-size: 200% 100%;
        color: #ffffff;
        border: none;
        border-radius: 14px;
        padding: 11px 24px;
        font-weight: 800;
        font-size: 14px;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35);
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-family: inherit;
    }
    .btn-shimmer-cta:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 26px rgba(37, 99, 235, 0.45);
        animation: shimmerBtn 2s infinite linear;
    }

    /* Ultra-Clean Single Export Dropdown Button & Menu */
    .export-dropdown-wrap {
        position: relative;
        display: inline-block;
    }
    .export-dropdown-menu {
        position: absolute;
        right: 0;
        top: 100%;
        margin-top: 8px;
        min-width: 180px;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.08);
        border: 1px solid #e2e8f0;
        z-index: 9999;
        display: none;
        flex-direction: column;
        padding: 6px;
    }
    .export-dropdown-menu.open {
        display: flex !important;
        animation: modalPopIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .export-dropdown-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        font-size: 13.5px;
        font-weight: 600;
        color: #1e293b;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .export-dropdown-item:hover {
        background: #f1f5f9;
        color: #2563eb;
    }
</style>

<!-- PAGE CONTROL TOOLBAR -->
<div class="plans-header" style="justify-content: flex-end; margin-bottom: 20px;">
    <div class="header-actions">
        <div class="search-input-wrap">
            <i class="fas fa-search"></i>
            <input type="text" id="searchPlansInput" placeholder="Search plans, limits..." />
        </div>
        <div class="export-dropdown-wrap">
            <button type="button" class="btn-custom btn-outline-custom" id="exportPlansDropdownBtn" style="display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-download"></i> Export <i class="fas fa-chevron-down" style="font-size: 10px; margin-left: 2px;"></i>
            </button>
            <div class="export-dropdown-menu" id="exportPlansDropdownMenu">
                <a href="#" class="export-dropdown-item" id="exportPlansCsvOption">
                    <i class="fas fa-file-csv" style="color: #10b981; font-size: 16px;"></i> Export CSV
                </a>
                <a href="#" class="export-dropdown-item" id="exportPlansPdfOption">
                    <i class="fas fa-file-pdf" style="color: #ef4444; font-size: 16px;"></i> Export PDF
                </a>
            </div>
        </div>
        <button class="btn-custom btn-primary-custom" id="openCreatePlanModalBtn">
            <i class="fas fa-plus"></i> Create Plan
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

<!-- SUMMARY KPI CARDS BAR -->
<div class="plans-kpi-grid">
    <div class="kpi-card">
        <div class="head">
            <span class="lbl">Total Plans</span>
            <div style="width: 32px; height: 32px; border-radius: 8px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fas fa-layer-group"></i></div>
        </div>
        <div class="val">{{ $plans->count() }}</div>
        <div class="sub">Standard platform tiers</div>
    </div>

    <div class="kpi-card">
        <div class="head">
            <span class="lbl">Active Plans</span>
            <div style="width: 32px; height: 32px; border-radius: 8px; background: #f0fdf4; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fas fa-circle-check"></i></div>
        </div>
        <div class="val">{{ $plans->where('is_active', true)->count() }}</div>
        <div class="sub" style="color: var(--success); font-weight: 600;">Active availability</div>
    </div>

    <div class="kpi-card">
        <div class="head">
            <span class="lbl">Total Subscribers</span>
            <div style="width: 32px; height: 32px; border-radius: 8px; background: #f0f9ff; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fas fa-building"></i></div>
        </div>
        <div class="val">{{ number_format($companies->count()) }}</div>
        <div class="sub">Active tenant companies</div>
    </div>

    <div class="kpi-card">
        <div class="head">
            <span class="lbl">Monthly Revenue</span>
            <div style="width: 32px; height: 32px; border-radius: 8px; background: #f0fdf4; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fas fa-indian-rupee-sign"></i></div>
        </div>
        <div class="val">₹2,48,500</div>
        <div class="sub" style="color: var(--success); font-weight: 600;">+18.2% vs last month</div>
    </div>

    <div class="kpi-card">
        <div class="head">
            <span class="lbl">Most Popular Plan</span>
            <div style="width: 32px; height: 32px; border-radius: 8px; background: #fffbeb; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fas fa-crown"></i></div>
        </div>
        <div class="val" style="color: #d97706;">GOLD</div>
        <div class="sub">31% of subscribers</div>
    </div>
</div>

<!-- PLAN OVERVIEW CARDS GRID (SORTED BY MONTHLY PRICE ASCENDING: LOWER ON LEFT, HIGHER ON RIGHT) -->
<div class="plans-cards-grid">
    @forelse($plans->sortBy('monthly_price') as $plan)
        @php
            $pName = strtoupper($plan->name);
            $pClass = strtolower($pName);
            if (!in_array($pClass, ['free', 'gold', 'platinum', 'diamond'])) {
                $pClass = 'gold';
            }
            $storageGb = round(($plan->max_storage_mb ?? 0) / 1024, 1);
            if ($storageGb == intval($storageGb)) { $storageGb = intval($storageGb); }
        @endphp
        <div class="plan-card-item theme-{{ $pClass }}">
            @if($pName === 'GOLD')
                <div class="popular-ribbon">Most Popular</div>
            @endif
            <div class="plan-card-header">
                <div class="plan-icon-box">
                    @if($pName === 'FREE') <i class="fas fa-tag"></i>
                    @elseif($pName === 'GOLD') <i class="fas fa-crown"></i>
                    @elseif($pName === 'PLATINUM') <i class="fas fa-gem"></i>
                    @elseif($pName === 'DIAMOND') <i class="fas fa-wand-magic-sparkles"></i>
                    @else <i class="fas fa-layer-group"></i> @endif
                </div>
                <span class="plan-badge-tag">{{ $pName }}</span>
                @if(!$plan->is_active)
                    <span class="status-pill status-expired" style="font-size: 10px; padding: 2px 8px;"><span class="dot"></span> Inactive</span>
                @endif
            </div>

            <div class="plan-name-title">{{ $plan->name }} Plan</div>
            <div class="plan-desc-text">{{ $plan->description ?? 'Custom subscription tier for workspace teams.' }}</div>

            <div class="plan-price-wrap">
                <div class="plan-price-amount">₹{{ number_format($plan->monthly_price) }}</div>
                <div class="plan-price-cycle">per company / month (billed ₹{{ number_format($plan->yearly_price ?? ($plan->monthly_price * 10)) }}/yr)</div>
            </div>

            <div class="plan-stats-row">
                <div class="plan-stats-item">
                    <span>User Limit</span>
                    <strong>{{ $plan->max_users > 0 ? $plan->max_users . ' Users' : 'Unlimited Users' }}</strong>
                </div>
                <div class="plan-stats-item" style="text-align: right;">
                    <span>Storage</span>
                    <strong>{{ $storageGb > 0 ? $storageGb . ' GB' : 'Unlimited Storage' }}</strong>
                </div>
            </div>

            <ul class="plan-features-list">
                <li><i class="fas fa-check"></i> {{ $plan->max_users > 0 ? 'Up to ' . $plan->max_users . ' User Accounts' : 'Unlimited User Accounts' }}</li>
                <li><i class="fas fa-check"></i> {{ $storageGb > 0 ? $storageGb . ' GB Dedicated Storage' : 'Unlimited Dedicated Storage' }}</li>
                <li><i class="fas fa-check"></i> Single Tenant Database</li>
                <li><i class="fas fa-check"></i> Enterprise Admin Controls</li>
            </ul>

            <div class="plan-actions-footer">
                <button type="button" class="btn-custom btn-outline-custom btn-xs-custom open-plan-details" 
                        data-id="{{ $plan->id }}"
                        data-name="{{ $plan->name }}"
                        data-price="{{ $plan->monthly_price }}"
                        data-users="{{ $plan->max_users }}"
                        data-storage="{{ $storageGb }}"
                        data-desc="{{ $plan->description }}"
                        data-status="{{ $plan->is_active ? 1 : 0 }}"
                        style="flex: 1;">
                    <i class="fas fa-eye"></i> View Details
                </button>
                <button type="button" class="btn-custom btn-primary-custom btn-xs-custom open-plan-edit" 
                        data-id="{{ $plan->id }}" 
                        data-name="{{ $plan->name }}" 
                        data-price="{{ $plan->monthly_price }}" 
                        data-users="{{ $plan->max_users }}" 
                        data-storage="{{ $storageGb }}" 
                        data-status="{{ $plan->is_active ? 1 : 0 }}"
                        style="flex: 1;">
                    <i class="fas fa-pen"></i> Edit Plan
                </button>
                <form method="POST" action="{{ route('super-admin.plans.destroy', $plan->id) }}" onsubmit="return confirm('Are you sure you want to delete {{ addslashes($plan->name) }} plan?');" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-custom btn-outline-custom btn-xs-custom" style="color: var(--danger); border-color: var(--danger-border);" title="Delete Plan">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; padding: 32px; text-align: center; color: var(--text-subtle); background: #fff; border-radius: 16px; border: 1px solid var(--border-color);">
            No subscription plans found. Click "Create Plan" to set up your first tier.
        </div>
    @endforelse
</div>

<!-- 2-COLUMN ANALYTICS SECTION -->
<div class="analytics-2col-grid">
    <!-- Subscription Distribution Donut -->
    <div class="chart-card-wrapper">
        <div class="chart-card-head">
            <div class="chart-card-title"><i class="fas fa-chart-pie" style="color: var(--primary);"></i> Subscription Distribution</div>
            <span style="font-size: 12px; color: var(--text-subtle);">Active company count</span>
        </div>
        <div style="height: 240px; position: relative;">
            <canvas id="planDonutCanvas"></canvas>
        </div>
    </div>

    <!-- Plan Performance Bar Chart -->
    <div class="chart-card-wrapper">
        <div class="chart-card-head">
            <div class="chart-card-title"><i class="fas fa-chart-bar" style="color: var(--success);"></i> Plan Revenue Performance</div>
            <span style="font-size: 12px; color: var(--text-subtle);">Monthly MRR generated</span>
        </div>
        <div style="height: 240px; position: relative;">
            <canvas id="planPerformanceCanvas"></canvas>
        </div>
    </div>
</div>

<!-- PLAN COMPARISON MATRIX TABLE -->
<div class="comparison-card">
    <div class="chart-card-head" style="margin-bottom: 16px;">
        <div class="chart-card-title"><i class="fas fa-layer-group" style="color: #7c3aed;"></i> Feature & Limit Comparison Matrix</div>
        <span style="font-size: 12px; color: var(--text-subtle);">Detailed side-by-side tier features</span>
    </div>
    <table class="matrix-table">
        <thead>
            <tr>
                <th>Feature / Limit</th>
                <th>FREE</th>
                <th>GOLD</th>
                <th>PLATINUM</th>
                <th>DIAMOND</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Monthly Price</strong></td>
                <td>₹0 / mo</td>
                <td><strong style="color: #d97706;">₹4,999 / mo</strong></td>
                <td><strong style="color: #0284c7;">₹9,999 / mo</strong></td>
                <td><strong style="color: #7c3aed;">₹19,999 / mo</strong></td>
            </tr>
            <tr>
                <td><strong>User Limit</strong></td>
                <td>5 Users</td>
                <td>25 Users</td>
                <td>100 Users</td>
                <td><span style="color: var(--success); font-weight: 700;">Unlimited</span></td>
            </tr>
            <tr>
                <td><strong>Storage Quota</strong></td>
                <td>5 GB</td>
                <td>25 GB</td>
                <td>100 GB</td>
                <td>500 GB High-Speed</td>
            </tr>
            <tr>
                <td><strong>Multi-Tenant DB</strong></td>
                <td>✓ Included</td>
                <td>✓ Included</td>
                <td>✓ Included</td>
                <td>✓ Dedicated DB</td>
            </tr>
            <tr>
                <td><strong>Custom Domain Support</strong></td>
                <td>—</td>
                <td>✓ Included</td>
                <td>✓ Included</td>
                <td>✓ Included</td>
            </tr>
            <tr>
                <td><strong>Priority SLA Guarantee</strong></td>
                <td>—</td>
                <td>—</td>
                <td>✓ 99.9% SLA</td>
                <td>✓ 99.99% Dedicated SLA</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- ULTRA-PREMIUM PLAN EDIT / CREATE MODAL -->
<div class="modal-backdrop-custom" id="planModalDialog">
    <div class="modal-dialog-custom">
        <!-- Close Cross Button -->
        <button type="button" id="closePlanModalCrossBtn" style="position: absolute; right: 20px; top: 20px; width: 34px; height: 34px; border-radius: 50%; background: #f1f5f9; border: none; color: #64748b; font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease;">
            <i class="bx bx-x"></i>
        </button>

        <!-- Form Header with Gradient Icon Badge -->
        <div style="display: flex; align-items: flex-start; gap: 16px; margin-bottom: 20px;">
            <div style="width: 52px; height: 52px; border-radius: 16px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3); flex-shrink: 0;">
                <i class="bx bx-layer"></i>
            </div>
            <div>
                <h3 style="font-size: 22px; font-weight: 800; margin: 0; color: #0f172a; letter-spacing: -0.4px;" id="modalPlanTitle">Edit Subscription Plan</h3>
                <p style="font-size: 13px; color: #64748b; margin: 4px 0 0 0; line-height: 1.4;">
                    Configure tier parameters, user quotas, storage allocation, and monthly pricing.
                </p>
            </div>
        </div>

        <!-- Dynamic Live Calculated Badge Pill -->
        <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #cbd5e1; border-radius: 14px; padding: 12px 16px; margin-bottom: 22px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="plan-badge-tag" id="livePreviewTierTag" style="background: #2563eb; color: #fff; font-size: 11px; padding: 4px 10px; border-radius: 999px; font-weight: 800;">FREE</span>
                <span style="font-size: 13px; font-weight: 700; color: #0f172a;" id="livePreviewPrice">₹0 / month</span>
            </div>
            <div style="font-size: 12px; color: #64748b; font-weight: 600;" id="livePreviewQuotas">
                <i class="bx bx-user" style="color: #2563eb;"></i> <span id="liveUsersVal">5 Users</span> &bull; <i class="bx bx-hdd" style="color: #0284c7;"></i> <span id="liveStorageVal">5 GB</span>
            </div>
        </div>

        <form method="POST" action="{{ route('super-admin.plans.store') }}" id="planModalForm">
            @csrf
            <input type="hidden" name="_method" id="planModalMethod" value="POST">

            <div style="display: flex; flex-direction: column; gap: 18px; margin-bottom: 24px;">
                <!-- PLAN NAME -->
                <div>
                    <label style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">PLAN NAME</label>
                    <div class="modal-input-wrap">
                        <input type="text" name="name" id="modalPlanNameInput" required placeholder="GOLD" class="modal-input-field" />
                        <i class="bx bx-tag"></i>
                    </div>
                </div>

                <!-- MONTHLY PRICE & MAX USERS -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">MONTHLY PRICE (₹)</label>
                        <div class="modal-input-wrap">
                            <input type="number" step="0.01" name="monthly_price" id="modalPlanPriceInput" required placeholder="4999" class="modal-input-field" />
                            <i class="bx bx-rupee"></i>
                        </div>
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">MAX USERS</label>
                        <div class="modal-input-wrap">
                            <input type="number" name="max_users" id="modalPlanUsersInput" required placeholder="25" class="modal-input-field" />
                            <i class="bx bx-group"></i>
                        </div>
                    </div>
                </div>

                <!-- STORAGE & AVAILABILITY -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">STORAGE (GB)</label>
                        <div class="modal-input-wrap">
                            <input type="number" step="0.1" name="max_storage_gb" id="modalPlanStorageInput" required placeholder="25" class="modal-input-field" />
                            <i class="bx bx-hdd"></i>
                        </div>
                    </div>
                    <div>
                        <label style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">AVAILABILITY</label>
                        <div class="modal-input-wrap">
                            <select name="is_active" id="modalPlanStatusSelect" class="modal-input-field" style="appearance: none;">
                                <option value="1">Active (Available)</option>
                                <option value="0">Inactive (Hidden)</option>
                            </select>
                            <i class="bx bx-toggle-right"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER ACTIONS -->
            <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                <button type="button" class="btn-custom btn-outline-custom" id="closePlanModalBtn" style="padding: 11px 24px; border-radius: 14px; font-weight: 700; height: 44px;">Cancel</button>
                <button type="submit" class="btn-shimmer-cta" id="savePlanModalBtn" style="height: 44px;">
                    <i class="bx bx-check-circle" style="font-size: 18px;"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- VIEW PLAN DETAILS MODAL -->
<div class="modal-backdrop-custom" id="planDetailsModalDialog">
    <div class="modal-dialog-custom" style="max-width: 620px; border-radius: 24px; padding: 32px; background: #ffffff; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.3);">
        <button type="button" id="closeDetailsModalCrossBtn" style="position: absolute; right: 20px; top: 20px; width: 34px; height: 34px; border-radius: 50%; background: #f1f5f9; border: none; color: #64748b; font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease;">
            <i class="bx bx-x"></i>
        </button>

        <!-- Header -->
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
            <div id="detailsIconBox" style="width: 54px; height: 54px; border-radius: 16px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 26px; box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3); flex-shrink: 0;">
                <i class="bx bx-crown"></i>
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h3 style="font-size: 22px; font-weight: 800; margin: 0; color: #0f172a;" id="detailsPlanTitle">GOLD Plan</h3>
                    <span class="plan-badge-tag" id="detailsPlanTag" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a;">GOLD</span>
                </div>
                <div style="font-size: 13px; color: #64748b; margin-top: 4px;" id="detailsPlanDesc">Popular for growing businesses needing team collaboration.</div>
            </div>
        </div>

        <!-- Pricing & Status Banner Card -->
        <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #cbd5e1; border-radius: 16px; padding: 18px 22px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">SUBSCRIPTION COST</div>
                <div style="font-size: 26px; font-weight: 800; color: #0f172a; margin-top: 2px;" id="detailsPriceText">₹4,999 <span style="font-size: 13px; font-weight: 600; color: #64748b;">/ month</span></div>
                <div style="font-size: 12px; color: #059669; font-weight: 700;" id="detailsYearlyText">Billed ₹49,990 / year</div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">TIER STATUS</div>
                <span class="status-pill status-active" id="detailsStatusPill"><span class="dot"></span> Active Availability</span>
            </div>
        </div>

        <!-- Resource Quotas 2x2 Grid -->
        <h4 style="font-size: 13.5px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">Allocated Resource Quotas</h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 24px;">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; display: flex; align-items: center; gap: 12px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 20px;"><i class="bx bx-group"></i></div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">USER ALLOCATION</div>
                    <strong style="font-size: 14px; color: #0f172a;" id="detailsUserQuotaText">25 Active Users</strong>
                </div>
            </div>

            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; display: flex; align-items: center; gap: 12px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: #f0f9ff; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 20px;"><i class="bx bx-hdd"></i></div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">STORAGE ALLOCATION</div>
                    <strong style="font-size: 14px; color: #0f172a;" id="detailsStorageQuotaText">25 GB Dedicated NVMe</strong>
                </div>
            </div>

            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; display: flex; align-items: center; gap: 12px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: #f0fdf4; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 20px;"><i class="bx bx-data"></i></div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">DATABASE ISOLATION</div>
                    <strong style="font-size: 14px; color: #0f172a;">Single Tenant Database</strong>
                </div>
            </div>

            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; display: flex; align-items: center; gap: 12px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: #f5f3ff; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 20px;"><i class="bx bx-shield-quarter"></i></div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">SLA &amp; SUPPORT</div>
                    <strong style="font-size: 14px; color: #0f172a;" id="detailsSlaText">99.9% Uptime SLA</strong>
                </div>
            </div>
        </div>

        <!-- Included Features Checklist -->
        <h4 style="font-size: 13.5px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">Included Capabilities &amp; Features</h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #334155; font-weight: 600;">
                <i class="bx bx-check-circle" style="color: #10b981; font-size: 18px;"></i> Multi-Project Workspace Management
            </div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #334155; font-weight: 600;">
                <i class="bx bx-check-circle" style="color: #10b981; font-size: 18px;"></i> Role-Based Access Control (RBAC)
            </div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #334155; font-weight: 600;">
                <i class="bx bx-check-circle" style="color: #10b981; font-size: 18px;"></i> Automated Daily Database Backups
            </div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #334155; font-weight: 600;">
                <i class="bx bx-check-circle" style="color: #10b981; font-size: 18px;"></i> Custom Subdomain Routing
            </div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #334155; font-weight: 600;">
                <i class="bx bx-check-circle" style="color: #10b981; font-size: 18px;"></i> Real-Time Telemetry &amp; Audit Logs
            </div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #334155; font-weight: 600;">
                <i class="bx bx-check-circle" style="color: #10b981; font-size: 18px;"></i> Data Export Tools (CSV, PDF)
            </div>
        </div>

        <!-- Footer Actions -->
        <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
            <button type="button" class="btn-custom btn-outline-custom" id="closeDetailsModalBtn" style="padding: 10px 22px; border-radius: 12px; font-weight: 700; height: 42px;">Close</button>
            <button type="button" class="btn-custom btn-primary-custom" id="switchDetailsToEditBtn" style="padding: 10px 22px; border-radius: 12px; font-weight: 700; background: #2563eb; color: #fff; height: 42px;">
                <i class="bx bx-edit"></i> Edit This Plan
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Subscription Donut Chart
    const planDonutCtx = document.getElementById('planDonutCanvas')?.getContext('2d');
    if (planDonutCtx) {
        new Chart(planDonutCtx, {
            type: 'doughnut',
            data: {
                labels: ['FREE', 'GOLD', 'PLATINUM', 'DIAMOND'],
                datasets: [{
                    data: [42, 31, 18, 9],
                    backgroundColor: ['#64748b', '#d97706', '#0284c7', '#7c3aed'],
                    borderWidth: 3,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'right', labels: { font: { family: 'Inter', size: 12 } } }
                }
            }
        });
    }

    // 2. Plan Revenue Bar Chart
    const planPerfCtx = document.getElementById('planPerformanceCanvas')?.getContext('2d');
    if (planPerfCtx) {
        new Chart(planPerfCtx, {
            type: 'bar',
            data: {
                labels: ['FREE', 'GOLD', 'PLATINUM', 'DIAMOND'],
                datasets: [{
                    label: 'MRR Contribution (₹)',
                    data: [0, 189962, 309969, 339983],
                    backgroundColor: ['#64748b', '#d97706', '#0284c7', '#7c3aed'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // 3. Modal Dialog Triggers & Dynamic Live Preview Binding
    const planModal = document.getElementById('planModalDialog');
    const planForm = document.getElementById('planModalForm');
    const methodInput = document.getElementById('planModalMethod');
    const titleEl = document.getElementById('modalPlanTitle');
    const nameInput = document.getElementById('modalPlanNameInput');
    const priceInput = document.getElementById('modalPlanPriceInput');
    const usersInput = document.getElementById('modalPlanUsersInput');
    const storageInput = document.getElementById('modalPlanStorageInput');
    const statusSelect = document.getElementById('modalPlanStatusSelect');
    const openCreateBtn = document.getElementById('openCreatePlanModalBtn');
    const closeBtn = document.getElementById('closePlanModalBtn');
    const closeCrossBtn = document.getElementById('closePlanModalCrossBtn');

    // Details Modal Elements
    const detailsModal = document.getElementById('planDetailsModalDialog');
    const detailsTitle = document.getElementById('detailsPlanTitle');
    const detailsTag = document.getElementById('detailsPlanTag');
    const detailsDesc = document.getElementById('detailsPlanDesc');
    const detailsPriceText = document.getElementById('detailsPriceText');
    const detailsYearlyText = document.getElementById('detailsYearlyText');
    const detailsStatusPill = document.getElementById('detailsStatusPill');
    const detailsUserQuota = document.getElementById('detailsUserQuotaText');
    const detailsStorageQuota = document.getElementById('detailsStorageQuotaText');
    const closeDetailsBtn = document.getElementById('closeDetailsModalBtn');
    const closeDetailsCross = document.getElementById('closeDetailsModalCrossBtn');
    const switchEditBtn = document.getElementById('switchDetailsToEditBtn');

    let activeEditPlanData = null;

    // Live preview elements
    const liveTag = document.getElementById('livePreviewTierTag');
    const livePrice = document.getElementById('livePreviewPrice');
    const liveUsers = document.getElementById('liveUsersVal');
    const liveStorage = document.getElementById('liveStorageVal');

    function updateLivePreview() {
        if (!liveTag) return;
        const nameVal = (nameInput.value || 'NEW TIER').toUpperCase();
        const priceVal = parseFloat(priceInput.value) || 0;
        const usersVal = parseInt(usersInput.value) || 0;
        const storageVal = parseFloat(storageInput.value) || 0;

        liveTag.innerText = nameVal;
        livePrice.innerText = '₹' + priceVal.toLocaleString('en-IN') + ' / month';
        liveUsers.innerText = usersVal > 0 ? (usersVal + ' Users') : 'Unlimited Users';
        liveStorage.innerText = storageVal > 0 ? (storageVal + ' GB') : 'Unlimited Storage';
    }

    if (nameInput) nameInput.addEventListener('input', updateLivePreview);
    if (priceInput) priceInput.addEventListener('input', updateLivePreview);
    if (usersInput) usersInput.addEventListener('input', updateLivePreview);
    if (storageInput) storageInput.addEventListener('input', updateLivePreview);

    if (openCreateBtn && planModal) {
        openCreateBtn.addEventListener('click', function() {
            if (titleEl) titleEl.innerText = 'Create New Subscription Plan';
            if (planForm) planForm.action = "{{ route('super-admin.plans.store') }}";
            if (methodInput) methodInput.value = 'POST';
            if (nameInput) nameInput.value = '';
            if (priceInput) priceInput.value = '';
            if (usersInput) usersInput.value = '';
            if (storageInput) storageInput.value = '';
            if (statusSelect) statusSelect.value = '1';

            updateLivePreview();
            planModal.classList.add('open');
        });
    }

    // EDIT BUTTON CLICK HANDLER
    function openEditModalWithData(data) {
        if (titleEl) titleEl.innerText = 'Edit ' + data.name + ' Subscription Plan';
        if (planForm) planForm.action = data.id ? ("/super-admin/plans/" + data.id) : "{{ route('super-admin.plans.store') }}";
        if (methodInput) methodInput.value = data.id ? 'PUT' : 'POST';
        if (nameInput) nameInput.value = data.name;
        if (priceInput) priceInput.value = data.price;
        if (usersInput) usersInput.value = data.users;
        if (storageInput) storageInput.value = data.storage;
        if (statusSelect) statusSelect.value = data.status;

        updateLivePreview();
        if (planModal) planModal.classList.add('open');
    }

    document.querySelectorAll('.open-plan-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = {
                id: this.getAttribute('data-id'),
                name: this.getAttribute('data-name') || this.getAttribute('data-plan').toUpperCase(),
                price: this.getAttribute('data-price') || '0',
                users: this.getAttribute('data-users') || '0',
                storage: this.getAttribute('data-storage') || '0',
                status: this.getAttribute('data-status') || '1'
            };
            openEditModalWithData(data);
        });
    });

    // VIEW DETAILS BUTTON CLICK HANDLER
    document.querySelectorAll('.open-plan-details').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = {
                id: this.getAttribute('data-id'),
                name: this.getAttribute('data-name') || 'PLAN',
                price: parseFloat(this.getAttribute('data-price')) || 0,
                users: parseInt(this.getAttribute('data-users')) || 0,
                storage: parseFloat(this.getAttribute('data-storage')) || 0,
                desc: this.getAttribute('data-desc') || 'Custom subscription tier for workspace teams.',
                status: this.getAttribute('data-status') || '1'
            };

            activeEditPlanData = data;

            if (detailsTitle) detailsTitle.innerText = data.name + ' Plan';
            if (detailsTag) detailsTag.innerText = data.name.toUpperCase();
            if (detailsDesc) detailsDesc.innerText = data.desc;
            if (detailsPriceText) detailsPriceText.innerHTML = '₹' + data.price.toLocaleString('en-IN') + ' <span style="font-size: 13px; font-weight: 600; color: #64748b;">/ month</span>';
            if (detailsYearlyText) detailsYearlyText.innerText = 'Billed ₹' + (data.price * 10).toLocaleString('en-IN') + ' / year';
            if (detailsUserQuota) detailsUserQuota.innerText = data.users > 0 ? (data.users + ' Active Users') : 'Unlimited Users';
            if (detailsStorageQuota) detailsStorageQuota.innerText = data.storage > 0 ? (data.storage + ' GB Dedicated NVMe') : 'Unlimited Storage';

            if (detailsStatusPill) {
                if (data.status === '1') {
                    detailsStatusPill.className = 'status-pill status-active';
                    detailsStatusPill.innerHTML = '<span class="dot"></span> Active Availability';
                } else {
                    detailsStatusPill.className = 'status-pill status-expired';
                    detailsStatusPill.innerHTML = '<span class="dot"></span> Inactive (Hidden)';
                }
            }

            if (detailsModal) detailsModal.classList.add('open');
        });
    });

    const closeEditModal = function() {
        if (planModal) planModal.classList.remove('open');
    };
    const closeDetailsModal = function() {
        if (detailsModal) detailsModal.classList.remove('open');
    };

    if (closeBtn) closeBtn.addEventListener('click', closeEditModal);
    if (closeCrossBtn) closeCrossBtn.addEventListener('click', closeEditModal);

    if (closeDetailsBtn) closeDetailsBtn.addEventListener('click', closeDetailsModal);
    if (closeDetailsCross) closeDetailsCross.addEventListener('click', closeDetailsModal);

    if (switchEditBtn) {
        switchEditBtn.addEventListener('click', function() {
            closeDetailsModal();
            if (activeEditPlanData) {
                openEditModalWithData(activeEditPlanData);
            }
        });
    }

    if (planModal) {
        planModal.addEventListener('click', function(e) {
            if (e.target === planModal) closeEditModal();
        });
    }

    if (detailsModal) {
        detailsModal.addEventListener('click', function(e) {
            if (e.target === detailsModal) closeDetailsModal();
        });
    }

    // 4. Export Dropdown Handlers (CSV & PDF)
    const exportBtn = document.getElementById('exportPlansDropdownBtn');
    const exportMenu = document.getElementById('exportPlansDropdownMenu');
    const exportCsvOpt = document.getElementById('exportPlansCsvOption');
    const exportPdfOpt = document.getElementById('exportPlansPdfOption');

    if (exportBtn && exportMenu) {
        exportBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            exportMenu.classList.toggle('open');
        });

        document.addEventListener('click', function() {
            exportMenu.classList.remove('open');
        });
    }

    // Export CSV Handler
    if (exportCsvOpt) {
        exportCsvOpt.addEventListener('click', function(e) {
            e.preventDefault();
            if (exportMenu) exportMenu.classList.remove('open');

            let csvRows = [];
            csvRows.push(["Plan Name", "Monthly Price (INR)", "Yearly Price (INR)", "Max Users", "Max Storage (GB)", "Availability Status"]);

            @foreach($plans as $p)
                @php
                    $sGb = round(($p->max_storage_mb ?? 0) / 1024, 1);
                    if ($sGb == intval($sGb)) { $sGb = intval($sGb); }
                @endphp
                csvRows.push([
                    "{{ addslashes($p->name) }}",
                    "{{ $p->monthly_price }}",
                    "{{ $p->yearly_price ?? ($p->monthly_price * 10) }}",
                    "{{ $p->max_users > 0 ? $p->max_users : 'Unlimited' }}",
                    "{{ $sGb > 0 ? $sGb : 'Unlimited' }}",
                    "{{ $p->is_active ? 'Active' : 'Inactive' }}"
                ]);
            @endforeach

            let csvContent = csvRows.map(e => e.map(cell => '"' + String(cell).replace(/"/g, '""') + '"').join(",")).join("\n");
            let blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
            let link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.setAttribute("download", "plans_catalog_export.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }

    // Export PDF Handler (Auto-Download PDF)
    if (exportPdfOpt) {
        exportPdfOpt.addEventListener('click', function(e) {
            e.preventDefault();
            if (exportMenu) exportMenu.classList.remove('open');

            const reportEl = document.createElement('div');
            reportEl.style.padding = '24px';
            reportEl.style.background = '#ffffff';
            reportEl.style.fontFamily = "'Inter', Arial, sans-serif";
            reportEl.style.color = '#0f172a';

            const formattedDate = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

            reportEl.innerHTML = `
                <style>
                    .pdf-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #2563eb; padding-bottom: 14px; margin-bottom: 20px; }
                    .pdf-brand { display: flex; align-items: center; gap: 12px; }
                    .pdf-logo { width: 32px; height: 32px; background: #2563eb; color: #ffffff; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; flex-shrink: 0; }
                    .pdf-title { font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; }
                    .pdf-sub { font-size: 11px; color: #64748b; margin: 2px 0 0 0; }
                    .pdf-meta { font-size: 11px; color: #64748b; text-align: right; }
                    table.pdf-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
                    table.pdf-table th, table.pdf-table td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: left; vertical-align: middle; }
                    table.pdf-table th { background: #f8fafc; color: #475569; font-weight: 800; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
                    .badge-active { background: #dcfce7; color: #15803d; font-size: 9px; font-weight: 800; padding: 2px 6px; border-radius: 999px; }
                    .badge-inactive { background: #fee2e2; color: #b91c1c; font-size: 9px; font-weight: 800; padding: 2px 6px; border-radius: 999px; }
                    .plan-tier-name { font-weight: 800; color: #0f172a; font-size: 12px; }
                </style>
                <div class="pdf-header">
                    <div class="pdf-brand">
                        <div class="pdf-logo">P</div>
                        <div>
                            <h1 class="pdf-title">Subscription Plans Catalog Report</h1>
                            <p class="pdf-sub">Super Admin Executive SaaS Tier Breakdown</p>
                        </div>
                    </div>
                    <div class="pdf-meta">
                        <strong>Export Date:</strong> ${formattedDate}<br>
                        <strong>Total Tiers:</strong> {{ count($plans) }} Active Tiers
                    </div>
                </div>

                <table class="pdf-table">
                    <thead>
                        <tr>
                            <th>Plan Tier</th>
                            <th>Monthly Price</th>
                            <th>Yearly Price</th>
                            <th>User Limit</th>
                            <th>Storage</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plans as $p)
                            @php
                                $sGb = round(($p->max_storage_mb ?? 0) / 1024, 1);
                                if ($sGb == intval($sGb)) { $sGb = intval($sGb); }
                            @endphp
                            <tr>
                                <td><span class="plan-tier-name">{{ strtoupper($p->name) }}</span></td>
                                <td><strong>₹{{ number_format($p->monthly_price) }}</strong> / mo</td>
                                <td>₹{{ number_format($p->yearly_price ?? ($p->monthly_price * 10)) }} / yr</td>
                                <td>{{ $p->max_users > 0 ? $p->max_users . ' Users' : 'Unlimited' }}</td>
                                <td>{{ $sGb > 0 ? $sGb . ' GB' : 'Unlimited' }}</td>
                                <td>
                                    @if($p->is_active)
                                        <span class="badge-active">ACTIVE</span>
                                    @else
                                        <span class="badge-inactive">INACTIVE</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            `;

            if (typeof html2pdf !== 'undefined') {
                const opt = {
                    margin:       [10, 10, 10, 10],
                    filename:     'plans_catalog_report.pdf',
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { scale: 2, useCORS: true, logging: false },
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };
                html2pdf().set(opt).from(reportEl).save();
            } else {
                const printWin = window.open('', '_blank');
                printWin.document.write(`<!DOCTYPE html><html><head><title>Plans Catalog Report</title></head><body>${reportEl.innerHTML}<script>window.onload=function(){window.print();};<\\/script></body></html>`);
            }
        });
    }
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
@endpush
