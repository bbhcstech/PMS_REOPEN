@extends('admin.layout.app')

@section('title', 'Company Profile Settings')

@push('styles')
<style>
    .company-profile-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f4fbf7 100%);
        color: #0a2e1f;
    }

    .company-profile-shell {
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

    .badge-live-pulse {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.55rem 1.3rem;
        border-radius: 40px;
        background: linear-gradient(145deg, #ecfdf5, #d1fae5);
        color: #065f46;
        font-size: 0.78rem;
        font-weight: 800;
        border: 1px solid rgba(5, 150, 105, 0.25);
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.12);
    }

    .badge-live-pulse::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #10b981;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulseGlowGreen 2s infinite;
    }

    @keyframes pulseGlowGreen {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    /* ===== HERO BANNER ===== */
    .company-hero-banner {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        padding: 1.75rem 2.25rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(16, 185, 129, 0.15);
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08);
        position: relative;
        overflow: hidden;
    }

    .company-logo-wrapper {
        position: relative;
        width: 100px;
        height: 100px;
        border-radius: 22px;
        background: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(5, 150, 105, 0.25);
        padding: 8px;
        border: 1px solid rgba(16, 185, 129, 0.2);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .company-logo-wrapper:hover {
        transform: scale(1.04);
        border-color: #34d399;
    }

    .company-logo-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 14px;
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

    .logo-upload-dropzone {
        border: 2px dashed rgba(16, 185, 129, 0.35);
        background-color: rgba(16, 185, 129, 0.02);
        border-radius: 20px;
        padding: 28px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .logo-upload-dropzone:hover, .logo-upload-dropzone.dragover {
        border-color: #059669;
        background-color: rgba(16, 185, 129, 0.08);
        transform: translateY(-2px);
    }

    .upload-icon-box {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        margin: 0 auto;
        box-shadow: 0 6px 16px -4px rgba(16, 185, 129, 0.25);
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

    /* Phone and Country Code Combo */
    .phone-combo-group {
        display: flex;
        align-items: stretch;
    }

    .phone-combo-group .country-code-wrapper {
        min-width: 145px;
        max-width: 155px;
        flex-shrink: 0;
        border-right: 1px solid rgba(16, 185, 129, 0.2);
    }

    .phone-combo-group .select2-container--bootstrap-5 .select2-selection,
    .phone-combo-group .select2-container--default .select2-selection--single {
        height: 50px !important;
        border: none !important;
        border-radius: 0 !important;
        background: transparent !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 10px !important;
        box-shadow: none !important;
    }

    .phone-combo-group .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 50px !important;
        font-weight: 700;
        color: #0a2e1f;
        font-size: 0.92rem;
        padding-left: 0 !important;
    }

    .phone-combo-group .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 50px !important;
        right: 8px !important;
    }

    .select2-dropdown {
        border: 1px solid rgba(16, 185, 129, 0.25) !important;
        border-radius: 16px !important;
        box-shadow: 0 14px 34px rgba(10, 46, 31, 0.12) !important;
        overflow: hidden !important;
        z-index: 1070 !important;
        background: #ffffff !important;
    }

    .select2-search--dropdown .select2-search__field {
        border-radius: 10px !important;
        border: 1px solid rgba(16, 185, 129, 0.3) !important;
        padding: 8px 12px !important;
        font-size: 0.88rem;
    }

    .select2-results__option--highlighted[aria-selected] {
        background-color: #ecfdf5 !important;
        color: #065f46 !important;
    }

    .validation-feedback-pill {
        display: none;
        align-items: center;
        gap: 5px;
        font-size: 0.78rem;
        font-weight: 600;
        color: #dc2626;
        margin-top: 5px;
    }

    .validation-feedback-pill.is-visible {
        display: flex;
    }

    @media (max-width: 768px) {
        .company-profile-page {
            padding: 1.25rem 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="company-profile-page">
    <div class="company-profile-shell">
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
                <span>Company Profile</span>
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

            <!-- Live Interactive Preview Hero Banner -->
            <div class="company-hero-banner">
                <div class="row align-items-center g-4">
                    <div class="col-auto">
                        <div class="company-logo-wrapper">
                            @php
                                $hasLogo = !empty($company->company_logo) && file_exists(public_path($company->company_logo));
                                $logoPath = $hasLogo ? asset($company->company_logo) : asset('admin/assets/img/favicon/favicon.ico');
                            @endphp
                            <img id="liveBannerLogo" src="{{ $logoPath }}" alt="Company Logo" class="company-logo-img">
                        </div>
                    </div>
                    <div class="col">
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <h3 class="fw-bold mb-0" style="color: #0a2e1f; font-size: 1.8rem;" id="liveCompanyName">
                                {{ $company->company_name ?? 'Your Company Name' }}
                            </h3>
                        </div>
                        <div class="d-flex align-items-center gap-3 text-muted flex-wrap small">
                            <span id="liveCompanyEmail" class="d-flex align-items-center gap-1.5 fw-semibold" style="color: #4b5563;">
                                <i class="fas fa-envelope text-success"></i> {{ $company->company_email ?? 'email@company.com' }}
                            </span>
                            <span class="text-muted">•</span>
                            <span id="liveCompanyPhone" class="d-flex align-items-center gap-1.5 fw-semibold" style="color: #4b5563;">
                                <i class="fas fa-phone-alt text-success"></i> {{ $company->company_phone ?? '+1 (234) 567-890' }}
                            </span>
                            <span class="text-muted">•</span>
                            <span id="liveCompanyLocation" class="d-flex align-items-center gap-1.5 fw-semibold" style="color: #4b5563;">
                                <i class="fas fa-map-marker-alt text-success"></i> {{ $company->company_location ?? 'Location not specified' }}
                            </span>
                        </div>
                    </div>
                    @if($company && $company->company_website)
                    <div class="col-md-auto text-end">
                        <a href="{{ $company->company_website }}" target="_blank" id="liveCompanyWebsiteBtn" class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold text-decoration-none" style="background: #e6f3ec; color: #0f744c; border: 1px solid rgba(16, 185, 129, 0.25);">
                            <i class="fas fa-external-link-alt me-1"></i> Visit Website
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Main Settings Form Card -->
            <div class="address-card-elevated">
                <div class="card-header-custom">
                    <div class="card-header-avatar shadow-sm">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold fs-5" style="color: #0a2e1f;">Company Profile & Branding Details</h5>
                        <small class="text-muted">Upload organization logo and update official records</small>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <form method="POST" action="{{ route('settings.company.store') }}" enctype="multipart/form-data" id="companyProfileForm">
                        @csrf

                        @if($company && $company->id)
                            <input type="hidden" name="id" value="{{ $company->id }}">
                        @endif

                        @if($isSettingsReadOnly)
                            <div class="alert d-flex align-items-center mb-4 rounded-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #1e40af; border-left: 4px solid #3b82f6 !important; padding: 14px 18px;">
                                <i class="fas fa-eye me-3 fs-3 text-primary"></i>
                                <div>
                                    <strong class="d-block text-primary fw-bold" style="font-size: 14px;">View-Only Mode</strong>
                                    <span style="font-size: 13px; color: #1e3a8a;">You are viewing company profile settings in read-only mode. Only Administrators have permission to modify or update company settings.</span>
                                </div>
                            </div>
                        @endif

                        <!-- Section 1: Company Logo Upload -->
                        <div class="section-badge">
                            <i class="fas fa-image"></i> Company Logo & Active Branding
                        </div>

                        <div class="mb-5">
                            <label for="company_logo_input" class="logo-upload-dropzone d-block w-100 mb-0" id="logoDropzone" @if($isSettingsReadOnly) style="pointer-events: none; opacity: 0.85; cursor: default;" @endif>
                                <input type="file" name="company_logo" id="company_logo_input" class="d-none" accept="image/png, image/jpeg, image/gif, image/svg+xml, image/webp" onchange="previewLogo(this)" @if($isSettingsReadOnly) disabled @endif>
                                
                                <div class="d-flex flex-column align-items-center gap-2">
                                    <div class="upload-icon-box shadow-sm">
                                        <i class="fas {{ $isSettingsReadOnly ? 'fa-shield-alt text-primary' : 'fa-cloud-upload-alt text-success' }} fs-2"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1" style="color: #0a2e1f;">{{ $isSettingsReadOnly ? 'Company Logo (Admin Managed)' : 'Click to upload company logo or drag & drop' }}</h6>
                                        <p class="text-muted small mb-0">{{ $isSettingsReadOnly ? 'Official company logo asset' : 'Supported formats: PNG, JPG, GIF, SVG, or WEBP (Max 3MB)' }}</p>
                                    </div>
                                    
                                    @if($hasLogo)
                                        <div id="currentLogoBadge" class="badge rounded-pill mt-2 px-3 py-1.5" style="background: linear-gradient(145deg, #ecfdf5, #d1fae5); color: #065f46; border: 1px solid rgba(5, 150, 105, 0.25);">
                                            <i class="fas fa-check-circle me-1" style="color: #059669;"></i> Logo is currently set {{ $isSettingsReadOnly ? '' : '(Click to change)' }}
                                        </div>
                                    @endif

                                    <div id="fileSelectedBadge" class="badge rounded-pill mt-2 d-none px-3 py-1.5" style="background: linear-gradient(145deg, #ecfdf5, #d1fae5); color: #065f46; border: 1px solid rgba(5, 150, 105, 0.25);">
                                        <i class="fas fa-check me-1" style="color: #059669;"></i> <span id="fileNameText">Image ready for update</span>
                                    </div>
                                </div>
                            </label>
                            @error('company_logo')
                                <div class="text-danger small mt-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Section 2: Core Details -->
                        <div class="section-badge">
                            <i class="fas fa-info-circle"></i> Official Organization Details
                        </div>

                        <div class="row g-4">
                            <!-- Company Name -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Company Name <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    <input type="text" name="company_name" id="input_company_name" class="form-control @error('company_name') is-invalid @enderror"
                                        placeholder="Enter company name"
                                        value="{{ old('company_name', $company->company_name ?? '') }}"
                                        {{ $isSettingsReadOnly ? 'readonly' : 'required' }}>
                                </div>
                                @error('company_name')
                                    <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Company Email -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Company Email <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom @error('company_email') border-danger @enderror" id="emailGroup">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="company_email" id="input_company_email" class="form-control @error('company_email') is-invalid @enderror"
                                        placeholder="admin@company.com"
                                        value="{{ old('company_email', $company->company_email ?? '') }}"
                                        {{ $isSettingsReadOnly ? 'readonly' : 'required' }}>
                                </div>
                                <div id="emailValidationMsg" class="validation-feedback-pill @error('company_email') is-visible @enderror">
                                    <i class="fas fa-exclamation-circle"></i> <span id="emailValidationText">{{ $errors->first('company_email') ?: 'Please enter a valid email address (e.g. info@company.com)' }}</span>
                                </div>
                            </div>

                            <!-- Company Phone with All Countries Code -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Company Phone <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom phone-combo-group @if($errors->has('company_phone') || $errors->has('company_phone_number') || $errors->has('company_country_code')) border-danger @endif" id="phoneGroup">
                                    <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                                    <div class="country-code-wrapper">
                                        <select name="company_country_code" id="company_country_code" class="form-select country-code-select" {{ $isSettingsReadOnly ? 'disabled' : 'required' }}>
                                            @foreach($countryMap as $countryName => $meta)
                                                @php
                                                    $dialCode = $meta['dial_code'];
                                                    $iso = strtolower($meta['iso']);
                                                    $flag = 'https://flagcdn.com/w20/' . $iso . '.png';
                                                    $minD = $meta['min_digits'] ?? 6;
                                                    $maxD = $meta['max_digits'] ?? 15;
                                                    $isCurrentSelected = (old('company_country_code', $selectedCountryCode) === $dialCode);
                                                @endphp
                                                <option value="{{ $dialCode }}" data-country="{{ $countryName }}" data-flag="{{ $flag }}" data-min-digits="{{ $minD }}" data-max-digits="{{ $maxD }}" {{ $isCurrentSelected ? 'selected' : '' }}>
                                                    {{ $countryName }} ({{ $dialCode }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input type="text" name="company_phone_number" id="input_company_phone"
                                        class="form-control @error('company_phone_number') is-invalid @enderror @error('company_phone') is-invalid @enderror"
                                        placeholder="98765 43210"
                                        value="{{ old('company_phone_number', $phoneDigits) }}"
                                        {{ $isSettingsReadOnly ? 'readonly' : 'required' }} maxlength="18">
                                    <input type="hidden" name="company_phone" id="hidden_company_phone" value="{{ old('company_phone', $company->company_phone ?? '') }}">
                                </div>
                                <div id="phoneValidationMsg" class="validation-feedback-pill @if($errors->has('company_phone') || $errors->has('company_phone_number') || $errors->has('company_country_code')) is-visible @endif">
                                    <i class="fas fa-exclamation-circle"></i> <span id="phoneValidationText">{{ $errors->first('company_phone_number') ?: ($errors->first('company_phone') ?: ($errors->first('company_country_code') ?: 'Please enter a valid phone number.')) }}</span>
                                </div>
                            </div>

                            <!-- Company Website -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Company Website</label>
                                <div class="input-group input-group-custom @error('company_website') border-danger @enderror" id="websiteGroup">
                                    <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                    <input type="text" name="company_website" id="input_company_website" class="form-control @error('company_website') is-invalid @enderror"
                                        placeholder="https://company.com"
                                        value="{{ old('company_website', $company->company_website ?? '') }}"
                                        {{ $isSettingsReadOnly ? 'readonly' : '' }}>
                                </div>
                                <div id="websiteValidationMsg" class="validation-feedback-pill @error('company_website') is-visible @enderror">
                                    <i class="fas fa-exclamation-circle"></i> <span id="websiteValidationText">{{ $errors->first('company_website') ?: 'Please enter a valid website URL (e.g. https://company.com)' }}</span>
                                </div>
                            </div>

                            <!-- Company Location -->
                            <div class="col-md-12">
                                <label class="form-label-custom">Company Location / Address</label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <input type="text" name="company_location" id="input_company_location" class="form-control @error('company_location') is-invalid @enderror"
                                        placeholder="Enter physical location or office address (e.g. 100 Main St, New York, NY)"
                                        value="{{ old('company_location', $company->company_location ?? '') }}"
                                        {{ $isSettingsReadOnly ? 'readonly' : '' }}>
                                </div>
                                @error('company_location')
                                    <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="mt-5 pt-4 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                            @if($isSettingsReadOnly)
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge rounded-pill px-3.5 py-2 fw-bold" style="background: #f1f5f9; color: #64748b; font-size: 13px; border: 1px solid #cbd5e1;">
                                        <i class="fas fa-lock me-1.5 text-muted"></i> Read-Only (Admin Managed)
                                    </span>
                                </div>
                            @else
                                <div class="d-flex align-items-center gap-2">
                                    <button type="submit" class="btn-save-address">
                                        <i class="fas fa-save me-1.5"></i> {{ ($company && $company->id) ? 'Update Settings' : 'Save Settings' }}
                                    </button>
                                </div>

                                @if($company && $company->id)
                                    <button type="button" class="btn rounded-pill px-3.5 py-2 fw-bold" style="background: #fef2f2; color: #b91c1c; border: 1px solid rgba(220, 38, 38, 0.25);" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="fas fa-trash-alt me-1"></i> Reset Settings
                                    </button>
                                @endif
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Confirmation Modal -->
    @if($company && $company->id)
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
           <form method="POST" action="{{ route('settings.company.destroy') }}">
                @csrf
                @method('DELETE')

                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header border-bottom py-3 px-4" style="background: #fef2f2;">
                        <h5 class="modal-title fw-bold text-danger d-flex align-items-center"><i class="fas fa-exclamation-circle me-2 fs-4"></i>Confirm Reset</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="mb-0 text-secondary">Are you sure you want to reset all company settings? This action will remove your uploaded company logo and restore default profile details.</p>
                    </div>
                    <div class="modal-footer border-top p-3" style="background: #fafefb;">
                        <button type="button" class="btn rounded-pill px-3.5 fw-bold" style="background: #f1f5f9; color: #475569;" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Yes, Reset Settings</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Format Select2 country code options with flag and dial code
    function formatCountryCodeOption(state) {
        if (!state.id) return state.text;
        const flag = $(state.element).data("flag");
        const country = $(state.element).data("country");
        const code = state.id;
        if (flag) {
            return $('<span><img src="' + flag + '" width="20" height="14" style="object-fit: cover; border-radius: 2px; margin-right: 8px; vertical-align: middle;"/> ' + country + ' <strong style="color: #059669;">(' + code + ')</strong></span>');
        }
        return state.text;
    }

    function formatCountryCodeSelection(state) {
        if (!state.id) return state.text;
        const flag = $(state.element).data("flag");
        const code = state.id;
        if (flag) {
            return $('<span><img src="' + flag + '" width="18" height="12" style="object-fit: cover; border-radius: 2px; margin-right: 6px; vertical-align: middle;"/> <strong>' + code + '</strong></span>');
        }
        return code;
    }

    // Live logo preview function
    function previewLogo(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const liveLogo = document.getElementById('liveBannerLogo');
                if (liveLogo) {
                    liveLogo.src = e.target.result;
                    liveLogo.parentElement.style.animation = 'pulseGlow 1s ease';
                }

                const badge = document.getElementById('fileSelectedBadge');
                const badgeText = document.getElementById('fileNameText');
                const currentBadge = document.getElementById('currentLogoBadge');

                if (currentBadge) {
                    currentBadge.classList.add('d-none');
                }

                if (badge && badgeText) {
                    badgeText.innerText = file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
                    badge.classList.remove('d-none');
                }
            }
            
            reader.readAsDataURL(file);
        }
    }

    // Drag and Drop interaction for logo dropzone
    const dropzone = document.getElementById('logoDropzone');
    const fileInput = document.getElementById('company_logo_input');

    if (dropzone && fileInput) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('dragover');
            }, false);
        });

        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(files[0]);
                fileInput.files = dataTransfer.files;
                previewLogo(fileInput);
            }
        }, false);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('companyProfileForm');
        const nameInput = document.getElementById('input_company_name');
        const emailInput = document.getElementById('input_company_email');
        const phoneInput = document.getElementById('input_company_phone');
        const countryCodeSelect = document.getElementById('company_country_code');
        const hiddenPhoneInput = document.getElementById('hidden_company_phone');
        const websiteInput = document.getElementById('input_company_website');
        const locationInput = document.getElementById('input_company_location');

        const emailGroup = document.getElementById('emailGroup');
        const emailMsg = document.getElementById('emailValidationMsg');
        const emailText = document.getElementById('emailValidationText');

        const phoneGroup = document.getElementById('phoneGroup');
        const phoneMsg = document.getElementById('phoneValidationMsg');
        const phoneText = document.getElementById('phoneValidationText');

        const websiteGroup = document.getElementById('websiteGroup');
        const websiteMsg = document.getElementById('websiteValidationMsg');
        const websiteText = document.getElementById('websiteValidationText');

        function getSelectedCountryDigitRules() {
            const selectedOption = $('#company_country_code option:selected');
            const minDigits = parseInt(selectedOption.attr('data-min-digits')) || 6;
            const maxDigits = parseInt(selectedOption.attr('data-max-digits')) || 15;
            const country = selectedOption.attr('data-country') || 'Selected Country';
            const code = $('#company_country_code').val() || '+91';
            return { minDigits, maxDigits, country, code };
        }

        function updatePhoneInputAttributes() {
            const rules = getSelectedCountryDigitRules();
            const expectedDesc = (rules.minDigits === rules.maxDigits)
                ? `${rules.minDigits} digits`
                : `${rules.minDigits}-${rules.maxDigits} digits`;

            if (phoneInput) {
                phoneInput.placeholder = `e.g. ${expectedDesc} for ${rules.country}`;
                phoneInput.maxLength = rules.maxDigits + 4;
            }
        }

        // Initialize Select2 on Country Code
        if ($('#company_country_code').length) {
            $('#company_country_code').select2({
                theme: "bootstrap-5",
                templateResult: formatCountryCodeOption,
                templateSelection: formatCountryCodeSelection,
                width: '100%',
                dropdownAutoWidth: true,
                dropdownParent: $(document.body)
            }).on('change', function () {
                updatePhoneInputAttributes();
                syncFullPhone();
                if (phoneInput && phoneInput.value.trim().length > 0) {
                    validatePhoneField();
                }
            });
        }

        // Phone Sync helper
        function syncFullPhone() {
            const code = $('#company_country_code').val() || '+91';
            const digits = (phoneInput ? phoneInput.value.trim() : '');
            if (hiddenPhoneInput) {
                hiddenPhoneInput.value = digits ? (code + ' ' + digits) : '';
            }
            const livePhone = document.getElementById('liveCompanyPhone');
            if (livePhone) {
                livePhone.innerHTML = '<i class="fas fa-phone-alt text-success"></i> ' + (digits ? (code + ' ' + digits) : '+1 (234) 567-890');
            }
        }

        // Validation helper functions
        function validateEmailField() {
            const val = emailInput ? emailInput.value.trim() : '';
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!val) {
                showError(emailGroup, emailMsg, emailText, 'Company email is required.');
                return false;
            } else if (!emailRegex.test(val)) {
                showError(emailGroup, emailMsg, emailText, 'Please enter a valid email address with a domain (e.g. info@company.com).');
                return false;
            } else {
                clearError(emailGroup, emailMsg);
                return true;
            }
        }

        function validatePhoneField() {
            const rawDigits = phoneInput ? phoneInput.value.trim() : '';
            const digits = rawDigits.replace(/\D/g, '');
            const rules = getSelectedCountryDigitRules();

            if (!rawDigits) {
                showError(phoneGroup, phoneMsg, phoneText, 'Company phone number is required.');
                return false;
            }

            const expectedDesc = (rules.minDigits === rules.maxDigits)
                ? `exactly ${rules.minDigits} digits`
                : `between ${rules.minDigits} and ${rules.maxDigits} digits`;

            if (digits.length < rules.minDigits || digits.length > rules.maxDigits) {
                showError(phoneGroup, phoneMsg, phoneText, `Phone number for ${rules.country} (${rules.code}) must have ${expectedDesc} (currently entered ${digits.length} digits).`);
                return false;
            } else {
                clearError(phoneGroup, phoneMsg);
                return true;
            }
        }

        function validateWebsiteField() {
            let val = websiteInput ? websiteInput.value.trim() : '';
            if (!val) {
                clearError(websiteGroup, websiteMsg);
                return true;
            }
            // Auto prepend https:// if protocol missing and domain is valid
            if (!/^https?:\/\//i.test(val) && /^([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}/i.test(val)) {
                val = 'https://' + val;
                websiteInput.value = val;
            }
            const urlRegex = /^(https?:\/\/)?([a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}(\/.*)?$/i;
            if (!urlRegex.test(val)) {
                showError(websiteGroup, websiteMsg, websiteText, 'Please enter a valid website URL (e.g. https://example.com).');
                return false;
            } else {
                clearError(websiteGroup, websiteMsg);
                return true;
            }
        }

        function showError(groupEl, msgEl, textEl, message) {
            if (groupEl) {
                groupEl.classList.add('border-danger');
            }
            if (msgEl) {
                msgEl.classList.add('is-visible');
            }
            if (textEl) {
                textEl.innerText = message;
            }
        }

        function clearError(groupEl, msgEl) {
            if (groupEl) {
                groupEl.classList.remove('border-danger');
            }
            if (msgEl) {
                msgEl.classList.remove('is-visible');
            }
        }

        // Live input listeners
        if (nameInput) {
            nameInput.addEventListener('input', function() {
                document.getElementById('liveCompanyName').innerText = this.value.trim() || 'Your Company Name';
            });
        }

        if (emailInput) {
            emailInput.addEventListener('input', function() {
                const liveEmail = document.getElementById('liveCompanyEmail');
                if (liveEmail) {
                    liveEmail.innerHTML = '<i class="fas fa-envelope text-success"></i> ' + (this.value.trim() || 'email@company.com');
                }
            });
            emailInput.addEventListener('blur', validateEmailField);
        }

        if (phoneInput) {
            // Allow only digits, space, hyphen, parentheses, plus
            phoneInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9\s\-()]/g, '');
                syncFullPhone();
                if (phoneMsg && phoneMsg.classList.contains('is-visible')) {
                    validatePhoneField();
                }
            });
            phoneInput.addEventListener('blur', validatePhoneField);
        }

        if (websiteInput) {
            websiteInput.addEventListener('input', function() {
                const liveBtn = document.getElementById('liveCompanyWebsiteBtn');
                if (liveBtn) {
                    liveBtn.href = this.value.trim() || '#';
                }
            });
            websiteInput.addEventListener('blur', validateWebsiteField);
        }

        if (locationInput) {
            locationInput.addEventListener('input', function() {
                const liveLoc = document.getElementById('liveCompanyLocation');
                if (liveLoc) {
                    liveLoc.innerHTML = '<i class="fas fa-map-marker-alt text-success"></i> ' + (this.value.trim() || 'Location not specified');
                }
            });
        }

        // Form submission client validation
        if (form) {
            form.addEventListener('submit', function(e) {
                syncFullPhone();
                const isEmailValid = validateEmailField();
                const isPhoneValid = validatePhoneField();
                const isWebsiteValid = validateWebsiteField();

                if (!isEmailValid || !isPhoneValid || !isWebsiteValid) {
                    e.preventDefault();
                    if (!isEmailValid && emailInput) {
                        emailInput.focus();
                    } else if (!isPhoneValid && phoneInput) {
                        phoneInput.focus();
                    } else if (!isWebsiteValid && websiteInput) {
                        websiteInput.focus();
                    }
                }
            });
        }

        // Initial setup on load
        updatePhoneInputAttributes();
        syncFullPhone();
    });
</script>
@endpush
