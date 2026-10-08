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
                            <input name="email" id="client_email" type="email" class="form-control form-control-custom @error('email') is-invalid @enderror" placeholder="e.g. admin@bloodlife.com" value="{{ old('email') }}" required>
                            <div class="invalid-feedback">{{ $errors->first('email') ?: 'Please enter a valid email address.' }}</div>
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
                            <small class="form-text text-muted d-block" id="password_help_text">Must be at least 8 characters (1 uppercase, 1 lowercase, 1 number & 1 special char)</small>
                            <div class="invalid-feedback" id="password_feedback">Password must be at least 8 characters with 1 uppercase, 1 lowercase, 1 number, and 1 special character.</div>
                        </div>

                        <!-- Country -->
                        <div class="col-md-5 mb-2">
                            <label class="form-label fw-semibold text-secondary">Country <sup class="text-danger">*</sup></label>
                            <select name="country" id="country" class="form-select form-control-custom select2" required>
                                <option value="">Select country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->name }}" 
                                            data-flag="{{ $country->flag_url }}"
                                            data-dial-code="{{ $country->phone_code ?? '+91' }}"
                                            data-min-digits="{{ $country->min_digits ?? 10 }}"
                                            data-max-digits="{{ $country->max_digits ?? 10 }}"
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
                            <div class="input-group">
                                <select name="mobile_country_code" id="mobile_country_code" class="form-select form-control-custom" style="max-width: 115px; flex-shrink: 0;">
                                    @php $selMobCode = old('mobile_country_code', '+91'); @endphp
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->phone_code }}" data-country="{{ $country->name }}" data-min-digits="{{ $country->min_digits ?? 10 }}" data-max-digits="{{ $country->max_digits ?? 10 }}" {{ $selMobCode == $country->phone_code ? 'selected' : '' }}>
                                            {{ $country->iso_code ? $country->iso_code . ' ' : '' }}({{ $country->phone_code }})
                                        </option>
                                    @endforeach
                                </select>
                                <input name="mobile" id="client_mobile" type="text" class="form-control form-control-custom" placeholder="e.g. +919876543210" value="{{ old('mobile', '+91') }}" required>
                            </div>
                            <div class="invalid-feedback" id="mobile_feedback">Please enter a valid 10-digit mobile number starting with +91.</div>
                            <small class="text-muted d-block" id="mobile_format_hint">Format: +91XXXXXXXXXX (10 digits)</small>
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
                                <button type="button" class="btn btn-outline-secondary flex-shrink-0 text-nowrap px-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal" title="Add Category" style="white-space: nowrap; min-width: 75px;"><i class="fas fa-plus me-1"></i> Add</button>
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
                                <button type="button" class="btn btn-outline-secondary flex-shrink-0 text-nowrap px-3" data-bs-toggle="modal" data-bs-target="#addSubCategoryModal" title="Add Sub Category" style="white-space: nowrap; min-width: 75px;"><i class="fas fa-plus me-1"></i> Add</button>
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
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Tax Name</label>
                            <input name="tax_name" id="tax_name" type="text" class="form-control form-control-custom" placeholder="e.g. GST/VAT" value="{{ old('tax_name') }}">
                        </div>

                        <!-- GST/VAT Number -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">GST/VAT Number</label>
                            <input name="tax_number" id="tax_number" type="text" class="form-control form-control-custom" placeholder="e.g. 18AABCU960XXXXX" value="{{ old('tax_number') }}">
                        </div>

                        <!-- Company Country -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Country</label>
                            <select name="company_country" id="company_country" class="form-select form-control-custom select2">
                                <option value="">Select country</option>
                                @foreach($countries as $c)
                                    <option value="{{ $c->name }}" 
                                            data-flag="{{ $c->flag_url }}"
                                            data-dial-code="{{ $c->phone_code ?? '+91' }}"
                                            data-min-digits="{{ $c->min_digits ?? 10 }}"
                                            data-max-digits="{{ $c->max_digits ?? 10 }}"
                                            {{ old('company_country', old('country', 'India')) == $c->name ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Office Phone -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Office Phone</label>
                            <div class="input-group">
                                <select name="office_country_code" id="office_country_code" class="form-select form-control-custom" style="max-width: 115px; flex-shrink: 0;">
                                    @php $selOffCode = old('office_country_code', '+91'); @endphp
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->phone_code }}" data-country="{{ $country->name }}" data-min-digits="{{ $country->min_digits ?? 10 }}" data-max-digits="{{ $country->max_digits ?? 10 }}" {{ $selOffCode == $country->phone_code ? 'selected' : '' }}>
                                            {{ $country->iso_code ? $country->iso_code . ' ' : '' }}({{ $country->phone_code }})
                                        </option>
                                    @endforeach
                                </select>
                                <input name="office_phone" id="office_phone" type="text" class="form-control form-control-custom" placeholder="e.g. +919876543210" value="{{ old('office_phone') }}">
                            </div>
                            <div class="invalid-feedback" id="office_phone_feedback"></div>
                            <small class="text-muted d-block" id="office_phone_format_hint">Format: +91XXXXXXXXXX (10 digits)</small>
                        </div>

                        <!-- Postal Code / Pincode -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary d-flex align-items-center justify-content-between">
                                <span>Postal Code / Pincode</span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 10px; font-weight: normal;">
                                    <i class="fas fa-magic me-1"></i>Auto-detects Area, City & State
                                </span>
                            </label>
                            <div class="position-relative">
                                <input name="postal_code" id="postal_code" type="text" class="form-control form-control-custom pe-5" placeholder="e.g. 700001 or 110001" value="{{ old('postal_code') }}" autocomplete="off">
                                <div id="pincode_spinner" class="position-absolute end-0 top-50 translate-middle-y me-3" style="display: none; pointer-events: none;">
                                    <i class="fas fa-spinner fa-spin text-primary"></i>
                                </div>
                            </div>
                            <div id="pincode_feedback_pill" class="mt-1" style="display: none;"></div>
                        </div>

                        <!-- State -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">State / Province</label>
                            <select name="state" id="state" class="form-select form-control-custom select2">
                                <option value="">Select state/province</option>
                            </select>
                        </div>

                        <!-- City -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">City</label>
                            <select name="city" id="city" class="form-select form-control-custom select2">
                                <option value="">Select city</option>
                            </select>
                        </div>

                        <!-- Detected Area / Locality Selector (Auto-shown when pincode has multiple areas) -->
                        <div class="col-md-12" id="area_selection_wrapper" style="display: none;">
                            <div class="p-3 rounded-3" style="background: rgba(37, 99, 235, 0.05); border: 1px solid rgba(37, 99, 235, 0.2);">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label fw-semibold text-primary mb-0">
                                        <i class="fas fa-map-marker-alt me-1"></i> Auto-Detected Areas / Localities for this Pincode
                                    </label>
                                    <span class="badge bg-primary text-white" id="area_count_badge">0 areas</span>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <select id="detected_area_select" class="form-select form-control-custom">
                                            <option value="">-- Choose specific area / post office --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i> Selecting an area automatically populates your Company Address below.
                                        </small>
                                    </div>
                                </div>
                            </div>
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
                            <div class="invalid-feedback" id="project_deadline_feedback">Deadline must be later than the start date.</div>
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
                                <button type="button" class="btn btn-outline-secondary flex-shrink-0 text-nowrap px-3" data-bs-toggle="modal" data-bs-target="#addProjectCategoryModal" title="Add Project Category" style="white-space: nowrap; min-width: 75px;"><i class="fas fa-plus me-1"></i> Add</button>
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
                                    <label class="form-label fw-semibold text-secondary" id="deal_value_label">Deal Value (<span id="deal_currency_symbol_label">{{ ($currencies ?? collect())->where('currency_code', old('deal_currency', 'INR'))->first()?->currency_symbol ?? '₹' }}</span>)</label>
                                    <div class="input-group deal-value-input-group">
                                        <select name="deal_currency" id="deal_currency" class="form-select form-control-custom flex-grow-0" style="width: auto; min-width: 120px; max-width: 145px; border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important; font-weight: 600;" aria-label="Deal Currency">
                                            @foreach($currencies ?? [] as $curr)
                                                @php
                                                    $displaySym = ($curr->currency_symbol && $curr->currency_symbol !== $curr->currency_code) ? $curr->currency_symbol . ' ' : '';
                                                @endphp
                                                <option value="{{ $curr->currency_code }}" 
                                                        data-symbol="{{ $curr->currency_symbol }}"
                                                        {{ (old('deal_currency', 'INR') == $curr->currency_code) ? 'selected' : '' }}>
                                                    {{ $displaySym }}{{ $curr->currency_code }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="number" step="0.01" min="0" class="form-control form-control-custom" id="deal_value" name="deal_value" placeholder="0.00" value="{{ old('deal_value') }}" style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
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
                                        <div class="invalid-feedback" id="deal_next_follow_up_feedback">Next follow-up date must be later than the close date.</div>
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
    background: linear-gradient(90deg, #10b981, #2F6BFF);
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
    color: #475569 !important;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
.step-circle .step-number {
    color: #475569 !important;
    font-weight: 700;
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

/* Dark Mode Stepper Overrides */
html[data-pms-theme="dark"] .step-circle,
html[data-theme="dark"] .step-circle,
html[data-bs-theme="dark"] .step-circle,
[data-pms-theme="dark"] .step-circle,
[data-theme="dark"] .step-circle,
[data-bs-theme="dark"] .step-circle,
.dark-mode .step-circle {
    background: #141B3D !important;
    border-color: #334155 !important;
    color: #E2E8F0 !important;
}

html[data-pms-theme="dark"] .step-circle .step-number,
html[data-theme="dark"] .step-circle .step-number,
html[data-bs-theme="dark"] .step-circle .step-number,
[data-pms-theme="dark"] .step-circle .step-number,
[data-theme="dark"] .step-circle .step-number,
[data-bs-theme="dark"] .step-circle .step-number,
.dark-mode .step-circle .step-number {
    color: #E2E8F0 !important;
}

html[data-pms-theme="dark"] .step-title,
html[data-theme="dark"] .step-title,
html[data-bs-theme="dark"] .step-title,
[data-pms-theme="dark"] .step-title,
[data-theme="dark"] .step-title,
[data-bs-theme="dark"] .step-title,
.dark-mode .step-title {
    color: #CBD5E1 !important;
}

/* Active Step */
.step-item.active .step-circle,
html[data-pms-theme="dark"] .step-item.active .step-circle,
[data-pms-theme="dark"] .step-item.active .step-circle {
    background: #0f766e !important;
    border-color: #0f766e !important;
    color: #ffffff !important;
    box-shadow: 0 0 0 5px rgba(15, 118, 110, 0.25) !important;
}
.step-item.active .step-circle .step-number,
html[data-pms-theme="dark"] .step-item.active .step-circle .step-number {
    color: #ffffff !important;
}
.step-item.active .step-title,
html[data-pms-theme="dark"] .step-item.active .step-title {
    color: #0f766e !important;
    font-weight: 700;
}

/* Completed Step */
.step-item.completed .step-circle,
html[data-pms-theme="dark"] .step-item.completed .step-circle,
[data-pms-theme="dark"] .step-item.completed .step-circle {
    background: #10b981 !important;
    border-color: #10b981 !important;
    color: #ffffff !important;
}
.step-item.completed .step-number {
    display: none;
}
.step-item.completed .step-check {
    display: inline-block;
}
.step-item.completed .step-title,
html[data-pms-theme="dark"] .step-item.completed .step-title {
    color: #10b981 !important;
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

/* Select2 Dark Mode & Visibility Fixes */
.select2-container .select2-selection--single,
.select2-container--classic .select2-selection--single,
.select2-container--bootstrap-5 .select2-selection--single,
.select2-container--default .select2-selection--single {
    background-color: var(--bx-surface, #ffffff) !important;
    background-image: none !important;
    border: 1px solid var(--bx-border-strong, #e2e8f0) !important;
    border-radius: 8px !important;
    height: 42px !important;
    display: flex !important;
    align-items: center !important;
    color: var(--bx-ink, #1e293b) !important;
}

.select2-container .select2-selection__rendered,
.select2-container--classic .select2-selection--single .select2-selection__rendered,
.select2-container--bootstrap-5 .select2-selection__rendered,
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: var(--bx-ink, #1e293b) !important;
    line-height: 40px !important;
    padding-left: 0.75rem !important;
    padding-right: 2rem !important;
}

.select2-container .select2-selection__arrow,
.select2-container--classic .select2-selection--single .select2-selection__arrow,
.select2-container--bootstrap-5 .select2-selection__arrow,
.select2-container--default .select2-selection--single .select2-selection__arrow {
    background: transparent !important;
    background-image: none !important;
    border: none !important;
    height: 40px !important;
    top: 0 !important;
    right: 8px !important;
}

.select2-container--classic .select2-selection--single .select2-selection__arrow b,
.select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: var(--bx-ink-muted, #64748b) transparent transparent transparent !important;
}

html[data-pms-theme="dark"] .select2-container .select2-selection--single,
html[data-theme="dark"] .select2-container .select2-selection--single,
html[data-bs-theme="dark"] .select2-container .select2-selection--single,
[data-pms-theme="dark"] .select2-container .select2-selection--single,
[data-theme="dark"] .select2-container .select2-selection--single,
[data-bs-theme="dark"] .select2-container .select2-selection--single,
.dark-mode .select2-container .select2-selection--single {
    background-color: var(--bx-surface, #0F1530) !important;
    border-color: var(--bx-border-strong, rgba(238, 241, 251, 0.15)) !important;
    color: #EEF1FB !important;
}

html[data-pms-theme="dark"] .select2-container .select2-selection__rendered,
html[data-theme="dark"] .select2-container .select2-selection__rendered,
html[data-bs-theme="dark"] .select2-container .select2-selection__rendered,
[data-pms-theme="dark"] .select2-container .select2-selection__rendered,
[data-theme="dark"] .select2-container .select2-selection__rendered,
[data-bs-theme="dark"] .select2-container .select2-selection__rendered,
.dark-mode .select2-container .select2-selection__rendered {
    color: #EEF1FB !important;
}

html[data-pms-theme="dark"] .select2-dropdown,
html[data-theme="dark"] .select2-dropdown,
html[data-bs-theme="dark"] .select2-dropdown,
[data-pms-theme="dark"] .select2-dropdown,
[data-theme="dark"] .select2-dropdown,
[data-bs-theme="dark"] .select2-dropdown,
.dark-mode .select2-dropdown {
    background-color: #0F1530 !important;
    border-color: rgba(238, 241, 251, 0.15) !important;
    color: #EEF1FB !important;
}

html[data-pms-theme="dark"] .select2-results__option,
html[data-theme="dark"] .select2-results__option,
html[data-bs-theme="dark"] .select2-results__option,
[data-pms-theme="dark"] .select2-results__option,
[data-theme="dark"] .select2-results__option,
[data-bs-theme="dark"] .select2-results__option,
.dark-mode .select2-results__option {
    background-color: #0F1530 !important;
    color: #EEF1FB !important;
}

html[data-pms-theme="dark"] .select2-results__option--highlighted,
html[data-theme="dark"] .select2-results__option--highlighted,
html[data-bs-theme="dark"] .select2-results__option--highlighted,
[data-pms-theme="dark"] .select2-results__option--highlighted,
[data-theme="dark"] .select2-results__option--highlighted,
[data-bs-theme="dark"] .select2-results__option--highlighted,
.dark-mode .select2-results__option--highlighted {
    background-color: #2F6BFF !important;
    color: #FFFFFF !important;
}

html[data-pms-theme="dark"] .select2-search--dropdown .select2-search__field,
html[data-theme="dark"] .select2-search--dropdown .select2-search__field,
html[data-bs-theme="dark"] .select2-search--dropdown .select2-search__field,
[data-pms-theme="dark"] .select2-search--dropdown .select2-search__field,
[data-theme="dark"] .select2-search--dropdown .select2-search__field,
[data-bs-theme="dark"] .select2-search--dropdown .select2-search__field,
.dark-mode .select2-search--dropdown .select2-search__field {
    background-color: #141B3D !important;
    border-color: rgba(238, 241, 251, 0.15) !important;
    color: #EEF1FB !important;
}

/* Deal Currency Selector in Input Group */
.deal-value-input-group {
    display: flex;
    flex-wrap: nowrap;
}
.deal-value-input-group #deal_currency {
    cursor: pointer;
    background-color: var(--bx-surface, #ffffff);
    color: var(--bx-ink, #1e293b);
    border-color: #e2e8f0;
}
.deal-value-input-group #deal_currency:focus {
    z-index: 3;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}
.deal-value-input-group #deal_value:focus {
    z-index: 3;
}

/* Dark Mode Overrides for Deal Currency */
html[data-pms-theme="dark"] .deal-value-input-group #deal_currency,
html[data-theme="dark"] .deal-value-input-group #deal_currency,
html[data-bs-theme="dark"] .deal-value-input-group #deal_currency,
[data-pms-theme="dark"] .deal-value-input-group #deal_currency,
[data-theme="dark"] .deal-value-input-group #deal_currency,
[data-bs-theme="dark"] .deal-value-input-group #deal_currency,
.dark-mode .deal-value-input-group #deal_currency {
    background-color: #141B3D !important;
    border-color: rgba(238, 241, 251, 0.15) !important;
    color: #EEF1FB !important;
}

html[data-pms-theme="dark"] .deal-value-input-group #deal_currency option,
html[data-theme="dark"] .deal-value-input-group #deal_currency option,
html[data-bs-theme="dark"] .deal-value-input-group #deal_currency option,
[data-pms-theme="dark"] .deal-value-input-group #deal_currency option,
[data-theme="dark"] .deal-value-input-group #deal_currency option,
[data-bs-theme="dark"] .deal-value-input-group #deal_currency option,
.dark-mode .deal-value-input-group #deal_currency option {
    background-color: #0F1530 !important;
    color: #EEF1FB !important;
}
</style>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
window.locationData = @json(\App\Support\LocationData::data());
let currentStep = 1;
const totalSteps = 4;
let dealCurrencyManuallyChanged = {{ old('deal_currency') ? 'true' : 'false' }};

const countryToCurrencyMap = {
    'India': 'INR',
    'United States': 'USD',
    'United Kingdom': 'GBP',
    'Canada': 'CAD',
    'Australia': 'AUD',
    'Bangladesh': 'BDT',
    'United Arab Emirates': 'AED',
    'Saudi Arabia': 'SAR',
    'Japan': 'JPY',
    'Switzerland': 'CHF',
    'South Africa': 'ZAR',
    'New Zealand': 'NZD',
    'Singapore': 'SGD',
    'Germany': 'EUR',
    'France': 'EUR',
    'Italy': 'EUR',
    'Spain': 'EUR',
    'Netherlands': 'EUR',
    'Ireland': 'EUR',
    'Portugal': 'EUR',
    'Greece': 'EUR',
    'Austria': 'EUR',
    'Belgium': 'EUR',
    'Finland': 'EUR'
};

function updateDealCurrencyLabel() {
    const $selected = $('#deal_currency option:selected');
    const symbol = $selected.data('symbol') || $selected.val() || '₹';
    $('#deal_currency_symbol_label').text(symbol);
}

function syncDealCurrencyWithCountry(countryName) {
    if (dealCurrencyManuallyChanged || !countryName) return;
    const mappedCurrency = countryToCurrencyMap[countryName];
    if (mappedCurrency && $('#deal_currency').find(`option[value="${mappedCurrency}"]`).length) {
        $('#deal_currency').val(mappedCurrency);
        updateDealCurrencyLabel();
    }
}

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

function getSelectedCountryPhoneRules() {
    const $mOpt = $('#mobile_country_code option:selected');
    const dialCode = $mOpt.val() || $('#country option:selected').data('dial-code') || '+91';
    const minDigits = parseInt($mOpt.data('min-digits')) || parseInt($('#country option:selected').data('min-digits')) || 10;
    const maxDigits = parseInt($mOpt.data('max-digits')) || parseInt($('#country option:selected').data('max-digits')) || 10;
    const countryName = $mOpt.data('country') || $('#country').val() || 'India';
    return { dialCode, minDigits, maxDigits, countryName };
}

function getCompanyCountryPhoneRules() {
    const $oOpt = $('#office_country_code option:selected');
    let dialCode = $oOpt.val() || $('#company_country option:selected').data('dial-code');
    let minDigits = parseInt($oOpt.data('min-digits')) || parseInt($('#company_country option:selected').data('min-digits'));
    let maxDigits = parseInt($oOpt.data('max-digits')) || parseInt($('#company_country option:selected').data('max-digits'));
    let countryName = $oOpt.data('country') || $('#company_country').val();

    if (!dialCode) {
        return getSelectedCountryPhoneRules();
    }
    return {
        dialCode: dialCode || '+91',
        minDigits: minDigits || 10,
        maxDigits: maxDigits || 10,
        countryName: countryName || 'India'
    };
}

function updateMobileFormatHint(rules) {
    const { dialCode, minDigits, maxDigits } = rules || getSelectedCountryPhoneRules();
    let helpMsg = minDigits === maxDigits
        ? `Format: ${dialCode}XXXXXXXXXX (${minDigits} digits)`
        : `Format: ${dialCode}XXXXXXXXXX (${minDigits}-${maxDigits} digits)`;
    $('#mobile_format_hint').text(helpMsg);

    const totalMaxLen = dialCode.length + maxDigits;
    $('#client_mobile').attr({
        'placeholder': 'e.g. ' + dialCode + '9876543210',
        'maxlength': totalMaxLen
    });
}

function updateOfficePhoneFormatHint(rules) {
    const { dialCode, minDigits, maxDigits } = rules || getCompanyCountryPhoneRules();
    let helpMsg = minDigits === maxDigits
        ? `Format: ${dialCode}XXXXXXXXXX (${minDigits} digits)`
        : `Format: ${dialCode}XXXXXXXXXX (${minDigits}-${maxDigits} digits)`;
    if ($('#office_phone_format_hint').length) {
        $('#office_phone_format_hint').text(helpMsg);
    }

    const totalMaxLen = dialCode.length + maxDigits;
    $('#office_phone').attr({
        'placeholder': 'e.g. ' + dialCode + '9876543210',
        'maxlength': totalMaxLen
    });
}

function populateStates(country, selectedState = null) {
    const $state = $('#state');
    if (!$state.length) return;

    $state.empty();
    $state.append(new Option('Select state/province', '', true, false));

    const countryData = window.locationData && window.locationData[country] ? window.locationData[country] : null;
    let statesList = [];
    if (countryData) {
        statesList = Object.keys(countryData);
    }

    if (selectedState && !statesList.includes(selectedState)) {
        statesList.push(selectedState);
    }

    statesList.forEach(st => {
        const isSelected = selectedState && selectedState.toLowerCase() === st.toLowerCase();
        const option = new Option(st, st, isSelected, isSelected);
        $state.append(option);
    });

    $state.trigger('change.select2');
}

function populateCities(country, state, selectedCity = null) {
    const $city = $('#city');
    if (!$city.length) return;

    $city.empty();
    $city.append(new Option('Select city', '', true, false));

    const countryData = window.locationData && window.locationData[country] ? window.locationData[country] : null;
    let citiesList = [];

    if (countryData) {
        if (state && countryData[state]) {
            citiesList = countryData[state];
        } else {
            const set = new Set();
            Object.values(countryData).forEach(arr => {
                if (Array.isArray(arr)) {
                    arr.forEach(c => set.add(c));
                }
            });
            citiesList = Array.from(set);
        }
    }

    if (selectedCity && !citiesList.includes(selectedCity)) {
        citiesList.push(selectedCity);
    }

    citiesList.forEach(ct => {
        const isSelected = selectedCity && selectedCity.toLowerCase() === ct.toLowerCase();
        const option = new Option(ct, ct, isSelected, isSelected);
        $city.append(option);
    });

    $city.trigger('change.select2');
}

let pincodeLookupTimer = null;
let currentPincodeAjax = null;

function showPincodeNotFound() {
    $('#pincode_spinner').hide();
    $('#pincode_feedback_pill').html(`
        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 11px;">
            <i class="fas fa-times-circle me-1"></i> No data found
        </span>
    `).show();
    $('#area_selection_wrapper').slideUp(150);
}

function performPincodeLookup(pincode) {
    pincode = (pincode || '').trim().replace(/\s+/g, '');
    const country = $('#company_country').val() || $('#country').val() || 'India';
    const isIndia = !country || country.toLowerCase() === 'india';

    // Abort previous in-flight request if user is typing
    if (currentPincodeAjax && typeof currentPincodeAjax.abort === 'function') {
        currentPincodeAjax.abort();
        currentPincodeAjax = null;
    }

    if (!pincode) {
        $('#pincode_spinner').hide();
        $('#pincode_feedback_pill').hide();
        $('#area_selection_wrapper').slideUp(150);
        return;
    }

    // India pincodes must be 6 digits and start with 1-9
    if (isIndia) {
        if (pincode.length !== 6 || !/^[1-9]\d{5}$/.test(pincode)) {
            showPincodeNotFound();
            return;
        }
    } else {
        if (pincode.length < 3) {
            showPincodeNotFound();
            return;
        }
    }

    $('#pincode_spinner').show();
    $('#pincode_feedback_pill').html('<span class="text-primary small"><i class="fas fa-spinner fa-spin me-1"></i> Checking location...</span>').show();

    const lookupUrl = "{{ Route::has('clients.lookup-pincode') ? route('clients.lookup-pincode') : url('/clients/lookup-pincode') }}";
    
    currentPincodeAjax = $.ajax({
        url: lookupUrl,
        type: 'GET',
        data: { pincode: pincode, country: country },
        timeout: 3500,
        success: function(res) {
            if (res && res.success) {
                applyPincodeLocationData(res, country);
            } else {
                showPincodeNotFound();
            }
        },
        error: function(xhr, status) {
            if (status === 'abort') {
                $('#pincode_spinner').hide();
                return;
            }
            showPincodeNotFound();
        },
        complete: function() {
            $('#pincode_spinner').hide();
        }
    });
}

function applyPincodeLocationData(data, country) {
    const detectedState = data.state;
    const detectedCity = data.city || data.district;
    const areas = data.areas || [];

    // 1. Ensure State is in dropdown and selected
    if (detectedState) {
        let stateFound = false;
        $('#state option').each(function() {
            if ($(this).val().toLowerCase() === detectedState.toLowerCase()) {
                $('#state').val($(this).val()).trigger('change.select2');
                stateFound = true;
                return false;
            }
        });

        if (!stateFound) {
            const newOpt = new Option(detectedState, detectedState, true, true);
            $('#state').append(newOpt).trigger('change.select2');
        }

        // Populate cities for this state
        populateCities(country, detectedState, detectedCity);
    }

    // 2. Ensure City is in dropdown and selected
    if (detectedCity) {
        setTimeout(function() {
            let cityFound = false;
            $('#city option').each(function() {
                if ($(this).val().toLowerCase() === detectedCity.toLowerCase()) {
                    $('#city').val($(this).val()).trigger('change.select2');
                    cityFound = true;
                    return false;
                }
            });

            if (!cityFound) {
                const newOpt = new Option(detectedCity, detectedCity, true, true);
                $('#city').append(newOpt).trigger('change.select2');
            }
        }, 100);
    }

    // 3. Populate Areas in Detected Area Selector
    const $areaSelect = $('#detected_area_select');
    $areaSelect.empty();
    $areaSelect.append(new Option('-- Choose specific area / post office --', '', true, true));

    if (areas.length > 0) {
        areas.forEach(function(area) {
            $areaSelect.append(new Option(area, area));
        });
        $('#area_count_badge').text(areas.length + ' found');
        $('#area_selection_wrapper').slideDown(200);

        if ($areaSelect.hasClass('select2-hidden-accessible')) {
            $areaSelect.select2('destroy');
        }
        $areaSelect.select2({
            theme: "classic",
            width: '100%',
            placeholder: "-- Choose specific area / post office --"
        });
    } else {
        $('#area_selection_wrapper').slideUp(150);
    }

    // 4. Update feedback pill
    const areaSummary = areas.length ? ` (${areas.length} areas found)` : '';
    $('#pincode_feedback_pill').html(`
        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
            <i class="fas fa-check-circle me-1"></i> Auto-detected: ${detectedCity ? detectedCity + ', ' : ''}${detectedState}${areaSummary}
        </span>
    `).show();
}

function sanitizePhoneNumber(inputElement) {
    if (!inputElement) return '';
    const $input = $(inputElement);
    const isOffice = $input.attr('id') === 'office_phone';
    const rules = isOffice ? getCompanyCountryPhoneRules() : getSelectedCountryPhoneRules();
    const dialCode = rules.dialCode;
    const maxDigits = rules.maxDigits;

    let val = $input.val();
    if (!val) return '';

    let nationalPart = '';
    if (val.startsWith(dialCode)) {
        nationalPart = val.substring(dialCode.length);
    } else if (val.startsWith('+')) {
        nationalPart = val.replace(/^\+\d*/, '');
    } else {
        nationalPart = val;
    }

    // Strip leading 0 if present and total digits exceed maxDigits
    if (nationalPart.startsWith('0') && nationalPart.replace(/\D/g, '').length > maxDigits) {
        nationalPart = nationalPart.replace(/^0+/, '');
    }

    // STRICT: ONLY DIGITS ALLOWED - strip any text, spaces, or symbols
    let cleanDigits = nationalPart.replace(/\D/g, '');

    // Limit to maxDigits
    if (cleanDigits.length > maxDigits) {
        cleanDigits = cleanDigits.substring(0, maxDigits);
    }

    const newVal = dialCode + cleanDigits;
    if ($input.val() !== newVal) {
        $input.val(newVal);
    }

    return cleanDigits;
}

function handlePhoneKeyDown(e, inputElement) {
    const isOffice = $(inputElement).attr('id') === 'office_phone';
    const rules = isOffice ? getCompanyCountryPhoneRules() : getSelectedCountryPhoneRules();
    const dialCode = rules.dialCode;
    const maxDigits = rules.maxDigits;

    // Allow navigation/control keys: Backspace (8), Tab (9), Enter (13), Esc (27), Delete (46)
    if ([8, 9, 13, 27, 46].indexOf(e.keyCode) !== -1 ||
        // Allow: Ctrl/Cmd + A, C, V, X, Z
        ((e.ctrlKey || e.metaKey) && [65, 67, 86, 88, 90].indexOf(e.keyCode) !== -1) ||
        // Allow: Home, End, Left, Right, Up, Down (35-40)
        (e.keyCode >= 35 && e.keyCode <= 40)) {

        // Prevent deleting dial code prefix with backspace
        if (e.keyCode === 8) {
            const start = inputElement.selectionStart;
            const end = inputElement.selectionEnd;
            if (start <= dialCode.length && end <= dialCode.length) {
                e.preventDefault();
            }
        }
        return;
    }

    // Digits only: regular keys 0-9 (48-57, without shift) or numpad 0-9 (96-105)
    const isDigit = (!e.shiftKey && e.keyCode >= 48 && e.keyCode <= 57) ||
                    (e.keyCode >= 96 && e.keyCode <= 105);

    // BLOCK all non-digit keys (letters, symbols, punctuation)
    if (!isDigit) {
        e.preventDefault();
        return;
    }

    // If max digits reached, prevent typing more digits unless replacing selected text
    const curVal = $(inputElement).val();
    let currentDigits = '';
    if (curVal.startsWith(dialCode)) {
        currentDigits = curVal.substring(dialCode.length).replace(/\D/g, '');
    } else {
        currentDigits = curVal.replace(/^\+\d*/, '').replace(/\D/g, '');
    }

    if (inputElement.selectionStart === inputElement.selectionEnd && currentDigits.length >= maxDigits) {
        e.preventDefault();
    }
}

function validateMobileInput(inputId = 'client_mobile', isRequired = true) {
    const isOffice = inputId === 'office_phone';
    const rules = isOffice ? getCompanyCountryPhoneRules() : getSelectedCountryPhoneRules();
    const { dialCode, minDigits, maxDigits, countryName } = rules;
    const $input = $('#' + inputId);
    if (!$input.length) return true;

    const $feedback = inputId === 'client_mobile' ? $('#mobile_feedback') : $('#' + inputId + '_feedback');
    const fieldLabel = inputId === 'office_phone' ? 'Office phone' : 'Mobile number';

    // Run sanitization first to guarantee NO letters exist in input
    const cleanDigits = sanitizePhoneNumber($input[0]);
    const val = $input.val().trim();

    if (!val || val === dialCode || val === '+') {
        if (isRequired) {
            $input.addClass('is-invalid').removeClass('is-valid');
            if ($feedback.length) {
                $feedback.text(`Please enter ${fieldLabel.toLowerCase()}.`).show();
            }
            return false;
        } else {
            $input.removeClass('is-invalid is-valid');
            if ($feedback.length) {
                $feedback.text('').hide();
            }
            return true;
        }
    }

    const digitCount = cleanDigits.length;
    let errorMsg = '';

    if (!val.startsWith(dialCode)) {
        errorMsg = `${fieldLabel} must start with country code ${dialCode}.`;
    } else if (minDigits === maxDigits) {
        if (digitCount !== minDigits) {
            errorMsg = `${fieldLabel} must be exactly ${minDigits} digits (${digitCount}/${minDigits} entered).`;
        }
    } else {
        if (digitCount < minDigits || digitCount > maxDigits) {
            errorMsg = `${fieldLabel} must be between ${minDigits} and ${maxDigits} digits (${digitCount} entered).`;
        }
    }

    if (errorMsg) {
        $input.addClass('is-invalid').removeClass('is-valid');
        if ($feedback.length) {
            $feedback.text(errorMsg).show();
        }
        return false;
    } else {
        $input.removeClass('is-invalid').addClass('is-valid');
        if ($feedback.length) {
            $feedback.text('').hide();
        }
        return true;
    }
}

function generateCompliantPassword(len = 12) {
    const uppers = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    const lowers = 'abcdefghijkmnopqrstuvwxyz';
    const digits = '23456789';
    const specials = '!@#$%^&*()-_=+';
    const all = uppers + lowers + digits + specials;

    let pwd = [
        uppers[Math.floor(Math.random() * uppers.length)],
        lowers[Math.floor(Math.random() * lowers.length)],
        digits[Math.floor(Math.random() * digits.length)],
        specials[Math.floor(Math.random() * specials.length)]
    ];
    for (let i = 4; i < len; i++) {
        pwd.push(all[Math.floor(Math.random() * all.length)]);
    }
    return pwd.sort(() => Math.random() - 0.5).join('');
}

function checkPasswordComplexity(val, isRequired = true) {
    if (!val) {
        return isRequired ? 'Password is required.' : '';
    }
    if (val.length < 8) {
        return 'Password must be at least 8 characters long.';
    }
    if (!/[A-Z]/.test(val)) {
        return 'Password must contain at least 1 uppercase letter.';
    }
    if (!/[a-z]/.test(val)) {
        return 'Password must contain at least 1 lowercase letter.';
    }
    if (!/[0-9]/.test(val)) {
        return 'Password must contain at least 1 number.';
    }
    if (!/[^A-Za-z0-9]/.test(val)) {
        return 'Password must contain at least 1 special character.';
    }
    return '';
}

function validatePasswordInput(inputId = 'password', isRequired = true) {
    const $input = $('#' + inputId);
    if (!$input.length) return true;

    const val = $input.val();
    const err = checkPasswordComplexity(val, isRequired);
    const $feedback = $('#password_feedback');

    if (err) {
        $input.addClass('is-invalid');
        if ($feedback.length) {
            $feedback.text(err);
        }
        return false;
    } else {
        $input.removeClass('is-invalid');
        return true;
    }
}

function validateStep(step) {
    let isValid = true;

    if (step === 1) {
        const name = $('#client_name').val().trim();
        const email = $('#client_email').val().trim();
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

        // Validate Password Complexity
        if (!validatePasswordInput('password', true)) {
            isValid = false;
        }

        // Validate Country
        if (!country) {
            $('#country').addClass('is-invalid');
            isValid = false;
        } else {
            $('#country').removeClass('is-invalid');
        }

        // Validate Mobile using selected Country rules
        if (!validateMobileInput('client_mobile', true)) {
            isValid = false;
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
        const { dialCode } = getCompanyCountryPhoneRules();
        if (officePhone === dialCode || officePhone === '+') {
            $('#office_phone').val('').removeClass('is-invalid is-valid');
            $('#office_phone_feedback').text('').hide();
            officePhone = '';
        }

        if (officePhone) {
            if (!validateMobileInput('office_phone', false)) {
                isValid = false;
                $('#office_phone').focus();
            }
        } else {
            $('#office_phone').removeClass('is-invalid');
            $('#office_phone_feedback').text('').hide();
        }
    }

    if (step === 3) {
        if (!validateProjectDates()) {
            isValid = false;
            $('#project_deadline').focus();
        }
    }

    if (step === 4) {
        if (!validateDealDates()) {
            isValid = false;
            $('#deal_next_follow_up').focus();
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
        if (!dealCurrencyManuallyChanged) {
            const selCountry = $('#company_country').val() || $('#country').val();
            syncDealCurrencyWithCountry(selCountry);
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

function syncProjectDateConstraints() {
    const $start = $('#project_start_date');
    const $deadline = $('#project_deadline');
    const $withoutDeadline = $('#project_without_deadline');

    if ($withoutDeadline.is(':checked')) {
        $deadline.prop('disabled', true).val('').removeClass('is-invalid');
        $('#project_deadline_feedback').text('').hide();
        return true;
    } else {
        $deadline.prop('disabled', false);
    }

    const startVal = $start.val();
    if (startVal) {
        // Calculate the next day (start_date + 1 day) as minimum allowed deadline
        const startDate = new Date(startVal + 'T00:00:00');
        if (!isNaN(startDate.getTime())) {
            const nextDay = new Date(startDate);
            nextDay.setDate(nextDay.getDate() + 1);
            const yyyy = nextDay.getFullYear();
            const mm = String(nextDay.getMonth() + 1).padStart(2, '0');
            const dd = String(nextDay.getDate()).padStart(2, '0');
            const minDateStr = `${yyyy}-${mm}-${dd}`;
            $deadline.attr('min', minDateStr);

            // If deadline is already filled, validate it
            const deadlineVal = $deadline.val();
            if (deadlineVal) {
                const deadlineDate = new Date(deadlineVal + 'T00:00:00');
                if (!isNaN(deadlineDate.getTime()) && deadlineDate <= startDate) {
                    $deadline.addClass('is-invalid');
                    $('#project_deadline_feedback').text('Deadline must be later than the project start date (' + startVal + ').').show();
                    return false;
                } else {
                    $deadline.removeClass('is-invalid');
                    $('#project_deadline_feedback').text('').hide();
                }
            }
        }
    } else {
        $deadline.removeAttr('min');
    }
    return true;
}

function validateProjectDates() {
    const $withoutDeadline = $('#project_without_deadline');
    if ($withoutDeadline.is(':checked')) {
        $('#project_deadline').removeClass('is-invalid');
        $('#project_deadline_feedback').hide();
        return true;
    }

    const startVal = $('#project_start_date').val();
    const deadlineVal = $('#project_deadline').val();

    if (startVal && deadlineVal) {
        const startDate = new Date(startVal + 'T00:00:00');
        const deadlineDate = new Date(deadlineVal + 'T00:00:00');

        if (deadlineDate <= startDate) {
            $('#project_deadline').addClass('is-invalid');
            $('#project_deadline_feedback').text('Deadline must be later than the project start date (' + startVal + ').').show();
            return false;
        } else {
            $('#project_deadline').removeClass('is-invalid');
            $('#project_deadline_feedback').hide();
        }
    }
    return true;
}

function validateDealDates() {
    const closeDateVal = $('#deal_close_date').val();
    const followUpVal = $('#deal_next_follow_up').val();

    if (closeDateVal && followUpVal) {
        const closeDate = new Date(closeDateVal + 'T00:00:00');
        const followUpDate = new Date(followUpVal + 'T00:00:00');

        if (followUpDate <= closeDate) {
            $('#deal_next_follow_up').addClass('is-invalid');
            $('#deal_next_follow_up_feedback').text('Next follow-up date must be later than the close date (' + closeDateVal + ').').show();
            return false;
        } else {
            $('#deal_next_follow_up').removeClass('is-invalid');
            $('#deal_next_follow_up_feedback').hide();
        }
    }
    return true;
}

function toggleDeadlineInput() {
    syncProjectDateConstraints();
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

    // Generate Compliant Random Password
    $('.generate-password').on('click', function () {
        const randomPassword = generateCompliantPassword(12);
        $('#password').val(randomPassword).trigger('input');
        validatePasswordInput('password', true);
    });

    // Real-time Password Complexity Validation
    $('#password').on('input blur', function () {
        validatePasswordInput('password', true);
    });

    // Setup strict phone input behaviors
    function bindPhoneInput($input, isRequired) {
        if (!$input.length) return;

        $input.on('keydown', function(e) {
            handlePhoneKeyDown(e, this);
        });

        $input.on('input', function() {
            sanitizePhoneNumber(this);
            const val = $(this).val().trim();
            const isOffice = $(this).attr('id') === 'office_phone';
            const { dialCode } = isOffice ? getCompanyCountryPhoneRules() : getSelectedCountryPhoneRules();
            if (!isRequired && (val === '' || val === dialCode || val === '+')) {
                $(this).removeClass('is-invalid is-valid');
                const feedbackId = $(this).attr('id') === 'client_mobile' ? '#mobile_feedback' : '#' + $(this).attr('id') + '_feedback';
                $(feedbackId).text('').hide();
                return;
            }
            validateMobileInput($(this).attr('id'), isRequired);
        });

        $input.on('paste', function() {
            setTimeout(() => {
                sanitizePhoneNumber(this);
                validateMobileInput($(this).attr('id'), isRequired);
            }, 10);
        });

        $input.on('focus', function() {
            const isOffice = $(this).attr('id') === 'office_phone';
            const { dialCode } = isOffice ? getCompanyCountryPhoneRules() : getSelectedCountryPhoneRules();
            if (!$(this).val() || $(this).val().trim() === '') {
                $(this).val(dialCode);
            }
        });

        $input.on('blur', function() {
            sanitizePhoneNumber(this);
            const val = $(this).val().trim();
            const isOffice = $(this).attr('id') === 'office_phone';
            const { dialCode } = isOffice ? getCompanyCountryPhoneRules() : getSelectedCountryPhoneRules();
            if (!isRequired && (val === dialCode || val === '' || val === '+')) {
                $(this).val('').removeClass('is-invalid is-valid');
                const feedbackId = $(this).attr('id') === 'client_mobile' ? '#mobile_feedback' : '#' + $(this).attr('id') + '_feedback';
                $(feedbackId).text('').hide();
                return;
            }
            validateMobileInput($(this).attr('id'), isRequired);
        });
    }

    bindPhoneInput($('#client_mobile'), true);
    bindPhoneInput($('#office_phone'), false);

    // Custom formatting for Select2 flags
    function formatOption (state) {
        if (!state.id) return state.text;
        let flag = $(state.element).data("flag");
        if (flag) {
            return $('<span><img src="' + flag + '" width="20" class="me-2 rounded-1"/> ' + state.text + '</span>');
        }
        return state.text;
    }

    let companyCountryTouchedByUser = false;

    // Step 1: Client Country dropdown Select2
    $('#country').select2({
        theme: "classic",
        templateResult: formatOption,
        templateSelection: formatOption,
        placeholder: "Select country",
        width: '100%'
    }).on('change', function() {
        if ($(this).val()) {
            $(this).removeClass('is-invalid');
        }
        const selDial = $(this).find('option:selected').data('dial-code');
        if (selDial && $('#mobile_country_code').val() !== selDial) {
            $('#mobile_country_code').val(selDial);
        }
        const rules = getSelectedCountryPhoneRules();
        updateMobileFormatHint(rules);

        const $mobile = $('#client_mobile');
        let currentVal = $mobile.val().trim();
        let nationalDigits = '';
        if (currentVal.startsWith('+')) {
            nationalDigits = currentVal.replace(/^\+\d+/, '').replace(/\D/g, '');
        } else {
            nationalDigits = currentVal.replace(/\D/g, '');
        }
        nationalDigits = nationalDigits.substring(0, rules.maxDigits);
        $mobile.val(rules.dialCode + nationalDigits);

        if ($mobile.val() && $mobile.val() !== rules.dialCode) {
            validateMobileInput('client_mobile', true);
        } else {
            $mobile.removeClass('is-invalid is-valid');
            $('#mobile_feedback').text('');
        }

        // If user hasn't manually selected a different company_country, auto-sync company country
        if (!companyCountryTouchedByUser && $('#company_country').length) {
            const clientCountry = $(this).val();
            if ($('#company_country').val() !== clientCountry) {
                $('#company_country').val(clientCountry).trigger('change', [true]);
            }
        }

        if (!dealCurrencyManuallyChanged) {
            syncDealCurrencyWithCountry($(this).val());
        }
    });

    $('#mobile_country_code').on('change', function() {
        const selCountry = $(this).find('option:selected').data('country');
        if (selCountry && $('#country').val() !== selCountry) {
            $('#country').val(selCountry).trigger('change');
        } else {
            const rules = getSelectedCountryPhoneRules();
            updateMobileFormatHint(rules);
            const $mobile = $('#client_mobile');
            let currentVal = $mobile.val().trim();
            let nationalDigits = currentVal.replace(/^\+\d*/, '').replace(/\D/g, '').substring(0, rules.maxDigits);
            $mobile.val(rules.dialCode + nationalDigits);
            if ($mobile.val() && $mobile.val() !== rules.dialCode) {
                validateMobileInput('client_mobile', true);
            }
        }
    });

    // Step 2: Company Country dropdown Select2
    $('#company_country').select2({
        theme: "classic",
        templateResult: formatOption,
        templateSelection: formatOption,
        placeholder: "Select country",
        width: '100%'
    }).on('change', function(e, triggeredProgrammatically) {
        if (!triggeredProgrammatically) {
            companyCountryTouchedByUser = true;
        }
        const countryVal = $(this).val();
        const selOfficeDial = $(this).find('option:selected').data('dial-code');
        if (selOfficeDial && $('#office_country_code').val() !== selOfficeDial) {
            $('#office_country_code').val(selOfficeDial);
        }
        const rules = getCompanyCountryPhoneRules();
        updateOfficePhoneFormatHint(rules);

        const $office = $('#office_phone');
        if ($office.length && $office.val().trim()) {
            let officeVal = $office.val().trim();
            let officeDigits = '';
            if (officeVal.startsWith('+')) {
                officeDigits = officeVal.replace(/^\+\d+/, '').replace(/\D/g, '');
            } else {
                officeDigits = officeVal.replace(/\D/g, '');
            }
            officeDigits = officeDigits.substring(0, rules.maxDigits);
            if (officeDigits) {
                $office.val(rules.dialCode + officeDigits);
                validateMobileInput('office_phone', false);
            } else {
                $office.val('');
                $office.removeClass('is-invalid is-valid');
            }
        }

        // Populate State & City dropdowns dynamically
        populateStates(countryVal, $('#state').val());
        populateCities(countryVal, $('#state').val(), $('#city').val());

        if (!dealCurrencyManuallyChanged) {
            syncDealCurrencyWithCountry(countryVal);
        }
    });

    $('#office_country_code').on('change', function() {
        const rules = getCompanyCountryPhoneRules();
        updateOfficePhoneFormatHint(rules);
        const $office = $('#office_phone');
        let currentVal = $office.val().trim();
        if (currentVal && currentVal !== '+') {
            let nationalDigits = currentVal.replace(/^\+\d*/, '').replace(/\D/g, '').substring(0, rules.maxDigits);
            $office.val(rules.dialCode + nationalDigits);
            validateMobileInput('office_phone', false);
        }
    });

    // Step 2: State dropdown Select2 (searchable + tags allowed)
    $('#state').select2({
        theme: "classic",
        tags: true,
        placeholder: "Select or type state",
        width: '100%',
        allowClear: true
    }).on('change', function() {
        const countryVal = $('#company_country').val() || $('#country').val();
        populateCities(countryVal, $(this).val(), $('#city').val());
    });

    // Step 2: City dropdown Select2 (searchable + tags allowed)
    $('#city').select2({
        theme: "classic",
        tags: true,
        placeholder: "Select or type city",
        width: '100%',
        allowClear: true
    });

    // Step 2: Postal Code Auto-Detection
    $('#postal_code').on('input paste change', function() {
        clearTimeout(pincodeLookupTimer);
        const val = $(this).val().trim();
        if (!val) {
            if (currentPincodeAjax && typeof currentPincodeAjax.abort === 'function') {
                currentPincodeAjax.abort();
                currentPincodeAjax = null;
            }
            $('#pincode_spinner').hide();
            $('#pincode_feedback_pill').hide();
            $('#area_selection_wrapper').slideUp(150);
            return;
        }
        pincodeLookupTimer = setTimeout(function() {
            performPincodeLookup(val);
        }, 350);
    });

    $('#postal_code').on('blur', function() {
        const val = $(this).val().trim();
        if (!val) {
            if (currentPincodeAjax && typeof currentPincodeAjax.abort === 'function') {
                currentPincodeAjax.abort();
                currentPincodeAjax = null;
            }
            $('#pincode_spinner').hide();
            $('#pincode_feedback_pill').hide();
            $('#area_selection_wrapper').slideUp(150);
        } else {
            performPincodeLookup(val);
        }
    });

    // Step 2: Selecting an auto-detected area populates company address
    $('#detected_area_select').on('change', function() {
        const selectedArea = $(this).val();
        if (!selectedArea) return;

        const $addr = $('#company_address');
        const currentAddr = $addr.val().trim();
        const city = $('#city').val() || '';
        const state = $('#state').val() || '';
        const pin = $('#postal_code').val() || '';

        if (!currentAddr) {
            const fullAddr = [selectedArea, city, state, pin].filter(Boolean).join(', ');
            $addr.val(fullAddr);
        } else if (!currentAddr.toLowerCase().includes(selectedArea.toLowerCase())) {
            $addr.val(selectedArea + ', ' + currentAddr);
        }
    });

    // Auto-trigger pincode detection if postal_code already filled on load
    if ($('#postal_code').val() && $('#postal_code').val().trim()) {
        performPincodeLookup($('#postal_code').val().trim());
    }

    // Initialize country & office phone format hints on page load
    updateMobileFormatHint();
    updateOfficePhoneFormatHint();

    // Initial population for states & cities based on old or initial country
    const initialCompanyCountry = $('#company_country').val() || $('#country').val() || 'India';
    const oldState = @json(old('state', ''));
    const oldCity = @json(old('city', ''));
    populateStates(initialCompanyCountry, oldState);
    populateCities(initialCompanyCountry, oldState, oldCity);

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

    function closeModalSafely(modalId) {
        const modalEl = document.getElementById(modalId);
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const bsModal = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
            if (bsModal) {
                bsModal.hide();
            }
        }
        $('#' + modalId).modal('hide');
        setTimeout(function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css({
                overflow: '',
                paddingRight: ''
            });
        }, 150);
    }

    $('.modal').on('hidden.bs.modal', function() {
        $('body').removeClass('modal-open').css({
            overflow: '',
            paddingRight: ''
        });
        $('.modal-backdrop').remove();
    });

    // AJAX Form: Add Client Category
    $('#addCategoryForm').submit(function(e) {
        e.preventDefault();
        const name = $('#categoryName').val().trim();
        if (!name) return;

        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: "{{ route('client-categories.store') }}",
            data: form.serialize(),
            success: function(data) {
                $('#client_category_id').append(
                    `<option value="${data.id}" selected>${data.name}</option>`
                );
                $('#client_category_id').val(data.id).trigger('change');
                closeModalSafely('addCategoryModal');
                form[0].reset();
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON?.errors?.name) {
                    alert(xhr.responseJSON.errors.name[0]);
                } else {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Failed to add category'));
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
            }
        });
    });

    // AJAX Form: Add Client Sub-Category
    $('#addSubCategoryForm').submit(function(e) {
        e.preventDefault();
        const name = $('#subcategoryName').val().trim();
        if (!name) return;

        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: "{{ route('client-sub-categories.store') }}",
            data: form.serialize(),
            success: function(data) {
                $('#client_sub_category_id').append(
                    `<option value="${data.id}" selected>${data.name}</option>`
                );
                $('#client_sub_category_id').val(data.id).trigger('change');
                closeModalSafely('addSubCategoryModal');
                form[0].reset();
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON?.errors?.name) {
                    alert(xhr.responseJSON.errors.name[0]);
                } else {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Failed to add subcategory'));
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
            }
        });
    });

    // AJAX Form: Add Project Category
    $('#addProjectCategoryForm').submit(function(e) {
        e.preventDefault();
        const name = $('#projectCategoryName').val().trim();
        if (!name) return;

        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: "{{ route('project-categories.store') }}",
            data: form.serialize(),
            success: function(data) {
                if (data.id && data.category_name) {
                    $('#project_category_id').append(
                        `<option value="${data.id}" selected>${data.category_name}</option>`
                    );
                    $('#project_category_id').val(data.id).trigger('change');
                }
                closeModalSafely('addProjectCategoryModal');
                form[0].reset();
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Failed to add project category'));
            },
            complete: function() {
                submitBtn.prop('disabled', false);
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

    // Deal currency selector change listener & initialization
    $('#deal_currency').on('change', function() {
        dealCurrencyManuallyChanged = true;
        updateDealCurrencyLabel();
    });
    updateDealCurrencyLabel();
    if (!dealCurrencyManuallyChanged) {
        const initialCountry = $('#company_country').val() || $('#country').val() || 'India';
        syncDealCurrencyWithCountry(initialCountry);
    }

    // Real-time synchronization and validation for Project Dates
    $('#project_start_date').on('change input', function() {
        syncProjectDateConstraints();
    });

    $('#project_deadline').on('change input', function() {
        validateProjectDates();
    });

    $('#deal_close_date').on('change input', function() {
        const closeVal = $(this).val();
        if (closeVal) {
            const cDate = new Date(closeVal + 'T00:00:00');
            if (!isNaN(cDate.getTime())) {
                const nextDay = new Date(cDate);
                nextDay.setDate(nextDay.getDate() + 1);
                const yyyy = nextDay.getFullYear();
                const mm = String(nextDay.getMonth() + 1).padStart(2, '0');
                const dd = String(nextDay.getDate()).padStart(2, '0');
                $('#deal_next_follow_up').attr('min', `${yyyy}-${mm}-${dd}`);
            }
        }
        validateDealDates();
    });

    $('#deal_next_follow_up').on('change input', function() {
        validateDealDates();
    });

    // Initialize date constraints on page load
    syncProjectDateConstraints();

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

        // Validate Step 3 (Project deadline must be later than start date)
        if (!validateStep(3)) {
            goToStep(3);
            return false;
        }

        // Validate Step 4 (Deal dates)
        if (!validateStep(4)) {
            goToStep(4);
            return false;
        }

        // If without_deadline is checked, ensure deadline input value is cleared
        if ($('#project_without_deadline').is(':checked')) {
            $('#project_deadline').val('');
        }
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
