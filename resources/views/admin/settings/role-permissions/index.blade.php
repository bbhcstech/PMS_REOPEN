@extends('admin.layout.app')

@section('title', 'Role & Permission')

@push('styles')
<style>
    .role-permission-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #F8FAFC 0%, #EEF2FF 50%, #F8FAFC 100%);
        color: #0F172A;
    }

    .role-permission-shell {
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
        background: transparent !important;
        background-color: transparent !important;
        fill: #ffffff !important;
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
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 1.25rem !important;
        margin-bottom: 2rem !important;
    }

    .stat-card,
    .role-permission-page .stat-card,
    .role-permission-page .stat-card:first-of-type {
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

    .role-permission-page .stat-card:first-of-type *,
    .role-permission-page .stat-card * {
        -webkit-text-fill-color: initial;
    }

    .role-permission-page .stat-card h3,
    .role-permission-page .stat-card:first-of-type h3 {
        color: #0F172A !important;
        -webkit-text-fill-color: #0F172A !important;
    }

    .role-permission-page .stat-card h6,
    .role-permission-page .stat-card span,
    .role-permission-page .stat-card:first-of-type span,
    .role-permission-page .stat-card:first-of-type h6 {
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

    .stat-icon.roleactive,
    .role-permission-page .stat-card:first-of-type .stat-icon.roleactive {
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF) !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
    }

    .stat-icon.roleactive i,
    .stat-icon.roleactive svg,
    .stat-icon.roleactive [class*="fa"],
    .role-permission-page .stat-card .stat-icon.roleactive i,
    .role-permission-page .stat-card .stat-icon.roleactive svg,
    .role-permission-page .stat-card .stat-icon.roleactive [class*="fa"],
    .role-permission-page .stat-card:first-of-type .stat-icon.roleactive i,
    .role-permission-page .stat-card:first-of-type .stat-icon.roleactive svg,
    .role-permission-page .stat-card:first-of-type .stat-icon.roleactive [class*="fa"] {
        background: transparent !important;
        background-color: transparent !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        fill: #2F6BFF !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .stat-icon.totroles,
    .role-permission-page .stat-card .stat-icon.totroles {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
    }

    .stat-icon.totroles i,
    .stat-icon.totroles svg,
    .stat-icon.totroles [class*="fa"],
    .role-permission-page .stat-card .stat-icon.totroles i,
    .role-permission-page .stat-card .stat-icon.totroles svg,
    .role-permission-page .stat-card .stat-icon.totroles [class*="fa"],
    .role-permission-page .stat-card:first-of-type .stat-icon.totroles i,
    .role-permission-page .stat-card:first-of-type .stat-icon.totroles svg,
    .role-permission-page .stat-card:first-of-type .stat-icon.totroles [class*="fa"] {
        background: transparent !important;
        background-color: transparent !important;
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
        fill: #0284c7 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .stat-icon.totmodules,
    .role-permission-page .stat-card .stat-icon.totmodules {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
    }

    .stat-icon.totmodules i,
    .stat-icon.totmodules svg,
    .stat-icon.totmodules [class*="fa"],
    .role-permission-page .stat-card .stat-icon.totmodules i,
    .role-permission-page .stat-card .stat-icon.totmodules svg,
    .role-permission-page .stat-card .stat-icon.totmodules [class*="fa"],
    .role-permission-page .stat-card:first-of-type .stat-icon.totmodules i,
    .role-permission-page .stat-card:first-of-type .stat-icon.totmodules svg,
    .role-permission-page .stat-card:first-of-type .stat-icon.totmodules [class*="fa"] {
        background: transparent !important;
        background-color: transparent !important;
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
        fill: #d97706 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .stat-icon.actions,
    .role-permission-page .stat-card .stat-icon.actions {
        background: linear-gradient(145deg, #e0e7ff, #c7d2fe) !important;
        color: #4f46e5 !important;
        -webkit-text-fill-color: #4f46e5 !important;
    }

    .stat-icon.actions i,
    .stat-icon.actions svg,
    .stat-icon.actions [class*="fa"],
    .role-permission-page .stat-card .stat-icon.actions i,
    .role-permission-page .stat-card .stat-icon.actions svg,
    .role-permission-page .stat-card .stat-icon.actions [class*="fa"],
    .role-permission-page .stat-card:first-of-type .stat-icon.actions i,
    .role-permission-page .stat-card:first-of-type .stat-icon.actions svg,
    .role-permission-page .stat-card:first-of-type .stat-icon.actions [class*="fa"] {
        background: transparent !important;
        background-color: transparent !important;
        color: #4f46e5 !important;
        -webkit-text-fill-color: #4f46e5 !important;
        fill: #4f46e5 !important;
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

    /* ===== ROLE SELECT CARD ===== */
    .role-select-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 1.5rem 2.25rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(47, 107, 255, 0.15);
        box-shadow: 0 10px 30px -10px rgba(47, 107, 255, 0.08);
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

    .input-group-custom .input-group-text i,
    .input-group-custom .input-group-text svg,
    .input-group-custom .input-group-text [class*="fa"] {
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        background: transparent !important;
        background-color: transparent !important;
        fill: #2F6BFF !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .input-group-custom .form-select {
        border: none;
        background-color: transparent;
        font-size: 0.95rem;
        font-weight: 700;
        color: #0F172A;
        padding-right: 18px;
        height: 50px;
    }

    .input-group-custom .form-select:focus {
        box-shadow: none;
        background-color: transparent;
    }

    /* ===== MATRIX TABLE CARD ===== */
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
        justify-content: space-between;
        background: transparent;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .card-header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
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

    .matrix-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .matrix-table th {
        background: linear-gradient(90deg, #EEF2FF, #F8FAFC);
        color: #065f46;
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(47, 107, 255, 0.2);
    }

    .matrix-table td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
        border-bottom: 1px solid rgba(47, 107, 255, 0.08);
        transition: background-color 0.2s ease;
    }

    .matrix-table tr:hover td {
        background-color: rgba(240, 253, 244, 0.6);
    }

    .module-title-box {
        display: flex;
        flex-direction: column;
    }

    .module-title-box strong {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0F172A;
    }

    .module-title-box small {
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 500;
    }

    /* Custom Checkbox */
    .perm-checkbox {
        width: 20px;
        height: 20px;
        border-radius: 6px;
        border: 2px solid rgba(47, 107, 255, 0.35);
        accent-color: #2F6BFF;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .perm-checkbox:hover {
        transform: scale(1.15);
        border-color: #60A5FA;
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

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    @media (max-width: 768px) {
        .role-permission-page {
            padding: 1.25rem 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* ===== DARK MODE SUPPORT ===== */
    html[data-pms-theme="dark"] .role-permission-page,
    html[data-theme="dark"] .role-permission-page,
    html[data-bs-theme="dark"] .role-permission-page,
    body[data-pms-theme="dark"] .role-permission-page,
    body[data-theme="dark"] .role-permission-page,
    body[data-bs-theme="dark"] .role-permission-page,
    [data-pms-theme="dark"] .role-permission-page,
    [data-theme="dark"] .role-permission-page,
    [data-bs-theme="dark"] .role-permission-page,
    .dark-mode .role-permission-page {
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
    html[data-pms-theme="dark"] .role-select-card,
    html[data-theme="dark"] .role-select-card,
    html[data-bs-theme="dark"] .role-select-card,
    body[data-pms-theme="dark"] .role-select-card,
    body[data-theme="dark"] .role-select-card,
    body[data-bs-theme="dark"] .role-select-card,
    [data-pms-theme="dark"] .role-select-card,
    [data-theme="dark"] .role-select-card,
    [data-bs-theme="dark"] .role-select-card,
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
    .dark-mode .role-select-card,
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
    html[data-pms-theme="dark"] .role-permission-page .stat-card,
    html[data-theme="dark"] .role-permission-page .stat-card,
    html[data-bs-theme="dark"] .role-permission-page .stat-card,
    body[data-pms-theme="dark"] .role-permission-page .stat-card,
    body[data-theme="dark"] .role-permission-page .stat-card,
    body[data-bs-theme="dark"] .role-permission-page .stat-card,
    [data-pms-theme="dark"] .role-permission-page .stat-card,
    [data-theme="dark"] .role-permission-page .stat-card,
    [data-bs-theme="dark"] .role-permission-page .stat-card,
    html[data-pms-theme="dark"] .role-permission-page .stat-card:first-of-type,
    html[data-theme="dark"] .role-permission-page .stat-card:first-of-type,
    html[data-bs-theme="dark"] .role-permission-page .stat-card:first-of-type,
    body[data-pms-theme="dark"] .role-permission-page .stat-card:first-of-type,
    body[data-theme="dark"] .role-permission-page .stat-card:first-of-type,
    body[data-bs-theme="dark"] .role-permission-page .stat-card:first-of-type,
    [data-pms-theme="dark"] .role-permission-page .stat-card:first-of-type,
    [data-theme="dark"] .role-permission-page .stat-card:first-of-type,
    [data-bs-theme="dark"] .role-permission-page .stat-card:first-of-type,
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

    html[data-pms-theme="dark"] .role-permission-page .stat-card h3,
    html[data-theme="dark"] .role-permission-page .stat-card h3,
    html[data-bs-theme="dark"] .role-permission-page .stat-card h3,
    body[data-pms-theme="dark"] .role-permission-page .stat-card h3,
    body[data-theme="dark"] .role-permission-page .stat-card h3,
    body[data-bs-theme="dark"] .role-permission-page .stat-card h3,
    [data-pms-theme="dark"] .role-permission-page .stat-card h3,
    [data-theme="dark"] .role-permission-page .stat-card h3,
    [data-bs-theme="dark"] .role-permission-page .stat-card h3,
    html[data-pms-theme="dark"] .role-permission-page .stat-card:first-of-type h3,
    html[data-theme="dark"] .role-permission-page .stat-card:first-of-type h3,
    html[data-bs-theme="dark"] .role-permission-page .stat-card:first-of-type h3,
    body[data-pms-theme="dark"] .role-permission-page .stat-card:first-of-type h3,
    body[data-theme="dark"] .role-permission-page .stat-card:first-of-type h3,
    body[data-bs-theme="dark"] .role-permission-page .stat-card:first-of-type h3,
    [data-pms-theme="dark"] .role-permission-page .stat-card:first-of-type h3,
    [data-theme="dark"] .role-permission-page .stat-card:first-of-type h3,
    [data-bs-theme="dark"] .role-permission-page .stat-card:first-of-type h3,
    .dark-mode .stat-card h3 {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .role-permission-page .stat-card h6,
    html[data-theme="dark"] .role-permission-page .stat-card h6,
    html[data-bs-theme="dark"] .role-permission-page .stat-card h6,
    body[data-pms-theme="dark"] .role-permission-page .stat-card h6,
    body[data-theme="dark"] .role-permission-page .stat-card h6,
    body[data-bs-theme="dark"] .role-permission-page .stat-card h6,
    [data-pms-theme="dark"] .role-permission-page .stat-card h6,
    [data-theme="dark"] .role-permission-page .stat-card h6,
    [data-bs-theme="dark"] .role-permission-page .stat-card h6,
    html[data-pms-theme="dark"] .role-permission-page .stat-card:first-of-type h6,
    html[data-theme="dark"] .role-permission-page .stat-card:first-of-type h6,
    html[data-bs-theme="dark"] .role-permission-page .stat-card:first-of-type h6,
    body[data-pms-theme="dark"] .role-permission-page .stat-card:first-of-type h6,
    body[data-theme="dark"] .role-permission-page .stat-card:first-of-type h6,
    body[data-bs-theme="dark"] .role-permission-page .stat-card:first-of-type h6,
    [data-pms-theme="dark"] .role-permission-page .stat-card:first-of-type h6,
    [data-theme="dark"] .role-permission-page .stat-card:first-of-type h6,
    [data-bs-theme="dark"] .role-permission-page .stat-card:first-of-type h6,
    .dark-mode .stat-card h6 {
        color: #94a3b8 !important;
        -webkit-text-fill-color: #94a3b8 !important;
    }

    /* Dark Mode Stat Icons */
    html[data-pms-theme="dark"] .stat-icon.roleactive,
    html[data-theme="dark"] .stat-icon.roleactive,
    html[data-bs-theme="dark"] .stat-icon.roleactive,
    body[data-pms-theme="dark"] .stat-icon.roleactive,
    body[data-theme="dark"] .stat-icon.roleactive,
    body[data-bs-theme="dark"] .stat-icon.roleactive,
    [data-pms-theme="dark"] .stat-icon.roleactive,
    [data-theme="dark"] .stat-icon.roleactive,
    [data-bs-theme="dark"] .stat-icon.roleactive,
    .dark-mode .stat-icon.roleactive,
    [data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.roleactive,
    [data-pms-theme="dark"] .role-permission-page .stat-card:first-of-type .stat-icon.roleactive,
    [data-theme="dark"] .role-permission-page .stat-card .stat-icon.roleactive,
    [data-theme="dark"] .role-permission-page .stat-card:first-of-type .stat-icon.roleactive,
    [data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.roleactive,
    [data-bs-theme="dark"] .role-permission-page .stat-card:first-of-type .stat-icon.roleactive,
    html[data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.roleactive,
    html[data-pms-theme="dark"] .role-permission-page .stat-card:first-of-type .stat-icon.roleactive,
    html[data-theme="dark"] .role-permission-page .stat-card .stat-icon.roleactive,
    html[data-theme="dark"] .role-permission-page .stat-card:first-of-type .stat-icon.roleactive,
    html[data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.roleactive,
    html[data-bs-theme="dark"] .role-permission-page .stat-card:first-of-type .stat-icon.roleactive {
        background: rgba(47, 107, 255, 0.25) !important;
        background-image: none !important;
        border: 1px solid rgba(79, 131, 255, 0.4) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.roleactive i,
    html[data-pms-theme="dark"] .stat-icon.roleactive svg,
    html[data-pms-theme="dark"] .stat-icon.roleactive [class*="fa"],
    html[data-theme="dark"] .stat-icon.roleactive i,
    html[data-theme="dark"] .stat-icon.roleactive svg,
    html[data-theme="dark"] .stat-icon.roleactive [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.roleactive i,
    html[data-bs-theme="dark"] .stat-icon.roleactive svg,
    html[data-bs-theme="dark"] .stat-icon.roleactive [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.roleactive i,
    body[data-theme="dark"] .stat-icon.roleactive i,
    body[data-bs-theme="dark"] .stat-icon.roleactive i,
    [data-pms-theme="dark"] .stat-icon.roleactive i,
    [data-theme="dark"] .stat-icon.roleactive i,
    [data-bs-theme="dark"] .stat-icon.roleactive i,
    .dark-mode .stat-icon.roleactive i,
    .dark-mode .stat-icon.roleactive svg,
    .dark-mode .stat-icon.roleactive [class*="fa"],
    html[data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.roleactive i,
    html[data-pms-theme="dark"] .role-permission-page .stat-card:first-of-type .stat-icon.roleactive i,
    html[data-theme="dark"] .role-permission-page .stat-card .stat-icon.roleactive i,
    html[data-theme="dark"] .role-permission-page .stat-card:first-of-type .stat-icon.roleactive i,
    html[data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.roleactive i,
    html[data-bs-theme="dark"] .role-permission-page .stat-card:first-of-type .stat-icon.roleactive i,
    body[data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.roleactive i,
    body[data-pms-theme="dark"] .role-permission-page .stat-card:first-of-type .stat-icon.roleactive i,
    body[data-theme="dark"] .role-permission-page .stat-card .stat-icon.roleactive i,
    body[data-theme="dark"] .role-permission-page .stat-card:first-of-type .stat-icon.roleactive i,
    body[data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.roleactive i,
    body[data-bs-theme="dark"] .role-permission-page .stat-card:first-of-type .stat-icon.roleactive i,
    [data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.roleactive i,
    [data-pms-theme="dark"] .role-permission-page .stat-card:first-of-type .stat-icon.roleactive i,
    [data-theme="dark"] .role-permission-page .stat-card .stat-icon.roleactive i,
    [data-theme="dark"] .role-permission-page .stat-card:first-of-type .stat-icon.roleactive i,
    [data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.roleactive i,
    [data-bs-theme="dark"] .role-permission-page .stat-card:first-of-type .stat-icon.roleactive i,
    .dark-mode .role-permission-page .stat-card .stat-icon.roleactive i,
    .dark-mode .role-permission-page .stat-card:first-of-type .stat-icon.roleactive i {
        background: transparent !important;
        background-image: none !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
        fill: #60A5FA !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    html[data-pms-theme="dark"] .stat-icon.totroles,
    html[data-theme="dark"] .stat-icon.totroles,
    html[data-bs-theme="dark"] .stat-icon.totroles,
    body[data-pms-theme="dark"] .stat-icon.totroles,
    body[data-theme="dark"] .stat-icon.totroles,
    body[data-bs-theme="dark"] .stat-icon.totroles,
    [data-pms-theme="dark"] .stat-icon.totroles,
    [data-theme="dark"] .stat-icon.totroles,
    [data-bs-theme="dark"] .stat-icon.totroles,
    .dark-mode .stat-icon.totroles,
    [data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.totroles,
    [data-theme="dark"] .role-permission-page .stat-card .stat-icon.totroles,
    [data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.totroles,
    html[data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.totroles,
    html[data-theme="dark"] .role-permission-page .stat-card .stat-icon.totroles,
    html[data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.totroles {
        background: rgba(56, 189, 248, 0.25) !important;
        background-image: none !important;
        border: 1px solid rgba(56, 189, 248, 0.4) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.totroles i,
    html[data-pms-theme="dark"] .stat-icon.totroles svg,
    html[data-pms-theme="dark"] .stat-icon.totroles [class*="fa"],
    html[data-theme="dark"] .stat-icon.totroles i,
    html[data-theme="dark"] .stat-icon.totroles svg,
    html[data-theme="dark"] .stat-icon.totroles [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.totroles i,
    html[data-bs-theme="dark"] .stat-icon.totroles svg,
    html[data-bs-theme="dark"] .stat-icon.totroles [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.totroles i,
    body[data-theme="dark"] .stat-icon.totroles i,
    body[data-bs-theme="dark"] .stat-icon.totroles i,
    [data-pms-theme="dark"] .stat-icon.totroles i,
    [data-theme="dark"] .stat-icon.totroles i,
    [data-bs-theme="dark"] .stat-icon.totroles i,
    .dark-mode .stat-icon.totroles i,
    .dark-mode .stat-icon.totroles svg,
    .dark-mode .stat-icon.totroles [class*="fa"],
    html[data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.totroles i,
    html[data-theme="dark"] .role-permission-page .stat-card .stat-icon.totroles i,
    html[data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.totroles i,
    body[data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.totroles i,
    body[data-theme="dark"] .role-permission-page .stat-card .stat-icon.totroles i,
    body[data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.totroles i,
    [data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.totroles i,
    [data-theme="dark"] .role-permission-page .stat-card .stat-icon.totroles i,
    [data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.totroles i,
    .dark-mode .role-permission-page .stat-card .stat-icon.totroles i {
        background: transparent !important;
        background-image: none !important;
        color: #38bdf8 !important;
        -webkit-text-fill-color: #38bdf8 !important;
        fill: #38bdf8 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    html[data-pms-theme="dark"] .stat-icon.totmodules,
    html[data-theme="dark"] .stat-icon.totmodules,
    html[data-bs-theme="dark"] .stat-icon.totmodules,
    body[data-pms-theme="dark"] .stat-icon.totmodules,
    body[data-theme="dark"] .stat-icon.totmodules,
    body[data-bs-theme="dark"] .stat-icon.totmodules,
    [data-pms-theme="dark"] .stat-icon.totmodules,
    [data-theme="dark"] .stat-icon.totmodules,
    [data-bs-theme="dark"] .stat-icon.totmodules,
    .dark-mode .stat-icon.totmodules,
    [data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.totmodules,
    [data-theme="dark"] .role-permission-page .stat-card .stat-icon.totmodules,
    [data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.totmodules,
    html[data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.totmodules,
    html[data-theme="dark"] .role-permission-page .stat-card .stat-icon.totmodules,
    html[data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.totmodules {
        background: rgba(251, 191, 36, 0.25) !important;
        background-image: none !important;
        border: 1px solid rgba(251, 191, 36, 0.4) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.totmodules i,
    html[data-pms-theme="dark"] .stat-icon.totmodules svg,
    html[data-pms-theme="dark"] .stat-icon.totmodules [class*="fa"],
    html[data-theme="dark"] .stat-icon.totmodules i,
    html[data-theme="dark"] .stat-icon.totmodules svg,
    html[data-theme="dark"] .stat-icon.totmodules [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.totmodules i,
    html[data-bs-theme="dark"] .stat-icon.totmodules svg,
    html[data-bs-theme="dark"] .stat-icon.totmodules [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.totmodules i,
    body[data-theme="dark"] .stat-icon.totmodules i,
    body[data-bs-theme="dark"] .stat-icon.totmodules i,
    [data-pms-theme="dark"] .stat-icon.totmodules i,
    [data-theme="dark"] .stat-icon.totmodules i,
    [data-bs-theme="dark"] .stat-icon.totmodules i,
    .dark-mode .stat-icon.totmodules i,
    .dark-mode .stat-icon.totmodules svg,
    .dark-mode .stat-icon.totmodules [class*="fa"],
    html[data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.totmodules i,
    html[data-theme="dark"] .role-permission-page .stat-card .stat-icon.totmodules i,
    html[data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.totmodules i,
    body[data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.totmodules i,
    body[data-theme="dark"] .role-permission-page .stat-card .stat-icon.totmodules i,
    body[data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.totmodules i,
    [data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.totmodules i,
    [data-theme="dark"] .role-permission-page .stat-card .stat-icon.totmodules i,
    [data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.totmodules i,
    .dark-mode .role-permission-page .stat-card .stat-icon.totmodules i {
        background: transparent !important;
        background-image: none !important;
        color: #fbbf24 !important;
        -webkit-text-fill-color: #fbbf24 !important;
        fill: #fbbf24 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    html[data-pms-theme="dark"] .stat-icon.actions,
    html[data-theme="dark"] .stat-icon.actions,
    html[data-bs-theme="dark"] .stat-icon.actions,
    body[data-pms-theme="dark"] .stat-icon.actions,
    body[data-theme="dark"] .stat-icon.actions,
    body[data-bs-theme="dark"] .stat-icon.actions,
    [data-pms-theme="dark"] .stat-icon.actions,
    [data-theme="dark"] .stat-icon.actions,
    [data-bs-theme="dark"] .stat-icon.actions,
    .dark-mode .stat-icon.actions,
    [data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.actions,
    [data-theme="dark"] .role-permission-page .stat-card .stat-icon.actions,
    [data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.actions,
    html[data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.actions,
    html[data-theme="dark"] .role-permission-page .stat-card .stat-icon.actions,
    html[data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.actions {
        background: rgba(129, 140, 248, 0.25) !important;
        background-image: none !important;
        border: 1px solid rgba(129, 140, 248, 0.4) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.actions i,
    html[data-pms-theme="dark"] .stat-icon.actions svg,
    html[data-pms-theme="dark"] .stat-icon.actions [class*="fa"],
    html[data-theme="dark"] .stat-icon.actions i,
    html[data-theme="dark"] .stat-icon.actions svg,
    html[data-theme="dark"] .stat-icon.actions [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.actions i,
    html[data-bs-theme="dark"] .stat-icon.actions svg,
    html[data-bs-theme="dark"] .stat-icon.actions [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.actions i,
    body[data-theme="dark"] .stat-icon.actions i,
    body[data-bs-theme="dark"] .stat-icon.actions i,
    [data-pms-theme="dark"] .stat-icon.actions i,
    [data-theme="dark"] .stat-icon.actions i,
    [data-bs-theme="dark"] .stat-icon.actions i,
    .dark-mode .stat-icon.actions i,
    .dark-mode .stat-icon.actions svg,
    .dark-mode .stat-icon.actions [class*="fa"],
    html[data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.actions i,
    html[data-theme="dark"] .role-permission-page .stat-card .stat-icon.actions i,
    html[data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.actions i,
    body[data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.actions i,
    body[data-theme="dark"] .role-permission-page .stat-card .stat-icon.actions i,
    body[data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.actions i,
    [data-pms-theme="dark"] .role-permission-page .stat-card .stat-icon.actions i,
    [data-theme="dark"] .role-permission-page .stat-card .stat-icon.actions i,
    [data-bs-theme="dark"] .role-permission-page .stat-card .stat-icon.actions i,
    .dark-mode .role-permission-page .stat-card .stat-icon.actions i {
        background: transparent !important;
        background-image: none !important;
        color: #a5b4fc !important;
        -webkit-text-fill-color: #a5b4fc !important;
        fill: #a5b4fc !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    /* Form Controls & Select Cards in Dark Mode */
    html[data-pms-theme="dark"] .input-group-custom,
    html[data-theme="dark"] .input-group-custom,
    html[data-bs-theme="dark"] .input-group-custom,
    [data-pms-theme="dark"] .input-group-custom {
        background-color: #1a2234 !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
    }

    html[data-pms-theme="dark"] .input-group-custom .form-select,
    html[data-theme="dark"] .input-group-custom .form-select,
    [data-pms-theme="dark"] .input-group-custom .form-select {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .input-group-custom .input-group-text i,
    html[data-theme="dark"] .input-group-custom .input-group-text i,
    [data-pms-theme="dark"] .input-group-custom .input-group-text i {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .role-select-card label,
    html[data-theme="dark"] .role-select-card label,
    [data-pms-theme="dark"] .role-select-card label,
    html[data-pms-theme="dark"] .role-select-card span,
    html[data-theme="dark"] .role-select-card span,
    [data-pms-theme="dark"] .role-select-card span {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .role-select-card small,
    html[data-theme="dark"] .role-select-card small,
    [data-pms-theme="dark"] .role-select-card small,
    html[data-pms-theme="dark"] .role-select-card .text-muted,
    html[data-theme="dark"] .role-select-card .text-muted,
    [data-pms-theme="dark"] .role-select-card .text-muted {
        color: #94a3b8 !important;
        -webkit-text-fill-color: #94a3b8 !important;
    }

    /* Matrix Table in Dark Mode */
    html[data-pms-theme="dark"] .matrix-table th,
    html[data-theme="dark"] .matrix-table th,
    [data-pms-theme="dark"] .matrix-table th {
        background: #1e293b !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
        border-bottom-color: rgba(255, 255, 255, 0.08) !important;
    }

    html[data-pms-theme="dark"] .matrix-table td,
    html[data-theme="dark"] .matrix-table td,
    [data-pms-theme="dark"] .matrix-table td {
        border-bottom-color: rgba(255, 255, 255, 0.08) !important;
        color: #e2e8f0 !important;
    }

    html[data-pms-theme="dark"] .module-title-box strong,
    html[data-theme="dark"] .module-title-box strong,
    [data-pms-theme="dark"] .module-title-box strong {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .module-title-box small,
    html[data-theme="dark"] .module-title-box small,
    [data-pms-theme="dark"] .module-title-box small {
        color: #94a3b8 !important;
        -webkit-text-fill-color: #94a3b8 !important;
    }

    html[data-pms-theme="dark"] .matrix-table tr:hover td,
    html[data-theme="dark"] .matrix-table tr:hover td,
    [data-pms-theme="dark"] .matrix-table tr:hover td {
        background-color: rgba(255, 255, 255, 0.03) !important;
    }
</style>
@endpush

@section('content')
<div class="role-permission-page">
    <div class="role-permission-shell">
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
                <span>Role & Permission Matrix</span>
            </div>

            <!-- Page Header Card -->
            <div class="branches-header">
                <div class="header-left-box">
                    <div class="header-icon-badge">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="header-title">
                        <h1>Role & Permission Management</h1>
                        <p>Configure access control levels, granular action permissions, and module capabilities for system roles.</p>
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
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon roleactive">
                        <i class="fas fa-user-gear"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Active Role</h6>
                        <h3>{{ ucfirst($role) }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon totroles">
                        <i class="fas fa-users-gear"></i>
                    </div>
                    <div class="stat-info">
                        <h6>System Roles</h6>
                        <h3>{{ count($roles) }} Configured</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon totmodules">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Total Modules</h6>
                        <h3>{{ count($modules) }} Modules</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon actions">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Action Types</h6>
                        <h3>{{ count($permissions) }} Permissions</h3>
                    </div>
                </div>
            </div>

            <!-- Select Role Card -->
            <div class="role-select-card">
                <form method="GET" action="{{ route('admin.role-permissions.index') }}">
                    <div class="row align-items-center g-3">
                        <div class="col-md-5 col-lg-4">
                            <label class="form-label fw-bold text-dark mb-1.5" style="font-size: 0.9rem;"><i class="fas fa-sliders me-1.5" style="color: #2F6BFF;"></i>Select Role to Modify Permissions</label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                <select name="role" class="form-select" onchange="this.form.submit()">
                                    @foreach($roles as $option)
                                        <option value="{{ $option }}" @selected($role === $option)>{{ ucfirst($option) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-7 col-lg-8">
                            <small class="text-muted d-block mt-md-4"><i class="fas fa-info-circle me-1" style="color: #2F6BFF;"></i>Changing the selected role will dynamically reload module access control capabilities below.</small>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Permissions Matrix Table Card -->
            <form method="POST" action="{{ route('admin.role-permissions.update') }}">
                @csrf
                <input type="hidden" name="role" value="{{ $role }}">

                <div class="address-card-elevated">
                    <div class="card-header-custom">
                        <div class="card-header-left">
                            <div class="card-header-avatar shadow-sm">
                                <i class="fas fa-table-cells"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold fs-5" style="color: #0F172A;">Module Access Control Matrix</h5>
                                <small class="text-muted">Grant or revoke granular action privileges for <strong style="color: #2F6BFF;">{{ ucfirst($role) }}</strong></small>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="matrix-table">
                            <thead>
                                <tr>
                                    <th style="min-width: 240px;">Module Name</th>
                                    @foreach($permissions as $permission)
                                        <th class="text-center">{{ ucfirst($permission) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($modules as $module)
                                    @php $saved = $savedPermissions->get($module->id); @endphp
                                    <tr>
                                        <td>
                                            <div class="module-title-box">
                                                <strong>{{ $module->name }}</strong>
                                                @if($module->parent)<small><i class="fas fa-angle-right me-1" style="color: #2F6BFF;"></i>{{ $module->parent->name }}</small>@endif
                                            </div>
                                        </td>
                                        @foreach($permissions as $permission)
                                            <td class="text-center">
                                                <input type="checkbox" class="perm-checkbox" name="permissions[{{ $module->id }}][]" value="{{ $permission }}" @checked($role === 'admin' || (bool) optional($saved)->{'can_' . $permission})>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-top d-flex justify-content-between align-items-center flex-wrap gap-3" style="background: rgba(248, 250, 252, 0.6);">
                        <small class="text-muted"><i class="fas fa-shield-halved me-1" style="color: #2F6BFF;"></i>Changes will immediately affect users assigned to the <strong>{{ ucfirst($role) }}</strong> role.</small>
                        <button type="submit" class="btn-save-address">
                            <i class="fas fa-save me-1.5"></i> Save Permissions
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
