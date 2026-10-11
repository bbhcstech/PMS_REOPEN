@php
    // One payslip document for the on-screen view (embedded), print and PDF download.
    // Layout uses tables only so dompdf renders it exactly like the browser does.
    $isPdf = ($mode ?? 'screen') === 'pdf';
    $payslip = $payslip ?? ($samplePayslip ?? null);
    $historyObj = $history ?? ($payslip?->payrollHistory ?? (($payslip?->payroll_id && $payslip?->user_id) ? \App\Models\PayrollHistory::where('payroll_id', $payslip->payroll_id)->where('user_id', $payslip->user_id)->first() : null));
    $u = $user ?? ($payslip?->user ?? ($historyObj?->user ?? null));
    $emp = $u?->employeeDetail ?? null;
    $payroll = $payslip?->payroll ?? ($historyObj?->payroll ?? null);

    $periodStart = \Carbon\Carbon::parse($payroll?->period_start ?? now()->startOfMonth());
    $monthNum = (int) ($payroll?->metadata['month'] ?? $periodStart->month);
    $yearNum = (int) ($payroll?->metadata['year'] ?? $periodStart->year);
    $period = \Carbon\Carbon::createFromDate($yearNum, $monthNum, 1);
    $monthName = $period->format('F');
    $pStatus = strtolower($payroll?->status ?? 'approved');

    $snap = $snap ?? ($payslip?->employee_snapshot ?? ($historyObj?->snapshot ?? []));
    $b = is_array($snap) ? $snap : (is_string($snap) ? (json_decode($snap, true) ?: []) : []);
    $n = fn ($key, $default = 0) => (float) ($b[$key] ?? $default);
    $money = fn ($v) => '₹' . number_format((float) $v, 2);
    $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');

    // Company branding (tenant company first, then legacy company settings).
    $companyName = $company?->name ?? $company?->company_name ?? config('app.name', 'Company');
    $companyAddress = $company?->address ?? $company?->company_address ?? null;
    $companyContact = collect([$company?->email ?? null, $company?->phone ?? null, $company?->website ?? null])->filter()->implode('  ·  ');
    $companyIds = collect([
        ($company?->gst_number ?? null) ? 'GSTIN: ' . $company->gst_number : null,
        ($company?->pan_number ?? null) ? 'PAN: ' . $company->pan_number : null,
    ])->filter()->implode('  ·  ');
    $logoSrc = null;
    $logoPath = $company?->logo ?? null;
    if ($logoPath) {
        $file = collect([public_path($logoPath), public_path('storage/' . ltrim($logoPath, '/')), storage_path('app/public/' . ltrim($logoPath, '/'))])
            ->first(fn ($p) => is_file($p));
        if ($file && ($mime = @mime_content_type($file)) && str_starts_with($mime, 'image/') && $mime !== 'image/svg+xml') {
            $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file));
        }
    }
    $initials = collect(preg_split('/\s+/', trim($companyName)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

    // Employee details: snapshot values are what was paid, live record fills the gaps.
    $empName = $u?->name ?? ($b['employee_name'] ?? 'Employee');
    $empCode = $b['employee_id'] ?? ($emp?->employee_id ?? ('EMP' . ($u?->id ?? '')));
    $designation = ($b['designation'] ?? '-') !== '-' ? $b['designation'] : ($emp?->designation?->name ?? '—');
    $department = ($b['department'] ?? '-') !== '-' ? $b['department'] : ($emp?->department?->dpt_name ?? $emp?->department?->name ?? '—');
    $branchName = ($b['branch'] ?? '-') !== '-' ? $b['branch'] : ($emp?->business_address ?? '—');
    $grade = $b['grade'] ?? '—';
    $joining = ($b['joining_date'] ?? '-') !== '-' ? $b['joining_date'] : ($emp?->joining_date ? \Carbon\Carbon::parse($emp->joining_date)->format('d M Y') : '—');
    $payslipNumber = $payslip?->payslip_number ?? ('PS-' . $period->format('Ym') . '-' . str_pad((string) ($historyObj?->id ?? 1), 4, '0', STR_PAD_LEFT));
    $payDate = $payroll?->pay_date ? \Carbon\Carbon::parse($payroll->pay_date) : $period->copy()->endOfMonth();

    // Attendance & leave, straight from the payroll run's attendance/leave calculation.
    $workingDays = $n('working_days');
    $payableDays = array_key_exists('payable_days', $b) ? $n('payable_days') : $workingDays;
    $attendance = [
        ['Working Days', $qty($workingDays)],
        ['Payable Days', $qty($payableDays)],
        ['Present', $qty($n('presents', $b['present'] ?? 0))],
        ['Paid Leave', $qty($n('paid_leave'))],
        ['Unpaid Leave', $qty($n('unpaid_leave'))],
        ['Absent', $qty($n('total_absent', $b['absent'] ?? 0))],
        ['Half Days', $qty($n('half_day', $b['half_absent'] ?? 0))],
        ['WFH', $qty($n('wfh'))],
        ['Hours Worked', $qty($n('working_hours')) . ' h'],
        ['Overtime', $qty($n('overtime_hours')) . ' h'],
    ];
    $hasLeaveBalance = array_key_exists('initial_leave_balance', $b) || array_key_exists('current_leave_balance', $b);

    // Earnings: full-month rate vs amount earned for the payable days.
    $earnings = [
        ['Basic Salary', $n('basic'), $n('current_basic', $b['ac_basic'] ?? 0)],
        ['House Rent Allowance', $n('hra'), $n('current_hra', $b['ac_hra'] ?? 0)],
        ['Special Allowance', $n('special'), $n('current_special', $b['ac_special'] ?? 0)],
    ];
    if ($n('other_allowances') > 0) {
        $earnings[] = ['Other Allowances', $n('other_allowances'), $n('other_allowances')];
    }
    $gross = $n('gross_salary', $b['ac_gross'] ?? ($payslip?->gross_salary ?? 0));
    $earnedSum = array_sum(array_column($earnings, 2));
    if (abs($gross - $earnedSum) >= 0.01) {
        $earnings[] = ['Other Earnings', null, round($gross - $earnedSum, 2)];
    }
    $positiveAdjustment = array_key_exists('positive_adjustment', $b) ? $n('positive_adjustment') : max(0, $n('adjustment'));
    $negativeAdjustment = array_key_exists('negative_adjustment', $b) ? $n('negative_adjustment') : max(0, -$n('adjustment'));
    $additional = array_filter([
        ['Attendance Bonus', $n('attendance_bonus')],
        ['Best Employee Bonus', $n('best_employee_bonus')],
        ['Travel Allowance', $n('ta')],
        ['Overtime Pay (' . $qty($n('overtime_hours')) . ' h)', $n('overtime')],
        ['Commission', $n('commission')],
        ['Salary Adjustment', $positiveAdjustment],
    ], fn ($row) => abs($row[1]) >= 0.01);
    $additionalTotal = $n('total_additional_earnings', $b['additional_earnings'] ?? 0) + $negativeAdjustment;
    $additionalSum = array_sum(array_column($additional, 1));
    if (abs($additionalTotal - $additionalSum) >= 0.01) {
        $additional[] = ['Other Additions', round($additionalTotal - $additionalSum, 2)];
        $additionalSum = $additionalTotal;
    }
    foreach ($additional as [$label, $amount]) {
        $earnings[] = [$label, null, $amount];
    }
    $totalEarnings = round($gross + $additionalSum, 2);

    // Deductions, labelled as generated on the payslip (falls back to the snapshot).
    $deductions = [];
    $storedDeductions = is_array($payslip?->deductions ?? null) ? $payslip->deductions : [];
    if ($storedDeductions) {
        foreach ($storedDeductions as $label => $amount) {
            $deductions[] = [$label, (float) $amount];
        }
    } else {
        foreach ([['Provident Fund (PF)', 'pf'], ['Employee State Insurance (ESI)', 'esi'], ['Professional Tax', 'pt'], ['Income Tax (TDS)', 'tds'], ['Other Deductions', 'other_deductions']] as [$label, $key]) {
            if ($n($key) > 0) {
                $deductions[] = [$label, $n($key)];
            }
        }
    }
    $statutoryDeductions = $n('total_deductions', $b['total_deduction'] ?? ($payslip?->total_deductions ?? 0));
    $deductionSum = array_sum(array_column($deductions, 1));
    if (abs($statutoryDeductions - $deductionSum) >= 0.01) {
        $deductions[] = ['Other Deductions', round($statutoryDeductions - $deductionSum, 2)];
    }
    if ($negativeAdjustment >= 0.01) {
        $deductions[] = ['Salary Adjustment (Recovery)', $negativeAdjustment];
    }
    $totalDeductions = round($statutoryDeductions + $negativeAdjustment, 2);

    $netPay = $n('total_in_hand', $b['net_pay'] ?? ($payslip?->net_salary ?? 0));
    $netInWords = \App\Support\AmountInWords::rupees($netPay);

    $employer = array_filter([
        ['Employer PF', $n('employer_pf', $b['cpf'] ?? 0)],
        ['Employer ESI', $n('employer_esi', $b['cesi'] ?? 0)],
        ['EDLI', $n('edli')],
        ['Other Employer Contribution', $n('other_employer_contribution')],
    ], fn ($row) => $row[1] >= 0.01);

    $statusStyles = [
        'finalized' => ['#065f46', '#d1fae5', 'Finalized'],
        'approved' => ['#1e40af', '#dbeafe', 'Approved'],
        'reviewed' => ['#92400e', '#fef3c7', 'Reviewed'],
    ];
    [$statusInk, $statusBg, $statusLabel] = $statusStyles[$pStatus] ?? ['#475569', '#e2e8f0', ucfirst($pStatus)];
    $rows = max(count($earnings), count($deductions));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payslip — {{ $empName }} — {{ $monthName }} {{ $yearNum }}</title>
    <style>
        @page { size: A4 portrait; margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: 'DejaVu Sans', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1f2937;
            font-size: 10.5px;
            line-height: 1.45;
            background: #ffffff;
        }
        body.screen { background: #e9edf5; padding: 28px 16px; }
        .sheet { max-width: 820px; margin: 0 auto; background: #ffffff; }
        body.screen .sheet { padding: 34px 38px; border-radius: 14px; box-shadow: 0 18px 50px rgba(15, 23, 42, 0.14); }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; padding: 0; }
        .accent { height: 5px; background: #4338ca; border-radius: 3px; margin-bottom: 18px; }

        /* Header */
        .brand td { vertical-align: middle; }
        .logo-img { max-height: 54px; max-width: 150px; }
        .logo-badge {
            width: 52px; height: 52px; border-radius: 12px; background: #4338ca; color: #ffffff;
            font-size: 19px; font-weight: bold; text-align: center; line-height: 52px; letter-spacing: 0.5px;
        }
        .co-name { font-size: 19px; font-weight: bold; color: #111827; margin: 0 0 2px; letter-spacing: -0.2px; }
        .co-line { color: #6b7280; font-size: 9.5px; margin: 0; }
        .doc-title { text-align: right; }
        .doc-title .label { font-size: 9px; letter-spacing: 2.5px; color: #6b7280; font-weight: bold; text-transform: uppercase; }
        .doc-title .month { font-size: 17px; font-weight: bold; color: #111827; margin: 2px 0 6px; }
        .status {
            display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 9px; font-weight: bold;
            letter-spacing: 0.6px; text-transform: uppercase;
        }
        .rule { border-top: 1px solid #e5e7eb; margin: 16px 0; }

        /* Net pay hero */
        .hero { background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 12px; }
        .hero td { padding: 14px 18px; vertical-align: middle; }
        .hero .k { font-size: 9px; letter-spacing: 1.4px; text-transform: uppercase; color: #4338ca; font-weight: bold; }
        .hero .net { font-size: 26px; font-weight: bold; color: #1e1b4b; margin: 2px 0; letter-spacing: -0.5px; }
        .hero .words { font-size: 9.5px; color: #4b5563; font-style: italic; }
        .hero .mini { text-align: right; font-size: 9.5px; color: #4b5563; }
        .hero .mini b { color: #111827; font-size: 11px; white-space: nowrap; }

        /* Details */
        .section { font-size: 9px; letter-spacing: 1.4px; text-transform: uppercase; color: #4338ca; font-weight: bold; margin: 18px 0 8px; }
        .details td.col { width: 50%; }
        .kv td { padding: 4px 0; border-bottom: 1px dashed #eceff4; }
        .kv td.k { color: #6b7280; width: 42%; }
        .kv td.v { color: #111827; font-weight: bold; }
        .details td.gap { width: 24px; }

        /* Attendance tiles */
        .tiles td { width: 10%; padding: 0 2px; }
        .tile { border: 1px solid #e5e7eb; border-radius: 8px; padding: 7px 4px; text-align: center; background: #f9fafb; }
        .tile .t-v { font-size: 13px; font-weight: bold; color: #111827; }
        .tile .t-k { font-size: 7.5px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.2px; margin-top: 1px; white-space: nowrap; }
        .leave-line { margin-top: 8px; font-size: 9.5px; color: #4b5563; }
        .leave-line b { color: #111827; }

        /* Money tables */
        .money { border: 1px solid #e5e7eb; border-radius: 10px; }
        .money th {
            background: #f3f4f6; color: #374151; font-size: 9px; text-transform: uppercase; letter-spacing: 0.8px;
            padding: 8px 10px; text-align: left; border-bottom: 1px solid #e5e7eb;
        }
        .money td { padding: 6px 10px; border-bottom: 1px solid #f1f2f5; color: #1f2937; }
        .money .r { text-align: right; white-space: nowrap; }
        .money .muted { color: #9ca3af; }
        .money tr.total td { background: #f9fafb; font-weight: bold; color: #111827; border-bottom: 0; border-top: 1px solid #e5e7eb; }
        .money td.split { border-left: 1px solid #e5e7eb; }

        .summary td { padding: 10px 14px; border: 1px solid #e5e7eb; text-align: center; }
        .summary .s-k { font-size: 8.5px; letter-spacing: 1px; text-transform: uppercase; color: #6b7280; font-weight: bold; }
        .summary .s-v { font-size: 15px; font-weight: bold; color: #111827; margin-top: 2px; }
        .summary td.net { background: #4338ca; border-color: #4338ca; }
        .summary td.net .s-k { color: #c7d2fe; }
        .summary td.net .s-v { color: #ffffff; }

        .employer td { padding: 5px 0; color: #4b5563; font-size: 9.5px; border-bottom: 1px dashed #eceff4; }
        .employer td.r { text-align: right; color: #111827; }
        .employer tr.ctc td { font-weight: bold; color: #111827; border-bottom: 0; font-size: 10.5px; }

        .foot { margin-top: 22px; padding-top: 10px; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 8.5px; }
        .foot td.r { text-align: right; }

        @media print {
            body.screen { background: #ffffff; padding: 0; }
            body.screen .sheet { box-shadow: none; padding: 0; border-radius: 0; max-width: 100%; }
        }
    </style>
</head>
<body class="{{ $isPdf ? 'pdf' : 'screen' }}">
<div class="sheet">
    <div class="accent"></div>

    {{-- Company header --}}
    <table class="brand">
        <tr>
            <td style="width:66px;">
                @if($logoSrc)
                    <img class="logo-img" src="{{ $logoSrc }}" alt="{{ $companyName }}">
                @else
                    <div class="logo-badge">{{ $initials ?: 'C' }}</div>
                @endif
            </td>
            <td style="padding-left:12px;">
                <p class="co-name">{{ $companyName }}</p>
                @if($companyAddress)<p class="co-line">{{ $companyAddress }}</p>@endif
                @if($companyContact)<p class="co-line">{{ $companyContact }}</p>@endif
                @if($companyIds)<p class="co-line">{{ $companyIds }}</p>@endif
            </td>
            <td class="doc-title" style="width:190px;">
                <div class="label">Payslip</div>
                <div class="month">{{ $monthName }} {{ $yearNum }}</div>
                <span class="status" style="color:{{ $statusInk }};background:{{ $statusBg }};">{{ $statusLabel }}</span>
            </td>
        </tr>
    </table>

    <div class="rule"></div>

    {{-- Net pay --}}
    <table class="hero">
        <tr>
            <td>
                <div class="k">Net Pay for {{ $monthName }} {{ $yearNum }}</div>
                <div class="net">{{ $money($netPay) }}</div>
                <div class="words">{{ $netInWords }}</div>
            </td>
            <td class="mini" style="width:250px;">
                Paid days&nbsp; <b>{{ $qty($payableDays) }} / {{ $qty($workingDays) }}</b><br>
                Pay date&nbsp; <b>{{ $payDate->format('d M Y') }}</b><br>
                Payslip no.&nbsp; <b>{{ $payslipNumber }}</b>
            </td>
        </tr>
    </table>

    {{-- Employee & pay period --}}
    <table class="details">
        <tr>
            <td class="col">
                <div class="section">Employee Details</div>
                <table class="kv">
                    <tr><td class="k">Employee Name</td><td class="v">{{ $empName }}</td></tr>
                    <tr><td class="k">Employee ID</td><td class="v">{{ $empCode }}</td></tr>
                    <tr><td class="k">Designation</td><td class="v">{{ $designation }}</td></tr>
                    <tr><td class="k">Department</td><td class="v">{{ $department }}</td></tr>
                    <tr><td class="k">Grade</td><td class="v">{{ $grade }}</td></tr>
                </table>
            </td>
            <td class="gap"></td>
            <td class="col">
                <div class="section">Pay Period</div>
                <table class="kv">
                    <tr><td class="k">Period</td><td class="v">{{ $period->format('d M') }} – {{ $period->copy()->endOfMonth()->format('d M Y') }}</td></tr>
                    <tr><td class="k">Branch / Office</td><td class="v">{{ $branchName }}</td></tr>
                    <tr><td class="k">Date of Joining</td><td class="v">{{ $joining }}</td></tr>
                    <tr><td class="k">Email</td><td class="v">{{ $u?->email ?? ($b['email'] ?? '—') }}</td></tr>
                    <tr><td class="k">Payroll Run</td><td class="v">#{{ $payroll?->id ?? '—' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Attendance & leave --}}
    <div class="section">Attendance &amp; Leave</div>
    <table class="tiles">
        <tr>
            @foreach($attendance as [$label, $value])
                <td><div class="tile"><div class="t-v">{{ $value }}</div><div class="t-k">{{ $label }}</div></div></td>
            @endforeach
        </tr>
    </table>
    @if($hasLeaveBalance)
        <div class="leave-line">
            Leave balance — opening <b>{{ $qty($n('initial_leave_balance')) }}</b>,
            taken this month <b>{{ $qty($n('total_leave')) }}</b>,
            closing <b>{{ $qty($n('current_leave_balance')) }}</b> days.
            @if($n('unpaid_leave') > 0)
                Unpaid leave reduces pay by {{ $qty($n('unpaid_salary_deduction_percent')) }}% (already reflected in earnings).
            @endif
        </div>
    @endif

    {{-- Earnings & deductions --}}
    <div class="section">Salary Breakdown</div>
    <table class="money">
        <thead>
            <tr>
                <th>Earnings</th>
                <th class="r">Full Month</th>
                <th class="r">Earned</th>
                <th class="split" style="border-left:1px solid #e5e7eb;">Deductions</th>
                <th class="r">Amount</th>
            </tr>
        </thead>
        <tbody>
            @for($i = 0; $i < $rows; $i++)
                @php($e = $earnings[$i] ?? null)
                @php($d = $deductions[$i] ?? null)
                <tr>
                    <td>{{ $e[0] ?? '' }}</td>
                    <td class="r {{ ($e && $e[1] === null) ? 'muted' : '' }}">{{ $e ? ($e[1] === null ? '—' : $money($e[1])) : '' }}</td>
                    <td class="r">{{ $e ? $money($e[2]) : '' }}</td>
                    <td class="split">{{ $d[0] ?? '' }}</td>
                    <td class="r">{{ $d ? $money($d[1]) : '' }}</td>
                </tr>
            @endfor
            @if($rows === 0)
                <tr><td colspan="5" class="muted" style="text-align:center;">No salary components recorded.</td></tr>
            @endif
            <tr class="total">
                <td>Total Earnings</td>
                <td class="r">{{ $money($n('gross')) }}</td>
                <td class="r">{{ $money($totalEarnings) }}</td>
                <td class="split">Total Deductions</td>
                <td class="r">{{ $money($totalDeductions) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="summary" style="margin-top:12px;">
        <tr>
            <td><div class="s-k">Gross Earnings</div><div class="s-v">{{ $money($totalEarnings) }}</div></td>
            <td><div class="s-k">Total Deductions</div><div class="s-v">{{ $money($totalDeductions) }}</div></td>
            <td class="net"><div class="s-k">Net Pay</div><div class="s-v">{{ $money($netPay) }}</div></td>
        </tr>
    </table>

    {{-- Employer contributions --}}
    @if($employer || $n('ctc') > 0)
        <div class="section">Employer Contributions (not part of take-home)</div>
        <table class="employer">
            @foreach($employer as [$label, $amount])
                <tr><td>{{ $label }}</td><td class="r">{{ $money($amount) }}</td></tr>
            @endforeach
            <tr class="ctc"><td>Cost to Company (this month)</td><td class="r">{{ $money($n('ctc')) }}</td></tr>
        </table>
    @endif

    <table class="foot">
        <tr>
            <td>This is a system-generated payslip from {{ $companyName }} and does not require a signature.</td>
            <td class="r">Generated {{ ($payslip?->updated_at ?? now())->format('d M Y, h:i A') }}</td>
        </tr>
    </table>
</div>

@unless($isPdf)
<script>
    // Opened through the Print route (not embedded): open the print dialog once loaded.
    if (window.self === window.top && window.location.search.indexOf('noprint') === -1 && window.location.pathname.indexOf('/print') !== -1) {
        window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 400); });
    }
</script>
@endunless
</body>
</html>
