@extends('admin.layout.app')

@section('title', 'Payroll Reports')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bx bx-bar-chart-alt-2 text-primary me-2"></i>Payroll Reports</h4>
            <p class="text-muted mb-0">Comprehensive payroll analytics and downloadable reports</p>
        </div>
        <a href="{{ route('payroll.reports.export') }}" class="btn btn-primary fw-bold shadow-sm">
            <i class="bx bx-download me-1"></i> Export CSV
        </a>
    </div>

    {{-- KPI Cards --}}
    @php
        $totalGross  = $payrolls->sum('gross_total');
        $totalDed    = $payrolls->sum('deduction_total');
        $totalTax    = $payrolls->sum('tax_total');
        $totalNet    = $payrolls->sum('net_total');
        $statusGroup = $payrolls->groupBy('status');
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm p-3 text-center" style="border-radius:12px;border-top:3px solid #4f46e5;">
                <div class="fs-5 fw-bold text-primary">₹{{ number_format($totalGross, 0) }}</div>
                <div class="small text-muted mt-1">Total Gross Payroll</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm p-3 text-center" style="border-radius:12px;border-top:3px solid #f59e0b;">
                <div class="fs-5 fw-bold text-warning">₹{{ number_format($totalDed, 0) }}</div>
                <div class="small text-muted mt-1">Total Deductions</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm p-3 text-center" style="border-radius:12px;border-top:3px solid #ef4444;">
                <div class="fs-5 fw-bold text-danger">₹{{ number_format($totalTax, 0) }}</div>
                <div class="small text-muted mt-1">Total Tax</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm p-3 text-center" style="border-radius:12px;border-top:3px solid #10b981;">
                <div class="fs-5 fw-bold text-success">₹{{ number_format($totalNet, 0) }}</div>
                <div class="small text-muted mt-1">Total Net Pay</div>
            </div>
        </div>
    </div>

    {{-- Quick Report Type Cards --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
            <h5 class="fw-bold mb-0"><i class="bx bx-grid-alt text-primary me-2"></i>Report Types</h5>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="row g-3">
                @php $reportTypes = [
                    ['icon'=>'bx-money','color'=>'primary','label'=>'Payroll Summary','desc'=>'Monthly payroll overview'],
                    ['icon'=>'bx-list-ol','color'=>'success','label'=>'Salary Register','desc'=>'Full employee salary list'],
                    ['icon'=>'bx-gift','color'=>'warning','label'=>'Bonus Report','desc'=>'Bonus payments breakdown'],
                    ['icon'=>'bx-minus-circle','color'=>'danger','label'=>'Deduction Report','desc'=>'All deduction details'],
                    ['icon'=>'bx-receipt','color'=>'info','label'=>'Tax Report','desc'=>'TDS & tax summary'],
                    ['icon'=>'bx-time-five','color'=>'secondary','label'=>'Overtime Report','desc'=>'Overtime payments'],
                    ['icon'=>'bx-building','color'=>'primary','label'=>'Dept-wise Payroll','desc'=>'By department breakdown'],
                    ['icon'=>'bx-user','color'=>'success','label'=>'Employee-wise','desc'=>'Per employee history'],
                    ['icon'=>'bx-calendar','color'=>'warning','label'=>'Monthly Analysis','desc'=>'Month-over-month trends'],
                    ['icon'=>'bx-calendar-check','color'=>'info','label'=>'Yearly Summary','desc'=>'Annual payroll wrap-up'],
                    ['icon'=>'bx-filter','color'=>'secondary','label'=>'Custom Builder','desc'=>'Filter & build custom report'],
                ]; @endphp
                @foreach($reportTypes as $r)
                    <div class="col-xl-3 col-md-4 col-6">
                        <div class="border rounded-3 p-3 d-flex align-items-center gap-3 h-100" style="transition:.2s;cursor:pointer;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                            <div class="avatar avatar-sm rounded-3 d-flex align-items-center justify-content-center" style="background:var(--bs-{{ $r['color'] }}-light,#eef2ff);min-width:36px;">
                                <i class="bx {{ $r['icon'] }} text-{{ $r['color'] }}"></i>
                            </div>
                            <div>
                                <div class="fw-semibold small">{{ $r['label'] }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ $r['desc'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Payroll Runs Table --}}
    <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="fw-bold mb-0"><i class="bx bx-table text-primary me-2"></i>All Payroll Runs</h5>
                <input type="text" id="reportSearch" class="form-control form-control-sm" placeholder="Search..." style="width:200px;">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="reportTable">
                <thead style="background:#f8fafc;font-size:12px;">
                    <tr class="text-muted">
                        <th class="px-4 fw-semibold text-uppercase">Period</th>
                        <th class="fw-semibold text-uppercase">Status</th>
                        <th class="fw-semibold text-uppercase text-end">Gross</th>
                        <th class="fw-semibold text-uppercase text-end">Deductions</th>
                        <th class="fw-semibold text-uppercase text-end">Tax</th>
                        <th class="fw-semibold text-uppercase text-end pe-4">Net Pay</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $payroll)
                        @php $sc = match($payroll->status) { 'finalized'=>'success','calculated'=>'info','review_required'=>'warning','archived'=>'dark', default=>'secondary' }; @endphp
                        <tr>
                            <td class="px-4">
                                <div class="fw-semibold small">
                                    {{ optional($payroll->period_start)->format('d M') }} – {{ optional($payroll->period_end)->format('d M Y') }}
                                </div>
                                <div class="text-muted" style="font-size:11px;">Run #{{ $payroll->id }}</div>
                            </td>
                            <td><span class="badge bg-label-{{ $sc }}">{{ ucfirst(str_replace('_',' ',$payroll->status)) }}</span></td>
                            <td class="text-end fw-semibold small">₹{{ number_format($payroll->gross_total, 2) }}</td>
                            <td class="text-end small text-danger">₹{{ number_format($payroll->deduction_total, 2) }}</td>
                            <td class="text-end small text-warning">₹{{ number_format($payroll->tax_total, 2) }}</td>
                            <td class="text-end fw-bold text-success pe-4">₹{{ number_format($payroll->net_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bx bx-data fs-1 d-block mb-2 opacity-30"></i>
                                No payroll data available. Run a payroll first.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($payrolls->isNotEmpty())
                    <tfoot style="background:#f8fafc;">
                        <tr class="fw-bold">
                            <td class="px-4 text-muted small" colspan="2">Totals</td>
                            <td class="text-end small">₹{{ number_format($totalGross, 2) }}</td>
                            <td class="text-end small text-danger">₹{{ number_format($totalDed, 2) }}</td>
                            <td class="text-end small text-warning">₹{{ number_format($totalTax, 2) }}</td>
                            <td class="text-end text-success pe-4">₹{{ number_format($totalNet, 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>

<script>
document.getElementById('reportSearch')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#reportTable tbody tr').forEach(r => {
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endsection
