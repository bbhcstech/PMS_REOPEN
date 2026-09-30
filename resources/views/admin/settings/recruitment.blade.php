@extends('admin.layout.app')

@section('title', 'Recruitment Settings')

@push('styles')
<style>
    .recruitment-settings-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #F8FAFC 0%, #EEF2FF 50%, #F8FAFC 100%);
        color: #0F172A;
    }

    .recruitment-settings-shell {
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
        color: white;
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
        opacity: 1 !important;
        visibility: visible !important;
    }

    .header-title h1 {
        font-size: 1.95rem;
        font-weight: 800;
        background: linear-gradient(135deg, #0F172A, #2F6BFF, #10b981);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        margin: 0 0 0.2rem 0;
        letter-spacing: -0.03em;
    }

    .header-title p {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
        margin: 0;
    }

    .btn-back-settings {
        background-color: #ffffff;
        border: 1px solid rgba(47, 107, 255, 0.25);
        color: #2F6BFF !important;
        font-weight: 700;
        font-size: 0.9rem;
        border-radius: 40px;
        padding: 0.65rem 1.4rem;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-back-settings:hover {
        background-color: #EEF2FF;
        color: #2F6BFF !important;
        border-color: rgba(47, 107, 255, 0.4);
        transform: translateY(-2px);
    }

    .btn-back-settings:hover .back-arrow-icon {
        transform: translateX(-4px);
    }

    .back-arrow-icon {
        transition: transform 0.25s ease;
        display: inline-block;
    }

    /* ===== STATS GRID ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .stat-card,
    .recruitment-settings-page .stat-card,
    .recruitment-settings-page .stat-card:first-of-type {
        background: #ffffff !important;
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 1.5rem;
        border: 1px solid rgba(47, 107, 255, 0.14) !important;
        box-shadow: 0 10px 30px -10px rgba(47, 107, 255, 0.08) !important;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        color: #0F172A !important;
    }

    .recruitment-settings-page .stat-card:first-of-type *,
    .recruitment-settings-page .stat-card * {
        -webkit-text-fill-color: initial;
    }

    .recruitment-settings-page .stat-card h3,
    .recruitment-settings-page .stat-card:first-of-type h3 {
        color: #0F172A !important;
        -webkit-text-fill-color: #0F172A !important;
    }

    .recruitment-settings-page .stat-card h6,
    .recruitment-settings-page .stat-card span,
    .recruitment-settings-page .stat-card:first-of-type span,
    .recruitment-settings-page .stat-card:first-of-type h6 {
        color: #64748b !important;
        -webkit-text-fill-color: #64748b !important;
    }

    .stat-card::after {
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

    .stat-card:hover::after {
        transform: scaleX(1);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 35px -12px rgba(47, 107, 255, 0.15) !important;
        border-color: rgba(47, 107, 255, 0.25) !important;
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

    .stat-icon.categories {
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF) !important;
        border: 1px solid rgba(47, 107, 255, 0.25) !important;
    }

    .stat-icon.categories i,
    .stat-icon.categories svg,
    .stat-icon.categories [class*="fa"],
    .recruitment-settings-page .stat-card .stat-icon.categories i,
    .recruitment-settings-page .stat-card:first-of-type .stat-icon.categories i {
        background: transparent !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .stat-icon.stages {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        border: 1px solid rgba(56, 189, 248, 0.25) !important;
    }

    .stat-icon.stages i,
    .stat-icon.stages svg,
    .stat-icon.stages [class*="fa"],
    .recruitment-settings-page .stat-card .stat-icon.stages i {
        background: transparent !important;
        color: #0369a1 !important;
        -webkit-text-fill-color: #0369a1 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .stat-icon.resumesize {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        border: 1px solid rgba(251, 191, 36, 0.25) !important;
    }

    .stat-icon.resumesize i,
    .stat-icon.resumesize svg,
    .stat-icon.resumesize [class*="fa"],
    .recruitment-settings-page .stat-card .stat-icon.resumesize i {
        background: transparent !important;
        color: #b45309 !important;
        -webkit-text-fill-color: #b45309 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .stat-icon.autoreply {
        background: linear-gradient(145deg, #e0e7ff, #c7d2fe) !important;
        border: 1px solid rgba(129, 140, 248, 0.25) !important;
    }

    .stat-icon.autoreply i,
    .stat-icon.autoreply svg,
    .stat-icon.autoreply [class*="fa"],
    .recruitment-settings-page .stat-card .stat-icon.autoreply i {
        background: transparent !important;
        color: #3730a3 !important;
        -webkit-text-fill-color: #3730a3 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .stat-icon.sla {
        background: linear-gradient(145deg, #dcfce7, #bbf7d0) !important;
        border: 1px solid rgba(74, 222, 128, 0.25) !important;
    }

    .stat-icon.sla i,
    .stat-icon.sla svg,
    .stat-icon.sla [class*="fa"],
    .recruitment-settings-page .stat-card .stat-icon.sla i {
        background: transparent !important;
        color: #15803d !important;
        -webkit-text-fill-color: #15803d !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .stat-icon.probation {
        background: linear-gradient(145deg, #f3e8ff, #e9d5ff) !important;
        border: 1px solid rgba(192, 132, 252, 0.25) !important;
    }

    .stat-icon.probation i,
    .stat-icon.probation svg,
    .stat-icon.probation [class*="fa"],
    .recruitment-settings-page .stat-card .stat-icon.probation i {
        background: transparent !important;
        color: #7e22ce !important;
        -webkit-text-fill-color: #7e22ce !important;
        opacity: 1 !important;
        visibility: visible !important;
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
        opacity: 1 !important;
        visibility: visible !important;
    }

    .card-header-avatar i.text-white,
    .card-header-avatar .text-white,
    .card-header-avatar[style*="background"] i,
    .card-header-avatar[style*="background"] svg,
    .card-header-avatar[style*="background"] [class*="fa"] {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
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

    .section-badge i,
    .section-badge svg,
    .section-badge [class*="fa"] {
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        opacity: 1 !important;
        visibility: visible !important;
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

    .textarea-custom {
        border-radius: 16px;
        border: 1px solid rgba(47, 107, 255, 0.2);
        background-color: #fafefb;
        padding: 14px 18px;
        font-size: 0.92rem;
        font-weight: 600;
        color: #0F172A;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        width: 100%;
        resize: vertical;
    }

    .textarea-custom:focus {
        outline: none;
        border-color: #60A5FA;
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(79, 131, 255, 0.15);
        transform: translateY(-1px);
    }

    /* Policy Switch Box */
    .policy-switch-box {
        background: #f0fdf4;
        border: 1px solid rgba(47, 107, 255, 0.25);
        border-radius: 20px;
        padding: 1.2rem 1.5rem;
        transition: all 0.25s ease;
        height: 100%;
        display: flex;
        align-items: center;
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

    /* ===== DARK MODE SUPPORT ===== */
    html[data-pms-theme="dark"] .recruitment-settings-page,
    html[data-theme="dark"] .recruitment-settings-page,
    html[data-bs-theme="dark"] .recruitment-settings-page,
    body[data-pms-theme="dark"] .recruitment-settings-page,
    body[data-theme="dark"] .recruitment-settings-page,
    body[data-bs-theme="dark"] .recruitment-settings-page,
    [data-pms-theme="dark"] .recruitment-settings-page,
    [data-theme="dark"] .recruitment-settings-page,
    [data-bs-theme="dark"] .recruitment-settings-page,
    .dark-mode .recruitment-settings-page {
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

    /* Stat Cards in Dark Mode */
    html[data-pms-theme="dark"] .recruitment-settings-page .stat-card,
    html[data-theme="dark"] .recruitment-settings-page .stat-card,
    html[data-bs-theme="dark"] .recruitment-settings-page .stat-card,
    body[data-pms-theme="dark"] .recruitment-settings-page .stat-card,
    body[data-theme="dark"] .recruitment-settings-page .stat-card,
    body[data-bs-theme="dark"] .recruitment-settings-page .stat-card,
    [data-pms-theme="dark"] .recruitment-settings-page .stat-card,
    [data-theme="dark"] .recruitment-settings-page .stat-card,
    [data-bs-theme="dark"] .recruitment-settings-page .stat-card,
    html[data-pms-theme="dark"] .recruitment-settings-page .stat-card:first-of-type,
    html[data-theme="dark"] .recruitment-settings-page .stat-card:first-of-type,
    html[data-bs-theme="dark"] .recruitment-settings-page .stat-card:first-of-type,
    body[data-pms-theme="dark"] .recruitment-settings-page .stat-card:first-of-type,
    body[data-theme="dark"] .recruitment-settings-page .stat-card:first-of-type,
    body[data-bs-theme="dark"] .recruitment-settings-page .stat-card:first-of-type,
    [data-pms-theme="dark"] .recruitment-settings-page .stat-card:first-of-type,
    [data-theme="dark"] .recruitment-settings-page .stat-card:first-of-type,
    [data-bs-theme="dark"] .recruitment-settings-page .stat-card:first-of-type,
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

    html[data-pms-theme="dark"] .recruitment-settings-page .stat-card h3,
    html[data-theme="dark"] .recruitment-settings-page .stat-card h3,
    html[data-bs-theme="dark"] .recruitment-settings-page .stat-card h3,
    body[data-pms-theme="dark"] .recruitment-settings-page .stat-card h3,
    body[data-theme="dark"] .recruitment-settings-page .stat-card h3,
    body[data-bs-theme="dark"] .recruitment-settings-page .stat-card h3,
    [data-pms-theme="dark"] .recruitment-settings-page .stat-card h3,
    [data-theme="dark"] .recruitment-settings-page .stat-card h3,
    [data-bs-theme="dark"] .recruitment-settings-page .stat-card h3,
    html[data-pms-theme="dark"] .recruitment-settings-page .stat-card:first-of-type h3,
    html[data-theme="dark"] .recruitment-settings-page .stat-card:first-of-type h3,
    html[data-bs-theme="dark"] .recruitment-settings-page .stat-card:first-of-type h3,
    body[data-pms-theme="dark"] .recruitment-settings-page .stat-card:first-of-type h3,
    body[data-theme="dark"] .recruitment-settings-page .stat-card:first-of-type h3,
    body[data-bs-theme="dark"] .recruitment-settings-page .stat-card:first-of-type h3,
    [data-pms-theme="dark"] .recruitment-settings-page .stat-card:first-of-type h3,
    [data-theme="dark"] .recruitment-settings-page .stat-card:first-of-type h3,
    [data-bs-theme="dark"] .recruitment-settings-page .stat-card:first-of-type h3,
    .dark-mode .stat-card h3 {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .recruitment-settings-page .stat-card h6,
    html[data-theme="dark"] .recruitment-settings-page .stat-card h6,
    html[data-bs-theme="dark"] .recruitment-settings-page .stat-card h6,
    body[data-pms-theme="dark"] .recruitment-settings-page .stat-card h6,
    body[data-theme="dark"] .recruitment-settings-page .stat-card h6,
    body[data-bs-theme="dark"] .recruitment-settings-page .stat-card h6,
    [data-pms-theme="dark"] .recruitment-settings-page .stat-card h6,
    [data-theme="dark"] .recruitment-settings-page .stat-card h6,
    [data-bs-theme="dark"] .recruitment-settings-page .stat-card h6,
    html[data-pms-theme="dark"] .recruitment-settings-page .stat-card:first-of-type h6,
    html[data-theme="dark"] .recruitment-settings-page .stat-card:first-of-type h6,
    html[data-bs-theme="dark"] .recruitment-settings-page .stat-card:first-of-type h6,
    body[data-pms-theme="dark"] .recruitment-settings-page .stat-card:first-of-type h6,
    body[data-theme="dark"] .recruitment-settings-page .stat-card:first-of-type h6,
    body[data-bs-theme="dark"] .recruitment-settings-page .stat-card:first-of-type h6,
    [data-pms-theme="dark"] .recruitment-settings-page .stat-card:first-of-type h6,
    [data-theme="dark"] .recruitment-settings-page .stat-card:first-of-type h6,
    [data-bs-theme="dark"] .recruitment-settings-page .stat-card:first-of-type h6,
    .dark-mode .stat-card h6 {
        color: #94a3b8 !important;
        -webkit-text-fill-color: #94a3b8 !important;
    }

    /* Stat Card Icons in Dark Mode */
    html[data-pms-theme="dark"] .stat-icon.categories,
    html[data-theme="dark"] .stat-icon.categories,
    html[data-bs-theme="dark"] .stat-icon.categories,
    body[data-pms-theme="dark"] .stat-icon.categories,
    body[data-theme="dark"] .stat-icon.categories,
    body[data-bs-theme="dark"] .stat-icon.categories,
    [data-pms-theme="dark"] .stat-icon.categories,
    [data-theme="dark"] .stat-icon.categories,
    [data-bs-theme="dark"] .stat-icon.categories,
    .dark-mode .stat-icon.categories {
        background: rgba(47, 107, 255, 0.25) !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.categories i,
    html[data-pms-theme="dark"] .stat-icon.categories svg,
    html[data-pms-theme="dark"] .stat-icon.categories [class*="fa"],
    html[data-theme="dark"] .stat-icon.categories i,
    html[data-theme="dark"] .stat-icon.categories svg,
    html[data-theme="dark"] .stat-icon.categories [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.categories i,
    html[data-bs-theme="dark"] .stat-icon.categories svg,
    html[data-bs-theme="dark"] .stat-icon.categories [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.categories i,
    body[data-theme="dark"] .stat-icon.categories i,
    body[data-bs-theme="dark"] .stat-icon.categories i,
    [data-pms-theme="dark"] .stat-icon.categories i,
    [data-theme="dark"] .stat-icon.categories i,
    [data-bs-theme="dark"] .stat-icon.categories i,
    .dark-mode .stat-icon.categories i,
    .dark-mode .stat-icon.categories svg,
    .dark-mode .stat-icon.categories [class*="fa"],
    [data-pms-theme="dark"] .recruitment-settings-page .stat-card .stat-icon.categories i,
    [data-pms-theme="dark"] .recruitment-settings-page .stat-card:first-of-type .stat-icon.categories i,
    [data-theme="dark"] .recruitment-settings-page .stat-card .stat-icon.categories i,
    [data-theme="dark"] .recruitment-settings-page .stat-card:first-of-type .stat-icon.categories i,
    [data-bs-theme="dark"] .recruitment-settings-page .stat-card .stat-icon.categories i,
    [data-bs-theme="dark"] .recruitment-settings-page .stat-card:first-of-type .stat-icon.categories i,
    .dark-mode .recruitment-settings-page .stat-card .stat-icon.categories i,
    .dark-mode .recruitment-settings-page .stat-card:first-of-type .stat-icon.categories i {
        background: transparent !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    html[data-pms-theme="dark"] .stat-icon.stages,
    html[data-theme="dark"] .stat-icon.stages,
    html[data-bs-theme="dark"] .stat-icon.stages,
    body[data-pms-theme="dark"] .stat-icon.stages,
    body[data-theme="dark"] .stat-icon.stages,
    body[data-bs-theme="dark"] .stat-icon.stages,
    [data-pms-theme="dark"] .stat-icon.stages,
    [data-theme="dark"] .stat-icon.stages,
    [data-bs-theme="dark"] .stat-icon.stages,
    .dark-mode .stat-icon.stages {
        background: rgba(56, 189, 248, 0.25) !important;
        border: 1px solid rgba(56, 189, 248, 0.35) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.stages i,
    html[data-pms-theme="dark"] .stat-icon.stages svg,
    html[data-pms-theme="dark"] .stat-icon.stages [class*="fa"],
    html[data-theme="dark"] .stat-icon.stages i,
    html[data-theme="dark"] .stat-icon.stages svg,
    html[data-theme="dark"] .stat-icon.stages [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.stages i,
    html[data-bs-theme="dark"] .stat-icon.stages svg,
    html[data-bs-theme="dark"] .stat-icon.stages [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.stages i,
    body[data-theme="dark"] .stat-icon.stages i,
    body[data-bs-theme="dark"] .stat-icon.stages i,
    [data-pms-theme="dark"] .stat-icon.stages i,
    [data-theme="dark"] .stat-icon.stages i,
    [data-bs-theme="dark"] .stat-icon.stages i,
    .dark-mode .stat-icon.stages i,
    .dark-mode .stat-icon.stages svg,
    .dark-mode .stat-icon.stages [class*="fa"],
    [data-pms-theme="dark"] .recruitment-settings-page .stat-card .stat-icon.stages i,
    [data-theme="dark"] .recruitment-settings-page .stat-card .stat-icon.stages i,
    [data-bs-theme="dark"] .recruitment-settings-page .stat-card .stat-icon.stages i,
    .dark-mode .recruitment-settings-page .stat-card .stat-icon.stages i {
        background: transparent !important;
        color: #38bdf8 !important;
        -webkit-text-fill-color: #38bdf8 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    html[data-pms-theme="dark"] .stat-icon.resumesize,
    html[data-theme="dark"] .stat-icon.resumesize,
    html[data-bs-theme="dark"] .stat-icon.resumesize,
    body[data-pms-theme="dark"] .stat-icon.resumesize,
    body[data-theme="dark"] .stat-icon.resumesize,
    body[data-bs-theme="dark"] .stat-icon.resumesize,
    [data-pms-theme="dark"] .stat-icon.resumesize,
    [data-theme="dark"] .stat-icon.resumesize,
    [data-bs-theme="dark"] .stat-icon.resumesize,
    .dark-mode .stat-icon.resumesize {
        background: rgba(251, 191, 36, 0.25) !important;
        border: 1px solid rgba(251, 191, 36, 0.35) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.resumesize i,
    html[data-pms-theme="dark"] .stat-icon.resumesize svg,
    html[data-pms-theme="dark"] .stat-icon.resumesize [class*="fa"],
    html[data-theme="dark"] .stat-icon.resumesize i,
    html[data-theme="dark"] .stat-icon.resumesize svg,
    html[data-theme="dark"] .stat-icon.resumesize [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.resumesize i,
    html[data-bs-theme="dark"] .stat-icon.resumesize svg,
    html[data-bs-theme="dark"] .stat-icon.resumesize [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.resumesize i,
    body[data-theme="dark"] .stat-icon.resumesize i,
    body[data-bs-theme="dark"] .stat-icon.resumesize i,
    [data-pms-theme="dark"] .stat-icon.resumesize i,
    [data-theme="dark"] .stat-icon.resumesize i,
    [data-bs-theme="dark"] .stat-icon.resumesize i,
    .dark-mode .stat-icon.resumesize i,
    .dark-mode .stat-icon.resumesize svg,
    .dark-mode .stat-icon.resumesize [class*="fa"],
    [data-pms-theme="dark"] .recruitment-settings-page .stat-card .stat-icon.resumesize i,
    [data-theme="dark"] .recruitment-settings-page .stat-card .stat-icon.resumesize i,
    [data-bs-theme="dark"] .recruitment-settings-page .stat-card .stat-icon.resumesize i,
    .dark-mode .recruitment-settings-page .stat-card .stat-icon.resumesize i {
        background: transparent !important;
        color: #fbbf24 !important;
        -webkit-text-fill-color: #fbbf24 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    html[data-pms-theme="dark"] .stat-icon.autoreply,
    html[data-theme="dark"] .stat-icon.autoreply,
    html[data-bs-theme="dark"] .stat-icon.autoreply,
    body[data-pms-theme="dark"] .stat-icon.autoreply,
    body[data-theme="dark"] .stat-icon.autoreply,
    body[data-bs-theme="dark"] .stat-icon.autoreply,
    [data-pms-theme="dark"] .stat-icon.autoreply,
    [data-theme="dark"] .stat-icon.autoreply,
    [data-bs-theme="dark"] .stat-icon.autoreply,
    .dark-mode .stat-icon.autoreply {
        background: rgba(129, 140, 248, 0.25) !important;
        border: 1px solid rgba(129, 140, 248, 0.35) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.autoreply i,
    html[data-pms-theme="dark"] .stat-icon.autoreply svg,
    html[data-pms-theme="dark"] .stat-icon.autoreply [class*="fa"],
    html[data-theme="dark"] .stat-icon.autoreply i,
    html[data-theme="dark"] .stat-icon.autoreply svg,
    html[data-theme="dark"] .stat-icon.autoreply [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.autoreply i,
    html[data-bs-theme="dark"] .stat-icon.autoreply svg,
    html[data-bs-theme="dark"] .stat-icon.autoreply [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.autoreply i,
    body[data-theme="dark"] .stat-icon.autoreply i,
    body[data-bs-theme="dark"] .stat-icon.autoreply i,
    [data-pms-theme="dark"] .stat-icon.autoreply i,
    [data-theme="dark"] .stat-icon.autoreply i,
    [data-bs-theme="dark"] .stat-icon.autoreply i,
    .dark-mode .stat-icon.autoreply i,
    .dark-mode .stat-icon.autoreply svg,
    .dark-mode .stat-icon.autoreply [class*="fa"],
    [data-pms-theme="dark"] .recruitment-settings-page .stat-card .stat-icon.autoreply i,
    [data-theme="dark"] .recruitment-settings-page .stat-card .stat-icon.autoreply i,
    [data-bs-theme="dark"] .recruitment-settings-page .stat-card .stat-icon.autoreply i,
    .dark-mode .recruitment-settings-page .stat-card .stat-icon.autoreply i {
        background: transparent !important;
        color: #a5b4fc !important;
        -webkit-text-fill-color: #a5b4fc !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    /* Section Badge & Form Controls in Dark Mode */
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

    html[data-pms-theme="dark"] .card-header-avatar:not([style*="background"]),
    html[data-theme="dark"] .card-header-avatar:not([style*="background"]),
    html[data-bs-theme="dark"] .card-header-avatar:not([style*="background"]),
    [data-pms-theme="dark"] .card-header-avatar:not([style*="background"]) {
        background: rgba(47, 107, 255, 0.2) !important;
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .card-header-avatar:not([style*="background"]) i,
    html[data-theme="dark"] .card-header-avatar:not([style*="background"]) i,
    html[data-bs-theme="dark"] .card-header-avatar:not([style*="background"]) i,
    [data-pms-theme="dark"] .card-header-avatar:not([style*="background"]) i {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .form-label-custom,
    html[data-bs-theme="dark"] .form-label-custom,
    body[data-pms-theme="dark"] .form-label-custom,
    [data-pms-theme="dark"] .form-label-custom {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .input-group-custom,
    html[data-bs-theme="dark"] .input-group-custom,
    body[data-pms-theme="dark"] .input-group-custom,
    [data-pms-theme="dark"] .input-group-custom,
    html[data-pms-theme="dark"] .textarea-custom,
    html[data-bs-theme="dark"] .textarea-custom,
    body[data-pms-theme="dark"] .textarea-custom,
    [data-pms-theme="dark"] .textarea-custom,
    html[data-pms-theme="dark"] .policy-switch-box,
    html[data-bs-theme="dark"] .policy-switch-box,
    body[data-pms-theme="dark"] .policy-switch-box,
    [data-pms-theme="dark"] .policy-switch-box {
        background-color: #1e293b !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .input-group-custom .form-control,
    html[data-bs-theme="dark"] .input-group-custom .form-control,
    body[data-pms-theme="dark"] .input-group-custom .form-control,
    [data-pms-theme="dark"] .input-group-custom .form-control,
    html[data-pms-theme="dark"] .input-group-custom .form-select,
    html[data-bs-theme="dark"] .input-group-custom .form-select,
    body[data-pms-theme="dark"] .input-group-custom .form-select,
    [data-pms-theme="dark"] .input-group-custom .form-select,
    html[data-pms-theme="dark"] .textarea-custom,
    html[data-bs-theme="dark"] .textarea-custom,
    body[data-pms-theme="dark"] .textarea-custom,
    [data-pms-theme="dark"] .textarea-custom {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
        background-color: transparent !important;
    }

    html[data-pms-theme="dark"] .policy-switch-box label,
    html[data-bs-theme="dark"] .policy-switch-box label,
    body[data-pms-theme="dark"] .policy-switch-box label,
    [data-pms-theme="dark"] .policy-switch-box label {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .policy-switch-box .text-muted,
    html[data-bs-theme="dark"] .policy-switch-box .text-muted,
    body[data-pms-theme="dark"] .policy-switch-box .text-muted,
    [data-pms-theme="dark"] .policy-switch-box .text-muted {
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
        border-color: rgba(79, 131, 255, 0.35) !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2) !important;
    }
</style>
@endpush

@section('content')
<div class="recruitment-settings-page">
    <div class="recruitment-settings-shell">
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
                <span>Recruitment & Hiring</span>
            </div>

            <!-- Page Header Card -->
            <div class="branches-header">
                <div class="header-left-box">
                    <div class="header-icon-badge">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="header-title">
                        <h1>Recruitment & Hiring Settings</h1>
                        <p>Configure job categories, recruitment pipeline stages, candidate auto-reply, and file attachment rules.</p>
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
                $jobCats = explode(',', $settings['job_categories'] ?? 'Engineering, Design, Marketing, Sales, HR');
                $stages = explode(',', $settings['pipeline_stages'] ?? 'Applied, Screening, Technical Interview, HR Interview, Offered, Hired');
                $catCount = count(array_filter($jobCats));
                $stageCount = count(array_filter($stages));
                $maxSize = $settings['max_resume_size_mb'] ?? '5';
                $autoReply = ($settings['auto_reply'] ?? '1') == '1';
                $slaDays = $settings['hiring_sla_days'] ?? '30';
                $probationMonths = $settings['probation_period_months'] ?? '3';
            @endphp
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon categories">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Job Categories</h6>
                        <h3>{{ $catCount }} Active Categories</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stages">
                        <i class="fas fa-diagram-project"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Pipeline Stages</h6>
                        <h3>{{ $stageCount }} Workflow Stages</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon sla">
                        <i class="fas fa-business-time"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Hiring SLA Target</h6>
                        <h3>{{ $slaDays }} Days SLA</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon probation">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Probation Period</h6>
                        <h3>{{ $probationMonths }} Months</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon resumesize">
                        <i class="fas fa-file-arrow-up"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Max Resume Size</h6>
                        <h3>{{ $maxSize }} MB Limit</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon autoreply">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Auto-Reply Email</h6>
                        <h3>{{ $autoReply ? 'Enabled' : 'Disabled' }}</h3>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="address-card-elevated">
                <div class="card-header-custom">
                    <div class="card-header-avatar shadow-sm">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold fs-5" style="color: #0F172A;">Hiring Pipeline & Job Postings</h5>
                        <small class="text-muted">Set up application workflows, job categories, and candidate evaluation criteria</small>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    @if($isSettingsReadOnly)
                        <div class="alert d-flex align-items-center mb-4 rounded-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #1e40af; border-left: 4px solid #3b82f6 !important; padding: 14px 18px;">
                            <i class="fas fa-eye me-3 fs-3 text-primary"></i>
                            <div>
                                <strong class="d-block text-primary fw-bold" style="font-size: 14px;">View-Only Mode</strong>
                                <span style="font-size: 13px; color: #1e3a8a;">You are viewing recruitment and hiring pipeline settings in read-only mode. Only Administrators have permission to modify hiring workflows and application rules.</span>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.settings.recruitment.update') }}">
                        @csrf

                        <!-- Section 1: Categories & Pipeline -->
                        <div class="section-badge">
                            <i class="fas fa-layer-group"></i> Job Categories & Pipeline Workflows
                        </div>

                        <div class="row g-4 mb-5">
                            <!-- Job Categories -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Job Categories (Comma Separated) <span class="req-asterisk">*</span></label>
                                <textarea name="job_categories" class="textarea-custom" rows="4" {{ $isSettingsReadOnly ? 'readonly' : 'required' }} placeholder="e.g. Engineering, Design, Marketing, Sales, HR">{{ old('job_categories', $settings['job_categories'] ?? '') }}</textarea>
                                <small class="text-muted mt-1.5 d-block"><i class="fas fa-info-circle me-1" style="color: #2F6BFF;"></i>Separate multiple categories with commas (e.g. Engineering, Design, Marketing, Sales, HR).</small>
                            </div>

                            <!-- Pipeline Stages -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Recruitment Pipeline Stages (Comma Separated) <span class="req-asterisk">*</span></label>
                                <textarea name="pipeline_stages" class="textarea-custom" rows="4" {{ $isSettingsReadOnly ? 'readonly' : 'required' }} placeholder="e.g. Applied, Screening, Technical Interview, HR Interview, Offered, Hired">{{ old('pipeline_stages', $settings['pipeline_stages'] ?? '') }}</textarea>
                                <small class="text-muted mt-1.5 d-block"><i class="fas fa-info-circle me-1" style="color: #2F6BFF;"></i>Define candidate pipeline progression steps (e.g. Applied, Screening, Technical Interview, HR Interview, Offered, Hired).</small>
                            </div>
                        </div>

                        <!-- Section 2: Resume File Rules & Auto Reply -->
                        <div class="section-badge">
                            <i class="fas fa-paperclip"></i> Resume File Rules & Candidate Notifications
                        </div>

                        <div class="row g-4">
                            <!-- Max Resume Size -->
                            <div class="col-md-4">
                                <label class="form-label-custom">Max Resume Upload Size (MB) <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-file-pdf"></i></span>
                                    <input type="number" name="max_resume_size_mb" class="form-control" min="1" max="50"
                                        value="{{ old('max_resume_size_mb', $settings['max_resume_size_mb'] ?? '5') }}"
                                        {{ $isSettingsReadOnly ? 'readonly' : 'required' }}>
                                </div>
                                <small class="text-muted mt-1 d-block">Maximum allowed file size per candidate application.</small>
                            </div>

                            <!-- Allowed Extensions -->
                            <div class="col-md-4">
                                <label class="form-label-custom">Allowed Resume Extensions <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-file-code"></i></span>
                                    <input type="text" name="allowed_file_types" class="form-control"
                                        value="{{ old('allowed_file_types', $settings['allowed_file_types'] ?? 'pdf,doc,docx') }}"
                                        {{ $isSettingsReadOnly ? 'readonly' : 'required' }}>
                                </div>
                                <small class="text-muted mt-1 d-block">Comma-separated file extensions (e.g. pdf, doc, docx).</small>
                            </div>

                            <!-- Auto Reply Switch Box -->
                            <div class="col-md-4">
                                <label class="form-label-custom">Auto-Acknowledgement Email</label>
                                <div class="policy-switch-box">
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-3 w-100">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="auto_reply" value="1" id="autoReplySwitch"
                                            style="width: 2.8em; height: 1.4em; cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }};"
                                            {{ ($settings['auto_reply'] ?? '1') == '1' ? 'checked' : '' }}
                                            {{ $isSettingsReadOnly ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="autoReplySwitch" style="cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }}; font-size: 0.9rem;">
                                            Send Candidate Auto-Acknowledgement Email
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: SLA & Probation Governance -->
                        <div class="section-badge mt-5">
                            <i class="fas fa-business-time"></i> Recruitment SLA & Probation Governance
                        </div>

                        <div class="row g-4">
                            <!-- Hiring SLA Target (Days) -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Hiring SLA Target (Days) <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-business-time"></i></span>
                                    <input type="number" name="hiring_sla_days" class="form-control" min="1" max="365"
                                        value="{{ old('hiring_sla_days', $settings['hiring_sla_days'] ?? '30') }}"
                                        {{ $isSettingsReadOnly ? 'readonly' : 'required' }}>
                                    <span class="input-group-text fw-bold text-dark bg-light">Days</span>
                                </div>
                                <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1" style="color: #2F6BFF;"></i>Target timeline (in days) from requirement creation to job offer issue.</small>
                            </div>

                            <!-- Probation Period (Months) -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Standard Probation Period (Months) <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-user-clock"></i></span>
                                    <input type="number" name="probation_period_months" class="form-control" min="0" max="24"
                                        value="{{ old('probation_period_months', $settings['probation_period_months'] ?? '3') }}"
                                        {{ $isSettingsReadOnly ? 'readonly' : 'required' }}>
                                    <span class="input-group-text fw-bold text-dark bg-light">Months</span>
                                </div>
                                <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1" style="color: #2F6BFF;"></i>Standard evaluation period assigned to candidates post placement.</small>
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
                                    <i class="fas fa-save me-1.5"></i> Save Recruitment Settings
                                </button>
                            @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
