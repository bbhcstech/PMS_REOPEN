@extends('admin.layout.app')

@section('content')
@php
    $project = $project ?? $currentProject ?? (request('project_id') ? \App\Models\Project::find(request('project_id')) : null);
@endphp
<div class="container-fluid px-4 py-4">
    @if($project)
        {{-- Standardized Project Header & 13-Tab Navigation --}}
        @include('admin.projects.partials.header', [
            'project' => $project,
            'activeTab' => 'tickets'
        ])
    @else
        <!-- Top Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h3 class="fw-bold mb-0 text-dark">Support Tickets</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tickets</li>
                    </ol>
                </nav>
            </div>

            @if(in_array(strtolower((string) auth()->user()?->role), ['admin', 'hr', 'manager'], true))
                <a href="{{ route('tickets.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i class="bx bx-plus-circle me-1"></i> Create Ticket
                </a>
            @endif
        </div>
    @endif

    <!-- Flash Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="bx bx-check-circle me-2 fs-5 align-middle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filters Section -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('tickets.index') }}">
                @if(request('project_id'))
                    <input type="hidden" name="project_id" value="{{ request('project_id') }}">
                @endif
                <div class="row g-3 align-items-end">
                    <!-- Date Range Filter -->
                    <div class="col-lg-4 col-md-5">
                        <label for="duration" class="form-label fw-semibold text-dark mb-1">Duration / Date Range</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bx bx-calendar text-primary"></i></span>
                            <input type="text"
                                   class="form-control border-start-0 ps-0 rounded-end"
                                   id="duration"
                                   name="duration"
                                   value="{{ request('duration') }}"
                                   placeholder="Select Range (e.g. Today, This Month)"
                                   autocomplete="off">
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-lg-3 col-md-4">
                        <label for="status" class="form-label fw-semibold text-dark mb-1">Filter by Status</label>
                        <select class="form-select rounded-3" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="reopened" {{ request('status') == 'reopened' ? 'selected' : '' }}>REOPENED</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    <!-- Filter & Reset Buttons -->
                    <div class="col-lg-3 col-md-3 d-flex align-items-center gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 flex-grow-1 text-nowrap">
                            <i class="bx bx-filter-alt me-1"></i> Filter
                        </button>
                        <a href="{{ request('project_id') ? route('tickets.index', ['project_id' => request('project_id')]) : route('tickets.index') }}" class="btn btn-outline-secondary rounded-pill px-4 flex-grow-1 text-nowrap">
                            <i class="bx bx-refresh me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards Row (6 Columns) -->
    <div class="row g-3 mb-4">
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100 bg-white">
                <small class="text-muted text-uppercase fw-bold mb-1">Total</small>
                <h4 class="fw-extrabold text-dark mb-0">{{ $tickets->count() }}</h4>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100 bg-warning-subtle text-dark border-start border-warning border-4">
                <small class="text-warning-emphasis text-uppercase fw-bold mb-1">Open</small>
                <h4 class="fw-extrabold text-warning-emphasis mb-0">{{ $tickets->where('status', 'open')->count() }}</h4>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100 bg-danger-subtle text-danger border-start border-danger border-4">
                <small class="text-danger text-uppercase fw-bold mb-1">Reopened</small>
                <h4 class="fw-extrabold text-danger mb-0">{{ $tickets->where('status', 'reopened')->count() }}</h4>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100 bg-info-subtle text-info border-start border-info border-4">
                <small class="text-info-emphasis text-uppercase fw-bold mb-1">Pending</small>
                <h4 class="fw-extrabold text-info-emphasis mb-0">{{ $tickets->where('status', 'pending')->count() }}</h4>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100 bg-success-subtle text-success border-start border-success border-4">
                <small class="text-success text-uppercase fw-bold mb-1">Resolved</small>
                <h4 class="fw-extrabold text-success mb-0">{{ $tickets->where('status', 'resolved')->count() }}</h4>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100 bg-secondary-subtle text-secondary border-start border-secondary border-4">
                <small class="text-secondary text-uppercase fw-bold mb-1">Closed</small>
                <h4 class="fw-extrabold text-secondary mb-0">{{ $tickets->where('status', 'closed')->count() }}</h4>
            </div>
        </div>
    </div>

    <!-- Bulk Action Toolbar -->
    <div class="d-flex align-items-center justify-content-between mb-3" id="bulk-action-div" style="display: none !important;">
        <div class="d-flex align-items-center gap-2 bg-white p-2 px-3 rounded-pill shadow-sm border">
            <span class="fw-semibold text-dark small me-1"><i class="bx bx-check-square text-primary me-1"></i> Bulk Action:</span>
            <select id="bulk-action" class="form-select form-select-sm rounded-pill w-auto">
                <option value="">-- Choose Action --</option>
                <option value="change_status">Change Status</option>
                <option value="delete">Delete Selected</option>
            </select>
            <button id="apply-bulk" class="btn btn-sm btn-primary rounded-pill px-3">Apply</button>
        </div>
    </div>

    <!-- Tickets Datatable Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle w-100" id="tickets-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30px;"><input type="checkbox" id="select-all" class="form-check-input"></th>
                            <th style="width: 80px;">Ticket#</th>
                            <th>Subject & Project</th>
                            <th>Requester (Client)</th>
                            <th>Assigned Developer</th>
                            <th>Requested On</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th style="width: 80px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            <tr>
                                <td>
                                    <input type="checkbox" class="ticket-checkbox form-check-input" value="{{ $ticket->id }}">
                                </td>
                                <td class="fw-bold">#{{ $ticket->id }}</td>
                                <td>
                                    <div class="fw-bold text-dark mb-0">{{ Str::limit($ticket->subject, 40) }}</div>
                                    <small class="text-muted"><i class="bx bx-folder me-1 text-primary"></i> {{ $ticket->project?->name ?? 'General Support' }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $ticket->requester_name ?? 'Client' }}</div>
                                    <small class="text-muted">{{ ucfirst($ticket->requester_type ?? 'client') }}</small>
                                </td>
                                <td>
                                    @if($ticket->agent)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold small" style="width: 28px; height: 28px;">
                                                {{ strtoupper(substr($ticket->agent->name, 0, 2)) }}
                                            </div>
                                            <span class="fw-semibold text-dark">{{ $ticket->agent->name }}</span>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-muted border"><i class="bx bx-user-x me-1"></i> Unassigned</span>
                                    @endif
                                </td>
                                <td class="text-nowrap small text-muted">
                                    {{ $ticket->created_at->format('d M Y') }}
                                    <div class="text-muted opacity-75" style="font-size: 0.75rem;">{{ $ticket->created_at->format('h:i A') }}</div>
                                </td>
                                <td>
                                    @php
                                        $badge = match(strtolower((string)$ticket->priority)) {
                                            'low' => 'secondary',
                                            'medium' => 'info',
                                            'high' => 'warning text-dark',
                                            'critical' => 'danger',
                                            default => 'light text-dark'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badge }} rounded-pill px-3 py-1">{{ ucfirst($ticket->priority) }}</span>
                                </td>
                                <td>
                                    <select name="status"
                                            class="form-select form-select-sm change-status rounded-pill px-3"
                                            data-ticket-id="{{ $ticket->id }}"
                                            style="min-width: 130px;">
                                        <option value="open" {{ strtolower($ticket->status) == 'open' ? 'selected' : '' }}>Open</option>
                                        <option value="reopened" {{ strtolower($ticket->status) == 'reopened' ? 'selected' : '' }}>REOPENED</option>
                                        <option value="pending" {{ strtolower($ticket->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="resolved" {{ strtolower($ticket->status) == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                        <option value="closed" {{ strtolower($ticket->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                                    </select>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end rounded-3 shadow border-0">
                                            <li>
                                                <a class="dropdown-item py-2" href="{{ route('tickets.show', $ticket->id) }}">
                                                    <i class="bx bx-show me-2 text-primary"></i> View Details
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-2" href="{{ route('tickets.edit', $ticket->id) }}">
                                                    <i class="bx bx-edit me-2 text-info"></i> Edit Ticket
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('tickets.destroy', $ticket->id) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Are you sure you want to delete this ticket?');">
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
@endsection

@push('js')
<!-- Styles for DataTables & Pickers -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
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
        let ticketsDatatable = $('#tickets-table').DataTable({
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
                searchPlaceholder: "Start typing to search tickets..."
            },
            columnDefs: [
                { targets: [0, 8], searchable: false, orderable: false }
            ]
        });

        // Status change handler
        $('#tickets-table').on('change', '.change-status', function () {
            var ticketId = $(this).data('ticket-id');
            var status = $(this).val();
            var token = '{{ csrf_token() }}';

            $.ajax({
                url: '{{ route("tickets.change-status") }}',
                method: 'POST',
                data: {
                    _token: token,
                    ticketId: ticketId,
                    status: status
                },
                success: function (response) {
                    if (response.status === 'success') {
                        // Toast or alert notification
                    }
                },
                error: function () {
                    alert('Error updating ticket status');
                }
            });
        });

        // Bulk UI toggle
        function toggleBulkUi() {
            const anyChecked = $('.ticket-checkbox:checked').length > 0;
            if (anyChecked) {
                $('#bulk-action-div').css('display', 'flex');
            } else {
                $('#bulk-action-div').css('display', 'none');
                $('#bulk-action').val('');
            }
        }

        $('#tickets-table').on('change', '.ticket-checkbox', toggleBulkUi);

        $('#select-all').on('click', function() {
            $('.ticket-checkbox').prop('checked', this.checked);
            toggleBulkUi();
        });

        $('#apply-bulk').on('click', function() {
            var action = $('#bulk-action').val();
            var selected = $('.ticket-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (!action) { alert('Please select an action'); return; }
            if (selected.length === 0) { alert('Please select at least one ticket'); return; }
            if (action === 'delete' && !confirm('Are you sure you want to delete selected tickets?')) return;

            $.ajax({
                url: '{{ route("tickets.bulk-action") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    action: action,
                    tickets: selected
                },
                success: function(response) {
                    if (response.status === 'success') location.reload();
                },
                error: function() {
                    alert('Error performing bulk action');
                }
            });
        });

        // Daterange picker
        const predefinedRanges = {
            'Today': [moment(), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Last 90 Days': [moment().subtract(89, 'days'), moment()],
            'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment()],
            'Last 1 Year': [moment().subtract(1, 'year').startOf('month'), moment()]
        };

        $('#duration').daterangepicker({
            autoUpdateInput: false,
            showDropdowns: true,
            opens: 'left',
            locale: { format: 'YYYY-MM-DD', cancelLabel: 'Clear' },
            ranges: predefinedRanges
        });

        $('#duration').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.chosenLabel === 'Custom Range' ? picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD') : picker.chosenLabel);
        });

        $('#duration').on('cancel.daterangepicker', function () {
            $(this).val('');
        });
    });
</script>
@endpush
