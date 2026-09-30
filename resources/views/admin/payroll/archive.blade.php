@extends('admin.layout.app')

@section('title', 'Payroll Archive')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#374151 0%,#111827 100%);border-radius:14px;">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="text-white">
                    <h4 class="fw-bold mb-1"><i class="bx bx-archive me-2"></i>Payroll Archive</h4>
                    <p class="opacity-75 mb-0">Archive finalized payroll runs for long-term record keeping</p>
                </div>
                @if($payrolls->isNotEmpty())
                    <button class="btn btn-light fw-bold" data-bs-toggle="modal" data-bs-target="#archiveModal">
                        <i class="bx bx-archive-in me-1"></i> Archive Payroll Run
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
                <div class="fs-2 fw-bold text-dark">{{ method_exists($archives, 'total') ? $archives->total() : $archives->count() }}</div>
                <div class="small text-muted">Total Archives</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
                <div class="fs-2 fw-bold text-warning">{{ $payrolls->count() }}</div>
                <div class="small text-muted">Finalized (Eligible)</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
                <div class="fs-2 fw-bold text-success">{{ $archives->where('archivable_type', \App\Models\Payroll::class)->count() }}</div>
                <div class="small text-muted">Payroll Runs Archived</div>
            </div>
        </div>
    </div>

    {{-- Archives Table --}}
    <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
            <h5 class="fw-bold mb-0"><i class="bx bx-list-ul text-primary me-2"></i>Archive Records</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f8fafc;font-size:12px;">
                    <tr class="text-muted">
                        <th class="px-4 fw-semibold text-uppercase">#</th>
                        <th class="fw-semibold text-uppercase">Type</th>
                        <th class="fw-semibold text-uppercase">Record ID</th>
                        <th class="fw-semibold text-uppercase">Reason</th>
                        <th class="fw-semibold text-uppercase">Archived By</th>
                        <th class="fw-semibold text-uppercase pe-4">Archived At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($archives as $archive)
                        <tr>
                            <td class="px-4 text-muted small">{{ $loop->iteration }}</td>
                            <td>
                                @php $typeShort = class_basename($archive->archivable_type ?? 'Record'); @endphp
                                <span class="badge bg-label-dark">{{ $typeShort }}</span>
                            </td>
                            <td class="fw-semibold small">#{{ $archive->archivable_id }}</td>
                            <td class="small text-muted" style="max-width:250px;">{{ Str::limit($archive->reason, 60) ?? '—' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-xs rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                         style="background:#4f46e5;font-size:11px;width:28px;height:28px;">
                                        {{ strtoupper(substr($archive->archiver?->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <span class="small">{{ $archive->archiver?->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td class="small text-muted pe-4">{{ optional($archive->archived_at)->format('d M Y H:i') ?? $archive->created_at?->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bx bx-archive fs-1 d-block mb-2 opacity-30"></i>
                                No archived payroll records yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($archives, 'hasPages') && $archives->hasPages())
            <div class="p-3">{{ $archives->links() }}</div>
        @endif
    </div>

</div>

{{-- Archive Modal --}}
@if($payrolls->isNotEmpty())
<div class="modal fade" id="archiveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius:14px;">
            <div class="modal-header border-0 bg-dark text-white" style="border-radius:14px 14px 0 0;">
                <h5 class="modal-title fw-bold"><i class="bx bx-archive-in me-2"></i>Archive Payroll Run</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="archiveForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-0 mb-3">
                        <i class="bx bx-error me-1"></i>
                        <strong>Warning:</strong> Archiving a payroll run sets its status to "archived" and creates a permanent snapshot record.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Finalized Payroll Run <span class="text-danger">*</span></label>
                        <select id="archivePayrollSelect" class="form-select" required>
                            <option value="">— Select a finalized run —</option>
                            @foreach($payrolls as $payroll)
                                <option value="{{ route('payroll.archive.store', $payroll) }}">
                                    Payroll #{{ $payroll->id }}
                                    ({{ optional($payroll->period_start)->format('M Y') }})
                                    — {{ ucfirst($payroll->status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason <span class="text-muted small">(optional)</span></label>
                        <textarea name="reason" class="form-control" rows="2"
                            placeholder="e.g. End of financial year 2025, manual archive..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark fw-bold px-4"
                        onclick="return confirm('This will archive the selected payroll run and it cannot be unarchived. Continue?')">
                        <i class="bx bx-archive-in me-1"></i> Archive
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('archivePayrollSelect').addEventListener('change', function() {
    document.getElementById('archiveForm').action = this.value;
});
</script>
@endif

@endsection
