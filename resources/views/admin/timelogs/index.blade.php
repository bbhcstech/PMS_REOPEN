@extends('admin.layout.app')

@section('content')
<style>
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.85rem;
        font-size: 0.82rem;
        font-weight: 700;
        border-radius: 999px;
        white-space: nowrap;
        line-height: 1.2;
        color: #000000 !important;
    }
    .status-badge.completed, .status-badge.approved {
        background-color: #d1fae5 !important;
        color: #000000 !important;
        border: 1px solid #86efac !important;
    }
    .status-badge.doing, .status-badge.in-progress, .status-badge.inprogress {
        background-color: #dbeafe !important;
        color: #000000 !important;
        border: 1px solid #93c5fd !important;
    }
    .status-badge.pending {
        background-color: #fef08a !important;
        color: #000000 !important;
        border: 1px solid #fde047 !important;
    }
    .status-badge.to-do, .status-badge.todo {
        background-color: #e2e8f0 !important;
        color: #000000 !important;
        border: 1px solid #cbd5e1 !important;
    }
    .status-badge.incomplete, .status-badge.rejected {
        background-color: #fee2e2 !important;
        color: #000000 !important;
        border: 1px solid #fca5a5 !important;
    }
    .status-badge.waiting-for-approval {
        background-color: #ffedd5 !important;
        color: #000000 !important;
        border: 1px solid #fdba74 !important;
    }
</style>

