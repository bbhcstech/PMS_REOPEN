@extends('admin.layout.app')

@section('title', 'Admin Settings Dashboard')

@push('styles')
<style>
    .settings-hub-page {
        --sh-bg: #f8fafc;
        --sh-surface: #ffffff;
        --sh-surface-soft: #f8fafc;
        --sh-surface-muted: #f1f5f9;
        --sh-border: rgba(226, 232, 240, 0.9);
        --sh-border-subtle: #f1f5f9;
        --sh-text: #1e293b;
        --sh-heading: #0f172a;
        --sh-muted: #64748b;
        --sh-subtle: #94a3b8;
        --sh-brand: #2563eb;
        --sh-brand-hover: #1d4ed8;
        --sh-brand-soft: #eff6ff;
        --sh-brand-border: #dbeafe;
        --sh-shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.03);
        --sh-shadow-md: 0 12px 26px -6px rgba(15, 23, 42, 0.08), 0 4px 10px -4px rgba(15, 23, 42, 0.04);
        --sh-shadow-brand: 0 6px 16px -3px rgba(37, 99, 235, 0.28);

        min-height: calc(100vh - 96px);
        padding: 1.75rem 1.5rem 2.75rem;
        background: var(--sh-bg);
        color: var(--sh-text);
        font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif;
    }

    .settings-hub-page *,
    .settings-hub-page *::before,
    .settings-hub-page *::after {
        box-sizing: border-box;
    }

    .sh-shell {
        max-width: 1280px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* Breadcrumb & Status Topbar */
    .sh-topbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem 1rem;
    }

    .sh-breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        font-size: 0.78rem;
        font-weight: 500;
        color: var(--sh-muted);
    }

    .sh-breadcrumb a {
        color: var(--sh-muted);
        text-decoration: none;
        transition: color 0.15s ease;
    }

    .sh-breadcrumb a:hover {
        color: var(--sh-brand);
    }

    .sh-breadcrumb-sep {
        color: #cbd5e1;
    }

    .sh-breadcrumb-current {
        color: var(--sh-brand-hover);
        background: var(--sh-brand-soft);
        border: 1px solid var(--sh-brand-border);
        padding: 0.2rem 0.6rem;
        border-radius: 0.45rem;
        font-weight: 600;
    }

    .sh-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.85rem;
        border-radius: 9999px;
        background: var(--sh-surface);
        border: 1px solid var(--sh-border);
        box-shadow: var(--sh-shadow-sm);
        font-size: 0.74rem;
        font-weight: 600;
        color: var(--sh-muted);
        letter-spacing: 0.03em;
    }

    .sh-pulse-dot {
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 9999px;
        background: #10b981;
        display: inline-block;
        animation: shPulse 2s ease-in-out infinite;
    }

    @keyframes shPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.45; transform: scale(0.9); }
    }

    /* Header Card */
    .sh-header-card {
        background: var(--sh-surface);
        border: 1px solid var(--sh-border);
        border-radius: 1rem;
        padding: 1.5rem 1.75rem;
        box-shadow: var(--sh-shadow-sm);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
    }

    .sh-header-left {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        min-width: 0;
        flex: 1 1 320px;
    }

    .sh-header-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.75rem;
        background: var(--sh-brand-soft);
        border: 1px solid var(--sh-brand-border);
        color: var(--sh-brand-hover);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: var(--sh-shadow-sm);
    }

    .sh-header-icon svg {
        width: 1.5rem;
        height: 1.5rem;
    }

    .sh-header-title-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
    }

    .sh-header-title-row h1 {
        margin: 0;
        font-size: 1.5rem;
        line-height: 1.25;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--sh-heading);
    }

    .sh-active-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.22rem 0.65rem;
        border-radius: 9999px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #047857;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .sh-active-badge-dot {
        width: 0.4rem;
        height: 0.4rem;
        border-radius: 9999px;
        background: #10b981;
    }

    .sh-header-subtitle {
        margin: 0.35rem 0 0;
        font-size: 0.875rem;
        color: var(--sh-muted);
        line-height: 1.45;
    }

    .sh-header-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.65rem;
        flex-shrink: 0;
    }

    .sh-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border-radius: 0.75rem;
        font-size: 0.78rem;
        font-weight: 600;
        line-height: 1;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.18s ease;
        white-space: nowrap;
    }

    .sh-btn svg {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
    }

    .sh-btn-secondary {
        padding: 0.68rem 1rem;
        background: var(--sh-surface);
        border: 1px solid var(--sh-border);
        color: #334155 !important;
        box-shadow: var(--sh-shadow-sm);
    }

    .sh-btn-secondary svg {
        color: #94a3b8;
    }

    .sh-btn-secondary:hover {
        background: var(--sh-surface-soft);
        border-color: #cbd5e1;
        color: var(--sh-heading) !important;
        transform: translateY(-1px);
    }

    .sh-btn-primary {
        padding: 0.72rem 1.25rem;
        background: var(--sh-brand);
        border: 1px solid var(--sh-brand);
        color: #ffffff !important;
        box-shadow: var(--sh-shadow-brand);
    }

    .sh-btn-primary:hover {
        background: var(--sh-brand-hover);
        border-color: var(--sh-brand-hover);
        color: #ffffff !important;
        transform: translateY(-1px);
    }

    /* Alert */
    .sh-alert-success {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.9rem 1.1rem;
        border-radius: 0.875rem;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        font-size: 0.85rem;
        font-weight: 500;
        box-shadow: var(--sh-shadow-sm);
    }

    /* 4 KPI Summary Cards */
    .sh-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.25rem;
    }

    .sh-kpi-card {
        background: var(--sh-surface);
        border: 1px solid var(--sh-border);
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: var(--sh-shadow-sm);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 0.9rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .sh-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--sh-shadow-md);
        border-color: #cbd5e1;
    }

    .sh-kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .sh-kpi-label {
        font-size: 0.69rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--sh-subtle);
    }

    .sh-kpi-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sh-kpi-icon svg {
        width: 1.2rem;
        height: 1.2rem;
    }

    .sh-kpi-brand {
        background: #eff6ff;
        border: 1px solid #dbeafe;
        color: #2563eb;
    }

    .sh-kpi-indigo {
        background: #eef2ff;
        border: 1px solid #e0e7ff;
        color: #4f46e5;
    }

    .sh-kpi-blue {
        background: #ecfeff;
        border: 1px solid #cffafe;
        color: #0284c7;
    }

    .sh-kpi-purple {
        background: #faf5ff;
        border: 1px solid #f3e8ff;
        color: #7e22ce;
    }

    .sh-kpi-value {
        font-size: 1.5rem;
        line-height: 1.15;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--sh-heading);
    }

    .sh-kpi-hint {
        margin: 0.25rem 0 0;
        font-size: 0.76rem;
        font-weight: 500;
        color: var(--sh-muted);
    }

    /* Filter & Search Bar */
    .sh-toolbar-card {
        background: var(--sh-surface);
        border: 1px solid var(--sh-border);
        border-radius: 1rem;
        padding: 1rem 1.25rem;
        box-shadow: var(--sh-shadow-sm);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.9rem 1.25rem;
    }

    .sh-filter-pills {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem;
    }

    .sh-filter-btn {
        border: 1px solid var(--sh-border);
        background: var(--sh-surface-soft);
        color: #475569;
        font-size: 0.76rem;
        font-weight: 600;
        padding: 0.48rem 0.9rem;
        border-radius: 0.65rem;
        cursor: pointer;
        transition: all 0.16s ease;
        line-height: 1.1;
    }

    .sh-filter-btn:hover {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: var(--sh-brand-hover);
    }

    .sh-filter-btn.active {
        background: var(--sh-brand);
        border-color: var(--sh-brand);
        color: #ffffff;
        box-shadow: 0 4px 12px -2px rgba(37, 99, 235, 0.35);
    }

    .sh-search-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex: 0 1 340px;
        min-width: 240px;
        margin-left: auto;
    }

    .sh-search-wrap {
        position: relative;
        flex: 1;
        display: flex;
        align-items: center;
    }

    .sh-search-icon {
        position: absolute;
        left: 0.85rem;
        color: var(--sh-subtle);
        pointer-events: none;
        display: inline-flex;
    }

    .sh-search-icon svg {
        width: 0.95rem;
        height: 0.95rem;
    }

    .sh-search-input {
        width: 100%;
        border-radius: 0.75rem;
        background: var(--sh-surface-soft);
        border: 1px solid var(--sh-border);
        color: var(--sh-heading);
        font-size: 0.8rem;
        font-weight: 500;
        padding: 0.56rem 0.9rem 0.56rem 2.35rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
    }

    .sh-search-input::placeholder {
        color: var(--sh-subtle);
    }

    .sh-search-input:focus {
        outline: none;
        background: var(--sh-surface);
        border-color: var(--sh-brand);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
    }

    .sh-result-count {
        font-size: 0.74rem;
        font-weight: 600;
        color: var(--sh-muted);
        white-space: nowrap;
    }

    /* Settings Modules Grid */
    .sh-modules-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.25rem;
    }

    .setting-card-item {
        position: relative;
        min-width: 0;
    }

    .setting-card-item.is-dropdown-open {
        z-index: 40;
    }

    .sh-module-card {
        height: 100%;
        background: var(--sh-surface);
        border: 1px solid var(--sh-border);
        border-radius: 1rem;
        padding: 1.35rem;
        box-shadow: var(--sh-shadow-sm);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 1.1rem;
        text-decoration: none;
        color: inherit;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        cursor: pointer;
    }

    .sh-module-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--sh-shadow-md);
        border-color: #bfdbfe;
    }

    .sh-module-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .setting-icon-box {
        width: 2.9rem;
        height: 2.9rem;
        border-radius: 0.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.35rem;
        transition: transform 0.2s ease;
    }

    .sh-module-card:hover .setting-icon-box {
        transform: scale(1.06);
    }

    .sh-cat-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.24rem 0.6rem;
        border-radius: 9999px;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        white-space: nowrap;
    }

    /* Color Accents */
    .sh-tone-primary {
        background: #eff6ff;
        border: 1px solid #dbeafe;
        color: #2563eb;
    }
    .sh-tone-primary i { color: #2563eb !important; -webkit-text-fill-color: #2563eb !important; }

    .sh-tone-info {
        background: #ecfeff;
        border: 1px solid #cffafe;
        color: #0284c7;
    }
    .sh-tone-info i { color: #0284c7 !important; -webkit-text-fill-color: #0284c7 !important; }

    .sh-tone-success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #059669;
    }
    .sh-tone-success i { color: #059669 !important; -webkit-text-fill-color: #059669 !important; }

    .sh-tone-warning {
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #d97706;
    }
    .sh-tone-warning i { color: #d97706 !important; -webkit-text-fill-color: #d97706 !important; }

    .sh-tone-danger {
        background: #fff1f2;
        border: 1px solid #fecdd3;
        color: #e11d48;
    }
    .sh-tone-danger i { color: #e11d48 !important; -webkit-text-fill-color: #e11d48 !important; }

    .sh-tone-secondary {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #475569;
    }
    .sh-tone-secondary i { color: #475569 !important; -webkit-text-fill-color: #475569 !important; }

    .sh-module-body h3 {
        margin: 0 0 0.4rem;
        font-size: 0.96rem;
        font-weight: 700;
        line-height: 1.3;
        letter-spacing: -0.01em;
        color: var(--sh-heading);
    }

    .sh-module-body p {
        margin: 0;
        font-size: 0.79rem;
        line-height: 1.5;
        color: var(--sh-muted);
    }

    .sh-module-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 0.85rem;
        border-top: 1px solid var(--sh-border-subtle);
        font-size: 0.76rem;
        font-weight: 600;
        color: var(--sh-brand);
    }

    .sh-manage-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: var(--sh-brand);
        transition: gap 0.16s ease;
    }

    .sh-module-card:hover .sh-manage-pill {
        gap: 0.55rem;
    }

    .sh-module-card.dropdown-toggle::after {
        display: none !important;
    }

    /* Department Dropdown Menu */
    .sh-dropdown-menu {
        min-width: 265px;
        width: 100%;
        padding: 0.5rem;
        margin-top: 0.45rem !important;
        border-radius: 0.95rem !important;
        background: var(--sh-surface) !important;
        border: 1px solid var(--sh-border) !important;
        box-shadow: 0 18px 36px -8px rgba(15, 23, 42, 0.18) !important;
    }

    .sh-dropdown-header {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--sh-subtle);
        padding: 0.4rem 0.65rem;
    }

    .sh-dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.6rem 0.7rem !important;
        border-radius: 0.65rem !important;
        text-decoration: none;
        transition: background-color 0.15s ease;
    }

    .sh-dropdown-item:hover {
        background: var(--sh-surface-soft) !important;
    }

    .sh-dropdown-item-title {
        font-size: 0.84rem;
        font-weight: 700;
        color: var(--sh-heading);
        line-height: 1.2;
    }

    .sh-dropdown-item-desc {
        font-size: 0.72rem;
        color: var(--sh-muted);
        display: block;
        margin-top: 0.15rem;
    }

    /* Empty / Placeholder States */
    .sh-empty-card {
        background: var(--sh-surface);
        border: 1px dashed var(--sh-border);
        border-radius: 1rem;
        padding: 2.25rem 1.5rem;
        text-align: center;
        box-shadow: var(--sh-shadow-sm);
    }

    .sh-empty-card h4 {
        margin: 0 0 0.4rem;
        font-size: 1rem;
        font-weight: 700;
        color: var(--sh-heading);
    }

    .sh-empty-card p {
        margin: 0;
        font-size: 0.84rem;
        color: var(--sh-muted);
    }

    /* System Status Footer Card */
    .sh-footer-card {
        background: var(--sh-surface);
        border: 1px solid var(--sh-border);
        border-radius: 1rem;
        padding: 1.35rem 1.5rem;
        box-shadow: var(--sh-shadow-sm);
        display: flex;
        flex-direction: column;
        gap: 1.15rem;
    }

    .sh-footer-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding-bottom: 0.9rem;
        border-bottom: 1px solid var(--sh-border-subtle);
    }

    .sh-footer-title {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        margin: 0;
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--sh-heading);
    }

    .sh-footer-title svg {
        width: 1.1rem;
        height: 1.1rem;
        color: #10b981;
    }

    .sh-footer-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }

    .sh-footer-stat {
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        background: var(--sh-surface-soft);
        border: 1px solid var(--sh-border);
        text-align: center;
    }

    .sh-footer-stat strong {
        display: block;
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.2;
        color: var(--sh-heading);
    }

    .sh-footer-stat span {
        display: block;
        margin-top: 0.2rem;
        font-size: 0.74rem;
        font-weight: 500;
        color: var(--sh-muted);
    }

    /* Responsive Breakpoints */
    @media (max-width: 1200px) {
        .sh-kpi-grid,
        .sh-modules-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 992px) {
        .sh-kpi-grid,
        .sh-modules-grid,
        .sh-footer-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .sh-search-group {
            flex: 1 1 100%;
            margin-left: 0;
        }
    }

    @media (max-width: 600px) {
        .settings-hub-page {
            padding: 1rem;
        }

        .sh-header-card,
        .sh-toolbar-card,
        .sh-footer-card {
            padding: 1.15rem;
        }

        .sh-kpi-grid,
        .sh-modules-grid,
        .sh-footer-stats {
            grid-template-columns: 1fr;
        }
    }

    /* Dark Mode Overrides */
    html[data-pms-theme="dark"] .settings-hub-page,
    html[data-theme="dark"] .settings-hub-page,
    html[data-bs-theme="dark"] .settings-hub-page,
    body[data-pms-theme="dark"] .settings-hub-page,
    body[data-theme="dark"] .settings-hub-page,
    body[data-bs-theme="dark"] .settings-hub-page,
    .dark-mode .settings-hub-page {
        --sh-bg: #0b1120;
        --sh-surface: #111827;
        --sh-surface-soft: #1e293b;
        --sh-surface-muted: #1e293b;
        --sh-border: rgba(148, 163, 184, 0.18);
        --sh-border-subtle: rgba(148, 163, 184, 0.14);
        --sh-text: #e2e8f0;
        --sh-heading: #f8fafc;
        --sh-muted: #94a3b8;
        --sh-subtle: #94a3b8;
        --sh-brand-soft: rgba(37, 99, 235, 0.18);
        --sh-brand-border: rgba(96, 165, 250, 0.35);
    }

    html[data-pms-theme="dark"] .settings-hub-page .sh-btn-secondary,
    html[data-theme="dark"] .settings-hub-page .sh-btn-secondary,
    html[data-bs-theme="dark"] .settings-hub-page .sh-btn-secondary,
    .dark-mode .settings-hub-page .sh-btn-secondary,
    html[data-pms-theme="dark"] .settings-hub-page .sh-filter-btn:not(.active),
    html[data-theme="dark"] .settings-hub-page .sh-filter-btn:not(.active),
    html[data-bs-theme="dark"] .settings-hub-page .sh-filter-btn:not(.active),
    .dark-mode .settings-hub-page .sh-filter-btn:not(.active) {
        background: #1e293b;
        color: #e2e8f0 !important;
        border-color: rgba(148, 163, 184, 0.24);
    }

    html[data-pms-theme="dark"] .settings-hub-page .sh-breadcrumb-current,
    html[data-theme="dark"] .settings-hub-page .sh-breadcrumb-current,
    html[data-bs-theme="dark"] .settings-hub-page .sh-breadcrumb-current,
    .dark-mode .settings-hub-page .sh-breadcrumb-current,
    html[data-pms-theme="dark"] .settings-hub-page .sh-module-footer,
    html[data-theme="dark"] .settings-hub-page .sh-module-footer,
    html[data-bs-theme="dark"] .settings-hub-page .sh-module-footer,
    .dark-mode .settings-hub-page .sh-module-footer,
    html[data-pms-theme="dark"] .settings-hub-page .sh-manage-pill,
    html[data-theme="dark"] .settings-hub-page .sh-manage-pill,
    html[data-bs-theme="dark"] .settings-hub-page .sh-manage-pill,
    .dark-mode .settings-hub-page .sh-manage-pill {
        color: #93c5fd;
    }

    html[data-pms-theme="dark"] .settings-hub-page .sh-active-badge,
    html[data-theme="dark"] .settings-hub-page .sh-active-badge,
    html[data-bs-theme="dark"] .settings-hub-page .sh-active-badge,
    .dark-mode .settings-hub-page .sh-active-badge {
        background: rgba(16, 185, 129, 0.16);
        border-color: rgba(52, 211, 153, 0.35);
        color: #6ee7b7;
    }

    html[data-pms-theme="dark"] .settings-hub-page .sh-kpi-brand,
    html[data-theme="dark"] .settings-hub-page .sh-kpi-brand,
    html[data-bs-theme="dark"] .settings-hub-page .sh-kpi-brand,
    .dark-mode .settings-hub-page .sh-kpi-brand,
    html[data-pms-theme="dark"] .settings-hub-page .sh-header-icon,
    html[data-theme="dark"] .settings-hub-page .sh-header-icon,
    html[data-bs-theme="dark"] .settings-hub-page .sh-header-icon,
    .dark-mode .settings-hub-page .sh-header-icon,
    html[data-pms-theme="dark"] .settings-hub-page .sh-tone-primary,
    html[data-theme="dark"] .settings-hub-page .sh-tone-primary,
    html[data-bs-theme="dark"] .settings-hub-page .sh-tone-primary,
    .dark-mode .settings-hub-page .sh-tone-primary {
        background: rgba(37, 99, 235, 0.22);
        border-color: rgba(96, 165, 250, 0.35);
        color: #93c5fd;
    }
    html[data-pms-theme="dark"] .settings-hub-page .sh-tone-primary i,
    html[data-theme="dark"] .settings-hub-page .sh-tone-primary i,
    html[data-bs-theme="dark"] .settings-hub-page .sh-tone-primary i,
    .dark-mode .settings-hub-page .sh-tone-primary i {
        color: #93c5fd !important;
        -webkit-text-fill-color: #93c5fd !important;
    }

    html[data-pms-theme="dark"] .settings-hub-page .sh-kpi-indigo,
    html[data-theme="dark"] .settings-hub-page .sh-kpi-indigo,
    html[data-bs-theme="dark"] .settings-hub-page .sh-kpi-indigo,
    .dark-mode .settings-hub-page .sh-kpi-indigo {
        background: rgba(99, 102, 241, 0.22);
        border-color: rgba(129, 140, 248, 0.35);
        color: #a5b4fc;
    }

    html[data-pms-theme="dark"] .settings-hub-page .sh-kpi-blue,
    html[data-theme="dark"] .settings-hub-page .sh-kpi-blue,
    html[data-bs-theme="dark"] .settings-hub-page .sh-kpi-blue,
    .dark-mode .settings-hub-page .sh-kpi-blue,
    html[data-pms-theme="dark"] .settings-hub-page .sh-tone-info,
    html[data-theme="dark"] .settings-hub-page .sh-tone-info,
    html[data-bs-theme="dark"] .settings-hub-page .sh-tone-info,
    .dark-mode .settings-hub-page .sh-tone-info {
        background: rgba(14, 165, 233, 0.22);
        border-color: rgba(56, 189, 248, 0.35);
        color: #7dd3fc;
    }
    html[data-pms-theme="dark"] .settings-hub-page .sh-tone-info i,
    html[data-theme="dark"] .settings-hub-page .sh-tone-info i,
    html[data-bs-theme="dark"] .settings-hub-page .sh-tone-info i,
    .dark-mode .settings-hub-page .sh-tone-info i {
        color: #7dd3fc !important;
        -webkit-text-fill-color: #7dd3fc !important;
    }

    html[data-pms-theme="dark"] .settings-hub-page .sh-kpi-purple,
    html[data-theme="dark"] .settings-hub-page .sh-kpi-purple,
    html[data-bs-theme="dark"] .settings-hub-page .sh-kpi-purple,
    .dark-mode .settings-hub-page .sh-kpi-purple {
        background: rgba(168, 85, 247, 0.22);
        border-color: rgba(192, 132, 252, 0.35);
        color: #d8b4fe;
    }

    html[data-pms-theme="dark"] .settings-hub-page .sh-tone-success,
    html[data-theme="dark"] .settings-hub-page .sh-tone-success,
    html[data-bs-theme="dark"] .settings-hub-page .sh-tone-success,
    .dark-mode .settings-hub-page .sh-tone-success {
        background: rgba(16, 185, 129, 0.2);
        border-color: rgba(52, 211, 153, 0.35);
        color: #6ee7b7;
    }
    html[data-pms-theme="dark"] .settings-hub-page .sh-tone-success i,
    html[data-theme="dark"] .settings-hub-page .sh-tone-success i,
    html[data-bs-theme="dark"] .settings-hub-page .sh-tone-success i,
    .dark-mode .settings-hub-page .sh-tone-success i {
        color: #6ee7b7 !important;
        -webkit-text-fill-color: #6ee7b7 !important;
    }

    html[data-pms-theme="dark"] .settings-hub-page .sh-tone-warning,
    html[data-theme="dark"] .settings-hub-page .sh-tone-warning,
    html[data-bs-theme="dark"] .settings-hub-page .sh-tone-warning,
    .dark-mode .settings-hub-page .sh-tone-warning {
        background: rgba(245, 158, 11, 0.2);
        border-color: rgba(251, 191, 36, 0.35);
        color: #fcd34d;
    }
    html[data-pms-theme="dark"] .settings-hub-page .sh-tone-warning i,
    html[data-theme="dark"] .settings-hub-page .sh-tone-warning i,
    html[data-bs-theme="dark"] .settings-hub-page .sh-tone-warning i,
    .dark-mode .settings-hub-page .sh-tone-warning i {
        color: #fcd34d !important;
        -webkit-text-fill-color: #fcd34d !important;
    }

    html[data-pms-theme="dark"] .settings-hub-page .sh-tone-danger,
    html[data-theme="dark"] .settings-hub-page .sh-tone-danger,
    html[data-bs-theme="dark"] .settings-hub-page .sh-tone-danger,
    .dark-mode .settings-hub-page .sh-tone-danger {
        background: rgba(244, 63, 94, 0.2);
        border-color: rgba(251, 113, 133, 0.35);
        color: #fda4af;
    }
    html[data-pms-theme="dark"] .settings-hub-page .sh-tone-danger i,
    html[data-theme="dark"] .settings-hub-page .sh-tone-danger i,
    html[data-bs-theme="dark"] .settings-hub-page .sh-tone-danger i,
    .dark-mode .settings-hub-page .sh-tone-danger i {
        color: #fda4af !important;
        -webkit-text-fill-color: #fda4af !important;
    }
