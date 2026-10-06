@extends('admin.layout.app')

@section('title', isset($designation) ? 'Edit Designation' : 'Add Designation')

@section('content')

<div class="designation-form-page {{ isset($designation) ? 'designation-edit-mode' : 'designation-add-mode' }}">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <i class="fas {{ isset($designation) ? 'fa-user-edit' : 'fa-user-plus' }}"></i>
        Admin / Settings / Designations / {{ isset($designation) ? 'Edit' : 'Add' }}
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
            <div class="form-field field-code">
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
            <div class="form-field field-name">
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

            <!-- Parent Designation Field -->
            <div class="form-field field-parent">
                <div class="field-icon">
                    <i class="fas fa-sitemap"></i>
                </div>
                <div class="field-content">
                    <label for="parent_id">
                        Parent Designation
                        <span class="text-muted small">(Optional - select reporting hierarchy)</span>
                    </label>
                    <select
                        id="parent_id"
                        name="parent_id"
                        class="form-control @error('parent_id') is-invalid @enderror"
                    >
                        <option value="" data-level="-1">-- None (Top-Level Designation) --</option>
                        @foreach($designations as $parentDesig)
                            @php
                                $pLevel = (int) ($parentDesig->level ?? 0);
                                $isL6 = $pLevel >= 6;
                            @endphp
                            <option value="{{ $parentDesig->id }}"
                                data-level="{{ $pLevel }}"
                                {{ (string) old('parent_id', $designation->parent_id ?? '') === (string) $parentDesig->id ? 'selected' : '' }}
                                {{ $isL6 ? 'disabled class=text-muted' : '' }}>
                                {{ $parentDesig->name }}
                                @if(!empty($parentDesig->unique_code))
                                    ({{ $parentDesig->unique_code }})
                                @endif
                                - Level {{ $pLevel }}
                                @if($isL6)
                                    [Max Level 6 - cannot have subordinates]
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <span class="field-hint" id="parent-field-hint">Select parent designation (Level 0-5 only; Level 6 is the max organizational limit)</span>
                </div>
            </div>

            <!-- Level Field -->
            <div class="form-field field-level">
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
                        <option value="">Select Level (0 - 6)</option>
                        @for($i = 0; $i <= 6; $i++)
                            <option value="{{ $i }}"
                                data-level="{{ $i }}"
                                {{ (string) old('level', $designation->level ?? '') === (string) $i ? 'selected' : '' }}>
                                Level {{ $i }}
                            </option>
                        @endfor
                    </select>
                    @error('level')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <span class="field-hint" id="level-field-hint">Organizational hierarchy level (strictly Level 0 to Level 6)</span>
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
        background: linear-gradient(135deg, #F8FAFC 0%, #EEF2FF 50%, #F8FAFC 100%);
        color: #0F172A;
        position: relative;
    }

    .designation-form-page::before {
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

    .designation-add-mode .header-icon {
        background: linear-gradient(145deg, #4F83FF, #2F6BFF);
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
        background: linear-gradient(135deg, #0F172A, #1E4FCC);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .designation-edit-mode .header-card h1 {
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

    .alert-success {
        background: linear-gradient(135deg, #EEF2FF, #E0E7FF);
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
        font-size: 1.25rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .form-field .field-icon i {
        display: inline-block;
        line-height: 1;
    }

    .form-field.field-code .field-icon,
    .form-field:nth-child(1) .field-icon { background: linear-gradient(145deg, #EEF2FF, #E0E7FF); color: #2F6BFF; }
    .form-field.field-name .field-icon,
    .form-field:nth-child(2) .field-icon { background: linear-gradient(145deg, #dbeafe, #bfdbfe); color: #2563eb; }
    .form-field.field-parent .field-icon,
    .form-field:nth-child(3) .field-icon { background: linear-gradient(145deg, #fef3c7, #fde68a); color: #d97706; }
    .form-field.field-level .field-icon,
    .form-field:nth-child(4) .field-icon { background: linear-gradient(145deg, #ede9fe, #ddd6fe); color: #7c3aed; }

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
        border-color: #60A5FA;
        background: #F8FAFC;
    }

    .code-mode-option input[type="radio"] {
        accent-color: #2F6BFF;
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .code-mode-option input[type="radio"]:checked + span {
        color: #2F6BFF;
    }

    .code-mode-option:has(input[type="radio"]:checked) {
        border-color: #2F6BFF;
        background: #EEF2FF;
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

    .designation-edit-mode .btn-primary {
        background: linear-gradient(145deg, #fbbf24, #f59e0b);
        box-shadow: 0 8px 20px -6px rgba(245, 158, 11, 0.35);
    }

    .designation-edit-mode .btn-primary:hover {
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
    /* ==========================================================================
       CREATE / EDIT DESIGNATION FORM - COMPREHENSIVE DARK MODE SYSTEM
       ========================================================================== */
    html[data-pms-theme="dark"] .designation-form-page,
    html[data-bs-theme="dark"] .designation-form-page,
    html[data-theme="dark"] .designation-form-page,
    html.dark .designation-form-page,
    body[data-pms-theme="dark"] .designation-form-page,
    body[data-bs-theme="dark"] .designation-form-page,
    body[data-theme="dark"] .designation-form-page,
    body.dark .designation-form-page,
    [data-pms-theme="dark"] .designation-form-page,
    [data-bs-theme="dark"] .designation-form-page,
    [data-theme="dark"] .designation-form-page,
    .dark .designation-form-page {
        background: #070B1A !important;
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .designation-form-page .breadcrumb,
    html[data-bs-theme="dark"] .designation-form-page .breadcrumb,
    html[data-theme="dark"] .designation-form-page .breadcrumb,
    html.dark .designation-form-page .breadcrumb,
    body[data-pms-theme="dark"] .designation-form-page .breadcrumb,
    [data-pms-theme="dark"] .designation-form-page .breadcrumb,
    .dark .designation-form-page .breadcrumb {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.2) !important;
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .designation-form-page .header-card,
    html[data-bs-theme="dark"] .designation-form-page .header-card,
    html[data-theme="dark"] .designation-form-page .header-card,
    html.dark .designation-form-page .header-card,
    body[data-pms-theme="dark"] .designation-form-page .header-card,
    [data-pms-theme="dark"] .designation-form-page .header-card,
    .dark .designation-form-page .header-card {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.2) !important;
    }

    html[data-pms-theme="dark"] .designation-form-page .header-card h1,
    html[data-bs-theme="dark"] .designation-form-page .header-card h1,
    html[data-theme="dark"] .designation-form-page .header-card h1,
    html.dark .designation-form-page .header-card h1,
    body[data-pms-theme="dark"] .designation-form-page .header-card h1,
    [data-pms-theme="dark"] .designation-form-page .header-card h1,
    .dark .designation-form-page .header-card h1 {
        background: linear-gradient(135deg, #ffffff 0%, #60A5FA 100%) !important;
        background-clip: text !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .designation-form-page.designation-edit-mode .header-card h1,
    html[data-bs-theme="dark"] .designation-form-page.designation-edit-mode .header-card h1,
    html[data-theme="dark"] .designation-form-page.designation-edit-mode .header-card h1,
    html.dark .designation-form-page.designation-edit-mode .header-card h1 {
        background: linear-gradient(135deg, #ffffff 0%, #fbbf24 100%) !important;
        background-clip: text !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
    }

    html[data-pms-theme="dark"] .designation-form-page .header-card p,
    html[data-bs-theme="dark"] .designation-form-page .header-card p,
    html[data-theme="dark"] .designation-form-page .header-card p,
    html.dark .designation-form-page .header-card p {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    html[data-pms-theme="dark"] .designation-form-page .btn-light,
    html[data-bs-theme="dark"] .designation-form-page .btn-light,
    html[data-theme="dark"] .designation-form-page .btn-light,
    html.dark .designation-form-page .btn-light {
        background: rgba(47, 107, 255, 0.12) !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .designation-form-page .btn-light:hover,
    html[data-bs-theme="dark"] .designation-form-page .btn-light:hover,
    html[data-theme="dark"] .designation-form-page .btn-light:hover,
    html.dark .designation-form-page .btn-light:hover {
        background: rgba(79, 131, 255, 0.25) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    /* Form Card Container in Dark Mode */
    html[data-pms-theme="dark"] .form-card,
    html[data-bs-theme="dark"] .form-card,
    html[data-theme="dark"] .form-card,
    html.dark .form-card,
    body[data-pms-theme="dark"] .form-card,
    body[data-bs-theme="dark"] .form-card,
    body[data-theme="dark"] .form-card,
    body.dark .form-card,
    [data-pms-theme="dark"] .form-card,
    [data-bs-theme="dark"] .form-card,
    [data-theme="dark"] .form-card,
    .dark .form-card,
    html[data-pms-theme="dark"] .designation-form-page .form-card,
    html[data-bs-theme="dark"] .designation-form-page .form-card,
    html[data-theme="dark"] .designation-form-page .form-card,
    html.dark .designation-form-page .form-card,
    body[data-pms-theme="dark"] .designation-form-page .form-card,
    body[data-bs-theme="dark"] .designation-form-page .form-card,
    body[data-theme="dark"] .designation-form-page .form-card,
    body.dark .designation-form-page .form-card,
    [data-pms-theme="dark"] .designation-form-page .form-card,
    [data-bs-theme="dark"] .designation-form-page .form-card,
    [data-theme="dark"] .designation-form-page .form-card,
    .dark .designation-form-page .form-card {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.2) !important;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.4) !important;
        color: #EEF1FB !important;
    }

    /* Form Content Area in Dark Mode */
    html[data-pms-theme="dark"] .form-content,
    html[data-bs-theme="dark"] .form-content,
    html[data-theme="dark"] .form-content,
    html.dark .form-content,
    body[data-pms-theme="dark"] .form-content,
    [data-pms-theme="dark"] .form-content,
    .dark .form-content {
        background: transparent !important;
        color: #EEF1FB !important;
    }

    /* Form Fields in Dark Mode */
    html[data-pms-theme="dark"] .form-field,
    html[data-bs-theme="dark"] .form-field,
    html[data-theme="dark"] .form-field,
    html.dark .form-field,
    body[data-pms-theme="dark"] .form-field,
    body[data-bs-theme="dark"] .form-field,
    body[data-theme="dark"] .form-field,
    body.dark .form-field,
    [data-pms-theme="dark"] .form-field,
    [data-bs-theme="dark"] .form-field,
    [data-theme="dark"] .form-field,
    .dark .form-field,
    html[data-pms-theme="dark"] .designation-form-page .form-field,
    html[data-bs-theme="dark"] .designation-form-page .form-field,
    html[data-theme="dark"] .designation-form-page .form-field,
    html.dark .designation-form-page .form-field,
    body[data-pms-theme="dark"] .designation-form-page .form-field,
    body[data-bs-theme="dark"] .designation-form-page .form-field,
    body[data-theme="dark"] .designation-form-page .form-field,
    body.dark .designation-form-page .form-field,
    [data-pms-theme="dark"] .designation-form-page .form-field,
    [data-bs-theme="dark"] .designation-form-page .form-field,
    [data-theme="dark"] .designation-form-page .form-field,
    .dark .designation-form-page .form-field {
        background: #141B3D !important;
        border: 1px solid rgba(79, 131, 255, 0.15) !important;
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .form-field:focus-within,
    html[data-bs-theme="dark"] .form-field:focus-within,
    [data-pms-theme="dark"] .form-field:focus-within {
        border-color: rgba(79, 131, 255, 0.35) !important;
        box-shadow: 0 8px 24px rgba(79, 131, 255, 0.08) !important;
    }

    /* Field Labels in Dark Mode */
    html[data-pms-theme="dark"] .form-card label,
    html[data-bs-theme="dark"] .form-card label,
    html[data-theme="dark"] .form-card label,
    html.dark .form-card label,
    body[data-pms-theme="dark"] .form-card label,
    body[data-bs-theme="dark"] .form-card label,
    body[data-theme="dark"] .form-card label,
    body.dark .form-card label,
    [data-pms-theme="dark"] .form-card label,
    [data-bs-theme="dark"] .form-card label,
    [data-theme="dark"] .form-card label,
    .dark .form-card label,
    html[data-pms-theme="dark"] .field-content label,
    html[data-bs-theme="dark"] .field-content label,
    html[data-theme="dark"] .field-content label,
    html.dark .field-content label,
    body[data-pms-theme="dark"] .field-content label,
    body[data-bs-theme="dark"] .field-content label,
    body[data-theme="dark"] .field-content label,
    body.dark .field-content label,
    [data-pms-theme="dark"] .field-content label,
    [data-bs-theme="dark"] .field-content label,
    [data-theme="dark"] .field-content label,
    .dark .field-content label {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    /* Hints and Muted Texts in Dark Mode */
    html[data-pms-theme="dark"] .field-hint,
    html[data-bs-theme="dark"] .field-hint,
    html[data-theme="dark"] .field-hint,
    html.dark .field-hint,
    body[data-pms-theme="dark"] .field-hint,
    body[data-bs-theme="dark"] .field-hint,
    body[data-theme="dark"] .field-hint,
    body.dark .field-hint,
    [data-pms-theme="dark"] .field-hint,
    [data-bs-theme="dark"] .field-hint,
    [data-theme="dark"] .field-hint,
    .dark .field-hint,
    html[data-pms-theme="dark"] .field-content .text-muted,
    html[data-bs-theme="dark"] .field-content .text-muted,
    html[data-theme="dark"] .field-content .text-muted,
    html.dark .field-content .text-muted,
    body[data-pms-theme="dark"] .field-content .text-muted,
    body[data-bs-theme="dark"] .field-content .text-muted,
    body[data-theme="dark"] .field-content .text-muted,
    body.dark .field-content .text-muted,
    [data-pms-theme="dark"] .field-content .text-muted,
    [data-bs-theme="dark"] .field-content .text-muted,
    [data-theme="dark"] .field-content .text-muted,
    .dark .field-content .text-muted {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    /* Radio Code Generation Option Pills in Dark Mode */
    html[data-pms-theme="dark"] .code-mode-option,
    html[data-bs-theme="dark"] .code-mode-option,
    html[data-theme="dark"] .code-mode-option,
    html.dark .code-mode-option,
    body[data-pms-theme="dark"] .code-mode-option,
    body[data-bs-theme="dark"] .code-mode-option,
    body[data-theme="dark"] .code-mode-option,
    body.dark .code-mode-option,
    [data-pms-theme="dark"] .code-mode-option,
    [data-bs-theme="dark"] .code-mode-option,
    [data-theme="dark"] .code-mode-option,
    .dark .code-mode-option {
        background: #141B3D !important;
        border: 1px solid rgba(79, 131, 255, 0.3) !important;
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .code-mode-option span,
    html[data-bs-theme="dark"] .code-mode-option span,
    html[data-theme="dark"] .code-mode-option span,
    html.dark .code-mode-option span,
    body[data-pms-theme="dark"] .code-mode-option span,
    [data-pms-theme="dark"] .code-mode-option span,
    .dark .code-mode-option span {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .code-mode-option:hover,
    html[data-bs-theme="dark"] .code-mode-option:hover,
    html[data-pms-theme="dark"] .code-mode-option:has(input[type="radio"]:checked),
    html[data-bs-theme="dark"] .code-mode-option:has(input[type="radio"]:checked) {
        border-color: #2F6BFF !important;
        background: rgba(79, 131, 255, 0.22) !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .code-mode-option input[type="radio"]:checked + span,
    html[data-bs-theme="dark"] .code-mode-option input[type="radio"]:checked + span {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    /* Form Controls & Select Inputs in Dark Mode */
    html[data-pms-theme="dark"] .form-control,
    html[data-bs-theme="dark"] .form-control,
    html[data-theme="dark"] .form-control,
    html.dark .form-control,
    body[data-pms-theme="dark"] .form-control,
    body[data-bs-theme="dark"] .form-control,
    body[data-theme="dark"] .form-control,
    body.dark .form-control,
    [data-pms-theme="dark"] .form-control,
    [data-bs-theme="dark"] .form-control,
    [data-theme="dark"] .form-control,
    .dark .form-control,
    html[data-pms-theme="dark"] .form-select,
    html[data-bs-theme="dark"] .form-select,
    html[data-theme="dark"] .form-select,
    html.dark .form-select,
    body[data-pms-theme="dark"] .form-select,
    body[data-bs-theme="dark"] .form-select,
    body[data-theme="dark"] .form-select,
    body.dark .form-select,
    [data-pms-theme="dark"] .form-select,
    [data-bs-theme="dark"] .form-select,
    [data-theme="dark"] .form-select,
    .dark .form-select {
        background: #141B3D !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
    }

    html[data-pms-theme="dark"] select option,
    html[data-bs-theme="dark"] select option,
    html[data-theme="dark"] select option,
    html.dark select option,
    body[data-pms-theme="dark"] select option,
    [data-pms-theme="dark"] select option,
    .dark select option {
        background-color: #0F1530 !important;
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .form-control:focus,
    html[data-bs-theme="dark"] .form-control:focus,
    html[data-theme="dark"] .form-control:focus,
    html.dark .form-control:focus {
        border-color: #2F6BFF !important;
        box-shadow: 0 0 0 4px rgba(79, 131, 255, 0.15) !important;
    }

    html[data-pms-theme="dark"] .form-control[readonly],
    html[data-pms-theme="dark"] .form-control:disabled,
    html[data-bs-theme="dark"] .form-control[readonly],
    html[data-bs-theme="dark"] .form-control:disabled,
    body[data-pms-theme="dark"] .form-control[readonly],
    body[data-pms-theme="dark"] .form-control:disabled,
    [data-pms-theme="dark"] .form-control[readonly],
    [data-pms-theme="dark"] .form-control:disabled {
        background: #0F1530 !important;
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    /* Field Icons in Dark Mode */
    html[data-pms-theme="dark"] .form-field.field-code .field-icon,
    html[data-bs-theme="dark"] .form-field.field-code .field-icon,
    [data-pms-theme="dark"] .form-field.field-code .field-icon,
    html[data-pms-theme="dark"] .form-field:nth-child(1) .field-icon,
    html[data-bs-theme="dark"] .form-field:nth-child(1) .field-icon,
    [data-pms-theme="dark"] .form-field:nth-child(1) .field-icon {
        background: rgba(47, 107, 255, 0.22) !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
        border: 1px solid rgba(79, 131, 255, 0.4) !important;
    }

    html[data-pms-theme="dark"] .form-field.field-name .field-icon,
    html[data-bs-theme="dark"] .form-field.field-name .field-icon,
    [data-pms-theme="dark"] .form-field.field-name .field-icon,
    html[data-pms-theme="dark"] .form-field:nth-child(2) .field-icon,
    html[data-bs-theme="dark"] .form-field:nth-child(2) .field-icon,
    [data-pms-theme="dark"] .form-field:nth-child(2) .field-icon {
        background: rgba(56, 189, 248, 0.22) !important;
        color: #38bdf8 !important;
        -webkit-text-fill-color: #38bdf8 !important;
        border: 1px solid rgba(56, 189, 248, 0.4) !important;
    }

    html[data-pms-theme="dark"] .form-field.field-parent .field-icon,
    html[data-bs-theme="dark"] .form-field.field-parent .field-icon,
    [data-pms-theme="dark"] .form-field.field-parent .field-icon,
    html[data-pms-theme="dark"] .form-field:nth-child(3) .field-icon,
    html[data-bs-theme="dark"] .form-field:nth-child(3) .field-icon,
    [data-pms-theme="dark"] .form-field:nth-child(3) .field-icon {
        background: rgba(245, 158, 11, 0.22) !important;
        color: #fbbf24 !important;
        -webkit-text-fill-color: #fbbf24 !important;
        border: 1px solid rgba(245, 158, 11, 0.4) !important;
    }

    html[data-pms-theme="dark"] .form-field.field-level .field-icon,
    html[data-bs-theme="dark"] .form-field.field-level .field-icon,
    [data-pms-theme="dark"] .form-field.field-level .field-icon,
    html[data-pms-theme="dark"] .form-field:nth-child(4) .field-icon,
    html[data-bs-theme="dark"] .form-field:nth-child(4) .field-icon,
    [data-pms-theme="dark"] .form-field:nth-child(4) .field-icon {
        background: rgba(168, 85, 247, 0.22) !important;
        color: #c084fc !important;
        -webkit-text-fill-color: #c084fc !important;
        border: 1px solid rgba(168, 85, 247, 0.4) !important;
    }

    html[data-pms-theme="dark"] .btn-secondary,
    html[data-bs-theme="dark"] .btn-secondary,
    html[data-theme="dark"] .btn-secondary,
    html.dark .btn-secondary {
        background: rgba(47, 107, 255, 0.12) !important;
        color: #60A5FA !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
    }

    html[data-pms-theme="dark"] .form-actions,
    html[data-bs-theme="dark"] .form-actions {
        border-top: 1px solid rgba(79, 131, 255, 0.15) !important;
    }

    html[data-pms-theme="dark"] .alert-success,
    html[data-bs-theme="dark"] .alert-success {
        background: rgba(16, 185, 129, 0.14) !important;
        color: #10B981 !important;
        border-left: 4px solid #10B981 !important;
    }

    html[data-pms-theme="dark"] .alert-danger,
    html[data-bs-theme="dark"] .alert-danger {
        background: #1a0d0d !important;
        color: #fca5a5 !important;
        border-left: 4px solid #ef4444 !important;
    }
</style>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const codeInput = document.getElementById('unique_code');
    const modeInputs = document.querySelectorAll('input[name="code_generation_mode"]');
    const parentSelect = document.getElementById('parent_id');
    const levelSelect = document.getElementById('level');
    const levelHint = document.getElementById('level-field-hint');
    const form = document.querySelector('.form-content');

    function syncCodeMode() {
        if (!codeInput || !modeInputs.length) return;
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

    // Strict 0-6 hierarchy level synchronization
    function syncParentAndLevel() {
        if (!parentSelect || !levelSelect) return;

        const selectedOption = parentSelect.options[parentSelect.selectedIndex];
        const parentLevel = selectedOption ? parseInt(selectedOption.getAttribute('data-level'), 10) : -1;

        if (parentLevel >= 0) {
            // Parent is selected. Child level must be between (parentLevel + 1) and 6.
            const minChildLevel = parentLevel + 1;

            Array.from(levelSelect.options).forEach(opt => {
                const val = parseInt(opt.value, 10);
                if (opt.value === '') return;

                if (val < minChildLevel || val > 6) {
                    opt.disabled = true;
                    opt.classList.add('text-muted');
                } else {
                    opt.disabled = false;
                    opt.classList.remove('text-muted');
                }
            });

            const currentLevel = parseInt(levelSelect.value, 10);
            if (isNaN(currentLevel) || currentLevel < minChildLevel || currentLevel > 6) {
                if (minChildLevel <= 6) {
                    levelSelect.value = String(minChildLevel);
                }
            }

            if (levelHint) {
                levelHint.innerHTML = `<span style="color: #2F6BFF; font-weight: 600;"><i class="fas fa-info-circle"></i> Reports to Level ${parentLevel}: Subordinate level must be between Level ${minChildLevel} and Level 6.</span>`;
            }
        } else {
            // Top-Level (no parent selected)
            Array.from(levelSelect.options).forEach(opt => {
                opt.disabled = false;
                opt.classList.remove('text-muted');
            });

            if (levelHint) {
                levelHint.textContent = 'Top-level designation: Organizational hierarchy level (strictly Level 0 to Level 6).';
            }
        }
    }

    if (parentSelect && levelSelect) {
        parentSelect.addEventListener('change', syncParentAndLevel);
        syncParentAndLevel();
    }

    // Client-side validation to guarantee 0-6 rule
    if (form) {
        form.addEventListener('submit', function (e) {
            const lvl = parseInt(levelSelect?.value, 10);
            if (isNaN(lvl) || lvl < 0 || lvl > 6) {
                e.preventDefault();
                alert('Designation level must be strictly between 0 and 6.');
                return false;
            }

            const selectedOption = parentSelect?.options[parentSelect.selectedIndex];
            const pLevel = selectedOption ? parseInt(selectedOption.getAttribute('data-level'), 10) : -1;

            if (pLevel >= 6) {
                e.preventDefault();
                alert('A Level 6 designation cannot have subordinate designations as Level 6 is the maximum organizational level allowed.');
                return false;
            }

            if (pLevel >= 0 && lvl <= pLevel) {
                e.preventDefault();
                alert(`Subordinate designation level (${lvl}) must be greater than parent designation level (${pLevel}). Minimum allowed level is ${pLevel + 1}.`);
                return false;
            }
        });
    }
});
</script>
@endpush

@endsection
