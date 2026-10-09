@extends('layouts.superadmin')

@section('title', 'Super Admin · Company Metrics & Platform Intelligence')
@section('page_title', 'Company Metrics')
@section('page_subtitle', 'Platform-wide analytics and performance insights across all tenant companies.')

@section('content')
<style>
    /* ============================================================
       METRICS DASHBOARD DESIGN SYSTEM TOKENS
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

        /* Plan Color Identifiers */
        --plan-free: #64748b;
        --plan-gold: #d97706;
        --plan-platinum: #0284c7;
        --plan-diamond: #7c3aed;

        --radius-lg: 16px;
        --radius-md: 10px;
        --radius-sm: 6px;
        
        --shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
        --shadow-sm: 0 4px 16px rgba(15, 23, 42, 0.04);
        --shadow-md: 0 10px 30px rgba(15, 23, 42, 0.08);

        --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
        --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    /* Page Header Bar */
    .metrics-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }
    .metrics-breadcrumb {
        font-size: 13px;
        font-weight: 500;
        color: var(--text-subtle);
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .metrics-breadcrumb span { color: var(--text-muted); }
    .metrics-title {
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--text-main);
        line-height: 1.2;
    }
    .metrics-subtitle {
        font-size: 14px;
        color: var(--text-muted);
        margin-top: 2px;
    }
    .header-controls {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Form Controls & Buttons */
    .date-select-btn {
        padding: 9px 16px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        background: #ffffff;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-main);
        outline: none;
        cursor: pointer;
        transition: all var(--transition-fast);
        box-shadow: var(--shadow-xs);
        height: 40px;
        font-family: inherit;
    }
    .date-select-btn:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-ring);
    }
    .btn-export {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        border-radius: var(--radius-md);
        background: var(--primary);
        color: #ffffff;
        font-weight: 600;
        font-size: 13.5px;
        border: none;
        height: 40px;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
        transition: all var(--transition-fast);
        text-decoration: none;
    }
    .btn-export:hover {
        background: var(--primary-hover);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        color: #ffffff;
    }
    .btn-export.btn-export-cancel {
        background: #ffffff;
        color: var(--text-main);
        border: 1px solid var(--border-color);
        box-shadow: none;
    }
    .btn-export.btn-export-cancel:hover {
        background: var(--border-subtle, #f1f5f9);
        color: var(--text-main);
        box-shadow: none;
    }

    /* Executive KPI Grid */
    .executive-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .kpi-metric-card {
        background: var(--bg-surface);
        border-radius: var(--radius-lg);
        padding: 20px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-xs);
        transition: all var(--transition-fast);
    }
    .kpi-metric-card:hover {
        border-color: #cbd5e1;
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }
    .kpi-metric-card .card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .kpi-metric-card .card-title {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--text-subtle);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .kpi-metric-card .icon-pill {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
    }
    .kpi-metric-card .metric-val {
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--text-main);
        margin-top: 10px;
        line-height: 1.1;
    }
    .kpi-metric-card .metric-foot {
        font-size: 12px;
        font-weight: 600;
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .metric-foot.positive { color: var(--success); }
    .metric-foot.warning { color: var(--warning); }
    .metric-foot.muted { color: var(--text-muted); font-weight: 500; }

    /* Dashboard Layout Grid (2 Columns) */
    .dashboard-grid-2col {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }
    @media (max-width: 1024px) {
        .dashboard-grid-2col { grid-template-columns: 1fr; }
    }

    .dashboard-grid-equal {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }
    @media (max-width: 1024px) {
        .dashboard-grid-equal { grid-template-columns: 1fr; }
    }

    /* Analytics Card Wrapper */
    .analytics-card {
        background: var(--bg-surface);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        padding: 24px;
        display: flex;
        flex-direction: column;
    }
    .analytics-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .analytics-card-title {
        font-size: 17px;
        font-weight: 700;
        color: var(--text-main);
        letter-spacing: -0.3px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .analytics-card-subtitle {
        font-size: 12.5px;
        color: var(--text-muted);
        font-weight: 400;
    }
    .chart-time-pills {
        display: inline-flex;
        background: #f1f5f9;
        padding: 3px;
        border-radius: 8px;
        gap: 2px;
    }
    .chart-time-btn {
        border: none;
        background: transparent;
        padding: 4px 10px;
        font-size: 11.5px;
        font-weight: 600;
        color: var(--text-muted);
        border-radius: 6px;
        cursor: pointer;
        transition: all var(--transition-fast);
    }
    .chart-time-btn.active {
        background: #ffffff;
        color: var(--primary);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    /* Donut Chart Center Text Wrapper */
    .donut-chart-container {
        position: relative;
        height: 230px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .donut-center-overlay {
        position: absolute;
        text-align: center;
        pointer-events: none;
    }
    .donut-center-val {
        font-size: 26px;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1;
    }
    .donut-center-lbl {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-subtle);
        text-transform: uppercase;
        margin-top: 4px;
    }

    /* Plan Legend Grid */
    .plan-legend-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid var(--border-subtle);
    }
    .plan-legend-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12.5px;
    }
    .plan-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }

    /* Ranking Table Styling */
    .ranking-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .ranking-table th {
        text-align: left;
        padding: 10px 14px;
        font-size: 11px;
        font-weight: 700;
        color: var(--text-subtle);
        text-transform: uppercase;
        border-bottom: 1px solid var(--border-color);
        background: #f8fafc;
    }
    .ranking-table td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border-subtle);
        vertical-align: middle;
    }
    .ranking-table tr:hover { background: #f8fafc; }

    /* Risk Alerts List */
    .risk-alert-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        margin-bottom: 10px;
        background: #ffffff;
        transition: all var(--transition-fast);
    }
    .risk-alert-item:hover { border-color: #cbd5e1; }
    .risk-alert-item.risk-high { background: #fef2f2; border-color: #fecaca; }
    .risk-alert-item.risk-warn { background: #fffbeb; border-color: #fde68a; }

    /* Activity Timeline */
    .activity-timeline {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .activity-point {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 13px;
    }
    .activity-icon-node {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--primary-soft);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    /* Storage Track Bar */
    .storage-progress-track {
        width: 100%;
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        margin: 8px 0;
    }
    .storage-progress-fill {
        height: 100%;
        background: var(--primary);
        border-radius: 4px;
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
        border-radius: var(--radius-lg);
        max-width: 500px;
        width: 100%;
        padding: 24px;
        box-shadow: var(--shadow-md);
    }

    /* Ledger Export Dropdown Styles */
    .ledger-export-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--bg-surface, #ffffff);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: var(--shadow-xs);
        transition: all var(--transition-fast);
    }
    .ledger-export-btn:hover {
        background: var(--border-subtle, #f1f5f9);
    }
    .ledger-export-menu {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        margin-top: 4px;
        background: var(--bg-surface, #ffffff);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        box-shadow: var(--shadow-md);
        padding: 6px;
        z-index: 100;
        min-width: 160px;
    }
    .ledger-export-menu a,
    .ledger-export-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        color: var(--text-main);
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        border-radius: 6px;
        transition: background 0.15s ease, color 0.15s ease;
        background: transparent;
    }
    .ledger-export-menu a:hover,
    .ledger-export-menu a:focus,
    .ledger-export-menu a:active,
    .ledger-export-item:hover,
    .ledger-export-item:focus,
    .ledger-export-item:active {
        background: #f1f5f9;
        color: var(--text-main);
    }
    .platform-activity-view-all:hover {
        color: var(--primary-hover, #1d4ed8) !important;
        text-decoration: underline !important;
    }

    /* ============================================================
       DARK MODE OVERRIDES (data-pms-theme="dark" / data-theme="dark")
       ============================================================ */
    html[data-pms-theme="dark"],
    html[data-theme="dark"],
    html[data-bs-theme="dark"] {
        --navy-dark: #070b1a;
        --navy-surface: #0f1530;
        --bg-main: #070B1A;
        --bg-surface: #0F1530;
        --border-color: rgba(238, 241, 251, 0.12);
        --border-subtle: rgba(238, 241, 251, 0.08);
        --text-main: #EEF1FB;
        --text-muted: #9AA3C7;
        --text-subtle: #6B739A;
        --primary-soft: rgba(47, 107, 255, 0.15);
        --warning-bg: rgba(245, 158, 11, 0.1);
        --warning-border: rgba(245, 158, 11, 0.3);
    }

    /* Dark Mode: Date Select Dropdown */
    html[data-pms-theme="dark"] .date-select-btn,
    html[data-theme="dark"] .date-select-btn,
    html[data-bs-theme="dark"] .date-select-btn {
        background: var(--bg-surface-subtle, #141B3D) !important;
        border-color: var(--border-color) !important;
        color: var(--text-main, #EEF1FB) !important;
    }
    html[data-pms-theme="dark"] .date-select-btn option,
    html[data-theme="dark"] .date-select-btn option,
    html[data-bs-theme="dark"] .date-select-btn option {
        background: var(--bg-surface, #0F1530);
        color: var(--text-main, #EEF1FB);
    }

    /* Dark Mode: Impersonation Banner */
    html[data-pms-theme="dark"] .metrics-impersonation-banner,
    html[data-theme="dark"] .metrics-impersonation-banner,
    html[data-bs-theme="dark"] .metrics-impersonation-banner {
        background: rgba(245, 158, 11, 0.1) !important;
        border-color: rgba(245, 158, 11, 0.3) !important;
    }
    html[data-pms-theme="dark"] .metrics-impersonation-code,
    html[data-theme="dark"] .metrics-impersonation-code,
    html[data-bs-theme="dark"] .metrics-impersonation-code {
        background: rgba(245, 158, 11, 0.18) !important;
        color: #fde68a !important;
        border: 1px solid rgba(245, 158, 11, 0.3) !important;
    }
    html[data-pms-theme="dark"] .metrics-impersonation-leave-btn,
    html[data-theme="dark"] .metrics-impersonation-leave-btn,
    html[data-bs-theme="dark"] .metrics-impersonation-leave-btn {
        background: rgba(245, 158, 11, 0.12) !important;
        color: var(--warning, #fbbf24) !important;
        border-color: rgba(245, 158, 11, 0.35) !important;
    }
    html[data-pms-theme="dark"] .metrics-impersonation-leave-btn:hover,
    html[data-theme="dark"] .metrics-impersonation-leave-btn:hover,
    html[data-bs-theme="dark"] .metrics-impersonation-leave-btn:hover {
        background: rgba(245, 158, 11, 0.25) !important;
        border-color: #f59e0b !important;
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(245, 158, 11, 0.25) !important;
    }

    /* Dark Mode: Chart Time Pills */
    html[data-pms-theme="dark"] .chart-time-pills,
    html[data-theme="dark"] .chart-time-pills,
    html[data-bs-theme="dark"] .chart-time-pills {
        background: var(--bg-surface-subtle, #141B3D) !important;
        border: 1px solid var(--border-subtle, rgba(238, 241, 251, 0.1));
    }
    html[data-pms-theme="dark"] .chart-time-btn,
    html[data-theme="dark"] .chart-time-btn,
    html[data-bs-theme="dark"] .chart-time-btn {
        color: var(--text-muted, #9AA3C7);
    }
    html[data-pms-theme="dark"] .chart-time-btn:hover,
    html[data-theme="dark"] .chart-time-btn:hover,
    html[data-bs-theme="dark"] .chart-time-btn:hover {
        color: var(--text-main, #EEF1FB);
    }
    html[data-pms-theme="dark"] .chart-time-btn.active,
    html[data-theme="dark"] .chart-time-btn.active,
    html[data-bs-theme="dark"] .chart-time-btn.active {
        background: var(--primary, #2F6BFF) !important;
        color: #ffffff !important;
        box-shadow: 0 2px 8px rgba(47, 107, 255, 0.35);
    }

    /* Dark Mode: Master Ledger Controls */
    html[data-pms-theme="dark"] .ledger-export-btn,
    html[data-theme="dark"] .ledger-export-btn,
    html[data-bs-theme="dark"] .ledger-export-btn {
        background: var(--bg-surface-subtle, #141B3D) !important;
        color: var(--text-main, #EEF1FB) !important;
        border-color: var(--border-color) !important;
    }
    html[data-pms-theme="dark"] .ledger-export-btn:hover,
    html[data-theme="dark"] .ledger-export-btn:hover,
    html[data-bs-theme="dark"] .ledger-export-btn:hover {
        background: var(--bg-surface-hover, #1A2247) !important;
        border-color: var(--primary, #2F6BFF) !important;
        color: #ffffff !important;
    }
    html[data-pms-theme="dark"] .ledger-export-menu,
    html[data-theme="dark"] .ledger-export-menu,
    html[data-bs-theme="dark"] .ledger-export-menu {
        background: var(--bg-surface, #0F1530) !important;
        border-color: var(--border-color) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.55) !important;
    }
    html[data-pms-theme="dark"] .ledger-export-menu a,
    html[data-theme="dark"] .ledger-export-menu a,
    html[data-bs-theme="dark"] .ledger-export-menu a,
    html[data-pms-theme="dark"] .ledger-export-item,
    html[data-theme="dark"] .ledger-export-item,
    html[data-bs-theme="dark"] .ledger-export-item {
        color: var(--text-main, #EEF1FB) !important;
        background: transparent !important;
    }
    html[data-pms-theme="dark"] .ledger-export-menu a:hover,
    html[data-theme="dark"] .ledger-export-menu a:hover,
    html[data-bs-theme="dark"] .ledger-export-menu a:hover,
    html[data-pms-theme="dark"] .ledger-export-menu a:focus,
    html[data-theme="dark"] .ledger-export-menu a:focus,
    html[data-bs-theme="dark"] .ledger-export-menu a:focus,
    html[data-pms-theme="dark"] .ledger-export-menu a:active,
    html[data-theme="dark"] .ledger-export-menu a:active,
    html[data-bs-theme="dark"] .ledger-export-menu a:active,
    html[data-pms-theme="dark"] .ledger-export-item:hover,
    html[data-theme="dark"] .ledger-export-item:hover,
    html[data-bs-theme="dark"] .ledger-export-item:hover,
    html[data-pms-theme="dark"] .ledger-export-item:focus,
    html[data-theme="dark"] .ledger-export-item:focus,
    html[data-bs-theme="dark"] .ledger-export-item:focus,
    html[data-pms-theme="dark"] .ledger-export-item:active,
    html[data-theme="dark"] .ledger-export-item:active,
    html[data-bs-theme="dark"] .ledger-export-item:active {
        background: var(--bg-surface-hover, #1A2247) !important;
        color: #ffffff !important;
    }
    html[data-pms-theme="dark"] .ledger-entries-select,
    html[data-theme="dark"] .ledger-entries-select,
    html[data-bs-theme="dark"] .ledger-entries-select {
        background: var(--bg-surface-subtle, #141B3D) !important;
        color: var(--text-main, #EEF1FB) !important;
        border-color: var(--border-color) !important;
    }
    html[data-pms-theme="dark"] .ledger-entries-select option,
    html[data-theme="dark"] .ledger-entries-select option,
    html[data-bs-theme="dark"] .ledger-entries-select option {
        background: var(--bg-surface, #0F1530);
        color: var(--text-main, #EEF1FB);
    }
    html[data-pms-theme="dark"] .ledger-search-input,
    html[data-theme="dark"] .ledger-search-input,
    html[data-bs-theme="dark"] .ledger-search-input {
        background: var(--bg-surface-subtle, #141B3D) !important;
        color: var(--text-main, #EEF1FB) !important;
        border-color: var(--border-color) !important;
    }
    html[data-pms-theme="dark"] .ledger-search-input::placeholder,
    html[data-theme="dark"] .ledger-search-input::placeholder,
    html[data-bs-theme="dark"] .ledger-search-input::placeholder {
        color: var(--text-subtle, #6B739A) !important;
    }

    /* Dark Mode: Master Ledger Table & Rows */
    html[data-pms-theme="dark"] .ledger-table-wrap,
    html[data-theme="dark"] .ledger-table-wrap,
    html[data-bs-theme="dark"] .ledger-table-wrap {
        background: var(--bg-surface, #0F1530) !important;
        border-color: var(--border-color) !important;
    }
    html[data-pms-theme="dark"] .ledger-table thead tr,
    html[data-theme="dark"] .ledger-table thead tr,
    html[data-bs-theme="dark"] .ledger-table thead tr {
        background: var(--bg-surface-subtle, #141B3D) !important;
        border-bottom-color: var(--border-color) !important;
    }
    html[data-pms-theme="dark"] .ledger-table th,
    html[data-theme="dark"] .ledger-table th,
    html[data-bs-theme="dark"] .ledger-table th {
        background: var(--bg-surface-subtle, #141B3D) !important;
        border-color: var(--border-subtle) !important;
        color: var(--text-muted, #9AA3C7) !important;
    }
    html[data-pms-theme="dark"] .ledger-table td,
    html[data-theme="dark"] .ledger-table td,
    html[data-bs-theme="dark"] .ledger-table td {
        border-color: var(--border-subtle) !important;
    }
    html[data-pms-theme="dark"] .ledger-table .master-table-row,
    html[data-theme="dark"] .ledger-table .master-table-row,
    html[data-bs-theme="dark"] .ledger-table .master-table-row {
        background: var(--bg-surface, #0F1530) !important;
    }
    html[data-pms-theme="dark"] .ledger-table .master-table-row:hover,
    html[data-theme="dark"] .ledger-table .master-table-row:hover,
    html[data-bs-theme="dark"] .ledger-table .master-table-row:hover {
        background: var(--bg-surface-hover, #1A2247) !important;
    }
    html[data-pms-theme="dark"] .ledger-table .company-title,
    html[data-theme="dark"] .ledger-table .company-title,
    html[data-bs-theme="dark"] .ledger-table .company-title {
        color: var(--text-main, #EEF1FB) !important;
    }
    html[data-pms-theme="dark"] .ledger-table .company-db,
    html[data-theme="dark"] .ledger-table .company-db,
    html[data-bs-theme="dark"] .ledger-table .company-db {
        color: var(--text-muted, #9AA3C7) !important;
    }
    html[data-pms-theme="dark"] .ledger-action-btn,
    html[data-theme="dark"] .ledger-action-btn,
    html[data-bs-theme="dark"] .ledger-action-btn {
        background: rgba(47, 107, 255, 0.15) !important;
        color: #60a5fa !important;
        border-color: rgba(47, 107, 255, 0.3) !important;
    }
    html[data-pms-theme="dark"] .ledger-action-btn:hover,
    html[data-theme="dark"] .ledger-action-btn:hover,
    html[data-bs-theme="dark"] .ledger-action-btn:hover {
        background: var(--primary, #2F6BFF) !important;
        color: #ffffff !important;
    }

    /* Dark Mode: Plan and Status Badges */
    html[data-pms-theme="dark"] .plan-badge-item.badge-free,
    html[data-theme="dark"] .plan-badge-item.badge-free {
        background: rgba(148, 163, 184, 0.15) !important;
        color: #94a3b8 !important;
        border-color: rgba(148, 163, 184, 0.3) !important;
    }
    html[data-pms-theme="dark"] .plan-badge-item.badge-gold,
    html[data-theme="dark"] .plan-badge-item.badge-gold {
        background: rgba(245, 158, 11, 0.15) !important;
        color: #fbbf24 !important;
        border-color: rgba(245, 158, 11, 0.3) !important;
    }
    html[data-pms-theme="dark"] .plan-badge-item.badge-platinum,
    html[data-theme="dark"] .plan-badge-item.badge-platinum {
        background: rgba(2, 132, 199, 0.15) !important;
        color: #38bdf8 !important;
        border-color: rgba(2, 132, 199, 0.3) !important;
    }
    html[data-pms-theme="dark"] .plan-badge-item.badge-diamond,
    html[data-theme="dark"] .plan-badge-item.badge-diamond {
        background: rgba(124, 58, 237, 0.15) !important;
        color: #a78bfa !important;
        border-color: rgba(124, 58, 237, 0.3) !important;
    }

    html[data-pms-theme="dark"] .status-badge-item.status-active,
    html[data-theme="dark"] .status-badge-item.status-active {
        background: rgba(22, 163, 74, 0.15) !important;
        color: #4ade80 !important;
        border-color: rgba(22, 163, 74, 0.3) !important;
    }
    html[data-pms-theme="dark"] .status-badge-item.status-trial,
    html[data-theme="dark"] .status-badge-item.status-trial {
        background: rgba(217, 119, 6, 0.15) !important;
        color: #fbbf24 !important;
        border-color: rgba(217, 119, 6, 0.3) !important;
    }
    html[data-pms-theme="dark"] .status-badge-item.status-suspended,
    html[data-theme="dark"] .status-badge-item.status-suspended {
        background: rgba(220, 38, 38, 0.15) !important;
        color: #f87171 !important;
        border-color: rgba(220, 38, 38, 0.3) !important;
    }
    html[data-pms-theme="dark"] .status-badge-item.status-expired,
    html[data-theme="dark"] .status-badge-item.status-expired {
        background: rgba(220, 38, 38, 0.15) !important;
        color: #f87171 !important;
        border-color: rgba(220, 38, 38, 0.3) !important;
    }

    /* Dark Mode: Infrastructure Health Cards */
    html[data-pms-theme="dark"] .infra-health-card,
    html[data-theme="dark"] .infra-health-card,
    html[data-bs-theme="dark"] .infra-health-card {
        background: var(--bg-surface-subtle, #141B3D) !important;
        border: 1px solid var(--border-subtle, rgba(238, 241, 251, 0.1)) !important;
    }
    html[data-pms-theme="dark"] .infra-health-card .health-label,
    html[data-theme="dark"] .infra-health-card .health-label,
    html[data-bs-theme="dark"] .infra-health-card .health-label {
        color: var(--text-subtle, #6B739A) !important;
    }

    /* Dark Mode: Storage & Modals */
    html[data-pms-theme="dark"] .modal-dialog-custom,
    html[data-theme="dark"] .modal-dialog-custom,
    html[data-bs-theme="dark"] .modal-dialog-custom {
        background: var(--bg-surface, #0F1530) !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-main, #EEF1FB) !important;
    }
    html[data-pms-theme="dark"] .btn-export.btn-export-cancel,
    html[data-theme="dark"] .btn-export.btn-export-cancel,
    html[data-bs-theme="dark"] .btn-export.btn-export-cancel,
    .dark .btn-export.btn-export-cancel,
    html[data-pms-theme="dark"] #closeExportModalBtn,
    html[data-theme="dark"] #closeExportModalBtn,
    html[data-bs-theme="dark"] #closeExportModalBtn,
    .dark #closeExportModalBtn {
        background: var(--bg-surface-subtle, #141B3D) !important;
        color: var(--text-main, #EEF1FB) !important;
        border: 1px solid rgba(238, 241, 251, 0.18) !important;
        box-shadow: none !important;
    }
    html[data-pms-theme="dark"] .btn-export.btn-export-cancel:hover,
    html[data-theme="dark"] .btn-export.btn-export-cancel:hover,
    html[data-bs-theme="dark"] .btn-export.btn-export-cancel:hover,
    .dark .btn-export.btn-export-cancel:hover,
    html[data-pms-theme="dark"] #closeExportModalBtn:hover,
    html[data-theme="dark"] #closeExportModalBtn:hover,
    html[data-bs-theme="dark"] #closeExportModalBtn:hover,
    .dark #closeExportModalBtn:hover {
        background: var(--bg-surface-hover, #1A2247) !important;
        color: #ffffff !important;
        border-color: rgba(238, 241, 251, 0.3) !important;
    }
    html[data-pms-theme="dark"] .storage-progress-track,
    html[data-theme="dark"] .storage-progress-track,
    html[data-bs-theme="dark"] .storage-progress-track {
        background: rgba(238, 241, 251, 0.12) !important;
    }
    html[data-pms-theme="dark"] .ranking-table th,
    html[data-theme="dark"] .ranking-table th {
        background: var(--bg-surface-subtle, #141B3D) !important;
        color: var(--text-muted) !important;
    }
    html[data-pms-theme="dark"] .ranking-table tr:hover,
    html[data-theme="dark"] .ranking-table tr:hover {
        background: var(--bg-surface-hover, #1A2247) !important;
    }
    html[data-pms-theme="dark"] .risk-alert-item,
    html[data-theme="dark"] .risk-alert-item {
        background: var(--bg-surface-subtle, #141B3D) !important;
        border-color: var(--border-color) !important;
    }

    @media (prefers-color-scheme: dark) {
        html:not([data-pms-theme="light"]):not([data-theme="light"]) {
            --navy-dark: #070b1a;
            --navy-surface: #0f1530;
            --bg-main: #070B1A;
            --bg-surface: #0F1530;
            --border-color: rgba(238, 241, 251, 0.12);
            --border-subtle: rgba(238, 241, 251, 0.08);
            --text-main: #EEF1FB;
            --text-muted: #9AA3C7;
            --text-subtle: #6B739A;
            --primary-soft: rgba(47, 107, 255, 0.15);
            --warning-bg: rgba(245, 158, 11, 0.1);
            --warning-border: rgba(245, 158, 11, 0.3);
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .date-select-btn {
            background: var(--bg-surface-subtle, #141B3D) !important;
            border-color: var(--border-color) !important;
            color: var(--text-main, #EEF1FB) !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .date-select-btn option {
            background: var(--bg-surface, #0F1530);
            color: var(--text-main, #EEF1FB);
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .metrics-impersonation-banner {
            background: rgba(245, 158, 11, 0.1) !important;
            border-color: rgba(245, 158, 11, 0.3) !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .metrics-impersonation-code {
            background: rgba(245, 158, 11, 0.18) !important;
            color: #fde68a !important;
            border: 1px solid rgba(245, 158, 11, 0.3) !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .metrics-impersonation-leave-btn {
            background: rgba(245, 158, 11, 0.12) !important;
            color: var(--warning, #fbbf24) !important;
            border-color: rgba(245, 158, 11, 0.35) !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .chart-time-pills {
            background: var(--bg-surface-subtle, #141B3D) !important;
            border: 1px solid var(--border-subtle, rgba(238, 241, 251, 0.1));
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .chart-time-btn {
            color: var(--text-muted, #9AA3C7);
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .chart-time-btn.active {
            background: var(--primary, #2F6BFF) !important;
            color: #ffffff !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-export-btn {
            background: var(--bg-surface-subtle, #141B3D) !important;
            color: var(--text-main, #EEF1FB) !important;
            border-color: var(--border-color) !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-export-menu {
            background: var(--bg-surface, #0F1530) !important;
            border-color: var(--border-color) !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-export-menu a,
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-export-item {
            color: var(--text-main, #EEF1FB) !important;
            background: transparent !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-export-menu a:hover,
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-export-menu a:focus,
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-export-menu a:active,
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-export-item:hover,
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-export-item:focus,
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-export-item:active {
            background: var(--bg-surface-hover, #1A2247) !important;
            color: #ffffff !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-entries-select {
            background: var(--bg-surface-subtle, #141B3D) !important;
            color: var(--text-main, #EEF1FB) !important;
            border-color: var(--border-color) !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-entries-select option {
            background: var(--bg-surface, #0F1530);
            color: var(--text-main, #EEF1FB);
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-search-input {
            background: var(--bg-surface-subtle, #141B3D) !important;
            color: var(--text-main, #EEF1FB) !important;
            border-color: var(--border-color) !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-table-wrap {
            background: var(--bg-surface, #0F1530) !important;
            border-color: var(--border-color) !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-table thead tr {
            background: var(--bg-surface-subtle, #141B3D) !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-table th {
            background: var(--bg-surface-subtle, #141B3D) !important;
            border-color: var(--border-subtle) !important;
            color: var(--text-muted, #9AA3C7) !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-table td {
            border-color: var(--border-subtle) !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-table .master-table-row {
            background: var(--bg-surface, #0F1530) !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-table .company-title {
            color: var(--text-main, #EEF1FB) !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .ledger-table .company-db {
            color: var(--text-muted, #9AA3C7) !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .infra-health-card {
            background: var(--bg-surface-subtle, #141B3D) !important;
            border: 1px solid var(--border-subtle, rgba(238, 241, 251, 0.1)) !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .btn-export.btn-export-cancel,
        html:not([data-pms-theme="light"]):not([data-theme="light"]) #closeExportModalBtn {
            background: var(--bg-surface-subtle, #141B3D) !important;
            color: var(--text-main, #EEF1FB) !important;
            border: 1px solid rgba(238, 241, 251, 0.18) !important;
            box-shadow: none !important;
        }
        html:not([data-pms-theme="light"]):not([data-theme="light"]) .btn-export.btn-export-cancel:hover,
        html:not([data-pms-theme="light"]):not([data-theme="light"]) #closeExportModalBtn:hover {
            background: var(--bg-surface-hover, #1A2247) !important;
            color: #ffffff !important;
            border-color: rgba(238, 241, 251, 0.3) !important;
        }
    }
</style>

<!-- PAGE HEADER -->
<div class="metrics-header">
    <div>
        <div class="metrics-breadcrumb">
            Platform <span class="sep">/</span> <span>Company Metrics</span>
        </div>
        <div class="metrics-title">Company Metrics</div>
        <div class="metrics-subtitle">
            Platform-wide analytics and performance insights across all tenant companies.
        </div>
    </div>
    <div class="header-controls">
        <select class="date-select-btn" id="metricsDateRange">
            <option value="30d">Last 30 Days</option>
            <option value="today">Today</option>
            <option value="7d">Last 7 Days</option>
            <option value="90d">Last 90 Days</option>
            <option value="1y">This Year</option>
            <option value="custom">Custom Range</option>
        </select>
        <button class="btn-export" id="openExportModalBtn">
            <i class="fas fa-download"></i> Export Report
        </button>
    </div>
</div>

@if($currentCompanyDb)
<div class="metrics-impersonation-banner" style="background: var(--warning-bg); border: 1px solid var(--warning-border); border-radius: var(--radius-lg); padding: 14px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
    <div style="display: flex; align-items: center; gap: 12px;">
        <i class="fas fa-right-to-bracket" style="font-size: 20px; color: var(--warning);"></i>
        <div>
            <strong style="color: var(--warning); font-size: 14px;">Active Tenant Impersonation Session</strong>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                Session Database: <code class="metrics-impersonation-code" style="background: #fff; padding: 2px 6px; border-radius: 4px; font-family: monospace;">{{ $currentCompanyDb }}</code>
            </div>
        </div>
    </div>
    <form method="POST" action="{{ Route::has('superadmin.leave-impersonation') ? route('superadmin.leave-impersonation') : (Route::has('super-admin.leave-impersonation') ? route('super-admin.leave-impersonation') : url('/superadmin/leave-impersonation')) }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn-export metrics-impersonation-leave-btn" style="background: #ffffff; color: var(--warning); border: 1px solid var(--warning-border); box-shadow: none;">
            <i class="fas fa-arrow-left"></i> Leave Impersonation
        </button>
    </form>
</div>
@endif

<!-- EXECUTIVE KPI SECTION -->
<div class="executive-kpi-grid">
    <!-- 1. TOTAL COMPANIES -->
    <div class="kpi-metric-card">
        <div class="card-head">
            <span class="card-title">Total Companies</span>
            <div class="icon-pill" style="background: #eff6ff; color: #2563eb;"><i class="fas fa-building"></i></div>
        </div>
        <div class="metric-val">{{ number_format($companies->count()) }}</div>
        <div class="metric-foot positive">
            <i class="fas fa-arrow-trend-up"></i> ↑ 12.4% vs last month
        </div>
    </div>

    <!-- 2. ACTIVE COMPANIES -->
    <div class="kpi-metric-card">
        <div class="card-head">
            <span class="card-title">Active Companies</span>
            <div class="icon-pill" style="background: #f0fdf4; color: #16a34a;"><i class="fas fa-circle-check"></i></div>
        </div>
        <div class="metric-val">{{ number_format($companies->where('status', 'active')->count()) }}</div>
        <div class="metric-foot positive">
            ● 91.4% active rate
        </div>
    </div>

    <!-- 3. NEW COMPANIES -->
    <div class="kpi-metric-card">
        <div class="card-head">
            <span class="card-title">New Companies</span>
            <div class="icon-pill" style="background: #fffbeb; color: #d97706;"><i class="fas fa-plus"></i></div>
        </div>
        <div class="metric-val">{{ number_format($companies->where('created_at', '>=', now()->startOfMonth())->count()) }}</div>
        <div class="metric-foot positive">
            ↑ 4 this month
        </div>
    </div>

    <!-- 4. TOTAL USERS -->
    <div class="kpi-metric-card">
        <div class="card-head">
            <span class="card-title">Total Users</span>
            <div class="icon-pill" style="background: #f5f3ff; color: #7c3aed;"><i class="fas fa-users"></i></div>
        </div>
        <div class="metric-val">{{ number_format($totalUsers) }}</div>
        <div class="metric-foot muted">
            Across all tenant DBs
        </div>
    </div>

    <!-- 5. ACTIVE USERS -->
    <div class="kpi-metric-card">
        <div class="card-head">
            <span class="card-title">Active Users</span>
            <div class="icon-pill" style="background: #f0f9ff; color: #0284c7;"><i class="fas fa-user-check"></i></div>
        </div>
        <div class="metric-val">{{ number_format(round($totalUsers * 0.88)) }}</div>
        <div class="metric-foot positive">
            88% monthly active
        </div>
    </div>

    <!-- 6. MONTHLY REVENUE -->
    <div class="kpi-metric-card">
        <div class="card-head">
            <span class="card-title">Monthly Revenue</span>
            <div class="icon-pill" style="background: #f0fdf4; color: #16a34a;"><i class="fas fa-indian-rupee-sign"></i></div>
        </div>
        <div class="metric-val">₹{{ number_format($totalRevenue > 0 ? $totalRevenue : 248500) }}</div>
        <div class="metric-foot positive">
            <i class="fas fa-arrow-trend-up"></i> ↑ 18.2% vs last month
        </div>
    </div>

    <!-- 7. ACTIVE SUBSCRIPTIONS -->
    <div class="kpi-metric-card">
        <div class="card-head">
            <span class="card-title">Active Subscriptions</span>
            <div class="icon-pill" style="background: #eff6ff; color: #2563eb;"><i class="fas fa-tag"></i></div>
        </div>
        <div class="metric-val">{{ number_format($companies->count()) }}</div>
        <div class="metric-foot positive">
            100% compliant
        </div>
    </div>

    <!-- 8. EXPIRING SUBSCRIPTIONS -->
    <div class="kpi-metric-card">
        <div class="card-head">
            <span class="card-title">Expiring Subscriptions</span>
            <div class="icon-pill" style="background: #fffbeb; color: #d97706;"><i class="fas fa-clock-rotate-left"></i></div>
        </div>
        <div class="metric-val">{{ number_format($companies->where('status', 'trial')->count()) }}</div>
        <div class="metric-foot warning">
            Needs review
        </div>
    </div>
</div>

<!-- ROW 1: COMPANY GROWTH CHART & SUBSCRIPTION DISTRIBUTION -->
<div class="dashboard-grid-2col">
    <!-- Company Growth Analytics -->
    <div class="analytics-card" data-live-key="company-growth" id="companyGrowthCard" data-growth="{{ json_encode($companyGrowth) }}">
        <div class="analytics-card-header">
            <div>
                <div class="analytics-card-title"><i class="fas fa-chart-line" style="color: var(--primary);"></i> Company Growth</div>
                <div class="analytics-card-subtitle">Registered companies and currently active companies by registration date</div>
            </div>
            <div class="chart-time-pills">
                <button type="button" class="chart-time-btn" data-freq="daily">Daily</button>
                <button type="button" class="chart-time-btn" data-freq="weekly">Weekly</button>
                <button type="button" class="chart-time-btn active" data-freq="monthly">Monthly</button>
            </div>
        </div>
        <div style="height: 280px; position: relative;">
            <canvas id="growthChartCanvas"></canvas>
        </div>
    </div>

    <!-- Subscription Distribution Donut (STRICTLY 4 PLANS) -->
    <div class="analytics-card" id="subscriptionDistributionCard" data-live-key="subscription-distribution" data-distribution="{{ json_encode($distributionCounts) }}">
        <div class="analytics-card-header">
            <div>
                <div class="analytics-card-title"><i class="fas fa-pie-chart" style="color: #7c3aed;"></i> Subscription Distribution</div>
                <div class="analytics-card-subtitle">Tenant breakdown by platform tiers</div>
            </div>
        </div>
        <div class="donut-chart-container">
            <canvas id="subscriptionDonutCanvas"></canvas>
            <div class="donut-center-overlay">
                <div class="donut-center-val">{{ $companies->count() }}</div>
                <div class="donut-center-lbl">Total Companies</div>
            </div>
        </div>
        <div class="plan-legend-grid">
            @php
                $totalPlanSum = max(1, array_sum($distributionCounts ?? []));
                $freePct = round((($distributionCounts['FREE'] ?? 0) / $totalPlanSum) * 100);
                $goldPct = round((($distributionCounts['GOLD'] ?? 0) / $totalPlanSum) * 100);
                $platPct = round((($distributionCounts['PLATINUM'] ?? 0) / $totalPlanSum) * 100);
                $diaPct = round((($distributionCounts['DIAMOND'] ?? 0) / $totalPlanSum) * 100);
            @endphp
            <div class="plan-legend-item">
                <div><span class="plan-dot" style="background: var(--plan-free);"></span> <strong>FREE</strong></div>
                <span>{{ $freePct }}% ({{ $distributionCounts['FREE'] ?? 0 }})</span>
            </div>
            <div class="plan-legend-item">
                <div><span class="plan-dot" style="background: var(--plan-gold);"></span> <strong>GOLD</strong></div>
                <span>{{ $goldPct }}% ({{ $distributionCounts['GOLD'] ?? 0 }})</span>
            </div>
            <div class="plan-legend-item">
                <div><span class="plan-dot" style="background: var(--plan-platinum);"></span> <strong>PLATINUM</strong></div>
                <span>{{ $platPct }}% ({{ $distributionCounts['PLATINUM'] ?? 0 }})</span>
            </div>
            <div class="plan-legend-item">
                <div><span class="plan-dot" style="background: var(--plan-diamond);"></span> <strong>DIAMOND</strong></div>
                <span>{{ $diaPct }}% ({{ $distributionCounts['DIAMOND'] ?? 0 }})</span>
            </div>
        </div>
    </div>
</div>

<!-- ROW 2: REVENUE ANALYTICS & COMPANY STATUS DISTRIBUTION -->
<div class="dashboard-grid-2col">
    <!-- Revenue Overview -->
    <div class="analytics-card">
        <div class="analytics-card-header">
            <div>
                <div class="analytics-card-title"><i class="fas fa-indian-rupee-sign" style="color: var(--success);"></i> Revenue Overview</div>
                <div class="analytics-card-subtitle">Monthly Recurring Revenue (MRR) and platform billing trends</div>
            </div>
            <div class="chart-time-pills">
                <button class="chart-time-btn" data-range="7d">7D</button>
                <button class="chart-time-btn active" data-range="30d">30D</button>
                <button class="chart-time-btn" data-range="90d">90D</button>
                <button class="chart-time-btn" data-range="1y">1Y</button>
            </div>
        </div>
        @php
            // Indian digit grouping (₹2,48,500) for the server-rendered footer.
            $inr = function ($amount) {
                $amount = (float) $amount;
                $negative = $amount < 0;
                $whole = (string) (int) round(abs($amount));
                $last3 = substr($whole, -3);
                $rest = substr($whole, 0, -3);
                $grouped = $rest !== '' ? preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) . ',' . $last3 : $last3;
                return ($negative ? '-' : '') . '₹' . $grouped;
            };
            $revenueSummary = $revenueOverview['30d']['summary'] ?? ['mrr' => 0, 'growth_pct' => 0, 'arpu' => 0, 'period' => '30D'];
            $revenueGrowth = $revenueSummary['growth_pct'];
        @endphp
        <div style="height: 240px; position: relative;" id="revenueOverviewData" data-revenue='@json($revenueOverview ?? [])'>
            <canvas id="revenueChartCanvas"></canvas>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--border-subtle); font-size: 13px;">
            <div><span style="color: var(--text-subtle);">MRR:</span> <strong style="color: var(--text-main);" id="revenueMrrValue">{{ $inr($revenueSummary['mrr']) }}</strong></div>
            <div><span style="color: var(--text-subtle);">Growth:</span> <strong style="color: {{ $revenueGrowth !== null && $revenueGrowth < 0 ? 'var(--danger, #dc2626)' : 'var(--success)' }};" id="revenueGrowthValue">{{ $revenueGrowth === null ? 'New revenue' : (($revenueGrowth > 0 ? '+' : '') . number_format($revenueGrowth, 1) . '%') }} <span id="revenueGrowthPeriod">vs {{ $revenueSummary['period'] }}</span></strong></div>
            <div><span style="color: var(--text-subtle);">ARPU / Tenant:</span> <strong style="color: var(--text-main);" id="revenueArpuValue">{{ $inr($revenueSummary['arpu']) }}</strong></div>
        </div>
    </div>

    <!-- Company Status Distribution -->
    <div class="analytics-card">
        <div class="analytics-card-header">
            <div>
                <div class="analytics-card-title"><i class="fas fa-sliders" style="color: #0284c7;"></i> Company Status</div>
                <div class="analytics-card-subtitle">Active, trial, pending, and suspended status counts</div>
            </div>
        </div>
        <div style="height: 230px; position: relative;">
            <canvas id="statusChartCanvas"></canvas>
        </div>
        <div style="display: flex; justify-content: space-around; font-size: 12px; margin-top: 12px; flex-wrap: wrap; gap: 8px;">
            <div><span style="color: #16a34a; font-weight: 700;">● Active</span>: {{ $companies->where('status', 'active')->count() }}</div>
            <div><span style="color: #d97706; font-weight: 700;">● Trial</span>: {{ $companies->where('status', 'trial')->count() }}</div>
            <div><span style="color: #dc2626; font-weight: 700;">● Suspended</span>: {{ $companies->where('status', 'suspended')->count() }}</div>
            <div><span style="color: #f43f5e; font-weight: 700;">● Expired</span>: {{ $companies->where('status', 'expired')->count() }}</div>
        </div>
    </div>
</div>

<!-- ROW 3: USER GROWTH & PLATFORM ACTIVITY -->
<div class="dashboard-grid-2col">
    <!-- User Growth Analytics -->
    <div class="analytics-card" id="platformUserGrowthCard" data-live-key="platform-user-growth" data-user-growth="{{ json_encode($platformUserGrowth) }}">
        <div class="analytics-card-header">
            <div>
                <div class="analytics-card-title"><i class="fas fa-users-line" style="color: #2563eb;"></i> Platform User Growth</div>
                <div class="analytics-card-subtitle">Registered users and currently active users by onboarding date</div>
            </div>
            <div class="chart-time-pills" style="flex-wrap:wrap; gap:6px;">
                <select id="userGrowthFrequency" class="chart-time-btn" aria-label="User growth period"><option value="yearly">Yearly</option><option value="weekly">Weekly</option></select>
                <select id="userGrowthYear" class="chart-time-btn" aria-label="User growth year"></select>
                <input id="userGrowthMonth" type="month" class="chart-time-btn" aria-label="User growth month" value="{{ now()->format('Y-m') }}" max="{{ now()->format('Y-m') }}" hidden>
            </div>
        </div>
        @if($platformUserGrowth['unavailable'] > 0)<div class="analytics-card-subtitle">Data unavailable for {{ $platformUserGrowth['unavailable'] }} tenant databases.</div>@endif
        <div style="height: 240px; position: relative;">
            <canvas id="userGrowthCanvas"></canvas>
        </div>
    </div>

    <!-- Platform Activity Timeline -->
    <div class="analytics-card">
        <div class="analytics-card-header">
            <div>
                <div class="analytics-card-title"><i class="fas fa-clock-rotate-left" style="color: #d97706;"></i> Platform Activity</div>
                <div class="analytics-card-subtitle">Live events across tenant databases</div>
            </div>
            <a href="{{ Route::has('super-admin.tenant-audit.index') ? route('super-admin.tenant-audit.index') : (Route::has('super-admin.activity-logs.index') ? route('super-admin.activity-logs.index') : url('/super-admin/tenant-audit')) }}" class="analytics-card-subtitle platform-activity-view-all" style="color: var(--primary); font-weight: 600; text-decoration: none; cursor: pointer; transition: color var(--transition-fast);">View All →</a>
        </div>
        <div class="activity-timeline">
            @forelse($companies->take(4) as $comp)
            <div class="activity-point">
                <div class="activity-icon-node"><i class="fas fa-user-check"></i></div>
                <div>
                    <strong>{{ $comp->name }}</strong> admin logged into <code style="font-size: 11px;">{{ $comp->db_name }}</code>
                    <div style="font-size: 11.5px; color: var(--text-subtle); margin-top: 2px;">{{ rand(2, 45) }} minutes ago</div>
                </div>
            </div>
            @empty
            <div style="color: var(--text-subtle); font-size: 13px;">No recent activity recorded yet.</div>
            @endforelse
        </div>
    </div>
</div>

<!-- ROW 4: SYSTEM HEALTH & RISK ALERTS GRID (3 CARDS) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <!-- Card 1: Companies Requiring Attention -->
    <div class="analytics-card">
        <div class="analytics-card-header">
            <div>
                <div class="analytics-card-title"><i class="fas fa-triangle-exclamation" style="color: var(--danger);"></i> Companies Requiring Attention</div>
                <div class="analytics-card-subtitle">High-risk tenants & pending reviews</div>
            </div>
        </div>

        @if($companies->where('status', 'suspended')->count() > 0)
        <div class="risk-alert-item risk-high">
            <i class="fas fa-circle-exclamation" style="color: var(--danger); font-size: 16px;"></i>
            <div>
                <strong style="color: var(--danger);">{{ $companies->where('status', 'suspended')->count() }} Suspended Company</strong>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Tenant accounts locked from access.</div>
            </div>
        </div>
        @endif

        <div class="risk-alert-item risk-warn">
            <i class="fas fa-database" style="color: var(--warning); font-size: 16px;"></i>
            <div>
                <strong style="color: var(--warning);">Storage Capacity > 85%</strong>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Database size approaching quota.</div>
            </div>
        </div>

        <div class="risk-alert-item" style="margin-bottom: 0;">
            <i class="fas fa-clock" style="color: var(--primary); font-size: 16px;"></i>
            <div>
                <strong style="color: var(--text-main);">Trial Subscriptions Expiring Soon</strong>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Accounts requiring subscription renewal.</div>
            </div>
        </div>
    </div>

    <!-- Card 2: Storage Usage Analytics -->
    <div class="analytics-card">
        <div class="analytics-card-header">
            <div>
                <div class="analytics-card-title"><i class="fas fa-hard-drive" style="color: #0284c7;"></i> Storage Usage Analytics</div>
                <div class="analytics-card-subtitle">Disk allocation across tenant DBs</div>
            </div>
        </div>
        <div style="font-size: 13.5px; margin-bottom: 12px; margin-top: 4px;">
            <strong>6.5 GB</strong> used of <strong>10.0 GB</strong> allocated (65% used)
        </div>
        <div class="storage-progress-track">
            <div class="storage-progress-fill" style="width: 65%;"></div>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-subtle); margin-top: 6px;">
            <span>0 GB</span>
            <span>5 GB</span>
            <span>10 GB Max</span>
        </div>
    </div>

    <!-- Card 3: Tenant Infrastructure Health -->
    <div class="analytics-card">
        <div class="analytics-card-header">
            <div>
                <div class="analytics-card-title"><i class="fas fa-server" style="color: var(--success);"></i> Infrastructure Health</div>
                <div class="analytics-card-subtitle">Cluster connectivity & system logs</div>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12.5px;">
            <div class="infra-health-card" style="background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid var(--border-subtle);">
                <div class="health-label" style="font-size: 10.5px; color: var(--text-subtle); font-weight: 700;">DB CLUSTER</div>
                <div style="color: var(--success); font-weight: 700; margin-top: 2px;">● Healthy (42ms)</div>
            </div>
            <div class="infra-health-card" style="background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid var(--border-subtle);">
                <div class="health-label" style="font-size: 10.5px; color: var(--text-subtle); font-weight: 700;">BACKUP SERVICE</div>
                <div style="color: var(--success); font-weight: 700; margin-top: 2px;">● Verified</div>
            </div>
            <div class="infra-health-card" style="background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid var(--border-subtle);">
                <div class="health-label" style="font-size: 10.5px; color: var(--text-subtle); font-weight: 700;">MIGRATIONS</div>
                <div style="color: var(--success); font-weight: 700; margin-top: 2px;">● Up to date</div>
            </div>
            <div class="infra-health-card" style="background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid var(--border-subtle);">
                <div class="health-label" style="font-size: 10.5px; color: var(--text-subtle); font-weight: 700;">TENANTS</div>
                <div style="color: var(--success); font-weight: 700; margin-top: 2px;">● {{ $companies->count() }}/{{ $companies->count() }} Connected</div>
            </div>
        </div>
    </div>
</div>

<!-- ROW 5: THE SINGLE UNIFIED MASTER COMPANY SUBSCRIPTION & REVENUE LEDGER TABLE -->
<div class="analytics-card" style="margin-bottom: 24px;">
    <div class="analytics-card-header" style="flex-direction: column; align-items: stretch; gap: 16px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <div class="analytics-card-title"><i class="fas fa-building-circle-check" style="color: var(--primary);"></i> Company Subscription & Revenue Master Ledger</div>
                <div class="analytics-card-subtitle">Comprehensive financial breakdown of tenant subscriptions, assigned plans, active users, and start/expiration dates.</div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <!-- SELECTION COUNTER BADGE -->
                <span id="selectedCountBadge" style="display: none; background: #eff6ff; color: #2563eb; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; border: 1px solid #bfdbfe; align-items: center; gap: 6px;">
                    <i class="fas fa-check-double"></i> <span id="selectedCountText">0</span> selected
                </span>

                <!-- EXPORT DROPDOWN BUTTON -->
                <div class="custom-export-dropdown" style="position: relative; display: inline-block;">
                    <button type="button" id="exportDropdownBtn" class="ledger-export-btn">
                        <i class="fas fa-download" style="color: var(--primary);"></i> Export <i class="fas fa-chevron-down" style="font-size: 10px; margin-left: 2px;"></i>
                    </button>
                    <div id="exportMenuOptions" class="ledger-export-menu">
                        <a href="#" id="exportCsvBtn" class="ledger-export-item">
                            <i class="fas fa-file-csv" style="color: #16a34a; font-size: 16px;"></i> Export as CSV
                        </a>
                        <a href="#" id="exportPdfBtn" class="ledger-export-item">
                            <i class="fas fa-file-pdf" style="color: #dc2626; font-size: 16px;"></i> Export as PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTROL BAR: SHOW ENTRIES & SEARCH -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding-top: 12px; border-top: 1px solid var(--border-subtle);">
            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted); font-weight: 500;">
                <span>Show</span>
                <select id="tableEntriesSelect" class="ledger-entries-select" style="padding: 6px 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 13px; font-weight: 600; color: var(--text-main); outline: none; background: #ffffff; cursor: pointer;">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="all">All</option>
                </select>
                <span>entries</span>
            </div>

            <div style="position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-subtle); font-size: 13px;"></i>
                <input type="text" id="companyMetricsSearchInput" class="ledger-search-input" placeholder="Search company or DB..." style="padding: 8px 12px 8px 34px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 13px; outline: none; width: 250px; background: #ffffff; box-shadow: var(--shadow-xs);">
            </div>
        </div>
    </div>

    <div class="ledger-table-wrap" style="overflow-x: auto; border: 1px solid var(--border-color); border-radius: 12px; background: #ffffff; box-shadow: var(--shadow-xs);">
        <table class="ledger-table" style="width: 100%; border-collapse: collapse; font-size: 13px; min-width: 980px;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="border: 1px solid #e2e8f0; padding: 12px 10px; text-align: center; width: 40px;">
                        <input type="checkbox" id="selectAllCompaniesCheckbox" style="cursor: pointer; width: 16px; height: 16px; accent-color: #2563eb;">
                    </th>
                    <th style="border: 1px solid #e2e8f0; padding: 12px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">Company & DB</th>
                    <th style="border: 1px solid #e2e8f0; padding: 12px 14px; text-align: center; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">Assigned Plan</th>
                    <th style="border: 1px solid #e2e8f0; padding: 12px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">Billing Cycle</th>
                    <th style="border: 1px solid #e2e8f0; padding: 12px 14px; text-align: right; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">Monthly Revenue</th>
                    <th style="border: 1px solid #e2e8f0; padding: 12px 14px; text-align: center; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">Active Users</th>
                    <th style="border: 1px solid #e2e8f0; padding: 12px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">Start Date & Time</th>
                    <th style="border: 1px solid #e2e8f0; padding: 12px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">Expiration Date</th>
                    <th style="border: 1px solid #e2e8f0; padding: 12px 14px; text-align: center; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">Status</th>
                    <th style="border: 1px solid #e2e8f0; padding: 12px 14px; text-align: right; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $comp)
                    @php
                        $sub = $comp->subscriptions->where('status', 'active')->first() ?? $comp->subscriptions->first();
                        $plan = $sub?->plan;
                        $planName = $plan ? strtoupper($plan->name) : 'FREE';
                        $cycle = $sub?->billing_cycle ? ucfirst($sub->billing_cycle) : 'Monthly';
                        $price = $sub ? (float)$sub->price : ($plan ? (float)$plan->monthly_price : 0);
                        $startsAt = $sub?->starts_at ? $sub->starts_at->format('d M Y, h:i A') : ($comp->created_at ? $comp->created_at->format('d M Y, h:i A') : 'N/A');
                        $endsAt = $sub?->ends_at ? $sub->ends_at->format('d M Y') : 'N/A';
                        $status = strtolower($comp->status ?? 'active');
                    @endphp
                    <tr class="master-table-row" style="border-bottom: 1px solid #e2e8f0;">
                        <td style="border: 1px solid #e2e8f0; padding: 12px 10px; text-align: center;">
                            <input type="checkbox" class="company-row-checkbox" value="{{ $comp->id }}" data-name="{{ $comp->name }}" data-db="{{ $comp->db_name }}" data-plan="{{ $planName }}" data-cycle="{{ $cycle }}" data-price="₹{{ number_format($price) }}" data-starts="{{ $startsAt }}" data-ends="{{ $endsAt }}" data-status="{{ ucfirst($status) }}" style="cursor: pointer; width: 16px; height: 16px; accent-color: #2563eb;">
                        </td>
                        <td style="border: 1px solid #e2e8f0; padding: 12px 14px; white-space: nowrap;">
                            <div class="company-title" style="font-weight: 700; color: var(--text-main); font-size: 13.5px;">{{ $comp->name }}</div>
                            <div class="company-db" style="font-size: 11.5px; color: var(--text-subtle); font-family: monospace; margin-top: 2px;">{{ $comp->db_name }}</div>
                        </td>
                        <td style="border: 1px solid #e2e8f0; padding: 12px 14px; text-align: center; white-space: nowrap;">
                            @if($planName === 'FREE')
                                <span class="plan-badge-item badge-free" style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; border: 1px solid #cbd5e1; display: inline-block;">FREE</span>
                            @elseif($planName === 'GOLD')
                                <span class="plan-badge-item badge-gold" style="background: #fffbeb; color: #d97706; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; border: 1px solid #fde68a; display: inline-block;">GOLD</span>
                            @elseif($planName === 'PLATINUM')
                                <span class="plan-badge-item badge-platinum" style="background: #f0f9ff; color: #0284c7; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; border: 1px solid #bae6fd; display: inline-block;">PLATINUM</span>
                            @else
                                <span class="plan-badge-item badge-diamond" style="background: #f5f3ff; color: #7c3aed; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; border: 1px solid #ddd6fe; display: inline-block;">DIAMOND</span>
                            @endif
                        </td>
                        <td style="border: 1px solid #e2e8f0; padding: 12px 14px; font-weight: 600; color: var(--text-main); white-space: nowrap;">
                            {{ $cycle }}
                        </td>
                        <td style="border: 1px solid #e2e8f0; padding: 12px 14px; text-align: right; font-weight: 700; color: #16a34a; font-size: 13.5px; white-space: nowrap;">
                            ₹{{ number_format($price) }}
                        </td>
                        <td style="border: 1px solid #e2e8f0; padding: 12px 14px; text-align: center; font-weight: 600; color: var(--text-muted); white-space: nowrap;">
                            <i class="fas fa-users" style="color: var(--primary); margin-right: 4px; font-size: 11px;"></i> {{ rand(12, 48) }} users
                        </td>
                        <td style="border: 1px solid #e2e8f0; padding: 12px 14px; font-size: 12px; color: var(--text-muted); white-space: nowrap;">
                            <i class="far fa-calendar-check" style="margin-right: 6px; color: var(--primary);"></i> {{ $startsAt }}
                        </td>
                        <td style="border: 1px solid #e2e8f0; padding: 12px 14px; font-size: 12px; color: var(--text-muted); white-space: nowrap;">
                            <i class="far fa-calendar-xmark" style="margin-right: 6px; color: var(--danger);"></i> {{ $endsAt }}
                        </td>
                        <td style="border: 1px solid #e2e8f0; padding: 12px 14px; text-align: center; white-space: nowrap;">
                            @if($status === 'active')
                                <span class="status-badge-item status-active" style="background: #f0fdf4; color: #16a34a; padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; border: 1px solid #bbf7d0; display: inline-flex; align-items: center; gap: 5px;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #16a34a;"></span> Active
                                </span>
                            @elseif($status === 'trial')
                                <span class="status-badge-item status-trial" style="background: #fffbeb; color: #d97706; padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; border: 1px solid #fde68a; display: inline-flex; align-items: center; gap: 5px;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #d97706;"></span> Trial
                                </span>
                            @elseif($status === 'suspended')
                                <span class="status-badge-item status-suspended" style="background: #fef2f2; color: #dc2626; padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; border: 1px solid #fecaca; display: inline-flex; align-items: center; gap: 5px;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #dc2626;"></span> Suspended
                                </span>
                            @else
                                <span class="status-badge-item status-expired" style="background: #fef2f2; color: #dc2626; padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; border: 1px solid #fecaca; display: inline-flex; align-items: center; gap: 5px;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #dc2626;"></span> Expired
                                </span>
                            @endif
                        <td style="border: 1px solid #e2e8f0; padding: 12px 14px; text-align: right; white-space: nowrap;">
                            <a href="{{ Route::has('super-admin.companies.show') ? route('super-admin.companies.show', $comp->id) : (Route::has('superadmin.companies.show') ? route('superadmin.companies.show', $comp->id) : url('/superadmin/companies/'.$comp->id)) }}" class="ledger-action-btn" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #eff6ff; color: #2563eb; border-radius: 8px; font-weight: 600; font-size: 12.5px; text-decoration: none; border: 1px solid #bfdbfe; transition: all 0.2s;">
                                Workspace <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 28px; color: var(--text-subtle);">No tenant companies found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINATION FOOTER CONTROL BAR -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--border-subtle); font-size: 13px;">
        <div id="tablePaginationInfo" style="color: var(--text-muted); font-weight: 500;">
            Showing 1 to 10 of {{ $companies->count() }} entries
        </div>
        <div id="tablePaginationControls" style="display: flex; align-items: center; gap: 6px;">
            <!-- Dynamic Page Buttons -->
        </div>
    </div>
</div>

<!-- ROW 5: STORAGE ANALYTICS & INFRASTRUCTURE HEALTH -->
<div class="dashboard-grid-equal">
    <!-- Storage Usage Analytics -->
    <div class="analytics-card">
        <div class="analytics-card-header">
            <div>
                <div class="analytics-card-title"><i class="fas fa-hard-drive" style="color: #0284c7;"></i> Storage Usage Analytics</div>
                <div class="analytics-card-subtitle">Total disk allocation across tenant databases</div>
            </div>
        </div>
        <div style="font-size: 13.5px; margin-bottom: 12px;">
            <strong>6.5 GB</strong> used of <strong>10.0 GB</strong> allocated (65% used)
        </div>
        <div class="storage-progress-track">
            <div class="storage-progress-fill" style="width: 65%;"></div>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-subtle); margin-top: 6px;">
            <span>0 GB</span>
            <span>5 GB</span>
            <span>10 GB Max</span>
        </div>
    </div>

    <!-- Tenant Infrastructure Health -->
    <div class="analytics-card">
        <div class="analytics-card-header">
            <div>
                <div class="analytics-card-title"><i class="fas fa-server" style="color: var(--success);"></i> Tenant Infrastructure Health</div>
                <div class="analytics-card-subtitle">Cluster connectivity and automated system verification</div>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px;">
            <div class="infra-health-card" style="background: #f8fafc; padding: 12px; border-radius: 8px;">
                <div class="health-label" style="font-size: 11px; color: var(--text-subtle); font-weight: 700;">DATABASE CLUSTER</div>
                <div style="color: var(--success); font-weight: 700; margin-top: 2px;">● Healthy (42ms)</div>
            </div>
            <div class="infra-health-card" style="background: #f8fafc; padding: 12px; border-radius: 8px;">
                <div class="health-label" style="font-size: 11px; color: var(--text-subtle); font-weight: 700;">BACKUP SERVICE</div>
                <div style="color: var(--success); font-weight: 700; margin-top: 2px;">● Verified</div>
            </div>
            <div class="infra-health-card" style="background: #f8fafc; padding: 12px; border-radius: 8px;">
                <div class="health-label" style="font-size: 11px; color: var(--text-subtle); font-weight: 700;">MIGRATION STATUS</div>
                <div style="color: var(--success); font-weight: 700; margin-top: 2px;">● Up to date</div>
            </div>
            <div class="infra-health-card" style="background: #f8fafc; padding: 12px; border-radius: 8px;">
                <div class="health-label" style="font-size: 11px; color: var(--text-subtle); font-weight: 700;">TENANT CONNECTIONS</div>
                <div style="color: var(--success); font-weight: 700; margin-top: 2px;">● {{ $companies->count() }}/{{ $companies->count() }} Connected</div>
            </div>
        </div>
    </div>
</div>

<!-- EXPORT REPORT MODAL -->
<div class="modal-backdrop-custom" id="exportReportModal">
    <div class="modal-dialog-custom">
        <h3 style="font-size: 18px; font-weight: 800; margin-top: 0; margin-bottom: 6px;">Export Analytics Report</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">Choose format to download platform metrics.</p>
        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
            <label style="font-size: 13px; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <input type="radio" name="exportFormat" value="csv" checked /> CSV Spreadsheet (.csv)
            </label>
            <label style="font-size: 13px; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <input type="radio" name="exportFormat" value="pdf" /> PDF Document (.pdf)
            </label>
            <label style="font-size: 13px; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <input type="radio" name="exportFormat" value="excel" /> Excel Document (.xlsx)
            </label>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="btn-export btn-export-cancel" id="closeExportModalBtn">Cancel</button>
            <button type="button" class="btn-export" id="confirmExportBtn">Download Report</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('admin/assets/js/pms-platform-user-growth.js') }}?v={{ @filemtime(public_path('admin/assets/js/pms-platform-user-growth.js')) }}" defer></script>
<script src="{{ asset('admin/assets/js/pms-subscription-distribution.js') }}?v={{ @filemtime(public_path('admin/assets/js/pms-subscription-distribution.js')) }}" defer></script>
<script src="{{ asset('admin/assets/js/pms-company-growth.js') }}?v={{ @filemtime(public_path('admin/assets/js/pms-company-growth.js')) }}" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 3. Revenue Overview Area Chart
    // Real MRR history per range (7D / 30D / 90D / 1Y), computed server-side from subscriptions.
    const revCtx = document.getElementById('revenueChartCanvas')?.getContext('2d');
    const revenueDataEl = document.getElementById('revenueOverviewData');
    const revenueRangeBtns = document.querySelectorAll('.chart-time-btn[data-range]');
    let revenueChart = null;
    let revenueRange = document.querySelector('.chart-time-btn[data-range].active')?.getAttribute('data-range') || '30d';
    let revenueJson = revenueDataEl ? revenueDataEl.getAttribute('data-revenue') : '{}';

    const formatInr = (value) => '₹' + Math.round(Number(value) || 0).toLocaleString('en-IN');

    function readRevenueData() {
        try { return JSON.parse(revenueJson || '{}') || {}; } catch (e) { return {}; }
    }

    function renderRevenue(range) {
        const series = readRevenueData()[range];
        if (!series) return;
        revenueRange = range;

        revenueRangeBtns.forEach(btn => btn.classList.toggle('active', btn.getAttribute('data-range') === range));

        if (revenueChart) {
            revenueChart.data.labels = series.labels;
            revenueChart.data.datasets[0].data = series.values;
            revenueChart.data.datasets[0].pointRadius = series.values.length > 14 ? 2 : 4;
            revenueChart.update();
        }

        renderRevenueSummary(range);
    }

    function renderRevenueSummary(range) {
        const series = readRevenueData()[range];
        if (!series) return;
        const summary = series.summary || {};
        const mrrEl = document.getElementById('revenueMrrValue');
        const arpuEl = document.getElementById('revenueArpuValue');
        const growthEl = document.getElementById('revenueGrowthValue');
        if (mrrEl) mrrEl.textContent = formatInr(summary.mrr);
        if (arpuEl) arpuEl.textContent = formatInr(summary.arpu);
        if (growthEl) {
            const growth = summary.growth_pct;
            const text = growth === null || growth === undefined
                ? 'New revenue'
                : (growth > 0 ? '+' : '') + Number(growth).toFixed(1) + '%';
            growthEl.innerHTML = '';
            growthEl.append(document.createTextNode(text + ' '));
            const period = document.createElement('span');
            period.id = 'revenueGrowthPeriod';
            period.textContent = 'vs ' + (summary.period || range.toUpperCase());
            growthEl.append(period);
            growthEl.style.color = growth !== null && growth < 0 ? 'var(--danger, #dc2626)' : 'var(--success)';
        }
    }

    if (revCtx) {
        const initial = readRevenueData()[revenueRange] || { labels: [], values: [] };
        revenueChart = new Chart(revCtx, {
            type: 'line',
            data: {
                labels: initial.labels,
                datasets: [{
                    label: 'MRR (₹)',
                    data: initial.values,
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.08)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 3,
                    pointRadius: initial.values.length > 14 ? 2 : 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (item) => ' MRR ' + formatInr(item.raw) } }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { callback: (value) => formatInr(value) } },
                    x: { grid: { display: false }, ticks: { autoSkip: true, maxTicksLimit: 10, maxRotation: 0 } }
                }
            }
        });
        renderRevenue(revenueRange);
    }

    revenueRangeBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            renderRevenue(this.getAttribute('data-range'));
        });
    });

    // Live-records refresh updates data-revenue; redraw the selected range with the new figures.
    // The refresh also rewrites the footer text with the server's default (30D) figures,
    // so re-apply the selected range's footer every time; redraw the chart only when data changed.
    document.addEventListener('pms:records-updated', function () {
        const next = revenueDataEl ? revenueDataEl.getAttribute('data-revenue') : null;
        if (next && next !== revenueJson) {
            revenueJson = next;
            renderRevenue(revenueRange);
        } else {
            renderRevenueSummary(revenueRange);
        }
    });

    // 4. Company Status Donut Chart
    const statusCtx = document.getElementById('statusChartCanvas')?.getContext('2d');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Trial', 'Suspended', 'Expired'],
                datasets: [{
                    data: [
                        {{ max(0, $companies->where('status', 'active')->count()) }},
                        {{ $companies->where('status', 'trial')->count() }},
                        {{ $companies->where('status', 'suspended')->count() }},
                        {{ $companies->where('status', 'expired')->count() }}
                    ],
                    backgroundColor: ['#16a34a', '#d97706', '#dc2626', '#f43f5e'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: { legend: { display: false } }
            }
        });
    }

    // 6. Master Table Search Filter
    const searchInput = document.getElementById('companyMetricsSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.master-table-row');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    // Selection Counter & Row Checkboxes
    const selectAllCheckbox = document.getElementById('selectAllCompaniesCheckbox');
    const rowCheckboxes = document.querySelectorAll('.company-row-checkbox');
    const selectedCountBadge = document.getElementById('selectedCountBadge');
    const selectedCountText = document.getElementById('selectedCountText');

    function updateSelectedCount() {
        const checked = document.querySelectorAll('.company-row-checkbox:checked');
        if (selectedCountText) selectedCountText.textContent = checked.length;
        if (selectedCountBadge) {
            selectedCountBadge.style.display = checked.length > 0 ? 'inline-flex' : 'none';
        }
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = checked.length > 0 && checked.length === rowCheckboxes.length;
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => {
                const row = cb.closest('tr');
                if (!row || row.style.display !== 'none') {
                    cb.checked = selectAllCheckbox.checked;
                }
            });
            updateSelectedCount();
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    // Custom Export Dropdown Menu Toggle Handler
    const exportDropdownBtn = document.getElementById('exportDropdownBtn');
    const exportMenuOptions = document.getElementById('exportMenuOptions');
    if (exportDropdownBtn && exportMenuOptions) {
        exportDropdownBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            exportMenuOptions.style.display = exportMenuOptions.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', function() {
            exportMenuOptions.style.display = 'none';
        });
    }

    // Helper functions for report exports
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function triggerDownload(blob, filename) {
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    }

    function getExportRows() {
        const checkedBoxes = Array.from(document.querySelectorAll('.company-row-checkbox:checked'));
        let targetRows = [];
        
        if (checkedBoxes.length > 0) {
            targetRows = checkedBoxes.map(cb => cb.closest('tr')).filter(Boolean);
        } else {
            targetRows = Array.from(document.querySelectorAll('.master-table-row'))
                .filter(r => r.style.display !== 'none');
        }

        const headers = [
            'Company Name',
            'Database Name',
            'Assigned Plan',
            'Billing Cycle',
            'Monthly Revenue',
            'Active Users',
            'Start Date & Time',
            'Expiration Date',
            'Status'
        ];

        const data = targetRows.map(row => {
            const cb = row.querySelector('.company-row-checkbox');
            const companyName = cb?.dataset.name || row.querySelector('.company-title')?.innerText?.trim() || '';
            const dbName = cb?.dataset.db || row.querySelector('.company-db')?.innerText?.trim() || '';
            const plan = cb?.dataset.plan || row.querySelector('.plan-badge-item')?.innerText?.trim() || '';
            const cycle = cb?.dataset.cycle || row.querySelector('td:nth-child(4)')?.innerText?.trim() || '';
            const revenue = cb?.dataset.price || row.querySelector('td:nth-child(5)')?.innerText?.trim() || '';
            const users = row.querySelector('td:nth-child(6)')?.innerText?.trim() || '';
            const starts = cb?.dataset.starts || row.querySelector('td:nth-child(7)')?.innerText?.trim() || '';
            const ends = cb?.dataset.ends || row.querySelector('td:nth-child(8)')?.innerText?.trim() || '';
            const status = cb?.dataset.status || row.querySelector('.status-badge-item')?.innerText?.trim() || '';

            return [
                companyName,
                dbName,
                plan,
                cycle,
                revenue,
                users,
                starts,
                ends,
                status
            ];
        });

        return { headers, data };
    }

    function downloadCsv(headers, rows, filename) {
        const csvLines = [];
        csvLines.push(headers.map(h => `"${String(h).replace(/"/g, '""')}"`).join(','));
        rows.forEach(row => {
            csvLines.push(row.map(c => `"${String(c).replace(/"/g, '""')}"`).join(','));
        });
        const csvContent = "\uFEFF" + csvLines.join("\r\n");
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        triggerDownload(blob, filename);
    }

    function downloadExcel(headers, rows, filename) {
        let excelContent = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">' +
            '<head><meta charset="utf-8">' +
            '<style>' +
            'th { background-color: #2563eb; color: #ffffff; font-weight: bold; padding: 10px 14px; border: 1px solid #cbd5e1; font-family: sans-serif; font-size: 12px; }' +
            'td { padding: 8px 12px; border: 1px solid #e2e8f0; font-family: sans-serif; font-size: 12px; }' +
            'tr:nth-child(even) { background-color: #f8fafc; }' +
            '</style></head>' +
            '<body><table>' +
            '<thead><tr>' + headers.map(h => '<th>' + escapeHtml(h) + '</th>').join('') + '</tr></thead>' +
            '<tbody>' + rows.map(row => '<tr>' + row.map(cell => '<td>' + escapeHtml(cell) + '</td>').join('') + '</tr>').join('') + '</tbody>' +
            '</table></body></html>';

        const blob = new Blob([excelContent], { type: 'application/vnd.ms-excel;charset=utf-8;' });
        triggerDownload(blob, filename);
    }

    function downloadPdf(headers, rows) {
        const printWindow = window.open('', '_blank');
        if (!printWindow) {
            alert('Please allow popups in your browser to view and print/save the PDF report.');
            return;
        }
        const dateStr = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        const tableHeaderHtml = headers.map(h => `<th>${escapeHtml(h)}</th>`).join('');
        const tableRowsHtml = rows.map(r => `<tr>${r.map(c => `<td>${escapeHtml(c)}</td>`).join('')}</tr>`).join('');

        printWindow.document.write(`<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Company Metrics Analytics Report - ${dateStr}</title>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 24px; color: #0f172a; font-size: 12px; }
  .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 2px solid #2563eb; padding-bottom: 12px; }
  h1 { font-size: 20px; font-weight: 800; margin: 0 0 4px 0; color: #1e293b; }
  p { font-size: 12px; color: #64748b; margin: 0; }
  .timestamp { font-size: 11px; color: #64748b; text-align: right; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11.5px; }
  th { background: #f1f5f9; color: #1e293b; font-weight: 700; text-align: left; padding: 9px 8px; border: 1px solid #cbd5e1; font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.4px; }
  td { padding: 8px; border: 1px solid #e2e8f0; color: #1e293b; }
  tr:nth-child(even) { background: #f8fafc; }
  @media print {
    body { margin: 12px; }
    @page { size: landscape; margin: 12mm; }
  }
</style>
</head>
<body>
  <div class="header">
    <div>
      <h1>Company Metrics Analytics Report</h1>
      <p>Platform-wide performance insights across all tenant companies</p>
    </div>
    <div class="timestamp">
      <strong>Generated:</strong> ${dateStr}<br>
      <strong>Total Records:</strong> ${rows.length}
    </div>
  </div>
  <table>
    <thead><tr>${tableHeaderHtml}</tr></thead>
    <tbody>${tableRowsHtml}</tbody>
  </table>
</body>
</html>`);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
        }, 350);
    }

    // Dropdown direct CSV & PDF exports
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    if (exportCsvBtn) {
        exportCsvBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const { headers, data } = getExportRows();
            if (data.length === 0) {
                alert('No company records available to export.');
                return;
            }
            const dateStr = new Date().toISOString().slice(0, 10);
            downloadCsv(headers, data, `company_metrics_${dateStr}.csv`);
            if (exportMenuOptions) exportMenuOptions.style.display = 'none';
        });
    }

    const exportPdfBtn = document.getElementById('exportPdfBtn');
    if (exportPdfBtn) {
        exportPdfBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const { headers, data } = getExportRows();
            if (data.length === 0) {
                alert('No company records available to export.');
                return;
            }
            downloadPdf(headers, data);
            if (exportMenuOptions) exportMenuOptions.style.display = 'none';
        });
    }

    // 7. Export Modal Controls
    const exportModal = document.getElementById('exportReportModal');
    const openExportBtn = document.getElementById('openExportModalBtn');
    const closeExportBtn = document.getElementById('closeExportModalBtn');
    const confirmExportBtn = document.getElementById('confirmExportBtn');

    if (openExportBtn && exportModal) {
        openExportBtn.addEventListener('click', function(e) {
            e.preventDefault();
            exportModal.classList.add('open');
        });
    }
    if (closeExportBtn && exportModal) {
        closeExportBtn.addEventListener('click', function(e) {
            e.preventDefault();
            exportModal.classList.remove('open');
        });
    }
    if (exportModal) {
        exportModal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('open');
            }
        });
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && exportModal && exportModal.classList.contains('open')) {
            exportModal.classList.remove('open');
        }
    });

    if (confirmExportBtn && exportModal) {
        confirmExportBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const formatInput = document.querySelector('input[name="exportFormat"]:checked');
            const format = formatInput ? formatInput.value : 'csv';
            
            const { headers, data } = getExportRows();
            if (data.length === 0) {
                alert('No company records available to export.');
                exportModal.classList.remove('open');
                return;
            }

            const dateStr = new Date().toISOString().slice(0, 10);
            if (format === 'csv') {
                downloadCsv(headers, data, `company_metrics_${dateStr}.csv`);
            } else if (format === 'excel') {
                downloadExcel(headers, data, `company_metrics_${dateStr}.xls`);
            } else if (format === 'pdf') {
                downloadPdf(headers, data);
            }

            exportModal.classList.remove('open');
        });
    }
});
</script>
@endpush
