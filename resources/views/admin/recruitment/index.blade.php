@extends('admin.layout.app')

@section('title', 'Recruitment & Job Requirements')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');

    .recruitment-shell {
        --rec-green-900: #071a12;
        --rec-green-800: #0a2e1f;
        --rec-green-700: #0f744c;
        --rec-green-600: #059669;
        --rec-green-500: #10b981;
        --rec-green-400: #34d399;
        --rec-green-100: #ecfdf5;
        --rec-green-50: #f0fdf4;
        --rec-border: rgba(15, 116, 76, 0.12);
        --rec-surface: #ffffff;
        --rec-text-dark: #0f172a;
        --rec-text-muted: #64748b;
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        padding-bottom: 2.5rem;
    }

    /* Ambient entry animations */
    .rec-animate-in {
        animation: recSlideUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    @keyframes recSlideUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ===== BREADCRUMB ===== */
    .breadcrumb-custom {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--rec-text-muted);
        margin-bottom: 1.25rem;
    }
    .breadcrumb-custom a {
        color: var(--rec-green-700);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: color 0.2s ease;
    }
    .breadcrumb-custom a:hover {
        color: var(--rec-green-600);
    }
    .breadcrumb-custom .separator {
        font-size: 1.1rem;
        color: #94a3b8;
    }
    .breadcrumb-custom .active-crumb {
        color: var(--rec-text-dark);
        font-weight: 700;
    }

    /* ===== ELEVATED HEADER CARD ===== */
    .rec-header-card {
        background: #ffffff;
        border-radius: 24px;
        padding: 1.6rem 2rem;
        margin-bottom: 1.75rem;
        border: 1px solid var(--rec-border);
        box-shadow: 0 14px 36px -10px rgba(15, 116, 76, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.25rem;
        position: relative;
        overflow: hidden;
    }
    .rec-header-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #10b981 0%, #059669 50%, #047857 100%);
    }
    .rec-header-left {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }
    .rec-header-icon {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        color: #ffffff !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        box-shadow: 0 8px 20px -4px rgba(5, 150, 105, 0.35);
        flex-shrink: 0;
    }
    .rec-header-icon i {
        color: #ffffff !important;
    }
    .rec-title {
        font-size: 1.55rem;
        font-weight: 800;
        color: var(--rec-green-900);
        margin: 0;
        letter-spacing: -0.025em;
        line-height: 1.25;
    }
    .rec-live-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.2rem 0.65rem;
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid rgba(16, 185, 129, 0.25);
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .live-pulse-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background-color: #10b981;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulseLive 2s infinite;
    }
    @keyframes pulseLive {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .rec-subtitle {
        color: var(--rec-text-muted);
        font-size: 0.9rem;
        margin: 0;
        font-weight: 500;
    }
    .rec-header-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    /* Buttons */
    .btn-rec-primary {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: #ffffff !important;
        border: none !important;
        font-weight: 700;
        font-size: 0.9rem;
        padding: 0.65rem 1.35rem;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        white-space: nowrap !important;
        box-shadow: 0 8px 18px -4px rgba(16, 185, 129, 0.35);
        transition: all 0.25s ease;
        text-decoration: none;
    }
    .btn-rec-primary:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
        color: #ffffff !important;
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -4px rgba(16, 185, 129, 0.45);
    }
    .btn-rec-primary i {
        color: #ffffff !important;
        font-size: 1.15rem;
    }
    .btn-rec-outline {
        background: #ffffff;
        color: var(--rec-green-700) !important;
        border: 1px solid rgba(15, 116, 76, 0.25) !important;
        font-weight: 700;
        font-size: 0.9rem;
        padding: 0.65rem 1.25rem;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.25s ease;
        text-decoration: none;
    }
    .btn-rec-outline:hover {
        background: #ecfdf5;
        color: var(--rec-green-800) !important;
        border-color: rgba(16, 185, 129, 0.4) !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px -2px rgba(15, 116, 76, 0.08);
    }
    .btn-rec-outline i {
        color: var(--rec-green-700) !important;
        font-size: 1.15rem;
    }
    .badge-rec-readonly {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
        padding: 0.6rem 1rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* ===== POLICY CARD (HERO ELEMENT) ===== */
    .policy-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid rgba(16, 185, 129, 0.2);
        box-shadow: 0 16px 36px -12px rgba(15, 116, 76, 0.1);
        margin-bottom: 2rem;
        overflow: hidden;
        position: relative;
    }
    .policy-card-header {
        background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #047857 100%) !important;
        color: #ffffff !important;
        padding: 1.25rem 1.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .policy-header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .policy-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.22) !important;
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.55rem;
        color: #ffffff !important;
        border: 1.5px solid rgba(255, 255, 255, 0.45) !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
        flex-shrink: 0;
    }
    .policy-icon-wrapper i {
        color: #ffffff !important;
        font-size: 1.55rem !important;
    }
    .policy-title {
        color: #ffffff !important;
        font-weight: 800;
        font-size: 1.22rem;
        margin: 0 0 0.25rem 0;
        letter-spacing: -0.015em;
    }
    .policy-meta-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.92) !important;
    }
    .policy-code-pill {
        background: rgba(255, 255, 255, 0.22) !important;
        color: #ffffff !important;
        padding: 0.2rem 0.65rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.8rem;
        letter-spacing: 0.03em;
        border: 1px solid rgba(255, 255, 255, 0.35) !important;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .policy-code-pill i, .policy-updated-text i {
        color: #ffffff !important;
    }
    .policy-updated-text {
        color: rgba(255, 255, 255, 0.92) !important;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .policy-divider {
        color: rgba(255, 255, 255, 0.5) !important;
    }
    .policy-header-right {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .policy-status-pill {
        background: rgba(4, 78, 59, 0.72) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.5);
        padding: 0.38rem 0.95rem;
        border-radius: 30px;
        font-weight: 800;
        font-size: 0.825rem;
        letter-spacing: 0.02em;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .policy-status-pill,
    .policy-status-pill * {
        color: #ffffff !important;
    }
    .status-pulse-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background-color: #10b981;
    }
    .btn-policy-toggle {
        background: rgba(255, 255, 255, 0.18) !important;
        backdrop-filter: blur(8px);
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.45) !important;
        padding: 0.38rem 0.95rem;
        border-radius: 30px;
        font-weight: 700;
        font-size: 0.825rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s ease;
    }
    .btn-policy-toggle:hover {
        background: rgba(255, 255, 255, 0.32) !important;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.75) !important;
        transform: translateY(-1px);
    }
    .btn-policy-toggle i {
        color: #ffffff !important;
        transition: transform 0.25s ease;
    }
    .btn-policy-toggle[aria-expanded="false"] .toggle-chevron {
        transform: rotate(-90deg);
    }

    /* Ultra-Specific Pure White Guarantee for Policy Card Header */
    html body .policy-card .policy-card-header,
    html body .policy-card .policy-card-header *,
    html body .policy-card .policy-card-header h5,
    html body .policy-card .policy-card-header h5 *,
    html body .policy-card .policy-card-header .policy-title,
    html body .policy-card .policy-card-header .policy-title *,
    html body .policy-card .policy-card-header i,
    html body .policy-card .policy-card-header i[class^="bx"],
    html body .policy-card .policy-card-header i[class*=" bx"],
    html body .policy-card .policy-card-header .policy-icon-wrapper,
    html body .policy-card .policy-card-header .policy-icon-wrapper i,
    html body .policy-card .policy-card-header .policy-meta-row,
    html body .policy-card .policy-card-header .policy-meta-row *,
    html body .policy-card .policy-card-header .policy-code-pill,
    html body .policy-card .policy-card-header .policy-code-pill *,
    html body .policy-card .policy-card-header .policy-updated-text,
    html body .policy-card .policy-card-header .policy-updated-text *,
    html body .policy-card .policy-card-header .btn-policy-toggle,
    html body .policy-card .policy-card-header .btn-policy-toggle * {
        color: #ffffff !important;
    }

    html body .policy-card .policy-card-header h5.policy-title,
    html body .policy-card .policy-card-header h5.policy-title span {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .policy-card-body {
        padding: 1.5rem 1.75rem;
        background: #ffffff;
    }
    .policy-param-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1rem 1.15rem;
        display: flex;
        align-items: center;
        gap: 0.85rem;
        height: 100%;
        transition: all 0.2s ease;
    }
    .policy-param-box:hover {
        background: #ffffff;
        border-color: rgba(16, 185, 129, 0.4);
        box-shadow: 0 8px 20px -4px rgba(15, 116, 76, 0.08);
        transform: translateY(-2px);
    }
    .policy-param-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #ecfdf5;
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .policy-param-icon i {
        color: #059669 !important;
    }
    .policy-param-label {
        font-size: 0.775rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 0.2rem;
    }
    .policy-param-value {
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--rec-green-900);
    }

    /* Pipeline Flow */
    .pipeline-section {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1rem 1.25rem;
    }
    .pipeline-flow-wrapper {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .pipeline-step-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.4rem 0.85rem;
        background: #ffffff;
        color: var(--rec-green-800);
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 10px;
        font-size: 0.825rem;
        font-weight: 700;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    }
    .pipeline-num {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #059669;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 800;
    }
    .pipeline-arrow {
        color: #94a3b8;
        font-size: 1.25rem;
    }

    /* Policy Footer Cards */
    .policy-footer-card {
        display: flex;
        gap: 0.85rem;
        align-items: flex-start;
        padding: 0.85rem 1rem;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        height: 100%;
    }
    .policy-footer-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #ecfdf5;
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .policy-footer-icon i {
        color: #059669 !important;
    }
    .policy-footer-title {
        font-size: 0.825rem;
        font-weight: 700;
        color: var(--rec-green-900);
        margin-bottom: 0.2rem;
    }
    .policy-footer-desc {
        font-size: 0.8rem;
        color: #64748b;
        margin: 0;
        line-height: 1.4;
    }

    /* ===== METRIC CARDS ===== */
    .rec-metric-card {
        background: #ffffff;
        border: 1px solid var(--rec-border);
        border-radius: 18px;
        padding: 1.25rem 1.35rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 8px 24px -6px rgba(15, 116, 76, 0.05);
        margin-bottom: 1.5rem;
        transition: all 0.25s ease;
    }
    .rec-metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 28px -6px rgba(15, 116, 76, 0.12);
        border-color: rgba(16, 185, 129, 0.35);
    }
    .rec-metric-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    .rec-metric-icon.emerald { background: #ecfdf5; color: #059669; }
    .rec-metric-icon.blue { background: #eff6ff; color: #2563eb; }
    .rec-metric-icon.amber { background: #fffbeb; color: #d97706; }
    .rec-metric-icon.green { background: #f0fdf4; color: #16a34a; }
    .rec-metric-icon i { color: inherit !important; }
    .rec-metric-info h4 {
        margin: 0 0 0.15rem 0;
        font-weight: 800;
        color: var(--rec-green-900);
        font-size: 1.5rem;
        letter-spacing: -0.02em;
    }
    .rec-metric-info span {
        font-size: 0.825rem;
        color: #64748b;
        font-weight: 600;
    }

    /* ===== FILTER CARD ===== */
    .rec-filter-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid var(--rec-border);
        box-shadow: 0 8px 24px -6px rgba(15, 116, 76, 0.04);
        margin-bottom: 1.5rem;
        padding: 1.25rem 1.5rem;
    }

    /* ===== TABLE CARD ===== */
    .rec-table-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid var(--rec-border);
        box-shadow: 0 12px 30px -8px rgba(15, 116, 76, 0.06);
        overflow: hidden;
    }
    .rec-table th {
        background: #f8fafc;
        font-weight: 700;
        color: #475569;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .rec-table td {
        padding: 1.1rem 1.25rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
    .rec-table tbody tr {
        transition: background-color 0.2s ease;
    }
    .rec-table tbody tr:hover {
        background: #f0fdf4;
    }
    .badge-employment {
        background-color: #ecfdf5;
        color: #065f46;
        border: 1px solid rgba(16, 185, 129, 0.2);
        font-weight: 700;
        font-size: 0.75rem;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        display: inline-block;
        margin-top: 0.25rem;
    }

    :root {
        --rec-badge-bg: #ecfdf5;
        --rec-badge-color: #065f46;
        --rec-badge-border: rgba(16, 185, 129, 0.25);
        --rec-pill-bg: #ffffff;
        --rec-pill-color: #065f46;
        --rec-pill-border: rgba(16, 185, 129, 0.2);
        --rec-btn-icon-bg: #f3f4f6;
        --rec-btn-icon-border: #e5e7eb;
        --rec-btn-icon-color: #475569;
    }

    html[data-pms-theme="dark"],
    html[data-bs-theme="dark"],
    html[data-theme="dark"],
    html.dark,
    body[data-pms-theme="dark"],
    body[data-bs-theme="dark"],
    body.dark,
    [data-pms-theme="dark"],
    [data-bs-theme="dark"],
    .dark {
        --rec-badge-bg: rgba(16, 185, 129, 0.18);
        --rec-badge-color: #7af0b5;
        --rec-badge-border: rgba(122, 240, 181, 0.35);
        --rec-pill-bg: #0d1a14;
        --rec-pill-color: #f8fffb;
        --rec-pill-border: rgba(122, 240, 181, 0.35);
        --rec-btn-icon-bg: #183026;
        --rec-btn-icon-border: rgba(122, 240, 181, 0.35);
        --rec-btn-icon-color: #7af0b5;
    }

    /* ===== DARK MODE COMPREHENSIVE ADJUSTMENTS ===== */
    html[data-pms-theme="dark"] .rec-header-card,
    html[data-bs-theme="dark"] .rec-header-card,
    html[data-theme="dark"] .rec-header-card,
    html.dark .rec-header-card,
    body[data-pms-theme="dark"] .rec-header-card,
    [data-pms-theme="dark"] .rec-header-card,
    .dark .rec-header-card,
    html[data-pms-theme="dark"] .policy-card,
    html[data-bs-theme="dark"] .policy-card,
    html[data-theme="dark"] .policy-card,
    html.dark .policy-card,
    body[data-pms-theme="dark"] .policy-card,
    [data-pms-theme="dark"] .policy-card,
    .dark .policy-card,
    html[data-pms-theme="dark"] .policy-card-body,
    html[data-bs-theme="dark"] .policy-card-body,
    html[data-theme="dark"] .policy-card-body,
    html.dark .policy-card-body,
    body[data-pms-theme="dark"] .policy-card-body,
    [data-pms-theme="dark"] .policy-card-body,
    .dark .policy-card-body,
    html[data-pms-theme="dark"] .rec-metric-card,
    html[data-bs-theme="dark"] .rec-metric-card,
    html[data-theme="dark"] .rec-metric-card,
    html.dark .rec-metric-card,
    body[data-pms-theme="dark"] .rec-metric-card,
    [data-pms-theme="dark"] .rec-metric-card,
    .dark .rec-metric-card,
    html[data-pms-theme="dark"] .rec-filter-card,
    html[data-bs-theme="dark"] .rec-filter-card,
    html[data-theme="dark"] .rec-filter-card,
    html.dark .rec-filter-card,
    body[data-pms-theme="dark"] .rec-filter-card,
    [data-pms-theme="dark"] .rec-filter-card,
    .dark .rec-filter-card,
    html[data-pms-theme="dark"] .rec-table-card,
    html[data-bs-theme="dark"] .rec-table-card,
    html[data-theme="dark"] .rec-table-card,
    html.dark .rec-table-card,
    body[data-pms-theme="dark"] .rec-table-card,
    [data-pms-theme="dark"] .rec-table-card,
    .dark .rec-table-card {
        background: #102119 !important;
        border-color: rgba(122, 240, 181, 0.18) !important;
    }

    /* 1. Header Live Portal Badge */
    html[data-pms-theme="dark"] .rec-live-badge,
    html[data-bs-theme="dark"] .rec-live-badge,
    html[data-theme="dark"] .rec-live-badge,
    html.dark .rec-live-badge,
    body[data-pms-theme="dark"] .rec-live-badge,
    [data-pms-theme="dark"] .rec-live-badge,
    .dark .rec-live-badge,
    html[data-pms-theme="dark"] .rec-live-badge *,
    html[data-bs-theme="dark"] .rec-live-badge *,
    html[data-theme="dark"] .rec-live-badge *,
    html.dark .rec-live-badge *,
    body[data-pms-theme="dark"] .rec-live-badge *,
    [data-pms-theme="dark"] .rec-live-badge *,
    .dark .rec-live-badge * {
        background: rgba(16, 185, 129, 0.18) !important;
        border-color: rgba(122, 240, 181, 0.35) !important;
        color: #7af0b5 !important;
        -webkit-text-fill-color: #7af0b5 !important;
        font-weight: 750 !important;
    }

    html[data-pms-theme="dark"] .rec-live-badge .live-pulse-dot,
    html[data-bs-theme="dark"] .rec-live-badge .live-pulse-dot,
    html[data-theme="dark"] .rec-live-badge .live-pulse-dot,
    html.dark .rec-live-badge .live-pulse-dot {
        background-color: #34d399 !important;
        box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.7);
    }

    /* 2. Add New Requirement Button */
    html[data-pms-theme="dark"] .btn-rec-primary,
    html[data-bs-theme="dark"] .btn-rec-primary,
    html[data-theme="dark"] .btn-rec-primary,
    html.dark .btn-rec-primary,
    body[data-pms-theme="dark"] .btn-rec-primary,
    [data-pms-theme="dark"] .btn-rec-primary,
    .dark .btn-rec-primary {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        border: none !important;
        box-shadow: 0 8px 20px -4px rgba(16, 185, 129, 0.45) !important;
    }

    html[data-pms-theme="dark"] .btn-rec-primary *,
    html[data-bs-theme="dark"] .btn-rec-primary *,
    html[data-theme="dark"] .btn-rec-primary *,
    html.dark .btn-rec-primary *,
    body[data-pms-theme="dark"] .btn-rec-primary *,
    [data-pms-theme="dark"] .btn-rec-primary *,
    .dark .btn-rec-primary *,
    html[data-pms-theme="dark"] .btn-rec-primary i,
    html[data-bs-theme="dark"] .btn-rec-primary i,
    html[data-theme="dark"] .btn-rec-primary i,
    html.dark .btn-rec-primary i,
    body[data-pms-theme="dark"] .btn-rec-primary i,
    [data-pms-theme="dark"] .btn-rec-primary i,
    .dark .btn-rec-primary i,
    html[data-pms-theme="dark"] .btn-rec-primary span,
    html[data-bs-theme="dark"] .btn-rec-primary span,
    html[data-theme="dark"] .btn-rec-primary span,
    html.dark .btn-rec-primary span,
    body[data-pms-theme="dark"] .btn-rec-primary span,
    [data-pms-theme="dark"] .btn-rec-primary span,
    .dark .btn-rec-primary span {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        fill: #ffffff !important;
    }

    html[data-pms-theme="dark"] .btn-rec-outline,
    html[data-bs-theme="dark"] .btn-rec-outline,
    html[data-theme="dark"] .btn-rec-outline,
    html.dark .btn-rec-outline,
    body[data-pms-theme="dark"] .btn-rec-outline,
    [data-pms-theme="dark"] .btn-rec-outline,
    .dark .btn-rec-outline {
        background: #14281e !important;
        color: #7af0b5 !important;
        -webkit-text-fill-color: #7af0b5 !important;
        border: 1px solid rgba(122, 240, 181, 0.35) !important;
    }

    html[data-pms-theme="dark"] .btn-rec-outline *,
    html[data-bs-theme="dark"] .btn-rec-outline *,
    html[data-theme="dark"] .btn-rec-outline *,
    html.dark .btn-rec-outline *,
    body[data-pms-theme="dark"] .btn-rec-outline *,
    [data-pms-theme="dark"] .btn-rec-outline *,
    .dark .btn-rec-outline *,
    html[data-pms-theme="dark"] .btn-rec-outline i,
    html[data-bs-theme="dark"] .btn-rec-outline i,
    html[data-theme="dark"] .btn-rec-outline i,
    html.dark .btn-rec-outline i,
    html[data-pms-theme="dark"] .btn-rec-outline span,
    html[data-bs-theme="dark"] .btn-rec-outline span,
    html[data-theme="dark"] .btn-rec-outline span,
    html.dark .btn-rec-outline span {
        color: #7af0b5 !important;
        -webkit-text-fill-color: #7af0b5 !important;
    }

    /* 3. Pipeline Section & Step Pills (1 to 6) */
    html[data-pms-theme="dark"] .pipeline-section,
    html[data-bs-theme="dark"] .pipeline-section,
    html[data-theme="dark"] .pipeline-section,
    html.dark .pipeline-section,
    body[data-pms-theme="dark"] .pipeline-section,
    [data-pms-theme="dark"] .pipeline-section,
    .dark .pipeline-section {
        background: #14281e !important;
        border: 1px solid rgba(122, 240, 181, 0.2) !important;
    }

    html[data-pms-theme="dark"] .pipeline-section .policy-param-label,
    html[data-bs-theme="dark"] .pipeline-section .policy-param-label,
    html[data-theme="dark"] .pipeline-section .policy-param-label,
    html.dark .pipeline-section .policy-param-label,
    body[data-pms-theme="dark"] .pipeline-section .policy-param-label,
    [data-pms-theme="dark"] .pipeline-section .policy-param-label,
    .dark .pipeline-section .policy-param-label {
        color: #a7f3d0 !important;
        -webkit-text-fill-color: #a7f3d0 !important;
        font-weight: 750 !important;
    }

    html[data-pms-theme="dark"] .pipeline-step-pill,
    html[data-bs-theme="dark"] .pipeline-step-pill,
    html[data-theme="dark"] .pipeline-step-pill,
    html.dark .pipeline-step-pill,
    body[data-pms-theme="dark"] .pipeline-step-pill,
    [data-pms-theme="dark"] .pipeline-step-pill,
    .dark .pipeline-step-pill {
        background: #0d1a14 !important;
        border: 1px solid rgba(122, 240, 181, 0.35) !important;
        color: #f8fffb !important;
        -webkit-text-fill-color: #f8fffb !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
    }

    html[data-pms-theme="dark"] .pipeline-step-pill *,
    html[data-bs-theme="dark"] .pipeline-step-pill *,
    html[data-theme="dark"] .pipeline-step-pill *,
    html.dark .pipeline-step-pill *,
    body[data-pms-theme="dark"] .pipeline-step-pill *,
    [data-pms-theme="dark"] .pipeline-step-pill *,
    .dark .pipeline-step-pill *,
    html[data-pms-theme="dark"] .pipeline-step-pill span,
    html[data-bs-theme="dark"] .pipeline-step-pill span,
    html[data-theme="dark"] .pipeline-step-pill span,
    html.dark .pipeline-step-pill span,
    body[data-pms-theme="dark"] .pipeline-step-pill span,
    [data-pms-theme="dark"] .pipeline-step-pill span,
    .dark .pipeline-step-pill span {
        color: #f8fffb !important;
        -webkit-text-fill-color: #f8fffb !important;
        font-weight: 700 !important;
    }

    html[data-pms-theme="dark"] .pipeline-num,
    html[data-bs-theme="dark"] .pipeline-num,
    html[data-theme="dark"] .pipeline-num,
    html.dark .pipeline-num,
    body[data-pms-theme="dark"] .pipeline-num,
    [data-pms-theme="dark"] .pipeline-num,
    .dark .pipeline-num {
        background: linear-gradient(135deg, #10b981, #059669) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 800 !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25) !important;
    }

    html[data-pms-theme="dark"] .pipeline-arrow,
    html[data-bs-theme="dark"] .pipeline-arrow,
    html[data-theme="dark"] .pipeline-arrow,
    html.dark .pipeline-arrow,
    body[data-pms-theme="dark"] .pipeline-arrow,
    [data-pms-theme="dark"] .pipeline-arrow,
    .dark .pipeline-arrow {
        color: #7af0b5 !important;
        -webkit-text-fill-color: #7af0b5 !important;
    }

    /* 4. Policy Param Boxes & Policy Footer Cards */
    html[data-pms-theme="dark"] .policy-param-box,
    html[data-bs-theme="dark"] .policy-param-box,
    html[data-theme="dark"] .policy-param-box,
    html.dark .policy-param-box,
    body[data-pms-theme="dark"] .policy-param-box,
    [data-pms-theme="dark"] .policy-param-box,
    .dark .policy-param-box,
    html[data-pms-theme="dark"] .policy-footer-card,
    html[data-bs-theme="dark"] .policy-footer-card,
    html[data-theme="dark"] .policy-footer-card,
    html.dark .policy-footer-card,
    body[data-pms-theme="dark"] .policy-footer-card,
    [data-pms-theme="dark"] .policy-footer-card,
    .dark .policy-footer-card {
        background: #14281e !important;
        border: 1px solid rgba(122, 240, 181, 0.2) !important;
    }

    html[data-pms-theme="dark"] .policy-param-icon,
    html[data-bs-theme="dark"] .policy-param-icon,
    html[data-theme="dark"] .policy-param-icon,
    html.dark .policy-param-icon,
    body[data-pms-theme="dark"] .policy-param-icon,
    [data-pms-theme="dark"] .policy-param-icon,
    .dark .policy-param-icon,
    html[data-pms-theme="dark"] .policy-footer-icon,
    html[data-bs-theme="dark"] .policy-footer-icon,
    html[data-theme="dark"] .policy-footer-icon,
    html.dark .policy-footer-icon,
    body[data-pms-theme="dark"] .policy-footer-icon,
    [data-pms-theme="dark"] .policy-footer-icon,
    .dark .policy-footer-icon {
        background: rgba(16, 185, 129, 0.2) !important;
        border: 1px solid rgba(122, 240, 181, 0.3) !important;
        color: #7af0b5 !important;
    }

    html[data-pms-theme="dark"] .policy-param-icon i,
    html[data-bs-theme="dark"] .policy-param-icon i,
    html[data-theme="dark"] .policy-param-icon i,
    html.dark .policy-param-icon i,
    html[data-pms-theme="dark"] .policy-footer-icon i,
    html[data-bs-theme="dark"] .policy-footer-icon i,
    html[data-theme="dark"] .policy-footer-icon i,
    html.dark .policy-footer-icon i {
        color: #7af0b5 !important;
        -webkit-text-fill-color: #7af0b5 !important;
    }

    html[data-pms-theme="dark"] .policy-param-label,
    html[data-bs-theme="dark"] .policy-param-label,
    html[data-theme="dark"] .policy-param-label,
    html.dark .policy-param-label {
        color: #a7f3d0 !important;
        -webkit-text-fill-color: #a7f3d0 !important;
        font-weight: 750 !important;
    }

    html[data-pms-theme="dark"] .policy-param-value,
    html[data-bs-theme="dark"] .policy-param-value,
    html[data-theme="dark"] .policy-param-value,
    html.dark .policy-param-value,
    html[data-pms-theme="dark"] .policy-footer-title,
    html[data-bs-theme="dark"] .policy-footer-title,
    html[data-theme="dark"] .policy-footer-title,
    html.dark .policy-footer-title {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 750 !important;
    }

    html[data-pms-theme="dark"] .policy-param-value small,
    html[data-bs-theme="dark"] .policy-param-value small,
    html[data-theme="dark"] .policy-param-value small,
    html.dark .policy-param-value small,
    html[data-pms-theme="dark"] .policy-footer-desc,
    html[data-bs-theme="dark"] .policy-footer-desc,
    html[data-theme="dark"] .policy-footer-desc,
    html.dark .policy-footer-desc {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    /* 5. General Typography, Cards & Tables */
    html[data-pms-theme="dark"] .rec-title,
    html[data-bs-theme="dark"] .rec-title,
    html[data-theme="dark"] .rec-title,
    html.dark .rec-title,
    html[data-pms-theme="dark"] .rec-metric-info h4,
    html[data-bs-theme="dark"] .rec-metric-info h4,
    html[data-theme="dark"] .rec-metric-info h4,
    html.dark .rec-metric-info h4 {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .rec-subtitle,
    html[data-bs-theme="dark"] .rec-subtitle,
    html[data-theme="dark"] .rec-subtitle,
    html.dark .rec-subtitle {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .breadcrumb-custom .active-crumb,
    html[data-bs-theme="dark"] .breadcrumb-custom .active-crumb,
    html[data-theme="dark"] .breadcrumb-custom .active-crumb,
    html.dark .breadcrumb-custom .active-crumb {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .breadcrumb-custom a,
    html[data-bs-theme="dark"] .breadcrumb-custom a,
    html[data-theme="dark"] .breadcrumb-custom a,
    html.dark .breadcrumb-custom a {
        color: #7af0b5 !important;
        -webkit-text-fill-color: #7af0b5 !important;
    }

    html[data-pms-theme="dark"] .rec-metric-info span,
    html[data-bs-theme="dark"] .rec-metric-info span,
    html[data-theme="dark"] .rec-metric-info span,
    html.dark .rec-metric-info span {
        color: #a7f3d0 !important;
        -webkit-text-fill-color: #a7f3d0 !important;
        font-weight: 650 !important;
    }

    html[data-pms-theme="dark"] .rec-filter-card label,
    html[data-bs-theme="dark"] .rec-filter-card label,
    html[data-theme="dark"] .rec-filter-card label,
    html.dark .rec-filter-card label {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .rec-filter-card .form-control,
    html[data-bs-theme="dark"] .rec-filter-card .form-control,
    html[data-theme="dark"] .rec-filter-card .form-control,
    html.dark .rec-filter-card .form-control,
    html[data-pms-theme="dark"] .rec-filter-card .form-select,
    html[data-bs-theme="dark"] .rec-filter-card .form-select,
    html[data-theme="dark"] .rec-filter-card .form-select,
    html.dark .rec-filter-card .form-select,
    html[data-pms-theme="dark"] .rec-filter-card .input-group-text,
    html[data-bs-theme="dark"] .rec-filter-card .input-group-text,
    html[data-theme="dark"] .rec-filter-card .input-group-text,
    html.dark .rec-filter-card .input-group-text {
        background: #183026 !important;
        border-color: rgba(122, 240, 181, 0.25) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .rec-table th,
    html[data-bs-theme="dark"] .rec-table th,
    html[data-theme="dark"] .rec-table th,
    html.dark .rec-table th {
        background: #0d1a14 !important;
        color: #a7f3d0 !important;
        -webkit-text-fill-color: #a7f3d0 !important;
        border-bottom: 1px solid rgba(122, 240, 181, 0.18) !important;
    }

    html[data-pms-theme="dark"] .rec-table td,
    html[data-bs-theme="dark"] .rec-table td,
    html[data-theme="dark"] .rec-table td,
    html.dark .rec-table td {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
        border-bottom: 1px solid rgba(122, 240, 181, 0.08) !important;
    }

    html[data-pms-theme="dark"] .rec-table td .fw-bold,
    html[data-bs-theme="dark"] .rec-table td .fw-bold,
    html[data-theme="dark"] .rec-table td .fw-bold,
    html.dark .rec-table td .fw-bold,
    html[data-pms-theme="dark"] .rec-table td .fs-6,
    html[data-bs-theme="dark"] .rec-table td .fs-6,
    html[data-theme="dark"] .rec-table td .fs-6,
    html.dark .rec-table td .fs-6 {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .rec-table td .text-secondary,
    html[data-bs-theme="dark"] .rec-table td .text-secondary,
    html[data-theme="dark"] .rec-table td .text-secondary,
    html.dark .rec-table td .text-secondary,
    html[data-pms-theme="dark"] .rec-table td .text-muted,
    html[data-bs-theme="dark"] .rec-table td .text-muted,
    html[data-theme="dark"] .rec-table td .text-muted,
    html.dark .rec-table td .text-muted {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    html[data-pms-theme="dark"] .rec-table tbody tr:hover,
    html[data-bs-theme="dark"] .rec-table tbody tr:hover,
    html[data-theme="dark"] .rec-table tbody tr:hover,
    html.dark .rec-table tbody tr:hover,
    html[data-pms-theme="dark"] .rec-table tbody tr:hover td,
    html[data-bs-theme="dark"] .rec-table tbody tr:hover td,
    html[data-theme="dark"] .rec-table tbody tr:hover td,
    html.dark .rec-table tbody tr:hover td {
        background: rgba(16, 185, 129, 0.08) !important;
    }

    html[data-pms-theme="dark"] .badge-employment,
    html[data-bs-theme="dark"] .badge-employment,
    html[data-theme="dark"] .badge-employment,
    html.dark .badge-employment {
        background: rgba(16, 185, 129, 0.18) !important;
        color: #7af0b5 !important;
        -webkit-text-fill-color: #7af0b5 !important;
        border-color: rgba(122, 240, 181, 0.35) !important;
    }

    /* Table Action Icon Buttons (.btn-icon, .btn-light) */
    .rec-table .btn-icon,
    .rec-table .btn-light {
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--rec-btn-icon-bg, #f3f4f6) !important;
        border: 1px solid var(--rec-btn-icon-border, #e5e7eb) !important;
        color: var(--rec-btn-icon-color, #475569) !important;
        transition: all 0.25s ease;
    }

    .rec-table .btn-icon i,
    .rec-table .btn-light i {
        font-size: 1.15rem;
        color: var(--rec-btn-icon-color, #475569) !important;
        -webkit-text-fill-color: var(--rec-btn-icon-color, #475569) !important;
    }

    html[data-pms-theme="dark"] .rec-table .btn-icon,
    html[data-bs-theme="dark"] .rec-table .btn-icon,
    html[data-theme="dark"] .rec-table .btn-icon,
    html.dark .rec-table .btn-icon,
    body[data-pms-theme="dark"] .rec-table .btn-icon,
    [data-pms-theme="dark"] .rec-table .btn-icon,
    .dark .rec-table .btn-icon,
    html[data-pms-theme="dark"] .rec-table .btn-light,
    html[data-bs-theme="dark"] .rec-table .btn-light,
    html[data-theme="dark"] .rec-table .btn-light,
    html.dark .rec-table .btn-light,
    body[data-pms-theme="dark"] .rec-table .btn-light,
    [data-pms-theme="dark"] .rec-table .btn-light,
    .dark .rec-table .btn-light {
        background: #183026 !important;
        border: 1px solid rgba(122, 240, 181, 0.35) !important;
        color: #7af0b5 !important;
        -webkit-text-fill-color: #7af0b5 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
    }

    html[data-pms-theme="dark"] .rec-table .btn-icon *,
    html[data-bs-theme="dark"] .rec-table .btn-icon *,
    html[data-theme="dark"] .rec-table .btn-icon *,
    html.dark .rec-table .btn-icon *,
    body[data-pms-theme="dark"] .rec-table .btn-icon *,
    [data-pms-theme="dark"] .rec-table .btn-icon *,
    .dark .rec-table .btn-icon *,
    html[data-pms-theme="dark"] .rec-table .btn-icon i,
    html[data-bs-theme="dark"] .rec-table .btn-icon i,
    html[data-theme="dark"] .rec-table .btn-icon i,
    html.dark .rec-table .btn-icon i,
    body[data-pms-theme="dark"] .rec-table .btn-icon i,
    [data-pms-theme="dark"] .rec-table .btn-icon i,
    .dark .rec-table .btn-icon i,
    html[data-pms-theme="dark"] .rec-table .btn-light i,
    html[data-bs-theme="dark"] .rec-table .btn-light i,
    html[data-theme="dark"] .rec-table .btn-light i,
    html.dark .rec-table .btn-light i,
    body[data-pms-theme="dark"] .rec-table .btn-light i,
    [data-pms-theme="dark"] .rec-table .btn-light i,
    .dark .rec-table .btn-light i {
        color: #7af0b5 !important;
        -webkit-text-fill-color: #7af0b5 !important;
    }

    html[data-pms-theme="dark"] .rec-table .btn-icon:hover,
    html[data-bs-theme="dark"] .rec-table .btn-icon:hover,
    html[data-theme="dark"] .rec-table .btn-icon:hover,
    html.dark .rec-table .btn-icon:hover,
    body[data-pms-theme="dark"] .rec-table .btn-icon:hover,
    [data-pms-theme="dark"] .rec-table .btn-icon:hover,
    .dark .rec-table .btn-icon:hover,
    html[data-pms-theme="dark"] .rec-table .btn-light:hover,
    html[data-bs-theme="dark"] .rec-table .btn-light:hover,
    html[data-theme="dark"] .rec-table .btn-light:hover,
    html.dark .rec-table .btn-light:hover,
    body[data-pms-theme="dark"] .rec-table .btn-light:hover,
    [data-pms-theme="dark"] .rec-table .btn-light:hover,
    .dark .rec-table .btn-light:hover {
        background: #1f3e31 !important;
        border-color: rgba(122, 240, 181, 0.6) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        transform: scale(1.08) !important;
    }

    html[data-pms-theme="dark"] .rec-table .btn-icon:hover i,
    html[data-bs-theme="dark"] .rec-table .btn-icon:hover i,
    html[data-theme="dark"] .rec-table .btn-icon:hover i,
    html.dark .rec-table .btn-icon:hover i,
    html[data-pms-theme="dark"] .rec-table .btn-light:hover i,
    html[data-bs-theme="dark"] .rec-table .btn-light:hover i,
    html[data-theme="dark"] .rec-table .btn-light:hover i,
    html.dark .rec-table .btn-light:hover i {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    /* Modals in Dark Mode */
    html[data-pms-theme="dark"] .modal-content,
    html[data-bs-theme="dark"] .modal-content,
    html[data-theme="dark"] .modal-content,
    html.dark .modal-content {
        background: #102119 !important;
        border: 1px solid rgba(122, 240, 181, 0.25) !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .modal-body,
    html[data-bs-theme="dark"] .modal-body,
    html[data-theme="dark"] .modal-body,
    html.dark .modal-body {
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .modal-body .bg-light,
    html[data-bs-theme="dark"] .modal-body .bg-light,
    html[data-theme="dark"] .modal-body .bg-light,
    html.dark .modal-body .bg-light {
        background: #183026 !important;
        border: 1px solid rgba(122, 240, 181, 0.18) !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .modal-body .text-dark,
    html[data-bs-theme="dark"] .modal-body .text-dark,
    html[data-theme="dark"] .modal-body .text-dark,
    html.dark .modal-body .text-dark,
    html[data-pms-theme="dark"] .modal-body strong,
    html[data-bs-theme="dark"] .modal-body strong,
    html[data-theme="dark"] .modal-body strong,
    html.dark .modal-body strong {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .modal-body .text-muted,
    html[data-bs-theme="dark"] .modal-body .text-muted,
    html[data-theme="dark"] .modal-body .text-muted,
    html.dark .modal-body .text-muted {
        color: #a7f3d0 !important;
        -webkit-text-fill-color: #a7f3d0 !important;
    }

    /* ===== MODAL ALERT BOX (LIGHT & DARK MODE) ===== */
    .rec-notification-alert {
        background: #ecfdf5 !important;
        border: 1px solid #a7f3d0 !important;
        color: #065f46 !important;
    }
    .rec-notification-alert div {
        color: #065f46 !important;
        -webkit-text-fill-color: #065f46 !important;
    }
    .rec-notification-alert strong,
    .rec-notification-alert .rec-alert-heading {
        color: #064e3b !important;
        -webkit-text-fill-color: #064e3b !important;
        font-weight: 700 !important;
    }
    .rec-notification-alert i,
    .rec-notification-alert-icon {
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
    }

    /* Modal alert box in Dark Mode */
    html[data-pms-theme="dark"] .modal-body .rec-notification-alert,
    html[data-bs-theme="dark"] .modal-body .rec-notification-alert,
    html[data-theme="dark"] .modal-body .rec-notification-alert,
    html.dark .modal-body .rec-notification-alert,
    body[data-pms-theme="dark"] .modal-body .rec-notification-alert,
    [data-pms-theme="dark"] .modal-body .rec-notification-alert,
    .dark .modal-body .rec-notification-alert {
        background: rgba(16, 185, 129, 0.16) !important;
        border: 1px solid rgba(122, 240, 181, 0.35) !important;
        color: #e2fbf0 !important;
    }

    html[data-pms-theme="dark"] .modal-body .rec-notification-alert div,
    html[data-bs-theme="dark"] .modal-body .rec-notification-alert div,
    html[data-theme="dark"] .modal-body .rec-notification-alert div,
    html.dark .modal-body .rec-notification-alert div,
    body[data-pms-theme="dark"] .modal-body .rec-notification-alert div,
    [data-pms-theme="dark"] .modal-body .rec-notification-alert div,
    .dark .modal-body .rec-notification-alert div {
        color: #e2fbf0 !important;
        -webkit-text-fill-color: #e2fbf0 !important;
    }

    html[data-pms-theme="dark"] .modal-body .rec-notification-alert strong,
    html[data-bs-theme="dark"] .modal-body .rec-notification-alert strong,
    html[data-theme="dark"] .modal-body .rec-notification-alert strong,
    html.dark .modal-body .rec-notification-alert strong,
    body[data-pms-theme="dark"] .modal-body .rec-notification-alert strong,
    [data-pms-theme="dark"] .modal-body .rec-notification-alert strong,
    .dark .modal-body .rec-notification-alert strong,
    html[data-pms-theme="dark"] .modal-body .rec-notification-alert .rec-alert-heading,
    html[data-bs-theme="dark"] .modal-body .rec-notification-alert .rec-alert-heading,
    html[data-theme="dark"] .modal-body .rec-notification-alert .rec-alert-heading,
    html.dark .modal-body .rec-notification-alert .rec-alert-heading,
    body[data-pms-theme="dark"] .modal-body .rec-notification-alert .rec-alert-heading,
    [data-pms-theme="dark"] .modal-body .rec-notification-alert .rec-alert-heading,
    .dark .modal-body .rec-notification-alert .rec-alert-heading {
        color: #7af0b5 !important;
        -webkit-text-fill-color: #7af0b5 !important;
    }

    html[data-pms-theme="dark"] .modal-body .rec-notification-alert i,
    html[data-bs-theme="dark"] .modal-body .rec-notification-alert i,
    html[data-theme="dark"] .modal-body .rec-notification-alert i,
    html.dark .modal-body .rec-notification-alert i,
    body[data-pms-theme="dark"] .modal-body .rec-notification-alert i,
    [data-pms-theme="dark"] .modal-body .rec-notification-alert i,
    .dark .modal-body .rec-notification-alert i,
    html[data-pms-theme="dark"] .modal-body .rec-notification-alert-icon,
    html[data-bs-theme="dark"] .modal-body .rec-notification-alert-icon,
    html[data-theme="dark"] .modal-body .rec-notification-alert-icon,
    html.dark .modal-body .rec-notification-alert-icon,
    body[data-pms-theme="dark"] .modal-body .rec-notification-alert-icon,
    [data-pms-theme="dark"] .modal-body .rec-notification-alert-icon,
    .dark .modal-body .rec-notification-alert-icon {
        color: #7af0b5 !important;
        -webkit-text-fill-color: #7af0b5 !important;
    }

    html[data-pms-theme="dark"] .modal-footer,
    html[data-bs-theme="dark"] .modal-footer,
    html[data-theme="dark"] .modal-footer,
    html.dark .modal-footer {
        background: #0d1a14 !important;
        border-top: 1px solid rgba(122, 240, 181, 0.18) !important;
    }

    @media (max-width: 767.98px) {
        .rec-header-card { padding: 1.25rem; }
        .rec-header-left { flex-direction: column; align-items: flex-start; }
        .rec-header-actions { width: 100%; }
        .rec-header-actions .btn-rec-primary,
        .rec-header-actions .btn-rec-outline { flex: 1 1 auto; justify-content: center; }
        .policy-card-header { padding: 1rem; }
        .policy-header-right { width: 100%; justify-content: space-between; }
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y recruitment-shell">

    {{-- Breadcrumb Navigation --}}
    <div class="breadcrumb-custom rec-animate-in">
        <a href="{{ route('dashboard') }}"><i class="bx bx-home-alt me-1"></i> Dashboard</a>
        <i class="bx bx-chevron-right separator"></i>
        <span>HR</span>
        <i class="bx bx-chevron-right separator"></i>
        <span class="active-crumb"><i class="bx bx-briefcase-alt me-1 text-success"></i> Recruitment & Talent Acquisition</span>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 rec-animate-in" role="alert">
        <i class="bx bx-check-circle me-2 fs-4"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 rec-animate-in" role="alert">
        <i class="bx bx-error-circle me-2 fs-4"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Elevated Page Header Card --}}
    <div class="rec-header-card rec-animate-in">
        <div class="rec-header-left">
            <div class="rec-header-icon" style="background: linear-gradient(135deg, #10b981 0%, #047857 100%) !important; color: #ffffff !important;">
                <i class="bx bx-user-plus" style="color: #ffffff !important; font-size: 1.75rem;"></i>
            </div>
            <div class="rec-header-text">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h1 class="rec-title">Recruitment & Talent Acquisition</h1>
                    <span class="rec-live-badge" style="background: var(--rec-badge-bg, #ecfdf5) !important; color: var(--rec-badge-color, #065f46) !important; -webkit-text-fill-color: var(--rec-badge-color, #065f46) !important; border-color: var(--rec-badge-border, rgba(16, 185, 129, 0.25)) !important;">
                        <span class="live-pulse-dot"></span> Live Portal
                    </span>
                </div>
                <p class="rec-subtitle">
                    Manage corporate job requirements, monitor live recruitment policies, and download requirement specifications.
                </p>
            </div>
        </div>
        <div class="rec-header-actions">
            @if(auth()->user()?->role === 'admin')
            <a href="{{ route('admin.settings.recruitment') }}" class="btn-rec-outline" title="Configure Recruitment Policies">
                <i class="bx bx-cog"></i>
                <span>Recruitment Settings</span>
            </a>
            <button class="btn-rec-primary" data-bs-toggle="modal" data-bs-target="#createRequirementModal" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;">
                <i class="bx bx-plus-circle" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;"></i>
                <span style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;">Add New Requirement</span>
            </button>
            @else
            <span class="badge-rec-readonly">
                <i class="bx bx-lock-alt"></i> Read-Only Mode (Download Available)
            </span>
            @endif
        </div>
    </div>

    {{-- AUTOMATICALLY GENERATED RECRUITMENT POLICY CARD --}}
    <div class="policy-card rec-animate-in">
        <div class="policy-card-header" style="background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #047857 100%) !important; color: #ffffff !important;">
            <div class="policy-header-left">
                <div class="policy-icon-wrapper" style="background: rgba(255, 255, 255, 0.22) !important; border: 1.5px solid rgba(255, 255, 255, 0.45) !important; color: #ffffff !important;">
                    <i class="bx bx-file-find" style="color: #ffffff !important; font-size: 1.55rem;"></i>
                </div>
                <div>
                    <h5 class="policy-title" style="color: #ffffff !important; font-weight: 800; font-size: 1.22rem; margin: 0 0 4px 0;">
                        <span style="color: #ffffff !important;">Automatically Generated Recruitment Policy Card</span>
                    </h5>
                    <div class="policy-meta-row" style="color: rgba(255, 255, 255, 0.92) !important;">
                        <span class="policy-code-pill" style="background: rgba(255, 255, 255, 0.22) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.35) !important;">
                            <i class="bx bx-hash" style="color: #ffffff !important;"></i> <span style="color: #ffffff !important;">{{ $policyCard['code'] }}</span>
                        </span>
                        <span class="policy-divider" style="color: rgba(255, 255, 255, 0.5) !important;">·</span>
                        <span class="policy-updated-text" style="color: rgba(255, 255, 255, 0.92) !important;">
                            <i class="bx bx-time-five" style="color: #ffffff !important;"></i> <span style="color: #ffffff !important;">Updated: {{ $policyCard['generated_at'] }}</span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="policy-header-right">
                <span class="policy-status-pill">
                    <span class="status-pulse-dot"></span> {{ $policyCard['status'] }}
                </span>
                <button class="btn btn-policy-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#policyCardContent" aria-expanded="true" style="color: #ffffff !important; background: rgba(255, 255, 255, 0.18) !important; border: 1px solid rgba(255, 255, 255, 0.45) !important;">
                    <i class="bx bx-chevron-down toggle-chevron" style="color: #ffffff !important;"></i>
                    <span class="toggle-text" style="color: #ffffff !important;">Toggle Details</span>
                </button>
            </div>
        </div>
        <div class="collapse show policy-card-body" id="policyCardContent">
            <div class="row g-3 mb-3">
                <div class="col-lg-3 col-sm-6">
                    <div class="policy-param-box">
                        <div class="policy-param-icon">
                            <i class="bx bx-calendar-check"></i>
                        </div>
                        <div>
                            <div class="policy-param-label">Probation Period</div>
                            <div class="policy-param-value">{{ $policyCard['probation_period'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="policy-param-box">
                        <div class="policy-param-icon">
                            <i class="bx bx-target-lock"></i>
                        </div>
                        <div>
                            <div class="policy-param-label">Target Hiring SLA</div>
                            <div class="policy-param-value">{{ $policyCard['hiring_sla'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="policy-param-box">
                        <div class="policy-param-icon">
                            <i class="bx bx-file"></i>
                        </div>
                        <div>
                            <div class="policy-param-label">Allowed Resume Formats</div>
                            <div class="policy-param-value">{{ $policyCard['allowed_file_types'] }} <small class="text-muted fw-normal">(Max {{ $policyCard['max_resume_size'] }})</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="policy-param-box">
                        <div class="policy-param-icon">
                            <i class="bx bx-bot"></i>
                        </div>
                        <div>
                            <div class="policy-param-label">Candidate Auto-Reply</div>
                            <div class="policy-param-value">
                                @if($policyCard['auto_reply_enabled'])
                                    <span class="text-success d-inline-flex align-items-center gap-1"><i class="bx bx-check-circle"></i> Enabled</span>
                                @else
                                    <span class="text-muted d-inline-flex align-items-center gap-1"><i class="bx bx-x-circle"></i> Disabled</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pipeline-section mb-3">
                <div class="policy-param-label mb-2"><i class="bx bx-git-commit me-1 text-success"></i> Standard Hiring Pipeline Stages</div>
                <div class="pipeline-flow-wrapper">
                    @foreach($policyCard['pipeline_stages'] as $index => $stage)
                        <div class="pipeline-step-pill" style="background: var(--rec-pill-bg, #ffffff) !important; color: var(--rec-pill-color, #065f46) !important; -webkit-text-fill-color: var(--rec-pill-color, #065f46) !important; border-color: var(--rec-pill-border, rgba(16, 185, 129, 0.2)) !important;">
                            <span class="pipeline-num" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;">{{ $index + 1 }}</span>
                            <span style="color: var(--rec-pill-color, #065f46) !important; -webkit-text-fill-color: var(--rec-pill-color, #065f46) !important; font-weight: 700;">{{ trim($stage) }}</span>
                        </div>
                        @if(!$loop->last)
                            <i class="bx bx-chevron-right pipeline-arrow"></i>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="row g-3 pt-2 border-top">
                <div class="col-md-6">
                    <div class="policy-footer-card">
                        <div class="policy-footer-icon">
                            <i class="bx bx-shield-quarter"></i>
                        </div>
                        <div>
                            <div class="policy-footer-title">Equal Opportunity Policy</div>
                            <p class="policy-footer-desc">{{ $policyCard['equal_opportunity'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="policy-footer-card">
                        <div class="policy-footer-icon">
                            <i class="bx bx-gift"></i>
                        </div>
                        <div>
                            <div class="policy-footer-title">Internal Referral & Background Checks</div>
                            <p class="policy-footer-desc">{{ $policyCard['referral_policy'] }} {{ $policyCard['background_check'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- METRIC COUNTER CARDS --}}
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="rec-metric-card">
                <div class="rec-metric-icon emerald">
                    <i class="bx bx-briefcase"></i>
                </div>
                <div class="rec-metric-info">
                    <h4>{{ $totalOpen }}</h4>
                    <span>Active Openings</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="rec-metric-card">
                <div class="rec-metric-icon blue">
                    <i class="bx bx-loader-circle"></i>
                </div>
                <div class="rec-metric-info">
                    <h4>{{ $totalInProgress }}</h4>
                    <span>In Progress</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="rec-metric-card">
                <div class="rec-metric-icon amber">
                    <i class="bx bx-group"></i>
                </div>
                <div class="rec-metric-info">
                    <h4>{{ $totalPositionsOpen }}</h4>
                    <span>Positions Needed</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="rec-metric-card">
                <div class="rec-metric-icon green">
                    <i class="bx bx-check-shield"></i>
                </div>
                <div class="rec-metric-info">
                    <h4>{{ $totalClosed }}</h4>
                    <span>Fulfilled / Closed</span>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER & SEARCH BAR --}}
    <div class="rec-filter-card">
        <form method="GET" action="{{ route('recruitment.index') }}" class="row g-3 align-items-end">
            <div class="col-xl-4 col-lg-3 col-md-6 col-12">
                <label class="form-label fw-bold small text-uppercase" style="letter-spacing: 0.04em;">Search Requirement</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by title, department, location..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                <label class="form-label fw-bold small text-uppercase" style="letter-spacing: 0.04em;">Status Filter</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                <label class="form-label fw-bold small text-uppercase" style="letter-spacing: 0.04em;">Department</label>
                <select name="department_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->dpt_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-6 col-12 d-flex gap-2">
                <button type="submit" class="btn btn-rec-primary flex-grow-1 justify-content-center" style="white-space: nowrap !important; min-width: 90px; height: 38.6px; padding: 0.45rem 1rem;">
                    <i class="bx bx-filter-alt me-1"></i> Filter
                </button>
                <a href="{{ route('recruitment.index') }}" class="btn btn-outline-secondary" title="Reset Filters" style="height: 38.6px; width: 38.6px; min-width: 38.6px; padding: 0; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="bx bx-refresh fs-5"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- REQUIREMENTS TABLE --}}
    <div class="rec-table-card">
        <div class="table-responsive">
            <table class="table rec-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Department</th>
                        <th>Positions</th>
                        <th>Experience</th>
                        <th>Salary & Location</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requirements as $req)
                    <tr>
                        <td>
                            <div class="fw-bold fs-6">{{ $req->title }}</div>
                            <span class="badge-employment">{{ $req->employment_type }}</span>
                        </td>
                        <td>
                            <span class="fw-semibold text-secondary">{{ $req->department_name ?? 'General' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-label-primary px-3 py-2 fs-6 rounded-pill fw-bold">{{ $req->positions }}</span>
                        </td>
                        <td>
                            <span class="text-muted small">{{ $req->experience_required ?? 'Not Specified' }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold small">{{ $req->salary_range ?? 'Negotiable' }}</div>
                            <small class="text-muted"><i class="bx bx-map-pin"></i> {{ $req->location ?? 'Headquarters' }}</small>
                        </td>
                        <td>
                            <span class="badge {{ $req->status_badge }} px-3 py-2">
                                {{ $req->status_label }}
                            </span>
                        </td>
                        <td>
                            <div class="small fw-semibold">{{ $req->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">By {{ $req->creator?->name ?? 'System' }}</small>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex align-items-center gap-1">
                                {{-- DOWNLOAD BUTTON ACCESSIBLE TO ALL ROLES --}}
                                <a href="{{ route('recruitment.download', $req->id) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" title="Download Requirement PDF to Share Personally">
                                    <i class="bx bx-download"></i> Download
                                </a>

                                <button class="btn btn-sm btn-icon btn-light" type="button" data-bs-toggle="modal" data-bs-target="#viewReqModal-{{ $req->id }}" title="View Full Details">
                                    <i class="bx bx-show text-info"></i>
                                </button>

                                @if(auth()->user()?->role === 'admin')
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-icon btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li class="dropdown-header">Admin Management</li>
                                        <li>
                                            <form method="POST" action="{{ route('recruitment.status', $req->id) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="open">
                                                <button type="submit" class="dropdown-item {{ $req->status == 'open' ? 'active' : '' }}">
                                                    <i class="bx bx-check-circle me-2 text-success"></i> Mark Open
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('recruitment.status', $req->id) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="in_progress">
                                                <button type="submit" class="dropdown-item {{ $req->status == 'in_progress' ? 'active' : '' }}">
                                                    <i class="bx bx-loader me-2 text-warning"></i> Mark In Progress
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('recruitment.status', $req->id) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="closed">
                                                <button type="submit" class="dropdown-item {{ $req->status == 'closed' ? 'active' : '' }}">
                                                    <i class="bx bx-x-circle me-2 text-secondary"></i> Mark Closed
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('recruitment.destroy', $req->id) }}" onsubmit="return confirm('Are you sure you want to delete this requirement?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bx bx-trash me-2"></i> Delete Requirement
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- VIEW REQUIREMENT DETAIL MODAL --}}
                    <div class="modal fade" id="viewReqModal-{{ $req->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg">
                                <div class="modal-header" style="background: linear-gradient(135deg, #065f46 0%, #047857 100%); color: #ffffff;">
                                    <h5 class="modal-title fw-bold" style="color: #ffffff !important;"><i class="bx bx-briefcase me-2" style="color: #ffffff !important;"></i> {{ $req->title }}</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <span class="badge {{ $req->status_badge }} px-3 py-2 me-2">{{ $req->status_label }}</span>
                                            <span class="badge-employment">{{ $req->employment_type }}</span>
                                        </div>
                                        <small class="text-muted">Posted on {{ $req->created_at->format('F d, Y') }}</small>
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-sm-6">
                                            <div class="p-3 bg-light rounded-3">
                                                <small class="text-muted d-block fw-bold mb-1">Department</small>
                                                <span class="fw-semibold">{{ $req->department_name ?? 'General' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="p-3 bg-light rounded-3">
                                                <small class="text-muted d-block fw-bold mb-1">Vacancies / Positions</small>
                                                <span class="fw-bold text-success">{{ $req->positions }} Position(s)</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="p-3 bg-light rounded-3">
                                                <small class="text-muted d-block fw-bold mb-1">Experience Required</small>
                                                <span class="fw-semibold">{{ $req->experience_required ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="p-3 bg-light rounded-3">
                                                <small class="text-muted d-block fw-bold mb-1">Salary & Location</small>
                                                <span class="fw-semibold">{{ $req->salary_range ?? 'Negotiable' }} | {{ $req->location ?? 'Headquarters' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    @if($req->description)
                                    <div class="mb-3">
                                        <h6 class="fw-bold mb-2"><i class="bx bx-text me-1 text-success"></i> Job Description</h6>
                                        <div class="p-3 bg-light border rounded-3" style="white-space: pre-line;">{{ $req->description }}</div>
                                    </div>
                                    @endif

                                    @if($req->requirements_summary)
                                    <div class="mb-3">
                                        <h6 class="fw-bold mb-2"><i class="bx bx-list-check me-1 text-success"></i> Candidate Requirements & Qualifications</h6>
                                        <div class="p-3 bg-light border rounded-3" style="white-space: pre-line;">{{ $req->requirements_summary }}</div>
                                    </div>
                                    @endif

                                    <div class="alert alert-info d-flex align-items-center mb-0 mt-3 rounded-3">
                                        <i class="bx bx-bell me-2 fs-4"></i>
                                        <small>Notification regarding this requirement has been broadcasted to all employees, managers, and HR personnel.</small>
                                    </div>
                                </div>
                                <div class="modal-footer d-flex justify-content-between">
                                    <a href="{{ route('recruitment.download', $req->id) }}" class="btn btn-rec-primary">
                                        <i class="bx bx-download me-1"></i> Download Requirement PDF
                                    </a>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="py-4">
                                <i class="bx bx-briefcase-alt-2 fs-1 text-muted mb-2"></i>
                                <h5 class="text-secondary fw-bold">No Job Requirements Found</h5>
                                <p class="text-muted mb-3">There are no job requirements posted at this moment.</p>
                                @if(auth()->user()?->role === 'admin')
                                <button class="btn btn-rec-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createRequirementModal">
                                    <i class="bx bx-plus-circle me-1"></i> Add First Requirement
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(auth()->user()?->role === 'admin')
{{-- CREATE NEW REQUIREMENT MODAL (ADMIN ONLY) --}}
<div class="modal fade" id="createRequirementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST" action="{{ route('recruitment.store') }}">
                @csrf
                <div class="modal-header" style="background: linear-gradient(135deg, #065f46 0%, #047857 100%); color: #ffffff;">
                    <h5 class="modal-title fw-bold" style="color: #ffffff !important;"><i class="bx bx-plus-circle me-2" style="color: #ffffff !important;"></i> Create New Recruitment Requirement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">

                    <div class="alert rec-notification-alert d-flex align-items-center mb-4 rounded-3">
                        <i class="bx bx-bell-plus me-2 fs-3 rec-notification-alert-icon"></i>
                        <div>
                            <strong class="rec-alert-heading">Automatic All-Role Notification:</strong>
                            Submitting this new requirement will automatically broadcast an in-app notification to <strong class="rec-alert-heading">all Employees, Managers, and HR staff</strong>.
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Job Title <sup class="text-danger">*</sup></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Senior Laravel Developer" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Positions Needed <sup class="text-danger">*</sup></label>
                            <input type="number" name="positions" class="form-control" value="1" min="1" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">-- Select Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->dpt_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Employment Type <sup class="text-danger">*</sup></label>
                            <select name="employment_type" class="form-select" required>
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Contract">Contract</option>
                                <option value="Remote">Remote</option>
                                <option value="Internship">Internship</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Experience Required</label>
                            <input type="text" name="experience_required" class="form-control" placeholder="e.g. 2 - 4 Years">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Salary Range</label>
                            <input type="text" name="salary_range" class="form-control" placeholder="e.g. $60,000 - $80,000 / year">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Location</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Headquarters / Remote">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Job Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Provide brief overview of responsibilities and scope..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Candidate Qualifications & Requirements</label>
                            <textarea name="requirements_summary" class="form-control" rows="3" placeholder="Key skills, degrees, technologies, or certifications needed..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-rec-primary px-4"><i class="bx bx-paper-plane me-1"></i> Create & Broadcast Requirement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
