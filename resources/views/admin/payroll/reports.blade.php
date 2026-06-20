@extends('admin.layout.app')

@section('title', 'Payroll Reports')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Payroll Reports</h5></div>
        <div class="card-body">
            <div class="row g-3">
                @foreach(['Payroll Summary','Salary Register','Bonus Report','Deduction Report','Tax Report','Overtime Report','Department Wise Payroll','Employee Wise Payroll','Monthly Payroll','Yearly Payroll','Custom Report Builder'] as $report)
                    <div class="col-md-4"><div class="border rounded p-3">{{ $report }}</div></div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h5 class="mb-0">Payroll Totals</h5></div>
        <div class="table-responsive"><table class="table"><thead><tr><th>Status</th><th>Gross</th><th>Deductions</th><th>Tax</th><th>Net</th></tr></thead><tbody>
            @forelse($payrolls as $payroll)
                <tr><td>{{ ucfirst($payroll->status) }}</td><td>{{ number_format($payroll->gross_total, 2) }}</td><td>{{ number_format($payroll->deduction_total, 2) }}</td><td>{{ number_format($payroll->tax_total, 2) }}</td><td>{{ number_format($payroll->net_total, 2) }}</td></tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">No payroll data found.</td></tr>
            @endforelse
        </tbody></table></div>
    </div>
</div>
@endsection
