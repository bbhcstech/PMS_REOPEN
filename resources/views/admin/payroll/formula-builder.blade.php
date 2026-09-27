@extends('admin.layout.app')

@section('title', 'Formula Builder')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#1e293b 0%,#334155 100%);border-radius:14px;">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="text-white">
                    <h4 class="fw-bold mb-1"><i class="bx bx-code-alt me-2"></i>Formula Builder</h4>
                    <p class="opacity-75 mb-0">Build and validate custom payroll calculation formulas using variable expressions</p>
                </div>
                <button class="btn btn-light fw-bold" data-bs-toggle="modal" data-bs-target="#createFormulaModal">
                    <i class="bx bx-plus me-1"></i> New Formula
                </button>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Formula Editor Panel --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                    <h5 class="fw-bold mb-0"><i class="bx bx-terminal text-primary me-2"></i>Live Formula Tester</h5>
                    <p class="text-muted small mb-0">Enter a formula and test values to validate output</p>
                </div>
                <div class="card-body px-4">
                    {{-- Formula Input --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Formula Expression</label>
                        <textarea id="liveFormula" class="form-control font-monospace" rows="3"
                            placeholder="e.g. BASIC * 0.12 + (HRA * 0.5)"
                            style="font-size:14px;border-radius:10px;"></textarea>
                        <div class="form-text">Use uppercase variable names (BASIC, HRA, GROSS, etc.)</div>
                    </div>

                    {{-- Available Variables --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-muted" style="letter-spacing:.05em;">Available Variables — click to insert</label>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($variables as $var)
                                <button type="button" class="btn btn-xs btn-outline-primary var-chip fw-semibold"
                                    style="font-size:11px;padding:2px 8px;border-radius:6px;" data-var="{{ $var }}">
                                    {{ $var }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Test Inputs --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Test Input Values</label>
                        <div id="testInputs">
                            <div class="row g-2 mb-2">
                                <div class="col-5"><input type="text" class="form-control form-control-sm var-key" placeholder="Variable (e.g. BASIC)" style="font-family:monospace;"></div>
                                <div class="col-5"><input type="number" class="form-control form-control-sm var-val" placeholder="Value" step="0.01"></div>
                                <div class="col-2"><button type="button" class="btn btn-sm btn-outline-secondary w-100 add-var-row">+</button></div>
                            </div>
                        </div>
                        <button id="validateBtn" class="btn btn-primary fw-bold w-100 mt-2" style="border-radius:10px;">
                            <i class="bx bx-check-shield me-1"></i> Validate Formula
                        </button>
                    </div>

                    {{-- Result Area --}}
                    <div id="resultArea" class="d-none p-3 rounded-3 text-center mb-2">
                        <div class="result-label small text-muted fw-bold text-uppercase mb-1" style="letter-spacing:.05em;">Result</div>
                        <div class="result-value fw-bold fs-4"></div>
                        <div class="result-error text-danger small fw-semibold mt-1"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Saved Formulas --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="bx bx-list-ul text-primary me-2"></i>Saved Formulas ({{ method_exists($formulas, 'total') ? $formulas->total() : $formulas->count() }})</h5>
                        <input type="text" id="formulaSearch" class="form-control form-control-sm" placeholder="Search..." style="width:180px;">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="formulaTable">
                        <thead style="background:#f8fafc;font-size:12px;">
                            <tr class="text-muted">
                                <th class="px-4 fw-semibold text-uppercase">Name / Code</th>
                                <th class="fw-semibold text-uppercase">Category</th>
                                <th class="fw-semibold text-uppercase">Formula</th>
                                <th class="fw-semibold text-uppercase">Valid</th>
                                <th class="fw-semibold text-end pe-4 text-uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($formulas as $formula)
                                <tr>
                                    <td class="px-4">
                                        <div class="fw-semibold small">{{ $formula->name }}</div>
                                        <div class="font-monospace text-muted" style="font-size:11px;">{{ $formula->code }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $catColors = ['earnings'=>'success','deduction'=>'danger','tax'=>'warning','bonus'=>'info','custom'=>'primary'];
                                            $catColor = $catColors[$formula->category] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-label-{{ $catColor }}">{{ ucfirst($formula->category) }}</span>
                                    </td>
                                    <td>
                                        <code class="text-muted small d-block" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $formula->formula }}">
                                            {{ $formula->formula }}
                                        </code>
                                        @if($formula->variables_used)
                                            <div class="d-flex gap-1 flex-wrap mt-1">
                                                @foreach(array_slice($formula->variables_used, 0, 3) as $v)
                                                    <span class="badge bg-label-secondary" style="font-size:10px;">{{ $v }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-label-{{ $formula->is_valid ? 'success' : 'warning' }}">
                                            {{ $formula->is_valid ? '✓ Valid' : 'Pending' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-xs btn-outline-primary load-formula me-1"
                                            data-formula="{{ $formula->formula }}"
                                            style="font-size:11px;padding:3px 8px;">Load</button>
                                        <form method="POST" action="{{ route('payroll.formula-builder.destroy', $formula) }}" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger"
                                                style="font-size:11px;padding:3px 8px;"
                                                onclick="return confirm('Delete formula: {{ $formula->name }}?')">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bx bx-code-alt fs-1 d-block mb-2 opacity-30"></i>
                                        No formulas saved yet. Use the tester on the left to build and save formulas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(method_exists($formulas, 'hasPages') && $formulas->hasPages())
                    <div class="p-3">{{ $formulas->links() }}</div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- Create Formula Modal --}}
<div class="modal fade" id="createFormulaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:14px;">
            <div class="modal-header border-0 bg-primary text-white" style="border-radius:14px 14px 0 0;">
                <h5 class="modal-title fw-bold"><i class="bx bx-code-alt me-2"></i>Save Formula</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('payroll.formula-builder.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Formula Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" placeholder="e.g. PF Contribution Employee Side" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ old('category')===$cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Formula Expression <span class="text-danger">*</span></label>
                            <textarea name="formula" id="saveFormula" class="form-control font-monospace @error('formula') is-invalid @enderror"
                                rows="3" placeholder="e.g. BASIC * 0.12" required>{{ old('formula') }}</textarea>
                            @error('formula') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Only arithmetic operators (+, -, *, /) and uppercase variable names allowed.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2"
                                placeholder="Optional: explain what this formula calculates...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bx bx-save me-1"></i> Save Formula
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Insert variable into formula textarea
document.querySelectorAll('.var-chip').forEach(btn => {
    btn.addEventListener('click', function() {
        const ta = document.getElementById('liveFormula');
        const v = this.dataset.var;
        const pos = ta.selectionStart;
        ta.value = ta.value.slice(0, pos) + v + ta.value.slice(pos);
        ta.focus();
    });
});

// Load formula from table into tester
document.querySelectorAll('.load-formula').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('liveFormula').value = this.dataset.formula;
    });
});

// Add variable row
document.querySelectorAll('.add-var-row').forEach(btn => {
    btn.addEventListener('click', function() {
        const container = document.getElementById('testInputs');
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2';
        row.innerHTML = `
            <div class="col-5"><input type="text" class="form-control form-control-sm var-key" placeholder="Variable" style="font-family:monospace;"></div>
            <div class="col-5"><input type="number" class="form-control form-control-sm var-val" placeholder="Value" step="0.01"></div>
            <div class="col-2"><button type="button" class="btn btn-sm btn-outline-danger w-100 remove-var-row">−</button></div>
        `;
        container.appendChild(row);
        row.querySelector('.remove-var-row').addEventListener('click', () => row.remove());
    });
});

// Validate formula
document.getElementById('validateBtn').addEventListener('click', function() {
    const formula = document.getElementById('liveFormula').value;
    const keys   = document.querySelectorAll('.var-key');
    const vals   = document.querySelectorAll('.var-val');
    const inputs = {};
    keys.forEach((k, i) => { if (k.value) inputs[k.value.toUpperCase()] = parseFloat(vals[i]?.value || 0); });

    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Validating...';

    fetch('{{ route('payroll.formula-builder.validate') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ formula, inputs })
    })
    .then(r => r.json())
    .then(data => {
        const area = document.getElementById('resultArea');
        area.classList.remove('d-none');
        if (data.valid) {
            area.style.background = 'linear-gradient(135deg,#ecfdf5,#d1fae5)';
            area.style.border = '2px solid #10b981';
            area.querySelector('.result-value').textContent = '₹ ' + Number(data.result).toLocaleString('en-IN', {minimumFractionDigits:2});
            area.querySelector('.result-value').style.color = '#059669';
            area.querySelector('.result-error').textContent = '';
            // Also set the save modal formula
            document.getElementById('saveFormula').value = formula;
        } else {
            area.style.background = 'linear-gradient(135deg,#fef2f2,#fee2e2)';
            area.style.border = '2px solid #ef4444';
            area.querySelector('.result-value').textContent = 'Error';
            area.querySelector('.result-value').style.color = '#ef4444';
            area.querySelector('.result-error').textContent = data.error || 'Invalid formula';
        }
    })
    .catch(() => {
        alert('Request failed. Please try again.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bx bx-check-shield me-1"></i> Validate Formula';
    });
});

// Search formulas
document.getElementById('formulaSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#formulaTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endsection
