@extends('admin.layout.app')

@section('title', 'Leave Report')

@section('content')
<div class="container-fluid px-4 pt-1 pb-4 leave-report-page">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4 leave-report-header">
        <div class="leave-report-header__left">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="header-icon-badge leave-report-header__icon" aria-hidden="true">
                    <i class="bx bx-calendar-event"></i>
                </div>
                <h3 id="leave-report-heading" class="leave-report-header__title mb-0">Leave Report</h3>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2 leave-report-header__meta">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb leave-report-breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Leave Report</li>
                    </ol>
                </nav>
                <span class="leave-report-header__dot" aria-hidden="true">·</span>
                <p class="leave-report-header__subtitle mb-0">Monitor, review, and track employee leave requests and approval statuses across the organisation.</p>
            </div>
        </div>

        <div class="leave-sync-pill" aria-label="Leave report status: Live Records Synchronized">
            <span class="leave-sync-pill__dot" aria-hidden="true"></span>
            <span class="leave-sync-pill__text">Leave Records Synchronized</span>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4" id="leaveReportSummary">
        <div class="col-xl-3 col-md-6">
            <div class="card leave-report-stat-card leave-report-summary-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="leave-report-stat-card__label">Total Leaves</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="leave-report-stat-card__value">{{ $summary['total'] }}</span>
                            <span class="leave-report-stat-card__unit">requests</span>
                        </div>
                    </div>
                    <div class="stat-icon leave-report-stat-icon leave-report-stat-icon--blue" aria-hidden="true">
                        <i class="bx bx-calendar"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card leave-report-stat-card leave-report-summary-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="leave-report-stat-card__label">Approved</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="leave-report-stat-card__value">{{ $summary['approved'] }}</span>
                            <span class="leave-report-stat-card__unit">approved</span>
                        </div>
                    </div>
                    <div class="stat-icon leave-report-stat-icon leave-report-stat-icon--green" aria-hidden="true">
                        <i class="bx bx-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card leave-report-stat-card leave-report-summary-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="leave-report-stat-card__label">Pending</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="leave-report-stat-card__value">{{ $summary['pending'] }}</span>
                            <span class="leave-report-stat-card__unit">awaiting</span>
                        </div>
                    </div>
                    <div class="stat-icon leave-report-stat-icon leave-report-stat-icon--amber" aria-hidden="true">
                        <i class="bx bx-time-five"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card leave-report-stat-card leave-report-summary-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="leave-report-stat-card__label">Rejected</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="leave-report-stat-card__value">{{ $summary['rejected'] }}</span>
                            <span class="leave-report-stat-card__unit">declined</span>
                        </div>
                    </div>
                    <div class="stat-icon leave-report-stat-icon leave-report-stat-icon--red" aria-hidden="true">
                        <i class="bx bx-x-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card leave-report-card leave-report-filter-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.leave.report') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label for="leaveReportEmployeeFilter" class="form-label leave-report-filter-label">Employee</label>
                        <select id="leaveReportEmployeeFilter" name="user_id" class="form-select leave-report-control">
                            <option value="">All Employees</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="leaveReportTypeFilter" class="form-label leave-report-filter-label">Leave Type</label>
                        <select id="leaveReportTypeFilter" name="type" class="form-select leave-report-control">
                            <option value="">All Types</option>
                            <option value="sick" {{ request('type') == 'sick' ? 'selected' : '' }}>Sick</option>
                            <option value="casual" {{ request('type') == 'casual' ? 'selected' : '' }}>Casual</option>
                            <option value="earned" {{ request('type') == 'earned' ? 'selected' : '' }}>Earned</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="leaveReportFromDate" class="form-label leave-report-filter-label">From</label>
                        <input id="leaveReportFromDate" type="date" name="from" value="{{ request('from') }}" class="form-control leave-report-control">
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="leaveReportToDate" class="form-label leave-report-filter-label">To</label>
                        <input id="leaveReportToDate" type="date" name="to" value="{{ request('to') }}" class="form-control leave-report-control">
                    </div>

                    <div class="col-lg-3 col-md-12 d-flex flex-wrap align-items-center gap-2">
                        <button type="submit" class="btn btn-primary leave-report-btn-filter text-nowrap">
                            <i class="bx bx-filter-alt me-1" aria-hidden="true"></i> Filter
                        </button>
                        <a href="{{ route('admin.leave.report') }}" class="btn leave-report-btn-reset text-nowrap">
                            <i class="bx bx-refresh me-1" aria-hidden="true"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Table Card -->
    <div class="card leave-report-card leave-report-table-card">
        <div class="card-body">
            <div id="leaveToolbarHost" class="leave-toolbar-host" data-live-preserve></div>
            <div class="table-responsive" tabindex="0" role="region" aria-labelledby="leave-report-heading">
                <table id="leavereportTable" data-pms-toolbar-host="leaveToolbarHost" class="table table-hover align-middle w-100 leave-report-table">
                    <thead>
                        <tr>
                            <th scope="col">Employee</th>
                            <th scope="col">Leave Type</th>
                            <th scope="col">From</th>
                            <th scope="col">To</th>
                            <th scope="col">Reason</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaves as $leave)
                            <tr>
                                <td><div class="fw-semibold text-dark">{{ $leave->user?->name ?? 'N/A' }}</div></td>
                                <td><span class="badge bg-light text-dark border">{{ ucfirst($leave->type) }}</span></td>
                                <td>{{ $leave->start_date }}</td>
                                <td>{{ $leave->end_date }}</td>
                                <td>{{ $leave->reason }}</td>
                                <td>
                                    <span class="badge bg-{{ $leave->status == 'approved' ? 'success' : ($leave->status == 'pending' ? 'warning text-dark' : 'danger') }} rounded-pill px-3 py-1">
                                        {{ ucfirst($leave->status) }}
                                    </span>
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
    .leave-report-page {
        accent-color: var(--pms-primary, #2F6BFF);
    }

    .leave-report-header__left {
        min-width: 0;
        flex: 1 1 320px;
    }

    .leave-report-header__icon {
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

    .leave-report-header__title {
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--pms-text, #0F172A);
    }

    .leave-report-breadcrumb {
        font-size: 0.78rem;
        font-weight: 500;
    }

    .leave-report-breadcrumb .breadcrumb-item a {
        color: var(--pms-muted, #64748B);
    }

    .leave-report-breadcrumb .breadcrumb-item.active {
        color: var(--pms-text, #0F172A);
        font-weight: 600;
    }

    .leave-report-header__dot {
        color: var(--pms-muted, #94A3B8);
        font-weight: 700;
    }

    .leave-report-header__subtitle {
        font-size: 0.82rem;
        color: var(--pms-muted, #64748B);
    }

    .leave-sync-pill {
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

    .leave-sync-pill__dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10B981;
        flex-shrink: 0;
    }

    .leave-sync-pill__text {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--pms-text, #334155);
        white-space: nowrap;
    }

    .leave-report-stat-card,
    .leave-report-card {
        background: var(--pms-surface, #FFFFFF);
        border: 1px solid var(--pms-border, #E2E8F0) !important;
        border-radius: 16px !important;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03) !important;
    }

    .leave-report-stat-card .card-body {
        padding: 1.25rem 1.35rem;
    }

    .leave-report-stat-card__label {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--pms-muted, #64748B);
        margin-bottom: 0.55rem;
    }

    .leave-report-stat-card__value {
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.1;
        color: var(--pms-text, #0F172A);
    }

    .leave-report-stat-card__unit {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--pms-muted, #64748B);
    }

    .leave-report-stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .leave-report-stat-icon--blue {
        background: #EFF6FF;
        border: 1px solid #DBEAFE;
        color: #2F6BFF;
    }

    .leave-report-stat-icon--green {
        background: #ECFDF5;
        border: 1px solid #D1FAE5;
        color: #10B981;
    }

    .leave-report-stat-icon--amber {
        background: #FFFBEB;
        border: 1px solid #FEF3C7;
        color: #D97706;
    }

    .leave-report-stat-icon--red {
        background: #FEF2F2;
        border: 1px solid #FECACA;
        color: #EF4444;
    }

    .leave-report-filter-card .card-body {
        padding: 1.2rem 1.4rem;
    }

    .leave-report-filter-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--pms-text, #0F172A);
        margin-bottom: 0.45rem;
    }

    .leave-report-control {
        height: 42px;
        border-radius: 10px !important;
        border: 1px solid var(--pms-border, #E2E8F0) !important;
        font-size: 0.86rem;
        font-weight: 500;
        padding-left: 0.9rem;
    }

    .leave-report-btn-filter {
        height: 42px;
        border-radius: 10px !important;
        padding: 0 1.25rem !important;
        font-size: 0.86rem !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 1 1 auto;
    }

    .leave-report-btn-reset {
        height: 42px;
        border-radius: 10px !important;
        padding: 0 1.1rem !important;
        font-size: 0.86rem !important;
        font-weight: 600 !important;
        background: var(--pms-surface, #FFFFFF) !important;
        border: 1px solid var(--pms-border, #E2E8F0) !important;
        color: var(--pms-text, #334155) !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 1 auto;
        min-width: 100px;
    }

    .leave-report-btn-reset:hover,
    .leave-report-btn-reset:focus-visible {
        background: var(--pms-surface-soft, #F8FAFC) !important;
        border-color: #CBD5E1 !important;
        color: var(--pms-text, #0F172A) !important;
    }

    .leave-report-table-card .card-body {
        padding: 0;
    }

    .leave-toolbar-host {
        width: 100%;
    }

    .leave-report-table-card .pms-table-tools {
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

    .leave-report-table-card .pms-table-tools__status {
        background: var(--pms-surface-soft, #F1F5F9);
        color: var(--pms-text, #334155);
        font-size: 0.78rem;
        font-weight: 700;
        padding: 0.3rem 0.65rem;
        border-radius: 999px;
        margin-right: 0;
        white-space: nowrap;
    }

    .leave-toolbar__subtitle {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--pms-muted, #64748B);
        margin-right: auto;
    }

    .leave-report-table-card .dataTables_filter {
        margin: 0 !important;
        padding: 0 !important;
        max-width: 100%;
    }

    .leave-report-table-card .dataTables_filter label {
        position: relative;
        display: inline-flex;
        align-items: center;
        margin: 0;
    }

    .leave-search-icon {
        position: absolute;
        left: 0.75rem;
        font-size: 0.95rem;
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
        pointer-events: none;
    }

    .leave-report-table-card .dataTables_filter input {
        border-radius: 10px !important;
        padding: 0.42rem 0.85rem 0.42rem 2.15rem !important;
        border: 1px solid var(--pms-border, #E2E8F0) !important;
        background: var(--pms-surface-soft, #F8FAFC) !important;
        font-size: 0.82rem;
        height: 36px;
        min-width: 210px;
        margin-left: 0 !important;
    }

    .leave-report-table-card .pms-table-export {
        margin-left: 0;
    }

    .leave-report-table-card .pms-table-export__toggle {
        background: var(--pms-surface, #FFFFFF);
        border: 1px solid var(--pms-border, #E2E8F0);
        color: var(--pms-text, #334155);
        font-weight: 600;
        font-size: 0.82rem;
        min-height: 36px;
        padding: 0.38rem 0.85rem;
        border-radius: 10px;
    }

    .leave-report-table-card .pms-table-export__toggle:hover,
    .leave-report-table-card .pms-table-export__toggle:focus-visible {
        background: var(--pms-surface-soft, #F1F5F9);
        border-color: #CBD5E1;
        color: var(--pms-text, #0F172A);
        box-shadow: none;
        transform: none;
    }

    .leave-report-table-card .pms-table-scroll-host {
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        overflow-x: auto !important;
    }

    .leave-report-table {
        margin-bottom: 0 !important;
        border-collapse: separate;
        border-spacing: 0;
    }

    .leave-report-table thead th {
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

    .leave-report-table td.dataTables_empty {
        padding: 0 !important;
        border-bottom: 0 !important;
        background: transparent !important;
    }

    .leave-report-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 4.5rem 1.5rem;
    }

    .leave-report-empty__icon {
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

    .content-wrapper .leave-report-empty__icon i.bx-calendar-x,
    .leave-report-empty__icon i.bx-calendar-x {
        color: #64748B !important;
        -webkit-text-fill-color: #64748B !important;
    }

    .leave-report-empty__badge {
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

    .leave-report-empty__title {
        font-size: 0.98rem;
        font-weight: 700;
        color: var(--pms-text, #0F172A);
        margin-bottom: 0.4rem;
    }

    .leave-report-empty__text {
        max-width: 340px;
        font-size: 0.8rem;
        line-height: 1.55;
        color: var(--pms-muted, #64748B);
    }

    .leave-report-empty__btn {
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

    .leave-report-empty__btn:hover,
    .leave-report-empty__btn:focus-visible {
        background: var(--pms-surface-soft, #F8FAFC);
        border-color: #CBD5E1;
        color: var(--pms-text, #0F172A) !important;
    }

    .leave-dt-footer {
        padding: 0.85rem 1.35rem;
        border-top: 1px solid var(--pms-border, #E2E8F0);
    }

    .leave-report-table-card .dataTables_info {
        font-size: 0.78rem !important;
        color: var(--pms-muted, #64748B) !important;
        padding: 0 !important;
    }

    .leave-report-table-card .dataTables_paginate {
        padding: 0 !important;
    }

    .leave-report-table-card .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        font-size: 0.78rem !important;
    }

    html[data-pms-theme="dark"] .leave-report-header__icon,
    html[data-pms-theme="dark"] .leave-report-stat-icon--blue {
        background: rgba(47, 107, 255, 0.16);
        border-color: rgba(47, 107, 255, 0.3);
        color: #60A5FA;
    }

    html[data-pms-theme="dark"] .leave-report-stat-icon--green {
        background: rgba(16, 185, 129, 0.16);
        border-color: rgba(16, 185, 129, 0.3);
        color: #34D399;
    }

    html[data-pms-theme="dark"] .leave-report-stat-icon--amber {
        background: rgba(245, 158, 11, 0.16);
        border-color: rgba(245, 158, 11, 0.3);
        color: #FBBF24;
    }

    html[data-pms-theme="dark"] .leave-report-stat-icon--red {
        background: rgba(239, 68, 68, 0.16);
        border-color: rgba(239, 68, 68, 0.3);
        color: #F87171;
    }

    html[data-pms-theme="dark"] .leave-report-table-card .pms-table-tools {
        background: #0F1530 !important;
        border-bottom-color: rgba(238, 241, 251, 0.1) !important;
    }

    html[data-pms-theme="dark"] .leave-report-table-card .pms-table-tools__status {
        background: #141B3D !important;
    }

    html[data-pms-theme="dark"] .leave-report-table thead th {
        background: #141B3D !important;
        color: #CBD5E1 !important;
        border-bottom-color: rgba(238, 241, 251, 0.1) !important;
    }

    html[data-pms-theme="dark"] .leave-report-empty__icon {
        background: rgba(148, 163, 184, 0.14);
    }

    html[data-pms-theme="dark"] .content-wrapper .leave-report-empty__icon i.bx-calendar-x,
    html[data-pms-theme="dark"] .leave-report-empty__icon i.bx-calendar-x {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    html[data-pms-theme="dark"] .leave-report-empty__badge {
        background: #1E293B;
        border-color: #0F1530;
    }
</style>
@endpush

@push('js')
<script>
    $(document).ready(function () {
        var emptyStateHtml =
            '<div class="leave-report-empty">' +
                '<div class="stat-icon leave-report-empty__icon" aria-hidden="true">' +
                    '<i class="bx bx-calendar-x"></i>' +
                    '<span class="leave-report-empty__badge"><i class="bx bx-search"></i></span>' +
                '</div>' +
                '<div class="leave-report-empty__title">No leave records found</div>' +
                '<p class="leave-report-empty__text mb-3">Try adjusting your employee, leave type, or date range filters above to view leave records.</p>' +
                '<a href="{{ route('admin.leave.report') }}" class="leave-report-empty__btn"><i class="bx bx-refresh" aria-hidden="true"></i> Reset Filters</a>' +
            '</div>';

        var table = $('#leavereportTable').DataTable({
            dom: '<"d-none"B><"leave-dt-search"f>rt<"d-flex flex-wrap align-items-center justify-content-between gap-3 leave-dt-footer"ip>',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                search: '',
                searchPlaceholder: 'Search leaves...',
                emptyTable: emptyStateHtml,
                zeroRecords: emptyStateHtml
            }
        });

        function mountLeaveToolbar() {
            var toolbar = document.querySelector('#leaveToolbarHost .pms-table-tools');
            var filterBox = document.getElementById('leavereportTable_filter');
            if (!toolbar) return;

            var status = toolbar.querySelector('.pms-table-tools__status');
            if (status && !toolbar.querySelector('.leave-toolbar__subtitle')) {
                var subtitle = document.createElement('span');
                subtitle.className = 'leave-toolbar__subtitle';
                subtitle.textContent = 'Showing all leave entries';
                status.insertAdjacentElement('afterend', subtitle);
            }

            if (filterBox) {
                var label = filterBox.querySelector('label');
                if (label && !label.querySelector('.leave-search-icon')) {
                    var icon = document.createElement('i');
                    icon.className = 'bx bx-search leave-search-icon';
                    icon.setAttribute('aria-hidden', 'true');
                    label.prepend(icon);
                }
                var exportBox = toolbar.querySelector('.pms-table-export');
                if (exportBox && filterBox.parentNode !== toolbar) {
                    toolbar.insertBefore(filterBox, exportBox);
                }
            }
        }

        window.requestAnimationFrame(mountLeaveToolbar);
        table.on('draw.dt', mountLeaveToolbar);

        var toolbarHost = document.getElementById('leaveToolbarHost');
        if (toolbarHost && window.MutationObserver) {
            new MutationObserver(mountLeaveToolbar).observe(toolbarHost, { childList: true });
        }
    });
</script>
@endpush
