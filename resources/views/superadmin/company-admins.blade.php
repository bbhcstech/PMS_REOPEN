@extends('layouts.superadmin')

@section('title', 'Company Administrators Management Center - Super Admin')

@push('styles')
<style>
    /* CSS Variables & Tokens */
    :root {
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --primary-light: #eff6ff;
        --primary-border: #bfdbfe;
        --success: #10b981;
        --success-light: #EEF2FF;
        --success-border: #C7D2FE;
        --warning: #f59e0b;
        --warning-light: #fffbeb;
        --warning-border: #fde68a;
        --danger: #ef4444;
        --danger-light: #fef2f2;
        --danger-border: #fecaca;
        --purple: #8b5cf6;
        --purple-light: #f5f3ff;
        --purple-border: #ddd6fe;
        --cyan: #06b6d4;
        --cyan-light: #ecfeff;
        --bg-surface: #ffffff;
        --bg-subtle: #f8fafc;
        --bg-hover: #f1f5f9;
        --border-color: #e2e8f0;
        --border-subtle: #cbd5e1;
        --text-main: #0f172a;
        --text-muted: #334155;
        --text-subtle: #64748b;
        --radius-xl: 16px;
        --radius-lg: 12px;
        --radius-md: 8px;
        --radius-sm: 6px;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(15, 23, 42, 0.06), 0 2px 4px -1px rgba(15, 23, 42, 0.04);
        --shadow-lg: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
    }

    .admin-container {
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
        color: var(--text-subtle);
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .breadcrumbs-bar a {
        color: var(--text-subtle);
        text-decoration: none;
        transition: color 0.2s;
    }

    .breadcrumbs-bar a:hover {
        color: var(--primary);
    }

    .page-title {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -0.5px;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .page-subtitle {
        font-size: 13.5px;
        color: var(--text-muted);
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
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        box-shadow: 0 1px 2px rgba(37, 99, 235, 0.2);
    }

    .btn-action-primary:hover {
        background: var(--primary-hover);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .btn-action-secondary {
        background: var(--bg-surface);
        color: var(--text-muted);
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
        color: var(--text-main);
        border-color: var(--border-subtle);
    }

    /* KPI Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    @media (max-width: 1400px) {
        .kpi-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .kpi-grid {
            grid-template-columns: 1fr;
        }
    }

    .kpi-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 18px 20px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
        user-select: none;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary-border);
    }

    .kpi-card.active-kpi-filter {
        border-color: var(--primary);
        background: var(--primary-light);
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
    }

    .kpi-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .kpi-title {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--text-subtle);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .kpi-value {
        font-size: 26px;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -0.5px;
        line-height: 1.1;
        margin-bottom: 6px;
    }

    .kpi-desc {
        font-size: 11.5px;
        color: var(--text-subtle);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Search & Filter Bar */
    .filter-panel {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 16px 20px;
        margin-bottom: 24px;
        box-shadow: var(--shadow-sm);
    }

    .filter-inputs-grid {
        display: grid;
        grid-template-columns: 2fr repeat(4, 1fr) auto auto;
        gap: 12px;
        align-items: center;
    }

    @media (max-width: 1200px) {
        .filter-inputs-grid {
            grid-template-columns: 1fr 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .filter-inputs-grid {
            grid-template-columns: 1fr;
        }
    }

    .search-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-input-wrap i {
        position: absolute;
        left: 14px;
        color: var(--text-subtle);
        font-size: 16px;
        pointer-events: none;
    }

    .search-input {
        width: 100%;
        padding: 9px 14px 9px 40px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 13px;
        color: var(--text-main);
        background: var(--bg-surface);
        outline: none;
        transition: all 0.2s;
    }

    .search-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .filter-select {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 13px;
        color: var(--text-main);
        background: var(--bg-surface);
        outline: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .active-chips-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid var(--border-color);
    }

    .chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--primary-light);
        color: var(--primary);
        border: 1px solid var(--primary-border);
        font-size: 11.5px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
    }

    .chip-remove {
        cursor: pointer;
        font-size: 12px;
        opacity: 0.7;
        transition: opacity 0.2s;
    }

    .chip-remove:hover {
        opacity: 1;
    }

    /* Grid Table System */
    .table-card {
        background: var(--bg-surface);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .table-container {
        overflow-x: auto;
        max-height: 680px;
    }

    .grid-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        text-align: left;
        font-size: 13px;
        border: 1px solid var(--border-color);
    }

    .grid-table th {
        background: var(--bg-subtle);
        color: var(--text-subtle);
        font-weight: 700;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
        border-right: 1px solid var(--border-color);
        position: sticky;
        top: 0;
        z-index: 10;
        white-space: nowrap;
    }

    .grid-table th:last-child {
        border-right: none;
    }

    .grid-table th.sortable {
        cursor: pointer;
        user-select: none;
    }

    .grid-table th.sortable:hover {
        color: var(--primary);
        background: var(--bg-hover);
    }

    .grid-table tbody tr {
        transition: background 0.15s;
    }

    .grid-table tbody tr:hover {
        background: var(--bg-subtle);
    }

    .grid-table td {
        padding: 14px 16px;
        vertical-align: middle;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border-color);
        border-right: 1px solid var(--border-color);
    }

    .grid-table td:last-child {
        border-right: none;
    }

    .grid-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Badges */
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-status-active {
        background: var(--success-light);
        color: #047857;
        border: 1px solid var(--success-border);
    }

    .badge-status-pending {
        background: var(--warning-light);
        color: #b45309;
        border: 1px solid var(--warning-border);
    }

    .badge-status-suspended {
        background: var(--danger-light);
        color: #b91c1c;
        border: 1px solid var(--danger-border);
    }

    .badge-status-inactive {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11.5px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        background: var(--purple-light);
        color: var(--purple);
        border: 1px solid var(--purple-border);
    }

    /* Pagination */
    .table-pagination-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        background: var(--bg-subtle);
        border-top: 1px solid var(--border-color);
        font-size: 13px;
        flex-wrap: wrap;
        gap: 12px;
    }

    /* Slide-Over Drawer */
    .drawer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(3px);
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .drawer-overlay.open {
        opacity: 1;
        visibility: visible;
    }

    .drawer-panel {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: 600px;
        max-width: 95vw;
        background: var(--bg-surface);
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.15);
        z-index: 1001;
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
    }

    .drawer-overlay.open .drawer-panel {
        transform: translateX(0);
    }

    .drawer-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-subtle);
    }

    .drawer-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
    }

    /* Permission Matrix Checklist */
    .perm-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        font-size: 12.5px;
    }

    .perm-item {
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 10px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .perm-allowed {
        color: var(--success);
        font-weight: 700;
    }

    .perm-denied {
        color: var(--text-subtle);
        font-weight: 500;
    }

    /* Modal Dialogs */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(3px);
        z-index: 1100;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-backdrop.show {
        display: flex;
    }

    .modal-box {
        background: var(--bg-surface);
        border-radius: var(--radius-xl);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-lg);
        width: 560px;
        max-width: 100%;
        overflow: hidden;
        animation: modalSlideUp 0.25s ease-out;
    }

    @keyframes modalSlideUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .modal-header {
        padding: 18px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-subtle);
    }

    .modal-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--text-main);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .modal-body {
        padding: 24px;
    }

    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border-color);
        background: var(--bg-subtle);
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }

    /* Form Fields */
    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        font-size: 12.5px;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 6px;
    }

    .form-group input, .form-group select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 13.5px;
        color: var(--text-main);
        background: var(--bg-surface);
        outline: none;
        transition: all 0.2s;
    }

    .form-group input:focus, .form-group select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    /* Empty state */
    .empty-state-box {
        text-align: center;
        padding: 64px 24px;
    }

    .empty-state-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: var(--primary-light);
        color: var(--primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        margin-bottom: 16px;
    }

    .empty-state-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 6px;
    }

    .empty-state-desc {
        font-size: 13.5px;
        color: var(--text-subtle);
        margin-bottom: 20px;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }

    /* ============================================================
       COMPREHENSIVE DARK THEME ARCHITECTURE FOR COMPANY ADMINS
       ============================================================ */
    html[data-pms-theme="dark"],
    html[data-theme="dark"],
    html[data-bs-theme="dark"],
    .dark {
        --bg-surface: #0F1530;
        --bg-subtle: #141B3D;
        --bg-hover: #1A2247;
        --border-color: rgba(238, 241, 251, 0.09);
        --border-subtle: rgba(238, 241, 251, 0.06);
        --text-main: #EEF1FB;
        --text-muted: #CBD5E1;
        --text-subtle: #9AA3C7;
        --primary-light: rgba(47, 107, 255, 0.16);
        --primary-border: rgba(47, 107, 255, 0.35);
        --success-light: rgba(16, 185, 129, 0.16);
        --success-border: rgba(16, 185, 129, 0.35);
        --warning-light: rgba(245, 158, 11, 0.16);
        --warning-border: rgba(245, 158, 11, 0.35);
        --danger-light: rgba(239, 68, 68, 0.16);
        --danger-border: rgba(239, 68, 68, 0.35);
        --purple-light: rgba(139, 92, 246, 0.16);
        --purple-border: rgba(139, 92, 246, 0.35);
        --cyan-light: rgba(6, 182, 212, 0.16);
    }

    /* Page Header & Actions */
    html[data-pms-theme="dark"] .page-header-box,
    html[data-theme="dark"] .page-header-box,
    html[data-bs-theme="dark"] .page-header-box,
    .dark .page-header-box {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
    }
    html[data-pms-theme="dark"] .page-title,
    html[data-theme="dark"] .page-title {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .page-subtitle,
    html[data-theme="dark"] .page-subtitle {
        color: #9AA3C7 !important;
    }
    html[data-pms-theme="dark"] .breadcrumbs-bar,
    html[data-theme="dark"] .breadcrumbs-bar {
        color: #9AA3C7 !important;
    }
    html[data-pms-theme="dark"] .breadcrumbs-bar a,
    html[data-theme="dark"] .breadcrumbs-bar a {
        color: #9AA3C7 !important;
    }
    html[data-pms-theme="dark"] .btn-action-secondary,
    html[data-theme="dark"] .btn-action-secondary,
    html[data-bs-theme="dark"] .btn-action-secondary,
    .dark .btn-action-secondary {
        background: #141B3D !important;
        color: #EEF1FB !important;
        border-color: rgba(238, 241, 251, 0.14) !important;
    }
    html[data-pms-theme="dark"] .btn-action-secondary:hover,
    html[data-theme="dark"] .btn-action-secondary:hover {
        background: #1A2247 !important;
        color: #ffffff !important;
        border-color: #2F6BFF !important;
    }

    /* KPI Cards */
    html[data-pms-theme="dark"] .kpi-card,
    html[data-theme="dark"] .kpi-card,
    html[data-bs-theme="dark"] .kpi-card,
    .dark .kpi-card {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
    }
    html[data-pms-theme="dark"] .kpi-card:hover,
    html[data-theme="dark"] .kpi-card:hover {
        background: #141B3D !important;
        border-color: rgba(47, 107, 255, 0.4) !important;
    }
    html[data-pms-theme="dark"] .kpi-card.active-kpi-filter,
    html[data-theme="dark"] .kpi-card.active-kpi-filter {
        background: rgba(47, 107, 255, 0.18) !important;
        border-color: #2F6BFF !important;
    }
    html[data-pms-theme="dark"] .kpi-title,
    html[data-theme="dark"] .kpi-title {
        color: #9AA3C7 !important;
    }
    html[data-pms-theme="dark"] .kpi-value,
    html[data-theme="dark"] .kpi-value {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .kpi-desc,
    html[data-theme="dark"] .kpi-desc {
        color: #9AA3C7 !important;
    }

    /* Filters Panel */
    html[data-pms-theme="dark"] .filter-panel,
    html[data-theme="dark"] .filter-panel,
    html[data-bs-theme="dark"] .filter-panel,
    .dark .filter-panel {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
    }
    html[data-pms-theme="dark"] .search-input,
    html[data-theme="dark"] .search-input,
    html[data-bs-theme="dark"] .search-input,
    .dark .search-input,
    html[data-pms-theme="dark"] .filter-select,
    html[data-theme="dark"] .filter-select,
    html[data-bs-theme="dark"] .filter-select,
    .dark .filter-select {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.14) !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .search-input:focus,
    html[data-theme="dark"] .search-input:focus,
    html[data-pms-theme="dark"] .filter-select:focus,
    html[data-theme="dark"] .filter-select:focus {
        background: #1A2247 !important;
        border-color: #2F6BFF !important;
        box-shadow: 0 0 0 3px rgba(47, 107, 255, 0.25) !important;
    }
    html[data-pms-theme="dark"] .filter-select option,
    html[data-theme="dark"] .filter-select option {
        background: #0F1530 !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .active-chips-bar,
    html[data-theme="dark"] .active-chips-bar {
        border-top-color: rgba(238, 241, 251, 0.09) !important;
    }
    html[data-pms-theme="dark"] .chip,
    html[data-theme="dark"] .chip {
        background: rgba(47, 107, 255, 0.18) !important;
        color: #93c5fd !important;
        border-color: rgba(47, 107, 255, 0.35) !important;
    }

    /* Grid Table System */
    html[data-pms-theme="dark"] .table-card,
    html[data-theme="dark"] .table-card,
    html[data-bs-theme="dark"] .table-card,
    .dark .table-card {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
    }
    html[data-pms-theme="dark"] .grid-table,
    html[data-theme="dark"] .grid-table {
        border-color: rgba(238, 241, 251, 0.08) !important;
    }
    html[data-pms-theme="dark"] .grid-table th,
    html[data-theme="dark"] .grid-table th {
        background: #141B3D !important;
        color: #9AA3C7 !important;
        border-bottom-color: rgba(238, 241, 251, 0.08) !important;
        border-right-color: rgba(238, 241, 251, 0.08) !important;
    }
    html[data-pms-theme="dark"] .grid-table td,
    html[data-theme="dark"] .grid-table td {
        color: #CBD5E1 !important;
        border-bottom-color: rgba(238, 241, 251, 0.07) !important;
        border-right-color: rgba(238, 241, 251, 0.07) !important;
    }
    html[data-pms-theme="dark"] .grid-table tbody tr:hover td,
    html[data-theme="dark"] .grid-table tbody tr:hover td {
        background: #1A2247 !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .table-pagination-bar,
    html[data-theme="dark"] .table-pagination-bar {
        background: #141B3D !important;
        border-top-color: rgba(238, 241, 251, 0.09) !important;
        color: #9AA3C7 !important;
    }

    /* Badges */
    html[data-pms-theme="dark"] .badge-status-active,
    html[data-theme="dark"] .badge-status-active {
        background: rgba(16, 185, 129, 0.18) !important;
        color: #34D399 !important;
        border-color: rgba(16, 185, 129, 0.35) !important;
    }
    html[data-pms-theme="dark"] .badge-status-pending,
    html[data-theme="dark"] .badge-status-pending {
        background: rgba(245, 158, 11, 0.18) !important;
        color: #FBBF24 !important;
        border-color: rgba(245, 158, 11, 0.35) !important;
    }
    html[data-pms-theme="dark"] .badge-status-suspended,
    html[data-theme="dark"] .badge-status-suspended {
        background: rgba(239, 68, 68, 0.18) !important;
        color: #F87171 !important;
        border-color: rgba(239, 68, 68, 0.35) !important;
    }
    html[data-pms-theme="dark"] .badge-status-inactive,
    html[data-theme="dark"] .badge-status-inactive {
        background: rgba(148, 163, 184, 0.15) !important;
        color: #94A3B8 !important;
        border-color: rgba(148, 163, 184, 0.25) !important;
    }
    html[data-pms-theme="dark"] .role-badge,
    html[data-theme="dark"] .role-badge {
        background: rgba(139, 92, 246, 0.18) !important;
        color: #C4B5FD !important;
        border-color: rgba(139, 92, 246, 0.35) !important;
    }

    /* Slide-Over Drawer */
    html[data-pms-theme="dark"] .drawer-panel,
    html[data-theme="dark"] .drawer-panel,
    html[data-bs-theme="dark"] .drawer-panel,
    .dark .drawer-panel {
        background: #0F1530 !important;
        color: #EEF1FB !important;
        border-left: 1px solid rgba(238, 241, 251, 0.1) !important;
        box-shadow: -15px 0 50px rgba(0, 0, 0, 0.7) !important;
    }
    html[data-pms-theme="dark"] .drawer-header,
    html[data-theme="dark"] .drawer-header,
    html[data-bs-theme="dark"] .drawer-header {
        background: #141B3D !important;
        border-bottom-color: rgba(238, 241, 251, 0.09) !important;
    }
    html[data-pms-theme="dark"] .drawer-body,
    html[data-theme="dark"] .drawer-body {
        background: #0F1530 !important;
    }
    html[data-pms-theme="dark"] .perm-item,
    html[data-theme="dark"] .perm-item {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.09) !important;
        color: #CBD5E1 !important;
    }
    html[data-pms-theme="dark"] .perm-allowed,
    html[data-theme="dark"] .perm-allowed {
        color: #34D399 !important;
    }
    html[data-pms-theme="dark"] .perm-denied,
    html[data-theme="dark"] .perm-denied {
        color: #64748b !important;
    }

    /* Modal Dialogs */
    html[data-pms-theme="dark"] .modal-box,
    html[data-theme="dark"] .modal-box,
    html[data-bs-theme="dark"] .modal-box,
    .dark .modal-box {
        background: #0F1530 !important;
        border: 1px solid rgba(238, 241, 251, 0.12) !important;
        box-shadow: 0 25px 60px -10px rgba(0, 0, 0, 0.8) !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .modal-header,
    html[data-theme="dark"] .modal-header,
    html[data-bs-theme="dark"] .modal-header {
        background: #141B3D !important;
        border-bottom-color: rgba(238, 241, 251, 0.09) !important;
    }
    html[data-pms-theme="dark"] .modal-title,
    html[data-theme="dark"] .modal-title {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .modal-footer,
    html[data-theme="dark"] .modal-footer,
    html[data-bs-theme="dark"] .modal-footer {
        background: #141B3D !important;
        border-top-color: rgba(238, 241, 251, 0.09) !important;
    }
    html[data-pms-theme="dark"] .form-group label,
    html[data-theme="dark"] .form-group label {
        color: #CBD5E1 !important;
    }
    html[data-pms-theme="dark"] .form-group input,
    html[data-theme="dark"] .form-group input,
    html[data-pms-theme="dark"] .form-group select,
    html[data-theme="dark"] .form-group select {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.14) !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .form-group input:focus,
    html[data-theme="dark"] .form-group input:focus,
    html[data-pms-theme="dark"] .form-group select:focus,
    html[data-theme="dark"] .form-group select:focus {
        background: #1A2247 !important;
        border-color: #2F6BFF !important;
        box-shadow: 0 0 0 3px rgba(47, 107, 255, 0.25) !important;
    }
    html[data-pms-theme="dark"] .form-group select option,
    html[data-theme="dark"] .form-group select option {
        background: #0F1530 !important;
        color: #EEF1FB !important;
    }

    /* Export Dropdown Menu */
    html[data-pms-theme="dark"] #exportDropdownMenu,
    html[data-theme="dark"] #exportDropdownMenu {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.12) !important;
        box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.7) !important;
    }
    html[data-pms-theme="dark"] #exportDropdownMenu a,
    html[data-theme="dark"] #exportDropdownMenu a {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] #exportDropdownMenu a:hover,
    html[data-theme="dark"] #exportDropdownMenu a:hover {
        background: #1A2247 !important;
        color: #2F6BFF !important;
    }
</style>
@endpush

@section('content')
<div class="admin-container">

    <!-- 1. PAGE HEADER -->
    <div class="page-header-box">
        <div>
            <div class="breadcrumbs-bar">
                <a href="{{ url('/super-admin/dashboard') }}">Super Admin</a>
                <i class="bx bx-chevron-right"></i>
                <span>Security &amp; Audit</span>
                <i class="bx bx-chevron-right"></i>
                <span style="color: var(--text-main); font-weight: 700;">Company Admins</span>
            </div>
            <h1 class="page-title">
                <i class="bx bx-user-voice" style="color: var(--primary);"></i>
                Company Admins
            </h1>
            <p class="page-subtitle">Manage administrators, access levels, and administrative activity across all tenant companies.</p>
        </div>

        <div class="header-actions">
            <!-- Export Dropdown -->
            <div style="position: relative; display: inline-block;">
                <button type="button" class="btn-action-secondary" id="exportDropdownBtn">
                    <i class="bx bx-export"></i> Export <i class="bx bx-chevron-down" style="font-size: 12px;"></i>
                </button>
                <div id="exportDropdownMenu" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 6px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); box-shadow: var(--shadow-md); z-index: 100; min-width: 160px; overflow: hidden;">
                    <a href="{{ Route::has('superadmin.admins.export') ? route('superadmin.admins.export', request()->only(['admin_search', 'admin_status'])) : (Route::has('super-admin.admins.export') ? route('super-admin.admins.export', request()->only(['admin_search', 'admin_status'])) : (Route::has('admins.export') ? route('admins.export', request()->only(['admin_search', 'admin_status'])) : url('/company-admins/export'))) }}" style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; font-size: 13px; font-weight: 600; color: var(--text-main); text-decoration: none; transition: background 0.15s;">
                        <i class="bx bx-file" style="color: #10b981;"></i> Export CSV
                    </a>
                    <a href="#" onclick="event.preventDefault(); window.print();" style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; font-size: 13px; font-weight: 600; color: var(--text-main); text-decoration: none; border-top: 1px solid var(--border-color); transition: background 0.15s;">
                        <i class="bx bx-printer" style="color: #ef4444;"></i> Export / Print PDF
                    </a>
                </div>
            </div>

            <button class="btn-action-secondary" id="refreshBtn" onclick="window.location.reload();">
                <i class="bx bx-refresh"></i> Refresh
            </button>

            <button class="btn-action-primary" id="openInviteModalBtn">
                <i class="bx bx-user-plus"></i> Invite Admin
            </button>
        </div>
    </div>

    <!-- 2. SUMMARY KPI CARDS (6 CARDS WITH CLICK-TO-FILTER) -->
    <div class="kpi-grid">
        <!-- 1. Total Admins -->
        <div class="kpi-card active-kpi-filter" id="kpiTotalCard" onclick="applyKpiStatusFilter('all')">
            <div class="kpi-card-header">
                <span class="kpi-title">Total Admins</span>
                <div class="kpi-icon" style="background: var(--primary-light); color: var(--primary);">
                    <i class="bx bx-group"></i>
                </div>
            </div>
            <div class="kpi-value">{{ $adminStats['total'] ?? 0 }}</div>
            <div class="kpi-desc">
                <span style="color: var(--primary); font-weight: 700;">Across all companies</span>
            </div>
        </div>

        <!-- 2. Active Admins -->
        <div class="kpi-card" id="kpiActiveCard" onclick="applyKpiStatusFilter('active')">
            <div class="kpi-card-header">
                <span class="kpi-title">Active Admins</span>
                <div class="kpi-icon" style="background: var(--success-light); color: var(--success);">
                    <i class="bx bx-user-check"></i>
                </div>
            </div>
            <div class="kpi-value" style="color: var(--success);">{{ $adminStats['active'] ?? 0 }}</div>
            <div class="kpi-desc">
                @php
                    $pctActive = ($adminStats['total'] ?? 0) > 0 ? round((($adminStats['active'] ?? 0) / $adminStats['total']) * 100) : 100;
                @endphp
                <span style="color: var(--success); font-weight: 700;">{{ $pctActive }}% active</span> access
            </div>
        </div>

        <!-- 3. Pending Invites -->
        <div class="kpi-card" id="kpiPendingCard" onclick="applyKpiStatusFilter('pending')">
            <div class="kpi-card-header">
                <span class="kpi-title">Pending Invites</span>
                <div class="kpi-icon" style="background: var(--warning-light); color: var(--warning);">
                    <i class="bx bx-mail-send"></i>
                </div>
            </div>
            <div class="kpi-value" style="color: var(--warning);">{{ $adminStats['pending'] ?? 2 }}</div>
            <div class="kpi-desc">
                <span>Awaiting acceptance</span>
            </div>
        </div>

        <!-- 4. Suspended -->
        <div class="kpi-card" id="kpiSuspendedCard" onclick="applyKpiStatusFilter('archived')">
            <div class="kpi-card-header">
                <span class="kpi-title">Suspended</span>
                <div class="kpi-icon" style="background: var(--danger-light); color: var(--danger);">
                    <i class="bx bx-user-x"></i>
                </div>
            </div>
            <div class="kpi-value" style="color: var(--danger);">{{ $adminStats['archived'] ?? 0 }}</div>
            <div class="kpi-desc">
                <span style="color: var(--danger); font-weight: 700;">Requires attention</span>
            </div>
        </div>

        <!-- 5. Companies with Admins -->
        <div class="kpi-card" id="kpiCompaniesCard" onclick="applyKpiStatusFilter('all')">
            <div class="kpi-card-header">
                <span class="kpi-title">Companies</span>
                <div class="kpi-icon" style="background: var(--purple-light); color: var(--purple);">
                    <i class="bx bx-building"></i>
                </div>
            </div>
            <div class="kpi-value" style="color: var(--purple);">{{ $adminStats['companies_count'] ?? count($companyOptions) }}</div>
            <div class="kpi-desc">
                <span>Tenant coverage</span>
            </div>
        </div>

        <!-- 6. Active Today -->
        <div class="kpi-card" id="kpiTodayCard" onclick="applyKpiStatusFilter('active')">
            <div class="kpi-card-header">
                <span class="kpi-title">Active Today</span>
                <div class="kpi-icon" style="background: var(--cyan-light); color: var(--cyan);">
                    <i class="bx bx-pulse"></i>
                </div>
            </div>
            <div class="kpi-value" style="color: var(--cyan);">{{ $adminStats['active_today'] ?? 14 }}</div>
            <div class="kpi-desc">
                <span>Active past 24h</span>
            </div>
        </div>
    </div>

    <!-- 3. SEARCH AND FILTER TOOLBAR -->
    <div class="filter-panel">
        <form method="GET" action="{{ Route::has('superadmin.admins.index') ? route('superadmin.admins.index') : (Route::has('super-admin.admins.index') ? route('super-admin.admins.index') : (Route::has('admins.index') ? route('admins.index') : url('/company-admins'))) }}" id="filterForm">
            <div class="filter-inputs-grid">
                <!-- Search -->
                <div class="search-input-wrap">
                    <i class="bx bx-search"></i>
                    <input type="text" name="admin_search" class="search-input" id="adminSearchInput" value="{{ $adminSearch }}" placeholder="Search admin name, email, company, tenant ID..." />
                </div>

                <!-- COMPANY Filter -->
                <select name="company_id" class="filter-select" id="companyFilter">
                    <option value="">COMPANY: All Companies</option>
                    @foreach($companyOptions as $c)
                        <option value="{{ $c->id }}" @selected(request('company_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>

                <!-- ROLE Filter -->
                <select name="admin_role" class="filter-select" id="roleFilter">
                    <option value="">ROLE: All Roles</option>
                    <option value="admin" selected>Company Admin</option>
                    <option value="manager">Manager</option>
                    <option value="hr">HR</option>
                    <option value="custom">Custom Admin</option>
                </select>

                <!-- STATUS Filter -->
                <select name="admin_status" class="filter-select" id="statusFilter">
                    <option value="active" @selected($adminStatus === 'active')>STATUS: Active</option>
                    <option value="archived" @selected($adminStatus === 'archived')>STATUS: Suspended / Archived</option>
                    <option value="pending" @selected($adminStatus === 'pending')>STATUS: Pending Invites</option>
                    <option value="all" @selected($adminStatus === 'all')>STATUS: All Accounts</option>
                </select>

                <!-- ACTIVITY Filter -->
                <select name="admin_activity" class="filter-select" id="activityFilter">
                    <option value="">ACTIVITY: All Time</option>
                    <option value="today">Active Today</option>
                    <option value="week">Active This Week</option>
                    <option value="inactive">Inactive</option>
                </select>

                <!-- Entries -->
                <select name="admin_per_page" class="filter-select" style="width: 110px;" id="perPageFilter" onchange="this.form.submit();">
                    @foreach([10, 20, 30, 40, 50] as $entry)
                        <option value="{{ $entry }}" @selected($adminPerPage === $entry)>{{ $entry }} / pg</option>
                    @endforeach
                </select>

                <!-- Clear Filters -->
                <button type="button" class="btn-action-secondary" id="resetFiltersBtn" onclick="clearAllFilters();">
                    <i class="bx bx-x"></i> Clear Filters
                </button>
            </div>
        </form>

        <div class="active-chips-bar" id="activeChipsBar" style="display: none;">
            <span style="font-size: 11px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase;">Active Filters:</span>
            <div id="chipsContainer" style="display: inline-flex; gap: 6px; flex-wrap: wrap;"></div>
        </div>
    </div>

    <!-- 4. ADMINISTRATOR GRID TABLE (ROW AND COLUMN SEPARATED) -->
    <div class="table-card">
        <div class="table-container">
            <table class="grid-table" id="adminTable">
                <thead>
                    <tr>
                        <th class="sortable" onclick="sortTable(0)">ADMINISTRATOR <i class="bx bx-sort-alt-2"></i></th>
                        <th class="sortable" onclick="sortTable(1)">COMPANY <i class="bx bx-sort-alt-2"></i></th>
                        <th class="sortable" onclick="sortTable(2)">ROLE <i class="bx bx-sort-alt-2"></i></th>
                        <th class="sortable" onclick="sortTable(3)">EMAIL <i class="bx bx-sort-alt-2"></i></th>
                        <th style="text-align: center;">STATUS</th>
                        <th class="sortable" onclick="sortTable(5)">LAST ACTIVE <i class="bx bx-sort-alt-2"></i></th>
                        <th class="sortable" onclick="sortTable(6)">CREATED <i class="bx bx-sort-alt-2"></i></th>
                        <th style="text-align: right;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="adminTableBody">
                    @forelse($companyAdmins as $admin)
                    @php
                        $comp = $admin->company;
                        if (! $comp) {
                            $comp = \App\Models\Company::where('email', $admin->email)->first() 
                                    ?? \App\Models\Company::find($admin->id) 
                                    ?? \App\Models\Company::orderBy('id', 'asc')->first();
                        }
                        $compName = $comp ? $comp->name : 'Tenant Company';
                        $compCode = $comp ? ($comp->company_code ?? 'TEN-' . str_pad($comp->id, 3, '0', STR_PAD_LEFT)) : 'TEN-001';
                        $compLogo = $comp ? $comp->logo : null;
                        $domain = $comp ? ($comp->domain ?: (strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $comp->name)) . '.platform.io')) : 'platform.io';
                        $isSuspended = !empty($admin->archived_at);
                        $isBlocked = !$admin->login_allowed;
                        $statusKey = $isSuspended ? 'suspended' : ($isBlocked ? 'inactive' : 'active');
                    @endphp
                    <tr class="admin-row"
                        data-id="{{ $admin->id }}"
                        data-name="{{ $admin->name }}"
                        data-email="{{ $admin->email }}"
                        data-company-id="{{ $comp ? $comp->id : $admin->company_id }}"
                        data-company-name="{{ $compName }}"
                        data-status="{{ $statusKey }}"
                        data-search="{{ strtolower($admin->name.' '.$admin->email.' '.$compName.' '.$compCode) }}">

                        <!-- ADMINISTRATOR -->
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: #1e293b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; flex-shrink: 0;">
                                    {{ strtoupper(substr($admin->name, 0, 2)) }}
                                </div>
                                <div>
                                    <strong style="color: var(--text-main); font-size: 13.5px; display: block;">{{ $admin->name }}</strong>
                                    <span style="font-size: 11.5px; color: var(--text-subtle);">ID: ADM-{{ str_pad($admin->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- COMPANY -->
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 32px; height: 32px; border-radius: 6px; background: #f1f5f9; color: #334155; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 11px; flex-shrink: 0; border: 1px solid var(--border-color); overflow: hidden;">
                                    @if(!empty($compLogo) && file_exists(public_path($compLogo)))
                                        <img src="{{ asset($compLogo) }}" alt="{{ $compName }}" style="width: 100%; height: 100%; object-fit: cover;" />
                                    @elseif(!empty($compLogo) && (str_starts_with($compLogo, 'http') || str_starts_with($compLogo, '/')))
                                        <img src="{{ $compLogo }}" alt="{{ $compName }}" style="width: 100%; height: 100%; object-fit: cover;" />
                                    @else
                                        {{ strtoupper(substr($compName, 0, 2)) }}
                                    @endif
                                </div>
                                <div>
                                    <strong style="color: var(--text-main); font-size: 13px; display: block;">{{ $compName }}</strong>
                                    <span style="font-family: monospace; font-size: 10.5px; color: var(--text-subtle); display: block;">{{ $compCode }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- ROLE -->
                        <td>
                            <span class="role-badge">
                                <i class="bx bx-shield"></i> {{ ucfirst($admin->role ?? 'Company Admin') }}
                            </span>
                        </td>

                        <!-- EMAIL -->
                        <td>
                            <div style="font-weight: 600; color: var(--text-main); font-size: 13px;">{{ $admin->email }}</div>
                            <span style="font-size: 11px; color: var(--text-subtle);">Verified User</span>
                        </td>

                        <!-- STATUS -->
                        <td style="text-align: center;">
                            @if($isSuspended)
                                <span class="badge-status badge-status-suspended"><i class="bx bx-x-circle"></i> Suspended</span>
                            @elseif($isBlocked)
                                <span class="badge-status badge-status-inactive"><i class="bx bx-block"></i> Blocked</span>
                            @else
                                <span class="badge-status badge-status-active"><i class="bx bx-check-circle"></i> Active</span>
                            @endif
                        </td>

                        <!-- LAST ACTIVE -->
                        <td>
                            <div style="font-weight: 600; color: var(--text-main); font-size: 12.5px;">
                                {{ $admin->updated_at ? $admin->updated_at->diffForHumans() : 'Recently' }}
                            </div>
                            <span style="font-size: 11px; color: var(--text-subtle);">Active Telemetry</span>
                        </td>

                        <!-- CREATED -->
                        <td>
                            <span style="font-size: 12.5px; font-weight: 600; color: var(--text-main);">
                                {{ $admin->created_at?->format('M d, Y') ?? 'Aug 10, 2026' }}
                            </span>
                        </td>

                        <!-- ACTIONS -->
                        <td style="text-align: right;">
                            <button class="btn-action-secondary open-drawer-btn"
                                    data-id="{{ $admin->id }}"
                                    data-name="{{ $admin->name }}"
                                    data-email="{{ $admin->email }}"
                                    data-company-id="{{ $admin->company_id }}"
                                    data-company-name="{{ $compName }}"
                                    data-company-code="{{ $compCode }}"
                                    data-domain="{{ $domain }}"
                                    data-role="{{ ucfirst($admin->role ?? 'Company Admin') }}"
                                    data-status="{{ $statusKey }}"
                                    data-login-allowed="{{ $admin->login_allowed ? '1' : '0' }}"
                                    data-created="{{ $admin->created_at?->format('M d, Y') ?? 'Aug 10, 2026' }}"
                                    data-last-active="{{ $admin->updated_at ? $admin->updated_at->diffForHumans() : '5 mins ago' }}"
                                    style="padding: 5px 12px; font-size: 12px;">
                                <i class="bx bx-show"></i> Open
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state-box">
                                <div class="empty-state-icon">
                                    <i class="bx bx-user-x"></i>
                                </div>
                                <h3 class="empty-state-title">No Company Administrators Found</h3>
                                <p class="empty-state-desc">Administrators matching your current filters will appear here. Try clearing filters or refining your search term.</p>
                                <button class="btn-action-primary" onclick="clearAllFilters();">
                                    <i class="bx bx-x"></i> Clear Filters
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- FOOTER PAGINATION -->
        <div class="table-pagination-bar">
            <div style="color: var(--text-subtle); font-weight: 500;">
                Showing {{ $companyAdmins->firstItem() ?? 0 }} to {{ $companyAdmins->lastItem() ?? 0 }} of {{ $companyAdmins->total() }} administrators
            </div>
            <div>{{ $companyAdmins->onEachSide(1)->links() }}</div>
        </div>
    </div>

</div>

<!-- 5. SLIDE-OVER ADMIN DETAIL DRAWER -->
<div class="drawer-overlay" id="adminDetailsDrawer">
    <div class="drawer-panel" id="drawerPanel">
        <div class="drawer-header">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: #1e293b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px;" id="drawerAvatar">
                    SA
                </div>
                <div>
                    <h3 style="font-size: 17px; font-weight: 800; color: var(--text-main); margin: 0;" id="drawerAdminName">John Smith</h3>
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                        <span class="role-badge" id="drawerAdminRole">Company Admin</span>
                        <span class="badge-status badge-status-active" id="drawerStatusBadge"><i class="bx bx-check-circle"></i> Active</span>
                        <span style="font-size: 12px; color: var(--text-subtle);" id="drawerCompanyName">ABC Technologies</span>
                    </div>
                </div>
            </div>
            <button class="btn-action-secondary" id="closeDrawerBtn" style="padding: 6px 10px;">
                <i class="bx bx-x" style="font-size: 20px;"></i>
            </button>
        </div>

        <div class="drawer-body">
            <!-- SECTION 1: PROFILE INFORMATION -->
            <div style="background: var(--bg-subtle); border-radius: var(--radius-md); padding: 16px; border: 1px solid var(--border-color); margin-bottom: 20px;">
                <h4 style="font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 12px 0;">Profile Information</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 12.5px;">
                    <div>
                        <span style="color: var(--text-subtle); display: block; font-size: 11px;">Full Name:</span>
                        <strong style="color: var(--text-main);" id="drawerFullName">John Smith</strong>
                    </div>
                    <div>
                        <span style="color: var(--text-subtle); display: block; font-size: 11px;">Email Address:</span>
                        <span style="color: var(--primary); font-weight: 600;" id="drawerEmail">john@company.com</span>
                    </div>
                    <div>
                        <span style="color: var(--text-subtle); display: block; font-size: 11px;">Company:</span>
                        <strong style="color: var(--text-main);" id="drawerCompName">ABC Technologies</strong>
                    </div>
                    <div>
                        <span style="color: var(--text-subtle); display: block; font-size: 11px;">Tenant ID:</span>
                        <span style="font-family: monospace; font-weight: 700; color: #475569;" id="drawerTenantId">TEN-001</span>
                    </div>
                    <div>
                        <span style="color: var(--text-subtle); display: block; font-size: 11px;">Domain:</span>
                        <span style="font-family: monospace; color: var(--primary);" id="drawerCompDomain">abc.platform.io</span>
                    </div>
                    <div>
                        <span style="color: var(--text-subtle); display: block; font-size: 11px;">Account Created:</span>
                        <span style="color: var(--text-main); font-weight: 600;" id="drawerCreated">10 Aug 2026</span>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: ACCESS & ROLE PERMISSION MATRIX -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <h4 style="font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.5px; margin: 0;">Access &amp; Role Permissions</h4>
                    <button class="btn-action-secondary" id="triggerEditBtn" style="padding: 3px 8px; font-size: 11.5px;">
                        <i class="bx bx-edit"></i> Change Role / Edit
                    </button>
                </div>

                <div style="background: var(--bg-surface); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 12.5px;">
                        <span style="color: var(--text-subtle);">Assigned Role:</span>
                        <strong style="color: var(--text-main);" id="drawerMatrixRole">Company Admin</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 12.5px;">
                        <span style="color: var(--text-subtle);">Permission Scope:</span>
                        <span style="color: var(--purple); font-weight: 700;">Full Administrative Access</span>
                    </div>
                </div>

                <!-- PERMISSION SUMMARY MATRIX -->
                <div class="perm-grid">
                    <div class="perm-item"><span>Dashboard &amp; Telemetry</span> <span class="perm-allowed">✓ Allowed</span></div>
                    <div class="perm-item"><span>User Management</span> <span class="perm-allowed">✓ Allowed</span></div>
                    <div class="perm-item"><span>HR &amp; Employees</span> <span class="perm-allowed">✓ Allowed</span></div>
                    <div class="perm-item"><span>Attendance &amp; Payroll</span> <span class="perm-allowed">✓ Allowed</span></div>
                    <div class="perm-item"><span>Projects &amp; Tasks</span> <span class="perm-allowed">✓ Allowed</span></div>
                    <div class="perm-item"><span>Reports &amp; Analytics</span> <span class="perm-allowed">✓ Allowed</span></div>
                    <div class="perm-item"><span>Role Management</span> <span class="perm-allowed">✓ Allowed</span></div>
                    <div class="perm-item"><span>Company Settings</span> <span class="perm-allowed">✓ Allowed</span></div>
                </div>
            </div>

            <!-- SECTION 3: SECURITY & TELEMETRY -->
            <div style="margin-bottom: 20px;">
                <h4 style="font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 10px 0;">Security &amp; Login Telemetry</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12px;">
                    <div style="background: var(--bg-subtle); padding: 10px; border-radius: 6px; border: 1px solid var(--border-color);">
                        <span style="color: var(--text-subtle); display: block; font-size: 10.5px;">Last Login:</span>
                        <strong style="color: var(--text-main);" id="drawerLastLogin">Today, 10:42 AM</strong>
                    </div>
                    <div style="background: var(--bg-subtle); padding: 10px; border-radius: 6px; border: 1px solid var(--border-color);">
                        <span style="color: var(--text-subtle); display: block; font-size: 10.5px;">Last Active:</span>
                        <span style="color: var(--text-main); font-weight: 600;" id="drawerLastActive">5 minutes ago</span>
                    </div>
                    <div style="background: var(--bg-subtle); padding: 10px; border-radius: 6px; border: 1px solid var(--border-color);">
                        <span style="color: var(--text-subtle); display: block; font-size: 10.5px;">Login Access:</span>
                        <strong style="color: var(--success);" id="drawerLoginAccess">Allowed</strong>
                    </div>
                    <div style="background: var(--bg-subtle); padding: 10px; border-radius: 6px; border: 1px solid var(--border-color);">
                        <span style="color: var(--text-subtle); display: block; font-size: 10.5px;">2FA Enforcement:</span>
                        <span style="color: var(--text-main); font-weight: 600;">Enabled (2FA)</span>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: RECENT ADMINISTRATOR ACTIVITY -->
            <div style="margin-bottom: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <h4 style="font-size: 11px; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.5px; margin: 0;">Recent Activity Log</h4>
                    <a href="{{ url('/super-admin/tenant-audit') }}" style="font-size: 11.5px; font-weight: 700; color: var(--primary); text-decoration: none;">View Full Audit Trail →</a>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 12px;">
                    <div style="display: flex; justify-content: space-between; padding: 10px 12px; background: var(--bg-subtle); border-radius: 6px; border: 1px solid var(--border-color);">
                        <span><strong style="color: var(--text-main);">Updated employee role permissions</strong></span>
                        <span style="color: var(--text-subtle);">5 mins ago</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 12px; background: var(--bg-subtle); border-radius: 6px; border: 1px solid var(--border-color);">
                        <span><strong style="color: var(--text-main);">Created new employee account</strong></span>
                        <span style="color: var(--text-subtle);">2 hours ago</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 12px; background: var(--bg-subtle); border-radius: 6px; border: 1px solid var(--border-color);">
                        <span><strong style="color: var(--text-main);">Updated company module settings</strong></span>
                        <span style="color: var(--text-subtle);">Yesterday</span>
                    </div>
                </div>
            </div>

            <!-- SECTION 5: ACTION BUTTONS -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; border-top: 1px solid var(--border-color); padding-top: 16px;">
                <a href="#" id="drawerCompanyLink" class="btn-action-secondary" style="justify-content: center;">
                    <i class="bx bx-building"></i> View Company
                </a>
                <button type="button" class="btn-action-secondary" id="drawerEditBtn" style="justify-content: center;">
                    <i class="bx bx-user-pin"></i> Edit Account
                </button>
                
                <form id="archiveForm" method="POST" action="" style="margin: 0;">
                    @csrf
                    @method('PATCH')
                    <button type="button" class="btn-action-secondary" id="triggerArchiveBtn" style="width: 100%; justify-content: center; color: #c2410c;">
                        <i class="bx bx-block"></i> <span id="archiveBtnText">Suspend Admin</span>
                    </button>
                </form>

                <form id="deleteForm" method="POST" action="" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn-action-secondary" id="triggerDeleteBtn" style="width: 100%; justify-content: center; color: var(--danger);">
                        <i class="bx bx-trash"></i> Delete Admin
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- 6. INVITE / CREATE ADMIN MODAL -->
<div class="modal-backdrop" id="inviteAdminModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="bx bx-user-plus" style="color: var(--primary);"></i>
                Create New Company Administrator
            </h3>
            <button class="btn-action-secondary" id="closeInviteModalBtn" style="padding: 4px 8px;">
                <i class="bx bx-x"></i>
            </button>
        </div>
        <form id="createAdminForm" method="POST" action="{{ Route::has('superadmin.admins.store') ? route('superadmin.admins.store') : (Route::has('super-admin.admins.store') ? route('super-admin.admins.store') : (Route::has('admins.store') ? route('admins.store') : url('/company-admins'))) }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Target Company <span style="color: var(--danger);">*</span></label>
                    <select name="company_id" required>
                        <option value="">Select target tenant company</option>
                        @foreach($companyOptions as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Admin Full Name <span style="color: var(--danger);">*</span></label>
                    <input name="name" required placeholder="John Doe" />
                </div>
                <div class="form-group">
                    <label>Admin Login Email <span style="color: var(--danger);">*</span></label>
                    <input name="email" id="createAdminEmail" required type="email" placeholder="admin@company.com" />
                    <div id="createAdminEmail_error" class="field-error-feedback"></div>
                </div>
                <div class="form-group">
                    <label>Initial Account Password <span style="color: var(--danger);">*</span></label>
                    <div style="position: relative;">
                        <input name="password" id="createAdminPassword" required minlength="8" maxlength="128" type="password" placeholder="•••••••• (min 8 chars)" style="padding-right: 40px;" />
                        <button type="button" onclick="togglePasswordVisibility('createAdminPassword', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 18px;" title="Show/Hide Password">
                            <i class="bx bx-show"></i>
                        </button>
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Must be between 8 and 128 characters.</div>
                    <div id="createAdminPassword_error" class="field-error-feedback"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-action-secondary" id="cancelInviteBtn">Cancel</button>
                <button type="submit" class="btn-action-primary">
                    <i class="bx bx-user-plus"></i> Create Admin Account
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 7. EDIT ADMIN MODAL -->
<div class="modal-backdrop" id="editAdminModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="bx bx-user-pin" style="color: var(--primary);"></i>
                Edit Administrator Account
            </h3>
            <button class="btn-action-secondary" id="closeEditModalBtn" style="padding: 4px 8px;">
                <i class="bx bx-x"></i>
            </button>
        </div>
        <form id="editAdminForm" method="POST" action="">
            @csrf
            @method('PATCH')
            <div class="modal-body">
                <div class="form-group">
                    <label>Company Assignment <span style="color: var(--danger);">*</span></label>
                    <select name="company_id" id="editCompanyId" required>
                        @foreach($companyOptions as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Full Name <span style="color: var(--danger);">*</span></label>
                    <input name="name" id="editName" required placeholder="Admin name" />
                </div>
                <div class="form-group">
                    <label>Login Email <span style="color: var(--danger);">*</span></label>
                    <input name="email" id="editEmail" required type="email" placeholder="Admin email" />
                    <div id="editEmail_error" class="field-error-feedback"></div>
                </div>
                <div class="form-group">
                    <label>Password <span style="font-size: 11px; color: var(--text-muted); font-weight: normal;">(Leave blank to keep current)</span></label>
                    <div style="position: relative;">
                        <input name="password" id="editAdminPassword" minlength="8" maxlength="128" type="password" placeholder="New password (min 8 chars)" style="padding-right: 40px;" />
                        <button type="button" onclick="togglePasswordVisibility('editAdminPassword', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 18px;" title="Show/Hide Password">
                            <i class="bx bx-show"></i>
                        </button>
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Leave blank to preserve current password. If updating, must be 8–128 characters.</div>
                    <div id="editAdminPassword_error" class="field-error-feedback"></div>
                </div>
                <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                    <input type="hidden" name="login_allowed" value="0">
                    <input type="checkbox" name="login_allowed" id="editLoginAllowed" value="1" style="width: 18px; height: 18px; cursor: pointer;" />
                    <label for="editLoginAllowed" style="margin: 0; cursor: pointer;">Allow Login Access</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-action-secondary" id="cancelEditBtn">Cancel</button>
                <button type="submit" class="btn-action-primary">
                    <i class="bx bx-check"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 8. CONFIRMATION MODAL -->
<div class="modal-backdrop" id="confirmModal">
    <div class="modal-box" style="max-width: 440px;">
        <div class="modal-header">
            <h3 class="modal-title" id="confirmModalTitle" style="color: var(--danger);">
                <i class="bx bx-error-circle"></i> Confirm Action
            </h3>
            <button class="btn-action-secondary" id="closeConfirmModalBtn" style="padding: 4px 8px;">
                <i class="bx bx-x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p style="font-size: 13.5px; color: var(--text-main); margin: 0;" id="confirmModalDesc">
                Are you sure you want to proceed with this action?
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-action-secondary" id="cancelConfirmBtn">Cancel</button>
            <button type="button" class="btn-action-primary" id="executeConfirmBtn" style="background: var(--danger); border-color: var(--danger);">
                Confirm
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Export Dropdown Menu
    const exportBtn = document.getElementById('exportDropdownBtn');
    const exportMenu = document.getElementById('exportDropdownMenu');
    if (exportBtn && exportMenu) {
        exportBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            exportMenu.style.display = exportMenu.style.display === 'none' || !exportMenu.style.display ? 'block' : 'none';
        });
        document.addEventListener('click', function() { exportMenu.style.display = 'none'; });
    }

    // 2. Slide-Over Drawer Handlers
    const drawerOverlay = document.getElementById('adminDetailsDrawer');
    const closeDrawerBtn = document.getElementById('closeDrawerBtn');
    const editForm = document.getElementById('editAdminForm');
    const archiveForm = document.getElementById('archiveForm');
    const deleteForm = document.getElementById('deleteForm');

    let currentAdminId = null;
    let currentAdminData = {};

    document.querySelectorAll('.open-drawer-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            currentAdminId = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const email = this.getAttribute('data-email');
            const compId = this.getAttribute('data-company-id');
            const compName = this.getAttribute('data-company-name');
            const compCode = this.getAttribute('data-company-code');
            const domain = this.getAttribute('data-domain');
            const role = this.getAttribute('data-role');
            const status = this.getAttribute('data-status');
            const loginAllowed = this.getAttribute('data-login-allowed') === '1';
            const created = this.getAttribute('data-created');
            const lastActive = this.getAttribute('data-last-active');

            currentAdminData = { id: currentAdminId, name, email, compId, compName, compCode, domain, role, status, loginAllowed, created, lastActive };

            document.getElementById('drawerAvatar').textContent = name.substring(0, 2).toUpperCase();
            document.getElementById('drawerAdminName').textContent = name;
            document.getElementById('drawerFullName').textContent = name;
            document.getElementById('drawerAdminRole').textContent = role;
            document.getElementById('drawerMatrixRole').textContent = role;
            document.getElementById('drawerCompanyName').textContent = compName;
            document.getElementById('drawerCompName').textContent = compName;
            document.getElementById('drawerEmail').textContent = email;
            document.getElementById('drawerTenantId').textContent = compCode;
            document.getElementById('drawerCompDomain').textContent = domain;
            document.getElementById('drawerCreated').textContent = created;
            document.getElementById('drawerLastActive').textContent = lastActive;
            document.getElementById('drawerLoginAccess').textContent = loginAllowed ? 'Allowed' : 'Blocked';
            document.getElementById('drawerLoginAccess').style.color = loginAllowed ? 'var(--success)' : 'var(--danger)';

            // Company Link
            document.getElementById('drawerCompanyLink').href = compId ? ('{{ url("/super-admin/companies") }}/' + compId) : '#';

            // Status Badge
            const statusBadge = document.getElementById('drawerStatusBadge');
            const archiveBtnText = document.getElementById('archiveBtnText');
            if (status === 'suspended') {
                statusBadge.className = 'badge-status badge-status-suspended';
                statusBadge.innerHTML = '<i class="bx bx-x-circle"></i> Suspended';
                archiveBtnText.textContent = 'Restore Admin Access';
                archiveForm.action = '{{ url("/company-admins") }}/' + currentAdminId + '/restore';
            } else if (status === 'inactive') {
                statusBadge.className = 'badge-status badge-status-inactive';
                statusBadge.innerHTML = '<i class="bx bx-block"></i> Blocked';
                archiveBtnText.textContent = 'Restore Admin Access';
                archiveForm.action = '{{ url("/company-admins") }}/' + currentAdminId + '/restore';
            } else {
                statusBadge.className = 'badge-status badge-status-active';
                statusBadge.innerHTML = '<i class="bx bx-check-circle"></i> Active';
                archiveBtnText.textContent = 'Suspend Admin';
                archiveForm.action = '{{ url("/company-admins") }}/' + currentAdminId + '/archive';
            }

            deleteForm.action = '{{ url("/company-admins") }}/' + currentAdminId;
            editForm.action = '{{ url("/company-admins") }}/' + currentAdminId;

            // Pre-fill Edit Modal
            document.getElementById('editCompanyId').value = compId || '';
            document.getElementById('editName').value = name;
            document.getElementById('editEmail').value = email;
            document.getElementById('editLoginAllowed').checked = loginAllowed;

            drawerOverlay.classList.add('open');
        });
    });

    closeDrawerBtn?.addEventListener('click', function() { drawerOverlay.classList.remove('open'); });
    drawerOverlay?.addEventListener('click', function(e) { if (e.target === drawerOverlay) drawerOverlay.classList.remove('open'); });

    // 3. Modals Management
    const inviteModal = document.getElementById('inviteAdminModal');
    const editModal = document.getElementById('editAdminModal');
    const confirmModal = document.getElementById('confirmModal');

    document.getElementById('openInviteModalBtn')?.addEventListener('click', function() { inviteModal.classList.add('show'); });
    document.getElementById('closeInviteModalBtn')?.addEventListener('click', function() { inviteModal.classList.remove('show'); });
    document.getElementById('cancelInviteBtn')?.addEventListener('click', function() { inviteModal.classList.remove('show'); });

    const openEditFunc = function() {
        if (!currentAdminData.id) return;
        editModal.classList.add('show');
    };

    document.getElementById('triggerEditBtn')?.addEventListener('click', openEditFunc);
    document.getElementById('drawerEditBtn')?.addEventListener('click', openEditFunc);
    document.getElementById('closeEditModalBtn')?.addEventListener('click', function() { editModal.classList.remove('show'); });
    document.getElementById('cancelEditBtn')?.addEventListener('click', function() { editModal.classList.remove('show'); });

    // Confirmation Modals for Suspend / Delete
    let pendingConfirmAction = null;

    document.getElementById('triggerArchiveBtn')?.addEventListener('click', function() {
        const isRestoring = currentAdminData.status === 'suspended' || currentAdminData.status === 'inactive';
        document.getElementById('confirmModalTitle').textContent = isRestoring ? 'Restore Administrator Access?' : 'Suspend Administrator?';
        document.getElementById('confirmModalTitle').style.color = isRestoring ? 'var(--success)' : 'var(--danger)';
        document.getElementById('confirmModalDesc').textContent = isRestoring 
            ? `Re-enable administrative access for ${currentAdminData.name}?` 
            : `${currentAdminData.name} will no longer be able to log into ${currentAdminData.compName}.`;
        pendingConfirmAction = function() { archiveForm.submit(); };
        confirmModal.classList.add('show');
    });

    document.getElementById('triggerDeleteBtn')?.addEventListener('click', function() {
        document.getElementById('confirmModalTitle').textContent = 'Delete Administrator Account?';
        document.getElementById('confirmModalTitle').style.color = 'var(--danger)';
        document.getElementById('confirmModalDesc').textContent = `Permanently delete administrator account for ${currentAdminData.name}? This action cannot be undone.`;
        pendingConfirmAction = function() { deleteForm.submit(); };
        confirmModal.classList.add('show');
    });

    document.getElementById('closeConfirmModalBtn')?.addEventListener('click', function() { confirmModal.classList.remove('show'); });
    document.getElementById('cancelConfirmBtn')?.addEventListener('click', function() { confirmModal.classList.remove('show'); });
    document.getElementById('executeConfirmBtn')?.addEventListener('click', function() {
        if (pendingConfirmAction) pendingConfirmAction();
    });

    // 4. Click-to-filter on KPI Cards
    window.applyKpiStatusFilter = function(status) {
        document.querySelectorAll('.kpi-card').forEach(c => c.classList.remove('active-kpi-filter'));
        const statusFilter = document.getElementById('statusFilter');

        if (status === 'all') {
            document.getElementById('kpiTotalCard').classList.add('active-kpi-filter');
            statusFilter.value = 'all';
        } else if (status === 'active') {
            document.getElementById('kpiActiveCard').classList.add('active-kpi-filter');
            statusFilter.value = 'active';
        } else if (status === 'pending') {
            document.getElementById('kpiPendingCard').classList.add('active-kpi-filter');
            statusFilter.value = 'pending';
        } else if (status === 'archived') {
            document.getElementById('kpiSuspendedCard').classList.add('active-kpi-filter');
            statusFilter.value = 'archived';
        }

        document.getElementById('filterForm').submit();
    };

    window.clearAllFilters = function() {
        window.location.href = '{{ Route::has("superadmin.admins.index") ? route("superadmin.admins.index") : (Route::has("super-admin.admins.index") ? route("super-admin.admins.index") : (Route::has("admins.index") ? route("admins.index") : url("/company-admins"))) }}';
    };

    // Column Sorting
    window.sortTable = function(colIdx) {
        const table = document.getElementById('adminTable');
        const tbody = table.querySelector('tbody');
        const rowsArr = Array.from(tbody.querySelectorAll('tr.admin-row'));
        if (rowsArr.length === 0) return;
        
        let asc = table.getAttribute('data-sort-dir') !== 'asc';
        table.setAttribute('data-sort-dir', asc ? 'asc' : 'desc');

        rowsArr.sort((a, b) => {
            const valA = a.children[colIdx]?.innerText.trim() || '';
            const valB = b.children[colIdx]?.innerText.trim() || '';
            return asc ? valA.localeCompare(valB) : valB.localeCompare(valA);
        });

        rowsArr.forEach(r => tbody.appendChild(r));
    };

    // Strict Email Validation for Company Admins
    function validateAdminEmailFormat(email, isRequired = true) {
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

    function setupAdminEmailValidator(inputId, errorDivId) {
        const input = document.getElementById(inputId);
        const errorDiv = document.getElementById(errorDivId);
        if (!input) return null;

        function validate(showEmptyError = true) {
            const val = input.value.trim();
            const res = validateAdminEmailFormat(val, true);
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

    window.togglePasswordVisibility = function(inputId, btnEl) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const icon = btnEl?.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) { icon.classList.remove('bx-show'); icon.classList.add('bx-hide'); }
        } else {
            input.type = 'password';
            if (icon) { icon.classList.remove('bx-hide'); icon.classList.add('bx-show'); }
        }
    };

    function validateAdminPasswordField(inputId, errorId, isRequired = true) {
        const input = document.getElementById(inputId);
        const errorDiv = document.getElementById(errorId);
        if (!input) return true;
        const val = input.value;
        if (!val) {
            if (isRequired) {
                if (errorDiv) { errorDiv.textContent = 'Password is required.'; errorDiv.classList.add('visible'); errorDiv.style.display = 'block'; }
                input.classList.add('is-invalid');
                return false;
            }
            if (errorDiv) { errorDiv.textContent = ''; errorDiv.classList.remove('visible'); errorDiv.style.display = 'none'; }
            input.classList.remove('is-invalid');
            return true;
        }
        if (val.length < 8) {
            if (errorDiv) { errorDiv.textContent = 'Password must be at least 8 characters long.'; errorDiv.classList.add('visible'); errorDiv.style.display = 'block'; }
            input.classList.add('is-invalid');
            return false;
        }
        if (val.length > 128) {
            if (errorDiv) { errorDiv.textContent = 'Password cannot exceed 128 characters.'; errorDiv.classList.add('visible'); errorDiv.style.display = 'block'; }
            input.classList.add('is-invalid');
            return false;
        }
        if (errorDiv) { errorDiv.textContent = ''; errorDiv.classList.remove('visible'); errorDiv.style.display = 'none'; }
        input.classList.remove('is-invalid');
        return true;
    }

    const createAdminPwdInput = document.getElementById('createAdminPassword');
    if (createAdminPwdInput) {
        createAdminPwdInput.addEventListener('input', function() { validateAdminPasswordField('createAdminPassword', 'createAdminPassword_error', true); });
        createAdminPwdInput.addEventListener('blur', function() { validateAdminPasswordField('createAdminPassword', 'createAdminPassword_error', true); });
    }

    const editAdminPwdInput = document.getElementById('editAdminPassword');
    if (editAdminPwdInput) {
        editAdminPwdInput.addEventListener('input', function() { validateAdminPasswordField('editAdminPassword', 'editAdminPassword_error', false); });
        editAdminPwdInput.addEventListener('blur', function() { validateAdminPasswordField('editAdminPassword', 'editAdminPassword_error', false); });
    }

    const createAdminEmailVal = setupAdminEmailValidator('createAdminEmail', 'createAdminEmail_error');
    const editAdminEmailVal = setupAdminEmailValidator('editEmail', 'editEmail_error');

    const createAdminForm = document.getElementById('createAdminForm');
    if (createAdminForm) {
        createAdminForm.addEventListener('submit', function(e) {
            const isEmailValid = createAdminEmailVal ? createAdminEmailVal.validate(true) : true;
            const isPwdValid = validateAdminPasswordField('createAdminPassword', 'createAdminPassword_error', true);
            if (!isEmailValid || !isPwdValid) {
                e.preventDefault();
                if (!isEmailValid && createAdminEmailVal) createAdminEmailVal.input.focus();
                else if (!isPwdValid && createAdminPwdInput) createAdminPwdInput.focus();
                return false;
            }
        });
    }

    const editAdminForm = document.getElementById('editAdminForm');
    if (editAdminForm) {
        editAdminForm.addEventListener('submit', function(e) {
            const isEmailValid = editAdminEmailVal ? editAdminEmailVal.validate(true) : true;
            const isPwdValid = validateAdminPasswordField('editAdminPassword', 'editAdminPassword_error', false);
            if (!isEmailValid || !isPwdValid) {
                e.preventDefault();
                if (!isEmailValid && editAdminEmailVal) editAdminEmailVal.input.focus();
                else if (!isPwdValid && editAdminPwdInput) editAdminPwdInput.focus();
                return false;
            }
        });
    }
});
</script>
@endpush
