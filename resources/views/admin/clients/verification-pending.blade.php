@extends('admin.layout.app')

@section('content')
@include('admin.clients.partials.workspace-styles')

<div class="container-fluid" id="pms-client-workspace">
    <!-- Top Header -->
    <div class="pms-cl-topbar">
        <div class="pms-cl-heading-group">
            <div class="pms-cl-title-icon">
                <i class="bi bi-person-exclamation"></i>
            </div>
            <div>
                <h1 class="pms-cl-title">Pending Client Verification</h1>
                <p class="pms-cl-subtitle">Review unverified client accounts, update status, or manage pending records.</p>
            </div>
        </div>

        <div class="pms-cl-actions">
            <div class="pms-cl-switcher" role="group" aria-label="View toggle">
                <a href="{{ route('clients.index') }}" class="pms-cl-switch-btn" data-toggle="tooltip" title="Table View">
                    <i class="bi bi-list-ul"></i>
                    <span>All Clients</span>
                </a>
                <a href="{{ route('clients.pending') }}" class="pms-cl-switch-btn is-active show-unverified" data-toggle="tooltip" title="Account verification pending">
                    <i class="bi bi-person-x"></i>
                    <span>Pending Verification</span>
                </a>
            </div>

            <a href="{{ route('clients.create') }}" class="pms-cl-btn-primary">
                <i class="bi bi-plus-circle"></i>
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

    <!-- Filters -->
    <div class="pms-cl-card mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('clients.pending') }}">
                <div class="row g-3 align-items-end">
                    <!-- Date Range -->
                    <div class="col-lg-4 col-md-5">
                        <label for="duration" class="pms-cl-label">Duration / Date Range</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0"><i class="bx bx-calendar text-primary"></i></span>
                            <input type="text"
                                   class="form-control border-start-0 ps-0"
                                   id="duration"
                                   name="duration"
                                   value="{{ request('duration') }}"
                                   placeholder="Select Range"
                                   autocomplete="off">
                        </div>
                    </div>

                    <!-- Clients -->
                    <div class="col-lg-4 col-md-4">
                        <label for="name" class="pms-cl-label">Filter by Client</label>
                        <select class="form-select select2" id="name" name="name">
                            <option value="">All Pending Clients</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ request('name') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="col-lg-4 col-md-3 d-flex align-items-center gap-2">
                        <button type="submit" class="pms-cl-btn-primary flex-grow-1 text-nowrap">
                            <i class="bx bx-filter-alt"></i>
                            <span>Filter</span>
                        </button>
                        <a href="{{ route('clients.pending') }}" class="pms-cl-btn-secondary flex-grow-1 text-nowrap">
                            <i class="bx bx-refresh"></i>
                            <span>Reset</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Client Table & Bulk Actions -->
    <div class="pms-cl-card mb-4">
        <div class="card-body p-4">
            <div class="pms-cl-table-toolbar">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="pms-cl-label mb-0 me-1">Bulk Action:</span>
                    <select class="form-select" id="quick-action-type" style="width: auto; min-width: 175px;">
                        <option value="">No Action</option>
                        <option value="change-status">Change Status</option>
                        <option value="delete">Delete</option>
                    </select>

                    <select class="form-select d-none" id="quick-action-status" style="width: auto; min-width: 145px;">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>

                    <button type="button" class="pms-cl-btn-primary" id="quick-action-apply">
                        <i class="bi bi-check2-circle"></i>
                        <span>Apply</span>
                    </button>
                </div>

                <span class="text-muted small fw-semibold">
                    {{ count($clients) }} pending {{ \Illuminate\Support\Str::plural('account', count($clients)) }}
                </span>
            </div>

            <div class="table-responsive">
                <table id="clientsTable" class="table table-hover table-striped align-middle pms-cl-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;"><input type="checkbox" class="form-check-input" id="selectAll"></th>
                            <th>ID</th>
                            <th style="white-space: nowrap;">Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th style="white-space: nowrap;">Created</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                            <tr>
                                <td><input type="checkbox" class="form-check-input client-checkbox" value="{{ $client->id }}"></td>
                                <td><span class="fw-semibold">#{{ $client->id }}</span></td>
                                <td style="white-space: nowrap;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="pms-cl-avatar">{{ strtoupper(substr($client->name ?? 'C', 0, 1)) }}</span>
                                        <div>
                                            <a href="{{ route('clients.show', $client->id) }}" class="pms-cl-client-link d-block">{{ $client->name }}</a>
                                            @if($client->salutation)
                                                <small class="text-muted">{{ $client->salutation }} {{ $client->name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $client->email }}</td>
                                <td>
                                    @php
                                        $vStatus = strtolower(trim((string)($client->status ?? 'pending')));
                                        $vPillClass = match($vStatus) {
                                            'active' => 'is-active',
                                            'pending' => 'is-pending',
                                            default => 'is-inactive'
                                        };
                                    @endphp
                                    <span class="pms-cl-status {{ $vPillClass }} text-capitalize">{{ $client->status ?? 'Pending' }}</span>
                                </td>
                                <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($client->created_at)->format('d-m-Y') }}</td>
                                <td class="text-end">
                                    <div class="dropdown dropup">
                                        <button class="pms-cl-action-trigger" type="button" id="dropdownMenuButton{{ $client->id }}" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $client->id }}">
                                            <li><a class="dropdown-item" href="{{ route('clients.show', $client->id) }}"><i class="bi bi-eye me-2"></i> View</a></li>
                                            <li><a class="dropdown-item" href="{{ route('clients.edit', $client->id) }}"><i class="bi bi-pencil-square me-2"></i> Edit</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('clients.destroy', $client->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this client?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i> Delete</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('js')
<!-- Bootstrap Select CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css">

<!-- Font Awesome (for icons) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">


<!-- Bootstrap Select JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
$(function() {
    // Initialize Select2
    $('.select2').select2({ width: '100%' });

    // Initialize DataTable
    $('#clientsTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy','csv','excel','pdf','print'],
        responsive: true,
        pageLength: 10,
        lengthMenu: [10,25,50,100],
        language: { search: "_INPUT_", searchPlaceholder: "Start typing to search..." }
    });

    // CSRF setup for AJAX
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // Toggle Apply button
    function toggleApplyButton() {
        let anyChecked = $('.client-checkbox:checked').length > 0;
        let actionSelected = $('#quick-action-type').val() !== '';
        $('#quick-action-apply').prop('disabled', !(anyChecked && actionSelected));
    }

    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.client-checkbox').prop('checked', this.checked);
        toggleApplyButton();
    });

    // Individual checkboxes & dropdown
    $('.client-checkbox, #quick-action-type, #quick-action-status').on('change', toggleApplyButton);

    // Show/hide status dropdown
    $('#quick-action-type').on('change', function() {
        if ($(this).val() === 'change-status') {
            $('#quick-action-status').removeClass('d-none');
        } else {
            $('#quick-action-status').addClass('d-none');
        }
        toggleApplyButton();
    });

    // Bulk action AJAX
    $('#quick-action-apply').on('click', function(e) {
        e.preventDefault();
        let ids = $('.client-checkbox:checked').map(function() { return $(this).val(); }).get();
        let action = $('#quick-action-type').val();
        let status = $('#quick-action-status').val();

        if (!ids.length) return alert('Please select at least one client.');
        if (action === 'delete' && !confirm('Are you sure you want to delete selected clients?')) return;

        $.post('{{ route("clients.bulkAction") }}', { client_ids: ids, action: action, status: status }, function() {
            location.reload();
        }).fail(function() { alert('Something went wrong'); });
    });

    // Date range picker
    const predefinedRanges = {
        'Today': [moment(), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1,'month').startOf('month'), moment().subtract(1,'month').endOf('month')],
        'Last 90 Days': [moment().subtract(89,'days'), moment()],
        'Last 6 Months': [moment().subtract(6,'months').startOf('month'), moment()],
        'Last 1 Year': [moment().subtract(1,'year').startOf('month'), moment()],
        'Custom Range': []
    };

    $('#duration').daterangepicker({
        autoUpdateInput: false,
        showDropdowns: true,
        opens: 'left',
        locale: { format: 'YYYY-MM-DD', cancelLabel: 'Clear' },
        ranges: predefinedRanges
    });

    $('#duration').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.chosenLabel === 'Custom Range' ? picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD') : picker.chosenLabel);
    });

    $('#duration').on('cancel.daterangepicker', function() { $(this).val(''); });
});
</script>
@endpush

@endsection
