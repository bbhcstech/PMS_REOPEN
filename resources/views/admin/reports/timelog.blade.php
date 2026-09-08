@extends('admin.layout.app')

@section('title', 'Time Log Report')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-dark"><i class="bx bx-time-five text-primary me-2"></i> Time Log Report</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Time Log Report</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-white h-100 border-start border-primary border-4">
                <small class="text-muted text-uppercase fw-bold mb-1">Total Log Entries</small>
                <h3 class="fw-extrabold text-primary mb-0">{{ $logs->count() }}</h3>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-white h-100 border-start border-success border-4">
                <small class="text-muted text-uppercase fw-bold mb-1">Total Working Hours Logged</small>
                <h3 class="fw-extrabold text-success mb-0">{{ number_format($totalHours, 2) }} hrs</h3>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-white h-100 border-start border-info border-4">
                <small class="text-muted text-uppercase fw-bold mb-1">Active Projects Logged</small>
                <h3 class="fw-extrabold text-info mb-0">{{ $logs->pluck('project_id')->unique()->count() }}</h3>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.timelog') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-4">
                        <label class="form-label fw-semibold text-dark mb-1">Filter by Employee</label>
                        <select name="user_id" class="form-select rounded-3">
                            <option value="">All Employees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-4">
                        <label class="form-label fw-semibold text-dark mb-1">Filter by Project</label>
                        <select name="project_id" class="form-select rounded-3">
                            <option value="">All Projects</option>
                            @foreach($projects as $proj)
                                <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                                    {{ $proj->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-6 col-md-4 d-flex align-items-center gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 flex-grow-1 text-nowrap">
                            <i class="bx bx-filter-alt me-1"></i> Filter Report
                        </button>
                        <a href="{{ route('reports.timelog') }}" class="btn btn-outline-secondary rounded-pill px-4 flex-grow-1 text-nowrap">
                            <i class="bx bx-refresh me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Table Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="reportTable" class="table table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Project</th>
                            <th>Task & Summary</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Logged Hours</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $key => $log)
                            <tr>
                                <td class="fw-bold">#{{ $key + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $log->employee->name ?? 'Employee' }}</div>
                                </td>
                                <td>{{ $log->project->name ?? 'General Project' }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $log->task->title ?? 'Work Log' }}</div>
                                    @if($log->memo)<small class="text-muted d-block">{{ Str::limit($log->memo, 35) }}</small>@endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($log->start_date ?? $log->start_time)->format('d M Y') }} {{ \Carbon\Carbon::parse($log->start_time)->format('h:i A') }}</td>
                                <td>{{ \Carbon\Carbon::parse($log->end_date ?? $log->end_time)->format('d M Y') }} {{ \Carbon\Carbon::parse($log->end_time)->format('h:i A') }}</td>
                                <td>
                                    @php $rawHours = is_numeric($log->total_hours) ? abs((float)$log->total_hours) : 0; @endphp
                                    <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1">{{ number_format($rawHours, 2) }} hrs</span>
                                </td>
                                <td>
                                    <span class="badge bg-success rounded-pill px-3 py-1">{{ ucfirst($log->status ?? 'Approved') }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .dt-buttons .btn { border-radius: 50rem !important; padding: 0.35rem 0.9rem !important; font-size: 0.82rem !important; font-weight: 600 !important; margin-right: 0.35rem !important; background: #ffffff !important; border: 1px solid #e5e7eb !important; color: #374151 !important; }
    .dt-buttons .btn:hover { background: #e4f3eb !important; color: #0f744c !important; }
    .dataTables_filter input { border-radius: 50rem !important; padding: 0.35rem 1rem !important; border: 1px solid #d1d5db !important; }
</style>
@endpush

@push('js')
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.3.0-beta.1/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.3.0-beta.1/vfs_fonts.js"></script>

<script>
    $(document).ready(function () {
        $('#reportTable').DataTable({
            dom: '<"d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3"Bf>rt<"d-flex flex-wrap align-items-center justify-content-between gap-3 mt-3"ip>',
            buttons: [
                { extend: 'copy', className: 'btn', text: '<i class="bx bx-copy me-1"></i> Copy' },
                { extend: 'csv', className: 'btn', text: '<i class="bx bx-file me-1"></i> CSV' },
                { extend: 'excel', className: 'btn', text: '<i class="bx bx-spreadsheet me-1"></i> Excel' },
                { extend: 'pdf', className: 'btn', text: '<i class="bx bxs-file-pdf me-1"></i> PDF' },
                { extend: 'print', className: 'btn', text: '<i class="bx bx-printer me-1"></i> Print' }
            ],
            pageLength: 25
        });
    });
</script>
@endpush
