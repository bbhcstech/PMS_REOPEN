@extends('admin.layout.app')

@section('title', 'Payroll Policies Engine')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Success & Error Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-check-circle fs-4 me-2"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-error-circle fs-4 me-2"></i>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 1. Enterprise Page Header & Action Controls -->
    <div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h4 class="fw-bold mb-0 text-dark"><i class="bx bx-shield-quarter text-primary me-2 fs-3"></i>Payroll Policies Management</h4>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-semibold">
                            ● Active Policy Set (v{{ $activePolicy->version }})
                        </span>
                    </div>
                    <p class="text-muted mb-0">Central rule engine to configure earnings, leave deductions, overtime multipliers, tax slabs, and processing rules.</p>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <small class="text-muted d-none d-md-inline me-2"><i class="bx bx-time me-1"></i>Last Updated: <strong>{{ $summary['last_updated'] }}</strong></small>
                    <button type="button" class="btn btn-outline-info fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#policySimulatorModal">
                        <i class="bx bx-line-chart me-1"></i>Test Policy (Simulator)
                    </button>
                    <button type="button" class="btn btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#createPolicyModal">
                        <i class="bx bx-plus me-1"></i>Create Policy
                    </button>
                    <button type="submit" form="mainPolicyForm" class="btn btn-primary fw-bold shadow-sm px-4">
                        <i class="bx bx-save me-1"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Structured Overview KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-4 rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Total Policies</span>
                        <div class="avatar avatar-sm bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center">
                            <i class="bx bx-file fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $summary['total_policies'] }}</h3>
                    <small class="text-muted">Configured Rulesets</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-4 rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Active Policies</span>
                        <div class="avatar avatar-sm bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center">
                            <i class="bx bx-check-shield fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-success">{{ $summary['active_policies'] }}</h3>
                    <small class="text-muted">Published Rules</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-4 rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Pending Changes</span>
                        <div class="avatar avatar-sm bg-warning-subtle text-warning rounded-3 d-flex align-items-center justify-content-center">
                            <i class="bx bx-edit-alt fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-warning">{{ $summary['pending_changes'] }}</h3>
                    <small class="text-muted">Draft Versions</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-4 rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Last Updated</span>
                        <div class="avatar avatar-sm bg-info-subtle text-info rounded-3 d-flex align-items-center justify-content-center">
                            <i class="bx bx-history fs-5"></i>
                        </div>
                    </div>
                    <h6 class="fw-bold mb-0 text-dark text-truncate">{{ $summary['last_updated'] }}</h6>
                    <small class="text-muted">Policy Engine Snapshot</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Policy Categories Navigation & Rules Cards -->
    <form action="{{ route('payroll.policies.update', $activePolicy->id) }}" method="POST" id="mainPolicyForm">
        @csrf
        @method('PUT')
        
        <input type="hidden" name="name" value="{{ $activePolicy->name }}">
        <input type="hidden" name="status" value="published">

        <div class="row g-4 mb-4">
            
            <!-- Structured Category Navigation Sidebar -->
            <div class="col-lg-3 col-12">
                <div class="card border-0 shadow-sm sticky-top" style="top: 80px; z-index: 5; border-radius: 12px;">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-category text-primary me-2"></i>Categories</h6>
                        <span class="badge bg-light text-primary border">12 Rulesets</span>
                    </div>
                    <div class="list-group list-group-flush p-2" id="policyTabs" role="tablist">
                        <a class="list-group-item list-group-item-action active rounded-3 mb-1 py-2.5 fw-semibold d-flex align-items-center justify-content-between" id="tab-salary-tab" data-bs-toggle="list" href="#tab-salary" role="tab">
                            <span><i class="bx bx-money me-2 text-primary"></i>1. Salary & Earnings</span>
                            <i class="bx bx-chevron-right text-muted fs-6"></i>
                        </a>
                        <a class="list-group-item list-group-item-action rounded-3 mb-1 py-2.5 fw-semibold d-flex align-items-center justify-content-between" id="tab-working-tab" data-bs-toggle="list" href="#tab-working" role="tab">
                            <span><i class="bx bx-calendar me-2 text-info"></i>2. Working Days</span>
                            <i class="bx bx-chevron-right text-muted fs-6"></i>
                        </a>
                        <a class="list-group-item list-group-item-action rounded-3 mb-1 py-2.5 fw-semibold d-flex align-items-center justify-content-between" id="tab-leave-tab" data-bs-toggle="list" href="#tab-leave" role="tab">
                            <span><i class="bx bx-time-five me-2 text-warning"></i>3. Leave & Absence</span>
                            <i class="bx bx-chevron-right text-muted fs-6"></i>
                        </a>
                        <a class="list-group-item list-group-item-action rounded-3 mb-1 py-2.5 fw-semibold d-flex align-items-center justify-content-between" id="tab-overtime-tab" data-bs-toggle="list" href="#tab-overtime" role="tab">
                            <span><i class="bx bx-timer me-2 text-danger"></i>4. Overtime Policy</span>
                            <i class="bx bx-chevron-right text-muted fs-6"></i>
                        </a>
                        <a class="list-group-item list-group-item-action rounded-3 mb-1 py-2.5 fw-semibold d-flex align-items-center justify-content-between" id="tab-deductions-tab" data-bs-toggle="list" href="#tab-deductions" role="tab">
                            <span><i class="bx bx-minus-circle me-2 text-secondary"></i>5. Deductions Rules</span>
                            <i class="bx bx-chevron-right text-muted fs-6"></i>
                        </a>
                        <a class="list-group-item list-group-item-action rounded-3 mb-1 py-2.5 fw-semibold d-flex align-items-center justify-content-between" id="tab-tax-tab" data-bs-toggle="list" href="#tab-tax" role="tab">
                            <span><i class="bx bx-receipt me-2 text-purple"></i>6. Tax Slabs</span>
                            <i class="bx bx-chevron-right text-muted fs-6"></i>
                        </a>
                        <a class="list-group-item list-group-item-action rounded-3 mb-1 py-2.5 fw-semibold d-flex align-items-center justify-content-between" id="tab-bonus-tab" data-bs-toggle="list" href="#tab-bonus" role="tab">
                            <span><i class="bx bx-gift me-2 text-success"></i>7. Bonus & Incentives</span>
                            <i class="bx bx-chevron-right text-muted fs-6"></i>
                        </a>
                        <a class="list-group-item list-group-item-action rounded-3 mb-1 py-2.5 fw-semibold d-flex align-items-center justify-content-between" id="tab-attendance-tab" data-bs-toggle="list" href="#tab-attendance" role="tab">
                            <span><i class="bx bx-check-double me-2 text-info"></i>8. Attendance Policy</span>
                            <i class="bx bx-chevron-right text-muted fs-6"></i>
                        </a>
                        <a class="list-group-item list-group-item-action rounded-3 mb-1 py-2.5 fw-semibold d-flex align-items-center justify-content-between" id="tab-processing-tab" data-bs-toggle="list" href="#tab-processing" role="tab">
                            <span><i class="bx bx-cog me-2 text-primary"></i>9. Processing Rules</span>
                            <i class="bx bx-chevron-right text-muted fs-6"></i>
                        </a>
                        <a class="list-group-item list-group-item-action rounded-3 mb-1 py-2.5 fw-semibold d-flex align-items-center justify-content-between" id="tab-rounding-tab" data-bs-toggle="list" href="#tab-rounding" role="tab">
                            <span><i class="bx bx-math me-2 text-dark"></i>10. Rounding & Precision</span>
                            <i class="bx bx-chevron-right text-muted fs-6"></i>
                        </a>
                        <a class="list-group-item list-group-item-action rounded-3 mb-1 py-2.5 fw-semibold d-flex align-items-center justify-content-between" id="tab-payslip-tab" data-bs-toggle="list" href="#tab-payslip" role="tab">
                            <span><i class="bx bx-detail me-2 text-success"></i>11. Payslip Display</span>
                            <i class="bx bx-chevron-right text-muted fs-6"></i>
                        </a>
                        <a class="list-group-item list-group-item-action rounded-3 py-2.5 fw-semibold d-flex align-items-center justify-content-between" id="tab-compliance-tab" data-bs-toggle="list" href="#tab-compliance" role="tab">
                            <span><i class="bx bx-shield-check me-2 text-primary"></i>12. Compliance</span>
                            <i class="bx bx-chevron-right text-muted fs-6"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Structured Main Rules Form Content -->
            <div class="col-lg-9 col-12">
                <div class="tab-content" id="policyTabContent">
                    
                    <!-- 1. SALARY & EARNINGS -->
                    @php $salaryRules = $activePolicy->salary_earnings_rules ?? []; @endphp
                    <div class="tab-pane fade show active" id="tab-salary" role="tabpanel">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-money text-primary me-2 fs-5"></i>1. Salary & Earnings Policy Configuration</h6>
                                <span class="badge bg-primary-subtle text-primary">Core Formula</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Basic Salary Allocation (% of Total CTC)</label>
                                            <p class="text-muted small mb-2">Percentage allocated to employee basic salary.</p>
                                            <div class="input-group">
                                                <input type="number" step="0.1" min="0" max="100" name="salary_earnings_rules[basic_percentage]" class="form-control border-secondary-subtle font-monospace fw-bold" value="{{ $salaryRules['basic_percentage'] ?? 50 }}" required>
                                                <span class="input-group-text bg-white fw-bold text-primary">%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">HRA Allowance (% of Basic Salary)</label>
                                            <p class="text-muted small mb-2">House Rent Allowance relative to Basic Salary.</p>
                                            <div class="input-group">
                                                <input type="number" step="0.1" min="0" max="100" name="salary_earnings_rules[hra_percentage]" class="form-control border-secondary-subtle font-monospace fw-bold" value="{{ $salaryRules['hra_percentage'] ?? 40 }}" required>
                                                <span class="input-group-text bg-white fw-bold text-primary">% of Basic</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Special Allowance Mode</label>
                                            <p class="text-muted small mb-2">Select how Special Allowance is computed.</p>
                                            <select name="salary_earnings_rules[special_allowance_mode]" class="form-select border-secondary-subtle fw-semibold">
                                                <option value="fixed" {{ ($salaryRules['special_allowance_mode'] ?? 'fixed') == 'fixed' ? 'selected' : '' }}>Fixed Default Amount</option>
                                                <option value="balancing" {{ ($salaryRules['special_allowance_mode'] ?? '') == 'balancing' ? 'selected' : '' }}>Balancing Figure (Gross - Basic - HRA)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Default Special Allowance (₹)</label>
                                            <p class="text-muted small mb-2">Standard fallback amount when fixed mode is active.</p>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white fw-bold text-dark">₹</span>
                                                <input type="number" min="0" name="salary_earnings_rules[special_allowance_default]" class="form-control border-secondary-subtle font-monospace fw-bold" value="{{ $salaryRules['special_allowance_default'] ?? 3000 }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Formula Preview Card -->
                                <div class="p-3 bg-light border-start border-primary border-4 rounded-3 mb-0">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="bx bx-calculator text-primary fs-5"></i>
                                        <strong class="text-primary">Live Formula Preview</strong>
                                    </div>
                                    <p class="font-monospace text-dark mb-0 small">
                                        Gross Salary = Basic Salary ({{ $salaryRules['basic_percentage'] ?? 50 }}%) + HRA ({{ $salaryRules['hra_percentage'] ?? 40 }}% of Basic) + Special Allowance (₹{{ number_format($salaryRules['special_allowance_default'] ?? 3000, 2) }})
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. WORKING DAYS -->
                    @php $workRules = $activePolicy->working_days_rules ?? []; @endphp
                    <div class="tab-pane fade" id="tab-working" role="tabpanel">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-calendar text-info me-2 fs-5"></i>2. Working Days Policy</h6>
                                <span class="badge bg-info-subtle text-info">Calendar & Days</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Working Days Calculation Method</label>
                                            <p class="text-muted small mb-2">Choose how standard monthly working days are defined.</p>
                                            <select name="working_days_rules[calculation_method]" class="form-select border-secondary-subtle fw-semibold">
                                                <option value="fixed" {{ ($workRules['calculation_method'] ?? 'fixed') == 'fixed' ? 'selected' : '' }}>Fixed Days Per Month (Default 22)</option>
                                                <option value="calendar" {{ ($workRules['calculation_method'] ?? '') == 'calendar' ? 'selected' : '' }}>Actual Calendar Days (e.g. 30/31)</option>
                                                <option value="attendance" {{ ($workRules['calculation_method'] ?? '') == 'attendance' ? 'selected' : '' }}>Attendance-based Working Days</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Standard Working Days Count</label>
                                            <p class="text-muted small mb-2">Base number of days for pro-rata pay calculation.</p>
                                            <input type="number" min="1" max="31" name="working_days_rules[standard_working_days]" class="form-control border-secondary-subtle font-monospace fw-bold" value="{{ $workRules['standard_working_days'] ?? 22 }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Holiday Treatment</label>
                                            <p class="text-muted small mb-2">How official company holidays affect salary.</p>
                                            <select name="working_days_rules[holiday_treatment]" class="form-select border-secondary-subtle fw-semibold">
                                                <option value="paid" {{ ($workRules['holiday_treatment'] ?? 'paid') == 'paid' ? 'selected' : '' }}>Paid Holiday (Included in Payable Days)</option>
                                                <option value="unpaid" {{ ($workRules['holiday_treatment'] ?? '') == 'unpaid' ? 'selected' : '' }}>Unpaid Holiday</option>
                                                <option value="excluded" {{ ($workRules['holiday_treatment'] ?? '') == 'excluded' ? 'selected' : '' }}>Excluded from Total Working Days</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. LEAVE & ABSENCE -->
                    @php $leaveRules = $activePolicy->leave_absence_rules ?? []; @endphp
                    <div class="tab-pane fade" id="tab-leave" role="tabpanel">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-time-five text-warning me-2 fs-5"></i>3. Leave & Absence Deduction Policy</h6>
                                <span class="badge bg-warning-subtle text-warning-emphasis">Absence Rules</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Full Day Approved Leave</label>
                                            <p class="text-muted small mb-2">Treatment for full day approved leaves.</p>
                                            <select name="leave_absence_rules[full_day_leave]" class="form-select border-secondary-subtle fw-semibold">
                                                <option value="paid" {{ ($leaveRules['full_day_leave'] ?? 'paid') == 'paid' ? 'selected' : '' }}>Paid Leave</option>
                                                <option value="unpaid" {{ ($leaveRules['full_day_leave'] ?? '') == 'unpaid' ? 'selected' : '' }}>Unpaid Leave</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Half Day Approved Leave</label>
                                            <p class="text-muted small mb-2">Treatment for half day approved leaves.</p>
                                            <select name="leave_absence_rules[half_day_leave]" class="form-select border-secondary-subtle fw-semibold">
                                                <option value="paid" {{ ($leaveRules['half_day_leave'] ?? 'paid') == 'paid' ? 'selected' : '' }}>Paid Half Day</option>
                                                <option value="unpaid" {{ ($leaveRules['half_day_leave'] ?? '') == 'unpaid' ? 'selected' : '' }}>Unpaid Half Day</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Leave Deduction Method</label>
                                            <p class="text-muted small mb-2">Formula used to compute per-day absence deduction.</p>
                                            <select name="leave_absence_rules[deduction_method]" class="form-select border-secondary-subtle fw-semibold">
                                                <option value="per_day_gross" {{ ($leaveRules['deduction_method'] ?? 'per_day_gross') == 'per_day_gross' ? 'selected' : '' }}>Per-Day Salary = Gross Salary / Working Days</option>
                                                <option value="per_day_basic" {{ ($leaveRules['deduction_method'] ?? '') == 'per_day_basic' ? 'selected' : '' }}>Per-Day Salary = Basic Salary / Working Days</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 bg-light border-start border-warning border-4 rounded-3">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="bx bx-info-circle text-warning-emphasis fs-5"></i>
                                        <strong class="text-warning-emphasis">Absence Deduction Formula</strong>
                                    </div>
                                    <p class="font-monospace text-dark mb-0 small">
                                        Unpaid Absence Deduction = Unpaid Absents × (Monthly Salary / Working Days)
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. OVERTIME POLICY -->
                    @php $otRules = $activePolicy->overtime_rules ?? []; @endphp
                    <div class="tab-pane fade" id="tab-overtime" role="tabpanel">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-timer text-danger me-2 fs-5"></i>4. Overtime Calculation Rules</h6>
                                <span class="badge bg-danger-subtle text-danger">Multipliers</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="p-3 bg-light rounded-3 border mb-4">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="overtime_rules[enabled]" id="otEnabledToggle" value="1" {{ !empty($otRules['enabled']) ? 'checked' : '' }}>
                                        <label class="form-check-input-label fw-bold text-dark ms-2" for="otEnabledToggle">Enable Overtime Pay Calculations</label>
                                    </div>
                                </div>
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Hourly Rate Basis</label>
                                            <p class="text-muted small mb-2">Base salary used to compute hourly overtime rate.</p>
                                            <select name="overtime_rules[calculation_basis]" class="form-select border-secondary-subtle fw-semibold">
                                                <option value="hourly_gross" {{ ($otRules['calculation_basis'] ?? 'hourly_gross') == 'hourly_gross' ? 'selected' : '' }}>Gross Salary Hourly Rate</option>
                                                <option value="hourly_basic" {{ ($otRules['calculation_basis'] ?? '') == 'hourly_basic' ? 'selected' : '' }}>Basic Salary Hourly Rate</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Weekday Multiplier</label>
                                            <p class="text-muted small mb-2">Normal overtime rate (e.g. 1.0x).</p>
                                            <input type="number" step="0.1" min="1.0" name="overtime_rules[normal_multiplier]" class="form-control border-secondary-subtle font-monospace fw-bold" value="{{ $otRules['normal_multiplier'] ?? 1.0 }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Weekend Multiplier</label>
                                            <p class="text-muted small mb-2">Saturday/Sunday overtime rate (e.g. 1.5x).</p>
                                            <input type="number" step="0.1" min="1.0" name="overtime_rules[weekend_multiplier]" class="form-control border-secondary-subtle font-monospace fw-bold" value="{{ $otRules['weekend_multiplier'] ?? 1.5 }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Public Holiday Multiplier</label>
                                            <p class="text-muted small mb-2">Holiday overtime rate (e.g. 2.0x).</p>
                                            <input type="number" step="0.1" min="1.0" name="overtime_rules[holiday_multiplier]" class="form-control border-secondary-subtle font-monospace fw-bold" value="{{ $otRules['holiday_multiplier'] ?? 2.0 }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. DEDUCTIONS -->
                    @php $dedRules = $activePolicy->deductions_rules ?? []; @endphp
                    <div class="tab-pane fade" id="tab-deductions" role="tabpanel">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-minus-circle text-secondary me-2 fs-5"></i>5. Statutory & Custom Deductions</h6>
                                <span class="badge bg-secondary-subtle text-secondary">PF / ESI / PT</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-light">
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" name="deductions_rules[pf_enabled]" id="pfToggle" value="1" {{ !empty($dedRules['pf_enabled']) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold text-dark ms-2" for="pfToggle">PF (Provident Fund)</label>
                                            </div>
                                            <div class="row g-2 pt-2">
                                                <div class="col-6">
                                                    <small class="text-muted d-block mb-1">Rate (% of Basic)</small>
                                                    <input type="number" step="0.1" name="deductions_rules[pf_percentage]" class="form-control form-control-sm font-monospace fw-bold" value="{{ $dedRules['pf_percentage'] ?? 12 }}">
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block mb-1">Max Limit (₹)</small>
                                                    <input type="number" name="deductions_rules[pf_max_limit]" class="form-control form-control-sm font-monospace fw-bold" value="{{ $dedRules['pf_max_limit'] ?? 1800 }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-light">
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" name="deductions_rules[esi_enabled]" id="esiToggle" value="1" {{ !empty($dedRules['esi_enabled']) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold text-dark ms-2" for="esiToggle">ESI (Employee State Insurance)</label>
                                            </div>
                                            <div class="pt-2">
                                                <small class="text-muted d-block mb-1">Rate (% of Gross Salary)</small>
                                                <input type="number" step="0.01" name="deductions_rules[esi_percentage]" class="form-control form-control-sm font-monospace fw-bold" value="{{ $dedRules['esi_percentage'] ?? 0.75 }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-light">
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" name="deductions_rules[pt_enabled]" id="ptToggle" value="1" {{ !empty($dedRules['pt_enabled']) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold text-dark ms-2" for="ptToggle">Professional Tax (PT)</label>
                                            </div>
                                            <div class="pt-2">
                                                <small class="text-muted d-block mb-1">Fixed Monthly Amount (₹)</small>
                                                <input type="number" name="deductions_rules[pt_fixed_amount]" class="form-control form-control-sm font-monospace fw-bold" value="{{ $dedRules['pt_fixed_amount'] ?? 200 }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6. TAX SLABS -->
                    @php $taxRules = $activePolicy->tax_rules ?? []; @endphp
                    <div class="tab-pane fade" id="tab-tax" role="tabpanel">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-receipt text-purple me-2 fs-5"></i>6. Income Tax (TDS) Slabs Configuration</h6>
                                <span class="badge bg-purple text-white">TDS Slabs</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="p-3 bg-light rounded-3 border mb-4">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="tax_rules[enabled]" id="taxToggle" value="1" {{ !empty($taxRules['enabled']) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark ms-2" for="taxToggle">Enable Automatic Tax (TDS) Calculation</label>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle text-nowrap">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="fw-bold text-dark">Slab Min Income (₹)</th>
                                                <th class="fw-bold text-dark">Slab Max Income (₹)</th>
                                                <th class="fw-bold text-dark">Tax Rate (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($taxRules['slabs'] ?? [
                                                ['min'=>0,'max'=>300000,'rate'=>0],
                                                ['min'=>300001,'max'=>600000,'rate'=>5],
                                                ['min'=>600001,'max'=>1200000,'rate'=>10],
                                                ['min'=>1200001,'max'=>99999999,'rate'=>20]
                                            ] as $idx => $slab)
                                                <tr>
                                                    <td><input type="number" name="tax_rules[slabs][{{ $idx }}][min]" class="form-control form-control-sm font-monospace" value="{{ $slab['min'] }}"></td>
                                                    <td><input type="number" name="tax_rules[slabs][{{ $idx }}][max]" class="form-control form-control-sm font-monospace" value="{{ $slab['max'] }}"></td>
                                                    <td><input type="number" step="0.1" name="tax_rules[slabs][{{ $idx }}][rate]" class="form-control form-control-sm font-monospace fw-bold text-primary" value="{{ $slab['rate'] }}"></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 7. BONUS -->
                    @php $bonusRules = $activePolicy->bonus_rules ?? []; @endphp
                    <div class="tab-pane fade" id="tab-bonus" role="tabpanel">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-gift text-success me-2 fs-5"></i>7. Bonus & Incentives Policy</h6>
                                <span class="badge bg-success-subtle text-success">Annual / Monthly</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="p-3 bg-light rounded-3 border mb-4">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="bonus_rules[enabled]" id="bonusToggle" value="1" {{ !empty($bonusRules['enabled']) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark ms-2" for="bonusToggle">Enable Statutory / Performance Bonus</label>
                                    </div>
                                </div>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Bonus Calculation Type</label>
                                            <select name="bonus_rules[bonus_type]" class="form-select border-secondary-subtle fw-semibold">
                                                <option value="percentage_basic" {{ ($bonusRules['bonus_type'] ?? 'percentage_basic') == 'percentage_basic' ? 'selected' : '' }}>Percentage of Basic Salary</option>
                                                <option value="fixed" {{ ($bonusRules['bonus_type'] ?? '') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Bonus Rate / Amount</label>
                                            <input type="number" step="0.1" name="bonus_rules[percentage]" class="form-control border-secondary-subtle font-monospace fw-bold" value="{{ $bonusRules['percentage'] ?? 10 }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 8. ATTENDANCE -->
                    @php $attRules = $activePolicy->attendance_rules ?? []; @endphp
                    <div class="tab-pane fade" id="tab-attendance" role="tabpanel">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-check-double text-info me-2 fs-5"></i>8. Attendance Policy</h6>
                                <span class="badge bg-info-subtle text-info">Grace & Lock</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Late Arrival Grace Period (Minutes)</label>
                                            <input type="number" name="attendance_rules[late_grace_minutes]" class="form-control border-secondary-subtle font-monospace fw-bold" value="{{ $attRules['late_grace_minutes'] ?? 15 }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Attendance Lock Date (Day of Month)</label>
                                            <input type="number" min="1" max="31" name="attendance_rules[lock_date]" class="form-control border-secondary-subtle font-monospace fw-bold" value="{{ $attRules['lock_date'] ?? 25 }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 9. PROCESSING -->
                    @php $procRules = $activePolicy->processing_rules ?? []; @endphp
                    <div class="tab-pane fade" id="tab-processing" role="tabpanel">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-cog text-primary me-2 fs-5"></i>9. Payroll Processing & Lock Policy</h6>
                                <span class="badge bg-primary-subtle text-primary">Execution</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Payroll Cycle Frequency</label>
                                            <select name="processing_rules[frequency]" class="form-select border-secondary-subtle fw-semibold">
                                                <option value="monthly" selected>Monthly</option>
                                                <option value="biweekly">Bi-Weekly</option>
                                                <option value="weekly">Weekly</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Salary Payment Day (Day of Month)</label>
                                            <input type="number" min="1" max="31" name="processing_rules[payment_date_day]" class="form-control border-secondary-subtle font-monospace fw-bold" value="{{ $procRules['payment_date_day'] ?? 5 }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 10. ROUNDING & PRECISION -->
                    @php $roundRules = $activePolicy->rounding_rules ?? []; @endphp
                    <div class="tab-pane fade" id="tab-rounding" role="tabpanel">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-math text-dark me-2 fs-5"></i>10. Rounding & Calculation Precision</h6>
                                <span class="badge bg-dark text-white">Precision</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Rounding Mode</label>
                                            <select name="rounding_rules[rounding_mode]" class="form-select border-secondary-subtle fw-semibold">
                                                <option value="nearest_rupee" {{ ($roundRules['rounding_mode'] ?? 'nearest_rupee') == 'nearest_rupee' ? 'selected' : '' }}>Round to Nearest Rupee (₹1)</option>
                                                <option value="nearest_5" {{ ($roundRules['rounding_mode'] ?? '') == 'nearest_5' ? 'selected' : '' }}>Round to Nearest ₹5</option>
                                                <option value="nearest_10" {{ ($roundRules['rounding_mode'] ?? '') == 'nearest_10' ? 'selected' : '' }}>Round to Nearest ₹10</option>
                                                <option value="none" {{ ($roundRules['rounding_mode'] ?? '') == 'none' ? 'selected' : '' }}>No Rounding (Exact Decimals)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <label class="form-label fw-bold text-dark mb-1">Decimal Precision</label>
                                            <input type="number" min="0" max="4" name="rounding_rules[decimal_precision]" class="form-control border-secondary-subtle font-monospace fw-bold" value="{{ $roundRules['decimal_precision'] ?? 2 }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 11. PAYSLIP DISPLAY -->
                    @php $payslipRules = $activePolicy->payslip_rules ?? []; @endphp
                    <div class="tab-pane fade" id="tab-payslip" role="tabpanel">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-detail text-success me-2 fs-5"></i>11. Payslip Display Preferences</h6>
                                <span class="badge bg-success-subtle text-success">Layout Toggles</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="payslip_rules[show_emp_id]" id="psEmpId" value="1" {{ !empty($payslipRules['show_emp_id']) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold text-dark ms-1" for="psEmpId">Show Employee ID</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="payslip_rules[show_attendance]" id="psAtt" value="1" {{ !empty($payslipRules['show_attendance']) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold text-dark ms-1" for="psAtt">Show Attendance Summary</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="payslip_rules[show_leave]" id="psLeave" value="1" {{ !empty($payslipRules['show_leave']) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold text-dark ms-1" for="psLeave">Show Leave Balances</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark mb-1">Payslip Footer Text</label>
                                    <input type="text" name="payslip_rules[footer_text]" class="form-control border-secondary-subtle" value="{{ $payslipRules['footer_text'] ?? 'This is a computer-generated payslip. Signature not required.' }}">
                                </div>
                                <div>
                                    <label class="form-label fw-bold text-dark mb-1">Authorized Signatory Title</label>
                                    <input type="text" name="payslip_rules[authorized_by]" class="form-control border-secondary-subtle" value="{{ $payslipRules['authorized_by'] ?? 'HR & Payroll Manager' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 12. STATUTORY COMPLIANCE -->
                    <div class="tab-pane fade" id="tab-compliance" role="tabpanel">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-shield-check text-primary me-2 fs-5"></i>12. Statutory Compliance Rules</h6>
                                <span class="badge bg-primary-subtle text-primary">Compliance</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="alert alert-success d-flex align-items-center mb-0 border-0 shadow-sm">
                                    <i class="bx bx-check-shield fs-2 me-3 text-success"></i>
                                    <div>
                                        <strong class="d-block mb-1 text-success-emphasis fs-6">Fully Compliant Engine:</strong>
                                        This policy ruleset automatically conforms to standard statutory payroll labor laws, Minimum Wage acts, and PF/ESI compliance checks.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>

    <!-- 4. Configured Policies Table & Version History -->
    <div class="row g-4">
        
        <!-- Policies Datatable -->
        <div class="col-lg-8 col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-table text-primary me-2"></i>Configured Policy Rulesets</h6>
                    <span class="badge bg-light text-dark border">Total: {{ $policiesList->count() }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-nowrap mb-0" style="font-size: 0.88rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Policy Name</th>
                                <th>Version</th>
                                <th>Status</th>
                                <th>Effective From</th>
                                <th>Created By</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($policiesList as $pol)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark d-block">{{ $pol->name }}</span>
                                        <small class="text-muted font-monospace">{{ $pol->code }}</small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">v{{ $pol->version }}</span></td>
                                    <td>
                                        @if($pol->status === 'published')
                                            <span class="badge bg-success">Published</span>
                                        @else
                                            <span class="badge bg-warning">Draft</span>
                                        @endif
                                    </td>
                                    <td>{{ $pol->effective_from ? $pol->effective_from->format('d M Y') : 'Immediate' }}</td>
                                    <td>{{ $pol->creator?->name ?: 'System' }}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <form action="{{ route('payroll.policies.duplicate', $pol->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-light border" title="Duplicate Policy">
                                                    <i class="bx bx-copy text-primary"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('payroll.policies.toggle-status', $pol->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-light border" title="Toggle Status">
                                                    <i class="bx bx-power-off {{ $pol->status === 'published' ? 'text-danger' : 'text-success' }}"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Version History Log -->
        <div class="col-lg-4 col-12">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-history text-info me-2"></i>Policy Version History</h6>
                </div>
                <div class="card-body p-3" style="max-height: 400px; overflow-y: auto;">
                    @forelse($histories as $h)
                        <div class="p-3 bg-light rounded-3 mb-2 border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-primary">v{{ $h->version }}</span>
                                <small class="text-muted">{{ $h->created_at->format('d M Y, h:i A') }}</small>
                            </div>
                            <small class="text-dark d-block fw-semibold">{{ $h->policy?->name ?: 'Payroll Policy' }}</small>
                            <small class="text-muted">Updated by: {{ $h->changer?->name ?: 'Super Admin' }}</small>
                        </div>
                    @empty
                        <p class="text-muted small text-center my-4">No version history records found.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ========================================================= -->
<!-- MODAL: POLICY SIMULATOR ("TEST POLICY") -->
<!-- ========================================================= -->
<div class="modal fade" id="policySimulatorModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-info text-white p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-white text-info rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4">
                        <i class="bx bx-line-chart"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0">Payroll Policy Simulator</h5>
                        <small class="text-white-50">Test policy impact on employee net pay without modifying actual database records</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 bg-light-subtle">
                <form id="simulatorForm">
                    @csrf
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Select Test Employee</label>
                            <select id="simUserId" class="form-select border-secondary-subtle" required>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employeeDetail?->employee_id ?: 'EMP-'.$emp->id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-dark">Year</label>
                            <input type="number" id="simYear" class="form-control border-secondary-subtle" value="{{ date('Y') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-dark">Month</label>
                            <select id="simMonth" class="form-select border-secondary-subtle">
                                @foreach(range(1,12) as $m)
                                    <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="text-end mb-4">
                        <button type="button" id="btnRunSimulation" class="btn btn-info text-white fw-bold shadow-sm px-4">
                            <i class="bx bx-play me-1"></i>Run Policy Impact Test
                        </button>
                    </div>
                </form>

                <!-- Simulation Output Panel -->
                <div id="simResultsContainer" class="d-none">
                    <hr>
                    <h6 class="fw-bold text-dark mb-3"><i class="bx bx-git-compare text-primary me-2"></i>Side-by-Side Impact Comparison</h6>
                    
                    <div class="row g-3 mb-3">
                        <!-- Current Policy Card -->
                        <div class="col-md-6">
                            <div class="p-3 bg-white rounded-3 border border-secondary-subtle h-100">
                                <span class="badge bg-secondary mb-2">Current Active Policy</span>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td class="text-muted">Basic Salary:</td><td class="text-end fw-bold" id="currBasic">₹0.00</td></tr>
                                    <tr><td class="text-muted">HRA Allowance:</td><td class="text-end fw-bold" id="currHra">₹0.00</td></tr>
                                    <tr><td class="text-muted">Gross Salary:</td><td class="text-end fw-bold" id="currGross">₹0.00</td></tr>
                                    <tr><td class="text-muted">Deductions:</td><td class="text-end fw-bold text-danger" id="currDeductions">₹0.00</td></tr>
                                    <tr class="border-top"><td class="fw-bold text-dark">Current Net Salary:</td><td class="text-end fw-bold text-dark fs-6" id="currNet">₹0.00</td></tr>
                                </table>
                            </div>
                        </div>

                        <!-- Simulated Policy Card -->
                        <div class="col-md-6">
                            <div class="p-3 bg-white rounded-3 border border-success-subtle h-100 shadow-sm">
                                <span class="badge bg-success mb-2">Simulated Policy Result</span>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td class="text-muted">Simulated Basic:</td><td class="text-end fw-bold" id="simBasic">₹0.00</td></tr>
                                    <tr><td class="text-muted">Simulated HRA:</td><td class="text-end fw-bold" id="simHra">₹0.00</td></tr>
                                    <tr><td class="text-muted">Simulated Gross:</td><td class="text-end fw-bold" id="simGross">₹0.00</td></tr>
                                    <tr><td class="text-muted">Simulated Deductions:</td><td class="text-end fw-bold text-danger" id="simDeductions">₹0.00</td></tr>
                                    <tr class="border-top"><td class="fw-bold text-success">Simulated Net Salary:</td><td class="text-end fw-bold text-success fs-5" id="simNet">₹0.00</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="simDifferenceBanner" class="p-3 rounded-3 border text-center">
                        <strong id="simDiffText" class="fs-6">Difference: ₹0.00</strong>
                    </div>
                </div>

            </div>
            
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary px-4 fw-semibold" data-bs-dismiss="modal">Close Simulator</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Create Policy -->
<div class="modal fade" id="createPolicyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold text-white"><i class="bx bx-plus me-2"></i>Create New Payroll Policy</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('payroll.policies.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Policy Ruleset Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. FY 2026-27 Executive Policy" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Initial Status</label>
                        <select name="status" class="form-select">
                            <option value="published" selected>Published (Active Immediately)</option>
                            <option value="draft">Draft (Review Later)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Effective From</label>
                        <input type="date" name="effective_from" class="form-control" value="{{ date('Y-m-01') }}">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Create Policy Ruleset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- JAVASCRIPT FOR SIMULATOR AJAX -->
<!-- ========================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnRunSim = document.getElementById('btnRunSimulation');
    const resultsContainer = document.getElementById('simResultsContainer');

    if (btnRunSim) {
        btnRunSim.addEventListener('click', function () {
            const userId = document.getElementById('simUserId').value;
            const year = document.getElementById('simYear').value;
            const month = document.getElementById('simMonth').value;

            btnRunSim.disabled = true;
            btnRunSim.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Running Simulation...';

            fetch("{{ route('payroll.policies.simulate') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    user_id: userId,
                    year: year,
                    month: month,
                    working_days: 22
                })
            })
            .then(res => res.json())
            .then(res => {
                btnRunSim.disabled = false;
                btnRunSim.innerHTML = '<i class="bx bx-play me-1"></i>Run Policy Impact Test';

                if (res.success && res.data) {
                    const d = res.data;
                    document.getElementById('currBasic').textContent = '₹' + d.current.basic.toLocaleString('en-IN', {minimumFractionDigits: 2});
                    document.getElementById('currHra').textContent = '₹' + d.current.hra.toLocaleString('en-IN', {minimumFractionDigits: 2});
                    document.getElementById('currGross').textContent = '₹' + d.current.gross.toLocaleString('en-IN', {minimumFractionDigits: 2});
                    document.getElementById('currDeductions').textContent = '₹' + d.current.deductions.toLocaleString('en-IN', {minimumFractionDigits: 2});
                    document.getElementById('currNet').textContent = '₹' + d.current.net_salary.toLocaleString('en-IN', {minimumFractionDigits: 2});

                    document.getElementById('simBasic').textContent = '₹' + d.simulated.basic.toLocaleString('en-IN', {minimumFractionDigits: 2});
                    document.getElementById('simHra').textContent = '₹' + d.simulated.hra.toLocaleString('en-IN', {minimumFractionDigits: 2});
                    document.getElementById('simGross').textContent = '₹' + d.simulated.gross.toLocaleString('en-IN', {minimumFractionDigits: 2});
                    document.getElementById('simDeductions').textContent = '₹' + d.simulated.deductions.toLocaleString('en-IN', {minimumFractionDigits: 2});
                    document.getElementById('simNet').textContent = '₹' + d.simulated.net_salary.toLocaleString('en-IN', {minimumFractionDigits: 2});

                    const banner = document.getElementById('simDifferenceBanner');
                    const diffText = document.getElementById('simDiffText');
                    
                    if (d.is_positive) {
                        banner.className = 'p-3 rounded-3 border bg-success-subtle border-success text-success text-center';
                        diffText.textContent = 'Net Salary Impact: +' + '₹' + Math.abs(d.difference).toLocaleString('en-IN', {minimumFractionDigits: 2}) + ' (Increase)';
                    } else {
                        banner.className = 'p-3 rounded-3 border bg-warning-subtle border-warning text-warning-emphasis text-center';
                        diffText.textContent = 'Net Salary Impact: -' + '₹' + Math.abs(d.difference).toLocaleString('en-IN', {minimumFractionDigits: 2}) + ' (Decrease)';
                    }

                    resultsContainer.classList.remove('d-none');
                } else {
                    alert('Failed to execute simulation. Please check employee configuration.');
                }
            })
            .catch(err => {
                btnRunSim.disabled = false;
                btnRunSim.innerHTML = '<i class="bx bx-play me-1"></i>Run Policy Impact Test';
                alert('An error occurred while running the simulation.');
            });
        });
    }
});
</script>

@endsection
