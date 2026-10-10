@extends('admin.layout.app')

@section('title', 'Edit Business Branch / Address')

@push('styles')
<style>
    .create-branch-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #F8FAFC 0%, #EEF2FF 50%, #F8FAFC 100%);
        color: #0F172A;
    }

    .create-branch-shell {
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

    /* Dark Mode Support for Back to Branches Button */
    html[data-pms-theme="dark"] .btn-back-settings,
    html[data-bs-theme="dark"] .btn-back-settings,
    body[data-pms-theme="dark"] .btn-back-settings,
    [data-pms-theme="dark"] .btn-back-settings {
        background-color: #141B3D !important;
        border-color: rgba(79, 131, 255, 0.2) !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .btn-back-settings .back-arrow-icon,
    html[data-bs-theme="dark"] .btn-back-settings .back-arrow-icon,
    body[data-pms-theme="dark"] .btn-back-settings .back-arrow-icon,
    [data-pms-theme="dark"] .btn-back-settings .back-arrow-icon {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .btn-back-settings:hover,
    html[data-bs-theme="dark"] .btn-back-settings:hover,
    body[data-pms-theme="dark"] .btn-back-settings:hover,
    [data-pms-theme="dark"] .btn-back-settings:hover {
        background-color: #2F6BFF !important;
        border-color: #2F6BFF !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .btn-back-settings:hover .back-arrow-icon,
    html[data-bs-theme="dark"] .btn-back-settings:hover .back-arrow-icon,
    body[data-pms-theme="dark"] .btn-back-settings:hover .back-arrow-icon,
    [data-pms-theme="dark"] .btn-back-settings:hover .back-arrow-icon {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    /* Default Toggle Box & Cancel Button */
    .default-toggle-box {
        background: #f0fdf4;
        border: 1px solid rgba(47, 107, 255, 0.25);
        border-radius: 16px;
        padding: 1rem 1.25rem;
        transition: all 0.25s ease;
    }

    .default-toggle-box label {
        color: #0F172A !important;
        -webkit-text-fill-color: #0F172A !important;
    }

    .default-toggle-box .text-muted {
        color: #4b5563 !important;
        -webkit-text-fill-color: #4b5563 !important;
    }

    .btn-cancel-custom {
        background: #f1f5f9;
        color: #475569 !important;
        -webkit-text-fill-color: #475569 !important;
        border: 1px solid #cbd5e1;
        transition: all 0.2s ease;
    }

    .btn-cancel-custom:hover {
        background: #e2e8f0;
        color: #1e293b !important;
    }

    /* Dark Mode Support for Default Toggle Box & Cancel Button */
    html[data-pms-theme="dark"] .default-toggle-box,
    html[data-bs-theme="dark"] .default-toggle-box,
    body[data-pms-theme="dark"] .default-toggle-box,
    [data-pms-theme="dark"] .default-toggle-box {
        background: rgba(47, 107, 255, 0.12) !important;
        border: 1px solid rgba(79, 131, 255, 0.3) !important;
    }

    html[data-pms-theme="dark"] .default-toggle-box label,
    html[data-bs-theme="dark"] .default-toggle-box label,
    body[data-pms-theme="dark"] .default-toggle-box label,
    [data-pms-theme="dark"] .default-toggle-box label {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .default-toggle-box .text-muted,
    html[data-bs-theme="dark"] .default-toggle-box .text-muted,
    body[data-pms-theme="dark"] .default-toggle-box .text-muted,
    [data-pms-theme="dark"] .default-toggle-box .text-muted {
        color: #94a3b8 !important;
        -webkit-text-fill-color: #94a3b8 !important;
    }

    html[data-pms-theme="dark"] .btn-cancel-custom,
    html[data-bs-theme="dark"] .btn-cancel-custom,
    body[data-pms-theme="dark"] .btn-cancel-custom,
    [data-pms-theme="dark"] .btn-cancel-custom {
        background: #1e293b !important;
        color: #cbd5e1 !important;
        -webkit-text-fill-color: #cbd5e1 !important;
        border: 1px solid rgba(238, 241, 251, 0.15) !important;
    }

    html[data-pms-theme="dark"] .btn-cancel-custom:hover,
    html[data-bs-theme="dark"] .btn-cancel-custom:hover,
    body[data-pms-theme="dark"] .btn-cancel-custom:hover,
    [data-pms-theme="dark"] .btn-cancel-custom:hover {
        background: #334155 !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
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
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
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

    .input-group-custom .form-control,
    .input-group-custom textarea {
        border: none;
        background-color: transparent;
        font-size: 0.92rem;
        font-weight: 600;
        color: #0F172A;
        padding-right: 18px;
    }

    .input-group-custom input.form-control {
        height: 50px;
    }

    .input-group-custom .form-control:focus {
        box-shadow: none;
        background-color: transparent;
    }

    .logo-upload-dropzone {
        border: 2px dashed rgba(47, 107, 255, 0.35);
        background-color: rgba(47, 107, 255, 0.02);
        border-radius: 20px;
        padding: 28px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .logo-upload-dropzone:hover, .logo-upload-dropzone.dragover {
        border-color: #2F6BFF;
        background-color: rgba(47, 107, 255, 0.08);
        transform: translateY(-2px);
    }

    .upload-icon-box {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF);
        color: #2F6BFF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        margin: 0 auto;
        box-shadow: 0 6px 16px -4px rgba(47, 107, 255, 0.25);
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

    @media (max-width: 768px) {
        .create-branch-page {
            padding: 1.25rem 1rem;
        }

        .card-header-custom {
            padding: 1.25rem 1.5rem;
        }
    }

    /* ===== INTL-TEL-INPUT PHONE VALIDATION STYLES ===== */
    .phone-input-container {
        position: relative;
        width: 100%;
    }

    .phone-input-shell {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
        min-height: 50px;
        border-radius: 16px;
        border: 1px solid rgba(47, 107, 255, 0.2);
        background-color: #fafefb;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 2px 14px 2px 4px;
    }

    .phone-input-shell:focus-within {
        border-color: #60A5FA;
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(79, 131, 255, 0.15);
        transform: translateY(-1px);
    }

    .phone-input-shell.is-valid-phone {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15) !important;
        background-color: #f0fdf4 !important;
    }

    .phone-input-shell.is-invalid-phone {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15) !important;
        background-color: #fef2f2 !important;
    }

    .iti {
        width: 100%;
        display: flex !important;
        align-items: center;
    }

    .iti__selected-country {
        padding: 0 10px 0 12px !important;
        height: 44px;
        border-radius: 12px;
        background: transparent;
        transition: background 0.2s ease;
        gap: 6px;
    }

    .iti__selected-country:hover {
        background-color: rgba(47, 107, 255, 0.08) !important;
    }

    .iti__selected-dial-code {
        font-size: 0.92rem;
        font-weight: 700;
        color: #2F6BFF !important;
    }

    .iti__tel-input {
        border: none !important;
        background: transparent !important;
        height: 46px !important;
        font-size: 0.92rem !important;
        font-weight: 600 !important;
        color: #0F172A !important;
        padding-left: 8px !important;
        padding-right: 32px !important;
        box-shadow: none !important;
        width: 100%;
    }

    .iti__tel-input:focus {
        outline: none !important;
        box-shadow: none !important;
    }

    .phone-status-icon {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.15rem;
        pointer-events: none;
        z-index: 5;
    }

    .phone-validation-feedback {
        border-radius: 10px;
        padding: 6px 12px;
        transition: all 0.25s ease;
        animation: fadeInPhoneFeedback 0.2s ease-in-out;
    }

    @keyframes fadeInPhoneFeedback {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes phoneShake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-6px); }
        40%, 80% { transform: translateX(6px); }
    }

    .phone-validation-feedback.valid-feedback-custom {
        background: rgba(16, 185, 129, 0.1);
        color: #065f46;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }

    .phone-validation-feedback.invalid-feedback-custom {
        background: rgba(239, 68, 68, 0.1);
        color: #991b1b;
        border: 1px solid rgba(239, 68, 68, 0.25);
    }

    .iti__dropdown-content {
        border-radius: 16px !important;
        border: 1px solid rgba(47, 107, 255, 0.2) !important;
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.15) !important;
        background: #ffffff !important;
        z-index: 1050 !important;
        max-height: 280px;
    }

    .iti__search-input {
        border-radius: 10px !important;
        border: 1px solid #cbd5e1 !important;
        padding: 8px 12px !important;
        font-size: 0.88rem !important;
        margin: 8px !important;
        width: calc(100% - 16px) !important;
    }

    .iti__country {
        padding: 8px 12px !important;
        font-size: 0.88rem !important;
        transition: background 0.15s ease;
    }

    .iti__country:hover, .iti__country.iti__highlight {
        background-color: #EEF2FF !important;
        color: #2F6BFF !important;
    }

    /* Dark Mode Overrides for Phone Input */
    html[data-pms-theme="dark"] .phone-input-shell,
    html[data-theme="dark"] .phone-input-shell,
    html[data-bs-theme="dark"] .phone-input-shell,
    [data-pms-theme="dark"] .phone-input-shell {
        background-color: #141B3D !important;
        border-color: rgba(79, 131, 255, 0.25) !important;
    }

    html[data-pms-theme="dark"] .phone-input-shell.is-valid-phone,
    html[data-theme="dark"] .phone-input-shell.is-valid-phone,
    [data-pms-theme="dark"] .phone-input-shell.is-valid-phone {
        background-color: rgba(16, 185, 129, 0.15) !important;
        border-color: #10b981 !important;
    }

    html[data-pms-theme="dark"] .phone-input-shell.is-invalid-phone,
    html[data-theme="dark"] .phone-input-shell.is-invalid-phone,
    [data-pms-theme="dark"] .phone-input-shell.is-invalid-phone {
        background-color: rgba(239, 68, 68, 0.15) !important;
        border-color: #ef4444 !important;
    }

    html[data-pms-theme="dark"] .iti__tel-input,
    html[data-theme="dark"] .iti__tel-input,
    [data-pms-theme="dark"] .iti__tel-input {
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .iti__selected-dial-code,
    html[data-theme="dark"] .iti__selected-dial-code,
    [data-pms-theme="dark"] .iti__selected-dial-code {
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .iti__dropdown-content,
    html[data-theme="dark"] .iti__dropdown-content,
    [data-pms-theme="dark"] .iti__dropdown-content {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.15) !important;
        color: #EEF1FB !important;
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.5) !important;
    }

    html[data-pms-theme="dark"] .iti__search-input,
    html[data-theme="dark"] .iti__search-input,
    [data-pms-theme="dark"] .iti__search-input {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.2) !important;
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .iti__country,
    html[data-theme="dark"] .iti__country,
    [data-pms-theme="dark"] .iti__country {
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .iti__country:hover,
    html[data-theme="dark"] .iti__country.iti__highlight,
    [data-pms-theme="dark"] .iti__country:hover,
    [data-pms-theme="dark"] .iti__country.iti__highlight {
        background-color: #1E2A52 !important;
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .iti__country-name,
    [data-pms-theme="dark"] .iti__country-name {
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .iti__dial-code,
    [data-pms-theme="dark"] .iti__dial-code {
        color: #9AA3C7 !important;
    }

    /* Primary-branch switch: self-contained sizing and colours so theme form rules cannot collapse it to a line. */
    html body .default-toggle-box .form-check.form-switch {
        flex: 0 0 auto;
        min-height: 0;
        margin: 0 !important;
        padding: 0 !important;
        display: flex;
        align-items: center;
    }
    html body .default-toggle-box .form-switch .form-check-input {
        float: none !important;
        flex: 0 0 auto;
        width: 3rem !important;
        min-width: 3rem !important;
        height: 1.6rem !important;
        margin: 0 !important;
        border-radius: 999px !important;
        border: 2px solid #94A3B8 !important;
        background-color: #E2E8F0 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%2364748B'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: left center !important;
        background-size: contain !important;
        opacity: 1 !important;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        transition: background-position .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out;
    }
    html body .default-toggle-box .form-switch .form-check-input:checked {
        border-color: #2F6BFF !important;
        background-color: #2F6BFF !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23ffffff'/%3e%3c/svg%3e") !important;
        background-position: right center !important;
    }
    html body .default-toggle-box .form-switch .form-check-input:focus-visible {
        outline: 3px solid rgba(47, 107, 255, 0.45);
        outline-offset: 2px;
        box-shadow: none !important;
    }
    html body.dark-mode .default-toggle-box .form-switch .form-check-input:not(:checked),
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) .default-toggle-box .form-switch .form-check-input:not(:checked) {
        border-color: #64748B !important;
        background-color: #1E2747 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23CBD5E1'/%3e%3c/svg%3e") !important;
    }
</style>
@endpush

@section('content')
<div class="create-branch-page">
    <div class="create-branch-shell">
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
                <a href="{{ route('admin.settings.business-address.index') }}">Branches & Locations</a>
                <span>/</span>
                <span>Edit {{ $businessAddress->display_name }}</span>
            </div>

            <!-- Page Header Card -->
            <div class="branches-header">
                <div class="header-left-box">
                    <div class="header-icon-badge">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="header-title">
                        <h1>Edit Business Branch</h1>
                        <p>Update branch details, contact info, logo, and address for {{ $businessAddress->display_name }}.</p>
                    </div>
                </div>

                <a href="{{ route('admin.settings.business-address.index') }}" class="btn-back-settings">
                    <i class="fas fa-arrow-left me-1 back-arrow-icon"></i> Back to List
                </a>
            </div>

            <!-- Form Card -->
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="address-card-elevated">
                        <div class="card-header-custom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="card-header-avatar shadow-sm">
                                    <i class="fas fa-city"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold fs-5" style="color: #0F172A;">Edit Branch Information</h5>
                                    <small class="text-muted">Modify official details for {{ $businessAddress->display_name }}</small>
                                </div>
                            </div>

                            @if($businessAddress->is_default)
                                <span class="badge rounded-pill px-3 py-2" style="background: linear-gradient(145deg, #EEF2FF, #E0E7FF); color: #065f46; border: 1px solid rgba(47, 107, 255, 0.25); font-weight: 750;">
                                    <i class="fas fa-star me-1" style="color: #2F6BFF;"></i> Current Primary Default
                                </span>
                            @endif
                        </div>

                        <div class="p-4 p-md-5">
                            <form id="branchForm" method="POST" action="{{ route('admin.settings.business-address.update', $businessAddress) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <!-- Section 1: Branch Information -->
                                <div class="section-badge">
                                    <i class="fas fa-info-circle"></i> Branch Information
                                </div>
                                <div class="row g-4 mb-4">
                                    <!-- Branch Name -->
                                    <div class="col-md-6">
                                        <label for="branch_name" class="form-label-custom">
                                            Branch Name <span class="req-asterisk">*</span>
                                        </label>
                                        <div class="input-group input-group-custom">
                                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                                            <input type="text" class="form-control @error('branch_name') is-invalid @enderror"
                                                   id="branch_name" name="branch_name"
                                                   value="{{ old('branch_name', $businessAddress->branch_name) }}"
                                                   placeholder="e.g. Kolkata Main Branch" required>
                                        </div>
                                        @error('branch_name')
                                            <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Branch Location -->
                                    <div class="col-md-6">
                                        <label for="location" class="form-label-custom">
                                            Branch Location / City <span class="req-asterisk">*</span>
                                        </label>
                                        <div class="input-group input-group-custom">
                                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                            <input type="text" class="form-control @error('location') is-invalid @enderror"
                                                   id="location" name="location"
                                                   value="{{ old('location', $businessAddress->location) }}"
                                                   placeholder="e.g. Salt Lake Sector 5, Kolkata" required>
                                        </div>
                                        @error('location')
                                            <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Branch Email -->
                                    <div class="col-md-6">
                                        <label for="email" class="form-label-custom">
                                            Branch Email
                                        </label>
                                        <div class="input-group input-group-custom">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                   id="email" name="email"
                                                   value="{{ old('email', $businessAddress->email) }}"
                                                   placeholder="kolkata@example.com">
                                        </div>
                                        @error('email')
                                            <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Branch Phone -->
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <label for="phone_input" class="form-label-custom mb-0">
                                                Branch Phone
                                            </label>
                                            <span class="badge rounded-pill px-2.5 py-1" id="countryBadge" style="background: rgba(47, 107, 255, 0.08); color: #2F6BFF; font-size: 0.75rem; font-weight: 700; border: 1px solid rgba(47, 107, 255, 0.2);">
                                                🇮🇳 India (+91)
                                            </span>
                                        </div>

                                        {{-- Hidden input submitted to server with standardized international format --}}
                                        <input type="hidden" name="phone" id="phone" value="{{ old('phone', $businessAddress->phone) }}">

                                        <div class="phone-input-container">
                                            <div class="phone-input-shell" id="phoneInputShell">
                                                <input type="tel" 
                                                       id="phone_input" 
                                                       class="form-control phone-tel-field @error('phone') is-invalid @enderror"
                                                       placeholder="98765 43210"
                                                       autocomplete="tel"
                                                       value="{{ old('phone', $businessAddress->phone) }}">
                                                <div id="phoneStatusIcon" class="phone-status-icon" style="display: none;"></div>
                                            </div>
                                        </div>

                                        <!-- Live Validation Feedback Box -->
                                        <div id="phoneValidationFeedback" class="phone-validation-feedback mt-1.5" style="display: none;"></div>

                                        <!-- Digit format guide & live counter -->
                                        <div class="d-flex align-items-center justify-content-between mt-1 text-muted" style="font-size: 0.75rem;">
                                            <span id="phoneFormatHint"><i class="fas fa-info-circle me-1 text-primary"></i> <span id="phoneHintText">Format: 10 digits for India (e.g. 98765 43210)</span></span>
                                            <span id="phoneDigitCounter" class="fw-bold" style="color: #64748b;">0 / 10 digits</span>
                                        </div>

                                        @error('phone')
                                            <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Section 2: Branch Logo -->
                                <div class="section-badge">
                                    <i class="fas fa-image"></i> Branch Logo
                                </div>
                                <div class="mb-5">
                                    @php
                                        $hasBranchLogo = !empty($businessAddress->logo) && file_exists(public_path($businessAddress->logo));
                                    @endphp
                                    <label for="branch_logo_input" class="logo-upload-dropzone d-block w-100 mb-0" id="logoDropzone">
                                        <input type="file" name="logo" id="branch_logo_input" class="d-none" accept="image/png, image/jpeg, image/gif, image/svg+xml, image/webp" onchange="previewBranchLogo(this)">
                                        
                                        <div class="d-flex flex-column align-items-center gap-2">
                                            <div class="upload-icon-box" id="logoPreviewWrapper">
                                                @if($hasBranchLogo)
                                                    <img id="logoPreviewImg" src="{{ asset($businessAddress->logo) }}" alt="Branch Logo" class="rounded-circle" style="width: 54px; height: 54px; object-fit: cover;">
                                                    <i class="fas fa-cloud-upload-alt d-none" id="logoDefaultIcon"></i>
                                                @else
                                                    <i class="fas fa-cloud-upload-alt" id="logoDefaultIcon"></i>
                                                    <img id="logoPreviewImg" src="" alt="Branch Logo Preview" class="d-none rounded-circle" style="width: 54px; height: 54px; object-fit: cover;">
                                                @endif
                                            </div>
                                            <div>
                                                <h6 class="fw-bold mb-1" style="color: #0F172A;">Click to change branch logo or drag & drop</h6>
                                                <p class="text-muted small mb-0">Supported formats: PNG, JPG, GIF, SVG, or WEBP (Max 3MB)</p>
                                            </div>

                                            @if($hasBranchLogo)
                                                <div id="currentBranchLogoBadge" class="badge rounded-pill mt-2 px-3 py-1.5" style="background: linear-gradient(145deg, #EEF2FF, #E0E7FF); color: #065f46; border: 1px solid rgba(47, 107, 255, 0.25);">
                                                    <i class="fas fa-check-circle me-1" style="color: #2F6BFF;"></i> Current logo loaded (Click to update)
                                                </div>
                                            @endif

                                            <div id="fileSelectedBadge" class="badge rounded-pill mt-2 d-none px-3 py-1.5" style="background: linear-gradient(145deg, #EEF2FF, #E0E7FF); color: #065f46; border: 1px solid rgba(47, 107, 255, 0.25);">
                                                <i class="fas fa-check me-1" style="color: #2F6BFF;"></i> <span id="fileNameText">New logo selected</span>
                                            </div>
                                        </div>
                                    </label>
                                    @error('logo')
                                        <div class="text-danger small mt-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Section 3: Address & Tax Details -->
                                <div class="section-badge">
                                    <i class="fas fa-map-marked-alt"></i> Address & Tax Details
                                </div>
                                <div class="row g-4">
                                    <!-- Country -->
                                    <div class="col-md-6">
                                        <label for="country" class="form-label-custom">
                                            Country <span class="req-asterisk">*</span>
                                        </label>
                                        <div class="input-group input-group-custom">
                                            <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                            <input type="text" class="form-control @error('country') is-invalid @enderror"
                                                   id="country" name="country" list="countryDatalist"
                                                   value="{{ old('country', $businessAddress->country) }}"
                                                   placeholder="e.g. India" required>
                                        </div>
                                        <datalist id="countryDatalist">
                                            <option value="India">
                                            <option value="United States">
                                            <option value="United Arab Emirates">
                                            <option value="United Kingdom">
                                            <option value="Saudi Arabia">
                                            <option value="Singapore">
                                            <option value="Canada">
                                            <option value="Australia">
                                            <option value="Germany">
                                            <option value="France">
                                            <option value="Bangladesh">
                                            <option value="Qatar">
                                            <option value="Kuwait">
                                            <option value="Oman">
                                            <option value="Bahrain">
                                            <option value="Malaysia">
                                            <option value="Nepal">
                                            <option value="Sri Lanka">
                                            <option value="Pakistan">
                                            <option value="Japan">
                                            <option value="South Africa">
                                            <option value="New Zealand">
                                        </datalist>
                                        @error('country')
                                            <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Tax Name -->
                                    <div class="col-md-6">
                                        <label for="tax_name" class="form-label-custom">
                                            Tax Identifier Name (Optional)
                                        </label>
                                        <div class="input-group input-group-custom">
                                            <span class="input-group-text"><i class="fas fa-file-invoice"></i></span>
                                            <input type="text" class="form-control @error('tax_name') is-invalid @enderror"
                                                   id="tax_name" name="tax_name"
                                                   value="{{ old('tax_name', $businessAddress->tax_name) }}"
                                                   placeholder="e.g. GST, VAT, PAN">
                                        </div>
                                        @error('tax_name')
                                            <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Full Address -->
                                    <div class="col-md-12">
                                        <label for="address" class="form-label-custom">
                                            Full Street Address <span class="req-asterisk">*</span>
                                        </label>
                                        <div class="input-group input-group-custom">
                                            <span class="input-group-text pt-3 align-self-start"><i class="fas fa-align-left"></i></span>
                                            <textarea class="form-control @error('address') is-invalid @enderror"
                                                      id="address" name="address" rows="3"
                                                      placeholder="Complete street address, building/suite number, city, state, and postal code"
                                                      required>{{ old('address', $businessAddress->address) }}</textarea>
                                        </div>
                                        @error('address')
                                            <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Is Default Checkbox -->
                                    <div class="col-md-12">
                                        <div class="default-toggle-box p-3.5 rounded-4 d-flex align-items-center gap-3">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input fs-5" type="checkbox"
                                                       id="is_default" name="is_default" value="1"
                                                       {{ old('is_default', $businessAddress->is_default) ? 'checked' : '' }}>
                                            </div>
                                            <div>
                                                <label class="form-check-label fw-bold mb-0" for="is_default">
                                                    Set as default primary branch address
                                                </label>
                                                <div class="text-muted small">If enabled, this location will be designated as the primary head office address.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Action Buttons -->
                                <div class="mt-5 pt-4 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <a href="{{ route('admin.settings.business-address.index') }}" class="btn btn-cancel-custom rounded-pill px-4 fw-bold">
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn-save-address">
                                        <i class="fas fa-save me-1.5"></i> Update Branch Address
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function previewBranchLogo(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                const img = document.getElementById('logoPreviewImg');
                const icon = document.getElementById('logoDefaultIcon');
                const badge = document.getElementById('fileSelectedBadge');
                const badgeText = document.getElementById('fileNameText');
                const currentBadge = document.getElementById('currentBranchLogoBadge');

                if (currentBadge) {
                    currentBadge.classList.add('d-none');
                }

                if (img) {
                    img.src = e.target.result;
                    img.classList.remove('d-none');
                }
                if (icon) {
                    icon.classList.add('d-none');
                }

                if (badge && badgeText) {
                    badgeText.innerText = file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
                    badge.classList.remove('d-none');
                }
            }

            reader.readAsDataURL(file);
        }
    }

    /* ===== INTERNATIONAL PHONE VALIDATION WITH RESPECT TO COUNTRY ===== */
    document.addEventListener('DOMContentLoaded', function () {
        const phoneInput   = document.getElementById('phone_input');
        const hiddenInput  = document.getElementById('phone');
        const countryInput = document.getElementById('country');
        const phoneShell   = document.getElementById('phoneInputShell');
        const statusIcon   = document.getElementById('phoneStatusIcon');
        const feedbackBox  = document.getElementById('phoneValidationFeedback');
        const countryBadge = document.getElementById('countryBadge');
        const hintText     = document.getElementById('phoneHintText');
        const digitCounter = document.getElementById('phoneDigitCounter');
        const branchForm   = document.getElementById('branchForm');

        if (!phoneInput) return;

        function getCountryFlagEmoji(iso2) {
            if (!iso2 || iso2.length !== 2) return '🌐';
            const codePoints = iso2.toUpperCase().split('').map(c => 127397 + c.charCodeAt(0));
            return String.fromCodePoint(...codePoints);
        }

        function initPhoneValidation() {
            if (!window.PmsPhoneFields) return;
            const iti = window.PmsPhoneFields.attach(phoneInput, { initialCountry: 'in' });

            // Get country digits expectation
            function getExpectedDigits(countryIso) {
                if (window.intlTelInputUtils) {
                    try {
                        const example = window.intlTelInputUtils.getExampleNumber(
                            countryIso, 
                            false, 
                            window.intlTelInputUtils.numberType.MOBILE
                        );
                        if (example) {
                            const digits = example.replace(/\D/g, '');
                            const countryData = iti.getSelectedCountryData();
                            const dc = String(countryData.dialCode || '');
                            if (digits.startsWith(dc)) {
                                return digits.slice(dc.length).length || 10;
                            }
                            return digits.length || 10;
                        }
                    } catch (e) {}
                }
                // Standard fallbacks for popular countries
                const standards = {
                    'in': 10, 'us': 10, 'ca': 10, 'ae': 9, 'gb': 10, 
                    'sa': 9, 'sg': 8, 'au': 9, 'de': 10, 'fr': 9, 'bd': 10, 'pk': 10
                };
                return standards[countryIso] || 10;
            }

            function updateCountryContext() {
                const countryData = iti.getSelectedCountryData();
                const countryName = countryData.name ? countryData.name.replace(/\s*\(.*?\)\s*/g, '').trim() : 'India';
                const dialCode = countryData.dialCode ? `+${countryData.dialCode}` : '+91';
                const expectedDigits = getExpectedDigits(countryData.iso2);

                if (countryBadge) {
                    countryBadge.innerHTML = `<span class="me-1">${getCountryFlagEmoji(countryData.iso2)}</span> ${countryName} (${dialCode})`;
                }

                if (hintText) {
                    const placeholder = phoneInput.getAttribute('placeholder') || (countryData.iso2 === 'in' ? '98765 43210' : '');
                    hintText.textContent = `Format: ${expectedDigits} digits for ${countryName}${placeholder ? ` (e.g. ${placeholder})` : ''}`;
                }

                return { countryName, dialCode, expectedDigits, iso2: countryData.iso2 };
            }

            function validatePhone() {
                const rawVal = phoneInput.value.trim();
                const { countryName, dialCode, expectedDigits, iso2 } = updateCountryContext();
                
                // Count entered digits
                const digitsOnly = rawVal.replace(/\D/g, '');
                const count = digitsOnly.length;

                if (digitCounter) {
                    digitCounter.textContent = `${count} / ${expectedDigits} digits`;
                    if (count === expectedDigits) {
                        digitCounter.style.color = '#10b981';
                    } else if (count > expectedDigits) {
                        digitCounter.style.color = '#ef4444';
                    } else {
                        digitCounter.style.color = '#64748b';
                    }
                }

                // If blank (optional field)
                if (rawVal === '') {
                    phoneShell.classList.remove('is-valid-phone', 'is-invalid-phone');
                    if (statusIcon) statusIcon.style.display = 'none';
                    if (feedbackBox) {
                        feedbackBox.style.display = 'none';
                        feedbackBox.innerHTML = '';
                    }
                    if (hiddenInput) hiddenInput.value = '';
                    return true;
                }

                const isValid = iti.isValidNumber();

                if (isValid) {
                    phoneShell.classList.add('is-valid-phone');
                    phoneShell.classList.remove('is-invalid-phone');

                    if (statusIcon) {
                        statusIcon.style.display = 'block';
                        statusIcon.innerHTML = '<i class="fas fa-check-circle" style="color: #10b981;"></i>';
                    }

                    const formatted = (window.intlTelInputUtils ? iti.getNumber(window.intlTelInputUtils.numberFormat.INTERNATIONAL) : null) || iti.getNumber();

                    if (feedbackBox) {
                        feedbackBox.style.display = 'block';
                        feedbackBox.className = 'phone-validation-feedback valid-feedback-custom mt-1.5';
                        feedbackBox.innerHTML = `
                            <div class="feedback-content d-flex align-items-center gap-1.5 small fw-semibold">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                <span>Valid ${countryName} phone number (${formatted})</span>
                            </div>
                        `;
                    }

                    if (hiddenInput) {
                        hiddenInput.value = iti.getNumber(); // Valid E.164 number
                    }
                    return true;
                } else {
                    phoneShell.classList.add('is-invalid-phone');
                    phoneShell.classList.remove('is-valid-phone');

                    if (statusIcon) {
                        statusIcon.style.display = 'block';
                        statusIcon.innerHTML = '<i class="fas fa-exclamation-circle" style="color: #ef4444;"></i>';
                    }

                    let message = `Please enter a valid phone number for ${countryName}.`;
                    const errorCode = iti.getValidationError();

                    switch (errorCode) {
                        case 1:
                            message = `Invalid country code selected (${dialCode}).`;
                            break;
                        case 2:
                            message = `Phone number is too short for ${countryName}. Required: ${expectedDigits} digits (entered ${count}).`;
                            break;
                        case 3:
                            message = `Phone number is too long for ${countryName}. Maximum: ${expectedDigits} digits.`;
                            break;
                        case 5:
                            message = `Invalid number of digits for ${countryName}. Required: ${expectedDigits} digits.`;
                            break;
                        default:
                            if (count < expectedDigits) {
                                message = `Phone number is too short for ${countryName}. Expected ${expectedDigits} digits (entered ${count}).`;
                            } else if (count > expectedDigits) {
                                message = `Phone number is too long for ${countryName}. Expected ${expectedDigits} digits.`;
                            } else {
                                message = `Invalid phone number digits for ${countryName}. Please verify.`;
                            }
                            break;
                    }

                    if (feedbackBox) {
                        feedbackBox.style.display = 'block';
                        feedbackBox.className = 'phone-validation-feedback invalid-feedback-custom mt-1.5';
                        feedbackBox.innerHTML = `
                            <div class="feedback-content d-flex align-items-center gap-1.5 small fw-semibold">
                                <i class="fas fa-times-circle text-danger me-1"></i>
                                <span>${message}</span>
                            </div>
                        `;
                    }

                    if (hiddenInput) {
                        hiddenInput.value = '';
                    }
                    return false;
                }
            }

            // Sync phone country to Country field
            phoneInput.addEventListener('countrychange', function () {
                const countryData = iti.getSelectedCountryData();
                if (countryData && countryData.name && countryInput) {
                    const cleanName = countryData.name.replace(/\s*\(.*?\)\s*/g, '').trim();
                    if (!countryInput.value || countryInput.value === 'India' || countryInput.dataset.autoSynced === 'true') {
                        countryInput.value = cleanName;
                        countryInput.dataset.autoSynced = 'true';
                    }
                }
                validatePhone();
            });

            // Sync Country field to phone dropdown country
            if (countryInput) {
                countryInput.addEventListener('change', function () {
                    const val = countryInput.value.trim().toLowerCase();
                    if (!val) return;
                    const allCountries = window.PmsPhoneFields.countries;
                    const matched = allCountries.find(c => 
                        c.name.toLowerCase() === val || 
                        c.name.toLowerCase().startsWith(val) ||
                        c.iso2.toLowerCase() === val
                    );
                    if (matched) {
                        iti.setCountry(matched.iso2);
                        validatePhone();
                    }
                });
            }

            phoneInput.addEventListener('input', validatePhone);
            phoneInput.addEventListener('blur', function () {
                if (phoneInput.value.trim() !== '') {
                    validatePhone();
                }
            });

            // Pre-fill existing value if available (edit mode or validation reload)
            const existingValue = (hiddenInput && hiddenInput.value) ? hiddenInput.value.trim() : phoneInput.value.trim();
            if (existingValue) {
                iti.setNumber(existingValue);
                setTimeout(validatePhone, 200);
            } else {
                iti.setCountry('in');
                updateCountryContext();
            }

            // Form Submit Interceptor
            if (branchForm) {
                branchForm.addEventListener('submit', function (e) {
                    const rawVal = phoneInput.value.trim();
                    if (rawVal !== '') {
                        const valid = validatePhone();
                        if (!valid) {
                            e.preventDefault();
                            e.stopPropagation();
                            phoneInput.focus();
                            phoneShell.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            phoneShell.style.animation = 'phoneShake 0.4s ease';
                            setTimeout(() => phoneShell.style.animation = '', 450);
                            return false;
                        }
                        if (hiddenInput) {
                            hiddenInput.value = iti.getNumber();
                        }
                    } else {
                        if (hiddenInput) {
                            hiddenInput.value = '';
                        }
                    }
                }, true);
            }
        }

        if (window.PmsPhoneFields) initPhoneValidation();
    });
</script>
@endpush
