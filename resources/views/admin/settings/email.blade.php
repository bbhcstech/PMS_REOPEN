@extends('admin.layout.app')

@section('title', 'Email SMTP Settings')

@push('styles')
<style>
    .email-settings-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f4fbf7 100%);
        color: #0a2e1f;
    }

    .email-settings-shell {
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
    .email-settings-page .stat-card,
    .email-settings-page .stat-card:first-of-type {
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

    .email-settings-page .stat-card:first-of-type *,
    .email-settings-page .stat-card * {
        -webkit-text-fill-color: initial;
    }

    .email-settings-page .stat-card h3,
    .email-settings-page .stat-card:first-of-type h3 {
        color: #0a2e1f !important;
        -webkit-text-fill-color: #0a2e1f !important;
    }

    .email-settings-page .stat-card h6,
    .email-settings-page .stat-card span,
    .email-settings-page .stat-card:first-of-type span,
    .email-settings-page .stat-card:first-of-type h6 {
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

    .stat-icon.mailer,
    .email-settings-page .stat-card:first-of-type .stat-icon.mailer {
        background: linear-gradient(145deg, #d1fae5, #a7f3d0) !important;
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
    }

    .stat-icon.host,
    .email-settings-page .stat-card .stat-icon.host {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
    }

    .stat-icon.encryption,
    .email-settings-page .stat-card .stat-icon.encryption {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
    }

    .stat-icon.sender,
    .email-settings-page .stat-card .stat-icon.sender {
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
        font-size: 1.15rem;
        font-weight: 800;
        color: #0a2e1f;
        margin: 0;
        line-height: 1.2;
    }

    /* ===== FORM CARDS & INPUTS ===== */
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

    .card-header-avatar.test {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd);
        color: #0284c7;
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

    .btn-send-test {
        height: 50px;
        border-radius: 40px;
        font-weight: 700;
        font-size: 0.92rem;
        padding: 0 24px;
        background: linear-gradient(145deg, #38bdf8, #0284c7);
        color: white !important;
        border: none;
        box-shadow: 0 6px 20px -4px rgba(2, 132, 199, 0.35);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
        width: 100%;
    }

    .btn-send-test:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 10px 28px -4px rgba(2, 132, 199, 0.45);
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
        .email-settings-page {
            padding: 1.25rem 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="email-settings-page">
    <div class="email-settings-shell">
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
                <span>SMTP & Mail Gateway</span>
            </div>

            <!-- Page Header Card -->
            <div class="branches-header">
                <div class="header-left-box">
                    <div class="header-icon-badge">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <div class="header-title">
                        <h1>SMTP & Mail Gateway Settings</h1>
                        <p>Configure email protocol, SMTP server hostname, port, credentials, and sender headers.</p>
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

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm rounded-4 border-0" style="background: rgba(254, 226, 226, 0.95); color: #991b1b; border-left: 5px solid #ef4444 !important;" role="alert">
                    <i class="fas fa-exclamation-triangle fs-4 me-2"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Executive Summary Stats Grid -->
            @php
                $mailerVal = strtoupper($settings['mail_mailer'] ?? 'smtp');
                $hostVal = $settings['mail_host'] ?? '127.0.0.1';
                $portVal = $settings['mail_port'] ?? '2525';
                $encVal = strtoupper($settings['mail_encryption'] ?? 'tls');
                $fromVal = $settings['mail_from_address'] ?? 'hello@example.com';
            @endphp
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon mailer">
                        <i class="fas fa-server"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Mail Driver</h6>
                        <h3>{{ $mailerVal }} Protocol</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon host">
                        <i class="fas fa-network-wired"></i>
                    </div>
                    <div class="stat-info">
                        <h6>SMTP Host & Port</h6>
                        <h3>{{ $hostVal }}:{{ $portVal }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon encryption">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Encryption</h6>
                        <h3>{{ $encVal }} Security</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon sender">
                        <i class="fas fa-at"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Sender Address</h6>
                        <h3>{{ $fromVal }}</h3>
                    </div>
                </div>
            </div>

            <!-- Main Layout Row -->
            <div class="row g-4">
                <!-- Left: SMTP Config Form -->
                <div class="col-lg-8">
                    <div class="address-card-elevated">
                        <div class="card-header-custom">
                            <div class="card-header-avatar shadow-sm">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold fs-5" style="color: #0a2e1f;">SMTP Mail Server Connection</h5>
                                <small class="text-muted">Set up mail delivery server options, credentials, and from-header defaults</small>
                            </div>
                        </div>

                        <div class="p-4 p-md-5">
                            <form method="POST" action="{{ route('admin.settings.email.update') }}">
                                @csrf

                                <!-- Section 1: Server Credentials -->
                                <div class="section-badge">
                                    <i class="fas fa-server"></i> Server Configuration & Credentials
                                </div>

                                <div class="row g-4 mb-5">
                                    <!-- Mailer Protocol -->
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Mail Driver / Protocol <span class="req-asterisk">*</span></label>
                                        <div class="input-group input-group-custom">
                                            <span class="input-group-text"><i class="fas fa-cog"></i></span>
                                            <select name="mail_mailer" class="form-select" required>
                                                <option value="smtp" {{ ($settings['mail_mailer'] ?? '') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                                <option value="sendmail" {{ ($settings['mail_mailer'] ?? '') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                                <option value="log" {{ ($settings['mail_mailer'] ?? '') == 'log' ? 'selected' : '' }}>Log (Dev Testing)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- SMTP Host -->
                                    <div class="col-md-6">
                                        <label class="form-label-custom">SMTP Hostname <span class="req-asterisk">*</span></label>
                                        <div class="input-group input-group-custom">
                                            <span class="input-group-text"><i class="fas fa-network-wired"></i></span>
                                            <input type="text" name="mail_host" class="form-control"
                                                placeholder="smtp.gmail.com or mail.yourdomain.com"
                                                value="{{ old('mail_host', $settings['mail_host'] ?? '') }}" required>
                                        </div>
                                    </div>

                                    <!-- SMTP Port -->
                                    <div class="col-md-4">
                                        <label class="form-label-custom">Port Number <span class="req-asterisk">*</span></label>
                                        <div class="input-group input-group-custom">
                                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                            <input type="number" name="mail_port" class="form-control"
                                                placeholder="587 / 465 / 25"
                                                value="{{ old('mail_port', $settings['mail_port'] ?? '') }}" required>
                                        </div>
                                    </div>

                                    <!-- Encryption -->
                                    <div class="col-md-4">
                                        <label class="form-label-custom">Encryption Protocol <span class="req-asterisk">*</span></label>
                                        <div class="input-group input-group-custom">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <select name="mail_encryption" class="form-select" required>
                                                <option value="tls" {{ ($settings['mail_encryption'] ?? '') == 'tls' ? 'selected' : '' }}>TLS</option>
                                                <option value="ssl" {{ ($settings['mail_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                                <option value="null" {{ ($settings['mail_encryption'] ?? '') == 'null' ? 'selected' : '' }}>None</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Username -->
                                    <div class="col-md-4">
                                        <label class="form-label-custom">SMTP Username</label>
                                        <div class="input-group input-group-custom">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" name="mail_username" class="form-control"
                                                placeholder="user@domain.com"
                                                value="{{ old('mail_username', $settings['mail_username'] ?? '') }}">
                                        </div>
                                    </div>

                                    <!-- Password -->
                                    <div class="col-md-6">
                                        <label class="form-label-custom">SMTP Password</label>
                                        <div class="input-group input-group-custom">
                                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                                            <input type="password" name="mail_password" class="form-control" placeholder="••••••••••••">
                                        </div>
                                        <small class="text-muted mt-1 d-block">Leave empty to keep existing password unchanged.</small>
                                    </div>

                                    <!-- From Address -->
                                    <div class="col-md-6">
                                        <label class="form-label-custom">From Sender Email <span class="req-asterisk">*</span></label>
                                        <div class="input-group input-group-custom">
                                            <span class="input-group-text"><i class="fas fa-at"></i></span>
                                            <input type="email" name="mail_from_address" class="form-control"
                                                placeholder="noreply@yourdomain.com"
                                                value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}" required>
                                        </div>
                                    </div>

                                    <!-- From Name -->
                                    <div class="col-md-12">
                                        <label class="form-label-custom">From Sender Name <span class="req-asterisk">*</span></label>
                                        <div class="input-group input-group-custom">
                                            <span class="input-group-text"><i class="fas fa-signature"></i></span>
                                            <input type="text" name="mail_from_name" class="form-control"
                                                placeholder="PMS System Notification"
                                                value="{{ old('mail_from_name', $settings['mail_from_name'] ?? '') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Action Buttons -->
                                <div class="mt-4 pt-4 border-top d-flex justify-content-end">
                                    <button type="submit" class="btn-save-address">
                                        <i class="fas fa-save me-1.5"></i> Save SMTP Configuration
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right: Send Test Email Widget -->
                <div class="col-lg-4">
                    <div class="address-card-elevated h-100">
                        <div class="card-header-custom">
                            <div class="card-header-avatar test shadow-sm">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold fs-5" style="color: #0a2e1f;">Test SMTP Delivery</h5>
                                <small class="text-muted">Verify your SMTP settings by sending a test email</small>
                            </div>
                        </div>

                        <div class="p-4 p-md-5">
                            <form method="POST" action="{{ route('admin.settings.email.test') }}">
                                @csrf

                                <div class="mb-4">
                                    <label class="form-label-custom">Recipient Email Address <span class="req-asterisk">*</span></label>
                                    <div class="input-group input-group-custom">
                                        <span class="input-group-text"><i class="fas fa-envelope-open-text"></i></span>
                                        <input type="email" name="test_email" class="form-control" placeholder="your-email@domain.com" required>
                                    </div>
                                    <small class="text-muted mt-1.5 d-block">System will attempt sending a real-time test mail to this address.</small>
                                </div>

                                <button type="submit" class="btn-send-test">
                                    <i class="fas fa-paper-plane me-1.5"></i> Send Test Email
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
