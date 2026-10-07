@extends('admin.layout.app')

@section('title', 'Payroll Formulas & Rules')

@section('content')
@include('admin.payroll.partials.styles')

<style>
.formula-code-box {
    background: #0f172a;
    color: #38bdf8;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: 13px;
    padding: 6px 10px;
    border-radius: 6px;
    display: inline-block;
    max-width: 100%;
    word-break: break-all;
    border: 1px solid #1e293b;
}
.var-pill {
    font-size: 11px;
    padding: 2px 7px;
    border-radius: 4px;
    background: #e0f2fe;
    color: #0369a1;
    font-weight: 600;
    font-family: monospace;
    display: inline-block;
    cursor: pointer;
    transition: all 0.15s;
    user-select: none;
}
.var-pill:hover {
    background: #0284c7;
    color: #ffffff;
    transform: translateY(-1px);
}
:is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], body.dark-mode, [data-pms-theme="dark"]) :is(.payroll-container, #createFormulaModal, #editFormulaModal) .var-pill {
    background: #1e3a5f !important;
    color: #bfdbfe !important;
    -webkit-text-fill-color: currentColor !important;
}
:is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], body.dark-mode, [data-pms-theme="dark"]) :is(.payroll-container, #createFormulaModal, #editFormulaModal) .var-pill:hover {
    background: #075985 !important;
    color: #ffffff !important;
}
.category-badge-earnings { background: #dcfce7; color: #15803d; }
.category-badge-deduction { background: #fee2e2; color: #b91c1c; }
.category-badge-bonus { background: #fef3c7; color: #b45309; }
.category-badge-custom { background: #e0e7ff; color: #4338ca; }
.category-badge-statutory { background: #f3e8ff; color: #7e22ce; }
</style>

<div class="payroll-container">
    @include('admin.payroll.partials.tabs')

    <div class="payroll-card">
        <div class="payroll-card-header">
            <div>
                <h4 class="payroll-card-title">📐 Payroll Formulas & Calculation Rules</h4>
                <p class="payroll-card-subtitle">
                    Live payslip calculation formulas and statutory rules. Edit mathematical expressions, configure variables, set effective dates, and activate rules to govern payroll processing.
                </p>
            </div>
            <div class="payroll-actions">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createFormulaModal">
                    <i class="bx bx-plus me-1"></i> Add Custom Formula
                </button>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="stats-grid mb-4">
            <div class="stat-card">
                <div class="stat-label">Total Formulas</div>
                <div class="stat-value">{{ $summary['total'] }}</div>
                <div class="stat-sub">Defined rules & expressions</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Active & Effective</div>
                <div class="stat-value text-success">{{ $summary['active'] }}</div>
                <div class="stat-sub">Currently governing payslips</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Earnings & Base Rules</div>
                <div class="stat-value text-primary">{{ $summary['earnings'] }}</div>
                <div class="stat-sub">Basic, HRA, Gross, Net Pay</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Deductions & Statutory</div>
                <div class="stat-value text-danger">{{ $summary['deductions'] + $summary['statutory'] }}</div>
                <div class="stat-sub">PF, ESI, PT, EDLI, CTC</div>
            </div>
        </div>

        {{-- Filter & Category Navigation --}}
        <div class="payroll-filters mb-3">
            <form method="GET" action="{{ route('payroll.formulas.index') }}" class="d-flex flex-wrap gap-2 align-items-center w-100">
                <div class="field" style="flex: 1; min-width: 200px;">
                    <label>Search Formulas</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, code or formula..." class="form-control form-control-sm">
                </div>
                <div class="field">
                    <label>Category</label>
                    <select name="category" onchange="this.form.submit()" class="form-select form-select-sm">
                        <option value="all">All Categories ({{ $summary['total'] }})</option>
                        <option value="earnings" {{ request('category') == 'earnings' ? 'selected' : '' }}>Earnings & Base ({{ $summary['earnings'] }})</option>
                        <option value="deduction" {{ request('category') == 'deduction' ? 'selected' : '' }}>Deductions ({{ $summary['deductions'] }})</option>
                        <option value="bonus" {{ request('category') == 'bonus' ? 'selected' : '' }}>Bonuses ({{ $summary['bonuses'] }})</option>
                        <option value="custom" {{ request('category') == 'custom' ? 'selected' : '' }}>Employer & CTC ({{ $summary['statutory'] }})</option>
                    </select>
                </div>
                <div class="field">
                    <label>Status</label>
                    <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                        <option value="all">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <a href="{{ route('payroll.formulas.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>

        {{-- Formulas Table --}}
        <div class="table-wrap">
            <table class="payroll-table">
                <thead>
                    <tr>
                        <th style="width: 220px;">Formula Name & Code</th>
                        <th style="width: 110px;">Category</th>
                        <th>Mathematical Formula Expression</th>
                        <th style="width: 140px;">Effective Date</th>
                        <th style="width: 70px;">Ver.</th>
                        <th style="width: 90px;">Status</th>
                        <th style="width: 140px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($formulas as $f)
                        <tr>
                            <td>
                                <strong class="d-block text-dark">{{ $f->name }}</strong>
                                <span class="badge bg-light text-muted border font-monospace" style="font-size: 11px;">{{ $f->code }}</span>
                                @if($f->description)
                                    <div class="text-muted small mt-1" style="font-size: 11px; line-height: 1.3;">{{ $f->description }}</div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $catClass = match($f->category) {
                                        'earnings' => 'category-badge-earnings',
                                        'deduction' => 'category-badge-deduction',
                                        'bonus' => 'category-badge-bonus',
                                        default => 'category-badge-custom',
                                    };
                                @endphp
                                <span class="badge {{ $catClass }} px-2 py-1" style="font-size: 11px; text-transform: capitalize;">
                                    {{ $f->category }}
                                </span>
                            </td>
                            <td>
                                <div class="formula-code-box mb-1">
                                    {{ $f->formula }}
                                </div>
                                @if(!empty($f->variables_used) && is_array($f->variables_used))
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        @foreach($f->variables_used as $var)
                                            <span class="var-pill" title="Click to test" onclick="openTestModal('{{ $f->name }}', '{{ addslashes($f->formula) }}', {{ json_encode($f->test_inputs ?: []) }})">
                                                {{ $var }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark">
                                    {{ $f->effective_from ? \Carbon\Carbon::parse($f->effective_from)->format('d M Y') : 'Immediate' }}
                                </div>
                                <div class="text-muted small" style="font-size: 11px;">
                                    {{ $f->effective_to ? 'to ' . \Carbon\Carbon::parse($f->effective_to)->format('d M Y') : 'Ongoing' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary" style="font-size: 11px;">v{{ $f->version ?? 1 }}</span>
                            </td>
                            <td>
                                @if($f->is_active)
                                    <span class="status-pill status-approved">● Active</span>
                                @else
                                    <span class="status-pill status-draft">Inactive</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div class="d-inline-flex gap-1">
                                    {{-- Quick Test Button --}}
                                    <button type="button" class="btn btn-xs btn-outline-info" title="Test Live"
                                        onclick="openTestModal('{{ $f->name }}', '{{ addslashes($f->formula) }}', {{ json_encode($f->test_inputs ?: []) }})">
                                        <i class="bx bx-play"></i>
                                    </button>

                                    {{-- Edit Button --}}
                                    <button type="button" class="btn btn-xs btn-outline-primary" title="Edit Formula"
                                        onclick="openEditModal({{ json_encode($f) }})">
                                        <i class="bx bx-edit"></i>
                                    </button>

                                    {{-- Toggle / Make Effective Button --}}
                                    <form method="POST" action="{{ route('payroll.formulas.toggle', $f->id) }}" class="d-inline" onsubmit="return confirm('Change active status for formula {{ $f->name }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-xs {{ $f->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}" title="{{ $f->is_active ? 'Deactivate' : 'Make Effective' }}">
                                            <i class="bx {{ $f->is_active ? 'bx-power-off' : 'bx-check' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bx bx-info-circle fs-3 d-block mb-2"></i>
                                No formulas found matching the selected criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL 1: Create Formula Modal --}}
<div class="modal fade" id="createFormulaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('payroll.formulas.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">➕ Create New Payroll Formula</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Formula Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Current Basic Salary" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Formula Code / Identifier <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control font-monospace" placeholder="e.g. CURRENT_BASIC" required>
                        <small class="text-muted">Unique uppercase identifier used in calculations.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="earnings">Earnings & Base</option>
                            <option value="deduction">Deduction</option>
                            <option value="bonus">Bonus & Incentive</option>
                            <option value="custom">Employer Contribution & CTC</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Effective From Date <span class="text-danger">*</span></label>
                        <input type="date" name="effective_from" value="{{ date('Y-m-01') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Effective Until (Optional)</label>
                        <input type="date" name="effective_to" class="form-control">
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="createIsActive" value="1" checked>
                            <label class="form-check-label fw-semibold" for="createIsActive">Make Active & Effective</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Mathematical Formula Expression <span class="text-danger">*</span></label>
                        <textarea name="formula" id="createFormulaExpr" class="form-control font-monospace" rows="3" placeholder="e.g. BASIC * (PAYABLE_DAYS / WORKING_DAYS)" required style="font-size: 14px; background: #f8fafc;"></textarea>
                        <div class="mt-2">
                            <small class="text-muted fw-semibold d-block mb-1">Click a variable to insert into formula:</small>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($commonVariables as $varKey => $varDesc)
                                    <span class="var-pill" title="{{ $varDesc }}" onclick="insertVariable('createFormulaExpr', '{{ $varKey }}')">
                                        + {{ $varKey }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Description / Purpose</label>
                        <input type="text" name="description" class="form-control" placeholder="Explains calculation rules and statutory notes">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-bold">Save & Make Effective</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: Edit Formula Modal --}}
<div class="modal fade" id="editFormulaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" id="editFormulaForm" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">✏️ Edit Formula: <span id="editModalFormulaTitle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Formula Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Formula Code / Identifier</label>
                        <input type="text" id="editCode" class="form-control font-monospace bg-light" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category" id="editCategory" class="form-select" required>
                            <option value="earnings">Earnings & Base</option>
                            <option value="deduction">Deduction</option>
                            <option value="bonus">Bonus & Incentive</option>
                            <option value="custom">Employer Contribution & CTC</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Effective From Date <span class="text-danger">*</span></label>
                        <input type="date" name="effective_from" id="editEffectiveFrom" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Effective Until (Optional)</label>
                        <input type="date" name="effective_to" id="editEffectiveTo" class="form-control">
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive" value="1">
                            <label class="form-check-label fw-semibold" for="editIsActive">Active & Effective</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Mathematical Formula Expression <span class="text-danger">*</span></label>
                        <textarea name="formula" id="editFormulaExpr" class="form-control font-monospace" rows="3" required style="font-size: 14px; background: #f8fafc;"></textarea>
                        <div class="mt-2">
                            <small class="text-muted fw-semibold d-block mb-1">Click a variable to insert into formula:</small>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($commonVariables as $varKey => $varDesc)
                                    <span class="var-pill" title="{{ $varDesc }}" onclick="insertVariable('editFormulaExpr', '{{ $varKey }}')">
                                        + {{ $varKey }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Description / Purpose</label>
                        <input type="text" name="description" id="editDescription" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-bold">Update & Make Effective</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: Live Interactive Tester Modal --}}
<div class="modal fade" id="testFormulaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">🧪 Live Formula Tester</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-semibold small text-muted text-uppercase mb-0">Testing Formula Expression</label>
                        <span id="testModalFormulaName" class="badge bg-light text-dark border"></span>
                    </div>
                    <textarea id="testModalFormulaExpr" class="form-control font-monospace" rows="2" style="font-size: 13px; background: #0f172a; color: #38bdf8; border: 1px solid #1e293b;" oninput="refreshTestVariablesFromInput()"></textarea>
                    <small class="text-muted" style="font-size: 11px;">You can edit the expression above to test different conditions and values live.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Variable Values for Test Calculation:</label>
                    <div id="testVariablesInputs" class="d-flex flex-column gap-2"></div>
                </div>

                <div id="testResultBox" class="p-3 rounded text-center d-none" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                    <div class="small text-muted fw-bold text-uppercase">Evaluated Calculation Result</div>
                    <div id="testResultVal" class="fs-3 fw-bold text-success"></div>
                </div>
                <div id="testErrorBox" class="p-3 rounded text-center d-none" style="background: #fef2f2; border: 1px solid #fecaca;">
                    <div class="small text-danger fw-bold" id="testErrorMsg"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary fw-bold" onclick="runLiveTest()">
                    <i class="bx bx-calculator me-1"></i> Calculate Live Result
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function insertVariable(targetId, varName) {
    const el = document.getElementById(targetId);
    if (!el) return;
    const start = el.selectionStart || el.value.length;
    const end = el.selectionEnd || el.value.length;
    const text = el.value;
    const before = text.substring(0, start);
    const after = text.substring(end, text.length);
    el.value = (before.length > 0 && !before.endsWith(' ') ? before + ' ' : before) + varName + ' ' + after;
    el.focus();
}

function openEditModal(formula) {
    document.getElementById('editModalFormulaTitle').innerText = formula.name;
    document.getElementById('editFormulaForm').action = '/payroll/formulas/' + formula.id;
    document.getElementById('editName').value = formula.name;
    document.getElementById('editCode').value = formula.code;
    document.getElementById('editCategory').value = formula.category;
    document.getElementById('editEffectiveFrom').value = formula.effective_from ? formula.effective_from.split('T')[0] : '{{ date("Y-m-01") }}';
    document.getElementById('editEffectiveTo').value = formula.effective_to ? formula.effective_to.split('T')[0] : '';
    document.getElementById('editIsActive').checked = !!formula.is_active;
    document.getElementById('editFormulaExpr').value = formula.formula;
    document.getElementById('editDescription').value = formula.description || '';

    const modal = new bootstrap.Modal(document.getElementById('editFormulaModal'));
    modal.show();
}

let activeTestSampleInputs = {};

function openTestModal(name, formula, sampleInputs) {
    document.getElementById('testModalFormulaName').innerText = name;
    document.getElementById('testModalFormulaExpr').value = formula;
    activeTestSampleInputs = sampleInputs || {};

    refreshTestVariablesFromInput();

    const modal = new bootstrap.Modal(document.getElementById('testFormulaModal'));
    modal.show();
}

function refreshTestVariablesFromInput() {
    const formula = document.getElementById('testModalFormulaExpr').value;
    const container = document.getElementById('testVariablesInputs');
    container.innerHTML = '';
    document.getElementById('testResultBox').classList.add('d-none');
    document.getElementById('testErrorBox').classList.add('d-none');

    // Extract uppercase variable names
    const regex = /\b[A-Z_]{2,}\b/g;
    const matches = Array.from(new Set(formula.match(regex) || []));

    if (matches.length === 0) {
        container.innerHTML = '<small class="text-muted">Formula uses direct constant values. Click Calculate to evaluate.</small>';
    } else {
        matches.forEach(v => {
            const defaultVal = activeTestSampleInputs && activeTestSampleInputs[v] !== undefined
                ? activeTestSampleInputs[v]
                : (v.includes('RATE') || v.includes('PERCENTAGE') ? 50 : (v.includes('HOURS') ? 185 : (v.includes('RANK') ? 1 : 30000)));

            const row = document.createElement('div');
            row.className = 'row align-items-center g-2';
            row.innerHTML = `
                <div class="col-6">
                    <span class="font-monospace fw-bold small text-dark">${v}</span>
                </div>
                <div class="col-6">
                    <input type="number" step="0.01" class="form-control form-control-sm test-var-input" data-var="${v}" value="${defaultVal}">
                </div>
            `;
            container.appendChild(row);
        });
    }
}

function runLiveTest() {
    const formula = document.getElementById('testModalFormulaExpr').value;
    const inputs = {};
    document.querySelectorAll('.test-var-input').forEach(input => {
        inputs[input.dataset.var] = parseFloat(input.value) || 0;
    });

    const resBox = document.getElementById('testResultBox');
    const errBox = document.getElementById('testErrorBox');
    resBox.classList.add('d-none');
    errBox.classList.add('d-none');

    fetch('{{ route("payroll.formulas.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            formula: formula,
            variables: inputs
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('testResultVal').innerText = data.formatted;
            resBox.classList.remove('d-none');
        } else {
            document.getElementById('testErrorMsg').innerText = data.error || 'Evaluation error';
            errBox.classList.remove('d-none');
        }
    })
    .catch(err => {
        document.getElementById('testErrorMsg').innerText = 'Request failed: ' + err.message;
        errBox.classList.remove('d-none');
    });
}
</script>
@endsection
