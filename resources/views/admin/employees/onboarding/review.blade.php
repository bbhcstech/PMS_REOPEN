@extends('admin.layout.app')

@section('title', 'Review Employee Onboarding Application')

@section('content')
@include('admin.employees.partials.directory-styles')
<style>
    .review-shell { max-width: 1500px; margin: 0 auto; padding: 1.5rem 1.25rem 3rem; }
    .review-hero {
        background: linear-gradient(120deg, #122544 18%, #1e40af 62%, #2563eb 100%);
        color: #fff; border-radius: 20px; padding: 1.75rem 2rem; margin-bottom: 1.5rem;
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;
        box-shadow: 0 18px 40px rgba(18, 37, 68, 0.2);
    }
    .review-hero h1 { font-size: 1.6rem; font-weight: 800; margin: 0; color: #fff !important; }
    .review-hero p { margin: 4px 0 0; color: rgba(255,255,255,0.88) !important; font-size: 0.92rem; }
    .review-card {
        background: var(--emp-surface, #fff); border: 1px solid var(--emp-border, #e2e8f0);
        border-radius: 18px; padding: 1.5rem; margin-bottom: 1.5rem;
        box-shadow: var(--emp-shadow-xs, 0 2px 8px rgba(15, 23, 42, 0.05));
    }
    .review-section-title {
        font-size: 1.08rem; font-weight: 800; color: var(--emp-primary, #2563eb);
        padding-bottom: 0.75rem; margin-bottom: 1.25rem;
        border-bottom: 1px solid var(--emp-border, #e2e8f0);
        display: flex; align-items: center; gap: 0.6rem;
    }
    .doc-tile {
        border: 1px solid var(--emp-border, #e2e8f0); border-radius: 14px;
        padding: 1rem; background: var(--emp-surface-subtle, #f8fafc);
        display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
    }
    .doc-thumb {
        width: 56px; height: 56px; border-radius: 12px; object-fit: cover;
        border: 1px solid var(--emp-border, #e2e8f0);
    }
    .audit-timeline { list-style: none; padding: 0; margin: 0; display: grid; gap: 10px; }
    .audit-timeline li {
        padding: 10px 14px; border-radius: 12px; background: var(--emp-surface-subtle, #f8fafc);
        border: 1px solid var(--emp-border, #e2e8f0); font-size: 0.86rem;
    }
    .review-action-bar {
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
        padding: 1.25rem 1.5rem; border-radius: 16px;
        background: var(--emp-surface, #fff); border: 1px solid var(--emp-border, #e2e8f0);
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) .review-card .form-control,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) .review-card .form-select {
        background: var(--emp-surface-subtle) !important;
        border-color: var(--emp-border) !important;
        color: var(--emp-text) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) .review-card .form-label,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) .review-card,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) .review-action-bar {
        color: var(--emp-text) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) .review-shell .text-muted {
        color: var(--emp-text-muted) !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #reviewCorrectionModal .modal-content,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #reviewRejectModal .modal-content,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #prtModal .modal-content,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #dptModal .modal-content,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #designationModal .modal-content {
        background: #0f1530;
        color: #eef1fb;
        border: 1px solid rgba(148, 163, 184, 0.22);
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #reviewCorrectionModal .modal-header,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #reviewCorrectionModal .modal-footer,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #reviewRejectModal .modal-header,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #reviewRejectModal .modal-footer,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #prtModal .modal-header,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #prtModal .modal-footer,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #dptModal .modal-header,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #dptModal .modal-footer,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #designationModal .modal-header,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #designationModal .modal-footer {
        border-color: rgba(148, 163, 184, 0.16);
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #reviewCorrectionModal :is(.form-control, .form-select),
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #reviewRejectModal :is(.form-control, .form-select),
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #prtModal :is(.form-control, .form-select),
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #dptModal :is(.form-control, .form-select),
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #designationModal :is(.form-control, .form-select) {
        background: #141b3d;
        color: #eef1fb;
        border-color: rgba(148, 163, 184, 0.25);
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) .review-shell ~ .modal .btn-close,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #reviewCorrectionModal .btn-close,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #reviewRejectModal .btn-close,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #prtModal .btn-close,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #dptModal .btn-close,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"]) #designationModal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
</style>

@php
    $canAct = $application->canApprove();
    $disabledAttr = $canAct ? '' : 'disabled';
    $adminCfg = $application->admin_configuration ?? [];
    $selectedCountry = old('country', $application->country ?? 'India');
    $selectedDialCode = old('mobile_country_code', $application->mobile_country_code ?? '+91');
    $selectedGender = old('gender', $application->gender ?? '');
    $selectedMarital = old('marital_status', $application->marital_status ?? '');
    $selectedLang = old('language', $application->language ?? 'en');
    $selectedDob = old('dob', $application->dob?->format('Y-m-d') ?? '');
    $empOption = old('employee_id_option', $adminCfg['employee_id_option'] ?? 'auto');
    $selectedDesignation = old('designation_id', $adminCfg['designation_id'] ?? '');
    $selectedPrt = old('parent_dpt_id', $adminCfg['parent_dpt_id'] ?? '');
    $selectedDpt = old('department_id', $adminCfg['department_id'] ?? '');
    $selectedEmployment = old('employment_type', $adminCfg['employment_type'] ?? 'full_time');
    $selectedJoining = old('joining_date', $adminCfg['joining_date'] ?? date('Y-m-d'));
    $selectedReporting = old('reporting_to', $adminCfg['reporting_to'] ?? auth()->id());
    $selectedRole = old('user_role', $adminCfg['user_role'] ?? 'employee');
    $loginAllowed = (string) old('login_allowed', $adminCfg['login_allowed'] ?? '1');
    $emailNotif = (string) old('email_notifications', $adminCfg['email_notifications'] ?? '1');
    $selectedStatus = old('status', $adminCfg['status'] ?? 'Active');
    $isAdminActor = in_array(auth()->user()?->normalizedRole(), ['admin', 'administrator', 'superadmin'], true);
@endphp

<div class="review-shell" id="employee-create-page" data-live-preserve>
    <header class="review-hero">
        <div>
            <h1><i class="fas fa-clipboard-check me-2"></i>Review Onboarding Application</h1>
            <p>
                {{ $application->name }} ({{ $application->email }}) • Status:
                <strong class="text-white">{{ $application->status }}</strong>
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('employees.onboarding.index') }}" class="btn btn-light fw-bold">
                <i class="fas fa-arrow-left me-1"></i> Back to Applications
            </a>
            @if($application->approved_user_id)
                <a href="{{ route('employees.show', $application->approved_user_id) }}" class="btn btn-success fw-bold">
                    <i class="fas fa-user me-1"></i> View Employee ({{ $application->approved_employee_id }})
                </a>
            @endif
        </div>
    </header>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mb-4" role="alert">
            <div class="fw-bold mb-1">Please review the following errors:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($application->status === 'Correction Requested' && $application->correction_reason)
        <div class="alert alert-warning mb-4">
            <strong>Pending Correction Requested:</strong> {{ $application->correction_reason }}
        </div>
    @elseif($application->status === 'Rejected' && $application->rejection_reason)
        <div class="alert alert-danger mb-4">
            <strong>Application Rejected:</strong> {{ $application->rejection_reason }}
        </div>
    @endif

    <form method="POST" action="{{ route('employees.onboarding.approve', $application->id) }}" enctype="multipart/form-data" id="onboardingApproveForm">
        @csrf
        <input type="hidden" name="company_id" value="{{ $company->id }}">
        <input type="hidden" name="new_designation" id="new_designation" value="{{ old('new_designation') }}">
        <input type="hidden" name="new_designation_level" id="new_designation_level" value="{{ old('new_designation_level') }}">
        <input type="hidden" name="new_department" id="new_department" value="{{ old('new_department') }}">
        <input type="hidden" name="new_sub_department" id="new_sub_department" value="{{ old('new_sub_department') }}">

        {{-- Section 1: Submitted Personal & Contact Information --}}
        <div class="review-card">
            <div class="review-section-title"><i class="fas fa-user"></i> Submitted Personal & Contact Information</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Employee Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required {{ $disabledAttr }} value="{{ old('name', $application->name) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" required {{ $disabledAttr }} value="{{ old('email', $application->email) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Country <span class="text-danger">*</span></label>
                    <select name="country" id="country" class="form-select" required {{ $disabledAttr }}>
                        @foreach($countries as $country)
                            <option value="{{ $country->name }}" {{ $selectedCountry === $country->name ? 'selected' : '' }}>{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Mobile <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select name="mobile_country_code" id="mobile_country_code" class="form-select" style="max-width:130px;" required {{ $disabledAttr }}>
                            @foreach($countries as $country)
                                @php $meta = \App\Support\CountryPhone::meta($country->name); @endphp
                                <option value="{{ $meta['dial_code'] }}" data-country="{{ $country->name }}"
                                        {{ $selectedDialCode === $meta['dial_code'] && $selectedCountry === $country->name ? 'selected' : '' }}>
                                    {{ $country->name }} ({{ $meta['dial_code'] }})
                                </option>
                            @endforeach
                        </select>
                        <input type="text" name="mobile" class="form-control" required {{ $disabledAttr }} value="{{ old('mobile', $application->mobile) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Date of Birth <span class="text-danger">*</span></label>
                    <input type="date" name="dob" class="form-control" required {{ $disabledAttr }} value="{{ $selectedDob }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Gender</label>
                    <select name="gender" class="form-select" {{ $disabledAttr }}>
                        <option value="">Select</option>
                        @foreach(['Male', 'Female', 'Other', 'Prefer not to say'] as $g)
                            <option value="{{ $g }}" {{ $selectedGender === $g ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Marital Status</label>
                    <select name="marital_status" class="form-select" {{ $disabledAttr }}>
                        <option value="">Select</option>
                        @foreach(['single' => 'Single', 'married' => 'Married', 'widower' => 'Widower', 'widow' => 'Widow', 'separate' => 'Separate', 'divorced' => 'Divorced', 'engaged' => 'Engaged'] as $val => $lbl)
                            <option value="{{ $val }}" {{ $selectedMarital === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Language</label>
                    <select name="language" class="form-select" {{ $disabledAttr }}>
                        @foreach(['en' => 'English', 'bn' => 'Bengali', 'hi' => 'Hindi', 'fr' => 'French', 'de' => 'German'] as $code => $langName)
                            <option value="{{ $code }}" {{ $selectedLang === $code ? 'selected' : '' }}>{{ $langName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Address</label>
                    <textarea name="address" class="form-control" rows="2" {{ $disabledAttr }}>{{ old('address', $application->address) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section 2: Professional & Directory Profile --}}
        <div class="review-card">
            <div class="review-section-title"><i class="fas fa-address-card"></i> Professional & Directory Profile</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Skills</label>
                    <textarea name="skills" class="form-control" rows="2" {{ $disabledAttr }}>{{ old('skills', $application->skills) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">About / Professional Summary</label>
                    <textarea name="about" class="form-control" rows="2" {{ $disabledAttr }}>{{ old('about', $application->about) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Public About / HR Rewrite</label>
                    <textarea name="directory_about" class="form-control" rows="3" {{ $disabledAttr }}>{{ old('directory_about', $application->directory_about) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">LinkedIn</label>
                    <input type="url" name="linkedin_url" class="form-control" {{ $disabledAttr }} value="{{ old('linkedin_url', $application->linkedin_url) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Portfolio</label>
                    <input type="url" name="portfolio_url" class="form-control" {{ $disabledAttr }} value="{{ old('portfolio_url', $application->portfolio_url) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Facebook</label>
                    <input type="url" name="facebook_url" class="form-control" {{ $disabledAttr }} value="{{ old('facebook_url', $application->facebook_url) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Instagram</label>
                    <input type="url" name="instagram_url" class="form-control" {{ $disabledAttr }} value="{{ old('instagram_url', $application->instagram_url) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">X / Twitter</label>
                    <input type="url" name="x_url" class="form-control" {{ $disabledAttr }} value="{{ old('x_url', $application->x_url) }}">
                </div>
            </div>
        </div>

        {{-- Section 3: Submitted Documents (Private Preview & Download + Optional Override) --}}
        <div class="review-card">
            <div class="review-section-title"><i class="fas fa-folder-open"></i> Submitted Files & Verification Documents</div>
            <div class="row g-3">
                @foreach([
                    'profile_picture' => ['label' => 'Profile Picture', 'path' => $application->profile_picture_path, 'name' => $application->profile_picture_name, 'is_image' => true, 'accept' => '.jpeg,.jpg,.png'],
                    'cv_file' => ['label' => 'CV / Resume', 'path' => $application->cv_file_path, 'name' => $application->cv_file_name, 'is_image' => false, 'accept' => '.pdf,.doc,.docx'],
                    'government_id_card' => ['label' => 'Government ID Card', 'path' => $application->government_id_card_path, 'name' => $application->government_id_card_name, 'is_image' => true, 'accept' => '.jpeg,.jpg,.png'],
                ] as $field => $doc)
                    <div class="col-md-4">
                        <div class="doc-tile h-100">
                            <div class="d-flex align-items-center gap-3">
                                @if($doc['path'] && $doc['is_image'])
                                    <img src="{{ route('employees.onboarding.document', [$application->id, $field]) }}" class="doc-thumb" alt="{{ $doc['label'] }}">
                                @else
                                    <span class="profile-placeholder d-inline-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                                        <i class="fas {{ $doc['is_image'] ? 'fa-image' : 'fa-file-pdf' }}"></i>
                                    </span>
                                @endif
                                <div>
                                    <div class="fw-bold">{{ $doc['label'] }}</div>
                                    <small class="text-muted d-block">{{ $doc['name'] ?: 'Not uploaded' }}</small>
                                </div>
                            </div>
                            <div class="d-flex gap-2 w-100">
                                @if($doc['path'])
                                    <a href="{{ route('employees.onboarding.document', [$application->id, $field]) }}" target="_blank" class="btn btn-sm btn-outline-primary flex-fill">
                                        <i class="fas fa-eye me-1"></i> Preview
                                    </a>
                                    <a href="{{ route('employees.onboarding.document', [$application->id, $field, 'download' => 1]) }}" class="btn btn-sm btn-outline-secondary flex-fill">
                                        <i class="fas fa-download me-1"></i> Download
                                    </a>
                                @endif
                            </div>
                            @if($canAct)
                                <input type="file" name="{{ $field }}" class="form-control form-control-sm" accept="{{ $doc['accept'] }}">
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section 4: Administrative Configuration (HR/Admin Owned) --}}
        <div class="review-card">
            <div class="review-section-title"><i class="fas fa-sliders"></i> Administrative Employee Configuration</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Employee ID <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="employee_id_option" id="emp_auto" value="auto" {{ $empOption === 'auto' ? 'checked' : '' }} {{ $disabledAttr }}>
                            <label class="form-check-label" for="emp_auto">Auto-generate</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="employee_id_option" id="emp_custom" value="custom" {{ $empOption === 'custom' ? 'checked' : '' }} {{ $disabledAttr }}>
                            <label class="form-check-label" for="emp_custom">Custom ID</label>
                        </div>
                    </div>
                    <input type="text" id="employee_id_input" name="employee_id" class="form-control" {{ $disabledAttr }}
                           value="{{ old('employee_id', $application->approved_employee_id ?: $nextEmployeeId) }}"
                           {{ $empOption === 'auto' ? 'readonly' : '' }}>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Designation <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select name="designation_id" id="designation_id" class="form-select" required {{ $disabledAttr }}>
                            <option value="">Select Designation</option>
                            @foreach($designations as $des)
                                <option value="{{ $des->id }}" {{ (string) $selectedDesignation === (string) $des->id ? 'selected' : '' }}>
                                    {{ $des->name }}@if($des->level !== null) - Level {{ $des->level }}@endif
                                </option>
                            @endforeach
                        </select>
                        @if($canAct)
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#designationModal" title="Add Designation">
                                <i class="fas fa-plus"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Department <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select name="parent_dpt_id" id="prt_department_id" class="form-select" required {{ $disabledAttr }}>
                            <option value="">Select Department</option>
                            @foreach($prtdepartments as $dept)
                                <option value="{{ $dept->id }}" {{ (string) $selectedPrt === (string) $dept->id ? 'selected' : '' }}>{{ $dept->dpt_name }}</option>
                            @endforeach
                        </select>
                        @if($canAct)
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#prtModal" title="Add Department">
                                <i class="fas fa-plus"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Sub Department</label>
                    <div class="input-group">
                        <select name="department_id" id="department_id" class="form-select" data-selected="{{ $selectedDpt }}" {{ $disabledAttr }}>
                            <option value="">Select Sub Department</option>
                            @foreach($departments as $sub)
                                <option value="{{ $sub->id }}" data-parent="{{ $sub->parent_dpt_id }}" {{ (string) $selectedDpt === (string) $sub->id ? 'selected' : '' }}>
                                    {{ $sub->dpt_name }}
                                </option>
                            @endforeach
                        </select>
                        @if($canAct)
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#dptModal" title="Add Sub Department">
                                <i class="fas fa-plus"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Employment Type <span class="text-danger">*</span></label>
                    <select name="employment_type" class="form-select" required {{ $disabledAttr }}>
                        @foreach(['full_time' => 'Full Time', 'part_time' => 'Part Time', 'on_contract' => 'On Contract', 'internship' => 'Internship', 'trainee' => 'Trainee'] as $val => $lbl)
                            <option value="{{ $val }}" {{ $selectedEmployment === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Joining Date <span class="text-danger">*</span></label>
                    <input type="date" name="joining_date" class="form-control" required {{ $disabledAttr }} value="{{ $selectedJoining }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Reporting To</label>
                    <select name="reporting_to" class="form-select" {{ $disabledAttr }}>
                        <option value="">Select Reporting Manager</option>
                        @foreach($users as $mgr)
                            <option value="{{ $mgr->id }}" {{ (string) $selectedReporting === (string) $mgr->id ? 'selected' : '' }}>{{ $mgr->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Login Allowed <span class="text-danger">*</span></label>
                    <select name="login_allowed" class="form-select" {{ $disabledAttr }}>
                        <option value="1" {{ $loginAllowed === '1' ? 'selected' : '' }}>Yes (Send account activation link)</option>
                        <option value="0" {{ $loginAllowed === '0' ? 'selected' : '' }}>No (Confirmation only, no login)</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Email Notifications</label>
                    <select name="email_notifications" class="form-select" {{ $disabledAttr }}>
                        <option value="1" {{ $emailNotif === '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ $emailNotif === '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                @if($isAdminActor)
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Portal Role</label>
                        <select name="user_role" class="form-select" {{ $disabledAttr }}>
                            @foreach(['employee' => 'Employee', 'hr' => 'HR', 'manager' => 'Manager', 'admin' => 'Admin'] as $rVal => $rLabel)
                                <option value="{{ $rVal }}" {{ $selectedRole === $rVal ? 'selected' : '' }}>{{ $rLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="user_role" value="employee">
                @endif

                <div class="col-md-4">
                    <label class="form-label fw-bold">Business Address</label>
                    <input type="text" name="business_address" class="form-control" {{ $disabledAttr }} value="{{ old('business_address', $adminCfg['business_address'] ?? ($company->address ?: 'Head Office')) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Account Status <span class="text-danger">*</span></label>
                    <select name="status" id="reviewStatusSelect" class="form-select" {{ $disabledAttr }}>
                        <option value="Active" {{ $selectedStatus === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ $selectedStatus === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-md-4" id="reviewExitDateWrap" style="display: {{ $selectedStatus === 'Inactive' ? 'block' : 'none' }};">
                    <label class="form-label fw-bold">Exit Date</label>
                    <input type="date" name="exit_date" class="form-control" {{ $disabledAttr }} value="{{ old('exit_date', $adminCfg['exit_date'] ?? '') }}">
                </div>
            </div>
        </div>

        {{-- Action Bar --}}
        <div class="review-action-bar mb-4">
            <a href="{{ route('employees.onboarding.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Applications
            </a>
            @if($canAct)
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#reviewCorrectionModal">
                        <i class="fas fa-pen-to-square me-1"></i> Request Correction
                    </button>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#reviewRejectModal">
                        <i class="fas fa-circle-xmark me-1"></i> Reject
                    </button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold" id="approveAddEmployeeBtn">
                        <i class="fas fa-user-plus me-1"></i> Add
                    </button>
                </div>
            @endif
        </div>
    </form>

    {{-- Audit History --}}
    @if($application->audits->isNotEmpty())
        <div class="review-card">
            <div class="review-section-title"><i class="fas fa-clock-rotate-left"></i> Audit History</div>
            <ul class="audit-timeline">
                @foreach($application->audits as $audit)
                    <li>
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <strong>{{ $audit->event }}</strong>
                            <small class="text-muted">{{ $audit->created_at?->format('d M Y, h:i A') }} · {{ $audit->actor_label ?: ucfirst($audit->actor_type ?? 'system') }}</small>
                        </div>
                        @if($audit->notes)
                            <div class="text-muted mt-1">{{ $audit->notes }}</div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection

@push('modals')
@if($canAct)
<div class="modal fade" id="reviewCorrectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('employees.onboarding.request-correction', $application->id) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-pen-to-square text-warning me-2"></i>Request Correction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-bold">Correction Message <span class="text-danger">*</span></label>
                <textarea name="correction_reason" class="form-control" rows="4" required minlength="5" maxlength="2000"
                          placeholder="Tell {{ $application->name }} which fields or documents need correction..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning">Send Correction Link</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="reviewRejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('employees.onboarding.reject', $application->id) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-circle-xmark text-danger me-2"></i>Reject Onboarding Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                <textarea name="rejection_reason" class="form-control" rows="4" required minlength="5" maxlength="2000"
                          placeholder="Enter rejection reason..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject Application</button>
            </div>
        </form>
    </div>
</div>

{{-- Inline Department / Sub-Department / Designation Creation Modals --}}
<div class="modal fade" id="prtModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="reviewAddPrtForm" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title">Add Department</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label fw-bold">Department Name *</label>
                <input type="text" name="dpt_name" class="form-control" required>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Department</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="dptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="reviewAddSubForm" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title">Add Sub Department</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Parent Department *</label>
                    <select name="parent_dpt_id" id="reviewSubParent" class="form-select" required>
                        @foreach($prtdepartments as $pd)<option value="{{ $pd->id }}">{{ $pd->dpt_name }}</option>@endforeach
                    </select>
                </div>
                <label class="form-label fw-bold">Sub Department Name *</label>
                <input type="text" name="dpt_name" class="form-control" required>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Sub Department</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="designationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="reviewAddDesForm" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title">Add Designation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Designation Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <label class="form-label fw-bold">Level (0-{{ \App\Services\DesignationLevels::maximum() }}) *</label>
                <input type="number" name="level" min="0" max="{{ \App\Services\DesignationLevels::maximum() }}" value="1" class="form-control" required>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Designation</button></div>
        </form>
    </div>
</div>
@endif
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const autoIdValue = @json($nextEmployeeId);
    const idInput = document.getElementById('employee_id_input');
    document.querySelectorAll('input[name="employee_id_option"]').forEach(r => {
        r.addEventListener('change', function () {
            if (this.value === 'auto') {
                idInput.readOnly = true;
                idInput.value = autoIdValue;
            } else {
                idInput.readOnly = false;
                idInput.focus();
            }
        });
    });

    document.getElementById('reviewStatusSelect')?.addEventListener('change', function () {
        document.getElementById('reviewExitDateWrap').style.display = this.value === 'Inactive' ? 'block' : 'none';
    });

    document.getElementById('prt_department_id')?.addEventListener('change', function () {
        const pid = this.value;
        const subSelect = document.getElementById('department_id');
        if (!subSelect) return;
        for (const opt of subSelect.options) {
            if (!opt.value) continue;
            opt.hidden = Boolean(pid && opt.dataset.parent && opt.dataset.parent !== pid);
        }
        if (subSelect.selectedOptions[0]?.hidden) subSelect.value = '';
    });

    const form = document.getElementById('onboardingApproveForm');
    form?.addEventListener('submit', function () {
        const btn = document.getElementById('approveAddEmployeeBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Creating Employee...';
        }
    });

    async function postQuickCreate(url, formEl, onSuccess) {
        const fd = new FormData(formEl);
        const res = await fetch(url, { method: 'POST', headers: { 'Accept': 'application/json' }, body: fd });
        const data = await res.json();
        if (!res.ok) { alert(data.message || 'Could not create item.'); return; }
        onSuccess(data);
        bootstrap.Modal.getInstance(formEl.closest('.modal'))?.hide();
        formEl.reset();
    }

    document.getElementById('reviewAddPrtForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        postQuickCreate(@json(route('parent-departments.quick-create')), this, function (data) {
            const dept = data.department;
            const sel = document.getElementById('prt_department_id');
            sel.add(new Option(dept.dpt_name, dept.id, true, true));
            document.getElementById('reviewSubParent')?.add(new Option(dept.dpt_name, dept.id));
        });
    });

    document.getElementById('reviewAddSubForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        postQuickCreate(@json(route('departments.store.ajax')), this, function (data) {
            const dept = data.department;
            const sel = document.getElementById('department_id');
            const opt = new Option(dept.dpt_name, dept.id, true, true);
            opt.dataset.parent = dept.parent_dpt_id;
            sel.add(opt);
        });
    });

    document.getElementById('reviewAddDesForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        postQuickCreate(@json(route('designations.store.ajax')), this, function (data) {
            const des = data.designation;
            const sel = document.getElementById('designation_id');
            sel.add(new Option(des.name + ' - Level ' + des.level, des.id, true, true));
        });
    });
});
</script>
@endpush
