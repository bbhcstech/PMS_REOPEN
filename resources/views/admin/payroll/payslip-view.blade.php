@extends('admin.layout.app')

@section('title', 'Payslip — ' . ($payslip?->user?->name ?? 'Employee'))

@section('content')
@php
    $activeId = $payslip->id;
    $period = \Carbon\Carbon::parse($payslip->payroll?->period_start ?? now());
@endphp

<div class="payroll-container">
    <div class="payroll-page-body">
        @include('admin.payroll.partials.styles')
        @include('admin.payroll.partials.tabs')

        <div class="topbar-crumb mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Payroll / Payslips /</span> {{ $payslip->user?->name ?? 'Employee' }}</h4>
                <div class="crumb text-muted small">{{ $payslip->payslip_number }} · {{ $period->format('F Y') }}</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('payroll.payslips.index') }}" class="btn btn-outline-secondary btn-sm">← Back to Payslips</a>
                @if(auth()->user()?->hasModulePermission('payslips', 'export'))
<a href="{{ route('payroll.payslips.print', $activeId) }}" target="_blank" class="btn btn-outline-secondary btn-sm">🖨 Print</a>
@endif
                @if(auth()->user()?->hasModulePermission('payslips', 'export'))
<a href="{{ route('payroll.payslips.pdf', $activeId) }}" class="btn btn-primary btn-sm">📥 Download PDF</a>
@endif
            </div>
        </div>

        {{-- The payslip is a standalone document (same file as print/PDF), so the admin theme cannot restyle it. --}}
        <iframe id="payslipFrame"
                src="{{ route('payroll.payslips.print', $activeId) }}?noprint=1"
                title="Payslip document"
                style="width:100%;min-height:1100px;border:0;border-radius:16px;display:block;background:transparent;"
                onload="try{this.style.height=(this.contentWindow.document.documentElement.scrollHeight+4)+'px';}catch(e){}"></iframe>
    </div>
</div>
@endsection
