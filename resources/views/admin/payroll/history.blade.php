@extends('admin.layout.app')

@section('title', 'Payroll History — Admin Workspace')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        @include('admin.payroll.partials.styles')

        <div class="topbar-crumb mb-3">
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Admin Workspace / Payroll /</span> History</h4>
            <div class="crumb text-muted small">View historical payroll records and audits</div>
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
                <h5 class="mb-0">📚 Payroll History</h5>
                <div class="text-muted small">Showing records across all processed runs</div>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('payroll.history') }}" class="filters p-3 border-bottom bg-light">
                <div class="search">
                    <input type="text" name="search" class="input form-control form-control-sm" placeholder="Search employee..." value="{{ request('search') }}">
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
                <div class="field">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="calculated" {{ request('status') == 'calculated' ? 'selected' : '' }}>Calculated</option>
                        <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="finalized" {{ request('status') == 'finalized' ? 'selected' : '' }}>Finalized</option>
                    </select>
                </div>
                <div class="d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <a href="{{ route('payroll.history') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-wrap">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Employee</th>
                            <th>Designation</th>
                            <th class="num text-end">Gross</th>
                            <th class="num text-end">Deductions</th>
                            <th class="num text-end">Net Pay</th>
                            <th class="num text-end">In Hand</th>
                            <th class="num text-end">CTC</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($histories as $h)
                            @php
                                $u = $h->user;
                                $empDetail = $u->employeeDetails ?? null;
                                $desigName = $empDetail->designation->name ?? '—';
                                $pStatus = strtolower($h->payroll->status ?? 'draft');
                                $monthName = date('M', mktime(0, 0, 0, $h->month ?? ($h->payroll->month ?? 1), 1));
                                $yearVal = $h->year ?? ($h->payroll->year ?? date('Y'));
                            @endphp
                            <tr>
                                <td><span class="badge bg-label-primary">{{ $monthName }} {{ $yearVal }}</span></td>
                                <td>
                                    <div class="cell-employee d-flex align-items-center gap-2">
                                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:30px;height:30px;font-size:11px;font-weight:700;">
                                            {{ strtoupper(substr($u->name ?? 'U', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $u->name ?? 'N/A' }}</div>
                                            <div class="meta text-muted small">{{ $empDetail->employee_id ?? 'EMP'.$u->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $desigName }}</td>
                                <td class="num text-end fw-semibold">₹{{ number_format((float)($h->gross_salary ?? 0), 2) }}</td>
                                <td class="num text-end text-danger">₹{{ number_format((float)($h->total_deductions ?? 0), 2) }}</td>
                                <td class="num text-end fw-bold">₹{{ number_format((float)($h->net_salary ?? 0), 2) }}</td>
                                <td class="num text-end text-success fw-bold">₹{{ number_format((float)($h->total_in_hand ?? $h->net_salary ?? 0), 2) }}</td>
                                <td class="num text-end text-primary fw-semibold">₹{{ number_format((float)($h->ctc ?? $h->gross_salary ?? 0), 2) }}</td>
                                <td>
                                    @if($pStatus == 'finalized')
                                        <span class="pill finalized">🔒 Finalized</span>
                                    @elseif($pStatus == 'approved')
                                        <span class="pill approved">✅ Approved</span>
                                    @elseif($pStatus == 'reviewed')
                                        <span class="pill reviewed">👁 Reviewed</span>
                                    @elseif($pStatus == 'calculated')
                                        <span class="pill calculated">⚙️ Calculated</span>
                                    @else
                                        <span class="pill draft">🟡 Draft</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('payroll.preview', $h->id) }}" class="btn btn-outline-secondary" title="View Breakdown">
                                            👁
                                        </a>
                                        <a href="{{ route('payroll.payslips.view', $h->id) }}" class="btn btn-outline-info" title="View Payslip">
                                            🧾
                                        </a>
                                        <a href="{{ route('payroll.payslips.print', $h->id) }}" target="_blank" class="btn btn-outline-secondary" title="Print Payslip">
                                            🖨
                                        </a>
                                        <a href="{{ route('payroll.payslips.pdf', $h->id) }}" class="btn btn-outline-primary" title="Download PDF">
                                            📥
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <i class="bx bx-calendar-x fs-1 d-block mb-1"></i>
                                    No historical payroll records found for the selected criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($histories->hasPages())
                <div class="pagination p-3 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        Showing {{ $histories->firstItem() }} to {{ $histories->lastItem() }} of {{ $histories->total() }} entries
                    </span>
                    <div>
                        {{ $histories->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
