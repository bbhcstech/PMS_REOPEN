@extends('admin.layout.app')

@section('title', $title)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $iconClass = match($slug) {
            'deduction-rules' => 'bx-minus-circle text-danger',
            'bonus-rules' => 'bx-gift text-warning',
            'tax-rules' => 'bx-receipt text-primary',
            'overtime-rules' => 'bx-time text-info',
            default => 'bx-slider-alt text-primary'
        };
        $accentColor = match($slug) {
            'deduction-rules' => 'danger',
            'bonus-rules' => 'warning',
            'tax-rules' => 'primary',
            'overtime-rules' => 'info',
            default => 'primary'
        };
    @endphp

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bx {{ $iconClass }} me-2"></i>{{ $title }}</h4>
            <p class="text-muted mb-0">Configure calculation algorithms, thresholds, and statutory guidelines for {{ strtolower($title) }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payroll.policies.index') }}" class="btn btn-outline-primary fw-semibold">
                <i class="bx bx-shield-quarter me-1"></i> Policy Engine
            </a>
            <a href="{{ route('payroll.formula-builder.index') }}" class="btn btn-outline-secondary fw-semibold">
                <i class="bx bx-math me-1"></i> Formula Builder
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">TOTAL RULES</div>
                        <div class="fs-4 fw-bold text-{{ $accentColor }} mt-1">{{ $rules->count() }}</div>
                    </div>
                    <div class="avatar avatar-md bg-label-{{ $accentColor }} rounded p-2">
                        <i class="bx {{ explode(' ', $iconClass)[0] }} fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">ACTIVE RULES</div>
                        <div class="fs-4 fw-bold text-success mt-1">{{ $rules->where('is_active', true)->count() ?: $rules->count() }}</div>
                    </div>
                    <div class="avatar avatar-md bg-label-success rounded p-2">
                        <i class="bx bx-check-circle fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">FORMULA RULES</div>
                        <div class="fs-4 fw-bold text-info mt-1">{{ $rules->whereNotNull('formula')->count() }}</div>
                    </div>
                    <div class="avatar avatar-md bg-label-info rounded p-2">
                        <i class="bx bx-code-alt fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">ACTIVE POLICY</div>
                        <div class="fs-6 fw-bold text-dark mt-1">Enterprise Standard</div>
                    </div>
                    <div class="avatar avatar-md bg-label-secondary rounded p-2">
                        <i class="bx bx-cog fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Section --}}
    <div class="row g-4">
        {{-- Left: Create Rule Form --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-plus-circle text-{{ $accentColor }} me-2"></i>New {{ Str::singular($title) }}</h5>
                </div>
                <div class="card-body pt-3">
                    <form method="POST" action="{{ route('payroll.' . $slug . '.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Rule Name <span class="text-danger">*</span></label>
                            <input name="name" class="form-control" placeholder="e.g. Standard Provident Fund" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Category / Type</label>
                            <input name="rule_type" class="form-control" placeholder="e.g. statutory, discretionary, performance">
                        </div>

                        @if($slug === 'tax-rules')
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted small text-uppercase">Jurisdiction / Country</label>
                                <select name="country" class="form-select">
                                    <option value="India" selected>India (New Regime / Old Regime)</option>
                                    <option value="USA">USA (Federal / State)</option>
                                    <option value="UK">UK (HMRC PAYE)</option>
                                    <option value="Custom">Custom Region</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted small text-uppercase">Tax Slabs (JSON)</label>
                                <textarea name="slabs" class="form-control font-monospace small" rows="2" placeholder='[{"min": 0, "max": 300000, "rate": 0}, {"min": 300000, "max": 700000, "rate": 5}]'></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted small text-uppercase">Exemptions & Standard Deductions (JSON)</label>
                                <textarea name="exemptions" class="form-control font-monospace small" rows="2" placeholder='{"standard_deduction": 75000, "section_80c": 150000}'></textarea>
                            </div>
                        @elseif($slug === 'overtime-rules')
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted small text-uppercase">Rate Multiplier <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.05" name="multiplier" class="form-control" value="1.50" required>
                                    <span class="input-group-text font-monospace">x Hourly Rate</span>
                                </div>
                            </div>
                        @else
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold text-muted small text-uppercase">Calculation</label>
                                    <select name="calculation_type" class="form-select">
                                        <option value="percentage">Percentage (%)</option>
                                        <option value="fixed">Fixed Amount</option>
                                        <option value="formula">Formula</option>
                                        <option value="conditional">Conditional</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold text-muted small text-uppercase">Value</label>
                                    <input type="number" step="0.01" name="value" class="form-control" placeholder="Rate or Amount">
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Formula (Optional)</label>
                            <textarea name="formula" class="form-control font-monospace small" rows="3" placeholder="IF (GROSS > 50000) THEN BASIC * 0.12 ELSE 1800"></textarea>
                            <small class="text-muted d-block mt-1">Available tokens: BASIC, HRA, SPECIAL, GROSS, PRESENT_DAYS</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Effective Date</label>
                            <input type="date" name="effective_date" class="form-control" value="{{ date('Y-m-01') }}">
                        </div>

                        <button type="submit" class="btn btn-{{ $accentColor }} w-100 fw-bold shadow-sm">
                            <i class="bx bx-save me-1"></i> Save {{ Str::singular($title) }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right: Rules List Table --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-list-check text-{{ $accentColor }} me-2"></i>Active {{ $title }}</h5>
                    <span class="badge bg-{{ $accentColor }}-subtle text-{{ $accentColor }} fw-bold">{{ $rules->count() }} Configured</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#f8fafc;font-size:12px;">
                            <tr class="text-muted">
                                <th class="px-4 fw-semibold">RULE NAME & CODE</th>
                                <th class="fw-semibold">TYPE / CALC</th>
                                <th class="fw-semibold">VALUE / SPEC</th>
                                <th class="fw-semibold">FORMULA</th>
                                <th class="fw-semibold">STATUS</th>
                                <th class="fw-semibold text-end pe-4">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rules as $rule)
                                <tr>
                                    <td class="px-4">
                                        <div class="fw-bold text-dark">{{ $rule->name }}</div>
                                        <small class="text-muted font-monospace">{{ $rule->code }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $typeVal = $rule->rule_type ?? ($rule->deduction_type ?? ($rule->bonus_type ?? ($rule->tax_type ?? ($rule->overtime_type ?? 'Standard'))));
                                        @endphp
                                        <span class="badge bg-light text-dark border font-monospace small">
                                            {{ Str::headline($typeVal) }}
                                        </span>
                                    </td>
                                    <td class="small">
                                        @if(isset($rule->multiplier))
                                            <span class="badge bg-info-subtle text-info fw-bold">{{ $rule->multiplier }}x Rate</span>
                                        @elseif(isset($rule->calculation_type))
                                            <span class="fw-semibold">{{ ucfirst($rule->calculation_type) }}</span>
                                            @if($rule->value)
                                                <span class="text-muted">({{ $rule->value }}{{ $rule->calculation_type === 'percentage' ? '%' : '' }})</span>
                                            @endif
                                        @elseif(isset($rule->country))
                                            <span class="fw-semibold text-dark"><i class="bx bx-globe me-1"></i>{{ $rule->country }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if($rule->formula)
                                            <code class="text-primary small bg-light p-1 rounded font-monospace" title="{{ $rule->formula }}">
                                                {{ Str::limit($rule->formula, 28) }}
                                            </code>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($rule->is_active ?? true)
                                            <span class="badge bg-success-subtle text-success fw-bold px-2 py-1">
                                                <i class="bx bx-check-circle me-1"></i>Active
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted border px-2 py-1">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <form method="POST" action="{{ route('payroll.' . $slug . '.destroy', $rule->id) }}" class="d-inline"
                                            onsubmit="return confirm('Delete rule {{ $rule->name }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Delete Rule">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bx bx-slider-alt fs-1 d-block mb-2 opacity-25"></i>
                                        No {{ strtolower($title) }} configured yet. Create one using the form on the left.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
