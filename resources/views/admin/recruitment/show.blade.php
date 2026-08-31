@extends('admin.layout.app')

@section('title', $requirement->title . ' - Recruitment Details')

@section('content')
<style>
    .rec-detail-shell {
        --rec-green-900: #071a12;
        --rec-green-800: #0a2e1f;
        --rec-green-700: #0f744c;
        --rec-green-600: #059669;
        --rec-green-500: #10b981;
        --rec-border: rgba(15, 116, 76, 0.12);
        font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif;
        padding-bottom: 2.5rem;
    }
    .breadcrumb-custom {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 1.25rem;
    }
    .breadcrumb-custom a {
        color: var(--rec-green-700);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: color 0.2s ease;
    }
    .breadcrumb-custom a:hover {
        color: var(--rec-green-600);
    }
    .breadcrumb-custom .separator {
        font-size: 1.1rem;
        color: #94a3b8;
    }
    .rec-header-card {
        background: #ffffff;
        border-radius: 24px;
        padding: 1.6rem 2rem;
        margin-bottom: 1.75rem;
        border: 1px solid var(--rec-border);
        box-shadow: 0 14px 36px -10px rgba(15, 116, 76, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.25rem;
        position: relative;
        overflow: hidden;
    }
    .rec-header-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #10b981 0%, #059669 50%, #047857 100%);
    }
    .btn-rec-primary {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: #ffffff !important;
        border: none !important;
        font-weight: 700;
        font-size: 0.9rem;
        padding: 0.65rem 1.35rem;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        white-space: nowrap !important;
        box-shadow: 0 8px 18px -4px rgba(16, 185, 129, 0.35);
        transition: all 0.25s ease;
        text-decoration: none;
    }
    .btn-rec-primary:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
        color: #ffffff !important;
        transform: translateY(-2px);
    }
    .btn-rec-primary i {
        color: #ffffff !important;
    }
    .btn-rec-outline {
        background: #ffffff;
        color: var(--rec-green-700) !important;
        border: 1px solid rgba(15, 116, 76, 0.25) !important;
        font-weight: 700;
        font-size: 0.9rem;
        padding: 0.65rem 1.25rem;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.25s ease;
        text-decoration: none;
    }
    .btn-rec-outline:hover {
        background: #ecfdf5;
        color: var(--rec-green-800) !important;
        transform: translateY(-2px);
    }
    .btn-rec-outline i {
        color: var(--rec-green-700) !important;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y rec-detail-shell">
    {{-- Breadcrumb Navigation --}}
    <div class="breadcrumb-custom">
        <a href="{{ route('dashboard') }}"><i class="bx bx-home-alt me-1"></i> Dashboard</a>
        <i class="bx bx-chevron-right separator"></i>
        <span>HR</span>
        <i class="bx bx-chevron-right separator"></i>
        <a href="{{ route('recruitment.index') }}">Recruitment</a>
        <i class="bx bx-chevron-right separator"></i>
        <span class="text-dark fw-bold">{{ $requirement->title }}</span>
    </div>

    {{-- Elevated Header Card --}}
    <div class="rec-header-card">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 54px; height: 54px; border-radius: 16px; background: linear-gradient(135deg, #10b981 0%, #047857 100%); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0; box-shadow: 0 8px 20px -4px rgba(5, 150, 105, 0.35);">
                <i class="bx bx-briefcase" style="color: #ffffff !important;"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h3 class="mb-0 fw-bold" style="color: #071a12; font-size: 1.45rem;">{{ $requirement->title }}</h3>
                    <span class="badge {{ $requirement->status_badge }} px-3 py-1 rounded-pill">{{ $requirement->status_label }}</span>
                    <span class="badge bg-label-primary px-3 py-1 rounded-pill">{{ $requirement->employment_type }}</span>
                </div>
                <p class="text-muted small mb-0">Posted by {{ $requirement->creator?->name ?: 'HR Department' }} on {{ $requirement->created_at->format('F d, Y') }}</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('recruitment.index') }}" class="btn-rec-outline">
                <i class="bx bx-arrow-back"></i> Back to Openings
            </a>
            <a href="{{ route('recruitment.download', $requirement->id) }}" class="btn-rec-primary">
                <i class="bx bx-download"></i> Download PDF
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4 rounded-4">
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 p-3 bg-light rounded-3">
                            <small class="text-muted d-block fw-bold mb-1">Department</small>
                            <strong class="fs-6 text-dark">{{ $requirement->department_name ?: 'General' }}</strong>
                        </div>
                        <div class="col-sm-6 p-3 bg-light rounded-3">
                            <small class="text-muted d-block fw-bold mb-1">Positions Needed</small>
                            <strong class="fs-6 text-success">{{ $requirement->positions }} Position(s)</strong>
                        </div>
                        <div class="col-sm-6 p-3 bg-light rounded-3">
                            <small class="text-muted d-block fw-bold mb-1">Experience Required</small>
                            <strong class="fs-6 text-dark">{{ $requirement->experience_required ?: 'Not specified' }}</strong>
                        </div>
                        <div class="col-sm-6 p-3 bg-light rounded-3">
                            <small class="text-muted d-block fw-bold mb-1">Salary and Location</small>
                            <strong class="fs-6 text-dark">{{ $requirement->salary_range ?: 'Negotiable' }} | {{ $requirement->location ?: 'Headquarters' }}</strong>
                        </div>
                    </div>

                    @if($requirement->description)
                    <div class="mb-4">
                        <h5 class="fw-bold text-dark mb-2"><i class="bx bx-text text-success me-1"></i> Job Description</h5>
                        <div class="p-3 bg-light border rounded-3 text-secondary" style="white-space: pre-line;">{{ $requirement->description }}</div>
                    </div>
                    @endif

                    @if($requirement->requirements_summary)
                    <div class="mb-3">
                        <h5 class="fw-bold text-dark mb-2"><i class="bx bx-list-check text-success me-1"></i> Candidate Requirements & Qualifications</h5>
                        <div class="p-3 bg-light border rounded-3 text-secondary" style="white-space: pre-line;">{{ $requirement->requirements_summary }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header rounded-top-4 p-3" style="background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #047857 100%); color: #ffffff;">
                    <h5 class="mb-0 fw-bold" style="color: #ffffff !important;"><i class="bx bx-shield-quarter me-1" style="color: #ffffff !important;"></i> Recruitment Policy</h5>
                    <small class="text-white-50" style="color: rgba(255,255,255,0.8) !important;">{{ $policyCard['code'] }} · {{ $policyCard['status'] }}</small>
                </div>
                <div class="card-body p-3">
                    <dl class="row small mb-0 g-2">
                        <dt class="col-6 text-muted">Probation</dt><dd class="col-6 fw-bold text-dark text-end">{{ $policyCard['probation_period'] }}</dd>
                        <dt class="col-6 text-muted">Hiring SLA</dt><dd class="col-6 fw-bold text-dark text-end">{{ $policyCard['hiring_sla'] }}</dd>
                        <dt class="col-6 text-muted">Resume formats</dt><dd class="col-6 fw-bold text-dark text-end">{{ $policyCard['allowed_file_types'] }}</dd>
                    </dl>
                </div>
            </div>
            <div class="text-muted small px-2">Posted {{ $requirement->created_at->format('M d, Y') }} by {{ $requirement->creator?->name ?: 'HR Department' }}</div>
        </div>
    </div>
</div>
@endsection