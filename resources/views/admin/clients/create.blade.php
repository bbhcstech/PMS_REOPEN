@extends('admin.layout.app')

@section('content')
<div class="container-fluid px-4 py-4">
    
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e293b;">Add New Client</h4>
            <div class="text-muted small">
                <span>Dashboard</span> <i class="fas fa-chevron-right mx-1" style="font-size: 10px;"></i>
                <span>Clients</span> <i class="fas fa-chevron-right mx-1" style="font-size: 10px;"></i>
                <span class="text-primary fw-semibold">Add Client Wizard</span>
            </div>
        </div>
        <div>
            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Clients
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
                <strong>Please correct the errors below:</strong>
            </div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Wizard Stepper Navigation -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: #ffffff;">
        <div class="card-body p-3 p-md-4">
            <div class="wizard-stepper">
                <div class="stepper-progress">
                    <div class="stepper-progress-bar" id="stepperProgressBar" style="width: 25%;"></div>
                </div>

                <div class="stepper-steps">
                    <!-- Step 1 Indicator -->
                    <div class="step-item active" id="stepIndicator1" onclick="navigateToStep(1)">
                        <div class="step-circle">
                            <span class="step-number">1</span>
                            <i class="fas fa-check step-check"></i>
                        </div>
                        <div class="step-label">
                            <span class="step-title">Account Details</span>
                            <span class="step-subtitle">Personal Details</span>
                        </div>
                    </div>

                    <!-- Step 2 Indicator -->
                    <div class="step-item" id="stepIndicator2" onclick="navigateToStep(2)">
                        <div class="step-circle">
                            <span class="step-number">2</span>
                            <i class="fas fa-check step-check"></i>
                        </div>
                        <div class="step-label">
                            <span class="step-title">Company Details</span>
                            <span class="step-subtitle">Business Info</span>
                        </div>
                    </div>

                    <!-- Step 3 Indicator -->
                    <div class="step-item" id="stepIndicator3" onclick="navigateToStep(3)">
                        <div class="step-circle">
                            <span class="step-number">3</span>
                            <i class="fas fa-check step-check"></i>
                        </div>
                        <div class="step-label">
                            <span class="step-title">Add Project</span>
                            <span class="step-subtitle">Project Setup</span>
                        </div>
                    </div>

                    <!-- Step 4 Indicator -->
                    <div class="step-item" id="stepIndicator4" onclick="navigateToStep(4)">
                        <div class="step-circle">
                            <span class="step-number">4</span>
                            <i class="fas fa-check step-check"></i>
                        </div>
                        <div class="step-label">
                            <span class="step-title">Add Deals</span>
                            <span class="step-subtitle">Deal & Pipeline</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Wizard Form -->
    <form action="{{ route('clients.store') }}" method="POST" enctype="multipart/form-data" id="clientWizardForm" novalidate>
        @csrf

        <!-- ==========================================
             STEP 1: CLIENT PERSONAL / ACCOUNT DETAILS
             ========================================== -->
        <div class="wizard-step-panel" id="stepPanel1">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="step-header-icon me-3">
                                <i class="fas fa-user text-primary fs-5"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold" style="color: #1e293b;">Account Details</h5>
                                <small class="text-muted">Fill in client personal identification and login credentials</small>
                            </div>
                        </div>
                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold">Step 1 of 4</span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row g-3">

                        <!-- Client ID (Readonly) -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Client ID</label>
                            <input type="text"
                                   class="form-control form-control-custom bg-light"
                                   value="{{ $nextClientCode ?? 'XINK-CL-0001' }}"
                                   readonly>
                        </div>

                        <!-- Salutation -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Salutation</label>
                            <select name="salutation" id="salutation" class="form-select form-control-custom">
                                <option value="">Select salutation</option>
                                <option value="Mr" {{ old('salutation') == 'Mr' ? 'selected' : '' }}>Mr</option>
                                <option value="Mrs" {{ old('salutation') == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                                <option value="Miss" {{ old('salutation') == 'Miss' ? 'selected' : '' }}>Miss</option>
                                <option value="Dr" {{ old('salutation') == 'Dr' ? 'selected' : '' }}>Dr</option>
                                <option value="Sir" {{ old('salutation') == 'Sir' ? 'selected' : '' }}>Sir</option>
                                <option value="Madam" {{ old('salutation') == 'Madam' ? 'selected' : '' }}>Madam</option>
                            </select>
                        </div>

                        <!-- Client Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Client Name <sup class="text-danger">*</sup></label>
                            <input name="name" id="client_name" type="text" class="form-control form-control-custom" placeholder="e.g. John Doe" value="{{ old('name') }}" required>
                            <div class="invalid-feedback">Please enter client name.</div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Email <sup class="text-danger">*</sup></label>
                            <input name="email" id="client_email" type="email" class="form-control form-control-custom" placeholder="e.g. admin@bloodlife.com" value="{{ old('email') }}" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>

                        <!-- Password -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">Password <sup class="text-danger">*</sup></label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control form-control-custom" autocomplete="off" minlength="8" required>
                                
                                <button type="button" class="btn btn-outline-secondary toggle-password" title="Show/Hide Password" tabindex="-1">
                                    <i class="fa fa-eye"></i>
                                </button>
                        
                                <button type="button" class="btn btn-outline-secondary generate-password" title="Generate Random Password" tabindex="-1">
                                    <i class="fa fa-random"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">Must have at least 9 characters</small>
                            <div class="invalid-feedback">Password must be at least 6 characters.</div>
                        </div>

                        <!-- Country -->
                        <div class="col-md-5 mb-2">
                            <label class="form-label fw-semibold text-secondary">Country <sup class="text-danger">*</sup></label>
                            <select name="country" id="country" class="form-select form-control-custom select2" required>
                                <option value="">Select members</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->name }}" 
                                            data-flag="{{ $country->flag_url }}"
                                            {{ old('country', 'India') == $country->name ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a country.</div>
                        </div>

                        <!-- Mobile -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">Mobile <sup class="text-danger">*</sup></label>
                            <input name="mobile" id="client_mobile" type="text" class="form-control form-control-custom" placeholder="e.g. 1234567890" value="{{ old('mobile', '+91') }}" required>
                            <small class="text-muted">Format: +91XXXXXXXXXX</small>
                            <div class="invalid-feedback">Please enter a 10-digit mobile number starting with +91.</div>
                        </div>

                        <!-- Profile Picture -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">Profile Picture</label>
                            <input name="profile_picture" id="profile_picture" type="file" class="form-control form-control-custom" accept="image/*">
                        </div>

                        <!-- Gender -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">Gender</label>
                            <select name="gender" id="gender" class="form-select form-control-custom">
                                <option value="">Select</option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <!-- Change Language -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">Change Language</label>
                            <select name="language" id="language" class="form-select form-control-custom select2">
                                <option value="en" data-flag="https://flagcdn.com/w20/gb.png" {{ old('language', 'en') == 'en' ? 'selected' : '' }}>English</option>
                                <option value="bn" data-flag="https://flagcdn.com/w20/bd.png" {{ old('language', 'bn') == 'bn' ? 'selected' : '' }}>Bengali</option>
                                <option value="hi" data-flag="https://flagcdn.com/w20/in.png" {{ old('language', 'hi') == 'hi' ? 'selected' : '' }}>Hindi</option>
                                <option value="fr" data-flag="https://flagcdn.com/w20/fr.png" {{ old('language', 'fr') == 'fr' ? 'selected' : '' }}>French</option>
                                <option value="de" data-flag="https://flagcdn.com/w20/de.png" {{ old('language', 'de') == 'de' ? 'selected' : '' }}>German</option>
                            </select>
                        </div>

                        <!-- Client Category -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">Client Category</label>
                            <div class="input-group">
                                <select name="client_category_id" id="client_category_id" class="form-select form-control-custom">
                                    <option value="">Select</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('client_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addCategoryModal" title="Add Category">+</button>
                            </div>
                        </div>
                    
                        <!-- Client Sub Category -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">Client Sub Category</label>
                            <div class="input-group">
                                <select name="client_sub_category_id" id="client_sub_category_id" class="form-select form-control-custom">
                                    <option value="">Select</option>
                                    @foreach($subcategories as $sub)
                                        <option value="{{ $sub->id }}" {{ old('client_sub_category_id') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addSubCategoryModal" title="Add Sub Category">+</button>
                            </div>
                        </div>

                        <!-- Login Allowed -->
                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-semibold text-secondary d-block">Login Allowed? <sup class="text-danger">*</sup></label>
                            <div class="form-check form-check-inline me-4">
                                <input class="form-check-input custom-radio" type="radio" name="login_allowed" id="login_allowed_yes" value="1" {{ old('login_allowed', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="login_allowed_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input custom-radio" type="radio" name="login_allowed" id="login_allowed_no" value="0" {{ old('login_allowed') == '0' ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="login_allowed_no">No</label>
                            </div>
                        </div>

                        <!-- Receive Email Notifications -->
                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-semibold text-secondary d-block">Receive Email Notifications?</label>
                            <div class="form-check form-check-inline me-4">
                                <input class="form-check-input custom-radio" type="radio" name="email_notifications" id="email_notifications_yes" value="1" {{ old('email_notifications', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="email_notifications_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input custom-radio" type="radio" name="email_notifications" id="email_notifications_no" value="0" {{ old('email_notifications') == '0' ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="email_notifications_no">No</label>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card-footer bg-white border-top p-4 d-flex justify-content-between align-items-center">
                    <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="button" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm next-step-btn" onclick="goToStep(2)">
                        Save and Continue <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </div>


        <!-- ==========================================
             STEP 2: COMPANY DETAILS
             ========================================== -->
        <div class="wizard-step-panel d-none" id="stepPanel2">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="step-header-icon me-3">
                                <i class="fas fa-building text-primary fs-5"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold" style="color: #1e293b;">Company Details</h5>
                                <small class="text-muted">Fill in business information and tax identification</small>
                            </div>
                        </div>
                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold">Step 2 of 4</span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row g-3">

                        <!-- Company Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Company Name</label>
                            <input name="company_name" id="company_name" type="text" class="form-control form-control-custom" placeholder="e.g. Acme Corporation" value="{{ old('company_name') }}">
                        </div>

                        <!-- Official Website -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Official Website</label>
                            <input name="website" id="website" type="url" class="form-control form-control-custom" placeholder="https://www.example.com" value="{{ old('website') }}">
                        </div>

                        <!-- Tax Name -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">Tax Name</label>
                            <input name="tax_name" id="tax_name" type="text" class="form-control form-control-custom" placeholder="e.g. GST/VAT" value="{{ old('tax_name') }}">
                        </div>

                        <!-- GST/VAT Number -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">GST/VAT Number</label>
                            <input name="tax_number" id="tax_number" type="text" class="form-control form-control-custom" placeholder="e.g. 18AABCU960XXXXX" value="{{ old('tax_number') }}">
                        </div>

                        <!-- Office Phone -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">Office Phone</label>
                            <input name="office_phone" id="office_phone" type="text" class="form-control form-control-custom" placeholder="+91XXXXXXXXXX" value="{{ old('office_phone') }}">
                            <small class="text-muted">Format: +91XXXXXXXXXX</small>
                        </div>

                        <!-- City -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">City</label>
                            <input name="city" id="city" type="text" class="form-control form-control-custom" placeholder="e.g. New York" value="{{ old('city') }}">
                        </div>

                        <!-- State -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">State</label>
                            <input name="state" id="state" type="text" class="form-control form-control-custom" placeholder="e.g. California" value="{{ old('state') }}">
                        </div>

                        <!-- Postal Code -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">Postal Code</label>
                            <input name="postal_code" id="postal_code" type="text" class="form-control form-control-custom" placeholder="e.g. 90250" value="{{ old('postal_code') }}">
                        </div>

                        <!-- Company Address -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-secondary">Company Address</label>
                            <textarea name="company_address" id="company_address" class="form-control form-control-custom" rows="2" placeholder="e.g. 132, My Street, Kingston, NY">{{ old('company_address') }}</textarea>
                        </div>

                        <!-- Shipping Address -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-secondary">Shipping Address</label>
                            <textarea name="shipping_address" id="shipping_address" class="form-control form-control-custom" rows="2" placeholder="e.g. 132, My Street, Kingston, NY">{{ old('shipping_address') }}</textarea>
                        </div>

                        <!-- Note -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-secondary">Note</label>
                            <textarea name="note" id="note" class="form-control form-control-custom" rows="2" placeholder="Write any additional note here...">{{ old('note') }}</textarea>
                        </div>

                        <!-- Company Logo -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Company Logo</label>
                            <input name="company_logo" id="company_logo" type="file" class="form-control form-control-custom" accept="image/*">
                        </div>

                        <!-- Added By -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Added By</label>
                            <select name="added_by" id="added_by" class="form-select form-control-custom">
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('added_by', auth()->id()) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>

                <div class="card-footer bg-white border-top p-4 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill prev-step-btn" onclick="goToStep(1)">
                        <i class="fas fa-arrow-left me-2"></i> Previous
                    </button>
                    <button type="button" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm next-step-btn" onclick="goToStep(3)">
                        Save and Continue <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </div>


        <!-- ==========================================
             STEP 3: ADD PROJECT PAGE
             ========================================== -->
        <div class="wizard-step-panel d-none" id="stepPanel3">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="step-header-icon me-3">
                                <i class="fas fa-project-diagram text-primary fs-5"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold" style="color: #1e293b;">Add Project</h5>
                                <small class="text-muted">Optionally create an initial project for this client</small>
                            </div>
                        </div>
                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold">Step 3 of 4</span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row g-3">

                        <!-- Short Code Option -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">
                                <i class="fas fa-code text-primary me-1"></i> Project Short Code
                            </label>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="project_shortcode_option" id="project_shortcode_auto" value="auto" checked onchange="toggleProjectCodeInput()">
                                    <label class="form-check-label" for="project_shortcode_auto">Auto-generate</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="project_shortcode_option" id="project_shortcode_manual_opt" value="manual" onchange="toggleProjectCodeInput()">
                                    <label class="form-check-label" for="project_shortcode_manual_opt">Custom</label>
                                </div>
                            </div>
                            <input type="text" id="project_shortcode_display" class="form-control form-control-custom bg-light" value="{{ $nextProjectCode ?? 'Will be generated automatically' }}" readonly>
                            <input type="text" name="project_shortcode_manual" id="project_shortcode_manual" class="form-control form-control-custom d-none" placeholder="Enter custom code e.g. bit25-26/0001">
                        </div>

                        <!-- Project Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">
                                <i class="fas fa-tag text-primary me-1"></i> Project Name
                            </label>
                            <input type="text" name="project_name" id="project_name" class="form-control form-control-custom" placeholder="e.g. Website Redesign & Branding" value="{{ old('project_name') }}">
                        </div>

                        <!-- Start Date -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">
                                <i class="fas fa-calendar-plus text-primary me-1"></i> Start Date
                            </label>
                            <input type="date" name="project_start_date" id="project_start_date" class="form-control form-control-custom" value="{{ old('project_start_date', date('Y-m-d')) }}">
                        </div>

                        <!-- Deadline -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">
                                <i class="fas fa-calendar-times text-primary me-1"></i> Deadline
                            </label>
                            <input type="date" name="project_deadline" id="project_deadline" class="form-control form-control-custom" value="{{ old('project_deadline') }}">
                        </div>

                        <!-- No Deadline Checkbox -->
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="project_without_deadline" id="project_without_deadline" value="1" onchange="toggleDeadlineInput()">
                                <label class="form-check-label fw-semibold text-secondary" for="project_without_deadline">
                                    <i class="fas fa-infinity text-muted me-1"></i> No deadline for this project
                                </label>
                            </div>
                        </div>

                        <!-- Priority -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">
                                <i class="fas fa-bolt text-primary me-1"></i> Priority
                            </label>
                            <select name="project_priority" id="project_priority" class="form-select form-control-custom">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>

                        <!-- Project Category -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">
                                <i class="fas fa-folder text-primary me-1"></i> Project Category
                            </label>
                            <div class="input-group">
                                <select name="project_category_id" id="project_category_id" class="form-select form-control-custom">
                                    <option value="">Select Category</option>
                                    @foreach($projectCategories as $pcat)
                                        <option value="{{ $pcat->id }}" {{ old('project_category_id') == $pcat->id ? 'selected' : '' }}>{{ $pcat->category_name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addProjectCategoryModal" title="Add Project Category">+</button>
                            </div>
                        </div>

                        <!-- Project Department -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">
                                <i class="fas fa-building text-primary me-1"></i> Project Department
                            </label>
                            <select name="project_department_ids[]" id="project_department_ids" class="form-select form-control-custom select2" multiple>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->dpt_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Assigned Employees -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">
                                <i class="fas fa-users text-primary me-1"></i> Assign Employees / Members
                            </label>
                            <select name="project_employee_ids[]" id="project_employee_ids" class="form-select form-control-custom select2" multiple>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->role }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Currency -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Currency</label>
                            <select name="project_currency_id" id="project_currency_id" class="form-select form-control-custom">
                                @foreach($currencies as $curr)
                                    <option value="{{ $curr->id }}" {{ (old('project_currency_id') == $curr->id || $curr->currency_code == 'INR') ? 'selected' : '' }}>
                                        {{ $curr->currency_symbol ?? '' }} {{ $curr->currency_code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Budget -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Project Budget</label>
                            <input type="number" step="0.01" name="project_budget" id="project_budget" class="form-control form-control-custom" placeholder="e.g. 50000" value="{{ old('project_budget') }}">
                        </div>

                        <!-- Hours Allocated -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Hours Allocated</label>
                            <input type="number" name="project_hours" id="project_hours" class="form-control form-control-custom" placeholder="e.g. 120" value="{{ old('project_hours') }}">
                        </div>

                        <!-- Progress Percentage -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Initial Progress (%)</label>
                            <input type="number" name="completion_percent" id="completion_percent" class="form-control form-control-custom" min="0" max="100" placeholder="0" value="{{ old('completion_percent', 0) }}">
                        </div>

                        <!-- Project Description -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Project Description</label>
                            <textarea name="project_description" id="project_description" class="form-control form-control-custom" rows="3" placeholder="Brief overview of the project scope and deliverables...">{{ old('project_description') }}</textarea>
                        </div>

                        <!-- Project Notes / Remarks -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Project Notes / Remarks</label>
                            <textarea name="project_notes" id="project_notes" class="form-control form-control-custom" rows="3" placeholder="Internal notes or special instructions...">{{ old('project_notes') }}</textarea>
                        </div>

                        <!-- Project File -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-secondary">Attach Project Specification / Brief File</label>
                            <input name="project_file" id="project_file" type="file" class="form-control form-control-custom">
                        </div>

                    </div>
                </div>

                <div class="card-footer bg-white border-top p-4 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill prev-step-btn" onclick="goToStep(2)">
                        <i class="fas fa-arrow-left me-2"></i> Previous
                    </button>
                    <button type="button" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm next-step-btn" onclick="goToStep(4)">
                        Save and Continue <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </div>


        <!-- ==========================================
             STEP 4: ADD DEALS PAGE (FINAL STEP)
             ========================================== -->
        <div class="wizard-step-panel d-none" id="stepPanel4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="step-header-icon me-3">
                                <i class="fas fa-handshake text-success fs-5"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold" style="color: #1e293b;">Add Deals</h5>
                                <small class="text-muted">Optionally add a sales deal and pipeline configuration</small>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold">Step 4 of 4 (Final)</span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row g-3">

                        <!-- Deal Information Card -->
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light bg-opacity-50 h-100">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="fas fa-file-invoice-dollar me-1"></i> Deal Information
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary">Deal Name</label>
                                    <input type="text" class="form-control form-control-custom" id="deal_name" name="deal_name" placeholder="e.g. Annual Software License & Retainer" value="{{ old('deal_name') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary">Deal Value (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">₹</span>
                                        <input type="number" step="0.01" class="form-control form-control-custom" id="deal_value" name="deal_value" placeholder="0.00" value="{{ old('deal_value') }}">
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-semibold text-secondary">Close Date</label>
                                        <input type="date" class="form-control form-control-custom" id="deal_close_date" name="deal_close_date" value="{{ old('deal_close_date', date('Y-m-d', strtotime('+7 days'))) }}">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-semibold text-secondary">Next Follow Up</label>
                                        <input type="date" class="form-control form-control-custom" id="deal_next_follow_up" name="deal_next_follow_up" value="{{ old('deal_next_follow_up', date('Y-m-d', strtotime('+14 days'))) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lead & Contact Information Card -->
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light bg-opacity-50 h-100">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="fas fa-address-card me-1"></i> Lead & Contact Information
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary">Lead / Contact Name</label>
                                    <input type="text" class="form-control form-control-custom" id="deal_lead_name" name="deal_lead_name" placeholder="Auto-populated from Client Name" value="{{ old('deal_lead_name') }}">
                                    <small class="text-muted">Defaults to client name if left blank</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary">Contact Details (Email / Phone)</label>
                                    <input type="text" class="form-control form-control-custom" id="deal_contact_details" name="deal_contact_details" placeholder="Auto-populated from Client Contact" value="{{ old('deal_contact_details') }}">
                                    <small class="text-muted">Defaults to client email/mobile if left blank</small>
                                </div>
                            </div>
                        </div>

                        <!-- Deal Configuration Card -->
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light bg-opacity-50 h-100">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="fas fa-sliders-h me-1"></i> Deal Configuration
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary">Deal Stage</label>
                                    <select class="form-select form-control-custom" id="deal_stage_id" name="deal_stage_id">
                                        <option value="">Select Stage</option>
                                        @foreach($dealStages as $stage)
                                            <option value="{{ $stage->id }}" data-color="{{ $stage->color }}" {{ old('deal_stage_id') == $stage->id ? 'selected' : '' }}>
                                                {{ $stage->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary">Deal Category</label>
                                    <select class="form-select form-control-custom" id="deal_category_id" name="deal_category_id">
                                        <option value="">Select Category</option>
                                        @foreach($dealCategories as $dcat)
                                            <option value="{{ $dcat->id }}" {{ old('deal_category_id') == $dcat->id ? 'selected' : '' }}>{{ $dcat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary">Deal Agent</label>
                                    <select class="form-select form-control-custom" id="deal_agent_id" name="deal_agent_id">
                                        <option value="">Select Agent</option>
                                        @foreach($dealAgents as $agent)
                                            <option value="{{ $agent->id }}" {{ old('deal_agent_id', auth()->id()) == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Product & Pipeline Card -->
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light bg-opacity-50 h-100">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="fas fa-stream me-1"></i> Product & Pipeline
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary">Pipeline</label>
                                    <select class="form-select form-control-custom" id="deal_pipeline" name="deal_pipeline">
                                        <option value="Sales Pipeline" selected>Sales Pipeline</option>
                                        <option value="Marketing Pipeline">Marketing Pipeline</option>
                                        <option value="Other Pipeline">Other Pipeline</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary">Product / Service</label>
                                    <select class="form-select form-control-custom" id="deal_product" name="deal_product">
                                        <option value="">Select Product</option>
                                        <option value="Project Management Software" selected>Project Management Software</option>
                                        <option value="Custom Website Development">Custom Website Development</option>
                                        <option value="Mobile App Development">Mobile App Development</option>
                                        <option value="Cloud & DevOps Services">Cloud & DevOps Services</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Deal Notes -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-secondary">Deal Notes</label>
                            <textarea class="form-control form-control-custom" id="deal_notes" name="deal_notes" rows="3" placeholder="Enter any additional deal notes, terms, or expectations...">{{ old('deal_notes') }}</textarea>
                        </div>

                    </div>
                </div>

                <div class="card-footer bg-white border-top p-4 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill prev-step-btn" onclick="goToStep(3)">
                        <i class="fas fa-arrow-left me-2"></i> Previous
                    </button>
                    <button type="button" class="btn btn-success btn-lg px-5 py-2 rounded-pill shadow" id="finalSubmitBtn">
                        <i class="fas fa-check-circle me-2"></i> Final Submit
                    </button>
                </div>
            </div>
        </div>

    </form>

    <!-- ==========================================
         MODALS (AJAX Category Creation)
         ========================================== -->
    <!-- Add Client Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="addCategoryForm" method="POST">
                @csrf
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold">Add Client Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="categoryName" class="form-control form-control-custom" placeholder="Enter category name" autocomplete="off" required>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Add Category</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Client Sub-Category Modal -->
    <div class="modal fade" id="addSubCategoryModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="addSubCategoryForm" method="POST">
                @csrf
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold">Add Client Sub Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Sub Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="subcategoryName" class="form-control form-control-custom" placeholder="Enter subcategory name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Parent Category <span class="text-danger">*</span></label>
                            <select name="client_category_id" class="form-select form-control-custom" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Add Sub Category</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Project Category Modal -->
    <div class="modal fade" id="addProjectCategoryModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="addProjectCategoryForm" method="POST">
                @csrf
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold">Add Project Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <label class="form-label fw-semibold">Project Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" id="projectCategoryName" class="form-control form-control-custom" placeholder="e.g. Web Development" autocomplete="off" required>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Add Category</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Wizard Styles -->
<style>
/* Custom form controls matching the images */
.form-control-custom {
    border-radius: 8px !important;
    border: 1px solid #e2e8f0;
    padding: 0.6rem 0.85rem;
    font-size: 0.925rem;
    transition: all 0.2s ease;
}
.form-control-custom:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.step-header-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(59, 130, 246, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Stepper styles */
.wizard-stepper {
    position: relative;
    padding: 10px 0;
}
.stepper-progress {
    position: absolute;
    top: 32px;
    left: 8%;
    right: 8%;
    height: 4px;
    background: #e2e8f0;
    z-index: 1;
    border-radius: 4px;
}
.stepper-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #059669);
    border-radius: 4px;
    transition: width 0.4s ease;
}
.stepper-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    z-index: 2;
}
.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    text-align: center;
    width: 25%;
}
.step-circle {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: #ffffff;
    border: 3px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #64748b;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
.step-item .step-check {
    display: none;
}
.step-label {
    margin-top: 10px;
    display: flex;
    flex-direction: column;
}
.step-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #475569;
    transition: color 0.3s ease;
}
.step-subtitle {
    font-size: 0.75rem;
    color: #94a3b8;
}

/* Active Step */
.step-item.active .step-circle {
    background: #0f766e;
    border-color: #0f766e;
    color: #ffffff;
    box-shadow: 0 0 0 5px rgba(15, 118, 110, 0.2);
}
.step-item.active .step-title {
    color: #0f766e;
    font-weight: 700;
}

/* Completed Step */
.step-item.completed .step-circle {
    background: #10b981;
    border-color: #10b981;
    color: #ffffff;
}
.step-item.completed .step-number {
    display: none;
}
.step-item.completed .step-check {
    display: inline-block;
}
.step-item.completed .step-title {
    color: #10b981;
}

/* Radio button style */
.custom-radio:checked {
    background-color: #0f766e;
    border-color: #0f766e;
}

@media (max-width: 768px) {
    .step-subtitle {
        display: none;
    }
    .step-title {
        font-size: 0.75rem;
    }
    .stepper-progress {
        top: 28px;
    }
    .step-circle {
        width: 36px;
        height: 36px;
        font-size: 0.85rem;
    }
}
</style>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
let currentStep = 1;
const totalSteps = 4;

function updateStepperUI(step) {
    // Update progress bar width
    const percent = ((step - 1) / (totalSteps - 1)) * 100;
    $('#stepperProgressBar').css('width', (percent === 0 ? 10 : percent) + '%');

    // Update step circles & labels
    for (let i = 1; i <= totalSteps; i++) {
        const indicator = $('#stepIndicator' + i);
        indicator.removeClass('active completed');
        if (i < step) {
            indicator.addClass('completed');
        } else if (i === step) {
            indicator.addClass('active');
        }
    }
}

function normalizeMobile(val) {
    if (!val) return '';
    val = val.trim().replace(/\s+/g, '');
    if (!val.startsWith('+91')) {
        // If it starts with 91 but no +, add +
        if (val.startsWith('91') && val.length === 12) {
            val = '+' + val;
        } else {
            // Remove leading 0 if any
            val = val.replace(/^0+/, '');
            val = '+91' + val;
        }
    }
    return val;
}

function validateStep(step) {
    let isValid = true;

    if (step === 1) {
        const name = $('#client_name').val().trim();
        const email = $('#client_email').val().trim();
        const password = $('#password').val();
        let mobile = $('#client_mobile').val().trim();
        const country = $('#country').val();

        // Validate Name
        if (!name) {
            $('#client_name').addClass('is-invalid');
            isValid = false;
        } else {
            $('#client_name').removeClass('is-invalid');
        }

        // Validate Email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email || !emailRegex.test(email)) {
            $('#client_email').addClass('is-invalid');
            isValid = false;
        } else {
            $('#client_email').removeClass('is-invalid');
        }

        // Validate Password
        if (!password || password.length < 6) {
            $('#password').addClass('is-invalid');
            isValid = false;
        } else {
            $('#password').removeClass('is-invalid');
        }

        // Validate & Normalize Mobile
        if (mobile && mobile !== '+91') {
            mobile = normalizeMobile(mobile);
            $('#client_mobile').val(mobile);
        }

        const phoneRegex = /^\+91[0-9]{10}$/;
        if (!mobile || !phoneRegex.test(mobile)) {
            $('#client_mobile').addClass('is-invalid');
            isValid = false;
        } else {
            $('#client_mobile').removeClass('is-invalid');
        }

        // Validate Country
        if (!country) {
            $('#country').addClass('is-invalid');
            isValid = false;
        } else {
            $('#country').removeClass('is-invalid');
        }

        if (!isValid) {
            const firstInvalid = $('.wizard-step-panel:visible .is-invalid').first();
            if (firstInvalid.length) {
                firstInvalid.focus();
            }
        }
    }

    if (step === 2) {
        let officePhone = $('#office_phone').val().trim();
        if (officePhone && officePhone !== '+91') {
            officePhone = normalizeMobile(officePhone);
            $('#office_phone').val(officePhone);
            const phoneRegex = /^\+91[0-9]{10}$/;
            if (!phoneRegex.test(officePhone)) {
                $('#office_phone').addClass('is-invalid');
                isValid = false;
                $('#office_phone').focus();
            } else {
                $('#office_phone').removeClass('is-invalid');
            }
        } else {
            $('#office_phone').removeClass('is-invalid');
        }
    }

    return isValid;
}

function goToStep(targetStep) {
    if (targetStep > currentStep) {
        // Validate current step before moving forward
        if (!validateStep(currentStep)) {
            return false;
        }
    }

    // Sync client name and email/mobile to Step 4 deals if empty
    if (targetStep === 4) {
        if (!$('#deal_lead_name').val()) {
            $('#deal_lead_name').val($('#client_name').val());
        }
        if (!$('#deal_contact_details').val()) {
            const contact = $('#client_email').val() || $('#client_mobile').val();
            $('#deal_contact_details').val(contact);
        }
    }

    // Hide all step panels & show target step panel
    $('.wizard-step-panel').addClass('d-none');
    $('#stepPanel' + targetStep).removeClass('d-none');

    currentStep = targetStep;
    updateStepperUI(targetStep);

    // Smooth scroll to top of wizard
    $('html, body').animate({
        scrollTop: $('#clientWizardForm').offset().top - 80
    }, 200);
}

function navigateToStep(targetStep) {
    // Only allow clicking on step indicators if previous steps are valid
    if (targetStep <= currentStep) {
        goToStep(targetStep);
    } else {
        // Must validate all previous steps
        for (let i = 1; i < targetStep; i++) {
            if (!validateStep(i)) {
                goToStep(i);
                return;
            }
        }
        goToStep(targetStep);
    }
}

function toggleProjectCodeInput() {
    if ($('#project_shortcode_manual_opt').is(':checked')) {
        $('#project_shortcode_display').addClass('d-none');
        $('#project_shortcode_manual').removeClass('d-none').focus();
    } else {
        $('#project_shortcode_manual').addClass('d-none');
        $('#project_shortcode_display').removeClass('d-none');
    }
}

function toggleDeadlineInput() {
    if ($('#project_without_deadline').is(':checked')) {
        $('#project_deadline').prop('disabled', true).val('');
    } else {
        $('#project_deadline').prop('disabled', false);
    }
}

$(document).ready(function () {
    // Toggle Show/Hide Password
    $('.toggle-password').on('click', function () {
        const passwordField = $('#password');
        const icon = $(this).find('i');

        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Generate Random Password
    $('.generate-password').on('click', function () {
        const randomPassword = Math.random().toString(36).slice(-8) + '!A9';
        $('#password').val(randomPassword).trigger('input').removeClass('is-invalid');
    });

    // Auto-prefix +91 on mobile input
    $('#client_mobile').on('focus', function () {
        if (!$(this).val() || $(this).val() === '') {
            $(this).val('+91');
        }
    }).on('blur', function() {
        if ($(this).val()) {
            $(this).val(normalizeMobile($(this).val()));
        }
    });

    // Auto-prefix +91 on office phone
    $('#office_phone').on('focus', function () {
        if (!$(this).val() || $(this).val() === '') {
            $(this).val('+91');
        }
    }).on('blur', function() {
        if ($(this).val() && $(this).val() !== '+91') {
            $(this).val(normalizeMobile($(this).val()));
        }
    });

    // Custom formatting for Select2 flags
    function formatOption (state) {
        if (!state.id) return state.text;
        let flag = $(state.element).data("flag");
        if (flag) {
            return $('<span><img src="' + flag + '" width="20" class="me-2 rounded-1"/> ' + state.text + '</span>');
        }
        return state.text;
    }

    // Country dropdown Select2
    $('#country').select2({
        theme: "classic",
        templateResult: formatOption,
        templateSelection: formatOption,
        placeholder: "Select members",
        width: '100%'
    }).on('change', function() {
        if ($(this).val()) {
            $(this).removeClass('is-invalid');
        }
    });

    // Language dropdown Select2
    $('#language').select2({
        theme: "classic",
        templateResult: formatOption,
        templateSelection: formatOption,
        placeholder: "Select Language",
        width: '100%'
    });

    // Multi-select Select2
    $('#project_department_ids, #project_employee_ids').select2({
        width: '100%',
        placeholder: "Select options"
    });

    // Remove is-invalid on user input
    $('input, select, textarea').on('input change', function() {
        $(this).removeClass('is-invalid');
    });

    // AJAX Form: Add Client Category
    $('#addCategoryForm').submit(function(e) {
        e.preventDefault();
        const name = $('#categoryName').val().trim();
        if (!name) return;

        $.ajax({
            type: 'POST',
            url: "{{ route('client-categories.store') }}",
            data: $(this).serialize(),
            success: function(data) {
                $('#client_category_id').append(
                    `<option value="${data.id}" selected>${data.name}</option>`
                );
                $('#addCategoryModal').modal('hide');
                $('#addCategoryForm')[0].reset();
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Failed to add category'));
            }
        });
    });

    // AJAX Form: Add Client Sub-Category
    $('#addSubCategoryForm').submit(function(e) {
        e.preventDefault();
        const name = $('#subcategoryName').val().trim();
        if (!name) return;

        $.ajax({
            type: 'POST',
            url: "{{ route('client-sub-categories.store') }}",
            data: $(this).serialize(),
            success: function(data) {
                $('#client_sub_category_id').append(
                    `<option value="${data.id}" selected>${data.name}</option>`
                );
                $('#addSubCategoryModal').modal('hide');
                $('#addSubCategoryForm')[0].reset();
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Failed to add subcategory'));
            }
        });
    });

    // AJAX Form: Add Project Category
    $('#addProjectCategoryForm').submit(function(e) {
        e.preventDefault();
        const name = $('#projectCategoryName').val().trim();
        if (!name) return;

        $.ajax({
            type: 'POST',
            url: "{{ route('project-categories.store') }}",
            data: $(this).serialize(),
            success: function(data) {
                if (data.id && data.category_name) {
                    $('#project_category_id').append(
                        `<option value="${data.id}" selected>${data.category_name}</option>`
                    );
                }
                $('#addProjectCategoryModal').modal('hide');
                $('#addProjectCategoryForm')[0].reset();
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Failed to add project category'));
            }
        });
    });

    // Stage color preview
    $('#deal_stage_id').on('change', function() {
        const selected = $(this).find('option:selected');
        const color = selected.data('color');
        if (color) {
            $(this).css('border-left', '4px solid ' + color);
        } else {
            $(this).css('border-left', '');
        }
    });

    // Direct Final Submit click handler
    $('#finalSubmitBtn').on('click', function(e) {
        e.preventDefault();

        // Validate Step 1 (Mandatory client fields)
        if (!validateStep(1)) {
            goToStep(1);
            return false;
        }

        // Validate Step 2 (Optional company fields formatting)
        if (!validateStep(2)) {
            goToStep(2);
            return false;
        }

        // Enable deadline if disabled so form doesn't miss it or leave it as is
        $('#project_deadline').prop('disabled', false);

        // Show loading state
        const submitBtn = $(this);
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Submitting...');

        // Submit form directly to server
        document.getElementById('clientWizardForm').submit();
    });
});
</script>

@endsection
