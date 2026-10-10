@extends('admin.layout.app')

@section('title', 'Payslip Document — Admin Workspace')

@section('content')
@php
    $payslip = $payslip ?? null;
    $historyObj = $history ?? ($payslip?->payrollHistory ?? (($payslip?->payroll_id && $payslip?->user_id) ? \App\Models\PayrollHistory::where('payroll_id', $payslip->payroll_id)->where('user_id', $payslip->user_id)->first() : null));
    $u = $user ?? ($payslip?->user ?? ($historyObj?->user ?? null));
    $emp = $u?->employeeDetail ?? $u?->employeeDetails ?? null;
    $payroll = $payslip?->payroll ?? ($historyObj?->payroll ?? null);
    
    $monthNum = (int)($payroll?->metadata['month'] ?? ($historyObj?->month ?? ($payroll?->month ?? (int)date('n', strtotime($payroll?->period_start ?? 'now')))));
    $yearNum = (int)($payroll?->metadata['year'] ?? ($historyObj?->year ?? ($payroll?->year ?? (int)date('Y', strtotime($payroll?->period_start ?? 'now')))));
    $monthName = date('F', mktime(0, 0, 0, $monthNum, 1));
    $pStatus = strtolower($payroll?->status ?? 'approved');

    // Snapshot breakdown
    $snap = $snap ?? ($payslip?->employee_snapshot ?? ($historyObj?->snapshot ?? []));
    $b = is_array($snap) ? $snap : (is_string($snap) ? json_decode($snap, true) : []);

    $companyName = $company?->name ?? config('app.name', 'PMS Company');
    $companyAddress = $company?->address ?? 'Corporate Headquarters';
    $branchName = $emp?->branch?->name ?? 'HQ';
    $activeId = $payslip?->id ?? ($historyObj?->id ?? 1);
    $payslipNum = $payslip?->payslip_number ?? ('PAY-' . $yearNum . '-' . str_pad($monthNum, 2, '0', STR_PAD_LEFT) . '-' . str_pad($activeId, 3, '0', STR_PAD_LEFT));