<div class="container">
    @php $canReviewTimeLogs = in_array(strtolower((string) auth()->user()?->role), ['admin', 'hr', 'manager'], true); @endphp
    <br>

    @if($project)
        {{-- Standardized Project Header & 13-Tab Navigation --}}
        @include('admin.projects.partials.header', [
            'project' => $project,
            'activeTab' => 'timesheet'
        ])
    @endif

    <form method="GET" action="{{ $project ? route('projects.timelogs.index', $project->id) : route('timelogs.index') }}" class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Duration</label>
            <input type="text" name="daterange" id="daterange" class="form-control"
                   value="{{ request('start_date') && request('end_date') ? request('start_date').' To '.request('end_date') : '' }}">
            <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
            <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Employee</label>
            <select name="user_id" class="form-select select2">
                @if($canReviewTimeLogs)
                    <option value="">All</option>
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

        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">Filter</button>
            <a href="{{ route('timelogs.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
    &nbsp;

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <a href="{{ route('timelogs.create') }}" class="btn btn-primary">Log Time</a>
        </div>

        <div class="d-flex align-items-center gap-1 mb-3">
            <a href="{{ route('timelogs.index') }}" class="btn btn-sm btn-outline-primary {{ request()->routeIs('timelogs.index') ? 'active' : '' }}" data-toggle="tooltip" title="Timesheet">
                <i class="side-icon bi bi-list-ul"></i>
            </a>

            <a href="{{ route('timelogs.calendar') }}" class="btn btn-sm btn-outline-primary {{ request()->routeIs('timelogs.calendar') ? 'active' : '' }}"  data-toggle="tooltip" title="Calendar">
                <i class="side-icon bi bi-calendar"></i>
            </a>

            <a href="{{ route('timelogs.byEmployee')}}" class="btn btn-sm btn-outline-primary {{ request()->routeIs('timelogs.byEmployee') ? 'active' : '' }}" data-toggle="tooltip" title="Employee TimeLogs">
                <i class="side-icon bi bi-person"></i>
            </a>

            <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#howItWorksModal" title="How It Works">
                <i class="side-icon bi bi-question-circle"></i>
            </button>
        </div>
    </div>
    &nbsp;

    @include('admin.timelogs.partials.how-it-works-modal')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table id="timelogTable" class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th style="width: 40px;"><input type="checkbox" id="selectAllLogs"></th>
                <th style="width: 50px;">Id</th>
                <th style="white-space: nowrap;">Code</th>
                <th>Task</th>
                <th>Employee</th>
                <th style="white-space: nowrap;">Start Time</th>
                <th style="white-space: nowrap;">End Time</th>
                <th style="white-space: nowrap;">Task Duration</th>
                <th>Status</th>
                <th style="width: 80px;" class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $key => $task)
                @php
                    $latestTimer = $task->timers->sortByDesc('id')->first();
                    $assignedUsers = $task->assignees ?? collect();
                    if ($assignedUsers->isEmpty() && $task->assigned_to) {
                        $ids = explode(',', (string) $task->assigned_to);
                        $assignedUsers = \App\Models\User::whereIn('id', $ids)->get();
                    }
                    $assignedNames = $assignedUsers->isNotEmpty()
                        ? $assignedUsers->pluck('name')->join(', ')
                        : ($task->assignee?->name ?? ($latestTimer?->user?->name ?? 'Unassigned'));

                    $estHours = (int) ($task->estimate_hours ?? 0);
                    $estMins = (int) ($task->estimate_minutes ?? 0);
                    $hasEst = ($estHours > 0 || $estMins > 0);
                    $loggedFormatted = $task->total_logged_formatted ?: '00h 00m 00s';

                    $codePrefix = $task->project?->project_code ?? '';
                    $taskCode = $codePrefix
                        ? $codePrefix . str_pad($task->id, 4, '0', STR_PAD_LEFT)
                        : ($task->task_short_code ?: 'TASK-' . str_pad($task->id, 4, '0', STR_PAD_LEFT));

                    $startDateStr = $latestTimer?->start_date
                        ? \Carbon\Carbon::parse($latestTimer->start_date)->format('d-m-Y') . ($latestTimer->start_time ? ' ' . \Carbon\Carbon::parse($latestTimer->start_time)->format('h:i A') : '')
                        : ($task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('d-m-Y') : '--');

                    $endDateStr = $latestTimer?->end_date
                        ? \Carbon\Carbon::parse($latestTimer->end_date)->format('d-m-Y') . ($latestTimer->end_time ? ' ' . \Carbon\Carbon::parse($latestTimer->end_time)->format('h:i A') : '')
                        : ($task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d-m-Y') : '--');

                    $statusValue = $latestTimer?->status ?: ($task->status ?: 'To Do');
                @endphp
                <tr data-id="{{ $task->id }}">
                    <td>
                        <input type="checkbox" class="log-checkbox task-checkbox" value="{{ $task->id }}">
                    </td>
                    <td>{{ $key + 1 }}</td>

                    {{-- ✅ AUTO-GENERATED CODE --}}
                    <td style="white-space: nowrap;">
                        <span class="badge bg-light text-dark border fw-bold">{{ $taskCode }}</span>
                    </td>

                    {{-- ✅ TASK & PROJECT --}}
                    <td>
                        <div class="d-flex flex-column">
                            <a href="{{ route('tasks.show', $task->id) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $task->title ?? 'Untitled Task' }}
                            </a>
                            @if($task->project)
                                <small class="text-muted">
                                    <i class="bi bi-folder2-open me-1 text-primary"></i>{{ $task->project->name }}
                                </small>
                            @else
                                <small class="text-muted">No Project</small>
                            @endif
                        </div>
                    </td>

                    {{-- ✅ EMPLOYEE --}}
                    <td>
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            <span class="badge bg-label-info text-dark">{{ $assignedNames }}</span>
                        </div>
                    </td>

                    {{-- ✅ START TIME --}}
                    <td style="white-space: nowrap;">{{ $startDateStr }}</td>

                    {{-- ✅ END TIME --}}
                    <td style="white-space: nowrap;">{{ $endDateStr }}</td>

                    {{-- ✅ TASK DURATION --}}
                    <td style="white-space: nowrap;">
                        <div class="d-flex flex-column">
                            <div class="fw-bold text-success">
                                <i class="bi bi-clock-history me-1"></i>{{ $loggedFormatted }}
                            </div>
                            @if($hasEst)
                                <small class="text-muted">
                                    <i class="bi bi-hourglass-split me-1"></i>Est: {{ $estHours }}h {{ $estMins }}m
                                </small>
                            @endif
                            @if($task->timers->isNotEmpty())
                                <small class="text-primary mt-1">
                                    <i class="bi bi-journal-text me-1"></i>{{ $task->timers->count() }} log{{ $task->timers->count() === 1 ? '' : 's' }} recorded
                                </small>
                            @endif
                        </div>
                    </td>

                    {{-- ✅ STATUS --}}
                    <td class="statusCell" style="white-space: nowrap;">
                        @php
                            $normalizedStatus = strtolower(str_replace([' ', '_'], '-', (string) $statusValue));
                        @endphp
                        <span class="status-badge {{ $normalizedStatus }}">
                            {{ ucfirst($statusValue) }}
                        </span>
                    </td>

                    {{-- ✅ ACTION --}}
                    <td class="text-center">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <a class="dropdown-item" href="{{ route('timelogs.create', ['project_id' => $task->project_id, 'task_id' => $task->id]) }}">
                                        <i class="bi bi-plus-circle text-primary me-2"></i> Log Time
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('tasks.show', $task->id) }}">
                                        <i class="bi bi-eye me-2 text-info"></i> View Task
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('tasks.edit', $task->id) }}">
                                        <i class="bi bi-pencil-square me-2 text-warning"></i> Edit Task
                                    </a>
                                </li>

                                @if($latestTimer)
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('timelogs.show', $latestTimer->id) }}">
                                            <i class="bi bi-clock me-2"></i> View Latest Log
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('timelogs.edit', $latestTimer->id) }}">
                                            <i class="bi bi-pencil me-2"></i> Edit Latest Log
                                        </a>
                                    </li>
                                    <li>
                                        <form action="{{ route('timelogs.destroy', $latestTimer->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this time log?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-trash me-2"></i> Delete Log
                                            </button>
                                        </form>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

