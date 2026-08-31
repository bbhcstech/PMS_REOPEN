@extends('admin.layout.app')

@section('title', 'Terms & Policy')

@push('styles')
<style>
    .terms-policy-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f4fbf7 100%);
        color: #0a2e1f;
    }

    .terms-policy-shell {
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

    .btn-header-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-view-public {
        background: linear-gradient(145deg, #059669, #047857);
        color: white !important;
        font-weight: 700;
        font-size: 0.9rem;
        border-radius: 40px;
        padding: 0.65rem 1.4rem;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 6px 18px -4px rgba(5, 150, 105, 0.35);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        border: none;
    }

    .btn-view-public:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px -4px rgba(5, 150, 105, 0.45);
        color: white !important;
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
    .terms-policy-page .stat-card,
    .terms-policy-page .stat-card:first-of-type {
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

    .terms-policy-page .stat-card:first-of-type *,
    .terms-policy-page .stat-card * {
        -webkit-text-fill-color: initial;
    }

    .terms-policy-page .stat-card h3,
    .terms-policy-page .stat-card:first-of-type h3 {
        color: #0a2e1f !important;
        -webkit-text-fill-color: #0a2e1f !important;
    }

    .terms-policy-page .stat-card h6,
    .terms-policy-page .stat-card span,
    .terms-policy-page .stat-card:first-of-type span,
    .terms-policy-page .stat-card:first-of-type h6 {
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

    .stat-icon.doc,
    .terms-policy-page .stat-card:first-of-type .stat-icon.doc {
        background: linear-gradient(145deg, #d1fae5, #a7f3d0) !important;
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
    }

    .stat-icon.date,
    .terms-policy-page .stat-card .stat-icon.date {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
    }

    .stat-icon.access,
    .terms-policy-page .stat-card .stat-icon.access {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
    }

    .stat-icon.length,
    .terms-policy-page .stat-card .stat-icon.length {
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

    .textarea-custom {
        border-radius: 16px;
        border: 1px solid rgba(16, 185, 129, 0.2);
        background-color: #fafefb;
        padding: 18px;
        font-size: 0.95rem;
        font-weight: 500;
        color: #0a2e1f;
        line-height: 1.7;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        width: 100%;
        resize: vertical;
    }

    .textarea-custom:focus {
        outline: none;
        border-color: #34d399;
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.15);
        transform: translateY(-1px);
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
        .terms-policy-page {
            padding: 1.25rem 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="terms-policy-page">
    <div class="terms-policy-shell">
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
                <span>Terms & Policy</span>
            </div>

            <!-- Page Header Card -->
            <div class="branches-header">
                <div class="header-left-box">
                    <div class="header-icon-badge">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <div class="header-title">
                        <h1>Terms & Policy Management</h1>
                        <p>Update the organization Terms & Conditions shown on login, registration, and compliance portals.</p>
                    </div>
                </div>

                <div class="btn-header-group">
                    <a href="{{ route('company.terms') }}" target="_blank" rel="noopener" class="btn-view-public">
                        <i class="fas fa-arrow-up-right-from-square me-1"></i> View Public Page
                    </a>
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

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm rounded-4 border-0" style="background: rgba(254, 226, 226, 0.95); color: #991b1b; border-left: 5px solid #ef4444 !important;" role="alert">
                    <i class="fas fa-exclamation-triangle fs-4 me-2"></i>
                    <div>{{ $errors->first() }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Executive Summary Stats Grid -->
            @php
                $termsTitle = old('legal_terms_title', $title ?? 'Terms & Conditions');
                $effDate = old('legal_terms_effective_date', $effectiveDate ?? date('Y-m-d'));
                $textLen = strlen(old('legal_terms_content', $content ?? ''));
                $wordCount = str_word_count(old('legal_terms_content', $content ?? ''));
            @endphp
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon doc">
                        <i class="fas fa-file-lines"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Document Title</h6>
                        <h3>{{ $termsTitle }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon date">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Effective Date</h6>
                        <h3>{{ $effDate ? date('d M, Y', strtotime($effDate)) : 'Immediate' }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon access">
                        <i class="fas fa-globe"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Public Visibility</h6>
                        <h3>Live on Login Portal</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon length">
                        <i class="fas fa-align-left"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Policy Length</h6>
                        <h3>{{ $wordCount }} Words</h3>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="address-card-elevated">
                <div class="card-header-custom">
                    <div class="card-header-avatar shadow-sm">
                        <i class="fas fa-scale-balanced"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold fs-5" style="color: #0a2e1f;">Terms & Conditions Content Editor</h5>
                        <small class="text-muted">Draft, publish, and schedule legally binding organization usage terms</small>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    @if($isSettingsReadOnly)
                        <div class="alert d-flex align-items-center mb-4 rounded-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #1e40af; border-left: 4px solid #3b82f6 !important; padding: 14px 18px;">
                            <i class="fas fa-eye me-3 fs-3 text-primary"></i>
                            <div>
                                <strong class="d-block text-primary fw-bold" style="font-size: 14px;">View-Only Mode</strong>
                                <span style="font-size: 13px; color: #1e3a8a;">You are viewing Terms & Conditions policy in read-only mode. Only Administrators have permission to modify legal policy content.</span>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.settings.terms-policy.update') }}">
                        @csrf
                        @method('PUT')

                        <!-- Section 1: Header Metadata -->
                        <div class="section-badge">
                            <i class="fas fa-heading"></i> Terms Header Metadata
                        </div>

                        <div class="row g-4 mb-5">
                            <!-- Title -->
                            <div class="col-md-8">
                                <label class="form-label-custom" for="legal_terms_title">Document Title <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-signature"></i></span>
                                    <input
                                        type="text"
                                        name="legal_terms_title"
                                        id="legal_terms_title"
                                        class="form-control @error('legal_terms_title') is-invalid @enderror"
                                        value="{{ old('legal_terms_title', $title) }}"
                                        {{ $isSettingsReadOnly ? 'readonly' : 'required' }}>
                                </div>
                                @error('legal_terms_title')
                                    <small class="text-danger mt-1 d-block">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Effective Date -->
                            <div class="col-md-4">
                                <label class="form-label-custom" for="legal_terms_effective_date">Effective Date</label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                                    <input
                                        type="date"
                                        name="legal_terms_effective_date"
                                        id="legal_terms_effective_date"
                                        class="form-control @error('legal_terms_effective_date') is-invalid @enderror"
                                        value="{{ old('legal_terms_effective_date', $effectiveDate) }}"
                                        {{ $isSettingsReadOnly ? 'readonly' : '' }}>
                                </div>
                                @error('legal_terms_effective_date')
                                    <small class="text-danger mt-1 d-block">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Section 2: Policy Text Content -->
                        <div class="section-badge">
                            <i class="fas fa-paragraph"></i> Legal Policy Text Content
                        </div>

                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label-custom" for="legal_terms_content">Policy Text Content <span class="req-asterisk">*</span></label>
                                <textarea
                                    name="legal_terms_content"
                                    id="legal_terms_content"
                                    rows="16"
                                    class="textarea-custom @error('legal_terms_content') is-invalid @enderror"
                                    {{ $isSettingsReadOnly ? 'readonly' : 'required' }}>{{ old('legal_terms_content', $content) }}</textarea>
                                <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle me-1" style="color: #059669;"></i>Use double line breaks to separate paragraphs. The public portal terms page updates immediately upon saving.</small>
                                @error('legal_terms_content')
                                    <small class="text-danger mt-1 d-block">{{ $message }}</small>
                                @enderror
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
                                    <i class="fas fa-save me-1.5"></i> Save Terms
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
