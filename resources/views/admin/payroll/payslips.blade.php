@extends('admin.layout.app')

@section('title', 'Payslips — Admin Workspace')

@section('content')
<div class="payroll-container" id="payslipsPage">
    <div class="container-xxl flex-grow-1 container-p-y">
        @include('admin.payroll.partials.styles')
        <style>
            #payslipsTable .payslip-selection { width:48px; min-width:48px; padding:12px!important; text-align:center; vertical-align:middle; }
            #payslipsTable .payslip-selection input { position:static!important; width:18px; height:18px; margin:0!important; cursor:pointer; accent-color:var(--pr-primary); }
            #payslipsTable .payslip-selection input:disabled { cursor:not-allowed; opacity:.4; }
            #payslipsTable .payslip-selection input:focus-visible { outline:2px solid var(--pr-primary); outline-offset:3px; }
            #payslipsTable td[colspan] { white-space:normal; }
            #payslipsTable td[colspan] i { text-align:center; }
            #payslipsPage .pr-filters .form-control, #payslipsPage .pr-filters .form-select { min-height:38px; }
            @media (max-width:575px) { #payslipsPage .pr-filters .field { flex-basis:100%; min-width:0; } }
        </style>

        <div class="topbar-crumb mb-3">
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Admin Workspace / Payroll /</span> Payslips</h4>
            <div class="crumb text-muted small">Generate, view, print and download employee payslips</div>
        </div>

        @include('admin.payroll.partials.tabs')

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-head d-flex align-items-center justify-content-between p-3 border-bottom flex-wrap gap-2">
                <h5 class="mb-0">🧾 Employee Payslips</h5>
                <div class="text-muted small">Generated for approved and finalized payroll periods</div>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('payroll.payslips.index') }}" class="pr-filters">
                <div class="field search-field">
                    <label for="payslipSearch">Employee</label>
                    <input id="payslipSearch" type="search" name="search" class="form-control form-control-sm" placeholder="Search employee..." value="{{ request('search') }}">
                </div>
                <div class="field">
                    <label class="form-label small mb-1">Month</label>
                    <select name="month" class="form-select form-select-sm">
                        <option value="">All Months</option>
                        @for($m = 1; $m <= 12; $m++)
                            @php $mName = date('F', mktime(0, 0, 0, $m, 1)); @endphp
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ $mName }}</option>
                        @endfor
                    </select>
                </div>
                <div class="field">
                    <label class="form-label small mb-1">Year</label>
                    <select name="year" class="form-select form-select-sm">
                        <option value="">All Years</option>
                        @for($y = date('Y') + 1; $y >= 2023; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="field">
                    <label class="form-label small mb-1">Department</label>
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->dpt_name ?? $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <a href="{{ route('payroll.payslips.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-wrap">
                <table class="table pr-table table-hover mb-0" id="payslipsTable">
                    <thead>
                        <tr>
                            <th data-pms-selection-column class="payslip-selection"><input type="checkbox" class="form-check-input" aria-label="Select all payslips on this page" @disabled($payslips->isEmpty())></th>
                            <th>Month</th>
                            <th>Employee</th>
                            <th>Emp ID</th>
                            <th>Designation / Dept</th>
                            <th class="num text-end">In Hand</th>
                            <th class="num text-end">CTC</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payslips as $ps)
                            @php
                                $u = $ps->user;
                                $empDetail = $u->employeeDetails ?? null;
                                $monthName = date('M', mktime(0, 0, 0, $ps->month ?? ($ps->payroll->month ?? 1), 1));
                                $yearVal = $ps->year ?? ($ps->payroll->year ?? date('Y'));
                                $pStatus = strtolower($ps->payroll->status ?? 'approved');
                            @endphp
                            <tr>
                                <td data-pms-selection-column class="payslip-selection"><input type="checkbox" class="form-check-input" aria-label="Select payslip for {{ $u->name ?? 'employee' }}" value="{{ $ps->id }}"></td>
                                <td><span class="badge bg-label-primary">{{ $monthName }} {{ $yearVal }}</span></td>
                                <td>
                                    <div class="cell-employee d-flex align-items-center gap-2">
                                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:30px;height:30px;font-size:11px;font-weight:700;">
                                            {{ strtoupper(substr($u->name ?? 'U', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $u->name ?? 'N/A' }}</div>
                                            <div class="meta text-muted small">{{ $u->email ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark">{{ $empDetail->employee_id ?? 'EMP'.$u->id }}</span></td>
                                <td>
                                    <div class="small fw-semibold">{{ $empDetail->designation->name ?? '—' }}</div>
                                    <div class="meta text-muted small">{{ $empDetail->department->name ?? '—' }}</div>
                                </td>
                                @php
                                    $snap = $ps->employee_snapshot ?? [];
                                    $totalInHand = $snap['total_in_hand'] ?? ($ps->net_salary ?? 0);
                                    $totalCtc = $snap['ctc'] ?? ($ps->gross_salary ?? 0);
                                @endphp
                                <td class="num text-end text-success fw-bold">
                                    ₹{{ number_format((float)$totalInHand, 2) }}
                                </td>
                                <td class="num text-end text-primary fw-semibold">
                                    ₹{{ number_format((float)$totalCtc, 2) }}
                                </td>
                                <td>
                                    @if($pStatus == 'finalized')
                                        <span class="pill finalized">🔒 Finalized</span>
                                    @else
                                        <span class="pill approved">✅ Approved</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('payroll.payslips.view', $ps->id) }}" class="btn btn-sm btn-outline-info">
                                        👁 View
                                    </a>
                                    <a href="{{ route('payroll.payslips.print', $ps->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        🖨 Print
                                    </a>
                                    <a href="{{ route('payroll.payslips.pdf', $ps->id) }}" class="btn btn-sm btn-primary">
                                        📥 PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="bx bx-receipt fs-1 d-block mb-1"></i>
                                    No payslips available for the selected criteria.
                                    <br><small>Payslips are generated once payroll is approved or finalized.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payslips->hasPages())
                <div class="pagination p-3 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        Showing {{ $payslips->firstItem() }} to {{ $payslips->lastItem() }} of {{ $payslips->total() }} entries
                    </span>
                    <div>
                        {{ $payslips->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
