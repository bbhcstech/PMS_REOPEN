@extends('admin.layout.app')

@section('title', 'Payroll Processing')

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

    <!-- Header & Breadcrumbs -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bx bx-calculator text-primary me-2"></i>Payroll Processing</h4>
            <p class="text-muted mb-0">Calculate, review, and process monthly employee payroll with real-time attendance and leave integration.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-left-arrow-alt me-1"></i>Payroll Dashboard
            </a>
            <a href="{{ route('payroll.reports.index') }}" class="btn btn-outline-primary">
                <i class="bx bx-history me-1"></i>Payroll History
            </a>
        </div>
    </div>

    <!-- Duplicate / Existing Payroll Protection Banner -->
    @if($existingPayroll)
        <div class="card border-warning mb-4 shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3 bg-warning-subtle rounded-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-warning text-white rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bx bx-info-circle fs-3"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-warning-emphasis fw-bold">
                            Payroll Record Found for {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
                        </h6>
                        <span class="badge bg-{{ $existingPayroll->status === 'finalized' ? 'success' : 'primary' }} me-2">
                            Status: {{ ucfirst(str_replace('_', ' ', $existingPayroll->status)) }}
                        </span>
                        <small class="text-muted">
                            Run #{{ $existingPayroll->id }} | Generated: {{ $existingPayroll->created_at?->format('d M Y, h:i A') }}
                        </small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    @if($existingPayroll->status !== 'finalized')
                        <button type="button" class="btn btn-warning shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#reviewPayrollModal">
                            <i class="bx bx-check-shield me-1"></i>Review & Finalize
                        </button>
                        <form action="{{ route('payroll.recalculate', $existingPayroll->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning" onclick="return confirm('Recalculate payroll data from current database records?')">
                                <i class="bx bx-refresh me-1"></i>Recalculate
                            </button>
                        </form>
                    @else
                        <span class="badge bg-success fs-6 align-self-center px-3 py-2"><i class="bx bx-lock me-1"></i>Finalized</span>
                        @if($payrollRun)
                            <a href="{{ route('payroll.export', $payrollRun->id) }}" class="btn btn-outline-success">
                                <i class="bx bx-download me-1"></i>Export CSV
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- 1. Payroll Configuration Card -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-slider-alt text-primary me-2"></i>Payroll Period & Filter Parameters</h6>
        </div>
        <div class="card-body pt-4">
            <form action="{{ route('payroll.calculate') }}" method="POST" id="payrollConfigForm">
                @csrf
                <div class="row g-3 align-items-end">
                    
                    <!-- Year -->
                    <div class="col-md-2 col-6">
                        <label for="selectYear" class="form-label fw-semibold text-dark">Year</label>
                        <select name="year" id="selectYear" class="form-select border-secondary-subtle">
                            @foreach(range(2022, 2030) as $y)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Month -->
                    <div class="col-md-2 col-6">
                        <label for="selectMonth" class="form-label fw-semibold text-dark">Month</label>
                        <select name="month" id="selectMonth" class="form-select border-secondary-subtle">
                            @foreach([1=>'January', 2=>'February', 3=>'March', 4=>'April', 5=>'May', 6=>'June', 7=>'July', 8=>'August', 9=>'September', 10=>'October', 11=>'November', 12=>'December'] as $mNum => $mName)
                                <option value="{{ $mNum }}" {{ $month == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Working Days -->
                    <div class="col-md-2 col-6">
                        <label for="workingDaysInput" class="form-label fw-semibold text-dark">Working Days</label>
                        <input type="number" name="working_days" id="workingDaysInput" class="form-control border-secondary-subtle" value="{{ $workingDays }}" min="1" max="31" required>
                    </div>

                    <!-- Office -->
                    <div class="col-md-2 col-6">
                        <label for="selectOffice" class="form-label fw-semibold text-dark">Office</label>
                        <select name="office" id="selectOffice" class="form-select border-secondary-subtle">
                            <option value="all" {{ $office == 'all' ? 'selected' : '' }}>All Offices</option>
                            @foreach($officesList as $off)
                                <option value="{{ $off }}" {{ $office == $off ? 'selected' : '' }}>{{ $off }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Employee Type -->
                    <div class="col-md-2 col-6">
                        <label for="selectEmpType" class="form-label fw-semibold text-dark">Employee Type</label>
                        <select name="employee_type" id="selectEmpType" class="form-select border-secondary-subtle">
                            <option value="all" {{ $employeeType == 'all' ? 'selected' : '' }}>All Types</option>
                            @foreach($empTypesList as $type)
                                <option value="{{ $type }}" {{ (strtolower($employeeType) == strtolower($type)) ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-md-2 col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                            <i class="bx bx-cog me-1"></i>Generate Payroll
                        </button>
                    </div>
                </div>

                <!-- Secondary Actions Bar -->
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <div class="text-muted small">
                        <i class="bx bx-info-circle me-1"></i>Period: <strong>{{ date('F 1, Y', mktime(0,0,0,$month,1,$year)) }} - {{ date('F t, Y', mktime(0,0,0,$month,1,$year)) }}</strong> ({{ $workingDays }} Days)
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('payroll.processing') }}" class="btn btn-sm btn-light border text-secondary">
                            <i class="bx bx-reset me-1"></i>Reset Filters
                        </a>
                        @if($payrollRun)
                            <a href="{{ route('payroll.export', $payrollRun->id) }}" class="btn btn-sm btn-outline-success">
                                <i class="bx bx-file me-1"></i>Export CSV
                            </a>
                            @if($payrollRun->status !== 'finalized')
                                <button type="button" class="btn btn-sm btn-warning fw-bold" data-bs-toggle="modal" data-bs-target="#reviewPayrollModal">
                                    <i class="bx bx-check-double me-1"></i>Review & Approve
                                </button>
                            @endif
                            @if(in_array($payrollRun->status, ['finalized', 'payslip_generated']))
                                <form action="{{ route('payroll.generate-payslips', $payrollRun->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-info text-white fw-bold">
                                        <i class="bx bx-receipt me-1"></i>Generate All Payslips
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Dynamic KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6 col-6">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">TOTAL EMPLOYEES</span>
                        <span class="badge bg-primary-subtle text-primary rounded-circle p-2"><i class="bx bx-group fs-5"></i></span>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $summary['total_employees'] }}</h3>
                    <small class="text-muted">{{ $office == 'all' ? 'All Offices' : $office }}</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="card border-0 shadow-sm h-100 border-start border-secondary border-4">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">GROSS PAYROLL</span>
                        <span class="badge bg-secondary-subtle text-secondary rounded-circle p-2"><i class="bx bx-wallet fs-5"></i></span>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">₹{{ number_format($summary['gross_payroll'], 2) }}</h3>
                    <small class="text-muted">Standard Salary Quota</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">ACTUAL PAYROLL</span>
                        <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="bx bx-money fs-5"></i></span>
                    </div>
                    <h3 class="fw-bold mb-0 text-success">₹{{ number_format($summary['actual_payroll'], 2) }}</h3>
                    <small class="text-muted">Pro-rata Payable Amount</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">PAYSLIPS / STATUS</span>
                        <span class="badge bg-info-subtle text-info rounded-circle p-2"><i class="bx bx-receipt fs-5"></i></span>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $summary['payslips_count'] }}</h3>
                    <small class="text-muted">
                        Status: <span class="badge bg-label-{{ $payrollRun && $payrollRun->status === 'finalized' ? 'success' : 'warning' }}">{{ $payrollRun ? ucfirst(str_replace('_', ' ', $payrollRun->status)) : 'Draft Preview' }}</span>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Enterprise Payroll Table Card -->
    <div class="card border-0 shadow-sm mb-4">
        
        <!-- Card Sub-Header & Controls Bar -->
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                
                <!-- Left: Title & Show Entries -->
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-table text-primary me-2"></i>Payroll Calculation Sheet ({{ date('F Y', mktime(0,0,0,$month,1,$year)) }})</h6>
                    <div class="d-flex align-items-center gap-2 border-start ps-3">
                        <label for="showEntriesSelect" class="small text-muted mb-0 fw-semibold">Show</label>
                        <select id="showEntriesSelect" class="form-select form-select-sm border-secondary-subtle text-dark fw-semibold" style="width: 80px;">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="all">All</option>
                        </select>
                        <label for="showEntriesSelect" class="small text-muted mb-0 fw-semibold">entries</label>
                    </div>
                </div>

                <!-- Center/Right: Bulk Actions Toolbar & Search -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    
                    <!-- Bulk Selection Actions Bar -->
                    <div id="bulkActionsToolbar" class="d-none align-items-center gap-2 bg-primary-subtle px-3 py-1 rounded-3 border border-primary-subtle">
                        <span id="selectedCountBadge" class="badge bg-primary fw-bold fs-7">0 selected</span>
                        @if($payrollRun)
                            <button type="button" id="btnExportSelected" class="btn btn-sm btn-success fw-semibold">
                                <i class="bx bx-download me-1"></i>Export Selected
                            </button>
                        @endif
                    </div>

                    <!-- Live Table Search -->
                    <div class="input-group input-group-sm" style="width: 220px;">
                        <span class="input-group-text bg-light border-secondary-subtle"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" id="payrollSearchInput" class="form-control border-secondary-subtle" placeholder="Search employee, ID...">
                    </div>

                    <div class="d-none d-sm-flex gap-2 ms-2">
                        <span class="badge bg-light text-dark border align-self-center">Working Days: {{ $workingDays }}</span>
                        <span class="badge bg-light text-dark border align-self-center">Total: {{ $payrollItems->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Responsive Container -->
        <div class="table-responsive" style="max-height: 650px; overflow-y: auto;">
            <table id="payrollCalculationTable" class="table table-hover table-bordered align-middle text-nowrap mb-0" style="font-size: 0.85rem;">
                <!-- Grouped Columns Header -->
                <thead class="table-dark sticky-top" style="z-index: 10;">
                    <tr class="text-center align-middle">
                        <!-- Checkbox Select All Column -->
                        <th rowspan="2" class="px-2 text-center align-middle" style="width: 40px; background-color: #2b3445;">
                            <input type="checkbox" id="selectAllCheckbox" class="form-check-input cursor-pointer" title="Select All Rows">
                        </th>
                        <th rowspan="2" class="px-2" style="width: 40px;">SR.</th>
                        <th colspan="2" class="bg-primary text-white border-end">EMPLOYEE</th>
                        <th colspan="4" class="bg-secondary text-white border-end">LEAVE</th>
                        <th colspan="4" class="bg-info text-white border-end">ATTENDANCE</th>
                        <th colspan="4" class="bg-dark text-white border-end">SALARY STRUCTURE</th>
                        <th colspan="4" class="bg-success text-white border-end">ACTUAL PAYABLE SALARY</th>
                        <th rowspan="2" class="align-middle">STATUS</th>
                        <th rowspan="2" class="align-middle">ACTION</th>
                    </tr>
                    <tr class="text-center small">
                        <!-- Employee -->
                        <th class="bg-primary-subtle text-primary-emphasis">Employee ID</th>
                        <th class="bg-primary-subtle text-primary-emphasis border-end">Employee Name</th>
                        
                        <!-- Leave -->
                        <th class="bg-secondary-subtle text-secondary-emphasis">Init. Bal</th>
                        <th class="bg-secondary-subtle text-secondary-emphasis">Full</th>
                        <th class="bg-secondary-subtle text-secondary-emphasis">Half</th>
                        <th class="bg-secondary-subtle text-secondary-emphasis border-end">Curr. Bal</th>

                        <!-- Attendance -->
                        <th class="bg-info-subtle text-info-emphasis">Full Abs</th>
                        <th class="bg-info-subtle text-info-emphasis">Half Abs</th>
                        <th class="bg-info-subtle text-info-emphasis">Tot Abs</th>
                        <th class="bg-info-subtle text-info-emphasis border-end">Presents</th>

                        <!-- Salary Structure -->
                        <th class="bg-dark-subtle text-dark-emphasis">Basic</th>
                        <th class="bg-dark-subtle text-dark-emphasis">HRA</th>
                        <th class="bg-dark-subtle text-dark-emphasis">Special</th>
                        <th class="bg-dark-subtle text-dark-emphasis border-end">Gross</th>

                        <!-- Actual Payable Salary -->
                        <th class="bg-success-subtle text-success-emphasis">Ac BASIC</th>
                        <th class="bg-success-subtle text-success-emphasis">Ac HRA</th>
                        <th class="bg-success-subtle text-success-emphasis">Ac SPECIAL</th>
                        <th class="bg-success-subtle text-success-emphasis border-end">Ac GROSS</th>
                    </tr>
                </thead>
                <tbody id="payrollTableBody">
                    @forelse($payrollItems as $item)
                        @php
                            $s = is_array($item->snapshot) ? $item->snapshot : (json_decode($item->snapshot ?? '{}', true) ?: []);
                            $hasStructure = $s['has_salary_structure'] ?? true;
                            $userId = $item->user_id ?? ($s['user_id'] ?? 0);
                        @endphp
                        <tr class="payroll-row" data-user-id="{{ $userId }}" data-search-text="{{ strtolower(($s['employee_name'] ?? '').' '.($s['employee_id'] ?? '')) }}">
                            <!-- Checkbox Row Selection -->
                            <td class="text-center align-middle">
                                <input type="checkbox" class="form-check-input row-checkbox cursor-pointer" value="{{ $userId }}">
                            </td>

                            <!-- Sr No -->
                            <td class="text-center fw-semibold text-muted sr-number">{{ $s['sr_no'] ?? $loop->iteration }}</td>

                            <!-- Employee ID & Name -->
                            <td>
                                <span class="badge bg-light text-primary border font-monospace">{{ $s['employee_id'] ?? 'EMP-'.$userId }}</span>
                            </td>
                            <td class="fw-bold text-dark border-end">
                                {{ $s['employee_name'] ?? 'User #'.$userId }}
                                @if(!$hasStructure)
                                    <br><small class="badge bg-warning text-dark font-normal"><i class="bx bx-error-circle me-1"></i>No Salary Configured</small>
                                @endif
                            </td>

                            <!-- Leave -->
                            <td class="text-center">{{ number_format($s['initial_leave_balance'] ?? 0, 1) }}</td>
                            <td class="text-center text-danger">{{ number_format($s['full_leave'] ?? 0, 1) }}</td>
                            <td class="text-center text-warning">{{ number_format($s['half_leave'] ?? 0, 1) }}</td>
                            <td class="text-center font-monospace fw-semibold border-end text-primary">{{ number_format($s['current_leave_balance'] ?? 0, 1) }}</td>

                            <!-- Attendance -->
                            <td class="text-center text-danger">{{ number_format($s['full_absent'] ?? 0, 1) }}</td>
                            <td class="text-center text-warning">{{ number_format($s['half_absent'] ?? 0, 1) }}</td>
                            <td class="text-center fw-bold text-danger">{{ number_format($s['total_absent'] ?? 0, 1) }}</td>
                            <td class="text-center font-monospace fw-bold text-success border-end">{{ number_format($s['presents'] ?? 0, 1) }}</td>

                            <!-- Salary Structure -->
                            <td class="text-end">₹{{ number_format($s['basic'] ?? 0, 2) }}</td>
                            <td class="text-end">₹{{ number_format($s['hra'] ?? 0, 2) }}</td>
                            <td class="text-end">₹{{ number_format($s['special'] ?? 0, 2) }}</td>
                            <td class="text-end fw-bold border-end">₹{{ number_format($s['gross'] ?? 0, 2) }}</td>

                            <!-- Actual Payable Salary -->
                            <td class="text-end text-success">₹{{ number_format($s['ac_basic'] ?? 0, 2) }}</td>
                            <td class="text-end text-success">₹{{ number_format($s['ac_hra'] ?? 0, 2) }}</td>
                            <td class="text-end text-success">₹{{ number_format($s['ac_special'] ?? 0, 2) }}</td>
                            <td class="text-end font-monospace fw-bold text-success fs-6 border-end">₹{{ number_format($s['ac_gross'] ?? 0, 2) }}</td>

                            <!-- Status -->
                            <td class="text-center">
                                @php
                                    $st = strtolower($item->payroll_status ?? 'calculated');
                                    $badgeClass = match($st) {
                                        'finalized', 'approved' => 'bg-success',
                                        'calculated' => 'bg-primary',
                                        'payslip_generated' => 'bg-info',
                                        'sent' => 'bg-purple text-white',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ ucfirst($item->payroll_status ?? 'Calculated') }}</span>
                            </td>

                            <!-- Action -->
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-light border btn-icon" title="View Details" data-bs-toggle="modal" data-bs-target="#empDetailModal{{ $loop->index }}">
                                        <i class="bx bx-show text-primary"></i>
                                    </button>
                                    @if($payrollRun && in_array($payrollRun->status, ['finalized', 'payslip_generated']))
                                        @php
                                            $payslipObj = \App\Models\Payslip::where('payroll_id', $payrollRun->id)->where('user_id', $userId)->first();
                                        @endphp
                                        @if($payslipObj)
                                            <form action="{{ route('payroll.payslips.send', $payslipObj->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-light border btn-icon" title="Send Payslip Email">
                                                    <i class="bx bx-paper-plane text-success"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="22" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bx bx-calculator fs-1 text-muted mb-2"></i>
                                    <h6 class="text-muted">No payroll calculation records found for the selected period.</h6>
                                    <p class="small text-muted mb-3">Select parameters above and click <strong>Generate Payroll</strong> to compute employee salaries.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Card Footer: Entries Info & Pagination -->
        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <span id="entriesPaginationInfo" class="text-muted small fw-semibold">
                    Showing 1 to {{ min(10, $payrollItems->count()) }} of {{ $payrollItems->count() }} entries
                </span>
            </div>
            <div>
                <ul id="paginationNav" class="pagination pagination-sm mb-0">
                    <!-- Dynamic Pagination Nav buttons populated by JS -->
                </ul>
            </div>
        </div>

    </div>
</div>

<!-- ========================================================= -->
<!-- MODALS OUTSIDE TABLE CONTAINER (PREVENTS HOVER SHRINKING) -->
<!-- ========================================================= -->

@foreach($payrollItems as $item)
    @php
        $s = is_array($item->snapshot) ? $item->snapshot : (json_decode($item->snapshot ?? '{}', true) ?: []);
        $userId = $item->user_id ?? ($s['user_id'] ?? 0);
    @endphp
    <div class="modal fade" id="empDetailModal{{ $loop->index }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                
                <!-- Modal Header -->
                <div class="modal-header bg-primary text-white p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-md bg-white text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4 shadow-sm" style="width: 48px; height: 48px;">
                            <i class="bx bx-user fs-3"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-1">{{ $s['employee_name'] ?? 'Employee Details' }}</h5>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-white text-primary fw-bold font-monospace px-2 py-1">{{ $s['employee_id'] ?? 'EMP-'.$userId }}</span>
                                <span class="badge bg-primary-subtle text-white border border-white-50">{{ $s['designation'] ?? 'Staff' }}</span>
                                <span class="badge bg-primary-subtle text-white border border-white-50">{{ $s['department'] ?? 'General' }}</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4 bg-light-subtle" style="color: #2b3445;">
                    
                    <!-- 4 Info Cards Header -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-white rounded-3 border shadow-sm h-100">
                                <small class="text-muted d-block mb-1">Office Location</small>
                                <span class="fw-bold text-dark"><i class="bx bx-building me-1 text-primary"></i>{{ $s['office'] ?? 'Main Office' }}</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-white rounded-3 border shadow-sm h-100">
                                <small class="text-muted d-block mb-1">Employment Type</small>
                                <span class="fw-bold text-dark"><i class="bx bx-briefcase me-1 text-info"></i>{{ $s['employment_type'] ?? 'Full Time' }}</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-white rounded-3 border shadow-sm h-100">
                                <small class="text-muted d-block mb-1">Working Days</small>
                                <span class="fw-bold text-dark"><i class="bx bx-calendar me-1 text-warning"></i>{{ $workingDays }} Days</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-white rounded-3 border shadow-sm h-100">
                                <small class="text-muted d-block mb-1">Payable Days</small>
                                <span class="fw-bold text-success"><i class="bx bx-check-circle me-1"></i>{{ $s['payable_days'] ?? 0 }} Days</span>
                            </div>
                        </div>
                    </div>

                    <!-- Calculation Breakdown Card & Table -->
                    <div class="card border shadow-sm mb-0">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-dark"><i class="bx bx-calculator text-primary me-2"></i>Detailed Calculation Breakdown</h6>
                            <span class="badge bg-light text-dark border">{{ date('F Y', mktime(0,0,0,$month,1,$year)) }}</span>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped align-middle mb-0" style="color: #2b3445; font-size: 0.95rem;">
                                <tbody>
                                    <tr>
                                        <td class="fw-semibold px-4 py-3 text-secondary" style="width: 50%;">Total Working Days</td>
                                        <td class="text-end px-4 py-3 fw-bold text-dark fs-6">{{ $workingDays }} Days</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold px-4 py-3 text-secondary">Present Days</td>
                                        <td class="text-end px-4 py-3 fw-bold text-success fs-6">{{ number_format($s['presents'] ?? 0, 1) }} Days</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold px-4 py-3 text-secondary">Approved Leaves (Full / Half)</td>
                                        <td class="text-end px-4 py-3 fw-bold text-primary fs-6">{{ number_format($s['full_leave'] ?? 0, 1) }} / {{ number_format($s['half_leave'] ?? 0, 1) }} Days</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold px-4 py-3 text-secondary">Total Absents (Full / Half)</td>
                                        <td class="text-end px-4 py-3 fw-bold text-danger fs-6">{{ number_format($s['total_absent'] ?? 0, 1) }} Days</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold px-4 py-3 text-secondary">Payable Days Ratio</td>
                                        <td class="text-end px-4 py-3 fw-bold text-dark fs-6">{{ $s['payable_days'] ?? 0 }} / {{ $workingDays }} Days</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold px-4 py-3 text-secondary">Basic Salary (Ac Basic)</td>
                                        <td class="text-end px-4 py-3 fw-bold text-dark fs-6">
                                            ₹{{ number_format($s['basic'] ?? 0, 2) }} 
                                            <span class="text-success small ms-2">(Ac: ₹{{ number_format($s['ac_basic'] ?? 0, 2) }})</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold px-4 py-3 text-secondary">HRA Allowance (Ac HRA)</td>
                                        <td class="text-end px-4 py-3 fw-bold text-dark fs-6">
                                            ₹{{ number_format($s['hra'] ?? 0, 2) }} 
                                            <span class="text-success small ms-2">(Ac: ₹{{ number_format($s['ac_hra'] ?? 0, 2) }})</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold px-4 py-3 text-secondary">Special Allowance (Ac Special)</td>
                                        <td class="text-end px-4 py-3 fw-bold text-dark fs-6">
                                            ₹{{ number_format($s['special'] ?? 0, 2) }} 
                                            <span class="text-success small ms-2">(Ac: ₹{{ number_format($s['ac_special'] ?? 0, 2) }})</span>
                                        </td>
                                    </tr>
                                    <tr class="table-light border-top">
                                        <td class="fw-bold px-4 py-3 text-dark">Standard Gross Salary</td>
                                        <td class="text-end px-4 py-3 fw-bold text-dark fs-6">₹{{ number_format($s['gross'] ?? 0, 2) }}</td>
                                    </tr>
                                    <tr class="table-success border-top border-2 border-success">
                                        <td class="fw-bold px-4 py-3 text-success-emphasis fs-6"><i class="bx bx-check-shield me-1"></i>ACTUAL PAYABLE GROSS SALARY</td>
                                        <td class="text-end px-4 py-3 fw-bold text-success fs-5">₹{{ number_format($s['ac_gross'] ?? 0, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
                
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-secondary px-4 fw-semibold" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- Payroll Review & Finalize Modal -->
@if($payrollRun)
<div class="modal fade" id="reviewPayrollModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-warning-subtle text-warning-emphasis p-4">
                <h5 class="modal-title fw-bold text-warning-emphasis mb-0"><i class="bx bx-shield-quarter me-2"></i>Review Payroll before Finalization</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="color: #2b3445;">
                <div class="alert alert-warning d-flex align-items-center mb-4 border-0 shadow-sm">
                    <i class="bx bx-error-circle fs-3 me-3 text-warning"></i>
                    <div>
                        <strong class="d-block mb-1">Important Administrative Notice:</strong>
                        Review payroll details carefully before finalization. Finalized payroll will be locked and cannot be modified without explicit authorized recalculation.
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="border rounded-3 p-3 bg-light text-center">
                            <small class="text-muted d-block mb-1">Payroll Period</small>
                            <h6 class="mb-0 fw-bold text-dark">{{ date('F Y', mktime(0,0,0,$month,1,$year)) }}</h6>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="border rounded-3 p-3 bg-light text-center">
                            <small class="text-muted d-block mb-1">Office Location</small>
                            <h6 class="mb-0 fw-bold text-dark">{{ $office == 'all' ? 'All Offices' : $office }}</h6>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="border rounded-3 p-3 bg-light text-center">
                            <small class="text-muted d-block mb-1">Total Employees</small>
                            <h6 class="mb-0 fw-bold text-primary">{{ $summary['total_employees'] }}</h6>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="border rounded-3 p-3 bg-light-subtle border-success text-center">
                            <small class="text-muted d-block mb-1">Actual Payable Total</small>
                            <h6 class="mb-0 fw-bold text-success">₹{{ number_format($summary['actual_payroll'], 2) }}</h6>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 border">
                    <p class="small text-muted mb-0">
                        Are you sure you want to finalize payroll for <strong>{{ date('F Y', mktime(0,0,0,$month,1,$year)) }}</strong> for <strong>{{ $summary['total_employees'] }} employees</strong> with a total payable amount of <strong>₹{{ number_format($summary['actual_payroll'], 2) }}</strong>?
                    </p>
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" data-bs-dismiss="modal">Back to Processing</button>
                <form action="{{ route('payroll.finalize', $payrollRun->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning fw-bold px-4 shadow-sm" onclick="return confirm('Confirm finalization of payroll for {{ date('F Y', mktime(0,0,0,$month,1,$year)) }}?')">
                        <i class="bx bx-check-circle me-1"></i>Finalize Payroll
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<!-- ========================================================= -->
<!-- JAVASCRIPT: SHOW ENTRIES, SEARCH FILTER, CHECKBOX & BULK -->
<!-- ========================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const allRows = Array.from(document.querySelectorAll('#payrollTableBody tr.payroll-row'));
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const showEntriesSelect = document.getElementById('showEntriesSelect');
    const searchInput = document.getElementById('payrollSearchInput');
    const bulkToolbar = document.getElementById('bulkActionsToolbar');
    const selectedCountBadge = document.getElementById('selectedCountBadge');
    const btnExportSelected = document.getElementById('btnExportSelected');
    const entriesInfo = document.getElementById('entriesPaginationInfo');
    const paginationNav = document.getElementById('paginationNav');

    let currentPage = 1;
    let pageSize = parseInt(showEntriesSelect.value) || 10;
    let filteredRows = [...allRows];

    // 1. Filter rows by Search & Update Pagination
    function updateTable() {
        const query = (searchInput.value || '').trim().toLowerCase();

        filteredRows = allRows.filter(row => {
            const text = row.getAttribute('data-search-text') || '';
            return !query || text.includes(query);
        });

        const totalFiltered = filteredRows.length;
        pageSize = showEntriesSelect.value === 'all' ? totalFiltered : parseInt(showEntriesSelect.value);
        if (pageSize <= 0) pageSize = totalFiltered || 1;

        const totalPages = Math.ceil(totalFiltered / pageSize) || 1;
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIndex = (currentPage - 1) * pageSize;
        const endIndex = showEntriesSelect.value === 'all' ? totalFiltered : Math.min(startIndex + pageSize, totalFiltered);

        // Hide all rows, then show only slice for current page
        allRows.forEach(row => row.style.display = 'none');
        
        filteredRows.slice(startIndex, endIndex).forEach(row => {
            row.style.display = '';
        });

        // Update Entries Info Text
        if (totalFiltered === 0) {
            entriesInfo.textContent = 'Showing 0 to 0 of 0 entries';
        } else {
            entriesInfo.textContent = `Showing ${startIndex + 1} to ${endIndex} of ${totalFiltered} entries`;
        }

        // Build Pagination Nav Buttons
        renderPagination(totalPages);
        updateCheckboxState();
    }

    // 2. Render Pagination Controls
    function renderPagination(totalPages) {
        paginationNav.innerHTML = '';
        if (totalPages <= 1) return;

        // Previous Button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="javascript:void(0)" aria-label="Previous"><i class="bx bx-chevron-left"></i></a>`;
        prevLi.addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                updateTable();
            }
        });
        paginationNav.appendChild(prevLi);

        // Page Numbers
        let startP = Math.max(1, currentPage - 2);
        let endP = Math.min(totalPages, startP + 4);
        if (endP - startP < 4) {
            startP = Math.max(1, endP - 4);
        }

        for (let p = startP; p <= endP; p++) {
            const li = document.createElement('li');
            li.className = `page-item ${p === currentPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="javascript:void(0)">${p}</a>`;
            li.addEventListener('click', (function(pageNum) {
                return function() {
                    currentPage = pageNum;
                    updateTable();
                };
            })(p));
            paginationNav.appendChild(li);
        }

        // Next Button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="javascript:void(0)" aria-label="Next"><i class="bx bx-chevron-right"></i></a>`;
        nextLi.addEventListener('click', function () {
            if (currentPage < totalPages) {
                currentPage++;
                updateTable();
            }
        });
        paginationNav.appendChild(nextLi);
    }

    // 3. Checkbox State & Bulk Actions Toolbar
    function updateCheckboxState() {
        const visibleRows = filteredRows.filter(r => r.style.display !== 'none');
        const visibleCheckboxes = visibleRows.map(r => r.querySelector('.row-checkbox')).filter(Boolean);
        const checkedBoxes = visibleCheckboxes.filter(cb => cb.checked);

        if (visibleCheckboxes.length > 0 && checkedBoxes.length === visibleCheckboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedBoxes.length > 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }

        const totalCheckedAll = document.querySelectorAll('.row-checkbox:checked').length;
        if (totalCheckedAll > 0) {
            bulkToolbar.classList.remove('d-none');
            bulkToolbar.classList.add('d-flex');
            selectedCountBadge.textContent = `${totalCheckedAll} selected`;
        } else {
            bulkToolbar.classList.add('d-none');
            bulkToolbar.classList.remove('d-flex');
        }
    }

    // Event: Select All Checkbox Clicked
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const isChecked = this.checked;
            const visibleRows = filteredRows.filter(r => r.style.display !== 'none');
            visibleRows.forEach(r => {
                const cb = r.querySelector('.row-checkbox');
                if (cb) cb.checked = isChecked;
            });
            updateCheckboxState();
        });
    }

    // Event: Row Checkbox Clicked
    document.addEventListener('change', function (e) {
        if (e.target && e.target.classList.contains('row-checkbox')) {
            updateCheckboxState();
        }
    });

    // Event: Show Entries Select Changed
    showEntriesSelect.addEventListener('change', function () {
        currentPage = 1;
        updateTable();
    });

    // Event: Search Input Typed
    searchInput.addEventListener('input', function () {
        currentPage = 1;
        updateTable();
    });

    // Event: Export Selected CSV Clicked
    if (btnExportSelected) {
        btnExportSelected.addEventListener('click', function () {
            const checkedBoxes = Array.from(document.querySelectorAll('.row-checkbox:checked'));
            const selectedIds = checkedBoxes.map(cb => cb.value).filter(Boolean);
            if (selectedIds.length === 0) {
                alert('Please select at least one employee row to export.');
                return;
            }
            @if($payrollRun)
                const baseUrl = "{{ route('payroll.export', $payrollRun->id) }}";
                window.location.href = baseUrl + '?selected_ids=' + selectedIds.join(',');
            @endif
        });
    }

    // Initialize Table View
    updateTable();
});
</script>

@endsection
