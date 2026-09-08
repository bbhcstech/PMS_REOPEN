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
        <div></div>

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

                                {{-- AUTO-GENERATED CODE --}}
                                <td style="white-space: nowrap;">
                                    <span class="badge bg-light text-dark border fw-bold">{{ $taskCode }}</span>
                                </td>

                                {{-- TASK & PROJECT --}}
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

                                {{-- EMPLOYEE --}}
                                <td>
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        <span class="badge bg-label-info text-dark">{{ $assignedNames }}</span>
                                    </div>
                                </td>

                                {{-- START TIME --}}
                                <td style="white-space: nowrap;">{{ $startDateStr }}</td>

                                {{-- END TIME --}}
                                <td style="white-space: nowrap;">{{ $endDateStr }}</td>

                                {{-- TASK DURATION --}}
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

                                {{-- STATUS --}}
                                <td class="statusCell" style="white-space: nowrap;">
                                    @php
                                        $normalizedStatus = strtolower(str_replace([' ', '_'], '-', (string) $statusValue));
                                    @endphp
                                    <span class="status-badge {{ $normalizedStatus }}">
                                        {{ ucfirst($statusValue) }}
                                    </span>
                                </td>

                                {{-- ACTION --}}
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
        </div>
    </div>
</div>

@include('admin.timelogs.partials.how-it-works-modal')
@endsection

@push('js')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
    $(document).ready(function () {
        $('#timelogTable').DataTable({
            responsive: true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            language: {
                search: "",
                searchPlaceholder: "Start typing to search timesheets..."
            },
            columnDefs: [
                { targets: [0, 9], searchable: false, orderable: false }
            ]
        });

        // Select all
        $('#selectAllLogs').on('click', function () {
            $('.log-checkbox').prop('checked', this.checked);
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
