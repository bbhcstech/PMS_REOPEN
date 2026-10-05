@extends('admin.layout.app')

@section('title', 'Security Settings')

@push('styles')
<style>
    .security-settings-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #F8FAFC 0%, #EEF2FF 50%, #F8FAFC 100%);
        color: #0F172A;
    }

    .security-settings-shell {
        position: relative;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Ambient Orbs */
    .ambient-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(130px);
        opacity: 0.35;
        pointer-events: none;
        z-index: 1;
    }

    .orb-1 {
        top: -100px;
        right: -100px;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(79, 131, 255, 0.12) 0%, transparent 70%);
        animation: orbFloat 20s ease-in-out infinite;
    }

    .orb-2 {
        bottom: -100px;
        left: -100px;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(47, 107, 255, 0.1) 0%, transparent 70%);
        animation: orbFloat 25s ease-in-out infinite reverse;
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

    /* ===== BREADCRUMB ===== */
    .breadcrumb-custom {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 12px;
    }

    .breadcrumb-custom a {
        color: #2F6BFF;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .breadcrumb-custom a:hover {
        color: #1E4FCC;
    }

    /* ===== HEADER CARD ===== */
    .branches-header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        padding: 1.75rem 2.25rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(47, 107, 255, 0.15);
        box-shadow: 0 10px 30px -10px rgba(47, 107, 255, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
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

    .header-icon-badge {
        width: 58px;
        height: 58px;
        border-radius: 20px;
        background: linear-gradient(145deg, #4F83FF, #2F6BFF);
        color: #ffffff !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        box-shadow: 0 8px 20px -4px rgba(47, 107, 255, 0.35);
        flex-shrink: 0;
    }

    .header-icon-badge i,
    .header-icon-badge svg,
    .header-icon-badge [class*="fa"] {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        background: transparent !important;
        background-color: transparent !important;
        fill: #ffffff !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .stat-icon.pass,
    .security-settings-page .stat-card:first-of-type .stat-icon.pass {
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF) !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
    }

    .stat-icon.pass i,
    .stat-icon.pass svg,
    .stat-icon.pass [class*="fa"],
    .security-settings-page .stat-card .stat-icon.pass i,
    .security-settings-page .stat-card .stat-icon.pass svg,
    .security-settings-page .stat-card .stat-icon.pass [class*="fa"],
    .security-settings-page .stat-card:first-of-type .stat-icon.pass i,
    .security-settings-page .stat-card:first-of-type .stat-icon.pass svg,
    .security-settings-page .stat-card:first-of-type .stat-icon.pass [class*="fa"] {
        background: transparent !important;
        background-color: transparent !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        fill: #2F6BFF !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .stat-icon.session,
    .security-settings-page .stat-card .stat-icon.session {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
    }

    .stat-icon.session i,
    .stat-icon.session svg,
    .stat-icon.session [class*="fa"],
    .security-settings-page .stat-card .stat-icon.session i,
    .security-settings-page .stat-card .stat-icon.session svg,
    .security-settings-page .stat-card .stat-icon.session [class*="fa"],
    .security-settings-page .stat-card:first-of-type .stat-icon.session i,
    .security-settings-page .stat-card:first-of-type .stat-icon.session svg,
    .security-settings-page .stat-card:first-of-type .stat-icon.session [class*="fa"] {
        background: transparent !important;
        background-color: transparent !important;
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
        fill: #0284c7 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .stat-icon.lockout,
    .security-settings-page .stat-card .stat-icon.lockout {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
    }

    .stat-icon.lockout i,
    .stat-icon.lockout svg,
    .stat-icon.lockout [class*="fa"],
    .security-settings-page .stat-card .stat-icon.lockout i,
    .security-settings-page .stat-card .stat-icon.lockout svg,
    .security-settings-page .stat-card .stat-icon.lockout [class*="fa"],
    .security-settings-page .stat-card:first-of-type .stat-icon.lockout i,
    .security-settings-page .stat-card:first-of-type .stat-icon.lockout svg,
    .security-settings-page .stat-card:first-of-type .stat-icon.lockout [class*="fa"] {
        background: transparent !important;
        background-color: transparent !important;
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
        fill: #d97706 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .stat-icon.twofa,
    .security-settings-page .stat-card .stat-icon.twofa {
        background: linear-gradient(145deg, #e0e7ff, #c7d2fe) !important;
        color: #4f46e5 !important;
        -webkit-text-fill-color: #4f46e5 !important;
    }

    .stat-icon.twofa i,
    .stat-icon.twofa svg,
    .stat-icon.twofa [class*="fa"],
    .security-settings-page .stat-card .stat-icon.twofa i,
    .security-settings-page .stat-card .stat-icon.twofa svg,
    .security-settings-page .stat-card .stat-icon.twofa [class*="fa"],
    .security-settings-page .stat-card:first-of-type .stat-icon.twofa i,
    .security-settings-page .stat-card:first-of-type .stat-icon.twofa svg,
    .security-settings-page .stat-card:first-of-type .stat-icon.twofa [class*="fa"] {
        background: transparent !important;
        background-color: transparent !important;
        color: #4f46e5 !important;
        -webkit-text-fill-color: #4f46e5 !important;
        fill: #4f46e5 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    /* ===== STATS GRID LAYOUT ===== */
    .stats-grid {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 1.25rem !important;
        margin-bottom: 2rem !important;
    }

    .stat-card,
    .security-settings-page .stat-card,
    .security-settings-page .stat-card:first-of-type {
        background: #ffffff !important;
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 1.5rem;
        border: 1px solid rgba(47, 107, 255, 0.14) !important;
        box-shadow: 0 10px 30px -10px rgba(47, 107, 255, 0.08) !important;
        display: flex !important;
        align-items: center !important;
        gap: 1.25rem !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        color: #0F172A !important;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .stat-info h6 {
        font-size: 0.72rem;
        color: #64748b;
        margin: 0 0 0.2rem 0;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.05em;
    }

    .stat-info h3 {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0F172A;
        margin: 0;
        line-height: 1.2;
    }

    /* ===== FORM CARD & INPUTS ===== */
    .address-card-elevated {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        border: 1px solid rgba(47, 107, 255, 0.15);
        box-shadow: 0 10px 30px -10px rgba(47, 107, 255, 0.08);
        overflow: hidden;
    }

    .card-header-custom {
        padding: 1.5rem 2.25rem;
        border-bottom: 1px solid rgba(47, 107, 255, 0.12);
        display: flex;
        align-items: center;
        gap: 1rem;
        background: transparent;
    }

    .card-header-avatar {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF);
        color: #2F6BFF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }

    .card-header-avatar i,
    .card-header-avatar svg,
    .card-header-avatar [class*="fa"] {
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        background: transparent !important;
        background-color: transparent !important;
        fill: #2F6BFF !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .section-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 1rem;
        border-radius: 40px;
        background: #EEF2FF;
        color: #2F6BFF;
        font-weight: 800;
        font-size: 0.82rem;
        letter-spacing: 0.03em;
        border: 1px solid rgba(47, 107, 255, 0.2);
        margin-bottom: 1.25rem;
    }

    .form-label-custom {
        font-size: 0.88rem;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .input-group-custom {
        border-radius: 16px;
        border: 1px solid rgba(47, 107, 255, 0.2);
        background-color: #fafefb;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .input-group-custom:focus-within {
        border-color: #60A5FA;
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(79, 131, 255, 0.15);
        transform: translateY(-1px);
    }

    .input-group-custom .input-group-text {
        background-color: transparent;
        border: none;
        color: #2F6BFF;
        padding-left: 18px;
        padding-right: 12px;
        font-size: 1.1rem;
    }

    .input-group-custom .form-control {
        border: none;
        background-color: transparent;
        font-size: 0.92rem;
        font-weight: 600;
        color: #0F172A;
        padding-right: 18px;
        height: 50px;
    }

    .input-group-custom .form-control:focus {
        box-shadow: none;
        background-color: transparent;
    }

    /* Policy Switch Box */
    .policy-switch-box {
        background: #f0fdf4;
        border: 1px solid rgba(47, 107, 255, 0.25);
        border-radius: 20px;
        padding: 1.3rem 1.6rem;
        transition: all 0.25s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .policy-switch-box:hover {
        border-color: #60A5FA;
        background: #EEF2FF;
        box-shadow: 0 4px 12px rgba(47, 107, 255, 0.08);
    }

    .btn-save-address {
        height: 50px;
        border-radius: 40px;
        font-weight: 700;
        font-size: 0.95rem;
        padding: 0 32px;
        background: linear-gradient(145deg, #4F83FF, #2F6BFF);
        color: white !important;
        border: none;
        box-shadow: 0 6px 20px -4px rgba(47, 107, 255, 0.35);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }

    .btn-save-address:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 10px 28px -4px rgba(47, 107, 255, 0.45);
        color: white !important;
    }

    .req-asterisk {
        color: #2F6BFF;
        font-weight: 800;
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .security-settings-page {
            padding: 1.25rem 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
    /* ===== DARK MODE SUPPORT ===== */
    html[data-pms-theme="dark"] .security-settings-page,
    html[data-theme="dark"] .security-settings-page,
    html[data-bs-theme="dark"] .security-settings-page,
    body[data-pms-theme="dark"] .security-settings-page,
    body[data-theme="dark"] .security-settings-page,
    body[data-bs-theme="dark"] .security-settings-page,
    [data-pms-theme="dark"] .security-settings-page,
    [data-theme="dark"] .security-settings-page,
    [data-bs-theme="dark"] .security-settings-page,
    .dark-mode .security-settings-page {
        background: #0b0f19 !important;
        color: #e2e8f0 !important;
    }

    html[data-pms-theme="dark"] .branches-header,
    html[data-theme="dark"] .branches-header,
    html[data-bs-theme="dark"] .branches-header,
    body[data-pms-theme="dark"] .branches-header,
    body[data-theme="dark"] .branches-header,
    body[data-bs-theme="dark"] .branches-header,
    [data-pms-theme="dark"] .branches-header,
    [data-theme="dark"] .branches-header,
    [data-bs-theme="dark"] .branches-header,
    html[data-pms-theme="dark"] .address-card-elevated,
    html[data-theme="dark"] .address-card-elevated,
    html[data-bs-theme="dark"] .address-card-elevated,
    body[data-pms-theme="dark"] .address-card-elevated,
    body[data-theme="dark"] .address-card-elevated,
    body[data-bs-theme="dark"] .address-card-elevated,
    [data-pms-theme="dark"] .address-card-elevated,
    [data-theme="dark"] .address-card-elevated,
    [data-bs-theme="dark"] .address-card-elevated,
    .dark-mode .branches-header,
    .dark-mode .address-card-elevated {
        background: #111827 !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.4) !important;
    }

    html[data-pms-theme="dark"] .card-header-custom,
    html[data-bs-theme="dark"] .card-header-custom,
    body[data-pms-theme="dark"] .card-header-custom,
    [data-pms-theme="dark"] .card-header-custom {
        border-bottom-color: rgba(255, 255, 255, 0.08) !important;
    }

    html[data-pms-theme="dark"] .card-header-custom h5,
    html[data-bs-theme="dark"] .card-header-custom h5,
    body[data-pms-theme="dark"] .card-header-custom h5,
    [data-pms-theme="dark"] .card-header-custom h5,
    html[data-pms-theme="dark"] .header-title h1,
    html[data-bs-theme="dark"] .header-title h1,
    body[data-pms-theme="dark"] .header-title h1,
    [data-pms-theme="dark"] .header-title h1 {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
        background: none !important;
    }

    html[data-pms-theme="dark"] .header-title p,
    html[data-bs-theme="dark"] .header-title p,
    body[data-pms-theme="dark"] .header-title p,
    [data-pms-theme="dark"] .header-title p,
    html[data-pms-theme="dark"] .card-header-custom .text-muted,
    html[data-bs-theme="dark"] .card-header-custom .text-muted,
    body[data-pms-theme="dark"] .card-header-custom .text-muted,
    [data-pms-theme="dark"] .card-header-custom .text-muted {
        color: #94a3b8 !important;
        -webkit-text-fill-color: #94a3b8 !important;
    }

    html[data-pms-theme="dark"] .btn-back-settings,
    html[data-theme="dark"] .btn-back-settings,
    html[data-bs-theme="dark"] .btn-back-settings,
    body[data-pms-theme="dark"] .btn-back-settings,
    body[data-theme="dark"] .btn-back-settings,
    body[data-bs-theme="dark"] .btn-back-settings,
    [data-pms-theme="dark"] .btn-back-settings,
    [data-theme="dark"] .btn-back-settings,
    [data-bs-theme="dark"] .btn-back-settings,
    .dark-mode .btn-back-settings {
        background-color: #1e293b !important;
        background: #1e293b !important;
        border-color: rgba(79, 131, 255, 0.35) !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2) !important;
    }

    html[data-pms-theme="dark"] .btn-back-settings i,
    html[data-theme="dark"] .btn-back-settings i,
    [data-pms-theme="dark"] .btn-back-settings i {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    /* Stat Cards in Dark Mode */
    html[data-pms-theme="dark"] .security-settings-page .stat-card,
    html[data-theme="dark"] .security-settings-page .stat-card,
    html[data-bs-theme="dark"] .security-settings-page .stat-card,
    body[data-pms-theme="dark"] .security-settings-page .stat-card,
    body[data-theme="dark"] .security-settings-page .stat-card,
    body[data-bs-theme="dark"] .security-settings-page .stat-card,
    [data-pms-theme="dark"] .security-settings-page .stat-card,
    [data-theme="dark"] .security-settings-page .stat-card,
    [data-bs-theme="dark"] .security-settings-page .stat-card,
    html[data-pms-theme="dark"] .security-settings-page .stat-card:first-of-type,
    html[data-theme="dark"] .security-settings-page .stat-card:first-of-type,
    html[data-bs-theme="dark"] .security-settings-page .stat-card:first-of-type,
    body[data-pms-theme="dark"] .security-settings-page .stat-card:first-of-type,
    body[data-theme="dark"] .security-settings-page .stat-card:first-of-type,
    body[data-bs-theme="dark"] .security-settings-page .stat-card:first-of-type,
    [data-pms-theme="dark"] .security-settings-page .stat-card:first-of-type,
    [data-theme="dark"] .security-settings-page .stat-card:first-of-type,
    [data-bs-theme="dark"] .security-settings-page .stat-card:first-of-type,
    html[data-pms-theme="dark"] .stat-card,
    html[data-theme="dark"] .stat-card,
    html[data-bs-theme="dark"] .stat-card,
    html[data-pms-theme="dark"] .stat-card:first-of-type,
    html[data-theme="dark"] .stat-card:first-of-type,
    html[data-bs-theme="dark"] .stat-card:first-of-type,
    .dark-mode .stat-card,
    .dark-mode .stat-card:first-of-type {
        background: #171e2e !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.3) !important;
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .security-settings-page .stat-card h3,
    html[data-theme="dark"] .security-settings-page .stat-card h3,
    html[data-bs-theme="dark"] .security-settings-page .stat-card h3,
    body[data-pms-theme="dark"] .security-settings-page .stat-card h3,
    body[data-theme="dark"] .security-settings-page .stat-card h3,
    body[data-bs-theme="dark"] .security-settings-page .stat-card h3,
    [data-pms-theme="dark"] .security-settings-page .stat-card h3,
    [data-theme="dark"] .security-settings-page .stat-card h3,
    [data-bs-theme="dark"] .security-settings-page .stat-card h3,
    html[data-pms-theme="dark"] .security-settings-page .stat-card:first-of-type h3,
    html[data-theme="dark"] .security-settings-page .stat-card:first-of-type h3,
    html[data-bs-theme="dark"] .security-settings-page .stat-card:first-of-type h3,
    body[data-pms-theme="dark"] .security-settings-page .stat-card:first-of-type h3,
    body[data-theme="dark"] .security-settings-page .stat-card:first-of-type h3,
    body[data-bs-theme="dark"] .security-settings-page .stat-card:first-of-type h3,
    [data-pms-theme="dark"] .security-settings-page .stat-card:first-of-type h3,
    [data-theme="dark"] .security-settings-page .stat-card:first-of-type h3,
    [data-bs-theme="dark"] .security-settings-page .stat-card:first-of-type h3,
    .dark-mode .stat-card h3 {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .security-settings-page .stat-card h6,
    html[data-theme="dark"] .security-settings-page .stat-card h6,
    html[data-bs-theme="dark"] .security-settings-page .stat-card h6,
    body[data-pms-theme="dark"] .security-settings-page .stat-card h6,
    body[data-theme="dark"] .security-settings-page .stat-card h6,
    body[data-bs-theme="dark"] .security-settings-page .stat-card h6,
    [data-pms-theme="dark"] .security-settings-page .stat-card h6,
    [data-theme="dark"] .security-settings-page .stat-card h6,
    [data-bs-theme="dark"] .security-settings-page .stat-card h6,
    html[data-pms-theme="dark"] .security-settings-page .stat-card:first-of-type h6,
    html[data-theme="dark"] .security-settings-page .stat-card:first-of-type h6,
    html[data-bs-theme="dark"] .security-settings-page .stat-card:first-of-type h6,
    body[data-pms-theme="dark"] .security-settings-page .stat-card:first-of-type h6,
    body[data-theme="dark"] .security-settings-page .stat-card:first-of-type h6,
    body[data-bs-theme="dark"] .security-settings-page .stat-card:first-of-type h6,
    [data-pms-theme="dark"] .security-settings-page .stat-card:first-of-type h6,
    [data-theme="dark"] .security-settings-page .stat-card:first-of-type h6,
    [data-bs-theme="dark"] .security-settings-page .stat-card:first-of-type h6,
    .dark-mode .stat-card h6 {
        color: #94a3b8 !important;
        -webkit-text-fill-color: #94a3b8 !important;
    }

    /* Dark Mode Stat Icons */
    html[data-pms-theme="dark"] .stat-icon.pass,
    html[data-theme="dark"] .stat-icon.pass,
    html[data-bs-theme="dark"] .stat-icon.pass,
    body[data-pms-theme="dark"] .stat-icon.pass,
    body[data-theme="dark"] .stat-icon.pass,
    body[data-bs-theme="dark"] .stat-icon.pass,
    [data-pms-theme="dark"] .stat-icon.pass,
    [data-theme="dark"] .stat-icon.pass,
    [data-bs-theme="dark"] .stat-icon.pass,
    .dark-mode .stat-icon.pass,
    [data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.pass,
    [data-pms-theme="dark"] .security-settings-page .stat-card:first-of-type .stat-icon.pass,
    [data-theme="dark"] .security-settings-page .stat-card .stat-icon.pass,
    [data-theme="dark"] .security-settings-page .stat-card:first-of-type .stat-icon.pass,
    [data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.pass,
    [data-bs-theme="dark"] .security-settings-page .stat-card:first-of-type .stat-icon.pass,
    html[data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.pass,
    html[data-pms-theme="dark"] .security-settings-page .stat-card:first-of-type .stat-icon.pass,
    html[data-theme="dark"] .security-settings-page .stat-card .stat-icon.pass,
    html[data-theme="dark"] .security-settings-page .stat-card:first-of-type .stat-icon.pass,
    html[data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.pass,
    html[data-bs-theme="dark"] .security-settings-page .stat-card:first-of-type .stat-icon.pass {
        background: rgba(47, 107, 255, 0.25) !important;
        background-image: none !important;
        border: 1px solid rgba(79, 131, 255, 0.4) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.pass i,
    html[data-pms-theme="dark"] .stat-icon.pass svg,
    html[data-pms-theme="dark"] .stat-icon.pass [class*="fa"],
    html[data-theme="dark"] .stat-icon.pass i,
    html[data-theme="dark"] .stat-icon.pass svg,
    html[data-theme="dark"] .stat-icon.pass [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.pass i,
    html[data-bs-theme="dark"] .stat-icon.pass svg,
    html[data-bs-theme="dark"] .stat-icon.pass [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.pass i,
    body[data-theme="dark"] .stat-icon.pass i,
    body[data-bs-theme="dark"] .stat-icon.pass i,
    [data-pms-theme="dark"] .stat-icon.pass i,
    [data-theme="dark"] .stat-icon.pass i,
    [data-bs-theme="dark"] .stat-icon.pass i,
    .dark-mode .stat-icon.pass i,
    .dark-mode .stat-icon.pass svg,
    .dark-mode .stat-icon.pass [class*="fa"],
    html[data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.pass i,
    html[data-pms-theme="dark"] .security-settings-page .stat-card:first-of-type .stat-icon.pass i,
    html[data-theme="dark"] .security-settings-page .stat-card .stat-icon.pass i,
    html[data-theme="dark"] .security-settings-page .stat-card:first-of-type .stat-icon.pass i,
    html[data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.pass i,
    html[data-bs-theme="dark"] .security-settings-page .stat-card:first-of-type .stat-icon.pass i,
    body[data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.pass i,
    body[data-pms-theme="dark"] .security-settings-page .stat-card:first-of-type .stat-icon.pass i,
    body[data-theme="dark"] .security-settings-page .stat-card .stat-icon.pass i,
    body[data-theme="dark"] .security-settings-page .stat-card:first-of-type .stat-icon.pass i,
    body[data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.pass i,
    body[data-bs-theme="dark"] .security-settings-page .stat-card:first-of-type .stat-icon.pass i,
    [data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.pass i,
    [data-pms-theme="dark"] .security-settings-page .stat-card:first-of-type .stat-icon.pass i,
    [data-theme="dark"] .security-settings-page .stat-card .stat-icon.pass i,
    [data-theme="dark"] .security-settings-page .stat-card:first-of-type .stat-icon.pass i,
    [data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.pass i,
    [data-bs-theme="dark"] .security-settings-page .stat-card:first-of-type .stat-icon.pass i,
    .dark-mode .security-settings-page .stat-card .stat-icon.pass i,
    .dark-mode .security-settings-page .stat-card:first-of-type .stat-icon.pass i {
        background: transparent !important;
        background-image: none !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
        fill: #60A5FA !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    html[data-pms-theme="dark"] .stat-icon.session,
    html[data-theme="dark"] .stat-icon.session,
    html[data-bs-theme="dark"] .stat-icon.session,
    body[data-pms-theme="dark"] .stat-icon.session,
    body[data-theme="dark"] .stat-icon.session,
    body[data-bs-theme="dark"] .stat-icon.session,
    [data-pms-theme="dark"] .stat-icon.session,
    [data-theme="dark"] .stat-icon.session,
    [data-bs-theme="dark"] .stat-icon.session,
    .dark-mode .stat-icon.session,
    [data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.session,
    [data-theme="dark"] .security-settings-page .stat-card .stat-icon.session,
    [data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.session,
    html[data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.session,
    html[data-theme="dark"] .security-settings-page .stat-card .stat-icon.session,
    html[data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.session {
        background: rgba(56, 189, 248, 0.25) !important;
        background-image: none !important;
        border: 1px solid rgba(56, 189, 248, 0.4) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.session i,
    html[data-pms-theme="dark"] .stat-icon.session svg,
    html[data-pms-theme="dark"] .stat-icon.session [class*="fa"],
    html[data-theme="dark"] .stat-icon.session i,
    html[data-theme="dark"] .stat-icon.session svg,
    html[data-theme="dark"] .stat-icon.session [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.session i,
    html[data-bs-theme="dark"] .stat-icon.session svg,
    html[data-bs-theme="dark"] .stat-icon.session [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.session i,
    body[data-theme="dark"] .stat-icon.session i,
    body[data-bs-theme="dark"] .stat-icon.session i,
    [data-pms-theme="dark"] .stat-icon.session i,
    [data-theme="dark"] .stat-icon.session i,
    [data-bs-theme="dark"] .stat-icon.session i,
    .dark-mode .stat-icon.session i,
    .dark-mode .stat-icon.session svg,
    .dark-mode .stat-icon.session [class*="fa"],
    html[data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.session i,
    html[data-theme="dark"] .security-settings-page .stat-card .stat-icon.session i,
    html[data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.session i,
    body[data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.session i,
    body[data-theme="dark"] .security-settings-page .stat-card .stat-icon.session i,
    body[data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.session i,
    [data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.session i,
    [data-theme="dark"] .security-settings-page .stat-card .stat-icon.session i,
    [data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.session i,
    .dark-mode .security-settings-page .stat-card .stat-icon.session i {
        background: transparent !important;
        background-image: none !important;
        color: #38bdf8 !important;
        -webkit-text-fill-color: #38bdf8 !important;
        fill: #38bdf8 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    html[data-pms-theme="dark"] .stat-icon.lockout,
    html[data-theme="dark"] .stat-icon.lockout,
    html[data-bs-theme="dark"] .stat-icon.lockout,
    body[data-pms-theme="dark"] .stat-icon.lockout,
    body[data-theme="dark"] .stat-icon.lockout,
    body[data-bs-theme="dark"] .stat-icon.lockout,
    [data-pms-theme="dark"] .stat-icon.lockout,
    [data-theme="dark"] .stat-icon.lockout,
    [data-bs-theme="dark"] .stat-icon.lockout,
    .dark-mode .stat-icon.lockout,
    [data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.lockout,
    [data-theme="dark"] .security-settings-page .stat-card .stat-icon.lockout,
    [data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.lockout,
    html[data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.lockout,
    html[data-theme="dark"] .security-settings-page .stat-card .stat-icon.lockout,
    html[data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.lockout {
        background: rgba(251, 191, 36, 0.25) !important;
        background-image: none !important;
        border: 1px solid rgba(251, 191, 36, 0.4) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.lockout i,
    html[data-pms-theme="dark"] .stat-icon.lockout svg,
    html[data-pms-theme="dark"] .stat-icon.lockout [class*="fa"],
    html[data-theme="dark"] .stat-icon.lockout i,
    html[data-theme="dark"] .stat-icon.lockout svg,
    html[data-theme="dark"] .stat-icon.lockout [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.lockout i,
    html[data-bs-theme="dark"] .stat-icon.lockout svg,
    html[data-bs-theme="dark"] .stat-icon.lockout [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.lockout i,
    body[data-theme="dark"] .stat-icon.lockout i,
    body[data-bs-theme="dark"] .stat-icon.lockout i,
    [data-pms-theme="dark"] .stat-icon.lockout i,
    [data-theme="dark"] .stat-icon.lockout i,
    [data-bs-theme="dark"] .stat-icon.lockout i,
    .dark-mode .stat-icon.lockout i,
    .dark-mode .stat-icon.lockout svg,
    .dark-mode .stat-icon.lockout [class*="fa"],
    html[data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.lockout i,
    html[data-theme="dark"] .security-settings-page .stat-card .stat-icon.lockout i,
    html[data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.lockout i,
    body[data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.lockout i,
    body[data-theme="dark"] .security-settings-page .stat-card .stat-icon.lockout i,
    body[data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.lockout i,
    [data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.lockout i,
    [data-theme="dark"] .security-settings-page .stat-card .stat-icon.lockout i,
    [data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.lockout i,
    .dark-mode .security-settings-page .stat-card .stat-icon.lockout i {
        background: transparent !important;
        background-image: none !important;
        color: #fbbf24 !important;
        -webkit-text-fill-color: #fbbf24 !important;
        fill: #fbbf24 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    html[data-pms-theme="dark"] .stat-icon.twofa,
    html[data-theme="dark"] .stat-icon.twofa,
    html[data-bs-theme="dark"] .stat-icon.twofa,
    body[data-pms-theme="dark"] .stat-icon.twofa,
    body[data-theme="dark"] .stat-icon.twofa,
    body[data-bs-theme="dark"] .stat-icon.twofa,
    [data-pms-theme="dark"] .stat-icon.twofa,
    [data-theme="dark"] .stat-icon.twofa,
    [data-bs-theme="dark"] .stat-icon.twofa,
    .dark-mode .stat-icon.twofa,
    [data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.twofa,
    [data-theme="dark"] .security-settings-page .stat-card .stat-icon.twofa,
    [data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.twofa,
    html[data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.twofa,
    html[data-theme="dark"] .security-settings-page .stat-card .stat-icon.twofa,
    html[data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.twofa {
        background: rgba(129, 140, 248, 0.25) !important;
        background-image: none !important;
        border: 1px solid rgba(129, 140, 248, 0.4) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.twofa i,
    html[data-pms-theme="dark"] .stat-icon.twofa svg,
    html[data-pms-theme="dark"] .stat-icon.twofa [class*="fa"],
    html[data-theme="dark"] .stat-icon.twofa i,
    html[data-theme="dark"] .stat-icon.twofa svg,
    html[data-theme="dark"] .stat-icon.twofa [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.twofa i,
    html[data-bs-theme="dark"] .stat-icon.twofa svg,
    html[data-bs-theme="dark"] .stat-icon.twofa [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.twofa i,
    body[data-theme="dark"] .stat-icon.twofa i,
    body[data-bs-theme="dark"] .stat-icon.twofa i,
    [data-pms-theme="dark"] .stat-icon.twofa i,
    [data-theme="dark"] .stat-icon.twofa i,
    [data-bs-theme="dark"] .stat-icon.twofa i,
    .dark-mode .stat-icon.twofa i,
    .dark-mode .stat-icon.twofa svg,
    .dark-mode .stat-icon.twofa [class*="fa"],
    html[data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.twofa i,
    html[data-theme="dark"] .security-settings-page .stat-card .stat-icon.twofa i,
    html[data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.twofa i,
    body[data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.twofa i,
    body[data-theme="dark"] .security-settings-page .stat-card .stat-icon.twofa i,
    body[data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.twofa i,
    [data-pms-theme="dark"] .security-settings-page .stat-card .stat-icon.twofa i,
    [data-theme="dark"] .security-settings-page .stat-card .stat-icon.twofa i,
    [data-bs-theme="dark"] .security-settings-page .stat-card .stat-icon.twofa i,
    .dark-mode .security-settings-page .stat-card .stat-icon.twofa i {
        background: transparent !important;
        background-image: none !important;
        color: #a5b4fc !important;
        -webkit-text-fill-color: #a5b4fc !important;
        fill: #a5b4fc !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    /* Form Controls & Switches in Dark Mode */
    html[data-pms-theme="dark"] .input-group-custom,
    html[data-theme="dark"] .input-group-custom,
    html[data-bs-theme="dark"] .input-group-custom,
    [data-pms-theme="dark"] .input-group-custom {
        background-color: #1a2234 !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
    }

    html[data-pms-theme="dark"] .input-group-custom .form-control,
    html[data-theme="dark"] .input-group-custom .form-control,
    [data-pms-theme="dark"] .input-group-custom .form-control {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .form-label-custom,
    html[data-theme="dark"] .form-label-custom,
    [data-pms-theme="dark"] .form-label-custom {
        color: #e2e8f0 !important;
        -webkit-text-fill-color: #e2e8f0 !important;
    }

    html[data-pms-theme="dark"] .section-badge,
    html[data-bs-theme="dark"] .section-badge,
    body[data-pms-theme="dark"] .section-badge,
    [data-pms-theme="dark"] .section-badge {
        background: rgba(47, 107, 255, 0.15) !important;
        color: #60A5FA !important;
        border-color: rgba(79, 131, 255, 0.3) !important;
    }

    html[data-pms-theme="dark"] .section-badge i,
    html[data-theme="dark"] .section-badge i,
    html[data-bs-theme="dark"] .section-badge i,
    [data-pms-theme="dark"] .section-badge i {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .policy-switch-box,
    html[data-theme="dark"] .policy-switch-box,
    [data-pms-theme="dark"] .policy-switch-box {
        background: #171e2e !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
    }

    html[data-pms-theme="dark"] .policy-switch-box label,
    html[data-theme="dark"] .policy-switch-box label,
    [data-pms-theme="dark"] .policy-switch-box label,
    html[data-pms-theme="dark"] .policy-switch-box .form-check-label,
    html[data-theme="dark"] .policy-switch-box .form-check-label,
    [data-pms-theme="dark"] .policy-switch-box .form-check-label {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }
</style>
@endpush

@section('content')
<div class="security-settings-page">
    <div class="security-settings-shell">
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
                <span>Security & Login Controls</span>
            </div>

            <!-- Page Header Card -->
            <div class="branches-header">
                <div class="header-left-box">
                    <div class="header-icon-badge">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <div class="header-title">
                        <h1>Security & Login Controls</h1>
                        <p>Configure password complexity policies, session timeout limits, two-factor authentication, and account lockouts.</p>
                    </div>
                </div>

                <a href="{{ route('admin.settings.index') }}" class="btn-back-settings">
                    <i class="fas fa-arrow-left me-1 back-arrow-icon"></i> Back to Settings
                </a>
            </div>

            <!-- Alert Notifications -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm rounded-4 border-0" style="background: rgba(220, 252, 231, 0.95); color: #065f46; border-left: 5px solid #10b981 !important;" role="alert">
                    <i class="fas fa-check-circle fs-4 me-2"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Executive Summary Stats Grid -->
            @php
                $minPass = $settings['min_password_length'] ?? '8';
                $sessionMins = $settings['session_timeout_mins'] ?? '120';
                $maxAttempts = $settings['max_login_attempts'] ?? '5';
                $lockoutMins = $settings['lockout_duration_mins'] ?? '15';
                $reqSpec = ($settings['require_special_char'] ?? '1') == '1';
                $reqNum = ($settings['require_numbers'] ?? '1') == '1';
                $reqUpper = ($settings['require_uppercase'] ?? '1') == '1';
                $reqLower = ($settings['require_lowercase'] ?? '1') == '1';
                $enable2fa = ($settings['enable_2fa'] ?? '0') == '1';
            @endphp
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon pass">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Password Length</h6>
                        <h3>Min {{ $minPass }} Characters</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon session">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Session Timeout</h6>
                        <h3>{{ $sessionMins }} Mins ({{ round($sessionMins / 60, 1) }}h)</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon lockout">
                        <i class="fas fa-user-lock"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Account Lockout</h6>
                        <h3>{{ $maxAttempts }} Attempts / {{ $lockoutMins }}m</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon twofa">
                        <i class="fas fa-shield-virus"></i>
                    </div>
                    <div class="stat-info">
                        <h6>2FA Verification</h6>
                        <h3>{{ $enable2fa ? 'Enforced' : 'Optional' }}</h3>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="address-card-elevated">
                <div class="card-header-custom">
                    <div class="card-header-avatar shadow-sm">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold fs-5" style="color: #0F172A;">Password Policies & Authentication Parameters</h5>
                        <small class="text-muted">Enforce robust login security rules, password complexity, and 2FA across all user accounts</small>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    @if($isSettingsReadOnly)
                        <div class="alert d-flex align-items-center mb-4 rounded-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #1e40af; border-left: 4px solid #3b82f6 !important; padding: 14px 18px;">
                            <i class="fas fa-eye me-3 fs-3 text-primary"></i>
                            <div>
                                <strong class="d-block text-primary fw-bold" style="font-size: 14px;">View-Only Mode</strong>
                                <span style="font-size: 13px; color: #1e3a8a;">You are viewing security policies in read-only mode. Only Administrators have permission to modify authentication rules, password requirements, and 2FA policies.</span>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.settings.security.update') }}">
                        @csrf

                        <!-- Section 1: Password & Session Rules -->
                        <div class="section-badge">
                            <i class="fas fa-key"></i> Password & Session Timeout Rules
                        </div>

                        <div class="row g-4 mb-5">
                            <!-- Min Password Length -->
                            <div class="col-md-4">
                                <label class="form-label-custom">Minimum Password Length <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="number" name="min_password_length" class="form-control" min="6" max="32"
                                        value="{{ old('min_password_length', $settings['min_password_length'] ?? '8') }}"
                                        {{ $isSettingsReadOnly ? 'readonly' : 'required' }}>
                                </div>
                                <small class="text-muted mt-1 d-block">Recommended: 8+ characters.</small>
                            </div>

                            <!-- Session Inactivity Timeout -->
                            <div class="col-md-4">
                                <label class="form-label-custom">Session Timeout (Minutes) <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-stopwatch"></i></span>
                                    <input type="number" name="session_timeout_mins" class="form-control" min="5" max="1440"
                                        value="{{ old('session_timeout_mins', $settings['session_timeout_mins'] ?? '120') }}"
                                        {{ $isSettingsReadOnly ? 'readonly' : 'required' }}>
                                </div>
                                <small class="text-muted mt-1 d-block">Automatic logout after inactivity (e.g. 120 mins).</small>
                            </div>

                            <!-- Max Failed Login Attempts -->
                            <div class="col-md-4">
                                <label class="form-label-custom">Max Failed Login Attempts <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-ban"></i></span>
                                    <input type="number" name="max_login_attempts" class="form-control" min="3" max="10"
                                        value="{{ old('max_login_attempts', $settings['max_login_attempts'] ?? '5') }}"
                                        {{ $isSettingsReadOnly ? 'readonly' : 'required' }}>
                                </div>
                                <small class="text-muted mt-1 d-block">Max failed passwords before account lock.</small>
                            </div>
                        </div>

                        <!-- Section 2: Account Lockout & Password Complexity -->
                        <div class="section-badge">
                            <i class="fas fa-user-shield"></i> Lockout Duration & Password Complexity
                        </div>

                        <div class="row g-4 mb-5">
                            <!-- Account Lockout Duration -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Account Lockout Duration (Minutes) <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-clock-rotate-left"></i></span>
                                    <input type="number" name="lockout_duration_mins" class="form-control" min="1" max="1440"
                                        value="{{ old('lockout_duration_mins', $settings['lockout_duration_mins'] ?? '15') }}"
                                        {{ $isSettingsReadOnly ? 'readonly' : 'required' }}>
                                </div>
                                <small class="text-muted mt-1 d-block">Time an account stays locked after max failed attempts.</small>
                            </div>

                            <!-- Password Complexity Box -->
                            <div class="col-md-6">
                                <div class="policy-switch-box">
                                    <label class="form-label-custom mb-3" style="color: #0F172A;"><i class="fas fa-shield-cat me-1.5" style="color: #2F6BFF;"></i>Password Complexity Requirements</label>
                                    
                                    <div class="form-check form-switch mb-2.5 d-flex align-items-center gap-3">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="require_uppercase" value="1" id="uppercaseSwitch"
                                            style="width: 2.5em; height: 1.3em; cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }};"
                                            {{ $reqUpper ? 'checked' : '' }} {{ $isSettingsReadOnly ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="uppercaseSwitch" style="cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }}; font-size: 0.88rem;">
                                            Atleast one Capital Letter (A-Z)
                                        </label>
                                    </div>

                                    <div class="form-check form-switch mb-2.5 d-flex align-items-center gap-3">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="require_lowercase" value="1" id="lowercaseSwitch"
                                            style="width: 2.5em; height: 1.3em; cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }};"
                                            {{ $reqLower ? 'checked' : '' }} {{ $isSettingsReadOnly ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="lowercaseSwitch" style="cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }}; font-size: 0.88rem;">
                                            Atleast one small Letter (a-z)
                                        </label>
                                    </div>

                                    <div class="form-check form-switch mb-2.5 d-flex align-items-center gap-3">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="require_numbers" value="1" id="numbersSwitch"
                                            style="width: 2.5em; height: 1.3em; cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }};"
                                            {{ $reqNum ? 'checked' : '' }} {{ $isSettingsReadOnly ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="numbersSwitch" style="cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }}; font-size: 0.88rem;">
                                            Require numeric digits (0-9)
                                        </label>
                                    </div>

                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-3">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="require_special_char" value="1" id="specCharSwitch"
                                            style="width: 2.5em; height: 1.3em; cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }};"
                                            {{ $reqSpec ? 'checked' : '' }} {{ $isSettingsReadOnly ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="specCharSwitch" style="cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }}; font-size: 0.88rem;">
                                            Require at least 1 special character (!@#$%^&*)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: 2FA Enforcement -->
                        <div class="section-badge">
                            <i class="fas fa-mobile-retro"></i> Multi-Factor Authentication (2FA)
                        </div>

                        <div class="row g-4">
                            <div class="col-12">
                                <div class="policy-switch-box" style="padding: 1.5rem 1.8rem;">
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-3 w-100">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="enable_2fa" value="1" id="twoFactorSwitch"
                                            style="width: 2.8em; height: 1.4em; cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }};"
                                            {{ $enable2fa ? 'checked' : '' }} {{ $isSettingsReadOnly ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="twoFactorSwitch" style="cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }};">
                                            Enforce Two-Factor Authentication (2FA) for Admin & Management Roles
                                            <small class="d-block text-muted fw-normal mt-0.5" style="font-size: 0.85rem;">Requires an OTP verification code during user login.</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="mt-5 pt-4 border-top d-flex justify-content-end">
                            @if($isSettingsReadOnly)
                                <span class="badge rounded-pill px-3.5 py-2 fw-bold" style="background: #f1f5f9; color: #64748b; font-size: 13px; border: 1px solid #cbd5e1;">
                                    <i class="fas fa-lock me-1.5 text-muted"></i> Read-Only (Admin Managed)
                                </span>
                            @else
                                <button type="submit" class="btn-save-address">
                                    <i class="fas fa-save me-1.5"></i> Save Security Settings
                                </button>
                            @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