</style>
@endpush

@section('content')
@php
    $groupsCollection = collect($settingsGroups);
    $totalModulesCount = $groupsCollection->count();
    $businessOrgCount = $groupsCollection->whereIn('category', ['Business', 'Organization'])->count();
    $hrCount = $groupsCollection->where('category', 'HR')->count();
    $systemCount = $groupsCollection->where('category', 'System')->count();
    $securityCount = $groupsCollection->where('category', 'Security')->count();
@endphp

<div class="settings-hub-page">
    <div class="sh-shell">
        <!-- Breadcrumb & Live Status Bar -->
        <div class="sh-topbar">
            <nav aria-label="Breadcrumb" class="sh-breadcrumb">
                <a href="{{ route('dashboard') }}">Admin</a>
                <span class="sh-breadcrumb-sep">/</span>
                <span class="sh-breadcrumb-current">Settings Dashboard</span>
            </nav>
            <div class="sh-status-pill">
                <span class="sh-pulse-dot" aria-hidden="true"></span>
                <span>WORKSPACE HUB • {{ $totalModulesCount }} MODULES ACTIVE</span>
            </div>
        </div>

        <!-- Header Card -->
        <section class="sh-header-card">
            <div class="sh-header-left">
                <div class="sh-header-icon" aria-hidden="true">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09a1.65 1.65 0 001.51-1 1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </div>
                <div style="min-width: 0;">
                    <div class="sh-header-title-row">
                        <h1>Settings Dashboard</h1>
                        <span class="sh-active-badge">
                            <span class="sh-active-badge-dot" aria-hidden="true"></span>
                            All {{ $totalModulesCount }} Active
                        </span>
                    </div>
                    <p class="sh-header-subtitle">
                        Manage and configure all admin settings modules from a single workspace.
                    </p>
                </div>
            </div>

            <div class="sh-header-actions">
                <button type="button" class="sh-btn sh-btn-secondary" onclick="location.reload()">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <path d="M20.49 15a9 9 0 11-2.12-9.36L23 10" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span>Refresh</span>
                </button>
                <a href="{{ route('dashboard') }}" class="sh-btn sh-btn-primary">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke-linecap="round" stroke-linejoin="round"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    <span>Dashboard</span>
                </a>
            </div>
        </section>

        <!-- Alert Notifications -->
        @if(session('success'))
            <div class="sh-alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bx-check-circle fs-4"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- KPI Summary Row (4 Cards) -->
        <section class="sh-kpi-grid" aria-label="Settings overview">
            <div class="sh-kpi-card">
                <div class="sh-kpi-top">
                    <span class="sh-kpi-label">Business &amp; Org</span>
                    <div class="sh-kpi-icon sh-kpi-brand" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1M14 9h1M9 13h1M14 13h1" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="sh-kpi-value">{{ $businessOrgCount }}</div>
                    <p class="sh-kpi-hint">Company, branches &amp; structure</p>
                </div>
            </div>

            <div class="sh-kpi-card">
                <div class="sh-kpi-top">
                    <span class="sh-kpi-label">HR &amp; Policies</span>
                    <div class="sh-kpi-icon sh-kpi-indigo" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="sh-kpi-value">{{ $hrCount }}</div>
                    <p class="sh-kpi-hint">Schedule, leave, attendance &amp; reviews</p>
                </div>
            </div>

            <div class="sh-kpi-card">
                <div class="sh-kpi-top">
                    <span class="sh-kpi-label">System &amp; Comms</span>
                    <div class="sh-kpi-icon sh-kpi-blue" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="sh-kpi-value">{{ $systemCount }}</div>
                    <p class="sh-kpi-hint">Notifications, SMTP &amp; localization</p>
                </div>
            </div>

            <div class="sh-kpi-card">
                <div class="sh-kpi-top">
                    <span class="sh-kpi-label">Security &amp; Access</span>
                    <div class="sh-kpi-icon sh-kpi-purple" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="sh-kpi-value">{{ $securityCount }}</div>
                    <p class="sh-kpi-hint">Security rules, passwords &amp; roles</p>
                </div>
            </div>
        </section>

        <!-- Category Filter & Search Card -->
        <section class="sh-toolbar-card" data-live-preserve>
            <div class="sh-filter-pills" role="group" aria-label="Filter settings by category" id="settingsCategoryFilter">
                <button type="button" class="sh-filter-btn active" data-filter="all">All Settings ({{ $totalModulesCount }})</button>
                <button type="button" class="sh-filter-btn" data-filter="Business">Business</button>
                <button type="button" class="sh-filter-btn" data-filter="Organization">Organization</button>
                <button type="button" class="sh-filter-btn" data-filter="HR">HR &amp; Employees</button>
                <button type="button" class="sh-filter-btn" data-filter="Finance">Finance</button>
                <button type="button" class="sh-filter-btn" data-filter="System">System</button>
                <button type="button" class="sh-filter-btn" data-filter="Security">Security</button>
            </div>

            <div class="sh-search-group">
                <div class="sh-search-wrap">
                    <span class="sh-search-icon" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </span>
                    <input type="search" id="settingsSearchInput" class="sh-search-input" placeholder="Search settings modules...">
                </div>
                <span class="sh-result-count" id="settingsVisibleCount">{{ $totalModulesCount }} shown</span>
            </div>
        </section>

        <!-- Settings Cards Grid -->
        <section class="sh-modules-grid" id="settingsGrid" data-live-preserve>
            @foreach($settingsGroups as $slug => $group)
                @php
                    $tone = $group['color'] ?? 'primary';
                    $toneClass = in_array($tone, ['primary', 'info', 'success', 'warning', 'danger', 'secondary'], true)
                        ? 'sh-tone-' . $tone
                        : 'sh-tone-primary';
                @endphp

                @if($group['has_dropdown'] ?? false)
                    <div class="setting-card-item dropdown" data-category="{{ $group['category'] ?? 'General' }}" data-name="{{ strtolower($group['name']) }}" data-desc="{{ strtolower($group['description']) }}">
                        <div class="sh-module-card dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" role="button" tabindex="0">
                            <div>
                                <div class="sh-module-head">
                                    <div class="setting-icon-box {{ $toneClass }}">
                                        <i class="{{ $group['icon'] }}"></i>
                                    </div>
                                    <span class="sh-cat-badge {{ $toneClass }}">{{ $group['category'] }}</span>
                                </div>
                                <div class="sh-module-body mt-3">
                                    <h3>{{ $group['name'] }}</h3>
                                    <p>{{ $group['description'] }}</p>
                                </div>
                            </div>
                            <div class="sh-module-footer">
                                <span>{{ count($group['dropdown_options'] ?? []) }} sections</span>
                                <span class="sh-manage-pill">
                                    Manage <i class="bx bx-chevron-down"></i>
                                </span>
                            </div>
                        </div>

                        <ul class="dropdown-menu sh-dropdown-menu">
                            <li class="sh-dropdown-header">Select Department Option</li>
                            <li><hr class="dropdown-divider my-1"></li>
                            @foreach($group['dropdown_options'] as $opt)
                                <li>
                                    <a class="dropdown-item sh-dropdown-item" href="{{ route($opt['route']) }}">
                                        <div class="setting-icon-box sh-tone-warning" style="width: 2.35rem; height: 2.35rem; font-size: 1.05rem;">
                                            <i class="{{ $opt['icon'] }}"></i>
                                        </div>
                                        <div>
                                            <div class="sh-dropdown-item-title">{{ $opt['name'] }}</div>
                                            <small class="sh-dropdown-item-desc">{{ $opt['description'] }}</small>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    @php
                        $hasRoute = isset($group['route']) && Route::has($group['route']);
                        $targetUrl = $hasRoute ? route($group['route']) : '#';
                    @endphp
                    <div class="setting-card-item" data-category="{{ $group['category'] ?? 'General' }}" data-name="{{ strtolower($group['name']) }}" data-desc="{{ strtolower($group['description']) }}">
                        <a href="{{ $targetUrl }}" class="sh-module-card">
                            <div>
                                <div class="sh-module-head">
                                    <div class="setting-icon-box {{ $toneClass }}">
                                        <i class="{{ $group['icon'] }}"></i>
                                    </div>
                                    <span class="sh-cat-badge {{ $toneClass }}">{{ $group['category'] }}</span>
                                </div>
                                <div class="sh-module-body mt-3">
                                    <h3>{{ $group['name'] }}</h3>
                                    <p>{{ $group['description'] }}</p>
                                </div>
                            </div>
                            <div class="sh-module-footer">
                                <span>Configure</span>
                                <span class="sh-manage-pill">
                                    Manage <i class="bx bx-right-arrow-alt"></i>
                                </span>
                            </div>
                        </a>
                    </div>
                @endif
            @endforeach
        </section>

        <!-- Finance Placeholder -->
        <div id="finance-settings-placeholder" class="sh-empty-card d-none" role="status" data-live-preserve>
            <h4>In Development</h4>
            <p>Finance settings are currently in development.</p>
        </div>

        <!-- Empty Search State -->
        <div id="settingsEmptyState" class="sh-empty-card d-none" role="status" data-live-preserve>
            <h4>No matching settings modules</h4>
            <p class="mb-3">Try a different keyword or reset the category filter.</p>
            <button type="button" id="resetSettingsFilters" class="sh-btn sh-btn-secondary">Reset filters</button>
        </div>

        <!-- System Configuration Status Footer -->
        <section class="sh-footer-card" aria-label="System configuration status">
            <div class="sh-footer-head">
                <h2 class="sh-footer-title">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span>System Configuration Status</span>
                </h2>
                <span class="sh-active-badge">
                    <span class="sh-active-badge-dot" aria-hidden="true"></span>
                    All {{ $totalModulesCount }} Settings Active
                </span>
            </div>

            <div class="sh-footer-stats">
                <div class="sh-footer-stat">
                    <strong>{{ $businessOrgCount }}</strong>
                    <span>Business &amp; Organization</span>
                </div>
                <div class="sh-footer-stat">
                    <strong>{{ $hrCount }}</strong>
                    <span>HR, Leave &amp; Schedule</span>
                </div>
                <div class="sh-footer-stat">
                    <strong>{{ $systemCount }}</strong>
                    <span>System &amp; Notifications</span>
                </div>
                <div class="sh-footer-stat">
                    <strong>{{ $securityCount }}</strong>
                    <span>Security &amp; Access</span>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('settingsSearchInput');
    const filterButtons = document.querySelectorAll('#settingsCategoryFilter button');
    const cards = document.querySelectorAll('.setting-card-item');
    const financePlaceholder = document.getElementById('finance-settings-placeholder');
    const emptyState = document.getElementById('settingsEmptyState');
    const visibleCountEl = document.getElementById('settingsVisibleCount');
    const resetBtn = document.getElementById('resetSettingsFilters');

    let currentFilter = 'all';

    function filterCards() {
        const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
        const isFinance = currentFilter === 'Finance';
        if (financePlaceholder) {
            financePlaceholder.classList.toggle('d-none', !isFinance);
        }

        let visibleCount = 0;
        cards.forEach(card => {
            const category = card.getAttribute('data-category') || '';
            const name = card.getAttribute('data-name') || '';
            const desc = card.getAttribute('data-desc') || '';

            const matchesCategory = (currentFilter === 'all') || (category === currentFilter);
            const matchesSearch = !query || name.includes(query) || desc.includes(query);
            const show = matchesCategory && matchesSearch;

            card.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });

        if (visibleCountEl) {
            visibleCountEl.textContent = visibleCount + ' shown';
        }
        if (emptyState) {
            emptyState.classList.toggle('d-none', isFinance || visibleCount > 0);
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterCards);
    }

    filterButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            currentFilter = this.getAttribute('data-filter') || 'all';
            filterCards();
        });
    });

    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            if (searchInput) searchInput.value = '';
            currentFilter = 'all';
            filterButtons.forEach(b => {
                b.classList.toggle('active', b.getAttribute('data-filter') === 'all');
            });
            filterCards();
        });
    }

    // Elevate open Bootstrap dropdown card above sibling grid cards
    document.querySelectorAll('.setting-card-item.dropdown').forEach(item => {
        item.addEventListener('show.bs.dropdown', () => item.classList.add('is-dropdown-open'));
        item.addEventListener('hide.bs.dropdown', () => item.classList.remove('is-dropdown-open'));
    });
});
</script>
@endsection
