@extends('admin.layout.app')

@section('title', 'Employee ID Settings')

@push('styles')
<style>
    .employee-id-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f4fbf7 100%);
        color: #0a2e1f;
    }

    .employee-id-shell {
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

    /* ===== PREVIEW HERO BANNER ===== */
    .preview-hero-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        padding: 1.75rem 2.25rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(16, 185, 129, 0.2);
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.12);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .preview-left {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .preview-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .preview-badge-code {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        font-size: 1.6rem;
        font-weight: 800;
        padding: 0.5rem 1.75rem;
        border-radius: 40px;
        background: linear-gradient(145deg, #ecfdf5, #d1fae5);
        color: #065f46;
        border: 1px solid rgba(5, 150, 105, 0.3);
        box-shadow: 0 6px 18px -4px rgba(5, 150, 105, 0.25);
        letter-spacing: 0.05em;
        transition: all 0.3s ease;
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

    .input-group-custom .form-control:focus,
    .input-group-custom .form-select:focus {
        box-shadow: none;
        background-color: transparent;
    }

    .switch-box-container {
        background: #f0fdf4;
        border: 1px solid rgba(16, 185, 129, 0.25);
        border-radius: 20px;
        padding: 16px 20px;
        height: 50px;
        display: flex;
        align-items: center;
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

    @media (max-width: 768px) {
        .employee-id-page {
            padding: 1.25rem 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="employee-id-page">
    <div class="employee-id-shell">
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
                <span>Employee ID Format</span>
            </div>

            <!-- Page Header Card -->
            <div class="branches-header">
                <div class="header-left-box">
                    <div class="header-icon-badge">
                        <i class="fas fa-id-badge"></i>
                    </div>
                    <div class="header-title">
                        <h1>Employee ID Format</h1>
                        <p>Define auto-generation numbering formats, prefixes, and separators for employee profiles.</p>
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

            <!-- Main Form Card -->
            <div class="address-card-elevated">
                <div class="card-header-custom">
                    <div class="card-header-avatar shadow-sm">
                        <i class="fas fa-barcode"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold fs-5" style="color: #0a2e1f;">Employee ID Auto-Generation & Formatting</h5>
                        <small class="text-muted">Configure initial employee code patterns</small>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <form method="POST" action="{{ route('admin.settings.employee-id.update') }}">
                        @csrf

                        <div class="section-badge">
                            <i class="fas fa-sliders-h"></i> Numbering Structure & Auto-Assignment
                        </div>

                        <div class="row g-4">
                            <!-- Prefix -->
                            <div class="col-md-4">
                                <label class="form-label-custom">Employee ID Prefix <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-font"></i></span>
                                    <input type="text" name="prefix" id="empPrefix" class="form-control"
                                        placeholder="e.g. EMP" value="{{ old('prefix', $settings['prefix'] ?? 'EMP') }}" required>
                                </div>
                            </div>

                            <!-- Separator -->
                            <div class="col-md-4">
                                <label class="form-label-custom">Separator <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-minus"></i></span>
                                    <select name="separator" id="empSeparator" class="form-select" required>
                                        <option value="-" {{ ($settings['separator'] ?? '-') == '-' ? 'selected' : '' }}>Dash ( - )</option>
                                        <option value="/" {{ ($settings['separator'] ?? '') == '/' ? 'selected' : '' }}>Slash ( / )</option>
                                        <option value="_" {{ ($settings['separator'] ?? '') == '_' ? 'selected' : '' }}>Underscore ( _ )</option>
                                        <option value="" {{ ($settings['separator'] ?? '') == '' ? 'selected' : '' }}>None</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Digits Count -->
                            <div class="col-md-4">
                                <label class="form-label-custom">Number Length (Digits) <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                    <input type="number" name="number_digits" id="empDigits" class="form-control" min="2" max="8"
                                        value="{{ old('number_digits', $settings['number_digits'] ?? '4') }}" required>
                                </div>
                            </div>

                            <!-- Starting Number -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Starting Number Sequence <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-list-ol"></i></span>
                                    <input type="number" name="start_number" id="empStart" class="form-control" min="1"
                                        value="{{ old('start_number', $settings['start_number'] ?? '1001') }}" required>
                                </div>
                            </div>

                            <!-- Auto-Generate Toggle -->
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="switch-box-container w-100">
                                    <div class="form-check form-switch mb-0 d-flex align-items-center gap-2">
                                        <input class="form-check-input" type="checkbox" name="auto_generate" value="1" id="autoGenerateSwitch"
                                            {{ ($settings['auto_generate'] ?? '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="autoGenerateSwitch" style="font-size: 0.9rem;">
                                            Auto-assign ID on new employee registration
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="mt-5 pt-4 border-top d-flex justify-content-end">
                            <button type="submit" class="btn-save-address">
                                <i class="fas fa-save me-1.5"></i> Save Employee ID Settings
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
document.addEventListener('DOMContentLoaded', function() {
    const prefixInput = document.getElementById('empPrefix');
    const separatorSelect = document.getElementById('empSeparator');
    const digitsInput = document.getElementById('empDigits');
    const startInput = document.getElementById('empStart');
    const preview = document.getElementById('empIdPreview');

    function updatePreview() {
        if (!preview) return;
        let p = prefixInput.value || '';
        const s = separatorSelect.value || '';
        const d = parseInt(digitsInput.value) || 4;
        const st = parseInt(startInput.value) || 1001;
        const paddedNum = String(st).padStart(d, '0');

        // Clean trailing separator from prefix if user entered it manually
        if (s && (p.endsWith('-') || p.endsWith('/') || p.endsWith('_'))) {
            p = p.slice(0, -1);
        }

        preview.textContent = `${p}${s}${paddedNum}`;
    }

    [prefixInput, separatorSelect, digitsInput, startInput].forEach(el => {
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });

    updatePreview();
});
</script>
@endpush
