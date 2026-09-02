@extends('admin.layout.app')

@section('title', 'Security Settings')

@push('styles')
<style>
    .security-settings-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f4fbf7 100%);
        color: #0a2e1f;
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
    .security-settings-page .stat-card,
    .security-settings-page .stat-card:first-of-type {
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

    .security-settings-page .stat-card:first-of-type *,
    .security-settings-page .stat-card * {
        -webkit-text-fill-color: initial;
    }

    .security-settings-page .stat-card h3,
    .security-settings-page .stat-card:first-of-type h3 {
        color: #0a2e1f !important;
        -webkit-text-fill-color: #0a2e1f !important;
    }

    .security-settings-page .stat-card h6,
    .security-settings-page .stat-card span,
    .security-settings-page .stat-card:first-of-type span,
    .security-settings-page .stat-card:first-of-type h6 {
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

    .stat-icon.pass,
    .security-settings-page .stat-card:first-of-type .stat-icon.pass {
        background: linear-gradient(145deg, #d1fae5, #a7f3d0) !important;
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
    }

    .stat-icon.session,
    .security-settings-page .stat-card .stat-icon.session {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
    }

    .stat-icon.lockout,
    .security-settings-page .stat-card .stat-icon.lockout {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
    }

    .stat-icon.twofa,
    .security-settings-page .stat-card .stat-icon.twofa {
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
        font-size: 1.25rem;
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

    .input-group-custom .form-control {
        border: none;
        background-color: transparent;
        font-size: 0.92rem;
        font-weight: 600;
        color: #0a2e1f;
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
        border: 1px solid rgba(16, 185, 129, 0.25);
        border-radius: 20px;
        padding: 1.3rem 1.6rem;
        transition: all 0.25s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .policy-switch-box:hover {
        border-color: #34d399;
        background: #ecfdf5;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08);
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
        .security-settings-page {
            padding: 1.25rem 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
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
                        <h5 class="mb-0 fw-bold fs-5" style="color: #0a2e1f;">Password Policies & Authentication Parameters</h5>
                        <small class="text-muted">Enforce robust login security rules, password complexity, and 2FA across all user accounts</small>
                    </div>
                </div>

                <div class="p-4 p-md-5">
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
                                        value="{{ old('min_password_length', $settings['min_password_length'] ?? '8') }}" required>
                                </div>
                                <small class="text-muted mt-1 d-block">Recommended: 8+ characters.</small>
                            </div>

                            <!-- Session Inactivity Timeout -->
                            <div class="col-md-4">
                                <label class="form-label-custom">Session Timeout (Minutes) <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-stopwatch"></i></span>
                                    <input type="number" name="session_timeout_mins" class="form-control" min="5" max="1440"
                                        value="{{ old('session_timeout_mins', $settings['session_timeout_mins'] ?? '120') }}" required>
                                </div>
                                <small class="text-muted mt-1 d-block">Automatic logout after inactivity (e.g. 120 mins).</small>
                            </div>

                            <!-- Max Failed Login Attempts -->
                            <div class="col-md-4">
                                <label class="form-label-custom">Max Failed Login Attempts <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-ban"></i></span>
                                    <input type="number" name="max_login_attempts" class="form-control" min="3" max="10"
                                        value="{{ old('max_login_attempts', $settings['max_login_attempts'] ?? '5') }}" required>
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
                                        value="{{ old('lockout_duration_mins', $settings['lockout_duration_mins'] ?? '15') }}" required>
                                </div>
                                <small class="text-muted mt-1 d-block">Time an account stays locked after max failed attempts.</small>
                            </div>

                            <!-- Password Complexity Box -->
                            <div class="col-md-6">
                                <div class="policy-switch-box">
                                    <label class="form-label-custom mb-3" style="color: #0a2e1f;"><i class="fas fa-shield-cat me-1.5" style="color: #059669;"></i>Password Complexity Requirements</label>
                                    
                                    <div class="form-check form-switch mb-2.5 d-flex align-items-center gap-3">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="require_uppercase" value="1" id="uppercaseSwitch"
                                            style="width: 2.5em; height: 1.3em; cursor: pointer;"
                                            {{ $reqUpper ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="uppercaseSwitch" style="cursor: pointer; font-size: 0.88rem;">
                                            Atleast one Capital Letter (A-Z)
                                        </label>
                                    </div>

                                    <div class="form-check form-switch mb-2.5 d-flex align-items-center gap-3">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="require_lowercase" value="1" id="lowercaseSwitch"
                                            style="width: 2.5em; height: 1.3em; cursor: pointer;"
                                            {{ $reqLower ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="lowercaseSwitch" style="cursor: pointer; font-size: 0.88rem;">
                                            Atleast one small Letter (a-z)
                                        </label>
                                    </div>

                                    <div class="form-check form-switch mb-2.5 d-flex align-items-center gap-3">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="require_numbers" value="1" id="numbersSwitch"
                                            style="width: 2.5em; height: 1.3em; cursor: pointer;"
                                            {{ $reqNum ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="numbersSwitch" style="cursor: pointer; font-size: 0.88rem;">
                                            Require numeric digits (0-9)
                                        </label>
                                    </div>

                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-3">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="require_special_char" value="1" id="specCharSwitch"
                                            style="width: 2.5em; height: 1.3em; cursor: pointer;"
                                            {{ $reqSpec ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="specCharSwitch" style="cursor: pointer; font-size: 0.88rem;">
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
                                            style="width: 2.8em; height: 1.4em; cursor: pointer;"
                                            {{ $enable2fa ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="twoFactorSwitch" style="cursor: pointer;">
                                            Enforce Two-Factor Authentication (2FA) for Admin & Management Roles
                                            <small class="d-block text-muted fw-normal mt-0.5" style="font-size: 0.85rem;">Requires an OTP verification code during user login.</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="mt-5 pt-4 border-top d-flex justify-content-end">
                            <button type="submit" class="btn-save-address">
                                <i class="fas fa-save me-1.5"></i> Save Security Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
