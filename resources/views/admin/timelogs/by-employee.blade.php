@extends('admin.layout.app')

@section('title', 'Employee Time Logs')

@section('content')
@include('admin.timelogs.partials.workspace-styles')

<div id="pms-timelogs-employee-page" class="pms-ts-page">
    <!-- Top Header -->
    <div class="pms-ts-topbar">
        <div class="pms-ts-title-wrap">
            <div class="pms-ts-title-icon" aria-hidden="true">
                <i class="bx bx-user-check"></i>
            </div>
            <div>
                <h3 class="pms-ts-title">Employee Time Logs</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('timelogs.index') }}" class="text-decoration-none">Timesheet</a></li>
                        <li class="breadcrumb-item active" aria-current="page">By Employee</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="pms-ts-actions">
            <a href="{{ route('timelogs.create') }}" class="btn pms-ts-btn-primary">
                <i class="bx bx-plus-circle"></i> Log Time
            </a>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card pms-ts-card mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('timelogs.byEmployee') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-4">
                        <label class="pms-ts-label">Duration / Date Range</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0"><i class="bx bx-calendar text-primary"></i></span>
                            <input type="text" name="daterange" id="daterange" class="form-control border-start-0 ps-0"
                                   value="{{ request('start_date') && request('end_date') ? request('start_date').' To '.request('end_date') : '' }}"
                                   placeholder="Select Range">
                        </div>
                        <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                        <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
                    </div>

                    <div class="col-lg-3 col-md-3">
                        <label class="pms-ts-label">Select Employee</label>
                        <select name="user_id" class="form-select">
                            <option value="">All Employees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-3">
                        <label class="pms-ts-label">Search Keywords</label>
                        <input type="text" name="search" class="form-control"
                               placeholder="Search task, project, memo..."
                               value="{{ request('search') }}">
                    </div>

                    <div class="col-lg-3 col-md-2 d-flex align-items-center gap-2">
                        <button type="submit" class="btn pms-ts-btn-primary flex-grow-1 text-nowrap">
                            <i class="bx bx-filter-alt"></i> Filter
                        </button>
                        <a href="{{ route('timelogs.byEmployee') }}" class="btn pms-ts-btn-secondary flex-grow-1 text-nowrap">
                            <i class="bx bx-refresh"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Actions & Views Switcher Toolbar -->
    <div class="pms-ts-switcher-bar">
        <div class="text-muted small">
            <i class="bx bx-info-circle text-primary me-1"></i> Displaying logged hours grouped by employees.
        </div>

        <!-- View Switcher -->
        <div class="pms-ts-switcher" role="group" aria-label="Timesheet views">
            <a href="{{ route('timelogs.index') }}" class="pms-ts-tab {{ request()->routeIs('timelogs.index') ? 'active' : '' }}" title="Timesheet List">
                <i class="bx bx-list-ul"></i> List
            </a>
            <a href="{{ route('timelogs.calendar') }}" class="pms-ts-tab {{ request()->routeIs('timelogs.calendar') ? 'active' : '' }}" title="Calendar View">
                <i class="bx bx-calendar"></i> Calendar
            </a>
            <a href="{{ route('timelogs.byEmployee')}}" class="pms-ts-tab {{ request()->routeIs('timelogs.byEmployee') ? 'active' : '' }}" title="By Employee Summary">
                <i class="bx bx-user"></i> By Employee
            </a>
            <button type="button" class="pms-ts-tab" data-bs-toggle="modal" data-bs-target="#howItWorksModal" title="How It Works">
                <i class="bx bx-help-circle"></i>
            </button>
        </div>
    </div>

    <!-- Employee TimeLogs Table Card -->
    <div class="card pms-ts-card overflow-hidden">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle w-100" id="employeeLogsTable">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Employee</th>
                            <th>Project</th>
                            <th>Task & Memo</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Total Hours</th>
                            <th>Status</th>
                            <th style="width: 80px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $key => $log)
                            <tr>
                                <td class="fw-bold">#{{ $key + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold small" style="width: 32px; height: 32px;">
                                            {{ strtoupper(substr($log->employee?->name ?? 'E', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $log->employee?->name ?? 'Employee' }}</div>
                                            <small class="text-muted">{{ ucfirst($log->employee?->role ?? 'employee') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark mb-0">{{ $log->project?->name ?? 'General Project' }}</div>
                                    <small class="text-muted">{{ $log->project?->project_code ?? '' }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $log->task?->title ?? 'Timesheet Work' }}</div>
                                    @if($log->memo)
                                        <small class="text-muted text-wrap d-block">{{ Str::limit($log->memo, 40) }}</small>
                                    @endif
                                </td>
                                <td class="text-nowrap small text-muted">
                                    {{ \Carbon\Carbon::parse($log->start_date ?? $log->start_time)->format('d M Y') }}
                                    <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($log->start_time)->format('h:i A') }}</div>
                                </td>
                                <td class="text-nowrap small text-muted">
                                    {{ \Carbon\Carbon::parse($log->end_date ?? $log->end_time)->format('d M Y') }}
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
                                <td>
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
                                    <a href="{{ route('timelogs.show', $log->id) }}" class="btn btn-sm pms-ts-btn-secondary" style="height: 32px; padding: 0 12px; font-size: 0.78rem;">
                                        <i class="bx bx-show"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="pms-ts-empty">
                                    <div class="pms-ts-empty-icon" aria-hidden="true">
                                        <i class="bx bx-user-check"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">No employee time logs found</h6>
                                    <p class="text-muted small mb-3">No logs matched your selected date range or filters.</p>
                                    <a href="{{ route('timelogs.create') }}" class="btn pms-ts-btn-primary">
                                        <i class="bx bx-plus-circle"></i> Log Time
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('admin.timelogs.partials.how-it-works-modal')
@endsection

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
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
                'Last 1 Year': [moment().subtract(1, 'year').startOf('day'), moment()]
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
@endpush
