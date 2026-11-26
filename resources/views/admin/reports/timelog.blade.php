@extends('admin.layout.app')

@section('title', 'Time Log Report')

@section('content')
<div class="container-fluid px-4 pt-1 pb-4 timelog-report-page">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4 timelog-report-header">
        <div class="timelog-report-header__left">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="header-icon-badge timelog-report-header__icon" aria-hidden="true">
                    <i class="bx bx-time-five"></i>
                </div>
                <h3 id="timelog-report-heading" class="timelog-report-header__title mb-0">Time Log Report</h3>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2 timelog-report-header__meta">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb timelog-report-breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Time Log Report</li>
                    </ol>
                </nav>
                <span class="timelog-report-header__dot" aria-hidden="true">·</span>
                <p class="timelog-report-header__subtitle mb-0">Monitor, track, and analyze employee working hours and project time logs across the organisation.</p>
            </div>
        </div>

        <div class="timelog-sync-pill" aria-label="Tracking status: Live Tracking Synchronized">
            <span class="timelog-sync-pill__dot" aria-hidden="true"></span>
            <span class="timelog-sync-pill__text">Live Tracking Synchronized</span>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="card timelog-report-stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="timelog-report-stat-card__label">Total Log Entries</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="timelog-report-stat-card__value">{{ $logs->count() }}</span>
                            <span class="timelog-report-stat-card__unit">entries logged</span>
                        </div>
                    </div>
                    <div class="stat-icon timelog-report-stat-icon timelog-report-stat-icon--blue" aria-hidden="true">
                        <i class="bx bx-file"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card timelog-report-stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="timelog-report-stat-card__label">Total Working Hours Logged</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="timelog-report-stat-card__value">{{ number_format($totalHours, 2) }}</span>
                            <span class="timelog-report-stat-card__unit">hrs</span>
                        </div>
                    </div>
                    <div class="stat-icon timelog-report-stat-icon timelog-report-stat-icon--green" aria-hidden="true">
                        <i class="bx bx-time-five"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12">
            <div class="card timelog-report-stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="timelog-report-stat-card__label">Active Projects Logged</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="timelog-report-stat-card__value">{{ $logs->pluck('project_id')->unique()->count() }}</span>
                            <span class="timelog-report-stat-card__unit">active projects</span>
                        </div>
                    </div>
                    <div class="stat-icon timelog-report-stat-icon timelog-report-stat-icon--amber" aria-hidden="true">
                        <i class="bx bx-folder"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card timelog-report-card timelog-report-filter-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.timelog') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-6">
                        <label for="timelogEmployeeFilter" class="form-label timelog-report-filter-label">Filter by Employee</label>
                        <select id="timelogEmployeeFilter" name="user_id" class="form-select timelog-report-select">
                            <option value="">All Employees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <label for="timelogProjectFilter" class="form-label timelog-report-filter-label">Filter by Project</label>
                        <select id="timelogProjectFilter" name="project_id" class="form-select timelog-report-select">
                            <option value="">All Projects</option>
                            @foreach($projects as $proj)
                                <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                                    {{ $proj->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-12 d-flex flex-wrap align-items-center gap-2">
                        <button type="submit" class="btn btn-primary timelog-report-btn-filter text-nowrap">
                            <i class="bx bx-filter-alt me-1" aria-hidden="true"></i> Filter Report
                        </button>
                        <a href="{{ route('reports.timelog') }}" class="btn timelog-report-btn-reset text-nowrap">
                            <i class="bx bx-refresh me-1" aria-hidden="true"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Table Card -->
    <div class="card timelog-report-card timelog-report-table-card">
        <div class="card-body">
            <div id="timelogToolbarHost" class="timelog-toolbar-host" data-live-preserve></div>
            <div class="table-responsive" tabindex="0" role="region" aria-labelledby="timelog-report-heading">
                <table id="reportTable" data-pms-toolbar-host="timelogToolbarHost" class="table table-hover align-middle w-100 timelog-report-table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Employee</th>
                            <th scope="col">Project</th>
                            <th scope="col">Task &amp; Summary</th>
                            <th scope="col">Start Time</th>
                            <th scope="col">End Time</th>
                            <th scope="col">Logged Hours</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $key => $log)
                            <tr>
                                <td class="fw-bold">#{{ $key + 1 }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $log->employee->name ?? 'Employee' }}</div>
                                </td>
                                <td>{{ $log->project->name ?? 'General Project' }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $log->task->title ?? 'Work Log' }}</div>
                                    @if($log->memo)<small class="text-muted d-block">{{ Str::limit($log->memo, 35) }}</small>@endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($log->start_date ?? $log->start_time)->format('d M Y') }} {{ \Carbon\Carbon::parse($log->start_time)->format('h:i A') }}</td>
                                <td>{{ \Carbon\Carbon::parse($log->end_date ?? $log->end_time)->format('d M Y') }} {{ \Carbon\Carbon::parse($log->end_time)->format('h:i A') }}</td>
                                <td>
                                    @php $rawHours = is_numeric($log->total_hours) ? abs((float)$log->total_hours) : 0; @endphp
                                    <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1">{{ number_format($rawHours, 2) }} hrs</span>
                                </td>
                                <td>
                                    <span class="badge bg-success rounded-pill px-3 py-1">{{ ucfirst($log->status ?? 'Approved') }}</span>
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

