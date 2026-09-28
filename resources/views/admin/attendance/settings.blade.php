@extends('admin.layout.app')

@section('title', 'Attendance Settings')

@push('styles')
<style>
    .attendance-settings-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #F8FAFC 0%, #EEF2FF 50%, #F8FAFC 100%);
        color: #0F172A;
    }

    .attendance-settings-shell {
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
    .attendance-settings-page .stat-card,
    .attendance-settings-page .stat-card:first-of-type {
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

    .attendance-settings-page .stat-card:first-of-type *,
    .attendance-settings-page .stat-card * {
        -webkit-text-fill-color: initial;
    }

    .attendance-settings-page .stat-card h3,
    .attendance-settings-page .stat-card:first-of-type h3 {
        color: #0F172A !important;
        -webkit-text-fill-color: #0F172A !important;
    }

    .attendance-settings-page .stat-card h6,
    .attendance-settings-page .stat-card span,
    .attendance-settings-page .stat-card:first-of-type span,
    .attendance-settings-page .stat-card:first-of-type h6 {
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

    .stat-icon.start {
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF) !important;
        border: 1px solid rgba(47, 107, 255, 0.25) !important;
    }

    .stat-icon.start i,
    .stat-icon.start svg,
    .stat-icon.start [class*="fa"],
    .attendance-settings-page .stat-card .stat-icon.start i,
    .attendance-settings-page .stat-card:first-of-type .stat-icon.start i {
        background: transparent !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .stat-icon.late {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        border: 1px solid rgba(56, 189, 248, 0.25) !important;
    }

    .stat-icon.late i,
    .stat-icon.late svg,
    .stat-icon.late [class*="fa"],
    .attendance-settings-page .stat-card .stat-icon.late i {
        background: transparent !important;
        color: #0369a1 !important;
        -webkit-text-fill-color: #0369a1 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .stat-icon.halfday {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        border: 1px solid rgba(251, 191, 36, 0.25) !important;
    }

    .stat-icon.halfday i,
    .stat-icon.halfday svg,
    .stat-icon.halfday [class*="fa"],
    .attendance-settings-page .stat-card .stat-icon.halfday i {
        background: transparent !important;
        color: #b45309 !important;
        -webkit-text-fill-color: #b45309 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .stat-icon.dayoff {
        background: linear-gradient(145deg, #e0e7ff, #c7d2fe) !important;
        border: 1px solid rgba(129, 140, 248, 0.25) !important;
    }

    .stat-icon.dayoff i,
    .stat-icon.dayoff svg,
    .stat-icon.dayoff [class*="fa"],
    .attendance-settings-page .stat-card .stat-icon.dayoff i {
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
    html[data-pms-theme="dark"] .attendance-settings-page,
    html[data-theme="dark"] .attendance-settings-page,
    html[data-bs-theme="dark"] .attendance-settings-page,
    body[data-pms-theme="dark"] .attendance-settings-page,
    body[data-theme="dark"] .attendance-settings-page,
    body[data-bs-theme="dark"] .attendance-settings-page,
    [data-pms-theme="dark"] .attendance-settings-page,
    [data-theme="dark"] .attendance-settings-page,
    [data-bs-theme="dark"] .attendance-settings-page,
    .dark-mode .attendance-settings-page {
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
    html[data-pms-theme="dark"] .attendance-settings-page .stat-card,
    html[data-theme="dark"] .attendance-settings-page .stat-card,
    html[data-bs-theme="dark"] .attendance-settings-page .stat-card,
    body[data-pms-theme="dark"] .attendance-settings-page .stat-card,
    body[data-theme="dark"] .attendance-settings-page .stat-card,
    body[data-bs-theme="dark"] .attendance-settings-page .stat-card,
    [data-pms-theme="dark"] .attendance-settings-page .stat-card,
    [data-theme="dark"] .attendance-settings-page .stat-card,
    [data-bs-theme="dark"] .attendance-settings-page .stat-card,
    html[data-pms-theme="dark"] .attendance-settings-page .stat-card:first-of-type,
    html[data-theme="dark"] .attendance-settings-page .stat-card:first-of-type,
    html[data-bs-theme="dark"] .attendance-settings-page .stat-card:first-of-type,
    body[data-pms-theme="dark"] .attendance-settings-page .stat-card:first-of-type,
    body[data-theme="dark"] .attendance-settings-page .stat-card:first-of-type,
    body[data-bs-theme="dark"] .attendance-settings-page .stat-card:first-of-type,
    [data-pms-theme="dark"] .attendance-settings-page .stat-card:first-of-type,
    [data-theme="dark"] .attendance-settings-page .stat-card:first-of-type,
    [data-bs-theme="dark"] .attendance-settings-page .stat-card:first-of-type,
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

    html[data-pms-theme="dark"] .attendance-settings-page .stat-card h3,
    html[data-theme="dark"] .attendance-settings-page .stat-card h3,
    html[data-bs-theme="dark"] .attendance-settings-page .stat-card h3,
    body[data-pms-theme="dark"] .attendance-settings-page .stat-card h3,
    body[data-theme="dark"] .attendance-settings-page .stat-card h3,
    body[data-bs-theme="dark"] .attendance-settings-page .stat-card h3,
    [data-pms-theme="dark"] .attendance-settings-page .stat-card h3,
    [data-theme="dark"] .attendance-settings-page .stat-card h3,
    [data-bs-theme="dark"] .attendance-settings-page .stat-card h3,
    html[data-pms-theme="dark"] .attendance-settings-page .stat-card:first-of-type h3,
    html[data-theme="dark"] .attendance-settings-page .stat-card:first-of-type h3,
    html[data-bs-theme="dark"] .attendance-settings-page .stat-card:first-of-type h3,
    body[data-pms-theme="dark"] .attendance-settings-page .stat-card:first-of-type h3,
    body[data-theme="dark"] .attendance-settings-page .stat-card:first-of-type h3,
    body[data-bs-theme="dark"] .attendance-settings-page .stat-card:first-of-type h3,
    [data-pms-theme="dark"] .attendance-settings-page .stat-card:first-of-type h3,
    [data-theme="dark"] .attendance-settings-page .stat-card:first-of-type h3,
    [data-bs-theme="dark"] .attendance-settings-page .stat-card:first-of-type h3,
    .dark-mode .stat-card h3 {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .attendance-settings-page .stat-card h6,
    html[data-theme="dark"] .attendance-settings-page .stat-card h6,
    html[data-bs-theme="dark"] .attendance-settings-page .stat-card h6,
    body[data-pms-theme="dark"] .attendance-settings-page .stat-card h6,
    body[data-theme="dark"] .attendance-settings-page .stat-card h6,
    body[data-bs-theme="dark"] .attendance-settings-page .stat-card h6,
    [data-pms-theme="dark"] .attendance-settings-page .stat-card h6,
    [data-theme="dark"] .attendance-settings-page .stat-card h6,
    [data-bs-theme="dark"] .attendance-settings-page .stat-card h6,
    html[data-pms-theme="dark"] .attendance-settings-page .stat-card:first-of-type h6,
    html[data-theme="dark"] .attendance-settings-page .stat-card:first-of-type h6,
    html[data-bs-theme="dark"] .attendance-settings-page .stat-card:first-of-type h6,
    body[data-pms-theme="dark"] .attendance-settings-page .stat-card:first-of-type h6,
    body[data-theme="dark"] .attendance-settings-page .stat-card:first-of-type h6,
    body[data-bs-theme="dark"] .attendance-settings-page .stat-card:first-of-type h6,
    [data-pms-theme="dark"] .attendance-settings-page .stat-card:first-of-type h6,
    [data-theme="dark"] .attendance-settings-page .stat-card:first-of-type h6,
    [data-bs-theme="dark"] .attendance-settings-page .stat-card:first-of-type h6,
    .dark-mode .stat-card h6 {
        color: #94a3b8 !important;
        -webkit-text-fill-color: #94a3b8 !important;
    }

    /* Stat Card Icons in Dark Mode */
    html[data-pms-theme="dark"] .stat-icon.start,
    html[data-theme="dark"] .stat-icon.start,
    html[data-bs-theme="dark"] .stat-icon.start,
    body[data-pms-theme="dark"] .stat-icon.start,
    body[data-theme="dark"] .stat-icon.start,
    body[data-bs-theme="dark"] .stat-icon.start,
    [data-pms-theme="dark"] .stat-icon.start,
    [data-theme="dark"] .stat-icon.start,
    [data-bs-theme="dark"] .stat-icon.start,
    .dark-mode .stat-icon.start {
        background: rgba(47, 107, 255, 0.25) !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.start i,
    html[data-pms-theme="dark"] .stat-icon.start svg,
    html[data-pms-theme="dark"] .stat-icon.start [class*="fa"],
    html[data-theme="dark"] .stat-icon.start i,
    html[data-theme="dark"] .stat-icon.start svg,
    html[data-theme="dark"] .stat-icon.start [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.start i,
    html[data-bs-theme="dark"] .stat-icon.start svg,
    html[data-bs-theme="dark"] .stat-icon.start [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.start i,
    body[data-theme="dark"] .stat-icon.start i,
    body[data-bs-theme="dark"] .stat-icon.start i,
    [data-pms-theme="dark"] .stat-icon.start i,
    [data-theme="dark"] .stat-icon.start i,
    [data-bs-theme="dark"] .stat-icon.start i,
    .dark-mode .stat-icon.start i,
    .dark-mode .stat-icon.start svg,
    .dark-mode .stat-icon.start [class*="fa"],
    [data-pms-theme="dark"] .attendance-settings-page .stat-card .stat-icon.start i,
    [data-pms-theme="dark"] .attendance-settings-page .stat-card:first-of-type .stat-icon.start i,
    [data-theme="dark"] .attendance-settings-page .stat-card .stat-icon.start i,
    [data-theme="dark"] .attendance-settings-page .stat-card:first-of-type .stat-icon.start i,
    [data-bs-theme="dark"] .attendance-settings-page .stat-card .stat-icon.start i,
    [data-bs-theme="dark"] .attendance-settings-page .stat-card:first-of-type .stat-icon.start i,
    .dark-mode .attendance-settings-page .stat-card .stat-icon.start i,
    .dark-mode .attendance-settings-page .stat-card:first-of-type .stat-icon.start i {
        background: transparent !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    html[data-pms-theme="dark"] .stat-icon.late,
    html[data-theme="dark"] .stat-icon.late,
    html[data-bs-theme="dark"] .stat-icon.late,
    body[data-pms-theme="dark"] .stat-icon.late,
    body[data-theme="dark"] .stat-icon.late,
    body[data-bs-theme="dark"] .stat-icon.late,
    [data-pms-theme="dark"] .stat-icon.late,
    [data-theme="dark"] .stat-icon.late,
    [data-bs-theme="dark"] .stat-icon.late,
    .dark-mode .stat-icon.late {
        background: rgba(56, 189, 248, 0.25) !important;
        border: 1px solid rgba(56, 189, 248, 0.35) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.late i,
    html[data-pms-theme="dark"] .stat-icon.late svg,
    html[data-pms-theme="dark"] .stat-icon.late [class*="fa"],
    html[data-theme="dark"] .stat-icon.late i,
    html[data-theme="dark"] .stat-icon.late svg,
    html[data-theme="dark"] .stat-icon.late [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.late i,
    html[data-bs-theme="dark"] .stat-icon.late svg,
    html[data-bs-theme="dark"] .stat-icon.late [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.late i,
    body[data-theme="dark"] .stat-icon.late i,
    body[data-bs-theme="dark"] .stat-icon.late i,
    [data-pms-theme="dark"] .stat-icon.late i,
    [data-theme="dark"] .stat-icon.late i,
    [data-bs-theme="dark"] .stat-icon.late i,
    .dark-mode .stat-icon.late i,
    .dark-mode .stat-icon.late svg,
    .dark-mode .stat-icon.late [class*="fa"],
    [data-pms-theme="dark"] .attendance-settings-page .stat-card .stat-icon.late i,
    [data-theme="dark"] .attendance-settings-page .stat-card .stat-icon.late i,
    [data-bs-theme="dark"] .attendance-settings-page .stat-card .stat-icon.late i,
    .dark-mode .attendance-settings-page .stat-card .stat-icon.late i {
        background: transparent !important;
        color: #38bdf8 !important;
        -webkit-text-fill-color: #38bdf8 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    html[data-pms-theme="dark"] .stat-icon.halfday,
    html[data-theme="dark"] .stat-icon.halfday,
    html[data-bs-theme="dark"] .stat-icon.halfday,
    body[data-pms-theme="dark"] .stat-icon.halfday,
    body[data-theme="dark"] .stat-icon.halfday,
    body[data-bs-theme="dark"] .stat-icon.halfday,
    [data-pms-theme="dark"] .stat-icon.halfday,
    [data-theme="dark"] .stat-icon.halfday,
    [data-bs-theme="dark"] .stat-icon.halfday,
    .dark-mode .stat-icon.halfday {
        background: rgba(251, 191, 36, 0.25) !important;
        border: 1px solid rgba(251, 191, 36, 0.35) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.halfday i,
    html[data-pms-theme="dark"] .stat-icon.halfday svg,
    html[data-pms-theme="dark"] .stat-icon.halfday [class*="fa"],
    html[data-theme="dark"] .stat-icon.halfday i,
    html[data-theme="dark"] .stat-icon.halfday svg,
    html[data-theme="dark"] .stat-icon.halfday [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.halfday i,
    html[data-bs-theme="dark"] .stat-icon.halfday svg,
    html[data-bs-theme="dark"] .stat-icon.halfday [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.halfday i,
    body[data-theme="dark"] .stat-icon.halfday i,
    body[data-bs-theme="dark"] .stat-icon.halfday i,
    [data-pms-theme="dark"] .stat-icon.halfday i,
    [data-theme="dark"] .stat-icon.halfday i,
    [data-bs-theme="dark"] .stat-icon.halfday i,
    .dark-mode .stat-icon.halfday i,
    .dark-mode .stat-icon.halfday svg,
    .dark-mode .stat-icon.halfday [class*="fa"],
    [data-pms-theme="dark"] .attendance-settings-page .stat-card .stat-icon.halfday i,
    [data-theme="dark"] .attendance-settings-page .stat-card .stat-icon.halfday i,
    [data-bs-theme="dark"] .attendance-settings-page .stat-card .stat-icon.halfday i,
    .dark-mode .attendance-settings-page .stat-card .stat-icon.halfday i {
        background: transparent !important;
        color: #fbbf24 !important;
        -webkit-text-fill-color: #fbbf24 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    html[data-pms-theme="dark"] .stat-icon.dayoff,
    html[data-theme="dark"] .stat-icon.dayoff,
    html[data-bs-theme="dark"] .stat-icon.dayoff,
    body[data-pms-theme="dark"] .stat-icon.dayoff,
    body[data-theme="dark"] .stat-icon.dayoff,
    body[data-bs-theme="dark"] .stat-icon.dayoff,
    [data-pms-theme="dark"] .stat-icon.dayoff,
    [data-theme="dark"] .stat-icon.dayoff,
    [data-bs-theme="dark"] .stat-icon.dayoff,
    .dark-mode .stat-icon.dayoff {
        background: rgba(129, 140, 248, 0.25) !important;
        border: 1px solid rgba(129, 140, 248, 0.35) !important;
    }

    html[data-pms-theme="dark"] .stat-icon.dayoff i,
    html[data-pms-theme="dark"] .stat-icon.dayoff svg,
    html[data-pms-theme="dark"] .stat-icon.dayoff [class*="fa"],
    html[data-theme="dark"] .stat-icon.dayoff i,
    html[data-theme="dark"] .stat-icon.dayoff svg,
    html[data-theme="dark"] .stat-icon.dayoff [class*="fa"],
    html[data-bs-theme="dark"] .stat-icon.dayoff i,
    html[data-bs-theme="dark"] .stat-icon.dayoff svg,
    html[data-bs-theme="dark"] .stat-icon.dayoff [class*="fa"],
    body[data-pms-theme="dark"] .stat-icon.dayoff i,
    body[data-theme="dark"] .stat-icon.dayoff i,
    body[data-bs-theme="dark"] .stat-icon.dayoff i,
    [data-pms-theme="dark"] .stat-icon.dayoff i,
    [data-theme="dark"] .stat-icon.dayoff i,
    [data-bs-theme="dark"] .stat-icon.dayoff i,
    .dark-mode .stat-icon.dayoff i,
    .dark-mode .stat-icon.dayoff svg,
    .dark-mode .stat-icon.dayoff [class*="fa"],
    [data-pms-theme="dark"] .attendance-settings-page .stat-card .stat-icon.dayoff i,
    [data-theme="dark"] .attendance-settings-page .stat-card .stat-icon.dayoff i,
    [data-bs-theme="dark"] .attendance-settings-page .stat-card .stat-icon.dayoff i,
    .dark-mode .attendance-settings-page .stat-card .stat-icon.dayoff i {
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
<div class="attendance-settings-page">
    <div class="attendance-settings-shell">
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
                <span>Attendance Settings</span>
            </div>

            <!-- Page Header Card -->
            <div class="branches-header">
                <div class="header-left-box">
                    <div class="header-icon-badge">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="header-title">
                        <h1>Attendance Settings</h1>
                        <p>Configure office start time, late thresholds, half-day limits, and day-off thresholds.</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2.5">
                    @if(Route::has('attendance.index'))
                    <a href="{{ route('attendance.index') }}" class="btn-manage-requests">
                        <i class="fas fa-clock me-1"></i> Attendance Log
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

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm rounded-4 border-0" style="background: rgba(254, 226, 226, 0.95); color: #991b1b; border-left: 5px solid #ef4444 !important;" role="alert">
                    <i class="fas fa-exclamation-triangle fs-4 me-2"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Executive Summary Stats Grid -->
            @php
                $startVal = !empty($setting->office_start_time) ? date('H:i', strtotime($setting->office_start_time)) : '09:30';
                $lateVal = !empty($setting->late_time) ? date('H:i', strtotime($setting->late_time)) : '09:30';
                $startFormatted = date('g:i A', strtotime($startVal));
                $lateFormatted = date('g:i A', strtotime($lateVal));
                $halfDayMins = $setting->half_day_threshold_minutes ?? 510;
                $dayOffMins = $setting->day_off_threshold_minutes ?? 270;
            @endphp
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon start">
                        <i class="fas fa-sun"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Office Start Time</h6>
                        <h3>{{ $startFormatted }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon late">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Late Cutoff</h6>
                        <h3>{{ $lateFormatted }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon halfday">
                        <i class="fas fa-business-time"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Half-Day Threshold</h6>
                        <h3>{{ $halfDayMins }} Mins ({{ round($halfDayMins / 60, 1) }}h)</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon dayoff">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Day-Off Threshold</h6>
                        <h3>{{ $dayOffMins }} Mins ({{ round($dayOffMins / 60, 1) }}h)</h3>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="address-card-elevated">
                <div class="card-header-custom">
                    <div class="card-header-avatar shadow-sm">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold fs-5" style="color: #0F172A;">Attendance Policy Rules & Thresholds</h5>
                        <small class="text-muted">Configure company office hours and attendance calculation thresholds</small>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <form method="POST" action="{{ route('attendance.settings.update') }}">
                        @csrf

                        <!-- Section 1: Office Hours -->
                        <div class="section-badge">
                            <i class="fas fa-clock"></i> Standard Office Hours & Late Cutoff
                        </div>

                        <div class="row g-4 mb-5">
                            <!-- Office Start Time -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Office Start Time <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-sun"></i></span>
                                    <input type="time" name="office_start_time" class="form-control @error('office_start_time') is-invalid @enderror"
                                        value="{{ old('office_start_time', $startVal) }}" required>
                                </div>
                                @error('office_start_time')
                                    <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Late Time Cutoff -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Late Cutoff Time <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-user-clock"></i></span>
                                    <input type="time" name="late_time" class="form-control @error('late_time') is-invalid @enderror"
                                        value="{{ old('late_time', $lateVal) }}" required>
                                </div>
                                @error('late_time')
                                    <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Section 2: Work Duration Thresholds -->
                        <div class="section-badge">
                            <i class="fas fa-hourglass-half"></i> Attendance Evaluation Thresholds (Minutes)
                        </div>

                        <div class="row g-4">
                            <!-- Half Day Threshold -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Half-Day Threshold (Minutes) <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-stopwatch"></i></span>
                                    <input type="number" name="half_day_threshold_minutes" class="form-control @error('half_day_threshold_minutes') is-invalid @enderror"
                                        min="1" max="1440" value="{{ old('half_day_threshold_minutes', $halfDayMins) }}" required>
                                </div>
                                <small class="text-muted mt-1 d-block">Minimum work duration required to be counted as Half-Day (Default: 510 mins = 8.5 hours).</small>
                                @error('half_day_threshold_minutes')
                                    <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Day Off Threshold -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Day-Off Threshold (Minutes) <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-user-slash"></i></span>
                                    <input type="number" name="day_off_threshold_minutes" class="form-control @error('day_off_threshold_minutes') is-invalid @enderror"
                                        min="1" max="1440" value="{{ old('day_off_threshold_minutes', $dayOffMins) }}" required>
                                </div>
                                <small class="text-muted mt-1 d-block">Work duration below this threshold is marked as Absent / Day-Off (Must be lower than half-day limit, Default: 270 mins = 4.5 hours).</small>
                                @error('day_off_threshold_minutes')
                                    <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="mt-5 pt-4 border-top d-flex justify-content-end">
                            <button type="submit" class="btn-save-address">
                                <i class="fas fa-save me-1.5"></i> Save Attendance Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
