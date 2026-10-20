@extends('admin.layout.app')

@section('title', isset($parentDepartment) ? 'Edit Department' : 'Add Department')

@section('content')

<div class="department-form-page {{ isset($parentDepartment) ? 'department-edit-mode' : 'department-add-mode' }}">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <i class="fas fa-building"></i>
        Dashboard / Parent Departments / {{ isset($parentDepartment) ? 'Edit' : 'Add' }}
    </div>

    <!-- Header Card -->
    <div class="header-card">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas {{ isset($parentDepartment) ? 'fa-edit' : 'fa-plus-circle' }}"></i>
            </div>
            <div>
                <h1>{{ isset($parentDepartment) ? 'Edit Department' : 'Add New Department' }}</h1>
                <p>{{ isset($parentDepartment) ? 'Update parent department information' : 'Create a new parent department for your organization' }}</p>
            </div>
        </div>
        <div class="btn-group">
            <a href="{{ route('parent-departments.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="form-card">
        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Validation Errors!</strong>
                    <ul class="mb-0 mt-1 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form class="form-content" method="POST"
              action="{{ isset($parentDepartment)
                        ? route('parent-departments.update', $parentDepartment->id)
                        : route('parent-departments.store') }}">
            @csrf
            @if(isset($parentDepartment)) @method('PUT') @endif

            <!-- Department Code Field -->
            <div class="form-field">
                <div class="field-icon">
                    <i class="fas fa-code"></i>
                </div>
                <div class="field-content">
                    <label for="dpt_code">
                        Department Code
                        @if(!isset($parentDepartment))
                            <span class="text-muted small">(Auto-generated or custom)</span>
                        @endif
                    </label>

                    @if(isset($parentDepartment))
                        <input type="text"
                               class="form-control @error('dpt_code') is-invalid @enderror"
                               name="dpt_code"
                               id="dpt_code"
                               value="{{ old('dpt_code', $parentDepartment->dpt_code) }}"
                               required
                               placeholder="Enter department code">
                        @error('dpt_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    @else
                        @php
                            $selectedCodeMode = old('code_generation_mode', 'auto');
                            $codeValue = old('dpt_code', $selectedCodeMode === 'custom' ? '' : ($nextCode ?? 'DEP-0001'));
                        @endphp
                        <div class="code-mode-options" role="group" aria-label="Department code generation mode">
                            <label class="code-mode-option" for="department_code_mode_auto">
                                <input
                                    type="radio"
                                    name="code_generation_mode"
                                    id="department_code_mode_auto"
                                    value="auto"
                                    {{ $selectedCodeMode === 'auto' ? 'checked' : '' }}>
                                <span><i class="fas fa-magic"></i> Auto generate</span>
                            </label>
                            <label class="code-mode-option" for="department_code_mode_custom">
                                <input
                                    type="radio"
                                    name="code_generation_mode"
                                    id="department_code_mode_custom"
                                    value="custom"
                                    {{ $selectedCodeMode === 'custom' ? 'checked' : '' }}>
                                <span><i class="fas fa-pen"></i> Custom</span>
                            </label>
                        </div>
                        <input type="text"
                               class="form-control @error('dpt_code') is-invalid @enderror"
                               name="dpt_code"
                               id="dpt_code"
                               value="{{ $codeValue }}"
                               data-auto-code="{{ $nextCode ?? 'DEP-0001' }}"
                               {{ $selectedCodeMode === 'auto' ? 'readonly disabled' : '' }}
                               placeholder="{{ $selectedCodeMode === 'auto' ? 'Auto-generated code' : 'Enter custom code' }}">
                        @error('dpt_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    @endif
                    <span class="field-hint">Unique identifier for this department</span>
                </div>
            </div>

            <!-- Department Name Field -->
            <div class="form-field">
                <div class="field-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="field-content">
                    <label for="dpt_name">
                        Department Name <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="dpt_name"
                           id="dpt_name"
                           class="form-control @error('dpt_name') is-invalid @enderror"
                           value="{{ old('dpt_name', $parentDepartment->dpt_name ?? '') }}"
                           required
                           placeholder="e.g. Information Technology">
                    @error('dpt_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <span class="field-hint">Official parent department name</span>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('parent-departments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas {{ isset($parentDepartment) ? 'fa-save' : 'fa-plus-circle' }}"></i>
                    {{ isset($parentDepartment) ? 'Update Department' : 'Create Department' }}
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* ===== PREMIUM FORM PAGE STYLES ===== */
    .department-form-page {
        padding: 30px 35px;
        min-height: 100vh;
        background: linear-gradient(135deg, #F8FAFC 0%, #EEF2FF 50%, #F8FAFC 100%);
        color: #0F172A;
        position: relative;
    }

    .department-form-page::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 0% 0%, rgba(47, 107, 255, 0.03) 0%, transparent 50%),
                    radial-gradient(circle at 100% 100%, rgba(79, 131, 255, 0.03) 0%, transparent 50%);
        pointer-events: none;
    }

    /* Breadcrumb */
    .breadcrumb {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        padding: 14px 24px;
        border-radius: 16px;
        border: 1px solid rgba(47, 107, 255, 0.15);
        margin-bottom: 28px;
        color: #2F6BFF;
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        position: relative;
        z-index: 1;
    }

    .breadcrumb i {
        margin-right: 8px;
        color: #60A5FA;
    }

    /* Header Card */
    .header-card {
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(8px);
        border-radius: 28px;
        padding: 28px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        box-shadow: 0 20px 40px -12px rgba(47, 107, 255, 0.12);
        border: 1px solid rgba(47, 107, 255, 0.12);
        margin-bottom: 32px;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
    }

    .header-card:hover {
        box-shadow: 0 24px 48px -16px rgba(47, 107, 255, 0.18);
        border-color: rgba(47, 107, 255, 0.2);
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 22px;
    }

    .header-icon {
        width: 72px;
        height: 72px;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        box-shadow: 0 12px 24px -8px rgba(47, 107, 255, 0.3);
        transition: all 0.3s ease;
    }

    .department-add-mode .header-icon {
        background: linear-gradient(145deg, #4F83FF, #2F6BFF);
        color: white;
    }

    .department-edit-mode .header-icon {
        background: linear-gradient(145deg, #fbbf24, #f59e0b);
        color: white;
        box-shadow: 0 12px 24px -8px rgba(245, 158, 11, 0.3);
    }

    .header-card:hover .header-icon {
        transform: scale(1.02);
    }

    .header-card h1 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 6px;
        background: linear-gradient(135deg, #0F172A, #1E4FCC);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .department-edit-mode .header-card h1 {
        background: linear-gradient(135deg, #0F172A, #d97706);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .header-card p {
        color: #5a6e63;
        font-size: 15px;
        font-weight: 500;
    }

    /* Buttons */
    .btn-group {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
    }

    .btn {
        border: none;
        padding: 12px 24px;
        border-radius: 16px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        font-size: 0.9rem;
        min-height: 48px;
    }

    .btn i {
        font-size: 1rem;
    }

    .btn-light {
        background: #F8FAFC;
        color: #2F6BFF;
        border: 1px solid rgba(47, 107, 255, 0.2);
    }

    .btn-light:hover {
        background: #EEF2FF;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -8px rgba(47, 107, 255, 0.25);
        border-color: #60A5FA;
    }

    /* Form Card */
    .form-card {
        background: white;
        border-radius: 28px;
        border: 1px solid rgba(47, 107, 255, 0.1);
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
        max-width: 920px;
        margin: 0 auto;
    }

    .form-card:hover {
        box-shadow: 0 12px 40px rgba(47, 107, 255, 0.08);
    }

    /* Alerts */
    .alert {
        border-radius: 18px;
        border: none;
        padding: 16px 20px;
        margin: 0 28px 20px 28px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        animation: slideDown 0.4s ease;
    }

    .alert-danger {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }

    .alert i {
        font-size: 1.25rem;
        margin-top: 2px;
    }

    .alert ul {
        margin-bottom: 0;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Form Content */
    .form-content {
        padding: 28px;
    }

    .form-field {
        padding: 22px;
        margin-bottom: 20px;
        border: 1px solid rgba(47, 107, 255, 0.08);
        border-radius: 20px;
        display: flex;
        gap: 18px;
        align-items: flex-start;
        transition: all 0.3s ease;
        background: #fafefb;
    }

    .form-field:focus-within {
        border-color: rgba(47, 107, 255, 0.3);
        box-shadow: 0 8px 24px rgba(47, 107, 255, 0.06);
        transform: translateY(-2px);
    }

    .form-field .field-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .form-field:nth-of-type(1) .field-icon,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .form-field:nth-child(1) .field-icon {
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF) !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .form-field:nth-of-type(1) .field-icon i,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .form-field:nth-of-type(1) .field-icon .fa,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .form-field:nth-of-type(1) .field-icon [class*="fa-"],
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .form-field:nth-child(1) .field-icon i,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .form-field:nth-child(1) .field-icon .fa,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .form-field:nth-child(1) .field-icon [class*="fa-"] {
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        background: transparent !important;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .form-field:nth-of-type(2) .field-icon,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .form-field:nth-child(2) .field-icon {
        background: linear-gradient(145deg, #dbeafe, #bfdbfe) !important;
        color: #2563eb !important;
        -webkit-text-fill-color: #2563eb !important;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .form-field:nth-of-type(2) .field-icon i,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .form-field:nth-of-type(2) .field-icon .fa,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .form-field:nth-of-type(2) .field-icon [class*="fa-"],
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .form-field:nth-child(2) .field-icon i,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .form-field:nth-child(2) .field-icon .fa,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .form-field:nth-child(2) .field-icon [class*="fa-"] {
        color: #2563eb !important;
        -webkit-text-fill-color: #2563eb !important;
        background: transparent !important;
    }

    .form-field:hover .field-icon {
        transform: scale(1.05);
    }

    .field-content {
        flex: 1;
        min-width: 0;
    }

    .field-content label {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        font-size: 0.85rem;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 8px;
    }

    .field-content label .text-muted {
        font-weight: 400;
        color: #64748B;
        font-size: 0.75rem;
    }

    .field-content .text-danger {
        color: #dc2626;
    }

    .form-control {
        min-height: 52px;
        padding: 12px 16px;
        border: 1.5px solid #e2e8f0;
        border-radius: 13px;
        background: #ffffff;
        color: #0F172A;
        font-size: 1rem;
        font-weight: 500;
        width: 100%;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        border-color: #60A5FA;
        box-shadow: 0 0 0 4px rgba(79, 131, 255, 0.1);
        outline: none;
    }

    .form-control[readonly],
    .form-control:disabled {
        background: #F8FAFC;
        color: #5a6e63;
        cursor: not-allowed;
    }

    .form-control.is-invalid {
        border-color: #ef4444;
    }

    .form-control.is-invalid:focus {
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    }

    .invalid-feedback {
        color: #dc2626;
        font-size: 0.8rem;
        font-weight: 500;
        margin-top: 4px;
        display: block;
    }

    .field-hint {
        display: block;
        font-size: 0.7rem;
        color: #9ca3af;
        margin-top: 6px;
    }

    .code-mode-options {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .code-mode-option {
        margin: 0;
        cursor: pointer;
    }

    .code-mode-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .code-mode-option span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 40px;
        padding: 9px 15px;
        border: 1px solid rgba(47, 107, 255, 0.16);
        border-radius: 14px;
        background: #ffffff;
        color: #2F6BFF;
        font-size: 0.86rem;
        font-weight: 700;
        transition: all 0.2s ease;
    }

    .code-mode-option input[type="radio"]:checked + span {
        background: linear-gradient(145deg, #4F83FF, #2F6BFF);
        color: #ffffff;
        border-color: transparent;
        box-shadow: 0 8px 20px -12px rgba(47, 107, 255, 0.8);
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 14px;
        padding-top: 24px;
        margin-top: 8px;
        border-top: 1px solid rgba(47, 107, 255, 0.1);
    }

    .btn-primary {
        background: linear-gradient(145deg, #4F83FF, #2F6BFF);
        color: white;
        box-shadow: 0 8px 20px -6px rgba(47, 107, 255, 0.35);
        border: none;
        padding: 12px 28px;
        min-height: 48px;
    }

    .department-edit-mode .btn-primary {
        background: linear-gradient(145deg, #fbbf24, #f59e0b);
        box-shadow: 0 8px 20px -6px rgba(245, 158, 11, 0.35);
    }

    .department-edit-mode .btn-primary:hover {
        box-shadow: 0 12px 28px -8px rgba(245, 158, 11, 0.45);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px -8px rgba(47, 107, 255, 0.45);
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
        padding: 12px 24px;
        min-height: 48px;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    /* Responsive */
    @media (max-width: 992px) {
        .department-form-page {
            padding: 20px 25px;
        }

        .header-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .btn-group {
            width: 100%;
            justify-content: flex-start;
        }

        .form-card {
            max-width: 100%;
        }
    }

    @media (max-width: 768px) {
        .department-form-page {
            padding: 16px;
        }

        .header-card {
            padding: 20px;
        }

        .header-icon {
            width: 56px;
            height: 56px;
            font-size: 24px;
        }

        .header-card h1 {
            font-size: 24px;
        }

        .form-content {
            padding: 16px;
        }

        .form-field {
            padding: 16px;
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .field-icon {
            width: 44px;
            height: 44px;
            font-size: 1rem;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .form-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .alert {
            margin: 0 16px 16px 16px;
        }
    }

    @media (max-width: 576px) {
        .department-form-page {
            padding: 12px;
        }

        .header-card {
            padding: 16px;
            border-radius: 20px;
        }

        .header-left {
            gap: 14px;
        }

        .header-icon {
            width: 48px;
            height: 48px;
            font-size: 20px;
            border-radius: 18px;
        }

        .header-card h1 {
            font-size: 20px;
        }

        .header-card p {
            font-size: 13px;
        }

        .form-card {
            border-radius: 20px;
        }

        .form-field {
            padding: 14px;
        }

        .form-control {
            font-size: 0.9rem;
            min-height: 44px;
        }

        .btn {
            font-size: 0.85rem;
            padding: 10px 16px;
            min-height: 40px;
        }
    }
</style>

<style>
    /* Larger typography for better readability */
    .department-form-page {
        font-size: 16px;
        line-height: 1.55;
    }

    .department-form-page .breadcrumb {
        font-size: 1rem;
    }

    .department-form-page .header-card h1 {
        font-size: 36px;
        line-height: 1.2;
    }

    .department-form-page .header-card p {
        font-size: 17px;
        line-height: 1.55;
    }

    .department-form-page .btn {
        font-size: 1rem;
        padding: 13px 24px;
    }

    .department-form-page .field-content label {
        font-size: 0.9rem;
    }

    .department-form-page .form-control {
        font-size: 1rem;
    }

    .department-form-page .field-hint {
        font-size: 0.75rem;
    }

    @media (max-width: 768px) {
        .department-form-page {
            font-size: 15px;
        }

        .department-form-page .header-card h1 {
            font-size: 28px;
        }

        .department-form-page .header-card p {
            font-size: 15px;
        }
    }
</style>

<style>
    /* Dark mode support */
    html[data-pms-theme="dark"] .department-form-page,
    html[data-bs-theme="dark"] .department-form-page,
    html[data-theme="dark"] .department-form-page,
    html.dark .department-form-page,
    body[data-pms-theme="dark"] .department-form-page,
    body[data-bs-theme="dark"] .department-form-page,
    body[data-theme="dark"] .department-form-page,
    body.dark .department-form-page,
    body.dark-mode .department-form-page {
        background: linear-gradient(135deg, #070B1A, #0F1530);
    }

    html[data-pms-theme="dark"] .breadcrumb,
    html[data-bs-theme="dark"] .breadcrumb,
    html[data-theme="dark"] .breadcrumb,
    html.dark .breadcrumb,
    body[data-pms-theme="dark"] .breadcrumb,
    body[data-bs-theme="dark"] .breadcrumb,
    body[data-theme="dark"] .breadcrumb,
    body.dark .breadcrumb,
    body.dark-mode .breadcrumb {
        background: rgba(15, 21, 48, 0.85);
        border-color: rgba(79, 131, 255, 0.15);
        color: #CBD5E1;
    }

    html[data-pms-theme="dark"] .breadcrumb i,
    html[data-bs-theme="dark"] .breadcrumb i,
    html[data-theme="dark"] .breadcrumb i,
    html.dark .breadcrumb i,
    body[data-pms-theme="dark"] .breadcrumb i,
    body[data-bs-theme="dark"] .breadcrumb i,
    body[data-theme="dark"] .breadcrumb i,
    body.dark .breadcrumb i,
    body.dark-mode .breadcrumb i {
        color: #60A5FA;
    }

    html[data-pms-theme="dark"] .header-card,
    html[data-bs-theme="dark"] .header-card,
    html[data-theme="dark"] .header-card,
    html.dark .header-card,
    body[data-pms-theme="dark"] .header-card,
    body[data-bs-theme="dark"] .header-card,
    body[data-theme="dark"] .header-card,
    body.dark .header-card,
    body.dark-mode .header-card {
        background: rgba(15, 21, 48, 0.95);
        border-color: rgba(79, 131, 255, 0.12);
    }

    html[data-pms-theme="dark"] .header-card h1,
    html[data-bs-theme="dark"] .header-card h1,
    html[data-theme="dark"] .header-card h1,
    html.dark .header-card h1,
    body[data-pms-theme="dark"] .header-card h1,
    body[data-bs-theme="dark"] .header-card h1,
    body[data-theme="dark"] .header-card h1,
    body.dark .header-card h1,
    body.dark-mode .header-card h1 {
        background: linear-gradient(135deg, #ffffff, #60A5FA);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    html[data-pms-theme="dark"] .department-edit-mode .header-card h1,
    html[data-bs-theme="dark"] .department-edit-mode .header-card h1,
    html[data-theme="dark"] .department-edit-mode .header-card h1,
    html.dark .department-edit-mode .header-card h1,
    body[data-pms-theme="dark"] .department-edit-mode .header-card h1,
    body[data-bs-theme="dark"] .department-edit-mode .header-card h1,
    body[data-theme="dark"] .department-edit-mode .header-card h1,
    body.dark .department-edit-mode .header-card h1,
    body.dark-mode .department-edit-mode .header-card h1 {
        background: linear-gradient(135deg, #ffffff, #fbbf24);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    html[data-pms-theme="dark"] .header-card p,
    html[data-bs-theme="dark"] .header-card p,
    html[data-theme="dark"] .header-card p,
    html.dark .header-card p,
    body[data-pms-theme="dark"] .header-card p,
    body[data-bs-theme="dark"] .header-card p,
    body[data-theme="dark"] .header-card p,
    body.dark .header-card p,
    body.dark-mode .header-card p {
        color: #94A3B8;
    }

    html[data-pms-theme="dark"] .btn-light,
    html[data-bs-theme="dark"] .btn-light,
    html[data-theme="dark"] .btn-light,
    html.dark .btn-light,
    body[data-pms-theme="dark"] .btn-light,
    body[data-bs-theme="dark"] .btn-light,
    body[data-theme="dark"] .btn-light,
    body.dark .btn-light,
    body.dark-mode .btn-light {
        background: #141B3D;
        color: #EEF1FB;
        border-color: rgba(79, 131, 255, 0.15);
    }

    html[data-pms-theme="dark"] .btn-light:hover,
    html[data-bs-theme="dark"] .btn-light:hover,
    html[data-theme="dark"] .btn-light:hover,
    html.dark .btn-light:hover,
    body[data-pms-theme="dark"] .btn-light:hover,
    body[data-bs-theme="dark"] .btn-light:hover,
    body[data-theme="dark"] .btn-light:hover,
    body.dark .btn-light:hover,
    body.dark-mode .btn-light:hover {
        background: rgba(79, 131, 255, 0.15);
        border-color: #60A5FA;
    }

    html[data-pms-theme="dark"] .form-card,
    html[data-bs-theme="dark"] .form-card,
    html[data-theme="dark"] .form-card,
    html.dark .form-card,
    body[data-pms-theme="dark"] .form-card,
    body[data-bs-theme="dark"] .form-card,
    body[data-theme="dark"] .form-card,
    body.dark .form-card,
    body.dark-mode .form-card {
        background: #0F1530;
        border-color: rgba(79, 131, 255, 0.08);
    }

    html[data-pms-theme="dark"] .form-field,
    html[data-bs-theme="dark"] .form-field,
    html[data-theme="dark"] .form-field,
    html.dark .form-field,
    body[data-pms-theme="dark"] .form-field,
    body[data-bs-theme="dark"] .form-field,
    body[data-theme="dark"] .form-field,
    body.dark .form-field,
    body.dark-mode .form-field {
        background: #141B3D;
        border-color: rgba(79, 131, 255, 0.06);
    }

    html[data-pms-theme="dark"] .form-field:focus-within,
    html[data-bs-theme="dark"] .form-field:focus-within,
    html[data-theme="dark"] .form-field:focus-within,
    html.dark .form-field:focus-within,
    body[data-pms-theme="dark"] .form-field:focus-within,
    body[data-bs-theme="dark"] .form-field:focus-within,
    body[data-theme="dark"] .form-field:focus-within,
    body.dark .form-field:focus-within,
    body.dark-mode .form-field:focus-within {
        border-color: rgba(79, 131, 255, 0.15);
    }

    /* Dark Mode Field Icon Logos */
    html[data-pms-theme="dark"] .form-field:nth-of-type(1) .field-icon,
    html[data-pms-theme="dark"] .form-field:nth-child(1) .field-icon,
    html[data-bs-theme="dark"] .form-field:nth-of-type(1) .field-icon,
    html[data-bs-theme="dark"] .form-field:nth-child(1) .field-icon,
    html[data-theme="dark"] .form-field:nth-of-type(1) .field-icon,
    html[data-theme="dark"] .form-field:nth-child(1) .field-icon,
    html.dark .form-field:nth-of-type(1) .field-icon,
    html.dark .form-field:nth-child(1) .field-icon,
    body[data-pms-theme="dark"] .form-field:nth-of-type(1) .field-icon,
    body[data-pms-theme="dark"] .form-field:nth-child(1) .field-icon,
    body[data-bs-theme="dark"] .form-field:nth-of-type(1) .field-icon,
    body[data-bs-theme="dark"] .form-field:nth-child(1) .field-icon,
    body[data-theme="dark"] .form-field:nth-of-type(1) .field-icon,
    body[data-theme="dark"] .form-field:nth-child(1) .field-icon,
    body.dark .form-field:nth-of-type(1) .field-icon,
    body.dark .form-field:nth-child(1) .field-icon,
    body.dark-mode .form-field:nth-of-type(1) .field-icon,
    body.dark-mode .form-field:nth-child(1) .field-icon {
        background: linear-gradient(145deg, rgba(47, 107, 255, 0.3), rgba(47, 107, 255, 0.5)) !important;
        border: 1px solid rgba(79, 131, 255, 0.4) !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
        box-shadow: 0 4px 14px rgba(47, 107, 255, 0.25) !important;
    }

    html[data-pms-theme="dark"] .form-field:nth-of-type(1) .field-icon i,
    html[data-pms-theme="dark"] .form-field:nth-child(1) .field-icon i,
    html[data-bs-theme="dark"] .form-field:nth-of-type(1) .field-icon i,
    html[data-bs-theme="dark"] .form-field:nth-child(1) .field-icon i,
    html[data-theme="dark"] .form-field:nth-of-type(1) .field-icon i,
    html[data-theme="dark"] .form-field:nth-child(1) .field-icon i,
    html.dark .form-field:nth-of-type(1) .field-icon i,
    html.dark .form-field:nth-child(1) .field-icon i,
    body[data-pms-theme="dark"] .form-field:nth-of-type(1) .field-icon i,
    body[data-pms-theme="dark"] .form-field:nth-child(1) .field-icon i,
    body[data-bs-theme="dark"] .form-field:nth-of-type(1) .field-icon i,
    body[data-bs-theme="dark"] .form-field:nth-child(1) .field-icon i,
    body[data-theme="dark"] .form-field:nth-of-type(1) .field-icon i,
    body[data-theme="dark"] .form-field:nth-child(1) .field-icon i,
    body.dark .form-field:nth-of-type(1) .field-icon i,
    body.dark .form-field:nth-child(1) .field-icon i,
    body.dark-mode .form-field:nth-of-type(1) .field-icon i,
    body.dark-mode .form-field:nth-child(1) .field-icon i,
    html[data-pms-theme="dark"] .form-field:nth-of-type(1) .field-icon [class*="fa-"],
    html[data-pms-theme="dark"] .form-field:nth-child(1) .field-icon [class*="fa-"],
    html[data-bs-theme="dark"] .form-field:nth-of-type(1) .field-icon [class*="fa-"],
    html[data-bs-theme="dark"] .form-field:nth-child(1) .field-icon [class*="fa-"],
    html[data-theme="dark"] .form-field:nth-of-type(1) .field-icon [class*="fa-"],
    html[data-theme="dark"] .form-field:nth-child(1) .field-icon [class*="fa-"],
    html.dark .form-field:nth-of-type(1) .field-icon [class*="fa-"],
    html.dark .form-field:nth-child(1) .field-icon [class*="fa-"],
    body[data-pms-theme="dark"] .form-field:nth-of-type(1) .field-icon [class*="fa-"],
    body[data-pms-theme="dark"] .form-field:nth-child(1) .field-icon [class*="fa-"],
    body[data-bs-theme="dark"] .form-field:nth-of-type(1) .field-icon [class*="fa-"],
    body[data-bs-theme="dark"] .form-field:nth-child(1) .field-icon [class*="fa-"],
    body[data-theme="dark"] .form-field:nth-of-type(1) .field-icon [class*="fa-"],
    body[data-theme="dark"] .form-field:nth-child(1) .field-icon [class*="fa-"],
    body.dark .form-field:nth-of-type(1) .field-icon [class*="fa-"],
    body.dark .form-field:nth-child(1) .field-icon [class*="fa-"],
    body.dark-mode .form-field:nth-of-type(1) .field-icon [class*="fa-"],
    body.dark-mode .form-field:nth-child(1) .field-icon [class*="fa-"] {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
        background: transparent !important;
    }

    html[data-pms-theme="dark"] .form-field:nth-of-type(2) .field-icon,
    html[data-pms-theme="dark"] .form-field:nth-child(2) .field-icon,
    html[data-bs-theme="dark"] .form-field:nth-of-type(2) .field-icon,
    html[data-bs-theme="dark"] .form-field:nth-child(2) .field-icon,
    html[data-theme="dark"] .form-field:nth-of-type(2) .field-icon,
    html[data-theme="dark"] .form-field:nth-child(2) .field-icon,
    html.dark .form-field:nth-of-type(2) .field-icon,
    html.dark .form-field:nth-child(2) .field-icon,
    body[data-pms-theme="dark"] .form-field:nth-of-type(2) .field-icon,
    body[data-pms-theme="dark"] .form-field:nth-child(2) .field-icon,
    body[data-bs-theme="dark"] .form-field:nth-of-type(2) .field-icon,
    body[data-bs-theme="dark"] .form-field:nth-child(2) .field-icon,
    body[data-theme="dark"] .form-field:nth-of-type(2) .field-icon,
    body[data-theme="dark"] .form-field:nth-child(2) .field-icon,
    body.dark .form-field:nth-of-type(2) .field-icon,
    body.dark .form-field:nth-child(2) .field-icon,
    body.dark-mode .form-field:nth-of-type(2) .field-icon,
    body.dark-mode .form-field:nth-child(2) .field-icon {
        background: linear-gradient(145deg, rgba(56, 189, 248, 0.3), rgba(2, 132, 199, 0.5)) !important;
        border: 1px solid rgba(56, 189, 248, 0.4) !important;
        color: #38bdf8 !important;
        -webkit-text-fill-color: #38bdf8 !important;
        box-shadow: 0 4px 14px rgba(56, 189, 248, 0.25) !important;
    }

    html[data-pms-theme="dark"] .form-field:nth-of-type(2) .field-icon i,
    html[data-pms-theme="dark"] .form-field:nth-child(2) .field-icon i,
    html[data-bs-theme="dark"] .form-field:nth-of-type(2) .field-icon i,
    html[data-bs-theme="dark"] .form-field:nth-child(2) .field-icon i,
    html[data-theme="dark"] .form-field:nth-of-type(2) .field-icon i,
    html[data-theme="dark"] .form-field:nth-child(2) .field-icon i,
    html.dark .form-field:nth-of-type(2) .field-icon i,
    html.dark .form-field:nth-child(2) .field-icon i,
    body[data-pms-theme="dark"] .form-field:nth-of-type(2) .field-icon i,
    body[data-pms-theme="dark"] .form-field:nth-child(2) .field-icon i,
    body[data-bs-theme="dark"] .form-field:nth-of-type(2) .field-icon i,
    body[data-bs-theme="dark"] .form-field:nth-child(2) .field-icon i,
    body[data-theme="dark"] .form-field:nth-of-type(2) .field-icon i,
    body[data-theme="dark"] .form-field:nth-child(2) .field-icon i,
    body.dark .form-field:nth-of-type(2) .field-icon i,
    body.dark .form-field:nth-child(2) .field-icon i,
    body.dark-mode .form-field:nth-of-type(2) .field-icon i,
    body.dark-mode .form-field:nth-child(2) .field-icon i,
    html[data-pms-theme="dark"] .form-field:nth-of-type(2) .field-icon [class*="fa-"],
    html[data-pms-theme="dark"] .form-field:nth-child(2) .field-icon [class*="fa-"],
    html[data-bs-theme="dark"] .form-field:nth-of-type(2) .field-icon [class*="fa-"],
    html[data-bs-theme="dark"] .form-field:nth-child(2) .field-icon [class*="fa-"],
    html[data-theme="dark"] .form-field:nth-of-type(2) .field-icon [class*="fa-"],
    html[data-theme="dark"] .form-field:nth-child(2) .field-icon [class*="fa-"],
    html.dark .form-field:nth-of-type(2) .field-icon [class*="fa-"],
    html.dark .form-field:nth-child(2) .field-icon [class*="fa-"],
    body[data-pms-theme="dark"] .form-field:nth-of-type(2) .field-icon [class*="fa-"],
    body[data-pms-theme="dark"] .form-field:nth-child(2) .field-icon [class*="fa-"],
    body[data-bs-theme="dark"] .form-field:nth-of-type(2) .field-icon [class*="fa-"],
    body[data-bs-theme="dark"] .form-field:nth-child(2) .field-icon [class*="fa-"],
    body[data-theme="dark"] .form-field:nth-of-type(2) .field-icon [class*="fa-"],
    body[data-theme="dark"] .form-field:nth-child(2) .field-icon [class*="fa-"],
    body.dark .form-field:nth-of-type(2) .field-icon [class*="fa-"],
    body.dark .form-field:nth-child(2) .field-icon [class*="fa-"],
    body.dark-mode .form-field:nth-of-type(2) .field-icon [class*="fa-"],
    body.dark-mode .form-field:nth-child(2) .field-icon [class*="fa-"] {
        color: #38bdf8 !important;
        -webkit-text-fill-color: #38bdf8 !important;
        background: transparent !important;
    }

    html[data-pms-theme="dark"] .field-content label,
    html[data-bs-theme="dark"] .field-content label,
    html[data-theme="dark"] .field-content label,
    html.dark .field-content label,
    body[data-pms-theme="dark"] .field-content label,
    body[data-bs-theme="dark"] .field-content label,
    body[data-theme="dark"] .field-content label,
    body.dark .field-content label,
    body.dark-mode .field-content label {
        color: #CBD5E1;
    }

    html[data-pms-theme="dark"] .form-control,
    html[data-bs-theme="dark"] .form-control,
    html[data-theme="dark"] .form-control,
    html.dark .form-control,
    body[data-pms-theme="dark"] .form-control,
    body[data-bs-theme="dark"] .form-control,
    body[data-theme="dark"] .form-control,
    body.dark .form-control,
    body.dark-mode .form-control {
        background: #0F1530;
        border-color: rgba(79, 131, 255, 0.15);
        color: #FFFFFF;
    }

    html[data-pms-theme="dark"] .form-control:focus,
    html[data-bs-theme="dark"] .form-control:focus,
    html[data-theme="dark"] .form-control:focus,
    html.dark .form-control:focus,
    body[data-pms-theme="dark"] .form-control:focus,
    body[data-bs-theme="dark"] .form-control:focus,
    body[data-theme="dark"] .form-control:focus,
    body.dark .form-control:focus,
    body.dark-mode .form-control:focus {
        border-color: #60A5FA;
        box-shadow: 0 0 0 4px rgba(79, 131, 255, 0.1);
    }

    html[data-pms-theme="dark"] .form-control[readonly],
    html[data-pms-theme="dark"] .form-control:disabled,
    html[data-bs-theme="dark"] .form-control[readonly],
    html[data-bs-theme="dark"] .form-control:disabled,
    html[data-theme="dark"] .form-control[readonly],
    html[data-theme="dark"] .form-control:disabled,
    html.dark .form-control[readonly],
    html.dark .form-control:disabled,
    body[data-pms-theme="dark"] .form-control[readonly],
    body[data-pms-theme="dark"] .form-control:disabled,
    body[data-bs-theme="dark"] .form-control[readonly],
    body[data-bs-theme="dark"] .form-control:disabled,
    body[data-theme="dark"] .form-control[readonly],
    body[data-theme="dark"] .form-control:disabled,
    body.dark .form-control[readonly],
    body.dark .form-control:disabled,
    body.dark-mode .form-control[readonly],
    body.dark-mode .form-control:disabled {
        background: #0F1530;
        color: #94A3B8;
    }

    html[data-pms-theme="dark"] .field-hint,
    html[data-bs-theme="dark"] .field-hint,
    html[data-theme="dark"] .field-hint,
    html.dark .field-hint,
    body[data-pms-theme="dark"] .field-hint,
    body[data-bs-theme="dark"] .field-hint,
    body[data-theme="dark"] .field-hint,
    body.dark .field-hint,
    body.dark-mode .field-hint {
        color: #94A3B8;
    }

    html[data-pms-theme="dark"] .code-mode-option span,
    html[data-bs-theme="dark"] .code-mode-option span,
    html[data-theme="dark"] .code-mode-option span,
    html.dark .code-mode-option span,
    body[data-pms-theme="dark"] .code-mode-option span,
    body[data-bs-theme="dark"] .code-mode-option span,
    body[data-theme="dark"] .code-mode-option span,
    body.dark .code-mode-option span,
    body.dark-mode .code-mode-option span {
        background: #0F1530;
        color: #60A5FA;
        border-color: rgba(79, 131, 255, 0.15);
    }

    html[data-pms-theme="dark"] .code-mode-option input[type="radio"]:checked + span,
    html[data-bs-theme="dark"] .code-mode-option input[type="radio"]:checked + span,
    html[data-theme="dark"] .code-mode-option input[type="radio"]:checked + span,
    html.dark .code-mode-option input[type="radio"]:checked + span,
    body[data-pms-theme="dark"] .code-mode-option input[type="radio"]:checked + span,
    body[data-bs-theme="dark"] .code-mode-option input[type="radio"]:checked + span,
    body[data-theme="dark"] .code-mode-option input[type="radio"]:checked + span,
    body.dark .code-mode-option input[type="radio"]:checked + span,
    body.dark-mode .code-mode-option input[type="radio"]:checked + span {
        background: linear-gradient(145deg, #4F83FF, #2F6BFF);
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .btn-secondary,
    html[data-bs-theme="dark"] .btn-secondary,
    html[data-theme="dark"] .btn-secondary,
    html.dark .btn-secondary,
    body[data-pms-theme="dark"] .btn-secondary,
    body[data-bs-theme="dark"] .btn-secondary,
    body[data-theme="dark"] .btn-secondary,
    body.dark .btn-secondary,
    body.dark-mode .btn-secondary {
        background: #141B3D;
        color: #60A5FA;
        border-color: rgba(79, 131, 255, 0.15);
    }

    html[data-pms-theme="dark"] .btn-secondary:hover,
    html[data-bs-theme="dark"] .btn-secondary:hover,
    html[data-theme="dark"] .btn-secondary:hover,
    html.dark .btn-secondary:hover,
    body[data-pms-theme="dark"] .btn-secondary:hover,
    body[data-bs-theme="dark"] .btn-secondary:hover,
    body[data-theme="dark"] .btn-secondary:hover,
    body.dark .btn-secondary:hover,
    body.dark-mode .btn-secondary:hover {
        background: rgba(79, 131, 255, 0.25);
    }

    html[data-pms-theme="dark"] .form-actions,
    html[data-bs-theme="dark"] .form-actions,
    html[data-theme="dark"] .form-actions,
    html.dark .form-actions,
    body[data-pms-theme="dark"] .form-actions,
    body[data-bs-theme="dark"] .form-actions,
    body[data-theme="dark"] .form-actions,
    body.dark .form-actions,
    body.dark-mode .form-actions {
        border-color: rgba(79, 131, 255, 0.06);
    }

    html[data-pms-theme="dark"] .alert-danger,
    html[data-bs-theme="dark"] .alert-danger,
    html[data-theme="dark"] .alert-danger,
    html.dark .alert-danger,
    body[data-pms-theme="dark"] .alert-danger,
    body[data-bs-theme="dark"] .alert-danger,
    body[data-theme="dark"] .alert-danger,
    body.dark .alert-danger,
    body.dark-mode .alert-danger {
        background: #1a0d0d;
        color: #fca5a5;
        border-left-color: #ef4444;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('dpt_code');
    const modeInputs = document.querySelectorAll('input[name="code_generation_mode"]');

    if (!codeInput || !modeInputs.length) {
        return;
    }

    function syncCodeMode() {
        const selectedMode = document.querySelector('input[name="code_generation_mode"]:checked')?.value || 'auto';

        if (selectedMode === 'custom') {
            codeInput.removeAttribute('readonly');
            codeInput.removeAttribute('disabled');
            if (codeInput.value === codeInput.dataset.autoCode) {
                codeInput.value = '';
            }
            codeInput.placeholder = 'Enter custom code';
            codeInput.focus();
            return;
        }

        codeInput.value = codeInput.dataset.autoCode || '';
        codeInput.setAttribute('readonly', 'readonly');
        codeInput.setAttribute('disabled', 'disabled');
        codeInput.placeholder = 'Auto-generated code';
    }

    modeInputs.forEach(function(input) {
        input.addEventListener('change', syncCodeMode);
    });

    syncCodeMode();
});
</script>

@endsection
