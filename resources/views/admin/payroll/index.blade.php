@extends('admin.layout.app')

@section('title', 'Payroll')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">Payroll</h5>
                <small class="text-muted">Enterprise payroll, payslips, rules, reports, and audit history.</small>
            </div>
            <a href="{{ route('payroll.cycles.index') }}" class="btn btn-primary">Payroll Cycles</a>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3"><div class="border rounded p-3"><small class="text-muted">Active Architecture</small><h6 class="mb-0">{{ $activeArchitecture?->name ?? 'Not configured' }}</h6></div></div>
                <div class="col-md-3"><div class="border rounded p-3"><small class="text-muted">Recent Cycles</small><h6 class="mb-0">{{ $cycles->count() }}</h6></div></div>
                <div class="col-md-3"><div class="border rounded p-3"><small class="text-muted">Payslips</small><h6 class="mb-0">{{ $payslipCount }}</h6></div></div>
                <div class="col-md-3"><div class="border rounded p-3"><small class="text-muted">History Records</small><h6 class="mb-0">{{ $historyCount }}</h6></div></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Recent Payroll Runs</h5></div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Cycle</th><th>Period</th><th>Status</th><th>Gross</th><th>Deductions</th><th>Net</th></tr></thead>
                <tbody>
                    @forelse($payrolls as $payroll)
                        <tr>
                            <td>{{ $payroll->cycle?->name ?? '-' }}</td>
                            <td>{{ optional($payroll->period_start)->format('d M Y') }} - {{ optional($payroll->period_end)->format('d M Y') }}</td>
                            <td><span class="badge bg-label-primary">{{ ucfirst(str_replace('_', ' ', $payroll->status)) }}</span></td>
                            <td>{{ number_format($payroll->gross_total, 2) }}</td>
                            <td>{{ number_format($payroll->deduction_total, 2) }}</td>
                            <td>{{ number_format($payroll->net_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No payroll runs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
