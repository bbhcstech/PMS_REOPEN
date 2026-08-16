@extends('admin.layout.app')

@section('title', 'Document System Settings')

@push('styles')
<style>
    .document-settings-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f4fbf7 100%);
        color: #0a2e1f;
    }

    .document-settings-shell {
        position: relative;
        max-width: 1280px;
        margin: 0 auto;
    }

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
    }

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
    }

    .header-icon-badge {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        box-shadow: 0 8px 20px -4px rgba(5, 150, 105, 0.4);
    }

    .header-title h1 {
        font-size: 1.65rem;
        font-weight: 800;
        color: #0a2e1f;
        margin: 0;
    }

    .header-title p {
        font-size: 0.9rem;
        color: #64748b;
        margin: 4px 0 0 0;
    }

    .btn-back-settings {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.65rem 1.4rem;
        border-radius: 40px;
        background: #ffffff;
        border: 1px solid rgba(16, 185, 129, 0.2);
        color: #059669;
        font-weight: 700;
        font-size: 0.88rem;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        transition: all 0.25s ease;
    }

    .btn-back-settings:hover {
        background: #ecfdf5;
        color: #047857;
        transform: translateY(-2px);
    }

    .address-card-elevated {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        border: 1px solid rgba(16, 185, 129, 0.15);
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08);
        overflow: hidden;
    }

    .card-header-custom {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid rgba(16, 185, 129, 0.12);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .card-header-avatar {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .form-label-custom {
        font-size: 0.9rem;
        font-weight: 700;
        color: #0a2e1f;
        margin-bottom: 8px;
    }

    .input-group-custom {
        border-radius: 16px;
        border: 1px solid rgba(16, 185, 129, 0.25);
        background-color: #fafefb;
        overflow: hidden;
        transition: all 0.25s ease;
    }

    .input-group-custom:focus-within {
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
    }

    .input-group-custom .input-group-text {
        background: rgba(16, 185, 129, 0.08);
        border: none;
        color: #059669;
        font-size: 1.1rem;
        padding-left: 1.25rem;
        padding-right: 1.25rem;
    }

    .input-group-custom .form-control {
        border: none;
        background: transparent;
        padding: 0.8rem 1.2rem;
        font-weight: 600;
        color: #0f172a;
    }

    .btn-save-rules {
        height: 50px;
        border-radius: 40px;
        font-weight: 700;
        padding: 0 32px;
        background: linear-gradient(145deg, #10b981, #059669);
        color: white !important;
        border: none;
        box-shadow: 0 6px 20px -4px rgba(5, 150, 105, 0.4);
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .btn-save-rules:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -4px rgba(5, 150, 105, 0.5);
    }

    .extension-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: rgba(16, 185, 129, 0.12);
        color: #047857;
        font-weight: 700;
        font-size: 0.78rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .policy-item-card {
        padding: 1rem 1.25rem;
        background: #f8fafc;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
<div class="document-settings-page">
    <div class="document-settings-shell">
        <div class="ambient-orb orb-1"></div>
        <div class="ambient-orb orb-2"></div>

        <div class="content-wrapper">
            <!-- Breadcrumb -->
            <div class="breadcrumb-custom">
                <i class="fas fa-building"></i>
                <a href="{{ route('admin.settings.index') }}">Admin</a>
                <span>/</span>
                <a href="{{ route('admin.settings.index') }}">Settings</a>
                <span>/</span>
                <span>Document System Settings</span>
            </div>

            <!-- Page Header -->
            <div class="branches-header">
                <div class="header-left-box d-flex align-items-center gap-3">
                    <div class="header-icon-badge">
                        <i class="fas fa-file-shield"></i>
                    </div>
                    <div class="header-title">
                        <h1>Document System Settings</h1>
                        <p>Configure global document upload policies, size caps, and allowed extension rules.</p>
                    </div>
                </div>

                <a href="{{ route('admin.settings.index') }}" class="btn-back-settings">
                    <i class="fas fa-arrow-left me-1"></i> Back to Settings
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
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm rounded-4 border-0" role="alert">
                    <i class="fas fa-exclamation-circle fs-4 me-2"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Main Layout Grid -->
            <div class="row g-4">

                <!-- Left Column: System File Upload Rules Form -->
                <div class="col-lg-7 col-xl-8">
                    <div class="address-card-elevated h-100">
                        <div class="card-header-custom">
                            <div class="card-header-avatar shadow-sm">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold fs-5" style="color: #0a2e1f;">File Upload Limitations & Formats</h5>
                                <small class="text-muted">Set active file size caps and permitted formats for all roles.</small>
                            </div>
                        </div>

                        <div class="p-4 p-md-5">
                            <form method="POST" action="{{ route('admin.settings.document.update') }}">
                                @csrf
                                
                                <div class="mb-4">
                                    <label class="form-label-custom">Maximum File Size Limit (MB) <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-custom">
                                        <span class="input-group-text"><i class="fas fa-weight-hanging"></i></span>
                                        <input type="number" name="max_file_size_mb" class="form-control" value="{{ $settings['max_file_size_mb'] ?? '10' }}" min="1" max="100" required>
                                        <span class="input-group-text bg-white text-muted fw-bold border-0 pe-3">MB</span>
                                    </div>
                                    <small class="text-muted mt-1.5 d-block"><i class="fas fa-info-circle text-emerald-600 me-1"></i> Recommended setting: 10 MB per uploaded document.</small>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label-custom">Allowed Extensions (Comma Separated) <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-custom">
                                        <span class="input-group-text"><i class="fas fa-file-code"></i></span>
                                        <input type="text" name="allowed_extensions" class="form-control" value="{{ $settings['allowed_extensions'] ?? 'pdf,png,jpg,jpeg,doc,docx' }}" required>
                                    </div>
                                    
                                    <!-- Extension Preview Badges -->
                                    <div class="mt-2.5 d-flex flex-wrap gap-1.5 align-items-center">
                                        <small class="text-muted fw-semibold me-1">Permitted formats:</small>
                                        @foreach(array_map('trim', explode(',', strtolower($settings['allowed_extensions'] ?? 'pdf,png,jpg,jpeg,doc,docx'))) as $ext)
                                            <span class="extension-pill"><i class="fas fa-file me-1"></i> .{{ $ext }}</span>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="pt-4 border-top d-flex justify-content-end">
                                    <button type="submit" class="btn-save-rules">
                                        <i class="fas fa-save me-2"></i> Save Upload Rules
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Column: System Storage Architecture & Security Overview -->
                <div class="col-lg-5 col-xl-4">
                    <div class="address-card-elevated h-100">
                        <div class="card-header-custom">
                            <div class="card-header-avatar shadow-sm" style="background: linear-gradient(145deg, #e0f2fe, #bae6fd); color: #0284c7;">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold fs-5" style="color: #0a2e1f;">Role Storage Architecture</h5>
                                <small class="text-muted">Separate database & access rule summary.</small>
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="policy-item-card">
                                <div class="fw-bold text-dark small mb-1"><i class="fas fa-database text-emerald-600 me-1.5"></i> Separate Role Databases</div>
                                <small class="text-muted d-block">Documents are stored in role-isolated tables: <strong>employee_documents</strong>, <strong>hr_documents</strong>, <strong>manager_documents</strong>, & <strong>admin_documents</strong>.</small>
                            </div>

                            <div class="policy-item-card">
                                <div class="fw-bold text-dark small mb-1"><i class="fas fa-eye text-info me-1.5"></i> Repository Access Rules</div>
                                <small class="text-muted d-block"><strong>Admin</strong> has full access. <strong>HR & Managers</strong> access Employee & own role DBs. <strong>Employees</strong> access own DB only.</small>
                            </div>

                            <div class="policy-item-card">
                                <div class="fw-bold text-dark small mb-1"><i class="fas fa-history text-indigo-600 me-1.5"></i> Access History Tracking</div>
                                <small class="text-muted d-block">Every view/download logs timestamp and viewer identity into <strong>document_views</strong> table ("Seen By Who").</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
