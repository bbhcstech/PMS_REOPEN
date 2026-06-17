@extends('admin.layout.app')

@section('title', 'Attendance by Hour')

@section('content')
@php
    use Carbon\Carbon;

    $authUser = Auth::user();
    $selectedUserId = request('user_id');

    if (! function_exists('attendanceHourSecsToHms')) {
        function attendanceHourSecsToHms($seconds) {
            $seconds = max(0, (int) $seconds);
            return sprintf(
                '%02d:%02d:%02d',
                intdiv($seconds, 3600),
                intdiv($seconds % 3600, 60),
                $seconds % 60
            );
        }
    }

    if (! function_exists('attendanceHourNormalizeRecords')) {
        function attendanceHourNormalizeRecords($cell) {
            if (is_null($cell)) return [];
            if ($cell instanceof \Illuminate\Support\Collection) return $cell->all();
            if (is_array($cell)) return $cell;
            if ($cell instanceof \App\Models\Attendance) return [$cell];
            return [];
        }
    }
@endphp

<style>
    :root {
        --primary-blue: #1e3a8a;
        --primary-teal: #0ea5a4;
        --primary-green: #22c55e;
        --glass-border: rgba(255, 255, 255, 0.7);
        --card-shadow: 0px 4px 20px rgba(0, 0, 0, 0.02),
            0px 8px 40px rgba(0, 0, 0, 0.04),
            0px 20px 60px rgba(30, 58, 138, 0.06);
        --card-shadow-hover: 0px 20px 50px rgba(0, 0, 0, 0.08),
            0px 30px 80px rgba(30, 58, 138, 0.12);
        --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --spring-transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .attendance-hour-container {
        background: linear-gradient(135deg, #f0f9ff 0%, #e6f7f5 50%, #f0fdf4 100%);
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        position: relative;
        overflow: hidden;
    }

    .content-wrapper {
        position: relative;
        z-index: 10;
    }

    .header-card,
    .filter-card,
    .legend-card,
    .table-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        box-shadow: var(--card-shadow);
        transition: var(--spring-transition);
    }

    .header-card:hover,
    .filter-card:hover,
    .legend-card:hover,
    .table-card:hover {
        box-shadow: var(--card-shadow-hover);
        border-color: rgba(14, 165, 164, 0.2);
    }

    .header-card {
        border-radius: 28px;
        padding: 1.75rem 2.25rem;
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .header-title h1 {
        font-size: 2.45rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-teal), var(--primary-green));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        margin-bottom: 0.25rem;
        letter-spacing: -0.03em;
    }

    .header-title p {
        color: #64748b;
        font-size: 1.18rem;
        font-weight: 500;
        margin: 0;
    }

    .header-title p i {
        color: var(--primary-teal);
    }

    .tabs-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .nav-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 1.2rem;
        border-radius: 40px;
        font-weight: 600;
        font-size: 1.05rem;
        color: #475569;
        background: #f1f5f9;
        text-decoration: none;
        transition: var(--spring-transition);
        border: 2px solid transparent;
    }

    .nav-tab-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
        transform: translateY(-2px);
    }

    .nav-tab-btn.active {
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-teal));
        color: white;
        box-shadow: 0 4px 15px rgba(14, 165, 164, 0.25);
    }

    .filter-card,
    .legend-card {
        border-radius: 24px;
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
    }

    .filter-header,
    .legend-title {
        margin-bottom: 1rem;
    }

    .filter-header h6,
    .legend-title {
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        font-size: 1.28rem;
    }

    .filter-header h6 i,
    .legend-title i {
        color: var(--primary-teal);
        margin-right: 0.5rem;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }

    .filter-group label {
        font-size: 0.92rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .filter-group label i {
        color: var(--primary-teal);
        font-size: 0.75rem;
    }

    .filter-group .form-control {
        padding: 0.65rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        font-size: 1.08rem;
        font-weight: 500;
        color: #0f172a;
        background: white;
        transition: var(--transition-smooth);
        outline: none;
        min-height: 46px;
        width: 100%;
        appearance: none;
        -webkit-appearance: none;
        cursor: pointer;
    }

    .filter-group .form-control:focus {
        border-color: var(--primary-teal);
        box-shadow: 0 0 0 4px rgba(14, 165, 164, 0.12);
    }

    .filter-actions,
    .action-buttons {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .btn-filter,
    .btn-reset {
        color: white;
        padding: 0.65rem 1.75rem;
        border-radius: 40px;
        border: none;
        font-weight: 700;
        font-size: 1.02rem;
        cursor: pointer;
        transition: var(--spring-transition);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        min-height: 46px;
        text-decoration: none;
    }

    .btn-filter {
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-teal));
        box-shadow: 0 4px 15px rgba(14, 165, 164, 0.2);
    }

    .btn-reset {
        background: linear-gradient(135deg, #64748b, #475569);
        box-shadow: 0 4px 15px rgba(100, 116, 139, 0.2);
    }

    .btn-filter:hover,
    .btn-reset:hover {
        transform: translateY(-2px);
        color: white;
    }

    .legend-items {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.95rem;
        color: #475569;
        font-weight: 600;
    }

    .legend-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .legend-dot.present { background: linear-gradient(135deg, #34d399, #22c55e); }
    .legend-dot.absent { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .legend-dot.late { background: linear-gradient(135deg, #fb923c, #f97316); }
    .legend-dot.halfday { background: linear-gradient(135deg, #818cf8, #6366f1); }
    .legend-dot.holiday { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
    .legend-dot.leave { background: linear-gradient(135deg, #22d3ee, #06b6d4); }
    .legend-dot.wfh { background: linear-gradient(135deg, #86efac, #22c55e); }
    .legend-dot.today { background: transparent; border: 2px solid var(--primary-teal); }

    .table-card {
        border-radius: 28px;
        overflow: hidden;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 1.75rem;
        border-bottom: 1px solid #e2e8f0;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .table-header h6 {
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        font-size: 1.25rem;
    }

    .table-header h6 i {
        color: var(--primary-teal);
        margin-right: 0.5rem;
    }

    .badge-count {
        display: inline-block;
        padding: 0.15rem 0.8rem;
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-teal));
        color: white;
        border-radius: 30px;
        font-size: 0.86rem;
        font-weight: 700;
        margin-left: 0.5rem;
    }

    .attendance-hour-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.7rem;
        flex-wrap: wrap;
    }

    .selected-count-badge,
    .btn-clear-selection,
    .btn-export-menu,
    .btn-archive-link {
        min-height: 38px;
        border-radius: 999px;
        padding: 0.5rem 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 1rem;
        font-weight: 800;
        transition: var(--spring-transition);
    }

    .selected-count-badge {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .btn-clear-selection {
        border: 0;
        background: #e2e8f0;
        color: #475569;
    }

    .btn-export-menu {
        border: 1px solid rgba(16, 185, 129, 0.2);
        background: #f0f9f4;
        color: #0f744c;
        border-radius: 16px;
        padding: 0.65rem 1.25rem;
    }

    .btn-archive-link {
        background: #fff7ed;
        color: #c2410c;
        border: 1px solid rgba(249, 115, 22, 0.22);
        border-radius: 16px;
        padding: 0.65rem 1.25rem;
        text-decoration: none;
    }

    .btn-clear-selection:hover,
    .btn-export-menu:hover,
    .btn-archive-link:hover {
        transform: translateY(-2px);
    }

    .btn-archive-link:hover {
        color: #9a3412;
        background: #ffedd5;
    }

    .archive-count-badge {
        min-width: 22px;
        height: 22px;
        border-radius: 999px;
        background: #ef4444;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 0.45rem;
        font-size: 0.84rem;
        font-weight: 800;
    }

    .attendance-hour-actions .dropdown-menu {
        min-width: 230px;
        margin-top: 10px !important;
        background: #ffffff;
        border: 1px solid rgba(16, 185, 129, 0.18);
        border-radius: 16px;
        padding: 10px;
        box-shadow: 0 24px 48px -14px rgba(10, 46, 31, 0.28);
        z-index: 9999 !important;
    }

    .attendance-hour-actions .dropdown-item {
        width: 100%;
        min-height: 44px;
        border-radius: 12px;
        padding: 10px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #374151;
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.2s ease;
    }

    .attendance-hour-actions .dropdown-item:hover {
        background: #ecfdf5;
        color: #059669;
    }

    .hour-export-buttons {
        position: absolute;
        left: -9999px;
        top: auto;
        width: 1px;
        height: 1px;
        overflow: hidden;
    }

    .table-responsive {
        overflow-x: auto;
        padding: 0.5rem;
    }

    .attendance-hour-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.2rem;
        min-width: 1280px;
    }

    .attendance-hour-table thead th {
        padding: 0.7rem 0.4rem;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        color: #475569;
        font-weight: 700;
        font-size: 0.92rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        text-align: center;
        border: none;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .attendance-hour-table thead th i {
        margin-right: 4px;
        color: var(--primary-teal);
    }

    .attendance-hour-table thead th.member-select-col {
        width: 54px;
        min-width: 54px;
    }

    .attendance-hour-table thead th.employee-col {
        text-align: left;
        min-width: 255px;
        border-radius: 14px 0 0 0;
    }

    .attendance-hour-table thead th.total-col {
        min-width: 135px;
    }

    .attendance-hour-table thead th.actions-col {
        min-width: 255px;
    }

    .attendance-hour-table thead th .day-number {
        display: block;
        font-size: 1.06rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }

    .attendance-hour-table thead th .day-name {
        font-size: 0.78rem;
        color: #94a3b8;
        font-weight: 600;
    }

    .attendance-hour-table thead th.weekend {
        background: rgba(245, 158, 11, 0.06);
    }

    .attendance-hour-table thead th.today {
        border: 2px solid var(--primary-teal);
        background: rgba(14, 165, 164, 0.06);
    }

    .attendance-hour-table tbody tr:hover td {
        background: #f8fafc;
    }

    .attendance-hour-table tbody td {
        padding: 0.5rem 0.3rem;
        text-align: center;
        vertical-align: middle;
        border: none;
        border-bottom: 1px solid #e2e8f0;
        transition: all 0.2s ease;
        font-size: 1.04rem;
    }

    .attendance-hour-table tbody td.weekend {
        background: rgba(245, 158, 11, 0.03);
    }

    .attendance-hour-table tbody td.today {
        border: 1px solid rgba(14, 165, 164, 0.2);
        border-radius: 8px;
    }

    .hour-row-checkbox,
    #hourSelectAll {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--primary-teal);
    }

    .attendance-hour-table tbody tr.hour-row-selected td {
        background: #ecfdf5;
    }

    .employee-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.3rem 0.5rem;
        min-height: 58px;
        text-align: left !important;
    }

    .employee-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-teal));
        color: white;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .employee-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .employee-info {
        flex: 1;
        min-width: 0;
    }

    .employee-name {
        font-weight: 700;
        color: #0f172a;
        font-size: 1.12rem;
        white-space: nowrap;
    }

    .employee-id {
        font-size: 0.9rem;
        color: #94a3b8;
        font-weight: 500;
        white-space: nowrap;
    }

    .hour-status-badge,
    .hour-badge,
    .status-badge,
    .total-hours-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: var(--spring-transition);
    }

    .hour-status-badge {
        min-width: 116px;
        min-height: 38px;
        gap: 0.45rem;
        padding: 0.35rem 0.7rem 0.35rem 0.38rem;
        border-radius: 999px;
        font-weight: 800;
        font-size: 0.88rem;
        white-space: nowrap;
    }

    .hour-status-badge .status-symbol {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        flex-shrink: 0;
    }

    .hour-status-badge.present {
        background: #dcfce7;
        color: #065f46;
        border: 2px solid rgba(34, 197, 94, 0.2);
    }

    .hour-status-badge.present .status-symbol { background: #22c55e; }

    .hour-status-badge.late {
        background: #ffedd5;
        color: #9a3412;
        border: 2px solid rgba(249, 115, 22, 0.2);
    }

    .hour-status-badge.late .status-symbol { background: #f59e0b; }

    .hour-status-badge.halfday {
        background: #e0e7ff;
        color: #3730a3;
        border: 2px solid rgba(99, 102, 241, 0.22);
    }

    .hour-status-badge.halfday .status-symbol { background: #8b5cf6; }

    .hour-status-badge.dayoff {
        background: #dbeafe;
        color: #1d4ed8;
        border: 2px solid rgba(37, 99, 235, 0.22);
    }

    .hour-status-badge.dayoff .status-symbol { background: #3b82f6; }

    .hour-badge {
        min-width: 86px;
        min-height: 36px;
        gap: 0.35rem;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        color: #065f46;
        border: 2px solid rgba(34, 197, 94, 0.2);
        font-weight: 800;
        font-size: 0.9rem;
        white-space: nowrap;
    }

    .status-badge {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        font-size: 0.92rem;
    }

    .status-badge.present {
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        color: #065f46;
        border: 2px solid rgba(34, 197, 94, 0.2);
    }

    .status-badge.absent {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #991b1b;
        border: 2px solid rgba(239, 68, 68, 0.18);
    }

    .status-badge.holiday {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
        border: 2px solid rgba(245, 158, 11, 0.24);
    }

    .status-badge.leave {
        background: linear-gradient(135deg, #cffafe, #a5f3fc);
        color: #0e7490;
        border: 2px solid rgba(6, 182, 212, 0.22);
    }

    .status-badge.wfh {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        color: #166534;
        border: 2px solid rgba(22, 163, 74, 0.22);
    }

    .status-badge.late {
        background: linear-gradient(135deg, #ffedd5, #fed7aa);
        color: #9a3412;
        border: 2px solid rgba(249, 115, 22, 0.2);
    }

    .status-badge.halfday {
        background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
        color: #3730a3;
        border: 2px solid rgba(99, 102, 241, 0.22);
    }

    .status-badge.dayoff {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        color: #1d4ed8;
        border: 2px solid rgba(37, 99, 235, 0.22);
    }

    .status-badge.empty {
        background: #f1f5f9;
        color: #64748b;
        border: 2px solid #e2e8f0;
    }

    .hour-day-action {
        display: inline-flex;
        text-decoration: none;
    }

    .hour-day-action:hover .hour-status-badge,
    .hour-day-action:hover .hour-badge,
    .hour-day-action:hover .status-badge {
        transform: scale(1.08);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.16);
    }

    .total-hours-badge {
        gap: 0.35rem;
        padding: 0.4rem 0.7rem;
        border-radius: 999px;
        background: #f8fafc;
        color: #0f172a;
        border: 1px solid #e2e8f0;
        font-weight: 800;
        font-size: 0.98rem;
    }

    .hour-row-actions {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        min-width: 245px;
    }

    .hour-action-btn {
        min-width: 72px;
        height: 40px;
        border: 0;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.38rem;
        color: #ffffff;
        font-size: 0.96rem;
        font-weight: 800;
        transition: all 0.2s ease;
    }

    .hour-action-btn:hover {
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.16);
    }

    .hour-action-btn:disabled {
        cursor: not-allowed;
        opacity: 0.72;
        transform: none;
        box-shadow: none;
    }

    .hour-action-btn.view { background: #2563eb; }
    .hour-action-btn.edit { background: #f59e0b; }
    .hour-action-btn.archive { background: #64748b; }

    .hour-month-summary {
        background: #f8fafc;
        padding: 1.25rem;
    }

    .hour-month-profile {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .hour-month-profile img {
        width: 64px;
        height: 64px;
        border-radius: 14px;
        object-fit: cover;
    }

    .hour-month-profile h5 {
        margin: 0;
        color: #0f172a;
        font-weight: 800;
        font-size: 1.32rem;
    }

    .hour-month-profile p {
        margin: 0.2rem 0 0;
        color: #64748b;
        font-weight: 600;
        font-size: 1.05rem;
    }

    .hour-month-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.45rem;
    }

    .hour-month-table th {
        color: #64748b;
        font-size: 1rem;
        text-transform: uppercase;
        padding: 0.65rem;
    }

    .hour-month-table td {
        background: #ffffff;
        color: #1e293b;
        padding: 0.85rem;
        font-size: 1.02rem;
        border-top: 1px solid #eef2f7;
        border-bottom: 1px solid #eef2f7;
    }

    .hour-month-table td:first-child {
        border-left: 1px solid #eef2f7;
        border-radius: 12px 0 0 12px;
    }

    .hour-month-table td:last-child {
        border-right: 1px solid #eef2f7;
        border-radius: 0 12px 12px 0;
    }

    .month-edit-day-btn {
        border: 0;
        border-radius: 999px;
        padding: 0.45rem 0.9rem;
        background: #fff7ed;
        color: #c2410c;
        font-weight: 800;
        font-size: 0.98rem;
    }

    .table-card .dataTables_wrapper {
        padding: 0 0.75rem 0.75rem;
    }

    .table-card .dataTables_length,
    .table-card .dataTables_filter,
    .table-card .dataTables_info,
    .table-card .dataTables_paginate {
        color: #64748b;
        font-size: 1rem;
        font-weight: 700;
        padding: 0.75rem 0.25rem;
    }

    .table-card .dataTables_length select,
    .table-card .dataTables_filter input {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.35rem 0.75rem;
        color: #0f172a;
        background: #ffffff;
        outline: none;
    }

    .table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1.75rem;
        border-top: 1px solid #e2e8f0;
        font-size: 0.9rem;
        color: #94a3b8;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .table-footer i {
        color: var(--primary-teal);
        margin-right: 0.3rem;
    }

    #hourAttendanceModal {
        z-index: 2060 !important;
    }

    #hourAttendanceModal .modal-dialog {
        max-width: min(1180px, calc(100vw - 2rem));
    }

    #hourAttendanceModal .modal-content {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
        overflow: hidden;
    }

    #hourAttendanceModal .modal-body {
        background: #ffffff;
        max-height: calc(100vh - 11rem);
        overflow-y: auto;
    }

    #hourAttendanceModal .attendance-details-container {
        min-height: auto;
        padding: 1.25rem;
        background: #f8fafc;
        overflow: visible;
    }

    #hourAttendanceModal .attendance-details-container .ambient-orb {
        display: none;
    }

    @media (max-width: 992px) {
        .attendance-hour-container {
            padding: 1.5rem 1.25rem;
        }

        .header-card {
            padding: 1.5rem;
            flex-direction: column;
            align-items: flex-start;
        }

        .header-title h1 {
            font-size: 2rem;
        }

        .filter-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .attendance-hour-table thead th {
            font-size: 0.78rem;
            padding: 0.4rem 0.2rem;
        }

        .attendance-hour-table thead th .day-number {
            font-size: 0.9rem;
        }

        .attendance-hour-table tbody td {
            font-size: 0.9rem;
            padding: 0.4rem 0.2rem;
        }

        .employee-name {
            font-size: 0.95rem;
        }

        .status-badge {
            width: 32px;
            height: 32px;
            font-size: 0.8rem;
        }

        .hour-badge {
            min-width: 78px;
            font-size: 0.82rem;
        }

        .hour-status-badge {
            min-width: 102px;
            font-size: 0.8rem;
        }

        .hour-status-badge .status-symbol {
            width: 28px;
            height: 28px;
        }

        .nav-tab-btn {
            font-size: 0.92rem;
            padding: 0.4rem 0.8rem;
        }
    }

    @media (max-width: 768px) {
        .attendance-hour-container {
            padding: 1rem;
        }

        .filter-grid {
            grid-template-columns: 1fr;
        }

        .filter-actions,
        .action-buttons {
            flex-direction: column;
            width: 100%;
        }

        .btn-filter,
        .btn-reset {
            width: 100%;
            justify-content: center;
        }

        .table-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .table-footer {
            flex-direction: column;
            text-align: center;
        }

        .attendance-hour-table thead th {
            font-size: 0.72rem;
            padding: 0.3rem 0.15rem;
        }

        .attendance-hour-table thead th .day-number {
            font-size: 0.82rem;
        }

        .attendance-hour-table thead th .day-name {
            font-size: 0.64rem;
        }

        .attendance-hour-table tbody td {
            font-size: 0.84rem;
            padding: 0.3rem 0.15rem;
        }

        .employee-cell {
            padding: 0.2rem 0.3rem;
            gap: 0.4rem;
            min-height: 40px;
        }

        .employee-avatar {
            width: 28px;
            height: 28px;
            font-size: 0.66rem;
            border-radius: 8px;
        }

        .employee-name {
            font-size: 0.9rem;
        }

        .employee-id {
            font-size: 0.72rem;
        }

        .status-badge {
            width: 30px;
            height: 30px;
            font-size: 0.76rem;
        }

        .hour-badge {
            min-width: 72px;
            min-height: 32px;
            font-size: 0.76rem;
            padding: 0.3rem 0.5rem;
        }

        .hour-status-badge {
            min-width: 94px;
            min-height: 32px;
            font-size: 0.74rem;
            padding: 0.25rem 0.45rem 0.25rem 0.28rem;
        }

        .hour-status-badge .status-symbol {
            width: 26px;
            height: 26px;
        }

        .nav-tab-btn {
            font-size: 0.82rem;
            padding: 0.3rem 0.6rem;
            gap: 0.2rem;
        }
    }

    html[data-pms-theme="dark"] .attendance-hour-container {
        background: linear-gradient(145deg, #07130d, #102119);
    }

    html[data-pms-theme="dark"] .header-card,
    html[data-pms-theme="dark"] .filter-card,
    html[data-pms-theme="dark"] .legend-card,
    html[data-pms-theme="dark"] .table-card {
        background: rgba(16, 33, 25, 0.95);
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .header-title h1 {
        background: linear-gradient(135deg, #60a5fa, #34d399, #22c55e);
        -webkit-background-clip: text;
        background-clip: text;
    }

    html[data-pms-theme="dark"] .header-title p,
    html[data-pms-theme="dark"] .legend-item {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .filter-header h6,
    html[data-pms-theme="dark"] .legend-title,
    html[data-pms-theme="dark"] .table-header h6,
    html[data-pms-theme="dark"] .employee-name {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .filter-group label,
    html[data-pms-theme="dark"] .employee-id {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .filter-group .form-control,
    html[data-pms-theme="dark"] .table-card .dataTables_length select,
    html[data-pms-theme="dark"] .table-card .dataTables_filter input {
        background: #183026;
        border-color: rgba(122, 240, 181, 0.2);
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .nav-tab-btn {
        background: #183026;
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .nav-tab-btn.active {
        background: linear-gradient(135deg, #0f744c, #10b981);
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .attendance-hour-table thead th {
        background: linear-gradient(135deg, #183026, #102119);
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .attendance-hour-table thead th .day-number,
    html[data-pms-theme="dark"] .hour-month-profile h5,
    html[data-pms-theme="dark"] .hour-month-table td {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .attendance-hour-table tbody tr:hover td {
        background: #183026;
    }

    html[data-pms-theme="dark"] .attendance-hour-table tbody td,
    html[data-pms-theme="dark"] .table-footer {
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .hour-month-summary {
        background: #07130d;
    }

    html[data-pms-theme="dark"] .hour-month-profile,
    html[data-pms-theme="dark"] .hour-month-table td {
        background: #102119;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .hour-status-badge.present,
    html[data-pms-theme="dark"] .status-badge.present,
    html[data-pms-theme="dark"] .status-badge.wfh {
        background: #064e3b;
        color: #34d399;
        border-color: rgba(52, 211, 153, 0.2);
    }

    html[data-pms-theme="dark"] .hour-status-badge.late,
    html[data-pms-theme="dark"] .status-badge.late,
    html[data-pms-theme="dark"] .status-badge.holiday {
        background: #451a03;
        color: #fbbf24;
        border-color: rgba(251, 191, 36, 0.2);
    }

    html[data-pms-theme="dark"] .hour-status-badge.halfday,
    html[data-pms-theme="dark"] .status-badge.halfday {
        background: #2e1065;
        color: #a78bfa;
        border-color: rgba(167, 139, 250, 0.2);
    }

    html[data-pms-theme="dark"] .status-badge.absent,
    html[data-pms-theme="dark"] .status-badge.empty {
        background: #183026;
        color: #8ba198;
        border-color: rgba(122, 240, 181, 0.15);
    }
</style>

<div class="attendance-hour-container">
    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="header-card">
                <div class="header-title">
                    <h1><i class="fas fa-clock me-2"></i>Attendance by Hour</h1>
                    <p><i class="fas fa-info-circle me-1"></i>Review daily worked hours for each employee</p>
                </div>
                <div class="tabs-wrapper">
                    <a href="{{ route('attendance.index') }}" class="nav-tab-btn">
                        <i class="fas fa-list-ul"></i> Summary
                    </a>
                    <a href="{{ route('attendance.byMember') }}" class="nav-tab-btn">
                        <i class="fas fa-user"></i> By Member
                    </a>
                    <a href="{{ route('attendance.byHour') }}" class="nav-tab-btn active">
                        <i class="fas fa-clock"></i> By Hour
                    </a>
                    <a href="{{ route('attendance.today.map', ['year' => $year, 'month' => $month]) }}" class="nav-tab-btn">
                        <i class="fas fa-map-marker-alt"></i> Location
                    </a>
                </div>
            </div>

            <div class="filter-card">
                <div class="filter-header">
                    <h6><i class="fas fa-filter"></i>Filter Attendance</h6>
                </div>
                <form id="attendanceFilter" method="GET" action="{{ route('attendance.byHour') }}" class="filter-grid">
                    <div class="filter-group">
                        <label for="hour_user_id"><i class="fas fa-user"></i> Employee</label>
                        <select id="hour_user_id" name="user_id" class="form-control">
                            <option value="">All Employees</option>
                            @foreach(($employeeOptions ?? $users) as $optionUser)
                                <option value="{{ $optionUser->id }}" {{ (string) $selectedUserId === (string) $optionUser->id ? 'selected' : '' }}>
                                    {{ $optionUser->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="month"><i class="fas fa-calendar-alt"></i> Month</label>
                        <select name="month" id="month" class="form-control" required>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ Carbon::createFromDate(null, $m)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="year"><i class="fas fa-calendar"></i> Year</label>
                        <select name="year" id="year" class="form-control" required>
                            @for($y = now()->year - 3; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="filter-group filter-actions">
                        <label>&nbsp;</label>
                        <div class="action-buttons">
                            <button type="submit" class="btn-filter">
                                <i class="fas fa-search"></i> Apply
                            </button>
                            <a href="{{ route('attendance.byHour') }}" class="btn-reset">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="legend-card">
                <div class="legend-title"><i class="fas fa-key"></i>Attendance Hour Legend</div>
                <div class="legend-items">
                    <span class="legend-item"><span class="legend-dot present"></span>Present</span>
                    <span class="legend-item"><span class="legend-dot absent"></span>Absent or No Punch</span>
                    <span class="legend-item"><span class="legend-dot late"></span>Late</span>
                    <span class="legend-item"><span class="legend-dot halfday"></span>Half Day</span>
                    <span class="legend-item"><span class="legend-dot holiday"></span>Holiday or Weekend</span>
                    <span class="legend-item"><span class="legend-dot leave"></span>On Leave</span>
                    <span class="legend-item"><span class="legend-dot wfh"></span>Work From Home</span>
                    <span class="legend-item"><span class="legend-dot today"></span>Today</span>
                </div>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <h6>
                        <i class="fas fa-table"></i>Hourly Attendance Summary
                        <span class="badge-count">{{ Carbon::createFromDate(null, $month)->format('F') }} {{ $year }}</span>
                    </h6>
                    <div class="attendance-hour-actions">
                        <span class="selected-count-badge" id="hourSelectedCount">
                            <i class="fas fa-check-square"></i>0 selected
                        </span>
                        <button type="button" class="btn-clear-selection" id="hourClearSelection">
                            <i class="fas fa-times"></i>Clear
                        </button>
                        <a href="{{ route('attendance.archive') }}" class="btn-archive-link">
                            <i class="fas fa-archive"></i> Archived
                            <span class="archive-count-badge">{{ $archivedCount ?? 0 }}</span>
                        </a>
                        <div class="dropdown">
                            <button class="btn-export-menu dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cloud-download-alt"></i> Export
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><button type="button" class="dropdown-item" onclick="exportHourAttendance('copy')"><i class="fas fa-copy text-primary"></i> Copy to Clipboard</button></li>
                                <li><button type="button" class="dropdown-item" onclick="exportHourAttendance('csv')"><i class="fas fa-file-csv text-success"></i> Export as CSV</button></li>
                                <li><button type="button" class="dropdown-item" onclick="exportHourAttendance('excel')"><i class="fas fa-file-excel text-success"></i> Export as Excel</button></li>
                                <li><button type="button" class="dropdown-item" onclick="exportHourAttendance('pdf')"><i class="fas fa-file-pdf text-danger"></i> Export as PDF</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><button type="button" class="dropdown-item" onclick="exportHourAttendance('print')"><i class="fas fa-print text-info"></i> Print</button></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="attendance-hour-table" id="hourAttendanceTable">
                        <thead>
                            <tr>
                                <th class="member-select-col no-export">
                                    <input type="checkbox" id="hourSelectAll" aria-label="Select all employees">
                                </th>
                                <th class="employee-col">
                                    <i class="fas fa-user-circle"></i> Employee Name
                                </th>
                                @for($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $date = Carbon::createFromDate($year, $month, $d);
                                        $isWeekend = $date->isWeekend();
                                        $isToday = $date->isToday();
                                    @endphp
                                    <th class="day-col {{ $isWeekend ? 'weekend' : '' }} {{ $isToday ? 'today' : '' }}">
                                        <span class="day-number">{{ $d }}</span>
                                        <span class="day-name">{{ $date->format('D') }}</span>
                                    </th>
                                @endfor
                                <th class="total-col">
                                    <i class="fas fa-business-time"></i> Total Hours
                                </th>
                                <th class="actions-col no-export">
                                    <i class="fas fa-bolt"></i> Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                @php
                                    $uid = $user->id;
                                    $userMap = $attendanceMap[$uid] ?? [];
                                    $controllerDayMap = $dayTotals[$uid] ?? [];
                                    $designationName = optional(optional($user->employeeDetail)->designation)->name
                                        ?? optional(optional($user->employeeDetail)->designation)->title
                                        ?? 'Employee';
                                    $monthRecords = [];
                                    $computedRowSeconds = 0;
                                @endphp
                                <tr class="attendance-hour-row" data-user-id="{{ $uid }}">
                                    <td class="member-select-col">
                                        <input type="checkbox" class="hour-row-checkbox" value="{{ $uid }}" aria-label="Select {{ $user->name }}">
                                    </td>
                                    <td class="employee-cell">
                                        <div class="employee-avatar">
                                            <img src="{{ $user->profile_image ? asset($user->profile_image) : asset('images/default-avatar.png') }}"
                                                 alt="{{ $user->name }}"
                                                 onerror="this.onerror=null; this.src='{{ asset('admin/assets/img/avatars/1.png') }}';">
                                        </div>
                                        <div class="employee-info">
                                            <div class="employee-name">{{ $user->name }}</div>
                                            <div class="employee-id">ID: {{ $user->employee_id ?? 'N/A' }} | {{ $designationName }}</div>
                                        </div>
                                    </td>

                                    @for($d = 1; $d <= $daysInMonth; $d++)
                                        @php
                                            $date = Carbon::createFromDate($year, $month, $d);
                                            $dateKey = $date->format('Y-m-d');
                                            $cell = $userMap[$dateKey] ?? null;
                                            $records = attendanceHourNormalizeRecords($cell);
                                            $isWeekend = $date->isWeekend();
                                            $isToday = $date->isToday();
                                            $status = is_object($cell) && property_exists($cell, 'status')
                                                ? strtolower($cell->status ?? '')
                                                : '';
                                            $controllerDaySeconds = $controllerDayMap[$dateKey] ?? null;
                                            $cellSeconds = 0;
                                            $hasOpenSession = false;
                                            $hasBadSession = false;
                                            $firstRecord = $records[0] ?? null;
                                            $firstId = $firstRecord->id ?? '';
                                            $displayHtml = '';
                                            $plainDisplay = '-';
                                            $statusLabel = 'No Record';
                                            $statusTitle = 'No record on ' . $date->format('d M Y');
                                            $statusClass = 'empty';
                                            $statusIcon = 'fa-minus';
                                            $canOpenDetails = false;
                                            $canEditDay = auth()->user()->role === 'admin';

                                            if (! empty($records)) {
                                                if ($controllerDaySeconds !== null) {
                                                    $cellSeconds = (int) $controllerDaySeconds;
                                                } else {
                                                    foreach ($records as $record) {
                                                        try {
                                                            $inRaw = $record->clock_in_datetime ?? ($record->clock_in ?? null);
                                                            $outRaw = $record->clock_out_datetime ?? ($record->clock_out ?? null);
                                                            $inDt = $inRaw ? Carbon::parse($inRaw) : null;
                                                            $outDt = $outRaw ? Carbon::parse($outRaw) : null;
                                                        } catch (\Throwable $e) {
                                                            $inDt = null;
                                                            $outDt = null;
                                                            $hasBadSession = true;
                                                        }

                                                        if ($inDt && $outDt) {
                                                            if ($outDt->lessThanOrEqualTo($inDt)) {
                                                                $hasBadSession = true;
                                                                continue;
                                                            }
                                                            $cellSeconds += $outDt->diffInSeconds($inDt);
                                                        } elseif ($inDt && ! $outDt) {
                                                            $hasOpenSession = true;
                                                        } else {
                                                            $hasBadSession = true;
                                                        }
                                                    }
                                                }

                                                $computedRowSeconds += $cellSeconds;
                                                $plainDisplay = attendanceHourSecsToHms($cellSeconds);
                                                $displayHtml = $plainDisplay;
                                                if ($hasOpenSession) $displayHtml .= ' <span class="text-warning" title="Open session">*</span>';
                                                if ($hasBadSession) $displayHtml .= ' <span class="text-danger" title="Bad data">!</span>';
                                                $recordStatus = strtolower($firstRecord->status ?? 'present');
                                                if ($recordStatus === 'present' && (($firstRecord->late ?? 'no') === 'yes')) {
                                                    $recordStatus = 'late';
                                                }
                                                if ($recordStatus === 'present' && (($firstRecord->half_day ?? 'no') === 'yes')) {
                                                    $recordStatus = 'half_day';
                                                }

                                                $statusLabel = [
                                                    'present' => 'Present',
                                                    'late' => 'Late',
                                                    'half_day' => 'Half Day',
                                                    'day_off' => 'Day Off',
                                                    'dayoff' => 'Day Off',
                                                    'leave' => 'On Leave',
                                                    'holiday' => 'Holiday',
                                                ][$recordStatus] ?? 'Present';

                                                $statusClass = [
                                                    'present' => 'present',
                                                    'late' => 'late',
                                                    'half_day' => 'halfday',
                                                    'day_off' => 'dayoff',
                                                    'dayoff' => 'dayoff',
                                                    'leave' => 'leave',
                                                    'holiday' => 'holiday',
                                                ][$recordStatus] ?? 'present';

                                                $statusIcon = [
                                                    'present' => 'fa-check',
                                                    'late' => 'fa-clock',
                                                    'half_day' => 'fa-star-half-alt',
                                                    'day_off' => 'fa-calendar',
                                                    'dayoff' => 'fa-calendar',
                                                    'leave' => 'fa-plane-departure',
                                                    'holiday' => 'fa-star',
                                                ][$recordStatus] ?? 'fa-check';

                                                $statusTitle = $statusLabel . ' - ' . $plainDisplay . ' worked on ' . $date->format('d M Y');
                                                $canOpenDetails = true;
                                                $canEditDay = false;
                                            } else {
                                                $today = Carbon::now()->format('Y-m-d');

                                                if ($status === 'holiday') {
                                                    $statusLabel = 'Holiday';
                                                    $statusTitle = ($cell->occassion ?? 'Holiday') . ' on ' . $date->format('d M Y');
                                                    $statusClass = 'holiday';
                                                    $statusIcon = 'fa-star';
                                                    $canEditDay = false;
                                                } elseif ($status === 'leave') {
                                                    $statusLabel = 'On Leave';
                                                    $statusTitle = ($cell->reason ?? 'Leave') . ' on ' . $date->format('d M Y');
                                                    $statusClass = 'leave';
                                                    $statusIcon = 'fa-plane-departure';
                                                    $canEditDay = false;
                                                } elseif ($date->isSaturday()) {
                                                    $statusLabel = 'Work From Home';
                                                    $statusTitle = 'Saturday - Work From Home on ' . $date->format('d M Y');
                                                    $statusClass = 'wfh';
                                                    $statusIcon = 'fa-laptop-house';
                                                    $canEditDay = false;
                                                } elseif ($date->isSunday()) {
                                                    $statusLabel = 'Holiday';
                                                    $statusTitle = 'Sunday Holiday on ' . $date->format('d M Y');
                                                    $statusClass = 'holiday';
                                                    $statusIcon = 'fa-star';
                                                    $canEditDay = false;
                                                } elseif ($dateKey <= $today) {
                                                    $statusLabel = 'Absent';
                                                    $statusTitle = 'Absent on ' . $date->format('d M Y');
                                                    $statusClass = 'absent';
                                                    $statusIcon = 'fa-times';
                                                } else {
                                                    $statusLabel = 'Upcoming';
                                                    $statusTitle = 'Upcoming date';
                                                    $statusClass = 'empty';
                                                    $statusIcon = 'fa-minus';
                                                    $canEditDay = false;
                                                }
                                            }

                                            $monthRecords[] = [
                                                'date' => $dateKey,
                                                'day' => $date->format('d D'),
                                                'attendance_id' => $firstId ?: null,
                                                'status' => $statusLabel,
                                                'clock_in' => $firstRecord && $firstRecord->clock_in ? Carbon::parse($firstRecord->clock_in)->format('h:i A') : '-',
                                                'clock_out' => $firstRecord && $firstRecord->clock_out ? Carbon::parse($firstRecord->clock_out)->format('h:i A') : '-',
                                                'total' => $plainDisplay,
                                                'note' => $statusClass === 'wfh'
                                                    ? 'Auto Saturday WFH'
                                                    : ($statusClass === 'holiday' && $date->isSunday()
                                                        ? 'Auto Sunday Holiday'
                                                        : ($hasOpenSession ? 'Open session' : ($hasBadSession ? 'Bad data detected' : ''))),
                                            ];
                                        @endphp
                                        <td class="{{ $isWeekend ? 'weekend' : '' }} {{ $isToday ? 'today' : '' }}">
                                            @if($canOpenDetails)
                                                <a href="javascript:;" class="hour-day-action view-attendance" data-attendance-id="{{ $firstId }}" data-user-id="{{ $uid }}" data-date="{{ $dateKey }}" aria-label="View {{ $statusTitle }}">
                                                    <span class="hour-status-badge {{ $statusClass }}" title="{{ $statusTitle }}">
                                                        <span class="status-symbol"><i class="fas {{ $statusIcon }}"></i></span>
                                                        <span class="hour-duration">{!! $displayHtml !!}</span>
                                                    </span>
                                                </a>
                                            @elseif($canEditDay)
                                                <a href="javascript:;" class="hour-day-action edit-attendance" data-attendance-id="" data-user-id="{{ $uid }}" data-date="{{ $dateKey }}" aria-label="Edit {{ $statusTitle }}">
                                                    <span class="status-badge {{ $statusClass }}" title="{{ $statusTitle }}">
                                                        <i class="fas {{ $statusIcon }}"></i>
                                                    </span>
                                                </a>
                                            @else
                                                <span class="status-badge {{ $statusClass }}" title="{{ $statusTitle }}">
                                                    <i class="fas {{ $statusIcon }}"></i>
                                                </span>
                                            @endif
                                        </td>
                                    @endfor

                                    @php
                                        $totalSeconds = (int) ($periodTotals[$uid]['seconds'] ?? 0);
                                        if ($totalSeconds <= 0) {
                                            $totalSeconds = $computedRowSeconds;
                                        }
                                        $totalHms = attendanceHourSecsToHms($totalSeconds);
                                        $employeeMonthPayload = [
                                            'user_id' => $uid,
                                            'name' => $user->name,
                                            'designation' => $designationName,
                                            'photo' => $user->profile_image ? asset($user->profile_image) : asset('images/default-avatar.png'),
                                            'month' => (int) $month,
                                            'year' => (int) $year,
                                            'month_name' => Carbon::createFromDate($year, $month)->format('F Y'),
                                            'total_hours' => $totalHms,
                                            'records' => $monthRecords,
                                        ];
                                        $employeeMonthPayloadEncoded = base64_encode(json_encode($employeeMonthPayload));
                                    @endphp
                                    <td>
                                        <span class="total-hours-badge">
                                            <i class="fas fa-clock"></i>{{ $totalHms }}
                                        </span>
                                    </td>
                                    <td class="no-export">
                                        <div class="hour-row-actions">
                                            <button type="button" class="hour-action-btn view js-hour-month-view" data-payload="{{ $employeeMonthPayloadEncoded }}" title="View month hour details">
                                                <i class="fas fa-eye"></i>
                                                <span>View</span>
                                            </button>
                                            <button type="button" class="hour-action-btn edit js-hour-month-edit" data-payload="{{ $employeeMonthPayloadEncoded }}" title="Edit month records">
                                                <i class="fas fa-pen"></i>
                                                <span>Edit</span>
                                            </button>
                                            <button type="button" class="hour-action-btn archive js-hour-month-archive" data-payload="{{ $employeeMonthPayloadEncoded }}" title="Archive month records">
                                                <i class="fas fa-box-archive"></i>
                                                <span>Archive</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <div>
                        <i class="fas fa-users"></i> Showing {{ $users->count() }} employees
                    </div>
                    <div>
                        <i class="fas fa-info-circle"></i>
                        {{ Carbon::createFromDate(null, $month)->format('F') }} {{ $year }} | {{ $daysInMonth }} days
                    </div>
                    <div>
                        <i class="fas fa-sync-alt"></i>
                        Last updated: {{ now()->format('d M Y, h:i A') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="hourAttendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Attendance Details</h5>
                <button type="button" class="btn-close btn-close-white" data-hour-attendance-modal-close aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="hourAttendanceModalBody">
                <div class="text-center py-5 bg-white">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3 mb-0 text-muted">Loading attendance details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-hour-attendance-modal-close>Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
        return;
    }

    const tableSelector = '#hourAttendanceTable';

    if ($.fn.DataTable.isDataTable(tableSelector)) {
        $(tableSelector).DataTable().destroy();
    }

    const exportColumnIndexes = [];
    const utilityColumnIndexes = [];

    $(tableSelector + ' thead th').each(function(index) {
        if ($(this).hasClass('no-export')) {
            utilityColumnIndexes.push(index);
        } else {
            exportColumnIndexes.push(index);
        }
    });

    function checkedRows() {
        return $(table.rows().nodes()).find('.hour-row-checkbox:checked');
    }

    function filteredRowCheckboxes() {
        return $(table.rows({ search: 'applied' }).nodes()).find('.hour-row-checkbox');
    }

    function exportRows(idx, data, node) {
        const selectedRows = checkedRows();
        if (selectedRows.length > 0) {
            return $(node).find('.hour-row-checkbox').is(':checked');
        }

        return true;
    }

    const exportFormat = {
        body: function(data, row, column, node) {
            const cell = $(node);
            const titledStatus = cell.find('[title]').first();

            if (titledStatus.length && titledStatus.attr('title')) {
                return titledStatus.attr('title');
            }

            return cell.text().replace(/\s+/g, ' ').trim();
        }
    };

    const table = $(tableSelector).DataTable({
        dom: '<"row align-items-center"<"col-md-6"l><"col-md-6"f>>Brt<"row align-items-center"<"col-md-6"i><"col-md-6"p>>',
        responsive: false,
        scrollX: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        order: [],
        columnDefs: [
            { orderable: false, searchable: false, targets: utilityColumnIndexes }
        ],
        buttons: [
            {
                extend: 'copyHtml5',
                text: 'Copy',
                title: 'Attendance by Hour',
                exportOptions: { columns: exportColumnIndexes, rows: exportRows, modifier: { search: 'applied' }, stripHtml: true, format: exportFormat }
            },
            {
                extend: 'csvHtml5',
                text: 'CSV',
                title: 'Attendance by Hour',
                filename: 'attendance-by-hour',
                exportOptions: { columns: exportColumnIndexes, rows: exportRows, modifier: { search: 'applied' }, stripHtml: true, format: exportFormat }
            },
            {
                extend: 'excelHtml5',
                text: 'Excel',
                title: 'Attendance by Hour',
                filename: 'attendance-by-hour',
                exportOptions: { columns: exportColumnIndexes, rows: exportRows, modifier: { search: 'applied' }, stripHtml: true, format: exportFormat }
            },
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                title: 'Attendance by Hour',
                filename: 'attendance-by-hour',
                pageSize: 'A3',
                orientation: 'landscape',
                exportOptions: { columns: exportColumnIndexes, rows: exportRows, modifier: { search: 'applied' }, stripHtml: true, format: exportFormat }
            },
            {
                extend: 'print',
                text: 'Print',
                title: 'Attendance by Hour',
                exportOptions: { columns: exportColumnIndexes, rows: exportRows, modifier: { search: 'applied' }, stripHtml: true, format: exportFormat }
            }
        ],
        language: {
            search: '_INPUT_',
            searchPlaceholder: 'Search employees...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'Showing 0 to 0 of 0 entries',
            paginate: {
                previous: "<i class='fas fa-chevron-left'></i>",
                next: "<i class='fas fa-chevron-right'></i>"
            }
        }
    });

    table.buttons().container().addClass('hour-export-buttons').appendTo('.table-card');

    function updateHourSelection() {
        const rowCheckboxes = filteredRowCheckboxes();
        const checked = rowCheckboxes.filter(':checked');
        const selectAll = document.getElementById('hourSelectAll');
        const selectedCount = document.getElementById('hourSelectedCount');

        if (selectedCount) {
            selectedCount.innerHTML = `<i class="fas fa-check-square"></i>${checked.length} selected`;
        }

        if (selectAll) {
            selectAll.checked = rowCheckboxes.length > 0 && checked.length === rowCheckboxes.length;
            selectAll.indeterminate = checked.length > 0 && checked.length < rowCheckboxes.length;
        }

        $(table.rows().nodes()).each(function() {
            const checkbox = $(this).find('.hour-row-checkbox');
            $(this).toggleClass('hour-row-selected', checkbox.is(':checked'));
        });
    }

    document.addEventListener('change', function(event) {
        if (event.target && event.target.id === 'hourSelectAll') {
            filteredRowCheckboxes().prop('checked', event.target.checked);
            updateHourSelection();
        }

        if (event.target && event.target.classList.contains('hour-row-checkbox')) {
            updateHourSelection();
        }
    });

    const clearSelectionButton = document.getElementById('hourClearSelection');
    if (clearSelectionButton) {
        clearSelectionButton.addEventListener('click', function() {
            $(table.rows().nodes()).find('.hour-row-checkbox').prop('checked', false);
            const selectAll = document.getElementById('hourSelectAll');
            if (selectAll) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            }
            updateHourSelection();
        });
    }

    function getHourAttendanceModal() {
        const modalEl = document.getElementById('hourAttendanceModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
            return null;
        }

        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        return bootstrap.Modal.getOrCreateInstance(modalEl, {
            backdrop: true,
            keyboard: true,
            focus: true
        });
    }

    function showHourModalLoading(message) {
        $('#hourAttendanceModalBody').html(
            '<div class="text-center py-5 bg-white">' +
                '<div class="spinner-border text-primary" role="status"></div>' +
                '<p class="mt-3 mb-0 text-muted">' + message + '</p>' +
            '</div>'
        );
    }

    function closeHourAttendanceModal() {
        const modalEl = document.getElementById('hourAttendanceModal');
        const modal = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;

        if (modal) {
            modal.hide();
        }

        $('#hourAttendanceModalBody').html('');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css({ overflow: '', paddingRight: '' });
    }

    function escapeHourHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function(char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function parseHourPayload(encoded) {
        const binary = atob(encoded);
        const bytes = new Uint8Array(binary.length);

        for (let i = 0; i < binary.length; i++) {
            bytes[i] = binary.charCodeAt(i);
        }

        if (window.TextDecoder) {
            return JSON.parse(new TextDecoder('utf-8').decode(bytes));
        }

        return JSON.parse(decodeURIComponent(escape(binary)));
    }

    function renderHourMonthDetails(payload, editMode) {
        const rows = (payload.records || []).map(function(record) {
            let editButton = '';

            if (editMode) {
                editButton = '<button type="button" class="month-edit-day-btn edit-attendance" ' +
                    'data-attendance-id="' + escapeHourHtml(record.attendance_id || '') + '" ' +
                    'data-user-id="' + escapeHourHtml(payload.user_id) + '" ' +
                    'data-date="' + escapeHourHtml(record.date) + '">' +
                    '<i class="fas fa-pen me-1"></i>Edit</button>';
            }

            return '<tr>' +
                '<td><strong>' + escapeHourHtml(record.day) + '</strong><div class="small text-muted">' + escapeHourHtml(record.date) + '</div></td>' +
                '<td>' + escapeHourHtml(record.status) + '</td>' +
                '<td>' + escapeHourHtml(record.clock_in) + '</td>' +
                '<td>' + escapeHourHtml(record.clock_out) + '</td>' +
                '<td>' + escapeHourHtml(record.total) + '</td>' +
                '<td>' + escapeHourHtml(record.note || '-') + '</td>' +
                (editMode ? '<td class="text-center">' + editButton + '</td>' : '') +
            '</tr>';
        }).join('');

        $('#hourAttendanceModal .modal-title').text(editMode ? 'Edit Monthly Attendance Hours' : 'Monthly Attendance Hours');
        $('#hourAttendanceModalBody').html(
            '<div class="hour-month-summary">' +
                '<div class="hour-month-profile">' +
                    '<img src="' + escapeHourHtml(payload.photo) + '" alt="' + escapeHourHtml(payload.name) + '">' +
                    '<div>' +
                        '<h5>' + escapeHourHtml(payload.name) + '</h5>' +
                        '<p>' + escapeHourHtml(payload.designation) + ' | ' + escapeHourHtml(payload.month_name) +
                        ' | Total: ' + escapeHourHtml(payload.total_hours) + '</p>' +
                    '</div>' +
                '</div>' +
                '<div class="table-responsive">' +
                    '<table class="hour-month-table">' +
                        '<thead><tr>' +
                            '<th>Date</th><th>Status</th><th>Clock In</th><th>Clock Out</th><th>Total</th><th>Note</th>' +
                            (editMode ? '<th class="text-center">Action</th>' : '') +
                        '</tr></thead>' +
                        '<tbody>' + rows + '</tbody>' +
                    '</table>' +
                '</div>' +
            '</div>'
        );

        const modal = getHourAttendanceModal();
        if (modal) {
            modal.show();
        }
    }

    $(document).off('click.hourMonthView', '.js-hour-month-view')
        .on('click.hourMonthView', '.js-hour-month-view', function() {
        renderHourMonthDetails(parseHourPayload(this.dataset.payload), false);
    });

    $(document).off('click.hourMonthEdit', '.js-hour-month-edit')
        .on('click.hourMonthEdit', '.js-hour-month-edit', function() {
        renderHourMonthDetails(parseHourPayload(this.dataset.payload), true);
    });

    $(document).off('click.hourMonthArchive', '.js-hour-month-archive')
        .on('click.hourMonthArchive', '.js-hour-month-archive', function() {
        const archiveButton = $(this);
        const originalHtml = archiveButton.html();
        const payload = parseHourPayload(this.dataset.payload);
        const message = 'Archive attendance records for ' + payload.name + ' in ' + payload.month_name + '?\n\nThey will move to Archived Attendance and can be restored later.';

        if (!confirm(message)) {
            return;
        }

        archiveButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i><span>Archiving</span>');

        $.ajax({
            url: "{{ route('attendance.month.archive') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                user_id: payload.user_id,
                month: payload.month,
                year: payload.year
            },
            success: function(response) {
                showHourNotification(response.message || 'Monthly attendance archived successfully.', 'success');
                setTimeout(function() {
                    window.location.reload();
                }, 900);
            },
            error: function(xhr) {
                let message = 'Unable to archive monthly attendance.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showHourNotification(message, 'danger');
            },
            complete: function() {
                archiveButton.prop('disabled', false).html(originalHtml);
            }
        });
    });

    $(document).off('click.hourAttendanceView', '.view-attendance')
        .on('click.hourAttendanceView', '.view-attendance', function(e) {
        e.preventDefault();

        const modal = getHourAttendanceModal();
        if (!modal) {
            return;
        }

        $('#hourAttendanceModal .modal-title').text('Attendance Details');
        showHourModalLoading('Loading attendance details...');
        modal.show();

        $.ajax({
            url: "{{ url('attendance/details') }}",
            type: 'GET',
            data: {
                attendance_id: $(this).data('attendance-id'),
                user_id: $(this).data('user-id'),
                date: $(this).data('date')
            },
            success: function(response) {
                $('#hourAttendanceModalBody').html(response);
            },
            error: function() {
                $('#hourAttendanceModalBody').html('<div class="alert alert-danger m-4">Error loading attendance details.</div>');
            }
        });
    });

    $(document).off('click.hourAttendanceEdit', '.edit-attendance')
        .on('click.hourAttendanceEdit', '.edit-attendance', function(e) {
        e.preventDefault();

        const modal = getHourAttendanceModal();
        if (!modal) {
            return;
        }

        $('#hourAttendanceModal .modal-title').text('Edit Attendance');
        showHourModalLoading('Loading attendance form...');
        modal.show();

        $.ajax({
            url: "{{ url('attendance/edit') }}",
            type: 'GET',
            data: {
                attendance_id: $(this).data('attendance-id'),
                user_id: $(this).data('user-id'),
                date: $(this).data('date')
            },
            success: function(response) {
                $('#hourAttendanceModalBody').html(response);
            },
            error: function() {
                $('#hourAttendanceModalBody').html('<div class="alert alert-danger m-4">Error loading attendance form.</div>');
            }
        });
    });

    $(document).off('submit.hourAttendanceForm', '#hourAttendanceModal .attendance-form')
        .on('submit.hourAttendanceForm', '#hourAttendanceModal .attendance-form', function(e) {
        e.preventDefault();

        const form = this;
        const submitButton = $(form).find('[type="submit"]').first();
        const originalHtml = submitButton.html();

        submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: form.action,
            type: form.method || 'POST',
            data: new FormData(form),
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function() {
                closeHourAttendanceModal();
                window.location.reload();
            },
            error: function(xhr) {
                let message = 'Unable to save attendance. Please check the form and try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                $(form).prepend('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>');
            },
            complete: function() {
                submitButton.prop('disabled', false).html(originalHtml);
            }
        });
    });

    $(document).off('click.hourAttendanceModalClose', '[data-hour-attendance-modal-close]')
        .on('click.hourAttendanceModalClose', '[data-hour-attendance-modal-close]', function(e) {
        e.preventDefault();
        closeHourAttendanceModal();
    });

    $(document).off('change.hourWorkFrom', '#hourAttendanceModal #work_from_type')
        .on('change.hourWorkFrom', '#hourAttendanceModal #work_from_type', function() {
        $('#hourAttendanceModal #other_location_div').toggleClass('hidden', this.value !== 'other');
    });

    $(document).off('change.hourStatusSync', '#hourAttendanceModal select[name="status"]')
        .on('change.hourStatusSync', '#hourAttendanceModal select[name="status"]', function() {
        const value = this.value;
        $('#hourAttendanceModal input[name="late"][value="' + (value === 'late' ? 'yes' : 'no') + '"]').prop('checked', true);
        $('#hourAttendanceModal input[name="half_day"][value="' + (value === 'half_day' ? 'yes' : 'no') + '"]').prop('checked', true);
    });

    table.on('draw', updateHourSelection);
    updateHourSelection();

    function showHourNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 12px;
            padding: 1rem 1.5rem;
        `;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        setTimeout(function() {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }

    window.exportHourAttendance = function(format) {
        if (!$.fn.DataTable.isDataTable(tableSelector)) {
            showHourNotification('The attendance table is not ready yet.', 'warning');
            return;
        }

        const buttonSelectors = {
            copy: '.buttons-copy',
            csv: '.buttons-csv',
            excel: '.buttons-excel',
            pdf: '.buttons-pdf',
            print: '.buttons-print'
        };
        const selector = buttonSelectors[format];

        if (!selector || !table.button(selector).node()) {
            showHourNotification('The selected export option is currently unavailable.', 'danger');
            return;
        }

        table.button(selector).trigger();

        if (format !== 'copy' && format !== 'print') {
            const selectedTotal = checkedRows().length;
            showHourNotification(
                selectedTotal > 0
                    ? format.toUpperCase() + ' export started for selected employees.'
                    : format.toUpperCase() + ' export started for all filtered employees.',
                'success'
            );
        }
    };
});
</script>
@endpush
