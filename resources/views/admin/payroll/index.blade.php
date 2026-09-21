@extends('admin.layout.app')

@section('title', 'Payroll Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2"><i class="bx bx-check-circle fs-5"></i> {{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2"><i class="bx bx-error-circle fs-5"></i> {{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Page Header ──────────────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);border-radius:16px;">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="text-white">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="avatar avatar-md rounded-3 d-flex align-items-center justify-content-center" style="background:rgba(255,255,255,.18);">
                            <i class="bx bx-wallet fs-4 text-white"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-white">Payroll Management</h4>
                            <small class="opacity-75">Enterprise payroll engine — {{ now()->format('F Y') }}</small>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('payroll.processing') }}" class="btn btn-light fw-bold shadow-sm">
                        <i class="bx bx-calculator me-1"></i> Open Processing
                    </a>
                    <a href="{{ route('payroll.cycles.index') }}" class="btn fw-semibold" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);">
                        <i class="bx bx-refresh me-1"></i> Payroll Cycles
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── KPI Summary Cards ─────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        {{-- Active Architecture --}}
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;border-left:4px solid #4f46e5 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase" style="letter-spacing:.05em;">Architecture</span>
                        <div class="avatar avatar-sm rounded-3 d-flex align-items-center justify-content-center" style="background:#eef2ff;">
                            <i class="bx bx-building fs-5 text-primary"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-0 text-dark">{{ $activeArchitecture?->name ?? 'Not Set' }}</h5>
                    <small class="text-muted">{{ $activeArchitecture ? 'v'.$activeArchitecture->version.' — Active' : 'Configure in Architectures' }}</small>
                </div>
            </div>
        </div>

        {{-- Recent Cycles --}}
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;border-left:4px solid #10b981 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase" style="letter-spacing:.05em;">Payroll Cycles</span>
                        <div class="avatar avatar-sm rounded-3 d-flex align-items-center justify-content-center" style="background:#d1fae5;">
                            <i class="bx bx-calendar-check fs-5 text-success"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $cycles->count() }}</h3>
                    <small class="text-muted">Recent payroll cycles</small>
                </div>
            </div>
        </div>

        {{-- Payslips --}}
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;border-left:4px solid #f59e0b !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase" style="letter-spacing:.05em;">Payslips</span>
                        <div class="avatar avatar-sm rounded-3 d-flex align-items-center justify-content-center" style="background:#fef3c7;">
                            <i class="bx bx-receipt fs-5 text-warning"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($payslipCount) }}</h3>
                    <small class="text-muted">Total payslips generated</small>
                </div>
            </div>
        </div>

        {{-- History Records --}}
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;border-left:4px solid #ef4444 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase" style="letter-spacing:.05em;">History Records</span>
                        <div class="avatar avatar-sm rounded-3 d-flex align-items-center justify-content-center" style="background:#fee2e2;">
                            <i class="bx bx-history fs-5 text-danger"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($historyCount) }}</h3>
                    <small class="text-muted">Employee payroll records</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Payroll Summary & Quick Actions ──────────────────────────────── --}}
    <div class="row g-4 mb-4">
        {{-- Payroll Summary Stats --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="bx bx-bar-chart-alt-2 text-primary me-2"></i>Payroll Runs Overview</h5>
                        <a href="{{ route('payroll.reports.index') }}" class="btn btn-sm btn-outline-primary fw-semibold">View Reports</a>
                    </div>
                </div>
                <div class="card-body px-4">
                    @php
                        $statusGroups = $payrolls->groupBy('status');
                        $grossTotal = $payrolls->sum('gross_total');
                        $netTotal = $payrolls->sum('net_total');
                        $deductionTotal = $payrolls->sum('deduction_total');
                    @endphp

                    {{-- Summary Row --}}
                    <div class="row g-3 mb-4">
                        <div class="col-4">
                            <div class="p-3 rounded-3 text-center" style="background:#f8fafc;">
                                <div class="text-muted small mb-1">Total Gross</div>
                                <div class="fw-bold fs-6 text-dark">₹{{ number_format($grossTotal, 0) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-3 text-center" style="background:#fef9ee;">
                                <div class="text-muted small mb-1">Total Deductions</div>
                                <div class="fw-bold fs-6 text-warning">₹{{ number_format($deductionTotal, 0) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-3 text-center" style="background:#f0fdf4;">
                                <div class="text-muted small mb-1">Total Net Pay</div>
                                <div class="fw-bold fs-6 text-success">₹{{ number_format($netTotal, 0) }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Payroll Status Breakdown --}}
                    @php
                        $statusColors = ['draft'=>'secondary','calculated'=>'info','review_required'=>'warning','finalized'=>'success','archived'=>'dark'];
                    @endphp
                    @if($payrolls->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead>
                                    <tr class="text-muted" style="font-size:12px;">
                                        <th class="fw-semibold text-uppercase">Cycle / Period</th>
                                        <th class="fw-semibold text-uppercase">Status</th>
                                        <th class="fw-semibold text-uppercase text-end">Gross</th>
                                        <th class="fw-semibold text-uppercase text-end">Deductions</th>
                                        <th class="fw-semibold text-uppercase text-end">Net</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payrolls->take(8) as $payroll)
                                        @php $sc = $statusColors[$payroll->status] ?? 'secondary'; @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-semibold small">{{ $payroll->cycle?->name ?? 'Manual Run #'.$payroll->id }}</div>
                                                <div class="text-muted" style="font-size:11px;">
                                                    {{ optional($payroll->period_start)->format('d M') }} – {{ optional($payroll->period_end)->format('d M Y') }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-label-{{ $sc }}" style="font-size:11px;">
                                                    {{ ucfirst(str_replace('_',' ',$payroll->status)) }}
                                                </span>
                                            </td>
                                            <td class="text-end fw-semibold small">₹{{ number_format($payroll->gross_total, 0) }}</td>
                                            <td class="text-end small text-danger">₹{{ number_format($payroll->deduction_total, 0) }}</td>
                                            <td class="text-end fw-bold small text-success">₹{{ number_format($payroll->net_total, 0) }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('payroll.processing', ['year'=>optional($payroll->period_start)->year, 'month'=>optional($payroll->period_start)->month]) }}" class="btn btn-xs btn-outline-primary" style="font-size:11px;padding:2px 8px;">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bx bx-data fs-1 d-block mb-2 opacity-50"></i>
                            <p class="mb-3">No payroll runs yet. Start by processing payroll.</p>
                            <a href="{{ route('payroll.processing') }}" class="btn btn-primary fw-bold">
                                <i class="bx bx-calculator me-1"></i> Process First Payroll
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick Actions Panel --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                    <h5 class="mb-0 fw-bold"><i class="bx bx-grid-alt text-primary me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body px-4">
                    <div class="d-grid gap-2">
                        <a href="{{ route('payroll.processing') }}" class="btn btn-primary fw-semibold d-flex align-items-center gap-2">
                            <i class="bx bx-calculator"></i> Run Payroll Processing
                        </a>
                        <a href="{{ route('payroll.cycles.index') }}" class="btn btn-outline-secondary fw-semibold d-flex align-items-center gap-2">
                            <i class="bx bx-calendar-plus"></i> Manage Cycles
                        </a>
                        <a href="{{ route('payroll.payslips.index') }}" class="btn btn-outline-secondary fw-semibold d-flex align-items-center gap-2">
                            <i class="bx bx-receipt"></i> View Payslips
                        </a>
                        <a href="{{ route('payroll.policies.index') }}" class="btn btn-outline-secondary fw-semibold d-flex align-items-center gap-2">
                            <i class="bx bx-shield-quarter"></i> Policy Engine
                        </a>
                        <a href="{{ route('payroll.salary-structures.index') }}" class="btn btn-outline-secondary fw-semibold d-flex align-items-center gap-2">
                            <i class="bx bx-layer"></i> Salary Structures
                        </a>
                        <a href="{{ route('payroll.reports.index') }}" class="btn btn-outline-secondary fw-semibold d-flex align-items-center gap-2">
                            <i class="bx bx-bar-chart-alt"></i> Payroll Reports
                        </a>
                        <a href="{{ route('payroll.import-export.index') }}" class="btn btn-outline-secondary fw-semibold d-flex align-items-center gap-2">
                            <i class="bx bx-import"></i> Import / Export
                        </a>
                        <a href="{{ route('payroll.audit-logs.index') }}" class="btn btn-outline-secondary fw-semibold d-flex align-items-center gap-2">
                            <i class="bx bx-list-check"></i> Audit Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Recent Cycles ─────────────────────────────────────────────────── --}}
    @if($cycles->isNotEmpty())
    <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bx bx-calendar text-primary me-2"></i>Recent Payroll Cycles</h5>
                <a href="{{ route('payroll.cycles.index') }}" class="btn btn-sm btn-outline-primary fw-semibold">All Cycles</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f8fafc;font-size:12px;">
                    <tr class="text-muted">
                        <th class="px-4 fw-semibold text-uppercase">Name</th>
                        <th class="fw-semibold text-uppercase">Type</th>
                        <th class="fw-semibold text-uppercase">Period</th>
                        <th class="fw-semibold text-uppercase">Pay Date</th>
                        <th class="fw-semibold text-uppercase">Status</th>
                        <th class="fw-semibold text-uppercase text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cycles as $cycle)
                        @php
                            $cycleStatus = $cycle->status ?? 'draft';
                            $csc = match($cycleStatus) { 'open'=>'success','processing'=>'info','closed'=>'secondary','cancelled'=>'danger', default=>'warning' };
                        @endphp
                        <tr>
                            <td class="px-4 fw-semibold">{{ $cycle->name }}</td>
                            <td><span class="badge bg-label-primary">{{ Str::headline($cycle->cycle_type) }}</span></td>
                            <td class="small text-muted">
                                {{ optional($cycle->start_date)->format('d M Y') }} — {{ optional($cycle->end_date)->format('d M Y') }}
                            </td>
                            <td class="small">{{ optional($cycle->pay_date)->format('d M Y') ?? '—' }}</td>
                            <td><span class="badge bg-label-{{ $csc }}">{{ ucfirst($cycleStatus) }}</span></td>
                            <td class="text-end pe-4">
                                <form method="POST" action="{{ route('payroll.cycles.process', $cycle) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-primary" style="font-size:11px;padding:3px 10px;"
                                        onclick="return confirm('Process payroll for cycle: {{ $cycle->name }}?')">
                                        <i class="bx bx-play-circle me-1"></i>Process
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
