@php
    $payslip = $payslip ?? ($samplePayslip ?? null);
    $historyObj = $history ?? ($payslip?->payrollHistory ?? (($payslip?->payroll_id && $payslip?->user_id) ? \App\Models\PayrollHistory::where('payroll_id', $payslip->payroll_id)->where('user_id', $payslip->user_id)->first() : null));
    $u = $user ?? ($payslip?->user ?? ($historyObj?->user ?? null));
    $emp = $u?->employeeDetail ?? $u?->employeeDetails ?? null;
    $payroll = $payslip?->payroll ?? ($historyObj?->payroll ?? null);

    $monthNum = (int)($payroll?->metadata['month'] ?? ($historyObj?->month ?? ($payroll?->month ?? (int)date('n', strtotime($payroll?->period_start ?? 'now')))));
    $yearNum = (int)($payroll?->metadata['year'] ?? ($historyObj?->year ?? ($payroll?->year ?? (int)date('Y', strtotime($payroll?->period_start ?? 'now')))));
    $monthName = date('F', mktime(0, 0, 0, $monthNum, 1));
    $pStatus = strtolower($payroll?->status ?? 'approved');

    $snap = $snap ?? ($payslip?->employee_snapshot ?? ($historyObj?->snapshot ?? []));
    $b = is_array($snap) ? $snap : (is_string($snap) ? json_decode($snap, true) : []);

    $companyName = $company?->name ?? config('app.name', 'PMS Company');
    $companyAddress = $company?->address ?? 'Corporate Headquarters';
    $branchName = $emp?->branch?->name ?? 'HQ';
    $payslipNumber = $payslip?->payslip_number ?? ('PAY-' . $yearNum . '-' . str_pad($monthNum, 2, '0', STR_PAD_LEFT) . '-' . str_pad($historyObj?->id ?? 1, 3, '0', STR_PAD_LEFT));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip - {{ $u?->name ?? 'Employee' }} - {{ $monthName }} {{ $yearNum }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
            padding: 24px;
            background: #fff;
        }
        .payslip-wrap {
            max-width: 780px;
            margin: 0 auto;
            border: 1px solid #e2e8f0;
            padding: 24px;
            border-radius: 8px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header .logo {
            display: inline-block;
            width: 44px;
            height: 44px;
            background: #2563eb;
            color: #fff;
            border-radius: 8px;
            font-size: 20px;
            font-weight: bold;
            line-height: 44px;
            text-align: center;
            margin-bottom: 6px;
        }
        .header h1 {
            font-size: 18px;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .header p {
            color: #64748b;
            font-size: 10px;
            margin: 1px 0;
        }
        .header .pay-badge {
            display: inline-block;
            background: #2563eb;
            color: #fff;
            padding: 3px 12px;
            border-radius: 12px;
            font-size: 10.5px;
            font-weight: bold;
            margin-top: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        table.two-col {
            width: 100%;
            margin-bottom: 14px;
        }
        table.two-col td {
            vertical-align: top;
            width: 50%;
            padding: 0 6px;
        }
        .section-title {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .detail-row {
            display: block;
            margin-bottom: 3px;
        }
        .detail-row .k {
            color: #64748b;
            display: inline-block;
            width: 90px;
        }
        .detail-row .v {
            font-weight: bold;
            color: #0f172a;
        }
        .table-data {
            border: 1px solid #e2e8f0;
        }
        .table-data th {
            background: #f1f5f9;
            color: #334155;
            font-weight: bold;
            font-size: 10px;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        .table-data td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            font-size: 10.5px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bg-subtle { background: #f8fafc; font-weight: bold; }
        .final-strip {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 12px;
            display: table;
            width: 100%;
        }
        .final-strip .box {
            display: table-cell;
            width: 50%;
            text-align: center;
        }
        .final-strip .lbl {
            font-size: 9.5px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }
        .final-strip .val {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
            margin-top: 2px;
        }
        .footer-note {
            text-align: center;
            font-size: 9.5px;
            color: #94a3b8;
            border-top: 1px dashed #cbd5e1;
            padding-top: 8px;
            margin-top: 12px;
        }
        @media print {
            body { padding: 0; background: #fff; }
            .payslip-wrap { border: none; max-width: 100%; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="payslip-wrap">
    <div class="header">
        <div class="logo">{{ strtoupper(substr($companyName, 0, 1)) }}</div>
        <h1>{{ $companyName }}</h1>
        <p>{{ $companyAddress }}</p>
        <p>Branch: {{ $branchName }}</p>
        <div class="pay-badge">PAYSLIP — {{ $monthName }} {{ $yearNum }}</div>
    </div>

    <!-- Employee & Payroll Details -->
    <table class="two-col">
        <tr>
            <td>
                <div class="section-title">Employee Details</div>
                <div class="detail-row"><span class="k">Name:</span> <span class="v">{{ $u->name ?? 'N/A' }}</span></div>
                <div class="detail-row"><span class="k">Employee ID:</span> <span class="v">{{ $emp?->employee_id ?? ('EMP' . ($u?->id ?? '')) }}</span></div>
                <div class="detail-row"><span class="k">Designation:</span> <span class="v">{{ $emp?->designation?->name ?? '—' }}</span></div>
                <div class="detail-row"><span class="k">Department:</span> <span class="v">{{ $emp?->department?->name ?? '—' }}</span></div>
                <div class="detail-row"><span class="k">Grade:</span> <span class="v">{{ $emp?->grade ?? ($b['grade'] ?? '—') }}</span></div>
                <div class="detail-row"><span class="k">Joining Date:</span> <span class="v">{{ $emp?->joining_date ? date('d M Y', strtotime($emp->joining_date)) : '—' }}</span></div>
            </td>
            <td>
                <div class="section-title">Payroll Details</div>
                <div class="detail-row"><span class="k">Payroll Month:</span> <span class="v">{{ $monthName }} {{ $yearNum }}</span></div>
                <div class="detail-row"><span class="k">Pay Date:</span> <span class="v">{{ $payroll?->finalized_at ? date('d M Y', strtotime($payroll->finalized_at)) : date('t M Y', mktime(0, 0, 0, $monthNum, 1, $yearNum)) }}</span></div>
                <div class="detail-row"><span class="k">Payroll ID:</span> <span class="v">{{ $payslipNumber }}</span></div>
                <div class="detail-row"><span class="k">Status:</span> <span class="v">{{ ucfirst($pStatus) }}</span></div>
                <div class="detail-row"><span class="k">Branch:</span> <span class="v">{{ $branchName }}</span></div>
            </td>
        </tr>
    </table>

    <!-- Attendance Summary -->
    <table class="table-data">
        <thead>
            <tr>
                <th colspan="4" class="text-center" style="background:#e2e8f0;">ATTENDANCE SUMMARY</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Working Days: <b>{{ $b['working_days'] ?? ($historyObj->total_working_days ?? 0) }}</b></td>
                <td>Present Days: <b>{{ $b['presents'] ?? ($historyObj->present_days ?? 0) }}</b></td>
                <td>Leave Days: <b>{{ $b['total_leave'] ?? ($historyObj->leave_days ?? 0) }}</b></td>
                <td>Absent Days: <b>{{ $b['total_absent'] ?? ($historyObj->absent_days ?? 0) }}</b></td>
            </tr>
            <tr>
                <td>Half Days: <b>{{ $b['half_days'] ?? ($historyObj->half_days ?? 0) }}</b></td>
                <td>WFH Days: <b>{{ $b['wfh_days'] ?? ($historyObj->wfh_days ?? 0) }}</b></td>
                <td>Working Hours: <b>{{ $b['actual_working_hours'] ?? ($historyObj->actual_working_hours ?? 0) }}</b></td>
                <td>Overtime: <b>{{ $b['overtime_hours'] ?? ($historyObj->overtime_hours ?? 0) }} hrs</b></td>
                </tbody>
    </table>

    <!-- Earnings & Deductions -->
    <table class="two-col" style="margin-bottom:0;">
        <tr>
            <td>
                <table class="table-data">
                    <thead>
                        <tr>
                            <th>EARNINGS</th>
                            <th class="text-right">AMOUNT (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Basic Salary</td><td class="text-right">{{ number_format((float)($b['standard_basic'] ?? $b['basic'] ?? $historyObj->basic_salary ?? 0), 2) }}</td></tr>
                        <tr><td>Current Basic</td><td class="text-right">{{ number_format((float)($b['current_basic'] ?? $b['ac_basic'] ?? $historyObj->basic_salary ?? 0), 2) }}</td></tr>
                        <tr><td>HRA</td><td class="text-right">{{ number_format((float)($b['current_hra'] ?? $b['ac_hra'] ?? $historyObj->hra ?? 0), 2) }}</td></tr>
                        <tr><td>Special Allowance</td><td class="text-right">{{ number_format((float)($b['current_special'] ?? $b['ac_special'] ?? $historyObj->special_allowance ?? 0), 2) }}</td></tr>
                        <tr><td>Other Allowances</td><td class="text-right">{{ number_format((float)($b['other_allowances'] ?? $historyObj->allowances ?? 0), 2) }}</td></tr>
                        <tr class="bg-subtle"><td><b>Gross Salary</b></td><td class="text-right"><b>{{ number_format((float)($b['gross_salary'] ?? $b['ac_gross'] ?? $historyObj->gross_salary ?? 0), 2) }}</b></td></tr>
                    </tbody>
                </table>
            </td>
            <td>
                <table class="table-data">
                    <thead>
                        <tr>
                            <th>DEDUCTIONS</th>
                            <th class="text-right">AMOUNT (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>PF (Employee 12%)</td><td class="text-right">{{ number_format((float)($b['pf'] ?? $historyObj->pf_employee ?? 0), 2) }}</td></tr>
                        <tr><td>ESI (Employee 0.75%)</td><td class="text-right">{{ number_format((float)($b['esi'] ?? $historyObj->esi_employee ?? 0), 2) }}</td></tr>
                        <tr><td>Other Deductions / TDS</td><td class="text-right">{{ number_format((float)(($b['other_deductions'] ?? 0) + ($b['tds'] ?? 0) + ($b['pt'] ?? 0) ?: ($historyObj->deductions ?? 0)), 2) }}</td></tr>
                        <tr class="bg-subtle"><td><b>Total Deductions</b></td><td class="text-right"><b>{{ number_format((float)($b['total_deductions'] ?? $historyObj->total_deductions ?? 0), 2) }}</b></td></tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- Additional Earnings -->
    <table class="table-data">
        <thead>
            <tr>
                <th>ADDITIONAL EARNINGS & BONUSES</th>
                <th class="text-right">AMOUNT (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Attendance Bonus (>180h: ₹400, >162h: ₹200)</td><td class="text-right">{{ number_format((float)($b['attendance_bonus'] ?? $historyObj->attendance_bonus ?? 0), 2) }}</td></tr>
            <tr><td>Best Employee Bonus</td><td class="text-right">{{ number_format((float)($b['best_employee_bonus'] ?? $historyObj->best_employee_bonus ?? 0), 2) }}</td></tr>
            <tr><td>Travel Allowance (TA)</td><td class="text-right">{{ number_format((float)($b['ta'] ?? $historyObj->ta ?? 0), 2) }}</td></tr>
            <tr><td>Overtime Amount</td><td class="text-right">{{ number_format((float)($b['overtime'] ?? $historyObj->overtime_amount ?? 0), 2) }}</td></tr>
            <tr><td>Commission</td><td class="text-right">{{ number_format((float)($b['commission'] ?? $historyObj->commission ?? 0), 2) }}</td></tr>
            <tr><td>Salary Adjustments</td><td class="text-right">{{ number_format((float)($b['adjustment'] ?? $historyObj->adjustment ?? 0), 2) }}</td></tr>
            <tr class="bg-subtle"><td><b>Total Additional Earnings</b></td><td class="text-right"><b>{{ number_format((float)($b['additional_earnings'] ?? $historyObj->additional_earnings ?? 0), 2) }}</b></td></tr>
        </tbody>
    </table>

    <!-- Final Strip -->
    <div class="final-strip">
        <div class="box">
            <div class="lbl">Net Pay (Gross − Deductions)</div>
            <div class="val">₹{{ number_format((float)($b['net_pay'] ?? $historyObj->net_salary ?? 0), 2) }}</div>
        </div>
        <div class="box">
            <div class="lbl">Total In Hand (Net + Addl)</div>
            <div class="val" style="color:#059669;">₹{{ number_format((float)($b['total_in_hand'] ?? $historyObj->total_in_hand ?? 0), 2) }}</div>
        </div>
    </div>

    <!-- Employer Contributions & CTC -->
    <table class="table-data">
        <thead>
            <tr>
                <th>EMPLOYER CONTRIBUTIONS</th>
                <th class="text-right">AMOUNT (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Employer PF (12%)</td><td class="text-right">{{ number_format((float)($b['employer_pf'] ?? $historyObj->pf_employer ?? 0), 2) }}</td></tr>
            <tr><td>Employer ESI (3.25%)</td><td class="text-right">{{ number_format((float)($b['employer_esi'] ?? $historyObj->esi_employer ?? 0), 2) }}</td></tr>
            <tr><td>EDLI (0.5%)</td><td class="text-right">{{ number_format((float)($b['edli'] ?? $historyObj->edli ?? 0), 2) }}</td></tr>
            <tr class="bg-subtle"><td><b>Total Cost to Company (CTC)</b></td><td class="text-right" style="color:#7c3aed;"><b>{{ number_format((float)($b['ctc'] ?? $historyObj->ctc ?? 0), 2) }}</b></td></tr>
        </tbody>
    </table>

    <div class="footer-note">
        This is a computer-generated payslip. No signature required. Generated on {{ date('d M Y, h:i A') }}
    </div>
</div>

<script>
    // Auto-trigger print dialog if accessed directly via browser for printing
    if (window.location.search.indexOf('noprint') === -1 && window.location.pathname.indexOf('/print') !== -1) {
        window.addEventListener('load', function() {
            setTimeout(function() { window.print(); }, 400);
        });
    }
</script>

</body>
</html>
