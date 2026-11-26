@extends('admin.layout.app')

@section('title', 'My Payslips')

@section('content')
@include('admin.payroll.partials.styles')
@php
    $money = fn ($v) => '₹' . number_format((float) $v, 2);
    $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
@endphp
<div class="payroll-container my-payslips-page">
    <div class="pr-card">
        <div class="pr-card-head">
            <h2>🧾 My Payslips</h2>
            <div class="spacer"></div>
            <form method="GET" action="{{ route('my-payslips.index') }}" class="d-flex gap-2 align-items-center">
                <label for="payslipYear" class="small text-muted mb-0">Year</label>
                <select id="payslipYear" name="year" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                    <option value="all">All years</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" @selected((string) request('year') === (string) $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <p class="px-3 pt-2 mb-0 small text-muted">Your salary is set by the company Admin. Payslips appear here once a payroll run is approved.</p>

        @if($latest)
            {{-- Latest payslip at a glance --}}
            <div class="row g-3 p-3">
                <div class="col-md-3 col-6"><div class="info-box py-2"><div class="small text-muted">Latest month</div><div class="fw-bold fs-6">{{ \Carbon\Carbon::parse($latest->payroll?->period_start)->format('F Y') }}</div></div></div>
                <div class="col-md-3 col-6"><div class="info-box py-2"><div class="small text-muted">Net pay</div><div class="fw-bold fs-6 text-success">{{ $money($snapshot['total_in_hand'] ?? $latest->net_salary) }}</div></div></div>
                <div class="col-md-3 col-6"><div class="info-box py-2"><div class="small text-muted">Paid days</div><div class="fw-bold fs-6">{{ $qty($snapshot['payable_days'] ?? 0) }} / {{ $qty($snapshot['working_days'] ?? 0) }}</div></div></div>
                <div class="col-md-3 col-6"><div class="info-box py-2"><div class="small text-muted">Deductions</div><div class="fw-bold fs-6 text-danger">{{ $money($snapshot['total_deductions'] ?? $latest->total_deductions) }}</div></div></div>
            </div>
        @endif

        <div class="table-wrap">
            <table class="pr-table" id="myPayslipsTable">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Payslip No.</th>
                        <th>Paid Days</th>
                        <th>Leave (Paid / Unpaid)</th>
                        <th class="text-end">Gross</th>
                        <th class="text-end">Deductions</th>
                        <th class="text-end">Net Pay</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payslips as $slip)
                        @php($s = is_array($slip->employee_snapshot) ? $slip->employee_snapshot : (json_decode((string) $slip->employee_snapshot, true) ?: []))
                        <tr>
                            <td><b>{{ \Carbon\Carbon::parse($slip->payroll?->period_start)->format('F Y') }}</b></td>
                            <td>{{ $slip->payslip_number }}</td>
                            <td>{{ $qty($s['payable_days'] ?? 0) }} / {{ $qty($s['working_days'] ?? 0) }}</td>
                            <td>{{ $qty($s['paid_leave'] ?? 0) }} / {{ $qty($s['unpaid_leave'] ?? 0) }}</td>
                            <td class="num">{{ $money($s['gross_salary'] ?? $slip->gross_salary) }}</td>
                            <td class="num">{{ $money($s['total_deductions'] ?? $slip->total_deductions) }}</td>
                            <td class="num text-success fw-bold">{{ $money($s['total_in_hand'] ?? $slip->net_salary) }}</td>
                            <td><span class="pr-pill {{ $slip->payroll?->status === 'finalized' ? 'finalized' : 'approved' }}">{{ ucfirst($slip->payroll?->status ?? 'approved') }}</span></td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('my-payslips.show', $slip->id) }}" class="pr-btn pr-btn-sm">👁 View</a>
                                <a href="{{ route('my-payslips.pdf', $slip->id) }}" class="pr-btn pr-btn-sm pr-btn-primary">📥 PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-4 text-muted">No payslips yet. They appear here after your company's payroll for a month is approved.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
