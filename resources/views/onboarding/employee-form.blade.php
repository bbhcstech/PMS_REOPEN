<!doctype html>
<html lang="en" data-pms-theme="light">
<head>
    @include('partials.pwa')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Employee Self-Onboarding — {{ $company->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <script>
        (function () {
            var theme = localStorage.getItem('pms-theme') || localStorage.getItem('bitroxia-theme') || 'light';
            document.documentElement.setAttribute('data-pms-theme', theme);
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    <style>
        :root {
            --ob-bg: #f3f6fc;
            --ob-surface: #ffffff;
            --ob-subtle: #f8fafc;
            --ob-border: #dce5f1;
            --ob-ink: #0f172a;
            --ob-muted: #52637b;
            --ob-primary: #2563eb;
            --ob-primary-hover: #1d4ed8;
            --ob-primary-soft: rgba(37, 99, 235, 0.1);
            --ob-warn-bg: #fffbeb;
            --ob-warn-border: #fcd34d;
            --ob-warn-ink: #92400e;
        }
        html[data-pms-theme="dark"] {
            color-scheme: dark;
            --ob-bg: #070b1a;
            --ob-surface: #0f1530;
            --ob-subtle: #141b3d;
            --ob-border: rgba(238, 241, 251, 0.12);
            --ob-ink: #eef1fb;
            --ob-muted: #9aa3c7;
            --ob-primary: #3b82f6;
            --ob-primary-hover: #60a5fa;
            --ob-primary-soft: rgba(59, 130, 246, 0.18);
            --ob-warn-bg: rgba(245, 158, 11, 0.14);
            --ob-warn-border: rgba(251, 191, 36, 0.35);
            --ob-warn-ink: #fde68a;
        }
        * { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        body { background: var(--ob-bg); color: var(--ob-ink); min-height: 100vh; padding-bottom: 48px; }
        .ob-shell { max-width: 1080px; margin: 0 auto; padding: 24px 16px; }
        .ob-hero {
            background: linear-gradient(120deg, #122544 18%, #1e40af 62%, #2563eb 100%);
            color: #fff;
            border-radius: 22px;
            padding: 28px 30px;
            margin-bottom: 22px;
            box-shadow: 0 18px 40px rgba(18, 37, 68, 0.22);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }
        .ob-brand { display: flex; align-items: center; gap: 16px; min-width: 0; }
        .ob-logo-wrap {
            width: 56px; height: 56px; border-radius: 16px;
            background: rgba(255,255,255,0.16); border: 1px solid rgba(255,255,255,0.28);
            display: grid; place-items: center; overflow: hidden; flex-shrink: 0;
        }
        .ob-logo-wrap img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .ob-hero h1 { font-size: clamp(1.3rem, 2.5vw, 1.8rem); font-weight: 800; margin: 0; }
        .ob-hero p { margin: 4px 0 0; color: rgba(255,255,255,0.88); font-size: 0.92rem; }
        .ob-theme-btn {
            background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);
            color: #fff; border-radius: 999px; padding: 8px 14px; font-weight: 600; font-size: 0.85rem; cursor: pointer;
        }
        .ob-progress-bar {
            display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-bottom: 22px;
        }
        .ob-step-pill {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 14px; border-radius: 14px;
            background: var(--ob-surface); border: 1px solid var(--ob-border);
            font-size: 0.82rem; font-weight: 700; color: var(--ob-muted);
            text-decoration: none;
        }
        .ob-step-pill.is-active {
            border-color: var(--ob-primary); color: var(--ob-primary); background: var(--ob-primary-soft);
        }
        .ob-step-num {
            width: 24px; height: 24px; border-radius: 50%; display: grid; place-items: center;
            background: var(--ob-primary); color: #fff; font-size: 0.75rem; flex-shrink: 0;
        }
        .ob-card {
            background: var(--ob-surface); border: 1px solid var(--ob-border);
            border-radius: 20px; padding: 24px; margin-bottom: 20px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        }
        .ob-section-head {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding-bottom: 14px; margin-bottom: 18px; border-bottom: 1px solid var(--ob-border);
        }
        .ob-section-head h2 {
            font-size: 1.08rem; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 10px; color: var(--ob-ink);
        }
        .ob-section-head h2 i { color: var(--ob-primary); }
        .ob-label {
            font-weight: 700; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.03em;
            color: var(--ob-ink); margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between; gap: 8px;
        }
        .badge-req {
            background: rgba(239, 68, 68, 0.12); color: #dc2626; font-size: 0.65rem;
            padding: 2px 7px; border-radius: 5px; font-weight: 700;
        }
        .badge-opt {
            background: var(--ob-subtle); color: var(--ob-muted); border: 1px solid var(--ob-border);
            font-size: 0.65rem; padding: 2px 7px; border-radius: 5px; font-weight: 600;
        }
        html[data-pms-theme="dark"] .badge-req { color: #fca5a5; }
        .ob-input, .ob-select {
            background: var(--ob-surface) !important; color: var(--ob-ink) !important;
            border: 1.5px solid var(--ob-border) !important; border-radius: 12px !important;
            padding: 0.65rem 0.9rem !important; font-size: 0.92rem !important;
        }
        .ob-input:focus, .ob-select:focus {
            border-color: var(--ob-primary) !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18) !important;
        }
        .ob-input[readonly] { background: var(--ob-subtle) !important; opacity: 0.9; }
        .ob-correction-banner {
            background: var(--ob-warn-bg); border: 1px solid var(--ob-warn-border); color: var(--ob-warn-ink);
            border-radius: 16px; padding: 16px 20px; margin-bottom: 20px;
        }
        .ob-otp-panel {
            margin-top: 10px; padding: 14px; border-radius: 12px;
            background: var(--ob-subtle); border: 1px solid var(--ob-border);
        }
        .ob-preview-thumb {
            margin-top: 8px; max-height: 92px; border-radius: 10px; border: 1px solid var(--ob-border); display: none;
        }
        .ob-admin-note {
            background: var(--ob-subtle); border: 1px dashed var(--ob-border); border-radius: 14px;
            padding: 14px 18px; color: var(--ob-muted); font-size: 0.86rem; margin-bottom: 20px;
        }
        .ob-submit-bar {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
            padding: 20px 24px; background: var(--ob-surface); border: 1px solid var(--ob-border); border-radius: 18px;
        }
        .ob-btn-submit {
            background: var(--ob-primary); border: none; color: #fff; font-weight: 700;
            padding: 12px 28px; border-radius: 12px; font-size: 0.96rem; cursor: pointer;
        }
        .ob-btn-submit:hover { background: var(--ob-primary-hover); }
        @media (max-width: 767.98px) {
            .ob-progress-bar { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>
</head>
<body>
@php
    $companyName = $company->name ?: config('app.name');
    $logoUrl = method_exists($company, 'logoUrl') ? $company->logoUrl() : null;
    $boundEmail = $isCorrection ? ($application->email ?? '') : ($invitation?->email ?? '');
    $isEmailLocked = !empty($boundEmail);
    $requiresEmailOtp = !$isCorrection && empty($invitation?->email);
    $isEmailVerified = $isEmailLocked || (bool) $invitation?->email_verified_at;
    $selectedCountry = old('country', $application?->country ?? 'India');
    $selectedDialCode = old('mobile_country_code', $application?->mobile_country_code ?? '+91');
    $selectedGender = old('gender', $application?->gender ?? '');
    $selectedMarital = old('marital_status', $application?->marital_status ?? '');
    $selectedLang = old('language', $application?->language ?? 'en');
    $selectedDob = old('dob', $application?->dob ? $application->dob->format('Y-m-d') : '');
@endphp

<div class="ob-shell">
    <header class="ob-hero">
        <div class="ob-brand">
            <div class="ob-logo-wrap">
                @if(!empty($logoUrl))
                    <img src="{{ $logoUrl }}" alt="{{ $companyName }}">
                @else
                    <i class="fas fa-building fs-4"></i>
                @endif
            </div>
            <div>
                <h1>{{ $isCorrection ? 'Update Onboarding Application' : 'Employee Self-Onboarding' }}</h1>
                <p>Complete your onboarding profile for <strong>{{ $companyName }}</strong>. HR will review and activate your account.</p>
            </div>
        </div>
        <button type="button" class="ob-theme-btn" id="obThemeToggle">
            <i class="fas fa-circle-half-stroke me-1"></i> Toggle Theme
        </button>
    </header>

    @if(!empty($invitation?->personal_message) && !$isCorrection)
        <div class="ob-card mb-3" style="border-left:4px solid var(--ob-primary);">
            <div class="fw-bold mb-1"><i class="fas fa-comment-dots text-primary me-2"></i>Message from HR Administrator</div>
            <div style="color:var(--ob-muted);">{!! nl2br(e($invitation->personal_message)) !!}</div>
        </div>
    @endif

    @if($isCorrection && !empty($application?->correction_reason))
        <div class="ob-correction-banner" role="alert">
            <div class="fw-bold mb-1"><i class="fas fa-triangle-exclamation me-2"></i>Correction Requested by HR</div>
            <div>{!! nl2br(e($application->correction_reason)) !!}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger rounded-4 mb-4" role="alert">
            <div class="fw-bold mb-1"><i class="fas fa-circle-exclamation me-2"></i>Please correct the highlighted fields:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <nav class="ob-progress-bar" aria-label="Onboarding Sections">
        <a href="#section-a" class="ob-step-pill is-active"><span class="ob-step-num">A</span> Personal Details</a>
        <a href="#section-b" class="ob-step-pill"><span class="ob-step-num">B</span> Professional Info</a>
        <a href="#section-c" class="ob-step-pill"><span class="ob-step-num">C</span> Directory Profile</a>
        <a href="#section-d" class="ob-step-pill"><span class="ob-step-num">D</span> Verification Docs</a>
    </nav>

    <form method="POST" action="{{ $submitUrl }}" enctype="multipart/form-data" id="publicOnboardingForm">
        @csrf

        {{-- Section A — Personal Information --}}
        <section class="ob-card" id="section-a">
            <div class="ob-section-head">
                <h2><i class="fas fa-user-check"></i> Section A — Personal Information</h2>
                <span class="badge bg-primary bg-opacity-10 text-primary">Step 1 of 4</span>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="ob-label" for="ob_name">
                        <span>Full Name</span>
                        <span class="badge-req">Required</span>
                    </label>
                    <input type="text" id="ob_name" name="name" class="form-control ob-input" required maxlength="191"
                           value="{{ old('name', $application?->name ?? '') }}" placeholder="Enter your full legal name">
                </div>

                <div class="col-md-6">
                    <label class="ob-label" for="ob_email">
                        <span>Email Address</span>
                        <span class="badge-req">{{ $isEmailLocked ? 'Verified / Locked' : 'Required & Verified' }}</span>
                    </label>
                    <div class="input-group">
                        <input type="email" id="ob_email" name="email" class="form-control ob-input" required maxlength="191"
                               value="{{ old('email', $boundEmail) }}"
                               {{ $isEmailLocked ? 'readonly' : '' }}
                               placeholder="you@example.com">
                        @if($requiresEmailOtp)
                            <button type="button" class="btn btn-outline-primary" id="sendOtpBtn">Send Code</button>
                        @endif
                    </div>

                    @if($requiresEmailOtp)
                        <div class="ob-otp-panel" id="otpVerificationBox" style="{{ $isEmailVerified ? 'display:none;' : '' }}">
                            <div class="small fw-semibold mb-2">Enter the 6-digit verification code sent to your email:</div>
                            <div class="input-group input-group-sm">
                                <input type="text" id="otpCodeInput" class="form-control ob-input" maxlength="6" placeholder="6-digit code">
                                <button type="button" class="btn btn-primary" id="verifyOtpBtn">Verify Email</button>
                            </div>
                            <div id="otpFeedback" class="small mt-2"></div>
                        </div>
                        <div id="otpVerifiedBadge" class="small text-success fw-semibold mt-1" style="{{ $isEmailVerified ? '' : 'display:none;' }}">
                            <i class="fas fa-circle-check me-1"></i> Email verified
                        </div>
                    @endif
                </div>

                <div class="col-md-4">
                    <label class="ob-label" for="country">
                        <span>Country</span>
                        <span class="badge-req">Required</span>
                    </label>
                    <select name="country" id="country" class="form-select ob-select" required>
                        <option value="">Select Country</option>
                        @foreach($countries as $country)
                            @php
                                $meta = \App\Support\CountryPhone::meta($country->name);
                            @endphp
                            <option value="{{ $country->name }}" data-dial-code="{{ $meta['dial_code'] }}" {{ $selectedCountry === $country->name ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="ob-label" for="mobile_only_digits">
                        <span>Mobile Number</span>
                        <span class="badge-req">Required</span>
                    </label>
                    <div class="input-group">
                        <select name="mobile_country_code" id="mobile_country_code" class="form-select ob-select" style="max-width:135px;" required>
                            @foreach($countries as $country)
                                @php $meta = \App\Support\CountryPhone::meta($country->name); @endphp
                                <option value="{{ $meta['dial_code'] }}" data-country="{{ $country->name }}"
                                        data-min-digits="{{ $meta['min_digits'] }}" data-max-digits="{{ $meta['max_digits'] }}"
                                        {{ $selectedDialCode === $meta['dial_code'] && $selectedCountry === $country->name ? 'selected' : '' }}>
                                    {{ $country->name }} ({{ $meta['dial_code'] }})
                                </option>
                            @endforeach
                        </select>
                        <input type="text" id="mobile_only_digits" name="mobile" class="form-control ob-input" required
                               value="{{ old('mobile', $application?->mobile ?? '') }}" placeholder="9876543210">
                    </div>
                    <small id="mobile_help_text" class="text-muted">Digits only, without leading zero.</small>
                </div>

                <div class="col-md-3">
                    <label class="ob-label" for="ob_dob">
                        <span>Date of Birth</span>
                        <span class="badge-req">Required</span>
                    </label>
                    <input type="date" id="ob_dob" name="dob" class="form-control ob-input" required value="{{ $selectedDob }}">
                </div>

                <div class="col-md-4">
                    <label class="ob-label" for="ob_gender">
                        <span>Gender</span>
                        <span class="badge-opt">Optional</span>
                    </label>
                    <select id="ob_gender" name="gender" class="form-select ob-select">
                        <option value="">Select</option>
                        @foreach(['Male', 'Female', 'Other', 'Prefer not to say'] as $g)
                            <option value="{{ $g }}" {{ $selectedGender === $g ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="ob-label" for="ob_marital">
                        <span>Marital Status</span>
                        <span class="badge-opt">Optional</span>
                    </label>
                    <select id="ob_marital" name="marital_status" class="form-select ob-select">
                        <option value="">Select</option>
                        @foreach(['single' => 'Single', 'married' => 'Married', 'widower' => 'Widower', 'widow' => 'Widow', 'separate' => 'Separate', 'divorced' => 'Divorced', 'engaged' => 'Engaged'] as $val => $label)
                            <option value="{{ $val }}" {{ $selectedMarital === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="ob-label" for="ob_language">
                        <span>Language</span>
                        <span class="badge-opt">Optional</span>
                    </label>
                    <select id="ob_language" name="language" class="form-select ob-select">
                        @foreach(['en' => 'English', 'bn' => 'Bengali', 'hi' => 'Hindi', 'fr' => 'French', 'de' => 'German'] as $code => $langName)
                            <option value="{{ $code }}" {{ $selectedLang === $code ? 'selected' : '' }}>{{ $langName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="ob-label" for="ob_address">
                        <span>Personal Address</span>
                        <span class="badge-opt">Optional</span>
                    </label>
                    <textarea id="ob_address" name="address" class="form-control ob-input" rows="2" placeholder="Residential street, city, state, postal code">{{ old('address', $application?->address ?? '') }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="ob-label" for="ob_profile_picture">
                        <span>Profile Picture</span>
                        <span class="{{ empty($application?->profile_picture_path) ? 'badge-req' : 'badge-opt' }}">
                            {{ empty($application?->profile_picture_path) ? 'Required' : 'Optional (Keep Existing)' }}
                        </span>
                    </label>
                    <input type="file" id="ob_profile_picture" name="profile_picture" class="form-control ob-input"
                           accept=".jpeg,.jpg,.png,image/jpeg,image/png"
                           {{ empty($application?->profile_picture_path) ? 'required' : '' }}>
                    <small class="text-muted d-block mt-1">JPEG or PNG, max 4 MB.
                        @if(!empty($application?->profile_picture_name))
                            Current file: <strong>{{ $application->profile_picture_name }}</strong>
                        @endif
                    </small>
                    <img id="preview_profile_picture" class="ob-preview-thumb" alt="Profile preview">
                </div>
            </div>
        </section>

        {{-- Section B — Professional Information --}}
        <section class="ob-card" id="section-b">
            <div class="ob-section-head">
                <h2><i class="fas fa-briefcase"></i> Section B — Professional Information</h2>
                <span class="badge bg-primary bg-opacity-10 text-primary">Step 2 of 4</span>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="ob-label" for="ob_skills">
                        <span>Skills</span>
                        <span class="badge-opt">Optional</span>
                    </label>
                    <textarea id="ob_skills" name="skills" class="form-control ob-input" rows="3"
                              placeholder="Comma separated skills (e.g., Laravel, PHP, MySQL, React)">{{ old('skills', $application?->skills ?? '') }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="ob-label" for="ob_about">
                        <span>Professional Summary / About</span>
                        <span class="badge-opt">Optional</span>
                    </label>
                    <textarea id="ob_about" name="about" class="form-control ob-input" rows="3"
                              placeholder="Summary of experience, background, and qualifications">{{ old('about', $application?->about ?? '') }}</textarea>
                </div>
            </div>
        </section>

        {{-- Section C — Directory Profile --}}
        <section class="ob-card" id="section-c">
            <div class="ob-section-head">
                <h2><i class="fas fa-address-card"></i> Section C — Directory Profile</h2>
                <span class="badge bg-primary bg-opacity-10 text-primary">Step 3 of 4</span>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <label class="ob-label" for="ob_directory_about">
                        <span>Public About (Subject to HR Review)</span>
                        <span class="badge-opt">Optional</span>
                    </label>
                    <textarea id="ob_directory_about" name="directory_about" class="form-control ob-input" rows="3"
                              placeholder="Short public bio for the internal organization directory">{{ old('directory_about', $application?->directory_about ?? '') }}</textarea>
                </div>

                <div class="col-md-4">
                    <label class="ob-label" for="ob_linkedin"><span><i class="fab fa-linkedin me-1"></i>LinkedIn</span><span class="badge-opt">Optional</span></label>
                    <input type="url" id="ob_linkedin" name="linkedin_url" class="form-control ob-input" value="{{ old('linkedin_url', $application?->linkedin_url ?? '') }}" placeholder="https://linkedin.com/in/username">
                </div>

                <div class="col-md-4">
                    <label class="ob-label" for="ob_portfolio"><span><i class="fas fa-globe me-1"></i>Portfolio</span><span class="badge-opt">Optional</span></label>
                    <input type="url" id="ob_portfolio" name="portfolio_url" class="form-control ob-input" value="{{ old('portfolio_url', $application?->portfolio_url ?? '') }}" placeholder="https://portfolio.example.com">
                </div>

                <div class="col-md-4">
                    <label class="ob-label" for="ob_cv_file"><span><i class="fas fa-file-lines me-1"></i>CV / Resume</span><span class="badge-opt">Optional</span></label>
                    <input type="file" id="ob_cv_file" name="cv_file" class="form-control ob-input" accept=".pdf,.doc,.docx,application/pdf">
                    <small class="text-muted">PDF, DOC, DOCX (max 4 MB).
                        @if(!empty($application?->cv_file_name))
                            Current: <strong>{{ $application->cv_file_name }}</strong>
                        @endif
                    </small>
                </div>

                <div class="col-md-4">
                    <label class="ob-label" for="ob_facebook"><span><i class="fab fa-facebook me-1"></i>Facebook</span><span class="badge-opt">Optional</span></label>
                    <input type="url" id="ob_facebook" name="facebook_url" class="form-control ob-input" value="{{ old('facebook_url', $application?->facebook_url ?? '') }}" placeholder="https://facebook.com/username">
                </div>

                <div class="col-md-4">
                    <label class="ob-label" for="ob_instagram"><span><i class="fab fa-instagram me-1"></i>Instagram</span><span class="badge-opt">Optional</span></label>
                    <input type="url" id="ob_instagram" name="instagram_url" class="form-control ob-input" value="{{ old('instagram_url', $application?->instagram_url ?? '') }}" placeholder="https://instagram.com/username">
                </div>

                <div class="col-md-4">
                    <label class="ob-label" for="ob_x"><span><i class="fab fa-x-twitter me-1"></i>X / Twitter</span><span class="badge-opt">Optional</span></label>
                    <input type="url" id="ob_x" name="x_url" class="form-control ob-input" value="{{ old('x_url', $application?->x_url ?? '') }}" placeholder="https://x.com/username">
                </div>
            </div>
        </section>

        {{-- Section D — Additional Information and Documents --}}
        <section class="ob-card" id="section-d">
            <div class="ob-section-head">
                <h2><i class="fas fa-id-card"></i> Section D — Verification Documents</h2>
                <span class="badge bg-primary bg-opacity-10 text-primary">Step 4 of 4</span>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="ob-label" for="ob_gov_id">
                        <span>Government ID Card (Stored Privately for HR Verification)</span>
                        <span class="badge-opt">Optional</span>
                    </label>
                    <input type="file" id="ob_gov_id" name="government_id_card" class="form-control ob-input" accept=".jpeg,.jpg,.png,image/jpeg,image/png">
                    <small class="text-muted d-block mt-1">JPEG or PNG, max 4 MB.
                        @if(!empty($application?->government_id_card_name))
                            Current file: <strong>{{ $application->government_id_card_name }}</strong>
                        @endif
                    </small>
                    <img id="preview_gov_id" class="ob-preview-thumb" alt="Government ID preview">
                </div>
            </div>
        </section>

        <div class="ob-admin-note">
            <i class="fas fa-shield-halved me-2 text-primary"></i>
            <strong>Administrative Assignment:</strong> Employee ID, Department, Designation, Reporting Manager, Joining Date, and Portal Permissions will be assigned securely by {{ $companyName }} HR during application review.
        </div>

        <div class="ob-submit-bar">
            <div class="small" style="color:var(--ob-muted);">
                By submitting, you confirm that the provided information and documents are accurate.
            </div>
            <button type="submit" class="ob-btn-submit" id="submitApplicationBtn">
                <i class="fas fa-paper-plane me-2"></i>{{ $isCorrection ? 'Resubmit Application' : 'Submit Application' }}
            </button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
document.getElementById('obThemeToggle')?.addEventListener('click', function () {
    const cur = document.documentElement.getAttribute('data-pms-theme') === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-pms-theme', cur);
    document.documentElement.setAttribute('data-bs-theme', cur);
    localStorage.setItem('pms-theme', cur);
});

function bindImagePreview(inputId, previewId) {
    const input = document.getElementById(inputId);
    const img = document.getElementById(previewId);
    if (!input || !img) return;
    input.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) { img.style.display = 'none'; return; }
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; img.style.display = 'block'; };
        reader.readAsDataURL(file);
    });
}
bindImagePreview('ob_profile_picture', 'preview_profile_picture');
bindImagePreview('ob_gov_id', 'preview_gov_id');

document.getElementById('country')?.addEventListener('change', function () {
    const codeSelect = document.getElementById('mobile_country_code');
    if (!codeSelect) return;
    const countryName = this.value;
    for (const opt of codeSelect.options) {
        if (opt.dataset.country === countryName) {
            opt.selected = true;
            break;
        }
    }
});

@if($requiresEmailOtp && $sendOtpUrl && $verifyOtpUrl)
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const feedback = document.getElementById('otpFeedback');
    const emailInput = document.getElementById('ob_email');
    const sendBtn = document.getElementById('sendOtpBtn');
    const verifyBtn = document.getElementById('verifyOtpBtn');

    sendBtn?.addEventListener('click', async function () {
        if (!emailInput.reportValidity()) return;
        sendBtn.disabled = true;
        feedback.className = 'small mt-2 text-muted';
        feedback.textContent = 'Sending verification code...';
        try {
            const res = await fetch(@json($sendOtpUrl), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ email: emailInput.value.trim() })
            });
            const data = await res.json();
            feedback.className = 'small mt-2 ' + (res.ok ? 'text-success' : 'text-danger');
            feedback.textContent = data.message || (res.ok ? 'Code sent.' : 'Failed to send code.');
        } catch (err) {
            feedback.className = 'small mt-2 text-danger';
            feedback.textContent = 'Network error sending verification code.';
        } finally {
            sendBtn.disabled = false;
        }
    });

    verifyBtn?.addEventListener('click', async function () {
        const otp = document.getElementById('otpCodeInput').value.trim();
        if (!/^\d{6}$/.test(otp)) {
            feedback.className = 'small mt-2 text-danger';
            feedback.textContent = 'Enter the 6-digit numeric code.';
            return;
        }
        verifyBtn.disabled = true;
        try {
            const res = await fetch(@json($verifyOtpUrl), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ email: emailInput.value.trim(), otp })
            });
            const data = await res.json();
            if (res.ok && data.verified) {
                emailInput.value = data.email;
                emailInput.readOnly = true;
                document.getElementById('otpVerificationBox').style.display = 'none';
                document.getElementById('otpVerifiedBadge').style.display = 'block';
                sendBtn.style.display = 'none';
            } else {
                feedback.className = 'small mt-2 text-danger';
                feedback.textContent = data.message || 'Verification failed.';
            }
        } catch (err) {
            feedback.className = 'small mt-2 text-danger';
            feedback.textContent = 'Network error verifying code.';
        } finally {
            verifyBtn.disabled = false;
        }
    });
})();
@endif

document.getElementById('publicOnboardingForm')?.addEventListener('submit', function () {
    const btn = document.getElementById('submitApplicationBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = 'Submitting...';
    }
});
</script>
</body>
</html>
