@extends('admin.layout.app')

@section('title', 'Payroll Processing')

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
            <h2>⚙️ Payroll Processing</h2>
            <div class="spacer"></div>
            @if($payrollRun)
                <span class="pr-pill {{ strtolower($payrollRun->status) }} fs-7">
                    Status: {{ ucfirst($payrollRun->status) }}
                </span>
            @else
                <span class="pr-pill draft">Draft Preview</span>
            @endif
        </div>

        <div class="pr-card-body">
            <!-- Steps Indicator Matching ui.html -->
            @php
                $st = strtolower($payrollRun?->status ?? 'draft');
                $isStep1Done = true;
                $isStep2Done = in_array($st, ['calculated', 'reviewed', 'approved', 'finalized']);
                $isStep3Done = in_array($st, ['reviewed', 'approved', 'finalized']);
                $isStep4Done = ($st === 'finalized');
            @endphp
            <div class="steps">
                <div class="step {{ $isStep1Done ? 'done' : 'active' }}">
                    <span class="num">{{ $isStep1Done ? '✓' : '1' }}</span> 1. Select Parameters
                </div>
                <div class="step {{ $isStep2Done ? ($isStep3Done ? 'done' : 'active') : '' }}">
                    <span class="num">{{ $isStep2Done && $isStep3Done ? '✓' : '2' }}</span> 2. Preview & Calculate
                </div>
                <div class="step {{ $isStep3Done ? ($isStep4Done ? 'done' : 'active') : '' }}">
                    <span class="num">{{ $isStep3Done && $isStep4Done ? '✓' : '3' }}</span> 3. Review
                </div>
                <div class="step {{ $isStep4Done ? 'done active' : '' }}">
                    <span class="num">{{ $isStep4Done ? '✓' : '4' }}</span> 4. Approve & Finalize
                </div>
            </div>

            <!-- Step 1: Parameters Form -->
            <form method="GET" action="{{ route('payroll.processing') }}" class="mb-4">
                <div class="fw-bold small text-uppercase text-muted mb-2">Step 1: Parameters</div>
                <div class="row g-2">
                    <div class="col-md-2 col-6">
                        <label class="form-label small fw-bold">Month *</label>
                        <select name="month" class="form-select form-select-sm">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label small fw-bold">Year *</label>
                        <select name="year" class="form-select form-select-sm">
                            @for($y = date('Y') + 1; $y >= 2022; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label small fw-bold">Working Days *</label>
                        <input type="number" name="working_days" class="form-control form-control-sm" value="{{ $workingDays }}" min="1" max="31" required />
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label small fw-bold">Branch / Office</label>
                        <select name="office" class="form-select form-select-sm">
                            <option value="all">All Branches</option>
                            @foreach($officesList as $off)
                                <option value="{{ $off }}" {{ $office === $off ? 'selected' : '' }}>{{ $off }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-12">
                        <label class="form-label small fw-bold">Department</label>
                        <select name="department_id" class="form-select form-select-sm">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->dpt_name ?? $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="text-end mt-2">
                    <button type="submit" class="pr-btn">
                        🔄 Fetch Employees
                    </button>
                </div>
            </form>

            <!-- Summary KPI Strip -->
            <div class="row g-2 mb-4">
                <div class="col-md-2 col-6">
                    <div class="info-box py-2">
                        <div class="small text-muted">Employees</div>
                        <div class="fw-bold fs-6" style="color: var(--pr-text);">{{ $summary['total_employees'] }}</div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="info-box py-2">
                        <div class="small text-muted">Total Gross</div>
                        <div class="fw-bold fs-6 text-primary">₹{{ number_format($summary['total_gross'], 2) }}</div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="info-box py-2">
                        <div class="small text-muted">Total Deductions</div>
                        <div class="fw-bold fs-6 text-danger">₹{{ number_format($summary['total_deductions'], 2) }}</div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="info-box py-2">
                        <div class="small text-muted">Total In Hand</div>
                        <div class="fw-bold fs-6 text-success">₹{{ number_format($summary['total_in_hand'], 2) }}</div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="info-box py-2">
                        <div class="small text-muted">Employer Cost</div>
                        <div class="fw-bold fs-6 text-purple">₹{{ number_format($summary['total_employer_contribution'], 2) }}</div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="info-box py-2">
                        <div class="small text-muted">Total CTC</div>
                        <div class="fw-bold fs-6" style="color: var(--pr-text);">₹{{ number_format($summary['total_ctc'], 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Employees Table -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-bold small text-uppercase text-muted">
                    Step 2: Employees Found ({{ $payrollItems->count() }})
                </div>
                @if($payrollRun)
                    <a href="{{ route('payroll.export', $payrollRun->id) }}" class="pr-btn pr-btn-sm">
                        📥 Export CSV
                    </a>
                @endif
            </div>

            <div class="table-wrap">
                <table class="pr-table">
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" checked /></th>
                            <th>Employee</th>
                            <th>Designation</th>
                            <th>Attendance</th>
                            <th class="text-end">Basic Salary</th>
                            <th class="text-end">Current Basic</th>
                            <th class="text-end">Net Pay</th>
                            <th class="text-end">Total In Hand</th>
                            <th class="text-end">CTC</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrollItems as $item)
                        @php
                            $s = is_array($item->snapshot) ? $item->snapshot : (json_decode($item->snapshot ?? '{}', true) ?: []);
                            $initials = strtoupper(substr($s['employee_name'] ?? 'U', 0, 2));
                        @endphp
                        <tr>
                            <td><input type="checkbox" checked /></td>
                            <td>
                                <div class="cell-employee">
                                    <div class="avatar-badge">{{ $initials }}</div>
                                    <div>
                                        <div class="name">{{ $s['employee_name'] ?? '-' }}</div>
                                        <div class="meta">{{ $s['employee_id'] ?? '-' }} · {{ $s['department'] ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $s['designation'] ?? '-' }} <span class="badge bg-label-secondary small">{{ $s['grade'] ?? '-' }}</span></td>
                            <td>
                                <b>{{ $s['present'] ?? $s['presents'] ?? $workingDays }}/{{ $workingDays }}</b>
                                @if(!empty($s['total_absent']) && (float)$s['total_absent'] > 0)
                                    <div class="small text-danger">Absent: {{ $s['total_absent'] }}d</div>
                                @endif
                                @if(!empty($s['working_hours']))
                                    <div class="small text-muted">{{ $s['working_hours'] }} hrs</div>
                                @endif
                            </td>
                            <td class="num">₹{{ number_format($s['basic'] ?? 0, 2) }}</td>
                            <td class="num">₹{{ number_format($s['current_basic'] ?? $s['ac_basic'] ?? 0, 2) }}</td>
                            <td class="num">₹{{ number_format($s['net_pay'] ?? $s['net_salary'] ?? 0, 2) }}</td>
                            <td class="num text-success fw-bold">₹{{ number_format($s['total_in_hand'] ?? 0, 2) }}</td>
                            <td class="num text-purple">₹{{ number_format($s['ctc'] ?? 0, 2) }}</td>
                            <td>
                                <span class="pr-pill {{ strtolower($item->payroll_status ?? 'calculated') }}">
                                    {{ ucfirst($item->payroll_status ?? 'Calculated') }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($item->id)
                                    <a href="{{ route('payroll.preview', $item->id) }}" class="pr-btn pr-btn-sm" title="Preview Calculation">
                                        📋 Preview
                                    </a>
                                    @if($st !== 'finalized')
                                        <button class="pr-btn pr-btn-sm" data-bs-toggle="modal" data-bs-target="#editLineModal{{ $item->id }}" title="Edit Inputs">
                                            ✏️ Edit
                                        </button>
                                    @endif
                                @else
                                    <span class="text-muted small">Run to view</span>
                                @endif
                            </td>
                        </tr>

                        @if($item->id && $st !== 'finalized')
                        <!-- Edit Inputs Modal -->
                        <div class="modal fade" id="editLineModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content" style="background: var(--pr-surface); border: 1px solid var(--pr-border);">
                                    <form action="{{ route('payroll.line-input.update', $item->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold" style="color: var(--pr-text);">✏️ Adjust Line Inputs — {{ $s['employee_name'] }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-2 mb-2">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">Travel Allowance (TA) ₹</label>
                                                    <input type="number" step="0.01" name="ta" class="form-control form-control-sm" value="{{ $s['ta'] ?? 0 }}" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">Commission ₹</label>
                                                    <input type="number" step="0.01" name="commission" class="form-control form-control-sm" value="{{ $s['commission'] ?? 0 }}" />
                                                </div>
                                            </div>
                                            <div class="row g-2 mb-2">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">Best Employee Rank</label>
                                                    <select name="be_rank" class="form-select form-select-sm">
                                                        <option value="0" {{ ($s['be_rank'] ?? 0) == 0 ? 'selected' : '' }}>None (₹0)</option>
                                                        <option value="1" {{ ($s['be_rank'] ?? 0) == 1 ? 'selected' : '' }}>Rank 1 (₹400)</option>
                                                        <option value="2" {{ ($s['be_rank'] ?? 0) == 2 ? 'selected' : '' }}>Rank 2 (₹200)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">Attendance Bonus ₹</label>
                                                    <input type="number" step="0.01" name="attendance_bonus" class="form-control form-control-sm" value="{{ $s['attendance_bonus'] ?? 0 }}" />
                                                </div>
                                            </div>
                                            <div class="row g-2 mb-2">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">Positive Adjustment (+ ₹)</label>
                                                    <input type="number" step="0.01" name="positive_adjustment" class="form-control form-control-sm" value="{{ $s['positive_adjustment'] ?? 0 }}" placeholder="Arrears..." />
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">Negative Adjustment (- ₹)</label>
                                                    <input type="number" step="0.01" name="negative_adjustment" class="form-control form-control-sm" value="{{ $s['negative_adjustment'] ?? 0 }}" placeholder="Recovery..." />
                                                </div>
                                            </div>
                                            <div class="row g-2 mb-2">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">Overtime Hours</label>
                                                    <input type="number" step="0.1" name="overtime_hours" class="form-control form-control-sm" value="{{ $s['overtime_hours'] ?? 0 }}" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">Overtime Rate ₹/hr</label>
                                                    <input type="number" step="0.01" name="overtime_rate" class="form-control form-control-sm" value="{{ $s['overtime_rate'] ?? 200 }}" />
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
                        @endif
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                No eligible employees found for the selected parameters.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Workflow Status Actions Matching Flow: Calculate -> Review -> Approve -> Finalize -->
            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
                <div>
                    <form action="{{ route('payroll.calculate') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="year" value="{{ $year }}" />
                        <input type="hidden" name="month" value="{{ $month }}" />
                        <input type="hidden" name="working_days" value="{{ $workingDays }}" />
                        <input type="hidden" name="office" value="{{ $office }}" />
                        <input type="hidden" name="department_id" value="{{ $departmentId }}" />
                        <button type="submit" class="pr-btn pr-btn-primary">
                            🧮 {{ $payrollRun ? 'Recalculate Run' : 'Calculate & Save Payroll Run' }}
                        </button>
                    </form>
                </div>

                @if($payrollRun)
                <div class="d-flex gap-2">
                    @if($st === 'calculated')
                        <form action="{{ route('payroll.review', $payrollRun->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="pr-btn pr-btn-warn">
                                👁️ Mark Reviewed
                            </button>
                        </form>
                    @endif

                    @if(in_array($st, ['calculated', 'reviewed']))
                        <form action="{{ route('payroll.approve', $payrollRun->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="pr-btn pr-btn-success">
                                ✅ Approve Payroll
                            </button>
                        </form>
                    @endif

                    @if(in_array($st, ['calculated', 'reviewed', 'approved']))
                        <form action="{{ route('payroll.finalize', $payrollRun->id) }}" method="POST" onsubmit="return confirm('Finalizing will permanently lock this payroll and generate immutable snapshots and payslips. Continue?');">
                            @csrf
                            <button type="submit" class="pr-btn pr-btn-primary">
                                🔒 Finalize Payroll
                            </button>
                        </form>
                    @endif

                    @if($st === 'finalized')
                        <span class="pr-pill finalized fs-7">
                            🔒 Finalized on {{ Carbon\Carbon::parse($payrollRun->finalized_at ?? now())->format('d M Y H:i') }}
                        </span>
                        <a href="{{ route('payroll.payslips.index', ['month' => $month, 'year' => $year]) }}" class="pr-btn pr-btn-success">
                            🧾 View Generated Payslips
                        </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
