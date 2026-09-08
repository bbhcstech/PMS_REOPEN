@extends('layouts.superadmin')

@section('title', 'Provision New Tenant Company')
@section('page_title', 'Tenant Management')
@section('page_subtitle', 'Provision New Tenant Company & Initialize Database')

@section('content')
<style>
  :root {
    --brand-primary: #0f744c;
    --brand-primary-hover: #0a5236;
    --brand-glow: rgba(15, 116, 76, 0.2);
    --brand-emerald: #10b981;
    --brand-indigo: #6366f1;
    --brand-purple: #8b5cf6;
    --brand-amber: #f59e0b;
    --slate-dark: #0f172a;
    --slate-body: #334155;
    --slate-muted: #64748b;
    --slate-light: #f8fafc;
    --border-subtle: #cbd5e1;
    --card-shadow: 0 20px 35px -10px rgba(15, 23, 42, 0.07), 0 10px 15px -5px rgba(15, 23, 42, 0.04);
  }

  /* KEYFRAME ANIMATIONS */
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(24px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes pulseGreenDot {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
  }

  @keyframes shimmerBtn {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
  }

  @keyframes floatIcon {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
  }

  @keyframes cardPop {
    0% { transform: scale(0.98); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
  }

  /* STAGGERED FADE IN */
  .anim-fade-1 { animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
  .anim-fade-2 { animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.1s forwards; opacity: 0; }
  .anim-fade-3 { animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards; opacity: 0; }
  .anim-fade-4 { animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards; opacity: 0; }
  .anim-fade-5 { animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) 0.4s forwards; opacity: 0; }

  .provision-container {
    max-width: 980px;
    margin: 0 auto 40px auto;
  }

  /* HEADER BAR */
  .provision-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 16px;
  }

  .breadcrumb-custom {
    font-size: 13px;
    font-weight: 600;
    color: var(--slate-muted);
    margin-bottom: 6px;
  }

  .breadcrumb-custom span {
    color: var(--brand-primary);
  }

  .provision-title {
    font-size: 26px;
    font-weight: 800;
    color: var(--slate-dark);
    margin: 0;
    letter-spacing: -0.6px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .provision-title-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, #0f744c 0%, #10b981 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    box-shadow: 0 8px 16px var(--brand-glow);
  }

  .provision-subtitle {
    font-size: 14px;
    color: var(--slate-muted);
    margin-top: 4px;
    font-weight: 500;
  }

  /* WIZARD STEPPER TRACKER */
  .stepper-wrap {
    position: relative;
    margin-bottom: 32px;
  }

  .stepper-track-line {
    position: absolute;
    top: 24px;
    left: 15%;
    right: 15%;
    height: 3px;
    background: #e2e8f0;
    z-index: 1;
    border-radius: 999px;
  }

  .stepper-track-fill {
    position: absolute;
    top: 24px;
    left: 15%;
    height: 3px;
    background: linear-gradient(90deg, var(--brand-primary), var(--brand-emerald));
    z-index: 1;
    border-radius: 999px;
    width: 0%;
    transition: width 0.4s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .wizard-stepper {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
  }

  .step-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border: 1.5px solid var(--border-subtle);
    border-radius: 16px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    cursor: pointer;
  }

  .step-card:hover {
    border-color: #94a3b8;
    transform: translateY(-2px);
  }

  .step-card.active {
    border-color: var(--brand-primary);
    background: #ffffff;
    box-shadow: 0 10px 25px var(--brand-glow), 0 0 0 1px var(--brand-primary);
  }

  .step-number {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: #f1f5f9;
    color: var(--slate-muted);
    font-weight: 800;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    flex-shrink: 0;
    border: 1px solid #cbd5e1;
  }

  .step-card.active .step-number {
    background: linear-gradient(135deg, var(--brand-primary), var(--brand-emerald));
    color: #ffffff;
    border-color: transparent;
    box-shadow: 0 4px 12px var(--brand-glow);
  }

  .step-info .step-title {
    font-size: 13.5px;
    font-weight: 800;
    color: var(--slate-dark);
    line-height: 1.2;
  }

  .step-info .step-desc {
    font-size: 11.5px;
    color: var(--slate-muted);
    margin-top: 3px;
    font-weight: 500;
  }

  /* FORM CARD SECTION */
  .form-section-card {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 24px;
    padding: 36px;
    box-shadow: var(--card-shadow);
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
  }

  .form-section-card:hover {
    border-color: #94a3b8;
  }

  .section-badge-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 20px;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 16px;
  }

  .section-badge-left {
    display: flex;
    align-items: center;
    gap: 14px;
  }

  .section-badge-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: rgba(15, 116, 76, 0.1);
    color: var(--brand-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
    animation: floatIcon 3s ease-in-out infinite;
  }

  .section-badge-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--slate-dark);
    letter-spacing: -0.3px;
  }

  .section-badge-sub {
    font-size: 12.5px;
    color: var(--slate-muted);
    font-weight: 500;
    margin-top: 2px;
  }

  /* LIVE TENANT PREVIEW CARD */
  .live-tenant-preview {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 8px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: inset 0 1px 2px rgba(255,255,255,0.8);
  }

  .live-avatar-box {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: var(--brand-primary);
    color: #fff;
    font-weight: 800;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
  }

  .live-avatar-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .live-preview-info {
    font-size: 12px;
  }

  .live-preview-name {
    font-weight: 800;
    color: var(--slate-dark);
    line-height: 1.1;
  }

  .live-preview-domain {
    font-size: 10.5px;
    color: var(--slate-muted);
    font-weight: 600;
  }

  /* INPUT FIELDS */
  .form-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
  }

  .form-field-group {
    display: flex;
    flex-direction: column;
    gap: 7px;
  }

  .form-label {
    font-size: 13px;
    font-weight: 700;
    color: var(--slate-dark);
    display: flex;
    align-items: center;
    gap: 4px;
  }

  .form-label .req {
    color: #ef4444;
  }

  .input-with-icon {
    position: relative;
    display: flex;
    align-items: center;
  }

  .input-with-icon i {
    position: absolute;
    left: 14px;
    font-size: 20px;
    color: #94a3b8;
    pointer-events: none;
    transition: color 0.25s ease;
  }

  .input-control {
    width: 100%;
    padding: 13px 16px 13px 44px;
    border: 1.5px solid #cbd5e1;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 500;
    color: var(--slate-dark);
    background: #f8fafc;
    outline: none;
    transition: all 0.25s ease;
  }

  .input-control:focus {
    border-color: var(--brand-primary);
    background: #ffffff;
    box-shadow: 0 0 0 4px var(--brand-glow);
  }

  .input-with-icon:focus-within i {
    color: var(--brand-primary);
  }

  /* SLUG WRAPPER */
  .slug-input-wrap {
    display: flex;
    border: 1.5px solid #cbd5e1;
    border-radius: 14px;
    overflow: hidden;
    background: #f8fafc;
    transition: all 0.25s ease;
  }

  .slug-input-wrap:focus-within {
    border-color: var(--brand-primary);
    box-shadow: 0 0 0 4px var(--brand-glow);
    background: #ffffff;
  }

  .slug-prefix {
    background: #e2e8f0;
    color: #475569;
    font-weight: 800;
    font-family: monospace;
    font-size: 13.5px;
    padding: 13px 16px;
    display: flex;
    align-items: center;
    border-right: 1.5px solid #cbd5e1;
  }

  .slug-input {
    width: 100%;
    padding: 13px 16px;
    border: none;
    outline: none;
    font-size: 14px;
    font-family: monospace;
    font-weight: 700;
    color: #0284c7;
    background: transparent;
  }

  .db-badge-preview {
    font-size: 12px;
    color: var(--slate-muted);
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
  }

  .pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    display: inline-block;
    animation: pulseGreenDot 2s infinite;
  }

  .db-badge-preview code {
    font-family: monospace;
    font-weight: 700;
    color: #0369a1;
    background: #e0f2fe;
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid #bae6fd;
  }

  /* PASSWORD STRENGTH METER */
  .password-meter-wrap {
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .meter-bar-track {
    flex: 1;
    height: 5px;
    background: #e2e8f0;
    border-radius: 999px;
    overflow: hidden;
  }

  .meter-bar-fill {
    height: 100%;
    width: 0%;
    background: #ef4444;
    transition: all 0.3s ease;
    border-radius: 999px;
  }

  .meter-text {
    font-size: 11px;
    font-weight: 700;
    color: var(--slate-muted);
    min-width: 60px;
    text-align: right;
  }

  /* DRAG AND DROP BOX */
  .drag-drop-box {
    border: 2px dashed #cbd5e1;
    border-radius: 18px;
    padding: 28px 24px;
    text-align: center;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.25s ease;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }

  .drag-drop-box:hover, .drag-drop-box.dragover {
    border-color: var(--brand-primary);
    background: rgba(15, 116, 76, 0.03);
    box-shadow: 0 8px 20px var(--brand-glow);
    transform: translateY(-2px);
  }

  .drag-drop-box input[type="file"] {
    display: none;
  }

  .upload-icon-circle {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: var(--brand-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 12px;
    box-shadow: 0 6px 14px rgba(0,0,0,0.04);
    transition: transform 0.25s ease;
  }

  .drag-drop-box:hover .upload-icon-circle {
    transform: translateY(-3px) scale(1.05);
    color: var(--brand-emerald);
  }

  .upload-primary-text {
    font-size: 14px;
    font-weight: 700;
    color: var(--slate-dark);
  }

  .upload-primary-text span {
    color: var(--brand-primary);
    text-decoration: underline;
  }

  .upload-sub-text {
    font-size: 11.5px;
    color: var(--slate-muted);
    margin-top: 4px;
  }

  .upload-preview-bar {
    display: none;
    align-items: center;
    gap: 16px;
    width: 100%;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    padding: 12px 16px;
    animation: fadeInUp 0.3s ease forwards;
  }

  .upload-preview-img {
    width: 52px;
    height: 52px;
    object-fit: cover;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
  }

  .upload-preview-img.circle {
    border-radius: 50%;
  }

  .upload-preview-meta {
    flex: 1;
    text-align: left;
    overflow: hidden;
  }

  .upload-file-name {
    font-size: 13.5px;
    font-weight: 800;
    color: var(--slate-dark);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .upload-status-tag {
    font-size: 11.5px;
    color: #10b981;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 2px;
  }

  .btn-remove-upload {
    background: #fee2e2;
    color: #ef4444;
    border: none;
    border-radius: 10px;
    padding: 8px 14px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .btn-remove-upload:hover {
    background: #fca5a5;
    color: #991b1b;
    transform: scale(1.03);
  }

  /* SUBSCRIPTION PLAN GRID */
  .subscription-tier-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
  }

  .subscription-card {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 20px;
    padding: 22px 16px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
  }

  .subscription-card:hover {
    border-color: #94a3b8;
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.05);
  }

  .subscription-card.selected {
    border-color: var(--brand-primary);
    background: #ffffff;
    box-shadow: 0 12px 28px var(--brand-glow), 0 0 0 1px var(--brand-primary);
    transform: translateY(-4px) scale(1.02);
  }

  .plan-tag {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    margin-bottom: 12px;
  }

  .plan-tag.free { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
  .plan-tag.gold { background: rgba(245, 158, 11, 0.15); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.3); }
  .plan-tag.platinum { background: rgba(37, 99, 235, 0.15); color: #2563eb; border: 1px solid rgba(37, 99, 235, 0.3); }
  .plan-tag.diamond { background: rgba(124, 58, 237, 0.15); color: #7c3aed; border: 1px solid rgba(124, 58, 237, 0.3); }

  .plan-price {
    font-size: 18px;
    font-weight: 800;
    color: var(--slate-dark);
    margin-bottom: 8px;
  }

  .plan-features {
    font-size: 11.5px;
    color: var(--slate-muted);
    font-weight: 600;
    line-height: 1.5;
  }

  .subscription-card .check-mark {
    position: absolute;
    top: 12px;
    right: 12px;
    font-size: 20px;
    color: var(--brand-primary);
    display: none;
    animation: cardPop 0.3s ease;
  }

  .subscription-card.selected .check-mark {
    display: block;
  }

  /* ACTION FOOTER BAR WITH SHIMMER CTA */
  .provision-action-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24px 28px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 20px;
    box-shadow: var(--card-shadow);
    flex-wrap: wrap;
    gap: 16px;
  }

  .notice-text {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    color: var(--slate-muted);
    font-weight: 600;
  }

  .notice-icon-box {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(15, 116, 76, 0.1);
    color: var(--brand-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
  }

  .btn-shimmer-cta {
    background: linear-gradient(135deg, #0f744c 0%, #10b981 50%, #0f744c 100%);
    background-size: 200% 100%;
    color: #ffffff;
    border: none;
    border-radius: 14px;
    padding: 14px 32px;
    font-size: 14.5px;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 10px 25px var(--brand-glow);
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .btn-shimmer-cta:hover {
    animation: shimmerBtn 2s infinite linear;
    transform: translateY(-2px);
    box-shadow: 0 14px 30px rgba(15, 116, 76, 0.35);
  }

  @media (max-width: 868px) {
    .form-grid-2, .wizard-stepper, .subscription-tier-grid {
      grid-template-columns: 1fr;
    }
    .stepper-track-line, .stepper-track-fill {
      display: none;
    }
  }
</style>

<div class="provision-container">
  <!-- PAGE HEADER -->
  <div class="provision-header anim-fade-1">
    <div>
      <div class="breadcrumb-custom">
        Platform <span>/</span> Companies <span>/</span> <span style="color: var(--slate-dark);">Provision Tenant</span>
      </div>
      <h1 class="provision-title">
        <div class="provision-title-icon"><i class="bx bx-buildings"></i></div>
        Provision New Tenant Company
      </h1>
      <p class="provision-subtitle">Create isolated MySQL database, run automated migrations, and initialize tenant admin access.</p>
    </div>
    <a href="{{ route('super-admin.companies.index') }}" class="btn-custom btn-outline-custom">
      <i class="bx bx-arrow-back"></i> Back to Companies
    </a>
  </div>

  <!-- WIZARD STEPPER TRACKER -->
  <div class="stepper-wrap anim-fade-2">
    <div class="stepper-track-line"></div>
    <div class="stepper-track-fill" id="stepperTrackFill"></div>

    <div class="wizard-stepper">
      <div class="step-card active" id="stepper-1" onclick="scrollToSection('section-1')">
        <div class="step-number">1</div>
        <div class="step-info">
          <div class="step-title">Organization &amp; DB</div>
          <div class="step-desc">Company details &amp; slug</div>
        </div>
      </div>

      <div class="step-card" id="stepper-2" onclick="scrollToSection('section-2')">
        <div class="step-number">2</div>
        <div class="step-info">
          <div class="step-title">Primary Admin</div>
          <div class="step-desc">Credentials &amp; avatar</div>
        </div>
      </div>

      <div class="step-card" id="stepper-3" onclick="scrollToSection('section-3')">
        <div class="step-number">3</div>
        <div class="step-info">
          <div class="step-title">Subscription Tier</div>
          <div class="step-desc">Plan &amp; resource quota</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ERROR ALERT -->
  @if ($errors->any())
    <div style="background: #fef2f2; border: 1px solid #fca5a5; border-radius: 16px; padding: 18px 24px; margin-bottom: 28px; color: #991b1b; font-size: 14px; animation: fadeInUp 0.4s ease;">
      <div style="font-weight: 800; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
        <i class="bx bx-error-circle" style="font-size: 22px; color: #dc2626;"></i> Please correct the following provisioning validation errors:
      </div>
      <ul style="margin: 0; padding-left: 28px; font-weight: 600;">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- FORM START -->
  <form method="POST" action="{{ route('super-admin.companies.store') }}" enctype="multipart/form-data" id="provisionCompanyForm">
    @csrf

    <!-- SECTION 1: COMPANY & DB DETAILS -->
    <div class="form-section-card anim-fade-3" id="section-1">
      <div class="section-badge-header">
        <div class="section-badge-left">
          <div class="section-badge-icon">
            <i class="bx bx-building-house"></i>
          </div>
          <div>
            <div class="section-badge-title">1. Organization Profile &amp; Database Configuration</div>
            <div class="section-badge-sub">Define company details, contact metadata, and automated database identifier</div>
          </div>
        </div>

        <!-- Live Tenant Card Preview Badge -->
        <div class="live-tenant-preview">
          <div class="live-avatar-box" id="live_company_logo_box">
            <span id="live_company_initials">CO</span>
          </div>
          <div class="live-preview-info">
            <div class="live-preview-name" id="live_company_name">Company Name</div>
            <div class="live-preview-domain" id="live_company_domain">company.platform.io</div>
          </div>
        </div>
      </div>

      <div class="form-grid-2">
        <!-- Company Name -->
        <div class="form-field-group">
          <label class="form-label">Company Name <span class="req">*</span></label>
          <div class="input-with-icon">
            <i class="bx bx-building"></i>
            <input type="text" name="name" id="company_name_input" value="{{ old('name') }}" required placeholder="e.g. Acme Enterprise Solutions" class="input-control" />
          </div>
        </div>

        <!-- Slug / DB Identifier -->
        <div class="form-field-group">
          <label class="form-label">Slug / DB Identifier <span class="req">*</span></label>
          <div class="slug-input-wrap">
            <span class="slug-prefix">{{ $dbPrefix ?? 'pms_' }}</span>
            <input type="text" name="slug" id="company_slug_input" value="{{ old('slug') }}" required placeholder="community_hub" class="slug-input" />
          </div>
          <div class="db-badge-preview">
            <span class="pulse-dot"></span> Target Database: <code id="db_name_preview">{{ $dbPrefix ?? 'pms_' }}community_hub</code> • Charset: <span>utf8mb4_general_ci</span>
          </div>
        </div>

        <!-- Contact Email -->
        <div class="form-field-group">
          <label class="form-label">Company Contact Email <span class="req">*</span></label>
          <div class="input-with-icon">
            <i class="bx bx-envelope"></i>
            <input type="email" name="email" value="{{ old('email') }}" required placeholder="contact@acme.com" class="input-control" />
          </div>
        </div>

        <!-- Phone Number -->
        <div class="form-field-group">
          <label class="form-label">Phone Number</label>
          <div class="input-with-icon">
            <i class="bx bx-phone"></i>
            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+1 (555) 019-2831" class="input-control" />
          </div>
        </div>

        <!-- Company Address -->
        <div class="form-field-group" style="grid-column: span 2;">
          <label class="form-label">Company Address</label>
          <div class="input-with-icon">
            <i class="bx bx-map" style="top: 14px;"></i>
            <textarea name="address" rows="2" placeholder="e.g. 100 Innovation Way, Suite 400, Tech City, CA 94016" class="input-control" style="resize: vertical; min-height: 72px;">{{ old('address') }}</textarea>
          </div>
        </div>

        <!-- Company Logo Upload -->
        <div class="form-field-group" style="grid-column: span 2;">
          <label class="form-label">Company Logo</label>
          <div class="drag-drop-box" id="logo_drop_box">
            <input type="file" name="company_logo" id="company_logo_input" accept="image/*" />
            <div class="default-upload-state">
              <div class="upload-icon-circle">
                <i class="bx bx-cloud-upload"></i>
              </div>
              <div class="upload-primary-text">Drag &amp; drop company logo here or <span>browse files</span></div>
              <div class="upload-sub-text">PNG, JPG, WEBP, or SVG up to 5MB (Recommended size: 512x512px)</div>
            </div>
            <div class="upload-preview-bar" id="company_logo_preview_bar">
              <img src="" alt="Company Logo Preview" class="upload-preview-img" id="company_logo_img" />
              <div class="upload-preview-meta">
                <div class="upload-file-name" id="company_logo_filename">logo.png</div>
                <div class="upload-status-tag"><i class="bx bx-check-circle"></i> Ready for upload</div>
              </div>
              <button type="button" class="btn-remove-upload" id="btn_remove_logo"><i class="bx bx-trash"></i> Remove</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION 2: INITIAL ADMIN CREDENTIALS -->
    <div class="form-section-card anim-fade-4" id="section-2">
      <div class="section-badge-header">
        <div class="section-badge-left">
          <div class="section-badge-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--brand-indigo);">
            <i class="bx bx-user-pin"></i>
          </div>
          <div>
            <div class="section-badge-title">2. Primary Tenant Administrator Account</div>
            <div class="section-badge-sub">Configure master admin credentials to initialize full company workspace access</div>
          </div>
        </div>
      </div>

      <div class="form-grid-2">
        <!-- Admin Full Name -->
        <div class="form-field-group" style="grid-column: span 2;">
          <label class="form-label">Admin Full Name <span class="req">*</span></label>
          <div class="input-with-icon">
            <i class="bx bx-user"></i>
            <input type="text" name="admin_name" value="{{ old('admin_name') }}" required placeholder="e.g. Sarah Connor" class="input-control" />
          </div>
        </div>

        <!-- Admin Login Email -->
        <div class="form-field-group">
          <label class="form-label">Admin Login Email <span class="req">*</span></label>
          <div class="input-with-icon">
            <i class="bx bx-at"></i>
            <input type="email" name="admin_email" value="{{ old('admin_email') }}" required placeholder="s.connor@acme.com" class="input-control" />
          </div>
        </div>

        <!-- Admin Password + Live Strength Meter -->
        <div class="form-field-group">
          <label class="form-label">Admin Password <span class="req">*</span></label>
          <div class="input-with-icon">
            <i class="bx bx-lock-alt"></i>
            <input type="password" name="admin_password" id="admin_password_input" required placeholder="••••••••••••" class="input-control" style="padding-right: 42px;" />
            <i class="bx bx-show" id="toggle_password_btn" style="left: auto; right: 14px; cursor: pointer; pointer-events: auto;"></i>
          </div>
          <div class="password-meter-wrap">
            <div class="meter-bar-track">
              <div class="meter-bar-fill" id="password_meter_fill"></div>
            </div>
            <div class="meter-text" id="password_meter_text">Enter password</div>
          </div>
        </div>

        <!-- Admin Profile Picture -->
        <div class="form-field-group" style="grid-column: span 2;">
          <label class="form-label">Admin Profile Picture</label>
          <div class="drag-drop-box" id="avatar_drop_box">
            <input type="file" name="admin_profile_image" id="admin_profile_input" accept="image/*" />
            <div class="default-upload-state">
              <div class="upload-icon-circle" style="color: var(--brand-indigo);">
                <i class="bx bx-user-circle"></i>
              </div>
              <div class="upload-primary-text">Drag &amp; drop admin avatar picture here or <span>browse files</span></div>
              <div class="upload-sub-text">PNG, JPG, WEBP up to 5MB (Square ratio recommended)</div>
            </div>
            <div class="upload-preview-bar" id="admin_profile_preview_bar">
              <img src="" alt="Admin Profile Preview" class="upload-preview-img circle" id="admin_profile_img" />
              <div class="upload-preview-meta">
                <div class="upload-file-name" id="admin_profile_filename">avatar.png</div>
                <div class="upload-status-tag"><i class="bx bx-check-circle"></i> Profile image attached</div>
              </div>
              <button type="button" class="btn-remove-upload" id="btn_remove_avatar"><i class="bx bx-trash"></i> Remove</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION 3: SUBSCRIPTION TIER SELECTION -->
    <div class="form-section-card anim-fade-5" id="section-3">
      <div class="section-badge-header">
        <div class="section-badge-left">
          <div class="section-badge-icon" style="background: rgba(124, 58, 237, 0.1); color: var(--brand-purple);">
            <i class="bx bx-layer"></i>
          </div>
          <div>
            <div class="section-badge-title">3. Initial Subscription Tier &amp; Resource Allocation</div>
            <div class="section-badge-sub">Set active plan tier for max users, project limits, and database storage quota</div>
          </div>
        </div>
      </div>

      <input type="hidden" name="subscription_plan" id="selectedPlanInput" value="free" />

      <div class="subscription-tier-grid">
        <!-- FREE PLAN -->
        <div class="subscription-card selected" data-plan="free">
          <i class="bx bx-check-circle check-mark"></i>
          <span class="plan-tag free">FREE</span>
          <div class="plan-price">₹0 <span style="font-size: 11px; color: var(--slate-muted); font-weight: 500;">/mo</span></div>
          <div class="plan-features">
            • 5 Users limit<br>
            • 5GB Storage<br>
            • Standard Support
          </div>
        </div>

        <!-- GOLD PLAN -->
        <div class="subscription-card" data-plan="gold">
          <i class="bx bx-check-circle check-mark"></i>
          <span class="plan-tag gold">GOLD</span>
          <div class="plan-price">₹4,999 <span style="font-size: 11px; color: var(--slate-muted); font-weight: 500;">/mo</span></div>
          <div class="plan-features">
            • 25 Users limit<br>
            • 25GB Storage<br>
            • Priority Email
          </div>
        </div>

        <!-- PLATINUM PLAN -->
        <div class="subscription-card" data-plan="platinum">
          <i class="bx bx-check-circle check-mark"></i>
          <span class="plan-tag platinum">PLATINUM</span>
          <div class="plan-price">₹9,999 <span style="font-size: 11px; color: var(--slate-muted); font-weight: 500;">/mo</span></div>
          <div class="plan-features">
            • 100 Users limit<br>
            • 100GB Storage<br>
            • 24/7 Phone Support
          </div>
        </div>

        <!-- DIAMOND PLAN -->
        <div class="subscription-card" data-plan="diamond">
          <i class="bx bx-check-circle check-mark"></i>
          <span class="plan-tag diamond">DIAMOND</span>
          <div class="plan-price">₹19,999 <span style="font-size: 11px; color: var(--slate-muted); font-weight: 500;">/mo</span></div>
          <div class="plan-features">
            • Unlimited Users<br>
            • Dedicated Storage<br>
            • 24/7 VIP SLA
          </div>
        </div>
      </div>
    </div>

    <!-- ACTION FOOTER BAR WITH SHIMMER CTA -->
    <div class="provision-action-footer">
      <div class="notice-text">
        <div class="notice-icon-box">
          <i class="bx bx-server"></i>
        </div>
        <div>
          Automated database creation: <code id="footer_db_code" style="font-family: monospace; font-weight: 700; color: var(--slate-dark);">pms_acme</code> &amp; schema migration execution.
        </div>
      </div>

      <div style="display: flex; align-items: center; gap: 14px;">
        <a href="{{ route('super-admin.companies.index') }}" class="btn-custom btn-outline-custom">Cancel</a>
        <button type="submit" class="btn-shimmer-cta" id="submitProvisionBtn">
          <i class="bx bx-rocket" style="font-size: 20px;"></i> Provision Company &amp; Run Migrations
        </button>
      </div>
    </div>
  </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // 1. Dynamic Auto Slug & Real-time Live Preview Header
  const nameInput = document.getElementById('company_name_input');
  const slugInput = document.getElementById('company_slug_input');
  const dbPreview = document.getElementById('db_name_preview');
  const footerDbCode = document.getElementById('footer_db_code');

  const liveName = document.getElementById('live_company_name');
  const liveDomain = document.getElementById('live_company_domain');
  const liveInitials = document.getElementById('live_company_initials');

  function updateSlugAndLivePreview() {
    let nameVal = nameInput ? nameInput.value.trim() : '';
    let slugVal = slugInput ? slugInput.value.trim().toLowerCase().replace(/[^a-z0-9_]/g, '') : '';

    if (!slugVal && nameVal) {
      slugVal = nameVal.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');
      if (slugInput) slugInput.value = slugVal;
    }

    const tenantPrefix = @json($dbPrefix ?? 'pms_');
    const fullDb = tenantPrefix + (slugVal || 'community_hub');
    if (dbPreview) dbPreview.textContent = fullDb;
    if (footerDbCode) footerDbCode.textContent = fullDb;

    if (liveName) liveName.textContent = nameVal || 'Company Name';
    if (liveDomain) liveDomain.textContent = (slugVal || 'company') + '.platform.io';
    if (liveInitials && nameVal) {
      liveInitials.textContent = nameVal.substring(0, 2).toUpperCase();
    }
  }

  if (nameInput) nameInput.addEventListener('input', updateSlugAndLivePreview);
  if (slugInput) slugInput.addEventListener('input', function() {
    this.value = this.value.toLowerCase().replace(/[^a-z0-9_]/g, '');
    updateSlugAndLivePreview();
  });

  // 2. Interactive Stepper Navigation & Track Bar
  window.scrollToSection = function(sectionId) {
    const el = document.getElementById(sectionId);
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  };

  const trackFill = document.getElementById('stepperTrackFill');
  const stepCards = document.querySelectorAll('.wizard-stepper .step-card');

  window.addEventListener('scroll', function() {
    const s1 = document.getElementById('section-1');
    const s2 = document.getElementById('section-2');
    const s3 = document.getElementById('section-3');

    if (!s1 || !s2 || !s3) return;

    const scrollPos = window.scrollY + 250;
    if (scrollPos >= s3.offsetTop) {
      if (trackFill) trackFill.style.width = '100%';
      stepCards.forEach(c => c.classList.remove('active'));
      document.getElementById('stepper-3')?.classList.add('active');
    } else if (scrollPos >= s2.offsetTop) {
      if (trackFill) trackFill.style.width = '50%';
      stepCards.forEach(c => c.classList.remove('active'));
      document.getElementById('stepper-2')?.classList.add('active');
    } else {
      if (trackFill) trackFill.style.width = '0%';
      stepCards.forEach(c => c.classList.remove('active'));
      document.getElementById('stepper-1')?.classList.add('active');
    }
  });

  // 3. Password Strength Meter
  const pwdInput = document.getElementById('admin_password_input');
  const pwdFill = document.getElementById('password_meter_fill');
  const pwdText = document.getElementById('password_meter_text');

  if (pwdInput && pwdFill && pwdText) {
    pwdInput.addEventListener('input', function() {
      const val = this.value;
      let score = 0;
      if (val.length >= 6) score += 25;
      if (val.length >= 10) score += 25;
      if (/[A-Z]/.test(val)) score += 25;
      if (/[0-9!@#$%^&*]/.test(val)) score += 25;

      pwdFill.style.width = score + '%';
      if (score === 0) {
        pwdFill.style.background = '#ef4444';
        pwdText.textContent = 'Enter password';
      } else if (score <= 25) {
        pwdFill.style.background = '#ef4444';
        pwdText.textContent = 'Weak';
      } else if (score <= 50) {
        pwdFill.style.background = '#f59e0b';
        pwdText.textContent = 'Fair';
      } else if (score <= 75) {
        pwdFill.style.background = '#3b82f6';
        pwdText.textContent = 'Good';
      } else {
        pwdFill.style.background = '#10b981';
        pwdText.textContent = 'Strong';
      }
    });
  }

  // 4. Password Toggle
  const toggleBtn = document.getElementById('toggle_password_btn');
  if (pwdInput && toggleBtn) {
    toggleBtn.addEventListener('click', function() {
      const type = pwdInput.getAttribute('type') === 'password' ? 'text' : 'password';
      pwdInput.setAttribute('type', type);
      this.className = type === 'password' ? 'bx bx-show' : 'bx bx-hide';
    });
  }

  // 5. Drag & Drop File Uploaders
  function bindUploader(boxId, inputId, defaultStateClass, previewBarId, imgId, filenameId, removeBtnId, liveAvatarBoxId) {
    const box = document.getElementById(boxId);
    const input = document.getElementById(inputId);
    const defaultState = box ? box.querySelector('.' + defaultStateClass) : null;
    const previewBar = document.getElementById(previewBarId);
    const img = document.getElementById(imgId);
    const filename = document.getElementById(filenameId);
    const removeBtn = document.getElementById(removeBtnId);
    const liveLogoBox = liveAvatarBoxId ? document.getElementById(liveAvatarBoxId) : null;

    if (!box || !input) return;

    box.addEventListener('click', function(e) {
      if (!e.target.closest('#' + removeBtnId)) {
        input.click();
      }
    });

    ['dragenter', 'dragover'].forEach(evt => {
      box.addEventListener(evt, e => {
        e.preventDefault(); e.stopPropagation();
        box.classList.add('dragover');
      });
    });

    ['dragleave', 'drop'].forEach(evt => {
      box.addEventListener(evt, e => {
        e.preventDefault(); e.stopPropagation();
        box.classList.remove('dragover');
      });
    });

    box.addEventListener('drop', e => {
      const files = e.dataTransfer.files;
      if (files && files.length > 0) {
        input.files = files;
        handleFile(files[0]);
      }
    });

    input.addEventListener('change', () => {
      if (input.files && input.files[0]) {
        handleFile(input.files[0]);
      }
    });

    function handleFile(file) {
      if (!file.type.startsWith('image/')) {
        alert('Please select a valid image file (PNG, JPG, WEBP, SVG).');
        return;
      }
      filename.textContent = file.name;
      const reader = new FileReader();
      reader.onload = e => {
        img.src = e.target.result;
        previewBar.style.display = 'flex';
        if (defaultState) defaultState.style.display = 'none';

        if (liveLogoBox) {
          liveLogoBox.innerHTML = `<img src="${e.target.result}" alt="Logo" />`;
        }
      };
      reader.readAsDataURL(file);
    }

    if (removeBtn) {
      removeBtn.addEventListener('click', e => {
        e.stopPropagation();
        input.value = '';
        img.src = '';
        previewBar.style.display = 'none';
        if (defaultState) defaultState.style.display = 'block';

        if (liveLogoBox) {
          const nameVal = nameInput ? nameInput.value.trim() : '';
          liveLogoBox.innerHTML = `<span id="live_company_initials">${nameVal ? nameVal.substring(0, 2).toUpperCase() : 'CO'}</span>`;
        }
      });
    }
  }

  bindUploader('logo_drop_box', 'company_logo_input', 'default-upload-state', 'company_logo_preview_bar', 'company_logo_img', 'company_logo_filename', 'btn_remove_logo', 'live_company_logo_box');
  bindUploader('avatar_drop_box', 'admin_profile_input', 'default-upload-state', 'admin_profile_preview_bar', 'admin_profile_img', 'admin_profile_filename', 'btn_remove_avatar');

  // 6. Subscription Tier Cards Selection
  const planCards = document.querySelectorAll('.subscription-card');
  const selectedPlanInput = document.getElementById('selectedPlanInput');

  planCards.forEach(card => {
    card.addEventListener('click', function() {
      planCards.forEach(c => c.classList.remove('selected'));
      this.classList.add('selected');
      const plan = this.getAttribute('data-plan');
      if (selectedPlanInput) selectedPlanInput.value = plan;
    });
  });

  // 7. Form Submit Loading Spinner State
  const form = document.getElementById('provisionCompanyForm');
  const submitBtn = document.getElementById('submitProvisionBtn');
  if (form && submitBtn) {
    form.addEventListener('submit', function() {
      submitBtn.disabled = true;
      submitBtn.style.opacity = '0.85';
      submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin" style="font-size: 20px;"></i> Provisioning Database &amp; Running Migrations...';
    });
  }
});
</script>
@endpush
@endsection