@push('js')
<script>
$(document).ready(function () {
    $('#timelogTable').DataTable({
        dom: 'rftip',
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Start typing to search..."
        }
    });

    // put Bulk Delete directly under "Showing X to Y of Z entries"
    const info = $('#timelogTable_info');
    if (info.length && !$('#bulkDeleteBtn').length) {
        info.parent().append(
            '<div class="mt-2">' +
                '<button id="bulkDeleteBtn" class="btn btn-danger btn-sm" disabled>Bulk Delete</button>' +
            '</div>'
        );
    }
});
</script>

<script>
document.getElementById('toggle-more').addEventListener('click', function(e) {
    e.preventDefault();
    const moreTabs = document.getElementById('more-tabs');
    if (moreTabs.classList.contains('d-none')) {
        moreTabs.classList.remove('d-none');
        this.innerHTML = 'Less ▴';
    } else {
        moreTabs.classList.add('d-none');
        this.innerHTML = 'More ▾';
    }
});
</script>

<script>
$(function() {
    $('#daterange').daterangepicker({
        autoUpdateInput: false,
        locale: { cancelLabel: 'Clear' },
        ranges: {
            'Today': [moment(), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Last 90 Days': [moment().subtract(89, 'days'), moment()],
            'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment()],
            'Last 1 Year': [moment().subtract(1, 'year').startOf('day'), moment()],
            'Custom Range': []
        }
    });

    $('#daterange').on('apply.daterangepicker', function(ev, picker) {
        $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
        $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
        $(this).val(picker.startDate.format('DD-MM-YYYY') + ' To ' + picker.endDate.format('DD-MM-YYYY'));
    });

    $('#daterange').on('cancel.daterangepicker', function() {
        $(this).val('');
        $('#start_date').val('');
        $('#end_date').val('');
    });
});
</script>

<script>
$(document).ready(function() {
    function refreshBulkControls() {
        let selectedCount = $('.log-checkbox:checked').length;
        let statusSelected = $('#bulkLogStatus').val() && $('#bulkLogStatus').val().length > 0;
        $('#bulkLogStatus').prop('disabled', selectedCount === 0);
        $('#applyBulkLogStatus').prop('disabled', selectedCount === 0 || !statusSelected);

        // enable / disable bulk delete
        $('#bulkDeleteBtn').prop('disabled', selectedCount === 0);
    }

    $('#timelogTable tbody').on('change', '.log-checkbox', function () {
        refreshBulkControls();
    });

    $('#selectAllLogs').on('change', function () {
        const checked = $(this).prop('checked');
        $('.log-checkbox').prop('checked', checked).trigger('change');
    });

    $('#bulkLogStatus').on('change', function() {
        refreshBulkControls();
    });

    function normalizeStatus(value) {
        if (!value) return '';
        const v = value.toLowerCase();
        if (v === 'approve' || v === 'approved') return 'approved';
        if (v === 'reject' || v === 'rejected') return 'rejected';
        return 'pending';
    }

    $('#applyBulkLogStatus').on('click', function() {
        let selectedIds = $('.log-checkbox:checked').map(function() { return $(this).val(); }).get();
        let statusRaw = $('#bulkLogStatus').val();
        let status = normalizeStatus(statusRaw);

        if(!status || selectedIds.length === 0){
            alert('Select logs and status to apply.');
            return;
        }

        if (!confirm(`Apply status "${status}" to ${selectedIds.length} selected logs?`)) return;

        $.ajax({
            url: "{{ route('timelogs.bulkStatusUpdate') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                ids: selectedIds,
                status: status
            },
            success: function(res) {
                if (res.success) {

                    selectedIds.forEach(function(id) {
                        const row = $("tr[data-id='" + id + "']");
                        if (row.length) {
                            row.find('.statusCell').text(status.charAt(0).toUpperCase() + status.slice(1));
                        }
                    });

                    $('#selectAllLogs').prop('checked', false);
                    $('.log-checkbox').prop('checked', false);
                    $('#bulkLogStatus').val('');
                    refreshBulkControls();

                    alert(res.message || 'Status updated successfully.');
                } else {
                    alert(res.message || 'Failed to update status.');
                }
            },
            error: function(xhr){
                console.error(xhr);
                let msg = 'Something went wrong!';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            }
        });
    });

    // bulk delete click
    $(document).on('click', '#bulkDeleteBtn', function () {
        let selectedIds = $('.log-checkbox:checked').map(function() { return $(this).val(); }).get();

        if (selectedIds.length === 0) {
            alert('Select at least one log to delete.');
            return;
        }

        if (!confirm(`Delete ${selectedIds.length} selected logs?`)) return;

        $.ajax({
            url: "{{ route('timelogs.bulk-delete') }}",
            type: 'DELETE',
            data: {
                _token: "{{ csrf_token() }}",
                ids: selectedIds
            },
            success: function(res) {
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.message || 'Failed to delete logs.');
                }
            },
            error: function(xhr){
                console.error(xhr);
                let msg = 'Something went wrong!';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            }
        });
    });
});
</script>
@endpush

@endsection
