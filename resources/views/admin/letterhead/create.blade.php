@extends('admin.layout.app')

@section('title', 'Send ' . ($currentTemplate['name'] ?? 'Official') . ' Letter')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap');

    .leave-form-page {
        padding: 24px 30px;
        min-height: 100vh;
        background: linear-gradient(135deg, #F8FAFC 0%, #f7fbff 100%);
        color: #0F1530;
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .leave-breadcrumb, .leave-form-hero, .form-card {
        border: 1px solid rgba(47, 107, 255, 0.14);
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 16px 36px -20px rgba(15, 23, 42, 0.18);
    }

    .leave-breadcrumb {
        display: inline-flex;
        gap: 8px;
        align-items: center;
        padding: 10px 18px;
        border-radius: 14px;
        color: #2F6BFF;
        font-weight: 800;
        font-size: 0.88rem;
        margin-bottom: 20px;
    }
    .leave-breadcrumb a {
        color: #2F6BFF;
        text-decoration: none;
    }

    .leave-form-hero {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: center;
        padding: 24px 28px;
        border-radius: 20px;
        margin-bottom: 22px;
        position: relative;
        overflow: hidden;
    }
    .leave-form-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #10b981 0%, #2F6BFF 50%, #1e40af 100%);
    }

    .leave-form-hero h1 {
        margin: 0 0 6px;
        font-size: 30px;
        font-weight: 900;
        color: #0f172a;
        letter-spacing: -0.02em;
    }
    .leave-form-hero p {
        margin: 0;
        color: #64748b;
        font-weight: 600;
        font-size: 0.94rem;
    }

    .form-card {
        padding: 26px;
        border-radius: 22px;
    }

    .form-grid.single {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    label {
        display: block;
        color: #475569;
        text-transform: uppercase;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        margin-bottom: 6px;
    }
    label span {
        color: #dc2626;
    }

    .form-control, .form-select {
        min-height: 46px;
        border-radius: 12px;
        border: 1px solid #dbe7e1;
        font-weight: 600;
        font-size: 0.92rem;
        color: #0f172a;
        transition: all 0.2s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(47, 107, 255, 0.15);
    }

    textarea.form-control {
        font-family: inherit;
        white-space: pre-wrap;
        line-height: 1.6;
    }

    /* Action Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 12px;
        min-height: 44px;
        padding: 0.6rem 1.25rem;
        font-weight: 800;
        font-size: 0.88rem;
        border: 0;
        transition: all 0.2s ease;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-primary {
        background: linear-gradient(135deg, #10b981 0%, #2F6BFF 100%);
        color: #ffffff !important;
        box-shadow: 0 6px 18px rgba(47, 107, 255, 0.35);
    }
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 22px rgba(47, 107, 255, 0.45);
        color: #ffffff !important;
    }

    .btn-word {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff !important;
        box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35);
    }
    .btn-word:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 22px rgba(37, 99, 235, 0.45);
        color: #ffffff !important;
    }

    .btn-light, .btn-secondary {
        background: #F8FAFC;
        color: #2F6BFF;
        border: 1px solid rgba(47, 107, 255, 0.22);
    }
    .btn-light:hover, .btn-secondary:hover {
        background: #e1f5ec;
        color: #0a5c3a;
    }

    /* Letterhead Customization Box */
    .lh-custom-box {
        background: #f8fafc;
        border: 1px solid #E0E7FF;
        border-radius: 16px;
        padding: 18px;
        margin-top: 16px;
        margin-bottom: 16px;
        box-shadow: 0 4px 12px rgba(47, 107, 255, 0.05);
    }

    .upload-slot-card {
        background: #ffffff;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        padding: 14px;
        transition: all 0.2s ease;
    }
    .upload-slot-card:hover {
        border-color: #10b981;
        background: #fcfdfd;
    }

    /* Preview Sheet Mini Modal */
    .lh-preview-card-a4 {
        width: 100%;
        max-width: 480px;
        min-height: 680px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        box-shadow: 0 16px 40px rgba(0,0,0,0.12);
        border-radius: 6px;
        position: relative;
        padding: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        background-size: 100% 100%;
        background-repeat: no-repeat;
        background-position: center;
    }

    .modal-preview-header-img {
        width: 100%;
        height: 65px;
        object-fit: fill;
        display: block;
        flex-shrink: 0;
    }
    .modal-preview-footer-img {
        width: 100%;
        height: 55px;
        object-fit: fill;
        display: block;
        margin-top: auto;
        flex-shrink: 0;
    }
    .modal-preview-body {
        padding: 15px 25px;
        flex-grow: 1;
        z-index: 2;
    }

    @media (max-width: 992px) {
        .leave-form-page { padding: 16px; }
        .leave-form-hero, .form-grid.two { grid-template-columns: 1fr; flex-direction: column; align-items: flex-start; }
    }

    /* ===== DARK MODE OVERRIDES ===== */
    html[data-pms-theme="dark"] .leave-form-page,
    html[data-bs-theme="dark"] .leave-form-page {
        background: transparent !important;
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .leave-breadcrumb,
    html[data-bs-theme="dark"] .leave-breadcrumb {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.12) !important;
        color: #60A5FA !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3) !important;
    }
    html[data-pms-theme="dark"] .leave-breadcrumb a,
    html[data-bs-theme="dark"] .leave-breadcrumb a {
        color: #60A5FA !important;
    }
    html[data-pms-theme="dark"] .leave-breadcrumb span,
    html[data-bs-theme="dark"] .leave-breadcrumb span {
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .leave-form-hero,
    html[data-bs-theme="dark"] .leave-form-hero {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.12) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4) !important;
    }
    html[data-pms-theme="dark"] .leave-form-hero h1,
    html[data-bs-theme="dark"] .leave-form-hero h1 {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .leave-form-hero p,
    html[data-bs-theme="dark"] .leave-form-hero p {
        color: #9AA3C7 !important;
    }

    html[data-pms-theme="dark"] .form-card,
    html[data-bs-theme="dark"] .form-card {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.12) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4) !important;
    }

    html[data-pms-theme="dark"] .btn-light,
    html[data-pms-theme="dark"] .btn-secondary,
    html[data-bs-theme="dark"] .btn-light,
    html[data-bs-theme="dark"] .btn-secondary {
        background: #0F1530 !important;
        color: #EEF1FB !important;
        border: 1px solid rgba(238, 241, 251, 0.18) !important;
    }
    html[data-pms-theme="dark"] .btn-light:hover,
    html[data-pms-theme="dark"] .btn-secondary:hover,
    html[data-bs-theme="dark"] .btn-light:hover,
    html[data-bs-theme="dark"] .btn-secondary:hover {
        background: #1A2247 !important;
        color: #60A5FA !important;
        border-color: #10b981 !important;
    }
    html[data-pms-theme="dark"] .btn-light span,
    html[data-pms-theme="dark"] .btn-light i,
    html[data-pms-theme="dark"] .btn-secondary span,
    html[data-pms-theme="dark"] .btn-secondary i,
    html[data-bs-theme="dark"] .btn-light span,
    html[data-bs-theme="dark"] .btn-light i,
    html[data-bs-theme="dark"] .btn-secondary span,
    html[data-bs-theme="dark"] .btn-secondary i {
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .bg-light.border,
    html[data-bs-theme="dark"] .bg-light.border {
        background-color: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.12) !important;
    }
    html[data-pms-theme="dark"] .bg-light.border label,
    html[data-bs-theme="dark"] .bg-light.border label {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .bg-light.border .btn-light:not(.bg-success),
    html[data-bs-theme="dark"] .bg-light.border .btn-light:not(.bg-success) {
        background: #141B3D !important;
        color: #CBD5E1 !important;
        border: 1px solid rgba(238, 241, 251, 0.12) !important;
    }

    html[data-pms-theme="dark"] label,
    html[data-bs-theme="dark"] label {
        color: #9AA3C7 !important;
    }
    html[data-pms-theme="dark"] .form-control,
    html[data-pms-theme="dark"] .form-select,
    html[data-bs-theme="dark"] .form-control,
    html[data-bs-theme="dark"] .form-select {
        background-color: #0F1530 !important;
        color: #EEF1FB !important;
        border-color: rgba(238, 241, 251, 0.18) !important;
    }
    html[data-pms-theme="dark"] .form-control::placeholder,
    html[data-bs-theme="dark"] .form-control::placeholder {
        color: #64748B !important;
    }
    html[data-pms-theme="dark"] .form-select option,
    html[data-bs-theme="dark"] .form-select option {
        background-color: #0F1530 !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .form-select optgroup,
    html[data-bs-theme="dark"] .form-select optgroup {
        background-color: #0F1530 !important;
        color: #38bdf8 !important;
    }

    html[data-pms-theme="dark"] .btn-link.text-success,
    html[data-bs-theme="dark"] .btn-link.text-success {
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .lh-custom-box,
    html[data-bs-theme="dark"] .lh-custom-box {
        background: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.12) !important;
    }
    html[data-pms-theme="dark"] .upload-slot-card,
    html[data-bs-theme="dark"] .upload-slot-card {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.18) !important;
        color: #CBD5E1 !important;
    }
    html[data-pms-theme="dark"] .text-dark,
    html[data-bs-theme="dark"] .text-dark {
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .text-muted,
    html[data-bs-theme="dark"] .text-muted {
        color: #9AA3C7 !important;
    }

    html[data-pms-theme="dark"] .dropdown-menu,
    html[data-bs-theme="dark"] .dropdown-menu {
        background-color: #141B3D !important;
        border: 1px solid rgba(238, 241, 251, 0.15) !important;
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.5) !important;
    }
    html[data-pms-theme="dark"] .dropdown-item,
    html[data-bs-theme="dark"] .dropdown-item {
        color: #CBD5E1 !important;
    }
    html[data-pms-theme="dark"] .dropdown-item:hover,
    html[data-bs-theme="dark"] .dropdown-item:hover {
        background-color: #1A2247 !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .modal-content,
    html[data-bs-theme="dark"] .modal-content {
        background-color: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.15) !important;
        color: #EEF1FB !important;
    }
    html[data-pms-theme="dark"] .modal-header,
    html[data-pms-theme="dark"] .modal-footer,
    html[data-bs-theme="dark"] .modal-header,
    html[data-bs-theme="dark"] .modal-footer {
        background-color: #0F1530 !important;
        border-color: rgba(238, 241, 251, 0.12) !important;
    }
    html[data-pms-theme="dark"] .modal-body.bg-light,
    html[data-bs-theme="dark"] .modal-body.bg-light {
        background-color: #0B0F19 !important;
    }
    #headerFooterSlots .upload-slot-label { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    #headerFooterSlots .upload-slot-label .badge { white-space: nowrap; line-height: 1.5; }
    #headerFooterSlots .upload-slot-card { height: 100%; min-width: 0; padding: 18px; }
    #headerFooterSlots input[type="file"] { width: 100%; min-width: 0; font-size: 14px; border-radius: 10px; }
    #headerFooterSlots input::file-selector-button { padding: 10px 12px; margin-right: 10px; cursor: pointer; }
    #headerFooterSlots .upload-size-error { color: #c62828; font-size: 14px; margin-top: 8px; overflow-wrap: anywhere; }
    #headerFooterSlots .upload-size-error:empty { display: none; }
    #headerFooterSlots input[aria-invalid="true"] { border-color: #c62828; }
    html[data-pms-theme="dark"] #headerFooterSlots .upload-size-error,
    html[data-bs-theme="dark"] #headerFooterSlots .upload-size-error { color: #ffb4b4; }
    .lh-persisted-file-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 8px;
        padding: 8px 10px;
        border-radius: 10px;
        background: rgba(16, 185, 129, 0.08);
        border: 1px solid rgba(16, 185, 129, 0.25);
        font-size: 0.78rem;
    }
    .lh-persisted-file-bar .lh-persisted-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        flex: 1;
    }
    .lh-persisted-file-bar .lh-persisted-thumb {
        width: 48px;
        height: 22px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid rgba(15, 23, 42, 0.12);
        background: #fff;
        flex-shrink: 0;
    }
    .lh-persisted-file-bar .lh-persisted-name {
        font-weight: 600;
        color: #065f46;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    html[data-pms-theme="dark"] .lh-persisted-file-bar,
    html[data-bs-theme="dark"] .lh-persisted-file-bar {
        background: rgba(16, 185, 129, 0.14);
        border-color: rgba(16, 185, 129, 0.32);
    }
    html[data-pms-theme="dark"] .lh-persisted-file-bar .lh-persisted-name,
    html[data-bs-theme="dark"] .lh-persisted-file-bar .lh-persisted-name {
        color: #6ee7b7;
    }
    .lh-preview-editable {
        border-radius: 4px;
        transition: background-color 0.15s ease, outline-color 0.15s ease;
        outline: 1px dashed transparent;
        outline-offset: 2px;
    }
    .lh-preview-editable:hover {
        outline-color: rgba(47, 107, 255, 0.35);
        background-color: rgba(47, 107, 255, 0.03);
        cursor: text;
    }
    .lh-preview-editable:focus {
        outline: 1.5px solid #2F6BFF;
        background-color: rgba(47, 107, 255, 0.05);
    }
    .lh-sig-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px;
    }
    html[data-pms-theme="dark"] .lh-sig-box,
    html[data-bs-theme="dark"] .lh-sig-box {
        background: #0F1530;
        border-color: rgba(238, 241, 251, 0.14);
    }
    .lh-sig-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 14px;
    }
    .lh-sig-tab-btn {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        border-radius: 10px;
        padding: 7px 14px;
        font-size: 0.84rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .lh-sig-tab-btn.active {
        background: #2F6BFF;
        border-color: #2F6BFF;
        color: #ffffff;
    }
    html[data-pms-theme="dark"] .lh-sig-tab-btn,
    html[data-bs-theme="dark"] .lh-sig-tab-btn {
        background: #141B3D;
        border-color: rgba(238, 241, 251, 0.18);
        color: #E2E8F0;
    }
    html[data-pms-theme="dark"] .lh-sig-tab-btn.active,
    html[data-bs-theme="dark"] .lh-sig-tab-btn.active {
        background: #2F6BFF;
        border-color: #2F6BFF;
        color: #ffffff;
    }
    .lh-sig-canvas-wrap {
        background: #ffffff;
        border: 1.5px dashed #cbd5e1;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
    }
    #signaturePadCanvas {
        display: block;
        width: 100%;
        height: 165px;
        touch-action: none;
        cursor: crosshair;
    }
    .lh-sig-ink-btn {
        width: 24px;
        height: 24px;
        border-radius: 999px;
        border: 2px solid transparent;
        cursor: pointer;
    }
    .lh-sig-ink-btn.active {
        border-color: #2F6BFF;
        box-shadow: 0 0 0 2px rgba(47, 107, 255, 0.25);
    }
    .lh-saved-sig-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 12px;
    }
    .lh-saved-sig-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .lh-saved-sig-card.selected {
        border-color: #2F6BFF;
        box-shadow: 0 0 0 2px rgba(47, 107, 255, 0.18);
    }
    html[data-pms-theme="dark"] .lh-saved-sig-card,
    html[data-bs-theme="dark"] .lh-saved-sig-card {
        background: #141B3D;
        border-color: rgba(238, 241, 251, 0.16);
    }
    .lh-saved-sig-thumb-box {
        height: 62px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 6px;
        overflow: hidden;
    }
    .lh-saved-sig-thumb-box img {
        max-width: 100%;
        max-height: 50px;
        object-fit: contain;
    }
    .lh-sig-active-preview {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 14px;
        min-height: 70px;
    }
