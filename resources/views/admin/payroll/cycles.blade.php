@extends('admin.layout.app')

@section('title', 'Payroll Cycles')

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
            <h4 class="fw-bold mb-1"><i class="bx bx-time-five text-primary me-2"></i>Payroll Cycles</h4>
            <p class="text-muted mb-0">Schedule and orchestrate monthly and periodic salary cutoffs and payment windows</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payroll.processing') }}" class="btn btn-primary fw-bold shadow-sm">
                <i class="bx bx-calculator me-1"></i> Open Processing Sheet
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">TOTAL CYCLES</div>
                        <div class="fs-4 fw-bold text-primary mt-1">{{ $cycles->count() }}</div>
                    </div>
                    <div class="avatar avatar-md bg-label-primary rounded p-2">
                        <i class="bx bx-calendar-event fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">OPEN CYCLES</div>
                        <div class="fs-4 fw-bold text-success mt-1">{{ $cycles->where('status', 'open')->count() }}</div>
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
                        <div class="text-muted small fw-semibold">PROCESSING</div>
                        <div class="fs-4 fw-bold text-warning mt-1">{{ $cycles->where('status', 'processing')->count() }}</div>
                    </div>
                    <div class="avatar avatar-md bg-label-warning rounded p-2">
                        <i class="bx bx-loader fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">CLOSED CYCLES</div>
                        <div class="fs-4 fw-bold text-secondary mt-1">{{ $cycles->where('status', 'closed')->count() }}</div>
                    </div>
                    <div class="avatar avatar-md bg-label-secondary rounded p-2">
                        <i class="bx bx-lock-alt fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Section --}}
    <div class="row g-4">
        {{-- Left: Create Cycle Form --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-plus-circle text-primary me-2"></i>Create Payroll Cycle</h5>
                </div>
                <div class="card-body pt-3">
                    <form method="POST" action="{{ route('payroll.cycles.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Cycle Name <span class="text-danger">*</span></label>
                            <input name="name" class="form-control" placeholder="e.g. October 2026 Regular Cycle" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Cycle Frequency <span class="text-danger">*</span></label>
                            <select name="cycle_type" class="form-select" required>
                                @foreach($cycleTypes as $type)
                                    <option value="{{ $type }}" @selected($type === 'monthly')>{{ Str::headline($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold text-muted small text-uppercase">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-01') }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold text-muted small text-uppercase">End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control" value="{{ date('Y-m-t') }}" required>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold text-muted small text-uppercase">Pay Date</label>
                                <input type="date" name="pay_date" class="form-control" value="{{ date('Y-m-05', strtotime('+1 month')) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold text-muted small text-uppercase">Lock Date</label>
                                <input type="date" name="lock_date" class="form-control" value="{{ date('Y-m-07', strtotime('+1 month')) }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Description / Notes</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional notes for payroll team..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                            <i class="bx bx-calendar-plus me-1"></i> Schedule Cycle
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right: Cycles List Table --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-list-check text-primary me-2"></i>Cycles Schedule</h5>
                    <span class="badge bg-primary-subtle text-primary fw-bold">{{ $cycles->count() }} Schedules</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#f8fafc;font-size:12px;">
                            <tr class="text-muted">
                                <th class="px-4 fw-semibold">CYCLE NAME</th>
                                <th class="fw-semibold">FREQUENCY</th>
                                <th class="fw-semibold">PERIOD</th>
                                <th class="fw-semibold">PAY DATE</th>
                                <th class="fw-semibold">STATUS</th>
                                <th class="fw-semibold text-end pe-4">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cycles as $cycle)
                                @php
                                    $st = strtolower($cycle->status ?? 'draft');
                                    $badgeClass = match($st) {
                                        'open' => 'bg-success-subtle text-success',
                                        'processing' => 'bg-warning-subtle text-warning',
                                        'closed' => 'bg-secondary-subtle text-secondary',
                                        'cancelled' => 'bg-danger-subtle text-danger',
                                        default => 'bg-primary-subtle text-primary'
                                    };
                                @endphp
                                <tr>
                                    <td class="px-4">
                                        <div class="fw-bold text-dark">{{ $cycle->name }}</div>
                                        @if($cycle->description)
                                            <small class="text-muted text-truncate d-block" style="max-width:200px;">{{ $cycle->description }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace">
                                            {{ Str::headline($cycle->cycle_type) }}
                                        </span>
                                    </td>
                                    <td class="small">
                                        <div><i class="bx bx-calendar text-muted me-1"></i>{{ $cycle->start_date ? \Carbon\Carbon::parse($cycle->start_date)->format('d M Y') : '-' }}</div>
                                        <div class="text-muted">to {{ $cycle->end_date ? \Carbon\Carbon::parse($cycle->end_date)->format('d M Y') : '-' }}</div>
                                    </td>
                                    <td class="small">
                                        @if($cycle->pay_date)
                                            <span class="text-dark fw-semibold"><i class="bx bx-money text-success me-1"></i>{{ \Carbon\Carbon::parse($cycle->pay_date)->format('d M Y') }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-xs badge {{ $badgeClass }} border-0 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor:pointer;font-size:12px;padding:4px 8px;">
                                                {{ ucfirst($cycle->status ?? 'Draft') }}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li><h6 class="dropdown-header">Update Status</h6></li>
                                                @foreach(['draft', 'open', 'processing', 'closed', 'cancelled'] as $statusOption)
                                                    @if($statusOption !== $st)
                                                        <li>
                                                            <form method="POST" action="{{ route('payroll.cycles.status', $cycle) }}">
                                                                @csrf @method('PATCH')
                                                                <input type="hidden" name="status" value="{{ $statusOption }}">
                                                                <button type="submit" class="dropdown-item small">
                                                                    Mark as {{ ucfirst($statusOption) }}
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-1">
                                            <form method="POST" action="{{ route('payroll.cycles.process', $cycle) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Generate Draft for this Cycle">
                                                    <i class="bx bx-play me-1"></i>Run
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('payroll.cycles.destroy', $cycle) }}" class="d-inline"
                                                onsubmit="return confirm('Delete this cycle schedule?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Delete Cycle">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bx bx-time-five fs-1 d-block mb-2 opacity-25"></i>
                                        No payroll cycles found. Create a new payroll cycle using the form.
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
