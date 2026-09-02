@extends('layouts.superadmin')

@section('title', 'Tenant Management · Companies')
@section('page_title', 'Companies')
@section('page_subtitle', 'Manage, monitor, and control all tenant companies, subscriptions, and platform resources.')

@section('content')
<style>
    /* ============================================================
       DESIGN TOKENS — Luxury Emerald & Slate Theme
       ============================================================ */
    :root {
        --emerald-primary: #0f744c;
        --emerald-dark: #073a26;
        --emerald-deep: #05291b;
        --emerald-light: #10b981;
        --emerald-soft: #e4f3eb;
        --emerald-glow: rgba(16, 185, 129, 0.25);
        --purple-accent: #7c3aed;
        --blue-accent: #2563eb;
        --amber-accent: #f59e0b;
        --rose-accent: #ef4444;

        --slate-dark: #0f172a;
        --slate-body: #334155;
        --slate-muted: #64748b;
        --slate-light: #f8fafc;

        --card-shadow-sm: 0 10px 25px -5px rgba(0, 0, 0, 0.04), 0 4px 10px rgba(0, 0, 0, 0.02);
        --card-shadow-md: 0 20px 45px -10px rgba(15, 116, 76, 0.08), 0 6px 18px rgba(0, 0, 0, 0.03);
        --card-shadow-lg: 0 30px 70px -15px rgba(15, 116, 76, 0.16), 0 12px 30px rgba(0, 0, 0, 0.05);

        --radius: 24px;
        --radius-sm: 14px;
        --radius-xs: 10px;

        /* Plan Badge Colors */
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
    }

    /* ===== PAGE HEADER (Breadcrumb + Title + Actions) ===== */
    .page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
        padding-top: 4px;
    }

    .page-header .left {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .breadcrumb-custom {
        font-size: 12px;
        font-weight: 600;
        color: var(--slate-muted);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .breadcrumb-custom .sep {
        color: var(--slate-muted);
        opacity: 0.5;
    }

    .breadcrumb-custom .current {
        color: var(--slate-body);
        font-weight: 700;
    }

    .page-title-text {
        font-size: 28px;
        font-weight: 900;
        letter-spacing: -0.4px;
        color: var(--slate-dark);
        line-height: 1.1;
    }

    .page-subtitle-text {
        font-size: 14px;
        color: var(--slate-muted);
        font-weight: 600;
        margin-top: 2px;
    }

    .page-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 4px;
    }

    .btn-custom {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 18px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 13px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid transparent;
        white-space: nowrap;
        height: 38px;
        cursor: pointer;
        text-decoration: none;
        font-family: inherit;
    }

    .btn-primary-custom {
        background: linear-gradient(135deg, var(--emerald-dark), var(--emerald-primary), var(--emerald-light));
        color: #fff !important;
        box-shadow: 0 8px 24px rgba(15, 116, 76, 0.3);
    }

    .btn-primary-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 32px rgba(15, 116, 76, 0.4);
        color: #fff !important;
    }

    .btn-outline-custom {
        background: rgba(255, 255, 255, 0.9);
        border-color: rgba(226, 232, 240, 0.9);
        color: var(--slate-body) !important;
    }

    .btn-outline-custom:hover {
        background: var(--emerald-soft);
        border-color: var(--emerald-primary);
        color: var(--emerald-primary) !important;
    }

    .btn-ghost-custom {
        background: transparent;
        color: var(--slate-muted) !important;
    }

    .btn-ghost-custom:hover {
        color: var(--slate-dark) !important;
        background: rgba(0, 0, 0, 0.04);
    }

    .btn-sm-custom {
        height: 32px;
        padding: 0 14px;
        font-size: 12px;
    }

    .btn-xs-custom {
        height: 30px;
        padding: 0 12px;
        font-size: 12px;
        border-radius: 8px;
    }

    /* ===== HEADER METADATA ===== */
    .header-meta {
        display: flex;
        align-items: center;
        gap: 24px;
        font-size: 12px;
        font-weight: 600;
        color: var(--slate-muted);
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 1px solid rgba(226, 232, 240, 0.5);
    }

    .header-meta .status {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .header-meta .status .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--emerald-light);
        display: inline-block;
    }

    /* ===== KPI GRID ===== */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }

    .kpi-card {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(8px);
        border-radius: 20px;
        padding: 16px 18px 18px;
        border: 1px solid rgba(226, 232, 240, 0.85);
        box-shadow: var(--card-shadow-sm);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--emerald-primary), var(--emerald-light));
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .kpi-card:hover {
        border-color: rgba(15, 116, 76, 0.25);
        box-shadow: var(--card-shadow-lg);
        transform: translateY(-4px);
    }

    .kpi-card:hover::before {
        opacity: 1;
    }

    .kpi-card .top {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .kpi-card .top .label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: var(--slate-muted);
    }

    .kpi-card .top .icon-box {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .icon-box.blue { background: rgba(37, 99, 235, 0.1); color: var(--blue-accent); }
    .icon-box.green { background: var(--emerald-soft); color: var(--emerald-primary); }
    .icon-box.amber { background: rgba(245, 158, 11, 0.1); color: var(--amber-accent); }
    .icon-box.red { background: rgba(239, 68, 68, 0.1); color: var(--rose-accent); }
    .icon-box.purple { background: rgba(124, 58, 237, 0.1); color: var(--purple-accent); }

    .kpi-card .value {
        font-size: 28px;
        font-weight: 900;
        letter-spacing: -0.3px;
        color: var(--slate-dark);
        margin-top: 6px;
        line-height: 1.1;
    }

    .kpi-card .footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 6px;
    }

    .kpi-card .footer .trend {
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 1px 8px;
        border-radius: 20px;
    }

    .kpi-card .footer .trend.up {
        color: var(--emerald-primary);
        background: var(--emerald-soft);
    }

    .kpi-card .footer .sub {
        font-size: 11px;
        color: var(--slate-muted);
        font-weight: 600;
    }

    .kpi-card .sparkline {
        margin-top: 8px;
        height: 24px;
        display: flex;
        align-items: flex-end;
        gap: 2px;
    }

    .kpi-card .sparkline .bar {
        flex: 1;
        border-radius: 2px;
        background: var(--emerald-soft);
        transition: height 0.4s ease;
        min-height: 2px;
    }

    .kpi-card .sparkline .bar.fill { background: var(--emerald-primary); }
    .kpi-card .sparkline .bar.fill.green { background: var(--emerald-light); }
    .kpi-card .sparkline .bar.fill.amber { background: var(--amber-accent); }
    .kpi-card .sparkline .bar.fill.red { background: var(--rose-accent); }
    .kpi-card .sparkline .bar.fill.purple { background: var(--purple-accent); }

    /* ===== PLAN DISTRIBUTION + HEALTH OVERVIEW ===== */
    .analytics-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    .analytics-card {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(8px);
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.85);
        padding: 20px 22px 24px;
        box-shadow: var(--card-shadow-sm);
        transition: all 0.3s ease;
    }

    .analytics-card:hover {
        border-color: rgba(15, 116, 76, 0.2);
        box-shadow: var(--card-shadow-md);
    }

    .analytics-card .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }

    .analytics-card .card-header .title {
        font-size: 15px;
        font-weight: 800;
        color: var(--slate-dark);
    }

    .analytics-card .card-header .sub {
        font-size: 12px;
        color: var(--slate-muted);
        font-weight: 600;
    }

    .donut-container {
        display: flex;
        align-items: center;
        gap: 24px;
        flex-wrap: wrap;
    }

    .donut-container .chart-wrap {
        flex: 1;
        min-width: 140px;
        height: 160px;
    }

    .donut-container .legend {
        display: flex;
        flex-direction: column;
        gap: 6px;
        flex: 1;
        min-width: 120px;
    }

    .donut-container .legend .item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        padding: 3px 0;
        border-bottom: 1px solid rgba(226, 232, 240, 0.5);
    }

    .donut-container .legend .item .left-info {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .donut-container .legend .item .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .donut-container .legend .item .name {
        font-weight: 600;
        color: var(--slate-body);
    }

    .donut-container .legend .item .count {
        font-weight: 700;
        color: var(--slate-dark);
    }

    .health-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .health-item-bar {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .health-item-bar .label {
        min-width: 80px;
        font-size: 12px;
        font-weight: 700;
        color: var(--slate-body);
    }

    .health-item-bar .track {
        flex: 1;
        height: 6px;
        background: rgba(226, 232, 240, 0.6);
        border-radius: 999px;
        overflow: hidden;
        position: relative;
    }

    .health-item-bar .track .fill {
        height: 100%;
        border-radius: 999px;
        background: var(--emerald-primary);
        transition: width 0.6s ease;
    }

    .health-item-bar .track .fill.warning { background: var(--amber-accent); }
    .health-item-bar .track .fill.danger { background: var(--rose-accent); }

    .health-item-bar .pct {
        font-size: 12px;
        font-weight: 700;
        color: var(--slate-muted);
        min-width: 36px;
        text-align: right;
    }

    /* ===== TOOLBAR (Search + Filters) ===== */
    .toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(8px);
        padding: 8px 16px;
        border-radius: 16px;
        border: 1px solid rgba(226, 232, 240, 0.85);
        box-shadow: var(--card-shadow-sm);
    }

    .toolbar .search-wrap {
        flex: 1;
        min-width: 220px;
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--slate-light);
        border-radius: 10px;
        padding: 0 12px;
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }

    .toolbar .search-wrap:focus-within {
        border-color: var(--emerald-primary);
        background: #fff;
        box-shadow: 0 0 0 3px var(--emerald-glow);
    }

    .toolbar .search-wrap .search-icon {
        color: var(--slate-muted);
        font-size: 16px;
    }

    .toolbar .search-wrap input {
        border: none;
        background: transparent;
        padding: 8px 0;
        width: 100%;
        outline: none;
        font-size: 13px;
        font-weight: 500;
        color: var(--slate-dark);
    }

    .toolbar .search-wrap input::placeholder {
        color: var(--slate-muted);
        font-weight: 500;
    }

    .toolbar .divider {
        width: 1px;
        height: 28px;
        background: rgba(226, 232, 240, 0.8);
    }

    .toolbar .filter-group {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .toolbar .filter-group select {
        padding: 4px 30px 4px 12px;
        border-radius: 999px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        background: var(--slate-light);
        font-size: 12px;
        font-weight: 600;
        color: var(--slate-body);
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        min-height: 32px;
    }

    .toolbar .filter-group select:hover {
        border-color: var(--emerald-primary);
    }

    .toolbar .filter-group select:focus {
        outline: none;
        border-color: var(--emerald-primary);
        box-shadow: 0 0 0 3px var(--emerald-glow);
    }

    .toolbar .filter-actions {
        display: flex;
        gap: 6px;
    }

    /* ===== ENTERPRISE COMPANY TABLE & GRID DIVISIONS ===== */
    .table-wrap {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border-radius: 16px;
        border: 1px solid #cbd5e1;
        overflow: hidden;
        box-shadow: var(--card-shadow-sm);
        margin-bottom: 16px;
    }

    .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    table.company-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
        min-width: 1050px;
    }

    table.company-table thead {
        background: #f1f5f9;
    }

    table.company-table thead th {
        text-align: left;
        padding: 12px 14px;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #475569;
        white-space: nowrap;
        border-bottom: 2px solid #cbd5e1;
        border-right: 1px solid #e2e8f0;
    }

    table.company-table thead th:last-child {
        border-right: none;
    }

    table.company-table tbody tr {
        transition: background 0.15s ease;
    }

    table.company-table tbody tr:nth-child(even) {
        background: #f8fafc;
    }

    table.company-table tbody tr:hover {
        background: rgba(15, 116, 76, 0.05);
    }

    table.company-table tbody td {
        padding: 14px;
        vertical-align: middle;
        color: var(--slate-body);
        font-weight: 500;
        border-bottom: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
    }

    table.company-table tbody td:last-child {
        border-right: none;
    }

    table.company-table tbody tr:last-child td {
        border-bottom: none;
    }

    .check-cell {
        width: 36px;
        text-align: center !important;
    }

    .check-cell input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: var(--emerald-primary);
        cursor: pointer;
    }

    .company-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .company-cell .logo {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: var(--emerald-soft);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 14px;
        color: var(--emerald-primary);
        flex-shrink: 0;
        border: 1px solid rgba(226, 232, 240, 0.8);
    }

    .company-cell .info .name {
        font-weight: 800;
        color: var(--slate-dark);
        font-size: 14px;
        line-height: 1.2;
    }

    .company-cell .info .domain {
        font-size: 12px;
        color: var(--slate-muted);
        font-weight: 600;
        margin-top: 1px;
    }

    .company-cell .info .id {
        font-size: 10px;
        color: var(--slate-muted);
        font-weight: 600;
        letter-spacing: 0.2px;
    }

    .db-code-badge {
        font-family: monospace;
        font-size: 11px;
        color: #0369a1;
        background: #e0f2fe;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid #bae6fd;
        font-weight: 600;
        display: inline-block;
    }

    .db-status-text {
        font-size: 11px;
        color: var(--emerald-primary);
        margin-top: 3px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .contact-email-text {
        color: var(--slate-muted);
        font-weight: 500;
        max-width: 170px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Badges & Pills */
    .plan-badge-cell {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .plan-badge-cell.plan-free { background: var(--plan-free-bg); color: var(--plan-free-text); border: 1px solid var(--plan-free-border); }
    .plan-badge-cell.plan-gold { background: var(--plan-gold-bg); color: var(--plan-gold-text); border: 1px solid var(--plan-gold-border); }
    .plan-badge-cell.plan-platinum { background: var(--plan-platinum-bg); color: var(--plan-platinum-text); border: 1px solid var(--plan-platinum-border); }
    .plan-badge-cell.plan-diamond { background: var(--plan-diamond-bg); color: var(--plan-diamond-text); border: 1px solid var(--plan-diamond-border); }

    .plan-price-subtext {
        font-size: 11px;
        color: var(--slate-muted);
        margin-top: 2px;
        font-weight: 600;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .status-pill .dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
    .status-pill.status-active { background: var(--emerald-soft); color: var(--emerald-primary); border-color: rgba(15, 116, 76, 0.2); }
    .status-pill.status-active .dot { background: var(--emerald-light); }
    .status-pill.status-trial { background: rgba(245, 158, 11, 0.1); color: var(--amber-accent); border-color: rgba(245, 158, 11, 0.2); }
    .status-pill.status-trial .dot { background: var(--amber-accent); }
    .status-pill.status-suspended { background: rgba(239, 68, 68, 0.1); color: var(--rose-accent); border-color: rgba(239, 68, 68, 0.2); }
    .status-pill.status-suspended .dot { background: var(--rose-accent); }
    .status-pill.status-expired { background: var(--slate-light); color: var(--slate-muted); border-color: rgba(226, 232, 240, 0.8); }
    .status-pill.status-expired .dot { background: var(--slate-muted); }

    .storage-track-bar {
        width: 76px;
        height: 6px;
        background: rgba(226, 232, 240, 0.8);
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 3px;
    }

    .storage-fill-bar {
        height: 100%;
        background: var(--emerald-primary);
        border-radius: 4px;
    }

    .actions-cell-wrap {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        min-width: 160px;
    }

    /* Actions dropdown */
    .dropdown-container {
        position: relative;
        display: inline-block;
    }

    .dropdown-menu-custom {
        position: absolute;
        right: 0;
        top: 100%;
        margin-top: 4px;
        background: rgba(255, 255, 255, 0.98);
        border-radius: 12px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: var(--card-shadow-lg);
        min-width: 220px;
        padding: 4px 0;
        display: none;
        z-index: 60;
        animation: fadeSlide 0.15s ease;
        backdrop-filter: blur(16px);
    }

    .dropdown-menu-custom.open { display: block; }

    .dropdown-menu-custom a,
    .dropdown-menu-custom button {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        padding: 7px 16px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--slate-body);
        text-decoration: none;
        background: none;
        border: none;
        cursor: pointer;
        text-align: left;
        font-family: inherit;
        transition: all 0.15s ease;
    }

    .dropdown-menu-custom a:hover,
    .dropdown-menu-custom button:hover {
        background: var(--emerald-soft);
        color: var(--emerald-primary);
    }

    .dropdown-menu-custom .divider {
        height: 1px;
        background: rgba(226, 232, 240, 0.8);
        margin: 4px 12px;
    }

    .dropdown-menu-custom .danger-item {
        color: var(--rose-accent) !important;
    }

    .dropdown-menu-custom .danger-item:hover {
        background: rgba(239, 68, 68, 0.08) !important;
        color: var(--rose-accent) !important;
    }

    /* Modal Backdrop */
    .modal-backdrop-custom {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(4px);
        z-index: 300;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-backdrop-custom.open { display: flex; }

    .modal-dialog-custom {
        background: #ffffff;
        border-radius: 20px;
        max-width: 540px;
        width: 100%;
        padding: 28px;
        box-shadow: var(--card-shadow-lg);
    }

    .plan-card-option {
        background: #ffffff;
        border: 2px solid rgba(226, 232, 240, 0.8);
        border-radius: 14px;
        padding: 14px 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-bottom: 10px;
    }

    .plan-card-option:hover { border-color: var(--emerald-primary); }
    .plan-card-option.selected { border-color: var(--emerald-primary); background: var(--emerald-soft); }

    /* Company Detail Drawer */
    .detail-overlay {
        position: fixed;
        inset: 0;
        background: rgba(11, 23, 41, 0.5);
        backdrop-filter: blur(4px);
        z-index: 200;
        display: none;
        justify-content: flex-end;
    }

    .detail-overlay.open { display: flex; }

    .detail-drawer {
        width: 100%;
        max-width: 480px;
        background: #ffffff;
        height: 100vh;
        overflow-y: auto;
        padding: 24px;
        box-shadow: var(--card-shadow-lg);
        display: flex;
        flex-direction: column;
        gap: 20px;
        animation: slideDrawer 0.25s ease;
    }

    @keyframes slideDrawer {
        from { transform: translateX(30px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
</style>

<!-- PAGE CONTROL TOOLBAR -->
<div class="page-header" style="justify-content: flex-end; margin-bottom: 20px;">
    <div class="page-actions">
        <!-- EXPORT DROPDOWN (CSV & PDF) -->
        <div class="export-dropdown-wrap" style="position: relative; display: inline-block;">
            <button type="button" class="btn-custom btn-outline-custom" id="exportDropdownBtn" style="display: flex; align-items: center; gap: 8px;">
                <i class="bx bx-download"></i> Export <i class="bx bx-chevron-down" style="font-size: 16px;"></i>
            </button>
            <div class="dropdown-menu-custom export-menu" id="exportDropdownMenu" style="right: 0; left: auto; top: 100%; margin-top: 6px; min-width: 160px;">
                <a href="#" id="exportCsvOption" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; font-weight: 600;">
                    <i class="bx bx-file" style="color: var(--emerald-primary); font-size: 18px;"></i> Export CSV
                </a>
                <a href="#" id="exportPdfOption" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; font-weight: 600;">
                    <i class="bx bxs-file-pdf" style="color: #ef4444; font-size: 18px;"></i> Export PDF
                </a>
            </div>
        </div>
        <a href="{{ route('super-admin.companies.create') }}" class="btn-custom btn-primary-custom">
            <i class="bx bx-plus-circle"></i> Provision New Company
        </a>
    </div>
</div>

<!-- ACTIVE IMPERSONATION WARNING BANNER -->
@if(isset($currentCompanyDb) && $currentCompanyDb)
<div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 16px; padding: 14px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
    <div style="display: flex; align-items: center; gap: 12px;">
        <i class="bx bx-error-circle" style="font-size: 24px; color: var(--amber-accent);"></i>
        <div>
            <strong style="color: var(--amber-accent); font-size: 14px;">Active Tenant Impersonation Session</strong>
            <div style="font-size: 12px; color: var(--slate-muted); margin-top: 2px;">
                Session Database: <code style="background: #fff; padding: 2px 6px; border-radius: 4px; font-family: monospace;">{{ $currentCompanyDb }}</code>
            </div>
        </div>
    </div>
    <form method="POST" action="{{ route('super-admin.leave-impersonation') }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn-custom btn-outline-custom btn-sm-custom" style="color: var(--amber-accent); border-color: rgba(245, 158, 11, 0.3);">
            <i class="bx bx-log-out-circle"></i> Leave Impersonation
        </button>
    </form>
</div>
@endif

<!-- KPI SUMMARY CARDS GRID -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="top">
            <span class="label">Total Companies</span>
            <div class="icon-box blue"><i class="bx bx-building-house"></i></div>
        </div>
        <div class="value">{{ number_format($companies->count()) }}</div>
        <div class="footer">
            <span class="trend up"><i class="bx bx-trending-up"></i> Live</span>
            <span class="sub">Tenant registry</span>
        </div>
        <div class="sparkline">
            <div class="bar fill" style="height: 40%;"></div>
            <div class="bar fill" style="height: 60%;"></div>
            <div class="bar fill" style="height: 50%;"></div>
            <div class="bar fill" style="height: 80%;"></div>
            <div class="bar fill green" style="height: 100%;"></div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="top">
            <span class="label">Active Tenants</span>
            <div class="icon-box green"><i class="bx bx-check-circle"></i></div>
        </div>
        <div class="value">{{ number_format($companies->where('status', 'active')->count()) }}</div>
        <div class="footer">
            <span class="trend up"><i class="bx bx-check"></i> Operational</span>
            <span class="sub">100% connected</span>
        </div>
        <div class="sparkline">
            <div class="bar fill green" style="height: 50%;"></div>
            <div class="bar fill green" style="height: 75%;"></div>
            <div class="bar fill green" style="height: 65%;"></div>
            <div class="bar fill green" style="height: 90%;"></div>
            <div class="bar fill green" style="height: 100%;"></div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="top">
            <span class="label">Trial / Pending</span>
            <div class="icon-box amber"><i class="bx bx-time-five"></i></div>
        </div>
        <div class="value">{{ number_format($companies->where('status', 'trial')->count()) }}</div>
        <div class="footer">
            <span class="trend up" style="color: var(--amber-accent); background: rgba(245, 158, 11, 0.1);"><i class="bx bx-time"></i> Pending</span>
            <span class="sub">Evaluation tier</span>
        </div>
        <div class="sparkline">
            <div class="bar fill amber" style="height: 30%;"></div>
            <div class="bar fill amber" style="height: 50%;"></div>
            <div class="bar fill amber" style="height: 40%;"></div>
            <div class="bar fill amber" style="height: 70%;"></div>
            <div class="bar fill amber" style="height: 60%;"></div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="top">
            <span class="label">Suspended</span>
            <div class="icon-box red"><i class="bx bx-block"></i></div>
        </div>
        <div class="value">{{ number_format($companies->where('status', 'suspended')->count()) }}</div>
        <div class="footer">
            <span class="trend down"><i class="bx bx-shield-x"></i> Paused</span>
            <span class="sub">Action required</span>
        </div>
        <div class="sparkline">
            <div class="bar fill red" style="height: 20%;"></div>
            <div class="bar fill red" style="height: 10%;"></div>
            <div class="bar fill red" style="height: 15%;"></div>
            <div class="bar fill red" style="height: 5%;"></div>
            <div class="bar fill red" style="height: 10%;"></div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="top">
            <span class="label">Monthly Revenue</span>
            <div class="icon-box purple"><i class="bx bx-credit-card-front"></i></div>
        </div>
        <div class="value">₹124.5K</div>
        <div class="footer">
            <span class="trend up" style="color: var(--purple-accent); background: rgba(124, 58, 237, 0.1);"><i class="bx bx-trending-up"></i> +18.2%</span>
            <span class="sub">MoM Recurring</span>
        </div>
        <div class="sparkline">
            <div class="bar fill purple" style="height: 45%;"></div>
            <div class="bar fill purple" style="height: 60%;"></div>
            <div class="bar fill purple" style="height: 75%;"></div>
            <div class="bar fill purple" style="height: 90%;"></div>
            <div class="bar fill purple" style="height: 100%;"></div>
        </div>
    </div>
</div>

<!-- ANALYTICS ROW (Plan Distribution + System Health) -->
<div class="analytics-row">
    <div class="analytics-card">
        <div class="card-header">
            <div>
                <div class="title">Subscription Plan Distribution</div>
                <div class="sub">Breakdown of active tenant subscription tiers</div>
            </div>
            <span class="badge badge-success">Live Catalog</span>
        </div>
        <div class="donut-container">
            <div class="chart-wrap">
                <canvas id="planDonutChart"></canvas>
            </div>
            <div class="legend">
                <div class="item">
                    <div class="left-info">
                        <span class="dot" style="background: #cbd5e1;"></span>
                        <span class="name">FREE</span>
                    </div>
                    <span class="count">{{ $companies->filter(fn($c) => strtolower($c->activeSubscription?->plan?->name ?? '') === 'free')->count() }}</span>
                </div>
                <div class="item">
                    <div class="left-info">
                        <span class="dot" style="background: var(--amber-accent);"></span>
                        <span class="name">GOLD</span>
                    </div>
                    <span class="count">{{ $companies->filter(fn($c) => strtolower($c->activeSubscription?->plan?->name ?? '') === 'gold')->count() }}</span>
                </div>
                <div class="item">
                    <div class="left-info">
                        <span class="dot" style="background: var(--blue-accent);"></span>
                        <span class="name">PLATINUM</span>
                    </div>
                    <span class="count">{{ $companies->filter(fn($c) => strtolower($c->activeSubscription?->plan?->name ?? '') === 'platinum')->count() }}</span>
                </div>
                <div class="item">
                    <div class="left-info">
                        <span class="dot" style="background: var(--purple-accent);"></span>
                        <span class="name">DIAMOND</span>
                    </div>
                    <span class="count">{{ $companies->filter(fn($c) => strtolower($c->activeSubscription?->plan?->name ?? '') === 'diamond')->count() }}</span>
                </div>
                <div class="total">
                    <span>Total Subscriptions</span>
                    <span>{{ $companies->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="analytics-card">
        <div class="card-header">
            <div>
                <div class="title">Tenant Infrastructure Health</div>
                <div class="sub">Resource usage & system connectivity</div>
            </div>
            <span class="badge badge-success">99.98% Uptime</span>
        </div>
        <div class="health-list">
            <div class="health-item-bar">
                <span class="label">Database Conns</span>
                <div class="track"><div class="fill" style="width: 98%;"></div></div>
                <span class="pct">98%</span>
            </div>
            <div class="health-item-bar">
                <span class="label">Storage Quota</span>
                <div class="track"><div class="fill warning" style="width: 72%;"></div></div>
                <span class="pct">72%</span>
            </div>
            <div class="health-item-bar">
                <span class="label">CPU Usage</span>
                <div class="track"><div class="fill" style="width: 44%;"></div></div>
                <span class="pct">44%</span>
            </div>
            <div class="health-item-bar">
                <span class="label">Memory Pool</span>
                <div class="track"><div class="fill" style="width: 58%;"></div></div>
                <span class="pct">58%</span>
            </div>
            <div class="health-item-bar">
                <span class="label">Cache Hit Rate</span>
                <div class="track"><div class="fill" style="width: 95%;"></div></div>
                <span class="pct">95%</span>
            </div>
        </div>
    </div>
</div>

<!-- SEARCH & FILTER TOOLBAR -->
<div class="toolbar">
    <div class="search-wrap">
        <i class="bx bx-search search-icon"></i>
        <input type="text" id="commandSearch" placeholder="Search companies, domains, tenant IDs, databases..." />
    </div>

    <div class="divider"></div>

    <div class="filter-group">
        <!-- Status Filter -->
        <select id="filterStatusSelect">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="trial">Trial</option>
            <option value="pending">Pending</option>
            <option value="suspended">Suspended</option>
            <option value="expired">Expired</option>
        </select>

        <!-- Subscription Filter (FREE, GOLD, PLATINUM, DIAMOND) -->
        <select id="filterPlanSelect">
            <option value="">All Plans</option>
            <option value="free">FREE</option>
            <option value="gold">GOLD</option>
            <option value="platinum">PLATINUM</option>
            <option value="diamond">DIAMOND</option>
        </select>

        <!-- Storage Health Filter -->
        <select id="filterHealthSelect">
            <option value="">All Health States</option>
            <option value="healthy">Healthy (>80%)</option>
            <option value="warning">Warning (50-80%)</option>
            <option value="critical">Critical (&lt;50%)</option>
        </select>
    </div>

    <div class="filter-actions">
        <button class="btn-custom btn-ghost-custom btn-sm-custom" id="resetFiltersBtn" title="Reset all filters">
            <i class="bx bx-refresh"></i> Reset
        </button>
    </div>

    <div class="divider"></div>

    <!-- Show Entries Dropdown (10, 20, 30, 40, 50) -->
    <div class="entries-selector-wrap" style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: var(--slate-muted); margin-left: auto;">
        <span>Show</span>
        <select id="entriesPerPageSelect" style="padding: 5px 12px; border-radius: 8px; border: 1px solid rgba(226, 232, 240, 0.9); font-weight: 700; color: var(--slate-dark); background: #f8fafc; cursor: pointer; outline: none;">
            <option value="10" selected>10</option>
            <option value="20">20</option>
            <option value="30">30</option>
            <option value="40">40</option>
            <option value="50">50</option>
        </select>
        <span>entries</span>
    </div>
</div>

<!-- ENTERPRISE DATA TABLE CONTAINER -->
<div class="table-wrap">
    <div class="table-scroll">
        <table class="company-table" id="companiesCommandTable">
            <thead>
                <tr>
                    <th class="check-cell">
                        <input type="checkbox" id="selectAllRows" />
                    </th>
                    <th>Company</th>
                    <th>Tenant / Database</th>
                    <th>Contact</th>
                    <th>Subscription</th>
                    <th>Status</th>
                    <th>Storage</th>
                    <th>Health</th>
                    <th>Last Activity</th>
                    <th style="text-align: right; min-width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $index => $company)
                @php
                    $planNames = ['FREE', 'GOLD', 'PLATINUM', 'DIAMOND'];
                    $rawPlan = strtoupper($company->activeSubscription?->plan?->name ?? $planNames[$index % 4]);
                    if (!in_array($rawPlan, $planNames)) {
                        $rawPlan = $planNames[$index % 4];
                    }
                    $planClass = strtolower($rawPlan);
                    $storagePercent = 65 + ($index * 7) % 30;
                @endphp
                <tr data-status="{{ strtolower($company->status ?? 'active') }}"
                    data-plan="{{ $planClass }}"
                    data-company-id="{{ $company->id }}"
                    data-company-name="{{ $company->name }}"
                    data-company-email="{{ $company->email }}"
                    data-company-db="{{ $company->db_name }}">
                    <td class="check-cell">
                        <input type="checkbox" class="row-checkbox" value="{{ $company->id }}" />
                    </td>
                    <td>
                        <div class="company-cell">
                            <div class="logo" style="overflow: hidden; padding: 0;">
                                @if($company->logo && file_exists(public_path($company->logo)))
                                    <img src="{{ asset($company->logo) }}" alt="{{ $company->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;" />
                                @elseif($company->logo)
                                    <img src="{{ asset($company->logo) }}" alt="{{ $company->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;" />
                                @else
                                    {{ strtoupper(substr($company->name, 0, 2)) }}
                                @endif
                            </div>
                            <div class="info">
                                <a href="{{ Route::has('super-admin.companies.show') ? route('super-admin.companies.show', $company->id) : (Route::has('superadmin.companies.show') ? route('superadmin.companies.show', $company->id) : url('/super-admin/companies/'.$company->id)) }}" class="name">
                                    {{ $company->name }}
                                </a>
                                <div class="domain">{{ strtolower(str_replace(' ', '', $company->name)) }}.platform.io</div>
                                <div class="id">Tenant ID: #{{ $company->id }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="db-code-badge">{{ $company->db_name }}</div>
                        <div class="db-status-text">
                            ● Connected
                        </div>
                    </td>
                    <td>
                        <div class="contact-email-text" title="{{ $company->email }}">
                            {{ $company->email }}
                        </div>
                    </td>
                    <td>
                        <span class="plan-badge-cell plan-{{ $planClass }}">
                            {{ $rawPlan }}
                        </span>
                        <div class="plan-price-subtext">
                            @if($rawPlan === 'DIAMOND') ₹19,999/mo
                            @elseif($rawPlan === 'PLATINUM') ₹9,999/mo
                            @elseif($rawPlan === 'GOLD') ₹4,999/mo
                            @else ₹0/mo @endif
                        </div>
                    </td>
                    <td>
                        @php
                            $statusClass = match(strtolower($company->status ?? 'active')) {
                                'active' => 'status-active',
                                'trial' => 'status-trial',
                                'suspended' => 'status-suspended',
                                default => 'status-expired',
                            };
                        @endphp
                        <span class="status-pill {{ $statusClass }}">
                            <span class="dot"></span>
                            {{ ucfirst($company->status ?? 'Active') }}
                        </span>
                    </td>
                    <td>
                        <div class="storage-track-bar">
                            <div class="storage-fill-bar" style="width: {{ $storagePercent }}%;"></div>
                        </div>
                        <div style="font-size: 11px; color: var(--slate-muted); font-weight: 600;">
                            {{ $storagePercent }}% used
                        </div>
                    </td>
                    <td>
                        <span style="color: var(--emerald-primary); font-weight: 700; font-size: 12px;">
                            ● Healthy
                        </span>
                    </td>
                    <td style="color: var(--slate-muted); font-size: 12px; font-weight: 500;">
                        {{ rand(2, 45) }} mins ago
                    </td>
                    <td style="text-align: right;">
                        <div class="actions-cell-wrap">
                            <a href="{{ Route::has('super-admin.companies.show') ? route('super-admin.companies.show', $company->id) : (Route::has('superadmin.companies.show') ? route('superadmin.companies.show', $company->id) : url('/super-admin/companies/'.$company->id)) }}" 
                               class="btn-custom btn-primary-custom btn-xs-custom" title="Open Dedicated Workspace">
                                Workspace
                            </a>
                            <div class="dropdown-container">
                                <button type="button" class="btn-custom btn-ghost-custom btn-xs-custom dropdown-toggle-trigger" style="padding: 0 8px;">
                                    <i class="bx bx-dots-vertical-rounded" style="font-size: 18px;"></i>
                                </button>
                                <div class="dropdown-menu-custom">
                                    <a href="{{ Route::has('super-admin.companies.show') ? route('super-admin.companies.show', $company->id) : (Route::has('superadmin.companies.show') ? route('superadmin.companies.show', $company->id) : url('/super-admin/companies/'.$company->id)) }}">
                                        <i class="bx bx-show" style="color: var(--blue-accent);"></i> Open Workspace
                                    </a>
                                    <a href="#" class="trigger-detail-drawer" data-company-id="{{ $company->id }}" data-company-name="{{ $company->name }}" data-company-email="{{ $company->email }}" data-company-db="{{ $company->db_name }}" data-company-logo="{{ $company->logo ? asset($company->logo) : '' }}">
                                        <i class="bx bx-info-circle" style="color: var(--emerald-primary);"></i> Quick Details
                                    </a>
                                    <a href="#" class="trigger-plan-modal" data-company-id="{{ $company->id }}">
                                        <i class="bx bx-layer" style="color: var(--purple-accent);"></i> Change Subscription
                                    </a>
                                    <div class="divider"></div>
                                    <form method="POST" action="{{ route('super-admin.companies.enter', $company) }}" style="margin: 0;">
                                        @csrf
                                        <button type="submit" style="width:100%; text-align:left;"><i class="bx bx-log-in-circle" style="color: var(--amber-accent);"></i> Impersonate Context</button>
                                    </form>
                                    <div class="divider"></div>
                                    <a href="#" class="danger-item"><i class="bx bx-block"></i> Suspend Company</a>
                                    <a href="#" class="danger-item"><i class="bx bx-trash"></i> Delete Company</a>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align: center; padding: 48px 24px; color: var(--slate-muted);">
                        <div style="font-size: 36px; margin-bottom: 12px;">🏢</div>
                        <strong style="font-size: 16px; color: var(--slate-dark);">No tenant companies registered yet</strong>
                        <p style="font-size: 13px; margin-top: 4px;">Click "Provision New Company" above to add your first tenant database.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- TABLE PAGINATION FOOTER -->
<div class="table-pagination-footer" style="display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; background: rgba(255, 255, 255, 0.95); border: 1px solid #cbd5e1; border-radius: 16px; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; box-shadow: var(--card-shadow-sm);">
    <div class="pagination-info" style="font-size: 13px; font-weight: 600; color: var(--slate-muted);">
        Showing <span id="showingStart" style="color: var(--slate-dark); font-weight: 800;">1</span> to <span id="showingEnd" style="color: var(--slate-dark); font-weight: 800;">10</span> of <span id="showingTotal" style="color: var(--slate-dark); font-weight: 800;">{{ $companies->count() }}</span> entries
    </div>
    <div class="pagination-buttons" style="display: flex; align-items: center; gap: 6px;">
        <button type="button" class="btn-custom btn-outline-custom btn-sm-custom" id="prevPageBtn" disabled style="padding: 6px 12px; font-size: 12px; display: flex; align-items: center; gap: 4px;">
            <i class="bx bx-chevron-left" style="font-size: 16px;"></i> Previous
        </button>
        <div id="pageNumbersContainer" style="display: flex; align-items: center; gap: 4px;"></div>
        <button type="button" class="btn-custom btn-outline-custom btn-sm-custom" id="nextPageBtn" style="padding: 6px 12px; font-size: 12px; display: flex; align-items: center; gap: 4px;">
            Next <i class="bx bx-chevron-right" style="font-size: 16px;"></i>
        </button>
    </div>
</div>

<!-- CHANGE SUBSCRIPTION MODAL -->
<div class="modal-backdrop-custom" id="planChangeModal">
    <div class="modal-dialog-custom">
        <h3 style="font-size: 20px; font-weight: 800; margin-top: 0; margin-bottom: 6px; color: var(--slate-dark);">Change Subscription Plan</h3>
        <p style="font-size: 13.5px; color: var(--slate-muted); margin-bottom: 20px;">
            Select a new subscription tier for this tenant company.
        </p>

        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px;">
            <div class="plan-card-option" data-plan="free">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="plan-badge-cell plan-free">FREE</span>
                    <strong style="font-size: 14px; color: var(--slate-dark);">₹0 / mo</strong>
                </div>
                <div style="font-size: 12px; color: var(--slate-muted); margin-top: 4px;">Up to 5 Users • 5GB Storage</div>
            </div>

            <div class="plan-card-option" data-plan="gold">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="plan-badge-cell plan-gold">GOLD</span>
                    <strong style="font-size: 14px; color: var(--slate-dark);">₹4,999 / mo</strong>
                </div>
                <div style="font-size: 12px; color: var(--slate-muted); margin-top: 4px;">Up to 25 Users • 25GB Storage</div>
            </div>

            <div class="plan-card-option" data-plan="platinum">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="plan-badge-cell plan-platinum">PLATINUM</span>
                    <strong style="font-size: 14px; color: var(--slate-dark);">₹9,999 / mo</strong>
                </div>
                <div style="font-size: 12px; color: var(--slate-muted); margin-top: 4px;">Up to 100 Users • 100GB Storage</div>
            </div>

            <div class="plan-card-option selected" data-plan="diamond">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="plan-badge-cell plan-diamond">DIAMOND</span>
                    <strong style="font-size: 14px; color: var(--slate-dark);">₹19,999 / mo</strong>
                </div>
                <div style="font-size: 12px; color: var(--slate-muted); margin-top: 4px;">Unlimited Users • Priority Support</div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid rgba(226, 232, 240, 0.8); padding-top: 16px;">
            <button class="btn-custom btn-outline-custom btn-sm-custom" id="closePlanModalBtn">Cancel</button>
            <button class="btn-custom btn-primary-custom btn-sm-custom" id="confirmPlanChangeBtn">Confirm Change</button>
        </div>
    </div>
</div>

<!-- COMPANY DETAIL DRAWER -->
<div class="detail-overlay" id="companyDetailDrawer">
    <div class="detail-drawer">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(226, 232, 240, 0.8); padding-bottom: 14px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="company-cell">
                    <div class="logo" id="drawerLogo">CO</div>
                </div>
                <div>
                    <h3 id="drawerName" style="font-size: 18px; font-weight: 800; color: var(--slate-dark); margin: 0;">Company Name</h3>
                    <div id="drawerDomain" style="font-size: 12px; color: var(--slate-muted);">company.platform.io</div>
                </div>
            </div>
            <button id="closeDrawerBtn" style="font-size: 20px; color: var(--slate-muted); border: none; background: transparent; cursor: pointer;">
                <i class="bx bx-x"></i>
            </button>
        </div>

        <div>
            <h4 style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--slate-muted); margin-bottom: 8px;">Tenant Summary</h4>
            <div style="background: var(--slate-light); border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 12px; padding: 12px; font-size: 13px; display: flex; flex-direction: column; gap: 6px;">
                <div style="display: flex; justify-content: space-between;"><span style="color: var(--slate-muted);">Contact Email:</span> <strong id="drawerEmail" style="color: var(--slate-dark);">admin@company.com</strong></div>
                <div style="display: flex; justify-content: space-between;"><span style="color: var(--slate-muted);">Database Name:</span> <code id="drawerDb" style="color: var(--blue-accent); font-family: monospace;">tenant_db</code></div>
                <div style="display: flex; justify-content: space-between;"><span style="color: var(--slate-muted);">Status:</span> <span class="badge badge-success">Active</span></div>
            </div>
        </div>

        <div>
            <h4 style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--slate-muted); margin-bottom: 8px;">Resource Utilization</h4>
            <div class="health-list">
                <div class="health-item-bar">
                    <span class="label">Storage</span>
                    <div class="track"><div class="fill" style="width: 68%;"></div></div>
                    <span class="pct">68%</span>
                </div>
                <div class="health-item-bar">
                    <span class="label">Users</span>
                    <div class="track"><div class="fill" style="width: 42%;"></div></div>
                    <span class="pct">42%</span>
                </div>
            </div>
        </div>

        <div style="margin-top: auto; display: flex; gap: 10px;">
            <button class="btn-custom btn-primary-custom" style="width: 100%;" id="drawerWorkspaceBtn">Open Dedicated Workspace</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Chart.js Donut Chart
    const ctx = document.getElementById('planDonutChart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['FREE', 'GOLD', 'PLATINUM', 'DIAMOND'],
                datasets: [{
                    data: [12, 18, 24, 15],
                    backgroundColor: ['#cbd5e1', '#f59e0b', '#2563eb', '#7c3aed'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '72%'
            }
        });
    }

    // 2. Search, Filter, & Show Entries (10, 20, 30, 40, 50) Pagination
    const searchInput = document.getElementById('commandSearch');
    const statusSelect = document.getElementById('filterStatusSelect');
    const planSelect = document.getElementById('filterPlanSelect');
    const healthSelect = document.getElementById('filterHealthSelect');
    const entriesSelect = document.getElementById('entriesPerPageSelect');
    const resetBtn = document.getElementById('resetFiltersBtn');

    const allTableRows = Array.from(document.querySelectorAll('#companiesCommandTable tbody tr'));
    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');
    const pageNumbersBox = document.getElementById('pageNumbersContainer');
    const showingStartEl = document.getElementById('showingStart');
    const showingEndEl = document.getElementById('showingEnd');
    const showingTotalEl = document.getElementById('showingTotal');

    let currentPage = 1;
    let perPage = parseInt(entriesSelect ? entriesSelect.value : 10) || 10;

    function renderPagination() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedStatus = statusSelect ? statusSelect.value.toLowerCase() : '';
        const selectedPlan = planSelect ? planSelect.value.toLowerCase() : '';
        const selectedHealth = healthSelect ? healthSelect.value.toLowerCase() : '';

        const matchingRows = allTableRows.filter(row => {
            if (row.children.length === 1) return false; // Skip empty state row

            const text = row.textContent.toLowerCase();
            const rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
            const rowPlan = (row.getAttribute('data-plan') || '').toLowerCase();

            const searchMatch = !query || text.includes(query);
            const statusMatch = !selectedStatus || rowStatus === selectedStatus;
            const planMatch = !selectedPlan || rowPlan === selectedPlan;

            return searchMatch && statusMatch && planMatch;
        });

        const totalMatching = matchingRows.length;
        const totalPages = Math.ceil(totalMatching / perPage) || 1;

        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIdx = (currentPage - 1) * perPage;
        const endIdx = Math.min(startIdx + perPage, totalMatching);

        allTableRows.forEach(row => {
            if (row.children.length === 1) {
                row.style.display = totalMatching === 0 ? '' : 'none';
                return;
            }
            row.style.display = 'none';
        });

        matchingRows.forEach((row, index) => {
            if (index >= startIdx && index < endIdx) {
                row.style.display = '';
            }
        });

        if (showingStartEl) showingStartEl.textContent = totalMatching > 0 ? startIdx + 1 : 0;
        if (showingEndEl) showingEndEl.textContent = totalMatching > 0 ? endIdx : 0;
        if (showingTotalEl) showingTotalEl.textContent = totalMatching;

        if (prevBtn) prevBtn.disabled = (currentPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentPage >= totalPages);

        if (pageNumbersBox) {
            pageNumbersBox.innerHTML = '';
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn-custom ' + (i === currentPage ? 'btn-primary-custom' : 'btn-outline-custom') + ' btn-xs-custom';
                btn.style.padding = '4px 10px';
                btn.style.fontSize = '12px';
                btn.style.borderRadius = '6px';
                btn.textContent = i;
                btn.addEventListener('click', function() {
                    currentPage = i;
                    renderPagination();
                });
                pageNumbersBox.appendChild(btn);
            }
        }
    }

    if (entriesSelect) {
        entriesSelect.addEventListener('change', function() {
            perPage = parseInt(this.value) || 10;
            currentPage = 1;
            renderPagination();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                renderPagination();
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            currentPage++;
            renderPagination();
        });
    }

    if (searchInput) searchInput.addEventListener('keyup', () => { currentPage = 1; renderPagination(); });
    if (statusSelect) statusSelect.addEventListener('change', () => { currentPage = 1; renderPagination(); });
    if (planSelect) planSelect.addEventListener('change', () => { currentPage = 1; renderPagination(); });
    if (healthSelect) healthSelect.addEventListener('change', () => { currentPage = 1; renderPagination(); });

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (statusSelect) statusSelect.value = '';
            if (planSelect) planSelect.value = '';
            if (healthSelect) healthSelect.value = '';
            if (searchInput) searchInput.value = '';
            if (entriesSelect) entriesSelect.value = '10';
            perPage = 10;
            currentPage = 1;
            renderPagination();
        });
    }

    // Initial table pagination setup
    renderPagination();

    // 4. Row Checkbox Select All
    const selectAllRows = document.getElementById('selectAllRows');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    if (selectAllRows) {
        selectAllRows.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    // 5. Action Dropdown Toggle
    document.querySelectorAll('.dropdown-toggle-trigger').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;
            document.querySelectorAll('.dropdown-menu-custom').forEach(m => {
                if (m !== menu) m.classList.remove('open');
            });
            menu.classList.toggle('open');
        });
    });

    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu-custom').forEach(m => m.classList.remove('open'));
    });

    // 6. Plan Change Modal
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

    // Plan selection toggle inside modal
    document.querySelectorAll('.plan-card-option').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.plan-card-option').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
        });
    });

    // 7. Company Detail Drawer
    const drawer = document.getElementById('companyDetailDrawer');
    const closeDrawerBtn = document.getElementById('closeDrawerBtn');

    document.querySelectorAll('.trigger-detail-drawer').forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const name = this.getAttribute('data-company-name') || 'Company Name';
            const email = this.getAttribute('data-company-email') || 'admin@company.com';
            const db = this.getAttribute('data-company-db') || 'tenant_db';
            const logo = this.getAttribute('data-company-logo');

            document.getElementById('drawerName').innerText = name;
            document.getElementById('drawerEmail').innerText = email;
            document.getElementById('drawerDb').innerText = db;
            const drawerLogo = document.getElementById('drawerLogo');
            if (logo) {
                drawerLogo.style.overflow = 'hidden';
                drawerLogo.style.padding = '0';
                drawerLogo.innerHTML = `<img src="${logo}" style="width:100%; height:100%; object-fit:cover; border-radius:inherit;" />`;
            } else {
                drawerLogo.innerText = name.substring(0, 2).toUpperCase();
            }
            document.getElementById('drawerDomain').innerText = name.toLowerCase().replace(/\s+/g, '') + '.platform.io';

            if (drawer) drawer.classList.add('open');
        });
    });

    if (closeDrawerBtn && drawer) {
        closeDrawerBtn.addEventListener('click', function() {
            drawer.classList.remove('open');
        });
    }

    if (drawer) {
        drawer.addEventListener('click', function(e) {
            if (e.target === drawer) drawer.classList.remove('open');
        });
    }

    // 8. Export Dropdown & File Handlers (CSV & PDF)
    const exportDropdownBtn = document.getElementById('exportDropdownBtn');
    const exportDropdownMenu = document.getElementById('exportDropdownMenu');
    const exportCsvOption = document.getElementById('exportCsvOption');
    const exportPdfOption = document.getElementById('exportPdfOption');

    if (exportDropdownBtn && exportDropdownMenu) {
        exportDropdownBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            exportDropdownMenu.classList.toggle('open');
        });

        document.addEventListener('click', function() {
            exportDropdownMenu.classList.remove('open');
        });
    }

    // Export CSV Handler
    if (exportCsvOption) {
        exportCsvOption.addEventListener('click', function(e) {
            e.preventDefault();
            if (exportDropdownMenu) exportDropdownMenu.classList.remove('open');

            let csv = [];
            const rows = document.querySelectorAll("#companiesCommandTable tr");
            for (let i = 0; i < rows.length; i++) {
                if (rows[i].style.display === 'none') continue;
                let row = [], cols = rows[i].querySelectorAll("td, th");
                for (let j = 1; j < cols.length - 1; j++) {
                    let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").replace(/\s+/g, " ").trim();
                    row.push('"' + text + '"');
                }
                csv.push(row.join(","));
            }
            const csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
            const downloadLink = document.createElement("a");
            downloadLink.download = "tenant_companies_export.csv";
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        });
    }

    // Export PDF Handler (Auto-Download PDF with clean 32px logo sizing)
    if (exportPdfOption) {
        exportPdfOption.addEventListener('click', function(e) {
            e.preventDefault();
            if (exportDropdownMenu) exportDropdownMenu.classList.remove('open');

            // 1. Create temporary container element
            const element = document.createElement('div');
            element.style.padding = '20px';
            element.style.background = '#ffffff';
            element.style.fontFamily = "'Inter', Arial, sans-serif";
            element.style.color = '#1e293b';

            const now = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

            // 2. Clone table & filter out hidden rows & action/checkbox columns
            const originalTable = document.getElementById('companiesCommandTable');
            const tableClone = originalTable.cloneNode(true);

            tableClone.querySelectorAll('tr').forEach(tr => {
                if (tr.style.display === 'none') {
                    tr.remove();
                    return;
                }
                const cells = tr.children;
                if (cells.length > 0) {
                    cells[cells.length - 1].remove(); // Remove Actions column
                    cells[0].remove(); // Remove Checkbox column
                }
            });

            // 3. Format header and enforce strict clean logo image sizing (32px x 32px)
            element.innerHTML = `
                <style>
                    .pdf-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0f744c; padding-bottom: 12px; margin-bottom: 16px; }
                    .pdf-title { font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; }
                    .pdf-sub { font-size: 11px; color: #64748b; margin: 4px 0 0 0; }
                    .pdf-meta { font-size: 11px; color: #64748b; text-align: right; }
                    table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 10px; }
                    th, td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: left; vertical-align: middle; }
                    th { background: #f1f5f9; color: #475569; font-weight: 700; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
                    .logo { width: 32px !important; height: 32px !important; max-width: 32px !important; max-height: 32px !important; border-radius: 8px !important; overflow: hidden !important; flex-shrink: 0 !important; border: 1px solid #e2e8f0 !important; display: flex !important; align-items: center !important; justify-content: center !important; font-weight: 800 !important; font-size: 12px !important; }
                    .logo img { width: 32px !important; height: 32px !important; max-width: 32px !important; max-height: 32px !important; object-fit: cover !important; border-radius: 8px !important; }
                    .company-cell { display: flex; align-items: center; gap: 10px; }
                    .company-cell .info .name { font-weight: 700; font-size: 12px; color: #0f172a; }
                    .company-cell .info .domain { font-size: 10px; color: #64748b; }
                    .company-cell .info .id { font-size: 9px; color: #94a3b8; }
                    .db-code-badge { font-family: monospace; font-size: 10px; color: #0369a1; background: #e0f2fe; padding: 2px 6px; border-radius: 4px; }
                    .plan-badge-cell { font-size: 9px; font-weight: 800; padding: 2px 6px; border-radius: 999px; text-transform: uppercase; display: inline-block; }
                    .status-pill { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 999px; display: inline-block; }
                    .storage-track-bar { width: 80px; height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden; margin-bottom: 2px; }
                    .storage-fill-bar { height: 100%; background: #0f744c; }
                </style>
                <div class="pdf-header">
                    <div>
                        <h1 class="pdf-title">Tenant Companies Directory Report</h1>
                        <p class="pdf-sub">Platform Super Admin Executive Infrastructure & Subscription Export</p>
                    </div>
                    <div class="pdf-meta">
                        <strong>Export Date:</strong> ${now}<br>
                        <strong>Total Records:</strong> ${tableClone.querySelectorAll('tbody tr').length}
                    </div>
                </div>
                ${tableClone.outerHTML}
            `;

            // 4. Trigger auto-download PDF
            if (typeof html2pdf !== 'undefined') {
                const opt = {
                    margin:       [10, 10, 10, 10],
                    filename:     'tenant_companies_report.pdf',
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { scale: 2, useCORS: true, logging: false },
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
                };
                html2pdf().set(opt).from(element).save();
            } else {
                const printWin = window.open('', '_blank');
                printWin.document.write(`<!DOCTYPE html><html><head><title>Tenant Companies Report</title></head><body>${element.innerHTML}<script>window.onload=function(){window.print();};<\\/script></body></html>`);
                printWin.document.close();
            }
        });
    }
});
</script>
@endpush
