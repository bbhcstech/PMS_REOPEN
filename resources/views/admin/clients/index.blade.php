@extends('admin.layout.app')

@section('title', 'Clients')

@section('content')
<style>
    .dt-buttons {
        display: none !important;
    }
</style>

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
                        <select class="form-select rounded-3 select2" id="name" name="name">
                            <option value="">All Clients</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ request('name') == $c->id ? 'selected' : '' }}>
                                    {{ $c->client_uid ?? $c->id }} - {{ $c->name }}
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-plus-circle me-1"></i> Add Client
        </a>

        <div class="btn-group" role="group" aria-label="View toggle">
            <a href="{{ route('clients.index') }}" class="btn btn-secondary f-14" data-toggle="tooltip" data-original-title="Table View">
                <i class="side-icon bi bi-list-ul"></i>
            </a>
            <a href="{{ route('clients.pending') }}" class="btn btn-secondary f-14 show-unverified btn-active" data-toggle="tooltip" data-original-title="Account verification pending">
                <i class="side-icon bi bi-person-x"></i>
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="clientsTable" class="table table-bordered table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:40px;"><input type="checkbox" id="selectAll"></th>
                            <th>Client ID</th>
                            <th style="white-space: nowrap;">Name</th>
                            <th>Email</th>
                            <th style="white-space: nowrap;">Company</th>
                            <th>Status</th>
                            <th style="white-space: nowrap;">Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                            <tr data-id="{{ $client->id }}">
                                <td><input type="checkbox" class="client-checkbox" value="{{ $client->id }}"></td>
                                <td>{{ $client->client_uid ?? '—' }}</td>
                                <td style="white-space: nowrap;">
                                    <strong>{{ $client->name }}</strong><br>
                                    @if($client->salutation)
                                        <small>{{ $client->salutation }} {{ $client->name }}</small>
                                    @endif
                                </td>
                                <td>{{ $client->email }}</td>
                                <td style="white-space: nowrap;">{{ $client->company_name ?? '—' }}</td>
                                <td>
                                    @php
                                        $clientStatus = strtolower(trim((string)($client->status ?? 'active')));
                                        $badgeClass = match($clientStatus) {
                                            'active' => 'bg-success',
                                            'pending' => 'bg-warning',
                                            'inactive', 'deactive', 'suspended', 'blocked' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} text-capitalize">
                                        {{ $client->status ?? 'Active' }}
                                    </span>
                                </td>
                                <td style="white-space: nowrap;">
                                    {{ $client->created_at ? \Carbon\Carbon::parse($client->created_at)->format('d-m-Y') : '—' }}
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" type="button" id="dropdownMenuButton{{ $client->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="dropdownMenuButton{{ $client->id }}">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('clients.show', $client->id) }}">
                                                    <i class="bi bi-eye me-2"></i> View
                                                </a>
                                            </li>

                                            <li>
                                                <a class="dropdown-item" href="{{ route('clients.show', $client->id) }}#projects">
                                                    <i class="bi bi-folder2 me-2 text-primary"></i> Projects
                                                </a>
                                            </li>

                                            <li>
                                                <a class="dropdown-item" href="{{ route('clients.edit', $client->id) }}">
                                                    <i class="bi bi-pencil-square me-2"></i> Edit
                                                </a>
                                            </li>

                                            <li>
                                                <form action="{{ route('clients.destroy', $client->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this client?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-trash me-2"></i> Delete
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

            {{-- Footer row: Bulk delete & Pagination --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-2">
                <div>
                    <button id="bulkDeleteBtn" class="btn btn-danger btn-sm" disabled>
                        <i class="bi bi-trash me-1"></i> Bulk Delete
                    </button>
                </div>

                <div class="client-pagination">
                    {{ $clients->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
    {{-- CSS/JS dependencies --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>

    <script>
    $(document).ready(function () {
        $('#clientsTable').DataTable({
            paging: false,
            info: false,
            responsive: false,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Start typing to search...",
                emptyTable: "No clients found."
            },
            drawCallback: function() {
                updateBulkButtonsState();
            }
        });

        // daterangepicker for Duration filter
        const predefinedRanges = {
            'Today': [moment(), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Last 90 Days': [moment().subtract(89, 'days'), moment()],
            'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment()],
            'Last 1 Year': [moment().subtract(1, 'year').startOf('month'), moment()],
            'Custom Range': []
        };

        $('#duration').daterangepicker({
            autoUpdateInput: false,
            showDropdowns: true,
            opens: 'left',
            locale: {
                format: 'YYYY-MM-DD',
                cancelLabel: 'Clear'
            },
            ranges: predefinedRanges
        });

        $('#duration').on('apply.daterangepicker', function (ev, picker) {
            if (picker.chosenLabel === 'Custom Range') {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
            } else {
                $(this).val(picker.chosenLabel);
            }
        });

        $('#duration').on('cancel.daterangepicker', function () {
            $(this).val('');
        });

        // select2 init
        $('.select2').select2({ width: '100%' });

        // Select all checkbox
        $('#selectAll').on('change', function () {
            $('.client-checkbox').prop('checked', this.checked).trigger('change');
            updateBulkButtonsState();
        });

        // Individual checkbox change
        $(document).on('change', '.client-checkbox', function () {
            $('#selectAll').prop(
                'checked',
                $('.client-checkbox').length === $('.client-checkbox:checked').length
            );
            updateBulkButtonsState();
        });

        // Bulk Delete button under table
        $('#bulkDeleteBtn').on('click', function () {
            const ids = $('.client-checkbox:checked').map(function () { return $(this).val(); }).get();

            if (ids.length === 0) {
                alert('Please select at least one client.');
                return;
            }

            if (!confirm('Are you sure you want to permanently delete the selected clients? This will also remove associated files and users.')) {
                return;
            }

            $.ajax({
                url: '{{ route("clients.bulk-delete") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    client_ids: ids
                },
                success: function (res) {
                    if (res.success) {
                        ids.forEach(function(id){
                            $("tr[data-id='" + id + "']").remove();
                        });

                        $('#selectAll').prop('checked', false);
                        updateBulkButtonsState();

                        alert(res.message || 'Clients deleted successfully.');
                    } else {
                        alert(res.message || 'Failed to delete clients.');
                    }
                },
                error: function (xhr) {
                    let msg = 'Something went wrong while deleting clients.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    alert(msg);
                }
            });
        });

        function updateBulkButtonsState() {
            const anyChecked = $('.client-checkbox:checked').length > 0;
            $('#bulkDeleteBtn').prop('disabled', !anyChecked);
        }

        updateBulkButtonsState();
    });
    </script>
@endpush
