@extends('admin.layout.app')

@section('title', 'Attendance Settings')

@push('styles')
<style>
    .attendance-settings-page {
        --as-bg: #f8fafc;
        --as-surface: #ffffff;
        --as-surface-soft: #f8fafc;
        --as-surface-muted: #f1f5f9;
        --as-border: rgba(226, 232, 240, 0.9);
        --as-border-subtle: #f1f5f9;
        --as-text: #1e293b;
        --as-heading: #0f172a;
        --as-muted: #64748b;
        --as-subtle: #94a3b8;
        --as-brand: #2563eb;
        --as-brand-hover: #1d4ed8;
        --as-brand-soft: #eff6ff;
        --as-brand-border: #dbeafe;
        --as-shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.03);
        --as-shadow-md: 0 10px 25px -5px rgba(15, 23, 42, 0.06), 0 4px 10px -4px rgba(15, 23, 42, 0.03);
        --as-shadow-brand: 0 6px 16px -3px rgba(37, 99, 235, 0.28);

        min-height: calc(100vh - 96px);
        padding: 1.75rem 1.5rem 2.75rem;
        background: var(--as-bg);
        color: var(--as-text);
        font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif;
    }

    .attendance-settings-page *,
    .attendance-settings-page *::before,
    .attendance-settings-page *::after {
        box-sizing: border-box;
    }

    .as-shell {
        max-width: 1280px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* Breadcrumb & Engine Status Bar */
    .as-topbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem 1rem;
    }

    .as-breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        font-size: 0.78rem;
        font-weight: 500;
        color: var(--as-muted);
    }

    .as-breadcrumb a {
        color: var(--as-muted);
        text-decoration: none;
        transition: color 0.15s ease;
    }

    .as-breadcrumb a:hover {
        color: var(--as-brand);
    }

    .as-breadcrumb-sep {
        color: #cbd5e1;
    }

    .as-breadcrumb-current {
        color: var(--as-brand-hover);
        background: var(--as-brand-soft);
        border: 1px solid var(--as-brand-border);
        padding: 0.2rem 0.6rem;
        border-radius: 0.45rem;
        font-weight: 600;
    }

    .as-engine-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.85rem;
        border-radius: 9999px;
        background: var(--as-surface);
        border: 1px solid var(--as-border);
        box-shadow: var(--as-shadow-sm);
        font-size: 0.74rem;
        font-weight: 600;
        color: var(--as-muted);
        letter-spacing: 0.03em;
    }

    .as-pulse-dot {
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 9999px;
        background: #10b981;
        display: inline-block;
        animation: asPulse 2s ease-in-out infinite;
    }

    @keyframes asPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.45; transform: scale(0.9); }
    }

    /* Header Card */
    .as-header-card {
        background: var(--as-surface);
        border: 1px solid var(--as-border);
        border-radius: 1rem;
        padding: 1.5rem 1.75rem;
        box-shadow: var(--as-shadow-sm);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
    }

    .as-header-left {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        min-width: 0;
        flex: 1 1 320px;
    }

    .as-header-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.75rem;
        background: var(--as-brand-soft);
        border: 1px solid var(--as-brand-border);
        color: var(--as-brand-hover);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: var(--as-shadow-sm);
    }

    .as-header-icon svg {
        width: 1.5rem;
        height: 1.5rem;
    }

    .as-header-title-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
    }

    .as-header-title-row h1 {
        margin: 0;
        font-size: 1.5rem;
        line-height: 1.25;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--as-heading);
    }

    .as-active-badge {
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

    .as-active-badge-dot {
        width: 0.4rem;
        height: 0.4rem;
        border-radius: 9999px;
        background: #10b981;
    }

    .as-header-subtitle {
        margin: 0.35rem 0 0;
        font-size: 0.875rem;
        color: var(--as-muted);
        line-height: 1.45;
    }

    .as-header-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.65rem;
        flex-shrink: 0;
    }

    .as-btn {
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

    .as-btn svg {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
    }

    .as-btn-secondary {
        padding: 0.68rem 1rem;
        background: var(--as-surface);
        border: 1px solid var(--as-border);
        color: #334155 !important;
        box-shadow: var(--as-shadow-sm);
    }

    .as-btn-secondary svg {
        color: #94a3b8;
    }

    .as-btn-secondary:hover {
        background: var(--as-surface-soft);
        border-color: #cbd5e1;
        color: var(--as-heading) !important;
        transform: translateY(-1px);
    }

    .as-btn-primary {
        padding: 0.72rem 1.25rem;
        background: var(--as-brand);
        border: 1px solid var(--as-brand);
        color: #ffffff !important;
        box-shadow: var(--as-shadow-brand);
    }

    .as-btn-primary:hover {
        background: var(--as-brand-hover);
        border-color: var(--as-brand-hover);
        color: #ffffff !important;
        transform: translateY(-1px);
    }

    .as-btn-primary:active {
        transform: scale(0.98);
    }

    /* Alerts */
    .as-alert {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.9rem 1.1rem;
        border-radius: 0.875rem;
        font-size: 0.85rem;
        font-weight: 500;
        box-shadow: var(--as-shadow-sm);
    }

    .as-alert-success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }

    .as-alert-danger {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .as-alert-body {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
    }

    /* KPI Summary Grid */
    .as-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.25rem;
    }

    .as-kpi-card {
        background: var(--as-surface);
        border: 1px solid var(--as-border);
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: var(--as-shadow-sm);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 1rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .as-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--as-shadow-md);
        border-color: #cbd5e1;
    }

    .as-kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .as-kpi-label {
        font-size: 0.69rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--as-subtle);
    }

    .as-kpi-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .as-kpi-icon svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    .as-kpi-icon-brand {
        background: #eff6ff;
        border: 1px solid #dbeafe;
        color: #2563eb;
    }

    .as-kpi-icon-indigo {
        background: #eef2ff;
        border: 1px solid #e0e7ff;
        color: #4f46e5;
    }

    .as-kpi-icon-blue {
        background: #eff6ff;
        border: 1px solid #dbeafe;
        color: #1d4ed8;
    }

    .as-kpi-icon-purple {
        background: #faf5ff;
        border: 1px solid #f3e8ff;
        color: #7e22ce;
    }

    .as-kpi-value {
        font-size: 1.45rem;
        line-height: 1.2;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--as-heading);
    }

    .as-kpi-hint {
        margin: 0.3rem 0 0;
        font-size: 0.76rem;
        font-weight: 500;
        color: var(--as-muted);
    }

    /* Main Policy Card */
    .as-policy-card {
        background: var(--as-surface);
        border: 1px solid var(--as-border);
        border-radius: 1rem;
        padding: 1.75rem 2rem;
        box-shadow: var(--as-shadow-sm);
        display: flex;
        flex-direction: column;
        gap: 1.75rem;
    }

    .as-policy-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid var(--as-border-subtle);
    }

    .as-policy-header h2 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 700;
        letter-spacing: -0.015em;
        color: var(--as-heading);
    }

    .as-policy-header p {
        margin: 0.25rem 0 0;
        font-size: 0.78rem;
        color: var(--as-muted);
    }

    .as-validation-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.8rem;
        border-radius: 0.6rem;
        background: var(--as-surface-soft);
        border: 1px solid var(--as-border);
        color: #475569;
        font-size: 0.74rem;
        font-weight: 600;
    }

    .as-validation-pill svg {
        width: 1rem;
        height: 1rem;
        color: #10b981;
    }

    /* Duration Classification Spectrum */
    .as-spectrum-box {
        border-radius: 0.875rem;
        background: var(--as-surface-soft);
        border: 1px solid var(--as-border);
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
    }

    .as-spectrum-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
    }

    .as-spectrum-title {
        font-size: 0.69rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--as-muted);
    }

    .as-spectrum-summary {
        font-size: 0.76rem;
        font-weight: 600;
        color: var(--as-brand-hover);
        background: var(--as-brand-soft);
        border: 1px solid var(--as-brand-border);
        padding: 0.25rem 0.7rem;
        border-radius: 9999px;
    }

    .as-spectrum-track {
        width: 100%;
        height: 0.75rem;
        border-radius: 9999px;
        background: #e2e8f0;
        overflow: hidden;
        display: flex;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.1);
    }

    .as-bar-dayoff {
        height: 100%;
        background: #f43f5e;
        transition: width 0.25s ease;
    }

    .as-bar-halfday {
        height: 100%;
        background: #fbbf24;
        transition: width 0.25s ease;
    }

    .as-bar-fullday {
        height: 100%;
        background: #10b981;
        transition: width 0.25s ease;
    }

    .as-spectrum-legend {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem 1.5rem;
        font-size: 0.76rem;
        font-weight: 500;
        color: #475569;
    }

    .as-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }

    .as-legend-dot {
        width: 0.6rem;
        height: 0.6rem;
        border-radius: 9999px;
        flex-shrink: 0;
    }

    /* Numbered Sections */
    .as-section {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .as-section-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding-bottom: 0.35rem;
    }

    .as-section-title-group {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .as-section-num {
        width: 1.65rem;
        height: 1.65rem;
        border-radius: 0.45rem;
        font-size: 0.74rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .as-num-brand {
        background: #eff6ff;
        border: 1px solid #dbeafe;
        color: #1d4ed8;
    }

    .as-num-indigo {
        background: #eef2ff;
        border: 1px solid #e0e7ff;
        color: #4f46e5;
    }

    .as-num-emerald {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #047857;
    }

    .as-section-head h3 {
        margin: 0;
        font-size: 0.92rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: var(--as-heading);
    }

    .as-fields-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem 1.5rem;
        transition: opacity 0.2s ease;
    }

    .as-field {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
    }

    .as-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #334155;
        margin: 0;
    }

    .as-req {
        color: #f43f5e;
        font-weight: 700;
    }

    .as-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .as-input-icon {
        position: absolute;
        left: 0.85rem;
        color: var(--as-subtle);
        pointer-events: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .as-input-icon svg {
        width: 1rem;
        height: 1rem;
    }

    .as-control {
        width: 100%;
        border-radius: 0.75rem;
        background: var(--as-surface-soft);
        border: 1px solid var(--as-border);
        color: var(--as-heading);
        font-size: 0.875rem;
        font-weight: 500;
        padding: 0.66rem 0.95rem 0.66rem 2.45rem;
        min-height: 2.75rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
    }

    .as-control.has-unit {
        padding-right: 4.5rem;
    }

    .as-control:focus {
        outline: none;
        background: var(--as-surface);
        border-color: var(--as-brand);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
    }

    .as-control.is-invalid {
        border-color: #f43f5e;
        box-shadow: 0 0 0 3px rgba(244, 63, 94, 0.12);
    }

    .as-unit-tag {
        position: absolute;
        right: 0.7rem;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        color: var(--as-subtle);
        background: var(--as-surface);
        border: 1px solid var(--as-border);
        padding: 0.2rem 0.5rem;
        border-radius: 0.4rem;
        pointer-events: none;
    }

    .as-help {
        margin: 0;
        font-size: 0.75rem;
        line-height: 1.45;
        color: var(--as-subtle);
    }

    .as-error {
        font-size: 0.76rem;
        font-weight: 500;
        color: #e11d48;
        margin-top: 0.15rem;
    }

    .as-warning-inline {
        display: none;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 0.9rem;
        border-radius: 0.75rem;
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .as-warning-inline.is-visible {
        display: flex;
    }

    /* Clock Window Switch Card */
    .as-divider {
        height: 1px;
        background: var(--as-border-subtle);
        margin: 0.25rem 0;
    }

    .as-switch-card {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem;
        padding: 0.95rem 1.15rem;
        border-radius: 0.875rem;
        background: var(--as-surface-soft);
        border: 1px solid var(--as-border);
    }

    .as-switch-copy {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }

    .as-switch-copy label {
        margin: 0;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--as-heading);
        cursor: pointer;
    }

    .as-switch-copy span {
        font-size: 0.75rem;
        color: var(--as-muted);
    }

    .as-switch-card .form-check-input {
        width: 2.75rem;
        height: 1.45rem;
        cursor: pointer;
    }

    .as-boundary-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .as-boundary-badge.is-on {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #047857;
    }

    .as-boundary-badge.is-off {
        background: var(--as-surface-muted);
        border: 1px solid var(--as-border);
        color: var(--as-muted);
    }

    /* Form Footer */
    .as-form-footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding-top: 1.25rem;
        border-top: 1px solid var(--as-border-subtle);
    }

    .as-form-footer-note {
        font-size: 0.76rem;
        color: var(--as-muted);
    }

    /* Responsive Breakpoints */
    @media (max-width: 1200px) {
        .as-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .attendance-settings-page {
            padding: 1rem;
        }

        .as-header-card,
        .as-policy-card {
            padding: 1.25rem;
        }

        .as-fields-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .as-kpi-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Dark Mode Overrides */
    html[data-pms-theme="dark"] .attendance-settings-page,
    html[data-theme="dark"] .attendance-settings-page,
    html[data-bs-theme="dark"] .attendance-settings-page,
    body[data-pms-theme="dark"] .attendance-settings-page,
    body[data-theme="dark"] .attendance-settings-page,
    body[data-bs-theme="dark"] .attendance-settings-page,
    .dark-mode .attendance-settings-page {
        --as-bg: #0b1120;
        --as-surface: #111827;
        --as-surface-soft: #1e293b;
        --as-surface-muted: #1e293b;
        --as-border: rgba(148, 163, 184, 0.18);
        --as-border-subtle: rgba(148, 163, 184, 0.14);
        --as-text: #e2e8f0;
        --as-heading: #f8fafc;
        --as-muted: #94a3b8;
        --as-subtle: #94a3b8;
        --as-brand-soft: rgba(37, 99, 235, 0.18);
        --as-brand-border: rgba(96, 165, 250, 0.35);
    }

    html[data-pms-theme="dark"] .attendance-settings-page .as-btn-secondary,
    html[data-theme="dark"] .attendance-settings-page .as-btn-secondary,
    html[data-bs-theme="dark"] .attendance-settings-page .as-btn-secondary,
    .dark-mode .attendance-settings-page .as-btn-secondary {
        background: #1e293b;
        color: #e2e8f0 !important;
        border-color: rgba(148, 163, 184, 0.25);
    }

    html[data-pms-theme="dark"] .attendance-settings-page .as-btn-secondary:hover,
    html[data-theme="dark"] .attendance-settings-page .as-btn-secondary:hover,
    html[data-bs-theme="dark"] .attendance-settings-page .as-btn-secondary:hover,
    .dark-mode .attendance-settings-page .as-btn-secondary:hover {
        background: #273549;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .attendance-settings-page .as-control,
    html[data-theme="dark"] .attendance-settings-page .as-control,
    html[data-bs-theme="dark"] .attendance-settings-page .as-control,
    .dark-mode .attendance-settings-page .as-control {
        color-scheme: dark;
        color: #f8fafc;
    }

    html[data-pms-theme="dark"] .attendance-settings-page .as-validation-pill,
    html[data-theme="dark"] .attendance-settings-page .as-validation-pill,
    html[data-bs-theme="dark"] .attendance-settings-page .as-validation-pill,
    .dark-mode .attendance-settings-page .as-validation-pill {
        color: #cbd5e1;
    }

    html[data-pms-theme="dark"] .attendance-settings-page .as-spectrum-legend,
    html[data-theme="dark"] .attendance-settings-page .as-spectrum-legend,
    html[data-bs-theme="dark"] .attendance-settings-page .as-spectrum-legend,
    .dark-mode .attendance-settings-page .as-spectrum-legend,
    html[data-pms-theme="dark"] .attendance-settings-page .as-label,
    html[data-theme="dark"] .attendance-settings-page .as-label,
    html[data-bs-theme="dark"] .attendance-settings-page .as-label,
    .dark-mode .attendance-settings-page .as-label {
        color: #cbd5e1;
    }

    html[data-pms-theme="dark"] .attendance-settings-page .as-spectrum-track,
    html[data-theme="dark"] .attendance-settings-page .as-spectrum-track,
    html[data-bs-theme="dark"] .attendance-settings-page .as-spectrum-track,
    .dark-mode .attendance-settings-page .as-spectrum-track {
        background: #334155;
    }

    html[data-pms-theme="dark"] .attendance-settings-page .as-breadcrumb-current,
    html[data-theme="dark"] .attendance-settings-page .as-breadcrumb-current,
    html[data-bs-theme="dark"] .attendance-settings-page .as-breadcrumb-current,
    .dark-mode .attendance-settings-page .as-breadcrumb-current,
    html[data-pms-theme="dark"] .attendance-settings-page .as-spectrum-summary,
    html[data-theme="dark"] .attendance-settings-page .as-spectrum-summary,
    html[data-bs-theme="dark"] .attendance-settings-page .as-spectrum-summary,
    .dark-mode .attendance-settings-page .as-spectrum-summary {
        color: #93c5fd;
    }

    html[data-pms-theme="dark"] .attendance-settings-page .as-kpi-icon-brand,
    html[data-theme="dark"] .attendance-settings-page .as-kpi-icon-brand,
    html[data-bs-theme="dark"] .attendance-settings-page .as-kpi-icon-brand,
    .dark-mode .attendance-settings-page .as-kpi-icon-brand,
    html[data-pms-theme="dark"] .attendance-settings-page .as-header-icon,
    html[data-theme="dark"] .attendance-settings-page .as-header-icon,
    html[data-bs-theme="dark"] .attendance-settings-page .as-header-icon,
    .dark-mode .attendance-settings-page .as-header-icon,
    html[data-pms-theme="dark"] .attendance-settings-page .as-num-brand,
    html[data-theme="dark"] .attendance-settings-page .as-num-brand,
    html[data-bs-theme="dark"] .attendance-settings-page .as-num-brand,
    .dark-mode .attendance-settings-page .as-num-brand {
        background: rgba(37, 99, 235, 0.22);
        border-color: rgba(96, 165, 250, 0.35);
        color: #60a5fa;
    }

    html[data-pms-theme="dark"] .attendance-settings-page .as-kpi-icon-indigo,
    html[data-theme="dark"] .attendance-settings-page .as-kpi-icon-indigo,
    html[data-bs-theme="dark"] .attendance-settings-page .as-kpi-icon-indigo,
    .dark-mode .attendance-settings-page .as-kpi-icon-indigo,
    html[data-pms-theme="dark"] .attendance-settings-page .as-num-indigo,
    html[data-theme="dark"] .attendance-settings-page .as-num-indigo,
    html[data-bs-theme="dark"] .attendance-settings-page .as-num-indigo,
    .dark-mode .attendance-settings-page .as-num-indigo {
        background: rgba(99, 102, 241, 0.22);
        border-color: rgba(129, 140, 248, 0.35);
        color: #a5b4fc;
    }

    html[data-pms-theme="dark"] .attendance-settings-page .as-kpi-icon-blue,
    html[data-theme="dark"] .attendance-settings-page .as-kpi-icon-blue,
    html[data-bs-theme="dark"] .attendance-settings-page .as-kpi-icon-blue,
    .dark-mode .attendance-settings-page .as-kpi-icon-blue {
        background: rgba(59, 130, 246, 0.22);
        border-color: rgba(96, 165, 250, 0.35);
        color: #93c5fd;
    }

    html[data-pms-theme="dark"] .attendance-settings-page .as-kpi-icon-purple,
    html[data-theme="dark"] .attendance-settings-page .as-kpi-icon-purple,
    html[data-bs-theme="dark"] .attendance-settings-page .as-kpi-icon-purple,
    .dark-mode .attendance-settings-page .as-kpi-icon-purple {
        background: rgba(168, 85, 247, 0.22);
        border-color: rgba(192, 132, 252, 0.35);
        color: #d8b4fe;
    }

    html[data-pms-theme="dark"] .attendance-settings-page .as-active-badge,
    html[data-theme="dark"] .attendance-settings-page .as-active-badge,
    html[data-bs-theme="dark"] .attendance-settings-page .as-active-badge,
    .dark-mode .attendance-settings-page .as-active-badge,
    html[data-pms-theme="dark"] .attendance-settings-page .as-num-emerald,
    html[data-theme="dark"] .attendance-settings-page .as-num-emerald,
    html[data-bs-theme="dark"] .attendance-settings-page .as-num-emerald,
    .dark-mode .attendance-settings-page .as-num-emerald,
    html[data-pms-theme="dark"] .attendance-settings-page .as-boundary-badge.is-on,
    html[data-theme="dark"] .attendance-settings-page .as-boundary-badge.is-on,
    html[data-bs-theme="dark"] .attendance-settings-page .as-boundary-badge.is-on,
    .dark-mode .attendance-settings-page .as-boundary-badge.is-on {
        background: rgba(16, 185, 129, 0.16);
        border-color: rgba(52, 211, 153, 0.35);
        color: #6ee7b7;
    }
</style>
@endpush

@section('content')
@php
    $startVal = old('office_start_time', !empty($setting->office_start_time) ? date('H:i', strtotime($setting->office_start_time)) : '09:30');
    $lateVal = old('late_time', !empty($setting->late_time) ? date('H:i', strtotime($setting->late_time)) : '09:30');
    $startFormatted = $startVal ? date('g:i A', strtotime($startVal)) : '--:--';
    $lateFormatted = $lateVal ? date('g:i A', strtotime($lateVal)) : '--:--';
    $halfDayMins = (int) old('half_day_threshold_minutes', $setting->half_day_threshold_minutes ?? 510);
    $dayOffMins = (int) old('day_off_threshold_minutes', $setting->day_off_threshold_minutes ?? 270);
    $hm = fn ($value, $default) => ! empty($value) ? date('H:i', strtotime($value)) : $default;
    $windowOn = (bool) old('clock_window_enabled', $setting->clock_window_enabled ?? false);
    $missedOut = old('missed_clock_out_status', $setting->missed_clock_out_status ?? 'half_day');
@endphp

<div class="attendance-settings-page" data-live-preserve data-live-records-off>
    <div class="as-shell">
        <!-- Breadcrumb & Live Status Bar -->
        <div class="as-topbar">
            <nav aria-label="Breadcrumb" class="as-breadcrumb">
                <a href="{{ route('admin.settings.index') }}">Admin</a>
                <span class="as-breadcrumb-sep">/</span>
                <a href="{{ route('admin.settings.index') }}">Settings</a>
                <span class="as-breadcrumb-sep">/</span>
                <span class="as-breadcrumb-current">Attendance Settings</span>
            </nav>
            <div class="as-engine-pill">
                <span class="as-pulse-dot" aria-hidden="true"></span>
                <span>POLICY ENGINE • SYNCED</span>
            </div>
        </div>

        <!-- Header Card -->
        <section class="as-header-card">
            <div class="as-header-left">
                <div class="as-header-icon" aria-hidden="true">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <div style="min-width: 0;">
                    <div class="as-header-title-row">
                        <h1>Attendance Settings</h1>
                        <span class="as-active-badge">
                            <span class="as-active-badge-dot" aria-hidden="true"></span>
                            Active Policy
                        </span>
                    </div>
                    <p class="as-header-subtitle">
                        Configure office start time, late thresholds, half-day limits, and day-off calculation parameters.
                    </p>
                </div>
            </div>

            <div class="as-header-actions">
                @if(Route::has('attendance.index'))
                    <a href="{{ route('attendance.index') }}" class="as-btn as-btn-secondary">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span>Attendance Log</span>
                    </a>
                @endif

                <a href="{{ route('admin.settings.index') }}" class="as-btn as-btn-secondary">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span>Back to Settings</span>
                </a>

                <button type="submit" form="attendanceSettingsForm" id="savePolicyBtn" class="as-btn as-btn-primary">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Save Policy Rules</span>
                </button>
            </div>
        </section>

        <!-- Alert Notifications -->
        @if(session('success'))
            <div class="as-alert as-alert-success alert-dismissible fade show" role="alert">
                <div class="as-alert-body">
                    <i class="fas fa-check-circle fs-5 mt-0.5"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="as-alert as-alert-danger alert-dismissible fade show" role="alert">
                <div class="as-alert-body">
                    <i class="fas fa-exclamation-triangle fs-5 mt-0.5"></i>
                    <div>{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="as-alert as-alert-danger alert-dismissible fade show" role="alert">
                <div class="as-alert-body">
                    <i class="fas fa-exclamation-circle fs-5 mt-0.5"></i>
                    <div>
                        <strong>Please review the highlighted settings:</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- KPI Summary Row (4 Cards) -->
        <section class="as-kpi-grid" aria-label="Attendance policy summary">
            <div class="as-kpi-card">
                <div class="as-kpi-top">
                    <span class="as-kpi-label">Office Start Time</span>
                    <div class="as-kpi-icon as-kpi-icon-brand" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="5"></circle>
                            <line x1="12" y1="1" x2="12" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="23"></line>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                            <line x1="1" y1="12" x2="3" y2="12"></line>
                            <line x1="21" y1="12" x2="23" y2="12"></line>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="as-kpi-value" id="kpiStartTime">{{ $startFormatted }}</div>
                    <p class="as-kpi-hint">Standard shift clock-in</p>
                </div>
            </div>

            <div class="as-kpi-card">
                <div class="as-kpi-top">
                    <span class="as-kpi-label">Late Cutoff</span>
                    <div class="as-kpi-icon as-kpi-icon-indigo" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="as-kpi-value" id="kpiLateCutoff">{{ $lateFormatted }}</div>
                    <p class="as-kpi-hint">Grace period boundary</p>
                </div>
            </div>

            <div class="as-kpi-card">
                <div class="as-kpi-top">
                    <span class="as-kpi-label">Half-Day Threshold</span>
                    <div class="as-kpi-icon as-kpi-icon-blue" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                            <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="as-kpi-value" id="kpiHalfDay">{{ $halfDayMins }} Mins ({{ round($halfDayMins / 60, 1) }}h)</div>
                    <p class="as-kpi-hint">Minimum half-day credit</p>
                </div>
            </div>

            <div class="as-kpi-card">
                <div class="as-kpi-top">
                    <span class="as-kpi-label">Day-Off Threshold</span>
                    <div class="as-kpi-icon as-kpi-icon-purple" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <line x1="18" y1="8" x2="23" y2="13"></line>
                            <line x1="23" y1="8" x2="18" y2="13"></line>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="as-kpi-value" id="kpiDayOff">{{ $dayOffMins }} Mins ({{ round($dayOffMins / 60, 1) }}h)</div>
                    <p class="as-kpi-hint">Absent / day-off floor</p>
                </div>
            </div>
        </section>

        <!-- Main Policy Rules & Duration Spectrum Card -->
        <section class="as-policy-card">
            <div class="as-policy-header">
                <div>
                    <h2>Attendance Policy Rules &amp; Thresholds</h2>
                    <p>Configure company office hours and precise automated attendance calculation logic.</p>
                </div>
                <div class="as-validation-pill">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span>AUTO-VALIDATION ENABLED</span>
                </div>
            </div>

            <!-- Visual Threshold Distribution Bar -->
            <div class="as-spectrum-box">
                <div class="as-spectrum-head">
                    <span class="as-spectrum-title">Duration Classification Spectrum</span>
                    <span class="as-spectrum-summary" id="spectrumSummary">
                        0m — {{ $dayOffMins }}m (Absent) • {{ $dayOffMins }}m — {{ $halfDayMins }}m (Half-Day) • {{ $halfDayMins }}m+ (Full Day)
                    </span>
                </div>
                <div class="as-spectrum-track" aria-hidden="true">
                    <div class="as-bar-dayoff" id="barDayOff" style="width: 45%;"></div>
                    <div class="as-bar-halfday" id="barHalfDay" style="width: 40%;"></div>
                    <div class="as-bar-fullday" id="barFullDay" style="width: 15%;"></div>
                </div>
                <div class="as-spectrum-legend">
                    <span class="as-legend-item">
                        <span class="as-legend-dot" style="background: #f43f5e;"></span>
                        <span id="legendDayOff">Absent / Day-Off (0 - {{ $dayOffMins }}m)</span>
                    </span>
                    <span class="as-legend-item">
                        <span class="as-legend-dot" style="background: #fbbf24;"></span>
                        <span id="legendHalfDay">Half-Day ({{ $dayOffMins }}m - {{ $halfDayMins }}m)</span>
                    </span>
                    <span class="as-legend-item">
                        <span class="as-legend-dot" style="background: #10b981;"></span>
                        <span id="legendFullDay">Full-Day Present ({{ $halfDayMins }}m+)</span>
                    </span>
                </div>
            </div>

            <form id="attendanceSettingsForm" method="POST" action="{{ route('attendance.settings.update') }}" style="display: flex; flex-direction: column; gap: 1.75rem;">
                @csrf

                <!-- Section 01: Standard Office Hours & Late Cutoff -->
                <div class="as-section">
                    <div class="as-section-head">
                        <div class="as-section-title-group">
                            <span class="as-section-num as-num-brand">01</span>
                            <h3>Standard Office Hours &amp; Late Cutoff</h3>
                        </div>
                    </div>

                    <div class="as-fields-grid">
                        <div class="as-field">
                            <label class="as-label" for="officeStartTime">
                                Office Start Time <span class="as-req">*</span>
                            </label>
                            <div class="as-input-wrap">
                                <span class="as-input-icon" aria-hidden="true">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                </span>
                                <input
                                    type="time"
                                    id="officeStartTime"
                                    name="office_start_time"
                                    class="as-control @error('office_start_time') is-invalid @enderror"
                                    value="{{ $startVal }}"
                                    required>
                            </div>
                            <p class="as-help">Official daily shift start time across all departments.</p>
                            @error('office_start_time')
                                <div class="as-error"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="as-field">
                            <label class="as-label" for="lateCutoffTime">
                                Late Cutoff Time <span class="as-req">*</span>
                            </label>
                            <div class="as-input-wrap">
                                <span class="as-input-icon" aria-hidden="true">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                </span>
                                <input
                                    type="time"
                                    id="lateCutoffTime"
                                    name="late_time"
                                    class="as-control @error('late_time') is-invalid @enderror"
                                    value="{{ $lateVal }}"
                                    required>
                            </div>
                            <p class="as-help">Check-ins logged after this time are automatically marked as Late.</p>
                            @error('late_time')
                                <div class="as-error"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 02: Attendance Evaluation Thresholds -->
                <div class="as-section">
                    <div class="as-section-head">
                        <div class="as-section-title-group">
                            <span class="as-section-num as-num-indigo">02</span>
                            <h3>Attendance Evaluation Thresholds (Minutes)</h3>
                        </div>
                    </div>

                    <div class="as-fields-grid">
                        <div class="as-field">
                            <label class="as-label" for="halfDayThreshold">
                                Half-Day Threshold (Minutes) <span class="as-req">*</span>
                            </label>
                            <div class="as-input-wrap">
                                <span class="as-input-icon" aria-hidden="true">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 14 10"></polyline>
                                    </svg>
                                </span>
                                <input
                                    type="number"
                                    id="halfDayThreshold"
                                    name="half_day_threshold_minutes"
                                    min="1"
                                    max="1440"
                                    class="as-control has-unit @error('half_day_threshold_minutes') is-invalid @enderror"
                                    value="{{ $halfDayMins }}"
                                    required>
                                <span class="as-unit-tag">MINS</span>
                            </div>
                            <p class="as-help">Minimum work duration required to be counted as Half-Day (Default: 510 mins = 8.5 hours).</p>
                            @error('half_day_threshold_minutes')
                                <div class="as-error"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="as-field">
                            <label class="as-label" for="dayOffThreshold">
                                Day-Off Threshold (Minutes) <span class="as-req">*</span>
                            </label>
                            <div class="as-input-wrap">
                                <span class="as-input-icon" aria-hidden="true">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M5 22h14"></path>
                                        <path d="M5 2h14"></path>
                                        <path d="M17 22v-4.172a2 2 0 00-.586-1.414L12 12l-4.414 4.414A2 2 0 007 17.828V22"></path>
                                        <path d="M7 2v4.172a2 2 0 00.586 1.414L12 12l4.414-4.414A2 2 0 0017 6.172V2"></path>
                                    </svg>
                                </span>
                                <input
                                    type="number"
                                    id="dayOffThreshold"
                                    name="day_off_threshold_minutes"
                                    min="1"
                                    max="1440"
                                    class="as-control has-unit @error('day_off_threshold_minutes') is-invalid @enderror"
                                    value="{{ $dayOffMins }}"
                                    required>
                                <span class="as-unit-tag">MINS</span>
                            </div>
                            <p class="as-help">Work duration below this floor is marked Absent / Day-Off (Must be lower than half-day limit, Default: 270 mins = 4.5 hours).</p>
                            @error('day_off_threshold_minutes')
                                <div class="as-error"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div id="thresholdOrderWarning" class="as-warning-inline" role="status">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Day-Off Threshold must be lower than Half-Day Threshold before saving.</span>
                    </div>
                </div>

                <div class="as-divider"></div>

                <!-- Section 03: Clock-in / Clock-out Boundary -->
                <div class="as-section">
                    <div class="as-section-head">
                        <div class="as-section-title-group">
                            <span class="as-section-num as-num-emerald">03</span>
                            <h3>Clock-in / Clock-out Boundary</h3>
                        </div>
                        <span id="clockWindowStatusBadge" class="as-boundary-badge {{ $windowOn ? 'is-on' : 'is-off' }}">
                            {{ $windowOn ? 'Boundary Active' : 'Off — Open Timing' }}
                        </span>
                    </div>

                    <p class="as-help" style="font-size: 0.8rem;">
                        Applies to Employees, HR, Managers and Associate Managers (everyone except Admin). Clock-in always requires a live photo.
                        While switched off, staff can clock in at any time as before.
                    </p>

                    <div class="as-switch-card">
                        <div class="as-switch-copy">
                            <label for="clockWindowEnabled">Enforce fixed clock-in / clock-out times</label>
                            <span>Restrict clock-in opening/closing hours and automate missed clock-out handling.</span>
                        </div>
                        <div class="form-check form-switch m-0">
                            <input type="hidden" name="clock_window_enabled" value="0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                role="switch"
                                id="clockWindowEnabled"
                                name="clock_window_enabled"
                                value="1"
                                @checked($windowOn)>
                        </div>
                    </div>

                    <div class="as-fields-grid" id="clockWindowFields">
                        @foreach([
                            ['clock_in_opens_at', 'Clock-in Opens At', '08:30', 'Earliest time staff can clock in.', true],
                            ['clock_in_closes_at', 'Clock-in Closes At', '11:00', 'No clock-in after this time; the working day is marked Absent.', true],
                            ['half_day_after_time', 'Half Day If Clocked In After', '10:30', 'Clock-in after this time counts as Half Day (between Late Cutoff and Clock-in Closes). Leave empty to only mark Late.', false],
                            ['office_end_time', 'Office End Time (Clock-out From)', '18:30', 'Expected clock-out time. Leaving earlier is judged by the work-duration thresholds above.', false],
                            ['clock_out_closes_at', 'Auto Clock-out At', '23:58', 'A shift still open at this time is closed automatically and treated as a missed clock-out.', false],
                        ] as [$field, $label, $default, $help, $required])
                            <div class="as-field">
                                <label class="as-label" for="field_{{ $field }}">
                                    {{ $label }} @if($required)<span class="as-req">*</span>@endif
                                </label>
                                <div class="as-input-wrap">
                                    <span class="as-input-icon" aria-hidden="true">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                    </span>
                                    <input
                                        type="time"
                                        id="field_{{ $field }}"
                                        name="{{ $field }}"
                                        class="as-control @error($field) is-invalid @enderror"
                                        value="{{ old($field, $hm($setting->{$field} ?? null, $required ? $default : '')) }}">
                                </div>
                                <p class="as-help">{{ $help }}</p>
                                @error($field)
                                    <div class="as-error"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach

                        <div class="as-field">
                            <label class="as-label" for="field_missed_clock_out_status">Missed Clock-out Counts As</label>
                            <div class="as-input-wrap">
                                <span class="as-input-icon" aria-hidden="true">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
                                        <circle cx="8.5" cy="7" r="4"></circle>
                                        <line x1="18" y1="8" x2="23" y2="13"></line>
                                        <line x1="23" y1="8" x2="18" y2="13"></line>
                                    </svg>
                                </span>
                                <select
                                    id="field_missed_clock_out_status"
                                    name="missed_clock_out_status"
                                    class="as-control form-select @error('missed_clock_out_status') is-invalid @enderror">
                                    <option value="half_day" @selected($missedOut === 'half_day')>Half Day</option>
                                    <option value="absent" @selected($missedOut === 'absent')>Absent</option>
                                    <option value="keep" @selected($missedOut === 'keep')>Keep calculated status</option>
                                </select>
                            </div>
                            <p class="as-help">Status for a day where the employee never clocked out.</p>
                            @error('missed_clock_out_status')
                                <div class="as-error"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Action Footer -->
                <div class="as-form-footer">
                    <span class="as-form-footer-note">
                        Saving recalculates existing non-archived attendance records against the updated rules.
                    </span>
                    <button type="submit" class="as-btn as-btn-primary">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <span>Save Attendance Settings</span>
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
    (function () {
        var startInput = document.getElementById('officeStartTime');
        var cutoffInput = document.getElementById('lateCutoffTime');
        var halfDayInput = document.getElementById('halfDayThreshold');
        var dayOffInput = document.getElementById('dayOffThreshold');

        var kpiStart = document.getElementById('kpiStartTime');
        var kpiCutoff = document.getElementById('kpiLateCutoff');
        var kpiHalfDay = document.getElementById('kpiHalfDay');
        var kpiDayOff = document.getElementById('kpiDayOff');

        var barDayOff = document.getElementById('barDayOff');
        var barHalfDay = document.getElementById('barHalfDay');
        var barFullDay = document.getElementById('barFullDay');
        var spectrumSummary = document.getElementById('spectrumSummary');
        var legendDayOff = document.getElementById('legendDayOff');
        var legendHalfDay = document.getElementById('legendHalfDay');
        var legendFullDay = document.getElementById('legendFullDay');
        var orderWarning = document.getElementById('thresholdOrderWarning');

        var toggle = document.getElementById('clockWindowEnabled');
        var fields = document.getElementById('clockWindowFields');
        var boundaryBadge = document.getElementById('clockWindowStatusBadge');

        function formatTime12h(val) {
            if (!val || val.indexOf(':') === -1) return '--:--';
            var parts = val.split(':');
            var h = parseInt(parts[0], 10);
            if (isNaN(h)) return '--:--';
            var m = (parts[1] || '00').slice(0, 2);
            var ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            return h + ':' + m + ' ' + ampm;
        }

        function updateMetrics() {
            if (kpiStart && startInput) kpiStart.textContent = formatTime12h(startInput.value);
            if (kpiCutoff && cutoffInput) kpiCutoff.textContent = formatTime12h(cutoffInput.value);

            var rawHalf = parseInt(halfDayInput ? halfDayInput.value : '', 10);
            var rawDayOff = parseInt(dayOffInput ? dayOffInput.value : '', 10);

            var halfMins = isNaN(rawHalf) ? 510 : Math.max(1, Math.min(1440, rawHalf));
            var dayOffRaw = isNaN(rawDayOff) ? 270 : Math.max(0, Math.min(1440, rawDayOff));

            if (orderWarning) {
                orderWarning.classList.toggle('is-visible', dayOffRaw >= halfMins);
            }

            var dayOffMins = Math.min(Math.max(0, halfMins - 1), dayOffRaw);

            if (kpiHalfDay) kpiHalfDay.textContent = halfMins + ' Mins (' + (halfMins / 60).toFixed(1) + 'h)';
            if (kpiDayOff) kpiDayOff.textContent = dayOffRaw + ' Mins (' + (dayOffRaw / 60).toFixed(1) + 'h)';

            var maxScale = Math.max(600, halfMins + 90);
            var dayOffPct = Math.max(2, Math.round((dayOffMins / maxScale) * 100));
            var halfDayPct = Math.max(2, Math.round(((halfMins - dayOffMins) / maxScale) * 100));
            var fullDayPct = Math.max(5, 100 - dayOffPct - halfDayPct);

            if (barDayOff) barDayOff.style.width = dayOffPct + '%';
            if (barHalfDay) barHalfDay.style.width = halfDayPct + '%';
            if (barFullDay) barFullDay.style.width = fullDayPct + '%';

            if (spectrumSummary) {
                spectrumSummary.textContent = '0m — ' + dayOffRaw + 'm (Absent) • ' + dayOffRaw + 'm — ' + halfMins + 'm (Half-Day) • ' + halfMins + 'm+ (Full Day)';
            }
            if (legendDayOff) legendDayOff.textContent = 'Absent / Day-Off (0 - ' + dayOffRaw + 'm)';
            if (legendHalfDay) legendHalfDay.textContent = 'Half-Day (' + dayOffRaw + 'm - ' + halfMins + 'm)';
            if (legendFullDay) legendFullDay.textContent = 'Full-Day Present (' + halfMins + 'm+)';
        }

        function syncClockWindow() {
            if (!toggle || !fields) return;
            fields.style.opacity = toggle.checked ? '1' : '0.5';
            if (boundaryBadge) {
                boundaryBadge.textContent = toggle.checked ? 'Boundary Active' : 'Off — Open Timing';
                boundaryBadge.classList.toggle('is-on', toggle.checked);
                boundaryBadge.classList.toggle('is-off', !toggle.checked);
            }
        }

        [startInput, cutoffInput, halfDayInput, dayOffInput].forEach(function (el) {
            if (el) el.addEventListener('input', updateMetrics);
        });

        if (toggle) {
            toggle.addEventListener('change', syncClockWindow);
            syncClockWindow();
        }

        updateMetrics();
    })();
</script>
@endsection
