<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip — {{ $payslip->user?->name ?? 'Employee' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #1e293b; background: #fff; }
        .page { max-width: 800px; margin: 0 auto; padding: 30px; }

        .header { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; padding: 28px 30px; border-radius: 12px 12px 0 0; }
        .header h2 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
        .header .sub { opacity: .75; font-size: 13px; }
        .header-row { display: flex; justify-content: space-between; align-items: center; }
        .header-right { text-align: right; }
        .header-right .company { font-size: 16px; font-weight: 700; }

        .employee-bar { display: flex; justify-content: space-between; background: #f8fafc; padding: 18px 30px; border-bottom: 1px solid #e2e8f0; }
        .emp-label { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: #64748b; margin-bottom: 4px; font-weight: 600; }
        .emp-val { font-size: 16px; font-weight: 700; color: #1e293b; }
        .emp-sub { font-size: 12px; color: #64748b; }

        .body { padding: 24px 30px; }
        .section-title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 10px; }
        .earnings { color: #059669; }
        .deductions { color: #dc2626; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .earnings-table td, .deductions-table td { padding: 5px 0; font-size: 13px; border: none; }
        .earnings-table .total td, .deductions-table .total td { border-top: 1.5px solid #e2e8f0; padding-top: 8px; font-weight: 700; font-size: 14px; }
        .text-right { text-align: right; }
        .text-success-dark { color: #059669; }
        .text-danger-dark { color: #dc2626; }

        .columns { display: flex; gap: 24px; }
        .col-half { flex: 1; }

        .net-pay-box { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border: 2px solid #10b981; border-radius: 10px; text-align: center; padding: 22px; margin: 20px 0; }
        .net-pay-box .label { font-size: 11px; text-transform: uppercase; letter-spacing: .1em; color: #64748b; font-weight: 600; margin-bottom: 6px; }
        .net-pay-box .amount { font-size: 36px; font-weight: 800; color: #059669; }

        .footer { padding: 16px 30px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; font-size: 11px; color: #94a3b8; background: #f8fafc; border-radius: 0 0 12px 12px; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 99px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .badge-success { background: #d1fae5; color: #059669; }
        .badge-warning { background: #fef3c7; color: #d97706; }

        @media print {
            body { margin: 0; }
            .page { max-width: none; padding: 20px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<div class="page">

    {{-- Print / Close buttons --}}
    <div class="no-print" style="text-align:right;margin-bottom:16px;">
        <button onclick="window.print()" style="background:#4f46e5;color:#fff;border:none;padding:8px 20px;border-radius:8px;cursor:pointer;font-weight:600;margin-right:8px;">🖨 Print / Save PDF</button>
        <button onclick="window.close()" style="background:#f1f5f9;color:#334155;border:none;padding:8px 20px;border-radius:8px;cursor:pointer;font-weight:600;">✕ Close</button>
    </div>

    <div style="border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
        {{-- Header --}}
        <div class="header">
            <div class="header-row">
                <div>
                    <h2>PAYSLIP</h2>
                    <div class="sub">
                        @php
                            if ($payslip->pay_period_start) {
                                echo \Carbon\Carbon::parse($payslip->pay_period_start)->format('F Y');
                            } elseif (isset($payslip->period_month)) {
                                echo date('F', mktime(0,0,0,$payslip->period_month,1)) . ' ' . $payslip->period_year;
                            } else {
                                echo $payslip->created_at?->format('F Y') ?? now()->format('F Y');
                            }
                        @endphp
                    </div>
                </div>
                <div class="header-right">
                    <div class="company">{{ config('app.name') }}</div>
                    <div class="sub">Payslip Ref: PS-{{ str_pad($payslip->id, 6, '0', STR_PAD_LEFT) }}</div>
                    <div class="sub">Generated: {{ $payslip->created_at?->format('d M Y') }}</div>
                </div>
            </div>
        </div>

        {{-- Employee Info --}}
        <div class="employee-bar">
            <div>
                <div class="emp-label">Employee</div>
                <div class="emp-val">{{ $payslip->user?->name ?? 'Employee #'.$payslip->user_id }}</div>
                <div class="emp-sub">ID: {{ $payslip->user?->employee_id ?? $payslip->user_id }} | {{ $payslip->user?->email ?? '' }}</div>
            </div>
            <div style="text-align:right">
                <div class="emp-label">Department</div>
                <div class="emp-val" style="font-size:14px;">{{ $payslip->user?->department ?? '—' }}</div>
                <div class="emp-sub">{{ $payslip->user?->designation ?? $payslip->user?->job_title ?? '—' }}</div>
            </div>
        </div>

        {{-- Earnings & Deductions --}}
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

        <div class="body">
            <div class="columns">
                {{-- Earnings --}}
                <div class="col-half">
                    <div class="section-title earnings">+ Earnings</div>
                    <table class="earnings-table">
                        @foreach($earnings as $label => $amount)
                            @if((float)$amount > 0)
                                <tr><td>{{ $label }}</td><td class="text-right">₹{{ number_format((float)$amount, 2) }}</td></tr>
                            @endif
                        @endforeach
                        <tr class="total">
                            <td class="text-success-dark">Gross Earnings</td>
                            <td class="text-right text-success-dark">₹{{ number_format($gross, 2) }}</td>
                        </tr>
                    </table>
                </div>

                {{-- Deductions --}}
                <div class="col-half">
                    <div class="section-title deductions">− Deductions</div>
                    <table class="deductions-table">
                        @foreach($deductions as $label => $amount)
                            @if((float)$amount > 0)
                                <tr><td>{{ $label }}</td><td class="text-right text-danger-dark">₹{{ number_format((float)$amount, 2) }}</td></tr>
                            @endif
                        @endforeach
                        <tr class="total">
                            <td class="text-danger-dark">Total Deductions</td>
                            <td class="text-right text-danger-dark">₹{{ number_format($totalDed, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Net Pay --}}
            <div class="net-pay-box">
                <div class="label">Net Pay</div>
                <div class="amount">₹{{ number_format($netPay, 2) }}</div>
            </div>

            @if($payslip->notes)
                <div style="background:#f1f5f9;border-radius:8px;padding:12px 16px;font-size:12px;color:#64748b;">
                    <strong>Note:</strong> {{ $payslip->notes }}
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="footer">
            <div>This is a system-generated payslip. No signature required.</div>
            <div>{{ config('app.name') }} | {{ now()->format('d M Y') }}</div>
        </div>
    </div>
</div>
</body>
</html>