</style>

<div class="leave-form-page">
    <!-- BREADCRUMB -->
    <div class="leave-breadcrumb">
        <i class="fas fa-envelope-open-text"></i>
        <a href="{{ route('dashboard') }}">Dashboard</a> / 
        <a href="{{ route('letterhead.index') }}">Letter Head Management</a> / 
        <span id="heroBreadcrumb">Send {{ $currentTemplate['name'] }}</span>
    </div>

    <!-- HERO HEADER -->
    <section class="leave-form-hero">
        <div>
            <h1 id="heroTitle">Send {{ $currentTemplate['name'] }}</h1>
            <p id="heroSubtitle">{{ $currentTemplate['description'] ?? 'Choose a template, configure recipient and letterhead images, and preview or download as PDF/Word.' }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('letterhead.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back to Letters
            </a>
        </div>
    </section>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4 border-0 shadow-sm" role="alert" style="background: #dcfce7; color: #166534;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle fs-5"></i>
                <strong>{{ session('success') }}</strong>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-4 mb-4 border-0 shadow-sm">
            <strong>Please resolve the following:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- MAIN SINGLE-COLUMN WORKSPACE -->
    <section class="form-card">
        <div class="form-grid single">
            <div>
                <form id="letterMainForm" method="POST" action="{{ route('letterhead.export-pdf') }}" enctype="multipart/form-data" onsubmit="return false;">
                    @csrf
                    <input type="hidden" name="ref_no" id="formRefNo" value="REF/{{ date('Y') }}/IT-{{ strtoupper(Str::random(4)) }}">
                    <input type="hidden" name="date" id="formDate" value="{{ now()->format('F d, Y') }}">
                    <input type="hidden" name="body" id="body" value="{{ old('body', $sampleText) }}">
                    <input type="hidden" name="recipient_name" id="recipient_name" value="{{ old('recipient_name', 'Alexander Wright') }}">
                    <input type="hidden" name="department" id="department" value="{{ old('department', '') }}">

                    <!-- 1. HEADER & FOOTER IMAGE / PDF UPLOAD SECTION (TOP) -->
                    <div class="lh-custom-box mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-image text-success fs-5"></i>
                                <span class="fw-bold text-dark">Header & Footer Image / PDF Placement</span>
                            </div>
                            <span class="badge bg-success text-white rounded-pill px-2">Image Placement</span>
                        </div>
                        <p class="text-muted small mb-3">
                            Upload separate <strong>Header & Footer Images</strong> or PDFs for official letterhead placement.
                        </p>

                        <input type="hidden" name="layout_mode" id="layout_mode" value="custom_header_footer">

                        @php
                            $savedHeaderPath = $defaultLetterhead?->header_image ?: null;
                            $savedFooterPath = $defaultLetterhead?->footer_image ?: null;
                            $headerPreviewUrl = $savedHeaderPath ? asset($savedHeaderPath) : asset('assets/letterhead/presets/bengal_header.svg');
                            $footerPreviewUrl = $savedFooterPath ? asset($savedFooterPath) : asset('assets/letterhead/presets/bengal_footer.svg');
                            $savedHeaderIsPdf = $savedHeaderPath && str_ends_with(strtolower($savedHeaderPath), '.pdf');
                            $savedFooterIsPdf = $savedFooterPath && str_ends_with(strtolower($savedFooterPath), '.pdf');
                        @endphp

                        <!-- Header & Footer Upload Slots -->
                        <div class="row g-2" id="headerFooterSlots">
                            <!-- Header Image Slot -->
                            <div class="col-md-6">
                                <div class="upload-slot-card">
                                    <label for="header_image" class="upload-slot-label text-success mb-1">
                                        <span><i class="fas fa-arrow-up me-1"></i> Header Image / PDF</span>
                                        <small class="badge bg-success text-white rounded-pill px-2" style="font-size: 0.68rem;">Fixed: 1240 × 160 px</small>
                                    </label>
                                    <input type="file" name="header_image" id="header_image" class="form-control form-control-sm" accept="image/*,.pdf" onchange="previewHeaderImage(this)">
                                    <div id="header_image_error" class="upload-size-error" role="alert">@error('header_image'){{ $message }}@enderror</div>
                                    <div id="header_persisted_bar" class="lh-persisted-file-bar {{ $savedHeaderPath ? '' : 'd-none' }}">
                                        <div class="lh-persisted-meta">
                                            <img id="header_persisted_thumb" src="{{ $savedHeaderPath && !$savedHeaderIsPdf ? $headerPreviewUrl : '' }}" alt="Saved Header" class="lh-persisted-thumb {{ ($savedHeaderPath && !$savedHeaderIsPdf) ? '' : 'd-none' }}">
                                            <i id="header_persisted_pdf_icon" class="fas fa-file-pdf text-danger {{ $savedHeaderIsPdf ? '' : 'd-none' }}"></i>
                                            <span id="header_persisted_name" class="lh-persisted-name" title="{{ $savedHeaderPath ? basename($savedHeaderPath) : '' }}">{{ $savedHeaderPath ? basename($savedHeaderPath) : '' }}</span>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 0.72rem;" onclick="removePersistedPlacement('header')">
                                            <i class="fas fa-trash-alt me-1"></i> Remove
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">
                                        Default: Bengal IT Hub Header Ribbon. Images must be exactly 1240 × 160 px. Maximum file size: 10 MB.
                                    </small>
                                </div>
                            </div>

                            <!-- Footer Image Slot -->
                            <div class="col-md-6">
                                <div class="upload-slot-card">
                                    <label for="footer_image" class="upload-slot-label text-success mb-1">
                                        <span><i class="fas fa-arrow-down me-1"></i> Footer Image / PDF</span>
                                        <small class="badge bg-success text-white rounded-pill px-2" style="font-size: 0.68rem;">Fixed: 1240 × 140 px</small>
                                    </label>
                                    <input type="file" name="footer_image" id="footer_image" class="form-control form-control-sm" accept="image/*,.pdf" onchange="previewFooterImage(this)">
                                    <div id="footer_image_error" class="upload-size-error" role="alert">@error('footer_image'){{ $message }}@enderror</div>
                                    <div id="footer_persisted_bar" class="lh-persisted-file-bar {{ $savedFooterPath ? '' : 'd-none' }}">
                                        <div class="lh-persisted-meta">
                                            <img id="footer_persisted_thumb" src="{{ $savedFooterPath && !$savedFooterIsPdf ? $footerPreviewUrl : '' }}" alt="Saved Footer" class="lh-persisted-thumb {{ ($savedFooterPath && !$savedFooterIsPdf) ? '' : 'd-none' }}">
                                            <i id="footer_persisted_pdf_icon" class="fas fa-file-pdf text-danger {{ $savedFooterIsPdf ? '' : 'd-none' }}"></i>
                                            <span id="footer_persisted_name" class="lh-persisted-name" title="{{ $savedFooterPath ? basename($savedFooterPath) : '' }}">{{ $savedFooterPath ? basename($savedFooterPath) : '' }}</span>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 0.72rem;" onclick="removePersistedPlacement('footer')">
                                            <i class="fas fa-trash-alt me-1"></i> Remove
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">
                                        Default: Bengal IT Hub Footer Address & Ribbons. Images must be exactly 1240 × 140 px. Maximum file size: 10 MB.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. RECIPIENT / EMPLOYEE NAME (INTERNAL & EXTERNAL) -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label id="lblInternalEmployee" for="internal_employee_select">Internal Employee</label>
                            <select id="internal_employee_select" class="form-select" onchange="handleInternalEmployeeChange(this)">
                                <option value="">-- Select Internal Employee --</option>
                                @foreach($employees ?? collect() as $emp)
                                    @php
                                        $empDetail = $emp->employeeDetail;
                                        $empDeptKey = $empDetail?->parentDepartment
                                            ? ('parent:' . $empDetail->parent_dpt_id)
                                            : ($empDetail?->department ? ('sub:' . $empDetail->department_id) : '');
                                        $empDeptName = $empDetail?->parentDepartment?->dpt_name
                                            ?? $empDetail?->department?->dpt_name
                                            ?? '';
                                    @endphp
                                    <option value="{{ $emp->id }}"
                                            data-name="{{ $emp->name }}"
                                            data-email="{{ $emp->email }}"
                                            data-designation="{{ $empDetail?->designation?->name ?? '' }}"
                                            data-empid="{{ $empDetail?->employee_id ?? '' }}"
                                            data-dept-id="{{ $empDeptKey }}"
                                            data-dept-name="{{ $empDeptName }}"
                                            {{ old('recipient_name') === $emp->name ? 'selected' : '' }}>
                                        {{ $emp->name }}@if($empDetail?->employee_id) ({{ $empDetail->employee_id }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label id="lblExternalEmployee" for="external_employee_name">External Employee</label>
                            <input type="text" id="external_employee_name" class="form-control"
                                   value="{{ old('recipient_name') && !($employees ?? collect())->contains('name', old('recipient_name')) ? old('recipient_name') : '' }}"
                                   placeholder="Enter external employee name" oninput="handleExternalEmployeeInput(this)">
                        </div>
                    </div>

                    <!-- 2B. DEPARTMENT (INTERNAL DROPDOWN & EXTERNAL MANUAL) -->
                    @php
                        $resolvedDeptOptions = $departmentOptions ?? ($departments ?? collect())->map(fn ($d) => ['key' => 'sub:' . $d->id, 'name' => $d->dpt_name]);
                    @endphp
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label id="lblInternalDepartment" for="internal_department_select">Department</label>
                            <select id="internal_department_select" class="form-select" onchange="handleInternalDepartmentChange(this)">
                                <option value="">-- Select Department --</option>
                                @foreach($resolvedDeptOptions as $deptOpt)
                                    <option value="{{ $deptOpt['key'] }}"
                                            data-name="{{ $deptOpt['name'] }}"
                                            {{ old('department') === $deptOpt['name'] ? 'selected' : '' }}>
                                        {{ $deptOpt['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label id="lblExternalDepartment" for="external_department">Department (External Employee)</label>
                            <input type="text" id="external_department" class="form-control"
                                   value="{{ old('department') && !$resolvedDeptOptions->contains('name', old('department')) ? old('department') : '' }}"
                                   placeholder="Enter department manually" oninput="handleExternalDepartmentInput(this)">
                        </div>
                    </div>

                    <!-- 3. RECIPIENT / HR EMAIL -->
                    <div class="mb-3">
                        <label id="lblRecipientEmail" for="recipient_email">Recipient / HR Email</label>
                        <input type="email" name="recipient_email" id="recipient_email" class="form-control"
                               value="{{ old('recipient_email', 'hr@bengalithub.com') }}" placeholder="hr@example.com">
                    </div>

                    <!-- 4. LETTER TYPE / CATEGORY -->
                    <div class="mb-3">
                        <label for="template_select">Letter Type / Category <span class="text-danger">*</span></label>
                        <select id="template_select" name="template_key" class="form-select" onchange="handleTemplateChange(this.value)">
                            @foreach($categories as $catKey => $cat)
                                <optgroup label="{{ $cat['name'] }}">
                                    @foreach($cat['templates'] as $tKey => $t)
                                        <option value="{{ $tKey }}"
                                                data-subject="{{ $t['subject'] }}"
                                                data-category="{{ $cat['name'] }}"
                                                data-catkey="{{ $catKey }}"
                                                {{ $tKey === $templateKey ? 'selected' : '' }}>
                                            {{ $t['name'] }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <!-- 5. SUBJECT LINE -->
                    <div class="mb-3">
                        <label for="subject">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" id="subject" class="form-control" required
                               value="{{ old('subject', $currentTemplate['subject']) }}">
                    </div>

                    <!-- 6. SIGNATORY CONFIGURATION (REMAINS AT BOTTOM) -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="signatory_name">Signatory Name</label>
                            <input type="text" name="signatory_name" id="signatory_name" class="form-control form-control-sm" value="{{ old('signatory_name', auth()->user()?->name ?: 'Arthur Pendelton') }}" placeholder="Authorized Signatory">
                        </div>
                        <div class="col-md-6">
                            <label for="signatory_title">Signatory Title</label>
                            <input type="text" name="signatory_title" id="signatory_title" class="form-control form-control-sm" value="{{ old('signatory_title', 'Authorized Signatory / HR Director') }}" placeholder="Title">
                        </div>
                    </div>

                    <!-- 6B. AUTHORIZED SIGNATURE SECTION -->
                    <input type="hidden" name="signature_mode" id="signature_mode" value="draw">
                    <input type="hidden" name="signature_id" id="signature_id" value="">
                    <input type="hidden" name="signature_data" id="signature_data" value="">
                    <input type="hidden" name="signature_alignment" id="signature_alignment" value="left">
                    <input type="hidden" name="signature_width_mm" id="signature_width_mm" value="40">

                    <div class="lh-sig-box mb-3" id="authorizedSignatureSection">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-signature text-primary"></i>
                                    <span class="fw-bold">Authorized Signature</span>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2" style="font-size: 0.7rem;">Electronic Signature</span>
                                </div>
                                <small class="text-muted d-block">Include an authorized handwritten signature above the signatory name in Preview, PDF, and Word exports.</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="signature_enabled" name="signature_enabled" value="1" onchange="toggleSignaturePanel(this.checked)">
                                <label class="form-check-label fw-semibold" for="signature_enabled">Enable Signature</label>
                            </div>
                        </div>

                        <div id="sigFeedbackBanner" class="alert d-none py-2 px-3 small mb-2" role="alert"></div>

                        <div id="signatureConfigPanel" class="d-none mt-3">
                            <div class="lh-sig-tabs" role="tablist">
                                <button type="button" class="lh-sig-tab-btn active" data-sig-tab="draw" onclick="switchSignatureTab('draw')">
                                    <i class="fas fa-pen-nib me-1"></i> Draw Signature
                                </button>
                                <button type="button" class="lh-sig-tab-btn" data-sig-tab="upload" onclick="switchSignatureTab('upload')">
                                    <i class="fas fa-upload me-1"></i> Upload Signature
                                </button>
                                <button type="button" class="lh-sig-tab-btn" data-sig-tab="saved" onclick="switchSignatureTab('saved')">
                                    <i class="fas fa-bookmark me-1"></i> Use Saved Signature
                                </button>
                            </div>

                            <!-- Tab 1: Draw Signature -->
                            <div id="sigTabDraw" class="sig-tab-pane">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="small fw-semibold">Ink Color:</span>
                                        <button type="button" class="lh-sig-ink-btn active" data-ink="#0f172a" style="background:#0f172a;" title="Black Ink" onclick="setSignatureInk('#0f172a', this)"></button>
                                        <button type="button" class="lh-sig-ink-btn" data-ink="#1e3a8a" style="background:#1e3a8a;" title="Dark Blue Ink" onclick="setSignatureInk('#1e3a8a', this)"></button>
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="undoSignatureStroke()">
                                            <i class="fas fa-undo me-1"></i> Undo
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearSignaturePad()">
                                            <i class="fas fa-eraser me-1"></i> Clear
                                        </button>
                                        <div class="form-check form-check-inline m-0 ms-1">
                                            <input class="form-check-input" type="checkbox" id="saveDrawnAsDefault">
                                            <label class="form-check-label small" for="saveDrawnAsDefault">Default</label>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="saveDrawnSignature()">
                                            <i class="fas fa-save me-1"></i> Save Signature
                                        </button>
                                    </div>
                                </div>
                                <div class="lh-sig-canvas-wrap">
                                    <canvas id="signaturePadCanvas" aria-label="Draw authorized signature"></canvas>
                                </div>
                                <small class="text-muted d-block mt-1">Draw using mouse, touch screen, or stylus. Exported as a transparent high-resolution PNG.</small>
                            </div>

                            <!-- Tab 2: Upload Signature -->
                            <div id="sigTabUpload" class="sig-tab-pane d-none">
                                <div class="mb-2">
                                    <input type="file" name="signature_file" id="signature_file" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg" onchange="handleSignatureUpload(this)">
                                    <small class="text-muted d-block mt-1">Supported: PNG, JPG, JPEG (transparent PNG recommended). Max size: 2 MB. Original aspect ratio is preserved.</small>
                                </div>
                                <div id="sigUploadPreviewWrap" class="d-none mt-2 p-2 border rounded-3 bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <img id="sigUploadPreviewImg" src="" alt="Uploaded signature preview" style="max-height: 60px; max-width: 220px; object-fit: contain;">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('signature_file').click()">Replace</button>
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeUploadedSignature()">Remove</button>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="saveUploadedSignature()">
                                            <i class="fas fa-save me-1"></i> Save Signature
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab 3: Use Saved Signature -->
                            <div id="sigTabSaved" class="sig-tab-pane d-none">
                                <div id="savedSignaturesGrid" class="lh-saved-sig-grid"></div>
                                <div id="savedSignaturesEmpty" class="text-muted small py-3 text-center d-none">
                                    No saved signatures found for your organization yet. Draw or upload a signature and click <strong>Save Signature</strong>.
                                </div>
                            </div>

                            <!-- Customization & Active Preview -->
                            <div class="mt-3 pt-3 border-top">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-4">
                                        <label class="small fw-semibold mb-1 d-block">Alignment</label>
                                        <div class="btn-group btn-group-sm w-100" role="group">
                                            <button type="button" class="btn btn-outline-primary active" data-sig-align="left" onclick="setSignatureAlignment('left', this)">Left</button>
                                            <button type="button" class="btn btn-outline-primary" data-sig-align="center" onclick="setSignatureAlignment('center', this)">Center</button>
                                            <button type="button" class="btn btn-outline-primary" data-sig-align="right" onclick="setSignatureAlignment('right', this)">Right</button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="signatureWidthRange" class="small fw-semibold mb-1 d-block">
                                            Signature Width: <span id="signatureWidthLabel">40</span> mm
                                        </label>
                                        <input type="range" class="form-range" id="signatureWidthRange" min="20" max="80" step="2" value="40" oninput="setSignatureWidth(this.value)">
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <label class="small fw-semibold mb-1 d-block">Active Signature Actions</label>
                                        <div class="d-flex justify-content-md-end gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="replaceActiveSignature()">Replace</button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeActiveSignature()">Remove</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="lh-sig-active-preview mt-2" id="activeSigPreviewBox" style="text-align: left;">
                                    <span id="activeSigPlaceholder" class="text-muted small">No signature applied yet. Draw, upload, or select a saved signature above.</span>
                                    <img id="activeSigPreviewImg" src="" alt="Active Signature Preview" class="d-none" style="width: 40mm; height: auto; max-height: 70px; display: inline-block;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 7. BOTTOM ACTION BUTTONS -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2">
                        <div class="d-flex gap-2">
                            <a href="{{ route('letterhead.index') }}" class="btn btn-light">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="button" class="btn btn-secondary" onclick="openPrintPreview()">
                                <i class="fas fa-eye"></i> Live Preview
                            </button>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <!-- Download PDF -->
                            <button type="button" class="btn btn-primary" onclick="submitExport('pdf')">
                                <i class="fas fa-file-pdf"></i> Download PDF
                            </button>

                            <!-- Download Word -->
                            <button type="button" class="btn btn-word" onclick="submitExport('word')">
                                <i class="fas fa-file-word"></i> Download Word (.docx)
                            </button>

                        </div>
                    </div>
                </form>
            </div>

        </div>
    </section>
</div>

<!-- ========================================================================= -->
<!-- LIVE PREVIEW MODAL WITH HEADER & FOOTER IMAGE PLACEMENT                   -->
<!-- ========================================================================= -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom px-4 pt-3 pb-3 bg-white">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h5 class="modal-title fw-bold text-dark mb-0">
                        <i class="fas fa-file-invoice text-success me-2"></i> Document Print Preview
                    </h5>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                        <i class="fas fa-pen me-1"></i> Click any text to edit before downloading
                    </span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light d-flex justify-content-center">
                <div class="lh-preview-card-a4 bg-white" id="modalA4Preview">
                    
                    <!-- 1. PLACED HEADER IMAGE (Used in custom_header_footer mode) -->
                    <img id="prevHeaderImgElement" src="{{ $headerPreviewUrl }}" class="modal-preview-header-img" alt="Header Image">

                    <!-- 2. CENTER CONTENT SECTION -->
                    <div class="modal-preview-body" id="modalPreviewBody">
                        <div style="display: flex; justify-content: space-between; font-size: 7.5pt; font-weight: bold; color: #475569; margin-bottom: 10px;">
                            <span id="prevRefNo" class="lh-preview-editable" contenteditable="true" oninput="syncPreviewEditsToForm()">REF/2026/IT-0842</span>
                            <span id="prevDate" class="lh-preview-editable" contenteditable="true" oninput="syncPreviewEditsToForm()">{{ now()->format('F d, Y') }}</span>
                        </div>

                        <div style="font-size: 8.5pt; font-weight: bold; color: #2F6BFF; text-decoration: underline; margin-bottom: 10px;">
                            <span>Subject: </span><span id="prevSubject" class="lh-preview-editable" contenteditable="true" oninput="syncPreviewEditsToForm()">Apology Letter Regarding Leave</span>
                        </div>

                        <div style="font-size: 8pt; line-height: 1.55; color: #334155; white-space: pre-wrap; min-height: 120px;" id="prevBody" class="lh-preview-editable" contenteditable="true" oninput="syncPreviewEditsToForm()">
                        </div>

                        <!-- Signatory Block -->
                        <div id="prevSignatoryBlock" style="margin-top: 18px; font-size: 7.5pt; text-align: left;">
                            <div id="prevSignatureWrap" class="d-none" style="margin-bottom: 4px;">
                                <img id="prevSignatureImg" src="" alt="Authorized Signature" style="width: 28mm; height: auto; display: inline-block;">
                            </div>
                            <div style="font-weight: bold; color: #1e293b;" id="prevSignatory" class="lh-preview-editable" contenteditable="true" oninput="syncPreviewEditsToForm()">Arthur Pendelton</div>
                            <div style="color: #64748b;" id="prevSignatoryTitle" class="lh-preview-editable" contenteditable="true" oninput="syncPreviewEditsToForm()">Authorized Signatory</div>
                        </div>
                    </div>

                    <!-- 3. PLACED FOOTER IMAGE (Used in custom_header_footer mode) -->
                    <img id="prevFooterImgElement" src="{{ $footerPreviewUrl }}" class="modal-preview-footer-img" alt="Footer Image">

                </div>
            </div>
            <div class="modal-footer border-top px-4 py-2 bg-white d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm" onclick="submitExport('pdf')">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </button>
                    <button type="button" class="btn btn-word btn-sm" onclick="submitExport('word')">
                        <i class="fas fa-file-word"></i> Download Word
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const templateData = @json($allTemplates);
    const bodyArea = document.getElementById('body');
    const subjectInput = document.getElementById('subject');
    const templateSelect = document.getElementById('template_select');

    let currentHeaderImgData = '{{ $headerPreviewUrl }}';
    let currentFooterImgData = '{{ $footerPreviewUrl }}';
    const csrfToken = '{{ csrf_token() }}';
    const placementUploadUrl = '{{ route('letterhead.placement.upload') }}';
    const placementRemoveUrl = '{{ route('letterhead.placement.remove') }}';

    // Template change handler
    window.handleTemplateChange = function (tplKey) {
        const tpl = templateData[tplKey];
        if (!tpl) return;

        document.getElementById('heroTitle').textContent = 'Send ' + tpl.name;
        document.getElementById('heroSubtitle').textContent = tpl.description || 'Edit your letter details and download as PDF/Word.';
        document.getElementById('heroBreadcrumb').textContent = 'Send ' + tpl.name;

        subjectInput.value = tpl.subject || tpl.name;
        if (bodyArea) {
            bodyArea.value = renderPlaceholders(tpl.content);
        }

        const isApology = tpl.category_key === 'apology' || tplKey.startsWith('apology_');
        const lblEmail = document.getElementById('lblRecipientEmail');
        if (lblEmail) {
            lblEmail.textContent = isApology ? 'Recipient / HR Email' : 'Recipient / Official Email';
        }
    };

    window.handleInternalEmployeeChange = function (select) {
        const opt = select.options[select.selectedIndex];
        const recipientHidden = document.getElementById('recipient_name');
        const externalInput = document.getElementById('external_employee_name');
        const emailInput = document.getElementById('recipient_email');
        const internalDeptSelect = document.getElementById('internal_department_select');
        const externalDeptInput = document.getElementById('external_department');
        const deptHidden = document.getElementById('department');

        if (opt && opt.value) {
            const empName = opt.dataset.name || opt.textContent.trim();
            if (externalInput) externalInput.value = '';
            if (recipientHidden) recipientHidden.value = empName;
            if (emailInput && opt.dataset.email) emailInput.value = opt.dataset.email;
            if (externalDeptInput) externalDeptInput.value = '';
            if (internalDeptSelect) internalDeptSelect.value = opt.dataset.deptId || '';
            if (deptHidden) deptHidden.value = opt.dataset.deptName || '';
        } else if (externalInput && externalInput.value.trim()) {
            if (recipientHidden) recipientHidden.value = externalInput.value.trim();
        } else {
            if (recipientHidden) recipientHidden.value = 'Alexander Wright';
            if (internalDeptSelect) internalDeptSelect.value = '';
            if (deptHidden) deptHidden.value = externalDeptInput?.value.trim() || '';
        }

        syncPlaceholderData();
    };

    window.handleExternalEmployeeInput = function (input) {
        const val = input.value.trim();
        const internalSelect = document.getElementById('internal_employee_select');
        const internalDeptSelect = document.getElementById('internal_department_select');
        const externalDeptInput = document.getElementById('external_department');
        const deptHidden = document.getElementById('department');
        const recipientHidden = document.getElementById('recipient_name');

        if (val) {
            if (internalSelect) internalSelect.value = '';
            if (internalDeptSelect) internalDeptSelect.value = '';
            if (recipientHidden) recipientHidden.value = val;
            if (deptHidden) deptHidden.value = externalDeptInput?.value.trim() || '';
        } else if (internalSelect && internalSelect.value) {
            const opt = internalSelect.options[internalSelect.selectedIndex];
            if (recipientHidden) recipientHidden.value = opt?.dataset?.name || 'Alexander Wright';
        } else if (recipientHidden) {
            recipientHidden.value = 'Alexander Wright';
        }

        syncPlaceholderData();
    };

    window.handleInternalDepartmentChange = function (select) {
        const opt = select.options[select.selectedIndex];
        const externalDeptInput = document.getElementById('external_department');
        const deptHidden = document.getElementById('department');

        if (opt && opt.value) {
            if (externalDeptInput) externalDeptInput.value = '';
            if (deptHidden) deptHidden.value = opt.dataset.name || opt.textContent.trim();
        } else if (deptHidden) {
            deptHidden.value = externalDeptInput?.value.trim() || '';
        }

        syncPlaceholderData();
    };

    window.handleExternalDepartmentInput = function (input) {
        const val = input.value.trim();
        const internalDeptSelect = document.getElementById('internal_department_select');
        const deptHidden = document.getElementById('department');

        if (val) {
            if (internalDeptSelect) internalDeptSelect.value = '';
            if (deptHidden) deptHidden.value = val;
        } else if (internalDeptSelect && internalDeptSelect.value) {
            const opt = internalDeptSelect.options[internalDeptSelect.selectedIndex];
            if (deptHidden) deptHidden.value = opt?.dataset?.name || '';
        } else if (deptHidden) {
            deptHidden.value = '';
        }

        syncPlaceholderData();
    };

    function renderPlaceholders(text) {
        const internalSelect = document.getElementById('internal_employee_select');
        const selectedOpt = internalSelect && internalSelect.value ? internalSelect.options[internalSelect.selectedIndex] : null;
        const recipient = document.getElementById('recipient_name')?.value || 'Alexander Wright';
        const designation = selectedOpt?.dataset?.designation || 'Senior Software Engineer';
        const empId = selectedOpt?.dataset?.empid || 'EMP-2026-0842';
        const department = document.getElementById('department')?.value || 'Engineering';
        const now = new Date();
        const dateStr = now.toLocaleDateString('en-US', { timeZone: 'Asia/Kolkata',  month: 'long', day: 'numeric', year: 'numeric' });

        return text
            .replace(/\[Date\]/g, dateStr)
            .replace(/\[Year\]/g, now.getFullYear())
            .replace(/\[Fiscal Year\]/g, now.getFullYear() + '-' + (now.getFullYear() + 1))
            .replace(/\[Company Name\]/g, 'Bengal IT Hub Private Limited')
            .replace(/\[Employee Name\]/g, recipient)
            .replace(/\[Candidate Name\]/g, recipient)
            .replace(/\[Intern Name\]/g, recipient)
            .replace(/\[Designation\]/g, designation)
            .replace(/\[Department \/ Team\]/g, department)
            .replace(/\[Department\]/g, department)
            .replace(/\[Employee ID\]/g, empId)
            .replace(/\[Start Date\]/g, 'August 25, 2026')
            .replace(/\[End Date\]/g, 'August 28, 2026')
            .replace(/\[HR Email\]/g, document.getElementById('recipient_email')?.value || 'hr@bengalithub.com')
            .replace(/\[HR Phone\]/g, '+91 92306 53975');
    }

    window.syncPlaceholderData = function () {
        const tpl = templateData[templateSelect?.value];
        if (tpl && bodyArea) {
            bodyArea.value = renderPlaceholders(tpl.content);
        }
    };

    function applyModalLayoutPreview() {
        const modalA4 = document.getElementById('modalA4Preview');
        const headerImgEl = document.getElementById('prevHeaderImgElement');
        const footerImgEl = document.getElementById('prevFooterImgElement');
        const bodyEl = document.getElementById('modalPreviewBody');

        if (modalA4 && headerImgEl && footerImgEl && bodyEl) {
            modalA4.style.backgroundImage = 'none';
            headerImgEl.style.display = 'block';
            footerImgEl.style.display = 'block';
            headerImgEl.src = currentHeaderImgData;
            footerImgEl.src = currentFooterImgData;
            bodyEl.style.paddingTop = '15px';
            bodyEl.style.paddingBottom = '15px';
            bodyEl.style.paddingLeft = '25px';
            bodyEl.style.paddingRight = '25px';
        }
    }

    function updatePersistedBar(slot, payload) {
        const bar = document.getElementById(slot + '_persisted_bar');
        const thumb = document.getElementById(slot + '_persisted_thumb');
        const pdfIcon = document.getElementById(slot + '_persisted_pdf_icon');
        const nameEl = document.getElementById(slot + '_persisted_name');
        if (!bar) return;

        if (!payload) {
            bar.classList.add('d-none');
            if (thumb) { thumb.src = ''; thumb.classList.add('d-none'); }
            if (pdfIcon) pdfIcon.classList.add('d-none');
            if (nameEl) { nameEl.textContent = ''; nameEl.title = ''; }
            return;
        }

        if (nameEl) {
            nameEl.textContent = payload.filename || 'Uploaded ' + slot;
            nameEl.title = payload.filename || '';
        }
        if (payload.is_pdf) {
            if (thumb) { thumb.src = ''; thumb.classList.add('d-none'); }
            if (pdfIcon) pdfIcon.classList.remove('d-none');
        } else {
            if (pdfIcon) pdfIcon.classList.add('d-none');
            if (thumb) { thumb.src = payload.url || ''; thumb.classList.remove('d-none'); }
        }
        bar.classList.remove('d-none');
    }

    async function persistPlacementUpload(slot, file, report) {
        const field = slot === 'header' ? 'header_image' : 'footer_image';
        const fd = new FormData();
        fd.append('slot', slot);
        fd.append(field, file);

        try {
            const resp = await fetch(placementUploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: fd,
            });
            const data = await resp.json().catch(() => ({}));
            if (!resp.ok || !data.success) {
                const firstErr = data?.errors?.[field]?.[0] || data?.message || 'Unable to save uploaded file.';
                if (report) report(firstErr);
                return;
            }
            updatePersistedBar(slot, data);
            if (!data.is_pdf && data.url) {
                if (slot === 'header') {
                    currentHeaderImgData = data.url;
                    document.getElementById('prevHeaderImgElement').src = currentHeaderImgData;
                } else {
                    currentFooterImgData = data.url;
                    document.getElementById('prevFooterImgElement').src = currentFooterImgData;
                }
            }
        } catch (err) {
            if (report) report('Network error while saving uploaded file.');
        }
    }

    window.removePersistedPlacement = async function (slot) {
        const input = document.getElementById(slot + '_image');
        const error = document.getElementById(slot + '_image_error');
        try {
            const resp = await fetch(placementRemoveUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ slot }),
            });
            const data = await resp.json().catch(() => ({}));
            if (!resp.ok || !data.success) return;

            if (input) {
                input.value = '';
                input.setCustomValidity('');
                input.setAttribute('aria-invalid', 'false');
            }
            if (error) error.textContent = '';
            updatePersistedBar(slot, null);

            if (slot === 'header' && data.default_url) {
                currentHeaderImgData = data.default_url;
                document.getElementById('prevHeaderImgElement').src = currentHeaderImgData;
            } else if (slot === 'footer' && data.default_url) {
                currentFooterImgData = data.default_url;
                document.getElementById('prevFooterImgElement').src = currentFooterImgData;
            }
        } catch (err) {
            // Keep UI unchanged if request failed
        }
    };

    function validatePlacement(input, height, slot, applyPreview) {
        const file = input.files && input.files[0];
        const error = document.getElementById(input.id + '_error');
        input.setAttribute('aria-describedby', error.id);
        const report = message => {
            input.setCustomValidity(message);
            input.setAttribute('aria-invalid', message ? 'true' : 'false');
            error.textContent = message;
        };
        report('');
        if (!file) return;
        if (file.size > 10 * 1024 * 1024) { report('File must be 10 MB or smaller.'); return; }
        if (file.type === 'application/pdf') {
            persistPlacementUpload(slot, file, report);
            return;
        }
        report('Checking image dimensions. Please wait.');
        const reader = new FileReader();
        reader.onerror = () => report('Unable to read this image. Please choose another file.');
        reader.onload = e => {
            if (input.files[0] !== file) return;
            const image = new Image();
            image.onload = () => {
                if (input.files[0] !== file) return;
                if (image.naturalWidth !== 1240 || image.naturalHeight !== height) {
                    report(`Image must be exactly 1240 × ${height} px. Uploaded image is ${image.naturalWidth} × ${image.naturalHeight} px. Resize it and upload again.`);
                    return;
                }
                report('');
                applyPreview(e.target.result);
                persistPlacementUpload(slot, file, report);
            };
            image.onerror = () => report('Unable to read image dimensions. Upload a valid image or PDF.');
            image.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    // Live Header Preview Handler
    window.previewHeaderImage = function (input) {
        validatePlacement(input, 160, 'header', data => {
                currentHeaderImgData = data;
                document.getElementById('prevHeaderImgElement').src = currentHeaderImgData;
        });
    };

    // Live Footer Preview Handler
    window.previewFooterImage = function (input) {
        validatePlacement(input, 140, 'footer', data => {
                currentFooterImgData = data;
                document.getElementById('prevFooterImgElement').src = currentFooterImgData;
        });
    };

    window.syncPreviewEditsToForm = function () {
        const prevRef = document.getElementById('prevRefNo');
        const prevDate = document.getElementById('prevDate');
        const prevSubj = document.getElementById('prevSubject');
        const prevBody = document.getElementById('prevBody');
        const prevSig = document.getElementById('prevSignatory');
        const prevSigTitle = document.getElementById('prevSignatoryTitle');

        if (prevRef) document.getElementById('formRefNo').value = prevRef.innerText.trim();
        if (prevDate) document.getElementById('formDate').value = prevDate.innerText.trim();
        if (prevSubj) document.getElementById('subject').value = prevSubj.innerText.trim();
        if (prevBody && bodyArea) bodyArea.value = prevBody.innerText.replace(/\r\n/g, '\n');
        if (prevSig) document.getElementById('signatory_name').value = prevSig.innerText.trim();
        if (prevSigTitle) document.getElementById('signatory_title').value = prevSigTitle.innerText.trim();
    };

    // =========================================================================
    // AUTHORIZED SIGNATURE (DRAW / UPLOAD / SAVED + CUSTOMIZATION + PREVIEW)
    // =========================================================================
    const sigRoutes = {
        index: '{{ route('letterhead.signatures.index') }}',
        store: '{{ route('letterhead.signatures.store') }}',
        updateBase: '{{ url('/letterhead/signatures') }}',
    };
    const sigMaxBytes = {{ \App\Services\LetterheadSignatureService::maxUploadKb() * 1024 }};

    @php
        $serializedSignatures = ($savedSignatures ?? collect())->map(fn ($s) => [
            'id' => $s->id,
            'user_id' => $s->user_id,
            'signatory_name' => $s->signatory_name,
            'signatory_title' => $s->signatory_title,
            'method' => $s->method,
            'is_default' => (bool) $s->is_default,
            'is_active' => (bool) $s->is_active,
            'image_url' => route('letterhead.signatures.image', $s->id),
        ])->values();
    @endphp
    let savedSignatures = @json($serializedSignatures);

    const sigState = {
        enabled: false,
        mode: 'draw',
        alignment: 'left',
        widthMm: 40,
        inkColor: '#0f172a',
        strokes: [],
        currentStroke: null,
        isDrawing: false,
        drawnDataUrl: '',
        uploadedDataUrl: '',
        selectedSavedId: null,
        selectedSavedUrl: '',
    };

    const sigCanvas = document.getElementById('signaturePadCanvas');
    const sigCtx = sigCanvas ? sigCanvas.getContext('2d') : null;

    function showSigFeedback(message, type = 'success') {
        const banner = document.getElementById('sigFeedbackBanner');
        if (!banner) return;
        if (!message) {
            banner.className = 'alert d-none py-2 px-3 small mb-2';
            banner.textContent = '';
            return;
        }
        banner.className = `alert alert-${type} py-2 px-3 small mb-2`;
        banner.textContent = message;
    }

    function resizeSignatureCanvas() {
        if (!sigCanvas || !sigCtx) return;
        const rect = sigCanvas.getBoundingClientRect();
        if (rect.width <= 0) return;
        const dpr = Math.max(1, window.devicePixelRatio || 1);
        sigCanvas.width = Math.round(rect.width * dpr);
        sigCanvas.height = Math.round(rect.height * dpr);
        sigCtx.setTransform(dpr, 0, 0, dpr, 0, 0);
        redrawSignatureCanvas();
    }

    function redrawSignatureCanvas() {
        if (!sigCanvas || !sigCtx) return;
        const rect = sigCanvas.getBoundingClientRect();
        sigCtx.clearRect(0, 0, rect.width || sigCanvas.width, rect.height || sigCanvas.height);

        for (const stroke of sigState.strokes) {
            if (!stroke.points.length) continue;
            sigCtx.strokeStyle = stroke.color;
            sigCtx.fillStyle = stroke.color;
            sigCtx.lineWidth = 2.4;
            sigCtx.lineCap = 'round';
            sigCtx.lineJoin = 'round';

            if (stroke.points.length === 1) {
                sigCtx.beginPath();
                sigCtx.arc(stroke.points[0].x, stroke.points[0].y, 1.3, 0, Math.PI * 2);
                sigCtx.fill();
                continue;
            }

            sigCtx.beginPath();
            sigCtx.moveTo(stroke.points[0].x, stroke.points[0].y);
            for (let i = 1; i < stroke.points.length - 1; i++) {
                const midX = (stroke.points[i].x + stroke.points[i + 1].x) / 2;
                const midY = (stroke.points[i].y + stroke.points[i + 1].y) / 2;
                sigCtx.quadraticCurveTo(stroke.points[i].x, stroke.points[i].y, midX, midY);
            }
            const last = stroke.points[stroke.points.length - 1];
            sigCtx.lineTo(last.x, last.y);
            sigCtx.stroke();
        }
    }

    function getCanvasPoint(ev) {
        const rect = sigCanvas.getBoundingClientRect();
        return { x: ev.clientX - rect.left, y: ev.clientY - rect.top };
    }

    if (sigCanvas) {
        sigCanvas.addEventListener('pointerdown', ev => {
            ev.preventDefault();
            sigCanvas.setPointerCapture(ev.pointerId);
            sigState.isDrawing = true;
            sigState.currentStroke = { color: sigState.inkColor, points: [getCanvasPoint(ev)] };
            sigState.strokes.push(sigState.currentStroke);
            redrawSignatureCanvas();
        });

        sigCanvas.addEventListener('pointermove', ev => {
            if (!sigState.isDrawing || !sigState.currentStroke) return;
            sigState.currentStroke.points.push(getCanvasPoint(ev));
            redrawSignatureCanvas();
        });

        const finishStroke = () => {
            if (!sigState.isDrawing) return;
            sigState.isDrawing = false;
            sigState.currentStroke = null;
            sigState.drawnDataUrl = sigState.strokes.length ? sigCanvas.toDataURL('image/png') : '';
            document.getElementById('signature_data').value = sigState.drawnDataUrl;
            updateActiveSignaturePreview();
        };

        sigCanvas.addEventListener('pointerup', finishStroke);
        sigCanvas.addEventListener('pointercancel', finishStroke);
        window.addEventListener('resize', () => {
            if (sigState.enabled && sigState.mode === 'draw') resizeSignatureCanvas();
        });
    }

    function activeSignatureUrl() {
        if (!sigState.enabled) return '';
        if (sigState.mode === 'draw') return sigState.drawnDataUrl;
        if (sigState.mode === 'upload') return sigState.uploadedDataUrl;
        if (sigState.mode === 'saved') return sigState.selectedSavedUrl;
        return '';
    }

    function updateActiveSignaturePreview() {
        const box = document.getElementById('activeSigPreviewBox');
        const img = document.getElementById('activeSigPreviewImg');
        const placeholder = document.getElementById('activeSigPlaceholder');
        const url = activeSignatureUrl();

        if (box) box.style.textAlign = sigState.alignment;
        if (img) {
            if (url) {
                img.src = url;
                img.style.width = sigState.widthMm + 'mm';
                img.classList.remove('d-none');
                if (placeholder) placeholder.classList.add('d-none');
            } else {
                img.src = '';
                img.classList.add('d-none');
                if (placeholder) placeholder.classList.remove('d-none');
            }
        }
        syncPreviewSignature();
    }

    function syncPreviewSignature() {
        const block = document.getElementById('prevSignatoryBlock');
        const wrap = document.getElementById('prevSignatureWrap');
        const img = document.getElementById('prevSignatureImg');
        if (!block || !wrap || !img) return;

        block.style.textAlign = sigState.alignment;
        const url = activeSignatureUrl();
        if (sigState.enabled && url) {
            img.src = url;
            img.style.width = Math.round(sigState.widthMm * 0.72) + 'mm';
            wrap.classList.remove('d-none');
        } else {
            img.src = '';
            wrap.classList.add('d-none');
        }
    }

    window.toggleSignaturePanel = function (enabled) {
        sigState.enabled = !!enabled;
        const panel = document.getElementById('signatureConfigPanel');
        if (panel) panel.classList.toggle('d-none', !sigState.enabled);
        showSigFeedback('');

        if (sigState.enabled) {
            if (sigState.mode === 'draw') {
                setTimeout(resizeSignatureCanvas, 30);
            }
            if (!sigState.selectedSavedId && savedSignatures.length) {
                const def = savedSignatures.find(s => s.is_active && s.is_default) || savedSignatures.find(s => s.is_active);
                if (def && !sigState.drawnDataUrl && !sigState.uploadedDataUrl) {
                    selectSavedSignature(def.id, false);
                    switchSignatureTab('saved');
                }
            }
        }
        updateActiveSignaturePreview();
    };

    window.switchSignatureTab = function (tab) {
        sigState.mode = tab;
        document.getElementById('signature_mode').value = tab;
        document.querySelectorAll('.lh-sig-tab-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.sigTab === tab);
        });
        document.getElementById('sigTabDraw')?.classList.toggle('d-none', tab !== 'draw');
        document.getElementById('sigTabUpload')?.classList.toggle('d-none', tab !== 'upload');
        document.getElementById('sigTabSaved')?.classList.toggle('d-none', tab !== 'saved');

        if (tab === 'draw') setTimeout(resizeSignatureCanvas, 30);
        if (tab === 'saved') renderSavedSignatures();
        updateActiveSignaturePreview();
    };

    window.setSignatureInk = function (color, btn) {
        sigState.inkColor = color;
        document.querySelectorAll('.lh-sig-ink-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
    };

    window.undoSignatureStroke = function () {
        if (!sigState.strokes.length) return;
        sigState.strokes.pop();
        redrawSignatureCanvas();
        sigState.drawnDataUrl = sigState.strokes.length && sigCanvas ? sigCanvas.toDataURL('image/png') : '';
        document.getElementById('signature_data').value = sigState.drawnDataUrl;
        updateActiveSignaturePreview();
    };

    window.clearSignaturePad = function () {
        sigState.strokes = [];
        sigState.drawnDataUrl = '';
        document.getElementById('signature_data').value = '';
        redrawSignatureCanvas();
        updateActiveSignaturePreview();
    };

    window.setSignatureAlignment = function (align, btn) {
        sigState.alignment = align;
        document.getElementById('signature_alignment').value = align;
        document.querySelectorAll('[data-sig-align]').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        updateActiveSignaturePreview();
    };

    window.setSignatureWidth = function (val) {
        sigState.widthMm = Math.max(20, Math.min(80, parseInt(val, 10) || 40));
        document.getElementById('signature_width_mm').value = sigState.widthMm;
        const lbl = document.getElementById('signatureWidthLabel');
        if (lbl) lbl.textContent = sigState.widthMm;
        updateActiveSignaturePreview();
    };

    window.handleSignatureUpload = function (input) {
        const file = input.files && input.files[0];
        if (!file) return;
        if (!['image/png', 'image/jpeg', 'image/jpg'].includes(file.type)) {
            showSigFeedback('Signature image must be a PNG, JPG, or JPEG file.', 'danger');
            input.value = '';
            return;
        }
        if (file.size > sigMaxBytes) {
            showSigFeedback('Signature image must be 2 MB or smaller.', 'danger');
            input.value = '';
            return;
        }
        showSigFeedback('');
        const reader = new FileReader();
        reader.onload = e => {
            sigState.uploadedDataUrl = e.target.result;
            document.getElementById('sigUploadPreviewImg').src = sigState.uploadedDataUrl;
            document.getElementById('sigUploadPreviewWrap').classList.remove('d-none');
            updateActiveSignaturePreview();
        };
        reader.readAsDataURL(file);
    };

    window.removeUploadedSignature = function () {
        const input = document.getElementById('signature_file');
        if (input) input.value = '';
        sigState.uploadedDataUrl = '';
        document.getElementById('sigUploadPreviewImg').src = '';
        document.getElementById('sigUploadPreviewWrap').classList.add('d-none');
        updateActiveSignaturePreview();
    };

    window.replaceActiveSignature = function () {
        if (sigState.mode === 'upload') {
            document.getElementById('signature_file')?.click();
        } else if (sigState.mode === 'draw') {
            clearSignaturePad();
        } else {
            switchSignatureTab('draw');
        }
    };

    window.removeActiveSignature = function () {
        if (sigState.mode === 'draw') clearSignaturePad();
        else if (sigState.mode === 'upload') removeUploadedSignature();
        else {
            sigState.selectedSavedId = null;
            sigState.selectedSavedUrl = '';
            document.getElementById('signature_id').value = '';
            renderSavedSignatures();
            updateActiveSignaturePreview();
        }
    };

    async function postSignatureSave(formData) {
        showSigFeedback('');
        try {
            const resp = await fetch(sigRoutes.store, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
            });
            const data = await resp.json().catch(() => ({}));
            if (!resp.ok || !data.success) {
                const firstErr = data?.errors ? Object.values(data.errors).flat()[0] : null;
                showSigFeedback(firstErr || data?.message || 'Unable to save signature.', 'danger');
                return;
            }
            if (data.signature) {
                if (data.signature.is_default) {
                    savedSignatures = savedSignatures.map(s => ({ ...s, is_default: false }));
                }
                savedSignatures.unshift(data.signature);
                selectSavedSignature(data.signature.id, true);
                switchSignatureTab('saved');
                showSigFeedback('Signature saved and selected.', 'success');
            }
        } catch (err) {
            showSigFeedback('Network error while saving signature.', 'danger');
        }
    }

    window.saveDrawnSignature = function () {
        if (!sigState.strokes.length || !sigState.drawnDataUrl) {
            showSigFeedback('Please draw a signature on the canvas first.', 'warning');
            return;
        }
        const fd = new FormData();
        fd.append('method', 'draw');
        fd.append('signatory_name', document.getElementById('signatory_name').value || 'Authorized Signatory');
        fd.append('signatory_title', document.getElementById('signatory_title').value || '');
        fd.append('signature_data', sigState.drawnDataUrl);
        fd.append('is_default', document.getElementById('saveDrawnAsDefault')?.checked ? '1' : '0');
        postSignatureSave(fd);
    };

    window.saveUploadedSignature = function () {
        const file = document.getElementById('signature_file')?.files?.[0];
        if (!file) {
            showSigFeedback('Please choose a signature image file first.', 'warning');
            return;
        }
        const fd = new FormData();
        fd.append('method', 'upload');
        fd.append('signatory_name', document.getElementById('signatory_name').value || 'Authorized Signatory');
        fd.append('signatory_title', document.getElementById('signatory_title').value || '');
        fd.append('signature_file', file);
        postSignatureSave(fd);
    };

    window.selectSavedSignature = function (id, switchMode = true) {
        const sig = savedSignatures.find(s => Number(s.id) === Number(id));
        if (!sig || !sig.is_active) return;
        sigState.selectedSavedId = sig.id;
        sigState.selectedSavedUrl = sig.image_url;
        document.getElementById('signature_id').value = sig.id;
        if (switchMode) {
            sigState.mode = 'saved';
            document.getElementById('signature_mode').value = 'saved';
        }
        if (sig.signatory_name) document.getElementById('signatory_name').value = sig.signatory_name;
        if (sig.signatory_title !== null && sig.signatory_title !== undefined) {
            document.getElementById('signatory_title').value = sig.signatory_title;
        }
        renderSavedSignatures();
        updateActiveSignaturePreview();
    };

    window.setSavedSignatureDefault = async function (id) {
        const resp = await fetch(`${sigRoutes.updateBase}/${id}/default`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await resp.json().catch(() => ({}));
        if (resp.ok && data.success) {
            savedSignatures = savedSignatures.map(s => ({ ...s, is_default: Number(s.id) === Number(id), is_active: Number(s.id) === Number(id) ? true : s.is_active }));
            renderSavedSignatures();
            showSigFeedback('Default signature updated.', 'success');
        }
    };

    window.toggleSavedSignatureActive = async function (id) {
        const resp = await fetch(`${sigRoutes.updateBase}/${id}/toggle-active`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await resp.json().catch(() => ({}));
        if (resp.ok && data.success && data.signature) {
            savedSignatures = savedSignatures.map(s => Number(s.id) === Number(id) ? data.signature : s);
            if (!data.signature.is_active && Number(sigState.selectedSavedId) === Number(id)) {
                sigState.selectedSavedId = null;
                sigState.selectedSavedUrl = '';
                document.getElementById('signature_id').value = '';
                updateActiveSignaturePreview();
            }
            renderSavedSignatures();
        }
    };

    window.editSavedSignatureDetails = async function (id) {
        const sig = savedSignatures.find(s => Number(s.id) === Number(id));
        if (!sig) return;
        const nextName = window.prompt('Signatory Name:', sig.signatory_name || '');
        if (nextName === null || !nextName.trim()) return;
        const nextTitle = window.prompt('Signatory Title:', sig.signatory_title || '');
        if (nextTitle === null) return;

        const resp = await fetch(`${sigRoutes.updateBase}/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ signatory_name: nextName.trim(), signatory_title: nextTitle.trim() }),
        });
        const data = await resp.json().catch(() => ({}));
        if (resp.ok && data.success && data.signature) {
            savedSignatures = savedSignatures.map(s => Number(s.id) === Number(id) ? data.signature : s);
            if (Number(sigState.selectedSavedId) === Number(id)) {
                document.getElementById('signatory_name').value = data.signature.signatory_name;
                document.getElementById('signatory_title').value = data.signature.signatory_title || '';
            }
            renderSavedSignatures();
            showSigFeedback('Signature details updated.', 'success');
        }
    };

    window.deleteSavedSignature = async function (id) {
        if (!window.confirm('Delete this saved signature? Previously finalized letters will retain their original signature snapshot.')) return;
        const resp = await fetch(`${sigRoutes.updateBase}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await resp.json().catch(() => ({}));
        if (resp.ok && data.success) {
            savedSignatures = savedSignatures.filter(s => Number(s.id) !== Number(id));
            if (Number(sigState.selectedSavedId) === Number(id)) {
                sigState.selectedSavedId = null;
                sigState.selectedSavedUrl = '';
                document.getElementById('signature_id').value = '';
                updateActiveSignaturePreview();
            }
            renderSavedSignatures();
            showSigFeedback('Saved signature deleted.', 'info');
        }
    };

    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, ch => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
    }

    function renderSavedSignatures() {
        const grid = document.getElementById('savedSignaturesGrid');
        const empty = document.getElementById('savedSignaturesEmpty');
        if (!grid || !empty) return;

        if (!savedSignatures.length) {
            grid.innerHTML = '';
            empty.classList.remove('d-none');
            return;
        }
        empty.classList.add('d-none');

        grid.innerHTML = savedSignatures.map(sig => {
            const isSelected = Number(sigState.selectedSavedId) === Number(sig.id);
            const badges = [
                sig.is_default ? '<span class="badge bg-primary-subtle text-primary">Default</span>' : '',
                sig.is_active ? '<span class="badge bg-success-subtle text-success">Active</span>' : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>',
            ].filter(Boolean).join(' ');

            return `<div class="lh-saved-sig-card ${isSelected ? 'selected' : ''}">
                <div class="lh-saved-sig-thumb-box"><img src="${escapeHtml(sig.image_url)}" alt="${escapeHtml(sig.signatory_name)}"></div>
                <div>
                    <div class="fw-bold small">${escapeHtml(sig.signatory_name)}</div>
                    <div class="text-muted" style="font-size:0.75rem;">${escapeHtml(sig.signatory_title || '')}</div>
                    <div class="d-flex gap-1 mt-1">${badges}</div>
                </div>
                <div class="d-flex flex-wrap gap-1 mt-auto pt-1">
                    <button type="button" class="btn btn-sm ${isSelected ? 'btn-primary' : 'btn-outline-primary'} py-0 px-2" style="font-size:0.74rem;" ${sig.is_active ? '' : 'disabled'} onclick="selectSavedSignature(${sig.id}, true)">${isSelected ? 'Selected' : 'Use'}</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:0.74rem;" onclick="setSavedSignatureDefault(${sig.id})">Default</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:0.74rem;" onclick="toggleSavedSignatureActive(${sig.id})">${sig.is_active ? 'Disable' : 'Enable'}</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:0.74rem;" onclick="editSavedSignatureDetails(${sig.id})">Edit</button>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:0.74rem;" onclick="deleteSavedSignature(${sig.id})">Delete</button>
                </div>
            </div>`;
        }).join('');
    }
    renderSavedSignatures();

    // Export Submit Handler (PDF or Word)
    window.submitExport = function (format) {
        const previewModalEl = document.getElementById('previewModal');
        if (previewModalEl && previewModalEl.classList.contains('show')) {
            syncPreviewEditsToForm();
        }
        if (sigState.enabled && !activeSignatureUrl()) {
            showSigFeedback('Please draw, upload, or select an active signature before downloading, or turn off Enable Signature.', 'warning');
            return;
        }
        const form = document.getElementById('letterMainForm');
        if (!form.reportValidity()) return;
        const originalAction = form.action;
        const originalTarget = form.target;

        if (format === 'pdf') {
            form.action = '{{ route('letterhead.export-pdf') }}';
            form.target = '_blank';
            form.submit();
        } else if (format === 'word') {
            form.action = '{{ route('letterhead.export-word') }}';
            form.target = '_blank';
            form.submit();
        }

        setTimeout(() => {
            form.action = originalAction;
            form.target = originalTarget;
        }, 500);
    };

    // Live Print Preview Modal
    window.openPrintPreview = function () {
        if (!document.getElementById('letterMainForm').reportValidity()) return;
        document.getElementById('prevSubject').textContent = document.getElementById('subject').value;
        document.getElementById('prevBody').textContent = document.getElementById('body').value;
        document.getElementById('prevSignatory').textContent = document.getElementById('signatory_name').value;
        document.getElementById('prevSignatoryTitle').textContent = document.getElementById('signatory_title').value;
        document.getElementById('prevRefNo').textContent = document.getElementById('formRefNo').value;
        document.getElementById('prevDate').textContent = document.getElementById('formDate').value;

        applyModalLayoutPreview();
        syncPreviewSignature();

        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
        modal.show();
    };
});
</script>
@endsection
