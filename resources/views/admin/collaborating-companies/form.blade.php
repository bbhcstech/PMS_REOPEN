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

    <form method="POST" action="{{ $mode === 'edit' ? route('collaborating-companies.update', $company) : route('collaborating-companies.store') }}" class="partner-form-card">
        @csrf
        @if($mode === 'edit')
            @method('PUT')
        @endif

        <div class="partner-form-grid">
            <div>
                <label>Company Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $company->name) }}" required>
                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div>
                <label>Industry</label>
                <input type="text" name="industry" class="form-control" value="{{ old('industry', $company->industry) }}" placeholder="IT, Marketing, Finance">
            </div>
            <div>
                <label>Collaboration Type</label>
                <input type="text" name="collaboration_type" class="form-control" value="{{ old('collaboration_type', $company->collaboration_type) }}" placeholder="Vendor, Partner, Client, Service Provider">
            </div>
            <div>
                <label>Status</label>
                <select name="status" class="form-select" required>
                    <option value="active" @selected(old('status', $company->status ?: 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $company->status) === 'inactive')>Inactive</option>
                </select>
            </div>
            <div>
                <label>Contact Person</label>
                <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $company->contact_person) }}">
            </div>
            <div>
                <label>Contact Email</label>
                <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $company->contact_email) }}">
            </div>
            <div>
                <label>Contact Phone</label>
                <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $company->contact_phone) }}">
            </div>
            <div>
                <label>Collaboration Started</label>
                <input type="date" name="started_on" class="form-control" value="{{ old('started_on', optional($company->started_on)->format('Y-m-d')) }}">
            </div>
            <div class="full">
                <label>Website</label>
                <input type="url" name="website" class="form-control" value="{{ old('website', $company->website) }}" placeholder="https://example.com">
            </div>
            <div class="full">
                <label>What This Company Does</label>
                <textarea name="description" rows="4" class="form-control">{{ old('description', $company->description) }}</textarea>
            </div>
            <div class="full">
                <label>Services / Collaboration Details</label>
                <textarea name="services" rows="4" class="form-control">{{ old('services', $company->services) }}</textarea>
            </div>
            @php $socials = old('social_links', $company->social_links ?? []); @endphp
            @foreach(['linkedin' => 'LinkedIn', 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X / Twitter', 'youtube' => 'YouTube'] as $key => $label)
                <div>
                    <label>{{ $label }}</label>
                    <input type="url" name="social_links[{{ $key }}]" class="form-control" value="{{ $socials[$key] ?? '' }}" placeholder="https://">
                </div>
            @endforeach
            <div class="full">
                <label>Internal Notes</label>
                <textarea name="notes" rows="3" class="form-control">{{ old('notes', $company->notes) }}</textarea>
            </div>
        </div>

        <div class="partner-form-actions">
            <button class="btn btn-primary"><i class="fas fa-save"></i> Save Company</button>
            <a href="{{ route('collaborating-companies.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </form>
</div>
@include('admin.collaborating-companies.styles')
@endsection
