@extends('admin.layout.app')

@section('title', 'Payroll Import / Export')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4">
            <i class="bx bx-error-circle me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#0f766e 0%,#0891b2 100%);border-radius:14px;">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8 text-white">
                    <h4 class="fw-bold mb-1"><i class="bx bx-transfer me-2"></i>Import / Export</h4>
                    <p class="opacity-75 mb-0">Import payroll data from CSV/Excel or export complete payroll records</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Import Card --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                    <h5 class="fw-bold mb-0"><i class="bx bx-import text-success me-2"></i>Import Payroll Data</h5>
                    <p class="text-muted small mb-0">Upload a CSV or Excel file with payroll data</p>
                </div>
                <div class="card-body px-4">
                    <div class="alert alert-info border-0 mb-3" style="background:#eff6ff;border-radius:10px;">
                        <div class="d-flex gap-2">
                            <i class="bx bx-info-circle text-info mt-1"></i>
                            <div class="small">
                                <strong>File Requirements:</strong>
                                <ul class="mb-0 ps-3 mt-1">
                                    <li>Format: CSV or Excel (.xlsx)</li>
                                    <li>Maximum size: 5MB</li>
                                    <li>First row must be headers</li>
                                    <li>Required columns: <code>employee_id, basic_salary, net_salary</code></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Download Template --}}
                    <a href="{{ route('payroll.import-export.template') }}" class="btn btn-outline-primary fw-semibold w-100 mb-3">
                        <i class="bx bx-download me-2"></i> Download Import Template (CSV)
                    </a>

                    {{-- Upload Form --}}
                    <form method="POST" action="{{ route('payroll.import-export.import') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select File</label>
                            <div class="drop-zone border-2 rounded-3 p-4 text-center" id="dropZone"
                                style="border: 2px dashed #d1d5db; border-radius: 12px; cursor: pointer; transition:.2s;">
                                <i class="bx bx-cloud-upload fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-1">Drag & drop your file here, or</p>
                                <label for="importFile" class="btn btn-sm btn-outline-primary mb-0">Browse File</label>
                                <input type="file" id="importFile" name="import_file" class="d-none" accept=".csv,.txt,.xlsx">
                                <p class="text-muted small mt-2 mb-0" id="fileName">No file selected</p>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success fw-bold w-100" style="border-radius:10px;" id="importBtn">
                            <i class="bx bx-upload me-1"></i> Upload & Import
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Export Card --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                    <h5 class="fw-bold mb-0"><i class="bx bx-export text-primary me-2"></i>Export Payroll Data</h5>
                    <p class="text-muted small mb-0">Download payroll records in CSV format</p>
                </div>
                <div class="card-body px-4">
                    <div class="d-grid gap-3">
                        <a href="{{ route('payroll.import-export.export-csv') }}" class="btn btn-primary fw-bold" style="border-radius:10px;">
                            <i class="bx bx-spreadsheet me-2"></i> Export Full Payroll History (CSV)
                        </a>
                        <a href="{{ route('payroll.reports.export') }}" class="btn btn-outline-primary fw-semibold" style="border-radius:10px;">
                            <i class="bx bx-bar-chart-alt me-2"></i> Export Payroll Reports (CSV)
                        </a>
                    </div>

                    <hr class="my-4">

                    <h6 class="fw-bold mb-3">Export includes:</h6>
                    <ul class="list-unstyled small text-muted">
                        <li class="mb-2"><i class="bx bx-check text-success me-2"></i>Employee ID and Name</li>
                        <li class="mb-2"><i class="bx bx-check text-success me-2"></i>Pay Period (Month/Year)</li>
                        <li class="mb-2"><i class="bx bx-check text-success me-2"></i>Gross Salary Breakdown</li>
                        <li class="mb-2"><i class="bx bx-check text-success me-2"></i>All Deductions (PF, ESI, PT, TDS)</li>
                        <li class="mb-2"><i class="bx bx-check text-success me-2"></i>Net Pay Calculated</li>
                        <li class="mb-2"><i class="bx bx-check text-success me-2"></i>Payroll Status</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Import Logs --}}
    @if($importLogs->isNotEmpty())
    <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
            <h5 class="fw-bold mb-0"><i class="bx bx-history text-primary me-2"></i>Recent Import Activity</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f8fafc;font-size:12px;">
                    <tr class="text-muted">
                        <th class="px-4 fw-semibold text-uppercase">File Name</th>
                        <th class="fw-semibold text-uppercase">Type</th>
                        <th class="fw-semibold text-uppercase">Status</th>
                        <th class="fw-semibold text-uppercase">Processed By</th>
                        <th class="fw-semibold text-uppercase pe-4">Uploaded</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($importLogs as $log)
                        @php $sc = match($log->status) { 'completed'=>'success','failed'=>'danger','processing'=>'info', default=>'warning' }; @endphp
                        <tr>
                            <td class="px-4 fw-semibold small">
                                <i class="bx bx-file me-1 text-muted"></i>{{ $log->file_name }}
                            </td>
                            <td class="small text-muted">{{ strtoupper($log->file_type ?? 'CSV') }}</td>
                            <td><span class="badge bg-label-{{ $sc }}">{{ ucfirst($log->status) }}</span></td>
                            <td class="small">{{ $log->processedBy?->name ?? 'System' }}</td>
                            <td class="small text-muted pe-4">{{ $log->created_at?->format('d M Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

<script>
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('importFile');
const fileName  = document.getElementById('fileName');

fileInput.addEventListener('change', function() {
    fileName.textContent = this.files[0]?.name ?? 'No file selected';
    dropZone.style.borderColor = '#4f46e5';
    dropZone.style.background = '#eef2ff';
});

dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.style.borderColor = '#4f46e5'; });
dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = '#d1d5db'; dropZone.style.background = ''; });
dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    fileInput.files = e.dataTransfer.files;
    fileName.textContent = e.dataTransfer.files[0]?.name ?? 'No file selected';
    dropZone.style.borderColor = '#4f46e5';
    dropZone.style.background = '#eef2ff';
});
</script>
@endsection