@push('styles')
<style>
    .timelog-report-page {
        accent-color: var(--pms-primary, #2F6BFF);
    }

    .timelog-report-header__left {
        min-width: 0;
        flex: 1 1 320px;
    }

    .timelog-report-header__icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #EFF6FF;
        border: 1px solid #DBEAFE;
        color: #2F6BFF;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }

    .timelog-report-header__title {
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--pms-text, #0F172A);
    }

    .timelog-report-breadcrumb {
        font-size: 0.78rem;
        font-weight: 500;
    }

    .timelog-report-breadcrumb .breadcrumb-item a {
        color: var(--pms-muted, #64748B);
    }

    .timelog-report-breadcrumb .breadcrumb-item.active {
        color: var(--pms-text, #0F172A);
        font-weight: 600;
    }

    .timelog-report-header__dot {
        color: var(--pms-muted, #94A3B8);
        font-weight: 700;
    }

    .timelog-report-header__subtitle {
        font-size: 0.82rem;
        color: var(--pms-muted, #64748B);
    }

    .timelog-sync-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.9rem;
        border-radius: 10px;
        background: var(--pms-surface, #FFFFFF);
        border: 1px solid var(--pms-border, #E2E8F0);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        flex-shrink: 0;
    }

    .timelog-sync-pill__dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10B981;
        flex-shrink: 0;
    }

    .timelog-sync-pill__text {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--pms-text, #334155);
        white-space: nowrap;
    }

    .timelog-report-stat-card,
    .timelog-report-card {
        background: var(--pms-surface, #FFFFFF);
        border: 1px solid var(--pms-border, #E2E8F0) !important;
        border-radius: 16px !important;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03) !important;
    }

    .timelog-report-stat-card .card-body {
        padding: 1.25rem 1.35rem;
    }

    .timelog-report-stat-card__label {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--pms-muted, #64748B);
        margin-bottom: 0.55rem;
    }

    .timelog-report-stat-card__value {
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.1;
        color: var(--pms-text, #0F172A);
    }

    .timelog-report-stat-card__unit {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--pms-muted, #64748B);
    }

    .timelog-report-stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .timelog-report-stat-icon--blue {
        background: #EFF6FF;
        border: 1px solid #DBEAFE;
        color: #2F6BFF;
    }

    .timelog-report-stat-icon--green {
        background: #ECFDF5;
        border: 1px solid #D1FAE5;
        color: #10B981;
    }

    .timelog-report-stat-icon--amber {
        background: #FFFBEB;
        border: 1px solid #FEF3C7;
        color: #D97706;
    }

    .timelog-report-filter-card .card-body {
        padding: 1.2rem 1.4rem;
    }

    .timelog-report-filter-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--pms-text, #0F172A);
        margin-bottom: 0.45rem;
    }

    .timelog-report-select {
        height: 42px;
        border-radius: 10px !important;
        border: 1px solid var(--pms-border, #E2E8F0) !important;
        font-size: 0.86rem;
        font-weight: 500;
        padding-left: 0.9rem;
    }

    .timelog-report-btn-filter {
        height: 42px;
        border-radius: 10px !important;
        padding: 0 1.35rem !important;
        font-size: 0.86rem !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 1 1 auto;
    }

    .timelog-report-btn-reset {
        height: 42px;
        border-radius: 10px !important;
        padding: 0 1.25rem !important;
        font-size: 0.86rem !important;
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

    .timelog-report-btn-reset:hover,
    .timelog-report-btn-reset:focus-visible {
        background: var(--pms-surface-soft, #F8FAFC) !important;
        border-color: #CBD5E1 !important;
        color: var(--pms-text, #0F172A) !important;
    }

    .timelog-report-table-card .card-body {
        padding: 0;
    }

    .timelog-toolbar-host {
        width: 100%;
    }

    .timelog-report-table-card .pms-table-tools {
        background: var(--pms-surface, #FFFFFF);
        border: 0;
        border-bottom: 1px solid var(--pms-border, #E2E8F0);
        border-radius: 16px 16px 0 0;
        min-height: 62px;
        padding: 0.85rem 1.35rem;
        flex-wrap: wrap;
        gap: 0.75rem;
        justify-content: flex-start;
    }

    .timelog-report-table-card .pms-table-tools__status {
        background: var(--pms-surface-soft, #F1F5F9);
        color: var(--pms-text, #334155);
        font-size: 0.78rem;
        font-weight: 700;
        padding: 0.3rem 0.65rem;
        border-radius: 999px;
        margin-right: 0;
        white-space: nowrap;
    }

    .timelog-toolbar__subtitle {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--pms-muted, #64748B);
        margin-right: auto;
    }

    .timelog-report-table-card .dataTables_filter {
        margin: 0 !important;
        padding: 0 !important;
        max-width: 100%;
    }

    .timelog-report-table-card .dataTables_filter label {
        position: relative;
        display: inline-flex;
        align-items: center;
        margin: 0;
    }

    .timelog-search-icon {
        position: absolute;
        left: 0.75rem;
        font-size: 0.95rem;
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
        pointer-events: none;
    }

    .timelog-report-table-card .dataTables_filter input {
        border-radius: 10px !important;
        padding: 0.42rem 0.85rem 0.42rem 2.15rem !important;
        border: 1px solid var(--pms-border, #E2E8F0) !important;
        background: var(--pms-surface-soft, #F8FAFC) !important;
        font-size: 0.82rem;
        height: 36px;
        min-width: 210px;
        margin-left: 0 !important;
    }

    .timelog-report-table-card .pms-table-export {
        margin-left: 0;
    }

    .timelog-report-table-card .pms-table-export__toggle {
        background: var(--pms-surface, #FFFFFF);
        border: 1px solid var(--pms-border, #E2E8F0);
        color: var(--pms-text, #334155);
        font-weight: 600;
        font-size: 0.82rem;
        min-height: 36px;
        padding: 0.38rem 0.85rem;
        border-radius: 10px;
    }

    .timelog-report-table-card .pms-table-export__toggle:hover,
    .timelog-report-table-card .pms-table-export__toggle:focus-visible {
        background: var(--pms-surface-soft, #F1F5F9);
        border-color: #CBD5E1;
        color: var(--pms-text, #0F172A);
        box-shadow: none;
        transform: none;
    }

    .timelog-report-table-card .pms-table-scroll-host {
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        overflow-x: auto !important;
    }

    .timelog-report-table {
        margin-bottom: 0 !important;
        border-collapse: separate;
        border-spacing: 0;
    }

    .timelog-report-table thead th {
        background: #F8FAFC !important;
        color: #64748B !important;
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        border-bottom: 1px solid #E2E8F0 !important;
        padding: 0.85rem 1rem !important;
        white-space: nowrap;
    }

    .timelog-report-table td.dataTables_empty {
        padding: 0 !important;
        border-bottom: 0 !important;
        background: transparent !important;
    }

    .timelog-report-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 4.5rem 1.5rem;
    }

    .timelog-report-empty__icon {
        position: relative;
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: #F1F5F9;
        color: #64748B;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .content-wrapper .timelog-report-empty__icon i.bx-time-five,
    .timelog-report-empty__icon i.bx-time-five {
        color: #64748B !important;
        -webkit-text-fill-color: #64748B !important;
    }

    .timelog-report-empty__badge {
        position: absolute;
        right: -4px;
        bottom: -4px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #EFF6FF;
        border: 2px solid #FFFFFF;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.68rem;
        color: #2F6BFF !important;
    }

    .timelog-report-empty__title {
        font-size: 0.98rem;
        font-weight: 700;
        color: var(--pms-text, #0F172A);
        margin-bottom: 0.4rem;
    }

    .timelog-report-empty__text {
        max-width: 340px;
        font-size: 0.8rem;
        line-height: 1.55;
        color: var(--pms-muted, #64748B);
    }

    .timelog-report-empty__btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        padding: 0.45rem 0.95rem;
        border-radius: 8px;
        background: var(--pms-surface, #FFFFFF);
        border: 1px solid var(--pms-border, #E2E8F0);
        color: var(--pms-text, #334155) !important;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }

    .timelog-report-empty__btn:hover,
    .timelog-report-empty__btn:focus-visible {
        background: var(--pms-surface-soft, #F8FAFC);
        border-color: #CBD5E1;
        color: var(--pms-text, #0F172A) !important;
    }

    .timelog-dt-footer {
        padding: 0.85rem 1.35rem;
        border-top: 1px solid var(--pms-border, #E2E8F0);
    }

    .timelog-report-table-card .dataTables_info {
        font-size: 0.78rem !important;
        color: var(--pms-muted, #64748B) !important;
        padding: 0 !important;
    }

    .timelog-report-table-card .dataTables_paginate {
        padding: 0 !important;
    }

    .timelog-report-table-card .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        font-size: 0.78rem !important;
    }

    html[data-pms-theme="dark"] .timelog-report-header__icon,
    html[data-pms-theme="dark"] .timelog-report-stat-icon--blue {
        background: rgba(47, 107, 255, 0.16);
        border-color: rgba(47, 107, 255, 0.3);
        color: #60A5FA;
    }

    html[data-pms-theme="dark"] .timelog-report-stat-icon--green {
        background: rgba(16, 185, 129, 0.16);
        border-color: rgba(16, 185, 129, 0.3);
        color: #34D399;
    }

    html[data-pms-theme="dark"] .timelog-report-stat-icon--amber {
        background: rgba(245, 158, 11, 0.16);
        border-color: rgba(245, 158, 11, 0.3);
        color: #FBBF24;
    }

    html[data-pms-theme="dark"] .timelog-report-table-card .pms-table-tools {
        background: #0F1530 !important;
        border-bottom-color: rgba(238, 241, 251, 0.1) !important;
    }

    html[data-pms-theme="dark"] .timelog-report-table-card .pms-table-tools__status {
        background: #141B3D !important;
    }

    html[data-pms-theme="dark"] .timelog-report-table thead th {
        background: #141B3D !important;
        color: #CBD5E1 !important;
        border-bottom-color: rgba(238, 241, 251, 0.1) !important;
    }

    html[data-pms-theme="dark"] .timelog-report-empty__icon {
        background: rgba(148, 163, 184, 0.14);
    }

    html[data-pms-theme="dark"] .content-wrapper .timelog-report-empty__icon i.bx-time-five,
    html[data-pms-theme="dark"] .timelog-report-empty__icon i.bx-time-five {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    html[data-pms-theme="dark"] .timelog-report-empty__badge {
        background: #1E293B;
        border-color: #0F1530;
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
            '<div class="timelog-report-empty">' +
                '<div class="stat-icon timelog-report-empty__icon" aria-hidden="true">' +
                    '<i class="bx bx-time-five"></i>' +
                    '<span class="timelog-report-empty__badge"><i class="bx bx-search"></i></span>' +
                '</div>' +
                '<div class="timelog-report-empty__title">No time logs found</div>' +
                '<p class="timelog-report-empty__text mb-3">Try adjusting your employee or project filters above, or clear filters to view logged hours across all teams.</p>' +
                '<a href="{{ route('reports.timelog') }}" class="timelog-report-empty__btn"><i class="bx bx-refresh" aria-hidden="true"></i> Reset Filters</a>' +
            '</div>';

        var table = $('#reportTable').DataTable({
            dom: '<"d-none"B><"timelog-dt-search"f>rt<"d-flex flex-wrap align-items-center justify-content-between gap-3 timelog-dt-footer"ip>',
            buttons: [
                { extend: 'copy', className: 'btn', text: '<i class="bx bx-copy me-1"></i> Copy' },
                { extend: 'csv', className: 'btn', text: '<i class="bx bx-file me-1"></i> CSV' },
                { extend: 'excel', className: 'btn', text: '<i class="bx bx-spreadsheet me-1"></i> Excel' },
                { extend: 'pdf', className: 'btn', text: '<i class="bx bxs-file-pdf me-1"></i> PDF' },
                { extend: 'print', className: 'btn', text: '<i class="bx bx-printer me-1"></i> Print' }
            ],
            language: {
                search: '',
                searchPlaceholder: 'Search within logs...',
                emptyTable: emptyStateHtml,
                zeroRecords: emptyStateHtml
            },
            pageLength: 25
        });

        function mountTimelogToolbar() {
            var toolbar = document.querySelector('#timelogToolbarHost .pms-table-tools');
            var filterBox = document.getElementById('reportTable_filter');
            if (!toolbar) return;

            var status = toolbar.querySelector('.pms-table-tools__status');
            if (status && !toolbar.querySelector('.timelog-toolbar__subtitle')) {
                var subtitle = document.createElement('span');
                subtitle.className = 'timelog-toolbar__subtitle';
                subtitle.textContent = 'Showing all time entries';
                status.insertAdjacentElement('afterend', subtitle);
            }

            if (filterBox) {
                var label = filterBox.querySelector('label');
                if (label && !label.querySelector('.timelog-search-icon')) {
                    var icon = document.createElement('i');
                    icon.className = 'bx bx-search timelog-search-icon';
                    icon.setAttribute('aria-hidden', 'true');
                    label.prepend(icon);
                }
                var exportBox = toolbar.querySelector('.pms-table-export');
                if (exportBox && filterBox.parentNode !== toolbar) {
                    toolbar.insertBefore(filterBox, exportBox);
                }
            }
        }

        window.requestAnimationFrame(mountTimelogToolbar);
        table.on('draw.dt', mountTimelogToolbar);

        var toolbarHost = document.getElementById('timelogToolbarHost');
        if (toolbarHost && window.MutationObserver) {
            new MutationObserver(mountTimelogToolbar).observe(toolbarHost, { childList: true });
        }
    });
</script>
@endpush
