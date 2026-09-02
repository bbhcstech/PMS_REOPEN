@extends('admin.layout.app')

@section('title', 'HR Employee Appraisal Management')

@section('content')
<style>
    .appraisal-shell {
        font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif;
        padding-bottom: 2rem;
    }

    /* Page Header */
    .app-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .app-header h3 {
        font-weight: 800;
        color: #1f2937;
        margin: 0 0 0.25rem;
        font-size: 1.65rem;
    }
    .app-header p {
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
    .btn-purple-primary * { color: #ffffff !important; }

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

    /* Nav Tabs */
    .appraisal-tabs {
        border-bottom: 2px solid #ede9fe;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }
    .appraisal-shell #appraisalTabs .nav-link {
        border: none;
        color: #000000 !important;
        font-weight: 700;
        font-size: 0.925rem;
        padding: 0.75rem 1.25rem;
        border-radius: 10px 10px 0 0;
        transition: all 0.2s ease;
    }
    .appraisal-shell #appraisalTabs .nav-link:not(.active):hover {
        color: #000000 !important;
        background: #faf5ff;
    }
    .appraisal-shell #appraisalTabs .nav-link.active {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        background: var(--pms-primary, #0f744c) !important;
        border-bottom: 3px solid var(--pms-primary, #0f744c) !important;
        box-shadow: 0 12px 20px rgba(15, 116, 76, 0.2);
    }
    .appraisal-shell #appraisalTabs .nav-link.active i {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    /* Keep titles readable on every coloured table heading on this page. */
    .appraisal-shell #appraisalTabsContent .app-table-card > .text-white h5,
    .appraisal-shell #appraisalTabsContent .app-table-card > .text-white h5 i {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    /* Table styling & Horizontal Scrollbar */
    .table-responsive {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    .table-responsive::-webkit-scrollbar-thumb {
        background: #a78bfa;
        border-radius: 10px;
    }
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #7c3aed;
    }

    .app-table-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 16px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .app-table {
        min-width: 1100px !important;
        width: 100%;
        margin-bottom: 0;
    }
    .app-table th {
        background: #f9fafb;
        font-weight: 700;
        color: #374151;
        font-size: 0.825rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 1rem 1.25rem;
        white-space: nowrap !important;
        word-break: normal !important;
    }
    .app-table td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
        white-space: nowrap !important;
    }

    /* High contrast grade badges */
    .badge-grade-excellent {
        background-color: #059669 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 0.8rem !important;
        padding: 0.4rem 0.85rem !important;
        border-radius: 50px !important;
        box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25) !important;
        display: inline-block !important;
    }
    .badge-grade-good {
        background-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 0.8rem !important;
        padding: 0.4rem 0.85rem !important;
        border-radius: 50px !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25) !important;
        display: inline-block !important;
    }
    .badge-grade-satisfactory {
        background-color: #d97706 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 0.8rem !important;
        padding: 0.4rem 0.85rem !important;
        border-radius: 50px !important;
        box-shadow: 0 2px 6px rgba(217, 119, 6, 0.25) !important;
        display: inline-block !important;
    }
    .badge-grade-danger {
        background-color: #dc2626 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 0.8rem !important;
        padding: 0.4rem 0.85rem !important;
        border-radius: 50px !important;
        box-shadow: 0 2px 6px rgba(220, 38, 38, 0.25) !important;
        display: inline-block !important;
    }

    /* Progress bar */
    .score-progress {
        height: 8px;
        border-radius: 999px;
        background: #f3f4f6;
        overflow: hidden;
    }
    .score-progress-bar {
        height: 100%;
        border-radius: 999px;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y appraisal-shell">

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
        <i class="bx bx-check-circle me-2 fs-4"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Page Header --}}
    <div class="app-header">
        <div>
            <h3>Employee Performance Appraisal</h3>
            <p>Evaluates personnel performance separately across Project Work (40%), Attendance (30%), and Behaviour (30%) with a Master Summary Table.</p>
        </div>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('appraisal.autoCalculate') }}">
                @csrf
                <input type="hidden" name="period" value="{{ $selectedPeriod }}">
                <button type="submit" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                    <i class="bx bx-refresh"></i> Auto-Calculate Realtime Metrics
                </button>
            </form>
            @if(auth()->user()?->role === 'admin' || auth()->user()?->role === 'hr')
            <button class="btn btn-purple-primary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#evaluateAppraisalModal">
                <i class="bx bx-edit-alt"></i> Evaluate Employee
            </button>
            @endif
        </div>
    </div>

    {{-- METRIC SUMMARY CARDS --}}
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="metric-card">
                <div class="metric-icon purple">
                    <i class="bx bx-user-check"></i>
                </div>
                <div class="metric-info">
                    <h4>{{ $totalEvaluated }}</h4>
                    <span>Total Evaluated</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="metric-card">
                <div class="metric-icon blue">
                    <i class="bx bx-tachometer"></i>
                </div>
                <div class="metric-info">
                    <h4>{{ number_format($avgScore, 1) }} / 100</h4>
                    <span>Avg Overall Score</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="metric-card">
                <div class="metric-icon green">
                    <i class="bx bx-star"></i>
                </div>
                <div class="metric-info">
                    <h4>{{ $topPerformersCount }}</h4>
                    <span>Top Performers (Grade A/A+)</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="metric-card">
                <div class="metric-icon amber">
                    <i class="bx bx-trending-down"></i>
                </div>
                <div class="metric-info">
                    <h4>{{ $needsImpCount }}</h4>
                    <span>Needs Improvement</span>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER BAR & PERIOD SELECTOR --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('appraisal.index') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Search Personnel</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email, designation..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Appraisal Period</label>
                    <select name="period" class="form-select" onchange="this.form.submit()">
                        <option value="2026 Q3" {{ $selectedPeriod == '2026 Q3' ? 'selected' : '' }}>2026 Q3 (Current Quarter)</option>
                        <option value="2026 Q2" {{ $selectedPeriod == '2026 Q2' ? 'selected' : '' }}>2026 Q2</option>
                        <option value="2026 Q1" {{ $selectedPeriod == '2026 Q1' ? 'selected' : '' }}>2026 Q1</option>
                        <option value="Annual 2025" {{ $selectedPeriod == 'Annual 2025' ? 'selected' : '' }}>Annual 2025</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt"></i> Filter</button>
                    <a href="{{ route('appraisal.index') }}" class="btn btn-outline-secondary" title="Reset Filters"><i class="bx bx-refresh"></i></a>
                </div>
            </form>
        </div>
    </div>

    {{-- APPRAISAL TABS FOR SEPARATE TABLES & MASTER OVERALL TABLE --}}
    <ul class="nav nav-tabs appraisal-tabs" id="appraisalTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="project-tab" data-bs-toggle="tab" data-bs-target="#project-table-pane" type="button" role="tab">
                <i class="bx bx-folder-open me-1 text-primary"></i> 1. Project Work Appraisal Table
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance-table-pane" type="button" role="tab">
                <i class="bx bx-calendar-check me-1 text-success"></i> 2. Attendance Appraisal Table
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="behaviour-tab" data-bs-toggle="tab" data-bs-target="#behaviour-table-pane" type="button" role="tab">
                <i class="bx bx-smile me-1 text-warning"></i> 3. Behaviour & Conduct Table
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overall-tab" data-bs-toggle="tab" data-bs-target="#overall-table-pane" type="button" role="tab">
                <i class="bx bx-award me-1 text-purple"></i> 4. Overall Master Summary Table
            </button>
        </li>
    </ul>

    <div class="tab-content" id="appraisalTabsContent">

        {{-- TABLE 4: OVERALL MASTER SUMMARY TABLE --}}
        <div class="tab-pane fade show active" id="overall-table-pane" role="tabpanel">
            <div class="app-table-card">
                <div class="p-3 text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #6d28d9 0%, #7c3aed 100%);">
                    <h5 class="text-white fw-bold mb-0"><i class="bx bx-trophy me-1"></i> Combined Master Performance Appraisal Summary Table</h5>
                    <span class="badge bg-white text-dark fw-bold px-3 py-1.5" style="color: #6d28d9 !important;">Weighted Formula: 40% Project + 30% Attendance + 30% Behaviour</span>
                </div>
                <div class="table-responsive">
                    <table class="table app-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 220px;">Employee</th>
                                <th style="min-width: 140px;">Project (40%)</th>
                                <th style="min-width: 150px;">Attendance (30%)</th>
                                <th style="min-width: 150px;">Behaviour (30%)</th>
                                <th style="min-width: 190px;">Overall Score</th>
                                <th style="min-width: 120px;">Grade</th>
                                <th style="min-width: 260px;">Recommendation</th>
                                <th class="text-end" style="min-width: 130px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appraisals as $appr)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-sm">
                                            <span class="avatar-initial rounded-circle bg-label-purple fw-bold">{{ strtoupper(substr($appr->employee?->name ?? 'E', 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-6">{{ $appr->employee?->name ?? 'Employee' }}</div>
                                            <small class="text-muted">{{ $appr->employee?->designation ?? ucfirst($appr->employee?->role ?? 'Staff') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary">{{ number_format($appr->project_score, 1) }} / 100</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">{{ number_format($appr->attendance_score, 1) }} / 100</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-warning">{{ number_format($appr->behaviour_score, 1) }} / 100</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="fw-extrabold text-dark fs-6" style="min-width: 55px;">{{ number_format($appr->overall_score, 1) }}%</div>
                                        <div class="score-progress flex-grow-1" style="width: 80px;">
                                            <div class="score-progress-bar {{ $appr->overall_score >= 80 ? 'bg-success' : ($appr->overall_score >= 70 ? 'bg-primary' : 'bg-warning') }}" style="width: {{ min(100, $appr->overall_score) }}%;"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="{{ $appr->grade_badge }}">
                                        Grade {{ $appr->overall_grade }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge px-3 py-1.5 fw-bold" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;">
                                        <i class="bx bx-check me-1"></i> {{ $appr->recommendation }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-1 justify-content-end">
                                        <button class="btn btn-sm btn-icon btn-light" type="button" data-bs-toggle="modal" data-bs-target="#viewAppraisalModal-{{ $appr->id }}" title="View Full Breakdown Card">
                                            <i class="bx bx-show text-info"></i>
                                        </button>
                                        @if(auth()->user()?->role === 'admin' || auth()->user()?->role === 'hr')
                                        <button class="btn btn-sm btn-icon btn-light" type="button" data-bs-toggle="modal" data-bs-target="#editAppraisalModal-{{ $appr->id }}" title="Edit Scores">
                                            <i class="bx bx-edit text-primary"></i>
                                        </button>
                                        <form method="POST" action="{{ route('appraisal.destroy', $appr->id) }}" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this appraisal record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-light" title="Delete Entry">
                                                <i class="bx bx-trash text-danger"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- VIEW FULL APPRAISAL CARD MODAL --}}
                            <div class="modal fade" id="viewAppraisalModal-{{ $appr->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-purple text-white">
                                            <h5 class="modal-title text-white fw-bold"><i class="bx bx-award me-1"></i> Appraisal Summary: {{ $appr->employee?->name }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="d-flex justify-content-between align-items-center mb-4">
                                                <div>
                                                    <h4 class="fw-bold text-dark mb-0">{{ $appr->employee?->name }}</h4>
                                                    <small class="text-muted">{{ $appr->employee?->designation ?? 'Staff Member' }} | Period: <strong>{{ $appr->appraisal_period }}</strong></small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="{{ $appr->grade_badge }}">Grade {{ $appr->overall_grade }}</span>
                                                    <div class="fw-bold text-dark mt-1">Final Score: {{ number_format($appr->overall_score, 1) }} / 100</div>
                                                </div>
                                            </div>

                                            <div class="row g-3 mb-4">
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded border border-primary">
                                                        <small class="text-primary d-block fw-bold uppercase">1. Project Work (40%)</small>
                                                        <div class="fs-4 fw-extrabold text-dark mt-1">{{ number_format($appr->project_score, 1) }} / 100</div>
                                                        <small class="text-muted d-block mt-1">Projects: {{ $appr->projects_count }} | Tasks Done: {{ $appr->completed_tasks }}</small>
                                                        <small class="text-secondary d-block mt-2 font-italic">"{{ $appr->project_remarks }}"</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded border border-success">
                                                        <small class="text-success d-block fw-bold uppercase">2. Attendance (30%)</small>
                                                        <div class="fs-4 fw-extrabold text-dark mt-1">{{ number_format($appr->attendance_score, 1) }} / 100</div>
                                                        <small class="text-muted d-block mt-1">Present: {{ $appr->present_days }} / {{ $appr->total_working_days }} Days ({{ number_format($appr->attendance_percentage, 1) }}%)</small>
                                                        <small class="text-secondary d-block mt-2 font-italic">"{{ $appr->attendance_remarks }}"</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded border border-warning">
                                                        <small class="text-warning d-block fw-bold uppercase">3. Behaviour (30%)</small>
                                                        <div class="fs-4 fw-extrabold text-dark mt-1">{{ number_format($appr->behaviour_score, 1) }} / 100</div>
                                                        <small class="text-muted d-block mt-1">Teamwork: {{ $appr->teamwork_score }}/10 | Comm: {{ $appr->communication_score }}/10</small>
                                                        <small class="text-secondary d-block mt-2 font-italic">"{{ $appr->behaviour_remarks }}"</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="p-3 bg-purple-soft rounded border border-purple">
                                                <small class="text-purple fw-bold uppercase d-block">Official Evaluation Recommendation</small>
                                                <h5 class="fw-extrabold text-purple mb-0 mt-1">{{ $appr->recommendation }}</h5>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(auth()->user()?->role === 'admin' || auth()->user()?->role === 'hr')
                            {{-- EDIT APPRAISAL MODAL --}}
                            <div class="modal fade" id="editAppraisalModal-{{ $appr->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('appraisal.store') }}">
                                            @csrf
                                            <input type="hidden" name="employee_id" value="{{ $appr->employee_id }}">
                                            <input type="hidden" name="appraisal_period" value="{{ $appr->appraisal_period }}">
                                            <div class="modal-header bg-purple text-white">
                                                <h5 class="modal-title text-white fw-bold"><i class="bx bx-edit-alt me-1"></i> Edit Appraisal: {{ $appr->employee?->name }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold text-primary">Project Work Score (0-100)</label>
                                                        <input type="number" name="project_score" class="form-control" value="{{ $appr->project_score }}" step="0.1" min="0" max="100" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold text-success">Attendance Score (0-100)</label>
                                                        <input type="number" name="attendance_score" class="form-control" value="{{ $appr->attendance_score }}" step="0.1" min="0" max="100" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold text-warning">Teamwork Rating (1-10)</label>
                                                        <input type="number" name="teamwork_score" class="form-control" value="{{ $appr->teamwork_score }}" step="0.1" min="1" max="10" required>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold text-warning">Communication Rating (1-10)</label>
                                                        <input type="number" name="communication_score" class="form-control" value="{{ $appr->communication_score }}" step="0.1" min="1" max="10" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold text-warning">Punctuality Rating (1-10)</label>
                                                        <input type="number" name="punctuality_score" class="form-control" value="{{ $appr->punctuality_score }}" step="0.1" min="1" max="10" required>
                                                    </div>

                                                    <div class="col-12">
                                                        <label class="form-label fw-bold">Project Work Remarks</label>
                                                        <textarea name="project_remarks" class="form-control" rows="2">{{ $appr->project_remarks }}</textarea>
                                                    </div>

                                                    <div class="col-12">
                                                        <label class="form-label fw-bold">Behaviour & Soft Skills Remarks</label>
                                                        <textarea name="behaviour_remarks" class="form-control" rows="2">{{ $appr->behaviour_remarks }}</textarea>
                                                    </div>

                                                    <div class="col-12">
                                                        <label class="form-label fw-bold">HR Recommendation</label>
                                                        <select name="recommendation" class="form-select">
                                                            <option value="Promote & Merit Increment" {{ $appr->recommendation == 'Promote & Merit Increment' ? 'selected' : '' }}>Promote & Merit Increment</option>
                                                            <option value="Salary Increment & Role Growth" {{ $appr->recommendation == 'Salary Increment & Role Growth' ? 'selected' : '' }}>Salary Increment & Role Growth</option>
                                                            <option value="Standard Bonus & Continuation" {{ $appr->recommendation == 'Standard Bonus & Continuation' ? 'selected' : '' }}>Standard Bonus & Continuation</option>
                                                            <option value="Role Maintenance & Skill Mentoring" {{ $appr->recommendation == 'Role Maintenance & Skill Mentoring' ? 'selected' : '' }}>Role Maintenance & Skill Mentoring</option>
                                                            <option value="Performance Improvement Plan (PIP)" {{ $appr->recommendation == 'Performance Improvement Plan (PIP)' ? 'selected' : '' }}>Performance Improvement Plan (PIP)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-purple-primary"><i class="bx bx-check-double me-1"></i> Save Updated Appraisal</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">No appraisal records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TABLE 1: PROJECT WORK APPRAISAL TABLE --}}
        <div class="tab-pane fade" id="project-table-pane" role="tabpanel">
            <div class="app-table-card">
                <div class="p-3 text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #065f46 0%, #047857 100%);">
                    <h5 class="text-white fw-bold mb-0"><i class="bx bx-folder-open me-1"></i> Table 1: Project Work Performance Appraisal</h5>
                    <span class="badge bg-white text-dark fw-bold px-3 py-1.5" style="color: #047857 !important;">Weight in Overall Appraisal: 40%</span>
                </div>
                <div class="table-responsive">
                    <table class="table app-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 200px;">Employee</th>
                                <th style="min-width: 160px;">Projects Assigned</th>
                                <th style="min-width: 160px;">Tasks Completed</th>
                                <th style="min-width: 180px;">Project Score (0-100)</th>
                                <th style="min-width: 150px;">Project Grade</th>
                                <th style="min-width: 250px;">Project Performance Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appraisals as $appr)
                            <tr>
                                <td class="fw-bold text-dark">{{ $appr->employee?->name }}</td>
                                <td><span class="badge px-3 py-1.5 fw-bold" style="background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe;">{{ $appr->projects_count }} Project(s)</span></td>
                                <td><span class="badge px-3 py-1.5 fw-bold" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">{{ $appr->completed_tasks }} Task(s)</span></td>
                                <td>
                                    <div class="fw-extrabold text-primary fs-6">{{ number_format($appr->project_score, 1) }} / 100</div>
                                </td>
                                <td>
                                    <span class="{{ $appr->project_score >= 85 ? 'badge-grade-excellent' : ($appr->project_score >= 75 ? 'badge-grade-good' : 'badge-grade-satisfactory') }}">
                                        {{ $appr->project_score >= 85 ? 'Excellent' : ($appr->project_score >= 75 ? 'Good' : 'Satisfactory') }}
                                    </span>
                                </td>
                                <td><small class="text-muted">{{ $appr->project_remarks ?? 'Milestones completed on time.' }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-4">No project metrics recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TABLE 2: ATTENDANCE APPRAISAL TABLE --}}
        <div class="tab-pane fade" id="attendance-table-pane" role="tabpanel">
            <div class="app-table-card">
                <div class="p-3 text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%);">
                    <h5 class="text-white fw-bold mb-0"><i class="bx bx-calendar-check me-1"></i> Table 2: Attendance & Punctuality Appraisal</h5>
                    <span class="badge bg-white text-dark fw-bold px-3 py-1.5" style="color: #1e40af !important;">Weight in Overall Appraisal: 30%</span>
                </div>
                <div class="table-responsive">
                    <table class="table app-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 200px;">Employee</th>
                                <th style="min-width: 180px;">Present / Working Days</th>
                                <th style="min-width: 150px;">Attendance %</th>
                                <th style="min-width: 180px;">Attendance Score (0-100)</th>
                                <th style="min-width: 170px;">Punctuality Status</th>
                                <th style="min-width: 250px;">Attendance Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appraisals as $appr)
                            <tr>
                                <td class="fw-bold text-dark">{{ $appr->employee?->name }}</td>
                                <td><span class="fw-bold text-dark">{{ $appr->present_days }} / {{ $appr->total_working_days }} Days</span></td>
                                <td><span class="fw-extrabold text-success fs-6">{{ number_format($appr->attendance_percentage, 1) }}%</span></td>
                                <td><span class="fw-extrabold text-dark fs-6">{{ number_format($appr->attendance_score, 1) }} / 100</span></td>
                                <td>
                                    <span class="{{ $appr->attendance_percentage >= 90 ? 'badge-grade-excellent' : 'badge-grade-satisfactory' }}">
                                        {{ $appr->attendance_percentage >= 90 ? 'High Punctuality' : 'Standard Punctuality' }}
                                    </span>
                                </td>
                                <td><small class="text-muted">{{ $appr->attendance_remarks ?? 'Consistent daily attendance.' }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-4">No attendance metrics recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TABLE 3: BEHAVIOUR & CONDUCT APPRAISAL TABLE --}}
        <div class="tab-pane fade" id="behaviour-table-pane" role="tabpanel">
            <div class="app-table-card">
                <div class="p-3 text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%);">
                    <h5 class="text-white fw-bold mb-0"><i class="bx bx-smile me-1"></i> Table 3: Behaviour, Soft Skills & Conduct Appraisal</h5>
                    <span class="badge bg-white text-dark fw-bold px-3 py-1.5" style="color: #b45309 !important;">Weight in Overall Appraisal: 30%</span>
                </div>
                <div class="table-responsive">
                    <table class="table app-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 200px;">Employee</th>
                                <th style="min-width: 150px;">Teamwork (1-10)</th>
                                <th style="min-width: 170px;">Communication (1-10)</th>
                                <th style="min-width: 170px;">Punctuality Rating (1-10)</th>
                                <th style="min-width: 180px;">Behaviour Score (0-100)</th>
                                <th style="min-width: 250px;">Conduct Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appraisals as $appr)
                            <tr>
                                <td class="fw-bold text-dark">{{ $appr->employee?->name }}</td>
                                <td><span class="fw-bold text-dark">{{ number_format($appr->teamwork_score, 1) }}</span></td>
                                <td><span class="fw-bold text-dark">{{ number_format($appr->communication_score, 1) }}</span></td>
                                <td><span class="fw-bold text-dark">{{ number_format($appr->punctuality_score, 1) }}</span></td>
                                <td><span class="fw-extrabold text-warning fs-6">{{ number_format($appr->behaviour_score, 1) }} / 100</span></td>
                                <td><small class="text-muted">{{ $appr->behaviour_remarks ?? 'Positive professional attitude.' }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-4">No behaviour metrics recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

@if(auth()->user()?->role === 'admin' || auth()->user()?->role === 'hr')
{{-- EVALUATE EMPLOYEE MODAL --}}
<div class="modal fade" id="evaluateAppraisalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('appraisal.store') }}">
                @csrf
                <div class="modal-header bg-purple text-white">
                    <h5 class="modal-title text-white fw-bold"><i class="bx bx-user-check me-1"></i> New Employee Performance Evaluation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Select Employee <sup class="text-danger">*</sup></label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">-- Choose Employee --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->designation ?? $emp->role }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Appraisal Period <sup class="text-danger">*</sup></label>
                            <input type="text" name="appraisal_period" class="form-control" value="{{ $selectedPeriod }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Project Work Score (0-100) <sup class="text-danger">*</sup></label>
                            <input type="number" name="project_score" class="form-control" value="85" step="0.1" min="0" max="100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-success">Attendance Score (0-100) <sup class="text-danger">*</sup></label>
                            <input type="number" name="attendance_score" class="form-control" value="90" step="0.1" min="0" max="100" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-warning">Teamwork Rating (1-10)</label>
                            <input type="number" name="teamwork_score" class="form-control" value="8.5" step="0.1" min="1" max="10" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-warning">Communication Rating (1-10)</label>
                            <input type="number" name="communication_score" class="form-control" value="8.5" step="0.1" min="1" max="10" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-warning">Punctuality Rating (1-10)</label>
                            <input type="number" name="punctuality_score" class="form-control" value="8.5" step="0.1" min="1" max="10" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Project Performance Remarks</label>
                            <textarea name="project_remarks" class="form-control" rows="2" placeholder="Key project accomplishments..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Behaviour & Soft Skills Remarks</label>
                            <textarea name="behaviour_remarks" class="form-control" rows="2" placeholder="Teamwork, conduct, and communication feedback..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">HR Recommendation</label>
                            <select name="recommendation" class="form-select">
                                <option value="Promote & Merit Increment">Promote & Merit Increment</option>
                                <option value="Salary Increment & Role Growth" selected>Salary Increment & Role Growth</option>
                                <option value="Standard Bonus & Continuation">Standard Bonus & Continuation</option>
                                <option value="Role Maintenance & Skill Mentoring">Role Maintenance & Skill Mentoring</option>
                                <option value="Performance Improvement Plan (PIP)">Performance Improvement Plan (PIP)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-purple-primary"><i class="bx bx-check-circle me-1"></i> Submit Evaluation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
