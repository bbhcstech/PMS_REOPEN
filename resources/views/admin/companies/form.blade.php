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
                    <div class="col-md-4">
                        <label class="form-label">Company Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $company->phone) }}">
                    </div>
                    <div class="col-md-4">
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
