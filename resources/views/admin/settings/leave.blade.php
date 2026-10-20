<script>window.location.href = "{{ route('leaves.index', ['open_policy' => 1]) }}";</script>
@extends('admin.layout.app')

@section('title', 'Leave Settings & Policy')

@push('styles')
<style>
    .leave-settings-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #F8FAFC 0%, #EEF2FF 50%, #F8FAFC 100%);
        color: #0F172A;
    }

    .leave-settings-shell {
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

    .btn-manage-requests {
        background-color: #EEF2FF;
        border: 1px solid rgba(47, 107, 255, 0.3);
        color: #2F6BFF !important;
        font-weight: 700;
        font-size: 0.9rem;
        border-radius: 40px;
        padding: 0.65rem 1.4rem;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 4px 14px rgba(47, 107, 255, 0.12);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-manage-requests i,
    .btn-manage-requests svg,
    .btn-manage-requests [class*="fa"] {
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
    }

    .btn-manage-requests:hover {
        background-color: #E0E7FF;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(47, 107, 255, 0.2);
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
    .leave-settings-page .stat-card,
    .leave-settings-page .stat-card:first-of-type {
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

    .leave-settings-page .stat-card:first-of-type *,
    .leave-settings-page .stat-card * {
        -webkit-text-fill-color: initial;
    }

    .leave-settings-page .stat-card h3,
    .leave-settings-page .stat-card:first-of-type h3 {
        color: #0F172A !important;
        -webkit-text-fill-color: #0F172A !important;
    }

    .leave-settings-page .stat-card h6,
    .leave-settings-page .stat-card span,
    .leave-settings-page .stat-card:first-of-type span,
    .leave-settings-page .stat-card:first-of-type h6 {
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

    .stat-icon.casual {
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF) !important;
        border: 1px solid rgba(47, 107, 255, 0.25) !important;
    }

    .stat-icon.casual i,
    .stat-icon.casual svg,
    .stat-icon.casual [class*="fa"],
    .leave-settings-page .stat-card .stat-icon.casual i,
    .leave-settings-page .stat-card:first-of-type .stat-icon.casual i {
        background: transparent !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .stat-icon.sick {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        border: 1px solid rgba(56, 189, 248, 0.25) !important;
    }

    .stat-icon.sick i,
    .stat-icon.sick svg,
    .stat-icon.sick [class*="fa"],
    .leave-settings-page .stat-card .stat-icon.sick i {
        background: transparent !important;
        color: #0369a1 !important;
        -webkit-text-fill-color: #0369a1 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .stat-icon.carry {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        border: 1px solid rgba(251, 191, 36, 0.25) !important;
    }

    .stat-icon.carry i,
    .stat-icon.carry svg,
    .stat-icon.carry [class*="fa"],
    .leave-settings-page .stat-card .stat-icon.carry i {
        background: transparent !important;
        color: #b45309 !important;
        -webkit-text-fill-color: #b45309 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .stat-icon.policy {
        background: linear-gradient(145deg, #e0e7ff, #c7d2fe) !important;
        border: 1px solid rgba(129, 140, 248, 0.25) !important;
    }

    .stat-icon.policy i,
    .stat-icon.policy svg,
    .stat-icon.policy [class*="fa"],
    .leave-settings-page .stat-card .stat-icon.policy i {
        background: transparent !important;
        color: #3730a3 !important;
        -webkit-text-fill-color: #3730a3 !important;
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

    .switch-box-container {
        background: #f0fdf4;
        border: 1px solid rgba(47, 107, 255, 0.25);
        border-radius: 20px;
        padding: 18px 20px;
        min-height: 100px;
        display: flex;
        align-items: center;
    }

    .switch-box-container label,
    .switch-box-container .form-check-label {
        color: #0F172A !important;
        -webkit-text-fill-color: #0F172A !important;
    }

    html[data-pms-theme="dark"] .switch-box-container,
    html[data-theme="dark"] .switch-box-container,
    html[data-bs-theme="dark"] .switch-box-container,
    body[data-pms-theme="dark"] .switch-box-container,
    body[data-theme="dark"] .switch-box-container,
    body[data-bs-theme="dark"] .switch-box-container,
    [data-pms-theme="dark"] .switch-box-container,
    [data-theme="dark"] .switch-box-container,
    [data-bs-theme="dark"] .switch-box-container,
    .dark-mode .switch-box-container {
        background: #171e2e !important;
        background-color: #171e2e !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
    }

    html[data-pms-theme="dark"] .switch-box-container label,
    html[data-theme="dark"] .switch-box-container label,
    html[data-bs-theme="dark"] .switch-box-container label,
    body[data-pms-theme="dark"] .switch-box-container label,
    body[data-theme="dark"] .switch-box-container label,
    body[data-bs-theme="dark"] .switch-box-container label,
    [data-pms-theme="dark"] .switch-box-container label,
    [data-theme="dark"] .switch-box-container label,
    [data-bs-theme="dark"] .switch-box-container label,
    .dark-mode .switch-box-container label,
    html[data-pms-theme="dark"] .switch-box-container .form-check-label,
    html[data-theme="dark"] .switch-box-container .form-check-label,
    html[data-bs-theme="dark"] .switch-box-container .form-check-label,
    body[data-pms-theme="dark"] .switch-box-container .form-check-label,
    body[data-theme="dark"] .switch-box-container .form-check-label,
    body[data-bs-theme="dark"] .switch-box-container .form-check-label,
    [data-pms-theme="dark"] .switch-box-container .form-check-label,
    [data-theme="dark"] .switch-box-container .form-check-label,
    [data-bs-theme="dark"] .switch-box-container .form-check-label,
    .dark-mode .switch-box-container .form-check-label,
    html[data-pms-theme="dark"] .switch-box-container .text-dark,
    html[data-theme="dark"] .switch-box-container .text-dark,
    html[data-bs-theme="dark"] .switch-box-container .text-dark,
    body[data-pms-theme="dark"] .switch-box-container .text-dark,
    body[data-theme="dark"] .switch-box-container .text-dark,
    body[data-bs-theme="dark"] .switch-box-container .text-dark,
    [data-pms-theme="dark"] .switch-box-container .text-dark,
    [data-theme="dark"] .switch-box-container .text-dark,
    [data-bs-theme="dark"] .switch-box-container .text-dark,
    .dark-mode .switch-box-container .text-dark {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .form-check-input:checked {
        background-color: #10b981;
        border-color: #10b981;
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
    html[data-pms-theme="dark"] .leave-settings-page,
    html[data-theme="dark"] .leave-settings-page,
    html[data-bs-theme="dark"] .leave-settings-page,
    body[data-pms-theme="dark"] .leave-settings-page,
    body[data-theme="dark"] .leave-settings-page,
    body[data-bs-theme="dark"] .leave-settings-page,
    [data-pms-theme="dark"] .leave-settings-page,
    [data-theme="dark"] .leave-settings-page,
    [data-bs-theme="dark"] .leave-settings-page,
    .dark-mode .leave-settings-page {
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
    html[data-pms-theme="dark"] .leave-settings-page .stat-card,
    html[data-theme="dark"] .leave-settings-page .stat-card,
    html[data-bs-theme="dark"] .leave-settings-page .stat-card,
    body[data-pms-theme="dark"] .leave-settings-page .stat-card,
    body[data-theme="dark"] .leave-settings-page .stat-card,
    body[data-bs-theme="dark"] .leave-settings-page .stat-card,
    [data-pms-theme="dark"] .leave-settings-page .stat-card,
    [data-theme="dark"] .leave-settings-page .stat-card,
    [data-bs-theme="dark"] .leave-settings-page .stat-card,
    html[data-pms-theme="dark"] .leave-settings-page .stat-card:first-of-type,
    html[data-theme="dark"] .leave-settings-page .stat-card:first-of-type,
    html[data-bs-theme="dark"] .leave-settings-page .stat-card:first-of-type,
    body[data-pms-theme="dark"] .leave-settings-page .stat-card:first-of-type,
    body[data-theme="dark"] .leave-settings-page .stat-card:first-of-type,
    body[data-bs-theme="dark"] .leave-settings-page .stat-card:first-of-type,
    [data-pms-theme="dark"] .leave-settings-page .stat-card:first-of-type,
    [data-theme="dark"] .leave-settings-page .stat-card:first-of-type,
    [data-bs-theme="dark"] .leave-settings-page .stat-card:first-of-type,
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

    html[data-pms-theme="dark"] .leave-settings-page .stat-card h3,
    html[data-theme="dark"] .leave-settings-page .stat-card h3,
    html[data-bs-theme="dark"] .leave-settings-page .stat-card h3,
    body[data-pms-theme="dark"] .leave-settings-page .stat-card h3,
    body[data-theme="dark"] .leave-settings-page .stat-card h3,
    body[data-bs-theme="dark"] .leave-settings-page .stat-card h3,
    [data-pms-theme="dark"] .leave-settings-page .stat-card h3,
    [data-theme="dark"] .leave-settings-page .stat-card h3,
    [data-bs-theme="dark"] .leave-settings-page .stat-card h3,
    html[data-pms-theme="dark"] .leave-settings-page .stat-card:first-of-type h3,
    html[data-theme="dark"] .leave-settings-page .stat-card:first-of-type h3,
    html[data-bs-theme="dark"] .leave-settings-page .stat-card:first-of-type h3,
    body[data-pms-theme="dark"] .leave-settings-page .stat-card:first-of-type h3,
    body[data-theme="dark"] .leave-settings-page .stat-card:first-of-type h3,
    body[data-bs-theme="dark"] .leave-settings-page .stat-card:first-of-type h3,
    [data-pms-theme="dark"] .leave-settings-page .stat-card:first-of-type h3,
    [data-theme="dark"] .leave-settings-page .stat-card:first-of-type h3,
    [data-bs-theme="dark"] .leave-settings-page .stat-card:first-of-type h3,
    .dark-mode .stat-card h3 {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .leave-settings-page .stat-card h6,
    html[data-theme="dark"] .leave-settings-page .stat-card h6,
    html[data-bs-theme="dark"] .leave-settings-page .stat-card h6,
    body[data-pms-theme="dark"] .leave-settings-page .stat-card h6,
    body[data-theme="dark"] .leave-settings-page .stat-card h6,
    body[data-bs-theme="dark"] .leave-settings-page .stat-card h6,
    [data-pms-theme="dark"] .leave-settings-page .stat-card h6,
    [data-theme="dark"] .leave-settings-page .stat-card h6,
    [data-bs-theme="dark"] .leave-settings-page .stat-card h6,
    html[data-pms-theme="dark"] .leave-settings-page .stat-card:first-of-type h6,
    html[data-theme="dark"] .leave-settings-page .stat-card:first-of-type h6,
    html[data-bs-theme="dark"] .leave-settings-page .stat-card:first-of-type h6,
    body[data-pms-theme="dark"] .leave-settings-page .stat-card:first-of-type h6,
    body[data-theme="dark"] .leave-settings-page .stat-card:first-of-type h6,
    body[data-bs-theme="dark"] .leave-settings-page .stat-card:first-of-type h6,
    [data-pms-theme="dark"] .leave-settings-page .stat-card:first-of-type h6,
    [data-theme="dark"] .leave-settings-page .stat-card:first-of-type h6,
    [data-bs-theme="dark"] .leave-settings-page .stat-card:first-of-type h6,
    .dark-mode .stat-card h6 {
        color: #94a3b8 !important;
        -webkit-text-fill-color: #94a3b8 !important;
    }

    /* Stat Card Icons in Dark Mode */
    html[data-pms-theme="dark"] .stat-icon.casual,
    html[data-theme="dark"] .stat-icon.casual,
    html[data-bs-theme="dark"] .stat-icon.casual,
    body[data-pms-theme="dark"] .stat-icon.casual,
    body[data-theme="dark"] .stat-icon.casual,
    body[data-bs-theme="dark"] .stat-icon.casual,
    [data-pms-theme="dark"] .stat-icon.casual,
    [data-theme="dark"] .stat-icon.casual,
    [data-bs-theme="dark"] .stat-icon.casual,
    .dark-mode .stat-icon.casual {
        background: rgba(47, 107, 255, 0.25) !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.casual i,
    html[data-pms-theme="dark"] .stat-icon.casual svg,
    html[data-pms-theme="dark"] .stat-icon.casual [class*="fa"],
    html[data-theme="dark"] .stat-icon.casual i,
    html[data-theme="dark"] .stat-icon.casual svg,
    html[data-theme="dark"] .stat-icon.casual [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.casual i,
    html[data-bs-theme="dark"] .stat-icon.casual svg,
    html[data-bs-theme="dark"] .stat-icon.casual [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.casual i,
    body[data-theme="dark"] .stat-icon.casual i,
    body[data-bs-theme="dark"] .stat-icon.casual i,
    [data-pms-theme="dark"] .stat-icon.casual i,
    [data-theme="dark"] .stat-icon.casual i,
    [data-bs-theme="dark"] .stat-icon.casual i,
    .dark-mode .stat-icon.casual i,
    .dark-mode .stat-icon.casual svg,
    .dark-mode .stat-icon.casual [class*="fa"],
    [data-pms-theme="dark"] .leave-settings-page .stat-card .stat-icon.casual i,
    [data-pms-theme="dark"] .leave-settings-page .stat-card:first-of-type .stat-icon.casual i,
    [data-theme="dark"] .leave-settings-page .stat-card .stat-icon.casual i,
    [data-theme="dark"] .leave-settings-page .stat-card:first-of-type .stat-icon.casual i,
    [data-bs-theme="dark"] .leave-settings-page .stat-card .stat-icon.casual i,
    [data-bs-theme="dark"] .leave-settings-page .stat-card:first-of-type .stat-icon.casual i,
    .dark-mode .leave-settings-page .stat-card .stat-icon.casual i,
    .dark-mode .leave-settings-page .stat-card:first-of-type .stat-icon.casual i {
        background: transparent !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    html[data-pms-theme="dark"] .stat-icon.sick,
    html[data-theme="dark"] .stat-icon.sick,
    html[data-bs-theme="dark"] .stat-icon.sick,
    body[data-pms-theme="dark"] .stat-icon.sick,
    body[data-theme="dark"] .stat-icon.sick,
    body[data-bs-theme="dark"] .stat-icon.sick,
    [data-pms-theme="dark"] .stat-icon.sick,
    [data-theme="dark"] .stat-icon.sick,
    [data-bs-theme="dark"] .stat-icon.sick,
    .dark-mode .stat-icon.sick {
        background: rgba(56, 189, 248, 0.25) !important;
        border: 1px solid rgba(56, 189, 248, 0.35) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.sick i,
    html[data-pms-theme="dark"] .stat-icon.sick svg,
    html[data-pms-theme="dark"] .stat-icon.sick [class*="fa"],
    html[data-theme="dark"] .stat-icon.sick i,
    html[data-theme="dark"] .stat-icon.sick svg,
    html[data-theme="dark"] .stat-icon.sick [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.sick i,
    html[data-bs-theme="dark"] .stat-icon.sick svg,
    html[data-bs-theme="dark"] .stat-icon.sick [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.sick i,
    body[data-theme="dark"] .stat-icon.sick i,
    body[data-bs-theme="dark"] .stat-icon.sick i,
    [data-pms-theme="dark"] .stat-icon.sick i,
    [data-theme="dark"] .stat-icon.sick i,
    [data-bs-theme="dark"] .stat-icon.sick i,
    .dark-mode .stat-icon.sick i,
    .dark-mode .stat-icon.sick svg,
    .dark-mode .stat-icon.sick [class*="fa"],
    [data-pms-theme="dark"] .leave-settings-page .stat-card .stat-icon.sick i,
    [data-theme="dark"] .leave-settings-page .stat-card .stat-icon.sick i,
    [data-bs-theme="dark"] .leave-settings-page .stat-card .stat-icon.sick i,
    .dark-mode .leave-settings-page .stat-card .stat-icon.sick i {
        background: transparent !important;
        color: #38bdf8 !important;
        -webkit-text-fill-color: #38bdf8 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    html[data-pms-theme="dark"] .stat-icon.carry,
    html[data-theme="dark"] .stat-icon.carry,
    html[data-bs-theme="dark"] .stat-icon.carry,
    body[data-pms-theme="dark"] .stat-icon.carry,
    body[data-theme="dark"] .stat-icon.carry,
    body[data-bs-theme="dark"] .stat-icon.carry,
    [data-pms-theme="dark"] .stat-icon.carry,
    [data-theme="dark"] .stat-icon.carry,
    [data-bs-theme="dark"] .stat-icon.carry,
    .dark-mode .stat-icon.carry {
        background: rgba(251, 191, 36, 0.25) !important;
        border: 1px solid rgba(251, 191, 36, 0.35) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.carry i,
    html[data-pms-theme="dark"] .stat-icon.carry svg,
    html[data-pms-theme="dark"] .stat-icon.carry [class*="fa"],
    html[data-theme="dark"] .stat-icon.carry i,
    html[data-theme="dark"] .stat-icon.carry svg,
    html[data-theme="dark"] .stat-icon.carry [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.carry i,
    html[data-bs-theme="dark"] .stat-icon.carry svg,
    html[data-bs-theme="dark"] .stat-icon.carry [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.carry i,
    body[data-theme="dark"] .stat-icon.carry i,
    body[data-bs-theme="dark"] .stat-icon.carry i,
    [data-pms-theme="dark"] .stat-icon.carry i,
    [data-theme="dark"] .stat-icon.carry i,
    [data-bs-theme="dark"] .stat-icon.carry i,
    .dark-mode .stat-icon.carry i,
    .dark-mode .stat-icon.carry svg,
    .dark-mode .stat-icon.carry [class*="fa"],
    [data-pms-theme="dark"] .leave-settings-page .stat-card .stat-icon.carry i,
    [data-theme="dark"] .leave-settings-page .stat-card .stat-icon.carry i,
    [data-bs-theme="dark"] .leave-settings-page .stat-card .stat-icon.carry i,
    .dark-mode .leave-settings-page .stat-card .stat-icon.carry i {
        background: transparent !important;
        color: #fbbf24 !important;
        -webkit-text-fill-color: #fbbf24 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    html[data-pms-theme="dark"] .stat-icon.policy,
    html[data-theme="dark"] .stat-icon.policy,
    html[data-bs-theme="dark"] .stat-icon.policy,
    body[data-pms-theme="dark"] .stat-icon.policy,
    body[data-theme="dark"] .stat-icon.policy,
    body[data-bs-theme="dark"] .stat-icon.policy,
    [data-pms-theme="dark"] .stat-icon.policy,
    [data-theme="dark"] .stat-icon.policy,
    [data-bs-theme="dark"] .stat-icon.policy,
    .dark-mode .stat-icon.policy {
        background: rgba(129, 140, 248, 0.25) !important;
        border: 1px solid rgba(129, 140, 248, 0.35) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.policy i,
    html[data-pms-theme="dark"] .stat-icon.policy svg,
    html[data-pms-theme="dark"] .stat-icon.policy [class*="fa"],
    html[data-theme="dark"] .stat-icon.policy i,
    html[data-theme="dark"] .stat-icon.policy svg,
    html[data-theme="dark"] .stat-icon.policy [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.policy i,
    html[data-bs-theme="dark"] .stat-icon.policy svg,
    html[data-bs-theme="dark"] .stat-icon.policy [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.policy i,
    body[data-theme="dark"] .stat-icon.policy i,
    body[data-bs-theme="dark"] .stat-icon.policy i,
    [data-pms-theme="dark"] .stat-icon.policy i,
    [data-theme="dark"] .stat-icon.policy i,
    [data-bs-theme="dark"] .stat-icon.policy i,
    .dark-mode .stat-icon.policy i,
    .dark-mode .stat-icon.policy svg,
    .dark-mode .stat-icon.policy [class*="fa"],
    [data-pms-theme="dark"] .leave-settings-page .stat-card .stat-icon.policy i,
    [data-theme="dark"] .leave-settings-page .stat-card .stat-icon.policy i,
    [data-bs-theme="dark"] .leave-settings-page .stat-card .stat-icon.policy i,
    .dark-mode .leave-settings-page .stat-card .stat-icon.policy i {
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
    [data-pms-theme="dark"] .input-group-custom {
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
    [data-pms-theme="dark"] .input-group-custom .form-select {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
        background-color: transparent !important;
    }

    html[data-pms-theme="dark"] .btn-manage-requests,
    html[data-theme="dark"] .btn-manage-requests,
    html[data-bs-theme="dark"] .btn-manage-requests,
    body[data-pms-theme="dark"] .btn-manage-requests,
    body[data-theme="dark"] .btn-manage-requests,
    body[data-bs-theme="dark"] .btn-manage-requests,
    [data-pms-theme="dark"] .btn-manage-requests,
    [data-theme="dark"] .btn-manage-requests,
    [data-bs-theme="dark"] .btn-manage-requests,
    .dark-mode .btn-manage-requests,
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

    html[data-pms-theme="dark"] .btn-manage-requests i,
    html[data-pms-theme="dark"] .btn-manage-requests svg,
    html[data-pms-theme="dark"] .btn-manage-requests [class*="fa"],
    html[data-theme="dark"] .btn-manage-requests i,
    html[data-theme="dark"] .btn-manage-requests svg,
    html[data-theme="dark"] .btn-manage-requests [class*="fa"],
    html[data-bs-theme="dark"] .btn-manage-requests i,
    html[data-bs-theme="dark"] .btn-manage-requests svg,
    html[data-bs-theme="dark"] .btn-manage-requests [class*="fa"],
    body[data-pms-theme="dark"] .btn-manage-requests i,
    body[data-theme="dark"] .btn-manage-requests i,
    body[data-bs-theme="dark"] .btn-manage-requests i,
    [data-pms-theme="dark"] .btn-manage-requests i,
    [data-theme="dark"] .btn-manage-requests i,
    [data-bs-theme="dark"] .btn-manage-requests i,
    .dark-mode .btn-manage-requests i,
    .dark-mode .btn-manage-requests svg,
    .dark-mode .btn-manage-requests [class*="fa"],
    html[data-pms-theme="dark"] .btn-back-settings i,
    html[data-pms-theme="dark"] .btn-back-settings svg,
    html[data-pms-theme="dark"] .btn-back-settings [class*="fa"],
    html[data-theme="dark"] .btn-back-settings i,
    html[data-theme="dark"] .btn-back-settings svg,
    html[data-theme="dark"] .btn-back-settings [class*="fa"],
    html[data-bs-theme="dark"] .btn-back-settings i,
    html[data-bs-theme="dark"] .btn-back-settings svg,
    html[data-bs-theme="dark"] .btn-back-settings [class*="fa"],
    body[data-pms-theme="dark"] .btn-back-settings i,
    body[data-theme="dark"] .btn-back-settings i,
    body[data-bs-theme="dark"] .btn-back-settings i,
    [data-pms-theme="dark"] .btn-back-settings i,
    [data-theme="dark"] .btn-back-settings i,
    [data-bs-theme="dark"] .btn-back-settings i,
    .dark-mode .btn-back-settings i,
    .dark-mode .btn-back-settings svg,
    .dark-mode .btn-back-settings [class*="fa"] {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }
</style>
@endpush

@section('content')
<div class="leave-settings-page">
    <div class="leave-settings-shell">
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
                <span>Leave Policies & Allowances</span>
            </div>

            <!-- Page Header Card -->
            <div class="branches-header">
                <div class="header-left-box">
                    <div class="header-icon-badge">
                        <i class="fas fa-plane-departure"></i>
                    </div>
                    <div class="header-title">
                        <h1>Leave Policies & Allowances</h1>
                        <p>Configure default annual allowances, leave encashment, approval workflows, and carry-forward rules.</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2.5">
                    @if(Route::has('leaves.index'))
                    <a href="{{ route('leaves.index') }}" class="btn-manage-requests">
                        <i class="fas fa-calendar-alt me-1"></i> Manage Leave Requests
                    </a>
                    @endif

                    <a href="{{ route('admin.settings.index') }}" class="btn-back-settings">
                        <i class="fas fa-arrow-left me-1 back-arrow-icon"></i> Back to Settings
                    </a>
                </div>
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
                $casualDays = $settings['annual_casual_leave'] ?? '14';
                $sickDays = $settings['annual_sick_leave'] ?? '10';
                $carryDays = $settings['carry_forward_limit'] ?? '5';
                $approvalReq = ($settings['require_approval'] ?? '1') == '1';
            @endphp
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon casual">
                        <i class="fas fa-sun"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Casual Leave</h6>
                        <h3>{{ $casualDays }} Days / Year</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon sick">
                        <i class="fas fa-notes-medical"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Sick Leave</h6>
                        <h3>{{ $sickDays }} Days / Year</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon carry">
                        <i class="fas fa-redo-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Carry Forward</h6>
                        <h3>Max {{ $carryDays }} Days</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon policy">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Approval Workflow</h6>
                        <h3>{{ $approvalReq ? 'Manager Approval' : 'Auto Approve' }}</h3>
                    </div>
                </div>
            </div>

            <!-- Main Leave Settings Form Card -->
            <div class="address-card-elevated">
                <div class="card-header-custom">
                    <div class="card-header-avatar shadow-sm">
                        <i class="fas fa-business-time"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold fs-5" style="color: #0F172A;">Global Leave Allocation Rules</h5>
                        <small class="text-muted">Set standard annual leave allowances per employee</small>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    @if($isSettingsReadOnly)
                        <div class="alert d-flex align-items-center mb-4 rounded-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #1e40af; border-left: 4px solid #3b82f6 !important; padding: 14px 18px;">
                            <i class="fas fa-eye me-3 fs-3 text-primary"></i>
                            <div>
                                <strong class="d-block text-primary fw-bold" style="font-size: 14px;">View-Only Mode</strong>
                                <span style="font-size: 13px; color: #1e3a8a;">You are viewing leave policy settings in read-only mode. Only Administrators have permission to modify leave allowances and policy workflows.</span>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.settings.leave.update') }}">
                        @csrf

                        <!-- Section 1: Allowances -->
                        <div class="section-badge">
                            <i class="fas fa-calculator"></i> Annual Allowances & Carry Forward
                        </div>

                        <div class="row g-4 mb-5">
                            <!-- Casual Leave Allowance -->
                            <div class="col-md-4">
                                <label class="form-label-custom">Annual Casual Leave Days <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-sun"></i></span>
                                    <input type="number" name="annual_casual_leave" class="form-control" min="0" max="365"
                                        value="{{ old('annual_casual_leave', $settings['annual_casual_leave'] ?? '14') }}"
                                        {{ $isSettingsReadOnly ? 'readonly' : 'required' }}>
                                </div>
                            </div>

                            <!-- Sick Leave Allowance -->
                            <div class="col-md-4">
                                <label class="form-label-custom">Annual Sick Leave Days <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-notes-medical"></i></span>
                                    <input type="number" name="annual_sick_leave" class="form-control" min="0" max="365"
                                        value="{{ old('annual_sick_leave', $settings['annual_sick_leave'] ?? '10') }}"
                                        {{ $isSettingsReadOnly ? 'readonly' : 'required' }}>
                                </div>
                            </div>

                            <!-- Carry Forward Limit -->
                            <div class="col-md-4">
                                <label class="form-label-custom">Max Carry-Forward Days <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-redo-alt"></i></span>
                                    <input type="number" name="carry_forward_limit" class="form-control" min="0" max="100"
                                        value="{{ old('carry_forward_limit', $settings['carry_forward_limit'] ?? '5') }}"
                                        {{ $isSettingsReadOnly ? 'readonly' : 'required' }}>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Policy Rules & Workflows -->
                        <div class="section-badge">
                            <i class="fas fa-user-shield"></i> Policy Controls & Workflow Rules
                        </div>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="switch-box-container">
                                    <div class="form-check form-switch mb-0 d-flex align-items-center gap-3">
                                        <input class="form-check-input ms-0" type="checkbox" name="require_approval" value="1" id="approvalSwitch"
                                            style="width: 44px; height: 24px; cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }};"
                                            {{ ($settings['require_approval'] ?? '1') == '1' ? 'checked' : '' }}
                                            {{ $isSettingsReadOnly ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="approvalSwitch" style="font-size: 0.9rem; cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }};">
                                            Require Admin / Manager Approval
                                            <small class="d-block text-muted fw-normal" style="font-size: 0.82rem;">All leave applications must be manually approved by supervisor.</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="switch-box-container">
                                    <div class="form-check form-switch mb-0 d-flex align-items-center gap-3">
                                        <input class="form-check-input ms-0" type="checkbox" name="probation_leave_allowed" value="1" id="probationSwitch"
                                            style="width: 44px; height: 24px; cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }};"
                                            {{ ($settings['probation_leave_allowed'] ?? '0') == '1' ? 'checked' : '' }}
                                            {{ $isSettingsReadOnly ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="probationSwitch" style="font-size: 0.9rem; cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }};">
                                            Allow Leave During Probation
                                            <small class="d-block text-muted fw-normal" style="font-size: 0.82rem;">Permit newly joined employees to apply for paid leave.</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="switch-box-container">
                                    <div class="form-check form-switch mb-0 d-flex align-items-center gap-3">
                                        <input class="form-check-input ms-0" type="checkbox" name="enable_encashment" value="1" id="encashmentSwitch"
                                            style="width: 44px; height: 24px; cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }};"
                                            {{ ($settings['enable_encashment'] ?? '0') == '1' ? 'checked' : '' }}
                                            {{ $isSettingsReadOnly ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="encashmentSwitch" style="font-size: 0.9rem; cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }};">
                                            Enable Leave Encashment
                                            <small class="d-block text-muted fw-normal" style="font-size: 0.82rem;">Employees can request payout for unused leave balance.</small>
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
                                    <i class="fas fa-save me-1.5"></i> Save Leave Settings
                                </button>
                            @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
