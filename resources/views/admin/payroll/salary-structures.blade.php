@extends('admin.layout.app')

@section('title', 'Salary Structures')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bx bx-layer text-primary me-2"></i>Salary Structures</h4>
            <p class="text-muted mb-0">Build salary grading templates, define component rules, and configure compensation packages</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payroll.architectures.index') }}" class="btn btn-outline-primary fw-semibold">
                <i class="bx bx-building-house me-1"></i> Architectures
            </a>
            <a href="{{ route('payroll.formula-builder.index') }}" class="btn btn-outline-secondary fw-semibold">
                <i class="bx bx-math me-1"></i> Formula Builder
            </a>
            <a href="{{ route('payroll.processing') }}" class="btn btn-primary fw-bold shadow-sm">
                <i class="bx bx-calculator me-1"></i> Payroll Processing
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">STRUCTURES</div>
                        <div class="fs-4 fw-bold text-primary mt-1">{{ $structures->count() }}</div>
                    </div>
                    <div class="avatar avatar-md bg-label-primary rounded p-2">
                        <i class="bx bx-grid-alt fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">TOTAL COMPONENTS</div>
                        <div class="fs-4 fw-bold text-info mt-1">{{ $components->count() }}</div>
                    </div>
                    <div class="avatar avatar-md bg-label-info rounded p-2">
                        <i class="bx bx-pie-chart-alt fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">TAXABLE COMPONENTS</div>
                        <div class="fs-4 fw-bold text-warning mt-1">{{ $components->where('taxable', true)->count() }}</div>
                    </div>
                    <div class="avatar avatar-md bg-label-warning rounded p-2">
                        <i class="bx bx-coin-stack fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">MANDATORY RULES</div>
                        <div class="fs-4 fw-bold text-success mt-1">{{ $components->where('required', true)->count() }}</div>
                    </div>
                    <div class="avatar avatar-md bg-label-success rounded p-2">
                        <i class="bx bx-check-shield fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main 2-Column Section --}}
    <div class="row g-4">
        {{-- Left: Forms for Structure & Component --}}
        <div class="col-lg-4">
            {{-- Form 1: New Salary Structure --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-plus-circle text-primary me-2"></i>New Salary Structure</h5>
                </div>
                <div class="card-body pt-3">
                    <form method="POST" action="{{ route('payroll.salary-structures.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Structure Name <span class="text-danger">*</span></label>
                            <input name="name" class="form-control" placeholder="e.g. Senior Tech Grade A" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Effective Date</label>
                            <input type="date" name="effective_date" class="form-control" value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Grade tier, employee level, or package scope..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                            <i class="bx bx-save me-1"></i> Save Structure
                        </button>
                    </form>
                </div>
            </div>

            {{-- Form 2: Add Component --}}
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-coin text-primary me-2"></i>Add Component</h5>
                </div>
                <div class="card-body pt-3">
                    <form method="POST" action="{{ route('payroll.salary-components.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Target Structure</label>
                            <select name="salary_structure_id" class="form-select">
                                <option value="">Global (All Structures)</option>
                                @foreach($structures as $structure)
                                    <option value="{{ $structure->id }}">{{ $structure->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Component Name <span class="text-danger">*</span></label>
                            <input name="name" class="form-control" placeholder="e.g. Medical Allowance" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold text-muted small text-uppercase">Type</label>
                                <select name="component_type" class="form-select">
                                    <option value="basic">Basic</option>
                                    <option value="allowance" selected>Allowance</option>
                                    <option value="bonus">Bonus</option>
                                    <option value="deduction">Deduction</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold text-muted small text-uppercase">Calculation</label>
                                <select name="calculation_type" class="form-select">
                                    <option value="fixed">Fixed</option>
                                    <option value="percentage">Percentage</option>
                                    <option value="formula">Formula</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Value (if fixed/percentage)</label>
                            <input type="number" step="0.01" name="value" class="form-control" placeholder="Amount or %">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Formula (if formula-based)</label>
                            <textarea name="formula" class="form-control" rows="2" placeholder="BASIC * 0.40"></textarea>
                        </div>
                        <div class="d-flex gap-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="taxable" id="taxableCheck" value="1" checked>
                                <label class="form-check-label small fw-semibold" for="taxableCheck">Taxable</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="required" id="requiredCheck" value="1">
                                <label class="form-check-label small fw-semibold" for="requiredCheck">Mandatory</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline-primary w-100 fw-bold">
                            <i class="bx bx-plus me-1"></i> Add Component
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right: Structures & Components Tables --}}
        <div class="col-lg-8">
            {{-- Table 1: Salary Structures --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-grid-alt text-primary me-2"></i>Configured Structures</h5>
                    <span class="badge bg-primary-subtle text-primary fw-bold">{{ $structures->count() }} Structures</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#f8fafc;font-size:12px;">
                            <tr class="text-muted">
                                <th class="px-4 fw-semibold">STRUCTURE</th>
                                <th class="fw-semibold">CODE</th>
                                <th class="fw-semibold">VERSION</th>
                                <th class="fw-semibold text-center">COMPONENTS</th>
                                <th class="fw-semibold">EFFECTIVE</th>
                                <th class="fw-semibold text-end pe-4">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($structures as $structure)
                                <tr>
                                    <td class="px-4">
                                        <div class="fw-bold text-dark">{{ $structure->name }}</div>
                                        @if($structure->description)
                                            <small class="text-muted text-truncate d-block" style="max-width:200px;">{{ $structure->description }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-monospace small text-muted">{{ $structure->code }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border font-monospace">v{{ $structure->version }}.0</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info-subtle text-info fw-bold">{{ $structure->components_count }} items</span>
                                    </td>
                                    <td class="small text-muted">
                                        {{ $structure->effective_date ? \Carbon\Carbon::parse($structure->effective_date)->format('d M Y') : 'Active' }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <span class="badge bg-success-subtle text-success fw-bold px-2 py-1">
                                            <i class="bx bx-check-circle me-1"></i>Active
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No salary structures found. Add your first structure using the form on the left.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Table 2: Salary Components --}}
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-pie-chart-alt text-primary me-2"></i>Active Components Breakdown</h5>
                    <span class="badge bg-info-subtle text-info fw-bold">{{ $components->count() }} Components</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#f8fafc;font-size:12px;">
                            <tr class="text-muted">
                                <th class="px-4 fw-semibold">COMPONENT</th>
                                <th class="fw-semibold">STRUCTURE</th>
                                <th class="fw-semibold">TYPE</th>
                                <th class="fw-semibold">CALCULATION</th>
                                <th class="fw-semibold text-center">TAXABLE</th>
                                <th class="fw-semibold text-center">REQUIRED</th>
                                <th class="fw-semibold text-end pe-4">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($components as $component)
                                @php
                                    $typeBadge = match(strtolower($component->component_type)) {
                                        'basic' => 'primary',
                                        'allowance' => 'success',
                                        'bonus' => 'warning',
                                        'deduction' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <tr>
                                    <td class="px-4">
                                        <div class="fw-bold text-dark">{{ $component->name }}</div>
                                        <small class="text-muted font-monospace">{{ $component->code }}</small>
                                    </td>
                                    <td class="small">
                                        <span class="badge bg-light text-dark border">
                                            {{ $component->salaryStructure?->name ?? 'Global' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $typeBadge }}-subtle text-{{ $typeBadge }} fw-semibold">
                                            {{ ucfirst($component->component_type) }}
                                        </span>
                                    </td>
                                    <td class="small">
                                        <span class="fw-semibold">{{ ucfirst($component->calculation_type) }}</span>
                                        @if($component->value)
                                            <span class="text-muted">({{ $component->value }})</span>
                                        @elseif($component->formula)
                                            <span class="font-monospace text-muted d-block small">{{ Str::limit($component->formula, 20) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($component->taxable)
                                            <span class="badge bg-warning-subtle text-warning">Yes</span>
                                        @else
                                            <span class="badge bg-light text-muted border">No</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($component->required)
                                            <span class="badge bg-primary-subtle text-primary">Yes</span>
                                        @else
                                            <span class="badge bg-light text-muted border">No</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <form method="POST" action="{{ route('payroll.salary-components.destroy', $component) }}" class="d-inline"
                                            onsubmit="return confirm('Delete component {{ $component->name }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Delete Component">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        No salary components configured yet. Add one using the form on the left.
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
