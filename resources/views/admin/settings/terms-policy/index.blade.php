@extends('admin.layout.app')

@section('title', 'Terms & Policy')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Terms & Policy</h4>
            <p class="text-muted mb-0">Update the organization Terms & Conditions shown from the login page.</p>
        </div>
        <a href="{{ route('company.terms') }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
            <i class="bx bx-link-external me-1"></i> View Public Page
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Terms & Conditions Content</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.terms-policy.update') }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label" for="legal_terms_title">Title <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="legal_terms_title"
                            id="legal_terms_title"
                            class="form-control @error('legal_terms_title') is-invalid @enderror"
                            value="{{ old('legal_terms_title', $title) }}"
                            required>
                        @error('legal_terms_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="legal_terms_effective_date">Effective Date</label>
                        <input
                            type="date"
                            name="legal_terms_effective_date"
                            id="legal_terms_effective_date"
                            class="form-control @error('legal_terms_effective_date') is-invalid @enderror"
                            value="{{ old('legal_terms_effective_date', $effectiveDate) }}">
                        @error('legal_terms_effective_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="legal_terms_content">Policy Text <span class="text-danger">*</span></label>
                        <textarea
                            name="legal_terms_content"
                            id="legal_terms_content"
                            rows="18"
                            class="form-control @error('legal_terms_content') is-invalid @enderror"
                            required>{{ old('legal_terms_content', $content) }}</textarea>
                        <div class="form-text">Use line breaks to separate sections. The public page updates immediately after saving.</div>
                        @error('legal_terms_content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> Save Terms
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
