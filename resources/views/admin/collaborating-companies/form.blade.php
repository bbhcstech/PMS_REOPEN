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
                <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $company->contact_phone) }}" required>
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
