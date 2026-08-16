@extends('admin.layout.app')

@section('title', 'Recruitment & Job Requirements')

@section('content')
<style>
    .recruitment-shell {
        font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif;
        padding-bottom: 2rem;
    }

    /* Page Header */
    .rec-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .rec-header h3 {
        font-weight: 800;
        color: #1f2937;
        margin: 0 0 0.25rem;
        font-size: 1.65rem;
    }
    .rec-header p {
        color: #6b7280;
        margin: 0;
        font-size: 0.925rem;
    }

    /* Buttons */
    .btn-purple-primary {
        background: linear-gradient(135deg, #7C3AED 0%, #6D28D9 100%) !important;
        color: #ffffff !important;
        border: none !important;
        font-weight: 700;
        box-shadow: 0 4px 14px rgba(124, 58, 237, 0.25);
        transition: all 0.2s ease;
    }
    .btn-purple-primary:hover {
        background: linear-gradient(135deg, #6D28D9 0%, #5B21B6 100%) !important;
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(124, 58, 237, 0.35);
    }
    .btn-purple-primary * {
        color: #ffffff !important;
    }

    /* Policy Card styling */
    .policy-card {
        background: linear-gradient(135deg, #ffffff 0%, #faf5ff 100%);
        border: 1px solid rgba(124, 58, 237, 0.18);
        border-radius: 16px;
        box-shadow: 0 10px 30px -5px rgba(124, 58, 237, 0.1);
        margin-bottom: 2rem;
        overflow: hidden;
        position: relative;
    }
    .policy-card-header {
        background: linear-gradient(135deg, #7C3AED 0%, #8B5CF6 100%);
        color: #ffffff;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .policy-card-header h5 {
        color: #ffffff !important;
        font-weight: 800;
        margin: 0;
        font-size: 1.15rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .policy-card-body {
        padding: 1.5rem;
    }
    .policy-param-box {
        background: #ffffff;
        border: 1px solid #ede9fe;
        border-radius: 12px;
        padding: 1rem;
        height: 100%;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .policy-param-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(124, 58, 237, 0.08);
    }
    .policy-param-label {
        font-size: 0.775rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6d28d9;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }
    .policy-param-value {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1f2937;
    }
    .pipeline-step-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        background: #f3e8ff;
        color: #6d28d9;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 700;
        margin-right: 0.35rem;
        margin-bottom: 0.35rem;
    }

    /* Metric Cards */
    .metric-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        margin-bottom: 1.5rem;
    }
    .metric-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }
    .metric-icon.purple { background: #ede9fe; color: #7C3AED; }
    .metric-icon.blue { background: #dbeafe; color: #2563eb; }
    .metric-icon.green { background: #dcfce7; color: #16a34a; }
    .metric-icon.amber { background: #fef3c7; color: #d97706; }
    .metric-info h4 {
        margin: 0;
        font-weight: 800;
        color: #1f2937;
        font-size: 1.35rem;
    }
    .metric-info span {
        font-size: 0.825rem;
        color: #6b7280;
        font-weight: 600;
    }

    /* Table styling */
    .rec-table-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 16px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .rec-table th {
        background: #f9fafb;
        font-weight: 700;
        color: #374151;
        font-size: 0.825rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 1rem 1.25rem;
    }
    .rec-table td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
    }

    .badge-employment {
        background-color: #f3f4f6;
        color: #4b5563;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y recruitment-shell">

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
        <i class="bx bx-check-circle me-2 fs-4"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
        <i class="bx bx-error-circle me-2 fs-4"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Page Header --}}
    <div class="rec-header">
        <div>
            <h3>Recruitment & Talent Acquisition</h3>
            <p>View corporate job requirements, read the auto-generated policy card, and download requirement specifications for sharing.</p>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()?->role === 'admin')
            <a href="{{ route('admin.settings.recruitment') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <i class="bx bx-cog"></i> Recruitment Settings
            </a>
            <button class="btn btn-purple-primary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#createRequirementModal">
                <i class="bx bx-plus-circle"></i> Add New Requirement
            </button>
            @else
            <span class="badge bg-label-info p-2 d-flex align-items-center gap-1">
                <i class="bx bx-lock-alt"></i> Read-Only Mode (Download Available)
            </span>
            @endif
        </div>
    </div>

    {{-- AUTOMATICALLY GENERATED RECRUITMENT POLICY CARD --}}
    <div class="policy-card">
        <div class="policy-card-header">
            <div>
                <h5><i class="bx bx-file-find"></i> Automatically Generated Recruitment Policy Card</h5>
                <small class="text-white-50">System Code: {{ $policyCard['code'] }} | Updated: {{ $policyCard['generated_at'] }}</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-purple fw-bold">{{ $policyCard['status'] }}</span>
                <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#policyCardContent">
                    <i class="bx bx-chevron-down"></i> Toggle Details
                </button>
            </div>
        </div>
        <div class="collapse show policy-card-body" id="policyCardContent">
            <div class="row g-3 mb-3">
                <div class="col-md-3 col-sm-6">
                    <div class="policy-param-box">
                        <div class="policy-param-label">Probation Period</div>
                        <div class="policy-param-value">{{ $policyCard['probation_period'] }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="policy-param-box">
                        <div class="policy-param-label">Target Hiring SLA</div>
                        <div class="policy-param-value">{{ $policyCard['hiring_sla'] }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="policy-param-box">
                        <div class="policy-param-label">Allowed Resume Formats</div>
                        <div class="policy-param-value">{{ $policyCard['allowed_file_types'] }} (Max {{ $policyCard['max_resume_size'] }})</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="policy-param-box">
                        <div class="policy-param-label">Candidate Auto-Reply</div>
                        <div class="policy-param-value">
                            @if($policyCard['auto_reply_enabled'])
                                <span class="text-success"><i class="bx bx-check-circle"></i> Enabled</span>
                            @else
                                <span class="text-muted"><i class="bx bx-x-circle"></i> Disabled</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="policy-param-label mb-2">Standard Hiring Pipeline Stages</div>
                <div>
                    @foreach($policyCard['pipeline_stages'] as $index => $stage)
                        <span class="pipeline-step-pill">
                            <span class="badge bg-purple rounded-circle text-white me-1">{{ $index + 1 }}</span>
                            {{ trim($stage) }}
                        </span>
                        @if(!$loop->last)
                            <i class="bx bx-right-arrow-alt text-muted me-1"></i>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="row g-3 pt-2 border-top">
                <div class="col-md-6">
                    <small class="text-muted d-block fw-bold mb-1"><i class="bx bx-shield-quarter"></i> Equal Opportunity Policy:</small>
                    <small class="text-secondary d-block">{{ $policyCard['equal_opportunity'] }}</small>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block fw-bold mb-1"><i class="bx bx-gift"></i> Internal Referral & Background Checks:</small>
                    <small class="text-secondary d-block">{{ $policyCard['referral_policy'] }} {{ $policyCard['background_check'] }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- METRIC COUNTER CARDS --}}
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="metric-card">
                <div class="metric-icon purple">
                    <i class="bx bx-briefcase"></i>
                </div>
                <div class="metric-info">
                    <h4>{{ $totalOpen }}</h4>
                    <span>Active Openings</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="metric-card">
                <div class="metric-icon blue">
                    <i class="bx bx-loader-circle"></i>
                </div>
                <div class="metric-info">
                    <h4>{{ $totalInProgress }}</h4>
                    <span>In Progress</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="metric-card">
                <div class="metric-icon amber">
                    <i class="bx bx-group"></i>
                </div>
                <div class="metric-info">
                    <h4>{{ $totalPositionsOpen }}</h4>
                    <span>Positions Needed</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="metric-card">
                <div class="metric-icon green">
                    <i class="bx bx-check-shield"></i>
                </div>
                <div class="metric-info">
                    <h4>{{ $totalClosed }}</h4>
                    <span>Fulfilled / Closed</span>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER & SEARCH BAR --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('recruitment.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Search Requirement</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by title, department, location..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Status Filter</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Department</label>
                    <select name="department_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->dpt_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt"></i> Filter</button>
                    <a href="{{ route('recruitment.index') }}" class="btn btn-outline-secondary" title="Reset Filters"><i class="bx bx-refresh"></i></a>
                </div>
            </form>
        </div>
    </div>

    {{-- REQUIREMENTS TABLE --}}
    <div class="rec-table-card">
        <div class="table-responsive">
            <table class="table rec-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Department</th>
                        <th>Positions</th>
                        <th>Experience</th>
                        <th>Salary & Location</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requirements as $req)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark fs-6">{{ $req->title }}</div>
                            <span class="badge-employment">{{ $req->employment_type }}</span>
                        </td>
                        <td>
                            <span class="fw-semibold text-secondary">{{ $req->department_name ?? 'General' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-label-primary px-3 py-2 fs-6 rounded-pill fw-bold">{{ $req->positions }}</span>
                        </td>
                        <td>
                            <span class="text-muted small">{{ $req->experience_required ?? 'Not Specified' }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark small">{{ $req->salary_range ?? 'Negotiable' }}</div>
                            <small class="text-muted"><i class="bx bx-map-pin"></i> {{ $req->location ?? 'Headquarters' }}</small>
                        </td>
                        <td>
                            <span class="badge {{ $req->status_badge }} px-3 py-2">
                                {{ $req->status_label }}
                            </span>
                        </td>
                        <td>
                            <div class="small fw-semibold text-dark">{{ $req->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">By {{ $req->creator?->name ?? 'System' }}</small>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex align-items-center gap-1">
                                {{-- DOWNLOAD BUTTON ACCESSIBLE TO ALL ROLES --}}
                                <a href="{{ route('recruitment.download', $req->id) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" title="Download Requirement PDF to Share Personally">
                                    <i class="bx bx-download"></i> Download
                                </a>

                                <button class="btn btn-sm btn-icon btn-light" type="button" data-bs-toggle="modal" data-bs-target="#viewReqModal-{{ $req->id }}" title="View Full Details">
                                    <i class="bx bx-show text-info"></i>
                                </button>

                                @if(auth()->user()?->role === 'admin')
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-icon btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li class="dropdown-header">Admin Management</li>
                                        <li>
                                            <form method="POST" action="{{ route('recruitment.status', $req->id) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="open">
                                                <button type="submit" class="dropdown-item {{ $req->status == 'open' ? 'active' : '' }}">
                                                    <i class="bx bx-check-circle me-2 text-success"></i> Mark Open
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('recruitment.status', $req->id) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="in_progress">
                                                <button type="submit" class="dropdown-item {{ $req->status == 'in_progress' ? 'active' : '' }}">
                                                    <i class="bx bx-loader me-2 text-warning"></i> Mark In Progress
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('recruitment.status', $req->id) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="closed">
                                                <button type="submit" class="dropdown-item {{ $req->status == 'closed' ? 'active' : '' }}">
                                                    <i class="bx bx-x-circle me-2 text-secondary"></i> Mark Closed
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('recruitment.destroy', $req->id) }}" onsubmit="return confirm('Are you sure you want to delete this requirement?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bx bx-trash me-2"></i> Delete Requirement
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- VIEW REQUIREMENT DETAIL MODAL --}}
                    <div class="modal fade" id="viewReqModal-{{ $req->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-purple text-white">
                                    <h5 class="modal-title text-white fw-bold"><i class="bx bx-briefcase me-1"></i> {{ $req->title }}</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <span class="badge {{ $req->status_badge }} px-3 py-2 me-2">{{ $req->status_label }}</span>
                                            <span class="badge-employment">{{ $req->employment_type }}</span>
                                        </div>
                                        <small class="text-muted">Posted on {{ $req->created_at->format('F d, Y') }}</small>
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-sm-6">
                                            <div class="p-3 bg-light rounded">
                                                <small class="text-muted d-block fw-bold">Department</small>
                                                <span class="fw-semibold text-dark">{{ $req->department_name ?? 'General' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="p-3 bg-light rounded">
                                                <small class="text-muted d-block fw-bold">Vacancies / Positions</small>
                                                <span class="fw-bold text-purple">{{ $req->positions }} Position(s)</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="p-3 bg-light rounded">
                                                <small class="text-muted d-block fw-bold">Experience Required</small>
                                                <span class="fw-semibold text-dark">{{ $req->experience_required ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="p-3 bg-light rounded">
                                                <small class="text-muted d-block fw-bold">Salary & Location</small>
                                                <span class="fw-semibold text-dark">{{ $req->salary_range ?? 'Negotiable' }} | {{ $req->location ?? 'Headquarters' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    @if($req->description)
                                    <div class="mb-3">
                                        <h6 class="fw-bold text-dark"><i class="bx bx-text me-1"></i> Job Description</h6>
                                        <div class="p-3 bg-white border rounded text-secondary" style="white-space: pre-line;">{{ $req->description }}</div>
                                    </div>
                                    @endif

                                    @if($req->requirements_summary)
                                    <div class="mb-3">
                                        <h6 class="fw-bold text-dark"><i class="bx bx-list-check me-1"></i> Candidate Requirements & Qualifications</h6>
                                        <div class="p-3 bg-white border rounded text-secondary" style="white-space: pre-line;">{{ $req->requirements_summary }}</div>
                                    </div>
                                    @endif

                                    <div class="alert alert-info d-flex align-items-center mb-0 mt-3">
                                        <i class="bx bx-bell me-2 fs-4"></i>
                                        <small>Notification regarding this requirement has been broadcasted to all employees, managers, and HR personnel.</small>
                                    </div>
                                </div>
                                <div class="modal-footer d-flex justify-content-between">
                                    <a href="{{ route('recruitment.download', $req->id) }}" class="btn btn-purple-primary">
                                        <i class="bx bx-download me-1"></i> Download Requirement PDF
                                    </a>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="py-4">
                                <i class="bx bx-briefcase-alt-2 fs-1 text-muted mb-2"></i>
                                <h5 class="text-secondary fw-bold">No Job Requirements Found</h5>
                                <p class="text-muted mb-3">There are no job requirements posted at this moment.</p>
                                @if(auth()->user()?->role === 'admin')
                                <button class="btn btn-purple-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createRequirementModal">
                                    <i class="bx bx-plus-circle me-1"></i> Add First Requirement
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(auth()->user()?->role === 'admin')
{{-- CREATE NEW REQUIREMENT MODAL (ADMIN ONLY) --}}
<div class="modal fade" id="createRequirementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('recruitment.store') }}">
                @csrf
                <div class="modal-header bg-purple text-white">
                    <h5 class="modal-title text-white fw-bold"><i class="bx bx-plus-circle me-1"></i> Create New Recruitment Requirement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">

                    <div class="alert alert-purple d-flex align-items-center mb-4" style="background: #f3e8ff; border: 1px solid #d8b4fe; color: #5b21b6;">
                        <i class="bx bx-bell-plus me-2 fs-3"></i>
                        <div>
                            <strong>Automatic All-Role Notification:</strong>
                            Submitting this new requirement will automatically send an instant in-app notification to <strong>all Employees, Managers, and HR staff</strong>.
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Job Title <sup class="text-danger">*</sup></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Senior Laravel Developer" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Positions Needed <sup class="text-danger">*</sup></label>
                            <input type="number" name="positions" class="form-control" value="1" min="1" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">-- Select Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->dpt_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Employment Type <sup class="text-danger">*</sup></label>
                            <select name="employment_type" class="form-select" required>
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Contract">Contract</option>
                                <option value="Remote">Remote</option>
                                <option value="Internship">Internship</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Experience Required</label>
                            <input type="text" name="experience_required" class="form-control" placeholder="e.g. 2 - 4 Years">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Salary Range</label>
                            <input type="text" name="salary_range" class="form-control" placeholder="e.g. $60,000 - $80,000 / year">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Location</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Headquarters / Remote">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Job Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Provide brief overview of responsibilities and scope..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Candidate Qualifications & Requirements</label>
                            <textarea name="requirements_summary" class="form-control" rows="3" placeholder="Key skills, degrees, technologies, or certifications needed..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-purple-primary px-4"><i class="bx bx-paper-plane me-1"></i> Create & Broadcast Requirement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
