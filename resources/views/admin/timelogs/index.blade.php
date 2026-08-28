@extends('admin.layout.app')

@section('content')
<div class="container-fluid px-4 py-4">
    @php $canReviewTimeLogs = in_array(strtolower((string) auth()->user()?->role), ['admin', 'hr', 'manager'], true); @endphp

    @if($project)
        {{-- Standardized Project Header & 13-Tab Navigation --}}
        @include('admin.projects.partials.header', [
            'project' => $project,
            'activeTab' => 'timesheet'
        ])
    @endif

    <!-- Top Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-dark"><i class="bx bx-time-five text-primary me-2"></i> Employee Timesheet</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Timesheet</li>
                </ol>
            </nav>
>>>>>>> 2367e2c (client work timesheet reports ticket)
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('timelogs.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="bx bx-plus-circle me-1"></i> Log Time
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="bx bx-check-circle me-2 fs-5 align-middle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filters Section -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('timelogs.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-5">
                        <label class="form-label fw-semibold text-dark mb-1">Duration / Date Range</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bx bx-calendar text-primary"></i></span>
                            <input type="text" name="daterange" id="daterange" class="form-control border-start-0 ps-0 rounded-end"
                                   value="{{ request('start_date') && request('end_date') ? request('start_date').' To '.request('end_date') : '' }}"
                                   placeholder="Select Date Range">
                        </div>
                        <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                        <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
                    </div>

                    <div class="col-lg-4 col-md-4">
                        <label class="form-label fw-semibold text-dark mb-1">Filter by Employee</label>
                        <select name="user_id" class="form-select rounded-3">
                            @if($canReviewTimeLogs)
                                <option value="">All Employees</option>
                            @endif

                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}"
                                    @if(! $canReviewTimeLogs)
                                        selected
                                    @elseif(request('user_id') == $emp->id)
                                        selected
                                    @endif
                                >
                                    {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-3 d-flex align-items-center gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 flex-grow-1 text-nowrap">
                            <i class="bx bx-filter-alt me-1"></i> Filter
                        </button>
                        <a href="{{ route('timelogs.index') }}" class="btn btn-outline-secondary rounded-pill px-4 flex-grow-1 text-nowrap">
                            <i class="bx bx-refresh me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Actions & Views Switcher Toolbar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <!-- Bulk Actions -->
        <div class="d-flex align-items-center gap-2">
            @if($canReviewTimeLogs)
                <select id="bulkLogStatus" class="form-select form-select-sm rounded-pill w-auto" disabled>
                    <option value="">Change Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <button id="applyBulkLogStatus" class="btn btn-sm btn-primary rounded-pill px-3" disabled>Apply</button>
            @endif
        </div>

        <!-- View Switcher -->
        <div class="btn-group" role="group">
            <a href="{{ route('timelogs.index') }}" class="btn btn-sm btn-outline-primary {{ request()->routeIs('timelogs.index') ? 'active' : '' }}" title="Timesheet List">
                <i class="bx bx-list-ul me-1"></i> List
            </a>
            <a href="{{ route('timelogs.calendar') }}" class="btn btn-sm btn-outline-primary {{ request()->routeIs('timelogs.calendar') ? 'active' : '' }}" title="Calendar View">
                <i class="bx bx-calendar me-1"></i> Calendar
            </a>
            <a href="{{ route('timelogs.byEmployee')}}" class="btn btn-sm btn-outline-primary {{ request()->routeIs('timelogs.byEmployee') ? 'active' : '' }}" title="By Employee Summary">
                <i class="bx bx-user me-1"></i> By Employee
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#howItWorksModal" title="How It Works">
                <i class="bx bx-help-circle"></i>
            </button>
        </div>
    </div>

    <!-- Timesheet Table Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="timelogTable" class="table table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30px;"><input type="checkbox" id="selectAllLogs" class="form-check-input"></th>
                            <th style="width: 60px;">ID</th>
                            <th>Code</th>
                            <th>Project</th>
                            <th>Task & Memo</th>
                            <th>Employee</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Total Hours</th>
                            <th>Status</th>
                            <th style="width: 80px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $key => $log)
                        <tr data-id="{{ $log->id }}">
                            <td><input type="checkbox" class="log-checkbox form-check-input" value="{{ $log->id }}"></td>
                            <td class="fw-bold">#{{ $key + 1 }}</td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">
                                    @php
                                        $prefix = $log->project->project_code ?? 'LOG';
                                        $autoNumber = str_pad($log->id, 4, '0', STR_PAD_LEFT);
                                        echo $prefix . '-' . $autoNumber;
                                    @endphp
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-0">{{ $log->project->name ?? 'General Project' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $log->task->title ?? 'General Work' }}</div>
                                @if($log->memo)
                                    <small class="text-muted text-wrap d-block">{{ Str::limit($log->memo, 40) }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold small" style="width: 28px; height: 28px;">
                                        {{ strtoupper(substr($log->user->name ?? 'E', 0, 2)) }}
                                    </div>
                                    <span class="fw-semibold text-dark">{{ $log->user->name ?? 'Employee' }}</span>
                                </div>
                            </td>
                            <td class="text-nowrap small text-muted">
                                {{ \Carbon\Carbon::parse($log->start_date)->format('d M Y') }}
                                <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($log->start_time)->format('h:i A') }}</div>
                            </td>
                            <td class="text-nowrap small text-muted">
                                {{ \Carbon\Carbon::parse($log->end_date)->format('d M Y') }}
                                <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($log->end_time)->format('h:i A') }}</div>
                            </td>
                            <td>
                                @php
                                    $rawHours = is_numeric($log->total_hours) ? abs((float)$log->total_hours) : 0;
                                @endphp
                                <span class="badge bg-primary-subtle text-primary fw-bold fs-6 px-3 py-1">
                                    {{ number_format($rawHours, 2) }} hrs
                                </span>
                            </td>
                            <td class="statusCell">
                                @php
                                    $st = strtolower((string)($log->status ?? 'pending'));
                                @endphp
                                @if($st == 'approved')
                                    <span class="badge bg-success rounded-pill px-3 py-1">Approved</span>
                                @elseif($st == 'rejected')
                                    <span class="badge bg-danger rounded-pill px-3 py-1">Rejected</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1">Pending</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end rounded-3 shadow border-0">
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('timelogs.show', $log->id) }}">
                                                <i class="bx bx-show me-2 text-primary"></i> View Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('timelogs.edit', $log->id) }}">
                                                <i class="bx bx-edit me-2 text-info"></i> Edit Log
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('timelogs.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this log?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item py-2 text-danger">
                                                    <i class="bx bx-trash me-2"></i> Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: How it works -->
<div class="modal fade" id="howItWorksModal" tabindex="-1" aria-labelledby="howItWorksLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold" id="howItWorksLabel"><i class="bx bx-info-circle text-primary me-2"></i> Timesheet Lifecycle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center p-4">
        <img src="{{ asset('timesheet-lifecycle.png') }}" alt="Timesheet Lifecycle" class="img-fluid rounded-3">
      </div>
      <div class="modal-footer border-top">
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<style>
    /* DataTables polish */
    .dt-buttons .btn {
        border-radius: 50rem !important;
        padding: 0.35rem 0.9rem !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        margin-right: 0.35rem !important;
        background: #ffffff !important;
        border: 1px solid var(--pms-border, #e5e7eb) !important;
        color: #374151 !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
    }
    .dt-buttons .btn:hover {
        background: var(--pms-primary-soft, #e4f3eb) !important;
        color: var(--pms-primary, #0f744c) !important;
    }
    .dataTables_filter input {
        border-radius: 50rem !important;
        padding: 0.35rem 1rem !important;
        border: 1px solid #d1d5db !important;
        outline: none !important;
    }
    .dataTables_paginate .paginate_button.current {
        background: var(--pms-primary, #0f744c) !important;
        color: #ffffff !important;
        border-radius: 50rem !important;
        border: none !important;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<!-- DataTables buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.3.0-beta.1/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.3.0-beta.1/vfs_fonts.js"></script>

<script>
    $(document).ready(function () {
        let timelogDatatable = $('#timelogTable').DataTable({
            dom: '<"d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3"Bf>rt<"d-flex flex-wrap align-items-center justify-content-between gap-3 mt-3"ip>',
            buttons: [
                { extend: 'copy', className: 'btn', text: '<i class="bx bx-copy me-1"></i> Copy', exportOptions: { columns: ':not(:first-child):not(:last-child)' } },
                { extend: 'csv', className: 'btn', text: '<i class="bx bx-file me-1"></i> CSV', exportOptions: { columns: ':not(:first-child):not(:last-child)' } },
                { extend: 'excel', className: 'btn', text: '<i class="bx bx-spreadsheet me-1"></i> Excel', exportOptions: { columns: ':not(:first-child):not(:last-child)' } },
                { extend: 'pdf', className: 'btn', text: '<i class="bx bxs-file-pdf me-1"></i> PDF', exportOptions: { columns: ':not(:first-child):not(:last-child)' } },
                { extend: 'print', className: 'btn', text: '<i class="bx bx-printer me-1"></i> Print', exportOptions: { columns: ':not(:first-child):not(:last-child)' } }
            ],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                search: "",
                searchPlaceholder: "Start typing to search timesheets..."
            },
            columnDefs: [
                { targets: [0, 10], searchable: false, orderable: false }
            ]
        });

        // Toggle bulk UI
        function toggleBulkLogs() {
            const checked = $('.log-checkbox:checked').length;
            $('#bulkLogStatus, #applyBulkLogStatus').prop('disabled', checked === 0);
        }

        $('#timelogTable').on('change', '.log-checkbox', toggleBulkLogs);
        $('#selectAllLogs').on('click', function () {
            $('.log-checkbox').prop('checked', this.checked);
            toggleBulkLogs();
        });

        // Apply bulk status
        $('#applyBulkLogStatus').on('click', function () {
            const status = $('#bulkLogStatus').val();
            const ids = $('.log-checkbox:checked').map((_, el) => $(el).val()).get();

            if (!status) { alert('Please select a status.'); return; }
            if (ids.length === 0) { alert('Please select at least one log entry.'); return; }

            $.ajax({
                url: '{{ route("timelogs.bulkStatusUpdate") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status,
                    ids: ids
                },
                success: function (res) {
                    if (res.status === 'success') {
                        location.reload();
                    }
                },
                error: function () {
                    alert('Error applying bulk status.');
                }
            });
        });

        // Daterange picker
        $('#daterange').daterangepicker({
            autoUpdateInput: false,
            locale: { format: 'YYYY-MM-DD', cancelLabel: 'Clear' }
        });

        $('#daterange').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' To ' + picker.endDate.format('YYYY-MM-DD'));
            $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
            $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
        });

        $('#daterange').on('cancel.daterangepicker', function () {
            $(this).val('');
            $('#start_date').val('');
            $('#end_date').val('');
        });
    });
</script>
@endpush
