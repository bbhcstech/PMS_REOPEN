@extends('admin.layout.app')

@section('title', $mode === 'edit' ? 'Edit Collaborating Company' : 'Add Collaborating Company')

@section('content')
<div class="partner-page">
    <section class="partner-hero">
        <div>
            <span class="partner-eyebrow"><i class="fas fa-handshake"></i> Company Network</span>
            <h1>{{ $mode === 'edit' ? 'Edit Collaborating Company' : 'Add Collaborating Company' }}</h1>
            <p>Maintain collaboration details, services, contact information, and social media links.</p>
        </div>
        <div class="partner-actions">
            <a href="{{ route('collaborating-companies.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </section>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the highlighted details.</strong>
        </div>
    @endif

    <form method="POST" action="{{ $mode === 'edit' ? route('collaborating-companies.update', $company) : route('collaborating-companies.store') }}" class="partner-form-card" enctype="multipart/form-data">
        @csrf
        @if($mode === 'edit')
            @method('PUT')
        @endif

        <div class="partner-form-grid">
            <div>
                <label class="partner-form-label">
                    <span>Company Name <span class="text-danger">*</span></span>
                    <span class="partner-badge-required">Required</span>
                </label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $company->name) }}" required>
                @error('name')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
            </div>
            <div>
                <label class="partner-form-label">
                    <span>Company Image <span class="text-danger">*</span></span>
                    <span class="partner-badge-required">Required</span>
                </label>
                <input type="file" name="company_image" class="form-control" accept="image/png,image/jpeg,image/webp" {{ ($mode === 'create' && !$company->image_path) ? 'required' : '' }}>
                @error('company_image')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
            </div>
            @if($company->image_path)
                <div class="full">
                    <label class="partner-form-label">
                        <span>Current Image</span>
                    </label>
                    <img src="{{ asset($company->image_path) }}" alt="{{ $company->name }}" class="partner-form-preview">
                </div>
            @endif
            <div>
                <label class="partner-form-label">
                    <span>Industry <span class="text-danger">*</span></span>
                    <span class="partner-badge-required">Required</span>
                </label>
                <input type="text" name="industry" class="form-control" value="{{ old('industry', $company->industry) }}" placeholder="IT, Marketing, Finance" required>
                @error('industry')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
            </div>
            <div>
                <label class="partner-form-label">
                    <span>Collaboration Type <span class="text-danger">*</span></span>
                    <span class="partner-badge-required">Required</span>
                </label>
                <input type="text" name="collaboration_type" class="form-control" value="{{ old('collaboration_type', $company->collaboration_type) }}" placeholder="Vendor, Partner, Client, Service Provider" required>
                @error('collaboration_type')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
            </div>
            <div>
                <label class="partner-form-label">
                    <span>Status <span class="text-danger">*</span></span>
                    <span class="partner-badge-required">Required</span>
                </label>
                <select name="status" class="form-select" required>
                    <option value="active" @selected(old('status', $company->status ?: 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $company->status) === 'inactive')>Inactive</option>
                </select>
                @error('status')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
            </div>
            <div>
                <label class="partner-form-label">
                    <span>Contact Person <span class="text-danger">*</span></span>
                    <span class="partner-badge-required">Required</span>
                </label>
                <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $company->contact_person) }}" required>
                @error('contact_person')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
            </div>
            <div>
                <label class="partner-form-label">
                    <span>Contact Email <span class="text-danger">*</span></span>
                    <span class="partner-badge-required">Required</span>
                </label>
                <input type="email" name="contact_email" id="contact_email" class="form-control" value="{{ old('contact_email', $company->contact_email) }}" placeholder="name@company.com" required pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$" title="Please enter a valid email address with an '@' and a domain (e.g. name@company.com)">
                @error('contact_email')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
            </div>
            <div>
                <label class="partner-form-label">
                    <span>Contact Phone <span class="text-danger">*</span></span>
                    <span class="partner-badge-required">Required</span>
                </label>
                @php
                    $phoneCountries = \App\Support\CountryPhone::formMap();
                    $phoneCode = '+91';
                    $phoneNumber = trim((string) $company->contact_phone);
                    if (str_starts_with($phoneNumber, '+')) {
                        $compactPhone = preg_replace('/[\s().-]+/', '', $phoneNumber);
                        $dialCodes = array_unique(array_column($phoneCountries, 'dial_code'));
                        usort($dialCodes, fn ($a, $b) => strlen($b) <=> strlen($a));
                        foreach ($dialCodes as $code) {
                            if (str_starts_with($compactPhone, $code)) {
                                $phoneCode = $code;
                                $phoneNumber = substr($compactPhone, strlen($code));
                                break;
                            }
                        }
                    }
                    $phoneCode = old('contact_phone_country_code', $phoneCode);
                    $phoneNumber = old('contact_phone', $phoneNumber);
                @endphp
                <div class="d-flex gap-2">
                    <select name="contact_phone_country_code" id="contact_phone_country_code" class="form-select" aria-label="Contact phone country code" style="flex: 0 1 190px; min-width: 100px;" required>
                        @foreach($phoneCountries as $countryName => $meta)
                            <option value="{{ $meta['dial_code'] }}" data-min-digits="{{ $meta['min_digits'] }}" data-max-digits="{{ $meta['max_digits'] }}" @selected($phoneCode === $meta['dial_code'])>{{ $countryName }} ({{ $meta['dial_code'] }})</option>
                        @endforeach
                    </select>
                    <input type="tel" id="contact_phone" name="contact_phone" class="form-control" style="min-width: 0; flex: 1;" inputmode="numeric" value="{{ $phoneNumber }}" aria-label="Contact phone number" required>
                </div>
                <small id="contact_phone_help" class="text-muted d-block mt-1"></small>
                @error('contact_phone_country_code')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                @error('contact_phone')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
            </div>
            <div>
                <label class="partner-form-label">
                    <span>Collaboration Started <span class="text-danger">*</span></span>
                    <span class="partner-badge-required">Required</span>
                </label>
                <input type="date" name="started_on" class="form-control" value="{{ old('started_on', optional($company->started_on)->format('Y-m-d')) }}" required>
                @error('started_on')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
            </div>
            <div class="full">
                <label class="partner-form-label">
                    <span>Website <span class="text-danger">*</span></span>
                    <span class="partner-badge-required">Required</span>
                </label>
                <input type="url" name="website" class="form-control" value="{{ old('website', $company->website) }}" placeholder="https://example.com" required>
                @error('website')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
            </div>
            <div class="full">
                <label class="partner-form-label">
                    <span>What This Company Does <span class="text-danger">*</span></span>
                    <span class="partner-badge-required">Required</span>
                </label>
                <textarea name="description" rows="4" class="form-control" required>{{ old('description', $company->description) }}</textarea>
                @error('description')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
            </div>
            <div class="full">
                <label class="partner-form-label">
                    <span>Services / Collaboration Details <span class="text-danger">*</span></span>
                    <span class="partner-badge-required">Required</span>
                </label>
                <textarea name="services" rows="4" class="form-control" required>{{ old('services', $company->services) }}</textarea>
                @error('services')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
            </div>
            @php $socials = old('social_links', $company->social_links ?? []); @endphp
            @foreach(['linkedin' => 'LinkedIn', 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X / Twitter', 'youtube' => 'YouTube'] as $key => $label)
                <div>
                    <label class="partner-form-label">
                        <span>{{ $label }}</span>
                        <span class="partner-badge-optional">Optional</span>
                    </label>
                    <input type="url" name="social_links[{{ $key }}]" class="form-control" value="{{ $socials[$key] ?? '' }}" placeholder="https://">
                    @error('social_links.' . $key)<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                </div>
            @endforeach
            <div class="full">
                <label class="partner-form-label">
                    <span>Internal Notes</span>
                    <span class="partner-badge-optional">Optional</span>
                </label>
                <textarea name="notes" rows="3" class="form-control">{{ old('notes', $company->notes) }}</textarea>
                @error('notes')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="partner-form-actions">
            <button class="btn btn-primary"><i class="fas fa-save"></i> Save Company</button>
            <a href="{{ route('collaborating-companies.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </form>
</div>
@include('admin.collaborating-companies.styles')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const phoneInput = document.getElementById('contact_phone');
    const phoneCountry = document.getElementById('contact_phone_country_code');
    phoneInput.addEventListener('beforeinput', function (event) {
        if (event.inputType === 'insertText' && event.data && /[^0-9]/.test(event.data)) {
            event.preventDefault();
        }
    });
    phoneInput.addEventListener('input', function () {
        const value = phoneInput.value;
        const caret = phoneInput.selectionStart;
        const digits = value.replace(/[^0-9]/g, '');
        if (value !== digits) {
            phoneInput.value = digits;
            if (caret !== null) {
                const position = value.slice(0, caret).replace(/[^0-9]/g, '').length;
                phoneInput.setSelectionRange(position, position);
            }
        }
    });
    function updatePhoneRules() {
        const option = phoneCountry.options[phoneCountry.selectedIndex];
        const minDigits = Number(option.dataset.minDigits);
        const maxDigits = Number(option.dataset.maxDigits);
        phoneInput.minLength = minDigits;
        phoneInput.maxLength = maxDigits;
        phoneInput.pattern = '[1-9][0-9]{' + (minDigits - 1) + ',' + (maxDigits - 1) + '}';
        document.getElementById('contact_phone_help').textContent = minDigits === maxDigits
            ? 'Enter a ' + minDigits + '-digit phone number without the country code.'
            : 'Enter ' + minDigits + ' to ' + maxDigits + ' digits without the country code.';
    }
    phoneCountry.addEventListener('change', updatePhoneRules);
    updatePhoneRules();
    const emailInput = document.getElementById('contact_email');
    if (!emailInput) return;

    const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    function validateEmail() {
        const val = emailInput.value.trim();
        if (!val) {
            emailInput.setCustomValidity('Please fill in the Contact Email.');
        } else if (!val.includes('@')) {
            emailInput.setCustomValidity("Email address must include an '@' symbol.");
        } else if (!emailPattern.test(val)) {
            emailInput.setCustomValidity("Please enter a valid email address with an '@' and a domain (e.g. name@company.com).");
        } else {
            emailInput.setCustomValidity('');
        }
    }

    emailInput.addEventListener('input', validateEmail);
    emailInput.addEventListener('change', validateEmail);
    emailInput.addEventListener('invalid', validateEmail);
});
</script>
@endpush
@endsection
