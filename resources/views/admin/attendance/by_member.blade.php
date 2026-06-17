@extends('admin.layout.app')

@section('content')
<style>
    /* ===== PREMIUM ATTENDANCE BY MEMBER STYLES ===== */
    :root {
        --primary-blue: #1e3a8a;
        --primary-teal: #0ea5a4;
        --primary-green: #22c55e;
        --bg-light: #f8fafc;
        --glass-border: rgba(255, 255, 255, 0.7);
        --card-shadow: 0px 4px 20px rgba(0, 0, 0, 0.02),
            0px 8px 40px rgba(0, 0, 0, 0.04),
            0px 20px 60px rgba(30, 58, 138, 0.06);
        --card-shadow-hover: 0px 20px 50px rgba(0, 0, 0, 0.08),
            0px 30px 80px rgba(30, 58, 138, 0.12);
        --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --spring-transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .attendance-member-container {
        background: linear-gradient(135deg, #f0f9ff 0%, #e6f7f5 50%, #f0fdf4 100%);
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        position: relative;
        overflow: hidden;
    }

    .ambient-orb {
        display: none;
        position: absolute;
        border-radius: 50%;
        filter: blur(130px);
        opacity: 0.35;
        pointer-events: none;
        z-index: 1;
    }

    .orb-1 {
        top: -100px;
        right: -100px;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(30, 58, 138, 0.12) 0%, transparent 70%);
        animation: orbFloat 20s ease-in-out infinite;
    }

    .orb-2 {
        bottom: -100px;
        left: -100px;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(14, 165, 164, 0.1) 0%, transparent 70%);
        animation: orbFloat 25s ease-in-out infinite reverse;
    }

    .orb-3 {
        top: 50%;
        left: 50%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(34, 197, 94, 0.08) 0%, transparent 70%);
        animation: orbFloat 18s ease-in-out infinite;
        transform: translate(-50%, -50%);
    }

    @keyframes orbFloat {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(40px, -30px) scale(1.05); }
        66% { transform: translate(-30px, 40px) scale(0.95); }
    }

    .content-wrapper {
        position: relative;
        z-index: 10;
    }

    /* ===== HEADER CARD ===== */
    .header-card {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        padding: 1.75rem 2.25rem;
        margin-bottom: 2rem;
        border: 1px solid var(--glass-border);
        box-shadow: var(--card-shadow);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        animation: slideDown 0.6s ease;
        transition: var(--spring-transition);
    }

    .header-card:hover {
        box-shadow: var(--card-shadow-hover);
        border-color: rgba(14, 165, 164, 0.2);
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-30px); }
        to { opacity: 1; transform: translateY(0); }
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
        border-color: transparent;
        box-shadow: 0 4px 15px rgba(14, 165, 164, 0.25);
    }

    .nav-tab-btn i {
        font-size: 0.9rem;
    }

    /* ===== FILTER CARD ===== */
    .filter-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--glass-border);
        box-shadow: var(--card-shadow);
        transition: var(--spring-transition);
    }

    .filter-card:hover {
        box-shadow: var(--card-shadow-hover);
    }

    .filter-header {
        margin-bottom: 1rem;
    }

    .filter-header h6 {
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        font-size: 1.28rem;
    }

    .filter-header h6 i {
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

    .filter-group .form-control option {
        padding: 0.5rem;
    }

    .filter-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .btn-filter {
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-teal));
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
        box-shadow: 0 4px 15px rgba(14, 165, 164, 0.2);
    }

    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(14, 165, 164, 0.3);
    }

    .btn-reset {
        background: linear-gradient(135deg, #64748b, #475569);
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
        box-shadow: 0 4px 15px rgba(100, 116, 139, 0.2);
    }

    .btn-reset:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(100, 116, 139, 0.3);
        color: white;
    }

    /* ===== TABLE CARD ===== */
    .table-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        overflow: hidden;
        border: 1px solid var(--glass-border);
        box-shadow: var(--card-shadow);
        transition: var(--spring-transition);
    }

    .table-card:hover {
        box-shadow: var(--card-shadow-hover);
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

    .table-responsive {
        overflow-x: auto;
        padding: 0.5rem 0.5rem 0.5rem;
    }

    .attendance-member-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.2rem;
        min-width: 1280px;
    }

    .attendance-member-table thead th {
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

    .attendance-member-table thead th i {
        margin-right: 4px;
        color: var(--primary-teal);
        font-size: 0.8rem;
    }

    .attendance-member-table thead th.employee-col {
        text-align: left;
        min-width: 250px;
        border-radius: 14px 0 0 0;
    }

    .attendance-member-table thead th.total-col {
        min-width: 120px;
    }

    .attendance-member-table thead th.actions-col {
        min-width: 255px;
    }

    .attendance-member-table thead th .day-number {
        display: block;
        font-size: 1.06rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }

    .attendance-member-table thead th .day-name {
        font-size: 0.78rem;
        color: #94a3b8;
        font-weight: 600;
    }

    .attendance-member-table thead th.weekend {
        background: rgba(245, 158, 11, 0.06);
    }

    .attendance-member-table thead th.weekend .day-number {
        color: #d97706;
    }

    .attendance-member-table thead th.today {
        border: 2px solid var(--primary-teal);
        background: rgba(14, 165, 164, 0.06);
    }

    .attendance-member-table tbody tr {
        transition: all 0.3s ease;
    }

    .attendance-member-table tbody tr:hover td {
        background: #f8fafc;
    }

    .attendance-member-table tbody td {
        padding: 0.5rem 0.3rem;
        text-align: center;
        vertical-align: middle;
        border: none;
        border-bottom: 1px solid #e2e8f0;
        transition: all 0.2s ease;
        font-size: 1.04rem;
    }

    .attendance-member-table tbody td.weekend {
        background: rgba(245, 158, 11, 0.03);
    }

    .attendance-member-table tbody td.today {
        border: 1px solid rgba(14, 165, 164, 0.2);
        border-radius: 8px;
    }

    /* ===== EMPLOYEE CELL ===== */
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

    /* ===== STATUS BADGE ===== */
    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        font-size: 0.92rem;
        transition: var(--spring-transition);
    }

    .status-badge.present {
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        color: #065f46;
        border: 2px solid rgba(34, 197, 94, 0.2);
    }

    .status-badge:hover {
        transform: scale(1.15);
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.25);
    }

    .status-badge.absent {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #991b1b;
        border: 2px solid rgba(239, 68, 68, 0.18);
        font-weight: 700;
    }

    .status-badge.late {
        background: linear-gradient(135deg, #ffedd5, #fed7aa);
        color: #9a3412;
        border: 2px solid rgba(249, 115, 22, 0.2);
    }

    .status-badge.half-day {
        background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
        color: #3730a3;
        border: 2px solid rgba(99, 102, 241, 0.22);
    }

    .status-badge.holiday {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
        border: 2px solid rgba(245, 158, 11, 0.24);
    }

    .status-badge.day-off {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        color: #1d4ed8;
        border: 2px solid rgba(37, 99, 235, 0.22);
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

    .total-hours-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.7rem;
        border-radius: 999px;
        background: #f8fafc;
        color: #0f172a;
        border: 1px solid #e2e8f0;
        font-weight: 800;
        font-size: 0.98rem;
    }

    .member-day-action {
        display: inline-flex;
        text-decoration: none;
    }

    .member-day-action:hover .status-badge {
        transform: scale(1.15);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.16);
    }

    .member-row-actions {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        min-width: 245px;
    }

    .member-action-btn {
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

    .member-action-btn:hover {
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.16);
    }

    .member-action-btn:disabled {
        cursor: not-allowed;
        opacity: 0.72;
        transform: none;
        box-shadow: none;
    }

    .member-action-btn.view {
        background: #2563eb;
    }

    .member-action-btn.edit {
        background: #f59e0b;
    }

    .member-action-btn.archive {
        background: #64748b;
    }

    .member-month-summary {
        background: #f8fafc;
        padding: 1.25rem;
    }

    .member-month-profile {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .member-month-profile img {
        width: 64px;
        height: 64px;
        border-radius: 14px;
        object-fit: cover;
    }

    .member-month-profile h5 {
        margin: 0;
        color: #0f172a;
        font-weight: 800;
        font-size: 1.32rem;
    }

    .member-month-profile p {
        margin: 0.2rem 0 0;
        color: #64748b;
        font-weight: 600;
        font-size: 1.05rem;
    }

    .member-month-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.45rem;
    }

    .member-month-table th {
        color: #64748b;
        font-size: 1rem;
        text-transform: uppercase;
        padding: 0.65rem;
    }

    .member-month-table td {
        background: #ffffff;
        color: #1e293b;
        padding: 0.85rem;
        font-size: 1.02rem;
        border-top: 1px solid #eef2f7;
        border-bottom: 1px solid #eef2f7;
    }

    .member-month-table td:first-child {
        border-left: 1px solid #eef2f7;
        border-radius: 12px 0 0 12px;
    }

    .member-month-table td:last-child {
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

    .attendance-member-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.7rem;
        flex-wrap: wrap;
    }

    .selected-count-badge {
        min-height: 36px;
        padding: 0.45rem 0.9rem;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 1rem;
        font-weight: 800;
        border: 1px solid #e2e8f0;
    }

    .btn-clear-selection,
    .btn-export-menu,
    .btn-archive-link {
        min-height: 38px;
        border: 0;
        border-radius: 999px;
        padding: 0.5rem 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 1rem;
        font-weight: 800;
        transition: var(--spring-transition);
    }

    .btn-clear-selection {
        background: #e2e8f0;
        color: #475569;
    }

    .btn-clear-selection:hover {
        background: #cbd5e1;
        color: #0f172a;
        transform: translateY(-2px);
    }

    .btn-export-menu {
        background: #f0f9f4;
        color: #0f744c;
        border: 1px solid rgba(16, 185, 129, 0.2);
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

    .btn-archive-link:hover,
    .btn-archive-link:focus {
        background: #ffedd5;
        color: #9a3412;
        border-color: #fb923c;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -8px rgba(249, 115, 22, 0.3);
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

    .btn-export-menu:hover,
    .btn-export-menu:focus {
        background: #e6f3ec;
        color: #0f744c;
        border-color: #34d399;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -8px rgba(16, 185, 129, 0.25);
    }

    .attendance-member-actions .dropdown-menu {
        min-width: 230px;
        margin-top: 10px !important;
        background: #ffffff;
        border: 1px solid rgba(16, 185, 129, 0.18);
        border-radius: 16px;
        padding: 10px;
        box-shadow: 0 24px 48px -14px rgba(10, 46, 31, 0.28);
        z-index: 9999 !important;
    }

    .attendance-member-actions .dropdown-item {
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

    .attendance-member-actions .dropdown-item:hover {
        background: #ecfdf5;
        color: #059669;
    }

    .attendance-member-actions .dropdown-divider {
        margin: 8px 0;
        border-color: rgba(16, 185, 129, 0.16);
    }

    .member-export-buttons {
        position: absolute;
        left: -9999px;
        top: auto;
        width: 1px;
        height: 1px;
        overflow: hidden;
    }

    .member-select-col {
        width: 54px;
        min-width: 54px;
        text-align: center !important;
    }

    .member-row-checkbox,
    #memberSelectAll {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--primary-teal);
    }

    .attendance-member-table tbody tr.member-row-selected td {
        background: #ecfdf5;
    }

    .attendance-member-table.dataTable {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
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

    /* ===== TABLE FOOTER ===== */
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

    .legend-items {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.9rem;
        color: #475569;
        font-weight: 500;
    }

    .legend-dot {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .legend-dot.present {
        background: linear-gradient(135deg, #34d399, #22c55e);
        border: 1px solid rgba(34, 197, 94, 0.3);
    }

    .legend-dot.absent {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .legend-dot.late {
        background: linear-gradient(135deg, #fb923c, #f97316);
        border: 1px solid rgba(249, 115, 22, 0.3);
    }

    .legend-dot.half-day {
        background: linear-gradient(135deg, #818cf8, #6366f1);
        border: 1px solid rgba(99, 102, 241, 0.3);
    }

    .legend-dot.holiday {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .legend-dot.day-off {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        border: 1px solid rgba(37, 99, 235, 0.3);
    }

    .legend-dot.leave {
        background: linear-gradient(135deg, #22d3ee, #06b6d4);
        border: 1px solid rgba(6, 182, 212, 0.3);
    }

    .legend-dot.wfh {
        background: linear-gradient(135deg, #86efac, #22c55e);
        border: 1px solid rgba(34, 197, 94, 0.3);
    }

    .legend-dot.today {
        background: transparent;
        border: 2px solid var(--primary-teal);
        width: 14px;
        height: 14px;
    }

    .legend-dot.weekend {
        background: rgba(245, 158, 11, 0.15);
        border: 1px solid rgba(245, 158, 11, 0.2);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
        .attendance-member-container {
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

        .attendance-member-table thead th {
            font-size: 0.78rem;
            padding: 0.4rem 0.2rem;
        }

        .attendance-member-table thead th .day-number {
            font-size: 0.9rem;
        }

        .attendance-member-table tbody td {
            font-size: 0.9rem;
            padding: 0.4rem 0.2rem;
        }

        .employee-name {
            font-size: 0.95rem;
        }

        .employee-avatar {
            width: 30px;
            height: 30px;
            font-size: 0.7rem;
        }

        .status-badge {
            width: 32px;
            height: 32px;
            font-size: 0.8rem;
        }

        .nav-tab-btn {
            font-size: 0.92rem;
            padding: 0.4rem 0.8rem;
        }
    }

    @media (max-width: 768px) {
        .attendance-member-container {
            padding: 1rem;
        }

        .header-card {
            padding: 1.25rem;
        }

        .filter-grid {
            grid-template-columns: 1fr;
        }

        .filter-actions {
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

        .legend-items {
            justify-content: center;
        }

        .attendance-member-table thead th {
            font-size: 0.72rem;
            padding: 0.3rem 0.15rem;
        }

        .attendance-member-table thead th .day-number {
            font-size: 0.82rem;
        }

        .attendance-member-table thead th .day-name {
            font-size: 0.64rem;
        }

        .attendance-member-table tbody td {
            font-size: 0.84rem;
            padding: 0.3rem 0.15rem;
        }

        .employee-cell {
            padding: 0.2rem 0.3rem;
            gap: 0.4rem;
            min-height: 40px;
        }

        .employee-avatar {
            width: 24px;
            height: 24px;
            font-size: 0.6rem;
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

        .nav-tab-btn {
            font-size: 0.82rem;
            padding: 0.3rem 0.6rem;
            gap: 0.2rem;
        }

        .nav-tab-btn i {
            font-size: 0.7rem;
        }
    }

    /* ===== DARK MODE ===== */
    html[data-pms-theme="dark"] .attendance-member-container {
        background: linear-gradient(145deg, #07130d, #102119);
    }

    html[data-pms-theme="dark"] .header-card,
    html[data-pms-theme="dark"] .filter-card,
    html[data-pms-theme="dark"] .table-card {
        background: rgba(16, 33, 25, 0.95);
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .header-title h1 {
        background: linear-gradient(135deg, #60a5fa, #34d399, #22c55e);
        -webkit-background-clip: text;
        background-clip: text;
    }

    html[data-pms-theme="dark"] .header-title p {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .filter-header h6,
    html[data-pms-theme="dark"] .table-header h6 {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .filter-group label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .filter-group .form-control {
        background: #183026;
        border-color: rgba(122, 240, 181, 0.2);
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .filter-group .form-control:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.12);
    }

    html[data-pms-theme="dark"] .filter-group .form-control option {
        background: #183026;
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .nav-tab-btn {
        background: #183026;
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .nav-tab-btn:hover {
        background: #102119;
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .nav-tab-btn.active {
        background: linear-gradient(135deg, #0f744c, #10b981);
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .attendance-member-table thead th {
        background: linear-gradient(135deg, #183026, #102119);
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .attendance-member-table thead th .day-number {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .attendance-member-table thead th .day-name {
        color: #64748b;
    }

    html[data-pms-theme="dark"] .attendance-member-table thead th.weekend {
        background: rgba(245, 158, 11, 0.08);
    }

    html[data-pms-theme="dark"] .attendance-member-table thead th.weekend .day-number {
        color: #fbbf24;
    }

    html[data-pms-theme="dark"] .attendance-member-table thead th.today {
        border-color: #34d399;
        background: rgba(52, 211, 153, 0.06);
    }

    html[data-pms-theme="dark"] .attendance-member-table tbody tr:hover td {
        background: #183026;
    }

    html[data-pms-theme="dark"] .attendance-member-table tbody td {
        border-color: rgba(122, 240, 181, 0.08);
    }

    html[data-pms-theme="dark"] .attendance-member-table tbody td.weekend {
        background: rgba(245, 158, 11, 0.04);
    }

    html[data-pms-theme="dark"] .attendance-member-table tbody td.today {
        border-color: rgba(52, 211, 153, 0.2);
    }

    html[data-pms-theme="dark"] .employee-name {
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .employee-id {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .employee-avatar {
        background: linear-gradient(135deg, #0f744c, #10b981);
    }

    html[data-pms-theme="dark"] .status-badge.present {
        background: linear-gradient(135deg, #064e3b, #047857);
        color: #34d399;
        border-color: rgba(52, 211, 153, 0.2);
    }

    html[data-pms-theme="dark"] .status-badge.present:hover {
        box-shadow: 0 4px 12px rgba(52, 211, 153, 0.25);
    }

    html[data-pms-theme="dark"] .status-badge.absent {
        background: #183026;
        color: #64748b;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .table-footer {
        border-color: rgba(122, 240, 181, 0.15);
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .badge-count {
        background: linear-gradient(135deg, #0f744c, #10b981);
    }

    html[data-pms-theme="dark"] .legend-item {
        color: #d9f1e4;
    }

    html[data-pms-theme="dark"] .legend-dot.absent {
        background: #183026;
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .legend-dot.weekend {
        background: rgba(245, 158, 11, 0.08);
        border-color: rgba(245, 158, 11, 0.15);
    }
</style>

<div class="attendance-member-container">
    <div class="ambient-orb orb-1"></div>
    <div class="ambient-orb orb-2"></div>
    <div class="ambient-orb orb-3"></div>

    <div class="content-wrapper">
        <div class="container-fluid">

            {{-- ===== HEADER CARD ===== --}}
            <div class="header-card">
                <div class="header-title">
                    <h1>
                        <i class="fas fa-user me-2"></i>Attendance by Member
                    </h1>
                    <p><i class="fas fa-info-circle me-1"></i>View monthly attendance overview for each employee</p>
                </div>
                <div class="tabs-wrapper">
                    <a href="{{ route('attendance.index') }}" class="nav-tab-btn">
                        <i class="fas fa-list-ul"></i> Summary
                    </a>
                    <a href="{{ route('attendance.byMember') }}" class="nav-tab-btn active">
                        <i class="fas fa-user"></i> By Member
                    </a>
                    <a href="{{ route('attendance.byHour') }}" class="nav-tab-btn">
                        <i class="fas fa-clock"></i> By Hour
                    </a>
                    <a href="{{ route('attendance.today.map', ['year' => $year, 'month' => $month]) }}" class="nav-tab-btn">
                        <i class="fas fa-map-marker-alt"></i> Location
                    </a>
                </div>
            </div>

            {{-- ===== FILTER CARD ===== --}}
            <div class="filter-card">
                <div class="filter-header">
                    <h6><i class="fas fa-filter"></i>Filter Attendance</h6>
                </div>
                <form method="GET" action="{{ route('attendance.byMember') }}" class="filter-grid">
                    <div class="filter-group">
                        <label for="month"><i class="fas fa-calendar-alt"></i> Month</label>
                        <select name="month" id="month" class="form-control" required>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(null, $m)->format('F') }}
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

                    @if(auth()->user()->role === 'admin')
                        <div class="filter-group">
                            <label for="user_id"><i class="fas fa-user"></i> Employee</label>
                            <select name="user_id" id="user_id" class="form-control">
                                <option value="">All Employees</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="filter-group filter-actions">
                        <label>&nbsp;</label>
                        <div class="action-buttons">
                            <button type="submit" class="btn-filter">
                                <i class="fas fa-search"></i> Apply
                            </button>
                            <a href="{{ route('attendance.byMember') }}" class="btn-reset">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- ===== TABLE CARD ===== --}}
            <div class="table-card">
                <div class="table-header">
                    <h6>
                        <i class="fas fa-users"></i>Monthly Attendance Overview
                        <span class="badge-count">{{ $users->count() }} Employees</span>
                    </h6>
                    <div class="attendance-member-actions">
                        <span class="selected-count-badge" id="memberSelectedCount">
                            <i class="fas fa-check-square"></i>0 selected
                        </span>
                        <button type="button" class="btn-clear-selection" id="memberClearSelection">
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
                                <li><button type="button" class="dropdown-item" onclick="exportMemberAttendance('copy')"><i class="fas fa-copy text-primary"></i> Copy to Clipboard</button></li>
                                <li><button type="button" class="dropdown-item" onclick="exportMemberAttendance('csv')"><i class="fas fa-file-csv text-success"></i> Export as CSV</button></li>
                                <li><button type="button" class="dropdown-item" onclick="exportMemberAttendance('excel')"><i class="fas fa-file-excel text-success"></i> Export as Excel</button></li>
                                <li><button type="button" class="dropdown-item" onclick="exportMemberAttendance('pdf')"><i class="fas fa-file-pdf text-danger"></i> Export as PDF</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><button type="button" class="dropdown-item" onclick="exportMemberAttendance('print')"><i class="fas fa-print text-info"></i> Print</button></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="attendance-member-table" id="memberAttendanceTable">
                        <thead>
                            <tr>
                                <th class="member-select-col no-export">
                                    <input type="checkbox" id="memberSelectAll" aria-label="Select all members">
                                </th>
                                <th class="employee-col">
                                    <i class="fas fa-user-circle"></i> Employee Name
                                </th>
                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $date = \Carbon\Carbon::createFromDate($year, $month, $d);
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
                                    $designationName = optional(optional($user->employeeDetail)->designation)->name
                                        ?? optional(optional($user->employeeDetail)->designation)->title
                                        ?? 'Employee';
                                    $monthRecords = [];
                                    $presentCount = 0;
                                @endphp
                                <tr>
                                    <td class="member-select-col">
                                        <input type="checkbox" class="member-row-checkbox" value="{{ $user->id }}" aria-label="Select {{ $user->name }}">
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

                                    @for ($d = 1; $d <= $daysInMonth; $d++)
                                        @php
                                            $dateKey = \Carbon\Carbon::createFromDate($year, $month, $d)->format('Y-m-d');
                                            $att = $attendanceMap[$user->id][$dateKey] ?? null;
                                            $date = \Carbon\Carbon::createFromDate($year, $month, $d);
                                            $isWeekend = $date->isWeekend();
                                            $isToday = $date->isToday();

                                            if ($att instanceof \Illuminate\Support\Collection) {
                                                $att = $att->first();
                                            }

                                            $isRealAttendance = $att instanceof \App\Models\Attendance;
                                            $status = strtolower($att->status ?? 'absent');

                                            if (in_array($status, ['on_leave', 'approved_leave'], true)) {
                                                $status = 'leave';
                                            }

                                            if ($status === 'present' && (($att->late ?? 'no') === 'yes')) {
                                                $status = 'late';
                                            }

                                            if ($status === 'present' && (($att->half_day ?? 'no') === 'yes')) {
                                                $status = 'half_day';
                                            }

                                            if ($status === 'absent' && $date->isSaturday()) {
                                                $status = 'wfh';
                                            } elseif ($status === 'absent' && $date->isSunday()) {
                                                $status = 'holiday';
                                            }

                                            $statusClass = [
                                                'present' => 'present',
                                                'absent' => 'absent',
                                                'late' => 'late',
                                                'half_day' => 'half-day',
                                                'holiday' => 'holiday',
                                                'day_off' => 'day-off',
                                                'leave' => 'leave',
                                                'wfh' => 'wfh',
                                            ][$status] ?? 'absent';

                                            $statusIcon = [
                                                'present' => 'fa-check',
                                                'absent' => 'fa-times',
                                                'late' => 'fa-clock',
                                                'half_day' => 'fa-star-half-alt',
                                                'holiday' => 'fa-star',
                                                'day_off' => 'fa-square',
                                                'leave' => 'fa-plane-departure',
                                                'wfh' => 'fa-home',
                                            ][$status] ?? 'fa-times';

                                            $statusLabel = [
                                                'present' => 'Present',
                                                'absent' => 'Absent',
                                                'late' => 'Late',
                                                'half_day' => 'Half Day',
                                                'holiday' => 'Holiday',
                                                'day_off' => 'Day Off',
                                                'leave' => 'On Leave',
                                                'wfh' => 'Work From Home',
                                            ][$status] ?? 'Absent';

                                            $statusTitle = $statusLabel . ' on ' . $date->format('d M Y');
                                            if ($status === 'leave' && !empty($att->leave_type)) {
                                                $statusTitle .= ' - ' . ucfirst(str_replace('_', ' ', $att->leave_type));
                                            }
                                            if ($status === 'holiday' && !empty($att->occassion)) {
                                                $statusTitle .= ' - ' . $att->occassion;
                                            }

                                            if (in_array($status, ['present', 'late'], true)) {
                                                $presentCount++;
                                            } elseif ($status === 'half_day') {
                                                $presentCount += 0.5;
                                            }

                                            $daySeconds = $isRealAttendance ? (int) ($att->total_seconds ?? 0) : 0;
                                            $monthRecords[] = [
                                                'date' => $dateKey,
                                                'day' => $date->format('d D'),
                                                'attendance_id' => $isRealAttendance ? $att->id : null,
                                                'status' => $statusLabel,
                                                'clock_in' => $isRealAttendance && $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('h:i A') : '-',
                                                'clock_out' => $isRealAttendance && $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('h:i A') : '-',
                                                'total' => $isRealAttendance ? sprintf('%02d:%02d:%02d', intdiv($daySeconds, 3600), intdiv($daySeconds % 3600, 60), $daySeconds % 60) : '-',
                                                'note' => $att->occassion ?? $att->reason ?? ($status === 'wfh' ? 'Auto Saturday WFH' : ($status === 'holiday' && $date->isSunday() ? 'Auto Sunday Holiday' : '')),
                                            ];

                                            $canOpenDetails = $isRealAttendance && !in_array($status, ['absent'], true);
                                            $canEditDay = auth()->user()->role === 'admin' && (!$isRealAttendance || $status === 'absent');
                                        @endphp
                                        <td class="{{ $isWeekend ? 'weekend' : '' }} {{ $isToday ? 'today' : '' }}">
                                            @if($canOpenDetails)
                                                <a href="javascript:;" class="member-day-action view-attendance" data-attendance-id="{{ $att->id }}" data-user-id="{{ $user->id }}" data-date="{{ $dateKey }}" aria-label="View {{ $statusTitle }}">
                                                    <span class="status-badge {{ $statusClass }}" title="{{ $statusTitle }}">
                                                        <i class="fas {{ $statusIcon }}"></i>
                                                    </span>
                                                </a>
                                            @elseif($canEditDay)
                                                <a href="javascript:;" class="member-day-action edit-attendance" data-attendance-id="{{ $isRealAttendance ? $att->id : '' }}" data-user-id="{{ $user->id }}" data-date="{{ $dateKey }}" aria-label="Edit {{ $statusTitle }}">
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
                                        $employeeMonthPayload = [
                                            'user_id' => $user->id,
                                            'name' => $user->name,
                                            'designation' => $designationName,
                                            'photo' => $user->profile_image ? asset($user->profile_image) : asset('images/default-avatar.png'),
                                            'month' => (int) $month,
                                            'year' => (int) $year,
                                            'month_name' => \Carbon\Carbon::createFromDate($year, $month)->format('F Y'),
                                            'total_hours' => $periodTotals[$user->id]['hhmm'] ?? '00:00',
                                            'present_count' => $presentCount,
                                            'days_in_month' => $daysInMonth,
                                            'records' => $monthRecords,
                                        ];
                                        $employeeMonthPayloadEncoded = base64_encode(json_encode($employeeMonthPayload));
                                    @endphp
                                    <td>
                                        <span class="total-hours-badge">
                                            <i class="fas fa-clock"></i>
                                            {{ $periodTotals[$user->id]['hhmm'] ?? '00:00' }}
                                        </span>
                                    </td>
                                    <td class="no-export">
                                        <div class="member-row-actions">
                                            <button type="button" class="member-action-btn view js-member-month-view" data-payload="{{ $employeeMonthPayloadEncoded }}" title="View month details">
                                                <i class="fas fa-eye"></i>
                                                <span>View</span>
                                            </button>
                                            <button type="button" class="member-action-btn edit js-member-month-edit" data-payload="{{ $employeeMonthPayloadEncoded }}" title="Edit month records">
                                                <i class="fas fa-pen"></i>
                                                <span>Edit</span>
                                            </button>
                                            <button type="button" class="member-action-btn archive js-member-month-archive" data-payload="{{ $employeeMonthPayloadEncoded }}" title="Archive month records">
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
                    <div class="footer-left">
                        <i class="fas fa-users"></i> Showing {{ $users->count() }} employees
                    </div>
                    <div class="legend-items">
                        <span class="legend-item">
                            <span class="legend-dot present"></span>
                            Present
                        </span>
                        <span class="legend-item">
                            <span class="legend-dot absent"></span>
                            Absent
                        </span>
                        <span class="legend-item">
                            <span class="legend-dot late"></span>
                            Late
                        </span>
                        <span class="legend-item">
                            <span class="legend-dot half-day"></span>
                            Half Day
                        </span>
                        <span class="legend-item">
                            <span class="legend-dot holiday"></span>
                            Holiday
                        </span>
                        <span class="legend-item">
                            <span class="legend-dot day-off"></span>
                            Day Off
                        </span>
                        <span class="legend-item">
                            <span class="legend-dot leave"></span>
                            On Leave
                        </span>
                        <span class="legend-item">
                            <span class="legend-dot wfh"></span>
                            Saturday WFH
                        </span>
                        <span class="legend-item">
                            <span class="legend-dot today"></span>
                            Today
                        </span>
                        <span class="legend-item">
                            <span class="legend-dot weekend"></span>
                            Weekend
                        </span>
                    </div>
                    <div class="footer-right">
                        <i class="fas fa-info-circle"></i>
                        {{ \Carbon\Carbon::createFromDate(null, $month)->format('F') }} {{ $year }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="memberAttendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Attendance Details</h5>
                <button type="button" class="btn-close btn-close-white" data-member-attendance-modal-close aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="memberAttendanceModalBody">
                <div class="text-center py-5 bg-white">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3 mb-0 text-muted">Loading attendance details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-member-attendance-modal-close>Close</button>
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

    const tableSelector = '#memberAttendanceTable';

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

    function exportRows(idx, data, node) {
        const selectedRows = checkedRows();
        if (selectedRows.length > 0) {
            return $(node).find('.member-row-checkbox').is(':checked');
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
                title: 'Attendance by Member',
                exportOptions: {
                    columns: exportColumnIndexes,
                    rows: exportRows,
                    modifier: { search: 'applied' },
                    stripHtml: true,
                    format: exportFormat
                }
            },
            {
                extend: 'csvHtml5',
                text: 'CSV',
                title: 'Attendance by Member',
                filename: 'attendance-by-member',
                exportOptions: {
                    columns: exportColumnIndexes,
                    rows: exportRows,
                    modifier: { search: 'applied' },
                    stripHtml: true,
                    format: exportFormat
                }
            },
            {
                extend: 'excelHtml5',
                text: 'Excel',
                title: 'Attendance by Member',
                filename: 'attendance-by-member',
                exportOptions: {
                    columns: exportColumnIndexes,
                    rows: exportRows,
                    modifier: { search: 'applied' },
                    stripHtml: true,
                    format: exportFormat
                }
            },
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                title: 'Attendance by Member',
                filename: 'attendance-by-member',
                pageSize: 'A3',
                orientation: 'landscape',
                exportOptions: {
                    columns: exportColumnIndexes,
                    rows: exportRows,
                    modifier: { search: 'applied' },
                    stripHtml: true,
                    format: exportFormat
                }
            },
            {
                extend: 'print',
                text: 'Print',
                title: 'Attendance by Member',
                exportOptions: {
                    columns: exportColumnIndexes,
                    rows: exportRows,
                    modifier: { search: 'applied' },
                    stripHtml: true,
                    format: exportFormat
                }
            }
        ],
        language: {
            search: '_INPUT_',
            searchPlaceholder: 'Search members...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'Showing 0 to 0 of 0 entries',
            paginate: {
                previous: "<i class='fas fa-chevron-left'></i>",
                next: "<i class='fas fa-chevron-right'></i>"
            }
        }
    });

    table.buttons().container().addClass('member-export-buttons').appendTo('.table-card');

    function checkedRows() {
        return $(table.rows().nodes()).find('.member-row-checkbox:checked');
    }

    function filteredRowCheckboxes() {
        return $(table.rows({ search: 'applied' }).nodes()).find('.member-row-checkbox');
    }

    function updateMemberSelection() {
        const rowCheckboxes = filteredRowCheckboxes();
        const checked = rowCheckboxes.filter(':checked');
        const selectAll = document.getElementById('memberSelectAll');
        const selectedCount = document.getElementById('memberSelectedCount');

        if (selectedCount) {
            selectedCount.innerHTML = `<i class="fas fa-check-square"></i>${checked.length} selected`;
        }

        if (selectAll) {
            selectAll.checked = rowCheckboxes.length > 0 && checked.length === rowCheckboxes.length;
            selectAll.indeterminate = checked.length > 0 && checked.length < rowCheckboxes.length;
        }

        $(table.rows().nodes()).each(function() {
            const checkbox = $(this).find('.member-row-checkbox');
            $(this).toggleClass('member-row-selected', checkbox.is(':checked'));
        });
    }

    document.addEventListener('change', function(event) {
        if (event.target && event.target.id === 'memberSelectAll') {
            filteredRowCheckboxes().prop('checked', event.target.checked);
            updateMemberSelection();
        }

        if (event.target && event.target.classList.contains('member-row-checkbox')) {
            updateMemberSelection();
        }
    });

    const clearSelectionButton = document.getElementById('memberClearSelection');
    if (clearSelectionButton) {
        clearSelectionButton.addEventListener('click', function() {
            $(table.rows().nodes()).find('.member-row-checkbox').prop('checked', false);
            const selectAll = document.getElementById('memberSelectAll');
            if (selectAll) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            }
            updateMemberSelection();
        });
    }

    function getMemberAttendanceModal() {
        const modalEl = document.getElementById('memberAttendanceModal');
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

    function showMemberModalLoading(message) {
        $('#memberAttendanceModalBody').html(
            '<div class="text-center py-5 bg-white">' +
                '<div class="spinner-border text-primary" role="status"></div>' +
                '<p class="mt-3 mb-0 text-muted">' + message + '</p>' +
            '</div>'
        );
    }

    function closeMemberAttendanceModal() {
        const modalEl = document.getElementById('memberAttendanceModal');
        const modal = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;

        if (modal) {
            modal.hide();
        }

        $('#memberAttendanceModalBody').html('');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css({ overflow: '', paddingRight: '' });
    }

    function escapeMemberHtml(value) {
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

    function parseMemberPayload(encoded) {
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

    function renderMemberMonthDetails(payload, editMode) {
        const rows = (payload.records || []).map(function(record) {
            let editButton = '';

            if (editMode) {
                editButton = '<button type="button" class="month-edit-day-btn edit-attendance" ' +
                    'data-attendance-id="' + escapeMemberHtml(record.attendance_id || '') + '" ' +
                    'data-user-id="' + escapeMemberHtml(payload.user_id) + '" ' +
                    'data-date="' + escapeMemberHtml(record.date) + '">' +
                    '<i class="fas fa-pen me-1"></i>Edit</button>';
            }

            return '<tr>' +
                '<td><strong>' + escapeMemberHtml(record.day) + '</strong><div class="small text-muted">' + escapeMemberHtml(record.date) + '</div></td>' +
                '<td>' + escapeMemberHtml(record.status) + '</td>' +
                '<td>' + escapeMemberHtml(record.clock_in) + '</td>' +
                '<td>' + escapeMemberHtml(record.clock_out) + '</td>' +
                '<td>' + escapeMemberHtml(record.total) + '</td>' +
                '<td>' + escapeMemberHtml(record.note || '-') + '</td>' +
                (editMode ? '<td class="text-center">' + editButton + '</td>' : '') +
            '</tr>';
        }).join('');

        $('#memberAttendanceModal .modal-title').text(editMode ? 'Edit Monthly Attendance' : 'Monthly Attendance Details');
        $('#memberAttendanceModalBody').html(
            '<div class="member-month-summary">' +
                '<div class="member-month-profile">' +
                    '<img src="' + escapeMemberHtml(payload.photo) + '" alt="' + escapeMemberHtml(payload.name) + '">' +
                    '<div>' +
                        '<h5>' + escapeMemberHtml(payload.name) + '</h5>' +
                        '<p>' + escapeMemberHtml(payload.designation) + ' | ' + escapeMemberHtml(payload.month_name) +
                        ' | Total: ' + escapeMemberHtml(payload.total_hours) +
                        ' | Present: ' + escapeMemberHtml(payload.present_count) + '/' + escapeMemberHtml(payload.days_in_month) + '</p>' +
                    '</div>' +
                '</div>' +
                '<div class="table-responsive">' +
                    '<table class="member-month-table">' +
                        '<thead><tr>' +
                            '<th>Date</th><th>Status</th><th>Clock In</th><th>Clock Out</th><th>Total</th><th>Note</th>' +
                            (editMode ? '<th class="text-center">Action</th>' : '') +
                        '</tr></thead>' +
                        '<tbody>' + rows + '</tbody>' +
                    '</table>' +
                '</div>' +
            '</div>'
        );

        const modal = getMemberAttendanceModal();
        if (modal) {
            modal.show();
        }
    }

    $(document).off('click.memberMonthView', '.js-member-month-view')
        .on('click.memberMonthView', '.js-member-month-view', function() {
        renderMemberMonthDetails(parseMemberPayload(this.dataset.payload), false);
    });

    $(document).off('click.memberMonthEdit', '.js-member-month-edit')
        .on('click.memberMonthEdit', '.js-member-month-edit', function() {
        renderMemberMonthDetails(parseMemberPayload(this.dataset.payload), true);
    });

    $(document).off('click.memberMonthArchive', '.js-member-month-archive')
        .on('click.memberMonthArchive', '.js-member-month-archive', function() {
        const archiveButton = $(this);
        const originalHtml = archiveButton.html();
        const payload = parseMemberPayload(this.dataset.payload);
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
                showMemberNotification(response.message || 'Monthly attendance archived successfully.', 'success');
                setTimeout(function() {
                    window.location.reload();
                }, 900);
            },
            error: function(xhr) {
                let message = 'Unable to archive monthly attendance.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showMemberNotification(message, 'danger');
            },
            complete: function() {
                archiveButton.prop('disabled', false).html(originalHtml);
            }
        });
    });

    $(document).off('click.memberAttendanceView', '.view-attendance')
        .on('click.memberAttendanceView', '.view-attendance', function(e) {
        e.preventDefault();

        const modal = getMemberAttendanceModal();
        if (!modal) {
            return;
        }

        $('#memberAttendanceModal .modal-title').text('Attendance Details');
        showMemberModalLoading('Loading attendance details...');
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
                $('#memberAttendanceModalBody').html(response);
            },
            error: function() {
                $('#memberAttendanceModalBody').html('<div class="alert alert-danger m-4">Error loading attendance details.</div>');
            }
        });
    });

    $(document).off('click.memberAttendanceEdit', '.edit-attendance')
        .on('click.memberAttendanceEdit', '.edit-attendance', function(e) {
        e.preventDefault();

        const modal = getMemberAttendanceModal();
        if (!modal) {
            return;
        }

        $('#memberAttendanceModal .modal-title').text('Edit Attendance');
        showMemberModalLoading('Loading attendance form...');
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
                $('#memberAttendanceModalBody').html(response);
            },
            error: function() {
                $('#memberAttendanceModalBody').html('<div class="alert alert-danger m-4">Error loading attendance form.</div>');
            }
        });
    });

    $(document).off('submit.memberAttendanceForm', '#memberAttendanceModal .attendance-form')
        .on('submit.memberAttendanceForm', '#memberAttendanceModal .attendance-form', function(e) {
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
                closeMemberAttendanceModal();
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

    $(document).off('click.memberAttendanceModalClose', '[data-member-attendance-modal-close]')
        .on('click.memberAttendanceModalClose', '[data-member-attendance-modal-close]', function(e) {
        e.preventDefault();
        closeMemberAttendanceModal();
    });

    $(document).off('change.memberWorkFrom', '#memberAttendanceModal #work_from_type')
        .on('change.memberWorkFrom', '#memberAttendanceModal #work_from_type', function() {
        $('#memberAttendanceModal #other_location_div').toggleClass('hidden', this.value !== 'other');
    });

    $(document).off('change.memberStatusSync', '#memberAttendanceModal select[name="status"]')
        .on('change.memberStatusSync', '#memberAttendanceModal select[name="status"]', function() {
        const value = this.value;
        $('#memberAttendanceModal input[name="late"][value="' + (value === 'late' ? 'yes' : 'no') + '"]').prop('checked', true);
        $('#memberAttendanceModal input[name="half_day"][value="' + (value === 'half_day' ? 'yes' : 'no') + '"]').prop('checked', true);
    });

    table.on('draw', updateMemberSelection);
    updateMemberSelection();

    function showMemberNotification(message, type = 'info') {
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

    window.exportMemberAttendance = function(format) {
        if (!$.fn.DataTable.isDataTable(tableSelector)) {
            showMemberNotification('The attendance table is not ready yet.', 'warning');
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
            showMemberNotification('The selected export option is currently unavailable.', 'danger');
            return;
        }

        table.button(selector).trigger();

        if (format !== 'copy' && format !== 'print') {
            const selectedTotal = checkedRows().length;
            showMemberNotification(
                selectedTotal > 0
                    ? format.toUpperCase() + ' export started for selected members.'
                    : format.toUpperCase() + ' export started for all filtered members.',
                'success'
            );
        }
    };
});
</script>
@endpush
