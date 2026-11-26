@extends('admin.layout.app')

@section('title', $company->exists ? 'Edit Company' : 'Add Company')

@section('content')
@php
    $theme = $company->theme ?? [];
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ $company->exists ? 'Edit Company' : 'Add Company' }}</h4>
            <p class="text-muted mb-0">Company branding and document prefixes.</p>
        </div>
        <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" action="{{ $company->exists ? route('admin.companies.update', $company) : route('admin.companies.store') }}">
        @csrf
        @if($company->exists)
            @method('PUT')
        @endif

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Company Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Company Code</label>
                        <input type="text" name="company_code" class="form-control" required value="{{ old('company_code', $company->company_code) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name', $company->name) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Short Name</label>
                        <input type="text" name="short_name" class="form-control" value="{{ old('short_name', $company->short_name) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Company Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $company->email) }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Company Phone</label>
                        <div class="input-group phone-combo-group @if($errors->has('phone') || $errors->has('phone_number') || $errors->has('phone_country_code')) border border-danger rounded @endif" id="companyPhoneGroup">
                            <span class="input-group-text"><i class="bx bx-phone"></i></span>
                            <div class="country-code-wrapper" style="min-width: 140px; max-width: 155px;">
                                <select name="phone_country_select" id="company_phone_country_select" class="form-select country-code-select">
                                    @php
                                        $activeSelectedDial = old('phone_country_code', $selectedCountryCode ?? '+91');
                                        $activeSelectedCountry = old('phone_country_name', $selectedCountry ?? 'India');
                                    @endphp
                                    @foreach(($countryMap ?? \App\Support\CountryPhone::formMap()) as $cName => $meta)
                                        @php
                                            $cDial = $meta['dial_code'];
                                            $cIso = strtolower($meta['iso']);
                                            $cFlag = 'https://flagcdn.com/w20/' . $cIso . '.png';
                                            $cMin = $meta['min_digits'] ?? 10;
                                            $cMax = $meta['max_digits'] ?? 10;
                                            $isOptSelected = ($cName === $activeSelectedCountry || (!$activeSelectedCountry && $cDial === $activeSelectedDial));
                                        @endphp
                                        <option value="{{ $cName }}" data-dial-code="{{ $cDial }}" data-country="{{ $cName }}" data-flag="{{ $cFlag }}" data-min-digits="{{ $cMin }}" data-max-digits="{{ $cMax }}" data-iso="{{ $cIso }}" {{ $isOptSelected ? 'selected' : '' }}>
                                            {{ $cName }} ({{ $cDial }}) - {{ $cMin === $cMax ? $cMin . ' digits' : $cMin . '-' . $cMax . ' digits' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="phone_country_code" id="company_phone_country_code" value="{{ old('phone_country_code', $selectedCountryCode ?? '+91') }}">
                            <input type="hidden" name="phone_country_name" id="company_phone_country_name" value="{{ old('phone_country_name', $selectedCountry ?? 'India') }}">
                            <input type="text" name="phone_number" id="input_company_phone_number"
                                class="form-control @error('phone_number') is-invalid @enderror @error('phone') is-invalid @enderror"
                                placeholder="98765 43210 (10 digits)"
                                value="{{ old('phone_number', $phoneDigits ?? '') }}" maxlength="18">
                            <input type="hidden" name="phone" id="hidden_company_phone" value="{{ old('phone', $company->phone ?? '') }}">
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-1 px-1">
                            <small id="companyPhoneRuleHint" class="text-muted" style="font-size: 0.78rem; font-weight: 500;">
                                <i class="bx bx-info-circle me-1 text-primary"></i> <span id="companyPhoneRuleText">Required: 10 digits for India (+91)</span>
                            </small>
                            <small id="companyPhoneDigitCounter" class="badge rounded-pill" style="font-size: 0.72rem; background: #EEF2FF; color: #2F6BFF; border: 1px solid rgba(47, 107, 255, 0.2); transition: all 0.2s ease;">
                                <span id="companyCurrentDigitCount">0</span>/<span id="companyRequiredDigitCount">10</span> digits
                            </small>
                        </div>
                        <div id="companyPhoneValidationMsg" class="validation-feedback-pill" style="display: {{ ($errors->has('phone') || $errors->has('phone_number') || $errors->has('phone_country_code')) ? 'flex' : 'none' }}; align-items: center; gap: 5px; font-size: 0.78rem; font-weight: 600; color: #dc2626; margin-top: 5px;">
                            <i class="bx bx-error-circle"></i> <span id="companyPhoneValidationText">{{ $errors->first('phone_number') ?: ($errors->first('phone') ?: ($errors->first('phone_country_code') ?: 'Please enter a valid phone number.')) }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Website</label>
                        <input type="url" name="website" class="form-control" value="{{ old('website', $company->website) }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $company->address) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Registration & Prefixes</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">GST</label>
                        <input type="text" name="gst_number" class="form-control" value="{{ old('gst_number', $company->gst_number) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">PAN</label>
                        <input type="text" name="pan_number" class="form-control" value="{{ old('pan_number', $company->pan_number) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Registration Number</label>
                        <input type="text" name="registration_number" class="form-control" value="{{ old('registration_number', $company->registration_number) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Employee ID Prefix</label>
                        <input type="text" name="employee_id_prefix" class="form-control" required value="{{ old('employee_id_prefix', $company->employee_id_prefix) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Leave Prefix</label>
                        <input type="text" name="leave_prefix" class="form-control" required value="{{ old('leave_prefix', $company->leave_prefix) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Payroll Prefix</label>
                        <input type="text" name="payroll_prefix" class="form-control" required value="{{ old('payroll_prefix', $company->payroll_prefix) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Payslip Prefix</label>
                        <input type="text" name="payslip_prefix" class="form-control" required value="{{ old('payslip_prefix', $company->payslip_prefix) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Branding</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Logo</label>
                        <input type="file" name="logo" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Favicon</label>
                        <input type="file" name="favicon" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Primary Color</label>
                        <input type="color" name="primary_color" class="form-control form-control-color" value="{{ old('primary_color', $theme['primary_color'] ?? '#7C3AED') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Secondary Color</label>
                        <input type="color" name="secondary_color" class="form-control form-control-color" value="{{ old('secondary_color', $theme['secondary_color'] ?? '#8B5CF6') }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Greeting Message</label>
                        <input type="text" name="greeting_message" class="form-control" value="{{ old('greeting_message', $company->greeting_message) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'trial' => 'Trial', 'suspended' => 'Suspended'] as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $company->status ?: 'active') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Company</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<style>
.phone-combo-group {
    display: flex;
    align-items: stretch;
}
.phone-combo-group .country-code-wrapper {
    min-width: 140px;
    max-width: 155px;
    flex-shrink: 0;
}
.phone-combo-group .country-code-select {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    font-weight: 600;
}
.phone-combo-group .select2-container--bootstrap-5 .select2-selection,
.phone-combo-group .select2-container--default .select2-selection--single {
    height: 38px !important;
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
    display: flex !important;
    align-items: center !important;
    padding: 0 8px !important;
}
.phone-combo-group .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px !important;
    font-weight: 600;
    font-size: 0.88rem;
    padding-left: 0 !important;
}
.phone-combo-group .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 38px !important;
    right: 6px !important;
}
.country-code-select-dropdown {
    min-width: 320px !important;
    max-width: 420px !important;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectEl = document.getElementById('company_phone_country_select');
    const hiddenCode = document.getElementById('company_phone_country_code');
    const hiddenName = document.getElementById('company_phone_country_name');
    const phoneInput = document.getElementById('input_company_phone_number');
    const hiddenPhone = document.getElementById('hidden_company_phone');
    const ruleText = document.getElementById('companyPhoneRuleText');
    const countBadge = document.getElementById('companyPhoneDigitCounter');
    const currentCount = document.getElementById('companyCurrentDigitCount');
    const requiredCount = document.getElementById('companyRequiredDigitCount');
    const valMsg = document.getElementById('companyPhoneValidationMsg');
    const valText = document.getElementById('companyPhoneValidationText');
    const groupEl = document.getElementById('companyPhoneGroup');

    function formatOption(state) {
        if (!state.id) return state.text;
        const el = state.element;
        const flag = (el ? $(el).data("flag") : null) || 'https://flagcdn.com/w20/in.png';
        const country = (el ? $(el).data("country") : null) || state.text;
        const dial = (el ? $(el).data("dial-code") : null) || '+91';
        const minDigits = el ? $(el).data("min-digits") : null;
        const maxDigits = el ? $(el).data("max-digits") : null;
        const digitBadge = minDigits ? ('<span class="badge bg-light text-secondary ms-auto" style="font-size: 0.72rem; border: 1px solid #e2e8f0; font-weight: 600;">' + (minDigits === maxDigits ? minDigits + ' digits' : minDigits + '-' + maxDigits + ' digits') + '</span>') : '';

        return $('<div class="d-flex align-items-center justify-content-between py-1 w-100" style="gap: 8px;"><span><img src="' + flag + '" width="20" height="14" style="object-fit: cover; border-radius: 2px; margin-right: 8px; vertical-align: middle; box-shadow: 0 1px 3px rgba(0,0,0,0.1);"/> ' + country + ' <strong style="color: #696cff;">(' + dial + ')</strong></span>' + digitBadge + '</div>');
    }

    function formatSelection(state) {
        if (!state.id) return state.text;
        const el = state.element || document.querySelector('#company_phone_country_select option:checked');
        const flag = (el ? $(el).data("flag") : null) || 'https://flagcdn.com/w20/in.png';
        const dial = (el ? $(el).data("dial-code") : null) || '+91';
        return $('<span><img src="' + flag + '" width="18" height="12" style="object-fit: cover; border-radius: 2px; margin-right: 6px; vertical-align: middle; box-shadow: 0 1px 2px rgba(0,0,0,0.1);"/> <strong>' + dial + '</strong></span>');
    }

    function getSelectedRules() {
        if (!selectEl) return { minDigits: 10, maxDigits: 10, country: 'India', code: '+91' };
        let opt = selectEl.selectedIndex >= 0 ? selectEl.options[selectEl.selectedIndex] : null;
        if (!opt) opt = selectEl.options[0];
        const minDigits = opt ? (parseInt(opt.getAttribute('data-min-digits')) || 10) : 10;
        const maxDigits = opt ? (parseInt(opt.getAttribute('data-max-digits')) || 10) : 10;
        const country = opt ? (opt.getAttribute('data-country') || 'India') : 'India';
        const code = opt ? (opt.getAttribute('data-dial-code') || '+91') : '+91';
        return { minDigits, maxDigits, country, code };
    }

    function syncPhone() {
        const rules = getSelectedRules();
        if (hiddenCode) hiddenCode.value = rules.code;
        if (hiddenName) hiddenName.value = rules.country;
        const digits = phoneInput ? phoneInput.value.trim() : '';
        if (hiddenPhone) {
            hiddenPhone.value = digits ? (rules.code + ' ' + digits) : '';
        }
    }

    function updateAttributes() {
        const rules = getSelectedRules();
        const expectedDesc = (rules.minDigits === rules.maxDigits)
            ? `${rules.minDigits} digits`
            : `${rules.minDigits}-${rules.maxDigits} digits`;

        if (phoneInput) {
            phoneInput.placeholder = (rules.code === '+91')
                ? '98765 43210 (10 digits)'
                : `e.g. ${expectedDesc} for ${rules.country}`;
            phoneInput.maxLength = rules.maxDigits + 3;
        }

        if (ruleText) {
            ruleText.textContent = `Required: ${expectedDesc} for ${rules.country} (${rules.code})`;
        }
        if (requiredCount) {
            requiredCount.textContent = (rules.minDigits === rules.maxDigits) ? rules.minDigits : `${rules.minDigits}-${rules.maxDigits}`;
        }
        updateCounter();
    }

    function updateCounter() {
        const rawDigits = phoneInput ? phoneInput.value : '';
        const digitsOnly = rawDigits.replace(/\D/g, '');
        const rules = getSelectedRules();
        const isValid = (digitsOnly.length >= rules.minDigits && digitsOnly.length <= rules.maxDigits && !/^0+$/.test(digitsOnly));
        const reqDisplay = (rules.minDigits === rules.maxDigits) ? rules.minDigits : (rules.minDigits + '-' + rules.maxDigits);

        if (countBadge) {
            if (digitsOnly.length === 0) {
                countBadge.style.background = '#EEF2FF';
                countBadge.style.color = '#2F6BFF';
                countBadge.style.borderColor = 'rgba(47, 107, 255, 0.2)';
                countBadge.innerHTML = '<span id="companyCurrentDigitCount">0</span>/<span id="companyRequiredDigitCount">' + reqDisplay + '</span> digits';
            } else if (isValid) {
                countBadge.style.background = '#ECFDF5';
                countBadge.style.color = '#059669';
                countBadge.style.borderColor = 'rgba(16, 185, 129, 0.3)';
                countBadge.innerHTML = '<i class="bx bx-check-circle me-1 text-success"></i> <span id="companyCurrentDigitCount">' + digitsOnly.length + '</span>/<span id="companyRequiredDigitCount">' + reqDisplay + '</span> digits';
            } else {
                countBadge.style.background = '#FEF2F2';
                countBadge.style.color = '#DC2626';
                countBadge.style.borderColor = 'rgba(220, 38, 38, 0.3)';
                countBadge.innerHTML = '<i class="bx bx-info-circle me-1 text-danger"></i> <span id="companyCurrentDigitCount">' + digitsOnly.length + '</span>/<span id="companyRequiredDigitCount">' + reqDisplay + '</span> digits';
            }
        }
    }

    function validatePhone() {
        const rawVal = phoneInput ? phoneInput.value.trim() : '';
        const digits = rawVal.replace(/\D/g, '');
        const rules = getSelectedRules();
        updateCounter();

        if (!rawVal) {
            clearErr();
            return true;
        }

        const expectedDesc = (rules.minDigits === rules.maxDigits)
            ? `exactly ${rules.minDigits} digits`
            : `between ${rules.minDigits} and ${rules.maxDigits} digits`;

        if (digits.length === 0 || /^0+$/.test(digits)) {
            showErr(`Please enter a valid phone number for ${rules.country} (${rules.code}).`);
            return false;
        }

        if (digits.length < rules.minDigits || digits.length > rules.maxDigits) {
            showErr(`Phone number for ${rules.country} (${rules.code}) must have ${expectedDesc} (currently entered ${digits.length} digits).`);
            return false;
        }

        clearErr();
        return true;
    }

    function showErr(msg) {
        if (groupEl) groupEl.classList.add('border', 'border-danger', 'rounded');
        if (valMsg) valMsg.style.display = 'flex';
        if (valText) valText.textContent = msg;
    }

    function clearErr() {
        if (groupEl) groupEl.classList.remove('border', 'border-danger');
        if (valMsg) valMsg.style.display = 'none';
    }

    if (window.jQuery && $('#company_phone_country_select').length && $.fn.select2) {
        $('#company_phone_country_select').select2({
            theme: "bootstrap-5",
            templateResult: formatOption,
            templateSelection: formatSelection,
            width: '100%',
            dropdownAutoWidth: true,
            dropdownCssClass: 'country-code-select-dropdown',
            dropdownParent: $(document.body)
        }).on('change select2:select', function () {
            syncPhone();
            updateAttributes();
            if (phoneInput && phoneInput.value.trim().length > 0) {
                validatePhone();
            }
        });
    } else if (selectEl) {
        selectEl.addEventListener('change', function() {
            syncPhone();
            updateAttributes();
            if (phoneInput && phoneInput.value.trim().length > 0) {
                validatePhone();
            }
        });
    }

    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9\s\-()]/g, '');
            syncPhone();
            updateCounter();
            if (valMsg && valMsg.style.display === 'flex') {
                validatePhone();
            }
        });

        phoneInput.addEventListener('blur', function() {
            if (this.value.trim().length > 0) {
                validatePhone();
            }
        });
    }

    const form = selectEl ? selectEl.closest('form') : null;
    if (form) {
        form.addEventListener('submit', function(e) {
            syncPhone();
            if (phoneInput && phoneInput.value.trim().length > 0) {
                if (!validatePhone()) {
                    e.preventDefault();
                    phoneInput.focus();
                }
            }
        });
    }

    syncPhone();
    updateAttributes();
});
</script>
@endpush
