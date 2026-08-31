@extends('admin.layout.app')

@section('title', 'Clients')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Top Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-dark"><i class="bx bx-user-voice text-primary me-2"></i> Client Management</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Clients</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('clients.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="bx bx-plus-circle me-1"></i> Add Client
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
            <form method="GET" action="{{ route('clients.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-5">
                        <label for="duration" class="form-label fw-semibold text-dark mb-1">Duration / Date Range</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bx bx-calendar text-primary"></i></span>
                            <input type="text"
                                   class="form-control border-start-0 ps-0 rounded-end"
                                   id="duration"
                                   name="duration"
                                   value="{{ request('duration') }}"
                                   placeholder="Select Date Range"
                                   autocomplete="off">
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-4">
                        <label for="name" class="form-label fw-semibold text-dark mb-1">Filter by Client</label>
                        <select class="form-select rounded-3" id="name" name="name">
                            <option value="">All Clients</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ request('name') == $c->id ? 'selected' : '' }}>
                                    {{ $c->client_uid ?? $c->id }} - {{ $c->name }} ({{ $c->company_name ?? 'Company' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-3 d-flex align-items-center gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 flex-grow-1 text-nowrap">
                            <i class="bx bx-filter-alt me-1"></i> Filter
                        </button>
                        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary rounded-pill px-4 flex-grow-1 text-nowrap">
                            <i class="bx bx-refresh me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Actions & Views Switcher Toolbar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <!-- Quick Bulk Actions -->
        <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm rounded-pill w-auto" id="quick-action-type">
                <option value="">Choose Bulk Action</option>
                <option value="change-status">Change Status</option>
                <option value="delete">Delete Selected</option>
            </select>

            <select class="form-select form-select-sm rounded-pill w-auto d-none" id="quick-action-status">
                <option value="Active">Mark Active</option>
                <option value="Inactive">Mark Inactive</option>
            </select>

            <button class="btn btn-sm btn-primary rounded-pill px-3" id="quick-action-apply" disabled>Apply</button>
        </div>

        <!-- View Switcher -->
        <div class="btn-group" role="group">
            <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-primary {{ request()->routeIs('clients.index') ? 'active' : '' }}" title="Table View">
                <i class="bx bx-list-ul me-1"></i> Client Directory
            </a>
            <a href="{{ route('clients.pending') }}" class="btn btn-sm btn-outline-warning {{ request()->routeIs('clients.pending') ? 'active' : '' }}" title="Pending Verification">
                <i class="bx bx-user-x me-1"></i> Verification Pending
            </a>
        </div>
    </div>

    <!-- Clients Table Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="clientsTable" class="table table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30px;"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                            <th style="width: 100px;">Client ID</th>
                            <th>Client & Company</th>
                            <th>Contact Info</th>
                            <th>Category</th>
                            <th>Active Projects</th>
                            <th>Status</th>
                            <th>Created On</th>
                            <th style="width: 80px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                            <tr data-id="{{ $client->id }}">
                                <td><input type="checkbox" class="client-checkbox form-check-input" value="{{ $client->id }}"></td>
                                <td class="fw-bold">
                                    <span class="badge bg-light text-dark border font-monospace">
                                        {{ $client->client_uid ?? ('CL-' . $client->id) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                            {{ strtoupper(substr($client->name ?? 'C', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark mb-0">
                                                <a href="{{ route('clients.show', $client->id) }}" class="text-dark text-decoration-none">
                                                    {{ $client->salutation }} {{ $client->name }}
                                                </a>
                                            </div>
                                            <small class="text-muted"><i class="bx bx-building me-1"></i> {{ $client->company_name ?? 'Individual Client' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><i class="bx bx-envelope text-primary me-1"></i> {{ $client->email }}</div>
                                    @if($client->mobile || $client->office_phone)
                                        <small class="text-muted"><i class="bx bx-phone me-1"></i> {{ $client->mobile ?? $client->office_phone }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1">
                                        {{ $client->category->name ?? 'Corporate Client' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $projCount = $client->projects->count();
                                    @endphp
                                    <a href="{{ route('projects.index', ['client_id' => $client->id]) }}" class="badge bg-success-subtle text-success border border-success-subtle text-decoration-none px-3 py-1">
                                        <i class="bx bx-briefcase me-1"></i> {{ $projCount }} Projects
                                    </a>
                                </td>
                                <td>
                                    @if(strtolower((string)($client->status ?? 'active')) == 'active')
                                        <span class="badge bg-success rounded-pill px-3 py-1">Active</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-3 py-1">{{ ucfirst($client->status ?? 'Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="text-nowrap small text-muted">
                                    {{ $client->created_at ? \Carbon\Carbon::parse($client->created_at)->format('d M Y') : '—' }}
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light rounded-circle shadow-sm" type="button" id="dropdownMenuButton{{ $client->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-end rounded-3 shadow border-0" aria-labelledby="dropdownMenuButton{{ $client->id }}">
                                            <li>
                                                <a class="dropdown-item py-2" href="{{ route('clients.show', $client->id) }}">
                                                    <i class="bx bx-show me-2 text-primary"></i> View Details & Deals
                                                </a>
                                            </li>

                                            <li>
                                                <a class="dropdown-item py-2" href="{{ route('clients.edit', $client->id) }}">
                                                    <i class="bx bx-edit me-2 text-info"></i> Edit Client
                                                </a>
                                            </li>

                                            <li><hr class="dropdown-divider"></li>

                                            <li>
                                                <form action="{{ route('clients.destroy', $client->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this client?');">
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

            <!-- Pagination control -->
            <div class="mt-3">
                {{ $clients->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
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
@endpush

@push('js')
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
        let clientsTable = $('#clientsTable').DataTable({
            dom: '<"d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3"Bf>rt<"d-flex flex-wrap align-items-center justify-content-between gap-3 mt-3"ip>',
            buttons: [
                { extend: 'copy', className: 'btn', text: '<i class="bx bx-copy me-1"></i> Copy', exportOptions: { columns: ':not(:first-child):not(:last-child)' } },
                { extend: 'csv', className: 'btn', text: '<i class="bx bx-file me-1"></i> CSV', exportOptions: { columns: ':not(:first-child):not(:last-child)' } },
                { extend: 'excel', className: 'btn', text: '<i class="bx bx-spreadsheet me-1"></i> Excel', exportOptions: { columns: ':not(:first-child):not(:last-child)' } },
                { extend: 'pdf', className: 'btn', text: '<i class="bx bxs-file-pdf me-1"></i> PDF', exportOptions: { columns: ':not(:first-child):not(:last-child)' } },
                { extend: 'print', className: 'btn', text: '<i class="bx bx-printer me-1"></i> Print', exportOptions: { columns: ':not(:first-child):not(:last-child)' } }
            ],
            paging: false,
            info: false,
            language: {
                search: "",
                searchPlaceholder: "Start typing to search clients..."
            },
            columnDefs: [
                { targets: [0, 8], searchable: false, orderable: false }
            ]
        });

        // Quick action handler
        $('#quick-action-type').on('change', function() {
            const action = $(this).val();
            if (action === 'change-status') {
                $('#quick-action-status').removeClass('d-none');
            } else {
                $('#quick-action-status').addClass('d-none');
            }
            toggleApplyButton();
        });

        function toggleApplyButton() {
            const hasChecked = $('.client-checkbox:checked').length > 0;
            const hasAction = $('#quick-action-type').val() !== '';
            $('#quick-action-apply').prop('disabled', !(hasChecked && hasAction));
        }

        $('#clientsTable').on('change', '.client-checkbox', toggleApplyButton);
        $('#selectAll').on('click', function() {
            $('.client-checkbox').prop('checked', this.checked);
            toggleApplyButton();
        });

        $('#quick-action-apply').on('click', function() {
            const action = $('#quick-action-type').val();
            const status = $('#quick-action-status').val();
            const selectedIds = $('.client-checkbox:checked').map((_, el) => $(el).val()).get();

            if (action === 'delete' && !confirm('Are you sure you want to delete selected clients?')) return;

            $.ajax({
                url: '{{ route("clients.bulkAction") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    action: action,
                    status: status,
                    client_ids: selectedIds
                },
                success: function(res) {
                    if (res.success) location.reload();
                },
                error: function() {
                    alert('Error applying bulk action');
                }
            });
        });

        // Daterange picker
        $('#duration').daterangepicker({
            autoUpdateInput: false,
            locale: { format: 'YYYY-MM-DD', cancelLabel: 'Clear' }
        });

        $('#duration').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $('#duration').on('cancel.daterangepicker', function () {
            $(this).val('');
        });
    });
</script>
@endpush
