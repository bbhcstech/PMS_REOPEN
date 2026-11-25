<?php

namespace App\Http\Controllers\Concerns;

use App\Models\PayrollFormula;
use App\Services\{PayrollExpression, PayrollFormulaCatalog, PayrollFormulaRules, WorkforceAccess};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\{Rule, ValidationException};

trait ManagesPayrollFormulas
{
    private function formulaCompany(Request $request): int
    {
        $companyId = $this->selectedCompanyId($request);
        abort_unless($companyId, 403, 'Select a company workspace first.');
        return $companyId;
    }

    private function authorizeFormulaChange(Request $request, string $action, ?PayrollFormula $formula = null): int
    {
        abort_unless(WorkforceAccess::isAdmin(auth()->user()), 403, 'Only company administrators can configure payroll formulas.');
        $this->authorizePayroll('formula-builder', $action);
        $companyId = $this->formulaCompany($request);
        if ($formula) abort_unless((int) $formula->company_id === $companyId, 404);
        return $companyId;
    }

    public function formulas(Request $request)
    {
        $this->authorizePayroll('formula-builder');
        $companyId = $this->formulaCompany($request);
        $catalog = app(PayrollFormulaCatalog::class);
        $all = PayrollFormula::where('company_id', $companyId)->orderBy('category')->orderBy('id')->get();
        $candidates = $request->status === 'archived' ? PayrollFormula::onlyTrashed()->where('company_id', $companyId)->orderBy('id')->get() : $all;
        $formulas = $candidates->filter(function ($formula) use ($request) {
            if ($request->filled('category') && $request->category !== 'all' && $formula->category !== $request->category) return false;
            if ($request->filled('status') && !in_array($request->status, ['all', 'archived'], true) && $formula->is_active !== ($request->status === 'active')) return false;
            $search = strtolower(trim((string) $request->query('search', '')));
            return $search === '' || str_contains(strtolower($formula->name.' '.$formula->code.' '.$formula->formula.' '.$formula->description), $search);
        })->values();
        $summary = ['total' => $all->count(), 'active' => app(PayrollFormulaRules::class)->effective($companyId, now()->startOfMonth())->where('is_valid', true)->count(),
            'earnings' => $all->where('category', 'earnings')->count(), 'deductions' => $all->whereIn('category', ['deduction', 'tax'])->count(),
            'bonuses' => $all->where('category', 'bonus')->count(), 'statutory' => $all->whereIn('category', ['custom', 'statutory'])->count()];
        $categories = $catalog->categories(); $samples = $catalog->samples(); $definitions = $catalog->definitions(); $examples = $catalog->examples();
        $customVariables = array_map(fn ($category) => $catalog->allowedVariables('CUSTOM_COMPONENT', $category), array_combine(array_keys($categories), array_keys($categories)));
        $editorFormulas = PayrollFormula::withTrashed()->where('company_id', $companyId)->get()->mapWithKeys(fn ($formula) => [$formula->id => array_merge($formula->toArray(), ['effective_from' => $formula->effective_from?->toDateString(), 'effective_to' => $formula->effective_to?->toDateString()])]);
        $canManage = WorkforceAccess::isAdmin(auth()->user());
        return view('admin.payroll.formulas', compact('formulas', 'summary', 'categories', 'samples', 'definitions', 'examples', 'canManage', 'customVariables', 'editorFormulas'));
    }

