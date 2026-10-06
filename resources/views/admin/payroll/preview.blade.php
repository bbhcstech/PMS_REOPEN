@extends('admin.layout.app')

@section('title', 'Payroll Preview — ' . ($snap['employee_name'] ?? 'Employee'))

@section('content')
@include('admin.payroll.partials.styles')

<div class="payroll-container">
    @include('admin.payroll.partials.tabs')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="pr-card">
        <div class="pr-card-head">
            <h2>📋 Payroll Preview — {{ $snap['employee_name'] ?? 'Employee' }} ({{ $snap['employee_id'] ?? '-' }})</h2>
            <div class="spacer"></div>
            <span class="pr-pill {{ strtolower($history->payroll_status ?? 'calculated') }}">
                {{ ucfirst($history->payroll_status ?? 'Calculated') }}
            </span>
            <a href="{{ route('payroll.processing', ['year' => date('Y', strtotime($history->period_start)), 'month' => date('n', strtotime($history->period_start))]) }}" class="pr-btn pr-btn-sm">
                ← Back to Run
            </a>
        </div>

        <div class="pr-card-body">
            <!-- 2-Column Preview Grid Matching ui.html -->
            <div class="preview-grid mb-3">
                <!-- Employee Box -->
                <div class="info-box">
                    <h4>👤 Employee Details</h4>
                    <div class="info-row"><span class="k">Name</span><span class="v">{{ $snap['employee_name'] ?? '-' }}</span></div>
                    <div class="info-row"><span class="k">Emp ID</span><span class="v">{{ $snap['employee_id'] ?? '-' }}</span></div>
                    <div class="info-row"><span class="k">Designation</span><span class="v">{{ $snap['designation'] ?? '-' }}</span></div>
                    <div class="info-row"><span class="k">Department</span><span class="v">{{ $snap['department'] ?? '-' }}</span></div>
                    <div class="info-row"><span class="k">Branch</span><span class="v">{{ $snap['branch'] ?? 'HQ' }}</span></div>
                    <div class="info-row"><span class="k">Grade</span><span class="v">{{ $snap['grade'] ?? 'Standard' }}</span></div>
                    <div class="info-row"><span class="k">Joining Date</span><span class="v">{{ $snap['joining_date'] ?? '-' }}</span></div>
                </div>

                <!-- Attendance Box -->
                <div class="info-box">
                    <h4>📅 Attendance — {{ Carbon\Carbon::parse($history->period_start)->format('F Y') }}</h4>
                    <div class="info-row"><span class="k">Working Days</span><span class="v">{{ $snap['working_days'] ?? 22 }}</span></div>
                    <div class="info-row"><span class="k">Present</span><span class="v">{{ $snap['present'] ?? 22 }}</span></div>
                    <div class="info-row"><span class="k">Paid Leave</span><span class="v">{{ $snap['paid_leave'] ?? 0 }}</span></div>
                    <div class="info-row"><span class="k">Unpaid Leave</span><span class="v text-danger">{{ $snap['unpaid_leave'] ?? 0 }}</span></div>
                    <div class="info-row"><span class="k">Absent</span><span class="v text-danger">{{ $snap['absent'] ?? 0 }}</span></div>
                    <div class="info-row"><span class="k">Half Day</span><span class="v">{{ $snap['half_day'] ?? 0 }}</span></div>
                    <div class="info-row"><span class="k">WFH</span><span class="v">{{ $snap['wfh'] ?? 0 }}</span></div>
                    <div class="info-row"><span class="k">Working Hours</span><span class="v">{{ $snap['working_hours'] ?? 0 }} hrs</span></div>
                    <div class="info-row"><span class="k">Overtime</span><span class="v">{{ $snap['overtime_hours'] ?? 0 }} hrs</span></div>
                </div>

                <!-- Earnings Box -->
                <div class="info-box">
                    <h4>💵 Base Earnings</h4>
                    <div class="info-row"><span class="k">Basic Salary</span><span class="v">₹{{ number_format($snap['basic'] ?? 0, 2) }}</span></div>
                    <div class="info-row"><span class="k">Current Basic (Pro-rata)</span><span class="v fw-bold">₹{{ number_format($snap['current_basic'] ?? 0, 2) }}</span></div>
                    <div class="info-row"><span class="k">HRA ({{ $snap['hra_type'] === 'percentage' ? ($snap['hra_value'].'%') : 'Fixed' }})</span><span class="v">₹{{ number_format($snap['current_hra'] ?? $snap['hra'] ?? 0, 2) }}</span></div>
                    <div class="info-row"><span class="k">Special Allowance ({{ $snap['special_type'] === 'percentage' ? ($snap['special_value'].'%') : 'Fixed' }})</span><span class="v">₹{{ number_format($snap['current_special'] ?? $snap['special'] ?? 0, 2) }}</span></div>
                    <div class="info-row"><span class="k">Other Allowances</span><span class="v">₹{{ number_format($snap['other_allowances'] ?? 0, 2) }}</span></div>
                    <div class="info-row" style="border-top: 1.5px solid var(--pr-border); margin-top: 8px; padding-top: 8px;">
                        <span class="k fw-bold" style="color: var(--pr-text);">Gross Salary</span>
                        <span class="v fs-6 fw-bold" style="color: var(--pr-primary);">₹{{ number_format($snap['gross_salary'] ?? $snap['current_gross'] ?? 0, 2) }}</span>
                    </div>
                </div>

                <!-- Deductions Box -->
                <div class="info-box">
                    <h4>📉 Employee Deductions</h4>
                    <div class="info-row"><span class="k">PF (12%)</span><span class="v">₹{{ number_format($snap['pf'] ?? 0, 2) }}</span></div>
                    <div class="info-row"><span class="k">ESI (0.75%)</span><span class="v">₹{{ number_format($snap['esi'] ?? 0, 2) }}</span></div>
                    <div class="info-row"><span class="k">Professional Tax (PT)</span><span class="v">₹{{ number_format($snap['pt'] ?? 0, 2) }}</span></div>
                    <div class="info-row"><span class="k">TDS / Income Tax</span><span class="v">₹{{ number_format($snap['tds'] ?? 0, 2) }}</span></div>
                    <div class="info-row"><span class="k">Other Deductions</span><span class="v">₹{{ number_format($snap['other_deductions'] ?? 0, 2) }}</span></div>
                    <div class="info-row" style="border-top: 1.5px solid var(--pr-border); margin-top: 8px; padding-top: 8px;">
                        <span class="k fw-bold" style="color: var(--pr-text);">Total Deductions</span>
                        <span class="v fs-6 fw-bold" style="color: var(--pr-danger);">₹{{ number_format($snap['total_deductions'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Additional Earnings Box -->
            <div class="info-box mb-3">
                <h4>🎁 Additional Earnings & Bonuses</h4>
                <div class="info-row">
                    <span class="k">Attendance Bonus</span>
                    <span class="v">₹{{ number_format($snap['attendance_bonus'] ?? 0, 2) }} <span class="text-muted fw-normal">({{ ($snap['working_hours'] ?? 0) > 180 ? '> 180 hrs' : (($snap['working_hours'] ?? 0) > 162 ? '> 162 hrs' : 'Standard') }})</span></span>
                </div>
                <div class="info-row">
                    <span class="k">Best Employee Bonus</span>
                    <span class="v">₹{{ number_format($snap['best_employee_bonus'] ?? 0, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="k">Travel Allowance (TA)</span>
                    <span class="v">₹{{ number_format($snap['ta'] ?? 0, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="k">Overtime ({{ $snap['overtime_hours'] ?? 0 }}h × ₹{{ $snap['overtime_rate'] ?? 200 }})</span>
                    <span class="v">₹{{ number_format($snap['overtime'] ?? 0, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="k">Commission</span>
                    <span class="v">₹{{ number_format($snap['commission'] ?? 0, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="k">Net Adjustments</span>
                    <span class="v">₹{{ number_format($snap['adjustment'] ?? 0, 2) }}</span>
                </div>
                <div class="info-row" style="border-top: 1.5px solid var(--pr-border); margin-top: 8px; padding-top: 8px;">
                    <span class="k fw-bold" style="color: var(--pr-text);">Total Additional Earnings</span>
                    <span class="v fs-6 fw-bold" style="color: var(--pr-success);">₹{{ number_format($snap['total_additional_earnings'] ?? 0, 2) }}</span>
                </div>
            </div>

            <!-- Summary Box Matching ui.html -->
            <div class="summary-box mb-3">
                <div class="item">
                    <div class="lbl">Net Pay (Gross − Deductions)</div>
                    <div class="val">₹{{ number_format($snap['net_pay'] ?? 0, 2) }}</div>
                </div>
                <div class="item">
                    <div class="lbl">Total In Hand (Net + Additional Earnings)</div>
                    <div class="val" style="color: var(--pr-success);">₹{{ number_format($snap['total_in_hand'] ?? 0, 2) }}</div>
                </div>
            </div>

            <!-- Employer Contributions Box (Separated from Employee Deductions) -->
            <div class="info-box mb-4">
                <h4>🏦 Employer Contributions (Statutory Cost)</h4>
                <div class="info-row"><span class="k">Employer PF (12%)</span><span class="v">₹{{ number_format($snap['employer_pf'] ?? 0, 2) }}</span></div>
                <div class="info-row"><span class="k">Employer ESI (3.25%)</span><span class="v">₹{{ number_format($snap['employer_esi'] ?? 0, 2) }}</span></div>
                <div class="info-row"><span class="k">EDLI (0.5%)</span><span class="v">₹{{ number_format($snap['edli'] ?? 0, 2) }}</span></div>
                <div class="info-row"><span class="k">Other Employer Contributions</span><span class="v">₹{{ number_format($snap['other_employer_contribution'] ?? 0, 2) }}</span></div>
                <div class="info-row" style="border-top: 1.5px solid var(--pr-border); margin-top: 8px; padding-top: 8px;">
                    <span class="k fw-bold" style="color: var(--pr-text);">Total CTC (Gross + Additional + Employer Contributions)</span>
                    <span class="v fs-6 fw-bold" style="color: var(--pr-purple);">₹{{ number_format($snap['ctc'] ?? 0, 2) }}</span>
                </div>
            </div>

            <!-- Bottom Actions -->
            <div class="d-flex justify-content-end gap-2 flex-wrap">
                @if(($history->payroll_status ?? '') !== 'finalized')
                    <button class="pr-btn" data-bs-toggle="modal" data-bs-target="#editLineModal">
                        ✏️ Edit Inputs
                    </button>
                    <form action="{{ route('payroll.review', $history->payroll_id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="pr-btn pr-btn-warn">
                            👁️ Mark Reviewed
                        </button>
                    </form>
                    <form action="{{ route('payroll.approve', $history->payroll_id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="pr-btn pr-btn-success">
                            ✅ Approve Run
                        </button>
                    </form>
                    <form action="{{ route('payroll.finalize', $history->payroll_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Finalize this payroll run?');">
                        @csrf
                        <button type="submit" class="pr-btn pr-btn-primary">
                            🔒 Finalize Run
                        </button>
                    </form>
                @else
                    <span class="pr-pill finalized fs-7">🔒 Finalized Run — Protected Record</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Inputs Modal -->
<div class="modal fade" id="editLineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--pr-surface); border: 1px solid var(--pr-border);">
            <form action="{{ route('payroll.line-input.update', $history->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" style="color: var(--pr-text);">✏️ Adjust Line Inputs — {{ $snap['employee_name'] }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Travel Allowance (TA) ₹</label>
                            <input type="number" step="0.01" name="ta" class="form-control form-control-sm" value="{{ $snap['ta'] ?? 0 }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Commission ₹</label>
                            <input type="number" step="0.01" name="commission" class="form-control form-control-sm" value="{{ $snap['commission'] ?? 0 }}" />
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Best Employee Rank</label>
                            <select name="be_rank" class="form-select form-select-sm">
                                <option value="0" {{ ($snap['be_rank'] ?? 0) == 0 ? 'selected' : '' }}>None (₹0)</option>
                                <option value="1" {{ ($snap['be_rank'] ?? 0) == 1 ? 'selected' : '' }}>Rank 1 (₹400)</option>
                                <option value="2" {{ ($snap['be_rank'] ?? 0) == 2 ? 'selected' : '' }}>Rank 2 (₹200)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Attendance Bonus ₹</label>
                            <input type="number" step="0.01" name="attendance_bonus" class="form-control form-control-sm" value="{{ $snap['attendance_bonus'] ?? 0 }}" />
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Positive Adjustment (+ ₹)</label>
                            <input type="number" step="0.01" name="positive_adjustment" class="form-control form-control-sm" value="{{ $snap['positive_adjustment'] ?? 0 }}" placeholder="Arrears..." />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Negative Adjustment (- ₹)</label>
                            <input type="number" step="0.01" name="negative_adjustment" class="form-control form-control-sm" value="{{ $snap['negative_adjustment'] ?? 0 }}" placeholder="Recovery..." />
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Overtime Hours</label>
                            <input type="number" step="0.1" name="overtime_hours" class="form-control form-control-sm" value="{{ $snap['overtime_hours'] ?? 0 }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Overtime Rate ₹/hr</label>
                            <input type="number" step="0.01" name="overtime_rate" class="form-control form-control-sm" value="{{ $snap['overtime_rate'] ?? 200 }}" />
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Reason / Remarks</label>
                        <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Adjustment justification..." />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="pr-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="pr-btn pr-btn-primary">💾 Recalculate Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
