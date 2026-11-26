@extends('admin.layout.app')

@section('title', 'Recruitment & Job Requirements')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');

    /* Preserved row-action symbol contract (tested standalone in recruitment_actions_layer.cjs) */
    html body .recruitment-row-action .recruitment-action-symbol {
        background: transparent !important;
        fill: none !important;
        stroke: #334155 !important;
        opacity: 1 !important;
        flex-shrink: 0;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], body.dark-mode) .recruitment-row-action .recruitment-action-symbol {
        stroke: #e2e8f0 !important;
    }

    :root {
        --rec-bg: #f8fafc;
        --rec-surface: #ffffff;
        --rec-surface-subtle: #f8fafc;
        --rec-surface-hover: #f8fafc;
        --rec-border: #e2e8f0;
        --rec-border-subtle: #f1f5f9;
        --rec-text-dark: #0f172a;
        --rec-text-body: #334155;
        --rec-text-muted: #64748b;
        --rec-text-faint: #94a3b8;
        --rec-primary: #2563eb;
        --rec-primary-hover: #1d4ed8;
        --rec-primary-soft: #eff6ff;
        --rec-primary-border: #dbeafe;
        --rec-shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
        --rec-shadow-sm: 0 2px 6px rgba(15, 23, 42, 0.04);
        --rec-btn-icon-bg: #f8fafc;
        --rec-btn-icon-border: #e2e8f0;
        --rec-btn-icon-color: #475569;
    }

    html:is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark),
    body:is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) {
        --rec-bg: #070b1a;
        --rec-surface: #0f1530;
        --rec-surface-subtle: #141b3d;
        --rec-surface-hover: rgba(37, 99, 235, 0.09);
        --rec-border: rgba(148, 163, 184, 0.18);
        --rec-border-subtle: rgba(148, 163, 184, 0.12);
        --rec-text-dark: #f8fafc;
        --rec-text-body: #e2e8f0;
        --rec-text-muted: #94a3b8;
        --rec-text-faint: #64748b;
        --rec-primary: #3b82f6;
        --rec-primary-hover: #60a5fa;
        --rec-primary-soft: rgba(59, 130, 246, 0.16);
        --rec-primary-border: rgba(96, 165, 250, 0.3);
        --rec-shadow-xs: 0 4px 12px rgba(0, 0, 0, 0.25);
        --rec-shadow-sm: 0 8px 20px rgba(0, 0, 0, 0.32);
        --rec-btn-icon-bg: #141b3d;
        --rec-btn-icon-border: rgba(96, 165, 250, 0.28);
        --rec-btn-icon-color: #60a5fa;
    }

    /* ===== CANVAS & PAGE SHELL ===== */
    body:has(#recruitmentDirectoryPage),
    body:has(#recruitmentDirectoryPage) .layout-page,
    body:has(#recruitmentDirectoryPage) .content-wrapper {
        background: var(--rec-bg) !important;
        background-image: none !important;
    }

    body:has(#recruitmentDirectoryPage) .pms-page-back-navigation {
        padding-bottom: 0 !important;
        margin-bottom: 0.75rem !important;
    }

    #recruitmentDirectoryPage.recruitment-shell {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--rec-text-dark);
        padding-top: 1.25rem !important;
        padding-bottom: 2.5rem !important;
    }

    /* ===== HEADER CARD ===== */
    #recruitmentDirectoryPage .rec-header-card {
        background: var(--rec-surface) !important;
        border: 1px solid var(--rec-border) !important;
        border-radius: 14px !important;
        padding: 1.2rem 1.45rem !important;
        margin-bottom: 1.15rem !important;
        box-shadow: var(--rec-shadow-xs) !important;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        position: relative;
        overflow: hidden;
    }

    #recruitmentDirectoryPage .rec-header-card::before {
        display: none !important;
        content: none !important;
    }

    #recruitmentDirectoryPage .rec-header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 0;
    }

    #recruitmentDirectoryPage .rec-header-icon {
        width: 44px !important;
        height: 44px !important;
        border-radius: 11px !important;
        background: var(--rec-primary-soft) !important;
        border: 1px solid var(--rec-primary-border) !important;
        color: var(--rec-primary) !important;
        box-shadow: none !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem !important;
        flex-shrink: 0;
    }

    #recruitmentDirectoryPage .rec-header-icon i {
        color: var(--rec-primary) !important;
        -webkit-text-fill-color: var(--rec-primary) !important;
        font-size: 1.3rem !important;
    }

    #recruitmentDirectoryPage .rec-header-text {
        min-width: 0;
    }

    #recruitmentDirectoryPage .rec-header-title-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.6rem;
        margin-bottom: 0.2rem;
    }

    #recruitmentDirectoryPage .rec-title {
        font-size: 1.22rem !important;
        font-weight: 700 !important;
        color: var(--rec-text-dark) !important;
        -webkit-text-fill-color: var(--rec-text-dark) !important;
        margin: 0;
        letter-spacing: -0.02em;
        line-height: 1.25;
    }

    #recruitmentDirectoryPage .rec-live-badge {
        display: inline-flex !important;
        align-items: center;
        gap: 6px;
        padding: 0.16rem 0.6rem !important;
        background: #ecfdf5 !important;
        color: #15803d !important;
        -webkit-text-fill-color: #15803d !important;
        border: 1px solid #bbf7d0 !important;
        border-radius: 999px !important;
        font-size: 0.72rem !important;
        font-weight: 600 !important;
        letter-spacing: 0.01em;
    }

    #recruitmentDirectoryPage .live-pulse-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: #16a34a !important;
        box-shadow: none !important;
        animation: none !important;
        flex-shrink: 0;
    }

    #recruitmentDirectoryPage .rec-subtitle {
        color: var(--rec-text-muted) !important;
        -webkit-text-fill-color: var(--rec-text-muted) !important;
        font-size: 0.83rem !important;
        margin: 0;
        font-weight: 400;
        line-height: 1.45;
    }

    #recruitmentDirectoryPage .rec-header-actions {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex-wrap: wrap;
    }

    /* Buttons */
    #recruitmentDirectoryPage .btn-rec-primary {
        background: var(--rec-primary) !important;
        background-image: none !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        border: 1px solid var(--rec-primary) !important;
        font-weight: 600 !important;
        font-size: 0.84rem !important;
        min-height: 38px;
        padding: 0.5rem 1rem !important;
        border-radius: 10px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        white-space: nowrap !important;
        box-shadow: var(--rec-shadow-xs) !important;
        transition: background-color 0.18s ease, border-color 0.18s ease;
        text-decoration: none;
    }

    #recruitmentDirectoryPage .btn-rec-primary:hover {
        background: var(--rec-primary-hover) !important;
        border-color: var(--rec-primary-hover) !important;
        color: #ffffff !important;
        transform: none !important;
        box-shadow: var(--rec-shadow-sm) !important;
    }

    #recruitmentDirectoryPage .btn-rec-primary i,
    #recruitmentDirectoryPage .btn-rec-primary span {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    #recruitmentDirectoryPage .btn-rec-primary i {
        font-size: 1rem;
    }

    #recruitmentDirectoryPage .btn-rec-outline {
        background: var(--rec-surface) !important;
        color: var(--rec-text-body) !important;
        -webkit-text-fill-color: var(--rec-text-body) !important;
        border: 1px solid var(--rec-border) !important;
        font-weight: 600 !important;
        font-size: 0.84rem !important;
        min-height: 38px;
        padding: 0.5rem 0.95rem !important;
        border-radius: 10px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        white-space: nowrap !important;
        box-shadow: var(--rec-shadow-xs) !important;
        transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
        text-decoration: none;
    }

    #recruitmentDirectoryPage .btn-rec-outline:hover {
        background: var(--rec-surface-subtle) !important;
        color: var(--rec-text-dark) !important;
        -webkit-text-fill-color: var(--rec-text-dark) !important;
        border-color: #cbd5e1 !important;
        transform: none !important;
        box-shadow: var(--rec-shadow-xs) !important;
    }

    #recruitmentDirectoryPage .btn-rec-outline i {
        color: var(--rec-text-muted) !important;
        -webkit-text-fill-color: var(--rec-text-muted) !important;
        font-size: 1rem;
    }

    #recruitmentDirectoryPage .badge-rec-readonly {
        background: var(--rec-surface-subtle) !important;
        color: var(--rec-text-body) !important;
        -webkit-text-fill-color: var(--rec-text-body) !important;
        border: 1px solid var(--rec-border) !important;
        padding: 0.52rem 0.95rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.82rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        max-width: 100%;
        white-space: normal;
    }

    #recruitmentDirectoryPage .badge-rec-readonly i,
    #recruitmentDirectoryPage .badge-rec-readonly i::before {
        color: inherit !important;
        -webkit-text-fill-color: inherit !important;
        background: transparent !important;
        flex-shrink: 0;
    }

    /* ===== POLICY CARD ===== */
    #recruitmentDirectoryPage .policy-card {
        background: var(--rec-surface) !important;
        border-radius: 14px !important;
        border: 1px solid var(--rec-border) !important;
        box-shadow: var(--rec-shadow-xs) !important;
        margin-bottom: 1.15rem !important;
        overflow: hidden;
        position: relative;
    }

    #recruitmentDirectoryPage .policy-card .policy-card-header {
        background: linear-gradient(100deg, #1d4ed8 0%, #2563eb 48%, #3730a3 100%) !important;
        color: #ffffff !important;
        padding: 1.15rem 1.45rem !important;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.8rem;
    }

    #recruitmentDirectoryPage .policy-header-left {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        width: 100%;
        min-width: 0;
    }

    #recruitmentDirectoryPage .policy-icon-wrapper {
        width: 38px !important;
        height: 38px !important;
        border-radius: 10px !important;
        background: rgba(255, 255, 255, 0.16) !important;
        border: 1px solid rgba(255, 255, 255, 0.28) !important;
        box-shadow: none !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem !important;
        color: #ffffff !important;
        flex-shrink: 0;
    }

    #recruitmentDirectoryPage .policy-icon-wrapper i {
        color: #ffffff !important;
        font-size: 1.15rem !important;
    }

    #recruitmentDirectoryPage .policy-heading-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem 0.7rem;
        min-width: 0;
    }

    #recruitmentDirectoryPage .policy-card-header .policy-title {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 1rem !important;
        margin: 0 !important;
        letter-spacing: -0.01em;
    }

    #recruitmentDirectoryPage .policy-meta-row {
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.55rem;
        font-size: 0.78rem;
        color: rgba(255, 255, 255, 0.92) !important;
    }

    #recruitmentDirectoryPage .policy-code-pill {
        background: rgba(255, 255, 255, 0.16) !important;
        color: #ffffff !important;
        padding: 0.18rem 0.58rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.74rem;
        letter-spacing: 0.02em;
        border: 1px solid rgba(255, 255, 255, 0.28) !important;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    #recruitmentDirectoryPage .policy-updated-text {
        color: rgba(255, 255, 255, 0.9) !important;
        font-weight: 500;
        font-size: 0.78rem;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    #recruitmentDirectoryPage .policy-header-right {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.55rem;
    }

    #recruitmentDirectoryPage .policy-card .policy-card-header .policy-status-pill {
        background: rgba(16, 185, 129, 0.22) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        padding: 0.26rem 0.72rem !important;
        border-radius: 999px !important;
        font-weight: 600 !important;
        font-size: 0.74rem !important;
        letter-spacing: 0.01em;
        box-shadow: none !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    #recruitmentDirectoryPage .policy-card .policy-card-header .policy-status-pill *,
    #recruitmentDirectoryPage .policy-card .policy-card-header .policy-title,
    #recruitmentDirectoryPage .policy-card .policy-card-header .policy-title *,
    #recruitmentDirectoryPage .policy-card .policy-card-header .policy-code-pill,
    #recruitmentDirectoryPage .policy-card .policy-card-header .policy-code-pill *,
    #recruitmentDirectoryPage .policy-card .policy-card-header .policy-updated-text,
    #recruitmentDirectoryPage .policy-card .policy-card-header .policy-updated-text *,
    #recruitmentDirectoryPage .policy-card .policy-card-header .btn-policy-toggle,
    #recruitmentDirectoryPage .policy-card .policy-card-header .btn-policy-toggle * {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    #recruitmentDirectoryPage .status-pulse-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: #34d399 !important;
        flex-shrink: 0;
    }

    #recruitmentDirectoryPage .policy-card .policy-card-header .btn-policy-toggle {
        background: rgba(255, 255, 255, 0.16) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.28) !important;
        padding: 0.26rem 0.72rem !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
        font-size: 0.75rem !important;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: background-color 0.18s ease, border-color 0.18s ease;
    }

    #recruitmentDirectoryPage .policy-card .policy-card-header .btn-policy-toggle:hover {
        background: rgba(255, 255, 255, 0.26) !important;
        border-color: rgba(255, 255, 255, 0.5) !important;
        transform: none !important;
    }

    #recruitmentDirectoryPage .btn-policy-toggle i {
        transition: transform 0.2s ease;
    }

    #recruitmentDirectoryPage .btn-policy-toggle[aria-expanded="false"] .toggle-chevron {
        transform: rotate(-90deg);
    }

    #recruitmentDirectoryPage .policy-card-body {
        padding: 1.25rem 1.45rem !important;
        background: var(--rec-surface) !important;
    }

    #recruitmentDirectoryPage .policy-param-box {
        background: var(--rec-surface) !important;
        border: 1px solid var(--rec-border) !important;
        border-radius: 12px !important;
        padding: 0.9rem 1rem !important;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        height: 100%;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    #recruitmentDirectoryPage .policy-param-box:hover {
        border-color: #cbd5e1 !important;
        box-shadow: var(--rec-shadow-xs) !important;
        transform: none !important;
    }

    #recruitmentDirectoryPage .policy-param-icon {
        width: 34px !important;
        height: 34px !important;
        border-radius: 9px !important;
        background: #eff6ff;
        color: #2563eb;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
        margin-top: 2px;
    }

    #recruitmentDirectoryPage .policy-param-icon.rec-param-probation {
        background: #eff6ff !important;
        color: #2563eb !important;
    }

    #recruitmentDirectoryPage .policy-param-icon.rec-param-sla {
        background: #eef2ff !important;
        color: #4f46e5 !important;
    }

    #recruitmentDirectoryPage .policy-param-icon.rec-param-files {
        background: #f0f9ff !important;
        color: #0284c7 !important;
    }

    #recruitmentDirectoryPage .policy-param-icon.rec-param-reply {
        background: #ecfdf5 !important;
        color: #16a34a !important;
    }

    #recruitmentDirectoryPage .policy-param-icon i {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
    }

    #recruitmentDirectoryPage .policy-param-label {
        font-size: 0.68rem !important;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--rec-text-muted) !important;
        -webkit-text-fill-color: var(--rec-text-muted) !important;
        font-weight: 700;
        margin-bottom: 0.2rem;
    }

    #recruitmentDirectoryPage .policy-param-value {
        font-size: 0.84rem !important;
        font-weight: 700 !important;
        color: var(--rec-text-dark) !important;
        -webkit-text-fill-color: var(--rec-text-dark) !important;
        line-height: 1.38;
    }

    #recruitmentDirectoryPage .policy-param-value small {
        color: var(--rec-text-muted) !important;
        -webkit-text-fill-color: var(--rec-text-muted) !important;
        font-weight: 500 !important;
    }

    #recruitmentDirectoryPage .rec-status-enabled {
        color: #16a34a !important;
        -webkit-text-fill-color: #16a34a !important;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    #recruitmentDirectoryPage .rec-status-disabled {
        color: var(--rec-text-muted) !important;
        -webkit-text-fill-color: var(--rec-text-muted) !important;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    /* Pipeline Flow */
    #recruitmentDirectoryPage .pipeline-section {
        background: var(--rec-surface-subtle) !important;
        border: 1px solid var(--rec-border) !important;
        border-radius: 12px !important;
        padding: 0.95rem 1.15rem !important;
    }

    #recruitmentDirectoryPage .pipeline-section .policy-param-label {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.65rem !important;
    }

    #recruitmentDirectoryPage .pipeline-section .policy-param-label i {
        color: var(--rec-primary) !important;
        font-size: 0.9rem;
    }

    #recruitmentDirectoryPage .pipeline-flow-wrapper {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    #recruitmentDirectoryPage .pipeline-step-pill {
        display: inline-flex !important;
        align-items: center;
        gap: 0.45rem;
        padding: 0.32rem 0.72rem !important;
        background: #eff6ff !important;
        color: #1d4ed8 !important;
        -webkit-text-fill-color: #1d4ed8 !important;
        border: 1px solid #dbeafe !important;
        border-radius: 999px !important;
        font-size: 0.78rem !important;
        font-weight: 600 !important;
        box-shadow: none !important;
    }

    #recruitmentDirectoryPage .pipeline-step-pill span:not(.pipeline-num) {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
    }

    #recruitmentDirectoryPage .pipeline-num {
        width: 18px !important;
        height: 18px !important;
        border-radius: 50%;
        background: #2563eb !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.68rem !important;
        font-weight: 700 !important;
        flex-shrink: 0;
    }

    #recruitmentDirectoryPage .pipeline-step-pill.pipeline-step-final {
        background: #ecfdf5 !important;
        color: #15803d !important;
        -webkit-text-fill-color: #15803d !important;
        border-color: #bbf7d0 !important;
    }

    #recruitmentDirectoryPage .pipeline-step-pill.pipeline-step-final .pipeline-num {
        background: #16a34a !important;
    }

    #recruitmentDirectoryPage .pipeline-arrow {
        color: var(--rec-text-faint) !important;
        -webkit-text-fill-color: var(--rec-text-faint) !important;
        font-size: 1rem !important;
    }

    /* Policy Footer Cards */
    #recruitmentDirectoryPage .policy-footer-card {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        padding: 0.95rem 1.05rem !important;
        background: var(--rec-surface) !important;
        border-radius: 12px !important;
        border: 1px solid var(--rec-border) !important;
        height: 100%;
    }

    #recruitmentDirectoryPage .policy-footer-icon {
        width: 32px !important;
        height: 32px !important;
        border-radius: 8px !important;
        background: var(--rec-primary-soft) !important;
        color: var(--rec-primary) !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
        margin-top: 2px;
    }

    #recruitmentDirectoryPage .policy-footer-icon i {
        color: var(--rec-primary) !important;
        -webkit-text-fill-color: var(--rec-primary) !important;
    }

    #recruitmentDirectoryPage .policy-footer-title {
        font-size: 0.84rem !important;
        font-weight: 700 !important;
        color: var(--rec-text-dark) !important;
        -webkit-text-fill-color: var(--rec-text-dark) !important;
        margin-bottom: 0.2rem;
    }

    #recruitmentDirectoryPage .policy-footer-desc {
        font-size: 0.78rem !important;
        color: var(--rec-text-muted) !important;
        -webkit-text-fill-color: var(--rec-text-muted) !important;
        margin: 0;
        line-height: 1.48;
    }

    /* ===== METRIC CARDS ===== */
    #recruitmentDirectoryPage .rec-metric-card {
        background: var(--rec-surface) !important;
        border: 1px solid var(--rec-border) !important;
        border-radius: 14px !important;
        padding: 1rem 1.2rem !important;
        display: flex;
        align-items: center;
        gap: 0.9rem;
        box-shadow: var(--rec-shadow-xs) !important;
        margin-bottom: 0 !important;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    #recruitmentDirectoryPage .rec-metric-card:hover {
        transform: none !important;
        border-color: #cbd5e1 !important;
        box-shadow: var(--rec-shadow-sm) !important;
    }

    #recruitmentDirectoryPage .rec-metric-icon {
        width: 42px !important;
        height: 42px !important;
        border-radius: 10px !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem !important;
        flex-shrink: 0;
    }

    #recruitmentDirectoryPage .rec-metric-icon.emerald { background: #eff6ff !important; color: #2563eb !important; --rec-metric-glyph: #2563eb; }
    #recruitmentDirectoryPage .rec-metric-icon.blue { background: #f0f9ff !important; color: #0284c7 !important; --rec-metric-glyph: #0284c7; }
    #recruitmentDirectoryPage .rec-metric-icon.amber { background: #fffbeb !important; color: #d97706 !important; --rec-metric-glyph: #d97706; }
    #recruitmentDirectoryPage .rec-metric-icon.green { background: #ecfdf5 !important; color: #16a34a !important; --rec-metric-glyph: #16a34a; }
    #recruitmentDirectoryPage .rec-metric-icon i { color: inherit !important; -webkit-text-fill-color: currentColor !important; }

    #recruitmentDirectoryPage .rec-metric-info h4 {
        margin: 0 0 0.12rem 0 !important;
        font-weight: 700 !important;
        color: var(--rec-text-dark) !important;
        -webkit-text-fill-color: var(--rec-text-dark) !important;
        font-size: 1.4rem !important;
        letter-spacing: -0.02em;
        line-height: 1.15;
    }

    #recruitmentDirectoryPage .rec-metric-info span {
        font-size: 0.78rem !important;
        color: var(--rec-text-muted) !important;
        -webkit-text-fill-color: var(--rec-text-muted) !important;
        font-weight: 500 !important;
    }

    /* ===== FILTER CARD ===== */
    #recruitmentDirectoryPage .rec-filter-card {
        background: var(--rec-surface) !important;
        border-radius: 14px !important;
        border: 1px solid var(--rec-border) !important;
        box-shadow: var(--rec-shadow-xs) !important;
        margin-bottom: 1.15rem !important;
        padding: 1rem 1.25rem !important;
    }

    #recruitmentDirectoryPage .rec-filter-card .form-label {
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.05em;
        color: var(--rec-text-body) !important;
        -webkit-text-fill-color: var(--rec-text-body) !important;
        text-transform: uppercase;
        margin-bottom: 0.4rem !important;
    }

    #recruitmentDirectoryPage .rec-search-input-group {
        border: 1px solid var(--rec-border);
        border-radius: 10px;
        background: var(--rec-surface);
        overflow: hidden;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    #recruitmentDirectoryPage .rec-search-input-group:focus-within {
        border-color: var(--rec-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    #recruitmentDirectoryPage .rec-search-input-group .input-group-text {
        background: transparent !important;
        border: 0 !important;
        color: var(--rec-text-faint) !important;
        padding-right: 0.35rem;
    }

    #recruitmentDirectoryPage .rec-search-input-group .form-control {
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
        height: 38px !important;
        font-size: 0.85rem !important;
        color: var(--rec-text-dark) !important;
    }

    #recruitmentDirectoryPage .rec-filter-card .form-select {
        height: 40px !important;
        border-radius: 10px !important;
        border: 1px solid var(--rec-border) !important;
        background-color: var(--rec-surface) !important;
        font-size: 0.85rem !important;
        color: var(--rec-text-dark) !important;
    }

    #recruitmentDirectoryPage .rec-filter-card .form-select:focus {
        border-color: var(--rec-primary) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
    }

    #recruitmentDirectoryPage .rec-filter-submit {
        height: 40px !important;
        min-height: 40px !important;
        border-radius: 10px !important;
        padding: 0 1.05rem !important;
        font-size: 0.84rem !important;
        font-weight: 600 !important;
    }

    #recruitmentDirectoryPage .rec-filter-reset {
        height: 40px !important;
        width: 40px !important;
        min-width: 40px !important;
        padding: 0 !important;
        border-radius: 10px !important;
        border: 1px solid var(--rec-border) !important;
        background: var(--rec-surface) !important;
        color: var(--rec-text-body) !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: var(--rec-shadow-xs);
    }

    #recruitmentDirectoryPage .rec-filter-reset:hover {
        background: var(--rec-surface-subtle) !important;
        border-color: #cbd5e1 !important;
        color: var(--rec-text-dark) !important;
    }

    /* ===== TABLE CARD & PMS-TABLE-TOOLS INTEGRATION ===== */
    #recruitmentDirectoryPage .rec-table-card {
        background: var(--rec-surface) !important;
        border-radius: 14px !important;
        border: 1px solid var(--rec-border) !important;
        box-shadow: var(--rec-shadow-xs) !important;
        overflow: hidden;
    }

    #recruitmentDirectoryPage .rec-table-card:has(.pms-table-export.is-open) {
        overflow: visible !important;
    }

    #recruitmentDirectoryPage .rec-table-card .pms-table-tools {
        background: var(--rec-surface) !important;
        border: 0 !important;
        border-bottom: 1px solid var(--rec-border) !important;
        border-radius: 14px 14px 0 0 !important;
        min-height: 54px !important;
        padding: 0.7rem 1.25rem !important;
    }

    #recruitmentDirectoryPage .rec-table-card .pms-table-tools__status {
        color: var(--rec-text-muted) !important;
        font-size: 0.82rem !important;
        font-weight: 500 !important;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    #recruitmentDirectoryPage .rec-table-card .pms-table-tools__status::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--rec-text-faint);
        opacity: 0.65;
        display: inline-block;
        flex-shrink: 0;
    }

    #recruitmentDirectoryPage .rec-table-card .pms-table-export__toggle {
        background: var(--rec-surface) !important;
        border: 1px solid var(--rec-border) !important;
        color: var(--rec-text-body) !important;
        border-radius: 9px !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        min-height: 36px !important;
        padding: 0.4rem 0.8rem !important;
        box-shadow: var(--rec-shadow-xs) !important;
    }

    #recruitmentDirectoryPage .rec-table-card .pms-table-export__toggle:hover,
    #recruitmentDirectoryPage .rec-table-card .pms-table-export.is-open .pms-table-export__toggle {
        background: var(--rec-surface-subtle) !important;
        border-color: #cbd5e1 !important;
        color: var(--rec-text-dark) !important;
        transform: none !important;
    }

    #recruitmentDirectoryPage .rec-table-card .pms-table-scroll-host {
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 0 14px 14px !important;
        box-shadow: none !important;
    }

    #recruitmentDirectoryPage .rec-table-card .pms-table-select {
        background: transparent !important;
    }

    #recruitmentDirectoryPage .rec-table-card .pms-table-select::after {
        display: none !important;
    }

    .rec-table th {
        background: #f8fafc !important;
        font-weight: 700 !important;
        color: #475569 !important;
        font-size: 0.72rem !important;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.85rem 1.15rem !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }

    .rec-table td {
        padding: 0.95rem 1.15rem !important;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #334155;
    }

    .rec-table tbody tr {
        transition: background-color 0.15s ease;
    }

    .rec-table tbody tr:hover,
    #recruitmentDirectoryPage .rec-table.pms-exportable-table > tbody > tr:hover > :is(th, td) {
        background-color: var(--rec-surface-hover) !important;
    }

    .badge-employment {
        background-color: #eff6ff !important;
        color: #1d4ed8 !important;
        border: 1px solid #dbeafe !important;
        font-weight: 600;
        font-size: 0.72rem;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        display: inline-block;
        margin-top: 0.25rem;
    }

    #recruitmentDirectoryPage .btn-rec-row-download {
        background: var(--rec-surface) !important;
        border: 1px solid var(--rec-primary-border) !important;
        color: var(--rec-primary) !important;
        font-weight: 600 !important;
        font-size: 0.78rem !important;
        border-radius: 8px !important;
        padding: 0.35rem 0.7rem !important;
    }

    #recruitmentDirectoryPage .btn-rec-row-download:hover {
        background: var(--rec-primary-soft) !important;
        border-color: var(--rec-primary) !important;
    }

    /* Empty state inside table */
    #recruitmentDirectoryPage .rec-empty-state {
        padding: 3.25rem 1.5rem;
        text-align: center;
    }

    #recruitmentDirectoryPage .rec-empty-icon {
        width: 52px;
        height: 52px;
        border-radius: 13px;
        background: var(--rec-surface-subtle) !important;
        border: 1px solid var(--rec-border) !important;
        color: var(--rec-text-muted) !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        margin-bottom: 0.9rem;
    }

    #recruitmentDirectoryPage .rec-empty-state h5 {
        color: var(--rec-text-dark) !important;
        -webkit-text-fill-color: var(--rec-text-dark) !important;
        font-size: 0.96rem !important;
        font-weight: 700 !important;
        margin-bottom: 0.3rem;
    }

    #recruitmentDirectoryPage .rec-empty-state p {
        color: var(--rec-text-muted) !important;
        -webkit-text-fill-color: var(--rec-text-muted) !important;
        font-size: 0.83rem !important;
        margin-bottom: 1.1rem;
    }

    /* Table Action Icon Buttons (.btn-icon, .btn-light) */
    .rec-table .btn-icon,
    .rec-table .btn-light {
        width: 34px;
        height: 34px;
        padding: 0;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--rec-btn-icon-bg, #f8fafc) !important;
        border: 1px solid var(--rec-btn-icon-border, #e2e8f0) !important;
        color: var(--rec-btn-icon-color, #475569) !important;
        transition: all 0.18s ease;
    }

    .rec-table .btn-icon i,
    .rec-table .btn-light i {
        font-size: 1.1rem;
        color: var(--rec-btn-icon-color, #475569) !important;
        -webkit-text-fill-color: var(--rec-btn-icon-color, #475569) !important;
    }

    /* ===== DARK MODE OVERRIDES FOR #recruitmentDirectoryPage & TABLES ===== */
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #recruitmentDirectoryPage .rec-live-badge {
        background: rgba(16, 185, 129, 0.16) !important;
        border-color: rgba(52, 211, 153, 0.32) !important;
        color: #6ee7b7 !important;
        -webkit-text-fill-color: #6ee7b7 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #recruitmentDirectoryPage .rec-metric-icon.emerald {
        background: rgba(59, 130, 246, 0.16) !important;
        color: #60a5fa !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #recruitmentDirectoryPage .rec-metric-icon.blue {
        background: rgba(14, 165, 233, 0.16) !important;
        color: #38bdf8 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #recruitmentDirectoryPage .rec-metric-icon.amber {
        background: rgba(245, 158, 11, 0.16) !important;
        color: #fbbf24 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #recruitmentDirectoryPage .rec-metric-icon.green {
        background: rgba(16, 185, 129, 0.16) !important;
        color: #34d399 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #recruitmentDirectoryPage .policy-param-icon.rec-param-probation {
        background: rgba(59, 130, 246, 0.16) !important;
        color: #60a5fa !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #recruitmentDirectoryPage .policy-param-icon.rec-param-sla {
        background: rgba(99, 102, 241, 0.18) !important;
        color: #a5b4fc !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #recruitmentDirectoryPage .policy-param-icon.rec-param-files {
        background: rgba(14, 165, 233, 0.16) !important;
        color: #38bdf8 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #recruitmentDirectoryPage .policy-param-icon.rec-param-reply {
        background: rgba(16, 185, 129, 0.16) !important;
        color: #34d399 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #recruitmentDirectoryPage .pipeline-step-pill {
        background: rgba(59, 130, 246, 0.16) !important;
        color: #93c5fd !important;
        -webkit-text-fill-color: #93c5fd !important;
        border-color: rgba(96, 165, 250, 0.3) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #recruitmentDirectoryPage .pipeline-step-pill.pipeline-step-final {
        background: rgba(16, 185, 129, 0.16) !important;
        color: #6ee7b7 !important;
        -webkit-text-fill-color: #6ee7b7 !important;
        border-color: rgba(52, 211, 153, 0.32) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #recruitmentDirectoryPage .rec-table th,
    html[data-pms-theme="dark"] .rec-table th,
    html[data-bs-theme="dark"] .rec-table th {
        background: #141b3d !important;
        color: #cbd5e1 !important;
        -webkit-text-fill-color: #cbd5e1 !important;
        border-bottom: 1px solid rgba(148, 163, 184, 0.18) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #recruitmentDirectoryPage .rec-table td,
    html[data-pms-theme="dark"] .rec-table td,
    html[data-bs-theme="dark"] .rec-table td {
        color: #cbd5e1 !important;
        -webkit-text-fill-color: #cbd5e1 !important;
        border-bottom: 1px solid rgba(148, 163, 184, 0.1) !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) .rec-table td :is(.fw-bold, .fs-6) {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) .rec-table td :is(.text-secondary, .text-muted) {
        color: #94a3b8 !important;
        -webkit-text-fill-color: #94a3b8 !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) .badge-employment {
        background: rgba(59, 130, 246, 0.18) !important;
        color: #93c5fd !important;
        -webkit-text-fill-color: #93c5fd !important;
        border-color: rgba(96, 165, 250, 0.35) !important;
    }

    /* Recruitment PDF branding controls */
    #createRequirementModal .rec-branding-card {
        display: grid;
        align-content: start;
        gap: 12px;
        height: 100%;
        padding: 14px;
        border: 1px solid var(--rec-border);
        border-radius: 14px;
        background: var(--rec-surface-subtle);
    }
    #createRequirementModal .rec-branding-card > div { min-width: 0; }
    #createRequirementModal .rec-branding-heading {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px 10px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--rec-border);
    }
    #createRequirementModal .rec-branding-icon {
        display: grid;
        place-items: center;
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        border-radius: 9px;
        background: var(--rec-primary-soft);
        color: var(--rec-btn-icon-color) !important;
        font-size: 1.15rem;
    }
    #createRequirementModal .rec-branding-heading h6 {
        margin: 0;
        font-size: .9rem;
        font-weight: 700;
        color: var(--rec-text-dark) !important;
        -webkit-text-fill-color: currentColor !important;
    }
    #createRequirementModal :is(.rec-branding-heading p, .rec-branding-hint, .rec-branding-feedback) {
        margin: 0;
        color: var(--rec-text-muted) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: .78rem;
    }
    #createRequirementModal .rec-branding-size {
        padding: 4px 8px;
        border: 1px solid var(--rec-primary-border);
        border-radius: 8px;
        background: var(--rec-primary-soft);
        color: var(--rec-text-body) !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: .73rem;
        font-weight: 650;
        white-space: nowrap;
    }
    #createRequirementModal .rec-branding-card .form-label {
        margin-bottom: 4px;
        font-size: .82rem;
        font-weight: 650;
    }
    #createRequirementModal .rec-branding-upload {
        display: grid;
        gap: 10px;
    }
    #createRequirementModal .rec-branding-upload > div { min-width: 0; }
    #createRequirementModal input[data-branding-file] { width: 100%; }
    #createRequirementModal p.rec-branding-hint { margin-top: 5px; }
    #createRequirementModal .rec-branding-preview {
        width: 100%;
        height: auto;
        border: 1px solid var(--rec-border);
        border-radius: 8px;
        background: #fff;
    }
    #createRequirementModal .rec-branding-feedback.is-error { color: var(--bs-form-invalid-color, #dc3545) !important; }
    #createRequirementModal .rec-branding-card [hidden] { display: none !important; }

    /* Modals & Alert Box in Dark Mode */
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) .modal-content {
        background: #0f1530 !important;
        border: 1px solid rgba(148, 163, 184, 0.2) !important;
        color: #ffffff !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) .modal-body {
        color: #ffffff !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) .modal-body .bg-light {
        background: #141b3d !important;
        border: 1px solid rgba(148, 163, 184, 0.18) !important;
        color: #ffffff !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) .modal-body :is(.text-dark, strong) {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) .modal-body .text-muted {
        color: #cbd5e1 !important;
        -webkit-text-fill-color: #cbd5e1 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) .modal-footer {
        background: #141b3d !important;
        border-top: 1px solid rgba(148, 163, 184, 0.16) !important;
    }

    .rec-notification-alert {
        background: #eff6ff !important;
        border: 1px solid #dbeafe !important;
        color: #1e3a8a !important;
    }
    .rec-notification-alert div {
        color: #1e3a8a !important;
        -webkit-text-fill-color: #1e3a8a !important;
    }
    .rec-notification-alert :is(strong, .rec-alert-heading) {
        color: #1d4ed8 !important;
        -webkit-text-fill-color: #1d4ed8 !important;
        font-weight: 700 !important;
    }
    .rec-notification-alert :is(i, .rec-notification-alert-icon) {
        color: #2563eb !important;
        -webkit-text-fill-color: #2563eb !important;
    }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) .modal-body .rec-notification-alert {
        background: rgba(59, 130, 246, 0.16) !important;
        border: 1px solid rgba(96, 165, 250, 0.35) !important;
        color: #eef1fb !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) .modal-body .rec-notification-alert div {
        color: #eef1fb !important;
        -webkit-text-fill-color: #eef1fb !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) .modal-body .rec-notification-alert :is(strong, .rec-alert-heading, i, .rec-notification-alert-icon) {
        color: #60a5fa !important;
        -webkit-text-fill-color: #60a5fa !important;
    }

    /* Quick Add Department Inline Panel */
    .quick-add-dept-panel {
        background: #f0fdf4;
        border: 1px solid #bbf7d0 !important;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
    }
    .quick-add-dept-panel .panel-title { color: #15803d !important; font-weight: 700; }
    .quick-add-dept-panel .panel-label { color: #1e293b !important; font-weight: 700; }
    .quick-add-dept-panel .panel-input { background-color: #ffffff !important; color: #0f172a !important; border: 1px solid #cbd5e1 !important; }
    .quick-add-dept-panel .panel-header { border-bottom: 1px solid rgba(22, 163, 74, 0.2) !important; }
    .quick-add-dept-panel .btn-panel-cancel { background-color: #ffffff !important; color: #334155 !important; border: 1px solid #cbd5e1 !important; }
    .quick-add-dept-panel .btn-panel-save { background: #16a34a !important; color: #ffffff !important; border: none !important; }

    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) .quick-add-dept-panel {
        background: #141b3d !important;
        border: 1px solid rgba(74, 222, 128, 0.4) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) .quick-add-dept-panel .panel-title {
        color: #4ade80 !important;
        -webkit-text-fill-color: #4ade80 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) .quick-add-dept-panel .panel-label {
        color: #f8fafc !important;
        -webkit-text-fill-color: #f8fafc !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) .quick-add-dept-panel .panel-input {
        background-color: #1e293b !important;
        color: #f8fafc !important;
        -webkit-text-fill-color: #f8fafc !important;
        border: 1px solid #334155 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) .quick-add-dept-panel .btn-panel-cancel {
        background-color: #334155 !important;
        color: #f8fafc !important;
        -webkit-text-fill-color: #f8fafc !important;
        border: 1px solid #475569 !important;
    }

    @media (max-width: 767.98px) {
        #recruitmentDirectoryPage .rec-header-card { padding: 1.05rem !important; }
        #recruitmentDirectoryPage .rec-header-left { align-items: flex-start; }
        #recruitmentDirectoryPage .rec-header-actions { width: 100%; }
        #recruitmentDirectoryPage .rec-header-actions .btn-rec-primary,
        #recruitmentDirectoryPage .rec-header-actions .btn-rec-outline { flex: 1 1 auto; justify-content: center; }
        #recruitmentDirectoryPage .policy-card .policy-card-header { padding: 1rem 1.1rem !important; }
        #recruitmentDirectoryPage .policy-card-body { padding: 1rem !important; }
    }
</style>

<div id="recruitmentDirectoryPage" class="container-xxl flex-grow-1 container-p-y recruitment-shell">

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
        <i class="bx bx-check-circle me-2 fs-4"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
        <i class="bx bx-error-circle me-2 fs-4"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Page Header Card --}}
    <div class="rec-header-card">
        <div class="rec-header-left">
            <div class="rec-header-icon">
                <i class="bx bx-user-plus"></i>
            </div>
            <div class="rec-header-text">
                <div class="rec-header-title-row">
                    <h1 class="rec-title">Recruitment &amp; Talent Acquisition</h1>
                    <span class="rec-live-badge">
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
                <i class="bx bx-slider-alt"></i>
                <span>Recruitment Settings</span>
            </a>
            <button type="button" class="btn-rec-primary" data-bs-toggle="modal" data-bs-target="#createRequirementModal">
                <i class="bx bx-plus"></i>
                <span>Add New Requirement</span>
            </button>
            @else
            <span class="badge-rec-readonly">
                <i class="bx bx-lock-alt"></i> Read-Only Mode (Download Available)
            </span>
            @endif
        </div>
    </div>

    {{-- AUTOMATICALLY GENERATED RECRUITMENT POLICY CARD --}}
    <div class="policy-card">
        <div class="policy-card-header">
            <div class="policy-header-left">
                <div class="policy-icon-wrapper">
                    <i class="bx bx-file"></i>
                </div>
                <div class="policy-heading-meta">
                    <h5 class="policy-title">
                        <span>Automatically Generated Recruitment Policy Card</span>
                    </h5>
                    <div class="policy-meta-row">
                        <span class="policy-code-pill">
                            <i class="bx bx-hash"></i> <span>{{ $policyCard['code'] }}</span>
                        </span>
                        <span class="policy-updated-text">
                            <i class="bx bx-time-five"></i> <span>Updated: {{ $policyCard['generated_at'] }}</span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="policy-header-right">
                <span class="policy-status-pill">
                    <span class="status-pulse-dot"></span> {{ $policyCard['status'] }}
                </span>
                <button class="btn btn-policy-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#policyCardContent" aria-expanded="true">
                    <i class="bx bx-chevron-down toggle-chevron"></i>
                    <span class="toggle-text">Toggle Details</span>
                </button>
            </div>
        </div>
        <div class="collapse show policy-card-body" id="policyCardContent">
            <div class="row g-3 mb-3">
                <div class="col-lg-3 col-sm-6">
                    <div class="policy-param-box">
                        <div class="policy-param-icon rec-param-probation">
                            <i class="bx bx-calendar"></i>
                        </div>
                        <div>
                            <div class="policy-param-label">Probation Period</div>
                            <div class="policy-param-value">{{ $policyCard['probation_period'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="policy-param-box">
                        <div class="policy-param-icon rec-param-sla">
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
                        <div class="policy-param-icon rec-param-files">
                            <i class="bx bx-file"></i>
                        </div>
                        <div>
                            <div class="policy-param-label">Allowed Resume Formats</div>
                            <div class="policy-param-value">{{ $policyCard['allowed_file_types'] }} <small>(Max {{ $policyCard['max_resume_size'] }})</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="policy-param-box">
                        <div class="policy-param-icon rec-param-reply">
                            <i class="bx bx-send"></i>
                        </div>
                        <div>
                            <div class="policy-param-label">Candidate Auto-Reply</div>
                            <div class="policy-param-value">
                                @if($policyCard['auto_reply_enabled'])
                                    <span class="rec-status-enabled"><i class="bx bx-check-circle"></i> Enabled</span>
                                @else
                                    <span class="rec-status-disabled"><i class="bx bx-x-circle"></i> Disabled</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pipeline-section mb-3">
                <div class="policy-param-label"><i class="bx bx-git-commit"></i> Standard Hiring Pipeline Stages</div>
                <div class="pipeline-flow-wrapper">
                    @foreach($policyCard['pipeline_stages'] as $index => $stage)
                        <div class="pipeline-step-pill {{ $loop->last ? 'pipeline-step-final' : '' }}">
                            <span class="pipeline-num">{{ $index + 1 }}</span>
                            <span>{{ trim($stage) }}</span>
                        </div>
                        @if(!$loop->last)
                            <i class="bx bx-chevron-right pipeline-arrow"></i>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="row g-3">
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
                            <i class="bx bx-badge-check"></i>
                        </div>
                        <div>
                            <div class="policy-footer-title">Internal Referral &amp; Background Checks</div>
                            <p class="policy-footer-desc">{{ $policyCard['referral_policy'] }} {{ $policyCard['background_check'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- METRIC COUNTER CARDS --}}
    <div class="row g-3 mb-3">
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
                    <i class="bx bx-slider-alt"></i>
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
                    <i class="bx bx-check-circle"></i>
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
                <label class="form-label">Search Requirement</label>
                <div class="input-group input-group-merge rec-search-input-group">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by title, department, location..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                <label class="form-label">Status Filter</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                <label class="form-label">Department</label>
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
                <button type="submit" class="btn btn-rec-primary rec-filter-submit flex-grow-1 justify-content-center">
                    <i class="bx bx-filter-alt"></i> Filter
                </button>
                <a href="{{ route('recruitment.index') }}" class="btn rec-filter-reset" title="Reset Filters">
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
                        <th>Salary &amp; Location</th>
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
                                <a href="{{ route('recruitment.download', $req->id) }}" class="btn btn-sm btn-rec-row-download d-inline-flex align-items-center gap-1" title="Download Requirement PDF to Share Personally">
                                    <i class="bx bx-download"></i> Download
                                </a>

                                <button class="btn btn-sm btn-icon btn-light recruitment-row-action" type="button" data-bs-toggle="modal" data-bs-target="#viewReqModal-{{ $req->id }}" title="View Full Details" aria-label="View requirement details">
                                    <svg class="recruitment-action-symbol" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>

                                @if(auth()->user()?->role === 'admin')
                                <div class="dropdown d-inline-block recruitment-actions-dropdown">
                                    <button class="btn btn-sm btn-icon btn-light recruitment-row-action" type="button" data-bs-toggle="dropdown" title="Requirement actions" aria-label="Requirement actions">
                                        <svg class="recruitment-action-symbol" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end recruitment-actions-menu">
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
                                <div class="modal-header" style="background: linear-gradient(135deg, #1E4FCC 0%, #2F6BFF 100%); color: #ffffff;">
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
                                                <small class="text-muted d-block fw-bold mb-1">Salary &amp; Location</small>
                                                <span class="fw-semibold">{{ $req->salary_range ?? 'Negotiable' }} | {{ $req->location ?? 'Headquarters' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    @if($req->description)
                                    <div class="mb-3">
                                        <h6 class="fw-bold mb-2"><i class="bx bx-text me-1 text-primary"></i> Job Description</h6>
                                        <div class="p-3 bg-light border rounded-3" style="white-space: pre-line;">{{ $req->description }}</div>
                                    </div>
                                    @endif

                                    @if($req->requirements_summary)
                                    <div class="mb-3">
                                        <h6 class="fw-bold mb-2"><i class="bx bx-list-check me-1 text-primary"></i> Candidate Requirements &amp; Qualifications</h6>
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
                        <td colspan="8" class="border-0 p-0">
                            <div class="rec-empty-state">
                                <div class="rec-empty-icon">
                                    <i class="bx bx-briefcase"></i>
                                </div>
                                <h5>No Job Requirements Found</h5>
                                <p>There are no job requirements posted at this moment.</p>
                                @if(auth()->user()?->role === 'admin')
                                <button type="button" class="btn btn-rec-primary" data-bs-toggle="modal" data-bs-target="#createRequirementModal">
                                    <i class="bx bx-plus"></i> Add First Requirement
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
@php
    $oldText = function ($key, $default = '') {
        $value = old($key, $default);
        return is_scalar($value) ? $value : $default;
    };
@endphp
@include('admin.recruitment.partials.create-modal-style')
<div class="modal fade" id="createRequirementModal" tabindex="-1" aria-hidden="true" aria-labelledby="createRequirementTitle">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST" action="{{ route('recruitment.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header rcm-header">
                    <span class="rcm-header-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M12 11v5M9.5 13.5h5"/></svg>
                    </span>
                    <div class="rcm-header-text">
                        <h5 class="modal-title" id="createRequirementTitle">Create New Recruitment Requirement</h5>
                        <p>Publish an opening, generate its PDF and share it with your team.</p>
                    </div>
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
                        <div class="col-12 rcm-section">
                            <span class="rcm-step" aria-hidden="true">1</span>
                            <div><h6>Role basics</h6><p>What the role is and how many people you need.</p></div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Job Title <sup class="text-danger">*</sup></label>
                            <input type="text" name="title" class="form-control" value="{{ $oldText('title') }}" placeholder="e.g. Senior Laravel Developer" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Positions Needed <sup class="text-danger">*</sup></label>
                            <input type="number" name="positions" class="form-control" value="{{ $oldText('positions', 1) }}" min="1" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Department</label>
                            <div class="input-group">
                                <select name="department_id" id="recruitment_department_select" class="form-select">
                                    <option value="">-- Select Department --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->dpt_name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-success d-flex align-items-center" onclick="toggleInlineAddDept();" title="Add New Department" style="border-top-right-radius: 0.375rem; border-bottom-right-radius: 0.375rem;">
                                    <i class="bx bx-plus fs-5"></i>
                                </button>
                            </div>

                            <!-- INLINE QUICK ADD DEPARTMENT PANEL -->
                            <div id="inlineAddDeptPanel" class="mt-2 p-3 rounded-3 quick-add-dept-panel d-none">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 panel-header">
                                    <span class="small d-flex align-items-center gap-1 panel-title">
                                        <i class="bx bx-buildings"></i> Quick Add Department
                                    </span>
                                    <button type="button" class="btn-close" style="font-size: 0.65rem;" onclick="toggleInlineAddDept(false);" aria-label="Close"></button>
                                </div>

                                <div id="inlineDptError" class="alert alert-danger py-1 px-2 small rounded-2 mb-2 d-none"></div>

                                <div class="mb-2">
                                    <label class="form-label small mb-1 panel-label">Department Name <sup class="text-danger">*</sup></label>
                                    <input type="text" id="inline_dpt_name" class="form-control form-control-sm panel-input" placeholder="e.g. PHP DEV, QA, Marketing" autocomplete="off">
                                </div>

                                <div class="mb-2">
                                    <label class="form-label small mb-1 panel-label">Parent Department <span class="text-muted fw-normal">(Optional)</span></label>
                                    <select id="inline_parent_dpt_id" class="form-select form-select-sm panel-input">
                                        <option value="">-- None / Top Level Department --</option>
                                        @if(isset($parentDepartments))
                                            @foreach($parentDepartments as $pDept)
                                                <option value="{{ $pDept->id }}">{{ $pDept->dpt_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-2 pt-1">
                                    <button type="button" class="btn btn-sm btn-panel-cancel" onclick="toggleInlineAddDept(false);">Cancel</button>
                                    <button type="button" id="btnSaveInlineDept" class="btn btn-sm btn-panel-save px-3" onclick="saveInlineDepartment();">
                                        <i class="bx bx-check me-1"></i> Save & Select
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Employment Type <sup class="text-danger">*</sup></label>
                            <select name="employment_type" class="form-select" required>
                                @foreach(['Full-time', 'Part-time', 'Contract', 'Remote', 'Internship'] as $employmentType)
                                    <option value="{{ $employmentType }}" @selected(old('employment_type') === $employmentType)>{{ $employmentType }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 rcm-section">
                            <span class="rcm-step" aria-hidden="true">2</span>
                            <div><h6>Experience, pay &amp; location</h6><p>Shown on the requirement card and in the PDF.</p></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Experience Required</label>
                            <select name="experience_required" class="form-select">
                                <option value="No">No</option>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" @selected(old('experience_required') == $i)>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="recruitment_salary_min">Salary Range (CTC)</label>
                            <div class="input-group recruitment-salary-ctc">
                                <input type="number" name="salary_min" id="recruitment_salary_min" class="form-control @error('salary_min') is-invalid @enderror"
                                       value="{{ old('salary_min') }}" min="0" max="999" step="0.01" inputmode="decimal" placeholder="Min" aria-label="Minimum CTC in LPA">
                                <span class="input-group-text">–</span>
                                <input type="number" name="salary_max" id="recruitment_salary_max" class="form-control @error('salary_max') is-invalid @enderror"
                                       value="{{ old('salary_max') }}" min="0" max="999" step="0.01" inputmode="decimal" placeholder="Max" aria-label="Maximum CTC in LPA">
                                <span class="input-group-text fw-bold">LPA</span>
                            </div>
                            @error('salary_min')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @error('salary_max')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <small class="text-muted d-block mt-1">Numbers only, in lakhs per annum (e.g. 4.5 – 6).</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="recruitment_location_select">Location</label>
                            <div class="input-group">
                                <select name="location" id="recruitment_location_select" class="form-select">
                                    <option value="">-- Select Location --</option>
                                    @if(isset($companyLocations))
                                        @foreach($companyLocations as $loc)
                                            <option value="{{ $loc['value'] }}" {{ old('location') === $loc['value'] ? 'selected' : '' }}>
                                                {{ $loc['label'] }}{{ !empty($loc['is_default']) ? ' (Default)' : '' }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <button type="button" class="btn btn-outline-success d-flex align-items-center" onclick="toggleInlineAddLocation();" title="Add New Location" aria-label="Add New Location" style="border-top-right-radius: 0.375rem; border-bottom-right-radius: 0.375rem;">
                                    <i class="bx bx-plus fs-5"></i>
                                </button>
                            </div>
                        </div>

                        <!-- INLINE QUICK ADD LOCATION PANEL -->
                        <div id="inlineAddLocationWrapper" class="col-12 d-none">
                            <div id="inlineAddLocationPanel" class="p-3 rounded-3 quick-add-dept-panel">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 panel-header">
                                    <span class="small d-flex align-items-center gap-1 panel-title">
                                        <i class="bx bx-map"></i> Quick Add Company Location
                                    </span>
                                    <button type="button" class="btn-close" style="font-size: 0.65rem;" onclick="toggleInlineAddLocation(false);" aria-label="Close"></button>
                                </div>

                                <div id="inlineLocationError" class="alert alert-danger py-1 px-2 small rounded-2 mb-2 d-none"></div>

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1 panel-label" for="inline_location_name">Location / City <sup class="text-danger">*</sup></label>
                                        <input type="text" id="inline_location_name" class="form-control form-control-sm panel-input" placeholder="e.g. Salt Lake Sector 5, Kolkata or Remote" autocomplete="off">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1 panel-label" for="inline_location_branch">Branch / Office Name <span class="text-muted fw-normal">(Optional)</span></label>
                                        <input type="text" id="inline_location_branch" class="form-control form-control-sm panel-input" placeholder="e.g. Headquarters, Main Branch" autocomplete="off">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label small mb-1 panel-label" for="inline_location_address">Address <span class="text-muted fw-normal">(Optional)</span></label>
                                        <input type="text" id="inline_location_address" class="form-control form-control-sm panel-input" placeholder="Street / building address" autocomplete="off">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-1 panel-label" for="inline_location_country">Country <span class="text-muted fw-normal">(Optional)</span></label>
                                        <input type="text" id="inline_location_country" class="form-control form-control-sm panel-input" placeholder="e.g. India" autocomplete="off">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-2 pt-1">
                                    <button type="button" class="btn btn-sm btn-panel-cancel" onclick="toggleInlineAddLocation(false);">Cancel</button>
                                    <button type="button" id="btnSaveInlineLocation" class="btn btn-sm btn-panel-save px-3" onclick="saveInlineLocation();">
                                        <i class="bx bx-check me-1"></i> Save &amp; Select
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 rcm-section">
                            <span class="rcm-step" aria-hidden="true">3</span>
                            <div><h6>Role details</h6><p>Responsibilities and the skills candidates should bring.</p></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Job Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Provide brief overview of responsibilities and scope...">{{ $oldText('description') }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Candidate Qualification</label>
                            <textarea name="requirements_summary" class="form-control" rows="3" placeholder="Key skills, degrees, technologies, or certifications needed...">{{ $oldText('requirements_summary') }}</textarea>
                        </div>

                        <div class="col-12 rcm-section">
                            <span class="rcm-step" aria-hidden="true">4</span>
                            <div><h6>PDF branding</h6><p>Your company header and footer printed on the requirement PDF.</p></div>
                        </div>
                        @foreach(\App\Support\RecruitmentPdfBranding::HEIGHTS as $kind => $height)
                            <div class="col-md-6">
                                @include('admin.recruitment.partials.branding-field', ['assets' => $brandingAssets[$kind] ?? collect()])
                            </div>
                        @endforeach
                        <p class="col-12 rec-branding-hint mb-0">New images are saved to your company’s dropdowns when you create the requirement.</p>
                    </div>
                </div>
                <div class="modal-footer rcm-footer">
                    <span class="rcm-footer-note"><sup class="text-danger">*</sup> Required fields</span>
                    <button type="button" class="btn rcm-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-rec-primary rcm-submit px-4"><i class="bx bx-paper-plane me-1"></i> Create &amp; Broadcast Requirement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Floating Toast Container for instant feedback -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11000">
    <div id="recruitmentToast" class="toast align-items-center text-white bg-dark border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bx bx-check-circle text-success fs-4" id="recruitmentToastIcon"></i>
                <span id="recruitmentToastMessage">Department created successfully.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script src="{{ asset('admin/assets/js/pms-recruitment-actions.js') }}"></script>
<script src="{{ asset('admin/assets/js/pms-recruitment-branding.js') }}?v={{ filemtime(public_path('admin/assets/js/pms-recruitment-branding.js')) }}"></script>
@if($errors->hasAny(['pdf_header_image', 'pdf_header_asset_id', 'pdf_header_name', 'pdf_footer_image', 'pdf_footer_asset_id', 'pdf_footer_name']))
<script>
document.addEventListener('DOMContentLoaded', function () {
    bootstrap.Modal.getOrCreateInstance(document.getElementById('createRequirementModal')).show();
});
</script>
@endif
<script>
// Salary Range (CTC): numbers only (LPA). Block letters/symbols that number inputs would otherwise accept (e, +, -),
// and keep Max from being lower than Min.
document.addEventListener('DOMContentLoaded', function () {
    const minInput = document.getElementById('recruitment_salary_min');
    const maxInput = document.getElementById('recruitment_salary_max');
    if (!minInput || !maxInput) return;
    [minInput, maxInput].forEach(function (input) {
        input.addEventListener('keydown', function (event) {
            if (['e', 'E', '+', '-'].includes(event.key)) event.preventDefault();
        });
        input.addEventListener('paste', function (event) {
            const text = (event.clipboardData || window.clipboardData).getData('text').trim();
            if (!/^\d+(\.\d{1,2})?$/.test(text)) event.preventDefault();
        });
    });
    const syncRange = function () {
        maxInput.min = minInput.value !== '' ? minInput.value : '0';
        const invalid = minInput.value !== '' && maxInput.value !== '' && parseFloat(maxInput.value) < parseFloat(minInput.value);
        maxInput.setCustomValidity(invalid ? 'Maximum CTC cannot be lower than the minimum.' : '');
    };
    minInput.addEventListener('input', syncRange);
    maxInput.addEventListener('input', syncRange);
    syncRange();
});

function toggleInlineAddDept(forceState) {
    const panel = document.getElementById('inlineAddDeptPanel');
    const input = document.getElementById('inline_dpt_name');
    const errorBox = document.getElementById('inlineDptError');
    if (!panel) return;

    if (errorBox) {
        errorBox.classList.add('d-none');
        errorBox.textContent = '';
    }

    const isCurrentlyHidden = panel.classList.contains('d-none');
    const shouldShow = (typeof forceState === 'boolean') ? forceState : isCurrentlyHidden;

    if (shouldShow) {
        panel.classList.remove('d-none');
        if (input) {
            setTimeout(() => input.focus(), 50);
        }
    } else {
        panel.classList.add('d-none');
        if (input) input.value = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const inlineInput = document.getElementById('inline_dpt_name');
    if (inlineInput) {
        inlineInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                e.stopPropagation();
                saveInlineDepartment();
            }
        });
    }
});

function saveInlineDepartment() {
    const btn = document.getElementById('btnSaveInlineDept');
    const errorBox = document.getElementById('inlineDptError');
    const inputName = document.getElementById('inline_dpt_name');
    const selectParent = document.getElementById('inline_parent_dpt_id');
    
    if (errorBox) {
        errorBox.classList.add('d-none');
        errorBox.textContent = '';
    }
    
    const dptName = inputName ? inputName.value.trim() : '';
    const parentDptId = selectParent ? selectParent.value : '';

    if (!dptName) {
        if (errorBox) {
            errorBox.textContent = 'Please enter a department name.';
            errorBox.classList.remove('d-none');
        }
        if (inputName) inputName.focus();
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Saving...';
    }

    fetch('{{ route("departments.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            dpt_name: dptName,
            parent_dpt_id: parentDptId || null,
            code_generation_mode: 'auto'
        })
    })
    .then(async res => {
        const data = await res.json().catch(() => ({}));
        return { status: res.status, data };
    })
    .then(({ status, data }) => {
        if (status >= 200 && status < 300 && data.success) {
            const dept = data.department || data.dpt;
            
            // Add option to Recruitment Requirement select and select it immediately
            const recSelect = document.getElementById('recruitment_department_select');
            if (recSelect && dept) {
                const newOption = new Option(dept.dpt_name, dept.id, true, true);
                recSelect.add(newOption);
                recSelect.value = dept.id;
            }

            // Also add to filter select if present on the page
            const filterSelect = document.querySelector('form[action*="recruitment"] select[name="department_id"]');
            if (filterSelect && recSelect !== filterSelect && dept) {
                filterSelect.add(new Option(dept.dpt_name, dept.id, false, false));
            }

            // Reset and close inline add panel
            if (inputName) inputName.value = '';
            if (selectParent) selectParent.value = '';
            toggleInlineAddDept(false);

            // Show instant success feedback toast
            showQuickToast(`Department "${dept.dpt_name}" added and selected!`, true);
        } else {
            let errMsg = data.message || 'Failed to create department. Please check the inputs.';
            if (data.errors) {
                errMsg = Object.values(data.errors).flat().join('<br>');
            }
            if (errorBox) {
                errorBox.innerHTML = errMsg;
                errorBox.classList.remove('d-none');
            }
        }
    })
    .catch(err => {
        console.error(err);
        if (errorBox) {
            errorBox.textContent = 'Network error while creating department. Please try again.';
            errorBox.classList.remove('d-none');
        }
    })
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bx bx-check me-1"></i> Save & Select';
        }
    });
}

function toggleInlineAddLocation(forceState) {
    const wrapper = document.getElementById('inlineAddLocationWrapper');
    const input = document.getElementById('inline_location_name');
    const errorBox = document.getElementById('inlineLocationError');
    if (!wrapper) return;

    if (errorBox) {
        errorBox.classList.add('d-none');
        errorBox.textContent = '';
    }

    const isCurrentlyHidden = wrapper.classList.contains('d-none');
    const shouldShow = (typeof forceState === 'boolean') ? forceState : isCurrentlyHidden;

    if (shouldShow) {
        wrapper.classList.remove('d-none');
        if (input) {
            setTimeout(() => input.focus(), 50);
        }
    } else {
        wrapper.classList.add('d-none');
        ['inline_location_name', 'inline_location_branch', 'inline_location_address', 'inline_location_country'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const inlineLocInput = document.getElementById('inline_location_name');
    if (inlineLocInput) {
        inlineLocInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                e.stopPropagation();
                saveInlineLocation();
            }
        });
    }
});

function saveInlineLocation() {
    const btn = document.getElementById('btnSaveInlineLocation');
    const errorBox = document.getElementById('inlineLocationError');
    const inputLocation = document.getElementById('inline_location_name');
    const inputBranch = document.getElementById('inline_location_branch');
    const inputAddress = document.getElementById('inline_location_address');
    const inputCountry = document.getElementById('inline_location_country');

    if (errorBox) {
        errorBox.classList.add('d-none');
        errorBox.textContent = '';
    }

    const locationName = inputLocation ? inputLocation.value.trim() : '';
    const branchName = inputBranch ? inputBranch.value.trim() : '';
    const streetAddress = inputAddress ? inputAddress.value.trim() : '';
    const country = inputCountry ? inputCountry.value.trim() : '';

    if (!locationName) {
        if (errorBox) {
            errorBox.textContent = 'Please enter a location or city name.';
            errorBox.classList.remove('d-none');
        }
        if (inputLocation) inputLocation.focus();
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Saving...';
    }

    fetch('{{ route("recruitment.locations.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            location: locationName,
            branch_name: branchName || null,
            address: streetAddress || null,
            country: country || null
        })
    })
    .then(async res => {
        const data = await res.json().catch(() => ({}));
        return { status: res.status, data };
    })
    .then(({ status, data }) => {
        if (status >= 200 && status < 300 && data.success && data.location) {
            const loc = data.location;
            const locSelect = document.getElementById('recruitment_location_select');
            if (locSelect) {
                const existing = Array.from(locSelect.options).find(opt => opt.value === loc.value);
                if (!existing) {
                    const labelText = loc.is_default ? `${loc.label} (Default)` : loc.label;
                    locSelect.add(new Option(labelText, loc.value, true, true));
                }
                locSelect.value = loc.value;
            }

            toggleInlineAddLocation(false);
            showQuickToast(`Location "${loc.label}" added and selected!`, true);
        } else {
            let errMsg = data.message || 'Failed to add location. Please check the inputs.';
            if (data.errors) {
                errMsg = Object.values(data.errors).flat().join('<br>');
            }
            if (errorBox) {
                errorBox.innerHTML = errMsg;
                errorBox.classList.remove('d-none');
            }
        }
    })
    .catch(err => {
        console.error(err);
        if (errorBox) {
            errorBox.textContent = 'Network error while adding location. Please try again.';
            errorBox.classList.remove('d-none');
        }
    })
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bx bx-check me-1"></i> Save & Select';
        }
    });
}

function showQuickToast(message, isSuccess = true) {
    const toastEl = document.getElementById('recruitmentToast');
    const msgEl = document.getElementById('recruitmentToastMessage');
    const iconEl = document.getElementById('recruitmentToastIcon');
    
    if (msgEl) msgEl.textContent = message;
    if (iconEl) {
        iconEl.className = isSuccess ? 'bx bx-check-circle text-success fs-4' : 'bx bx-error-circle text-danger fs-4';
    }
    
    if (toastEl && window.bootstrap) {
        const toast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3500 });
        toast.show();
    }
}
</script>
@endif
@endsection
