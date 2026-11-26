@extends('admin.layout.app')

@section('title', 'Clients')

@section('content')
@include('admin.clients.partials.workspace-styles')

<div class="container-fluid" id="pms-client-workspace">
    <!-- Top Header -->
    <div class="pms-cl-topbar">
        <div class="pms-cl-heading-group">
            <div class="pms-cl-title-icon">
                <i class="bx bx-user-voice"></i>
            </div>
            <div>
                <h1 class="pms-cl-title">Client Management</h1>
                <p class="pms-cl-subtitle">Manage client accounts, company profiles, verification status, and linked projects.</p>
            </div>
        </div>

        <div class="pms-cl-actions">
            <div class="pms-cl-switcher" role="group" aria-label="View toggle">
                <a href="{{ route('clients.index') }}" class="pms-cl-switch-btn is-active" data-toggle="tooltip" title="Table View">
                    <i class="bi bi-list-ul"></i>
                    <span>All Clients</span>
                </a>
                <a href="{{ route('clients.pending') }}" class="pms-cl-switch-btn show-unverified" data-toggle="tooltip" title="Account verification pending">
                    <i class="bi bi-person-x"></i>
                    <span>Pending Verification</span>
                </a>
            </div>

            <a href="{{ route('clients.create') }}" class="pms-cl-btn-primary">
                <i class="bx bx-plus-circle"></i>
                <span>Add Client</span>
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
    <div class="pms-cl-card mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('clients.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-5">
                        <label for="duration" class="pms-cl-label">Duration / Date Range</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0"><i class="bx bx-calendar text-primary"></i></span>
                            <input type="text"
                                   class="form-control border-start-0 ps-0"
                                   id="duration"
                                   name="duration"
                                   value="{{ request('duration') }}"
                                   placeholder="Select Date Range"
                                   autocomplete="off">
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-4">
                        <label for="name" class="pms-cl-label">Filter by Client</label>
                        <select class="form-select select2" id="name" name="name">
                            <option value="">All Clients</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ request('name') == $c->id ? 'selected' : '' }}>
                                    {{ $c->client_uid ?? $c->id }} - {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-3 d-flex align-items-center gap-2">
                        <button type="submit" class="pms-cl-btn-primary flex-grow-1 text-nowrap">
                            <i class="bx bx-filter-alt"></i>
                            <span>Filter</span>
                        </button>
                        <a href="{{ route('clients.index') }}" class="pms-cl-btn-secondary flex-grow-1 text-nowrap">
                            <i class="bx bx-refresh"></i>
                            <span>Reset</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="pms-cl-card mb-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="clientsTable" class="table table-hover table-striped align-middle pms-cl-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px;"><input type="checkbox" class="form-check-input" id="selectAll"></th>
                            <th>Client ID</th>
                            <th style="white-space: nowrap;">Name</th>
                            <th>Email</th>
                            <th style="white-space: nowrap;">Company</th>
                            <th>Status</th>
                            <th style="white-space: nowrap;">Created</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                            <tr data-id="{{ $client->id }}">
                                <td><input type="checkbox" class="form-check-input client-checkbox" value="{{ $client->id }}"></td>
                                <td><span class="fw-semibold">{{ $client->client_uid ?? '—' }}</span></td>
                                <td style="white-space: nowrap;">
                                    <div class="d-flex align-items-center gap-2">
                                        @if(!empty($client->profile_picture))
                                            <img src="{{ asset($client->profile_picture) }}" alt="{{ $client->name }}" class="pms-cl-avatar">
                                        @else
                                            <span class="pms-cl-avatar">{{ strtoupper(substr($client->name ?? 'C', 0, 1)) }}</span>
                                        @endif
                                        <div>
                                            <a href="{{ route('clients.show', $client->id) }}" class="pms-cl-client-link d-block">{{ $client->name }}</a>
                                            @if($client->salutation)
                                                <small class="text-muted">{{ $client->salutation }} {{ $client->name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $client->email }}</td>
                                <td style="white-space: nowrap;">{{ $client->company_name ?? '—' }}</td>
                                <td>
                                    @php
                                        $clientStatus = strtolower(trim((string)($client->status ?? 'active')));
                                        $statusPillClass = match($clientStatus) {
                                            'active' => 'is-active',
                                            'pending' => 'is-pending',
                                            default => 'is-inactive'
                                        };
                                    @endphp
                                    <span class="pms-cl-status {{ $statusPillClass }} text-capitalize">
                                        {{ $client->status ?? 'Active' }}
                                    </span>
                                </td>
                                <td style="white-space: nowrap;">
                                    {{ $client->created_at ? \Carbon\Carbon::parse($client->created_at)->format('d-m-Y') : '—' }}
                                </td>
                                <td class="text-end">
                                    <div class="dropdown dropup">
                                        <button class="pms-cl-action-trigger" type="button" id="dropdownMenuButton{{ $client->id }}" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $client->id }}">
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

                                            <li><hr class="dropdown-divider"></li>

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
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3 border-top">
                <div>
                    <button id="bulkDeleteBtn" class="pms-cl-btn-danger" disabled>
                        <i class="bi bi-trash"></i>
                        <span>Bulk Delete</span>
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
                initClientDropdowns();
            }
        });

        function initClientDropdowns() {
            document.querySelectorAll('#clientsTable [data-bs-toggle="dropdown"]').forEach(function(btn) {
                if (window.bootstrap && bootstrap.Dropdown) {
                    bootstrap.Dropdown.getOrCreateInstance(btn, {
                        boundary: 'viewport',
                        popperConfig: function(defaultBsPopperConfig) {
                            return Object.assign({}, defaultBsPopperConfig, {
                                strategy: 'fixed',
                                placement: 'top-end'
                            });
                        }
                    });
                }
            });
        }

        initClientDropdowns();

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
