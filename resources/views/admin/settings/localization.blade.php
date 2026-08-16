@extends('admin.layout.app')

@section('title', 'Localization Settings')

@push('styles')
<style>
    .localization-settings-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f4fbf7 100%);
        color: #0a2e1f;
    }

    .localization-settings-shell {
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
        background: radial-gradient(circle, rgba(52, 211, 153, 0.12) 0%, transparent 70%);
        animation: orbFloat 20s ease-in-out infinite;
    }

    .orb-2 {
        bottom: -100px;
        left: -100px;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);
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
        color: #059669;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .breadcrumb-custom a:hover {
        color: #047857;
    }

    /* ===== HEADER CARD ===== */
    .branches-header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        padding: 1.75rem 2.25rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(16, 185, 129, 0.15);
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08);
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
        background: linear-gradient(145deg, #34d399, #059669);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        box-shadow: 0 8px 20px -4px rgba(5, 150, 105, 0.35);
        flex-shrink: 0;
    }

    .header-title h1 {
        font-size: 1.95rem;
        font-weight: 800;
        background: linear-gradient(135deg, #0a2e1f, #059669, #10b981);
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
        border: 1px solid rgba(16, 185, 129, 0.25);
        color: #0f744c !important;
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
        background-color: #e6f3ec;
        color: #059669 !important;
        border-color: rgba(16, 185, 129, 0.4);
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
    .localization-settings-page .stat-card,
    .localization-settings-page .stat-card:first-of-type {
        background: #ffffff !important;
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 1.5rem;
        border: 1px solid rgba(16, 185, 129, 0.14) !important;
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08) !important;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        color: #0a2e1f !important;
    }

    .localization-settings-page .stat-card:first-of-type *,
    .localization-settings-page .stat-card * {
        -webkit-text-fill-color: initial;
    }

    .localization-settings-page .stat-card h3,
    .localization-settings-page .stat-card:first-of-type h3 {
        color: #0a2e1f !important;
        -webkit-text-fill-color: #0a2e1f !important;
    }

    .localization-settings-page .stat-card h6,
    .localization-settings-page .stat-card span,
    .localization-settings-page .stat-card:first-of-type span,
    .localization-settings-page .stat-card:first-of-type h6 {
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
        background: linear-gradient(90deg, #34d399, #059669);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .stat-card:hover::after {
        transform: scaleX(1);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 35px -12px rgba(16, 185, 129, 0.15) !important;
        border-color: rgba(16, 185, 129, 0.25) !important;
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

    .stat-icon.curr,
    .localization-settings-page .stat-card:first-of-type .stat-icon.curr {
        background: linear-gradient(145deg, #d1fae5, #a7f3d0) !important;
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
    }

    .stat-icon.tz,
    .localization-settings-page .stat-card .stat-icon.tz {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
    }

    .stat-icon.lang,
    .localization-settings-page .stat-card .stat-icon.lang {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
    }

    .stat-icon.format,
    .localization-settings-page .stat-card .stat-icon.format {
        background: linear-gradient(145deg, #e0e7ff, #c7d2fe) !important;
        color: #4f46e5 !important;
        -webkit-text-fill-color: #4f46e5 !important;
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
        font-size: 1.2rem;
        font-weight: 800;
        color: #0a2e1f;
        margin: 0;
        line-height: 1.2;
    }

    /* ===== FORM CARD & INPUTS ===== */
    .address-card-elevated {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        border: 1px solid rgba(16, 185, 129, 0.15);
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08);
        overflow: hidden;
    }

    .card-header-custom {
        padding: 1.5rem 2.25rem;
        border-bottom: 1px solid rgba(16, 185, 129, 0.12);
        display: flex;
        align-items: center;
        gap: 1rem;
        background: transparent;
    }

    .card-header-avatar {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        color: #059669;
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
        background: #ecfdf5;
        color: #059669;
        font-weight: 800;
        font-size: 0.82rem;
        letter-spacing: 0.03em;
        border: 1px solid rgba(5, 150, 105, 0.2);
        margin-bottom: 1.25rem;
    }

    .form-label-custom {
        font-size: 0.88rem;
        font-weight: 700;
        color: #0a2e1f;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .input-group-custom {
        border-radius: 16px;
        border: 1px solid rgba(16, 185, 129, 0.2);
        background-color: #fafefb;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .input-group-custom:focus-within {
        border-color: #34d399;
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.15);
        transform: translateY(-1px);
    }

    .input-group-custom .input-group-text {
        background-color: transparent;
        border: none;
        color: #059669;
        padding-left: 18px;
        padding-right: 12px;
        font-size: 1.1rem;
    }

    .input-group-custom .form-control,
    .input-group-custom .form-select {
        border: none;
        background-color: transparent;
        font-size: 0.92rem;
        font-weight: 600;
        color: #0a2e1f;
        padding-right: 18px;
        height: 50px;
    }

    .input-group-custom .form-control[readonly] {
        background-color: #f1f5f9;
        cursor: not-allowed;
        color: #475569;
    }

    .input-group-custom .form-control:focus,
    .input-group-custom .form-select:focus {
        box-shadow: none;
        background-color: transparent;
    }

    .btn-save-address {
        height: 50px;
        border-radius: 40px;
        font-weight: 700;
        font-size: 0.95rem;
        padding: 0 32px;
        background: linear-gradient(145deg, #34d399, #059669);
        color: white !important;
        border: none;
        box-shadow: 0 6px 20px -4px rgba(5, 150, 105, 0.35);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }

    .btn-save-address:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 10px 28px -4px rgba(5, 150, 105, 0.45);
        color: white !important;
    }

    .req-asterisk {
        color: #059669;
        font-weight: 800;
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .localization-settings-page {
            padding: 1.25rem 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
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
            <div class="branches-header">
                <div class="header-left-box">
                    <div class="header-icon-badge">
                        <i class="fas fa-globe"></i>
                    </div>
                    <div class="header-title">
                        <h1>Regional & Localization Settings</h1>
                        <p>Configure currency, timezone, date & time display formats, and default system language preferences.</p>
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
                $currCode = $settings['currency'] ?? 'USD';
                $currSym = $settings['currency_symbol'] ?? '$';
                $tzVal = $settings['timezone'] ?? 'UTC';
                $langVal = strtoupper($settings['language'] ?? 'en');
                $dateFormatVal = $settings['date_format'] ?? 'Y-m-d';
                $timeFormatVal = ($settings['time_format'] ?? '') == 'H:i' ? '24-Hour' : '12-Hour';
            @endphp
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon curr">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Default Currency</h6>
                        <h3 id="statCurrencyDisplay">{{ $currCode }} ({{ $currSym }})</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon tz">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h6>System Timezone</h6>
                        <h3>{{ $tzVal }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon lang">
                        <i class="fas fa-language"></i>
                    </div>
                    <div class="stat-info">
                        <h6>System Language</h6>
                        <h3>{{ $langVal }} Language</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon format">
                        <i class="fas fa-calendar-days"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Date / Time Format</h6>
                        <h3>{{ $dateFormatVal }} ({{ $timeFormatVal }})</h3>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="address-card-elevated">
                <div class="card-header-custom">
                    <div class="card-header-avatar shadow-sm">
                        <i class="fas fa-sliders"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold fs-5" style="color: #0a2e1f;">Currency, Timezone & Regional Formats</h5>
                        <small class="text-muted">Set global default formatting preferences across financial reports, time logs, and employee portals</small>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <form method="POST" action="{{ route('admin.settings.localization.update') }}">
                        @csrf

                        <!-- Section 1: Currency & Financial Formatting -->
                        <div class="section-badge">
                            <i class="fas fa-coins"></i> Currency & Financial Formatting
                        </div>

                        <div class="row g-4 mb-5">
                            <!-- Currency Code -->
                            <div class="col-md-4">
                                <label class="form-label-custom">Default Currency Code <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                    <select name="currency" class="form-select" required>
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
                                <label class="form-label-custom">Currency Symbol <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    <input type="text" name="currency_symbol" class="form-control"
                                        value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '$') }}" readonly required
                                        title="Auto-filled based on selected Default Currency Code">
                                </div>
                                <small class="text-muted mt-1 d-block"><i class="fas fa-lock me-1" style="color: #059669;"></i>Auto-derived from selected Currency Code.</small>
                            </div>

                            <!-- Currency Symbol Position -->
                            <div class="col-md-4">
                                <label class="form-label-custom">Symbol Position <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                                    <select name="currency_position" class="form-select" required>
                                        <option value="left" {{ ($settings['currency_position'] ?? '') == 'left' ? 'selected' : '' }}>Left ({{ $currSym }}100)</option>
                                        <option value="right" {{ ($settings['currency_position'] ?? '') == 'right' ? 'selected' : '' }}>Right (100{{ $currSym }})</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Timezone & Language Preferences -->
                        <div class="section-badge">
                            <i class="fas fa-earth-americas"></i> Timezone & Language Preferences
                        </div>

                        <div class="row g-4 mb-5">
                            <!-- Timezone -->
                            <div class="col-md-6">
                                <label class="form-label-custom">System Timezone <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-globe-americas"></i></span>
                                    <select name="timezone" class="form-select" required>
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
                                <label class="form-label-custom">Default System Language <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-language"></i></span>
                                    <select name="language" class="form-select" required>
                                        <option value="en" {{ ($settings['language'] ?? '') == 'en' ? 'selected' : '' }}>English</option>
                                        <option value="bn" {{ ($settings['language'] ?? '') == 'bn' ? 'selected' : '' }}>Bengali</option>
                                        <option value="es" {{ ($settings['language'] ?? '') == 'es' ? 'selected' : '' }}>Spanish</option>
                                        <option value="fr" {{ ($settings['language'] ?? '') == 'fr' ? 'selected' : '' }}>French</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Date & Time Display Formats -->
                        <div class="section-badge">
                            <i class="fas fa-calendar-days"></i> Date & Time Display Formats
                        </div>

                        <div class="row g-4">
                            <!-- Date Format -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Date Display Format <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                                    <select name="date_format" class="form-select" required>
                                        <option value="Y-m-d" {{ ($settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD (2026-08-12)</option>
                                        <option value="d-m-Y" {{ ($settings['date_format'] ?? '') == 'd-m-Y' ? 'selected' : '' }}>DD-MM-YYYY (12-08-2026)</option>
                                        <option value="m/d/Y" {{ ($settings['date_format'] ?? '') == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY (08/12/2026)</option>
                                        <option value="d M, Y" {{ ($settings['date_format'] ?? '') == 'd M, Y' ? 'selected' : '' }}>DD MMM, YYYY (12 Aug, 2026)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Time Format -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Time Display Format <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                    <select name="time_format" class="form-select" required>
                                        <option value="h:i A" {{ ($settings['time_format'] ?? '') == 'h:i A' ? 'selected' : '' }}>12-Hour Format (02:30 PM)</option>
                                        <option value="H:i" {{ ($settings['time_format'] ?? '') == 'H:i' ? 'selected' : '' }}>24-Hour Format (14:30)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="mt-5 pt-4 border-top d-flex justify-content-end">
                            <button type="submit" class="btn-save-address">
                                <i class="fas fa-save me-1.5"></i> Save Localization Settings
                            </button>
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