@endphp

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        @include('admin.payroll.partials.styles')

        <div class="topbar-crumb mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Admin Workspace / Payroll /</span> Payslip Document</h4>
                <div class="crumb text-muted small">Official payroll statement for employee</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('payroll.payslips.index') }}" class="btn btn-outline-secondary btn-sm">
                    ← Back to Payslips
                </a>
                <a href="{{ route('payroll.payslips.print', $activeId) }}" target="_blank" class="btn btn-outline-dark btn-sm">
                    🖨 Print
                </a>
                <a href="{{ route('payroll.payslips.pdf', $activeId) }}" class="btn btn-primary btn-sm">
                    📥 Download PDF
                </a>
            </div>
        </div>

        <div class="payslip shadow-sm my-3">
            <div class="payslip-head">
                <div class="logo">
                    {{ strtoupper(substr($companyName, 0, 1)) }}
                </div>
                <h1>{{ $companyName }}</h1>
                <p>{{ $companyAddress }}</p>
                <p>Branch: {{ $branchName }}</p>
                <div class="pay-month">PAYSLIP — {{ $monthName }} {{ $yearNum }}</div>
            </div>

            <div class="grid2">
                <div>
                    <h4 style="font-size:11.5px;color:var(--muted);text-transform:uppercase;margin-bottom:8px">Employee Details</h4>
                    <div class="info-row"><span class="k">Name</span><span class="v">{{ $u?->name ?? 'N/A' }}</span></div>
                    <div class="info-row"><span class="k">Employee ID</span><span class="v">{{ $emp?->employee_id ?? ('EMP' . ($u?->id ?? '')) }}</span></div>
                    <div class="info-row"><span class="k">Designation</span><span class="v">{{ $emp?->designation?->name ?? '—' }}</span></div>
                    <div class="info-row"><span class="k">Department</span><span class="v">{{ $emp?->department?->name ?? '—' }}</span></div>
                    <div class="info-row"><span class="k">Branch</span><span class="v">{{ $branchName }}</span></div>
                    <div class="info-row"><span class="k">Grade</span><span class="v">{{ $emp?->grade ?? ($b['grade'] ?? '—') }}</span></div>
                    <div class="info-row"><span class="k">Joining Date</span><span class="v">{{ $emp?->joining_date ? date('d M Y', strtotime($emp->joining_date)) : '—' }}</span></div>
                </div>
                <div>
                    <h4 style="font-size:11.5px;color:var(--muted);text-transform:uppercase;margin-bottom:8px">Payroll Details</h4>
                    <div class="info-row"><span class="k">Payroll Month</span><span class="v">{{ $monthName }} {{ $yearNum }}</span></div>
                    <div class="info-row"><span class="k">Pay Date</span><span class="v">{{ ($payroll && $payroll->finalized_at) ? date('d M Y', strtotime($payroll->finalized_at)) : date('t M Y', mktime(0, 0, 0, $monthNum, 1, $yearNum)) }}</span></div>
                    <div class="info-row"><span class="k">Payroll ID</span><span class="v">{{ $payslipNum }}</span></div>
                    <div class="info-row"><span class="k">Status</span><span class="v">
                        @if($pStatus == 'finalized')
                            <span class="pill finalized">🔒 Finalized</span>
                        @elseif($pStatus == 'approved')
                            <span class="pill approved">✅ Approved</span>
                        @elseif($pStatus == 'reviewed')
                            <span class="pill reviewed">👁 Reviewed</span>
                        @else
                            <span class="pill draft">🟡 Draft</span>
                        @endif
                    </span></div>
                </div>
            </div>

            <table class="ps">
                <thead>
                    <tr><th colspan="4">Attendance Summary</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Working Days: <b>{{ $b['working_days'] ?? ($historyObj->total_working_days ?? 0) }}</b></td>
                        <td>Present: <b>{{ $b['presents'] ?? ($historyObj->present_days ?? 0) }}</b></td>
                        <td>Leave: <b>{{ $b['total_leave'] ?? ($historyObj->leave_days ?? 0) }}</b></td>
                        <td>Absent: <b>{{ $b['total_absent'] ?? ($historyObj->absent_days ?? 0) }}</b></td>
                    </tr>
                    @if(array_key_exists('unpaid_deduction_percentage', $b))
                    <tr>
                        <td>Paid leave: <b>{{ $b['paid_leave'] ?? 0 }}</b></td>
                        <td>Unpaid leave: <b>{{ $b['unpaid_leave'] ?? 0 }}</b></td>
                        <td>Rate per unpaid day: <b>{{ $b['unpaid_deduction_percentage'] }}% of monthly salary</b></td>
                        <td>Unpaid adjustment: <b>{{ $b['unpaid_salary_deduction_percent'] ?? 0 }}%</b> (included in earnings)</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Half Day: <b>{{ $b['half_days'] ?? ($historyObj->half_days ?? 0) }}</b></td>
                        <td>WFH: <b>{{ $b['wfh_days'] ?? ($historyObj->wfh_days ?? 0) }}</b></td>
                        <td>Working Hours: <b>{{ $b['actual_working_hours'] ?? ($historyObj->actual_working_hours ?? 0) }}</b></td>
                        <td>Overtime: <b>{{ $b['overtime_hours'] ?? ($historyObj->overtime_hours ?? 0) }} h</b></td>
                    </tr>
                </tbody>
            </table>

            <div class="grid2">
                <table class="ps">
                    <thead>
                        <tr>
                            <th>Earnings</th>
                            <th style="text-align:right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Basic Salary</td>
                            <td style="text-align:right">₹{{ number_format((float)($b['standard_basic'] ?? $b['basic'] ?? $historyObj->basic_salary ?? 0), 2) }}</td>
                        </tr>
                        <tr>
                            <td>Current Basic</td>
                            <td style="text-align:right">₹{{ number_format((float)($b['current_basic'] ?? $b['ac_basic'] ?? $historyObj->basic_salary ?? 0), 2) }}</td>
                        </tr>
                        <tr>
                            <td>HRA</td>
                            <td style="text-align:right">₹{{ number_format((float)($b['current_hra'] ?? $b['ac_hra'] ?? $historyObj->hra ?? 0), 2) }}</td>
                        </tr>
                        <tr>
                            <td>Special Allowance</td>
                            <td style="text-align:right">₹{{ number_format((float)($b['current_special'] ?? $b['ac_special'] ?? $historyObj->special_allowance ?? 0), 2) }}</td>
                        </tr>
                        <tr>
                            <td>Other Allowances</td>
                            <td style="text-align:right">₹{{ number_format((float)($b['other_allowances'] ?? $historyObj->allowances ?? 0), 2) }}</td>
                        </tr>
                        <tr style="background:#f8fafc">
                            <td><b>Gross Salary</b></td>
                            <td style="text-align:right"><b>₹{{ number_format((float)($b['gross_salary'] ?? $b['ac_gross'] ?? $historyObj->gross_salary ?? 0), 2) }}</b></td>
                        </tr>
                    </tbody>
                </table>

                <table class="ps">
                    <thead>
                        <tr>
                            <th>Deductions</th>
                            <th style="text-align:right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>PF (Employee 12%)</td>
                            <td style="text-align:right">₹{{ number_format((float)($b['pf'] ?? $historyObj->pf_employee ?? 0), 2) }}</td>
                        </tr>
                        <tr>
                            <td>ESI (Employee 0.75%)</td>
                            <td style="text-align:right">₹{{ number_format((float)($b['esi'] ?? $historyObj->esi_employee ?? 0), 2) }}</td>
                        </tr>
                        <tr>
                            <td>Other Deductions / TDS</td>
                            <td style="text-align:right">₹{{ number_format((float)(($b['other_deductions'] ?? 0) + ($b['tds'] ?? 0) + ($b['pt'] ?? 0) ?: ($historyObj->deductions ?? 0)), 2) }}</td>
                        </tr>
                        <tr style="background:#f8fafc">
                            <td><b>Total Deductions</b></td>
                            <td style="text-align:right"><b>₹{{ number_format((float)($b['total_deductions'] ?? $historyObj->total_deductions ?? 0), 2) }}</b></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <table class="ps">
                <thead>
                    <tr><th colspan="2">Additional Earnings</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Attendance Bonus</td>
                        <td style="text-align:right">₹{{ number_format((float)($b['attendance_bonus'] ?? $historyObj->attendance_bonus ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td>Best Employee Bonus</td>
                        <td style="text-align:right">₹{{ number_format((float)($b['best_employee_bonus'] ?? $historyObj->best_employee_bonus ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td>Travel Allowance (TA)</td>
                        <td style="text-align:right">₹{{ number_format((float)($b['ta'] ?? $historyObj->ta ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td>Overtime ({{ $b['overtime_hours'] ?? ($historyObj->overtime_hours ?? 0) }}h)</td>
                        <td style="text-align:right">₹{{ number_format((float)($b['overtime'] ?? $historyObj->overtime_amount ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td>Commission</td>
                        <td style="text-align:right">₹{{ number_format((float)($b['commission'] ?? $historyObj->commission ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td>Adjustments (+ / -)</td>
                        <td style="text-align:right">₹{{ number_format((float)($b['adjustment'] ?? $historyObj->adjustment ?? 0), 2) }}</td>
                    </tr>
                    <tr style="background:#f8fafc">
                        <td><b>Total Additional Earnings</b></td>
                        <td style="text-align:right"><b>₹{{ number_format((float)($b['additional_earnings'] ?? $historyObj->additional_earnings ?? 0), 2) }}</b></td>
                    </tr>
                </tbody>
            </table>

            <div class="final-strip">
                <div class="box">
                    <div class="lbl">Net Pay (Gross − Deductions)</div>
                    <div class="val">₹{{ number_format((float)($b['net_pay'] ?? $historyObj->net_salary ?? 0), 2) }}</div>
                </div>
                <div class="box">
                    <div class="lbl">Total In Hand (Net + Addl)</div>
                    <div class="val">₹{{ number_format((float)($b['total_in_hand'] ?? $historyObj->total_in_hand ?? 0), 2) }}</div>
                </div>
            </div>

            <table class="ps">
                <thead>
                    <tr><th colspan="2">Employer Contributions</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Employer PF (12%)</td>
                        <td style="text-align:right">₹{{ number_format((float)($b['employer_pf'] ?? $historyObj->pf_employer ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td>Employer ESI (3.25%)</td>
                        <td style="text-align:right">₹{{ number_format((float)($b['employer_esi'] ?? $historyObj->esi_employer ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td>EDLI (0.5%)</td>
                        <td style="text-align:right">₹{{ number_format((float)($b['edli'] ?? $historyObj->edli ?? 0), 2) }}</td>
                    </tr>
                    <tr style="background:#f8fafc">
                        <td><b>Total CTC (Cost to Company)</b></td>
                        <td style="text-align:right"><b>₹{{ number_format((float)($b['ctc'] ?? $historyObj->ctc ?? 0), 2) }}</b></td>
                    </tr>
                </tbody>
            </table>

            <div class="footer-note">
                This is a computer-generated payslip. No signature required. Generated on {{ date('d M Y, h:i A') }}
            </div>

            <div class="d-flex gap-2 justify-content-center mt-4">
                <a href="{{ route('payroll.payslips.print', $activeId) }}" target="_blank" class="btn btn-outline-secondary">
                    🖨 Print Payslip
                </a>
                <a href="{{ route('payroll.payslips.pdf', $activeId) }}" class="btn btn-primary">
                    📥 Download PDF
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