    private function validatedFormula(Request $request, ?PayrollFormula $existing = null): array
    {
        $code = strtoupper(trim((string) ($existing?->code ?? $request->input('code'))));
        $request->merge(['code' => $code]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'code' => ['required', 'regex:/^[A-Z][A-Z0-9_]{1,59}$/', Rule::unique('tenant.payroll_formulas', 'code')->ignore($existing?->id)],
            'category' => ['required', Rule::in(array_keys(app(PayrollFormulaCatalog::class)->categories()))],
            'formula' => ['required', 'string', 'max:4000'], 'description' => ['nullable', 'string', 'max:500'],
            'effective_from' => ['required', 'date_format:Y-m-d'], 'effective_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:effective_from'],
            'is_active' => ['sometimes', 'boolean'], 'version' => $existing ? ['required', 'integer', 'min:1'] : ['sometimes', 'integer'],
        ]);
        $catalog = app(PayrollFormulaCatalog::class); $engine = app(PayrollExpression::class);
        $definition = $catalog->definitions()[$code] ?? null;
        if ($definition && $definition[0] !== $data['category'] && !($code === 'PROFESSIONAL_TAX' && $data['category'] === 'deduction')
            && !($definition[0] === 'custom' && $data['category'] === 'statutory')) {
            throw ValidationException::withMessages(['category' => 'This built-in calculation belongs to '.$catalog->categories()[$definition[0]].'.']);
        }
        try {
            $variables = $engine->variables($data['formula']);
            $unknown = array_diff($variables, $catalog->allowedVariables($code, $data['category']));
            if ($unknown) throw new \InvalidArgumentException('Unavailable variables for this calculation stage: '.implode(', ', $unknown).'.');
            $inputs = $request->input('test_inputs');
            if (is_string($inputs)) $inputs = json_decode($inputs, true, 32, JSON_THROW_ON_ERROR);
            if ($inputs !== null && !is_array($inputs)) throw new \InvalidArgumentException('Test inputs must be a JSON object of numeric values.');
            $inputs ??= array_intersect_key($catalog->samples(), array_flip($variables));
            $result = $engine->evaluate($data['formula'], $inputs);
            if ($result < 0) throw new \InvalidArgumentException('The component result cannot be negative.');
        } catch (\InvalidArgumentException|\JsonException $e) {
            throw ValidationException::withMessages(['formula' => $e->getMessage()]);
        }
        return array_merge($data, ['formula' => rtrim(trim($data['formula']), "; \t\n\r"), 'variables_used' => $variables,
            'test_inputs' => $inputs, 'test_result' => round($result, 2), 'is_valid' => true, 'is_active' => $request->boolean('is_active'), 'last_validated_at' => now()]);
    }

    public function storeFormula(Request $request)
    {
        $companyId = $this->authorizeFormulaChange($request, 'create');
        $data = $this->validatedFormula($request);
        DB::connection('tenant')->transaction(function () use ($companyId, $data, $request) {
            $formula = PayrollFormula::create(array_merge($data, ['company_id' => $companyId, 'created_by' => auth()->id(), 'version' => 1]));
            $this->audit('created_payroll_formula', $formula, null, $formula->toArray(), $request);
        });
        return back()->with('success', 'Formula saved and tested. Its activation follows the selected payroll effective dates.');
    }

    public function updateFormula(Request $request, PayrollFormula $formula)
    {
        $this->authorizeFormulaChange($request, 'edit', $formula);
        $data = $this->validatedFormula($request, $formula);
        DB::connection('tenant')->transaction(function () use ($formula, $data, $request) {
            $record = PayrollFormula::whereKey($formula->id)->lockForUpdate()->firstOrFail();
            if ((int) $record->version !== (int) $data['version']) throw ValidationException::withMessages(['version' => 'Another administrator updated this formula. Reload before editing.']);
            $old = $record->toArray();
            $record->update(array_merge($data, ['version' => $record->version + 1, 'updated_by' => auth()->id()]));
            $this->audit('updated_payroll_formula', $record, $old, $record->fresh()->toArray(), $request);
        });
        return back()->with('success', 'Formula updated and tested. Existing finalized payslips are unchanged.');
    }

    public function toggleFormulaActive(Request $request, PayrollFormula $formula)
    {
        $this->authorizeFormulaChange($request, 'edit', $formula);
        $request->validate(['desired_active' => ['sometimes', 'boolean']]);
        DB::connection('tenant')->transaction(function () use ($formula, $request) {
            $record = PayrollFormula::whereKey($formula->id)->lockForUpdate()->firstOrFail(); $old = $record->toArray();
            $target = $request->has('desired_active') ? $request->boolean('desired_active') : !$record->is_active;
            if ($target === $record->is_active) return;
            $validated = [];
            if ($target) {
                $validationRequest = Request::create('/', 'POST', array_merge($record->toArray(), ['effective_from' => $record->effective_from?->toDateString() ?? now()->startOfMonth()->toDateString(),
                    'effective_to' => $record->effective_to?->toDateString(), 'test_inputs' => $record->test_inputs, 'version' => $record->version]));
                $validated = array_intersect_key($this->validatedFormula($validationRequest, $record), array_flip(['effective_from', 'effective_to', 'variables_used', 'test_inputs', 'test_result', 'last_validated_at', 'is_valid']));
            }
            $record->update(array_merge($validated, ['is_active' => $target, 'version' => $record->version + 1, 'updated_by' => auth()->id()]));
            $this->audit('toggled_payroll_formula', $record, $old, $record->fresh()->toArray(), $request);
        });
        return back()->with('success', 'Formula activation updated.');
    }

    public function destroyFormula(Request $request, PayrollFormula $formula)
    {
        $this->authorizeFormulaChange($request, 'delete', $formula);
        DB::connection('tenant')->transaction(function () use ($formula, $request) {
            $this->audit('archived_payroll_formula', $formula, $formula->toArray(), null, $request);
            $formula->delete();
        });
        return back()->with('success', 'Formula archived. Previously generated payslips are unchanged.');
    }

    public function testFormulaLive(Request $request)
    {
        $this->authorizePayroll('formula-builder'); $this->formulaCompany($request);
        $request->validate(['formula' => ['required', 'string', 'max:4000'], 'variables' => ['present', 'array'], 'variables.*' => ['numeric']]);
        try {
            $code = strtoupper(trim((string) $request->input('code', '')));
            $category = (string) $request->input('category', '');
            if ($code !== '' && isset(app(PayrollFormulaCatalog::class)->categories()[$category])) {
                $unknown = array_diff(app(PayrollExpression::class)->variables($request->formula), app(PayrollFormulaCatalog::class)->allowedVariables($code, $category));
                if ($unknown) throw new \InvalidArgumentException('Unavailable variables for this calculation stage: '.implode(', ', $unknown).'.');
            }
            $result = app(PayrollExpression::class)->evaluate($request->formula, $request->variables);
            return response()->json(['success' => true, 'result' => round($result, 2), 'formatted' => '₹'.number_format($result, 2)]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function restoreFormula(Request $request, PayrollFormula $formula)
    {
        $this->authorizeFormulaChange($request, 'edit', $formula);
        DB::connection('tenant')->transaction(function () use ($formula, $request) {
            $record = PayrollFormula::withTrashed()->whereKey($formula->id)->lockForUpdate()->firstOrFail();
            if (!$record->trashed()) return;
            $old = $record->toArray();
            $record->is_active = false;
            $record->version = $record->version + 1;
            $record->updated_by = auth()->id();
            $record->restore();
            $this->audit('restored_payroll_formula', $record, $old, $record->fresh()->toArray(), $request);
        });
        return back()->with('success', 'Formula restored as disabled. Review and test it before enabling.');
    }
}
