@extends('admin.layout.app')

@section('title', 'Performance Settings')

@push('styles')
<style>
    .performance-settings-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f4fbf7 100%);
        color: #0a2e1f;
    }

    .performance-settings-shell {
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
    .performance-settings-page .stat-card,
    .performance-settings-page .stat-card:first-of-type {
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

    .performance-settings-page .stat-card:first-of-type *,
    .performance-settings-page .stat-card * {
        -webkit-text-fill-color: initial;
    }

    .performance-settings-page .stat-card h3,
    .performance-settings-page .stat-card:first-of-type h3 {
        color: #0a2e1f !important;
        -webkit-text-fill-color: #0a2e1f !important;
    }

    .performance-settings-page .stat-card h6,
    .performance-settings-page .stat-card span,
    .performance-settings-page .stat-card:first-of-type span,
    .performance-settings-page .stat-card:first-of-type h6 {
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

    .stat-icon.cycle,
    .performance-settings-page .stat-card:first-of-type .stat-icon.cycle {
        background: linear-gradient(145deg, #d1fae5, #a7f3d0) !important;
        color: #059669 !important;
        -webkit-text-fill-color: #059669 !important;
    }

    .stat-icon.scale,
    .performance-settings-page .stat-card .stat-icon.scale {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
    }

    .stat-icon.metrics,
    .performance-settings-page .stat-card .stat-icon.metrics {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
    }

    .stat-icon.workflow,
    .performance-settings-page .stat-card .stat-icon.workflow {
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

    .input-group-custom .form-select {
        border: none;
        background-color: transparent;
        font-size: 0.92rem;
        font-weight: 600;
        color: #0a2e1f;
        padding-right: 18px;
        height: 50px;
    }

    .input-group-custom .form-select:focus {
        box-shadow: none;
        background-color: transparent;
    }

    .textarea-custom {
        border-radius: 16px;
        border: 1px solid rgba(16, 185, 129, 0.2);
        background-color: #fafefb;
        padding: 14px 18px;
        font-size: 0.92rem;
        font-weight: 600;
        color: #0a2e1f;
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

    /* Policy Switch Box */
    .policy-switch-box {
        background: #f0fdf4;
        border: 1px solid rgba(16, 185, 129, 0.25);
        border-radius: 20px;
        padding: 1.2rem 1.5rem;
        transition: all 0.25s ease;
        height: 100%;
        display: flex;
        align-items: center;
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
        .performance-settings-page {
            padding: 1.25rem 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="performance-settings-page">
    <div class="performance-settings-shell">
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
                <span>Performance & Appraisals</span>
            </div>

            <!-- Page Header Card -->
            <div class="branches-header">
                <div class="header-left-box">
                    <div class="header-icon-badge">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="header-title">
                        <h1>Performance & Appraisal Settings</h1>
                        <p>Configure review cycles, rating scales, self-assessments, 360-degree feedback, and evaluation metrics.</p>
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
                $cycleVal = $settings['appraisal_cycle'] ?? 'Quarterly';
                $scaleVal = $settings['rating_scale'] ?? '1-5 Star Scale';
                $metricsList = array_filter(explode(',', $settings['key_metrics'] ?? 'Task Completion Rate, Code Quality, Teamwork, Punctuality'));
                $metricCount = count($metricsList);
                $selfAssess = ($settings['self_assessment'] ?? '1') == '1';
                $peerReview = ($settings['peer_review'] ?? '1') == '1';
                $managerApproval = ($settings['manager_review_required'] ?? '1') == '1';
            @endphp
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon cycle">
                        <i class="fas fa-rotate-right"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Review Frequency</h6>
                        <h3>{{ $cycleVal }} Reviews</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon scale">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Rating Scale</h6>
                        <h3>{{ $scaleVal }}</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon metrics">
                        <i class="fas fa-list-check"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Evaluation Metrics</h6>
                        <h3>{{ $metricCount }} Key Metrics</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon workflow">
                        <i class="fas fa-users-gear"></i>
                    </div>
                    <div class="stat-info">
                        <h6>Evaluation Workflow</h6>
                        <h3>{{ $selfAssess ? 'Self' : '' }}{{ $peerReview ? ' + Peer' : '' }}{{ $managerApproval ? ' + Manager' : '' }}</h3>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="address-card-elevated">
                <div class="card-header-custom">
                    <div class="card-header-avatar shadow-sm">
                        <i class="fas fa-award"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold fs-5" style="color: #0a2e1f;">Appraisal Cycles & Performance Criteria</h5>
                        <small class="text-muted">Set up company performance review framework and evaluation rules</small>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    @if($isSettingsReadOnly)
                        <div class="alert d-flex align-items-center mb-4 rounded-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #1e40af; border-left: 4px solid #3b82f6 !important; padding: 14px 18px;">
                            <i class="fas fa-eye me-3 fs-3 text-primary"></i>
                            <div>
                                <strong class="d-block text-primary fw-bold" style="font-size: 14px;">View-Only Mode</strong>
                                <span style="font-size: 13px; color: #1e3a8a;">You are viewing performance appraisal settings in read-only mode. Only Administrators have permission to modify review cycles, rating metrics, and approval rules.</span>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.settings.performance.update') }}">
                        @csrf

                        <!-- Section 1: Review Cycle & Scale -->
                        <div class="section-badge">
                            <i class="fas fa-sliders"></i> Review Cycles & Rating Scale
                        </div>

                        <div class="row g-4 mb-5">
                            <!-- Appraisal Cycle -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Appraisal Review Frequency <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-sync-alt"></i></span>
                                    <select name="appraisal_cycle" class="form-select" {{ $isSettingsReadOnly ? 'disabled' : 'required' }}>
                                        <option value="Monthly" {{ $cycleVal == 'Monthly' ? 'selected' : '' }}>Monthly Reviews</option>
                                        <option value="Quarterly" {{ $cycleVal == 'Quarterly' ? 'selected' : '' }}>Quarterly Reviews</option>
                                        <option value="Bi-Annually" {{ $cycleVal == 'Bi-Annually' ? 'selected' : '' }}>Bi-Annual Reviews</option>
                                        <option value="Annually" {{ $cycleVal == 'Annually' ? 'selected' : '' }}>Annual Reviews</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Rating Scale -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Performance Rating Scale <span class="req-asterisk">*</span></label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text"><i class="fas fa-star"></i></span>
                                    <select name="rating_scale" class="form-select" {{ $isSettingsReadOnly ? 'disabled' : 'required' }}>
                                        <option value="1-5 Star Scale" {{ $scaleVal == '1-5 Star Scale' ? 'selected' : '' }}>1 to 5 Star Scale</option>
                                        <option value="1-10 Numerical Scale" {{ $scaleVal == '1-10 Numerical Scale' ? 'selected' : '' }}>1 to 10 Point Scale</option>
                                        <option value="Grade (A, B, C, D, F)" {{ $scaleVal == 'Grade (A, B, C, D, F)' ? 'selected' : '' }}>Grade (A, B, C, D, F)</option>
                                        <option value="Percentage (0-100%)" {{ $scaleVal == 'Percentage (0-100%)' ? 'selected' : '' }}>Percentage (0-100%)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Key Evaluation Metrics -->
                            <div class="col-12">
                                <label class="form-label-custom">Key Evaluation Metrics (Comma Separated) <span class="req-asterisk">*</span></label>
                                <textarea name="key_metrics" class="textarea-custom" rows="3" {{ $isSettingsReadOnly ? 'readonly' : 'required' }} placeholder="e.g. Task Completion Rate, Code Quality, Teamwork, Punctuality">{{ old('key_metrics', $settings['key_metrics'] ?? '') }}</textarea>
                                <small class="text-muted mt-1.5 d-block"><i class="fas fa-info-circle me-1" style="color: #059669;"></i>Comma-separated list of KPI indicators evaluated during appraisals (e.g. Task Completion Rate, Code Quality, Teamwork, Punctuality).</small>
                            </div>
                        </div>

                        <!-- Section 2: Review Participants & Approvals -->
                        <div class="section-badge">
                            <i class="fas fa-user-shield"></i> Review Participants & Approval Workflow
                        </div>

                        <div class="row g-4">
                            <!-- Self Assessment Switch Box -->
                            <div class="col-md-4">
                                <div class="policy-switch-box">
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-3 w-100">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="self_assessment" value="1" id="selfAssessmentSwitch"
                                            style="width: 2.8em; height: 1.4em; cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }};"
                                            {{ $selfAssess ? 'checked' : '' }}
                                            {{ $isSettingsReadOnly ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="selfAssessmentSwitch" style="cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }}; font-size: 0.88rem;">
                                            Enable Employee Self-Assessment
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Peer Review Switch Box -->
                            <div class="col-md-4">
                                <div class="policy-switch-box">
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-3 w-100">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="peer_review" value="1" id="peerReviewSwitch"
                                            style="width: 2.8em; height: 1.4em; cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }};"
                                            {{ $peerReview ? 'checked' : '' }}
                                            {{ $isSettingsReadOnly ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="peerReviewSwitch" style="cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }}; font-size: 0.88rem;">
                                            Enable 360-Degree Peer Feedback
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Manager Review Switch Box -->
                            <div class="col-md-4">
                                <div class="policy-switch-box">
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-3 w-100">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="manager_review_required" value="1" id="managerReviewSwitch"
                                            style="width: 2.8em; height: 1.4em; cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }};"
                                            {{ $managerApproval ? 'checked' : '' }}
                                            {{ $isSettingsReadOnly ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="managerReviewSwitch" style="cursor: {{ $isSettingsReadOnly ? 'default' : 'pointer' }}; font-size: 0.88rem;">
                                            Require Manager Final Approval
                                        </label>
                                    </div>
                                </div>
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
                                    <i class="fas fa-save me-1.5"></i> Save Performance Settings
                                </button>
                            @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
