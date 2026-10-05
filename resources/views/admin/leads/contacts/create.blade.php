@extends('admin.layout.app')

@section('content')

@php
    if (!isset($countries) || (is_object($countries) && method_exists($countries, 'isEmpty') && $countries->isEmpty())) {
        $countries = \App\Models\Country::orderBy('name')->get();
    }
@endphp

<style>
.form-card-modern {
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    transition: all 0.2s ease;
}
html[data-pms-theme="dark"] .form-card-modern {
    background: #0F1530;
    border-color: rgba(79, 131, 255, 0.12);
}
.form-card-header-modern {
    background: #ffffff;
    border-bottom: 1px solid #f1f5f9;
    padding: 1.1rem 1.5rem;
    border-top-left-radius: 16px;
    border-top-right-radius: 16px;
}
html[data-pms-theme="dark"] .form-card-header-modern {
    background: #0F1530;
    border-bottom-color: rgba(79, 131, 255, 0.08);
}
.form-label-modern {
    font-size: 0.84rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.4rem;
    display: block;
}
html[data-pms-theme="dark"] .form-label-modern {
    color: #cbd5e1;
}
.form-control-modern, .form-select-modern {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.55rem 0.9rem;
    font-size: 0.875rem;
    color: #0f172a;
    font-weight: 500;
    min-height: 42px;
    transition: all 0.18s ease-in-out;
}
html[data-pms-theme="dark"] .form-control-modern,
html[data-pms-theme="dark"] .form-select-modern {
    background-color: #141B3D;
    border-color: rgba(79, 131, 255, 0.15);
    color: #ffffff;
}
.form-control-modern:focus, .form-select-modern:focus {
    background-color: #ffffff;
    border-color: #2F6BFF;
    box-shadow: 0 0 0 3.5px rgba(47, 107, 255, 0.12);
    color: #0f172a;
    outline: none;
}
html[data-pms-theme="dark"] .form-control-modern:focus,
html[data-pms-theme="dark"] .form-select-modern:focus {
    background-color: #0F1530;
    border-color: #60A5FA;
    box-shadow: 0 0 0 3.5px rgba(79, 131, 255, 0.18);
    color: #ffffff;
}
.form-control-modern::placeholder {
    color: #94a3b8;
    font-weight: 400;
}
html[data-pms-theme="dark"] .form-control-modern::placeholder {
    color: #64748b;
}
textarea.form-control-modern {
    min-height: auto;
}
.btn-back-pill {
    background-color: #ffffff;
    border: 1px solid #cbd5e1;
    color: #334155;
    border-radius: 50px;
    padding: 0.45rem 1.15rem;
    font-size: 0.85rem;
    font-weight: 600;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
.btn-back-pill:hover {
    background-color: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
}
html[data-pms-theme="dark"] .btn-back-pill {
    background-color: #141B3D;
    border-color: rgba(79, 131, 255, 0.2);
    color: #e2e8f0;
}
html[data-pms-theme="dark"] .btn-back-pill:hover {
    background-color: rgba(79, 131, 255, 0.2);
    color: #ffffff;
}
.btn-submit-emerald {
    background: linear-gradient(135deg, #4F83FF 0%, #2F6BFF 100%);
    color: #ffffff;
    border: none;
    border-radius: 10px;
    padding: 0.65rem 1.75rem;
    font-size: 0.9rem;
    font-weight: 600;
    box-shadow: 0 4px 14px rgba(47, 107, 255, 0.25);
    transition: all 0.2s ease;
}
.btn-submit-emerald:hover {
    background: linear-gradient(135deg, #2F6BFF 0%, #1E4FCC 100%);
    color: #ffffff;
    box-shadow: 0 6px 18px rgba(47, 107, 255, 0.35);
    transform: translateY(-1px);
}
.btn-outline-emerald {
    background: transparent;
    border: 1.5px solid #2F6BFF;
    color: #2F6BFF;
    border-radius: 10px;
    padding: 0.65rem 1.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.2s ease;
}
.btn-outline-emerald:hover {
    background: #EEF2FF;
    color: #1E4FCC;
}
html[data-pms-theme="dark"] .btn-outline-emerald {
    border-color: #60A5FA;
    color: #60A5FA;
}
html[data-pms-theme="dark"] .btn-outline-emerald:hover {
    background: rgba(79, 131, 255, 0.15);
    color: #93C5FD;
}
.btn-cancel-modern {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #475569;
    border-radius: 10px;
    padding: 0.65rem 1.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.2s ease;
    text-decoration: none;
}
.btn-cancel-modern:hover {
    background: #e2e8f0;
    color: #1e293b;
}
html[data-pms-theme="dark"] .btn-cancel-modern {
    background: #141B3D;
    border-color: rgba(79, 131, 255, 0.15);
    color: #cbd5e1;
}
html[data-pms-theme="dark"] .btn-cancel-modern:hover {
    background: rgba(79, 131, 255, 0.2);
    color: #ffffff;
}
.input-group-modern {
    display: flex;
    position: relative;
    border-radius: 10px;
}
.input-group-text-modern {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-right: none;
    border-top-left-radius: 10px;
    border-bottom-left-radius: 10px;
    color: #64748b;
    padding: 0.55rem 0.9rem;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    transition: all 0.18s ease-in-out;
}
html[data-pms-theme="dark"] .input-group-text-modern {
    background-color: #141B3D;
    border-color: rgba(79, 131, 255, 0.15);
    color: #94a3b8;
}
.input-group:focus-within .input-group-text-modern {
    border-color: #2F6BFF;
    color: #2F6BFF;
}
html[data-pms-theme="dark"] .input-group:focus-within .input-group-text-modern {
    border-color: #60A5FA;
    color: #60A5FA;
}
.form-control-modern.has-prefix {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border-left: none;
}
.form-control-modern.is-valid {
    border-color: #10B981 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2310B981' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}
.form-control-modern.is-invalid {
    border-color: #EF4444 !important;
}
.validation-hint {
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 0.3rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}
html[data-pms-theme="dark"] .validation-hint {
    color: #94a3b8;
}
.email-suggestion-box {
    font-size: 0.78rem;
    background: rgba(47, 107, 255, 0.08);
    border: 1px dashed rgba(47, 107, 255, 0.3);
    color: #2563EB;
    border-radius: 8px;
    padding: 0.3rem 0.6rem;
    margin-top: 0.35rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.email-suggestion-box:hover {
    background: rgba(47, 107, 255, 0.15);
    color: #1D4ED8;
}
html[data-pms-theme="dark"] .email-suggestion-box {
    background: rgba(79, 131, 255, 0.15);
    border-color: rgba(79, 131, 255, 0.35);
    color: #93C5FD;
}

/* Select2 Modern Theme */
.select2-container .select2-selection--single {
    background-color: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 10px !important;
    height: 42px !important;
    display: flex !important;
    align-items: center !important;
    transition: all 0.18s ease-in-out !important;
}
.select2-container .select2-selection--single .select2-selection__rendered {
    color: #0f172a !important;
    font-weight: 500 !important;
    font-size: 0.875rem !important;
    line-height: 40px !important;
    padding-left: 0.9rem !important;
    padding-right: 2rem !important;
}
.select2-container .select2-selection--single .select2-selection__arrow {
    height: 40px !important;
    right: 10px !important;
}
.select2-container--open .select2-dropdown {
    border: 1px solid #2F6BFF !important;
    border-radius: 10px !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
    z-index: 1060 !important;
}
.select2-results__option {
    padding: 0.55rem 0.9rem !important;
    font-size: 0.875rem !important;
}
.select2-results__option--highlighted {
    background-color: #2F6BFF !important;
    color: #ffffff !important;
}
html[data-pms-theme="dark"] .select2-container .select2-selection--single {
    background-color: #141B3D !important;
    border-color: rgba(79, 131, 255, 0.15) !important;
}
html[data-pms-theme="dark"] .select2-container .select2-selection--single .select2-selection__rendered {
    color: #ffffff !important;
}
html[data-pms-theme="dark"] .select2-dropdown {
    background-color: #0F1530 !important;
    border-color: rgba(79, 131, 255, 0.25) !important;
    color: #ffffff !important;
}
html[data-pms-theme="dark"] .select2-results__option {
    background-color: #0F1530 !important;
    color: #cbd5e1 !important;
}
html[data-pms-theme="dark"] .select2-results__option--highlighted {
    background-color: #2F6BFF !important;
    color: #ffffff !important;
}
html[data-pms-theme="dark"] .select2-search--dropdown .select2-search__field {
    background-color: #141B3D !important;
    border-color: rgba(79, 131, 255, 0.2) !important;
    color: #ffffff !important;
}
</style>

<div class="container-fluid py-3">

    {{-- BREADCRUMB & HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 d-flex align-items-center gap-2" style="color: #0f172a;">
                <span class="fs-4">👤+</span> Add Lead Contact
            </h4>
            <p class="text-muted small mb-0">Create a new sales lead contact record in your organization pipeline</p>
        </div>
        <a href="{{ route('leads.contacts.index') }}" class="btn-back-pill">
            <i class="fas fa-arrow-left me-1"></i> Back to Lead Contacts
        </a>
    </div>

    {{-- DUPLICATE WARNING BANNER --}}
    <div id="duplicateAlert" class="alert alert-warning border-warning shadow-sm d-none mb-4 fade show" role="alert" style="border-radius: 12px;">
        <div class="d-flex align-items-start gap-3">
            <div class="fs-3 text-warning"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-1">Possible Duplicate Lead Found!</h6>
                <p class="mb-2 small" id="duplicateMessage">We found an existing lead matching this email/phone number in your database.</p>
                <div class="d-flex gap-2 flex-wrap" id="duplicateActions">
                    <!-- Dynamic duplicate links populated via JS -->
                </div>
            </div>
            <button type="button" class="btn-close" onclick="document.getElementById('duplicateAlert').classList.add('d-none')"></button>
        </div>
    </div>

    {{-- FORM CARD --}}
    <form action="{{ route('leads.contacts.store') }}" method="POST" id="leadCreateForm">
        @csrf

        {{-- SECTION 1: BASIC INFORMATION --}}
        <div class="card form-card-modern mb-4">
            <div class="card-header form-card-header-modern">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2"><span class="fs-5">💳</span> 1. Basic Information</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label-modern">Salutation</label>
                        <select name="salutation" class="form-select form-select-modern">
                            <option value="">None</option>
                            <option value="Mr." {{ old('salutation') == 'Mr.' ? 'selected' : '' }}>Mr.</option>
                            <option value="Mrs." {{ old('salutation') == 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                            <option value="Ms." {{ old('salutation') == 'Ms.' ? 'selected' : '' }}>Ms.</option>
                            <option value="Dr." {{ old('salutation') == 'Dr.' ? 'selected' : '' }}>Dr.</option>
                            <option value="Prof." {{ old('salutation') == 'Prof.' ? 'selected' : '' }}>Prof.</option>
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label-modern">Contact Name <span class="text-danger">*</span></label>
                        <input type="text" name="contact_name" class="form-control form-control-modern @error('contact_name') is-invalid @enderror" value="{{ old('contact_name') }}" required placeholder="e.g. John Doe">
                        @error('contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-5">
                        <label class="form-label-modern">Job Title / Designation</label>
                        <input type="text" name="job_title" class="form-control form-control-modern" value="{{ old('job_title') }}" placeholder="e.g. Senior Manager">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern" for="lead_country">Country <span class="text-danger">*</span></label>
                        <select name="country" id="lead_country" class="form-select form-select-modern select2" required>
                            <option value="">Select country</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->name }}"
                                    data-flag="{{ $c->flag_url }}"
                                    data-dial-code="{{ $c->phone_code }}"
                                    data-min-digits="{{ $c->min_digits ?? 6 }}"
                                    data-max-digits="{{ $c->max_digits ?? 15 }}"
                                    {{ old('country', 'India') == $c->name ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->phone_code }})
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback d-block" id="country_feedback" style="display: {{ $errors->has('country') ? 'block' : 'none' }};">
                            @error('country'){{ $message }}@enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern" for="inputPhone">Primary Phone <span class="text-danger">*</span></label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="fas fa-phone-alt"></i></span>
                            <input type="tel" name="phone" id="inputPhone" class="form-control form-control-modern has-prefix @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required maxlength="20" placeholder="e.g. 9876543210">
                        </div>
                        <div class="invalid-feedback d-block" id="phone_feedback" style="display: {{ $errors->has('phone') ? 'block' : 'none' }};">
                            @error('phone'){{ $message }}@enderror
                        </div>
                        <small class="validation-hint" id="phone_format_hint">
                            <i class="fas fa-info-circle"></i> <span id="phone_format_hint_text">Required: 10-digit number</span>
                        </small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern" for="inputMobile">Mobile / Cell</label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="fas fa-mobile-alt"></i></span>
                            <input type="tel" name="mobile" id="inputMobile" class="form-control form-control-modern has-prefix @error('mobile') is-invalid @enderror" value="{{ old('mobile') }}" maxlength="20" placeholder="e.g. 9876543210">
                        </div>
                        <div class="invalid-feedback d-block" id="mobile_feedback" style="display: {{ $errors->has('mobile') ? 'block' : 'none' }};">
                            @error('mobile'){{ $message }}@enderror
                        </div>
                        <small class="validation-hint" id="mobile_format_hint">
                            <i class="fas fa-info-circle"></i> <span id="mobile_format_hint_text">Optional mobile number</span>
                        </small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern" for="inputEmail">Email Address <span class="text-danger">*</span></label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" id="inputEmail" class="form-control form-control-modern has-prefix @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email" placeholder="e.g. john@gmail.com">
                        </div>
                        <div class="invalid-feedback d-block" id="email_feedback" style="display: {{ $errors->has('email') ? 'block' : 'none' }};">
                            @error('email'){{ $message }}@enderror
                        </div>
                        <div id="email_suggestion" class="email-suggestion-box d-none">
                            <i class="fas fa-lightbulb"></i> <span id="email_suggestion_text"></span>
                        </div>
                        <small class="validation-hint" id="email_format_hint">
                            <i class="fas fa-info-circle"></i> Accepts valid email (e.g. name@gmail.com or business email)
                        </small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern" for="inputAlternatePhone">Alternate Phone</label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="fas fa-phone"></i></span>
                            <input type="tel" name="alternate_phone" id="inputAlternatePhone" class="form-control form-control-modern has-prefix @error('alternate_phone') is-invalid @enderror" value="{{ old('alternate_phone') }}" maxlength="20" placeholder="e.g. 9876543210 or Ext 102">
                        </div>
                        <div class="invalid-feedback d-block" id="alternate_phone_feedback" style="display: {{ $errors->has('alternate_phone') ? 'block' : 'none' }};">
                            @error('alternate_phone'){{ $message }}@enderror
                        </div>
                        <small class="validation-hint" id="alternate_phone_hint">
                            <i class="fas fa-info-circle"></i> Optional secondary phone number
                        </small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern" for="inputWhatsapp">WhatsApp Number</label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="fab fa-whatsapp text-success"></i></span>
                            <input type="tel" name="whatsapp" id="inputWhatsapp" class="form-control form-control-modern has-prefix @error('whatsapp') is-invalid @enderror" value="{{ old('whatsapp') }}" maxlength="20" placeholder="e.g. 9876543210">
                        </div>
                        <div class="invalid-feedback d-block" id="whatsapp_feedback" style="display: {{ $errors->has('whatsapp') ? 'block' : 'none' }};">
                            @error('whatsapp'){{ $message }}@enderror
                        </div>
                        <small class="validation-hint" id="whatsapp_format_hint">
                            <i class="fas fa-info-circle"></i> Optional WhatsApp number
                        </small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Company Name</label>
                        <input type="text" name="company_name" class="form-control form-control-modern" value="{{ old('company_name') }}" placeholder="e.g. Acme Corp">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label-modern">Company Website</label>
                        <input type="url" name="website" class="form-control form-control-modern" value="{{ old('website') }}" placeholder="https://www.example.com">
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: LOCATION --}}
        <div class="card form-card-modern mb-4">
            <div class="card-header form-card-header-modern">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2"><span class="fs-5">📍</span> 2. Location Details</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label-modern" for="location_country">Country <span class="text-danger">*</span></label>
                        <select id="location_country" class="form-select form-select-modern select2">
                            <option value="">Select country</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->name }}"
                                    data-flag="{{ $c->flag_url }}"
                                    data-dial-code="{{ $c->phone_code }}"
                                    data-min-digits="{{ $c->min_digits ?? 6 }}"
                                    data-max-digits="{{ $c->max_digits ?? 15 }}"
                                    {{ old('country', 'India') == $c->name ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->phone_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-modern">State / Province</label>
                        <input type="text" name="state" class="form-control form-control-modern" value="{{ old('state') }}" placeholder="e.g. California">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-modern">City</label>
                        <input type="text" name="city" class="form-control form-control-modern" value="{{ old('city') }}" placeholder="e.g. San Francisco">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-modern">Postal / Zip Code</label>
                        <input type="text" name="postal_code" class="form-control form-control-modern" value="{{ old('postal_code') }}" placeholder="e.g. 94105">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label-modern">Street Address</label>
                        <textarea name="address" class="form-control form-control-modern" rows="2" placeholder="Full street address...">{{ old('address') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 3: LEAD INFORMATION --}}
        <div class="card form-card-modern mb-4">
            <div class="card-header form-card-header-modern">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2"><span class="fs-5">🎯</span> 3. Lead Information & Routing</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label-modern">Lead Source <span class="text-danger">*</span></label>
                        <select name="lead_source" class="form-select form-select-modern" required>
                            <option value="">Select Source</option>
                            <option value="email" {{ old('lead_source') == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="google" {{ old('lead_source') == 'google' ? 'selected' : '' }}>Google Search</option>
                            <option value="facebook" {{ old('lead_source') == 'facebook' ? 'selected' : '' }}>Facebook</option>
                            <option value="linkedin" {{ old('lead_source') == 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                            <option value="referral" {{ old('lead_source') == 'referral' ? 'selected' : '' }}>Referral</option>
                            <option value="website" {{ old('lead_source') == 'website' ? 'selected' : '' }}>Website Form</option>
                            <option value="phone" {{ old('lead_source') == 'phone' ? 'selected' : '' }}>Phone Call</option>
                            <option value="walkin" {{ old('lead_source') == 'walkin' ? 'selected' : '' }}>Walk-in</option>
                            <option value="other" {{ old('lead_source') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Lead Status</label>
                        <select name="status" class="form-select form-select-modern">
                            <option value="new" {{ old('status', 'new') == 'new' ? 'selected' : '' }}>New</option>
                            <option value="contacted" {{ old('status') == 'contacted' ? 'selected' : '' }}>Contacted</option>
                            <option value="qualified" {{ old('status') == 'qualified' ? 'selected' : '' }}>Qualified</option>
                            <option value="unqualified" {{ old('status') == 'unqualified' ? 'selected' : '' }}>Unqualified</option>
                            <option value="nurturing" {{ old('status') == 'nurturing' ? 'selected' : '' }}>Nurturing</option>
                            <option value="converted" {{ old('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                            <option value="lost" {{ old('status') == 'lost' ? 'selected' : '' }}>Lost</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Lead Priority</label>
                        <select name="priority" class="form-select form-select-modern">
                            <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Lead Owner <span class="text-danger">*</span></label>
                        <select name="lead_owner_id" class="form-select form-select-modern" required>
                            <option value="">Select Lead Owner</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('lead_owner_id', auth()->id()) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Expected Value</label>
                        <input type="number" step="0.01" name="expected_value" class="form-control form-control-modern" value="{{ old('expected_value') }}" placeholder="e.g. 50000.00">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Expected Closing Date</label>
                        <input type="date" name="expected_closing_date" class="form-control form-control-modern" value="{{ old('expected_closing_date') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-modern">Industry</label>
                        <input type="text" name="industry" class="form-control form-control-modern" value="{{ old('industry') }}" placeholder="e.g. Information Technology">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-modern">Tags (Comma Separated)</label>
                        <input type="text" name="tags" class="form-control form-control-modern" value="{{ old('tags') }}" placeholder="e.g. Enterprise, VIP, Tech">
                    </div>
                </div>

                {{-- OPTIONAL DEAL CREATION --}}
                <div class="border-top mt-4 pt-3">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="create_deal" value="1" id="createDealCheckbox" {{ old('create_deal') ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-dark" for="createDealCheckbox">
                            <i class="fas fa-handshake text-success me-1"></i> Create an Associated Deal immediately with this lead
                        </label>
                    </div>

                    <div id="dealFieldsRow" class="row g-3 p-3 bg-light rounded-3 border {{ old('create_deal') ? '' : 'd-none' }}">
                        <div class="col-md-6">
                            <label class="form-label-modern">Deal Name</label>
                            <input type="text" name="deal_name" class="form-control form-control-modern" value="{{ old('deal_name') }}" placeholder="e.g. Software License Renewal">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label-modern">Deal Value</label>
                            <input type="number" step="0.01" name="deal_value" class="form-control form-control-modern" value="{{ old('deal_value') }}" placeholder="0.00">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label-modern">Currency</label>
                            <select name="deal_currency" class="form-select form-select-modern">
                                <option value="INR" {{ old('deal_currency') == 'INR' ? 'selected' : '' }}>INR (₹)</option>
                                <option value="USD" {{ old('deal_currency') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                <option value="EUR" {{ old('deal_currency') == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                <option value="GBP" {{ old('deal_currency') == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 4: NOTES --}}
        <div class="card form-card-modern mb-4">
            <div class="card-header form-card-header-modern">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2"><span class="fs-5">📝</span> 4. Notes & Description</h6>
            </div>
            <div class="card-body p-4">
                <textarea name="description" class="form-control form-control-modern" rows="4" placeholder="Enter any initial discussion notes, requirements, or client background details...">{{ old('description') }}</textarea>
            </div>
        </div>

        {{-- SUBMIT BUTTONS --}}
        <div class="d-flex gap-2 justify-content-end mb-5">
            <a href="{{ route('leads.contacts.index') }}" class="btn-cancel-modern">Cancel</a>
            <button type="submit" name="save_and_add_more" value="1" class="btn-outline-emerald">Save & Add Another</button>
            <button type="submit" class="btn-submit-emerald"><i class="fas fa-check me-1"></i> Save Lead Contact</button>
        </div>
    </form>
</div>

<!-- Select2 & jQuery Resources -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const leadCreateForm = document.getElementById('leadCreateForm');
    const createDealCheckbox = document.getElementById('createDealCheckbox');
    const dealFieldsRow = document.getElementById('dealFieldsRow');

    if (createDealCheckbox && dealFieldsRow) {
        createDealCheckbox.addEventListener('change', function() {
            if (this.checked) {
                dealFieldsRow.classList.remove('d-none');
            } else {
                dealFieldsRow.classList.add('d-none');
            }
        });
    }

    // Input elements
    const leadCountrySelect = document.getElementById('lead_country');
    const locationCountrySelect = document.getElementById('location_country');
    const countryFeedback = document.getElementById('country_feedback');

    const inputEmail = document.getElementById('inputEmail');
    const emailFeedback = document.getElementById('email_feedback');
    const emailSuggestion = document.getElementById('email_suggestion');
    const emailSuggestionText = document.getElementById('email_suggestion_text');

    const inputPhone = document.getElementById('inputPhone');
    const phoneFeedback = document.getElementById('phone_feedback');
    const phoneFormatHint = document.getElementById('phone_format_hint_text');

    const inputMobile = document.getElementById('inputMobile');
    const mobileFeedback = document.getElementById('mobile_feedback');
    const mobileFormatHint = document.getElementById('mobile_format_hint_text');

    const inputAlternatePhone = document.getElementById('inputAlternatePhone');
    const alternatePhoneFeedback = document.getElementById('alternate_phone_feedback');

    const inputWhatsapp = document.getElementById('inputWhatsapp');
    const whatsappFeedback = document.getElementById('whatsapp_feedback');
    const whatsappFormatHint = document.getElementById('whatsapp_format_hint');

    const duplicateAlert = document.getElementById('duplicateAlert');
    const duplicateMessage = document.getElementById('duplicateMessage');
    const duplicateActions = document.getElementById('duplicateActions');

    // All known database dial codes sorted longest first for unambiguous prefix stripping
    const DB_DIAL_CODES = @json(\App\Models\Country::distinct()->pluck('phone_code')->filter()->values()->sort(function($a, $b) {
        return strlen($b) - strlen($a);
    })->values());

    // Helper to get selected country phone rules from database-driven option attributes
    function getCountryPhoneRules() {
        const sel = leadCountrySelect || locationCountrySelect;
        if (!sel || !sel.selectedOptions || sel.selectedOptions.length === 0) {
            return { dialCode: '+91', minDigits: 10, maxDigits: 10, countryName: 'India' };
        }
        const opt = sel.selectedOptions[0];
        const dialCode = opt.getAttribute('data-dial-code') || '+91';
        const minDigits = parseInt(opt.getAttribute('data-min-digits'), 10) || 10;
        const maxDigits = parseInt(opt.getAttribute('data-max-digits'), 10) || 10;
        const countryName = opt.value || 'India';

        return { dialCode, minDigits, maxDigits, countryName };
    }

    // Strip any existing dial codes (database-backed) and non-digit formatting from a phone string
    function extractNationalDigits(val, rules) {
        if (!val) return '';
        let clean = val.replace(/[\s\-\(\)\.]/g, '');

        // Repeatedly strip known dial codes if preceded by '+' (e.g. +91, +91+91, +44, etc.)
        while (clean.startsWith('+')) {
            let matched = false;
            for (let i = 0; i < DB_DIAL_CODES.length; i++) {
                const code = DB_DIAL_CODES[i];
                if (clean.startsWith(code)) {
                    clean = clean.substring(code.length);
                    matched = true;
                    break;
                }
            }
            if (!matched) {
                clean = clean.replace(/^\++/, '');
                break;
            }
        }

        // Isolate pure digits
        let digits = clean.replace(/\D/g, '');

        // If digits entered without '+' but prefixed with country's dial digits (e.g. 919876543210 for India)
        if (rules && rules.dialCode) {
            const dialDigits = rules.dialCode.replace(/\D/g, '');
            if (digits.length > rules.maxDigits && dialDigits !== '' && digits.startsWith(dialDigits)) {
                digits = digits.substring(dialDigits.length);
            }
        }

        // Strip domestic leading 0 if remainder meets or exceeds minDigits (e.g. 07123456789 -> 7123456789)
        if (rules && digits.startsWith('0') && (digits.length - 1 >= rules.minDigits)) {
            digits = digits.replace(/^0+/, '');
        }

        return digits;
    }

    // Format phone field with auto-prepended country code
    function formatPhoneInput(inputEl, forcePrefix = false) {
        if (!inputEl) return '';
        const rules = getCountryPhoneRules();
        const dialCode = rules.dialCode;
        const maxDigits = rules.maxDigits;

        let val = inputEl.value;
        if (!val) {
            if (forcePrefix) {
                inputEl.value = dialCode;
            }
            return '';
        }

        let digits = extractNationalDigits(val, rules);
        if (digits.length > maxDigits) {
            digits = digits.substring(0, maxDigits);
        }

        let formatted = '';
        if (digits.length > 0) {
            formatted = dialCode + digits;
        } else if (forcePrefix || val.startsWith('+')) {
            formatted = dialCode;
        }

        if (inputEl.value !== formatted) {
            inputEl.value = formatted;
        }

        return digits;
    }

    // Update placeholders and format hints according to database country rules
    function updateCountryPhoneHints() {
        const rules = getCountryPhoneRules();
        const dialCode = rules.dialCode;
        const minDigits = rules.minDigits;
        const maxDigits = rules.maxDigits;
        const countryName = rules.countryName;

        const rangeStr = minDigits === maxDigits ? `${minDigits} digits` : `${minDigits}-${maxDigits} digits`;
        const hintText = `Required: ${rangeStr} for ${countryName} (numbers only, country code added automatically)`;
        const optHintText = `Optional: ${rangeStr} for ${countryName} (numbers only)`;

        if (phoneFormatHint) {
            phoneFormatHint.textContent = hintText;
        }
        if (mobileFormatHint) {
            mobileFormatHint.textContent = optHintText;
        }

        const samplePlaceholder = `e.g. ${dialCode} 9876543210`;
        const totalMaxLen = dialCode.length + maxDigits;

        [inputPhone, inputMobile, inputAlternatePhone, inputWhatsapp].forEach(el => {
            if (el) {
                el.setAttribute('placeholder', samplePlaceholder);
                el.setAttribute('maxlength', totalMaxLen.toString());
            }
        });
    }

    // When country changes: update hints, refresh dial code prefix across all phone fields without duplicating
    function handleCountryChange(newCountryName) {
        const rules = getCountryPhoneRules();

        if (leadCountrySelect && $(leadCountrySelect).val() !== newCountryName) {
            $(leadCountrySelect).val(newCountryName).trigger('change.select2-sync');
        }
        if (locationCountrySelect && $(locationCountrySelect).val() !== newCountryName) {
            $(locationCountrySelect).val(newCountryName).trigger('change.select2-sync');
        }

        updateCountryPhoneHints();

        // Update phone fields with new country code while preserving entered national digits
        [
            { el: inputPhone, feedback: phoneFeedback, req: true, label: 'Primary phone' },
            { el: inputMobile, feedback: mobileFeedback, req: false, label: 'Mobile number' },
            { el: inputAlternatePhone, feedback: alternatePhoneFeedback, req: false, label: 'Alternate phone' },
            { el: inputWhatsapp, feedback: whatsappFeedback, req: false, label: 'WhatsApp number' }
        ].forEach(item => {
            if (!item.el) return;
            const currentVal = item.el.value.trim();
            if (currentVal) {
                const digits = extractNationalDigits(currentVal, rules);
                item.el.value = digits.length > 0 ? (rules.dialCode + digits.substring(0, rules.maxDigits)) : '';
                if (item.el.value) {
                    validatePhoneField(item.el, item.feedback, item.req, item.label);
                } else {
                    item.el.classList.remove('is-invalid', 'is-valid');
                    if (item.feedback) item.feedback.style.display = 'none';
                }
            }
        });
    }

    // Keydown guard: allow control keys and digits only, block letters and symbols
    function attachPhoneKeydownGuards(inputEl) {
        if (!inputEl) return;

        inputEl.addEventListener('keydown', function(e) {
            const rules = getCountryPhoneRules();
            const dialCode = rules.dialCode;

            // Allow navigation and control shortcuts: Backspace, Tab, Enter, Esc, Delete
            if ([8, 9, 13, 27, 46].indexOf(e.keyCode) !== -1 ||
                ((e.ctrlKey || e.metaKey) && [65, 67, 86, 88, 90].indexOf(e.keyCode) !== -1) ||
                (e.keyCode >= 35 && e.keyCode <= 40)) {

                // Prevent backspacing over the country dial code prefix
                if (e.keyCode === 8) {
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    if (start <= dialCode.length && end <= dialCode.length) {
                        e.preventDefault();
                    }
                }
                return;
            }

            // Allow digit keys: 0-9 on regular keyboard and numpad
            const isDigit = (!e.shiftKey && e.keyCode >= 48 && e.keyCode <= 57) ||
                            (e.keyCode >= 96 && e.keyCode <= 105);

            if (!isDigit) {
                e.preventDefault();
                return;
            }

            // Check if maxDigits limit reached
            const currentDigits = extractNationalDigits(this.value, rules);
            if (this.selectionStart === this.selectionEnd && currentDigits.length >= rules.maxDigits) {
                e.preventDefault();
            }
        });

        // Auto-prepend country code on focus if field is empty
        inputEl.addEventListener('focus', function() {
            const rules = getCountryPhoneRules();
            if (!this.value.trim()) {
                this.value = rules.dialCode;
            }
        });

        // Clean up on blur if user entered nothing beyond the country code
        inputEl.addEventListener('blur', function() {
            const rules = getCountryPhoneRules();
            const digits = extractNationalDigits(this.value, rules);
            if (digits.length === 0) {
                this.value = '';
            }
        });

        // Real-time formatting on input: keeps country code at front and removes non-digits
        inputEl.addEventListener('input', function() {
            formatPhoneInput(this, false);
        });
    }

    [inputPhone, inputMobile, inputAlternatePhone, inputWhatsapp].forEach(attachPhoneKeydownGuards);

    // Validate phone number field according to country database rules
    function validatePhoneField(inputEl, feedbackEl, isRequired = false, fieldLabel = 'Phone number') {
        if (!inputEl) return true;
        const rules = getCountryPhoneRules();
        const { dialCode, minDigits, maxDigits, countryName } = rules;

        // Auto-format first to guarantee no duplicate country code or invalid characters
        const cleanDigits = formatPhoneInput(inputEl, false);
        const val = inputEl.value.trim();

        // Empty field handling
        if (!val || val === dialCode || val === '+') {
            if (isRequired) {
                inputEl.classList.add('is-invalid');
                inputEl.classList.remove('is-valid');
                if (feedbackEl) {
                    feedbackEl.innerText = `${fieldLabel} is required for ${countryName} (${minDigits === maxDigits ? minDigits + ' digits' : minDigits + '-' + maxDigits + ' digits'}).`;
                    feedbackEl.style.display = 'block';
                }
                return false;
            } else {
                inputEl.classList.remove('is-invalid', 'is-valid');
                if (feedbackEl) feedbackEl.style.display = 'none';
                return true;
            }
        }

        // Must start with country code
        if (!val.startsWith(dialCode)) {
            inputEl.classList.add('is-invalid');
            inputEl.classList.remove('is-valid');
            if (feedbackEl) {
                feedbackEl.innerText = `${fieldLabel} must start with country code ${dialCode}.`;
                feedbackEl.style.display = 'block';
            }
            return false;
        }

        const digitCount = cleanDigits.length;

        // Digit count validation based on selected country database rules
        if (minDigits === maxDigits) {
            if (digitCount !== minDigits) {
                inputEl.classList.add('is-invalid');
                inputEl.classList.remove('is-valid');
                if (feedbackEl) {
                    feedbackEl.innerText = `${fieldLabel} for ${countryName} must be exactly ${minDigits} digits (${digitCount}/${minDigits} entered).`;
                    feedbackEl.style.display = 'block';
                }
                return false;
            }
        } else {
            if (digitCount < minDigits || digitCount > maxDigits) {
                inputEl.classList.add('is-invalid');
                inputEl.classList.remove('is-valid');
                if (feedbackEl) {
                    feedbackEl.innerText = `${fieldLabel} for ${countryName} must be between ${minDigits} and ${maxDigits} digits (${digitCount} entered).`;
                    feedbackEl.style.display = 'block';
                }
                return false;
            }
        }

        // Valid
        inputEl.classList.remove('is-invalid');
        inputEl.classList.add('is-valid');
        if (feedbackEl) feedbackEl.style.display = 'none';
        return true;
    }

    // Email validation function with Gmail typo assistance
    function validateEmail(inputEl, feedbackEl, suggestionEl, isRequired = true) {
        if (!inputEl) return true;
        const val = inputEl.value.trim();

        if (suggestionEl) {
            suggestionEl.classList.add('d-none');
        }

        if (!val) {
            if (isRequired) {
                inputEl.classList.add('is-invalid');
                inputEl.classList.remove('is-valid');
                if (feedbackEl) {
                    feedbackEl.innerText = 'Email address is required.';
                    feedbackEl.style.display = 'block';
                }
                return false;
            } else {
                inputEl.classList.remove('is-invalid', 'is-valid');
                if (feedbackEl) feedbackEl.style.display = 'none';
                return true;
            }
        }

        // Strict email format regex
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!emailRegex.test(val)) {
            inputEl.classList.add('is-invalid');
            inputEl.classList.remove('is-valid');
            if (feedbackEl) {
                feedbackEl.innerText = 'Please enter a valid email address (e.g. name@gmail.com).';
                feedbackEl.style.display = 'block';
            }
            return false;
        }

        // Check for common Gmail typos
        const parts = val.split('@');
        if (parts.length === 2) {
            const domain = parts[1].toLowerCase();
            const typos = ['gmai.com', 'gamil.com', 'gmaill.com', 'gmial.com', 'gmail.co', 'gmaill.co', 'gmai.in'];
            if (typos.includes(domain)) {
                const corrected = parts[0] + '@gmail.com';
                if (suggestionEl && emailSuggestionText) {
                    emailSuggestionText.innerText = `Did you mean ${corrected}? Click to fix.`;
                    suggestionEl.classList.remove('d-none');
                    suggestionEl.onclick = function() {
                        inputEl.value = corrected;
                        suggestionEl.classList.add('d-none');
                        validateEmail(inputEl, feedbackEl, suggestionEl, isRequired);
                        checkDuplicates();
                    };
                }
            }
        }

        inputEl.classList.remove('is-invalid');
        inputEl.classList.add('is-valid');
        if (feedbackEl) feedbackEl.style.display = 'none';
        return true;
    }

    // Real-time event listeners for phone & email
    if (inputEmail) {
        inputEmail.addEventListener('blur', function() {
            validateEmail(inputEmail, emailFeedback, emailSuggestion, true);
            checkDuplicates();
        });
        inputEmail.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateEmail(inputEmail, emailFeedback, emailSuggestion, true);
            }
        });
    }

    if (inputPhone) {
        inputPhone.addEventListener('blur', function() {
            validatePhoneField(inputPhone, phoneFeedback, true, 'Primary phone');
            checkDuplicates();
        });
        inputPhone.addEventListener('input', function() {
            const digits = extractNationalDigits(this.value, getCountryPhoneRules());
            if (this.classList.contains('is-invalid') || digits.length >= getCountryPhoneRules().minDigits) {
                validatePhoneField(inputPhone, phoneFeedback, true, 'Primary phone');
            }
        });
    }

    if (inputMobile) {
        inputMobile.addEventListener('blur', function() {
            validatePhoneField(inputMobile, mobileFeedback, false, 'Mobile number');
            checkDuplicates();
        });
        inputMobile.addEventListener('input', function() {
            const digits = extractNationalDigits(this.value, getCountryPhoneRules());
            if (this.classList.contains('is-invalid') || digits.length >= getCountryPhoneRules().minDigits) {
                validatePhoneField(inputMobile, mobileFeedback, false, 'Mobile number');
            }
        });
    }

    if (inputAlternatePhone) {
        inputAlternatePhone.addEventListener('blur', function() {
            validatePhoneField(inputAlternatePhone, alternatePhoneFeedback, false, 'Alternate phone');
        });
    }

    if (inputWhatsapp) {
        inputWhatsapp.addEventListener('blur', function() {
            validatePhoneField(inputWhatsapp, whatsappFeedback, false, 'WhatsApp number');
        });
    }

    // Initialize Select2 with Country Flags and two-way sync
    if (window.jQuery && $.fn.select2) {
        function formatCountryOption(state) {
            if (!state.id) return state.text;
            const flag = $(state.element).data('flag');
            if (flag) {
                return $('<span><img src="' + flag + '" width="20" height="14" class="me-2 rounded-1 align-text-top" style="object-fit: cover;" /> ' + state.text + '</span>');
            }
            return state.text;
        }

        $('#lead_country').select2({
            theme: 'classic',
            placeholder: 'Select country',
            width: '100%',
            templateResult: formatCountryOption,
            templateSelection: formatCountryOption
        }).on('change', function(e, isSync) {
            if (isSync) return;
            const countryVal = $(this).val();
            handleCountryChange(countryVal);
        });

        $('#location_country').select2({
            theme: 'classic',
            placeholder: 'Select country',
            width: '100%',
            templateResult: formatCountryOption,
            templateSelection: formatCountryOption
        }).on('change', function(e, isSync) {
            if (isSync) return;
            const countryVal = $(this).val();
            handleCountryChange(countryVal);
        });
    } else {
        if (leadCountrySelect) {
            leadCountrySelect.addEventListener('change', function() {
                handleCountryChange(this.value);
            });
        }
        if (locationCountrySelect) {
            locationCountrySelect.addEventListener('change', function() {
                handleCountryChange(this.value);
            });
        }
    }

    // Initialize country hints on page load
    updateCountryPhoneHints();

    // Form submit validation guard
    if (leadCreateForm) {
        leadCreateForm.addEventListener('submit', function(e) {
            const rules = getCountryPhoneRules();
            if (!leadCountrySelect || !leadCountrySelect.value) {
                e.preventDefault();
                if (countryFeedback) {
                    countryFeedback.innerText = 'Please select a country.';
                    countryFeedback.style.display = 'block';
                }
                if (leadCountrySelect) leadCountrySelect.focus();
                return false;
            }

            const isEmailValid = validateEmail(inputEmail, emailFeedback, emailSuggestion, true);
            const isPhoneValid = validatePhoneField(inputPhone, phoneFeedback, true, 'Primary phone');
            const isMobileValid = validatePhoneField(inputMobile, mobileFeedback, false, 'Mobile number');
            const isAlternateValid = validatePhoneField(inputAlternatePhone, alternatePhoneFeedback, false, 'Alternate phone');
            const isWhatsappValid = validatePhoneField(inputWhatsapp, whatsappFeedback, false, 'WhatsApp number');

            if (!isEmailValid || !isPhoneValid || !isMobileValid || !isAlternateValid || !isWhatsappValid) {
                e.preventDefault();
                e.stopPropagation();

                if (!isPhoneValid && inputPhone) {
                    inputPhone.focus();
                } else if (!isEmailValid && inputEmail) {
                    inputEmail.focus();
                } else if (!isMobileValid && inputMobile) {
                    inputMobile.focus();
                } else if (!isWhatsappValid && inputWhatsapp) {
                    inputWhatsapp.focus();
                } else if (!isAlternateValid && inputAlternatePhone) {
                    inputAlternatePhone.focus();
                }
                return false;
            }
        });
    }

    // Real-time Duplicate Lead Detection
    function checkDuplicates() {
        const email = inputEmail ? inputEmail.value.trim() : '';
        const phone = inputPhone ? inputPhone.value.trim() : '';
        const mobile = inputMobile ? inputMobile.value.trim() : '';

        if (!email && !phone && !mobile) {
            if (duplicateAlert) duplicateAlert.classList.add('d-none');
            return;
        }

        fetch('{{ route("leads.contacts.check-duplicate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ email: email, phone: phone, mobile: mobile })
        })
        .then(r => r.json())
        .then(data => {
            if (data.exists && data.duplicates.length > 0) {
                const first = data.duplicates[0];
                if (duplicateMessage) {
                    duplicateMessage.innerText = `Matching lead found: "${first.contact_name}" (${first.email || first.phone}) [Status: ${first.status}].`;
                }
                if (duplicateActions) {
                    duplicateActions.innerHTML = `
                        <a href="/leads/contacts/${first.id}" target="_blank" class="btn btn-sm btn-warning text-dark fw-bold"><i class="fas fa-eye me-1"></i> View Existing Lead</a>
                        <button type="button" class="btn btn-sm btn-outline-dark" onclick="document.getElementById('duplicateAlert').classList.add('d-none')">Create Anyway</button>
                    `;
                }
                if (duplicateAlert) duplicateAlert.classList.remove('d-none');
            } else {
                if (duplicateAlert) duplicateAlert.classList.add('d-none');
            }
        })
        .catch(err => console.error(err));
    }
});
</script>

@endsection
