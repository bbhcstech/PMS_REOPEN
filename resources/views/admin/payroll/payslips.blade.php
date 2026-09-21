@extends('admin.layout.app')

@section('title', 'Payslips')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bx bx-receipt text-primary me-2"></i>Payslips</h4>
            <p class="text-muted mb-0">View, print and distribute employee payslips</p>
        </div>
        <a href="{{ route('payroll.processing') }}" class="btn btn-primary fw-bold shadow-sm">
            <i class="bx bx-calculator me-1"></i> Generate Payslips
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
                <div class="fs-3 fw-bold text-primary">{{ $payslips->count() }}</div>
                <div class="small text-muted">Total Payslips</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
                <div class="fs-3 fw-bold text-success">{{ $payslips->where('status','sent')->count() }}</div>
                <div class="small text-muted">Sent</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
                <div class="fs-3 fw-bold text-warning">{{ $payslips->where('status','generated')->count() }}</div>
                <div class="small text-muted">Generated</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
                <div class="fs-3 fw-bold text-info">₹{{ number_format($payslips->sum('net_salary'), 0) }}</div>
                <div class="small text-muted">Total Net Pay</div>
            </div>
        </div>
    </div>

    {{-- Payslips Table --}}
    <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="fw-bold mb-0">All Payslips</h5>
                <div class="d-flex gap-2">
                    <input type="text" id="payslipSearch" class="form-control form-control-sm" placeholder="Search employee..." style="width:200px;">
                    <select id="monthFilter" class="form-select form-select-sm" style="width:140px;">
                        <option value="">All Months</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="payslipsTable">
                <thead style="background:#f8fafc;font-size:12px;">
                    <tr class="text-muted">
                        <th class="px-4 fw-semibold">#</th>
                        <th class="fw-semibold text-uppercase">Employee</th>
                        <th class="fw-semibold text-uppercase">Pay Period</th>
                        <th class="fw-semibold text-uppercase text-end">Gross</th>
                        <th class="fw-semibold text-uppercase text-end">Deductions</th>
                        <th class="fw-semibold text-uppercase text-end">Net Pay</th>
                        <th class="fw-semibold text-uppercase">Status</th>
                        <th class="fw-semibold text-uppercase text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payslips as $ps)
                        @php
                            $net = $ps->net_salary ?? $ps->net_pay ?? 0;
                            $gross = $ps->gross_salary ?? 0;
                            $ded = $ps->total_deductions ?? 0;
                            $sc = match($ps->status ?? 'generated') {
                                'sent' => 'success', 'viewed' => 'info', 'failed' => 'danger', default => 'warning'
                            };
                        @endphp
                        <tr>
                            <td class="px-4 text-muted small">{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                         style="background:{{ ['#4f46e5','#10b981','#f59e0b','#ef4444','#8b5cf6'][$loop->index % 5] }};font-size:12px;width:34px;height:34px;">
                                        {{ strtoupper(substr($ps->user?->name ?? 'E', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">{{ $ps->user?->name ?? 'Employee #'.$ps->user_id }}</div>
                                        <div class="text-muted" style="font-size:11px;">{{ $ps->user?->employee_id ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="small">
                                @if($ps->pay_period_start)
                                    {{ \Carbon\Carbon::parse($ps->pay_period_start)->format('M Y') }}
                                @elseif($ps->period_month)
                                    {{ date('F', mktime(0,0,0,$ps->period_month,1)) }} {{ $ps->period_year }}
                                @else
                                    {{ $ps->created_at?->format('M Y') ?? '—' }}
                                @endif
                            </td>
                            <td class="text-end fw-semibold small">₹{{ number_format($gross, 0) }}</td>
                            <td class="text-end small text-danger">₹{{ number_format($ded, 0) }}</td>
                            <td class="text-end fw-bold text-success">₹{{ number_format($net, 0) }}</td>
                            <td>
                                <span class="badge bg-label-{{ $sc }}" style="font-size:11px;">
                                    {{ ucfirst($ps->status ?? 'generated') }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('payroll.payslips.view', $ps) }}" class="btn btn-xs btn-primary" title="View" style="font-size:11px;padding:3px 8px;">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    <a href="{{ route('payroll.payslips.print', $ps) }}" target="_blank" class="btn btn-xs btn-outline-secondary" title="Print" style="font-size:11px;padding:3px 8px;">
                                        <i class="bx bx-printer"></i>
                                    </a>
                                    <form method="POST" action="{{ route('payroll.payslips.send', $ps) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-outline-success" title="Send Email" style="font-size:11px;padding:3px 8px;"
                                            onclick="return confirm('Send payslip to {{ $ps->user?->email }}?')">
                                            <i class="bx bx-envelope"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bx bx-receipt fs-1 d-block mb-2 opacity-30"></i>
                                No payslips found. Process a payroll cycle to generate payslips.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
document.getElementById('payslipSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#payslipsTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endsection
