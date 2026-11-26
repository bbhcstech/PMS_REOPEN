@extends('admin.layout.app')

@section('title', 'Attendance Report')

@section('content')
<div class="container-fluid px-4 pt-1 pb-4 attendance-report-page">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4 attendance-report-header">
        <div class="attendance-report-header__left">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="header-icon-badge attendance-report-header__icon" aria-hidden="true">
                    <i class="bx bx-calendar-check"></i>
                </div>
                <h3 id="attendance-report-heading" class="attendance-report-header__title mb-0">Attendance Report</h3>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2 attendance-report-header__meta">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb attendance-report-breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Attendance Report</li>
                    </ol>
                </nav>
                <span class="attendance-report-header__dot" aria-hidden="true">·</span>
                <p class="attendance-report-header__subtitle mb-0">View, track, and analyze monthly employee attendance records across the organisation.</p>
            </div>
        </div>

        <div class="attendance-sync-pill" aria-label="Attendance status: Attendance Records Synchronized">
            <span class="attendance-sync-pill__dot" aria-hidden="true"></span>
            <span class="attendance-sync-pill__text">Attendance Records Synchronized</span>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card attendance-report-card attendance-report-filter-card mb-4">
        <div class="card-body">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-6">
                        <label for="user_id" class="form-label attendance-report-filter-label">Employee</label>
                        <select name="user_id" id="user_id" class="form-select attendance-report-select">
                            <option value="">-- Select Employee --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label for="month" class="form-label attendance-report-filter-label">Month</label>
                        <select name="month" id="month" class="form-select attendance-report-select">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(null, $m)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="year" class="form-label attendance-report-filter-label">Year</label>
                        <select name="year" id="year" class="form-select attendance-report-select">
                            @foreach(range(date('Y') - 2, date('Y') + 1) as $y)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 d-flex flex-wrap align-items-center gap-2">
                        <button type="submit" class="btn btn-primary attendance-report-btn-filter text-nowrap">
                            <i class="bx bx-filter-alt me-1" aria-hidden="true"></i> Filter
                        </button>
                        <a href="{{ route('attendance.report') }}" class="btn attendance-report-btn-reset text-nowrap">
                            <i class="bx bx-refresh me-1" aria-hidden="true"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selectedUser)
        <!-- Summary Header + Stat Cards -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="attendance-summary-title">Summary for <strong>{{ $selectedUser->name }}</strong></span>
                <span class="attendance-period-badge">{{ \Carbon\Carbon::createFromDate(null, $month)->format('F Y') }}</span>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <form method="GET" action="{{ route('attendance.export.excel') }}" class="d-inline">
                    <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <button type="submit" class="btn attendance-export-btn attendance-export-btn--excel">
                        <i class="bx bx-spreadsheet me-1" aria-hidden="true"></i> Export Excel
                    </button>
                </form>

                <form method="GET" action="{{ route('attendance.export.pdf') }}" class="d-inline">
                    <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <button type="submit" class="btn attendance-export-btn attendance-export-btn--pdf">
                        <i class="bx bxs-file-pdf me-1" aria-hidden="true"></i> Export PDF
                    </button>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card attendance-report-stat-card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between gap-2">
                        <div>
                            <div class="attendance-report-stat-card__label">Present</div>
                            <div class="attendance-report-stat-card__value">{{ $summary['present'] }}</div>
                        </div>
                        <div class="stat-icon attendance-report-stat-icon attendance-report-stat-icon--green" aria-hidden="true">
                            <i class="bx bx-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card attendance-report-stat-card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between gap-2">
                        <div>
                            <div class="attendance-report-stat-card__label">Late</div>
                            <div class="attendance-report-stat-card__value">{{ $summary['late'] }}</div>
                        </div>
                        <div class="stat-icon attendance-report-stat-icon attendance-report-stat-icon--amber" aria-hidden="true">
                            <i class="bx bx-time-five"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card attendance-report-stat-card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between gap-2">
                        <div>
                            <div class="attendance-report-stat-card__label">Absent</div>
                            <div class="attendance-report-stat-card__value">{{ $summary['absent'] }}</div>
                        </div>
                        <div class="stat-icon attendance-report-stat-icon attendance-report-stat-icon--red" aria-hidden="true">
                            <i class="bx bx-x-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card attendance-report-stat-card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between gap-2">
                        <div>
                            <div class="attendance-report-stat-card__label">Leave</div>
                            <div class="attendance-report-stat-card__value">{{ $summary['leave'] }}</div>
                        </div>
                        <div class="stat-icon attendance-report-stat-icon attendance-report-stat-icon--cyan" aria-hidden="true">
                            <i class="bx bx-calendar-minus"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card attendance-report-stat-card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between gap-2">
                        <div>
                            <div class="attendance-report-stat-card__label">Half Day</div>
                            <div class="attendance-report-stat-card__value">{{ $summary['half_day'] ?? 0 }}</div>
                        </div>
                        <div class="stat-icon attendance-report-stat-icon attendance-report-stat-icon--purple" aria-hidden="true">
                            <i class="bx bx-hourglass"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card attendance-report-stat-card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between gap-2">
                        <div>
                            <div class="attendance-report-stat-card__label">Holiday</div>
                            <div class="attendance-report-stat-card__value">{{ $summary['holiday'] ?? 0 }}</div>
                        </div>
                        <div class="stat-icon attendance-report-stat-icon attendance-report-stat-icon--blue" aria-hidden="true">
                            <i class="bx bx-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Grid Table Card -->
        <div class="card attendance-report-card attendance-report-table-card">
            <div class="table-header attendance-grid-header">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 w-100">
                    <div class="d-flex align-items-center gap-2">
                        <span class="attendance-grid-header__title">Attendance Grid</span>
                        <span class="attendance-grid-header__employee">— {{ $selectedUser->name }}</span>
                    </div>
                    <div class="attendance-grid-legend">
                        ✔️ Present · ⚠️ Late · ❌ Absent · 🛫 Leave · ⏳ Half Day · ⭐ Holiday
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive" tabindex="0" role="region" aria-labelledby="attendance-report-heading">
                    <table class="attendance-grid">
                        <thead>
                            <tr>
                                @for($i = 1; $i <= $daysInMonth; $i++)
                                    @php
                                        $date = \Carbon\Carbon::create($year, $month, $i);
                                    @endphp
                                    <th scope="col">
                                        <span class="day-number">{{ $i }}</span>
                                        <span class="day-name">{{ $date->format('D') }}</span>
                                    </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @for($i = 1; $i <= $daysInMonth; $i++)
                                    @php
                                        $record = $attendances[$selectedUser->id][$i] ?? null;
                                        $status = $record->status ?? null;
                                        $symbol = match($status) {
                                            'present' => '✔️',
                                            'wfh' => '🏠',
                                            'absent' => '❌',
                                            'holiday' => '⭐',
                                            'late' => '⚠️',
                                            'half_day' => '⏳',
                                            'leave' => '🛫',
                                            'day_off', 'dayoff' => '📅',
                                            default => '—'
                                        };
                                        $class = $status ? str_replace('_', '-', $status) : 'default';
                                    @endphp
                                    <td class="{{ $class }}">
                                        {{ $symbol }}
                                        @if($status)
                                            <span class="status-tooltip">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="card attendance-report-card attendance-report-table-card">
            <div class="card-body">
                <div class="attendance-report-empty">
                    <div class="stat-icon attendance-report-empty__icon" aria-hidden="true">
                        <i class="bx bx-calendar-check"></i>
                        <span class="attendance-report-empty__badge"><i class="bx bx-search"></i></span>
                    </div>
                    <div class="attendance-report-empty__title">Please Select an Employee</div>
                    <p class="attendance-report-empty__text mb-0">Choose an employee from the dropdown above to view their monthly attendance report.</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .attendance-report-page {
        accent-color: var(--pms-primary, #2F6BFF);
    }

    .attendance-report-header__left {
        min-width: 0;
        flex: 1 1 320px;
    }

    .attendance-report-header__icon {
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

    .attendance-report-header__title {
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--pms-text, #0F172A);
    }

    .attendance-report-breadcrumb {
        font-size: 0.78rem;
        font-weight: 500;
    }

    .attendance-report-breadcrumb .breadcrumb-item a {
        color: var(--pms-muted, #64748B);
    }

    .attendance-report-breadcrumb .breadcrumb-item.active {
        color: var(--pms-text, #0F172A);
        font-weight: 600;
    }

    .attendance-report-header__dot {
        color: var(--pms-muted, #94A3B8);
        font-weight: 700;
    }

    .attendance-report-header__subtitle {
        font-size: 0.82rem;
        color: var(--pms-muted, #64748B);
    }

    .attendance-sync-pill {
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

    .attendance-sync-pill__dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10B981;
        flex-shrink: 0;
    }

    .attendance-sync-pill__text {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--pms-text, #334155);
        white-space: nowrap;
    }

    .attendance-report-stat-card,
    .attendance-report-card {
        background: var(--pms-surface, #FFFFFF);
        border: 1px solid var(--pms-border, #E2E8F0) !important;
        border-radius: 16px !important;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03) !important;
    }

    .attendance-report-filter-card .card-body {
        padding: 1.2rem 1.4rem;
    }

    .attendance-report-filter-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--pms-text, #0F172A);
        margin-bottom: 0.45rem;
    }

    .attendance-report-select {
        height: 42px;
        border-radius: 10px !important;
        border: 1px solid var(--pms-border, #E2E8F0) !important;
        font-size: 0.86rem;
        font-weight: 500;
        padding-left: 0.9rem;
    }

    .attendance-report-btn-filter {
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

    .attendance-report-btn-reset {
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

    .attendance-report-btn-reset:hover,
    .attendance-report-btn-reset:focus-visible {
        background: var(--pms-surface-soft, #F8FAFC) !important;
        border-color: #CBD5E1 !important;
        color: var(--pms-text, #0F172A) !important;
    }

    .attendance-summary-title {
        font-size: 0.92rem;
        color: var(--pms-muted, #64748B);
    }

    .attendance-summary-title strong {
        color: var(--pms-text, #0F172A);
    }

    .attendance-period-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.28rem 0.65rem;
        border-radius: 999px;
        background: var(--pms-surface-soft, #F1F5F9);
        border: 1px solid var(--pms-border, #E2E8F0);
        color: var(--pms-muted, #64748B);
    }

    .attendance-export-btn {
        height: 36px;
        border-radius: 10px !important;
        padding: 0 0.9rem !important;
        font-size: 0.8rem !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--pms-border, #E2E8F0) !important;
        background: var(--pms-surface, #FFFFFF) !important;
        color: var(--pms-text, #334155) !important;
    }

    .attendance-export-btn--excel:hover {
        background: #ECFDF5 !important;
        border-color: #A7F3D0 !important;
        color: #047857 !important;
    }

    .attendance-export-btn--pdf:hover {
        background: #FEF2F2 !important;
        border-color: #FECACA !important;
        color: #B91C1C !important;
    }

    .attendance-report-stat-card .card-body {
        padding: 1.05rem 1.15rem;
    }

    .attendance-report-stat-card__label {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--pms-muted, #64748B);
        margin-bottom: 0.35rem;
    }

    .attendance-report-stat-card__value {
        font-size: 1.55rem;
        font-weight: 800;
        line-height: 1.1;
        color: var(--pms-text, #0F172A);
    }

    .attendance-report-stat-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .attendance-report-stat-icon--blue {
        background: #EFF6FF;
        border: 1px solid #DBEAFE;
        color: #2F6BFF;
    }

    .attendance-report-stat-icon--green {
        background: #ECFDF5;
        border: 1px solid #D1FAE5;
        color: #10B981;
    }

    .attendance-report-stat-icon--amber {
        background: #FFFBEB;
        border: 1px solid #FEF3C7;
        color: #D97706;
    }

    .attendance-report-stat-icon--red {
        background: #FEF2F2;
        border: 1px solid #FECACA;
        color: #EF4444;
    }

    .attendance-report-stat-icon--cyan {
        background: #ECFEFF;
        border: 1px solid #CFFAFE;
        color: #0891B2;
    }

    .attendance-report-stat-icon--purple {
        background: #F5F3FF;
        border: 1px solid #EDE9FE;
        color: #7C3AED;
    }

    .attendance-grid-header {
        padding: 1rem 1.35rem;
        border-bottom: 1px solid var(--pms-border, #E2E8F0);
        background: var(--pms-surface, #FFFFFF);
        border-radius: 16px 16px 0 0;
    }

    .attendance-grid-header__title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--pms-text, #0F172A);
    }

    .attendance-grid-header__employee {
        font-size: 0.86rem;
        font-weight: 600;
        color: var(--pms-primary, #2F6BFF);
    }

    .attendance-grid-legend {
        font-size: 0.74rem;
        color: var(--pms-muted, #64748B);
    }

    .attendance-report-table-card .table-responsive {
        padding: 1.15rem 1.35rem 1.35rem;
        overflow-x: auto;
    }

    .attendance-report-table-card .pms-table-scroll-host {
        border: 0 !important;
        border-radius: 0 0 16px 16px !important;
        box-shadow: none !important;
    }

    .attendance-grid {
        width: 100%;
        border-collapse: separate;
        border-spacing: 3px;
        min-width: 720px;
    }

    .attendance-grid th {
        padding: 0.55rem 0.35rem;
        background: #F8FAFC;
        border-radius: 8px;
        font-size: 0.68rem;
        font-weight: 700;
        color: #64748B;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border: 1px solid #E2E8F0;
    }

    .attendance-grid th .day-number {
        font-size: 0.88rem;
        font-weight: 800;
        color: var(--pms-text, #0F172A);
        display: block;
    }

    .attendance-grid th .day-name {
        font-size: 0.6rem;
        color: #94A3B8;
        font-weight: 600;
    }

    .attendance-grid td {
        padding: 0.6rem 0.35rem;
        text-align: center;
        font-size: 1.15rem;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        background: #FFFFFF;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        min-width: 38px;
        position: relative;
    }

    .attendance-grid td:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.08);
        z-index: 2;
    }

    .attendance-grid td .status-tooltip {
        display: none;
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: #0F172A;
        color: #FFFFFF;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.64rem;
        font-weight: 600;
        white-space: nowrap;
        margin-bottom: 4px;
        z-index: 10;
    }

    .attendance-grid td:hover .status-tooltip {
        display: block;
    }

    .attendance-grid td.present { background: #E0E7FF; border-color: #A5B4FC; }
    .attendance-grid td.wfh { background: #ECFDF5; border-color: #A7F3D0; }
    .attendance-grid td.absent { background: #FEE2E2; border-color: #FCA5A5; }
    .attendance-grid td.late { background: #FEF3C7; border-color: #FCD34D; }
    .attendance-grid td.half-day,
    .attendance-grid td.half_day { background: #EDE9FE; border-color: #C4B5FD; }
    .attendance-grid td.leave { background: #CFFAFE; border-color: #67E8F9; }
    .attendance-grid td.holiday { background: #FFEDD5; border-color: #FDBA74; }
    .attendance-grid td.day-off,
    .attendance-grid td.dayoff { background: #DBEAFE; border-color: #93C5FD; }
    .attendance-grid td.default { background: #F8FAFC; border-color: #E2E8F0; }

    .attendance-report-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 4.5rem 1.5rem;
    }

    .attendance-report-empty__icon {
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

    .content-wrapper .attendance-report-empty__icon i.bx-calendar-check,
    .attendance-report-empty__icon i.bx-calendar-check {
        color: #64748B !important;
        -webkit-text-fill-color: #64748B !important;
    }

    .attendance-report-empty__badge {
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

    .attendance-report-empty__title {
        font-size: 0.98rem;
        font-weight: 700;
        color: var(--pms-text, #0F172A);
        margin-bottom: 0.4rem;
    }

    .attendance-report-empty__text {
        max-width: 360px;
        font-size: 0.8rem;
        line-height: 1.55;
        color: var(--pms-muted, #64748B);
    }

    html[data-pms-theme="dark"] .attendance-report-header__icon,
    html[data-pms-theme="dark"] .attendance-report-stat-icon--blue {
        background: rgba(47, 107, 255, 0.16);
        border-color: rgba(47, 107, 255, 0.3);
        color: #60A5FA;
    }

    html[data-pms-theme="dark"] .attendance-report-stat-icon--green {
        background: rgba(16, 185, 129, 0.16);
        border-color: rgba(16, 185, 129, 0.3);
        color: #34D399;
    }

    html[data-pms-theme="dark"] .attendance-report-stat-icon--amber {
        background: rgba(245, 158, 11, 0.16);
        border-color: rgba(245, 158, 11, 0.3);
        color: #FBBF24;
    }

    html[data-pms-theme="dark"] .attendance-report-stat-icon--red {
        background: rgba(239, 68, 68, 0.16);
        border-color: rgba(239, 68, 68, 0.3);
        color: #F87171;
    }

    html[data-pms-theme="dark"] .attendance-report-stat-icon--cyan {
        background: rgba(6, 182, 212, 0.16);
        border-color: rgba(6, 182, 212, 0.3);
        color: #22D3EE;
    }

    html[data-pms-theme="dark"] .attendance-report-stat-icon--purple {
        background: rgba(139, 92, 246, 0.16);
        border-color: rgba(139, 92, 246, 0.3);
        color: #A78BFA;
    }

    html[data-pms-theme="dark"] .attendance-grid-header {
        background: #0F1530;
        border-bottom-color: rgba(238, 241, 251, 0.1);
    }

    html[data-pms-theme="dark"] .attendance-grid th {
        background: #141B3D;
        color: #94A3B8;
        border-color: rgba(79, 131, 255, 0.15);
    }

    html[data-pms-theme="dark"] .attendance-grid th .day-number {
        color: #FFFFFF;
    }

    html[data-pms-theme="dark"] .attendance-grid td {
        background: #0F1530;
        border-color: rgba(79, 131, 255, 0.12);
    }

    html[data-pms-theme="dark"] .attendance-grid td.present { background: rgba(47, 107, 255, 0.2); border-color: rgba(79, 131, 255, 0.3); }
    html[data-pms-theme="dark"] .attendance-grid td.wfh { background: rgba(16, 185, 129, 0.2); border-color: rgba(16, 185, 129, 0.35); }
    html[data-pms-theme="dark"] .attendance-grid td.absent { background: #450A0A; border-color: #7F1D1D; }
    html[data-pms-theme="dark"] .attendance-grid td.late { background: #451A03; border-color: #78350F; }
    html[data-pms-theme="dark"] .attendance-grid td.half-day,
    html[data-pms-theme="dark"] .attendance-grid td.half_day { background: #2E1065; border-color: #4C1D95; }
    html[data-pms-theme="dark"] .attendance-grid td.leave { background: #164E63; border-color: #155E75; }
    html[data-pms-theme="dark"] .attendance-grid td.holiday { background: #431407; border-color: #7C2D12; }
    html[data-pms-theme="dark"] .attendance-grid td.day-off,
    html[data-pms-theme="dark"] .attendance-grid td.dayoff { background: #172554; border-color: #1E3A8A; }
    html[data-pms-theme="dark"] .attendance-grid td.default { background: #0F1530; border-color: rgba(79, 131, 255, 0.08); }

    html[data-pms-theme="dark"] .attendance-report-empty__icon {
        background: rgba(148, 163, 184, 0.14);
    }

    html[data-pms-theme="dark"] .content-wrapper .attendance-report-empty__icon i.bx-calendar-check,
    html[data-pms-theme="dark"] .attendance-report-empty__icon i.bx-calendar-check {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    html[data-pms-theme="dark"] .attendance-report-empty__badge {
        background: #1E293B;
        border-color: #0F1530;
    }
</style>
@endpush
