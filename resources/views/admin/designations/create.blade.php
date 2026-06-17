@extends('admin.layout.app')

@section('title', isset($designation) ? 'Edit Designation' : 'Add Designation')

@section('content')

<div class="designation-form-page {{ isset($designation) ? 'designation-edit-mode' : 'designation-add-mode' }}">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <i class="fas {{ isset($designation) ? 'fa-user-edit' : 'fa-user-plus' }}"></i>
        Dashboard / Designations / {{ isset($designation) ? 'Edit' : 'Add' }}
    </div>

    <!-- Header Card -->
    <div class="header-card">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas {{ isset($designation) ? 'fa-edit' : 'fa-plus-circle' }}"></i>
            </div>
            <div>
                <h1>{{ isset($designation) ? 'Edit Designation' : 'Add New Designation' }}</h1>
                <p>{{ isset($designation) ? 'Update designation details and hierarchy information' : 'Create a new designation for your organization' }}</p>
            </div>
        </div>
        <div class="btn-group">
            <a href="{{ route('designations.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="form-card">
        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>Success!</strong> {{ session('success') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Error Message -->
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Error!</strong> {{ session('error') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

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

        <form method="POST" class="form-content"
              action="{{ isset($designation) ? route('designations.update', $designation) : route('designations.store') }}">
            @csrf
            @if(isset($designation))
                @method('PUT')
            @endif

            <!-- Unique Code Field -->
            <div class="form-field">
                <div class="field-icon">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div class="field-content">
                    <label for="unique_code">
                        Unique Code
                        @if(!isset($designation))
                            <span class="text-muted small">(Auto-generated or custom)</span>
                        @endif
                    </label>

                    @if(isset($designation))
                        <!-- Edit Mode: Read-only code -->
                        <input type="text" class="form-control" value="{{ $designation->unique_code }}" readonly>
                        <span class="field-hint">Unique identifier for this designation</span>
                    @else
                        <!-- Add Mode: Code generation options -->
                        <div class="code-mode-options" role="group" aria-label="Unique code generation mode">
                            @php
                                $selectedCodeMode = old('code_generation_mode', 'auto');
                                $codeValue = old('unique_code', $selectedCodeMode === 'custom' ? '' : ($nextCode ?? ''));
                            @endphp
                            <label class="code-mode-option" for="code_mode_auto">
                                <input
                                    type="radio"
                                    name="code_generation_mode"
                                    id="code_mode_auto"
                                    value="auto"
                                    {{ $selectedCodeMode === 'auto' ? 'checked' : '' }}
                                >
                                <span><i class="fas fa-magic"></i> Auto generate</span>
                            </label>
                            <label class="code-mode-option" for="code_mode_custom">
                                <input
                                    type="radio"
                                    name="code_generation_mode"
                                    id="code_mode_custom"
                                    value="custom"
                                    {{ $selectedCodeMode === 'custom' ? 'checked' : '' }}
                                >
                                <span><i class="fas fa-pen"></i> Custom</span>
                            </label>
                        </div>
                        <input
                            type="text"
                            name="unique_code"
                            id="unique_code"
                            class="form-control @error('unique_code') is-invalid @enderror"
                            value="{{ $codeValue }}"
                            data-auto-code="{{ $nextCode ?? '' }}"
                            {{ $selectedCodeMode === 'auto' ? 'readonly disabled' : '' }}
                            placeholder="{{ $selectedCodeMode === 'auto' ? 'Auto-generated code' : 'Enter custom code' }}"
                        >
                        @error('unique_code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <span class="field-hint">Each designation must have a unique code</span>
                    @endif
                </div>
            </div>

            <!-- Designation Name Field -->
            <div class="form-field">
                <div class="field-icon">
                    <i class="fas fa-user-tag"></i>
                </div>
                <div class="field-content">
                    <label for="name">
                        Designation Name <span class="text-danger">*</span>
                    </label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $designation->name ?? '') }}"
                        required
                        placeholder="e.g. Senior Software Engineer"
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <span class="field-hint">Official designation title</span>
                </div>
            </div>

            <!-- Level Field -->
            <div class="form-field">
                <div class="field-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="field-content">
                    <label for="level">
                        Designation Level <span class="text-danger">*</span>
                    </label>
                    <select
                        id="level"
                        name="level"
                        class="form-control @error('level') is-invalid @enderror"
                        required
                    >
                        <option value="">Select Level</option>
                        @for($i = 0; $i <= 6; $i++)
                            <option value="{{ $i }}"
                                {{ old('level', $designation->level ?? '') == $i ? 'selected' : '' }}>
                                Level {{ $i }}
                            </option>
                        @endfor
                    </select>
                    @error('level')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <span class="field-hint">Organizational hierarchy level (0-6)</span>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('designations.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas {{ isset($designation) ? 'fa-save' : 'fa-plus-circle' }}"></i>
                    {{ isset($designation) ? 'Update Designation' : 'Create Designation' }}
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* ===== PREMIUM FORM PAGE STYLES ===== */
    .designation-form-page {
        padding: 30px 35px;
        min-height: 100vh;
        background: linear-gradient(135deg, #f0f9f4 0%, #e6f3ec 50%, #f4fbf7 100%);
        color: #0a2e1f;
        position: relative;
    }

    .designation-form-page::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 0% 0%, rgba(16, 185, 129, 0.03) 0%, transparent 50%),
                    radial-gradient(circle at 100% 100%, rgba(52, 211, 153, 0.03) 0%, transparent 50%);
        pointer-events: none;
    }

    /* Breadcrumb */
    .breadcrumb {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        padding: 14px 24px;
        border-radius: 16px;
        border: 1px solid rgba(16, 185, 129, 0.15);
        margin-bottom: 28px;
        color: #0f744c;
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        position: relative;
        z-index: 1;
    }

    .breadcrumb i {
        margin-right: 8px;
        color: #34d399;
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
        box-shadow: 0 20px 40px -12px rgba(16, 185, 129, 0.12);
        border: 1px solid rgba(16, 185, 129, 0.12);
        margin-bottom: 32px;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
    }

    .header-card:hover {
        box-shadow: 0 24px 48px -16px rgba(16, 185, 129, 0.18);
        border-color: rgba(16, 185, 129, 0.2);
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
        box-shadow: 0 12px 24px -8px rgba(5, 150, 105, 0.3);
        transition: all 0.3s ease;
    }

    .designation-add-mode .header-icon {
        background: linear-gradient(145deg, #34d399, #059669);
        color: white;
    }

    .designation-edit-mode .header-icon {
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
        background: linear-gradient(135deg, #0a2e1f, #0f744c);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .designation-edit-mode .header-card h1 {
        background: linear-gradient(135deg, #0a2e1f, #d97706);
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
        background: #f0f9f4;
        color: #0f744c;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .btn-light:hover {
        background: #e6f3ec;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -8px rgba(16, 185, 129, 0.25);
        border-color: #34d399;
    }

    /* Form Card */
    .form-card {
        background: white;
        border-radius: 28px;
        border: 1px solid rgba(16, 185, 129, 0.1);
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
    }

    .form-card:hover {
        box-shadow: 0 12px 40px rgba(16, 185, 129, 0.08);
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

    .alert-success {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        color: #065f46;
        border-left: 4px solid #10b981;
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
        border: 1px solid rgba(16, 185, 129, 0.08);
        border-radius: 20px;
        display: flex;
        gap: 18px;
        align-items: flex-start;
        transition: all 0.3s ease;
        background: #fafefb;
    }

    .form-field:focus-within {
        border-color: rgba(16, 185, 129, 0.3);
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.06);
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

    .form-field:nth-child(1) .field-icon { background: linear-gradient(145deg, #d1fae5, #a7f3d0); color: #059669; }
    .form-field:nth-child(2) .field-icon { background: linear-gradient(145deg, #dbeafe, #bfdbfe); color: #2563eb; }
    .form-field:nth-child(3) .field-icon { background: linear-gradient(145deg, #fef3c7, #fde68a); color: #d97706; }

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
        color: #0a2e1f;
        margin-bottom: 8px;
    }

    .field-content label .text-muted {
        font-weight: 400;
        color: #8ba198;
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
        color: #0a2e1f;
        font-size: 1rem;
        font-weight: 500;
        width: 100%;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.1);
        outline: none;
    }

    .form-control[readonly],
    .form-control:disabled {
        background: #f0f9f4;
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

    /* Code Mode Options */
    .code-mode-options {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 12px;
    }

    .code-mode-option {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 500;
        font-size: 0.85rem;
        color: #5a6e63;
        margin: 0;
    }

    .code-mode-option:hover {
        border-color: #34d399;
        background: #f0f9f4;
    }

    .code-mode-option input[type="radio"] {
        accent-color: #059669;
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .code-mode-option input[type="radio"]:checked + span {
        color: #059669;
    }

    .code-mode-option:has(input[type="radio"]:checked) {
        border-color: #059669;
        background: #ecfdf5;
    }

    .code-mode-option span i {
        margin-right: 4px;
        font-size: 0.85rem;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 14px;
        padding-top: 24px;
        margin-top: 8px;
        border-top: 1px solid rgba(16, 185, 129, 0.1);
    }

    .btn-primary {
        background: linear-gradient(145deg, #34d399, #059669);
        color: white;
        box-shadow: 0 8px 20px -6px rgba(5, 150, 105, 0.35);
        border: none;
        padding: 12px 28px;
        min-height: 48px;
    }

    .designation-edit-mode .btn-primary {
        background: linear-gradient(145deg, #fbbf24, #f59e0b);
        box-shadow: 0 8px 20px -6px rgba(245, 158, 11, 0.35);
    }

    .designation-edit-mode .btn-primary:hover {
        box-shadow: 0 12px 28px -8px rgba(245, 158, 11, 0.45);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px -8px rgba(5, 150, 105, 0.45);
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
        .designation-form-page {
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
    }

    @media (max-width: 768px) {
        .designation-form-page {
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

        .code-mode-options {
            width: 100%;
        }

        .code-mode-option {
            flex: 1;
            justify-content: center;
        }
    }

    @media (max-width: 576px) {
        .designation-form-page {
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
    .designation-form-page {
        font-size: 16px;
        line-height: 1.55;
    }

    .designation-form-page .breadcrumb {
        font-size: 1rem;
    }

    .designation-form-page .header-card h1 {
        font-size: 36px;
        line-height: 1.2;
    }

    .designation-form-page .header-card p {
        font-size: 17px;
        line-height: 1.55;
    }

    .designation-form-page .btn {
        font-size: 1rem;
        padding: 13px 24px;
    }

    .designation-form-page .field-content label {
        font-size: 0.9rem;
    }

    .designation-form-page .form-control {
        font-size: 1rem;
    }

    .designation-form-page .field-hint {
        font-size: 0.75rem;
    }

    .designation-form-page .code-mode-option {
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .designation-form-page {
            font-size: 15px;
        }

        .designation-form-page .header-card h1 {
            font-size: 28px;
        }

        .designation-form-page .header-card p {
            font-size: 15px;
        }
    }
</style>

<style>
    /* Dark mode support */
    html[data-pms-theme="dark"] .designation-form-page {
        background: linear-gradient(135deg, #07130d, #102119);
    }

    html[data-pms-theme="dark"] .breadcrumb {
        background: rgba(16, 33, 25, 0.85);
        border-color: rgba(122, 240, 181, 0.15);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .breadcrumb i {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .header-card {
        background: rgba(16, 33, 25, 0.95);
        border-color: rgba(122, 240, 181, 0.12);
    }

    html[data-pms-theme="dark"] .header-card h1 {
        background: linear-gradient(135deg, #d9f1e4, #34d399);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    html[data-pms-theme="dark"] .designation-edit-mode .header-card h1 {
        background: linear-gradient(135deg, #d9f1e4, #fbbf24);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    html[data-pms-theme="dark"] .header-card p {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .btn-light {
        background: #183026;
        color: #d9f1e4;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .btn-light:hover {
        background: #1f3d30;
        border-color: #34d399;
    }

    html[data-pms-theme="dark"] .form-card {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .form-field {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .form-field:focus-within {
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .field-content label {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .form-control {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.15);
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.1);
    }

    html[data-pms-theme="dark"] .form-control[readonly],
    html[data-pms-theme="dark"] .form-control:disabled {
        background: #0d1b14;
        color: #6b7280;
    }

    html[data-pms-theme="dark"] .field-hint {
        color: #6b7280;
    }

    html[data-pms-theme="dark"] .code-mode-option {
        background: #0d1b14;
        border-color: rgba(122, 240, 181, 0.15);
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .code-mode-option:hover {
        border-color: #34d399;
        background: #102119;
    }

    html[data-pms-theme="dark"] .code-mode-option:has(input[type="radio"]:checked) {
        border-color: #34d399;
        background: #183026;
    }

    html[data-pms-theme="dark"] .code-mode-option input[type="radio"]:checked + span {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .btn-secondary {
        background: #183026;
        color: #d9f1e4;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .btn-secondary:hover {
        background: #1f3d30;
    }

    html[data-pms-theme="dark"] .form-actions {
        border-color: rgba(122, 240, 181, 0.06);
    }

    html[data-pms-theme="dark"] .alert-success {
        background: #0d1b14;
        color: #34d399;
        border-left-color: #34d399;
    }

    html[data-pms-theme="dark"] .alert-danger {
        background: #1a0d0d;
        color: #fca5a5;
        border-left-color: #ef4444;
    }
</style>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const codeInput = document.getElementById('unique_code');
    const modeInputs = document.querySelectorAll('input[name="code_generation_mode"]');

    if (!codeInput || !modeInputs.length) {
        return;
    }

    function syncCodeMode() {
        const selectedMode = document.querySelector('input[name="code_generation_mode"]:checked')?.value || 'auto';

        if (selectedMode === 'custom') {
            codeInput.disabled = false;
            codeInput.readOnly = false;
            if (codeInput.value === codeInput.dataset.autoCode) {
                codeInput.value = '';
            }
            codeInput.focus();
            codeInput.placeholder = 'Enter custom code';
            return;
        }

        codeInput.value = codeInput.dataset.autoCode || '';
        codeInput.readOnly = true;
        codeInput.disabled = true;
        codeInput.placeholder = 'Auto-generated code';
    }

    modeInputs.forEach(function (input) {
        input.addEventListener('change', syncCodeMode);
    });

    syncCodeMode();
});
</script>
@endpush

@endsection
