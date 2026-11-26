@extends('admin.layout.app')
@section('title', 'Payroll Formulas & Rules')
@section('content')
@include('admin.payroll.partials.styles')
<style>
    #payrollFormulaPage { color: var(--pr-text); }
    #payrollFormulaPage .formula-heading { display:flex; justify-content:space-between; align-items:flex-start; gap:20px; flex-wrap:wrap; margin:20px 0; }
    #payrollFormulaPage .formula-heading h3 { color:var(--pr-text); font-weight:700; margin-bottom:8px; }
    #payrollFormulaPage .formula-heading p { max-width:760px; color:var(--pr-muted); margin:0; line-height:1.6; }
    #payrollFormulaPage .formula-filters { display:grid; grid-template-columns:minmax(220px, 2fr) repeat(2,minmax(140px,1fr)) auto; gap:12px; align-items:end; }
    #payrollFormulaPage .formula-examples { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
    #payrollFormulaPage .example { border:1px solid var(--pr-border); border-radius:10px; padding:16px; background:var(--pr-surface); }
    #payrollFormulaPage .formula-expression { display:block; padding:10px; border-radius:8px; font-size:12px; white-space:normal; overflow-wrap:anywhere; background:var(--pr-bg); color:var(--pr-text); border:1px solid var(--pr-border); }
    #payrollFormulaPage .formula-table { min-width:1000px; width:100%; }
    #payrollFormulaPage .formula-table th { font-size:12px; color:var(--pr-muted); background:var(--pr-bg); padding:14px 16px; }
    #payrollFormulaPage .formula-table td { padding:16px; border-top:1px solid var(--pr-border); vertical-align:middle; }
    #payrollFormulaPage .formula-name { font-weight:700; display:block; }
    #payrollFormulaPage .formula-code { color:var(--pr-muted); font-size:11px; }
    #payrollFormulaPage .formula-status { display:inline-block; padding:4px 8px; border-radius:6px; border:1px solid var(--pr-border); font-size:12px; }
    #payrollFormulaPage .formula-note { color:var(--pr-muted); font-size:12px; line-height:1.6; }
    :is(#formulaEditorModal,#formulaTesterModal) .modal-content { background:var(--pr-surface); color:var(--pr-text); border:1px solid var(--pr-border); }
    :is(#formulaEditorModal,#formulaTesterModal) :is(.modal-header,.modal-footer) { border-color:var(--pr-border); }
    :is(#formulaEditorModal,#formulaTesterModal) .formula-input-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
    :is(#formulaEditorModal,#formulaTesterModal) .formula-feedback { border-radius:8px; padding:12px; background:var(--pr-bg); border:1px solid var(--pr-border); }
    :is(#formulaEditorModal,#formulaTesterModal) .formula-variable { font-size:11px; overflow-wrap:anywhere; }
    @media(max-width:991px) { #payrollFormulaPage .formula-filters { grid-template-columns:1fr 1fr; } #payrollFormulaPage .formula-examples { grid-template-columns:1fr; } }
    @media(max-width:575px) { #payrollFormulaPage .formula-filters { grid-template-columns:1fr; } :is(#formulaEditorModal,#formulaTesterModal) .formula-input-grid { grid-template-columns:1fr 1fr; } }
</style>
<div class="container-xxl flex-grow-1 container-p-y payroll-container" id="payrollFormulaPage">
    @include('admin.payroll.partials.tabs')
    <div class="formula-heading">
        <div><h3>Payroll formulas & rules</h3><p>Configure employee earnings, deductions, appraisal payments and employer costs. Test each expression with numeric inputs before activating it.</p></div>
        @if($canManage)<button type="button" class="btn btn-primary" id="addPayrollFormula">Add formula</button>@endif
    </div>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert"><strong>Formula could not be saved.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="kpi-grid">
        @foreach([['total','Total formulas','Company calculation rules'],['active','Active this payroll month',now()->format('F Y').' · effective on month start'],['earnings','Earnings rules','Base salary and employee earnings'],['deductions','Deduction & tax rules','Amounts withheld from employee pay']] as [$key,$label,$description])
            <div class="kpi"><div class="label">{{ $label }}</div><div class="value">{{ $summary[$key] }}</div><div class="sub">{{ $description }}</div></div>
        @endforeach
    </div>
    <div class="pr-card">
        <div class="pr-card-body">
            <form method="GET" action="{{ route('payroll.formulas.index') }}" class="formula-filters">
                <div><label for="formulaSearch" class="form-label">Search formulas</label><input id="formulaSearch" type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, code or expression"></div>
                <div><label for="formulaCategory" class="form-label">Category</label><select id="formulaCategory" name="category" class="form-select"><option value="all">All categories</option>@foreach($categories as $key=>$label)<option value="{{ $key }}" @selected(request('category') === $key)>{{ $label }}</option>@endforeach</select></div>
                <div><label for="formulaStatus" class="form-label">Activation</label><select id="formulaStatus" name="status" class="form-select"><option value="all">All statuses</option><option value="active" @selected(request('status') === 'active')>Enabled</option><option value="inactive" @selected(request('status') === 'inactive')>Disabled</option><option value="archived" @selected(request('status') === 'archived')>Archived</option></select></div>
                <div class="d-flex gap-2"><button type="submit" class="btn btn-primary">Filter</button><a href="{{ route('payroll.formulas.index') }}" class="btn btn-outline-secondary">Reset</a></div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="formula-table">
                <thead><tr><th>Formula</th><th>Category</th><th>Expression</th><th>Effective dates</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($formulas as $formula)
                    <tr>
                        <td><span class="formula-name">{{ $formula->name }}</span><code class="formula-code">{{ $formula->code }} · v{{ $formula->version }}</code>@if($formula->description)<div class="formula-note mt-1">{{ $formula->description }}</div>@endif</td>
                        <td class="small">{{ $categories[$formula->category] ?? ucfirst($formula->category) }}</td>
                        <td><code class="formula-expression">{{ $formula->formula }}</code><small class="formula-note">{{ count($formula->variables_used ?? []) }} inputs · Last test: {{ $formula->test_result === null ? 'Not tested' : '₹'.number_format((float)$formula->test_result,2) }}</small></td>
                        <td class="small text-nowrap">{{ $formula->effective_from?->format('d M Y') ?? 'Immediate' }}<br><small class="formula-note">{{ $formula->effective_to ? 'Until '.$formula->effective_to->format('d M Y') : 'No end date' }}</small></td>
                        <td><span class="formula-status">{{ $formula->trashed() ? 'Archived' : (!$formula->is_active ? 'Disabled' : (!$formula->is_valid ? 'Needs validation' : ($formula->effective_from && $formula->effective_from->gt(now()->startOfMonth()) ? 'Scheduled' : ($formula->effective_to && $formula->effective_to->lt(now()->startOfMonth()) ? 'Expired' : 'Active')))) }}</span></td>
                        <td><div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-info" data-test-formula="{{ $formula->id }}">Test</button>
                            @if($canManage)
                                @if($formula->trashed())
                                    <form method="POST" action="{{ route('payroll.formulas.restore',$formula) }}">@csrf<button class="btn btn-sm btn-outline-primary" type="submit">Restore as disabled</button></form>
                                @else
                                <button type="button" class="btn btn-sm btn-outline-primary" data-edit-formula="{{ $formula->id }}">Edit</button>
                                <form method="POST" action="{{ route('payroll.formulas.toggle',$formula) }}">@csrf<input type="hidden" name="desired_active" value="{{ $formula->is_active ? '0' : '1' }}"><button class="btn btn-sm btn-outline-secondary" type="submit">{{ $formula->is_active ? 'Disable' : 'Enable' }}</button></form>
                                <form method="POST" action="{{ route('payroll.formulas.destroy',$formula) }}" onsubmit="return confirm('Archive this formula? It will stop applying to future calculations. Existing payslips remain unchanged.')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit">Archive</button></form>
                                @endif
                            @endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-5"><strong>{{ $summary['total'] ? 'No formulas match your filters.' : 'No company formulas configured yet.' }}</strong><p class="formula-note mt-2 mb-0">Try the examples below. Testing does not save a rule or change employee salaries.</p></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <h5 class="mb-2">Try sample payroll data</h5>
    <p class="formula-note">Monthly basic ₹30,000 · 20 payable days / 22 working days · gross ₹38,181.82 · approved appraisal score 85 with a 7% recommendation. These sample values are used only for testing.</p>
    <div class="formula-examples">
        @foreach($examples as $index=>$example)
            <div class="example"><h6>{{ $example['name'] }}</h6><code class="formula-expression mb-2">{{ $example['formula'] }}</code><div class="formula-note mb-3">Expected test result: <strong>₹{{ number_format($example['expected'],2) }}</strong></div><div class="d-flex gap-2"><button type="button" class="btn btn-sm btn-outline-info" data-test-example="{{ $index }}">Test example</button>@if($canManage)<button type="button" class="btn btn-sm btn-outline-primary" data-use-example="{{ $index }}">Use this formula</button>@endif</div></div>
        @endforeach
    </div>
    <div class="pr-card mt-4"><div class="pr-card-body"><h6>How these rules affect payroll</h6><p class="formula-note mb-0">Custom earnings add to Other Allowances, deductions and taxes add to Other Deductions, bonuses add to Additional Earnings, and employer rules add to employer costs. Built-in codes override their named calculation. Effective dates are checked at payroll month start. Appraisal inputs come only from approved reviews completed by month end; without an approved review they are zero. Use appraisal inputs in CURRENT_BASIC for an adjustment to calculated basic, or in a bonus rule for a separate payment. Enabling both combines their effects. Formulas do not permanently change the salary assignment. Built-in statutory overrides respect payroll policy enable/disable settings.</p></div></div>
</div>
@if($canManage)
<div class="modal fade" id="formulaEditorModal" tabindex="-1" aria-labelledby="formulaEditorTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable"><form id="payrollFormulaEditor" class="modal-content" method="POST" action="{{ route('payroll.formulas.store') }}">
        @csrf<input type="hidden" name="_method" value="POST"><input type="hidden" name="formula_id"><input type="hidden" name="version" value="1"><input type="hidden" name="test_inputs"><input type="hidden" name="is_active" value="0">
        <div class="modal-header"><h5 id="formulaEditorTitle" class="modal-title">Create payroll formula</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-6"><label for="formulaName" class="form-label">Name</label><input id="formulaName" name="name" class="form-control" maxlength="255" required></div>
                <div class="col-md-6"><label for="formulaCode" class="form-label">Calculation code</label><input id="formulaCode" name="code" class="form-control font-monospace" pattern="[A-Z][A-Z0-9_]{1,59}" maxlength="60" required placeholder="EXAMPLE_RECOVERY"><small class="formula-note">Built-in codes override a named step. A new code adds a custom component.</small></div>
                <div class="col-md-6"><label for="editorCategory" class="form-label">Payroll category</label><select id="editorCategory" name="category" class="form-select">@foreach($categories as $key=>$label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div>
                <div class="col-md-3"><label for="formulaFrom" class="form-label">Effective from</label><input id="formulaFrom" type="date" name="effective_from" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}" required></div>
                <div class="col-md-3"><label for="formulaUntil" class="form-label">Effective until</label><input id="formulaUntil" type="date" name="effective_to" class="form-control"></div>
                <div class="col-12"><label for="formulaExpression" class="form-label">Expression</label><textarea id="formulaExpression" name="formula" class="form-control font-monospace" rows="3" maxlength="4000" required placeholder="ROUND(GROSS_SALARY * 0.02, 2)"></textarea><small class="formula-note">Supports + − × / %, comparisons, ternary conditions, MIN, MAX, ROUND, ABS, FLOOR, CEIL and IF. No scripts or executable code.</small></div>
                <div class="col-12"><label class="form-label">Available variables for this calculation</label><div id="formulaVariablePalette" class="d-flex flex-wrap gap-2"></div></div>
                <div class="col-12"><label for="formulaDescription" class="form-label">Description</label><input id="formulaDescription" name="description" class="form-control" maxlength="500" placeholder="Purpose, approval requirements and calculation notes"></div>
                <div class="col-12"><h6>Test inputs</h6><div id="formulaEditorInputs" class="formula-input-grid"></div><p class="formula-note mt-2">Sample inputs validate the expression. Payroll uses actual salary, attendance and approved appraisal data.</p><div id="formulaEditorFeedback" class="formula-feedback" role="status" aria-live="polite">Enter an expression, then test it.</div></div>
                <div class="col-12"><div class="form-check form-switch"><input id="formulaEnabled" type="checkbox" name="is_active" value="1" class="form-check-input"><label for="formulaEnabled" class="form-check-label">Enable for eligible payroll periods</label></div></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-info" id="testEditorFormula">Run test</button><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save formula</button></div>
    </form></div>
</div>
@endif
<div class="modal fade" id="formulaTesterModal" tabindex="-1" aria-labelledby="formulaTesterTitle" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <div class="modal-header"><h5 id="formulaTesterTitle" class="modal-title">Formula test</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
    <div class="modal-body"><p class="formula-note">Test calculations are read-only. They do not change payroll or employee salaries.</p><label for="testerExpression" class="form-label">Expression</label><textarea id="testerExpression" class="form-control font-monospace mb-3" rows="3"></textarea><div id="formulaTesterInputs" class="formula-input-grid mb-3"></div><div id="formulaTesterFeedback" class="formula-feedback" role="status" aria-live="polite">Provide inputs and run the test.</div></div>
    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button><button type="button" class="btn btn-primary" id="runFormulaTest">Calculate test result</button></div>
</div></div></div>
@php
    $formulaPageData = ['formulas'=>$editorFormulas, 'samples'=>$samples, 'definitions'=>$definitions, 'customVariables'=>$customVariables,
        'examples'=>$examples, 'categories'=>$categories, 'old'=>old(), 'storeUrl'=>route('payroll.formulas.store'),
        'testUrl'=>route('payroll.formulas.test'), 'editUrl'=>route('payroll.formulas.update', ['formula'=>'__ID__']), 'csrf'=>csrf_token()];
@endphp
<script id="payrollFormulaData" type="application/json">@json($formulaPageData)</script>
<script src="{{ asset('admin/assets/js/pms-payroll-formulas.js') }}?v={{ filemtime(public_path('admin/assets/js/pms-payroll-formulas.js')) }}"></script>
@endsection
