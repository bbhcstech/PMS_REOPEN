@extends('admin.layout.app')

@section('content')
<div class="container mt-4">
    <h5>Edit Client</h5>
    
    
    @if(session('error'))
    <div class="alert alert-danger" style="background-color: #dc3545; color: white; border-color: #dc3545;">
        {{ session('error') }}
    </div>
@endif
            
    <form action="{{ route('clients.update', $client->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Account Details --}}
        <div class="card mb-4">
            <div class="card-header py-3 px-4 border-bottom">
                <h5 class="mb-0 fw-bold text-dark">Account Details</h5>
            </div>
            <div class="card-body row g-3">

                <div class="col-md-3">
                    <label>Salutation</label>
                    <select name="salutation" class="form-control">
                        <option value="">Select salutation</option>
                        @foreach(['Mr', 'Mrs', 'Miss', 'Dr', 'Sir', 'Madam'] as $salute)
                            <option value="{{ $salute }}" {{ $client->salutation == $salute ? 'selected' : '' }}>{{ $salute }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Client Name <sup class="text-danger">*</sup></label>
                    <input name="name" type="text" class="form-control" value="{{ $client->name }}" required>
                </div>

                <div class="col-md-3">
                    <label>Email <sup class="text-danger">*</sup></label>
                    <input name="email" required type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $client->email) }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" autocomplete="off" minlength="8">
                        <button type="button" class="btn btn-outline-secondary toggle-password" title="Show/Hide Password">
                            <i class="fa fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary generate-password" title="Generate Random Password">
                            <i class="fa fa-random"></i>
                        </button>
                    </div>
                    <small class="form-text text-muted d-block" id="password_help_text">Leave blank to keep current password (or min 8 chars with 1 uppercase, 1 lowercase, 1 number & 1 special char)</small>
                    <div class="invalid-feedback" id="password_feedback">Password must be at least 8 characters with 1 uppercase, 1 lowercase, 1 number, and 1 special character.</div>
                </div>

                <div class="col-md-4">
                    <label>Country <sup class="text-danger">*</sup></label>
                   <select name="country" id="country" class="form-select form-select-sm select2">
                        <option value="">Select</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->name }}" 
                                    data-flag="{{ $country->flag_url }}"
                                    data-dial-code="{{ $country->phone_code ?? '+91' }}"
                                    data-min-digits="{{ $country->min_digits ?? 10 }}"
                                    data-max-digits="{{ $country->max_digits ?? 10 }}"
                                    {{ $client->country == $country->name ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Mobile <sup class="text-danger">*</sup></label>
                    <div class="input-group">
                        <select name="mobile_country_code" id="mobile_country_code" class="form-select" style="max-width: 115px; flex-shrink: 0;">
                            @php
                                $cMobCode = '+91';
                                if ($client->mobile && str_starts_with($client->mobile, '+')) {
                                    foreach($countries as $c) {
                                        if (str_starts_with($client->mobile, $c->phone_code)) {
                                            $cMobCode = $c->phone_code;
                                            break;
                                        }
                                    }
                                }
                                $selMobCode = old('mobile_country_code', $cMobCode);
                            @endphp
                            @foreach ($countries as $country)
                                <option value="{{ $country->phone_code }}" data-country="{{ $country->name }}" data-min-digits="{{ $country->min_digits ?? 10 }}" data-max-digits="{{ $country->max_digits ?? 10 }}" {{ $selMobCode == $country->phone_code ? 'selected' : '' }}>
                                    {{ $country->iso_code ? $country->iso_code . ' ' : '' }}({{ $country->phone_code }})
                                </option>
                            @endforeach
                        </select>
                        <input name="mobile" id="client_mobile" type="text" class="form-control" value="{{ $client->mobile }}" required>
                    </div>
                    <small class="text-muted d-block" id="mobile_format_hint">Format: +91XXXXXXXXXX (10 digits)</small>
                    <div class="invalid-feedback" id="mobile_feedback">Please enter a valid mobile number for selected country.</div>
                </div>

                <div class="col-md-4">
                    <label>Profile Picture</label>
                    <input name="profile_picture" type="file" class="form-control">
                    @if($client->profile_picture)
                        <small>Current: <a href="{{ asset($client->profile_picture) }}" target="_blank">View</a></small>
                    @endif
                </div>

                <div class="col-md-4">
                    <label>Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select</option>
                        @foreach(['Male', 'Female', 'Other'] as $gender)
                            <option {{ $client->gender == $gender ? 'selected' : '' }}>{{ $gender }}</option>
                        @endforeach
                    </select>
                </div>

                <!--<div class="col-md-4">-->
                <!--    <label>Change Language</label>-->
                <!--    <select name="language" id="language" class="form-select form-select-sm select2">-->
                <!--        @foreach(['en'=>'English', 'bn'=>'Bengali', 'hi'=>'Hindi', 'fr'=>'French', 'de'=>'German'] as $code => $lang)-->
                <!--            <option value="{{ $code }}" {{ $client->language == $code ? 'selected' : '' }}>{{ $lang }}</option>-->
                <!--        @endforeach-->
                <!--    </select>-->
                <!--</div>-->
                
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Change Language</label>
                    <select name="language" id="language" class="form-select form-select-sm select2">
                        @include('admin.clients.partials.language-options', ['selectedLanguage' => old('language', $client->language)])
                    </select>
                </div>


                <div class="col-md-6">
                    <label>Client Category</label>
                    <select name="client_category_id" class="form-select">
                        <option value="">Select</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $client->client_category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Client Sub Category</label>
                    <select name="client_sub_category_id" class="form-select">
                        <option value="">Select</option>
                        @foreach($subcategories as $sub)
                            <option value="{{ $sub->id }}" {{ $client->client_sub_category_id == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Login Allowed?  <sup class="text-danger">*</sup></label><br>
                    <input type="radio" name="login_allowed" value="1" {{ $client->login_allowed ? 'checked' : '' }}> Yes
                    <input type="radio" name="login_allowed" value="0" {{ !$client->login_allowed ? 'checked' : '' }}> No
                </div>

                <div class="col-md-6">
                    <label>Receive Email Notifications?</label><br>
                    <input type="radio" name="email_notifications" value="1" {{ $client->email_notifications ? 'checked' : '' }}> Yes
                    <input type="radio" name="email_notifications" value="0" {{ !$client->email_notifications ? 'checked' : '' }}> No
                </div>

            </div>
        </div>

        {{-- Company Details --}}
        <div class="card mb-4">
            <div class="card-header py-3 px-4 border-bottom">
                <h5 class="mb-0 fw-bold text-dark">Company Details</h5>
            </div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label>Company Name</label>
                    <input name="company_name" id="company_name" type="text" class="form-control" value="{{ old('company_name', $client->company_name) }}">
                </div>

                <div class="col-md-6">
                    <label>Official Website</label>
                    <input name="website" id="website" type="url" class="form-control" value="{{ old('website', $client->website) }}">
                </div>

                <div class="col-md-3">
                    <label>Tax Name</label>
                    <input name="tax_name" id="tax_name" type="text" class="form-control" value="{{ old('tax_name', $client->tax_name) }}">
                </div>

                <div class="col-md-3">
                    <label>GST/VAT Number</label>
                    <input name="tax_number" id="tax_number" type="text" class="form-control" value="{{ old('tax_number', $client->tax_number) }}">
                </div>

                <div class="col-md-3">
                    <label>Country</label>
                    <select name="company_country" id="company_country" class="form-control select2">
                        <option value="">Select country</option>
                        @foreach($countries as $c)
                            <option value="{{ $c->name }}" 
                                    data-flag="{{ $c->flag_url }}"
                                    data-dial-code="{{ $c->phone_code ?? '+91' }}"
                                    data-min-digits="{{ $c->min_digits ?? 10 }}"
                                    data-max-digits="{{ $c->max_digits ?? 10 }}"
                                    {{ old('company_country', $client->company_country ?: ($client->country ?: 'India')) == $c->name ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Office Phone</label>
                    <div class="input-group">
                        <select name="office_country_code" id="office_country_code" class="form-select" style="max-width: 115px; flex-shrink: 0;">
                            @php
                                $cOffCode = '+91';
                                if ($client->office_phone && str_starts_with($client->office_phone, '+')) {
                                    foreach($countries as $c) {
                                        if (str_starts_with($client->office_phone, $c->phone_code)) {
                                            $cOffCode = $c->phone_code;
                                            break;
                                        }
                                    }
                                }
                                $selOffCode = old('office_country_code', $cOffCode);
                            @endphp
                            @foreach ($countries as $country)
                                <option value="{{ $country->phone_code }}" data-country="{{ $country->name }}" data-min-digits="{{ $country->min_digits ?? 10 }}" data-max-digits="{{ $country->max_digits ?? 10 }}" {{ $selOffCode == $country->phone_code ? 'selected' : '' }}>
                                    {{ $country->iso_code ? $country->iso_code . ' ' : '' }}({{ $country->phone_code }})
                                </option>
                            @endforeach
                        </select>
                        <input name="office_phone" id="office_phone" type="text" class="form-control" value="{{ old('office_phone', $client->office_phone) }}">
                    </div>
                    <div class="invalid-feedback" id="office_phone_feedback"></div>
                    <small class="text-muted d-block" id="office_phone_format_hint">Format: +91XXXXXXXXXX (10 digits)</small>
                </div>

                <div class="col-md-4">
                    <label>State / Province</label>
                    <select name="state" id="state" class="form-control select2">
                        <option value="">Select state/province</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label>City</label>
                    <select name="city" id="city" class="form-control select2">
                        <option value="">Select city</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="d-flex align-items-center justify-content-between">
                        <span>Postal Code / Pincode</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 10px; font-weight: normal;">
                            <i class="fas fa-magic me-1"></i>Auto-detects Area, City & State
                        </span>
                    </label>
                    <div class="position-relative">
                        <input name="postal_code" id="postal_code" type="text" class="form-control pe-5" value="{{ old('postal_code', $client->postal_code) }}" autocomplete="off">
                        <div id="pincode_spinner" class="position-absolute end-0 top-50 translate-middle-y me-3" style="display: none; pointer-events: none;">
                            <i class="fas fa-spinner fa-spin text-primary"></i>
                        </div>
                    </div>
                    <div id="pincode_feedback_pill" class="mt-1" style="display: none;"></div>
                </div>

                <!-- Detected Area / Locality Selector (Auto-shown when pincode has multiple areas) -->
                <div class="col-md-12" id="area_selection_wrapper" style="display: none;">
                    <div class="p-3 rounded-3 my-2" style="background: rgba(37, 99, 235, 0.05); border: 1px solid rgba(37, 99, 235, 0.2);">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label fw-semibold text-primary mb-0">
                                <i class="fas fa-map-marker-alt me-1"></i> Auto-Detected Areas / Localities for this Pincode
                            </label>
                            <span class="badge bg-primary text-white" id="area_count_badge">0 areas</span>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <select id="detected_area_select" class="form-select form-control">
                                    <option value="">-- Choose specific area / post office --</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i> Selecting an area automatically populates Company Address below.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <label>Company Address</label>
                    <textarea name="company_address" class="form-control" rows="2">{{ old('company_address', $client->company_address) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label>Shipping Address</label>
                    <textarea name="shipping_address" class="form-control" rows="2">{{ old('shipping_address', $client->shipping_address) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label>Note</label>
                    <textarea name="note" class="form-control" rows="2">{{ old('note', $client->note) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label>Company Logo</label>
                    <input name="company_logo" type="file" class="form-control">
                    &nbsp;
                    @if($client->company_logo)
                    <div class="border p-2 rounded bg-light" style="max-width: 150px;">
                        <img src="{{ asset($client->company_logo) }}" alt="Company Logo" class="img-fluid rounded" style="max-height: 100px;">
                        <div>
                            <small class="text-muted">Current Logo</small>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="col-md-6">
                    <label>Added By</label>
                    <select name="added_by" class="form-control">
                        <option value="">Select</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('added_by', $client->added_by) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="mb-3 text-end">
            <button class="btn btn-primary">Update Client</button>
            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </form>
</div>
<!-- ✅ jQuery First -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Optional: Bootstrap JS (after jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- ✅ Then your custom script -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<!-- Bootstrap-select CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/css/bootstrap-select.min.css" />

<!-- Bootstrap-select JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/js/bootstrap-select.min.js"></script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle Show/Hide Password
        document.querySelector('.toggle-password').addEventListener('click', function () {
            const passwordField = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Generate Random Password
        document.querySelector('.generate-password').addEventListener('click', function () {
            const passwordField = document.getElementById('password');
            const randomPassword = Math.random().toString(36).slice(-10) + '!A1'; // ensure complexity
            passwordField.value = randomPassword;
        });
    });
</script>


<script>

setTimeout(() => {
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
}, 500);

$('#addCategoryModal').on('shown.bs.modal', function () {
    $('#categoryName').focus();
});

$('#addSubCategoryModal').on('shown.bs.modal', function () {
    $('#subcategoryName').focus();
});

    $(document).ready(function () {
     $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

   
    
   $('#addCategoryForm').submit(function(e) {
    e.preventDefault();

    const name = $('#categoryName').val().trim();
    if (name === '') {
        alert('Please enter a category name');
        return;
    }

    const form = $(this);
    const formData = form.serialize();

    $.ajax({
        type: 'POST',
        url: "{{ route('client-categories.store') }}",
        data: formData,
        success: function(data) {
           
            $('#addCategoryModal').modal('hide');
            form[0].reset();

            // Clean backdrop in case it gets stuck
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        },
        error: function(xhr) {
            if (xhr.status === 422 && xhr.responseJSON.errors.name) {
                alert(xhr.responseJSON.errors.name[0]);
            } else {
                alert('Error: ' + xhr.responseJSON.message || 'Validation failed');
            }
        }
    });
});

   
    // ✅ Add Sub-Category
    $('#addSubCategoryForm').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = form.serialize();

        $.ajax({
            type: 'POST',
            url: "{{ route('client-sub-categories.store') }}",
            data: formData,
            success: function(data) {
                $('#client_sub_category_id').append(
                    `<option value="${data.id}" selected>${data.name}</option>`
                );
            
                $('#addSubCategoryModal').modal('hide');
                form[0].reset();
            
                // ✅ Clean stuck backdrop (just like category modal)
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            },
                        error: function(xhr) {
                alert('Error: ' + xhr.responseJSON.message || 'Validation failed');
            }
        });
    });

});
</script>

<script>
// $(document).ready(function () {
//     function formatCountry (state) {
//         if (!state.id) return state.text; // default option
//         let flag = $(state.element).data("flag");
//         if (flag) {
//             return $('<span><img src="' + flag + '" width="20" class="me-2"/> ' + state.text + '</span>');
//         }
//         return state.text;
//     }

//     $('#country').select2({
//         theme: "bootstrap-5",   // ✅ Bootstrap 5 theme
//         templateResult: formatCountry,
//         templateSelection: formatCountry,
//         placeholder: "Select Country",
//         allowClear: true
//     });
    
    
// });


$(document).ready(function () {
    window.locationData = @json(\App\Support\LocationData::data());

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
            timeout: 15000, // the server may try a second location provider
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

            populateCities(country, detectedState, detectedCity);
        }

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
                theme: "bootstrap-5",
                width: '100%',
                placeholder: "-- Choose specific area / post office --"
            });
        } else {
            $('#area_selection_wrapper').slideUp(150);
        }

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

    function formatOption (state) {
        if (!state.id) return state.text;
        let flag = $(state.element).data("flag");
        if (flag) {
            return $('<span><img src="' + flag + '" width="20" class="me-2"/> ' + state.text + '</span>');
        }
        return state.text;
    }

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

    let companyCountryTouchedByUser = false;

    // ✅ Country dropdown
    $('#country').select2({
        theme: "bootstrap-5",
        templateResult: formatOption,
        templateSelection: formatOption,
        placeholder: "Select Country",
        allowClear: true
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

        // Auto-sync company country if not manually touched
        if (!companyCountryTouchedByUser && $('#company_country').length) {
            const clientCountry = $(this).val();
            if ($('#company_country').val() !== clientCountry) {
                $('#company_country').val(clientCountry).trigger('change', [true]);
            }
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

    // ✅ Company Country dropdown
    $('#company_country').select2({
        theme: "bootstrap-5",
        templateResult: formatOption,
        templateSelection: formatOption,
        placeholder: "Select Country",
        allowClear: true
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

        populateStates(countryVal, $('#state').val());
        populateCities(countryVal, $('#state').val(), $('#city').val());
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

    // State & City dropdowns with tags
    $('#state').select2({
        theme: "bootstrap-5",
        tags: true,
        placeholder: "Select or type state",
        allowClear: true
    }).on('change', function() {
        const countryVal = $('#company_country').val() || $('#country').val();
        populateCities(countryVal, $(this).val(), $('#city').val());
    });

    $('#city').select2({
        theme: "bootstrap-5",
        tags: true,
        placeholder: "Select or type city",
        allowClear: true
    });

    // Postal Code Auto-Detection
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

    // Selecting an auto-detected area populates company address
    $('#detected_area_select').on('change', function() {
        const selectedArea = $(this).val();
        if (!selectedArea) return;

        const $addr = $('textarea[name="company_address"]');
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

    // Trigger on load if postal_code already filled
    if ($('#postal_code').val() && $('#postal_code').val().trim()) {
        performPincodeLookup($('#postal_code').val().trim());
    }

    updateMobileFormatHint();
    updateOfficePhoneFormatHint();

    const initialCompanyCountry = $('#company_country').val() || $('#country').val() || 'India';
    const clientState = @json(old('state', $client->state ?? ''));
    const clientCity = @json(old('city', $client->city ?? ''));
    populateStates(initialCompanyCountry, clientState);
    populateCities(initialCompanyCountry, clientState, clientCity);

    // ✅ Language dropdown (with search enabled)
    $('#language').select2({
        theme: "bootstrap-5",
        templateResult: formatOption,
        templateSelection: formatOption,
        placeholder: "Select Language",
        allowClear: true
    });

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

    function checkPasswordComplexity(val, isRequired = false) {
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

    function validatePasswordInput(inputId = 'password', isRequired = false) {
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

    // Generate Compliant Password
    $('.generate-password').on('click', function () {
        const randomPassword = generateCompliantPassword(12);
        $('#password').val(randomPassword).trigger('input');
        validatePasswordInput('password', false);
    });

    $('#password').on('input blur', function() {
        if ($(this).val()) {
            validatePasswordInput('password', false);
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Form submit validation
    $('form').on('submit', function(e) {
        if ($('#password').length && $('#password').val().trim() !== '') {
            if (!validatePasswordInput('password', false)) {
                e.preventDefault();
                $('#password').focus();
                return false;
            }
        }

        if (!validateMobileInput('client_mobile', true)) {
            e.preventDefault();
            $('#client_mobile').focus();
            return false;
        }

        if ($('#office_phone').length && $('#office_phone').val().trim() !== '') {
            if (!validateMobileInput('office_phone', false)) {
                e.preventDefault();
                $('#office_phone').focus();
                return false;
            }
        }
    });
});

</script>

<script>
// $(document).ready(function() {
//     function formatFlag(option) {
//         if (!option.id) { return option.text; }
//         var flagUrl = $(option.element).data('flag');
//         if (!flagUrl) { return option.text; }
//         return $('<span><img src="' + flagUrl + '" class="me-2" style="width:18px;"/> ' + option.text + '</span>');
//     }

//     $('#language').select2({
//         templateResult: formatFlag,
//         templateSelection: formatFlag,
//         minimumResultsForSearch: -1 // hides search box if not needed
//     });
// });
</script>


@endsection
