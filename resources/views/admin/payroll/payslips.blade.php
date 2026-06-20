@extends('admin.layout.app')

@section('title', 'Payslips')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Payslips</h5>
            <a href="{{ route('payroll.import-export.index') }}" class="btn btn-primary">Export</a>
        </div>
        <div class="table-responsive">
            <table class="table"><thead><tr><th>Payslip</th><th>Employee</th><th>Gross</th><th>Deductions</th><th>Net</th><th>Status</th></tr></thead><tbody>
                @forelse($payslips as $payslip)
                    <tr><td>{{ $payslip->payslip_number ?? 'Draft' }}</td><td>{{ $payslip->employee_snapshot['name'] ?? 'Employee #' . $payslip->user_id }}</td><td>{{ number_format($payslip->gross_salary, 2) }}</td><td>{{ number_format($payslip->total_deductions, 2) }}</td><td>{{ number_format($payslip->net_salary, 2) }}</td><td><span class="badge bg-label-primary">{{ ucfirst($payslip->status) }}</span></td></tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No payslips found.</td></tr>
                @endforelse
            </tbody></table>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h5 class="mb-0">Templates</h5></div>
        <div class="table-responsive"><table class="table"><thead><tr><th>Name</th><th>Type</th><th>Version</th><th>Status</th></tr></thead><tbody>
            @forelse($templates as $template)
                <tr><td>{{ $template->name }}</td><td>{{ Str::headline($template->template_type) }}</td><td>{{ $template->version }}</td><td>{{ $template->is_active ? 'Active' : 'Inactive' }}</td></tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">No templates configured.</td></tr>
            @endforelse
        </tbody></table></div>
    </div>
</div>
@endsection
