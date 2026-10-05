@extends('admin.layout.app')

@section('title', 'Payroll Architectures')

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
            <h4 class="fw-bold mb-1"><i class="bx bx-building-house text-primary me-2"></i>Payroll Architectures</h4>
            <p class="text-muted mb-0">Define organizational compensation models and compensation frameworks</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payroll.salary-structures.index') }}" class="btn btn-outline-primary fw-semibold">
                <i class="bx bx-layer me-1"></i> Salary Structures
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
                        <div class="text-muted small fw-semibold">TOTAL FRAMEWORKS</div>
                        <div class="fs-4 fw-bold text-primary mt-1">{{ $architectures->count() }}</div>
                    </div>
                    <div class="avatar avatar-md bg-label-primary rounded p-2">
                        <i class="bx bx-git-branch fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">ACTIVE FRAMEWORK</div>
                        @php $activeArch = $architectures->firstWhere('is_active', true); @endphp
                        <div class="fs-6 fw-bold text-success mt-1 text-truncate" style="max-width:140px;">
                            {{ $activeArch ? $activeArch->name : 'None Active' }}
                        </div>
                    </div>
                    <div class="avatar avatar-md bg-label-success rounded p-2">
                        <i class="bx bx-check-shield fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">FRAMEWORK TYPES</div>
                        <div class="fs-4 fw-bold text-info mt-1">{{ count($types) }}</div>
                    </div>
                    <div class="avatar avatar-md bg-label-info rounded p-2">
                        <i class="bx bx-category fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">LATEST VERSION</div>
                        <div class="fs-4 fw-bold text-warning mt-1">v{{ $architectures->max('version') ?? 1 }}.0</div>
                    </div>
                    <div class="avatar avatar-md bg-label-warning rounded p-2">
                        <i class="bx bx-revision fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main 2-Column Section --}}
    <div class="row g-4">
        {{-- Left: Create Architecture Form --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-plus-circle text-primary me-2"></i>New Architecture</h5>
                </div>
                <div class="card-body pt-3">
                    <form method="POST" action="{{ route('payroll.architectures.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Architecture Name <span class="text-danger">*</span></label>
                            <input name="name" class="form-control" placeholder="e.g. Standard Corporate 2026" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Compensation Model <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                @foreach($types as $type)
                                    <option value="{{ $type }}" @selected($loop->first)>{{ Str::headline($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Effective Date</label>
                            <input type="date" name="effective_date" class="form-control" value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Briefly describe what employee classes or pay scales this applies to..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                            <i class="bx bx-save me-1"></i> Save Architecture
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right: Architectures Table --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-list-ul text-primary me-2"></i>Configured Architectures</h5>
                    <span class="badge bg-primary-subtle text-primary fw-bold">{{ $architectures->count() }} Frameworks</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#f8fafc;font-size:12px;">
                            <tr class="text-muted">
                                <th class="px-4 fw-semibold">NAME & CODE</th>
                                <th class="fw-semibold">MODEL TYPE</th>
                                <th class="fw-semibold">VERSION</th>
                                <th class="fw-semibold">EFFECTIVE</th>
                                <th class="fw-semibold">STATUS</th>
                                <th class="fw-semibold text-end pe-4">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($architectures as $architecture)
                                @php
                                    $typeBadge = match(strtolower($architecture->type)) {
                                        'standard' => 'primary',
                                        'startup' => 'info',
                                        'hourly' => 'warning',
                                        'contract' => 'dark',
                                        'project_based' => 'success',
                                        'commission_based' => 'secondary',
                                        default => 'light text-dark'
                                    };
                                @endphp
                                <tr>
                                    <td class="px-4">
                                        <div class="fw-bold text-dark">{{ $architecture->name }}</div>
                                        <small class="text-muted font-monospace">{{ $architecture->code }}</small>
                                        @if($architecture->description)
                                            <div class="text-muted small text-truncate" style="max-width:220px;">{{ $architecture->description }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $typeBadge }}-subtle text-{{ $typeBadge }} fw-semibold">
                                            {{ Str::headline($architecture->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border font-monospace">v{{ $architecture->version }}.0</span>
                                    </td>
                                    <td class="small text-muted">
                                        {{ $architecture->effective_date ? \Carbon\Carbon::parse($architecture->effective_date)->format('d M Y') : 'Immediate' }}
                                    </td>
                                    <td>
                                        @if($architecture->is_active)
                                            <span class="badge bg-success-subtle text-success fw-bold px-2 py-1">
                                                <i class="bx bx-check-circle me-1"></i>Active
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted border px-2 py-1">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-1">
                                            @unless($architecture->is_active)
                                                <form method="POST" action="{{ route('payroll.architectures.activate', $architecture) }}" class="d-inline">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Activate this Architecture">
                                                        <i class="bx bx-check me-1"></i>Activate
                                                    </button>
                                                </form>
                                            @endunless
                                            <form method="POST" action="{{ route('payroll.architectures.destroy', $architecture) }}" class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this architecture?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Delete Architecture">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bx bx-building-house fs-1 d-block mb-2 opacity-25"></i>
                                        No payroll architectures configured yet. Use the form on the left to add one.
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
