@extends('admin.layout.app')

@section('content')

<style>
.form-card-modern {
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    transition: all 0.2s ease;
}
html[data-pms-theme="dark"] .form-card-modern {
    background: #102119;
    border-color: rgba(225, 255, 240, 0.12);
}
.form-card-header-modern {
    background: #ffffff;
    border-bottom: 1px solid #f1f5f9;
    padding: 1.1rem 1.5rem;
    border-top-left-radius: 16px;
    border-top-right-radius: 16px;
}
html[data-pms-theme="dark"] .form-card-header-modern {
    background: #102119;
    border-bottom-color: rgba(225, 255, 240, 0.08);
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
    background-color: #183026;
    border-color: rgba(225, 255, 240, 0.15);
    color: #ffffff;
}
.form-control-modern:focus, .form-select-modern:focus {
    background-color: #ffffff;
    border-color: #0f744c;
    box-shadow: 0 0 0 3.5px rgba(15, 116, 76, 0.12);
    color: #0f172a;
    outline: none;
}
html[data-pms-theme="dark"] .form-control-modern:focus,
html[data-pms-theme="dark"] .form-select-modern:focus {
    background-color: #102119;
    border-color: #40d48c;
    box-shadow: 0 0 0 3.5px rgba(64, 212, 140, 0.18);
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
    background-color: #183026;
    border-color: rgba(225, 255, 240, 0.2);
    color: #e2e8f0;
}
html[data-pms-theme="dark"] .btn-back-pill:hover {
    background-color: #204033;
    color: #ffffff;
}
.btn-submit-emerald {
    background: linear-gradient(135deg, #0f744c 0%, #10b981 100%);
    color: #ffffff;
    border: none;
    border-radius: 10px;
    padding: 0.65rem 1.75rem;
    font-size: 0.9rem;
    font-weight: 600;
    box-shadow: 0 4px 14px rgba(15, 116, 76, 0.25);
    transition: all 0.2s ease;
}
.btn-submit-emerald:hover {
    background: linear-gradient(135deg, #094c32 0%, #059669 100%);
    color: #ffffff;
    box-shadow: 0 6px 18px rgba(15, 116, 76, 0.35);
    transform: translateY(-1px);
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
    background: #183026;
    border-color: rgba(225, 255, 240, 0.15);
    color: #cbd5e1;
}
html[data-pms-theme="dark"] .btn-cancel-modern:hover {
    background: #204033;
    color: #ffffff;
}
</style>

<div class="container-fluid py-3">

    {{-- BREADCRUMB & HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 d-flex align-items-center gap-2" style="color: #0f172a;">
                <span class="fs-4">✏️</span> Edit Lead Contact
            </h4>
            <p class="text-muted small mb-0">Update contact information, status, priority, and score for {{ $lead->contact_name }}</p>
        </div>
        <a href="{{ route('leads.contacts.index') }}" class="btn-back-pill">
            <i class="fas fa-arrow-left me-1"></i> Back to Lead Contacts
        </a>
    </div>

    {{-- FORM CARD --}}
    <form action="{{ route('leads.contacts.update', $lead->id) }}" method="POST" id="leadEditForm">
        @csrf
        @method('PUT')

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
                            <option value="Mr." {{ old('salutation', $lead->salutation) == 'Mr.' ? 'selected' : '' }}>Mr.</option>
                            <option value="Mrs." {{ old('salutation', $lead->salutation) == 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                            <option value="Ms." {{ old('salutation', $lead->salutation) == 'Ms.' ? 'selected' : '' }}>Ms.</option>
                            <option value="Dr." {{ old('salutation', $lead->salutation) == 'Dr.' ? 'selected' : '' }}>Dr.</option>
                            <option value="Prof." {{ old('salutation', $lead->salutation) == 'Prof.' ? 'selected' : '' }}>Prof.</option>
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label-modern">Contact Name <span class="text-danger">*</span></label>
                        <input type="text" name="contact_name" class="form-control form-control-modern @error('contact_name') is-invalid @enderror" value="{{ old('contact_name', $lead->contact_name) }}" required placeholder="e.g. John Doe">
                        @error('contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-5">
                        <label class="form-label-modern">Job Title / Designation</label>
                        <input type="text" name="job_title" class="form-control form-control-modern" value="{{ old('job_title', $lead->job_title) }}" placeholder="e.g. Senior Manager">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control form-control-modern @error('email') is-invalid @enderror" value="{{ old('email', $lead->email) }}" required placeholder="e.g. john@example.com">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Primary Phone</label>
                        <input type="text" name="phone" class="form-control form-control-modern" value="{{ old('phone', $lead->phone) }}" placeholder="e.g. +1 234 567 890">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Mobile / Cell</label>
                        <input type="text" name="mobile" class="form-control form-control-modern" value="{{ old('mobile', $lead->mobile) }}" placeholder="e.g. +1 987 654 321">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Alternate Phone</label>
                        <input type="text" name="alternate_phone" class="form-control form-control-modern" value="{{ old('alternate_phone', $lead->alternate_phone) }}" placeholder="e.g. Office Ext 102">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">WhatsApp Number</label>
                        <input type="text" name="whatsapp" class="form-control form-control-modern" value="{{ old('whatsapp', $lead->whatsapp) }}" placeholder="e.g. +1 234 567 890">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Company Name</label>
                        <input type="text" name="company_name" class="form-control form-control-modern" value="{{ old('company_name', $lead->company_name) }}" placeholder="e.g. Acme Corp">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label-modern">Company Website</label>
                        <input type="url" name="website" class="form-control form-control-modern" value="{{ old('website', $lead->website) }}" placeholder="https://www.example.com">
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
                        <label class="form-label-modern">Country</label>
                        <input type="text" name="country" class="form-control form-control-modern" value="{{ old('country', $lead->country) }}" placeholder="e.g. United States">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-modern">State / Province</label>
                        <input type="text" name="state" class="form-control form-control-modern" value="{{ old('state', $lead->state) }}" placeholder="e.g. California">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-modern">City</label>
                        <input type="text" name="city" class="form-control form-control-modern" value="{{ old('city', $lead->city) }}" placeholder="e.g. San Francisco">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-modern">Postal / Zip Code</label>
                        <input type="text" name="postal_code" class="form-control form-control-modern" value="{{ old('postal_code', $lead->postal_code) }}" placeholder="e.g. 94105">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label-modern">Street Address</label>
                        <textarea name="address" class="form-control form-control-modern" rows="2" placeholder="Full street address...">{{ old('address', $lead->address) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 3: LEAD INFORMATION --}}
        <div class="card form-card-modern mb-4">
            <div class="card-header form-card-header-modern">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2"><span class="fs-5">🎯</span> 3. Lead Information & Status</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label-modern">Lead Source <span class="text-danger">*</span></label>
                        <select name="lead_source" class="form-select form-select-modern" required>
                            <option value="email" {{ old('lead_source', $lead->lead_source) == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="google" {{ old('lead_source', $lead->lead_source) == 'google' ? 'selected' : '' }}>Google Search</option>
                            <option value="facebook" {{ old('lead_source', $lead->lead_source) == 'facebook' ? 'selected' : '' }}>Facebook</option>
                            <option value="linkedin" {{ old('lead_source', $lead->lead_source) == 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                            <option value="referral" {{ old('lead_source', $lead->lead_source) == 'referral' ? 'selected' : '' }}>Referral</option>
                            <option value="website" {{ old('lead_source', $lead->lead_source) == 'website' ? 'selected' : '' }}>Website Form</option>
                            <option value="phone" {{ old('lead_source', $lead->lead_source) == 'phone' ? 'selected' : '' }}>Phone Call</option>
                            <option value="walkin" {{ old('lead_source', $lead->lead_source) == 'walkin' ? 'selected' : '' }}>Walk-in</option>
                            <option value="other" {{ old('lead_source', $lead->lead_source) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Lead Status</label>
                        <select name="status" class="form-select form-select-modern">
                            <option value="new" {{ old('status', $lead->status) == 'new' ? 'selected' : '' }}>New</option>
                            <option value="contacted" {{ old('status', $lead->status) == 'contacted' ? 'selected' : '' }}>Contacted</option>
                            <option value="qualified" {{ old('status', $lead->status) == 'qualified' ? 'selected' : '' }}>Qualified</option>
                            <option value="unqualified" {{ old('status', $lead->status) == 'unqualified' ? 'selected' : '' }}>Unqualified</option>
                            <option value="nurturing" {{ old('status', $lead->status) == 'nurturing' ? 'selected' : '' }}>Nurturing</option>
                            <option value="converted" {{ old('status', $lead->status) == 'converted' ? 'selected' : '' }}>Converted</option>
                            <option value="lost" {{ old('status', $lead->status) == 'lost' ? 'selected' : '' }}>Lost</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Lead Priority</label>
                        <select name="priority" class="form-select form-select-modern">
                            <option value="medium" {{ old('priority', $lead->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ old('priority', $lead->priority) == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="high" {{ old('priority', $lead->priority) == 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ old('priority', $lead->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Lead Owner <span class="text-danger">*</span></label>
                        <select name="lead_owner_id" class="form-select form-select-modern" required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('lead_owner_id', $lead->lead_owner_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Lead Score (0-100)</label>
                        <input type="number" min="0" max="100" name="lead_score" class="form-control form-control-modern" value="{{ old('lead_score', $lead->lead_score) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Expected Value</label>
                        <input type="number" step="0.01" name="expected_value" class="form-control form-control-modern" value="{{ old('expected_value', $lead->expected_value) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-modern">Industry</label>
                        <input type="text" name="industry" class="form-control form-control-modern" value="{{ old('industry', $lead->industry) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-modern">Tags</label>
                        <input type="text" name="tags" class="form-control form-control-modern" value="{{ old('tags', $lead->tags) }}">
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
                <textarea name="description" class="form-control form-control-modern" rows="4" placeholder="Enter notes...">{{ old('description', $lead->description) }}</textarea>
            </div>
        </div>

        {{-- SUBMIT BUTTONS --}}
        <div class="d-flex gap-2 justify-content-end mb-5">
            <a href="{{ route('leads.contacts.index') }}" class="btn-cancel-modern">Cancel</a>
            <button type="submit" class="btn-submit-emerald"><i class="fas fa-save me-1"></i> Update Lead Contact</button>
        </div>
    </form>
</div>

@endsection
