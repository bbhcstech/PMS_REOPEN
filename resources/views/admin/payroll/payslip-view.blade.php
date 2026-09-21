@extends('admin.layout.app')

@section('title', 'Payslip — ' . ($payslip->user?->name ?? 'Employee'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('payroll.payslips.index') }}" class="btn btn-sm btn-outline-secondary fw-semibold mb-2">
                <i class="bx bx-arrow-back me-1"></i> Back to Payslips
            </a>
            <h4 class="fw-bold mb-0"><i class="bx bx-receipt text-primary me-2"></i>Payslip Detail</h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payroll.payslips.print', $payslip) }}" target="_blank" class="btn btn-outline-primary fw-semibold">
                <i class="bx bx-printer me-1"></i> Print
            </a>
            <form method="POST" action="{{ route('payroll.payslips.send', $payslip) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success fw-semibold"
                    onclick="return confirm('Send payslip to {{ $payslip->user?->email }}?')">
                    <i class="bx bx-envelope me-1"></i> Send Email
                </button>
            </form>
        </div>
    </div>

    {{-- Payslip Card --}}
    <div class="card border-0 shadow" style="border-radius:16px;max-width:860px;margin:auto;">
        {{-- Company Header --}}
        <div class="card-body p-0">
            <div class="p-5 pb-4" style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);border-radius:16px 16px 0 0;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="text-white fw-bold mb-1">PAYSLIP</h3>
                        <p class="text-white opacity-75 mb-0">
                            @php
                                if ($payslip->pay_period_start) {
                                    echo \Carbon\Carbon::parse($payslip->pay_period_start)->format('F Y');
                                } elseif (isset($payslip->period_month)) {
                                    echo date('F', mktime(0,0,0,$payslip->period_month,1)) . ' ' . $payslip->period_year;
                                } else {
                                    echo $payslip->created_at?->format('F Y') ?? now()->format('F Y');
                                }
                            @endphp
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="text-white fw-bold">{{ config('app.name') }}</div>
                        <div class="text-white opacity-75 small">Payslip #{{ $payslip->id }}</div>
                        <div class="text-white opacity-75 small">Generated: {{ $payslip->created_at?->format('d M Y') }}</div>
                    </div>
                </div>
            </div>

            {{-- Employee Info --}}
            <div class="px-5 py-4 border-bottom" style="background:#f8fafc;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted mb-1 fw-semibold text-uppercase" style="letter-spacing:.05em;">Employee</div>
                        <div class="fw-bold fs-5">{{ $payslip->user?->name ?? 'Employee #'.$payslip->user_id }}</div>
                        <div class="text-muted small">{{ $payslip->user?->email ?? '' }}</div>
                        <div class="text-muted small">Employee ID: {{ $payslip->user?->employee_id ?? $payslip->user_id }}</div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="small text-muted mb-1 fw-semibold text-uppercase" style="letter-spacing:.05em;">Department</div>
                        <div class="fw-semibold">{{ $payslip->user?->department ?? '—' }}</div>
                        <div class="text-muted small">{{ $payslip->user?->designation ?? $payslip->user?->job_title ?? '—' }}</div>
                        <div class="mt-1">
                            @php $sc = match($payslip->status ?? 'generated') { 'sent'=>'success','viewed'=>'info','failed'=>'danger', default=>'warning' }; @endphp
                            <span class="badge bg-{{ $sc }}">{{ ucfirst($payslip->status ?? 'Generated') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Earnings & Deductions --}}
            <div class="px-5 py-4">
                @php
                    $data = is_array($payslip->data) ? $payslip->data : (json_decode($payslip->data ?? '{}', true) ?: []);
                    $earnings = $data['earnings'] ?? [
                        'Basic Salary'       => $payslip->basic_salary ?? 0,
                        'HRA'                => $payslip->hra ?? 0,
                        'Special Allowance'  => $payslip->special_allowance ?? 0,
                        'Bonus'              => $payslip->bonus_amount ?? 0,
                    ];
                    $deductions = $data['deductions'] ?? [
                        'Provident Fund (PF)' => $payslip->pf_deduction ?? $payslip->pf ?? 0,
                        'ESI'                 => $payslip->esi ?? 0,
                        'Professional Tax'    => $payslip->pt ?? 0,
                        'TDS'                 => $payslip->tds ?? 0,
                    ];
                    $gross  = $payslip->gross_salary ?? array_sum(array_values($earnings));
                    $totalDed = $payslip->total_deductions ?? array_sum(array_values($deductions));
                    $netPay = $payslip->net_salary ?? $payslip->net_pay ?? ($gross - $totalDed);
                @endphp

                <div class="row g-4">
                    {{-- Earnings --}}
                    <div class="col-md-6">
                        <h6 class="fw-bold text-success mb-3"><i class="bx bx-plus-circle me-1"></i>Earnings</h6>
                        <table class="table table-sm mb-0">
                            @foreach($earnings as $label => $amount)
                                @if((float)$amount > 0)
                                    <tr>
                                        <td class="ps-0 text-muted small border-0">{{ $label }}</td>
                                        <td class="text-end fw-semibold border-0">₹{{ number_format((float)$amount, 2) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            <tr class="border-top">
                                <td class="ps-0 fw-bold text-success border-0 pt-2">Gross Earnings</td>
                                <td class="text-end fw-bold text-success border-0 pt-2">₹{{ number_format($gross, 2) }}</td>
                            </tr>
                        </table>
                    </div>

                    {{-- Deductions --}}
                    <div class="col-md-6">
                        <h6 class="fw-bold text-danger mb-3"><i class="bx bx-minus-circle me-1"></i>Deductions</h6>
                        <table class="table table-sm mb-0">
                            @foreach($deductions as $label => $amount)
                                @if((float)$amount > 0)
                                    <tr>
                                        <td class="ps-0 text-muted small border-0">{{ $label }}</td>
                                        <td class="text-end fw-semibold border-0 text-danger">₹{{ number_format((float)$amount, 2) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            <tr class="border-top">
                                <td class="ps-0 fw-bold text-danger border-0 pt-2">Total Deductions</td>
                                <td class="text-end fw-bold text-danger border-0 pt-2">₹{{ number_format($totalDed, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Net Pay Banner --}}
            <div class="mx-5 mb-5 p-4 rounded-3 text-center" style="background:linear-gradient(135deg,#ecfdf5,#d1fae5);border:2px solid #10b981;">
                <div class="text-muted small fw-bold text-uppercase mb-1" style="letter-spacing:.1em;">Net Pay</div>
                <div class="display-5 fw-bold text-success">₹{{ number_format($netPay, 2) }}</div>
                <div class="text-muted small mt-1">For the period</div>
            </div>

            @if($payslip->notes)
            <div class="px-5 pb-5">
                <div class="alert alert-light border fw-semibold small">
                    <i class="bx bx-info-circle me-1"></i> Note: {{ $payslip->notes }}
                </div>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
