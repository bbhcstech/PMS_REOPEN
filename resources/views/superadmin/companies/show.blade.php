@extends('layouts.superadmin')

@section('title', 'Super Admin · ' . $company->name . ' Workspace')
@section('page_title', 'Company Workspace')
@section('page_subtitle', 'Enterprise tenant command center, resource allocation, and connection telemetry.')

@section('content')
<style>
    /* ============================================================
       DESIGN TOKENS — LIGHT MODE (DEFAULT)
       ============================================================ */
    :root {
        --primary: #2F6BFF;
        --primary-hover: #047857;
        --primary-glow: rgba(47, 107, 255, 0.18);
        --bg-main: #f8fafc;
        --bg-surface: #ffffff;
        --bg-subtle: #f8fafc;
        --bg-hover: #f1f5f9;
        --border-color: #cbd5e1;
        --border-subtle: #e2e8f0;
        --text-main: #0f172a;
        --text-muted: #475569;
        --text-subtle: #64748b;
        --success: #10b981;
        --success-bg: #EEF2FF;
        --success-border: #C7D2FE;
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

    /* ============================================================
       DESIGN TOKENS — DARK THEME OVERRIDES
       ============================================================ */
    html[data-pms-theme="dark"],
    html[data-theme="dark"],
    html[data-bs-theme="dark"] {
        --primary: #2F6BFF;
        --primary-hover: #1E4FCC;
        --primary-glow: rgba(47, 107, 255, 0.25);
        --bg-main: #070B1A;
        --bg-surface: #0F1530;
        --bg-subtle: #141B3D;
        --bg-hover: #1A2247;
        --border-color: rgba(238, 241, 251, 0.12);
        --border-subtle: rgba(238, 241, 251, 0.08);
        --text-main: #EEF1FB;
        --text-muted: #CBD5E1;
        --text-subtle: #9AA3C7;
        --shadow-card: 0 10px 25px -5px rgba(0, 0, 0, 0.4), 0 8px 10px -6px rgba(0, 0, 0, 0.3);

        --success: #10b981;
        --success-bg: rgba(47, 107, 255, 0.15);
        --success-border: rgba(79, 131, 255, 0.3);

        --warning: #f59e0b;
        --warning-bg: rgba(245, 158, 11, 0.15);
        --warning-border: rgba(251, 191, 36, 0.35);

        --danger: #ef4444;
        --danger-bg: rgba(239, 68, 68, 0.15);
        --danger-border: rgba(248, 113, 113, 0.35);

        --plan-free-bg: #141b3d;
        --plan-free-text: #cbd5e1;
        --plan-free-border: rgba(238, 241, 251, 0.16);

        --plan-gold-bg: rgba(245, 158, 11, 0.15);
        --plan-gold-text: #fbbf24;
        --plan-gold-border: rgba(245, 158, 11, 0.35);

        --plan-platinum-bg: rgba(2, 132, 199, 0.15);
        --plan-platinum-text: #38bdf8;
        --plan-platinum-border: rgba(56, 189, 248, 0.35);

        --plan-diamond-bg: rgba(109, 40, 217, 0.2);
        --plan-diamond-text: #c084fc;
        --plan-diamond-border: rgba(192, 132, 252, 0.35);
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
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    .back-btn-link:hover {
        color: var(--primary);
        border-color: var(--primary);
        background: var(--bg-hover);
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

    /* FLASH ALERT */
    .flash-alert-success {
        background: var(--success-bg);
        border: 1px solid var(--success-border);
        border-radius: 14px;
        padding: 14px 20px;
        margin-bottom: 24px;
        color: var(--success);
        font-size: 13.5px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* HEADER CARD */
    .company-header-card {
        background: var(--bg-surface);
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
        background: linear-gradient(90deg, #2F6BFF 0%, #10b981 50%, #3b82f6 100%);
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
        background: linear-gradient(135deg, var(--primary) 0%, #10b981 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 26px;
        box-shadow: 0 10px 20px var(--primary-glow);
        flex-shrink: 0;
        overflow: hidden;
        border: 2.5px solid var(--bg-surface);
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
        background: var(--bg-subtle);
        padding: 3px 10px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
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
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
        color: #ffffff !important;
        box-shadow: 0 4px 12px var(--primary-glow);
    }
    .btn-primary-custom:hover {
        background: linear-gradient(135deg, var(--primary-hover) 0%, var(--primary) 100%);
        transform: translateY(-1px);
        color: #ffffff !important;
    }
    .btn-outline-custom {
        background: var(--bg-surface);
        color: var(--text-main);
        border-color: var(--border-color);
    }
    .btn-outline-custom:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: var(--bg-hover);
    }
    .btn-warning-custom {
        background: var(--warning-bg);
        color: var(--warning);
        border: 1px solid var(--warning-border);
    }
    .btn-warning-custom:hover {
        background: var(--warning-bg);
        opacity: 0.9;
        transform: translateY(-1px);
    }
    .btn-danger-custom {
        background: var(--danger-bg);
        color: var(--danger);
        border: 1px solid var(--danger-border);
    }
    .btn-danger-custom:hover {
        background: var(--danger-bg);
        opacity: 0.9;
        transform: translateY(-1px);
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
        background: var(--bg-surface);
        border-radius: 18px;
        border: 1px solid var(--border-color);
        padding: 22px 20px;
        box-shadow: var(--shadow-card);
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }
    .metric-summary-card:hover {
        transform: translateY(-3px);
        border-color: var(--primary);
        box-shadow: 0 10px 25px -5px var(--primary-glow);
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
        background: var(--bg-subtle);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .metric-summary-card .icon-box.icon-box-blue {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
    }
    .metric-summary-card .icon-box.icon-box-purple {
        background: rgba(139, 92, 246, 0.12);
        color: #8b5cf6;
    }
    .metric-summary-card .icon-box.icon-box-amber {
        background: var(--warning-bg);
        color: var(--warning);
    }
    .metric-summary-card .icon-box.icon-box-success {
        background: var(--success-bg);
        color: var(--success);
    }
    .metric-summary-card .icon-box.icon-box-danger {
        background: var(--danger-bg);
        color: var(--danger);
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
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 14px 22px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        box-shadow: var(--shadow-card);
    }

    /* NAVIGATION TABS */
    .nav-tabs-wrapper {
        background: var(--bg-surface);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-card);
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
        background: var(--bg-surface);
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

    .db-code-pill {
        font-family: monospace;
        color: #0284c7;
        background: #e0f2fe;
        border: 1px solid #bae6fd;
        padding: 2px 8px;
        border-radius: 6px;
        display: inline-block;
        font-weight: 700;
    }
    html[data-pms-theme="dark"] .db-code-pill,
    html[data-theme="dark"] .db-code-pill,
    html[data-bs-theme="dark"] .db-code-pill {
        color: #38bdf8;
        background: rgba(2, 132, 199, 0.2);
        border-color: rgba(56, 189, 248, 0.3);
    }

    .password-box-pill {
        font-family: monospace;
        font-weight: 700;
        background: var(--bg-subtle);
        padding: 4px 10px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        color: var(--text-main);
    }
    .password-btn-icon {
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 4px 8px;
        cursor: pointer;
        color: var(--text-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    .password-btn-icon:hover {
        color: var(--primary);
        border-color: var(--primary);
        background: var(--bg-hover);
    }

    /* INFRASTRUCTURE TELEMETRY ROW */
    .telemetry-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        background: var(--bg-subtle);
        border: 1px solid var(--border-subtle);
        border-radius: 14px;
        margin-bottom: 12px;
    }
    .telemetry-row:last-child { margin-bottom: 0; }
    .telemetry-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: var(--primary);
    }

    /* TABLES & DIRECTORY */
    .workspace-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .workspace-table thead tr {
        border-bottom: 2px solid var(--border-color);
        text-align: left;
        color: var(--text-subtle);
        background: var(--bg-subtle);
    }
    .workspace-table th {
        padding: 14px 16px;
        font-weight: 800;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .workspace-table tbody tr {
        border-bottom: 1px solid var(--border-subtle);
        transition: background 0.15s ease;
    }
    .workspace-table tbody tr:hover {
        background: var(--bg-hover) !important;
    }

    /* ROLE BADGES */
    .role-pill {
        font-weight: 800;
        font-size: 11.5px;
        padding: 4px 12px;
        border-radius: 999px;
        display: inline-block;
    }
    .role-pill.role-admin { background: rgba(37, 99, 235, 0.12); color: #2563eb; border: 1px solid rgba(37, 99, 235, 0.25); }
    .role-pill.role-dev { background: var(--success-bg); color: var(--success); border: 1px solid var(--success-border); }
    .role-pill.role-manager { background: var(--warning-bg); color: var(--warning); border: 1px solid var(--warning-border); }
    .role-pill.role-user { background: var(--bg-subtle); color: var(--text-muted); border: 1px solid var(--border-color); }

    html[data-pms-theme="dark"] .role-pill.role-admin,
    html[data-theme="dark"] .role-pill.role-admin,
    html[data-bs-theme="dark"] .role-pill.role-admin { color: #60a5fa; }

    /* ADMIN ITEM BOX */
    .admin-card-item {
        font-size: 14px;
        padding: 18px 22px;
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
    }

    /* SUBSCRIPTION PLAN GRID ITEM */
    .plan-info-card {
        border-radius: 16px;
        padding: 20px;
        text-align: center;
    }
    .plan-info-card.plan-free { background: var(--plan-free-bg); border: 1px solid var(--plan-free-border); }
    .plan-info-card.plan-gold { background: var(--plan-gold-bg); border: 1px solid var(--plan-gold-border); }
    .plan-info-card.plan-platinum { background: var(--plan-platinum-bg); border: 1px solid var(--plan-platinum-border); }
    .plan-info-card.plan-diamond { background: var(--plan-diamond-bg); border: 1px solid var(--plan-diamond-border); }

    .plan-info-card .plan-price {
        font-size: 20px;
        font-weight: 800;
        margin: 10px 0;
    }
    .plan-info-card.plan-free .plan-price { color: var(--plan-free-text); }
    .plan-info-card.plan-gold .plan-price { color: var(--plan-gold-text); }
    .plan-info-card.plan-platinum .plan-price { color: var(--plan-platinum-text); }
    .plan-info-card.plan-diamond .plan-price { color: var(--plan-diamond-text); }

    .plan-info-card .plan-subtext {
        font-size: 12px;
        font-weight: 600;
    }
    .plan-info-card.plan-free .plan-subtext { color: var(--text-subtle); }
    .plan-info-card.plan-gold .plan-subtext { color: var(--plan-gold-text); }
    .plan-info-card.plan-platinum .plan-subtext { color: var(--plan-platinum-text); }
    .plan-info-card.plan-diamond .plan-subtext { color: var(--plan-diamond-text); }

    /* USAGE STAT CARD */
    .usage-stat-card {
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 22px;
        text-align: center;
    }

    /* SETTINGS ACTION CARDS */
    .settings-action-card {
        border-radius: 16px;
        padding: 22px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .settings-action-card.card-warning {
        background: var(--warning-bg);
        border: 1px solid var(--warning-border);
    }
    .settings-action-card.card-danger {
        background: var(--danger-bg);
        border: 1px solid var(--danger-border);
    }

    /* MODAL BACKDROP & DIALOG */
    .modal-backdrop-custom {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(7, 11, 26, 0.75);
        backdrop-filter: blur(6px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
    }
    .modal-backdrop-custom.open { display: flex; }
    .modal-dialog-custom {
        background: var(--bg-surface);
        border-radius: 20px;
        border: 1px solid var(--border-color);
        padding: 28px;
        max-width: 520px;
        width: 100%;
        box-shadow: var(--shadow-card);
        color: var(--text-main);
    }

    .plan-card-option {
        background: var(--bg-subtle);
        border: 1.5px solid var(--border-color);
        border-radius: 14px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }
    .plan-card-option:not(.disabled):hover {
        border-color: var(--primary);
        background: var(--bg-hover);
        transform: translateY(-1px);
    }
    .plan-card-option.selected {
        border-color: var(--primary) !important;
        background: var(--bg-hover) !important;
        box-shadow: 0 0 0 1.5px var(--primary), 0 4px 12px var(--primary-glow);
    }
    .plan-card-option.disabled {
        opacity: 0.45;
        cursor: not-allowed !important;
        background: var(--bg-subtle);
        border: 1.5px dashed var(--border-color);
        user-select: none;
    }
    .plan-card-option.disabled * {
        cursor: not-allowed !important;
    }
    .plan-tier-status-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        line-height: 1;
    }
    .plan-tier-status-current {
        background: rgba(47, 107, 255, 0.12);
        color: var(--primary);
        border: 1px solid rgba(47, 107, 255, 0.25);
    }
    .plan-tier-status-restricted {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border: 1px solid rgba(239, 68, 68, 0.25);
    }
    .plan-tier-status-upgrade {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }

    .user-avatar-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, #10b981 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 13.5px;
        box-shadow: 0 4px 8px var(--primary-glow);
        flex-shrink: 0;
        border: 1.5px solid var(--bg-surface);
    }

    /* COMPANY EDIT FORM STYLES */
    .form-grid-layout {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
    }
    .form-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
    }
    .form-grid-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
    }
    @media (max-width: 992px) {
        .form-grid-layout, .form-grid-3, .form-grid-4 {
            grid-template-columns: repeat(1, 1fr) !important;
        }
    }
    .form-field-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-field-group.full-width {
        grid-column: 1 / -1;
    }
    .form-field-label {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .form-field-label .req {
        color: var(--danger);
    }
    .form-field-label .help-hint {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-subtle);
        margin-left: auto;
    }
    .input-with-icon-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    .input-with-icon-wrapper i.input-icon {
        position: absolute;
        left: 14px;
        font-size: 18px;
        color: var(--text-subtle);
        pointer-events: none;
        transition: color 0.2s ease;
    }
    .form-control-custom {
        width: 100%;
        padding: 11px 14px 11px 40px;
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        font-size: 13.5px;
        font-weight: 500;
        color: var(--text-main);
        background: var(--bg-subtle);
        outline: none;
        transition: all 0.2s ease;
        font-family: inherit;
    }
    .form-control-custom:focus {
        border-color: var(--primary);
        background: var(--bg-surface);
        box-shadow: 0 0 0 3px var(--primary-glow);
    }
    .input-with-icon-wrapper:focus-within i.input-icon {
        color: var(--primary);
    }
    .form-control-custom:disabled, .form-control-custom[readonly] {
        opacity: 0.8;
        cursor: not-allowed;
        background: var(--bg-hover);
        border-style: dashed;
    }
    .form-control-textarea {
        width: 100%;
        padding: 12px 14px;
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        font-size: 13.5px;
        font-weight: 500;
        color: var(--text-main);
        background: var(--bg-subtle);
        outline: none;
        transition: all 0.2s ease;
        resize: vertical;
        font-family: inherit;
        min-height: 80px;
    }
    .form-control-textarea:focus {
        border-color: var(--primary);
        background: var(--bg-surface);
        box-shadow: 0 0 0 3px var(--primary-glow);
    }
    .form-section-divider {
        border: 0;
        height: 1px;
        background: var(--border-subtle);
        margin: 24px 0 20px 0;
    }
    .form-section-subtitle {
        font-size: 13px;
        font-weight: 800;
        color: var(--text-subtle);
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .logo-edit-preview-box {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        background: var(--bg-subtle);
        border: 1px dashed var(--border-color);
        border-radius: 14px;
    }
    .logo-preview-thumb {
        width: 64px;
        height: 64px;
        border-radius: 14px;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .logo-preview-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

@php
    $planNames = ['FREE', 'GOLD', 'PLATINUM', 'DIAMOND'];
    $rawPlan = strtoupper($company->activeSubscription?->plan?->name ?? 'FREE');
    if (!in_array($rawPlan, $planNames)) { $rawPlan = 'FREE'; }
    $planClass = strtolower($rawPlan);
    $companyStatus = strtolower($company->status ?? 'active');

    $planHierarchy = [
        'free' => [
            'level' => 0,
            'name' => 'FREE',
            'badge_class' => 'plan-free',
            'price' => '₹0 / mo',
            'features' => 'Up to 5 Users • 5GB Storage',
        ],
        'gold' => [
            'level' => 1,
            'name' => 'GOLD',
            'badge_class' => 'plan-gold',
            'price' => '₹4,999 / mo',
            'features' => 'Up to 25 Users • 25GB Storage',
        ],
        'platinum' => [
            'level' => 2,
            'name' => 'PLATINUM',
            'badge_class' => 'plan-platinum',
            'price' => '₹9,999 / mo',
            'features' => 'Up to 100 Users • 100GB Storage',
        ],
        'diamond' => [
            'level' => 3,
            'name' => 'DIAMOND',
            'badge_class' => 'plan-diamond',
            'price' => '₹19,999 / mo',
            'features' => 'Unlimited Users • Priority Support',
        ],
    ];

    $currentCompanyPlanSlug = strtolower($rawPlan);
    $currentTierLevel = $planHierarchy[$currentCompanyPlanSlug]['level'] ?? 0;

    // Retrieve highest tier level company has achieved (e.g. if company was previously upgraded)
    $highestTierLevel = 0;
    try {
        $highestTierLevel = \App\Services\PlanEligibilityService::getHighestLevel($company);
    } catch (\Throwable $e) {
        $highestTierLevel = $currentTierLevel;
    }
    $minAllowedTierLevel = max($currentTierLevel, $highestTierLevel);
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

    <!-- FLASH ALERTS -->
    @if(session('success'))
        <div class="flash-alert-success">
            <i class="bx bx-check-circle" style="font-size: 22px; color: var(--success);"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error') || $errors->has('error'))
        <div class="flash-alert-error" style="background: var(--danger-bg); border: 1.5px solid var(--danger-border); color: var(--danger); padding: 14px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; font-weight: 600; font-size: 14px;">
            <i class="bx bx-error-circle" style="font-size: 22px; color: var(--danger);"></i> {{ session('error') ?? $errors->first('error') }}
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
                <button type="submit" class="btn-custom btn-warning-custom btn-sm-custom">
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
                <div class="icon-box icon-box-blue"><i class="bx bx-user-pin"></i></div>
            </div>
            <div class="value">{{ $adminsCount ?? 0 }}</div>
            <div class="subtext">Company admins</div>
        </div>

        <div class="metric-summary-card">
            <div class="header-row">
                <div class="label">STORAGE</div>
                <div class="icon-box icon-box-purple"><i class="bx bx-hard-drive"></i></div>
            </div>
            <div class="value">0%</div>
            <div class="subtext">0 MB / {{ $company->max_storage_mb ?? 10000 }} MB</div>
        </div>

        <div class="metric-summary-card">
            <div class="header-row">
                <div class="label">DATABASE</div>
                <div class="icon-box {{ ($dbConnected ?? true) ? 'icon-box-success' : 'icon-box-danger' }}">
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
                <div class="icon-box icon-box-amber"><i class="bx bx-layer"></i></div>
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
                        <div class="val"><span class="db-code-pill">{{ $company->db_name }}</span></div>
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
                            <span id="companyPasswordDisplay" data-raw-password="{{ $companyPassword }}" class="password-box-pill">
                                {{ $companyPassword ? str_repeat('•', min(strlen($companyPassword), 10)) : 'N/A' }}
                            </span>
                            @if($companyPassword)
                                <button type="button" id="btnToggleCompanyPassword" onclick="toggleCompanyPassword()" title="Show/Hide Password" class="password-btn-icon">
                                    <i class="bx bx-show" id="passwordEyeIcon" style="font-size: 16px;"></i>
                                </button>
                                <button type="button" onclick="copyCompanyPassword('{{ addslashes($companyPassword) }}', this)" title="Copy Password" class="password-btn-icon">
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
                <table class="workspace-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenantUsers as $u)
                            <tr>
                                <td style="padding: 14px 16px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        @if(!empty($u->profile_image) && file_exists(public_path($u->profile_image)))
                                            <img src="{{ asset($u->profile_image) }}" alt="{{ $u->name }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1.5px solid var(--border-color);" />
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
                                        $roleClass = match(strtolower($u->role ?? '')) {
                                            'admin', 'superadmin' => 'role-admin',
                                            'developer', 'dev' => 'role-dev',
                                            'hr', 'manager' => 'role-manager',
                                            default => 'role-user',
                                        };
                                    @endphp
                                    <span class="role-pill {{ $roleClass }}">
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
                                    <i class="bx bx-group" style="font-size: 36px; color: var(--border-color); margin-bottom: 8px;"></i><br>
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
                    <div class="admin-card-item">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            @if(!empty($admin->profile_image) && file_exists(public_path($admin->profile_image)))
                                <img src="{{ asset($admin->profile_image) }}" alt="{{ $admin->name }}" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-color);" />
                            @else
                                <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, #10b981 100%); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 17px; box-shadow: 0 4px 10px var(--primary-glow);">
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
                        <i class="bx bx-user-x" style="font-size: 36px; color: var(--border-color); margin-bottom: 8px;"></i><br>
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
                <div class="plan-info-card plan-free">
                    <span class="plan-badge plan-free">FREE</span>
                    <div class="plan-price">₹0 / mo</div>
                    <div class="plan-subtext">Up to 5 Users • 5GB Storage</div>
                </div>
                <div class="plan-info-card plan-gold">
                    <span class="plan-badge plan-gold">GOLD</span>
                    <div class="plan-price">₹4,999 / mo</div>
                    <div class="plan-subtext">Up to 25 Users • 25GB Storage</div>
                </div>
                <div class="plan-info-card plan-platinum">
                    <span class="plan-badge plan-platinum">PLATINUM</span>
                    <div class="plan-price">₹9,999 / mo</div>
                    <div class="plan-subtext">Up to 100 Users • 100GB Storage</div>
                </div>
                <div class="plan-info-card plan-diamond">
                    <span class="plan-badge plan-diamond">DIAMOND</span>
                    <div class="plan-price">₹19,999 / mo</div>
                    <div class="plan-subtext">Unlimited Users • Priority Support</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 5: BILLING & INVOICES -->
    <div class="workspace-tab-content" id="tab-billing">
        <div class="dashboard-card">
            <h3 class="dashboard-card-title" style="margin-bottom: 18px;"><i class="bx bx-receipt" style="color: var(--primary);"></i> Billing &amp; Invoices</h3>
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Plan Tier</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 12px; font-weight: 700; font-family: monospace; color: var(--primary);">INV-2026-001</td>
                        <td style="padding: 12px; color: var(--text-muted);">Aug 01, 2026</td>
                        <td style="padding: 12px;"><span class="plan-badge plan-{{ $planClass }}">{{ $rawPlan }}</span></td>
                        <td style="padding: 12px; font-weight: 800; color: var(--text-main);">₹0.00</td>
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
                <div class="usage-stat-card">
                    <div style="font-size: 28px; font-weight: 800; color: var(--text-main);">{{ $totalUsersCount ?? 0 }} / {{ $company->max_users ?? 100 }}</div>
                    <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 600; margin-top: 4px;">Active Tenant User Accounts</div>
                </div>
                <div class="usage-stat-card">
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
                <div style="padding: 14px; border-bottom: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center;">
                    <div><strong style="color: var(--text-main);">Tenant Workspace Created</strong> — Database <span class="db-code-pill">{{ $company->db_name }}</span> provisioned.</div>
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
            <div style="font-size: 14px; background: var(--bg-subtle); border: 1px solid var(--border-color); border-radius: 16px; padding: 22px; color: var(--text-muted);">
                Target Database: <span class="db-code-pill">{{ $company->db_name }}</span><br><br>
                Connection Status: <span class="status-pill status-active"><span class="dot"></span> Connected ({{ $dbLatency ?? 0 }}ms)</span><br><br>
                Database Charset: <strong style="color: var(--text-main);">utf8mb4_general_ci</strong>
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
            <div style="font-size: 13.5px; color: var(--text-muted); font-weight: 600;">Last verified snapshot: <strong style="color: var(--text-main);">Today, 10:32 AM (2.4 MB)</strong></div>
        </div>
    </div>

    <!-- TAB 11: MIGRATIONS -->
    <div class="workspace-tab-content" id="tab-migrations">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h3 class="dashboard-card-title"><i class="bx bx-git-repo-forked" style="color: var(--primary);"></i> Schema Migrations History</h3>
                <button class="btn-custom btn-outline-custom btn-sm-custom">Run Migrations</button>
            </div>
            <div style="font-size: 13.5px; color: var(--text-muted); font-weight: 600;">Migration Version: <strong style="color: var(--text-main);">v1.8.2 (Up to date - 0 pending)</strong></div>
        </div>
    </div>

    <!-- TAB 12: SETTINGS (COMPANY EDIT FORM & SECURITY CONTROLS) -->
    <div class="workspace-tab-content" id="tab-settings">
        <!-- EDIT COMPANY FORM CARD -->
        <div class="dashboard-card" id="editCompanyCard" style="margin-bottom: 24px;">
            <div class="dashboard-card-header" style="margin-bottom: 24px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h3 class="dashboard-card-title" style="font-size: 18px; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 8px;">
                        <i class="bx bx-edit-alt" style="color: var(--primary);"></i> Edit Company Profile &amp; Workspace
                    </h3>
                    <p style="font-size: 13px; color: var(--text-muted); margin: 4px 0 0 0;">Update tenant identity, contact details, branding, quotas, and document prefixes.</p>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="tenant-id-tag">Tenant ID: #{{ $company->id }}</span>
                    <span class="status-pill {{ $statusPillClass }}">
                        <span class="dot"></span> {{ ucfirst($company->status ?? 'Active') }}
                    </span>
                </div>
            </div>

            @if(isset($errors) && $errors->any())
                <div style="background: var(--danger-bg); border: 1px solid var(--danger-border); color: var(--danger); border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; font-size: 13.5px;">
                    <div style="display: flex; align-items: center; gap: 8px; font-weight: 800; margin-bottom: 6px;">
                        <i class="bx bx-error-circle" style="font-size: 18px;"></i> Please review and resolve the errors below:
                    </div>
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('super-admin.companies.update', $company->id) }}" enctype="multipart/form-data" id="companyEditForm">
                @csrf
                @method('PUT')

                <!-- SECTION 1: IDENTITY & DOMAINS -->
                <div class="form-section-subtitle">
                    <i class="bx bx-building"></i> Company Identity &amp; Infrastructure
                </div>
                <div class="form-grid-layout">
                    <div class="form-field-group">
                        <label class="form-field-label" for="company_name_edit">Company Legal Name <span class="req">*</span></label>
                        <div class="input-with-icon-wrapper">
                            <i class="bx bx-building-house input-icon"></i>
                            <input type="text" name="name" id="company_name_edit" class="form-control-custom" required value="{{ old('name', $company->name) }}" placeholder="e.g. Amazon Technologies Inc." />
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label" for="company_short_name_edit">Brand / Short Name</label>
                        <div class="input-with-icon-wrapper">
                            <i class="bx bx-tag input-icon"></i>
                            <input type="text" name="short_name" id="company_short_name_edit" class="form-control-custom" value="{{ old('short_name', $company->short_name) }}" placeholder="e.g. Amazon" />
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label" for="company_code_edit">Company Code <span class="help-hint">Upper-case identifier</span></label>
                        <div class="input-with-icon-wrapper">
                            <i class="bx bx-barcode input-icon"></i>
                            <input type="text" name="company_code" id="company_code_edit" class="form-control-custom" style="text-transform: uppercase;" value="{{ old('company_code', $company->company_code) }}" placeholder="e.g. AMAZON" />
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label" for="company_db_name_edit">Tenant Database <span class="help-hint"><i class="bx bx-lock-alt"></i> Locked</span></label>
                        <div class="input-with-icon-wrapper">
                            <i class="bx bx-data input-icon"></i>
                            <input type="text" id="company_db_name_edit" class="form-control-custom" value="{{ $company->db_name }}" readonly disabled style="font-family: monospace;" />
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label" for="company_subdomain_edit">Subdomain</label>
                        <div class="input-with-icon-wrapper">
                            <i class="bx bx-globe input-icon"></i>
                            <input type="text" name="subdomain" id="company_subdomain_edit" class="form-control-custom" value="{{ old('subdomain', $company->subdomain) }}" placeholder="e.g. amazon" />
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label" for="company_domain_edit">Custom Domain</label>
                        <div class="input-with-icon-wrapper">
                            <i class="bx bx-link-external input-icon"></i>
                            <input type="text" name="domain" id="company_domain_edit" class="form-control-custom" value="{{ old('domain', $company->domain) }}" placeholder="e.g. portal.amazon.com" />
                        </div>
                    </div>
                </div>

                <hr class="form-section-divider" />

                <!-- SECTION 2: CONTACT & ADDRESS -->
                <div class="form-section-subtitle">
                    <i class="bx bx-envelope"></i> Contact Information &amp; Location
                </div>
                <div class="form-grid-layout">
                    <div class="form-field-group">
                        <label class="form-field-label" for="company_email_edit">Official Contact Email <span class="req">*</span></label>
                        <div class="input-with-icon-wrapper">
                            <i class="bx bx-envelope input-icon"></i>
                            <input type="email" name="email" id="company_email_edit" class="form-control-custom" required value="{{ old('email', $company->email) }}" placeholder="contact@company.com" />
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label" for="company_phone_edit">Phone Number</label>
                        <div class="input-with-icon-wrapper">
                            <i class="bx bx-phone input-icon"></i>
                            <input type="text" name="phone" id="company_phone_edit" class="form-control-custom" value="{{ old('phone', $company->phone) }}" placeholder="+91 9876543210" />
                        </div>
                    </div>

                    <div class="form-field-group full-width">
                        <label class="form-field-label" for="company_website_edit">Website URL</label>
                        <div class="input-with-icon-wrapper">
                            <i class="bx bx-globe-alt input-icon"></i>
                            <input type="url" name="website" id="company_website_edit" class="form-control-custom" value="{{ old('website', $company->website) }}" placeholder="https://www.company.com" />
                        </div>
                    </div>

                    <div class="form-field-group full-width">
                        <label class="form-field-label" for="company_address_edit">Registered Physical Address</label>
                        <textarea name="address" id="company_address_edit" class="form-control-textarea" rows="2" placeholder="Full postal / physical address...">{{ old('address', $company->address) }}</textarea>
                    </div>
                </div>

                <hr class="form-section-divider" />

                <!-- SECTION 3: BRANDING & LOGO -->
                <div class="form-section-subtitle">
                    <i class="bx bx-image-alt"></i> Company Branding &amp; Logo
                </div>
                <div class="logo-edit-preview-box">
                    <div class="logo-preview-thumb" id="editLogoThumb">
                        @if($company->logo && file_exists(public_path($company->logo)))
                            <img src="{{ asset($company->logo) }}" alt="{{ $company->name }}" id="currentLogoPreview" />
                        @elseif($company->logo)
                            <img src="{{ asset($company->logo) }}" alt="{{ $company->name }}" id="currentLogoPreview" />
                        @else
                            <span id="logoTextFallback" style="font-size: 20px; font-weight: 800; color: var(--primary);">{{ strtoupper(substr($company->name, 0, 2)) }}</span>
                        @endif
                    </div>
                    <div style="flex: 1;">
                        <label class="form-field-label" for="company_logo_edit" style="margin-bottom: 6px;">Upload New Company Logo</label>
                        <input type="file" name="company_logo" id="company_logo_edit" accept="image/*" class="form-control-custom" style="padding: 8px 12px;" onchange="previewEditLogo(this)" />
                        <div style="font-size: 11.5px; color: var(--text-subtle); margin-top: 5px;">
                            Supported formats: PNG, JPG, JPEG, SVG, WebP. Recommended size: 256x256px. Max 5MB.
                        </div>
                    </div>
                </div>

                <hr class="form-section-divider" />

                <!-- SECTION 4: RESOURCE QUOTAS & ALLOCATION -->
                <div class="form-section-subtitle">
                    <i class="bx bx-pie-chart-alt-2"></i> Resource Allocation &amp; Quotas
                </div>
                <div class="form-grid-4">
                    <div class="form-field-group">
                        <label class="form-field-label" for="company_max_users_edit">Max Users</label>
                        <div class="input-with-icon-wrapper">
                            <i class="bx bx-group input-icon"></i>
                            <input type="number" min="1" name="max_users" id="company_max_users_edit" class="form-control-custom" value="{{ old('max_users', $company->max_users ?? 10) }}" />
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label" for="company_max_storage_edit">Max Storage (MB)</label>
                        <div class="input-with-icon-wrapper">
                            <i class="bx bx-hard-drive input-icon"></i>
                            <input type="number" min="50" name="max_storage_mb" id="company_max_storage_edit" class="form-control-custom" value="{{ old('max_storage_mb', $company->max_storage_mb ?? 1024) }}" />
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label" for="company_max_projects_edit">Max Projects</label>
                        <div class="input-with-icon-wrapper">
                            <i class="bx bx-briefcase input-icon"></i>
                            <input type="number" min="0" name="max_projects" id="company_max_projects_edit" class="form-control-custom" value="{{ old('max_projects', $company->max_projects ?? 5) }}" />
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label" for="company_max_clients_edit">Max Clients</label>
                        <div class="input-with-icon-wrapper">
                            <i class="bx bx-user-check input-icon"></i>
                            <input type="number" min="0" name="max_clients" id="company_max_clients_edit" class="form-control-custom" value="{{ old('max_clients', $company->max_clients ?? 50) }}" />
                        </div>
                    </div>
                </div>

                <hr class="form-section-divider" />

                <!-- SECTION 5: REGISTRATION & PREFIXES -->
                <div class="form-section-subtitle">
                    <i class="bx bx-receipt"></i> Statutory Registration &amp; Prefixes
                </div>
                <div class="form-grid-3" style="margin-bottom: 18px;">
                    <div class="form-field-group">
                        <label class="form-field-label" for="company_gst_edit">GST Number</label>
                        <div class="input-with-icon-wrapper">
                            <i class="bx bx-file input-icon"></i>
                            <input type="text" name="gst_number" id="company_gst_edit" class="form-control-custom" value="{{ old('gst_number', $company->gst_number) }}" placeholder="e.g. 29ABCDE1234F1Z5" />
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label" for="company_pan_edit">PAN Number</label>
                        <div class="input-with-icon-wrapper">
                            <i class="bx bx-id-card input-icon"></i>
                            <input type="text" name="pan_number" id="company_pan_edit" class="form-control-custom" value="{{ old('pan_number', $company->pan_number) }}" placeholder="e.g. ABCDE1234F" />
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label" for="company_reg_edit">Registration No.</label>
                        <div class="input-with-icon-wrapper">
                            <i class="bx bx-check-shield input-icon"></i>
                            <input type="text" name="registration_number" id="company_reg_edit" class="form-control-custom" value="{{ old('registration_number', $company->registration_number) }}" placeholder="e.g. U72200MH2021PTC123456" />
                        </div>
                    </div>
                </div>

                <div class="form-grid-4">
                    <div class="form-field-group">
                        <label class="form-field-label" for="company_emp_prefix_edit">Employee ID Prefix</label>
                        <input type="text" name="employee_id_prefix" id="company_emp_prefix_edit" class="form-control-custom" style="padding-left: 14px;" value="{{ old('employee_id_prefix', $company->employee_id_prefix) }}" placeholder="e.g. EMP-" />
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label" for="company_leave_prefix_edit">Leave Prefix</label>
                        <input type="text" name="leave_prefix" id="company_leave_prefix_edit" class="form-control-custom" style="padding-left: 14px;" value="{{ old('leave_prefix', $company->leave_prefix) }}" placeholder="e.g. LV-" />
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label" for="company_payroll_prefix_edit">Payroll Prefix</label>
                        <input type="text" name="payroll_prefix" id="company_payroll_prefix_edit" class="form-control-custom" style="padding-left: 14px;" value="{{ old('payroll_prefix', $company->payroll_prefix) }}" placeholder="e.g. PAY-" />
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label" for="company_payslip_prefix_edit">Payslip Prefix</label>
                        <input type="text" name="payslip_prefix" id="company_payslip_prefix_edit" class="form-control-custom" style="padding-left: 14px;" value="{{ old('payslip_prefix', $company->payslip_prefix) }}" placeholder="e.g. SLIP-" />
                    </div>
                </div>

                <hr class="form-section-divider" />

                <!-- SECTION 6: CREDENTIALS (OPTIONAL) -->
                <div class="form-section-subtitle">
                    <i class="bx bx-shield-quarter"></i> Company Security Credentials
                </div>
                <div class="form-grid-layout" style="margin-bottom: 24px;">
                    <div class="form-field-group">
                        <label class="form-field-label" for="company_password_edit">
                            Update Company / Admin Password
                            <span class="help-hint">Leave blank to keep current</span>
                        </label>
                        <div class="input-with-icon-wrapper" style="position: relative;">
                            <i class="bx bx-key input-icon"></i>
                            <input type="password" name="password" id="company_password_edit" minlength="8" maxlength="128" class="form-control-custom @error('password') is-invalid @enderror" placeholder="•••••••••••• (min 8 chars)" style="padding-right: 44px;" />
                            <button type="button" onclick="toggleEditPasswordVisibility()" style="position: absolute; right: 12px; background: none; border: none; color: var(--text-subtle); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 18px;" title="Show/Hide">
                                <i class="bx bx-show" id="editPasswordEyeIcon"></i>
                            </button>
                        </div>
                        <div class="field-help-text" style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Leave blank to preserve current password. If updating, must be 8–128 characters.</div>
                        @error('password')
                            <div class="field-error-feedback visible" style="display: block; color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                        <div id="company_password_edit_error" class="field-error-feedback" style="display: none; color: var(--danger); font-size: 12px; margin-top: 4px;"></div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label">Current Login Email</label>
                        <div class="input-with-icon-wrapper">
                            <i class="bx bx-user-pin input-icon"></i>
                            <input type="text" class="form-control-custom" value="{{ $companyLoginEmail ?: ($company->email ?? 'N/A') }}" readonly disabled />
                        </div>
                    </div>
                </div>

                <!-- FORM ACTION BUTTONS -->
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; padding-top: 16px; border-top: 1px solid var(--border-subtle); flex-wrap: wrap;">
                    <button type="reset" class="btn-custom btn-outline-custom btn-sm-custom">
                        <i class="bx bx-undo"></i> Reset
                    </button>
                    <button type="submit" class="btn-custom btn-primary-custom btn-sm-custom" style="padding: 10px 24px; font-weight: 800;">
                        <i class="bx bx-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- TENANT LIFECYCLE & SECURITY CONTROLS CARD -->
        <div class="dashboard-card">
            <div class="dashboard-card-header" style="margin-bottom: 24px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h3 class="dashboard-card-title" style="font-size: 18px; font-weight: 800; color: var(--text-main); margin: 0;">Tenant Lifecycle &amp; Security Controls</h3>
                    <p style="font-size: 13px; color: var(--text-muted); margin: 4px 0 0 0;">Manage tenant operational status, suspension rules, and authorization access policies.</p>
                </div>
                <span class="status-pill {{ $statusPillClass }}">
                    <span class="dot"></span> {{ ucfirst($company->status ?? 'Active') }}
                </span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr; max-width: 680px;">
                <!-- SUSPEND / UNSUSPEND CARD -->
                <div class="settings-action-card card-warning">
                    <div>
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                            <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(245, 158, 11, 0.15); color: var(--warning); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                                <i class="bx bx-pause-circle"></i>
                            </div>
                            <div>
                                <strong style="font-size: 15px; color: var(--warning);">Company Suspension</strong>
                                <div style="font-size: 11.5px; color: var(--text-subtle);">Temporary operational freeze</div>
                            </div>
                        </div>
                        <p style="font-size: 12.5px; color: var(--text-muted); margin: 0 0 20px 0; line-height: 1.5;">
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
                                <button type="submit" class="btn-custom btn-warning-custom btn-sm-custom" style="width: 100%; padding: 10px 16px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <i class="bx bx-pause-circle"></i> Suspend Company
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
        <form method="POST" action="{{ route('super-admin.subscriptions.assign') }}" id="planChangeForm" style="margin: 0;">
            @csrf
            <input type="hidden" name="company_id" value="{{ $company->id }}">
            <input type="hidden" name="billing_cycle" value="monthly">
            <input type="hidden" name="plan_id" id="modalSelectedPlanInput" value="{{ $currentCompanyPlanSlug }}">

            <h3 style="font-size: 20px; font-weight: 800; margin-top: 0; margin-bottom: 6px; color: var(--text-main);">Change Subscription Plan</h3>
            <p style="font-size: 13.5px; color: var(--text-muted); margin-bottom: 20px;">
                Select a new subscription tier for {{ $company->name }}.
            </p>

            <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
                @foreach($planHierarchy as $slug => $tier)
                    @php
                        $isCurrent = ($slug === $currentCompanyPlanSlug);
                        $isLower = ($tier['level'] < $minAllowedTierLevel);
                        $isUpgrade = ($tier['level'] > $minAllowedTierLevel);
                    @endphp
                    <div class="plan-card-option {{ $isCurrent ? 'selected' : '' }} {{ $isLower ? 'disabled' : '' }}" 
                         data-plan="{{ $slug }}"
                         data-tier-level="{{ $tier['level'] }}"
                         data-plan-name="{{ $tier['name'] }}"
                         @if($isLower) title="Downgrading to a lower plan tier is restricted" @endif>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span class="plan-badge {{ $tier['badge_class'] }}">{{ $tier['name'] }}</span>
                                @if($isCurrent)
                                    <span class="plan-tier-status-badge plan-tier-status-current"><i class="bx bx-check-circle"></i> Current Plan</span>
                                @elseif($isLower)
                                    <span class="plan-tier-status-badge plan-tier-status-restricted"><i class="bx bx-lock-alt"></i> Downgrade Restricted</span>
                                @else
                                    <span class="plan-tier-status-badge plan-tier-status-upgrade"><i class="bx bx-up-arrow-alt"></i> Upgrade Available</span>
                                @endif
                            </div>
                            <strong style="font-size: 14px; color: var(--text-main);">{{ $tier['price'] }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px;">
                            <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">{{ $tier['features'] }}</span>
                            @if($isLower)
                                <span style="font-size: 11px; color: var(--danger); font-weight: 600;"><i class="bx bx-block"></i> Cannot downgrade</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border-subtle); padding-top: 16px;">
                <button type="button" class="btn-custom btn-outline-custom btn-sm-custom" id="closePlanModalBtn">Cancel</button>
                <button type="submit" class="btn-custom btn-primary-custom btn-sm-custom" id="confirmPlanChangeBtn">
                    <i class="bx bx-check"></i> Confirm Change
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching logic
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.workspace-tab-content');

    function switchTab(tabId, shouldScroll = false) {
        tabBtns.forEach(b => b.classList.remove('active'));
        tabContents.forEach(c => c.classList.remove('active'));

        const targetBtn = document.querySelector(`.tab-btn[data-tab="${tabId}"]`);
        const targetContent = document.getElementById(tabId);

        if (targetBtn) targetBtn.classList.add('active');
        if (targetContent) targetContent.classList.add('active');

        if (shouldScroll && tabId === 'tab-settings') {
            setTimeout(() => {
                const editCard = document.getElementById('editCompanyCard');
                if (editCard) {
                    editCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    const nameInput = document.getElementById('company_name_edit');
                    if (nameInput) nameInput.focus();
                }
            }, 80);
        }
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
            switchTab(jumpTab, jumpTab === 'tab-settings');
        });
    });

    // Check if initial tab is specified in URL query (?tab=settings or ?tab=tab-settings) or hash (#tab-settings) or if validation errors
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab') || window.location.hash.replace('#', '');
    const hasErrors = @json(isset($errors) && $errors->any());

    if (hasErrors || tabParam === 'settings' || tabParam === 'tab-settings') {
        switchTab('tab-settings', hasErrors || tabParam === 'settings' || tabParam === 'tab-settings');
    } else if (tabParam && document.getElementById(tabParam)) {
        switchTab(tabParam);
    }

    // Modal Triggers
    const planModal = document.getElementById('planChangeModal');
    const closePlanModalBtn = document.getElementById('closePlanModalBtn');
    const planChangeForm = document.getElementById('planChangeForm');
    const modalSelectedPlanInput = document.getElementById('modalSelectedPlanInput');
    const planOptions = document.querySelectorAll('#planChangeModal .plan-card-option');
    const confirmPlanBtn = document.getElementById('confirmPlanChangeBtn');
    const defaultPlanSlug = @json($currentCompanyPlanSlug);

    function resetPlanModalSelection() {
        if (!modalSelectedPlanInput) return;
        modalSelectedPlanInput.value = defaultPlanSlug;
        planOptions.forEach(card => {
            if (card.getAttribute('data-plan') === defaultPlanSlug) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        });
        if (confirmPlanBtn) {
            confirmPlanBtn.disabled = false;
            confirmPlanBtn.innerHTML = '<i class="bx bx-check"></i> Confirm Change';
        }
    }

    document.querySelectorAll('.trigger-plan-modal').forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            resetPlanModalSelection();
            if (planModal) planModal.classList.add('open');
        });
    });

    if (closePlanModalBtn && planModal) {
        closePlanModalBtn.addEventListener('click', function() {
            planModal.classList.remove('open');
        });
    }

    if (planModal) {
        planModal.addEventListener('click', function(e) {
            if (e.target === planModal) {
                planModal.classList.remove('open');
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && planModal && planModal.classList.contains('open')) {
            planModal.classList.remove('open');
        }
    });

    planOptions.forEach(option => {
        option.addEventListener('click', function() {
            if (this.classList.contains('disabled')) {
                return;
            }

            planOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');

            const selectedPlan = this.getAttribute('data-plan');
            const selectedName = this.getAttribute('data-plan-name') || selectedPlan.toUpperCase();
            if (modalSelectedPlanInput) {
                modalSelectedPlanInput.value = selectedPlan;
            }

            if (confirmPlanBtn) {
                if (selectedPlan === defaultPlanSlug) {
                    confirmPlanBtn.innerHTML = '<i class="bx bx-check"></i> Confirm Current Plan';
                } else {
                    confirmPlanBtn.innerHTML = `<i class="bx bx-up-arrow-circle"></i> Upgrade to ${selectedName}`;
                }
            }
        });
    });

    if (planChangeForm) {
        planChangeForm.addEventListener('submit', function() {
            if (confirmPlanBtn) {
                confirmPlanBtn.disabled = true;
                confirmPlanBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Updating Plan...';
            }
        });
    }
});

function previewEditLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const thumb = document.getElementById('editLogoThumb');
            if (thumb) {
                thumb.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;" />`;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function toggleEditPasswordVisibility() {
    const pwdInput = document.getElementById('company_password_edit');
    const eyeIcon = document.getElementById('editPasswordEyeIcon');
    if (!pwdInput) return;

    if (pwdInput.type === 'password') {
        pwdInput.type = 'text';
        if (eyeIcon) {
            eyeIcon.classList.remove('bx-show');
            eyeIcon.classList.add('bx-hide');
        }
    } else {
        pwdInput.type = 'password';
        if (eyeIcon) {
            eyeIcon.classList.remove('bx-hide');
            eyeIcon.classList.add('bx-show');
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.getElementById('companyEditForm');
    const pwdEditInput = document.getElementById('company_password_edit');
    const pwdEditError = document.getElementById('company_password_edit_error');

    function validateEditPassword() {
        if (!pwdEditInput) return true;
        const val = pwdEditInput.value;
        if (!val) {
            if (pwdEditError) { pwdEditError.textContent = ''; pwdEditError.style.display = 'none'; }
            pwdEditInput.classList.remove('is-invalid');
            return true;
        }
        if (val.length < 8) {
            if (pwdEditError) { pwdEditError.textContent = 'Password must be at least 8 characters long.'; pwdEditError.style.display = 'block'; }
            pwdEditInput.classList.add('is-invalid');
            return false;
        }
        if (val.length > 128) {
            if (pwdEditError) { pwdEditError.textContent = 'Password cannot exceed 128 characters.'; pwdEditError.style.display = 'block'; }
            pwdEditInput.classList.add('is-invalid');
            return false;
        }
        if (pwdEditError) { pwdEditError.textContent = ''; pwdEditError.style.display = 'none'; }
        pwdEditInput.classList.remove('is-invalid');
        return true;
    }

    if (pwdEditInput) {
        pwdEditInput.addEventListener('input', validateEditPassword);
        pwdEditInput.addEventListener('blur', validateEditPassword);
    }

    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            if (!validateEditPassword()) {
                e.preventDefault();
                pwdEditInput.focus();
                return false;
            }
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
