<?php

namespace App\Http\Controllers\Concerns\Payroll;

use App\Models\Designation;
use App\Models\Payroll;
use App\Models\PayrollHistory;
use Illuminate\Http\Request;

trait PayrollHistoryAndExports
{
    /**
     * 5. Payroll History
     */
    public function history(Request $request)
    {
        $this->authorizePayroll('payroll');
        $companyId = $this->selectedCompanyId($request);

        $query = PayrollHistory::with([
                'user.employeeDetail.designation',
                'user.employeeDetail.department',
                'payroll',
            ])
            ->when($companyId, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('company_id', $companyId)));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($uq) use ($search) {
                $uq->where('name', 'LIKE', "%{$search}%")
                   ->orWhere('email', 'LIKE', "%{$search}%")
                   ->orWhereHas('employeeDetail', fn ($ed) => $ed->where('employee_id', 'LIKE', "%{$search}%"));
            });
        }

        if ($request->filled('month') && $request->input('month') !== 'all') {
            $m = (int) $request->input('month');
            $query->whereMonth('period_start', $m);
        }

        if ($request->filled('year') && $request->input('year') !== 'all') {
            $y = (int) $request->input('year');
            $query->whereYear('period_start', $y);
        }

        if ($request->filled('department_id') && $request->input('department_id') !== 'all') {
            $query->whereHas('user.employeeDetail', fn ($ed) => $ed->where('department_id', $request->input('department_id')));
        }

        if ($request->filled('designation_id') && $request->input('designation_id') !== 'all') {
            $query->whereHas('user.employeeDetail', fn ($ed) => $ed->where('designation_id', $request->input('designation_id')));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('payroll_status', $request->input('status'));
        }

        $histories = $query->orderBy('period_start', 'desc')->orderBy('id', 'desc')->paginate(20);
        $departments = $this->getDepartments();
        $designations = Designation::orderBy('name')->get();

        return view('admin.payroll.history', compact('histories', 'departments', 'designations'));
    }

    public function export(Request $request, Payroll $payroll)
    {
        $this->authorizePayroll('payroll', 'export');

        $items = PayrollHistory::where('payroll_id', $payroll->id)->get();
        $fileName = 'Payroll_' . date('Y_m', strtotime($payroll->period_start)) . '_Export.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        // Lines saved without their own value use the run's working days, never an assumed number.
        $runWorkingDays = $payroll->metadata['working_days'] ?? '-';
        $callback = function () use ($items, $runWorkingDays) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Sr No', 'Emp ID', 'Employee Name', 'Designation', 'Grade',
                'Working Days', 'Present', 'Paid Leave', 'Unpaid Leave', 'Absent', 'Half Day', 'WFH', 'Working Hours',
                'Basic', 'Current Basic', 'HRA', 'Special Allowance', 'Gross',
                'PF', 'ESI', 'Total Deductions', 'Net Pay',
                'Attendance Bonus', 'Best Emp Bonus', 'TA', 'Overtime', 'Commission', 'Adjustments', 'Total In Hand',
                'Employer PF', 'Employer ESI', 'EDLI', 'CTC', 'Status'
            ]);

            foreach ($items as $item) {
                $s = is_array($item->snapshot) ? $item->snapshot : (json_decode($item->snapshot ?? '{}', true) ?: []);
                fputcsv($file, [
                    $s['sr_no'] ?? '-',
                    $s['employee_id'] ?? '-',
                    $s['employee_name'] ?? '-',
                    $s['designation'] ?? '-',
                    $s['grade'] ?? '-',
                    $s['working_days'] ?? $runWorkingDays,
                    $s['present'] ?? 0,
                    $s['paid_leave'] ?? 0,
                    $s['unpaid_leave'] ?? 0,
                    $s['absent'] ?? 0,
                    $s['half_day'] ?? 0,
                    $s['wfh'] ?? 0,
                    $s['working_hours'] ?? 0,
                    $s['basic'] ?? 0,
                    $s['current_basic'] ?? 0,
                    $s['current_hra'] ?? 0,
                    $s['current_special'] ?? 0,
                    $s['gross_salary'] ?? 0,
                    $s['pf'] ?? 0,
                    $s['esi'] ?? 0,
                    $s['total_deductions'] ?? 0,
                    $s['net_pay'] ?? 0,
                    $s['attendance_bonus'] ?? 0,
                    $s['best_employee_bonus'] ?? 0,
                    $s['ta'] ?? 0,
                    $s['overtime'] ?? 0,
                    $s['commission'] ?? 0,
                    $s['adjustment'] ?? 0,
                    $s['total_in_hand'] ?? 0,
                    $s['employer_pf'] ?? 0,
                    $s['employer_esi'] ?? 0,
                    $s['edli'] ?? 0,
                    $s['ctc'] ?? 0,
                    $item->payroll_status ?? 'Calculated',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
