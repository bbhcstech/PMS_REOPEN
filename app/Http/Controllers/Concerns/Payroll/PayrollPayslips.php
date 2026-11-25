<?php

namespace App\Http\Controllers\Concerns\Payroll;

use App\Models\Company;
use App\Models\Payroll;
use App\Models\PayrollHistory;
use App\Models\Payslip;
use App\Services\PayrollCalculationService;
use App\Services\PdfDocument as Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

trait PayrollPayslips
{
    /**
     * 6. Payslips
     */
    public function payslips(Request $request)
    {
        $this->authorizePayroll('payslips');
        $companyId = $this->selectedCompanyId($request);

        // Approved/finalized runs whose payslips were never generated (or failed earlier) get them now.
        $this->dashboardSection('generate missing payslips', function () use ($companyId) {
            $service = app(PayrollCalculationService::class);
            $this->companyQuery(Payroll::query(), $companyId)
                ->whereIn('status', ['approved', 'finalized'])
                ->get()
                ->each(function (Payroll $payroll) use ($service) {
                    $missing = PayrollHistory::where('payroll_id', $payroll->id)->whereNotExists(function ($query) {
                        $query->selectRaw('1')->from('payslips')
                            ->whereColumn('payslips.payroll_id', 'payroll_histories.payroll_id')
                            ->whereColumn('payslips.user_id', 'payroll_histories.user_id');
                    })->exists();
                    if ($missing) {
                        $service->generatePayslips($payroll, auth()->id(), true);
                    }
                });
        }, null);

        $query = Payslip::with([
                'user.employeeDetail.designation',
                'user.employeeDetail.department',
                'payroll',
            ])->whereHas('payroll', fn ($run) => $run->whereIn('status', ['approved', 'finalized'])
                ->when($companyId, fn ($own) => $own->where('company_id', $companyId)))
            ->when($companyId, function ($q) use ($companyId) {
                if (Schema::hasColumn('payslips', 'company_id')) {
                    // Older payslips may have no company recorded: match them through the employee.
                    $q->where(fn ($w) => $w->where('company_id', $companyId)
                        ->orWhere(fn ($n) => $n->whereNull('company_id')->whereHas('user', fn ($u) => $u->where('company_id', $companyId))));
                } else {
                    $q->whereHas('user', fn ($u) => $u->where('company_id', $companyId));
                }
            });

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('payslip_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'LIKE', "%{$search}%")
                      ->orWhereHas('employeeDetail', fn ($ed) => $ed->where('employee_id', 'LIKE', "%{$search}%")));
            });
        }

        if ($request->filled('month') && $request->input('month') !== 'all') {
            $m = (int) $request->input('month');
            $query->whereHas('payroll', fn ($p) => $p->whereMonth('period_start', $m));
        }

        if ($request->filled('year') && $request->input('year') !== 'all') {
            $y = (int) $request->input('year');
            $query->whereHas('payroll', fn ($p) => $p->whereYear('period_start', $y));
        }

        if ($request->filled('department_id') && $request->input('department_id') !== 'all') {
            $query->whereHas('user.employeeDetail', fn ($ed) => $ed->where('department_id', $request->input('department_id')));
        }

        // HR sees their own payslip under My Payslips, not in the payroll workspace.
        if ($this->ownSalaryLocked(auth()->id())) {
            $query->where('user_id', '!=', auth()->id());
        }

        $payslips = $query->latest()->paginate(20);
        $departments = $this->getDepartments();

        return view('admin.payroll.payslips', compact('payslips', 'departments'));
    }

    /**
     * 7. Formulas & Calculation Rules
     */
    protected function resolvePayslipModel($payslip): Payslip
    {
        if ($payslip instanceof Payslip) {
            return $payslip;
        }

        $found = Payslip::find($payslip);
        if ($found) {
            return $found;
        }

        // Fallback: check by payroll_history_id or PayrollHistory ID
        $history = PayrollHistory::find($payslip) ?? PayrollHistory::where('id', $payslip)->first();
        if ($history) {
            $existing = Payslip::where('payroll_history_id', $history->id)->first();
            if ($existing) {
                return $existing;
            }
            $payroll = $history->payroll ?: Payroll::find($history->payroll_id);
            if ($payroll) {
                app(PayrollCalculationService::class)->generatePayslips($payroll, auth()->id());
                $newPayslip = Payslip::where('payroll_history_id', $history->id)->first();
                if ($newPayslip) {
                    return $newPayslip;
                }
            }
        }

        abort(404, 'Payslip not found.');
    }

    /**
     * Company whose name, logo and address brand the payslip.
     */
    protected function payslipCompany(Payslip $payslip)
    {
        $companyId = $payslip->company_id ?: ($payslip->payroll?->company_id ?: ($payslip->user?->company_id ?: auth()->user()?->company_id));

        return ($companyId ? Company::find($companyId) : null) ?: \App\Models\CompanySetting::first();
    }

    public function viewPayslip($payslip)
    {
        $this->authorizePayroll('payslips');
        $payslip = $this->resolvePayslipModel($payslip);
        if ($this->ownSalaryLocked((int) $payslip->user_id)) {
            return redirect()->route('my-payslips.show', $payslip->id);
        }
        $payslip->load(['user.employeeDetail.designation', 'user.employeeDetail.department', 'payroll', 'payrollHistory']);

        $company = $this->payslipCompany($payslip);
        $snap = $payslip->employee_snapshot;
        $history = $payslip->payrollHistory ?: PayrollHistory::where('payroll_id', $payslip->payroll_id)->where('user_id', $payslip->user_id)->first();

        return view('admin.payroll.payslip-view', compact('payslip', 'company', 'snap', 'history'));
    }

    public function printPayslip($payslip)
    {
        // ?noprint=1 is the read-only document embedded in the payslip view page.
        request()->boolean('noprint') ? $this->authorizePayroll('payslips') : $this->authorizePayroll('payslips', 'export');
        $payslip = $this->resolvePayslipModel($payslip);
        $payslip->load(['user.employeeDetail.designation', 'user.employeeDetail.department', 'payroll', 'payrollHistory']);

        $company = $this->payslipCompany($payslip);
        $snap = $payslip->employee_snapshot;
        $history = $payslip->payrollHistory ?: PayrollHistory::where('payroll_id', $payslip->payroll_id)->where('user_id', $payslip->user_id)->first();

        return view('admin.payroll.payslip-print', compact('payslip', 'company', 'snap', 'history'));
    }

    public function downloadPdf($payslip)
    {
        $this->authorizePayroll('payslips', 'export');
        $payslip = $this->resolvePayslipModel($payslip);
        $payslip->load(['user.employeeDetail.designation', 'user.employeeDetail.department', 'payroll', 'payrollHistory']);

        $company = $this->payslipCompany($payslip);
        $snap = $payslip->employee_snapshot;
        $history = $payslip->payrollHistory ?: PayrollHistory::where('payroll_id', $payslip->payroll_id)->where('user_id', $payslip->user_id)->first();

        $mode = 'pdf';
        $pdf = Pdf::loadView('admin.payroll.payslip-pdf', compact('payslip', 'company', 'snap', 'history', 'mode'))
            ->setPaper('a4')
            ->setOption('isFontSubsettingEnabled', true);
        $name = preg_replace('/[^A-Za-z0-9_-]+/', '_', 'Payslip_' . ($payslip->user?->name ?? 'Employee') . '_' . $payslip->payslip_number);
        return $pdf->download($name . '.pdf');
    }

    public function sendPayslipSingle(Request $request, Payslip $payslip, PayrollCalculationService $payrollService)
    {
        $this->authorizePayroll('payslips', 'create');

        $sent = $payrollService->sendPayslip($payslip);
        if ($sent) {
            return back()->with('success', 'Payslip sent to ' . ($payslip->user?->email ?: 'employee') . ' successfully.');
        }
        return back()->with('error', 'Failed to send payslip. Please check email configuration.');
    }

    private function payslipQuery(?int $companyId = null)
    {
        $query = Payslip::query();
        if ($companyId) {
            if (Schema::hasColumn('payslips', 'company_id')) {
                $query->where('company_id', $companyId);
            } else {
                $query->whereHas('user', fn ($userQuery) => $userQuery->where('company_id', $companyId));
            }
        }
        return $query;
    }
}
