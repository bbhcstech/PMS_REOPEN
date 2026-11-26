@extends('admin.layout.app')

@section('title', 'Task Report')

@section('content')
<div class="container-fluid px-4 pt-1 pb-4 task-report-page">
    <!-- Header -->
    <div class="task-report-header mb-4">
        <div class="d-flex align-items-center gap-3 mb-1">
            <div class="header-icon-badge task-report-header__icon" aria-hidden="true">
                <i class="bx bx-task"></i>
            </div>
            <h3 id="task-report-heading" class="task-report-header__title mb-0">Task Report</h3>
        </div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb task-report-breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Task Report</li>
            </ol>
        </nav>
        <p class="task-report-header__subtitle mb-0">Monitor, analyse, and track task performance across all projects.</p>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card task-report-stat-card h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-3">
                    <div>
                        <div class="task-report-stat-card__label">Total Tasks</div>
                        <div class="task-report-stat-card__value">{{ $stats['total'] }}</div>
                    </div>
                    <div class="stat-icon task-report-stat-icon task-report-stat-icon--blue" aria-hidden="true">
                        <i class="bx bx-file"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card task-report-stat-card h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-3">
                    <div>
                        <div class="task-report-stat-card__label">Completed Tasks</div>
                        <div class="task-report-stat-card__value">{{ $stats['completed'] }}</div>
                    </div>
                    <div class="stat-icon task-report-stat-icon task-report-stat-icon--green" aria-hidden="true">
                        <i class="bx bx-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card task-report-stat-card h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-3">
                    <div>
                        <div class="task-report-stat-card__label">In Progress</div>
                        <div class="task-report-stat-card__value">{{ $stats['in_progress'] }}</div>
                    </div>
                    <div class="stat-icon task-report-stat-icon task-report-stat-icon--amber" aria-hidden="true">
                        <i class="bx bx-time-five"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card task-report-stat-card h-100">
                <div class="card-body d-flex align-items-start justify-content-between gap-3">
                    <div>
                        <div class="task-report-stat-card__label">Pending / Open</div>
                        <div class="task-report-stat-card__value">{{ $stats['pending'] }}</div>
                    </div>
                    <div class="stat-icon task-report-stat-icon task-report-stat-icon--slate" aria-hidden="true">
                        <i class="bx bx-time"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card task-report-card task-report-filter-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.task') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-6">
                        <label for="taskReportProjectFilter" class="form-label task-report-filter-label">Filter by Project</label>
                        <select id="taskReportProjectFilter" name="project_id" class="form-select task-report-select">
                            <option value="">All Projects</option>
                            @foreach($projects as $proj)
                                <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                                    {{ $proj->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <label for="taskReportStatusFilter" class="form-label task-report-filter-label">Filter by Status</label>
                        <select id="taskReportStatusFilter" name="status" class="form-select task-report-select">
                            <option value="">All Statuses</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-12 d-flex flex-wrap align-items-center gap-2">
                        <button type="submit" class="btn btn-primary task-report-btn-filter text-nowrap">
                            <i class="bx bx-filter-alt me-1" aria-hidden="true"></i> Filter Report
                        </button>
                        <a href="{{ route('reports.task') }}" class="btn task-report-btn-reset text-nowrap">
                            <i class="bx bx-refresh me-1" aria-hidden="true"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Table Card -->
    <div class="card task-report-card task-report-table-card">
        <div class="card-body">
            <div class="table-responsive" tabindex="0" role="region" aria-labelledby="task-report-heading">
                <table id="reportTable" class="table table-hover align-middle w-100 task-report-table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Task Title</th>
                            <th scope="col">Project</th>
                            <th scope="col">Category</th>
                            <th scope="col">Priority</th>
                            <th scope="col">Status</th>
                            <th scope="col">Due Date</th>
                            <th scope="col">Created On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $key => $task)
                            <tr>
                                <td class="fw-bold">#{{ $key + 1 }}</td>
                                <td><div class="fw-semibold text-dark">{{ $task->title }}</div></td>
                                <td>{{ $task->project->name ?? 'General' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $task->category->category_name ?? 'Task' }}</span></td>
                                <td>
                                    @php
                                        $pBadge = ['low'=>'secondary','medium'=>'info','high'=>'warning','critical'=>'danger'][strtolower((string)$task->priority)] ?? 'light';
                                    @endphp
                                    <span class="badge bg-{{ $pBadge }}">{{ ucfirst($task->priority) }}</span>
                                </td>
                                <td>
                                    @if(strtolower((string)$task->status) == 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif(strtolower((string)$task->status) == 'in_progress')
                                        <span class="badge bg-info">In Progress</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td>{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : '—' }}</td>
                                <td>{{ $task->created_at ? \Carbon\Carbon::parse($task->created_at)->format('d M Y') : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .task-report-page {
        accent-color: var(--pms-primary, #2F6BFF);
    }

    .task-report-header__icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: #0F172A;
        color: #FFFFFF;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.14);
    }

    .task-report-header__title {
        font-size: 1.55rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--pms-text, #0F172A);
    }

    .task-report-breadcrumb {
        font-size: 0.78rem;
        font-weight: 500;
    }

    .task-report-breadcrumb .breadcrumb-item a {
        color: var(--pms-muted, #64748B);
    }

    .task-report-breadcrumb .breadcrumb-item.active {
        color: var(--pms-text, #0F172A);
        font-weight: 600;
    }

    .task-report-header__subtitle {
        font-size: 0.84rem;
        color: var(--pms-muted, #64748B);
    }

    .task-report-stat-card,
    .task-report-card {
        background: var(--pms-surface, #FFFFFF);
        border: 1px solid var(--pms-border, #E2E8F0) !important;
        border-radius: 16px !important;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03) !important;
    }

    .task-report-stat-card .card-body {
        padding: 1.25rem 1.35rem;
    }

    .task-report-stat-card__label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--pms-muted, #64748B);
        margin-bottom: 0.5rem;
    }

    .task-report-stat-card__value {
        font-size: 1.9rem;
        font-weight: 800;
        line-height: 1.1;
        color: var(--pms-text, #0F172A);
    }

    .task-report-stat-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }

    .task-report-stat-icon--blue {
        background: #EFF6FF;
        border: 1px solid #DBEAFE;
        color: #2F6BFF;
    }

    .task-report-stat-icon--green {
        background: #ECFDF5;
        border: 1px solid #D1FAE5;
        color: #10B981;
    }

    .task-report-stat-icon--amber {
        background: #FFFBEB;
        border: 1px solid #FEF3C7;
        color: #D97706;
    }

    .task-report-stat-icon--slate {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        color: #475569;
    }

    .task-report-filter-card .card-body {
        padding: 1.25rem 1.5rem;
    }

    .task-report-filter-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--pms-text, #0F172A);
        margin-bottom: 0.45rem;
    }

    .task-report-select {
        height: 42px;
        border-radius: 10px !important;
        border: 1px solid var(--pms-border, #E2E8F0) !important;
        font-size: 0.88rem;
        font-weight: 500;
        padding-left: 0.9rem;
    }

    .task-report-btn-filter {
        height: 42px;
        border-radius: 10px !important;
        padding: 0 1.35rem !important;
        font-size: 0.88rem !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 1 1 auto;
    }

    .task-report-btn-reset {
        height: 42px;
        border-radius: 10px !important;
        padding: 0 1.25rem !important;
        font-size: 0.88rem !important;
        font-weight: 600 !important;
        background: var(--pms-surface, #FFFFFF) !important;
        border: 1px solid var(--pms-border, #E2E8F0) !important;
        color: var(--pms-text, #334155) !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 1 auto;
        min-width: 112px;
    }

    .task-report-btn-reset:hover,
    .task-report-btn-reset:focus-visible {
        background: var(--pms-surface-soft, #F8FAFC) !important;
        border-color: #CBD5E1 !important;
        color: var(--pms-text, #0F172A) !important;
    }

    .task-report-table-card .card-body {
        padding: 1.25rem 1.5rem;
    }

    /* Match the clean toolbar + left-aligned Search + framed table look */
    .task-report-table-card .pms-table-tools {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        min-height: 52px;
        padding: 0.55rem 0.9rem;
        margin-bottom: 1rem;
    }

    .task-report-table-card .pms-table-export__toggle {
        background: var(--pms-surface, #FFFFFF);
        border: 1px solid var(--pms-border, #E2E8F0);
        color: var(--pms-text, #334155);
        font-weight: 600;
        font-size: 0.82rem;
        min-height: 34px;
        padding: 0.35rem 0.8rem;
        border-radius: 8px;
    }

    .task-report-table-card .pms-table-export__toggle:hover,
    .task-report-table-card .pms-table-export__toggle:focus-visible {
        background: var(--pms-surface-soft, #F1F5F9);
        border-color: var(--pms-border, #CBD5E1);
        color: var(--pms-text, #0F172A);
        box-shadow: none;
        transform: none;
    }

    .task-report-table-card .dataTables_wrapper .dataTables_filter {
        text-align: left !important;
    }

    .task-report-table-card .dataTables_wrapper .dataTables_filter label {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.84rem;
        font-weight: 600;
        color: var(--pms-text, #0F172A);
        margin-bottom: 0;
    }

    .task-report-table-card .dataTables_wrapper .dataTables_filter input {
        border-radius: 8px !important;
        padding: 0.4rem 0.85rem !important;
        border: 1px solid var(--pms-border, #E2E8F0) !important;
        min-width: 220px;
        height: 36px;
        margin-left: 0 !important;
        background: var(--pms-surface, #FFFFFF) !important;
    }

    .task-report-table-card .pms-table-scroll-host {
        border: 1px solid var(--pms-border, #E2E8F0) !important;
        border-radius: 12px !important;
        box-shadow: none !important;
        overflow-x: auto !important;
    }

    .task-report-table {
        margin-bottom: 0 !important;
        border-collapse: separate;
        border-spacing: 0;
    }

    .task-report-table thead th {
        background: #F8FAFC !important;
        color: #475569 !important;
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        border-bottom: 1px solid #E2E8F0 !important;
        padding: 0.85rem 1rem !important;
        white-space: nowrap;
    }

    .task-report-table td.dataTables_empty {
        padding: 0 !important;
        border-bottom: 0 !important;
        background: transparent !important;
    }

    .task-report-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 4.5rem 1.5rem;
    }

    .task-report-empty__icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #F1F5F9;
        color: #64748B;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        margin-bottom: 0.9rem;
    }

    .content-wrapper .task-report-empty__icon i,
    .task-report-empty__icon i {
        color: #64748B !important;
        -webkit-text-fill-color: #64748B !important;
    }

    .task-report-empty__title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--pms-text, #0F172A);
        margin-bottom: 0.35rem;
    }

    .task-report-empty__text {
        max-width: 280px;
        font-size: 0.8rem;
        line-height: 1.5;
        color: var(--pms-muted, #64748B);
    }

    .task-report-empty__btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.45rem 1.05rem;
        border-radius: 8px;
        background: #EFF6FF;
        color: #2F6BFF !important;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .task-report-empty__btn:hover,
    .task-report-empty__btn:focus-visible {
        background: #DBEAFE;
        color: #1E4FCC !important;
    }

    .task-report-table-card .dataTables_info {
        font-size: 0.8rem !important;
        color: var(--pms-muted, #64748B) !important;
    }

    .task-report-table-card .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        font-size: 0.8rem !important;
    }

    html[data-pms-theme="dark"] .task-report-header__icon {
        background: #1E293B;
        border: 1px solid rgba(238, 241, 251, 0.12);
    }

    html[data-pms-theme="dark"] .task-report-stat-icon--blue {
        background: rgba(47, 107, 255, 0.16);
        border-color: rgba(47, 107, 255, 0.3);
        color: #60A5FA;
    }

    html[data-pms-theme="dark"] .task-report-stat-icon--green {
        background: rgba(16, 185, 129, 0.16);
        border-color: rgba(16, 185, 129, 0.3);
        color: #34D399;
    }

    html[data-pms-theme="dark"] .task-report-stat-icon--amber {
        background: rgba(245, 158, 11, 0.16);
        border-color: rgba(245, 158, 11, 0.3);
        color: #FBBF24;
    }

    html[data-pms-theme="dark"] .task-report-stat-icon--slate {
        background: rgba(148, 163, 184, 0.14);
        border-color: rgba(148, 163, 184, 0.25);
        color: #CBD5E1;
    }

    html[data-pms-theme="dark"] .task-report-table-card .pms-table-tools {
        background: #141B3D !important;
        border-color: rgba(238, 241, 251, 0.12) !important;
    }

    html[data-pms-theme="dark"] .task-report-table thead th {
        background: #141B3D !important;
        color: #CBD5E1 !important;
        border-bottom-color: rgba(238, 241, 251, 0.1) !important;
    }

    html[data-pms-theme="dark"] .task-report-empty__icon {
        background: rgba(148, 163, 184, 0.14);
        color: #94A3B8;
    }

    html[data-pms-theme="dark"] .content-wrapper .task-report-empty__icon i,
    html[data-pms-theme="dark"] .task-report-empty__icon i {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    html[data-pms-theme="dark"] .task-report-empty__btn {
        background: rgba(47, 107, 255, 0.18);
        color: #93C5FD !important;
    }
</style>
@endpush

@push('js')
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script>
    $(document).ready(function () {
        var emptyStateHtml =
            '<div class="task-report-empty">' +
                '<div class="stat-icon task-report-empty__icon" aria-hidden="true"><i class="bx bx-clipboard"></i></div>' +
                '<div class="task-report-empty__title">No tasks found</div>' +
                '<p class="task-report-empty__text mb-3">Try adjusting your filters or selecting a different project to view tasks.</p>' +
                '<a href="{{ route('reports.task') }}" class="task-report-empty__btn">Reset Filters</a>' +
            '</div>';

        $('#reportTable').DataTable({
            dom: '<"d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3"Bf>rt<"d-flex flex-wrap align-items-center justify-content-between gap-3 mt-3"ip>',
            buttons: [
                { extend: 'copy', className: 'btn', text: '<i class="bx bx-copy me-1"></i> Copy' },
                { extend: 'csv', className: 'btn', text: '<i class="bx bx-file me-1"></i> CSV' },
                { extend: 'excel', className: 'btn', text: '<i class="bx bx-spreadsheet me-1"></i> Excel' },
                { extend: 'pdf', className: 'btn', text: '<i class="bx bxs-file-pdf me-1"></i> PDF' },
                { extend: 'print', className: 'btn', text: '<i class="bx bx-printer me-1"></i> Print' }
            ],
            language: {
                emptyTable: emptyStateHtml,
                zeroRecords: emptyStateHtml
            },
            pageLength: 25
        });
    });
</script>
@endpush
