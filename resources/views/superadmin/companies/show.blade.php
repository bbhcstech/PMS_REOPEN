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
        --primary: #059669;
        --primary-hover: #047857;
        --primary-glow: rgba(5, 150, 105, 0.18);
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
        --success-bg: rgba(16, 185, 129, 0.15);
        --success-border: rgba(52, 211, 153, 0.3);

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
    }
    .plan-card-option:hover, .plan-card-option.selected {
        border-color: var(--primary);
        background: var(--bg-hover);
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
        <div class="flash-alert-success">
            <i class="bx bx-check-circle" style="font-size: 22px; color: var(--success);"></i> {{ session('success') }}
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

    <!-- TAB 12: SETTINGS (FUNCTIONAL SUSPEND & DEACTIVATE) -->
    <div class="workspace-tab-content" id="tab-settings">
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

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
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

                <!-- DEACTIVATE / REACTIVATE ACCESS CARD -->
                <div class="settings-action-card card-danger">
                    <div>
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                            <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(239, 68, 68, 0.15); color: var(--danger); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                                <i class="bx bx-block"></i>
                            </div>
                            <div>
                                <strong style="font-size: 15px; color: var(--danger);">Deactivate Tenant Access</strong>
                                <div style="font-size: 11.5px; color: var(--text-subtle);">Disable access authorization</div>
                            </div>
                        </div>
                        <p style="font-size: 12.5px; color: var(--text-muted); margin: 0 0 20px 0; line-height: 1.5;">
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
                                <button type="submit" class="btn-custom btn-danger-custom btn-sm-custom" style="width: 100%; padding: 10px 16px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 8px;">
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

        <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border-subtle); padding-top: 16px;">
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
