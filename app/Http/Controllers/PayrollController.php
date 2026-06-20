<?php

namespace App\Http\Controllers;

use App\Models\BonusRule;
use App\Models\DeductionComponent;
use App\Models\OvertimeRule;
use App\Models\Payroll;
use App\Models\PayrollArchitecture;
use App\Models\PayrollArchitectureVersion;
use App\Models\PayrollAuditLog;
use App\Models\PayrollCycle;
use App\Models\PayrollHistory;
use App\Models\Payslip;
use App\Models\PayslipTemplate;
use App\Models\SalaryComponent;
use App\Models\SalaryStructure;
use App\Models\SalaryStructureVersion;
use App\Models\TaxRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizePayroll('payroll');
        $companyId = $this->selectedCompanyId($request);

        return view('admin.payroll.index', [
            'activeArchitecture' => $this->companyQuery(PayrollArchitecture::query(), $companyId)->where('is_active', true)->first(),
            'cycles' => $this->companyQuery(PayrollCycle::query(), $companyId)->latest()->take(5)->get(),
            'payrolls' => $this->companyQuery(Payroll::with('cycle'), $companyId)->latest()->take(10)->get(),
            'payslipCount' => $this->payslipQuery($companyId)->count(),
            'historyCount' => $this->payrollHistoryQuery($companyId)->count(),
        ]);
    }

    public function architectures()
    {
        $this->authorizePayroll('payroll-architectures');
        $companyId = $this->selectedCompanyId(request());

        return view('admin.payroll.architectures', [
            'architectures' => $this->companyQuery(PayrollArchitecture::query(), $companyId)->latest()->get(),
            'types' => ['standard', 'startup', 'hourly', 'contract', 'project_based', 'commission_based', 'custom'],
        ]);
    }

    public function storeArchitecture(Request $request)
    {
        $this->authorizePayroll('payroll-architectures', 'create');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'effective_date' => ['nullable', 'date'],
        ]);

        $architecturePayload = [
            ...$data,
            'code' => Str::slug($data['name']) . '-' . Str::upper(Str::random(4)),
            'created_by' => auth()->id(),
            'version' => 1,
        ];
        if (Schema::hasColumn('payroll_architectures', 'company_id')) {
            $architecturePayload['company_id'] = auth()->user()?->company_id;
        }

        $architecture = PayrollArchitecture::create($architecturePayload);

        $this->versionArchitecture($architecture);
        $this->audit('created_architecture', $architecture, null, $architecture->toArray(), $request);

        return back()->with('success', 'Payroll architecture created.');
    }

    public function activateArchitecture(Request $request, PayrollArchitecture $architecture)
    {
        $this->authorizePayroll('payroll-architectures', 'edit');

        DB::transaction(function () use ($request, $architecture) {
            PayrollArchitecture::where('is_active', true)->update(['is_active' => false]);
            $old = $architecture->toArray();
            $architecture->update(['is_active' => true, 'version' => $architecture->version + 1]);
            $this->versionArchitecture($architecture->fresh());
            $this->audit('activated_architecture', $architecture, $old, $architecture->fresh()->toArray(), $request);
        });

        return back()->with('success', 'Active payroll architecture updated.');
    }

    public function salaryStructures()
    {
        $this->authorizePayroll('salary-structures');
        $companyId = $this->selectedCompanyId(request());

        return view('admin.payroll.salary-structures', [
            'structures' => $this->companyQuery(SalaryStructure::withCount('components'), $companyId)->latest()->get(),
            'components' => SalaryComponent::with('salaryStructure')->orderBy('sort_order')->latest()->get(),
        ]);
    }

    public function storeSalaryStructure(Request $request)
    {
        $this->authorizePayroll('salary-structures', 'create');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'effective_date' => ['nullable', 'date'],
        ]);

        $structurePayload = [
            ...$data,
            'code' => Str::slug($data['name']) . '-' . Str::upper(Str::random(4)),
            'created_by' => auth()->id(),
            'version' => 1,
        ];
        if (Schema::hasColumn('salary_structures', 'company_id')) {
            $structurePayload['company_id'] = auth()->user()?->company_id;
        }

        $structure = SalaryStructure::create($structurePayload);

        $this->versionSalaryStructure($structure);
        $this->audit('created_salary_structure', $structure, null, $structure->toArray(), $request);

        return back()->with('success', 'Salary structure created.');
    }

    public function storeSalaryComponent(Request $request)
    {
        $this->authorizePayroll('salary-structures', 'create');

        $data = $request->validate([
            'salary_structure_id' => ['nullable', 'exists:salary_structures,id'],
            'name' => ['required', 'string', 'max:255'],
            'component_type' => ['required', 'string', 'max:60'],
            'calculation_type' => ['required', 'string', 'max:60'],
            'value' => ['nullable', 'numeric'],
            'formula' => ['nullable', 'string'],
            'taxable' => ['nullable', 'boolean'],
            'required' => ['nullable', 'boolean'],
            'effective_date' => ['nullable', 'date'],
        ]);

        $component = SalaryComponent::create([
            ...$data,
            'code' => Str::slug($data['name']),
            'taxable' => $request->boolean('taxable'),
            'required' => $request->boolean('required'),
        ]);

        $this->audit('created_salary_component', $component, null, $component->toArray(), $request);

        return back()->with('success', 'Salary component added.');
    }

    public function deductionRules()
    {
        $this->authorizePayroll('deduction-rules');

        return $this->rulesView('Deduction Rules', 'deduction-rules', DeductionComponent::latest()->get());
    }

    public function storeDeductionRule(Request $request)
    {
        $this->authorizePayroll('deduction-rules', 'create');
        $rule = DeductionComponent::create($this->rulePayload($request, 'deduction_type'));
        $this->audit('created_deduction_rule', $rule, null, $rule->toArray(), $request);

        return back()->with('success', 'Deduction rule saved.');
    }

    public function bonusRules()
    {
        $this->authorizePayroll('bonus-rules');

        return $this->rulesView('Bonus Rules', 'bonus-rules', BonusRule::latest()->get());
    }

    public function storeBonusRule(Request $request)
    {
        $this->authorizePayroll('bonus-rules', 'create');
        $rule = BonusRule::create($this->rulePayload($request, 'bonus_type'));
        $this->audit('created_bonus_rule', $rule, null, $rule->toArray(), $request);

        return back()->with('success', 'Bonus rule saved.');
    }

    public function taxRules()
    {
        $this->authorizePayroll('tax-rules');

        return $this->rulesView('Tax Rules', 'tax-rules', TaxRule::latest()->get());
    }

    public function storeTaxRule(Request $request)
    {
        $this->authorizePayroll('tax-rules', 'create');
        $data = $this->baseRuleValidation($request);
        $rule = TaxRule::create([
            'code' => Str::slug($data['name']),
            'name' => $data['name'],
            'country' => $request->input('country', 'custom'),
            'formula' => $data['formula'] ?? null,
            'effective_date' => $data['effective_date'] ?? null,
            'slabs' => $this->jsonInput($request->input('slabs')),
            'exemptions' => $this->jsonInput($request->input('exemptions')),
        ]);
        $this->audit('created_tax_rule', $rule, null, $rule->toArray(), $request);

        return back()->with('success', 'Tax rule saved.');
    }

    public function overtimeRules()
    {
        $this->authorizePayroll('overtime-rules');

        return $this->rulesView('Overtime Rules', 'overtime-rules', OvertimeRule::latest()->get());
    }

    public function storeOvertimeRule(Request $request)
    {
        $this->authorizePayroll('overtime-rules', 'create');
        $data = $this->baseRuleValidation($request);
        $rule = OvertimeRule::create([
            'code' => Str::slug($data['name']),
            'name' => $data['name'],
            'overtime_type' => $request->input('rule_type', 'weekday'),
            'multiplier' => $request->input('multiplier', 1),
            'formula' => $data['formula'] ?? null,
            'effective_date' => $data['effective_date'] ?? null,
        ]);
        $this->audit('created_overtime_rule', $rule, null, $rule->toArray(), $request);

        return back()->with('success', 'Overtime rule saved.');
    }

    public function cycles()
    {
        $this->authorizePayroll('payroll-cycles');
        $companyId = $this->selectedCompanyId(request());

        return view('admin.payroll.cycles', [
            'cycles' => $this->companyQuery(PayrollCycle::query(), $companyId)->latest()->get(),
            'cycleTypes' => ['monthly', 'weekly', 'biweekly', 'quarterly', 'half_yearly', 'yearly', 'custom'],
        ]);
    }

    public function storeCycle(Request $request)
    {
        $this->authorizePayroll('payroll-cycles', 'create');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cycle_type' => ['required', 'string', 'max:60'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'pay_date' => ['nullable', 'date'],
            'lock_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $cyclePayload = [
            ...$data,
            'created_by' => auth()->id(),
        ];
        if (Schema::hasColumn('payroll_cycles', 'company_id')) {
            $cyclePayload['company_id'] = auth()->user()?->company_id;
        }

        $cycle = PayrollCycle::create($cyclePayload);
        $this->audit('created_payroll_cycle', $cycle, null, $cycle->toArray(), $request);

        return back()->with('success', 'Payroll cycle created.');
    }

    public function process(Request $request, PayrollCycle $cycle)
    {
        $this->authorizePayroll('payroll', 'create');

        $payrollPayload = [
            'payroll_cycle_id' => $cycle->id,
            'status' => 'draft',
            'period_start' => $cycle->start_date,
            'period_end' => $cycle->end_date,
            'pay_date' => $cycle->pay_date,
            'generated_by' => auth()->id(),
            'attendance_summary' => ['source' => 'attendance_leave_timesheet_integration'],
        ];
        if (Schema::hasColumn('payrolls', 'company_id')) {
            $payrollPayload['company_id'] = ($cycle->company_id ?? null) ?: auth()->user()?->company_id;
        }

        $payroll = Payroll::create($payrollPayload);

        $this->audit('generated_payroll_draft', $payroll, null, $payroll->toArray(), $request);

        return redirect()->route('payroll.index')->with('success', 'Payroll draft generated.');
    }

    public function payslips()
    {
        $this->authorizePayroll('payslips');

        return view('admin.payroll.payslips', [
            'payslips' => $this->payslipQuery($this->selectedCompanyId(request()))->latest()->get(),
            'templates' => PayslipTemplate::latest()->get(),
        ]);
    }

    public function reports()
    {
        $this->authorizePayroll('payroll-reports');

        return view('admin.payroll.reports', [
            'payrolls' => $this->companyQuery(Payroll::query(), $this->selectedCompanyId(request()))->latest()->get(),
            'histories' => $this->payrollHistoryQuery($this->selectedCompanyId(request()))->latest()->take(100)->get(),
        ]);
    }

    public function auditLogs()
    {
        $this->authorizePayroll('payroll-audit-logs');

        return view('admin.payroll.audit-logs', [
            'logs' => PayrollAuditLog::latest()->paginate(50),
        ]);
    }

    public function policies()
    {
        return $this->placeholder('policies');
    }

    public function settings()
    {
        return $this->placeholder('settings');
    }

    public function importExport()
    {
        return $this->placeholder('import-export');
    }

    public function archive()
    {
        return $this->placeholder('archive');
    }

    public function formulaBuilder()
    {
        return $this->placeholder('formula-builder');
    }

    public function placeholder(string $module)
    {
        $slug = match ($module) {
            'policies' => 'payroll-policies',
            'settings' => 'payroll-settings',
            'import-export' => 'payroll-import-export',
            'archive' => 'payroll-archive',
            'formula-builder' => 'formula-builder',
            default => 'payroll',
        };

        $this->authorizePayroll($slug);

        return view('admin.payroll.placeholder', [
            'title' => Str::headline($module),
            'moduleSlug' => $slug,
        ]);
    }

    private function authorizePayroll(string $moduleSlug, string $permission = 'view'): void
    {
        if (! auth()->user()?->hasModulePermission($moduleSlug, $permission)) {
            abort(403, 'You do not have permission to access this payroll module.');
        }
    }

    private function payslipQuery(?int $companyId = null)
    {
        $query = Payslip::query();
        if (auth()->user()->normalizedRole() === 'employee') {
            $query->where('user_id', auth()->id());
        } elseif (auth()->user()->normalizedRole() === 'manager') {
            $query->whereIn('user_id', auth()->user()->visibleEmployeeIds());
        } elseif ($companyId) {
            if (Schema::hasColumn('payslips', 'company_id')) {
                $query->where('company_id', $companyId);
            } else {
                $query->whereHas('user', fn ($userQuery) => $userQuery->where('company_id', $companyId));
            }
        }

        return $query;
    }

    private function payrollHistoryQuery(?int $companyId = null)
    {
        $query = PayrollHistory::query();

        if (auth()->user()->normalizedRole() === 'employee') {
            $query->where('user_id', auth()->id());
        } elseif ($companyId) {
            $query->whereHas('user', fn ($userQuery) => $userQuery->where('company_id', $companyId));
        }

        return $query;
    }

    private function companyQuery($query, ?int $companyId)
    {
        if (! $companyId) {
            return $query;
        }

        $model = $query->getModel();
        if (Schema::hasColumn($model->getTable(), 'company_id')) {
            $query->where($model->getTable() . '.company_id', $companyId);
        }

        return $query;
    }

    private function selectedCompanyId(Request $request): ?int
    {
        if (auth()->user()?->normalizedRole() !== 'admin') {
            return auth()->user()?->company_id;
        }

        return $request->integer('company_id') ?: null;
    }

    private function versionArchitecture(PayrollArchitecture $architecture): void
    {
        PayrollArchitectureVersion::updateOrCreate(
            ['payroll_architecture_id' => $architecture->id, 'version' => $architecture->version],
            [
                'snapshot' => $architecture->toArray(),
                'effective_date' => $architecture->effective_date,
                'created_by' => auth()->id(),
            ]
        );
    }

    private function versionSalaryStructure(SalaryStructure $structure): void
    {
        SalaryStructureVersion::updateOrCreate(
            ['salary_structure_id' => $structure->id, 'version' => $structure->version],
            [
                'snapshot' => $structure->load('components')->toArray(),
                'effective_date' => $structure->effective_date,
                'created_by' => auth()->id(),
            ]
        );
    }

    private function rulesView(string $title, string $slug, $rules)
    {
        return view('admin.payroll.rules', compact('title', 'slug', 'rules'));
    }

    private function rulePayload(Request $request, string $typeColumn): array
    {
        $data = $this->baseRuleValidation($request);

        return [
            'name' => $data['name'],
            'code' => Str::slug($data['name']),
            $typeColumn => $request->input('rule_type', 'custom'),
            'calculation_type' => $request->input('calculation_type', 'formula'),
            'value' => $request->input('value'),
            'formula' => $data['formula'] ?? null,
            'effective_date' => $data['effective_date'] ?? null,
        ];
    }

    private function baseRuleValidation(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rule_type' => ['nullable', 'string', 'max:80'],
            'calculation_type' => ['nullable', 'string', 'max:60'],
            'value' => ['nullable', 'numeric'],
            'formula' => ['nullable', 'string'],
            'effective_date' => ['nullable', 'date'],
        ]);
    }

    private function jsonInput(?string $value): ?array
    {
        if (! $value) {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : ['raw' => $value];
    }

    private function audit(string $action, object $model, ?array $oldValue, ?array $newValue, Request $request): void
    {
        PayrollAuditLog::create([
            'user_id' => auth()->id(),
            'role' => auth()->user()?->role,
            'action' => $action,
            'auditable_type' => $model::class,
            'auditable_id' => $model->id ?? null,
            'ip_address' => $request->ip(),
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'reason' => $request->input('reason'),
        ]);
    }
}
