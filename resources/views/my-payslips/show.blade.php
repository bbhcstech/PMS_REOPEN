@extends('admin.layout.app')

@section('title', 'Payslip — ' . \Carbon\Carbon::parse($payslip->payroll?->period_start)->format('F Y'))

@section('content')
@include('admin.payroll.partials.styles')
<div class="payroll-container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Payslip · {{ \Carbon\Carbon::parse($payslip->payroll?->period_start)->format('F Y') }}</h4>
            <div class="small text-muted">{{ $payslip->payslip_number }}</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('my-payslips.index') }}" class="btn btn-outline-secondary btn-sm">← My Payslips</a>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('payslipFrame').contentWindow.print()">🖨 Print</button>
            <a href="{{ route('my-payslips.pdf', $payslip->id) }}" class="btn btn-primary btn-sm">📥 Download PDF</a>
        </div>
    </div>

    {{-- Same company-branded document as the PDF, isolated from the admin theme. --}}
    <iframe id="payslipFrame" src="{{ route('my-payslips.document', $payslip->id) }}" title="Payslip document"
            style="width:100%;min-height:1100px;border:0;border-radius:16px;display:block;background:transparent;"
            onload="try{this.style.height=(this.contentWindow.document.documentElement.scrollHeight+4)+'px';}catch(e){}"></iframe>
</div>
@endsection
