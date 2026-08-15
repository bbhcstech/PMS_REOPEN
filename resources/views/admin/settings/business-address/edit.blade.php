@extends('admin.layout.app')

@section('title', 'Edit Business Branch / Address')

@push('styles')
<style>
    .create-branch-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f4fbf7 100%);
        color: #0a2e1f;
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
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
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
    .input-group-custom textarea {
        border: none;
        background-color: transparent;
        font-size: 0.92rem;
        font-weight: 600;
        color: #0a2e1f;
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

    @media (max-width: 768px) {
        .create-branch-page {
            padding: 1.25rem 1rem;
        }

        .card-header-custom {
            padding: 1.25rem 1.5rem;
        }
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
                                    <h5 class="mb-0 fw-bold fs-5" style="color: #0a2e1f;">Edit Branch Information</h5>
                                    <small class="text-muted">Modify official details for {{ $businessAddress->display_name }}</small>
                                </div>
                            </div>

                            @if($businessAddress->is_default)
                                <span class="badge rounded-pill px-3 py-2" style="background: linear-gradient(145deg, #ecfdf5, #d1fae5); color: #065f46; border: 1px solid rgba(5, 150, 105, 0.25); font-weight: 750;">
                                    <i class="fas fa-star me-1" style="color: #059669;"></i> Current Primary Default
                                </span>
                            @endif
                        </div>

                        <div class="p-4 p-md-5">
                            <form method="POST" action="{{ route('admin.settings.business-address.update', $businessAddress) }}" enctype="multipart/form-data">
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
                                        <label for="phone" class="form-label-custom">
                                            Branch Phone
                                        </label>
                                        <div class="input-group input-group-custom">
                                            <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                                   id="phone" name="phone"
                                                   value="{{ old('phone', $businessAddress->phone) }}"
                                                   placeholder="+91 98765 43210">
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
                                                <h6 class="fw-bold mb-1" style="color: #0a2e1f;">Click to change branch logo or drag & drop</h6>
                                                <p class="text-muted small mb-0">Supported formats: PNG, JPG, GIF, SVG, or WEBP (Max 3MB)</p>
                                            </div>

                                            @if($hasBranchLogo)
                                                <div id="currentBranchLogoBadge" class="badge rounded-pill mt-2 px-3 py-1.5" style="background: linear-gradient(145deg, #ecfdf5, #d1fae5); color: #065f46; border: 1px solid rgba(5, 150, 105, 0.25);">
                                                    <i class="fas fa-check-circle me-1" style="color: #059669;"></i> Current logo loaded (Click to update)
                                                </div>
                                            @endif

                                            <div id="fileSelectedBadge" class="badge rounded-pill mt-2 d-none px-3 py-1.5" style="background: linear-gradient(145deg, #ecfdf5, #d1fae5); color: #065f46; border: 1px solid rgba(5, 150, 105, 0.25);">
                                                <i class="fas fa-check me-1" style="color: #059669;"></i> <span id="fileNameText">New logo selected</span>
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
                                                   id="country" name="country"
                                                   value="{{ old('country', $businessAddress->country) }}"
                                                   placeholder="e.g. India" required>
                                        </div>
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
                                        <div class="p-3.5 rounded-4 d-flex align-items-center gap-3" style="background: #f0fdf4; border: 1px solid rgba(16, 185, 129, 0.25);">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input fs-5" type="checkbox"
                                                       id="is_default" name="is_default" value="1"
                                                       {{ old('is_default', $businessAddress->is_default) ? 'checked' : '' }}>
                                            </div>
                                            <div>
                                                <label class="form-check-label fw-bold mb-0" for="is_default" style="color: #0a2e1f;">
                                                    Set as default primary branch address
                                                </label>
                                                <div class="text-muted small">If enabled, this location will be designated as the primary head office address.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Action Buttons -->
                                <div class="mt-5 pt-4 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <a href="{{ route('admin.settings.business-address.index') }}" class="btn rounded-pill px-4 fw-bold" style="background: #f1f5f9; color: #475569;">
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
</script>
@endpush
