<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Payroll;
use App\Models\PayrollHistory;
use App\Models\Payslip;
use App\Services\PayrollCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/**
 * Self-service payslips: every staff member (HR, managers, employees, company roles) sees only
 * their own payslips of approved / finalized payroll runs. Salaries are set by the company Admin
 * (or HR for everyone except themselves) in the Payroll workspace.
 */
class MyPayslipController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user() ?? auth()->user();
        abort_unless($user, 401);
        \App\Services\PayrollSchema::ensure();
        $this->generateMissing($user);

        $payslips = Payslip::with('payroll')
            ->where('user_id', $user->id)
            ->whereHas('payroll', fn ($q) => $q->whereIn('status', ['approved', 'finalized']))
            ->when($request->filled('year') && $request->input('year') !== 'all',
                fn ($q) => $q->whereHas('payroll', fn ($p) => $p->whereYear('period_start', (int) $request->input('year'))))
            ->get()
            ->sortByDesc(fn ($slip) => $slip->payroll?->period_start)
            ->values();

        $years = Payslip::where('user_id', $user->id)->with('payroll')->get()
            ->map(fn ($slip) => $slip->payroll?->period_start ? \Carbon\Carbon::parse($slip->payroll->period_start)->year : null)
            ->filter()->unique()->sortDesc()->values();

        $latest = $payslips->first();
        $snapshot = $latest ? $this->snapshot($latest) : [];

        return view('my-payslips.index', compact('payslips', 'years', 'latest', 'snapshot'));
    }

    public function show(Request $request, $payslip)
    {
        $payslip = $this->ownPayslip($request, $payslip);
        return view('my-payslips.show', compact('payslip'));
    }

    public function document(Request $request, $payslip)
    {
        $payslip = $this->ownPayslip($request, $payslip);
        return view('admin.payroll.payslip-pdf', $this->documentData($payslip));
    }

    public function pdf(Request $request, $payslip)
    {
        $payslip = $this->ownPayslip($request, $payslip);
        $data = $this->documentData($payslip) + ['mode' => 'pdf'];
        $name = preg_replace('/[^A-Za-z0-9_-]+/', '_', 'Payslip_' . ($payslip->user?->name ?? 'Employee') . '_' . $payslip->payslip_number);

        return Pdf::loadView('admin.payroll.payslip-pdf', $data)
            ->setPaper('a4')
            ->setOption('isFontSubsettingEnabled', true)
            ->download($name . '.pdf');
    }

    private function ownPayslip(Request $request, $id): Payslip
    {
        $user = $request->user() ?? auth()->user();
        abort_unless($user, 401);
        $payslip = Payslip::with(['user.employeeDetail.designation', 'user.employeeDetail.department', 'payroll', 'payrollHistory'])->find($id);
        abort_unless($payslip && (int) $payslip->user_id === (int) $user->id, 404, 'Payslip not found.');
        abort_unless(in_array($payslip->payroll?->status, ['approved', 'finalized'], true), 404, 'This payslip is not released yet.');
        return $payslip;
    }

    private function documentData(Payslip $payslip): array
    {
        $companyId = $payslip->company_id ?: ($payslip->payroll?->company_id ?: $payslip->user?->company_id);
        $company = ($companyId ? Company::find($companyId) : null) ?: \App\Models\CompanySetting::first();
        $history = $payslip->payrollHistory ?: PayrollHistory::where('payroll_id', $payslip->payroll_id)->where('user_id', $payslip->user_id)->first();

        return ['payslip' => $payslip, 'company' => $company, 'snap' => $payslip->employee_snapshot, 'history' => $history];
    }

    private function snapshot(Payslip $payslip): array
    {
        $snap = $payslip->employee_snapshot;
        return is_array($snap) ? $snap : (json_decode((string) $snap, true) ?: []);
    }

    /** Released runs whose payslip for this person was never generated get it now. */
    private function generateMissing($user): void
    {
        try {
            $service = app(PayrollCalculationService::class);
            Payroll::whereIn('status', ['approved', 'finalized'])
                ->whereIn('id', PayrollHistory::where('user_id', $user->id)->pluck('payroll_id'))
                ->whereNotIn('id', Payslip::where('user_id', $user->id)->pluck('payroll_id'))
                ->get()
                ->each(fn (Payroll $payroll) => $service->generatePayslips($payroll));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
