@extends('layouts.superadmin')

@section('title', 'Developer Management Center - Super Admin')

@push('styles')
<style>
    /* CSS Tokens & Custom Styling */
    :root {
        --primary: #2F6BFF;
        --primary-hover: #073a26;
        --primary-light: #EEF2FF;
        --primary-border: #C7D2FE;
        --blue-accent: #2563eb;
        --blue-light: #eff6ff;
        --purple-accent: #7c3aed;
        --purple-light: #f5f3ff;
        --warning: #f59e0b;
        --warning-light: #fffbeb;
        --danger: #ef4444;
        --danger-light: #fef2f2;
        --slate-dark: #0f172a;
        --slate-body: #334155;
        --slate-muted: #64748b;
        --slate-subtle: #94a3b8;
        --bg-surface: #ffffff;
        --bg-subtle: #f8fafc;
        --border-color: #cbd5e1;
        --radius-xl: 16px;
        --radius-lg: 12px;
        --radius-md: 8px;
        --radius-sm: 6px;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(15, 23, 42, 0.06), 0 2px 4px -1px rgba(15, 23, 42, 0.04);
        --shadow-lg: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
    }

    .dev-container {
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
        color: var(--slate-muted);
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .breadcrumbs-bar a {
        color: var(--slate-muted);
        text-decoration: none;
    }
    .breadcrumbs-bar a:hover {
        color: var(--primary);
    }

    .page-title {
        font-size: 24px;
        font-weight: 800;
        color: var(--slate-dark);
        margin: 0;
        letter-spacing: -0.5px;
    }

    .page-subtitle {
        font-size: 13.5px;
        color: var(--slate-muted);
        margin-top: 4px;
        margin-bottom: 0;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Buttons */
    .btn-action-primary {
        background: var(--primary);
        color: #ffffff;
        border: 1px solid var(--primary-hover);
        border-radius: var(--radius-md);
        padding: 9px 16px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(47, 107, 255, 0.2);
    }

    .btn-action-primary:hover {
        background: var(--primary-hover);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(47, 107, 255, 0.3);
    }

    .btn-action-secondary {
        background: var(--bg-surface);
        color: var(--slate-body);
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
        border-color: var(--slate-subtle);
    }

    /* KPI Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .kpi-card {
        background: var(--bg-surface);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        padding: 16px 20px;
        box-shadow: var(--shadow-sm);
        transition: all 0.2s;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary-border);
    }

    .kpi-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .kpi-title {
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--slate-muted);
    }

    .kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .kpi-val {
        font-size: 26px;
        font-weight: 800;
        color: var(--slate-dark);
        line-height: 1.2;
        margin-bottom: 4px;
    }

    .kpi-sub {
        font-size: 11.5px;
        color: var(--slate-muted);
        display: flex;
        align-items: center;
        gap: 4px;
        font-weight: 500;
    }

    /* Toolbar & Filters */
    .filter-card {
        background: var(--bg-surface);
        border-radius: var(--radius-xl);
        border: 1px solid var(--border-color);
        padding: 18px 22px;
        margin-bottom: 24px;
        box-shadow: var(--shadow-sm);
    }

    .filter-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
    }

    .search-group {
        flex: 1;
        min-width: 280px;
        position: relative;
    }

    .search-group input {
        width: 100%;
        padding: 9px 14px 9px 38px;
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 13px;
        color: var(--slate-dark);
        outline: none;
        transition: all 0.2s;
    }

    .search-group input:focus {
        background: #ffffff;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(47, 107, 255, 0.15);
    }

    .search-group i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--slate-muted);
        font-size: 18px;
    }

    .filter-selects {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-selects select {
        padding: 9px 12px;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 12.5px;
        font-weight: 600;
        color: var(--slate-body);
        outline: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-selects select:focus {
        border-color: var(--primary);
    }

    /* Table Component - Grid Row & Column Separated */
    .table-card {
        background: var(--bg-surface);
        border-radius: var(--radius-xl);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 32px;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .enterprise-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        text-align: left;
        border: 1px solid var(--border-color);
        background: var(--bg-surface);
    }

    .enterprise-table th {
        background: #f1f5f9;
        padding: 13px 16px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: var(--slate-dark);
        border-bottom: 1px solid var(--border-color);
        border-right: 1px solid var(--border-color);
        white-space: nowrap;
    }
    .enterprise-table th:last-child {
        border-right: none;
    }

    .enterprise-table td {
        padding: 14px 16px;
        font-size: 13px;
        color: var(--slate-dark);
        border-bottom: 1px solid var(--border-color);
        border-right: 1px solid var(--border-color);
        vertical-align: middle;
    }
    .enterprise-table td:last-child {
        border-right: none;
    }
    .enterprise-table tbody tr:last-child td {
        border-bottom: none;
    }

    .enterprise-table tr:hover td {
        background: #f8fafc;
    }

    .enterprise-table tr.selected-row td {
        background: var(--primary-light) !important;
    }

    /* Custom Checkbox Input */
    .custom-checkbox {
        width: 16px;
        height: 16px;
        accent-color: var(--primary);
        cursor: pointer;
        border-radius: 4px;
        vertical-align: middle;
    }

    /* Entries Selector Dropdown */
    .entries-selector {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        color: var(--slate-muted);
        font-weight: 600;
    }
    .entries-selector select {
        padding: 5px 10px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 12px;
        font-weight: 700;
        color: var(--slate-dark);
        background: #ffffff;
        outline: none;
        cursor: pointer;
    }

    /* Export Dropdown Component */
    .export-dropdown-wrap {
        position: relative;
        display: inline-block;
    }

    .export-dropdown-menu {
        position: absolute;
        top: calc(100% + 6px);
        right: 0;
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
        min-width: 170px;
        z-index: 100;
        display: none;
        padding: 6px 0;
    }
    .export-dropdown-menu.show {
        display: block;
        animation: fadeIn 0.15s ease;
    }
    .export-dropdown-menu a {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--slate-body);
        text-decoration: none;
        transition: background 0.15s;
    }
    .export-dropdown-menu a:hover {
        background: #f1f5f9;
        color: var(--slate-dark);
    }

    /* Floating Bulk Actions Bar */
    .bulk-actions-bar {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: #0f172a;
        color: #ffffff;
        padding: 12px 24px;
        border-radius: 40px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        gap: 16px;
        z-index: 999;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .bulk-actions-bar.active {
        transform: translateX(-50%) translateY(0);
    }

    /* Laravel Pagination Styling Reset & Custom Formatting */
    .pagination-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .pagination-wrap nav {
        display: flex;
        align-items: center;
    }
    .pagination-wrap nav > div:first-child {
        display: none !important;
    }
    .pagination-wrap nav > div:last-child {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .pagination-wrap nav span[aria-label], 
    .pagination-wrap nav a[aria-label] {
        display: none !important;
    }
    .pagination-wrap nav svg {
        width: 14px !important;
        height: 14px !important;
        max-width: 14px !important;
        max-height: 14px !important;
        flex-shrink: 0;
    }
    .pagination-wrap nav span.relative,
    .pagination-wrap nav a.relative {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 32px !important;
        height: 32px !important;
        padding: 0 10px !important;
        border-radius: var(--radius-md) !important;
        border: 1px solid var(--border-color) !important;
        background: #ffffff !important;
        color: var(--slate-dark) !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
        text-decoration: none !important;
        box-shadow: none !important;
        margin: 0 2px !important;
    }
    .pagination-wrap nav span[aria-current="page"] span.relative {
        background: var(--primary) !important;
        color: #ffffff !important;
        border-color: var(--primary) !important;
    }
    .pagination-wrap nav a.relative:hover {
        background: var(--primary-light) !important;
        color: var(--primary) !important;
        border-color: var(--primary-border) !important;
    }

    /* Badges */
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
    }

    .badge-available { background: var(--primary-light); color: var(--primary); border: 1px solid var(--primary-border); }
    .badge-busy { background: var(--warning-light); color: #d97706; border: 1px solid #fde68a; }
    .badge-onleave { background: var(--purple-light); color: var(--purple-accent); border: 1px solid #ddd6fe; }
    .badge-inactive { background: #f1f5f9; color: var(--slate-muted); border: 1px solid #cbd5e1; }

    .skill-tag {
        display: inline-block;
        background: var(--bg-subtle);
        color: var(--slate-body);
        border: 1px solid var(--border-color);
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        margin-right: 4px;
        margin-bottom: 4px;
    }

    /* Capacity Progress Bar */
    .capacity-bar-wrap {
        width: 100px;
        height: 6px;
        background: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        margin-top: 4px;
    }
    .capacity-bar-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.3s;
    }

    /* Slide-Over Drawer */
    .drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(4px);
        z-index: 1000;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    .drawer-overlay.active {
        opacity: 1;
        pointer-events: auto;
    }

    .drawer-panel {
        position: fixed;
        top: 0;
        right: 0;
        width: 520px;
        max-width: 90vw;
        height: 100vh;
        background: #ffffff;
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.15);
        z-index: 1001;
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
    }
    .drawer-overlay.active .drawer-panel {
        transform: translateX(0);
    }

    .drawer-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
    }

    .drawer-body {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
    }

    /* Modals */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(4px);
        z-index: 1100;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }
    .modal-overlay.active {
        opacity: 1;
        pointer-events: auto;
    }

    .modal-box {
        background: #ffffff;
        border-radius: var(--radius-xl);
        width: 600px;
        max-width: 92vw;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
        transform: translateY(20px);
        transition: transform 0.25s ease;
    }
    .modal-overlay.active .modal-box {
        transform: translateY(0);
    }

    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
    }

    .modal-body {
        padding: 24px;
    }

    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        background: #f8fafc;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }
    /* ==========================================================================
       DARK MODE ENGINE & COMPONENT OVERRIDES
       ========================================================================== */
    html[data-pms-theme="dark"],
    html[data-theme="dark"],
    html[data-bs-theme="dark"],
    html.dark,
    body.dark-mode,
    body.dark,
    .dark {
        --slate-dark: #f8fafc;
        --slate-body: #cbd5e1;
        --slate-muted: #94a3b8;
        --slate-subtle: #64748b;
        --bg-surface: #1e293b;
        --bg-subtle: #0f172a;
        --border-color: #334155;
        --primary-light: rgba(47, 107, 255, 0.25);
        --primary-border: #15803d;
        --blue-light: rgba(37, 99, 235, 0.25);
        --purple-light: rgba(124, 58, 237, 0.25);
        --warning-light: rgba(245, 158, 11, 0.25);
        --danger-light: rgba(239, 68, 68, 0.25);
    }

    html[data-pms-theme="dark"] .page-header-box,
    html[data-theme="dark"] .page-header-box,
    html[data-bs-theme="dark"] .page-header-box,
    html.dark .page-header-box,
    body.dark-mode .page-header-box,
    html[data-pms-theme="dark"] .kpi-card,
    html[data-theme="dark"] .kpi-card,
    html[data-bs-theme="dark"] .kpi-card,
    html.dark .kpi-card,
    body.dark-mode .kpi-card,
    html[data-pms-theme="dark"] .filter-card,
    html[data-theme="dark"] .filter-card,
    html[data-bs-theme="dark"] .filter-card,
    html.dark .filter-card,
    body.dark-mode .filter-card,
    html[data-pms-theme="dark"] .table-card,
    html[data-theme="dark"] .table-card,
    html[data-bs-theme="dark"] .table-card,
    html.dark .table-card,
    body.dark-mode .table-card {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
    }

    html[data-pms-theme="dark"] .table-card > div[style*="background"],
    html[data-theme="dark"] .table-card > div[style*="background"],
    html[data-bs-theme="dark"] .table-card > div[style*="background"],
    html.dark .table-card > div[style*="background"],
    body.dark-mode .table-card > div[style*="background"] {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    html[data-pms-theme="dark"] .enterprise-table,
    html[data-theme="dark"] .enterprise-table,
    html[data-bs-theme="dark"] .enterprise-table,
    html.dark .enterprise-table,
    body.dark-mode .enterprise-table {
        background: #1e293b !important;
        border-color: #334155 !important;
    }

    html[data-pms-theme="dark"] .enterprise-table th,
    html[data-theme="dark"] .enterprise-table th,
    html[data-bs-theme="dark"] .enterprise-table th,
    html.dark .enterprise-table th,
    body.dark-mode .enterprise-table th {
        background: #0f172a !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }

    html[data-pms-theme="dark"] .enterprise-table td,
    html[data-theme="dark"] .enterprise-table td,
    html[data-bs-theme="dark"] .enterprise-table td,
    html.dark .enterprise-table td,
    body.dark-mode .enterprise-table td {
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }

    html[data-pms-theme="dark"] .enterprise-table td strong,
    html[data-theme="dark"] .enterprise-table td strong,
    html[data-bs-theme="dark"] .enterprise-table td strong,
    html.dark .enterprise-table td strong,
    body.dark-mode .enterprise-table td strong {
        color: #f8fafc !important;
    }

    html[data-pms-theme="dark"] .enterprise-table tr:hover td,
    html[data-theme="dark"] .enterprise-table tr:hover td,
    html[data-bs-theme="dark"] .enterprise-table tr:hover td,
    html.dark .enterprise-table tr:hover td,
    body.dark-mode .enterprise-table tr:hover td {
        background: #334155 !important;
    }

    html[data-pms-theme="dark"] .enterprise-table select[name="status"],
    html[data-theme="dark"] .enterprise-table select[name="status"],
    html[data-bs-theme="dark"] .enterprise-table select[name="status"],
    html.dark .enterprise-table select[name="status"],
    body.dark-mode .enterprise-table select[name="status"] {
        background: #0f172a !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }

    html[data-pms-theme="dark"] .btn-action-secondary,
    html[data-theme="dark"] .btn-action-secondary,
    html[data-bs-theme="dark"] .btn-action-secondary,
    html.dark .btn-action-secondary,
    body.dark-mode .btn-action-secondary {
        background: #0f172a !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }

    html[data-pms-theme="dark"] .btn-action-secondary:hover,
    html[data-theme="dark"] .btn-action-secondary:hover,
    html[data-bs-theme="dark"] .btn-action-secondary:hover,
    html.dark .btn-action-secondary:hover,
    body.dark-mode .btn-action-secondary:hover {
        background: #334155 !important;
        color: #ffffff !important;
        border-color: #475569 !important;
    }

    html[data-pms-theme="dark"] .search-group input,
    html[data-theme="dark"] .search-group input,
    html[data-bs-theme="dark"] .search-group input,
    html.dark .search-group input,
    body.dark-mode .search-group input,
    html[data-pms-theme="dark"] .filter-selects select,
    html[data-theme="dark"] .filter-selects select,
    html[data-bs-theme="dark"] .filter-selects select,
    html.dark .filter-selects select,
    body.dark-mode .filter-selects select,
    html[data-pms-theme="dark"] .entries-selector select,
    html[data-theme="dark"] .entries-selector select,
    html[data-bs-theme="dark"] .entries-selector select,
    html.dark .entries-selector select,
    body.dark-mode .entries-selector select {
        background: #0f172a !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }

    html[data-pms-theme="dark"] .export-dropdown-menu,
    html[data-theme="dark"] .export-dropdown-menu,
    html[data-bs-theme="dark"] .export-dropdown-menu,
    html.dark .export-dropdown-menu,
    body.dark-mode .export-dropdown-menu {
        background: #1e293b !important;
        border-color: #334155 !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5) !important;
    }

    html[data-pms-theme="dark"] .export-dropdown-menu a,
    html[data-theme="dark"] .export-dropdown-menu a,
    html[data-bs-theme="dark"] .export-dropdown-menu a,
    html.dark .export-dropdown-menu a,
    body.dark-mode .export-dropdown-menu a {
        color: #cbd5e1 !important;
    }

    html[data-pms-theme="dark"] .export-dropdown-menu a:hover,
    html[data-theme="dark"] .export-dropdown-menu a:hover,
    html[data-bs-theme="dark"] .export-dropdown-menu a:hover,
    html.dark .export-dropdown-menu a:hover,
    body.dark-mode .export-dropdown-menu a:hover {
        background: #0f172a !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .pagination-wrap nav span.relative,
    html[data-pms-theme="dark"] .pagination-wrap nav a.relative,
    html[data-theme="dark"] .pagination-wrap nav span.relative,
    html[data-theme="dark"] .pagination-wrap nav a.relative,
    html[data-bs-theme="dark"] .pagination-wrap nav span.relative,
    html[data-bs-theme="dark"] .pagination-wrap nav a.relative,
    html.dark .pagination-wrap nav span.relative,
    html.dark .pagination-wrap nav a.relative,
    body.dark-mode .pagination-wrap nav span.relative,
    body.dark-mode .pagination-wrap nav a.relative {
        background: #0f172a !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }

    html[data-pms-theme="dark"] .pagination-wrap nav span[aria-current="page"] span.relative,
    html[data-theme="dark"] .pagination-wrap nav span[aria-current="page"] span.relative,
    html[data-bs-theme="dark"] .pagination-wrap nav span[aria-current="page"] span.relative,
    html.dark .pagination-wrap nav span[aria-current="page"] span.relative,
    body.dark-mode .pagination-wrap nav span[aria-current="page"] span.relative {
        background: var(--primary) !important;
        color: #ffffff !important;
        border-color: var(--primary) !important;
    }

    /* Drawer & Modal Dialogs */
    html[data-pms-theme="dark"] .drawer-panel,
    html[data-theme="dark"] .drawer-panel,
    html[data-bs-theme="dark"] .drawer-panel,
    html.dark .drawer-panel,
    body.dark-mode .drawer-panel,
    html[data-pms-theme="dark"] .modal-box,
    html[data-theme="dark"] .modal-box,
    html[data-bs-theme="dark"] .modal-box,
    html.dark .modal-box,
    body.dark-mode .modal-box {
        background: #1e293b !important;
        color: #f8fafc !important;
        border: 1px solid #334155 !important;
    }

    html[data-pms-theme="dark"] .drawer-header,
    html[data-theme="dark"] .drawer-header,
    html[data-bs-theme="dark"] .drawer-header,
    html.dark .drawer-header,
    body.dark-mode .drawer-header,
    html[data-pms-theme="dark"] .modal-header,
    html[data-theme="dark"] .modal-header,
    html[data-bs-theme="dark"] .modal-header,
    html.dark .modal-header,
    body.dark-mode .modal-header,
    html[data-pms-theme="dark"] .modal-footer,
    html[data-theme="dark"] .modal-footer,
    html[data-bs-theme="dark"] .modal-footer,
    html.dark .modal-footer,
    body.dark-mode .modal-footer {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    html[data-pms-theme="dark"] .modal-body,
    html[data-theme="dark"] .modal-body,
    html[data-bs-theme="dark"] .modal-body,
    html.dark .modal-body,
    body.dark-mode .modal-body,
    html[data-pms-theme="dark"] .drawer-body,
    html[data-theme="dark"] .drawer-body,
    html[data-bs-theme="dark"] .drawer-body,
    html.dark .drawer-body,
    body.dark-mode .drawer-body {
        background: #1e293b !important;
        color: #cbd5e1 !important;
    }

    html[data-pms-theme="dark"] .modal-body label,
    html[data-theme="dark"] .modal-body label,
    html[data-bs-theme="dark"] .modal-body label,
    html.dark .modal-body label,
    body.dark-mode .modal-body label,
    html[data-pms-theme="dark"] .drawer-body label,
    html[data-theme="dark"] .drawer-body label,
    html[data-bs-theme="dark"] .drawer-body label,
    html.dark .drawer-body label,
    body.dark-mode .drawer-body label {
        color: #cbd5e1 !important;
    }

    html[data-pms-theme="dark"] .modal-body input,
    html[data-theme="dark"] .modal-body input,
    html[data-bs-theme="dark"] .modal-body input,
    html.dark .modal-body input,
    body.dark-mode .modal-body input,
    html[data-pms-theme="dark"] .modal-body select,
    html[data-theme="dark"] .modal-body select,
    html[data-bs-theme="dark"] .modal-body select,
    html.dark .modal-body select,
    body.dark-mode .modal-body select,
    html[data-pms-theme="dark"] .modal-body textarea,
    html[data-theme="dark"] .modal-body textarea,
    html[data-bs-theme="dark"] .modal-body textarea,
    html.dark .modal-body textarea,
    body.dark-mode .modal-body textarea,
    html[data-pms-theme="dark"] .drawer-body input,
    html[data-theme="dark"] .drawer-body input,
    html[data-bs-theme="dark"] .drawer-body input,
    html.dark .drawer-body input,
    body.dark-mode .drawer-body input,
    html[data-pms-theme="dark"] .drawer-body select,
    html[data-theme="dark"] .drawer-body select,
    html[data-bs-theme="dark"] .drawer-body select,
    html.dark .drawer-body select,
    body.dark-mode .drawer-body select,
    html[data-pms-theme="dark"] .drawer-body textarea,
    html[data-theme="dark"] .drawer-body textarea,
    html[data-bs-theme="dark"] .drawer-body textarea,
    html.dark .drawer-body textarea,
    body.dark-mode .drawer-body textarea {
        background: #0f172a !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }

    /* Assign Task Modal Inner Containers */
    html[data-pms-theme="dark"] #assignWorkModal div[style*="background: #f0fdf4"],
    html[data-theme="dark"] #assignWorkModal div[style*="background: #f0fdf4"],
    html[data-bs-theme="dark"] #assignWorkModal div[style*="background: #f0fdf4"],
    html.dark #assignWorkModal div[style*="background: #f0fdf4"],
    body.dark-mode #assignWorkModal div[style*="background: #f0fdf4"] {
        background: rgba(16, 185, 129, 0.15) !important;
        border-color: rgba(16, 185, 129, 0.3) !important;
        color: #6ee7b7 !important;
    }

    html[data-pms-theme="dark"] #manualDevFields,
    html[data-theme="dark"] #manualDevFields,
    html[data-bs-theme="dark"] #manualDevFields,
    html.dark #manualDevFields,
    body.dark-mode #manualDevFields,
    html[data-pms-theme="dark"] #devLookupCard,
    html[data-theme="dark"] #devLookupCard,
    html[data-bs-theme="dark"] #devLookupCard,
    html.dark #devLookupCard,
    body.dark-mode #devLookupCard {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    html[data-pms-theme="dark"] #devLookupCard #lookupDevName,
    html[data-theme="dark"] #devLookupCard #lookupDevName,
    html[data-bs-theme="dark"] #devLookupCard #lookupDevName,
    html.dark #devLookupCard #lookupDevName,
    body.dark-mode #devLookupCard #lookupDevName {
        color: #f8fafc !important;
    }

    html[data-pms-theme="dark"] #devLookupCard #lookupDevRole,
    html[data-theme="dark"] #devLookupCard #lookupDevRole,
    html[data-bs-theme="dark"] #devLookupCard #lookupDevRole,
    html.dark #devLookupCard #lookupDevRole,
    body.dark-mode #devLookupCard #lookupDevRole {
        color: #94a3b8 !important;
    }

    html[data-pms-theme="dark"] #workloadAlertBanner,
    html[data-theme="dark"] #workloadAlertBanner,
    html[data-bs-theme="dark"] #workloadAlertBanner,
    html.dark #workloadAlertBanner,
    body.dark-mode #workloadAlertBanner {
        background: rgba(245, 158, 11, 0.15) !important;
        border-color: rgba(245, 158, 11, 0.3) !important;
        color: #fcd34d !important;
    }

    /* Developer Credentials Modal */
    html[data-pms-theme="dark"] #devCredentialsModal div[style*="background: #f8fafc"],
    html[data-pms-theme="dark"] #devCredentialsModal div[style*="background:#f8fafc"],
    html[data-theme="dark"] #devCredentialsModal div[style*="background: #f8fafc"],
    html[data-theme="dark"] #devCredentialsModal div[style*="background:#f8fafc"],
    html[data-bs-theme="dark"] #devCredentialsModal div[style*="background: #f8fafc"],
    html[data-bs-theme="dark"] #devCredentialsModal div[style*="background:#f8fafc"],
    html.dark #devCredentialsModal div[style*="background: #f8fafc"],
    html.dark #devCredentialsModal div[style*="background:#f8fafc"],
    body.dark-mode #devCredentialsModal div[style*="background: #f8fafc"],
    body.dark-mode #devCredentialsModal div[style*="background:#f8fafc"] {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    html[data-pms-theme="dark"] #credModalLoginEmail,
    html[data-theme="dark"] #credModalLoginEmail,
    html[data-bs-theme="dark"] #credModalLoginEmail,
    html.dark #credModalLoginEmail,
    body.dark-mode #credModalLoginEmail,
    html[data-pms-theme="dark"] #credModalPhone,
    html[data-theme="dark"] #credModalPhone,
    html[data-bs-theme="dark"] #credModalPhone,
    html.dark #credModalPhone,
    body.dark-mode #credModalPhone {
        color: #f8fafc !important;
    }

    html[data-pms-theme="dark"] #credModalLoginPasswordInput,
    html[data-theme="dark"] #credModalLoginPasswordInput,
    html[data-bs-theme="dark"] #credModalLoginPasswordInput,
    html.dark #credModalLoginPasswordInput,
    body.dark-mode #credModalLoginPasswordInput {
        background: #1e293b !important;
        color: #34d399 !important;
        border-color: #334155 !important;
    }

    html[data-pms-theme="dark"] #devCredentialsModal div[style*="border-top: 1px solid #e2e8f0"],
    html[data-theme="dark"] #devCredentialsModal div[style*="border-top: 1px solid #e2e8f0"],
    html[data-bs-theme="dark"] #devCredentialsModal div[style*="border-top: 1px solid #e2e8f0"],
    html.dark #devCredentialsModal div[style*="border-top: 1px solid #e2e8f0"],
    body.dark-mode #devCredentialsModal div[style*="border-top: 1px solid #e2e8f0"] {
        border-top-color: #334155 !important;
    }

    html[data-pms-theme="dark"] #devCredentialsModal div[style*="background: #eff6ff"],
    html[data-theme="dark"] #devCredentialsModal div[style*="background: #eff6ff"],
    html[data-bs-theme="dark"] #devCredentialsModal div[style*="background: #eff6ff"],
    html.dark #devCredentialsModal div[style*="background: #eff6ff"],
    body.dark-mode #devCredentialsModal div[style*="background: #eff6ff"] {
        background: rgba(37, 99, 235, 0.15) !important;
        border-color: rgba(37, 99, 235, 0.3) !important;
        color: #93c5fd !important;
    }

    html[data-pms-theme="dark"] #devCredentialsModal button[onclick*="copyTextToClipboard"],
    html[data-theme="dark"] #devCredentialsModal button[onclick*="copyTextToClipboard"],
    html[data-bs-theme="dark"] #devCredentialsModal button[onclick*="copyTextToClipboard"],
    html.dark #devCredentialsModal button[onclick*="copyTextToClipboard"],
    body.dark-mode #devCredentialsModal button[onclick*="copyTextToClipboard"],
    html[data-pms-theme="dark"] #credMailtoBtn,
    html[data-theme="dark"] #credMailtoBtn,
    html[data-bs-theme="dark"] #credMailtoBtn,
    html.dark #credMailtoBtn,
    body.dark-mode #credMailtoBtn {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #60a5fa !important;
    }

    /* Task History Modal Timeline */
    html[data-pms-theme="dark"] #taskHistoryModal div[style*="background: #f8fafc"],
    html[data-pms-theme="dark"] #taskHistoryModal div[style*="background:#f8fafc"],
    html[data-theme="dark"] #taskHistoryModal div[style*="background: #f8fafc"],
    html[data-theme="dark"] #taskHistoryModal div[style*="background:#f8fafc"],
    html[data-bs-theme="dark"] #taskHistoryModal div[style*="background: #f8fafc"],
    html[data-bs-theme="dark"] #taskHistoryModal div[style*="background:#f8fafc"],
    html.dark #taskHistoryModal div[style*="background: #f8fafc"],
    html.dark #taskHistoryModal div[style*="background:#f8fafc"],
    body.dark-mode #taskHistoryModal div[style*="background: #f8fafc"],
    body.dark-mode #taskHistoryModal div[style*="background:#f8fafc"] {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    html[data-pms-theme="dark"] #taskHistoryModal strong,
    html[data-theme="dark"] #taskHistoryModal strong,
    html[data-bs-theme="dark"] #taskHistoryModal strong,
    html.dark #taskHistoryModal strong,
    body.dark-mode #taskHistoryModal strong {
        color: #f8fafc !important;
    }

    html[data-pms-theme="dark"] #taskHistoryModal div[style*="background: #ecfdf5"],
    html[data-theme="dark"] #taskHistoryModal div[style*="background: #ecfdf5"],
    html[data-bs-theme="dark"] #taskHistoryModal div[style*="background: #ecfdf5"],
    html.dark #taskHistoryModal div[style*="background: #ecfdf5"],
    body.dark-mode #taskHistoryModal div[style*="background: #ecfdf5"] {
        background: rgba(16, 185, 129, 0.15) !important;
        border-color: rgba(16, 185, 129, 0.3) !important;
        color: #34d399 !important;
    }

    /* Developer Drawer Dynamic Tasks */
    html[data-pms-theme="dark"] #drawerDevAssignmentsList > div,
    html[data-theme="dark"] #drawerDevAssignmentsList > div,
    html[data-bs-theme="dark"] #drawerDevAssignmentsList > div,
    html.dark #drawerDevAssignmentsList > div,
    body.dark-mode #drawerDevAssignmentsList > div {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    html[data-pms-theme="dark"] #drawerDevAssignmentsList strong,
    html[data-theme="dark"] #drawerDevAssignmentsList strong,
    html[data-bs-theme="dark"] #drawerDevAssignmentsList strong,
    html.dark #drawerDevAssignmentsList strong,
    body.dark-mode #drawerDevAssignmentsList strong {
        color: #f8fafc !important;
    }

    html[data-pms-theme="dark"] .badge-inactive,
    html[data-theme="dark"] .badge-inactive,
    html[data-bs-theme="dark"] .badge-inactive,
    html.dark .badge-inactive,
    body.dark-mode .badge-inactive {
        background: #334155 !important;
        color: #cbd5e1 !important;
        border-color: #475569 !important;
    }

    html[data-pms-theme="dark"] .skill-tag,
    html[data-theme="dark"] .skill-tag,
    html[data-bs-theme="dark"] .skill-tag,
    html.dark .skill-tag,
    body.dark-mode .skill-tag {
        background: #0f172a !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }

    html[data-pms-theme="dark"] .capacity-bar-wrap,
    html[data-theme="dark"] .capacity-bar-wrap,
    html[data-bs-theme="dark"] .capacity-bar-wrap,
    html.dark .capacity-bar-wrap,
    body.dark-mode .capacity-bar-wrap {
        background: #334155 !important;
    }

    html[data-pms-theme="dark"] h1, html[data-pms-theme="dark"] h2, html[data-pms-theme="dark"] h3, html[data-pms-theme="dark"] h4, html[data-pms-theme="dark"] h5, html[data-pms-theme="dark"] h6,
    html[data-theme="dark"] h1, html[data-theme="dark"] h2, html[data-theme="dark"] h3, html[data-theme="dark"] h4, html[data-theme="dark"] h5, html[data-theme="dark"] h6,
    html[data-bs-theme="dark"] h1, html[data-bs-theme="dark"] h2, html[data-bs-theme="dark"] h3, html[data-bs-theme="dark"] h4, html[data-bs-theme="dark"] h5, html[data-bs-theme="dark"] h6,
    html.dark h1, html.dark h2, html.dark h3, html.dark h4, html.dark h5, html.dark h6,
    body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, body.dark-mode h4, body.dark-mode h5, body.dark-mode h6 {
        color: #f8fafc !important;
    }
</style>
@endpush

@section('content')
<div class="dev-container">

    <!-- PAGE HEADER -->
    <div class="page-header-box">
        <div>
            <div class="breadcrumbs-bar">
                <a href="{{ Route::has('superadmin.dashboard') ? route('superadmin.dashboard') : route('super-admin.companies.index') }}">Super Admin</a>
                <i class="bx bx-chevron-right"></i>
                <a href="#">Security &amp; Audit</a>
                <i class="bx bx-chevron-right"></i>
                <span>Developer Management</span>
            </div>
            <h1 class="page-title">Developer Management</h1>
            <p class="page-subtitle">Manage developers, workloads, assignments, and development activities across the platform.</p>
        </div>

        <div class="header-actions">
            <a href="{{ route('developer.dashboard') }}" target="_blank" class="btn-action-secondary" style="text-decoration: none;">
                <i class="bx bx-laptop" style="font-size: 16px; color: var(--primary);"></i> Developer Portal
            </a>
            <button class="btn-action-secondary" onclick="window.location.reload();">
                <i class="bx bx-refresh" style="font-size: 16px;"></i> Refresh
            </button>
            <button class="btn-action-secondary" onclick="openAssignModal();">
                <i class="bx bx-send" style="font-size: 16px; color: var(--blue-accent);"></i> Assign Work
            </button>
            <button class="btn-action-primary" onclick="openAddDeveloperModal();">
                <i class="bx bx-plus-circle" style="font-size: 18px;"></i> Add Developer
            </button>
        </div>
    </div>

    <!-- KPI SUMMARY CARDS -->
    <div class="kpi-grid">
        <!-- TOTAL DEVELOPERS -->
        <div class="kpi-card" onclick="filterByStatus('all')">
            <div class="kpi-header">
                <span class="kpi-title">Total Developers</span>
                <div class="kpi-icon" style="background: var(--blue-light); color: var(--blue-accent);">
                    <i class="bx bx-code-block"></i>
                </div>
            </div>
            <div class="kpi-val">{{ $kpis['total'] }}</div>
            <div class="kpi-sub"><i class="bx bx-user-check" style="color: var(--primary);"></i> Registered Talent</div>
        </div>

        <!-- ACTIVE DEVELOPERS -->
        <div class="kpi-card" onclick="filterByStatus('active')">
            <div class="kpi-header">
                <span class="kpi-title">Active Developers</span>
                <div class="kpi-icon" style="background: var(--primary-light); color: var(--primary);">
                    <i class="bx bx-user-check"></i>
                </div>
            </div>
            <div class="kpi-val">{{ $kpis['active'] }}</div>
            <div class="kpi-sub"><i class="bx bx-check-double" style="color: var(--primary);"></i> Account Active</div>
        </div>

        <!-- AVAILABLE -->
        <div class="kpi-card" onclick="filterByStatus('available')">
            <div class="kpi-header">
                <span class="kpi-title">Available</span>
                <div class="kpi-icon" style="background: #EEF2FF; color: #2F6BFF;">
                    <i class="bx bx-check-circle"></i>
                </div>
            </div>
            <div class="kpi-val">{{ $kpis['available'] }}</div>
            <div class="kpi-sub"><i class="bx bx-time" style="color: #2F6BFF;"></i> Ready for Tasks</div>
        </div>

        <!-- CURRENTLY WORKING / BUSY -->
        <div class="kpi-card" onclick="filterByStatus('busy')">
            <div class="kpi-header">
                <span class="kpi-title">Currently Working</span>
                <div class="kpi-icon" style="background: var(--warning-light); color: #d97706;">
                    <i class="bx bx-briefcase-alt-2"></i>
                </div>
            </div>
            <div class="kpi-val">{{ $kpis['busy'] }}</div>
            <div class="kpi-sub"><i class="bx bx-trending-up" style="color: #d97706;"></i> High Workload</div>
        </div>

        <!-- ACTIVE ASSIGNMENTS -->
        <div class="kpi-card" onclick="scrollToHistory()">
            <div class="kpi-header">
                <span class="kpi-title">Active Assignments</span>
                <div class="kpi-icon" style="background: var(--purple-light); color: var(--purple-accent);">
                    <i class="bx bx-task"></i>
                </div>
            </div>
            <div class="kpi-val">{{ $kpis['assignments'] }}</div>
            <div class="kpi-sub"><i class="bx bx-git-branch" style="color: var(--purple-accent);"></i> Across Platform</div>
        </div>

        <!-- OVERDUE TASKS -->
        <div class="kpi-card" onclick="scrollToHistory()">
            <div class="kpi-header">
                <span class="kpi-title">Overdue Tasks</span>
                <div class="kpi-icon" style="background: var(--danger-light); color: var(--danger);">
                    <i class="bx bx-time-five"></i>
                </div>
            </div>
            <div class="kpi-val" style="color: var(--danger);">{{ $kpis['overdue'] }}</div>
            <div class="kpi-sub"><i class="bx bx-error" style="color: var(--danger);"></i> Action Needed</div>
        </div>

        <!-- COMPLETED THIS MONTH -->
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Completed (Month)</span>
                <div class="kpi-icon" style="background: #f0fdf4; color: #16a34a;">
                    <i class="bx bx-trophy"></i>
                </div>
            </div>
            <div class="kpi-val">{{ $kpis['completed_month'] }}</div>
            <div class="kpi-sub"><i class="bx bx-check-shield" style="color: #16a34a;"></i> Delivered Tasks</div>
        </div>
    </div>

    <!-- SEARCH AND FILTERS TOOLBAR -->
    <div class="filter-card">
        <form method="GET" action="{{ route('super-admin.developers.index') }}" id="devFilterForm">
            <input type="hidden" name="per_page" id="perPageHiddenInput" value="{{ request('per_page', 10) }}">
            <div class="filter-row">
                <!-- SEARCH -->
                <div class="search-group">
                    <i class="bx bx-search"></i>
                    <input type="text" name="admin_search" id="devSearchInput" value="{{ request('admin_search', request('search')) }}" placeholder="Search developer name, email, skill, technology..." />
                </div>

                <!-- FILTERS -->
                <div class="filter-selects">
                    <!-- STATUS -->
                    <select name="status" onchange="document.getElementById('devFilterForm').submit();">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Status: All</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="busy" {{ request('status') == 'busy' ? 'selected' : '' }}>Busy</option>
                        <option value="on_leave" {{ request('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    <!-- ROLE -->
                    <select name="role" onchange="document.getElementById('devFilterForm').submit();">
                        <option value="all" {{ request('role') == 'all' ? 'selected' : '' }}>Role: All</option>
                        <option value="Frontend Developer" {{ request('role') == 'Frontend Developer' ? 'selected' : '' }}>Frontend Developer</option>
                        <option value="Backend Developer" {{ request('role') == 'Backend Developer' ? 'selected' : '' }}>Backend Developer</option>
                        <option value="Full Stack Developer" {{ request('role') == 'Full Stack Developer' ? 'selected' : '' }}>Full Stack Developer</option>
                        <option value="DevOps" {{ request('role') == 'DevOps' ? 'selected' : '' }}>DevOps</option>
                        <option value="QA Engineer" {{ request('role') == 'QA Engineer' ? 'selected' : '' }}>QA Engineer</option>
                        <option value="UI/UX Developer" {{ request('role') == 'UI/UX Developer' ? 'selected' : '' }}>UI/UX Developer</option>
                    </select>

                    <!-- WORKLOAD -->
                    <select name="workload" onchange="document.getElementById('devFilterForm').submit();">
                        <option value="all" {{ request('workload') == 'all' ? 'selected' : '' }}>Workload: All</option>
                        <option value="available" {{ request('workload') == 'available' ? 'selected' : '' }}>Available (0-15h)</option>
                        <option value="light" {{ request('workload') == 'light' ? 'selected' : '' }}>Light (1-2 Tasks)</option>
                        <option value="medium" {{ request('workload') == 'medium' ? 'selected' : '' }}>Medium (3-4 Tasks)</option>
                        <option value="heavy" {{ request('workload') == 'heavy' ? 'selected' : '' }}>Heavy (5+ Tasks)</option>
                    </select>

                    <a href="{{ route('super-admin.developers.index') }}" class="btn-action-secondary" style="padding: 9px 12px; font-size: 12px;">
                        <i class="bx bx-x"></i> Clear Filters
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- DEVELOPER DIRECTORY TABLE (ROW & COLUMN SEPARATED GRID) -->
    <div class="table-card">
        <div style="padding: 14px 20px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; background: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="bx bx-user-pin" style="font-size: 20px; color: var(--primary);"></i>
                    <h3 style="font-size: 15px; font-weight: 800; color: var(--slate-dark); margin: 0;">Developer Directory</h3>
                    <span style="background: var(--primary-light); color: var(--primary); padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">
                        {{ $paginatedDevs->total() }} Developers
                    </span>
                </div>

                <!-- SHOW ENTRIES FUNCTIONALITY -->
                <div class="entries-selector">
                    <span>Show</span>
                    <select id="devEntriesSelect" onchange="changeDevEntries(this.value)">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="all" {{ request('per_page') == 'all' || request('per_page') == 500 ? 'selected' : '' }}>All</option>
                    </select>
                    <span>entries</span>
                </div>
            </div>

            <!-- EXPORT DROPDOWN IN TOOLBAR -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="export-dropdown-wrap">
                    <button type="button" class="btn-action-secondary" onclick="toggleExportDropdown('devExportMenu', event)" style="padding: 7px 14px; font-size: 12.5px;">
                        <i class="bx bx-download" style="font-size: 16px; color: var(--primary);"></i> Export <i class="bx bx-chevron-down"></i>
                    </button>
                    <div id="devExportMenu" class="export-dropdown-menu">
                        <a href="javascript:void(0)" onclick="exportDevelopersCSV()"><i class="bx bx-file-blank" style="color: #2F6BFF;"></i> Export as CSV</a>
                        <a href="javascript:void(0)" onclick="exportDevelopersPDF()"><i class="bx bxs-file-pdf" style="color: #dc2626;"></i> Export as PDF</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="enterprise-table" id="developersGridTable">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="selectAllDevs" class="custom-checkbox" onchange="toggleAllDevCheckboxes(this)" title="Select All Rows">
                        </th>
                        <th>Developer</th>
                        <th>Role</th>
                        <th>Registered Email</th>
                        <th>Skills &amp; Tech</th>
                        <th>Status</th>
                        <th>Active Tasks</th>
                        <th>Workload Capacity</th>
                        <th>Last Active</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedDevs as $dev)
                    <tr class="dev-table-row" data-id="{{ $dev->id }}" data-email="{{ $dev->email }}" data-name="{{ $dev->name }}" data-role="{{ $dev->role_title }}" data-status="{{ $dev->dev_status }}">
                        <!-- CHECKBOX -->
                        <td style="width: 40px; text-align: center;">
                            <input type="checkbox" class="dev-row-cb custom-checkbox" value="{{ $dev->id }}" data-id="{{ $dev->id }}" data-name="{{ $dev->name }}" data-email="{{ $dev->email }}" data-role="{{ $dev->role_title }}" data-status="{{ $dev->dev_status }}" onchange="updateDevSelectionState()">
                        </td>

                        <!-- DEVELOPER -->
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #1e293b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0;">
                                    {{ strtoupper(substr($dev->name, 0, 2)) }}
                                </div>
                                <div>
                                    <strong style="color: var(--slate-dark); font-size: 14px; display: block;">{{ $dev->name }}</strong>
                                    <span style="font-size: 11.5px; color: var(--slate-muted);">DEV-{{ str_pad($dev->id, 3, '0', STR_PAD_LEFT) }} · {{ $dev->company ? $dev->company->name : 'Platform Central' }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- ROLE -->
                        <td>
                            <span style="font-weight: 700; color: var(--slate-body); font-size: 12.5px; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="bx bx-code-alt" style="color: var(--primary);"></i> {{ $dev->role_title }}
                            </span>
                        </td>

                        <!-- EMAIL -->
                        <td>
                            <strong style="color: var(--blue-accent); font-family: monospace; font-size: 12.5px;">{{ $dev->email }}</strong>
                        </td>

                        <!-- SKILLS -->
                        <td>
                            <div style="max-width: 220px;">
                                @foreach(array_slice($dev->skills_list, 0, 3) as $skill)
                                    <span class="skill-tag">{{ $skill }}</span>
                                @endforeach
                                @if(count($dev->skills_list) > 3)
                                    <span style="font-size: 10.5px; color: var(--slate-muted); font-weight: 600;">+{{ count($dev->skills_list) - 3 }} more</span>
                                @endif
                            </div>
                        </td>

                        <!-- STATUS -->
                        <td>
                            @if($dev->dev_status === 'Available')
                                <span class="badge-status badge-available"><i class="bx bx-check-circle"></i> Available</span>
                            @elseif($dev->dev_status === 'Busy')
                                <span class="badge-status badge-busy"><i class="bx bx-briefcase"></i> Busy</span>
                            @elseif($dev->dev_status === 'On Leave')
                                <span class="badge-status badge-onleave"><i class="bx bx-time"></i> On Leave</span>
                            @else
                                <span class="badge-status badge-inactive"><i class="bx bx-minus-circle"></i> Inactive</span>
                            @endif
                        </td>

                        <!-- ACTIVE TASKS -->
                        <td>
                            <span style="font-size: 12.5px; font-weight: 700; color: var(--slate-dark);">
                                {{ $dev->active_tasks_count }} Active Tasks
                            </span>
                        </td>

                        <!-- WORKLOAD CAPACITY -->
                        <td>
                            <div>
                                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; font-weight: 700; color: var(--slate-muted);">
                                    <span>{{ $dev->workload_category }}</span>
                                    <span>{{ $dev->estimate_hours_total }}h / 40h</span>
                                </div>
                                <div class="capacity-bar-wrap">
                                    <div class="capacity-bar-fill" style="width: {{ $dev->capacity_percentage }}%; background: {{ $dev->capacity_percentage > 85 ? 'var(--danger)' : ($dev->capacity_percentage > 60 ? 'var(--warning)' : 'var(--primary)') }};"></div>
                                </div>
                            </div>
                        </td>

                        <!-- LAST ACTIVE -->
                        <td>
                            <span style="font-size: 12px; color: var(--slate-muted);">
                                {{ $dev->updated_at ? $dev->updated_at->diffForHumans() : '5 mins ago' }}
                            </span>
                        </td>

                        <!-- ACTIONS -->
                        <td style="text-align: right;">
                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 5px;">
                                <form method="POST" action="{{ route('super-admin.developers.enter-workspace', $dev->id) }}" style="display: inline;" onsubmit="return confirmWorkspacePreview('{{ addslashes($dev->name) }}', event, this);">
                                    @csrf
                                    <button type="submit" class="btn-action-secondary" style="padding: 5px 10px; font-size: 11.5px; color: #6366f1; border-color: #c7d2fe;" title="Enter Workspace as Super Admin">
                                        <i class="bx bx-laptop"></i> Workspace
                                    </button>
                                </form>
                                <button class="btn-action-secondary" onclick="openDevDrawer('{{ $dev->id }}')" style="padding: 5px 10px; font-size: 11.5px;" title="View Details">
                                    <i class="bx bx-show"></i> View
                                </button>
                                <button class="btn-action-secondary" onclick="quickAssignWork('{{ $dev->email }}')" style="padding: 5px 8px; font-size: 12px; color: var(--blue-accent);" title="Assign Work">
                                    <i class="bx bx-send"></i>
                                </button>
                                <!-- WHATSAPP SHARE -->
                                <button class="btn-action-secondary" onclick="shareDevWhatsApp('{{ addslashes($dev->name) }}', '{{ $dev->email }}', '{{ $dev->personal_email ?: $dev->email }}', '{{ $dev->phone_number }}', 'DEV-{{ str_pad($dev->id, 3, '0', STR_PAD_LEFT) }}', '{{ $dev->active_tasks_count }}', '{{ addslashes($dev->raw_password ?: 'Developer@123') }}')" style="padding: 5px 8px; font-size: 14px; color: #2F6BFF; border-color: #C7D2FE; background: #EEF2FF;" title="Share Credentials & Tasks via WhatsApp">
                                    <i class="bx bxl-whatsapp"></i>
                                </button>
                                <!-- EMAIL CREDENTIALS & NOTIFICATION -->
                                <button class="btn-action-secondary" onclick="openDevCredentialsModal('{{ $dev->id }}', '{{ addslashes($dev->name) }}', '{{ $dev->email }}', '{{ $dev->personal_email ?: $dev->email }}', '{{ $dev->phone_number }}', 'DEV-{{ str_pad($dev->id, 3, '0', STR_PAD_LEFT) }}', '{{ $dev->active_tasks_count }}', '{{ addslashes($dev->raw_password ?: 'Developer@123') }}')" style="padding: 5px 8px; font-size: 14px; color: #2563eb; border-color: #bfdbfe; background: #eff6ff;" title="Developer Credentials & Email Notification">
                                    <i class="bx bx-envelope"></i>
                                </button>
                                <button class="btn-action-secondary" onclick="openEditDeveloperModal('{{ $dev->id }}')" style="padding: 5px 8px; font-size: 12px;" title="Edit Profile">
                                    <i class="bx bx-edit-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 48px 20px;">
                            <i class="bx bx-code-block" style="font-size: 48px; color: var(--slate-subtle); margin-bottom: 12px;"></i>
                            <h4 style="font-size: 16px; font-weight: 700; color: var(--slate-dark); margin: 0 0 6px 0;">No Developers Found</h4>
                            <p style="font-size: 13px; color: var(--slate-muted); margin: 0 0 16px 0;">Add developers to start assigning development work.</p>
                            <button class="btn-action-primary" onclick="openAddDeveloperModal();">
                                <i class="bx bx-plus-circle"></i> Add Developer
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <div style="padding: 14px 20px; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; background: #ffffff;">
            <div style="color: var(--slate-muted); font-size: 12.5px; font-weight: 600;">
                Showing {{ $paginatedDevs->firstItem() ?? 0 }} to {{ $paginatedDevs->lastItem() ?? 0 }} of {{ $paginatedDevs->total() }} developers
            </div>
            <div class="pagination-wrap">{{ $paginatedDevs->appends(request()->query())->onEachSide(1)->links() }}</div>
        </div>
    </div>

    <!-- ASSIGNMENT HISTORY SECTION (ROW & COLUMN SEPARATED GRID) -->
    <div class="table-card" id="assignmentHistorySection">
        <div style="padding: 14px 20px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; background: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="bx bx-history" style="font-size: 20px; color: var(--purple-accent);"></i>
                    <h3 style="font-size: 15px; font-weight: 800; color: var(--slate-dark); margin: 0;">Assignment History &amp; Task Telemetry</h3>
                </div>

                <!-- SHOW ENTRIES FUNCTIONALITY FOR HISTORY -->
                <div class="entries-selector">
                    <span>Show</span>
                    <select id="historyEntriesSelect" onchange="filterHistoryEntries(this.value)">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="all">All</option>
                    </select>
                    <span>entries</span>
                </div>
            </div>

            <!-- EXPORT DROPDOWN FOR HISTORY -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="export-dropdown-wrap">
                    <button type="button" class="btn-action-secondary" onclick="toggleExportDropdown('historyExportMenu', event)" style="padding: 7px 14px; font-size: 12.5px;">
                        <i class="bx bx-download" style="font-size: 16px; color: var(--purple-accent);"></i> Export <i class="bx bx-chevron-down"></i>
                    </button>
                    <div id="historyExportMenu" class="export-dropdown-menu">
                        <a href="javascript:void(0)" onclick="exportHistoryCSV()"><i class="bx bx-file-blank" style="color: #2F6BFF;"></i> Export as CSV</a>
                        <a href="javascript:void(0)" onclick="exportHistoryPDF()"><i class="bx bxs-file-pdf" style="color: #dc2626;"></i> Export as PDF</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="enterprise-table" id="assignmentsGridTable">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="selectAllHistory" class="custom-checkbox" onchange="toggleAllHistoryCheckboxes(this)" title="Select All History Rows">
                        </th>
                        <th>Task Title</th>
                        <th>Assigned Developer</th>
                        <th>Company</th>
                        <th>Assigned By</th>
                        <th>Date</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignmentHistory as $history)
                    <tr class="history-table-row" data-id="{{ $history->id }}" data-title="{{ $history->title }}" data-developer="{{ $history->developer_name }}" data-email="{{ $history->developer_email }}" data-company="{{ $history->company_name }}" data-priority="{{ $history->priority }}" data-status="{{ $history->status }}">
                        <!-- CHECKBOX -->
                        <td style="width: 40px; text-align: center;">
                            <input type="checkbox" class="history-row-cb custom-checkbox" value="{{ $history->id }}" data-id="{{ $history->id }}" data-title="{{ $history->title }}" data-developer="{{ $history->developer_name }}" data-email="{{ $history->developer_email }}" data-company="{{ $history->company_name }}" data-priority="{{ $history->priority }}" data-status="{{ $history->status }}" onchange="updateHistorySelectionState()">
                        </td>

                        <td>
                            <strong style="color: var(--slate-dark); font-size: 13px;">{{ $history->title }}</strong>
                            <span style="display: block; font-size: 11px; color: var(--slate-muted);">Est: {{ $history->estimate_hours ?? 8 }}h</span>
                        </td>
                        <td>
                            <strong style="color: var(--slate-dark); font-size: 12.5px;">{{ $history->developer_name ?: 'Developer' }}</strong>
                            <span style="display: block; font-size: 11px; color: var(--blue-accent); font-family: monospace;">{{ $history->developer_email }}</span>
                        </td>
                        <td>
                            <span style="font-size: 12.5px; font-weight: 600; color: var(--slate-body);">{{ $history->company_name ?: 'Platform Central' }}</span>
                        </td>
                        <td>
                            <span style="font-size: 12px; color: var(--slate-muted);">{{ $history->assigner_name ?: 'Super Admin' }}</span>
                        </td>
                        <td>
                            <span style="font-size: 12px; color: var(--slate-muted);">{{ \Carbon\Carbon::parse($history->created_at)->format('M d, Y') }}</span>
                        </td>
                        <td>
                            @php
                                $prio = strtolower($history->priority ?? 'medium');
                                $prioStyle = $prio === 'critical' ? 'background: #fef2f2; color: #ef4444; border: 1px solid #fecaca;' : ($prio === 'high' ? 'background: #fffbeb; color: #d97706; border: 1px solid #fde68a;' : 'background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0;');
                            @endphp
                            <span style="padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 800; text-transform: uppercase; {{ $prioStyle }}">
                                {{ $prio }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-status {{ $history->status === 'completed' ? 'badge-available' : ($history->status === 'in_progress' ? 'badge-busy' : 'badge-inactive') }}">
                                {{ ucfirst(str_replace('_', ' ', $history->status)) }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <form method="POST" action="{{ route('super-admin.developers.task-status', $history->id) }}" style="display: inline-block;">
                                @csrf
                                <select name="status" onchange="this.form.submit();" style="padding: 4px 8px; font-size: 11px; border-radius: 6px; border: 1px solid var(--border-color); cursor: pointer;">
                                    <option value="assigned" {{ $history->status === 'assigned' ? 'selected' : '' }}>Assigned</option>
                                    <option value="in_progress" {{ $history->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="on_hold" {{ $history->status === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                    <option value="completed" {{ $history->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $history->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 24px; color: var(--slate-muted);">No assignment history available.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- FLOATING BULK ACTIONS TOOLBAR -->
<div id="bulkActionsBar" class="bulk-actions-bar">
    <div style="font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 8px;">
        <span id="bulkSelectedCount" style="background: var(--primary); color: #ffffff; padding: 2px 8px; border-radius: 10px; font-size: 12px;">0 Selected</span>
        <span>Developer(s) Selected</span>
    </div>
    <div style="display: flex; align-items: center; gap: 8px;">
        <button type="button" class="btn-action-secondary" onclick="exportSelectedDevelopersCSV()" style="padding: 6px 12px; font-size: 12px; background: #1e293b; color: #ffffff; border-color: #334155;">
            <i class="bx bx-file-blank" style="color: #10b981;"></i> Export CSV
        </button>
        <button type="button" class="btn-action-secondary" onclick="exportSelectedDevelopersPDF()" style="padding: 6px 12px; font-size: 12px; background: #1e293b; color: #ffffff; border-color: #334155;">
            <i class="bx bxs-file-pdf" style="color: #ef4444;"></i> Export PDF
        </button>
        <button type="button" class="btn-action-secondary" onclick="assignWorkToSelected()" style="padding: 6px 12px; font-size: 12px; background: var(--primary); color: #ffffff; border: none;">
            <i class="bx bx-send"></i> Assign Work
        </button>
        <button type="button" onclick="clearDevSelection()" style="background: transparent; border: none; color: #94a3b8; cursor: pointer; font-size: 18px; margin-left: 4px;" title="Clear Selection">
            <i class="bx bx-x"></i>
        </button>
    </div>
</div>

<!-- SLIDE-OVER DEVELOPER PROFILE DRAWER -->
<div class="drawer-overlay" id="devProfileDrawer">
    <div class="drawer-panel">
        <div class="drawer-header">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: #1e293b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px;" id="drawerDevAvatar">
                    RD
                </div>
                <div>
                    <h3 style="font-size: 17px; font-weight: 800; color: var(--slate-dark); margin: 0;" id="drawerDevName">Rahul Sharma</h3>
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                        <span style="font-size: 12px; font-weight: 700; color: var(--primary);" id="drawerDevRole">Backend Developer</span>
                        <span style="font-size: 11px; color: var(--slate-muted);" id="drawerDevCompany">TechEdFest</span>
                    </div>
                </div>
            </div>
            <button onclick="closeDevDrawer()" style="background: none; border: none; font-size: 22px; color: var(--slate-muted); cursor: pointer;">
                <i class="bx bx-x"></i>
            </button>
        </div>

        <div class="drawer-body">
            <!-- REGISTERED EMAIL IDENTIFIER -->
            <div style="background: var(--blue-light); border: 1px solid #bfdbfe; border-radius: var(--radius-md); padding: 14px 16px; margin-bottom: 20px;">
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--blue-accent); margin-bottom: 4px;">Registered Email Address</div>
                <div style="font-family: monospace; font-size: 14px; font-weight: 700; color: var(--slate-dark);" id="drawerDevEmail">rahul@company.com</div>
                <div style="font-size: 11.5px; color: var(--slate-muted); margin-top: 4px;">Used by Super Admin for work allocation &amp; automated notification routing.</div>
            </div>

            <!-- WORKLOAD METRICS -->
            <div style="background: var(--bg-subtle); border-radius: var(--radius-md); padding: 16px; margin-bottom: 20px; border: 1px solid var(--border-color);">
                <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--slate-muted); margin-bottom: 10px;">Capacity &amp; Workload</div>
                <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; margin-bottom: 6px;">
                    <span id="drawerDevWorkloadCategory">Light Workload</span>
                    <span id="drawerDevHours">16h / 40h</span>
                </div>
                <div class="capacity-bar-wrap" style="width: 100%; height: 8px;">
                    <div class="capacity-bar-fill" id="drawerCapacityBar" style="width: 40%; background: var(--primary);"></div>
                </div>
            </div>

            <!-- TECHNICAL SKILLS -->
            <div style="margin-bottom: 20px;">
                <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--slate-muted); margin-bottom: 8px;">Technical Stack &amp; Skills</div>
                <div id="drawerDevSkillsContainer"></div>
            </div>

            <!-- CURRENT ACTIVE ASSIGNMENTS -->
            <div style="margin-bottom: 20px;">
                <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--slate-muted); margin-bottom: 8px;">Active Work Assignments</div>
                <div id="drawerDevAssignmentsList"></div>
            </div>

            <!-- ACTION BUTTONS IN DRAWER -->
            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 30px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                <button class="btn-action-primary" id="drawerAssignWorkBtn" onclick="closeDevDrawer(); openAssignModal();" style="justify-content: center;">
                    <i class="bx bx-send"></i> Assign Work to This Developer
                </button>
                <button class="btn-action-secondary" id="drawerEditBtn" style="justify-content: center;">
                    <i class="bx bx-edit"></i> Edit Profile &amp; Skills
                </button>
                <form id="drawerStatusForm" method="POST" action="">
                    @csrf
                    <button type="submit" class="btn-action-secondary" style="width: 100%; justify-content: center; color: var(--danger); border-color: #fecaca;">
                        <i class="bx bx-power-off"></i> Toggle Account Access
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 1: ADD / EDIT DEVELOPER MODAL -->
<div class="modal-overlay" id="developerModal">
    <div class="modal-box" style="width: 650px;">
        <form id="devForm" method="POST" action="{{ route('super-admin.developers.store') }}">
            @csrf
            <input type="hidden" name="_method" id="devFormMethod" value="POST">

            <div class="modal-header">
                <div>
                    <h3 style="font-size: 17px; font-weight: 800; color: var(--slate-dark); margin: 0;" id="devModalTitle">Add New Developer</h3>
                    <p style="font-size: 12px; color: var(--slate-muted); margin: 2px 0 0 0;" id="devModalSubTitle">Create a permanent developer account (Only ONCE per developer).</p>
                </div>
                <button type="button" onclick="closeDeveloperModal()" style="background: none; border: none; font-size: 22px; color: var(--slate-muted); cursor: pointer;">
                    <i class="bx bx-x"></i>
                </button>
            </div>

            <div class="modal-body">

                <!-- NOTICE BANNER -->
                <div id="devNoticeBanner" style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: var(--radius-md); padding: 12px 14px; margin-bottom: 18px; color: #1e40af; font-size: 12px; display: flex; align-items: flex-start; gap: 10px;">
                    <i class="bx bx-info-circle" style="font-size: 18px; color: #2563eb; flex-shrink: 0; margin-top: 1px;"></i>
                    <div>
                        <strong style="display: block; font-size: 12.5px; margin-bottom: 2px;">Permanent Account Architecture:</strong>
                        <span>Creating a developer generates <strong>ONE permanent account</strong> and dispatches initial login credentials to their personal email address. Future task assignments will automatically reuse this account without generating new passwords.</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Full Name *</label>
                        <input type="text" name="name" id="devFormName" required style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;" placeholder="e.g. Siraj Ali Laskar">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Developer Login Email *</label>
                        <input type="email" name="email" id="devFormEmail" required style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;" placeholder="e.g. developer@example.com">
                        <div id="devFormEmail_error" class="field-error-feedback"></div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Personal Email (for Credentials) *</label>
                        <input type="email" name="personal_email" id="devFormPersonalEmail" required style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;" placeholder="e.g. siraj.personal@gmail.com">
                        <div id="devFormPersonalEmail_error" class="field-error-feedback"></div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Phone Number</label>
                        <div style="display: flex;">
                            <select id="devFormCountryCode" class="country-code-select" style="width: 110px; flex-shrink: 0; padding: 9px; border: 1px solid var(--border-color); border-right: 0; border-top-left-radius: var(--radius-md); border-bottom-left-radius: var(--radius-md); font-size: 13px; outline: none;">
                                @foreach(\App\Models\Country::getAllWithPhoneCodes() as $c)
                                    <option value="{{ $c->phone_code }}">
                                        {{ $c->iso_code ? $c->iso_code . ' ' : '' }}({{ $c->phone_code }})
                                    </option>
                                @endforeach
                            </select>
                            <input type="text" id="devFormMobileDisplay" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-top-right-radius: var(--radius-md); border-bottom-right-radius: var(--radius-md); font-size: 13px;" placeholder="98765 43210">
                            <input type="hidden" name="mobile" id="devFormMobile">
                        </div>
                        <div id="devFormMobile_error" class="field-error-feedback"></div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Designation / Role *</label>
                        <select name="role" id="devFormRole" required style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;">
                            <option value="Full Stack Developer">Full Stack Developer</option>
                            <option value="Backend Developer">Backend Developer</option>
                            <option value="Frontend Developer">Frontend Developer</option>
                            <option value="DevOps Engineer">DevOps Engineer</option>
                            <option value="QA Automation Engineer">QA Automation Engineer</option>
                            <option value="UI/UX Developer">UI/UX Developer</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Experience Level</label>
                        <input type="text" name="experience" id="devFormExperience" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;" placeholder="e.g. 3+ Years">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Assign to Company</label>
                        <select name="company_id" id="devFormCompany" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;">
                            <option value="">-- Platform Central Developer --</option>
                            @foreach($companyOptions as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->company_code ?? 'MAIN' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Joining Date</label>
                        <input type="date" name="joining_date" id="devFormJoiningDate" value="{{ date('Y-m-d') }}" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;">
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Skills &amp; Technology Stack (Comma Separated)</label>
                    <input type="text" name="skills" id="devFormSkills" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;" placeholder="e.g. PHP, Laravel, React, Node.js, Docker, MySQL">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-action-secondary" onclick="closeDeveloperModal()">Cancel</button>
                <button type="submit" class="btn-action-primary" id="devFormSubmitBtn">
                    <i class="bx bx-user-check"></i> Create Developer Account
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: ASSIGN TASK WORK ALLOCATION MODAL -->
<div class="modal-overlay" id="assignWorkModal">
    <div class="modal-box" style="width: 720px;">
        <form id="assignWorkForm" method="POST" action="{{ route('super-admin.developers.assign-work') }}">
            @csrf
            <div class="modal-header">
                <div>
                    <h3 style="font-size: 17px; font-weight: 800; color: var(--slate-dark); margin: 0;">Assign Development Work</h3>
                    <p style="font-size: 12px; color: var(--slate-muted); margin: 2px 0 0 0;">Assign task to an existing developer or auto-provision a new developer account.</p>
                </div>
                <button type="button" onclick="closeAssignModal()" style="background: none; border: none; font-size: 22px; color: var(--slate-muted); cursor: pointer;">
                    <i class="bx bx-x"></i>
                </button>
            </div>

            <div class="modal-body">

                <!-- RULE EXPLANATION BANNER -->
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius-md); padding: 12px 14px; margin-bottom: 16px; color: #166534; font-size: 12px; display: flex; align-items: center; gap: 10px;">
                    <i class="bx bx-shield-check" style="font-size: 20px; color: #16a34a; flex-shrink: 0;"></i>
                    <span><strong>1 Developer = 1 Account Rule:</strong> If the selected developer already exists, the task will be assigned to their existing account. No new account, password, or login credentials email will be generated.</span>
                </div>

                <!-- DEVELOPER SELECTOR DROPDOWN -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Select Developer *</label>
                    <select name="developer_id" id="assignDevSelect" onchange="onDevSelectChange(this)" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13.5px; font-weight: 600; color: var(--slate-dark);">
                        <option value="">-- Choose Existing Developer --</option>
                        @foreach($paginatedDevs as $d)
                            <option value="{{ $d->id }}" data-email="{{ $d->email }}" data-name="{{ $d->name }}" data-role="{{ $d->role_title }}" data-tasks="{{ $d->active_tasks_count }}" data-workload="{{ $d->workload_category }}">
                                {{ $d->name }} ({{ $d->role_title }}) — {{ $d->dev_status }} · {{ $d->active_tasks_count }} Active Tasks [{{ $d->email }}]
                            </option>
                        @endforeach
                        <option value="new">+ Enter New Developer Email...</option>
                    </select>
                </div>

                <!-- MANUAL EMAIL & NAME INPUTS (IF NEW DEVELOPER CHOSEN OR DIRECT LOOKUP) -->
                <div id="manualDevFields" style="display: none; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 16px; background: #f8fafc; padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 700; color: var(--slate-dark); margin-bottom: 4px;">Developer Email *</label>
                        <input type="email" name="developer_email" id="assignDevEmailInput" onkeyup="lookupDevEmail(this.value)" style="width: 100%; padding: 8px 10px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 12.5px; font-family: monospace;" placeholder="developer@example.com">
                        <div id="assignDevEmail_error" class="field-error-feedback"></div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 700; color: var(--slate-dark); margin-bottom: 4px;">Full Name</label>
                        <input type="text" name="developer_name" id="assignDevNameInput" style="width: 100%; padding: 8px 10px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 12.5px;" placeholder="Full Name">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 700; color: var(--slate-dark); margin-bottom: 4px;">Designation</label>
                        <input type="text" name="designation" id="assignDevRoleInput" style="width: 100%; padding: 8px 10px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 12.5px;" placeholder="Backend Developer">
                    </div>
                </div>

                <!-- LIVE DEVELOPER LOOKUP CARD -->
                <div id="devLookupCard" style="display: none; background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <strong style="font-size: 14px; color: var(--slate-dark);" id="lookupDevName">Rahul Sharma</strong>
                            <span style="font-size: 12px; color: var(--slate-muted); display: block;" id="lookupDevRole">Backend Developer</span>
                        </div>
                        <span style="background: var(--primary-light); color: var(--primary); padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 800;" id="lookupDevWorkloadBadge">Available</span>
                    </div>

                    <div id="workloadAlertBanner" style="display: none; margin-top: 10px; padding: 10px 12px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; color: #b45309; font-size: 12px; font-weight: 600;">
                        <i class="bx bx-error" style="font-size: 16px; vertical-align: middle;"></i> ⚠️ Developer workload is currently heavy. Consider assigning to another available developer.
                    </div>
                </div>

                <!-- TASK TITLE -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Task Title *</label>
                    <input type="text" name="task_title" required style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;" placeholder="e.g. Implement Subscription Expiry Notifications">
                </div>

                <!-- DESCRIPTION -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Detailed Description</label>
                    <textarea name="description" rows="3" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;" placeholder="Describe technical scope and acceptance criteria..."></textarea>
                </div>

                <!-- ADDITIONAL INSTRUCTIONS & ATTACHMENTS -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Additional Instructions</label>
                        <input type="text" name="additional_instructions" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;" placeholder="Special guidelines, repo links, branch names...">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Attachments / Docs Link</label>
                        <input type="text" name="attachments" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;" placeholder="e.g. Figma link, API doc URL, PDF path">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Company</label>
                        <select name="company_id" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;">
                            @foreach($companyOptions as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Project</label>
                        <select name="project_id" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;">
                            @foreach($projectOptions as $p)
                                <option value="{{ $p->id }}">{{ $p->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Priority *</label>
                        <select name="priority" required style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;">
                            <option value="critical">Critical</option>
                            <option value="high">High</option>
                            <option value="medium" selected>Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Estimate (Hours)</label>
                        <input type="number" name="estimate_hours" value="8" min="1" max="200" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Start Date</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Deadline *</label>
                        <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+3 days')) }}" required style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;">
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-action-secondary" onclick="closeAssignModal()">Cancel</button>
                <button type="submit" class="btn-action-primary">
                    <i class="bx bx-send"></i> Assign Task
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: TASK ACTIVITY TIMELINE HISTORY MODAL -->
<div class="modal-overlay" id="taskHistoryModal">
    <div class="modal-box" style="width: 650px;">
        <div class="modal-header">
            <div>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--slate-dark); margin: 0;" id="historyModalTaskTitle">Task Activity Timeline</h3>
                <p style="font-size: 12px; color: var(--slate-muted); margin: 2px 0 0 0;" id="historyModalTaskSub">Audit trail &amp; status change history</p>
            </div>
            <button type="button" onclick="closeTaskHistoryModal()" style="background: none; border: none; font-size: 22px; color: var(--slate-muted); cursor: pointer;">
                <i class="bx bx-x"></i>
            </button>
        </div>
        <div class="modal-body" id="taskHistoryTimelineBody">
            <div style="text-align: center; padding: 24px; color: var(--slate-muted);">Loading activity history...</div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-action-secondary" onclick="closeTaskHistoryModal()">Close</button>
        </div>
    </div>
</div>

<!-- MODAL 4: DEVELOPER CREDENTIALS & SHARE MODAL -->
<div class="modal-overlay" id="devCredentialsModal">
    <div class="modal-box" style="width: 620px;">
        <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid var(--border-color);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; flex-shrink: 0;">
                    <i class="bx bx-key"></i>
                </div>
                <div>
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--slate-dark); margin: 0;" id="credModalDevName">Developer Credentials</h3>
                    <p style="font-size: 11.5px; color: var(--slate-muted); margin: 2px 0 0 0;" id="credModalDevId">Account Credentials &amp; Contact Telemetry</p>
                </div>
            </div>
            <button type="button" onclick="closeDevCredentialsModal()" style="background: none; border: none; font-size: 22px; color: var(--slate-muted); cursor: pointer;">
                <i class="bx bx-x"></i>
            </button>
        </div>

        <div class="modal-body" style="padding: 20px;">
            <!-- CREDENTIAL SUMMARY CARD -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 16px; margin-bottom: 18px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; font-size: 12.5px; margin-bottom: 14px;">
                    <div>
                        <span style="color: var(--slate-muted); font-weight: 700; display: block; font-size: 11px; margin-bottom: 2px;">DEVELOPER LOGIN EMAIL</span>
                        <strong style="color: var(--slate-dark); font-family: monospace; font-size: 13px;" id="credModalLoginEmail">-</strong>
                    </div>
                    <div>
                        <span style="color: var(--slate-muted); font-weight: 700; display: block; font-size: 11px; margin-bottom: 2px;">DEVELOPER LOGIN PASSWORD</span>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <input type="password" id="credModalLoginPasswordInput" readonly value="Developer@123" style="font-family: monospace; font-size: 13px; font-weight: 700; color: #2F6BFF; border: 1px solid #cbd5e1; border-radius: 6px; padding: 3px 8px; width: 135px; background: #ffffff;">
                            <button type="button" onclick="togglePasswordVisibility('credModalLoginPasswordInput', this)" class="btn-action-secondary" style="padding: 3px 6px; font-size: 13px; border-color: #cbd5e1;" title="Show/Hide Password">
                                <i class="bx bx-show"></i>
                            </button>
                            <button type="button" onclick="copyTextToClipboard(document.getElementById('credModalLoginPasswordInput').value, 'Password copied to clipboard!')" class="btn-action-secondary" style="padding: 3px 6px; font-size: 13px; border-color: #cbd5e1;" title="Copy Password">
                                <i class="bx bx-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <span style="color: var(--slate-muted); font-weight: 700; display: block; font-size: 11px; margin-bottom: 2px;">PERSONAL EMAIL (CREDENTIAL INBOX)</span>
                        <strong style="color: var(--blue-accent); font-family: monospace; font-size: 13px;" id="credModalPersonalEmail">-</strong>
                    </div>
                    <div>
                        <span style="color: var(--slate-muted); font-weight: 700; display: block; font-size: 11px; margin-bottom: 2px;">PHONE / WHATSAPP NUMBER</span>
                        <strong style="color: var(--slate-dark); font-size: 12.5px;" id="credModalPhone">-</strong>
                    </div>
                </div>

                <div style="border-top: 1px solid #e2e8f0; padding-top: 10px; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <span style="color: var(--slate-muted); font-weight: 700; font-size: 11px;">ACTIVE ASSIGNED TASKS:</span>
                        <span style="background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; margin-left: 4px;" id="credModalActiveTasks">0 Tasks</span>
                    </div>
                    <div>
                        <span style="color: var(--slate-muted); font-weight: 700; font-size: 11px;">PASSWORD SYNC:</span>
                        <span style="background: #EEF2FF; color: #065f46; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; margin-left: 4px;">Live Auto-Updated</span>
                    </div>
                </div>
            </div>

            <!-- LOGIN PORTAL LINK INFO -->
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: var(--radius-md); padding: 12px 14px; margin-bottom: 18px; font-size: 12px; color: #1e40af;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                    <div>
                        <strong style="display: block; font-size: 12px; margin-bottom: 2px;">Developer Portal Login URL:</strong>
                        <span style="font-family: monospace; font-weight: 700;">{{ url('/login') }}</span>
                    </div>
                    <button type="button" onclick="copyTextToClipboard('{{ url('/login') }}', 'Login URL copied to clipboard!')" class="btn-action-secondary" style="padding: 4px 10px; font-size: 11px; background: #ffffff; color: #2563eb; border-color: #bfdbfe;">
                        <i class="bx bx-copy"></i> Copy Link
                    </button>
                </div>
            </div>

            <!-- ACTION OPTIONS -->
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <form id="credResetForm" method="POST" action="">
                    @csrf
                    <div style="margin-bottom: 12px; text-align: left;">
                        <label style="font-size: 11.5px; font-weight: 700; color: var(--slate-dark); display: block; margin-bottom: 4px;">
                            New Custom Password <span style="font-weight: normal; color: var(--slate-muted);">(Optional - leave blank to auto-generate 10-char password)</span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" name="password" id="devResetPasswordInput" minlength="8" maxlength="128" placeholder="Enter custom password (min 8 chars)" style="width: 100%; padding: 8px 36px 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: monospace; outline: none; background: #ffffff;">
                            <button type="button" onclick="togglePasswordVisibility('devResetPasswordInput', this)" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--slate-muted); font-size: 18px;" title="Show/Hide Password">
                                <i class="bx bx-show"></i>
                            </button>
                        </div>
                        <div style="font-size: 11px; color: var(--slate-muted); margin-top: 3px;">If provided, must be 8–128 characters.</div>
                        <div id="devResetPassword_error" style="display: none; color: #ef4444; font-size: 12px; margin-top: 3px; font-weight: 600;"></div>
                    </div>
                    <button type="submit" class="btn-action-primary" style="width: 100%; padding: 10px; justify-content: center; font-size: 13px;">
                        <i class="bx bx-paper-plane"></i> Reset Password &amp; Dispatch Credentials Email
                    </button>
                </form>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <button type="button" id="credMailtoBtn" class="btn-action-secondary" style="padding: 9px; justify-content: center; font-size: 12.5px; color: #2563eb; border-color: #bfdbfe; background: #f0f9ff;">
                        <i class="bx bx-envelope"></i> Open Mail Client
                    </button>
                    <button type="button" id="credWhatsappBtn" class="btn-action-secondary" style="padding: 9px; justify-content: center; font-size: 12.5px; color: #2F6BFF; border-color: #C7D2FE; background: #EEF2FF;">
                        <i class="bx bxl-whatsapp"></i> Share on WhatsApp
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-action-secondary" onclick="closeDevCredentialsModal()">Close</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const devData = @json($paginatedDevs->items());

    // Toggle Export Dropdown Menu
    function toggleExportDropdown(menuId, event) {
        if (event) event.stopPropagation();
        const targetMenu = document.getElementById(menuId);
        document.querySelectorAll('.export-dropdown-menu').forEach(menu => {
            if (menu.id !== menuId) menu.classList.remove('show');
        });
        if (targetMenu) {
            targetMenu.classList.toggle('show');
        }
    }

    // Close dropdowns on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.export-dropdown-wrap')) {
            document.querySelectorAll('.export-dropdown-menu').forEach(m => m.classList.remove('show'));
        }
    });

    // Change Entries for Developer Directory (submits form with per_page)
    function changeDevEntries(val) {
        const input = document.getElementById('perPageHiddenInput');
        if (input) input.value = val;
        const form = document.getElementById('devFilterForm');
        if (form) form.submit();
    }

    // Filter History Entries dynamically (client-side row limit)
    function filterHistoryEntries(val) {
        const historyRows = document.querySelectorAll('#assignmentsGridTable tbody tr.history-table-row');
        if (!historyRows.length) return;
        const limit = (val === 'all') ? historyRows.length : parseInt(val, 10);
        historyRows.forEach((row, idx) => {
            if (idx < limit) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Checkbox select all & update state for Developers
    function toggleAllDevCheckboxes(masterCb) {
        const devCbs = document.querySelectorAll('.dev-row-cb');
        devCbs.forEach(cb => {
            cb.checked = masterCb.checked;
            const tr = cb.closest('tr');
            if (tr) {
                if (cb.checked) tr.classList.add('selected-row');
                else tr.classList.remove('selected-row');
            }
        });
        updateDevSelectionState();
    }

    function updateDevSelectionState() {
        const selectedCbs = document.querySelectorAll('.dev-row-cb:checked');
        const allCbs = document.querySelectorAll('.dev-row-cb');
        const masterCb = document.getElementById('selectAllDevs');
        if (masterCb && allCbs.length > 0) {
            masterCb.checked = (selectedCbs.length === allCbs.length);
        }

        // Highlight rows
        document.querySelectorAll('.dev-row-cb').forEach(cb => {
            const tr = cb.closest('tr');
            if (tr) {
                if (cb.checked) tr.classList.add('selected-row');
                else tr.classList.remove('selected-row');
            }
        });

        const bulkBar = document.getElementById('bulkActionsBar');
        const countSpan = document.getElementById('bulkSelectedCount');
        if (bulkBar && countSpan) {
            if (selectedCbs.length > 0) {
                countSpan.textContent = selectedCbs.length + ' Selected';
                bulkBar.classList.add('active');
            } else {
                bulkBar.classList.remove('active');
            }
        }
    }

    function clearDevSelection() {
        document.querySelectorAll('.dev-row-cb, #selectAllDevs').forEach(cb => cb.checked = false);
        document.querySelectorAll('.dev-table-row').forEach(tr => tr.classList.remove('selected-row'));
        const bulkBar = document.getElementById('bulkActionsBar');
        if (bulkBar) bulkBar.classList.remove('active');
    }

    function confirmWorkspacePreview(devName, e, form) {
        if (e) e.preventDefault();
        if (confirm("Enter Developer Workspace?\n\nYou are about to access " + devName + "'s developer workspace as Super Admin in Preview Mode.\n\nContinue?")) {
            form.submit();
        }
        return false;
    }

    // Checkbox select all & update state for History
    function toggleAllHistoryCheckboxes(masterCb) {
        const historyCbs = document.querySelectorAll('.history-row-cb');
        historyCbs.forEach(cb => {
            cb.checked = masterCb.checked;
            const tr = cb.closest('tr');
            if (tr) {
                if (cb.checked) tr.classList.add('selected-row');
                else tr.classList.remove('selected-row');
            }
        });
    }

    function updateHistorySelectionState() {
        const selectedCbs = document.querySelectorAll('.history-row-cb:checked');
        const allCbs = document.querySelectorAll('.history-row-cb');
        const masterCb = document.getElementById('selectAllHistory');
        if (masterCb && allCbs.length > 0) {
            masterCb.checked = (selectedCbs.length === allCbs.length);
        }
        document.querySelectorAll('.history-row-cb').forEach(cb => {
            const tr = cb.closest('tr');
            if (tr) {
                if (cb.checked) tr.classList.add('selected-row');
                else tr.classList.remove('selected-row');
            }
        });
    }

    // CSV Download Helper
    function generateCSVDownload(filename, headers, rowsData) {
        let csvContent = "\uFEFF"; // UTF-8 BOM
        csvContent += headers.map(h => `"${h.replace(/"/g, '""')}"`).join(",") + "\n";
        rowsData.forEach(row => {
            csvContent += row.map(cell => `"${(cell || '').toString().replace(/"/g, '""')}"`).join(",") + "\n";
        });

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Export Developers as CSV
    function exportDevelopersCSV(onlySelected = false) {
        let targetRows = Array.from(document.querySelectorAll('#developersGridTable tbody tr.dev-table-row'));
        if (onlySelected) {
            targetRows = targetRows.filter(tr => tr.querySelector('.dev-row-cb')?.checked);
        }
        if (!targetRows.length) {
            alert('No developer records available to export.');
            return;
        }

        const headers = ['Developer Name', 'Role / Designation', 'Registered Email', 'Status', 'Active Tasks', 'Workload Capacity', 'Last Active'];
        const rowsData = targetRows.map(tr => {
            const name = tr.querySelector('strong')?.innerText?.trim() || '';
            const role = tr.querySelector('td:nth-child(3)')?.innerText?.trim() || '';
            const email = tr.getAttribute('data-email') || tr.querySelector('td:nth-child(4)')?.innerText?.trim() || '';
            const status = tr.getAttribute('data-status') || tr.querySelector('.badge-status')?.innerText?.trim() || '';
            const activeTasks = tr.querySelector('td:nth-child(7)')?.innerText?.trim() || '0';
            const workload = tr.querySelector('td:nth-child(8)')?.innerText?.replace(/\s+/g, ' ').trim() || '';
            const lastActive = tr.querySelector('td:nth-child(9)')?.innerText?.trim() || '';
            return [name, role, email, status, activeTasks, workload, lastActive];
        });

        const dateStr = new Date().toISOString().slice(0, 10);
        generateCSVDownload(`developers_export_${dateStr}.csv`, headers, rowsData);
    }

    function exportSelectedDevelopersCSV() {
        exportDevelopersCSV(true);
    }

    // Export History as CSV
    function exportHistoryCSV(onlySelected = false) {
        let targetRows = Array.from(document.querySelectorAll('#assignmentsGridTable tbody tr.history-table-row'));
        if (onlySelected) {
            targetRows = targetRows.filter(tr => tr.querySelector('.history-row-cb')?.checked);
        }
        if (!targetRows.length) {
            alert('No assignment history records available to export.');
            return;
        }

        const headers = ['Task Title', 'Assigned Developer', 'Developer Email', 'Company', 'Assigned By', 'Date', 'Priority', 'Status'];
        const rowsData = targetRows.map(tr => {
            const title = tr.getAttribute('data-title') || tr.querySelector('td:nth-child(2) strong')?.innerText?.trim() || '';
            const devName = tr.getAttribute('data-developer') || tr.querySelector('td:nth-child(3) strong')?.innerText?.trim() || '';
            const devEmail = tr.getAttribute('data-email') || tr.querySelector('td:nth-child(3) span')?.innerText?.trim() || '';
            const company = tr.getAttribute('data-company') || tr.querySelector('td:nth-child(4)')?.innerText?.trim() || '';
            const assigner = tr.querySelector('td:nth-child(5)')?.innerText?.trim() || '';
            const date = tr.querySelector('td:nth-child(6)')?.innerText?.trim() || '';
            const priority = tr.getAttribute('data-priority') || tr.querySelector('td:nth-child(7)')?.innerText?.trim() || '';
            const status = tr.getAttribute('data-status') || tr.querySelector('td:nth-child(8)')?.innerText?.trim() || '';
            return [title, devName, devEmail, company, assigner, date, priority, status];
        });

        const dateStr = new Date().toISOString().slice(0, 10);
        generateCSVDownload(`assignment_history_${dateStr}.csv`, headers, rowsData);
    }

    // Printable PDF Window Generator
    function generatePDFPrintWindow(title, subtitle, tableHtml) {
        const printWin = window.open('', '_blank', 'width=1100,height=850');
        if (!printWin) {
            alert('Please allow popups to export printable PDF documents.');
            return;
        }

        const nowStr = new Date().toLocaleString();
        printWin.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>${title} - ${nowStr}</title>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 24px; color: #0f172a; }
                    .header-box { border-bottom: 2px solid #2F6BFF; padding-bottom: 12px; margin-bottom: 20px; }
                    .title { font-size: 22px; font-weight: 800; color: #2F6BFF; margin: 0 0 4px 0; }
                    .subtitle { font-size: 12px; color: #64748b; margin: 0; }
                    .meta-bar { font-size: 11px; color: #64748b; margin-top: 8px; display: flex; justify-content: space-between; }
                    table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 12px; }
                    th { background: #f1f5f9; color: #0f172a; text-transform: uppercase; font-size: 10px; font-weight: 800; padding: 10px 12px; border: 1px solid #cbd5e1; text-align: left; }
                    td { padding: 9px 12px; border: 1px solid #cbd5e1; vertical-align: middle; }
                    tr:nth-child(even) { background: #f8fafc; }
                    .footer { margin-top: 24px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 12px; }
                    @media print {
                        body { padding: 0; }
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="no-print" style="margin-bottom: 16px; text-align: right;">
                    <button onclick="window.print()" style="background: #2F6BFF; color: white; border: none; padding: 8px 18px; font-weight: 700; border-radius: 6px; cursor: pointer;">🖨️ Print / Save as PDF</button>
                </div>
                <div class="header-box">
                    <h1 class="title">${title}</h1>
                    <p class="subtitle">${subtitle}</p>
                    <div class="meta-bar">
                        <span>Generated By: Super Admin Platform</span>
                        <span>Timestamp: ${nowStr}</span>
                    </div>
                </div>
                ${tableHtml}
                <div class="footer">
                    Platform Developer Management Audit Center &copy; ${new Date().getFullYear()}
                </div>
            </body>
            </html>
        `);
        printWin.document.close();
        printWin.focus();
    }

    function exportDevelopersPDF(onlySelected = false) {
        let targetRows = Array.from(document.querySelectorAll('#developersGridTable tbody tr.dev-table-row'));
        if (onlySelected) {
            targetRows = targetRows.filter(tr => tr.querySelector('.dev-row-cb')?.checked);
        }
        if (!targetRows.length) {
            alert('No developer records available to export.');
            return;
        }

        let rowsHtml = targetRows.map(tr => {
            const name = tr.querySelector('strong')?.innerText?.trim() || '';
            const role = tr.querySelector('td:nth-child(3)')?.innerText?.trim() || '';
            const email = tr.getAttribute('data-email') || tr.querySelector('td:nth-child(4)')?.innerText?.trim() || '';
            const status = tr.getAttribute('data-status') || tr.querySelector('.badge-status')?.innerText?.trim() || '';
            const activeTasks = tr.querySelector('td:nth-child(7)')?.innerText?.trim() || '0';
            const workload = tr.querySelector('td:nth-child(8)')?.innerText?.replace(/\s+/g, ' ').trim() || '';
            const lastActive = tr.querySelector('td:nth-child(9)')?.innerText?.trim() || '';
            return `<tr>
                <td><strong>${name}</strong></td>
                <td>${role}</td>
                <td style="font-family: monospace;">${email}</td>
                <td>${status}</td>
                <td>${activeTasks}</td>
                <td>${workload}</td>
                <td>${lastActive}</td>
            </tr>`;
        }).join('');

        const tableHtml = `
            <table>
                <thead>
                    <tr>
                        <th>Developer Name</th>
                        <th>Role</th>
                        <th>Registered Email</th>
                        <th>Status</th>
                        <th>Active Tasks</th>
                        <th>Workload Capacity</th>
                        <th>Last Active</th>
                    </tr>
                </thead>
                <tbody>${rowsHtml}</tbody>
            </table>
        `;

        generatePDFPrintWindow('Developer Directory Report', 'Enterprise Developer Management & Capacity Assessment Audit Report', tableHtml);
    }

    function exportSelectedDevelopersPDF() {
        exportDevelopersPDF(true);
    }

    function exportHistoryPDF(onlySelected = false) {
        let targetRows = Array.from(document.querySelectorAll('#assignmentsGridTable tbody tr.history-table-row'));
        if (onlySelected) {
            targetRows = targetRows.filter(tr => tr.querySelector('.history-row-cb')?.checked);
        }
        if (!targetRows.length) {
            alert('No assignment history records available to export.');
            return;
        }

        let rowsHtml = targetRows.map(tr => {
            const title = tr.getAttribute('data-title') || tr.querySelector('td:nth-child(2) strong')?.innerText?.trim() || '';
            const devName = tr.getAttribute('data-developer') || tr.querySelector('td:nth-child(3) strong')?.innerText?.trim() || '';
            const devEmail = tr.getAttribute('data-email') || tr.querySelector('td:nth-child(3) span')?.innerText?.trim() || '';
            const company = tr.getAttribute('data-company') || tr.querySelector('td:nth-child(4)')?.innerText?.trim() || '';
            const assigner = tr.querySelector('td:nth-child(5)')?.innerText?.trim() || '';
            const date = tr.querySelector('td:nth-child(6)')?.innerText?.trim() || '';
            const priority = tr.getAttribute('data-priority') || tr.querySelector('td:nth-child(7)')?.innerText?.trim() || '';
            const status = tr.getAttribute('data-status') || tr.querySelector('td:nth-child(8)')?.innerText?.trim() || '';
            return `<tr>
                <td><strong>${title}</strong></td>
                <td>${devName}<br><small style="color:#64748b;">${devEmail}</small></td>
                <td>${company}</td>
                <td>${assigner}</td>
                <td>${date}</td>
                <td>${priority}</td>
                <td>${status}</td>
            </tr>`;
        }).join('');

        const tableHtml = `
            <table>
                <thead>
                    <tr>
                        <th>Task Title</th>
                        <th>Assigned Developer</th>
                        <th>Company</th>
                        <th>Assigned By</th>
                        <th>Date</th>
                        <th>Priority</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>${rowsHtml}</tbody>
            </table>
        `;

        generatePDFPrintWindow('Work Allocation History Report', 'Recent Task Assignment Telemetry & Work Allocation Audit Log', tableHtml);
    }

    function assignWorkToSelected() {
        const selectedCbs = document.querySelectorAll('.dev-row-cb:checked');
        if (!selectedCbs.length) return;
        const firstEmail = selectedCbs[0].getAttribute('data-email');
        openAssignModal();
        if (firstEmail) {
            const input = document.getElementById('assignDevEmailInput');
            if (input) {
                input.value = firstEmail;
                lookupDevEmail(firstEmail);
            }
        }
    }

    // Scroll & Drawer functions
    function filterByStatus(st) {
        const form = document.getElementById('devFilterForm');
        const select = form.querySelector('select[name="status"]');
        if (select) {
            select.value = st;
            form.submit();
        }
    }

    function scrollToHistory() {
        const sec = document.getElementById('assignmentHistorySection');
        if (sec) sec.scrollIntoView({ behavior: 'smooth' });
    }

    // Slide-Over Drawer Data Population
    function openDevDrawer(devId) {
        const dev = devData.find(d => d.id == devId);
        if (!dev) return;

        document.getElementById('drawerDevAvatar').innerText = (dev.name || 'DV').substr(0, 2).toUpperCase();
        document.getElementById('drawerDevName').innerText = dev.name;
        document.getElementById('drawerDevRole').innerText = dev.role_title;
        document.getElementById('drawerDevCompany').innerText = dev.company ? dev.company.name : 'Platform Central';
        document.getElementById('drawerDevEmail').innerText = dev.email;
        document.getElementById('drawerDevWorkloadCategory').innerText = dev.workload_category + ' Workload';
        document.getElementById('drawerDevHours').innerText = dev.estimate_hours_total + 'h / 40h';
        document.getElementById('drawerCapacityBar').style.width = dev.capacity_percentage + '%';

        // Skills
        const skillsContainer = document.getElementById('drawerDevSkillsContainer');
        skillsContainer.innerHTML = '';
        (dev.skills_list || ['PHP', 'Laravel']).forEach(skill => {
            const span = document.createElement('span');
            span.className = 'skill-tag';
            span.innerText = skill;
            skillsContainer.appendChild(span);
        });

        // Current Assignments
        const assignList = document.getElementById('drawerDevAssignmentsList');
        assignList.innerHTML = '';
        if (dev.active_tasks && dev.active_tasks.length > 0) {
            dev.active_tasks.forEach(t => {
                const item = document.createElement('div');
                item.style.cssText = 'padding: 12px; background: #ffffff; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 8px;';
                item.innerHTML = `
                    <strong style="font-size: 13px; color: var(--slate-dark); display: block;">${t.title}</strong>
                    <span style="font-size: 11.5px; color: var(--slate-muted);">Priority: ${(t.priority || 'medium').toUpperCase()} · Status: ${(t.status || 'assigned').toUpperCase()}</span>
                `;
                assignList.appendChild(item);
            });
        } else {
            assignList.innerHTML = '<span style="font-size: 12px; color: var(--slate-muted);">No active tasks assigned to this developer.</span>';
        }

        // Actions
        document.getElementById('drawerEditBtn').onclick = function() {
            closeDevDrawer();
            openEditDeveloperModal(dev.id);
        };
        const statusForm = document.getElementById('drawerStatusForm');
        statusForm.action = '{{ url('/super-admin/developers') }}/' + dev.id + '/toggle-status';

        document.getElementById('devProfileDrawer').classList.add('active');
    }

    function closeDevDrawer() {
        document.getElementById('devProfileDrawer').classList.remove('active');
    }

    // Modal Control
    function openAddDeveloperModal() {
        document.getElementById('devModalTitle').innerText = 'Add New Developer';
        document.getElementById('devModalSubTitle').innerText = 'Create a permanent developer account (Only ONCE per developer).';
        const banner = document.getElementById('devNoticeBanner');
        if (banner) banner.style.display = 'flex';
        const submitBtn = document.getElementById('devFormSubmitBtn');
        if (submitBtn) submitBtn.innerHTML = '<i class="bx bx-user-check"></i> Create Developer Account';

        document.getElementById('devForm').action = '{{ route('super-admin.developers.store') }}';
        document.getElementById('devFormMethod').value = 'POST';
        document.getElementById('devFormName').value = '';
        document.getElementById('devFormEmail').value = '';
        document.getElementById('devFormPersonalEmail').value = '';
        document.getElementById('devFormCountryCode').value = '+91';
        document.getElementById('devFormMobileDisplay').value = '';
        document.getElementById('devFormMobile').value = '';
        document.getElementById('devFormRole').value = 'Full Stack Developer';
        document.getElementById('devFormExperience').value = '';
        document.getElementById('devFormCompany').value = '';
        document.getElementById('devFormJoiningDate').value = '{{ date('Y-m-d') }}';
        document.getElementById('devFormSkills').value = '';
        document.getElementById('developerModal').classList.add('active');
    }

    function openEditDeveloperModal(devId) {
        const dev = devData.find(d => d.id == devId);
        if (!dev) return;

        document.getElementById('devModalTitle').innerText = 'Edit Developer Profile & Details';
        document.getElementById('devModalSubTitle').innerText = 'Update profile, role specialization, and skills configuration for ' + dev.name + '.';
        const banner = document.getElementById('devNoticeBanner');
        if (banner) banner.style.display = 'none';
        const submitBtn = document.getElementById('devFormSubmitBtn');
        if (submitBtn) submitBtn.innerHTML = '<i class="bx bx-save"></i> Save Developer Profile';

        document.getElementById('devForm').action = '{{ url('/super-admin/developers') }}/' + dev.id;
        document.getElementById('devFormMethod').value = 'PUT';
        document.getElementById('devFormName').value = dev.name || '';
        document.getElementById('devFormEmail').value = dev.email || '';
        document.getElementById('devFormPersonalEmail').value = dev.personal_email || dev.email || '';
        let phoneVal = dev.phone_number || dev.mobile || '';
        let cc = '+91';
        let num = phoneVal;
        let match = phoneVal.match(/^(\+\d{1,4})\s*[-\s]?(.*)$/);
        if(match) {
            cc = match[1];
            num = match[2];
        } else if(phoneVal.startsWith('+')) {
            // fallback if it just starts with +
            cc = phoneVal.substring(0, phoneVal.indexOf(' '));
            num = phoneVal.substring(phoneVal.indexOf(' ') + 1);
            if(!cc) { cc = '+91'; num = phoneVal; }
        }
        document.getElementById('devFormCountryCode').value = cc;
        document.getElementById('devFormMobileDisplay').value = num;
        document.getElementById('devFormMobile').value = phoneVal;
        document.getElementById('devFormRole').value = dev.role_title || dev.designation || 'Full Stack Developer';
        document.getElementById('devFormExperience').value = dev.experience || '';
        document.getElementById('devFormCompany').value = dev.company_id || (dev.company ? dev.company.id : '');
        document.getElementById('devFormJoiningDate').value = dev.joining_date || '';
        document.getElementById('devFormSkills').value = (dev.skills_list || []).join(', ');
        document.getElementById('developerModal').classList.add('active');
    }

    function closeDeveloperModal() {
        document.getElementById('developerModal').classList.remove('active');
    }

    function openAssignModal() {
        document.getElementById('assignWorkModal').classList.add('active');
    }

    function closeAssignModal() {
        document.getElementById('assignWorkModal').classList.remove('active');
    }

    function quickAssignWork(email) {
        openAssignModal();
        const select = document.getElementById('assignDevSelect');
        let matched = false;
        if (select) {
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].getAttribute('data-email') === email) {
                    select.selectedIndex = i;
                    onDevSelectChange(select);
                    matched = true;
                    break;
                }
            }
        }
        if (!matched) {
            select.value = 'new';
            onDevSelectChange(select);
            const input = document.getElementById('assignDevEmailInput');
            if (input) {
                input.value = email;
                lookupDevEmail(email);
            }
        }
    }

    function onDevSelectChange(selectElem) {
        const val = selectElem.value;
        const manualFields = document.getElementById('manualDevFields');
        const emailInput = document.getElementById('assignDevEmailInput');
        const nameInput = document.getElementById('assignDevNameInput');
        const roleInput = document.getElementById('assignDevRoleInput');
        
        if (val === 'new') {
            manualFields.style.display = 'grid';
            emailInput.value = '';
            emailInput.required = true;
            nameInput.value = '';
            roleInput.value = '';
            document.getElementById('devLookupCard').style.display = 'none';
        } else if (val) {
            manualFields.style.display = 'none';
            const selectedOpt = selectElem.options[selectElem.selectedIndex];
            const email = selectedOpt.getAttribute('data-email');
            const name = selectedOpt.getAttribute('data-name');
            const role = selectedOpt.getAttribute('data-role');
            emailInput.value = email;
            nameInput.value = name;
            roleInput.value = role;
            lookupDevEmail(email);
        } else {
            manualFields.style.display = 'none';
            document.getElementById('devLookupCard').style.display = 'none';
        }
    }

    function lookupDevEmail(val) {
        const query = val.trim().toLowerCase();
        const card = document.getElementById('devLookupCard');
        const alertBanner = document.getElementById('workloadAlertBanner');
        if (query.length < 3) {
            card.style.display = 'none';
            return;
        }

        const dev = devData.find(d => d.email.toLowerCase() === query || d.name.toLowerCase().includes(query));
        if (dev) {
            card.style.display = 'block';
            document.getElementById('lookupDevName').innerText = dev.name;
            document.getElementById('lookupDevRole').innerText = dev.role_title + ' · ' + (dev.company ? dev.company.name : 'Platform Central');
            document.getElementById('lookupDevWorkloadBadge').innerText = dev.workload_category + ' Workload (' + dev.active_tasks_count + ' Active Tasks)';

            if (dev.workload_category === 'Heavy' || dev.active_tasks_count >= 4) {
                alertBanner.style.display = 'block';
            } else {
                alertBanner.style.display = 'none';
            }
        } else {
            card.style.display = 'block';
            document.getElementById('lookupDevName').innerText = 'New Developer Account';
            document.getElementById('lookupDevRole').innerText = 'A new developer account will be created once and initial login credentials sent to: ' + query;
            document.getElementById('lookupDevWorkloadBadge').innerText = 'New Account';
            alertBanner.style.display = 'none';
        }
    }

    function openTaskHistoryModal(taskId) {
        const modal = document.getElementById('taskHistoryModal');
        const body = document.getElementById('taskHistoryTimelineBody');
        body.innerHTML = '<div style="text-align:center; padding: 28px; color: var(--slate-muted);"><i class="bx bx-loader-alt bx-spin" style="font-size: 32px; color: var(--primary);"></i><br><span style="margin-top: 8px; display: inline-block;">Loading activity timeline...</span></div>';
        modal.classList.add('active');

        fetch('{{ url('/super-admin/developers/tasks') }}/' + taskId + '/history')
            .then(res => res.json())
            .then(data => {
                if (!data.success || !data.task) {
                    body.innerHTML = '<div style="text-align:center; padding: 24px; color: var(--danger);">Unable to load activity history.</div>';
                    return;
                }
                document.getElementById('historyModalTaskTitle').innerText = data.task.title;
                document.getElementById('historyModalTaskSub').innerText = 'Assigned Developer: ' + (data.task.developer_name || 'Developer') + ' (' + (data.task.company_name || 'Platform Central') + ')';
                
                if (!data.history || data.history.length === 0) {
                    body.innerHTML = `
                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; margin-bottom: 16px;">
                            <strong style="font-size: 13.5px; color: var(--slate-dark); display: block; margin-bottom: 4px;">Task Initialized: ${data.task.title}</strong>
                            <span style="font-size: 12px; color: var(--slate-muted);">Created on ${new Date(data.task.created_at).toLocaleString()} · Initial Status: ${data.task.status.toUpperCase()}</span>
                        </div>
                        <div style="text-align:center; padding: 20px; color: var(--slate-muted); font-size: 13px;">No further activity logs recorded yet.</div>
                    `;
                    return;
                }

                let html = '<div style="display: flex; flex-direction: column; gap: 14px;">';
                data.history.forEach((h, idx) => {
                    const dateStr = new Date(h.created_at).toLocaleString();
                    html += `
                        <div style="display: flex; gap: 14px; position: relative;">
                            <div style="width: 34px; height: 34px; border-radius: 50%; background: #EEF2FF; color: #2F6BFF; display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0; border: 1px solid #C7D2FE;">
                                <i class="bx bx-history"></i>
                            </div>
                            <div style="flex: 1; background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px 16px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                    <strong style="font-size: 13px; color: var(--slate-dark);">${h.user_name || 'System / Admin'}</strong>
                                    <span style="font-size: 11px; color: var(--slate-muted); font-weight: 600;">${dateStr}</span>
                                </div>
                                <div style="font-size: 12.5px; color: var(--slate-body);">${h.details || 'Task action logged.'}</div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                body.innerHTML = html;
            })
            .catch(err => {
                body.innerHTML = '<div style="text-align:center; padding: 24px; color: var(--danger);">Failed to load activity history timeline.</div>';
            });
    }

    function closeTaskHistoryModal() {
        document.getElementById('taskHistoryModal').classList.remove('active');
    }

    function shareDevWhatsApp(name, email, personalEmail, phone, devId, activeTasks, password) {
        let cleanPhone = (phone || '').replace(/[^0-9]/g, '');
        if (!cleanPhone) {
            cleanPhone = '919876543210';
        }

        const pwdText = password || 'Developer@123';
        const loginUrl = '{{ url('/login') }}';
        const msgText = `*PMS Developer Portal Credentials & Task Info*\n` +
            `Hello ${name},\n\n` +
            `Here are your Developer Workspace login details:\n` +
            `👤 Name: ${name}\n` +
            `🆔 Developer ID: ${devId}\n` +
            `📧 Login Email: ${email}\n` +
            `🔑 Login Password: ${pwdText}\n` +
            `📩 Personal Email: ${personalEmail || email}\n` +
            `🔗 Login Portal: ${loginUrl}\n` +
            `📋 Active Tasks: ${activeTasks || 0} Assigned\n\n` +
            `Please log into your Developer Workspace to review and complete your assigned tasks.`;

        const encodedMsg = encodeURIComponent(msgText);
        window.open(`https://api.whatsapp.com/send?phone=${cleanPhone}&text=${encodedMsg}`, '_blank');
    }

    let activeCredDevId = null;
    function openDevCredentialsModal(devId, name, email, personalEmail, phone, devCode, activeTasks, password) {
        activeCredDevId = devId;
        const pwdText = password || 'Developer@123';

        document.getElementById('credModalDevName').innerText = name + ' - Credentials';
        document.getElementById('credModalDevId').innerText = devCode + ' · ' + (personalEmail || email);
        document.getElementById('credModalLoginEmail').innerText = email;
        document.getElementById('credModalLoginPasswordInput').value = pwdText;
        document.getElementById('credModalPersonalEmail').innerText = personalEmail || email;
        document.getElementById('credModalPhone').innerText = phone || 'N/A';
        document.getElementById('credModalActiveTasks').innerText = (activeTasks || 0) + ' Active Tasks';

        document.getElementById('credResetForm').action = `{{ url('/super-admin/developers') }}/${devId}/reset-password`;

        const loginUrl = '{{ url('/login') }}';
        const msgText = `PMS Developer Portal Credentials for ${name}:\nLogin Email: ${email}\nLogin Password: ${pwdText}\nLogin Portal: ${loginUrl}\nActive Tasks: ${activeTasks || 0}`;
        const encodedText = encodeURIComponent(msgText);

        document.getElementById('credMailtoBtn').onclick = function() {
            const subject = encodeURIComponent(`Developer Workspace Credentials - ${name}`);
            window.location.href = `mailto:${personalEmail || email}?subject=${subject}&body=${encodedText}`;
        };

        document.getElementById('credWhatsappBtn').onclick = function() {
            shareDevWhatsApp(name, email, personalEmail, phone, devCode, activeTasks, pwdText);
        };

        document.getElementById('devCredentialsModal').classList.add('active');
    }

    function togglePasswordVisibility(inputId, btnElem) {
        const input = document.getElementById(inputId);
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            btnElem.innerHTML = '<i class="bx bx-hide"></i>';
        } else {
            input.type = 'password';
            btnElem.innerHTML = '<i class="bx bx-show"></i>';
        }
    }

    function closeDevCredentialsModal() {
        document.getElementById('devCredentialsModal').classList.remove('active');
    }

    const credResetForm = document.getElementById('credResetForm');
    if (credResetForm) {
        credResetForm.addEventListener('submit', function(e) {
            const pwdInput = document.getElementById('devResetPasswordInput');
            const errorDiv = document.getElementById('devResetPassword_error');
            if (pwdInput && pwdInput.value) {
                const val = pwdInput.value;
                if (val.length < 8) {
                    e.preventDefault();
                    if (errorDiv) { errorDiv.textContent = 'Password must be at least 8 characters long.'; errorDiv.style.display = 'block'; }
                    pwdInput.focus();
                    return false;
                }
                if (val.length > 128) {
                    e.preventDefault();
                    if (errorDiv) { errorDiv.textContent = 'Password cannot exceed 128 characters.'; errorDiv.style.display = 'block'; }
                    pwdInput.focus();
                    return false;
                }
            }
            if (errorDiv) { errorDiv.textContent = ''; errorDiv.style.display = 'none'; }
        });
    }

    function copyTextToClipboard(text, successMsg) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                alert(successMsg || 'Copied to clipboard!');
            });
        } else {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert(successMsg || 'Copied to clipboard!');
        }
    }

    // Developer Form Strict Email & Phone Validation
    document.addEventListener('DOMContentLoaded', function() {
        function getDevPhoneRule(countryCode) {
            switch (countryCode) {
                case '+91': return { min: 10, max: 10, regex: /^[6-9]\d{9}$/, placeholder: '9876543210', error: 'India phone number must be exactly 10 digits starting with 6, 7, 8, or 9.' };
                case '+1':  return { min: 10, max: 10, regex: /^[2-9]\d{9}$/, placeholder: '5550192831', error: 'US/Canada phone number must be exactly 10 digits (e.g. 5550192831).' };
                case '+44': return { min: 10, max: 11, regex: /^[1-9]\d{9,10}$/, placeholder: '7911123456', error: 'UK phone number must be 10 to 11 digits.' };
                case '+61': return { min: 9,  max: 10, regex: /^[1-9]\d{8,9}$/, placeholder: '412345678', error: 'Australia phone number must be 9 to 10 digits.' };
                case '+971':return { min: 9,  max: 9,  regex: /^[2-9]\d{8}$/, placeholder: '501234567', error: 'UAE phone number must be 9 digits (e.g. 501234567).' };
                case '+81': return { min: 10, max: 10, regex: /^[1-9]\d{9}$/, placeholder: '9012345678', error: 'Japan phone number must be 10 digits.' };
                case '+49': return { min: 10, max: 11, regex: /^[1-9]\d{9,10}$/, placeholder: '15123456789', error: 'Germany phone number must be 10 to 11 digits.' };
                case '+33': return { min: 9,  max: 9,  regex: /^[1-9]\d{8}$/, placeholder: '612345678', error: 'France phone number must be 9 digits.' };
                default:    return { min: 7,  max: 15, regex: /^\d{7,15}$/, placeholder: '1234567890', error: 'Phone number must be between 7 and 15 digits.' };
            }
        }

        function validateDevEmailFormat(email, isRequired = true) {
            const val = (email || '').trim();
            if (!val) {
                return isRequired ? { valid: false, message: 'Email address is required.' } : { valid: true };
            }
            if (/\s/.test(val)) {
                return { valid: false, message: 'Email address cannot contain spaces.' };
            }
            if (!val.includes('@')) {
                return { valid: false, message: "Email address must include an '@' symbol." };
            }
            const parts = val.split('@');
            if (parts.length !== 2) {
                return { valid: false, message: "Email address must contain only one '@' symbol." };
            }
            const [local, domain] = parts;
            if (!local) return { valid: false, message: "Missing username before '@'." };
            if (!domain) return { valid: false, message: "Missing domain after '@'." };
            if (!domain.includes('.')) return { valid: false, message: "Domain name must include a valid extension (e.g. .com)." };
            if (domain.startsWith('.') || domain.endsWith('.')) return { valid: false, message: "Domain name cannot start or end with a dot." };
            if (domain.includes('..')) return { valid: false, message: "Domain name cannot contain consecutive dots." };
            const strictRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!strictRegex.test(val)) {
                return { valid: false, message: 'Please enter a valid email address (e.g. name@company.com).' };
            }
            return { valid: true };
        }

        function setupDevPhoneValidator(countrySelectId, phoneInputId, hiddenInputId, errorDivId) {
            const countrySelect = document.getElementById(countrySelectId);
            const phoneInput = document.getElementById(phoneInputId);
            const hiddenInput = document.getElementById(hiddenInputId);
            const errorDiv = document.getElementById(errorDivId);
            if (!countrySelect || !phoneInput) return null;

            function updateRule() {
                const rule = getDevPhoneRule(countrySelect.value);
                phoneInput.placeholder = rule.placeholder;
                phoneInput.maxLength = rule.max;
            }

            phoneInput.addEventListener('keydown', function(e) {
                if (['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].includes(e.key) ||
                    ((e.ctrlKey || e.metaKey) && ['a', 'c', 'v', 'x', 'z'].includes(e.key.toLowerCase()))) {
                    return;
                }
                if (!/^\d$/.test(e.key)) {
                    e.preventDefault();
                }
            });

            function syncValue() {
                phoneInput.value = phoneInput.value.replace(/\D/g, '');
                const rule = getDevPhoneRule(countrySelect.value);
                if (phoneInput.value.length > rule.max) {
                    phoneInput.value = phoneInput.value.substring(0, rule.max);
                }
                if (hiddenInput) {
                    hiddenInput.value = phoneInput.value ? countrySelect.value + ' ' + phoneInput.value : '';
                }
            }

            phoneInput.addEventListener('input', function() {
                syncValue();
                validate(false);
            });

            countrySelect.addEventListener('change', function() {
                updateRule();
                syncValue();
                validate(false);
            });

            phoneInput.addEventListener('blur', function() {
                validate(true);
            });

            function validate(showEmptyError = true) {
                const val = phoneInput.value.trim();
                if (!val) {
                    clearError();
                    return true;
                }
                const rule = getDevPhoneRule(countrySelect.value);
                if (!rule.regex.test(val)) {
                    showError(rule.error);
                    return false;
                }
                clearError();
                return true;
            }

            function showError(msg) {
                if (errorDiv) {
                    errorDiv.textContent = msg;
                    errorDiv.classList.add('visible');
                }
                phoneInput.classList.add('is-invalid');
            }

            function clearError() {
                if (errorDiv) {
                    errorDiv.textContent = '';
                    errorDiv.classList.remove('visible');
                }
                phoneInput.classList.remove('is-invalid');
            }

            updateRule();
            syncValue();
            return { validate, input: phoneInput };
        }

        function setupDevEmailValidator(inputId, errorDivId, isRequired = true) {
            const input = document.getElementById(inputId);
            const errorDiv = document.getElementById(errorDivId);
            if (!input) return null;

            function validate(showEmptyError = true) {
                const val = input.value.trim();
                if (!val && !showEmptyError && !isRequired) {
                    clearError();
                    return true;
                }
                const res = validateDevEmailFormat(val, isRequired);
                if (!res.valid) {
                    if (val || showEmptyError) {
                        showError(res.message);
                        return false;
                    }
                    return false;
                }
                clearError();
                return true;
            }

            function showError(msg) {
                if (errorDiv) {
                    errorDiv.textContent = msg;
                    errorDiv.classList.add('visible');
                }
                input.classList.add('is-invalid');
            }

            function clearError() {
                if (errorDiv) {
                    errorDiv.textContent = '';
                    errorDiv.classList.remove('visible');
                }
                input.classList.remove('is-invalid');
            }

            input.addEventListener('input', function() {
                if (/\s/.test(this.value)) {
                    this.value = this.value.replace(/\s+/g, '');
                }
                validate(false);
            });

            input.addEventListener('blur', function() {
                validate(true);
            });

            return { validate, input };
        }

        const devPhoneVal = setupDevPhoneValidator('devFormCountryCode', 'devFormMobileDisplay', 'devFormMobile', 'devFormMobile_error');
        const devLoginEmailVal = setupDevEmailValidator('devFormEmail', 'devFormEmail_error', true);
        const devPersonalEmailVal = setupDevEmailValidator('devFormPersonalEmail', 'devFormPersonalEmail_error', true);
        const devAssignEmailVal = setupDevEmailValidator('assignDevEmailInput', 'assignDevEmail_error', false);

        const devForm = document.getElementById('devForm');
        if (devForm) {
            devForm.addEventListener('submit', function(e) {
                const isLoginEmailValid = devLoginEmailVal ? devLoginEmailVal.validate(true) : true;
                const isPersonalEmailValid = devPersonalEmailVal ? devPersonalEmailVal.validate(true) : true;
                const isPhoneValid = devPhoneVal ? devPhoneVal.validate(true) : true;

                if (!isLoginEmailValid || !isPersonalEmailValid || !isPhoneValid) {
                    e.preventDefault();
                    const firstInvalid = devForm.querySelector('input.is-invalid, select.is-invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                    }
                    return false;
                }
            });
        }

        const assignWorkForm = document.getElementById('assignWorkForm');
        if (assignWorkForm) {
            assignWorkForm.addEventListener('submit', function(e) {
                const select = document.getElementById('assignDevSelect');
                if (select && select.value === 'new') {
                    if (devAssignEmailVal && !devAssignEmailVal.validate(true)) {
                        e.preventDefault();
                        const input = document.getElementById('assignDevEmailInput');
                        if (input) input.focus();
                        return false;
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
