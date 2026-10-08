@extends('admin.layout.app')

@section('title', 'Localization Settings')

@push('styles')
<style>
    /* ==========================================================================
       LOCALIZATION SETTINGS MASTER THEME (LIGHT & DARK MODE)
       Scoped token design system ensuring 100% theme fidelity
       ========================================================================== */

    /* ----- LIGHT MODE DESIGN TOKENS ----- */
    .localization-settings-page {
        --loc-page-bg: linear-gradient(135deg, #F8FAFC 0%, #EEF2FF 50%, #F8FAFC 100%);
        --loc-card-bg: rgba(255, 255, 255, 0.95);
        --loc-card-border: rgba(47, 107, 255, 0.14);
        --loc-card-shadow: 0 10px 30px -10px rgba(47, 107, 255, 0.08);
        --loc-card-hover-shadow: 0 20px 35px -12px rgba(47, 107, 255, 0.15);

        --loc-text-title: #0F172A;
        --loc-text-body: #334155;
        --loc-text-muted: #64748B;
        --loc-heading-gradient: linear-gradient(135deg, #0F172A 0%, #2F6BFF 50%, #10B981 100%);

        --loc-divider: rgba(47, 107, 255, 0.12);

        /* Inputs & Form Controls */
        --loc-input-bg: #FFFFFF;
        --loc-input-border: rgba(47, 107, 255, 0.22);
        --loc-input-color: #0F172A;
        --loc-input-focus-border: #60A5FA;
        --loc-input-focus-shadow: 0 0 0 4px rgba(79, 131, 255, 0.15);
        --loc-input-icon: #2F6BFF;
        --loc-input-readonly-bg: #F1F5F9;
        --loc-input-readonly-color: #475569;
        --loc-select-option-bg: #FFFFFF;
        --loc-select-option-color: #0F172A;
        --loc-select-arrow: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%232F6BFF' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");

        /* Section Badges */
        --loc-badge-bg: #EEF2FF;
        --loc-badge-border: rgba(47, 107, 255, 0.2);
        --loc-badge-color: #2F6BFF;

        /* Executive Summary Stat Cards */
        --loc-stat-bg: #FFFFFF;
        --loc-stat-border: rgba(47, 107, 255, 0.14);
        --loc-stat-title: #64748B;
        --loc-stat-val: #0F172A;

        /* Stat Icon Palette */
        --loc-icon-curr-bg: linear-gradient(145deg, #EEF2FF, #E0E7FF);
        --loc-icon-curr-color: #2F6BFF;
        --loc-icon-tz-bg: linear-gradient(145deg, #E0F2FE, #BAE6FD);
        --loc-icon-tz-color: #0284C7;
        --loc-icon-lang-bg: linear-gradient(145deg, #FEF3C7, #FDE68A);
        --loc-icon-lang-color: #D97706;
        --loc-icon-format-bg: linear-gradient(145deg, #E0E7FF, #C7D2FE);
        --loc-icon-format-color: #4F46E5;

        /* Navigation Buttons */
        --loc-btn-back-bg: #FFFFFF;
        --loc-btn-back-border: rgba(47, 107, 255, 0.25);
        --loc-btn-back-color: #2F6BFF;
        --loc-btn-back-hover-bg: #EEF2FF;
        --loc-btn-back-hover-color: #1E4FCC;

        /* Alerts & Badges */
        --loc-alert-viewonly-bg: linear-gradient(135deg, #EFF6FF, #DBEAFE);
        --loc-alert-viewonly-border: #3B82F6;
        --loc-alert-viewonly-title: #1E40AF;
        --loc-alert-viewonly-text: #1E3A8A;
        --loc-alert-viewonly-icon: #2563EB;

        --loc-alert-success-bg: rgba(220, 252, 231, 0.95);
        --loc-alert-success-border: #10B981;
        --loc-alert-success-text: #065F46;

        --loc-readonly-badge-bg: #F1F5F9;
        --loc-readonly-badge-border: #CBD5E1;
        --loc-readonly-badge-color: #64748B;
    }

    /* ----- DARK MODE DESIGN TOKENS ----- */
    html[data-pms-theme="dark"] .localization-settings-page,
    html[data-theme="dark"] .localization-settings-page,
    html[data-bs-theme="dark"] .localization-settings-page,
    body[data-pms-theme="dark"] .localization-settings-page,
    body[data-theme="dark"] .localization-settings-page,
    body[data-bs-theme="dark"] .localization-settings-page,
    body.dark-mode .localization-settings-page,
    .dark-mode .localization-settings-page,
    [data-pms-theme="dark"] .localization-settings-page,
    [data-theme="dark"] .localization-settings-page,
    [data-bs-theme="dark"] .localization-settings-page {
        --loc-page-bg: #070B1A;
        --loc-card-bg: #0F1530;
        --loc-card-border: rgba(238, 241, 251, 0.09);
        --loc-card-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
        --loc-card-hover-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.7);

        --loc-text-title: #EEF1FB;
        --loc-text-body: #9AA3C7;
        --loc-text-muted: #848CB0;
        --loc-heading-gradient: linear-gradient(135deg, #60A5FA 0%, #22D3EE 50%, #34D399 100%);

        --loc-divider: rgba(238, 241, 251, 0.08);

        /* Inputs & Form Controls */
        --loc-input-bg: #141B3D;
        --loc-input-border: rgba(238, 241, 251, 0.14);
        --loc-input-color: #EEF1FB;
        --loc-input-focus-border: #4F83FF;
        --loc-input-focus-shadow: 0 0 0 4px rgba(47, 107, 255, 0.28);
        --loc-input-icon: #60A5FA;
        --loc-input-readonly-bg: #0B1028;
        --loc-input-readonly-color: #848CB0;
        --loc-select-option-bg: #141B3D;
        --loc-select-option-color: #EEF1FB;
        --loc-select-arrow: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2360A5FA' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");

        /* Section Badges */
        --loc-badge-bg: rgba(47, 107, 255, 0.16);
        --loc-badge-border: rgba(96, 165, 250, 0.3);
        --loc-badge-color: #60A5FA;

        /* Executive Summary Stat Cards */
        --loc-stat-bg: #141B3D;
        --loc-stat-border: rgba(238, 241, 251, 0.10);
        --loc-stat-title: #848CB0;
        --loc-stat-val: #EEF1FB;

        /* Stat Icon Palette */
        --loc-icon-curr-bg: rgba(47, 107, 255, 0.22);
        --loc-icon-curr-color: #60A5FA;
        --loc-icon-tz-bg: rgba(2, 132, 199, 0.22);
        --loc-icon-tz-color: #38BDF8;
        --loc-icon-lang-bg: rgba(217, 119, 6, 0.22);
        --loc-icon-lang-color: #FBBF24;
        --loc-icon-format-bg: rgba(79, 70, 229, 0.22);
        --loc-icon-format-color: #818CF8;

        /* Navigation Buttons */
        --loc-btn-back-bg: #141B3D;
        --loc-btn-back-border: rgba(238, 241, 251, 0.14);
        --loc-btn-back-color: #60A5FA;
        --loc-btn-back-hover-bg: #1A2247;
        --loc-btn-back-hover-color: #93C5FD;

        /* Alerts & Badges */
        --loc-alert-viewonly-bg: rgba(30, 58, 138, 0.25);
        --loc-alert-viewonly-border: #3B82F6;
        --loc-alert-viewonly-title: #93C5FD;
        --loc-alert-viewonly-text: #BFDBFE;
        --loc-alert-viewonly-icon: #60A5FA;

        --loc-alert-success-bg: rgba(6, 95, 70, 0.25);
        --loc-alert-success-border: #10B981;
        --loc-alert-success-text: #A7F3D0;

        --loc-readonly-badge-bg: #141B3D;
        --loc-readonly-badge-border: rgba(238, 241, 251, 0.14);
        --loc-readonly-badge-color: #94A3B8;
    }

    /* ==========================================================================
       PAGE CONTAINER & AMBIENT EFFECTS
       ========================================================================== */
    .localization-settings-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: var(--loc-page-bg) !important;
        color: var(--loc-text-title) !important;
        position: relative;
        transition: background 0.3s ease, color 0.3s ease;
    }

    .localization-settings-shell {
        position: relative;
        max-width: 1400px;
        margin: 0 auto;
    }

    .ambient-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(130px);
        opacity: 0.35;
        pointer-events: none;
        z-index: 1;
        transition: opacity 0.3s ease;
    }

    .orb-1 {
        top: -100px;
        right: -100px;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(79, 131, 255, 0.14) 0%, transparent 70%);
        animation: orbFloat 20s ease-in-out infinite;
    }

    .orb-2 {
        bottom: -100px;
        left: -100px;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(47, 107, 255, 0.12) 0%, transparent 70%);
        animation: orbFloat 25s ease-in-out infinite reverse;
    }

    html[data-pms-theme="dark"] .ambient-orb,
    html[data-theme="dark"] .ambient-orb,
    html[data-bs-theme="dark"] .ambient-orb,
    body[data-pms-theme="dark"] .ambient-orb,
    body.dark-mode .ambient-orb,
    .dark-mode .ambient-orb {
        opacity: 0.15 !important;
    }

    @keyframes orbFloat {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(40px, -30px) scale(1.05); }
        66% { transform: translate(-30px, 40px) scale(0.95); }
    }

    .content-wrapper {
        position: relative;
        z-index: 10;
    }

    /* ==========================================================================
       BREADCRUMB
       ========================================================================== */
    .localization-settings-page .breadcrumb-custom {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--loc-text-muted) !important;
        margin-bottom: 14px;
    }

    .localization-settings-page .breadcrumb-custom a {
        color: var(--loc-badge-color) !important;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .localization-settings-page .breadcrumb-custom a:hover {
        color: var(--loc-input-icon) !important;
        text-decoration: underline;
    }

    .localization-settings-page .breadcrumb-custom span {
        color: var(--loc-text-muted) !important;
    }

    /* ==========================================================================
       HEADER CARD
       ========================================================================== */
    .localization-settings-page .branches-header,
    .localization-settings-page .loc-header-card {
        background: var(--loc-card-bg) !important;
        backdrop-filter: blur(20px) !important;
        border-radius: 28px !important;
        padding: 1.75rem 2.25rem !important;
        margin-bottom: 2rem !important;
        border: 1px solid var(--loc-card-border) !important;
        box-shadow: var(--loc-card-shadow) !important;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.25rem;
        transition: all 0.3s ease;
        animation: slideDown 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .header-left-box {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .localization-settings-page .header-icon-badge,
    .localization-settings-page .loc-header-icon-badge {
        width: 58px !important;
        height: 58px !important;
        border-radius: 20px !important;
        background: linear-gradient(145deg, #4F83FF, #2F6BFF) !important;
        color: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.6rem !important;
        box-shadow: 0 8px 20px -4px rgba(47, 107, 255, 0.35) !important;
        flex-shrink: 0 !important;
    }

    .localization-settings-page .header-icon-badge i,
    .localization-settings-page .header-icon-badge svg,
    .localization-settings-page .loc-header-icon-badge i,
    .localization-settings-page .loc-header-icon-badge svg {
        color: #ffffff !important;
        fill: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        background: transparent !important;
    }

    .localization-settings-page .header-title h1,
    .localization-settings-page .loc-header-title h1 {
        font-size: 1.95rem !important;
        font-weight: 800 !important;
        background: var(--loc-heading-gradient) !important;
        -webkit-background-clip: text !important;
        background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        color: transparent !important;
        margin: 0 0 0.25rem 0 !important;
        letter-spacing: -0.03em !important;
    }

    .localization-settings-page .header-title p,
    .localization-settings-page .loc-header-title p {
        color: var(--loc-text-muted) !important;
        -webkit-text-fill-color: var(--loc-text-muted) !important;
        font-size: 0.92rem !important;
        font-weight: 500 !important;
        margin: 0 !important;
    }

    .localization-settings-page .btn-back-settings,
    .localization-settings-page .loc-btn-back {
        background-color: var(--loc-btn-back-bg) !important;
        border: 1px solid var(--loc-btn-back-border) !important;
        color: var(--loc-btn-back-color) !important;
        -webkit-text-fill-color: var(--loc-btn-back-color) !important;
        font-weight: 700 !important;
        font-size: 0.9rem !important;
        border-radius: 40px !important;
        padding: 0.65rem 1.4rem !important;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04) !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        text-decoration: none !important;
    }

    .localization-settings-page .btn-back-settings:hover,
    .localization-settings-page .loc-btn-back:hover {
        background-color: var(--loc-btn-back-hover-bg) !important;
        color: var(--loc-btn-back-hover-color) !important;
        -webkit-text-fill-color: var(--loc-btn-back-hover-color) !important;
        border-color: rgba(47, 107, 255, 0.4) !important;
        transform: translateY(-2px) !important;
    }

    .localization-settings-page .btn-back-settings:hover .back-arrow-icon,
    .localization-settings-page .loc-btn-back:hover .back-arrow-icon {
        transform: translateX(-4px);
    }

    .back-arrow-icon {
        transition: transform 0.25s ease;
        display: inline-block;
    }

    /* ==========================================================================
       EXECUTIVE SUMMARY STATS GRID (LIGHT & DARK BULLETPROOF)
       ========================================================================== */
    .localization-settings-page .stats-grid,
    .localization-settings-page .loc-stats-grid {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 1.25rem !important;
        margin-bottom: 2rem !important;
    }

    .localization-settings-page .stats-grid .loc-stat-card,
    .localization-settings-page .stats-grid .stat-card,
    .localization-settings-page .loc-stat-card,
    .localization-settings-page .stat-card,
    .localization-settings-page .stat-card:first-of-type,
    .localization-settings-page .loc-stat-card:first-of-type {
        background: var(--loc-stat-bg) !important;
        backdrop-filter: blur(20px) !important;
        border-radius: 24px !important;
        padding: 1.5rem !important;
        border: 1px solid var(--loc-stat-border) !important;
        box-shadow: var(--loc-card-shadow) !important;
        display: flex !important;
        align-items: center !important;
        gap: 1.25rem !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative !important;
        overflow: hidden !important;
        color: var(--loc-stat-val) !important;
    }

    .localization-settings-page .stats-grid .loc-stat-card::after,
    .localization-settings-page .stats-grid .stat-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4F83FF, #2F6BFF);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .localization-settings-page .stats-grid .loc-stat-card:hover::after,
    .localization-settings-page .stats-grid .stat-card:hover::after {
        transform: scaleX(1);
    }

    .localization-settings-page .stats-grid .loc-stat-card:hover,
    .localization-settings-page .stats-grid .stat-card:hover {
        transform: translateY(-4px) !important;
        box-shadow: var(--loc-card-hover-shadow) !important;
        border-color: rgba(47, 107, 255, 0.3) !important;
    }

    /* Stat Card Text Elements */
    .localization-settings-page .stat-info,
    .localization-settings-page .loc-stat-info {
        flex: 1;
        min-width: 0;
    }

    .localization-settings-page .stat-info h6,
    .localization-settings-page .loc-stat-info h6,
    .localization-settings-page .stat-card:first-of-type .stat-info h6,
    .localization-settings-page .stat-card .stat-info h6 {
        font-size: 0.72rem !important;
        color: var(--loc-stat-title) !important;
        -webkit-text-fill-color: var(--loc-stat-title) !important;
        margin: 0 0 0.25rem 0 !important;
        text-transform: uppercase !important;
        font-weight: 700 !important;
        letter-spacing: 0.05em !important;
    }

    .localization-settings-page .stat-info h3,
    .localization-settings-page .loc-stat-info h3,
    .localization-settings-page .stat-card:first-of-type .stat-info h3,
    .localization-settings-page .stat-card .stat-info h3 {
        font-size: 1.25rem !important;
        font-weight: 800 !important;
        color: var(--loc-stat-val) !important;
        -webkit-text-fill-color: var(--loc-stat-val) !important;
        margin: 0 !important;
        line-height: 1.2 !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Stat Icons Core */
    .localization-settings-page .stat-icon,
    .localization-settings-page .loc-stat-icon {
        width: 56px !important;
        height: 56px !important;
        border-radius: 18px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.5rem !important;
        flex-shrink: 0 !important;
        transition: transform 0.25s ease;
    }

    .localization-settings-page .stats-grid .loc-stat-card:hover .stat-icon,
    .localization-settings-page .stats-grid .stat-card:hover .stat-icon {
        transform: scale(1.08);
    }

    /* Currency Stat Icon */
    .localization-settings-page .stat-icon.curr,
    .localization-settings-page .loc-stat-icon.curr {
        background: var(--loc-icon-curr-bg) !important;
        color: var(--loc-icon-curr-color) !important;
    }
    .localization-settings-page .stat-icon.curr i,
    .localization-settings-page .loc-stat-icon.curr i,
    .localization-settings-page .stat-icon.curr svg,
    .localization-settings-page .loc-stat-icon.curr svg {
        color: var(--loc-icon-curr-color) !important;
        fill: var(--loc-icon-curr-color) !important;
        -webkit-text-fill-color: var(--loc-icon-curr-color) !important;
        background: transparent !important;
    }

    /* Timezone Stat Icon */
    .localization-settings-page .stat-icon.tz,
    .localization-settings-page .loc-stat-icon.tz {
        background: var(--loc-icon-tz-bg) !important;
        color: var(--loc-icon-tz-color) !important;
    }
    .localization-settings-page .stat-icon.tz i,
    .localization-settings-page .loc-stat-icon.tz i,
    .localization-settings-page .stat-icon.tz svg,
    .localization-settings-page .loc-stat-icon.tz svg {
        color: var(--loc-icon-tz-color) !important;
        fill: var(--loc-icon-tz-color) !important;
        -webkit-text-fill-color: var(--loc-icon-tz-color) !important;
        background: transparent !important;
    }

    /* Language Stat Icon */
    .localization-settings-page .stat-icon.lang,
    .localization-settings-page .loc-stat-icon.lang {
        background: var(--loc-icon-lang-bg) !important;
        color: var(--loc-icon-lang-color) !important;
    }
    .localization-settings-page .stat-icon.lang i,
    .localization-settings-page .loc-stat-icon.lang i,
    .localization-settings-page .stat-icon.lang svg,
    .localization-settings-page .loc-stat-icon.lang svg {
        color: var(--loc-icon-lang-color) !important;
        fill: var(--loc-icon-lang-color) !important;
        -webkit-text-fill-color: var(--loc-icon-lang-color) !important;
        background: transparent !important;
    }

    /* Format Stat Icon */
    .localization-settings-page .stat-icon.format,
    .localization-settings-page .loc-stat-icon.format {
        background: var(--loc-icon-format-bg) !important;
        color: var(--loc-icon-format-color) !important;
    }
    .localization-settings-page .stat-icon.format i,
    .localization-settings-page .loc-stat-icon.format i,
    .localization-settings-page .stat-icon.format svg,
    .localization-settings-page .loc-stat-icon.format svg {
        color: var(--loc-icon-format-color) !important;
        fill: var(--loc-icon-format-color) !important;
        -webkit-text-fill-color: var(--loc-icon-format-color) !important;
        background: transparent !important;
    }

    /* ==========================================================================
       MAIN FORM CARD & HEADER
       ========================================================================== */
    .localization-settings-page .address-card-elevated,
    .localization-settings-page .loc-form-card {
        background: var(--loc-card-bg) !important;
        backdrop-filter: blur(20px) !important;
        border-radius: 28px !important;
        border: 1px solid var(--loc-card-border) !important;
        box-shadow: var(--loc-card-shadow) !important;
        overflow: hidden !important;
        transition: all 0.3s ease;
    }

    .localization-settings-page .card-header-custom {
        padding: 1.5rem 2.25rem !important;
        border-bottom: 1px solid var(--loc-divider) !important;
        display: flex !important;
        align-items: center !important;
        gap: 1rem !important;
        background: transparent !important;
    }

    .localization-settings-page .card-header-avatar {
        width: 48px !important;
        height: 48px !important;
        border-radius: 16px !important;
        background: var(--loc-badge-bg) !important;
        color: var(--loc-badge-color) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.35rem !important;
        flex-shrink: 0 !important;
    }

    .localization-settings-page .card-header-avatar i {
        color: var(--loc-badge-color) !important;
        -webkit-text-fill-color: var(--loc-badge-color) !important;
        fill: var(--loc-badge-color) !important;
        background: transparent !important;
    }

    .localization-settings-page .card-header-custom h5 {
        color: var(--loc-text-title) !important;
        -webkit-text-fill-color: var(--loc-text-title) !important;
        margin: 0 !important;
    }

    .localization-settings-page .card-header-custom .text-muted,
    .localization-settings-page .card-header-custom small {
        color: var(--loc-text-muted) !important;
        -webkit-text-fill-color: var(--loc-text-muted) !important;
    }

    /* Section Badges */
    .localization-settings-page .section-badge,
    .localization-settings-page .loc-section-badge {
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
        padding: 0.45rem 1.15rem !important;
        border-radius: 40px !important;
        background: var(--loc-badge-bg) !important;
        color: var(--loc-badge-color) !important;
        -webkit-text-fill-color: var(--loc-badge-color) !important;
        font-weight: 800 !important;
        font-size: 0.82rem !important;
        letter-spacing: 0.03em !important;
        border: 1px solid var(--loc-badge-border) !important;
        margin-bottom: 1.25rem !important;
    }

    .localization-settings-page .section-badge i,
    .localization-settings-page .loc-section-badge i {
        color: var(--loc-badge-color) !important;
        -webkit-text-fill-color: var(--loc-badge-color) !important;
    }

    /* Labels */
    .localization-settings-page .form-label-custom,
    .localization-settings-page .loc-form-label {
        font-size: 0.88rem !important;
        font-weight: 700 !important;
        color: var(--loc-text-title) !important;
        -webkit-text-fill-color: var(--loc-text-title) !important;
        margin-bottom: 8px !important;
        display: flex !important;
        align-items: center !important;
        gap: 4px !important;
    }

    .req-asterisk {
        color: #2F6BFF;
        font-weight: 800;
    }

    html[data-pms-theme="dark"] .req-asterisk,
    html[data-theme="dark"] .req-asterisk,
    body[data-pms-theme="dark"] .req-asterisk,
    body.dark-mode .req-asterisk,
    .dark-mode .req-asterisk {
        color: #60A5FA !important;
    }

    /* ==========================================================================
       INPUT GROUPS, SELECTS & CONTROLS
       ========================================================================== */
    .localization-settings-page .input-group-custom,
    .localization-settings-page .loc-input-group {
        border-radius: 16px !important;
        border: 1px solid var(--loc-input-border) !important;
        background-color: var(--loc-input-bg) !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        overflow: hidden !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02) !important;
    }

    .localization-settings-page .input-group-custom:focus-within,
    .localization-settings-page .loc-input-group:focus-within {
        border-color: var(--loc-input-focus-border) !important;
        background-color: var(--loc-input-bg) !important;
        box-shadow: var(--loc-input-focus-shadow) !important;
        transform: translateY(-1px) !important;
    }

    .localization-settings-page .input-group-custom .input-group-text,
    .localization-settings-page .loc-input-group .input-group-text {
        background-color: transparent !important;
        border: none !important;
        color: var(--loc-input-icon) !important;
        padding-left: 18px !important;
        padding-right: 12px !important;
        font-size: 1.1rem !important;
    }

    .localization-settings-page .input-group-custom .form-control,
    .localization-settings-page .loc-input-group .form-control,
    .localization-settings-page .input-group-custom .form-select,
    .localization-settings-page .loc-input-group .form-select {
        border: none !important;
        background-color: transparent !important;
        font-size: 0.92rem !important;
        font-weight: 600 !important;
        color: var(--loc-input-color) !important;
        -webkit-text-fill-color: var(--loc-input-color) !important;
        padding-right: 2.25rem !important;
        height: 50px !important;
    }

    .localization-settings-page .input-group-custom .form-select,
    .localization-settings-page .loc-input-group .form-select {
        background-image: var(--loc-select-arrow) !important;
        background-repeat: no-repeat !important;
        background-position: right 1rem center !important;
        background-size: 16px 12px !important;
        cursor: pointer !important;
    }

    .localization-settings-page .input-group-custom .form-select option,
    .localization-settings-page .loc-input-group .form-select option {
        background-color: var(--loc-select-option-bg) !important;
        color: var(--loc-select-option-color) !important;
        font-weight: 500 !important;
        padding: 8px 12px !important;
    }

    .localization-settings-page .input-group-custom .form-control[readonly],
    .localization-settings-page .loc-input-group .form-control[readonly] {
        background-color: var(--loc-input-readonly-bg) !important;
        color: var(--loc-input-readonly-color) !important;
        -webkit-text-fill-color: var(--loc-input-readonly-color) !important;
        cursor: not-allowed !important;
    }

    .localization-settings-page .loc-input-group input[name="currency_symbol"][readonly] {
        font-family: "Nirmala UI", "Noto Sans Bengali", "Segoe UI", Arial, sans-serif !important;
        font-size: 1.35rem !important;
        font-weight: 600 !important;
        line-height: 1.5 !important;
        color: var(--loc-input-color) !important;
        -webkit-text-fill-color: var(--loc-input-color) !important;
        opacity: 1 !important;
        cursor: default !important;
    }

    .localization-settings-page .input-group-custom .form-select:disabled,
    .localization-settings-page .loc-input-group .form-select:disabled {
        background-color: var(--loc-input-readonly-bg) !important;
        color: var(--loc-input-readonly-color) !important;
        -webkit-text-fill-color: var(--loc-input-readonly-color) !important;
        cursor: not-allowed !important;
        opacity: 0.9 !important;
    }

    .localization-settings-page .input-group-custom .form-control:focus,
    .localization-settings-page .loc-input-group .form-control:focus,
    .localization-settings-page .input-group-custom .form-select:focus,
    .localization-settings-page .loc-input-group .form-select:focus {
        box-shadow: none !important;
        background-color: transparent !important;
        color: var(--loc-input-color) !important;
    }

    .localization-settings-page small.text-muted,
    .localization-settings-page .text-muted {
        color: var(--loc-text-muted) !important;
        -webkit-text-fill-color: var(--loc-text-muted) !important;
    }

    .localization-settings-page .loc-lock-icon {
        color: var(--loc-badge-color) !important;
    }

    /* ==========================================================================
       ALERTS & READONLY BADGES
       ========================================================================== */
    .localization-settings-page .loc-alert-success {
        background: var(--loc-alert-success-bg) !important;
        color: var(--loc-alert-success-text) !important;
        border-left: 5px solid var(--loc-alert-success-border) !important;
        border-top: none !important;
        border-right: none !important;
        border-bottom: none !important;
        border-radius: 18px !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
    }

    .localization-settings-page .loc-alert-success i {
        color: var(--loc-alert-success-border) !important;
    }

    .localization-settings-page .loc-alert-viewonly {
        background: var(--loc-alert-viewonly-bg) !important;
        border-left: 4px solid var(--loc-alert-viewonly-border) !important;
        border-top: none !important;
        border-right: none !important;
        border-bottom: none !important;
        border-radius: 16px !important;
        padding: 14px 18px !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
    }

    .localization-settings-page .loc-alert-viewonly .loc-viewonly-icon {
        color: var(--loc-alert-viewonly-icon) !important;
    }

    .localization-settings-page .loc-alert-viewonly .loc-viewonly-title {
        color: var(--loc-alert-viewonly-title) !important;
        font-size: 14px;
        font-weight: 700;
    }

    .localization-settings-page .loc-alert-viewonly .loc-viewonly-text {
        color: var(--loc-alert-viewonly-text) !important;
        font-size: 13px;
    }

    /* Card Footer & Action Buttons */
    .localization-settings-page .border-top {
        border-top: 1px solid var(--loc-divider) !important;
    }

    .localization-settings-page .btn-save-address {
        height: 50px !important;
        border-radius: 40px !important;
        font-weight: 700 !important;
        font-size: 0.95rem !important;
        padding: 0 32px !important;
        background: linear-gradient(145deg, #4F83FF, #2F6BFF) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        border: none !important;
        box-shadow: 0 6px 20px -4px rgba(47, 107, 255, 0.4) !important;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    .localization-settings-page .btn-save-address:hover {
        transform: translateY(-2px) scale(1.02) !important;
        box-shadow: 0 10px 28px -4px rgba(47, 107, 255, 0.5) !important;
        color: #ffffff !important;
    }

    .localization-settings-page .loc-readonly-badge {
        background: var(--loc-readonly-badge-bg) !important;
        border: 1px solid var(--loc-readonly-badge-border) !important;
        color: var(--loc-readonly-badge-color) !important;
        -webkit-text-fill-color: var(--loc-readonly-badge-color) !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        padding: 0.6rem 1.25rem !important;
        border-radius: 999px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    .localization-settings-page .loc-readonly-badge i {
        color: var(--loc-readonly-badge-color) !important;
    }

    /* ==========================================================================
       RESPONSIVE BREAKPOINTS
       ========================================================================== */
    @media (max-width: 1200px) {
        .localization-settings-page .stats-grid,
        .localization-settings-page .loc-stats-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    @media (max-width: 768px) {
        .localization-settings-page {
            padding: 1.25rem 1rem !important;
        }

        .localization-settings-page .branches-header,
        .localization-settings-page .loc-header-card {
            padding: 1.25rem 1.5rem !important;
        }

        .localization-settings-page .card-header-custom {
            padding: 1.25rem 1.5rem !important;
        }

        .localization-settings-page .stats-grid,
        .localization-settings-page .loc-stats-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush

@section('content')
<div class="localization-settings-page">
    <div class="localization-settings-shell">
        <div class="ambient-orb orb-1"></div>
        <div class="ambient-orb orb-2"></div>

        <div class="content-wrapper">
            <!-- Breadcrumbs -->
            <div class="breadcrumb-custom">
                <i class="fas fa-building"></i>
                <a href="{{ route('admin.settings.index') }}">Admin</a>
                <span>/</span>
                <a href="{{ route('admin.settings.index') }}">Settings</a>
                <span>/</span>
                <span>Regional & Localization</span>
            </div>

            <!-- Page Header Card -->
            <div class="branches-header loc-header-card">
                <div class="header-left-box">
                    <div class="header-icon-badge loc-header-icon-badge">
                        <i class="fas fa-globe"></i>
                    </div>
                    <div class="header-title loc-header-title">
                        <h1>Regional & Localization Settings</h1>
                        <p>Configure currency, timezone, date & time display formats, and default system language preferences.</p>
                    </div>
                </div>

                <a href="{{ route('admin.settings.index') }}" class="btn-back-settings loc-btn-back">
                    <i class="fas fa-arrow-left me-1 back-arrow-icon"></i> Back to Settings
                </a>
            </div>

            <!-- Alert Notifications -->
            @if(session('success'))
                <div class="alert loc-alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="fas fa-check-circle fs-4 me-2"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Executive Summary Stats Grid -->
            @php
                $currCode = $settings['currency'] ?? 'USD';
                $currSym = $settings['currency_symbol'] ?? '$';
                $tzVal = $settings['timezone'] ?? config('app.timezone', 'Asia/Kolkata');
                $langVal = strtoupper($settings['language'] ?? 'en');
                $dateFormatVal = $settings['date_format'] ?? 'Y-m-d';
                $timeFormatVal = ($settings['time_format'] ?? '') == 'H:i' ? '24-Hour' : '12-Hour';
            @endphp
            <div class="stats-grid loc-stats-grid">
                <div class="stat-card loc-stat-card">
                    <div class="stat-icon loc-stat-icon curr">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info loc-stat-info">
                        <h6>Default Currency</h6>
                        <h3 id="statCurrencyDisplay">{{ $currCode }} ({{ $currSym }})</h3>
                    </div>
                </div>
                <div class="stat-card loc-stat-card">
                    <div class="stat-icon loc-stat-icon tz">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info loc-stat-info">
                        <h6>System Timezone</h6>
                        <h3>{{ $tzVal }}</h3>
                    </div>
                </div>
                <div class="stat-card loc-stat-card">
                    <div class="stat-icon loc-stat-icon lang">
                        <i class="fas fa-language"></i>
                    </div>
                    <div class="stat-info loc-stat-info">
                        <h6>System Language</h6>
                        <h3>{{ $langVal }} Language</h3>
                    </div>
                </div>
                <div class="stat-card loc-stat-card">
                    <div class="stat-icon loc-stat-icon format">
                        <i class="fas fa-calendar-days"></i>
                    </div>
                    <div class="stat-info loc-stat-info">
                        <h6>Date / Time Format</h6>
                        <h3>{{ $dateFormatVal }} ({{ $timeFormatVal }})</h3>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="address-card-elevated loc-form-card">
                <div class="card-header-custom">
                    <div class="card-header-avatar shadow-sm">
                        <i class="fas fa-sliders"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold fs-5">Currency, Timezone & Regional Formats</h5>
                        <small class="text-muted">Set global default formatting preferences across financial reports, time logs, and employee portals</small>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    @if($isSettingsReadOnly)
                        <div class="alert loc-alert-viewonly d-flex align-items-center mb-4" role="alert">
                            <i class="fas fa-eye me-3 fs-3 loc-viewonly-icon"></i>
                            <div>
                                <strong class="d-block loc-viewonly-title">View-Only Mode</strong>
                                <span class="loc-viewonly-text">You are viewing localization and regional display settings in read-only mode. Only Administrators have permission to modify currency, timezone, and date/time formats.</span>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.settings.localization.update') }}">
                        @csrf

                        <!-- Section 1: Currency & Financial Formatting -->
                        <div class="section-badge loc-section-badge">
                            <i class="fas fa-coins"></i> Currency & Financial Formatting
                        </div>

                        <div class="row g-4 mb-5">
                            <!-- Currency Code -->
                            <div class="col-md-4">
                                <label class="form-label-custom loc-form-label">Default Currency Code <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom loc-input-group">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                    <select name="currency" class="form-select" {{ $isSettingsReadOnly ? 'disabled' : 'required' }}>
                                        <option value="USD" {{ ($settings['currency'] ?? '') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                        <option value="EUR" {{ ($settings['currency'] ?? '') == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                        <option value="GBP" {{ ($settings['currency'] ?? '') == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                        <option value="INR" {{ ($settings['currency'] ?? '') == 'INR' ? 'selected' : '' }}>INR (₹)</option>
                                        <option value="BDT" {{ ($settings['currency'] ?? '') == 'BDT' ? 'selected' : '' }}>BDT (৳)</option>
                                        <option value="CAD" {{ ($settings['currency'] ?? '') == 'CAD' ? 'selected' : '' }}>CAD ($)</option>
                                        <option value="AUD" {{ ($settings['currency'] ?? '') == 'AUD' ? 'selected' : '' }}>AUD ($)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Currency Symbol (Readonly) -->
                            <div class="col-md-4">
                                <label class="form-label-custom loc-form-label">Currency Symbol <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom loc-input-group">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    <input type="text" name="currency_symbol" class="form-control"
                                        value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '$') }}" readonly required
                                        title="Auto-filled based on selected Default Currency Code">
                                </div>
                                <small class="text-muted mt-1 d-block"><i class="fas fa-lock me-1 loc-lock-icon"></i>Auto-derived from selected Currency Code.</small>
                            </div>

                            <!-- Currency Symbol Position -->
                            <div class="col-md-4">
                                <label class="form-label-custom loc-form-label">Symbol Position <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom loc-input-group">
                                    <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                                    <select name="currency_position" class="form-select" {{ $isSettingsReadOnly ? 'disabled' : 'required' }}>
                                        <option value="left" {{ ($settings['currency_position'] ?? '') == 'left' ? 'selected' : '' }}>Left ({{ $currSym }}100)</option>
                                        <option value="right" {{ ($settings['currency_position'] ?? '') == 'right' ? 'selected' : '' }}>Right (100{{ $currSym }})</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Timezone & Language Preferences -->
                        <div class="section-badge loc-section-badge">
                            <i class="fas fa-earth-americas"></i> Timezone & Language Preferences
                        </div>

                        <div class="row g-4 mb-5">
                            <!-- Timezone -->
                            <div class="col-md-6">
                                <label class="form-label-custom loc-form-label">System Timezone <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom loc-input-group">
                                    <span class="input-group-text"><i class="fas fa-globe-americas"></i></span>
                                    <select name="timezone" class="form-select" {{ $isSettingsReadOnly ? 'disabled' : 'required' }}>
                                        <option value="UTC" {{ ($settings['timezone'] ?? '') == 'UTC' ? 'selected' : '' }}>UTC (Coordinated Universal Time)</option>
                                        <option value="Asia/Dhaka" {{ ($settings['timezone'] ?? '') == 'Asia/Dhaka' ? 'selected' : '' }}>Asia/Dhaka (GMT+6)</option>
                                        <option value="Asia/Kolkata" {{ ($settings['timezone'] ?? '') == 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (GMT+5:30)</option>
                                        <option value="America/New_York" {{ ($settings['timezone'] ?? '') == 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                                        <option value="Europe/London" {{ ($settings['timezone'] ?? '') == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Default Language -->
                            <div class="col-md-6">
                                <label class="form-label-custom loc-form-label">Default System Language <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom loc-input-group">
                                    <span class="input-group-text"><i class="fas fa-language"></i></span>
                                    <select name="language" class="form-select" {{ $isSettingsReadOnly ? 'disabled' : 'required' }}>
                                        <option value="en" {{ ($settings['language'] ?? '') == 'en' ? 'selected' : '' }}>English</option>
                                        <option value="bn" {{ ($settings['language'] ?? '') == 'bn' ? 'selected' : '' }}>Bengali</option>
                                        <option value="es" {{ ($settings['language'] ?? '') == 'es' ? 'selected' : '' }}>Spanish</option>
                                        <option value="fr" {{ ($settings['language'] ?? '') == 'fr' ? 'selected' : '' }}>French</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Date & Time Display Formats -->
                        <div class="section-badge loc-section-badge">
                            <i class="fas fa-calendar-days"></i> Date & Time Display Formats
                        </div>

                        <div class="row g-4">
                            <!-- Date Format -->
                            <div class="col-md-6">
                                <label class="form-label-custom loc-form-label">Date Display Format <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom loc-input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                                    <select name="date_format" class="form-select" {{ $isSettingsReadOnly ? 'disabled' : 'required' }}>
                                        <option value="Y-m-d" {{ ($settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD (2026-08-12)</option>
                                        <option value="d-m-Y" {{ ($settings['date_format'] ?? '') == 'd-m-Y' ? 'selected' : '' }}>DD-MM-YYYY (12-08-2026)</option>
                                        <option value="m/d/Y" {{ ($settings['date_format'] ?? '') == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY (08/12/2026)</option>
                                        <option value="d M, Y" {{ ($settings['date_format'] ?? '') == 'd M, Y' ? 'selected' : '' }}>DD MMM, YYYY (12 Aug, 2026)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Time Format -->
                            <div class="col-md-6">
                                <label class="form-label-custom loc-form-label">Time Display Format <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom loc-input-group">
                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                    <select name="time_format" class="form-select" {{ $isSettingsReadOnly ? 'disabled' : 'required' }}>
                                        <option value="h:i A" {{ ($settings['time_format'] ?? '') == 'h:i A' ? 'selected' : '' }}>12-Hour Format (02:30 PM)</option>
                                        <option value="H:i" {{ ($settings['time_format'] ?? '') == 'H:i' ? 'selected' : '' }}>24-Hour Format (14:30)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="mt-5 pt-4 border-top d-flex justify-content-end">
                            @if($isSettingsReadOnly)
                                <span class="badge rounded-pill loc-readonly-badge">
                                    <i class="fas fa-lock me-1"></i> Read-Only (Admin Managed)
                                </span>
                            @else
                                <button type="submit" class="btn-save-address">
                                    <i class="fas fa-save me-1.5"></i> Save Localization Settings
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const currencySelect = document.querySelector('select[name="currency"]');
    const symbolInput = document.querySelector('input[name="currency_symbol"]');
    const positionSelect = document.querySelector('select[name="currency_position"]');
    const statDisplay = document.getElementById('statCurrencyDisplay');

    const currencyMap = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'INR': '₹',
        'BDT': '৳',
        'CAD': '$',
        'AUD': '$'
    };

    function updateSymbolPositionOptions(symbol, code) {
        const sym = symbol || '$';
        if (positionSelect) {
            const leftOption = positionSelect.querySelector('option[value="left"]');
            const rightOption = positionSelect.querySelector('option[value="right"]');

            if (leftOption) {
                leftOption.textContent = `Left (${sym}100)`;
            }
            if (rightOption) {
                rightOption.textContent = `Right (100${sym})`;
            }
        }

        if (statDisplay) {
            const currentCode = code || (currencySelect ? currencySelect.value : 'USD');
            statDisplay.textContent = `${currentCode} (${sym})`;
        }
    }

    if (currencySelect && symbolInput) {
        currencySelect.addEventListener('change', function () {
            const selectedCode = this.value;
            let newSymbol = currencyMap[selectedCode];
            
            if (!newSymbol) {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption) {
                    const match = selectedOption.textContent.match(/\(([^)]+)\)/);
                    if (match && match[1]) {
                        newSymbol = match[1];
                    }
                }
            }

            if (newSymbol) {
                symbolInput.value = newSymbol;
                updateSymbolPositionOptions(newSymbol, selectedCode);
            }
        });

        // Initialize on load
        const initialSymbol = symbolInput.value.trim() || '$';
        const initialCode = currencySelect ? currencySelect.value : 'USD';
        updateSymbolPositionOptions(initialSymbol, initialCode);
    }
});
</script>
@endpush
