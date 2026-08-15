@extends('layouts.superadmin')

@section('title', 'Developer Management Center - Super Admin')

@push('styles')
<style>
    /* CSS Tokens & Custom Styling */
    :root {
        --primary: #0f744c;
        --primary-hover: #073a26;
        --primary-light: #e4f3eb;
        --primary-border: #a7f3d0;
        --blue-accent: #2563eb;
        --blue-light: #eff6ff;
        --purple-accent: #7c3aed;
        --purple-light: #f5f3ff;
        --warning: #f59e0b;
        --warning-light: #fffbeb;
        --danger: #ef4444;
        --danger-light: #fef2f2;
        --slate-dark: #0f172a;
        --slate-body: #334155;
        --slate-muted: #64748b;
        --slate-subtle: #94a3b8;
        --bg-surface: #ffffff;
        --bg-subtle: #f8fafc;
        --border-color: #cbd5e1;
        --radius-xl: 16px;
        --radius-lg: 12px;
        --radius-md: 8px;
        --radius-sm: 6px;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(15, 23, 42, 0.06), 0 2px 4px -1px rgba(15, 23, 42, 0.04);
        --shadow-lg: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
    }

    .dev-container {
        padding: 24px 32px 48px;
        max-width: 1680px;
        margin: 0 auto;
    }

    /* Page Header */
    .page-header-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
        background: var(--bg-surface);
        padding: 20px 24px;
        border-radius: var(--radius-xl);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
    }

    .breadcrumbs-bar {
        font-size: 12px;
        font-weight: 600;
        color: var(--slate-muted);
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .breadcrumbs-bar a {
        color: var(--slate-muted);
        text-decoration: none;
    }
    .breadcrumbs-bar a:hover {
        color: var(--primary);
    }

    .page-title {
        font-size: 24px;
        font-weight: 800;
        color: var(--slate-dark);
        margin: 0;
        letter-spacing: -0.5px;
    }

    .page-subtitle {
        font-size: 13.5px;
        color: var(--slate-muted);
        margin-top: 4px;
        margin-bottom: 0;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Buttons */
    .btn-action-primary {
        background: var(--primary);
        color: #ffffff;
        border: 1px solid var(--primary-hover);
        border-radius: var(--radius-md);
        padding: 9px 16px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(15, 116, 76, 0.2);
    }

    .btn-action-primary:hover {
        background: var(--primary-hover);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(15, 116, 76, 0.3);
    }

    .btn-action-secondary {
        background: var(--bg-surface);
        color: var(--slate-body);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 9px 15px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-action-secondary:hover {
        background: var(--bg-subtle);
        border-color: var(--slate-subtle);
    }

    /* KPI Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .kpi-card {
        background: var(--bg-surface);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        padding: 16px 20px;
        box-shadow: var(--shadow-sm);
        transition: all 0.2s;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary-border);
    }

    .kpi-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .kpi-title {
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--slate-muted);
    }

    .kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .kpi-val {
        font-size: 26px;
        font-weight: 800;
        color: var(--slate-dark);
        line-height: 1.2;
        margin-bottom: 4px;
    }

    .kpi-sub {
        font-size: 11.5px;
        color: var(--slate-muted);
        display: flex;
        align-items: center;
        gap: 4px;
        font-weight: 500;
    }

    /* Toolbar & Filters */
    .filter-card {
        background: var(--bg-surface);
        border-radius: var(--radius-xl);
        border: 1px solid var(--border-color);
        padding: 18px 22px;
        margin-bottom: 24px;
        box-shadow: var(--shadow-sm);
    }

    .filter-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
    }

    .search-group {
        flex: 1;
        min-width: 280px;
        position: relative;
    }

    .search-group input {
        width: 100%;
        padding: 9px 14px 9px 38px;
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 13px;
        color: var(--slate-dark);
        outline: none;
        transition: all 0.2s;
    }

    .search-group input:focus {
        background: #ffffff;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(15, 116, 76, 0.15);
    }

    .search-group i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--slate-muted);
        font-size: 18px;
    }

    .filter-selects {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-selects select {
        padding: 9px 12px;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 12.5px;
        font-weight: 600;
        color: var(--slate-body);
        outline: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-selects select:focus {
        border-color: var(--primary);
    }

    /* Table Component - Grid Row & Column Separated */
    .table-card {
        background: var(--bg-surface);
        border-radius: var(--radius-xl);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 32px;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .enterprise-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        text-align: left;
        border: 1px solid var(--border-color);
        background: var(--bg-surface);
    }

    .enterprise-table th {
        background: #f1f5f9;
        padding: 13px 16px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: var(--slate-dark);
        border-bottom: 1px solid var(--border-color);
        border-right: 1px solid var(--border-color);
        white-space: nowrap;
    }
    .enterprise-table th:last-child {
        border-right: none;
    }

    .enterprise-table td {
        padding: 14px 16px;
        font-size: 13px;
        color: var(--slate-dark);
        border-bottom: 1px solid var(--border-color);
        border-right: 1px solid var(--border-color);
        vertical-align: middle;
    }
    .enterprise-table td:last-child {
        border-right: none;
    }
    .enterprise-table tbody tr:last-child td {
        border-bottom: none;
    }

    .enterprise-table tr:hover td {
        background: #f8fafc;
    }

    .enterprise-table tr.selected-row td {
        background: var(--primary-light) !important;
    }

    /* Custom Checkbox Input */
    .custom-checkbox {
        width: 16px;
        height: 16px;
        accent-color: var(--primary);
        cursor: pointer;
        border-radius: 4px;
        vertical-align: middle;
    }

    /* Entries Selector Dropdown */
    .entries-selector {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        color: var(--slate-muted);
        font-weight: 600;
    }
    .entries-selector select {
        padding: 5px 10px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 12px;
        font-weight: 700;
        color: var(--slate-dark);
        background: #ffffff;
        outline: none;
        cursor: pointer;
    }

    /* Export Dropdown Component */
    .export-dropdown-wrap {
        position: relative;
        display: inline-block;
    }

    .export-dropdown-menu {
        position: absolute;
        top: calc(100% + 6px);
        right: 0;
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
        min-width: 170px;
        z-index: 100;
        display: none;
        padding: 6px 0;
    }
    .export-dropdown-menu.show {
        display: block;
        animation: fadeIn 0.15s ease;
    }
    .export-dropdown-menu a {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--slate-body);
        text-decoration: none;
        transition: background 0.15s;
    }
    .export-dropdown-menu a:hover {
        background: #f1f5f9;
        color: var(--slate-dark);
    }

    /* Floating Bulk Actions Bar */
    .bulk-actions-bar {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: #0f172a;
        color: #ffffff;
        padding: 12px 24px;
        border-radius: 40px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        gap: 16px;
        z-index: 999;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .bulk-actions-bar.active {
        transform: translateX(-50%) translateY(0);
    }

    /* Laravel Pagination Styling Reset & Custom Formatting */
    .pagination-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .pagination-wrap nav {
        display: flex;
        align-items: center;
    }
    .pagination-wrap nav > div:first-child {
        display: none !important;
    }
    .pagination-wrap nav > div:last-child {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .pagination-wrap nav span[aria-label], 
    .pagination-wrap nav a[aria-label] {
        display: none !important;
    }
    .pagination-wrap nav svg {
        width: 14px !important;
        height: 14px !important;
        max-width: 14px !important;
        max-height: 14px !important;
        flex-shrink: 0;
    }
    .pagination-wrap nav span.relative,
    .pagination-wrap nav a.relative {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 32px !important;
        height: 32px !important;
        padding: 0 10px !important;
        border-radius: var(--radius-md) !important;
        border: 1px solid var(--border-color) !important;
        background: #ffffff !important;
        color: var(--slate-dark) !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
        text-decoration: none !important;
        box-shadow: none !important;
        margin: 0 2px !important;
    }
    .pagination-wrap nav span[aria-current="page"] span.relative {
        background: var(--primary) !important;
        color: #ffffff !important;
        border-color: var(--primary) !important;
    }
    .pagination-wrap nav a.relative:hover {
        background: var(--primary-light) !important;
        color: var(--primary) !important;
        border-color: var(--primary-border) !important;
    }

    /* Badges */
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
    }

    .badge-available { background: var(--primary-light); color: var(--primary); border: 1px solid var(--primary-border); }
    .badge-busy { background: var(--warning-light); color: #d97706; border: 1px solid #fde68a; }
    .badge-onleave { background: var(--purple-light); color: var(--purple-accent); border: 1px solid #ddd6fe; }
    .badge-inactive { background: #f1f5f9; color: var(--slate-muted); border: 1px solid #cbd5e1; }

    .skill-tag {
        display: inline-block;
        background: var(--bg-subtle);
        color: var(--slate-body);
        border: 1px solid var(--border-color);
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        margin-right: 4px;
        margin-bottom: 4px;
    }

    /* Capacity Progress Bar */
    .capacity-bar-wrap {
        width: 100px;
        height: 6px;
        background: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        margin-top: 4px;
    }
    .capacity-bar-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.3s;
    }

    /* Slide-Over Drawer */
    .drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(4px);
        z-index: 1000;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    .drawer-overlay.active {
        opacity: 1;
        pointer-events: auto;
    }

    .drawer-panel {
        position: fixed;
        top: 0;
        right: 0;
        width: 520px;
        max-width: 90vw;
        height: 100vh;
        background: #ffffff;
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.15);
        z-index: 1001;
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
    }
    .drawer-overlay.active .drawer-panel {
        transform: translateX(0);
    }

    .drawer-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
    }

    .drawer-body {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
    }

    /* Modals */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(4px);
        z-index: 1100;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }
    .modal-overlay.active {
        opacity: 1;
        pointer-events: auto;
    }

    .modal-box {
        background: #ffffff;
        border-radius: var(--radius-xl);
        width: 600px;
        max-width: 92vw;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
        transform: translateY(20px);
        transition: transform 0.25s ease;
    }
    .modal-overlay.active .modal-box {
        transform: translateY(0);
    }

    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
    }

    .modal-body {
        padding: 24px;
    }

    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        background: #f8fafc;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="dev-container">

    <!-- PAGE HEADER -->
    <div class="page-header-box">
        <div>
            <div class="breadcrumbs-bar">
                <a href="{{ Route::has('superadmin.dashboard') ? route('superadmin.dashboard') : route('super-admin.companies.index') }}">Super Admin</a>
                <i class="bx bx-chevron-right"></i>
                <a href="#">Security &amp; Audit</a>
                <i class="bx bx-chevron-right"></i>
                <span>Developer Management</span>
            </div>
            <h1 class="page-title">Developer Management</h1>
            <p class="page-subtitle">Manage developers, workloads, assignments, and development activities across the platform.</p>
        </div>

        <div class="header-actions">
            <a href="{{ route('developer.dashboard') }}" target="_blank" class="btn-action-secondary" style="text-decoration: none;">
                <i class="bx bx-laptop" style="font-size: 16px; color: var(--primary);"></i> Developer Portal
            </a>
            <button class="btn-action-secondary" onclick="window.location.reload();">
                <i class="bx bx-refresh" style="font-size: 16px;"></i> Refresh
            </button>
            <button class="btn-action-secondary" onclick="openAssignModal();">
                <i class="bx bx-send" style="font-size: 16px; color: var(--blue-accent);"></i> Assign Work
            </button>
            <button class="btn-action-primary" onclick="openAddDeveloperModal();">
                <i class="bx bx-plus-circle" style="font-size: 18px;"></i> Add Developer
            </button>
        </div>
    </div>

    <!-- KPI SUMMARY CARDS -->
    <div class="kpi-grid">
        <!-- TOTAL DEVELOPERS -->
        <div class="kpi-card" onclick="filterByStatus('all')">
            <div class="kpi-header">
                <span class="kpi-title">Total Developers</span>
                <div class="kpi-icon" style="background: var(--blue-light); color: var(--blue-accent);">
                    <i class="bx bx-code-block"></i>
                </div>
            </div>
            <div class="kpi-val">{{ $kpis['total'] }}</div>
            <div class="kpi-sub"><i class="bx bx-user-check" style="color: var(--primary);"></i> Registered Talent</div>
        </div>

        <!-- ACTIVE DEVELOPERS -->
        <div class="kpi-card" onclick="filterByStatus('active')">
            <div class="kpi-header">
                <span class="kpi-title">Active Developers</span>
                <div class="kpi-icon" style="background: var(--primary-light); color: var(--primary);">
                    <i class="bx bx-user-check"></i>
                </div>
            </div>
            <div class="kpi-val">{{ $kpis['active'] }}</div>
            <div class="kpi-sub"><i class="bx bx-check-double" style="color: var(--primary);"></i> Account Active</div>
        </div>

        <!-- AVAILABLE -->
        <div class="kpi-card" onclick="filterByStatus('available')">
            <div class="kpi-header">
                <span class="kpi-title">Available</span>
                <div class="kpi-icon" style="background: #ecfdf5; color: #059669;">
                    <i class="bx bx-check-circle"></i>
                </div>
            </div>
            <div class="kpi-val">{{ $kpis['available'] }}</div>
            <div class="kpi-sub"><i class="bx bx-time" style="color: #059669;"></i> Ready for Tasks</div>
        </div>

        <!-- CURRENTLY WORKING / BUSY -->
        <div class="kpi-card" onclick="filterByStatus('busy')">
            <div class="kpi-header">
                <span class="kpi-title">Currently Working</span>
                <div class="kpi-icon" style="background: var(--warning-light); color: #d97706;">
                    <i class="bx bx-briefcase-alt-2"></i>
                </div>
            </div>
            <div class="kpi-val">{{ $kpis['busy'] }}</div>
            <div class="kpi-sub"><i class="bx bx-trending-up" style="color: #d97706;"></i> High Workload</div>
        </div>

        <!-- ACTIVE ASSIGNMENTS -->
        <div class="kpi-card" onclick="scrollToHistory()">
            <div class="kpi-header">
                <span class="kpi-title">Active Assignments</span>
                <div class="kpi-icon" style="background: var(--purple-light); color: var(--purple-accent);">
                    <i class="bx bx-task"></i>
                </div>
            </div>
            <div class="kpi-val">{{ $kpis['assignments'] }}</div>
            <div class="kpi-sub"><i class="bx bx-git-branch" style="color: var(--purple-accent);"></i> Across Platform</div>
        </div>

        <!-- OVERDUE TASKS -->
        <div class="kpi-card" onclick="scrollToHistory()">
            <div class="kpi-header">
                <span class="kpi-title">Overdue Tasks</span>
                <div class="kpi-icon" style="background: var(--danger-light); color: var(--danger);">
                    <i class="bx bx-time-five"></i>
                </div>
            </div>
            <div class="kpi-val" style="color: var(--danger);">{{ $kpis['overdue'] }}</div>
            <div class="kpi-sub"><i class="bx bx-error" style="color: var(--danger);"></i> Action Needed</div>
        </div>

        <!-- COMPLETED THIS MONTH -->
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Completed (Month)</span>
                <div class="kpi-icon" style="background: #f0fdf4; color: #16a34a;">
                    <i class="bx bx-trophy"></i>
                </div>
            </div>
            <div class="kpi-val">{{ $kpis['completed_month'] }}</div>
            <div class="kpi-sub"><i class="bx bx-check-shield" style="color: #16a34a;"></i> Delivered Tasks</div>
        </div>
    </div>

    <!-- SEARCH AND FILTERS TOOLBAR -->
    <div class="filter-card">
        <form method="GET" action="{{ route('super-admin.developers.index') }}" id="devFilterForm">
            <input type="hidden" name="per_page" id="perPageHiddenInput" value="{{ request('per_page', 10) }}">
            <div class="filter-row">
                <!-- SEARCH -->
                <div class="search-group">
                    <i class="bx bx-search"></i>
                    <input type="text" name="admin_search" id="devSearchInput" value="{{ request('admin_search', request('search')) }}" placeholder="Search developer name, email, skill, technology..." />
                </div>

                <!-- FILTERS -->
                <div class="filter-selects">
                    <!-- STATUS -->
                    <select name="status" onchange="document.getElementById('devFilterForm').submit();">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Status: All</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="busy" {{ request('status') == 'busy' ? 'selected' : '' }}>Busy</option>
                        <option value="on_leave" {{ request('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    <!-- ROLE -->
                    <select name="role" onchange="document.getElementById('devFilterForm').submit();">
                        <option value="all" {{ request('role') == 'all' ? 'selected' : '' }}>Role: All</option>
                        <option value="Frontend Developer" {{ request('role') == 'Frontend Developer' ? 'selected' : '' }}>Frontend Developer</option>
                        <option value="Backend Developer" {{ request('role') == 'Backend Developer' ? 'selected' : '' }}>Backend Developer</option>
                        <option value="Full Stack Developer" {{ request('role') == 'Full Stack Developer' ? 'selected' : '' }}>Full Stack Developer</option>
                        <option value="DevOps" {{ request('role') == 'DevOps' ? 'selected' : '' }}>DevOps</option>
                        <option value="QA Engineer" {{ request('role') == 'QA Engineer' ? 'selected' : '' }}>QA Engineer</option>
                        <option value="UI/UX Developer" {{ request('role') == 'UI/UX Developer' ? 'selected' : '' }}>UI/UX Developer</option>
                    </select>

                    <!-- WORKLOAD -->
                    <select name="workload" onchange="document.getElementById('devFilterForm').submit();">
                        <option value="all" {{ request('workload') == 'all' ? 'selected' : '' }}>Workload: All</option>
                        <option value="available" {{ request('workload') == 'available' ? 'selected' : '' }}>Available (0-15h)</option>
                        <option value="light" {{ request('workload') == 'light' ? 'selected' : '' }}>Light (1-2 Tasks)</option>
                        <option value="medium" {{ request('workload') == 'medium' ? 'selected' : '' }}>Medium (3-4 Tasks)</option>
                        <option value="heavy" {{ request('workload') == 'heavy' ? 'selected' : '' }}>Heavy (5+ Tasks)</option>
                    </select>

                    <a href="{{ route('super-admin.developers.index') }}" class="btn-action-secondary" style="padding: 9px 12px; font-size: 12px;">
                        <i class="bx bx-x"></i> Clear Filters
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- DEVELOPER DIRECTORY TABLE (ROW & COLUMN SEPARATED GRID) -->
    <div class="table-card">
        <div style="padding: 14px 20px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; background: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="bx bx-user-pin" style="font-size: 20px; color: var(--primary);"></i>
                    <h3 style="font-size: 15px; font-weight: 800; color: var(--slate-dark); margin: 0;">Developer Directory</h3>
                    <span style="background: var(--primary-light); color: var(--primary); padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">
                        {{ $paginatedDevs->total() }} Developers
                    </span>
                </div>

                <!-- SHOW ENTRIES FUNCTIONALITY -->
                <div class="entries-selector">
                    <span>Show</span>
                    <select id="devEntriesSelect" onchange="changeDevEntries(this.value)">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="all" {{ request('per_page') == 'all' || request('per_page') == 500 ? 'selected' : '' }}>All</option>
                    </select>
                    <span>entries</span>
                </div>
            </div>

            <!-- EXPORT DROPDOWN IN TOOLBAR -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="export-dropdown-wrap">
                    <button type="button" class="btn-action-secondary" onclick="toggleExportDropdown('devExportMenu', event)" style="padding: 7px 14px; font-size: 12.5px;">
                        <i class="bx bx-download" style="font-size: 16px; color: var(--primary);"></i> Export <i class="bx bx-chevron-down"></i>
                    </button>
                    <div id="devExportMenu" class="export-dropdown-menu">
                        <a href="javascript:void(0)" onclick="exportDevelopersCSV()"><i class="bx bx-file-blank" style="color: #059669;"></i> Export as CSV</a>
                        <a href="javascript:void(0)" onclick="exportDevelopersPDF()"><i class="bx bxs-file-pdf" style="color: #dc2626;"></i> Export as PDF</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="enterprise-table" id="developersGridTable">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="selectAllDevs" class="custom-checkbox" onchange="toggleAllDevCheckboxes(this)" title="Select All Rows">
                        </th>
                        <th>Developer</th>
                        <th>Role</th>
                        <th>Registered Email</th>
                        <th>Skills &amp; Tech</th>
                        <th>Status</th>
                        <th>Active Tasks</th>
                        <th>Workload Capacity</th>
                        <th>Last Active</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedDevs as $dev)
                    <tr class="dev-table-row" data-id="{{ $dev->id }}" data-email="{{ $dev->email }}" data-name="{{ $dev->name }}" data-role="{{ $dev->role_title }}" data-status="{{ $dev->dev_status }}">
                        <!-- CHECKBOX -->
                        <td style="width: 40px; text-align: center;">
                            <input type="checkbox" class="dev-row-cb custom-checkbox" value="{{ $dev->id }}" data-id="{{ $dev->id }}" data-name="{{ $dev->name }}" data-email="{{ $dev->email }}" data-role="{{ $dev->role_title }}" data-status="{{ $dev->dev_status }}" onchange="updateDevSelectionState()">
                        </td>

                        <!-- DEVELOPER -->
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #1e293b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0;">
                                    {{ strtoupper(substr($dev->name, 0, 2)) }}
                                </div>
                                <div>
                                    <strong style="color: var(--slate-dark); font-size: 14px; display: block;">{{ $dev->name }}</strong>
                                    <span style="font-size: 11.5px; color: var(--slate-muted);">DEV-{{ str_pad($dev->id, 3, '0', STR_PAD_LEFT) }} · {{ $dev->company ? $dev->company->name : 'Platform Central' }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- ROLE -->
                        <td>
                            <span style="font-weight: 700; color: var(--slate-body); font-size: 12.5px; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="bx bx-code-alt" style="color: var(--primary);"></i> {{ $dev->role_title }}
                            </span>
                        </td>

                        <!-- EMAIL -->
                        <td>
                            <strong style="color: var(--blue-accent); font-family: monospace; font-size: 12.5px;">{{ $dev->email }}</strong>
                        </td>

                        <!-- SKILLS -->
                        <td>
                            <div style="max-width: 220px;">
                                @foreach(array_slice($dev->skills_list, 0, 3) as $skill)
                                    <span class="skill-tag">{{ $skill }}</span>
                                @endforeach
                                @if(count($dev->skills_list) > 3)
                                    <span style="font-size: 10.5px; color: var(--slate-muted); font-weight: 600;">+{{ count($dev->skills_list) - 3 }} more</span>
                                @endif
                            </div>
                        </td>

                        <!-- STATUS -->
                        <td>
                            @if($dev->dev_status === 'Available')
                                <span class="badge-status badge-available"><i class="bx bx-check-circle"></i> Available</span>
                            @elseif($dev->dev_status === 'Busy')
                                <span class="badge-status badge-busy"><i class="bx bx-briefcase"></i> Busy</span>
                            @elseif($dev->dev_status === 'On Leave')
                                <span class="badge-status badge-onleave"><i class="bx bx-time"></i> On Leave</span>
                            @else
                                <span class="badge-status badge-inactive"><i class="bx bx-minus-circle"></i> Inactive</span>
                            @endif
                        </td>

                        <!-- ACTIVE TASKS -->
                        <td>
                            <span style="font-size: 12.5px; font-weight: 700; color: var(--slate-dark);">
                                {{ $dev->active_tasks_count }} Active Tasks
                            </span>
                        </td>

                        <!-- WORKLOAD CAPACITY -->
                        <td>
                            <div>
                                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; font-weight: 700; color: var(--slate-muted);">
                                    <span>{{ $dev->workload_category }}</span>
                                    <span>{{ $dev->estimate_hours_total }}h / 40h</span>
                                </div>
                                <div class="capacity-bar-wrap">
                                    <div class="capacity-bar-fill" style="width: {{ $dev->capacity_percentage }}%; background: {{ $dev->capacity_percentage > 85 ? 'var(--danger)' : ($dev->capacity_percentage > 60 ? 'var(--warning)' : 'var(--primary)') }};"></div>
                                </div>
                            </div>
                        </td>

                        <!-- LAST ACTIVE -->
                        <td>
                            <span style="font-size: 12px; color: var(--slate-muted);">
                                {{ $dev->updated_at ? $dev->updated_at->diffForHumans() : '5 mins ago' }}
                            </span>
                        </td>

                        <!-- ACTIONS -->
                        <td style="text-align: right;">
                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 6px;">
                                <form method="POST" action="{{ route('super-admin.developers.enter-workspace', $dev->id) }}" style="display: inline;" onsubmit="return confirmWorkspacePreview('{{ addslashes($dev->name) }}', event, this);">
                                    @csrf
                                    <button type="submit" class="btn-action-secondary" style="padding: 6px 12px; font-size: 12px; color: #6366f1; border-color: #c7d2fe;" title="Enter Workspace as Super Admin">
                                        <i class="bx bx-laptop"></i> Workspace
                                    </button>
                                </form>
                                <button class="btn-action-secondary" onclick="openDevDrawer('{{ $dev->id }}')" style="padding: 6px 12px; font-size: 12px;">
                                    <i class="bx bx-show"></i> View
                                </button>
                                <button class="btn-action-secondary" onclick="quickAssignWork('{{ $dev->email }}')" style="padding: 6px 10px; font-size: 12px; color: var(--blue-accent);" title="Assign Work">
                                    <i class="bx bx-send"></i>
                                </button>
                                <button class="btn-action-secondary" onclick="openEditDeveloperModal('{{ $dev->id }}')" style="padding: 6px 10px; font-size: 12px;" title="Edit Profile">
                                    <i class="bx bx-edit-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 48px 20px;">
                            <i class="bx bx-code-block" style="font-size: 48px; color: var(--slate-subtle); margin-bottom: 12px;"></i>
                            <h4 style="font-size: 16px; font-weight: 700; color: var(--slate-dark); margin: 0 0 6px 0;">No Developers Found</h4>
                            <p style="font-size: 13px; color: var(--slate-muted); margin: 0 0 16px 0;">Add developers to start assigning development work.</p>
                            <button class="btn-action-primary" onclick="openAddDeveloperModal();">
                                <i class="bx bx-plus-circle"></i> Add Developer
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <div style="padding: 14px 20px; border-top: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; background: #ffffff;">
            <div style="color: var(--slate-muted); font-size: 12.5px; font-weight: 600;">
                Showing {{ $paginatedDevs->firstItem() ?? 0 }} to {{ $paginatedDevs->lastItem() ?? 0 }} of {{ $paginatedDevs->total() }} developers
            </div>
            <div class="pagination-wrap">{{ $paginatedDevs->appends(request()->query())->onEachSide(1)->links() }}</div>
        </div>
    </div>

    <!-- ASSIGNMENT HISTORY SECTION (ROW & COLUMN SEPARATED GRID) -->
    <div class="table-card" id="assignmentHistorySection">
        <div style="padding: 14px 20px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; background: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="bx bx-history" style="font-size: 20px; color: var(--purple-accent);"></i>
                    <h3 style="font-size: 15px; font-weight: 800; color: var(--slate-dark); margin: 0;">Assignment History &amp; Task Telemetry</h3>
                </div>

                <!-- SHOW ENTRIES FUNCTIONALITY FOR HISTORY -->
                <div class="entries-selector">
                    <span>Show</span>
                    <select id="historyEntriesSelect" onchange="filterHistoryEntries(this.value)">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="all">All</option>
                    </select>
                    <span>entries</span>
                </div>
            </div>

            <!-- EXPORT DROPDOWN FOR HISTORY -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="export-dropdown-wrap">
                    <button type="button" class="btn-action-secondary" onclick="toggleExportDropdown('historyExportMenu', event)" style="padding: 7px 14px; font-size: 12.5px;">
                        <i class="bx bx-download" style="font-size: 16px; color: var(--purple-accent);"></i> Export <i class="bx bx-chevron-down"></i>
                    </button>
                    <div id="historyExportMenu" class="export-dropdown-menu">
                        <a href="javascript:void(0)" onclick="exportHistoryCSV()"><i class="bx bx-file-blank" style="color: #059669;"></i> Export as CSV</a>
                        <a href="javascript:void(0)" onclick="exportHistoryPDF()"><i class="bx bxs-file-pdf" style="color: #dc2626;"></i> Export as PDF</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="enterprise-table" id="assignmentsGridTable">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="selectAllHistory" class="custom-checkbox" onchange="toggleAllHistoryCheckboxes(this)" title="Select All History Rows">
                        </th>
                        <th>Task Title</th>
                        <th>Assigned Developer</th>
                        <th>Company</th>
                        <th>Assigned By</th>
                        <th>Date</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignmentHistory as $history)
                    <tr class="history-table-row" data-id="{{ $history->id }}" data-title="{{ $history->title }}" data-developer="{{ $history->developer_name }}" data-email="{{ $history->developer_email }}" data-company="{{ $history->company_name }}" data-priority="{{ $history->priority }}" data-status="{{ $history->status }}">
                        <!-- CHECKBOX -->
                        <td style="width: 40px; text-align: center;">
                            <input type="checkbox" class="history-row-cb custom-checkbox" value="{{ $history->id }}" data-id="{{ $history->id }}" data-title="{{ $history->title }}" data-developer="{{ $history->developer_name }}" data-email="{{ $history->developer_email }}" data-company="{{ $history->company_name }}" data-priority="{{ $history->priority }}" data-status="{{ $history->status }}" onchange="updateHistorySelectionState()">
                        </td>

                        <td>
                            <strong style="color: var(--slate-dark); font-size: 13px;">{{ $history->title }}</strong>
                            <span style="display: block; font-size: 11px; color: var(--slate-muted);">Est: {{ $history->estimate_hours ?? 8 }}h</span>
                        </td>
                        <td>
                            <strong style="color: var(--slate-dark); font-size: 12.5px;">{{ $history->developer_name ?: 'Developer' }}</strong>
                            <span style="display: block; font-size: 11px; color: var(--blue-accent); font-family: monospace;">{{ $history->developer_email }}</span>
                        </td>
                        <td>
                            <span style="font-size: 12.5px; font-weight: 600; color: var(--slate-body);">{{ $history->company_name ?: 'Platform Central' }}</span>
                        </td>
                        <td>
                            <span style="font-size: 12px; color: var(--slate-muted);">{{ $history->assigner_name ?: 'Super Admin' }}</span>
                        </td>
                        <td>
                            <span style="font-size: 12px; color: var(--slate-muted);">{{ \Carbon\Carbon::parse($history->created_at)->format('M d, Y') }}</span>
                        </td>
                        <td>
                            @php
                                $prio = strtolower($history->priority ?? 'medium');
                                $prioStyle = $prio === 'critical' ? 'background: #fef2f2; color: #ef4444; border: 1px solid #fecaca;' : ($prio === 'high' ? 'background: #fffbeb; color: #d97706; border: 1px solid #fde68a;' : 'background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0;');
                            @endphp
                            <span style="padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 800; text-transform: uppercase; {{ $prioStyle }}">
                                {{ $prio }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-status {{ $history->status === 'completed' ? 'badge-available' : ($history->status === 'in_progress' ? 'badge-busy' : 'badge-inactive') }}">
                                {{ ucfirst(str_replace('_', ' ', $history->status)) }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <form method="POST" action="{{ route('super-admin.developers.task-status', $history->id) }}" style="display: inline-block;">
                                @csrf
                                <select name="status" onchange="this.form.submit();" style="padding: 4px 8px; font-size: 11px; border-radius: 6px; border: 1px solid var(--border-color); cursor: pointer;">
                                    <option value="assigned" {{ $history->status === 'assigned' ? 'selected' : '' }}>Assigned</option>
                                    <option value="in_progress" {{ $history->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="on_hold" {{ $history->status === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                    <option value="completed" {{ $history->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $history->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 24px; color: var(--slate-muted);">No assignment history available.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- FLOATING BULK ACTIONS TOOLBAR -->
<div id="bulkActionsBar" class="bulk-actions-bar">
    <div style="font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 8px;">
        <span id="bulkSelectedCount" style="background: var(--primary); color: #ffffff; padding: 2px 8px; border-radius: 10px; font-size: 12px;">0 Selected</span>
        <span>Developer(s) Selected</span>
    </div>
    <div style="display: flex; align-items: center; gap: 8px;">
        <button type="button" class="btn-action-secondary" onclick="exportSelectedDevelopersCSV()" style="padding: 6px 12px; font-size: 12px; background: #1e293b; color: #ffffff; border-color: #334155;">
            <i class="bx bx-file-blank" style="color: #10b981;"></i> Export CSV
        </button>
        <button type="button" class="btn-action-secondary" onclick="exportSelectedDevelopersPDF()" style="padding: 6px 12px; font-size: 12px; background: #1e293b; color: #ffffff; border-color: #334155;">
            <i class="bx bxs-file-pdf" style="color: #ef4444;"></i> Export PDF
        </button>
        <button type="button" class="btn-action-secondary" onclick="assignWorkToSelected()" style="padding: 6px 12px; font-size: 12px; background: var(--primary); color: #ffffff; border: none;">
            <i class="bx bx-send"></i> Assign Work
        </button>
        <button type="button" onclick="clearDevSelection()" style="background: transparent; border: none; color: #94a3b8; cursor: pointer; font-size: 18px; margin-left: 4px;" title="Clear Selection">
            <i class="bx bx-x"></i>
        </button>
    </div>
</div>

<!-- SLIDE-OVER DEVELOPER PROFILE DRAWER -->
<div class="drawer-overlay" id="devProfileDrawer">
    <div class="drawer-panel">
        <div class="drawer-header">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: #1e293b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px;" id="drawerDevAvatar">
                    RD
                </div>
                <div>
                    <h3 style="font-size: 17px; font-weight: 800; color: var(--slate-dark); margin: 0;" id="drawerDevName">Rahul Sharma</h3>
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                        <span style="font-size: 12px; font-weight: 700; color: var(--primary);" id="drawerDevRole">Backend Developer</span>
                        <span style="font-size: 11px; color: var(--slate-muted);" id="drawerDevCompany">TechEdFest</span>
                    </div>
                </div>
            </div>
            <button onclick="closeDevDrawer()" style="background: none; border: none; font-size: 22px; color: var(--slate-muted); cursor: pointer;">
                <i class="bx bx-x"></i>
            </button>
        </div>

        <div class="drawer-body">
            <!-- REGISTERED EMAIL IDENTIFIER -->
            <div style="background: var(--blue-light); border: 1px solid #bfdbfe; border-radius: var(--radius-md); padding: 14px 16px; margin-bottom: 20px;">
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--blue-accent); margin-bottom: 4px;">Registered Email Address</div>
                <div style="font-family: monospace; font-size: 14px; font-weight: 700; color: var(--slate-dark);" id="drawerDevEmail">rahul@company.com</div>
                <div style="font-size: 11.5px; color: var(--slate-muted); margin-top: 4px;">Used by Super Admin for work allocation &amp; automated notification routing.</div>
            </div>

            <!-- WORKLOAD METRICS -->
            <div style="background: var(--bg-subtle); border-radius: var(--radius-md); padding: 16px; margin-bottom: 20px; border: 1px solid var(--border-color);">
                <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--slate-muted); margin-bottom: 10px;">Capacity &amp; Workload</div>
                <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; margin-bottom: 6px;">
                    <span id="drawerDevWorkloadCategory">Light Workload</span>
                    <span id="drawerDevHours">16h / 40h</span>
                </div>
                <div class="capacity-bar-wrap" style="width: 100%; height: 8px;">
                    <div class="capacity-bar-fill" id="drawerCapacityBar" style="width: 40%; background: var(--primary);"></div>
                </div>
            </div>

            <!-- TECHNICAL SKILLS -->
            <div style="margin-bottom: 20px;">
                <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--slate-muted); margin-bottom: 8px;">Technical Stack &amp; Skills</div>
                <div id="drawerDevSkillsContainer"></div>
            </div>

            <!-- CURRENT ACTIVE ASSIGNMENTS -->
            <div style="margin-bottom: 20px;">
                <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--slate-muted); margin-bottom: 8px;">Active Work Assignments</div>
                <div id="drawerDevAssignmentsList"></div>
            </div>

            <!-- ACTION BUTTONS IN DRAWER -->
            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 30px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                <button class="btn-action-primary" id="drawerAssignWorkBtn" onclick="closeDevDrawer(); openAssignModal();" style="justify-content: center;">
                    <i class="bx bx-send"></i> Assign Work to This Developer
                </button>
                <button class="btn-action-secondary" id="drawerEditBtn" style="justify-content: center;">
                    <i class="bx bx-edit"></i> Edit Profile &amp; Skills
                </button>
                <form id="drawerStatusForm" method="POST" action="">
                    @csrf
                    <button type="submit" class="btn-action-secondary" style="width: 100%; justify-content: center; color: var(--danger); border-color: #fecaca;">
                        <i class="bx bx-power-off"></i> Toggle Account Access
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 1: ADD / EDIT DEVELOPER MODAL -->
<div class="modal-overlay" id="developerModal">
    <div class="modal-box">
        <form id="devForm" method="POST" action="{{ route('super-admin.developers.store') }}">
            @csrf
            <input type="hidden" name="_method" id="devFormMethod" value="POST">

            <div class="modal-header">
                <h3 style="font-size: 17px; font-weight: 800; color: var(--slate-dark); margin: 0;" id="devModalTitle">Add New Developer</h3>
                <button type="button" onclick="closeDeveloperModal()" style="background: none; border: none; font-size: 22px; color: var(--slate-muted); cursor: pointer;">
                    <i class="bx bx-x"></i>
                </button>
            </div>

            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Developer Name *</label>
                        <input type="text" name="name" id="devFormName" required class="search-group" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;" placeholder="e.g. Karan Malhotra">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Registered Email Address *</label>
                        <input type="email" name="email" id="devFormEmail" required style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;" placeholder="e.g. karan@company.com">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Mobile Number</label>
                        <input type="text" name="mobile" id="devFormMobile" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;" placeholder="+91 98765 43210">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Role / Specialization *</label>
                        <select name="role" id="devFormRole" required style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;">
                            <option value="Full Stack Developer">Full Stack Developer</option>
                            <option value="Backend Developer">Backend Developer</option>
                            <option value="Frontend Developer">Frontend Developer</option>
                            <option value="DevOps Engineer">DevOps Engineer</option>
                            <option value="QA Automation Engineer">QA Automation Engineer</option>
                            <option value="UI/UX Developer">UI/UX Developer</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Assign to Tenant Company</label>
                    <select name="company_id" id="devFormCompany" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;">
                        <option value="">-- Central Platform Developer --</option>
                        @foreach($companyOptions as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->company_code ?? 'MAIN' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Skills &amp; Technology Stack (Comma Separated)</label>
                    <input type="text" name="skills" id="devFormSkills" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;" placeholder="e.g. PHP, Laravel, React, Node.js, Docker, MySQL">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-action-secondary" onclick="closeDeveloperModal()">Cancel</button>
                <button type="submit" class="btn-action-primary">Save Developer Profile</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: EMAIL-BASED WORK ALLOCATION MODAL -->
<div class="modal-overlay" id="assignWorkModal">
    <div class="modal-box" style="width: 650px;">
        <form method="POST" action="{{ route('super-admin.developers.assign-work') }}">
            @csrf
            <div class="modal-header">
                <div>
                    <h3 style="font-size: 17px; font-weight: 800; color: var(--slate-dark); margin: 0;">Assign Development Work</h3>
                    <p style="font-size: 12px; color: var(--slate-muted); margin: 2px 0 0 0;">Assign development task directly through developer's registered email address.</p>
                </div>
                <button type="button" onclick="closeAssignModal()" style="background: none; border: none; font-size: 22px; color: var(--slate-muted); cursor: pointer;">
                    <i class="bx bx-x"></i>
                </button>
            </div>

            <div class="modal-body">

                <!-- EMAIL LOOKUP FIELD -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Developer Registered Email *</label>
                    <div style="position: relative;">
                        <input type="email" name="developer_email" id="assignDevEmailInput" required onkeyup="lookupDevEmail(this.value)" style="width: 100%; padding: 10px 14px 10px 38px; border: 1px solid var(--blue-accent); border-radius: var(--radius-md); font-size: 13.5px; font-family: monospace;" placeholder="Enter developer email (e.g. rahul@company.com)">
                        <i class="bx bx-envelope" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 18px; color: var(--blue-accent);"></i>
                    </div>
                </div>

                <!-- LIVE DEVELOPER LOOKUP CARD -->
                <div id="devLookupCard" style="display: none; background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <strong style="font-size: 14px; color: var(--slate-dark);" id="lookupDevName">Rahul Sharma</strong>
                            <span style="font-size: 12px; color: var(--slate-muted); display: block;" id="lookupDevRole">Backend Developer · TechEdFest</span>
                        </div>
                        <span style="background: var(--primary-light); color: var(--primary); padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 800;" id="lookupDevWorkloadBadge">Available</span>
                    </div>

                    <!-- WORKLOAD PROTECTION WARNING ALERT -->
                    <div id="workloadAlertBanner" style="display: none; margin-top: 10px; padding: 10px 12px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; color: #b45309; font-size: 12px; font-weight: 600;">
                        <i class="bx bx-error" style="font-size: 16px; vertical-align: middle;"></i> ⚠️ Developer workload is currently heavy. Consider assigning to another available developer.
                    </div>
                </div>

                <!-- TASK DETAILS -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Task Title *</label>
                    <input type="text" name="task_title" required style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;" placeholder="e.g. Implement Webhook Payment Handler">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Task Description &amp; Technical Requirements</label>
                    <textarea name="description" rows="3" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;" placeholder="Provide brief specifications or instructions for the developer..."></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Target Company</label>
                        <select name="company_id" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;">
                            @foreach($companyOptions as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Project Context</label>
                        <select name="project_id" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;">
                            @foreach($projectOptions as $p)
                                <option value="{{ $p->id }}">{{ $p->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Priority *</label>
                        <select name="priority" required style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;">
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Due Date</label>
                        <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+3 days')) }}" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--slate-dark); margin-bottom: 6px;">Estimate (Hours)</label>
                        <input type="number" name="estimate_hours" value="8" min="1" max="200" style="width: 100%; padding: 9px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 13px;">
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-action-secondary" onclick="closeAssignModal()">Cancel</button>
                <button type="submit" class="btn-action-primary">
                    <i class="bx bx-send"></i> Assign Work &amp; Notify Developer
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const devData = @json($paginatedDevs->items());

    // Toggle Export Dropdown Menu
    function toggleExportDropdown(menuId, event) {
        if (event) event.stopPropagation();
        const targetMenu = document.getElementById(menuId);
        document.querySelectorAll('.export-dropdown-menu').forEach(menu => {
            if (menu.id !== menuId) menu.classList.remove('show');
        });
        if (targetMenu) {
            targetMenu.classList.toggle('show');
        }
    }

    // Close dropdowns on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.export-dropdown-wrap')) {
            document.querySelectorAll('.export-dropdown-menu').forEach(m => m.classList.remove('show'));
        }
    });

    // Change Entries for Developer Directory (submits form with per_page)
    function changeDevEntries(val) {
        const input = document.getElementById('perPageHiddenInput');
        if (input) input.value = val;
        const form = document.getElementById('devFilterForm');
        if (form) form.submit();
    }

    // Filter History Entries dynamically (client-side row limit)
    function filterHistoryEntries(val) {
        const historyRows = document.querySelectorAll('#assignmentsGridTable tbody tr.history-table-row');
        if (!historyRows.length) return;
        const limit = (val === 'all') ? historyRows.length : parseInt(val, 10);
        historyRows.forEach((row, idx) => {
            if (idx < limit) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Checkbox select all & update state for Developers
    function toggleAllDevCheckboxes(masterCb) {
        const devCbs = document.querySelectorAll('.dev-row-cb');
        devCbs.forEach(cb => {
            cb.checked = masterCb.checked;
            const tr = cb.closest('tr');
            if (tr) {
                if (cb.checked) tr.classList.add('selected-row');
                else tr.classList.remove('selected-row');
            }
        });
        updateDevSelectionState();
    }

    function updateDevSelectionState() {
        const selectedCbs = document.querySelectorAll('.dev-row-cb:checked');
        const allCbs = document.querySelectorAll('.dev-row-cb');
        const masterCb = document.getElementById('selectAllDevs');
        if (masterCb && allCbs.length > 0) {
            masterCb.checked = (selectedCbs.length === allCbs.length);
        }

        // Highlight rows
        document.querySelectorAll('.dev-row-cb').forEach(cb => {
            const tr = cb.closest('tr');
            if (tr) {
                if (cb.checked) tr.classList.add('selected-row');
                else tr.classList.remove('selected-row');
            }
        });

        const bulkBar = document.getElementById('bulkActionsBar');
        const countSpan = document.getElementById('bulkSelectedCount');
        if (bulkBar && countSpan) {
            if (selectedCbs.length > 0) {
                countSpan.textContent = selectedCbs.length + ' Selected';
                bulkBar.classList.add('active');
            } else {
                bulkBar.classList.remove('active');
            }
        }
    }

    function clearDevSelection() {
        document.querySelectorAll('.dev-row-cb, #selectAllDevs').forEach(cb => cb.checked = false);
        document.querySelectorAll('.dev-table-row').forEach(tr => tr.classList.remove('selected-row'));
        const bulkBar = document.getElementById('bulkActionsBar');
        if (bulkBar) bulkBar.classList.remove('active');
    }

    function confirmWorkspacePreview(devName, e, form) {
        if (e) e.preventDefault();
        if (confirm("Enter Developer Workspace?\n\nYou are about to access " + devName + "'s developer workspace as Super Admin in Preview Mode.\n\nContinue?")) {
            form.submit();
        }
        return false;
    }

    // Checkbox select all & update state for History
    function toggleAllHistoryCheckboxes(masterCb) {
        const historyCbs = document.querySelectorAll('.history-row-cb');
        historyCbs.forEach(cb => {
            cb.checked = masterCb.checked;
            const tr = cb.closest('tr');
            if (tr) {
                if (cb.checked) tr.classList.add('selected-row');
                else tr.classList.remove('selected-row');
            }
        });
    }

    function updateHistorySelectionState() {
        const selectedCbs = document.querySelectorAll('.history-row-cb:checked');
        const allCbs = document.querySelectorAll('.history-row-cb');
        const masterCb = document.getElementById('selectAllHistory');
        if (masterCb && allCbs.length > 0) {
            masterCb.checked = (selectedCbs.length === allCbs.length);
        }
        document.querySelectorAll('.history-row-cb').forEach(cb => {
            const tr = cb.closest('tr');
            if (tr) {
                if (cb.checked) tr.classList.add('selected-row');
                else tr.classList.remove('selected-row');
            }
        });
    }

    // CSV Download Helper
    function generateCSVDownload(filename, headers, rowsData) {
        let csvContent = "\uFEFF"; // UTF-8 BOM
        csvContent += headers.map(h => `"${h.replace(/"/g, '""')}"`).join(",") + "\n";
        rowsData.forEach(row => {
            csvContent += row.map(cell => `"${(cell || '').toString().replace(/"/g, '""')}"`).join(",") + "\n";
        });

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Export Developers as CSV
    function exportDevelopersCSV(onlySelected = false) {
        let targetRows = Array.from(document.querySelectorAll('#developersGridTable tbody tr.dev-table-row'));
        if (onlySelected) {
            targetRows = targetRows.filter(tr => tr.querySelector('.dev-row-cb')?.checked);
        }
        if (!targetRows.length) {
            alert('No developer records available to export.');
            return;
        }

        const headers = ['Developer Name', 'Role / Designation', 'Registered Email', 'Status', 'Active Tasks', 'Workload Capacity', 'Last Active'];
        const rowsData = targetRows.map(tr => {
            const name = tr.querySelector('strong')?.innerText?.trim() || '';
            const role = tr.querySelector('td:nth-child(3)')?.innerText?.trim() || '';
            const email = tr.getAttribute('data-email') || tr.querySelector('td:nth-child(4)')?.innerText?.trim() || '';
            const status = tr.getAttribute('data-status') || tr.querySelector('.badge-status')?.innerText?.trim() || '';
            const activeTasks = tr.querySelector('td:nth-child(7)')?.innerText?.trim() || '0';
            const workload = tr.querySelector('td:nth-child(8)')?.innerText?.replace(/\s+/g, ' ').trim() || '';
            const lastActive = tr.querySelector('td:nth-child(9)')?.innerText?.trim() || '';
            return [name, role, email, status, activeTasks, workload, lastActive];
        });

        const dateStr = new Date().toISOString().slice(0, 10);
        generateCSVDownload(`developers_export_${dateStr}.csv`, headers, rowsData);
    }

    function exportSelectedDevelopersCSV() {
        exportDevelopersCSV(true);
    }

    // Export History as CSV
    function exportHistoryCSV(onlySelected = false) {
        let targetRows = Array.from(document.querySelectorAll('#assignmentsGridTable tbody tr.history-table-row'));
        if (onlySelected) {
            targetRows = targetRows.filter(tr => tr.querySelector('.history-row-cb')?.checked);
        }
        if (!targetRows.length) {
            alert('No assignment history records available to export.');
            return;
        }

        const headers = ['Task Title', 'Assigned Developer', 'Developer Email', 'Company', 'Assigned By', 'Date', 'Priority', 'Status'];
        const rowsData = targetRows.map(tr => {
            const title = tr.getAttribute('data-title') || tr.querySelector('td:nth-child(2) strong')?.innerText?.trim() || '';
            const devName = tr.getAttribute('data-developer') || tr.querySelector('td:nth-child(3) strong')?.innerText?.trim() || '';
            const devEmail = tr.getAttribute('data-email') || tr.querySelector('td:nth-child(3) span')?.innerText?.trim() || '';
            const company = tr.getAttribute('data-company') || tr.querySelector('td:nth-child(4)')?.innerText?.trim() || '';
            const assigner = tr.querySelector('td:nth-child(5)')?.innerText?.trim() || '';
            const date = tr.querySelector('td:nth-child(6)')?.innerText?.trim() || '';
            const priority = tr.getAttribute('data-priority') || tr.querySelector('td:nth-child(7)')?.innerText?.trim() || '';
            const status = tr.getAttribute('data-status') || tr.querySelector('td:nth-child(8)')?.innerText?.trim() || '';
            return [title, devName, devEmail, company, assigner, date, priority, status];
        });

        const dateStr = new Date().toISOString().slice(0, 10);
        generateCSVDownload(`assignment_history_${dateStr}.csv`, headers, rowsData);
    }

    // Printable PDF Window Generator
    function generatePDFPrintWindow(title, subtitle, tableHtml) {
        const printWin = window.open('', '_blank', 'width=1100,height=850');
        if (!printWin) {
            alert('Please allow popups to export printable PDF documents.');
            return;
        }

        const nowStr = new Date().toLocaleString();
        printWin.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>${title} - ${nowStr}</title>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 24px; color: #0f172a; }
                    .header-box { border-bottom: 2px solid #0f744c; padding-bottom: 12px; margin-bottom: 20px; }
                    .title { font-size: 22px; font-weight: 800; color: #0f744c; margin: 0 0 4px 0; }
                    .subtitle { font-size: 12px; color: #64748b; margin: 0; }
                    .meta-bar { font-size: 11px; color: #64748b; margin-top: 8px; display: flex; justify-content: space-between; }
                    table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 12px; }
                    th { background: #f1f5f9; color: #0f172a; text-transform: uppercase; font-size: 10px; font-weight: 800; padding: 10px 12px; border: 1px solid #cbd5e1; text-align: left; }
                    td { padding: 9px 12px; border: 1px solid #cbd5e1; vertical-align: middle; }
                    tr:nth-child(even) { background: #f8fafc; }
                    .footer { margin-top: 24px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 12px; }
                    @media print {
                        body { padding: 0; }
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="no-print" style="margin-bottom: 16px; text-align: right;">
                    <button onclick="window.print()" style="background: #0f744c; color: white; border: none; padding: 8px 18px; font-weight: 700; border-radius: 6px; cursor: pointer;">🖨️ Print / Save as PDF</button>
                </div>
                <div class="header-box">
                    <h1 class="title">${title}</h1>
                    <p class="subtitle">${subtitle}</p>
                    <div class="meta-bar">
                        <span>Generated By: Super Admin Platform</span>
                        <span>Timestamp: ${nowStr}</span>
                    </div>
                </div>
                ${tableHtml}
                <div class="footer">
                    Platform Developer Management Audit Center &copy; ${new Date().getFullYear()}
                </div>
            </body>
            </html>
        `);
        printWin.document.close();
        printWin.focus();
    }

    function exportDevelopersPDF(onlySelected = false) {
        let targetRows = Array.from(document.querySelectorAll('#developersGridTable tbody tr.dev-table-row'));
        if (onlySelected) {
            targetRows = targetRows.filter(tr => tr.querySelector('.dev-row-cb')?.checked);
        }
        if (!targetRows.length) {
            alert('No developer records available to export.');
            return;
        }

        let rowsHtml = targetRows.map(tr => {
            const name = tr.querySelector('strong')?.innerText?.trim() || '';
            const role = tr.querySelector('td:nth-child(3)')?.innerText?.trim() || '';
            const email = tr.getAttribute('data-email') || tr.querySelector('td:nth-child(4)')?.innerText?.trim() || '';
            const status = tr.getAttribute('data-status') || tr.querySelector('.badge-status')?.innerText?.trim() || '';
            const activeTasks = tr.querySelector('td:nth-child(7)')?.innerText?.trim() || '0';
            const workload = tr.querySelector('td:nth-child(8)')?.innerText?.replace(/\s+/g, ' ').trim() || '';
            const lastActive = tr.querySelector('td:nth-child(9)')?.innerText?.trim() || '';
            return `<tr>
                <td><strong>${name}</strong></td>
                <td>${role}</td>
                <td style="font-family: monospace;">${email}</td>
                <td>${status}</td>
                <td>${activeTasks}</td>
                <td>${workload}</td>
                <td>${lastActive}</td>
            </tr>`;
        }).join('');

        const tableHtml = `
            <table>
                <thead>
                    <tr>
                        <th>Developer Name</th>
                        <th>Role</th>
                        <th>Registered Email</th>
                        <th>Status</th>
                        <th>Active Tasks</th>
                        <th>Workload Capacity</th>
                        <th>Last Active</th>
                    </tr>
                </thead>
                <tbody>${rowsHtml}</tbody>
            </table>
        `;

        generatePDFPrintWindow('Developer Directory Report', 'Enterprise Developer Management & Capacity Assessment Audit Report', tableHtml);
    }

    function exportSelectedDevelopersPDF() {
        exportDevelopersPDF(true);
    }

    function exportHistoryPDF(onlySelected = false) {
        let targetRows = Array.from(document.querySelectorAll('#assignmentsGridTable tbody tr.history-table-row'));
        if (onlySelected) {
            targetRows = targetRows.filter(tr => tr.querySelector('.history-row-cb')?.checked);
        }
        if (!targetRows.length) {
            alert('No assignment history records available to export.');
            return;
        }

        let rowsHtml = targetRows.map(tr => {
            const title = tr.getAttribute('data-title') || tr.querySelector('td:nth-child(2) strong')?.innerText?.trim() || '';
            const devName = tr.getAttribute('data-developer') || tr.querySelector('td:nth-child(3) strong')?.innerText?.trim() || '';
            const devEmail = tr.getAttribute('data-email') || tr.querySelector('td:nth-child(3) span')?.innerText?.trim() || '';
            const company = tr.getAttribute('data-company') || tr.querySelector('td:nth-child(4)')?.innerText?.trim() || '';
            const assigner = tr.querySelector('td:nth-child(5)')?.innerText?.trim() || '';
            const date = tr.querySelector('td:nth-child(6)')?.innerText?.trim() || '';
            const priority = tr.getAttribute('data-priority') || tr.querySelector('td:nth-child(7)')?.innerText?.trim() || '';
            const status = tr.getAttribute('data-status') || tr.querySelector('td:nth-child(8)')?.innerText?.trim() || '';
            return `<tr>
                <td><strong>${title}</strong></td>
                <td>${devName}<br><small style="color:#64748b;">${devEmail}</small></td>
                <td>${company}</td>
                <td>${assigner}</td>
                <td>${date}</td>
                <td>${priority}</td>
                <td>${status}</td>
            </tr>`;
        }).join('');

        const tableHtml = `
            <table>
                <thead>
                    <tr>
                        <th>Task Title</th>
                        <th>Assigned Developer</th>
                        <th>Company</th>
                        <th>Assigned By</th>
                        <th>Date</th>
                        <th>Priority</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>${rowsHtml}</tbody>
            </table>
        `;

        generatePDFPrintWindow('Work Allocation History Report', 'Recent Task Assignment Telemetry & Work Allocation Audit Log', tableHtml);
    }

    function assignWorkToSelected() {
        const selectedCbs = document.querySelectorAll('.dev-row-cb:checked');
        if (!selectedCbs.length) return;
        const firstEmail = selectedCbs[0].getAttribute('data-email');
        openAssignModal();
        if (firstEmail) {
            const input = document.getElementById('assignDevEmailInput');
            if (input) {
                input.value = firstEmail;
                lookupDevEmail(firstEmail);
            }
        }
    }

    // Scroll & Drawer functions
    function filterByStatus(st) {
        const form = document.getElementById('devFilterForm');
        const select = form.querySelector('select[name="status"]');
        if (select) {
            select.value = st;
            form.submit();
        }
    }

    function scrollToHistory() {
        const sec = document.getElementById('assignmentHistorySection');
        if (sec) sec.scrollIntoView({ behavior: 'smooth' });
    }

    // Slide-Over Drawer Data Population
    function openDevDrawer(devId) {
        const dev = devData.find(d => d.id == devId);
        if (!dev) return;

        document.getElementById('drawerDevAvatar').innerText = (dev.name || 'DV').substr(0, 2).toUpperCase();
        document.getElementById('drawerDevName').innerText = dev.name;
        document.getElementById('drawerDevRole').innerText = dev.role_title;
        document.getElementById('drawerDevCompany').innerText = dev.company ? dev.company.name : 'Platform Central';
        document.getElementById('drawerDevEmail').innerText = dev.email;
        document.getElementById('drawerDevWorkloadCategory').innerText = dev.workload_category + ' Workload';
        document.getElementById('drawerDevHours').innerText = dev.estimate_hours_total + 'h / 40h';
        document.getElementById('drawerCapacityBar').style.width = dev.capacity_percentage + '%';

        // Skills
        const skillsContainer = document.getElementById('drawerDevSkillsContainer');
        skillsContainer.innerHTML = '';
        (dev.skills_list || ['PHP', 'Laravel']).forEach(skill => {
            const span = document.createElement('span');
            span.className = 'skill-tag';
            span.innerText = skill;
            skillsContainer.appendChild(span);
        });

        // Current Assignments
        const assignList = document.getElementById('drawerDevAssignmentsList');
        assignList.innerHTML = '';
        if (dev.active_tasks && dev.active_tasks.length > 0) {
            dev.active_tasks.forEach(t => {
                const item = document.createElement('div');
                item.style.cssText = 'padding: 12px; background: #ffffff; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 8px;';
                item.innerHTML = `
                    <strong style="font-size: 13px; color: var(--slate-dark); display: block;">${t.title}</strong>
                    <span style="font-size: 11.5px; color: var(--slate-muted);">Priority: ${(t.priority || 'medium').toUpperCase()} · Status: ${(t.status || 'assigned').toUpperCase()}</span>
                `;
                assignList.appendChild(item);
            });
        } else {
            assignList.innerHTML = '<span style="font-size: 12px; color: var(--slate-muted);">No active tasks assigned to this developer.</span>';
        }

        // Actions
        document.getElementById('drawerEditBtn').onclick = function() {
            closeDevDrawer();
            openEditDeveloperModal(dev.id);
        };
        const statusForm = document.getElementById('drawerStatusForm');
        statusForm.action = '{{ url('/super-admin/developers') }}/' + dev.id + '/toggle-status';

        document.getElementById('devProfileDrawer').classList.add('active');
    }

    function closeDevDrawer() {
        document.getElementById('devProfileDrawer').classList.remove('active');
    }

    // Modal Control
    function openAddDeveloperModal() {
        document.getElementById('devModalTitle').innerText = 'Add New Developer';
        document.getElementById('devForm').action = '{{ route('super-admin.developers.store') }}';
        document.getElementById('devFormMethod').value = 'POST';
        document.getElementById('devFormName').value = '';
        document.getElementById('devFormEmail').value = '';
        document.getElementById('devFormMobile').value = '';
        document.getElementById('devFormSkills').value = '';
        document.getElementById('developerModal').classList.add('active');
    }

    function openEditDeveloperModal(devId) {
        const dev = devData.find(d => d.id == devId);
        if (!dev) return;

        document.getElementById('devModalTitle').innerText = 'Edit Developer Details';
        document.getElementById('devForm').action = '{{ url('/super-admin/developers') }}/' + dev.id;
        document.getElementById('devFormMethod').value = 'PUT';
        document.getElementById('devFormName').value = dev.name;
        document.getElementById('devFormEmail').value = dev.email;
        document.getElementById('devFormMobile').value = dev.phone_number;
        document.getElementById('devFormRole').value = dev.role_title;
        document.getElementById('devFormSkills').value = (dev.skills_list || []).join(', ');
        document.getElementById('developerModal').classList.add('active');
    }

    function closeDeveloperModal() {
        document.getElementById('developerModal').classList.remove('active');
    }

    function openAssignModal() {
        document.getElementById('assignWorkModal').classList.add('active');
    }

    function closeAssignModal() {
        document.getElementById('assignWorkModal').classList.remove('active');
    }

    function quickAssignWork(email) {
        openAssignModal();
        const input = document.getElementById('assignDevEmailInput');
        if (input) {
            input.value = email;
            lookupDevEmail(email);
        }
    }

    function lookupDevEmail(val) {
        const query = val.trim().toLowerCase();
        const card = document.getElementById('devLookupCard');
        const alertBanner = document.getElementById('workloadAlertBanner');
        if (query.length < 3) {
            card.style.display = 'none';
            return;
        }

        const dev = devData.find(d => d.email.toLowerCase() === query || d.name.toLowerCase().includes(query));
        if (dev) {
            card.style.display = 'block';
            document.getElementById('lookupDevName').innerText = dev.name;
            document.getElementById('lookupDevRole').innerText = dev.role_title + ' · ' + (dev.company ? dev.company.name : 'Platform Central');
            document.getElementById('lookupDevWorkloadBadge').innerText = dev.workload_category + ' Workload (' + dev.active_tasks_count + ' Active Tasks)';

            if (dev.workload_category === 'Heavy' || dev.active_tasks_count >= 4) {
                alertBanner.style.display = 'block';
            } else {
                alertBanner.style.display = 'none';
            }
        } else {
            card.style.display = 'block';
            document.getElementById('lookupDevName').innerText = 'Developer Not Found';
            document.getElementById('lookupDevRole').innerText = 'No registered developer matches: ' + query;
            document.getElementById('lookupDevWorkloadBadge').innerText = 'Unknown';
            alertBanner.style.display = 'none';
        }
    }
</script>
@endpush
@endsection
