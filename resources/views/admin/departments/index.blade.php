@extends('admin.layout.app')

@section('title', 'Sub Departments')

@section('content')
@include('admin.settings.partials.workforce-navigation')

<div id="subDepartmentPage" class="sub-department-page">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <i class="fas fa-sitemap"></i>
        <span>Admin</span>
        <span class="crumb-sep">/</span>
        <span>Settings</span>
        <span class="crumb-sep">/</span>
        <span class="crumb-current">Sub Department</span>
    </div>

    <!-- Header Card -->
    <div class="header-card">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas fa-sitemap"></i>
            </div>
            <div class="header-text">
                <h1>Sub Department Management</h1>
                <p>Manage teams and their parent department assignments.</p>
            </div>
        </div>
        <div class="btn-group header-actions">
            <a href="{{ \App\Support\WorkforceSettings::route('departments.archive') }}" class="btn btn-light">
                <i class="fas fa-box-archive"></i> Archived
                @if(($archivedCount ?? 0) > 0)
                    <span class="archive-count-badge">{{ $archivedCount }}</span>
                @endif
            </a>
            <a href="{{ \App\Support\WorkforceSettings::route('departments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Sub Department
            </a>
        </div>
    </div>

    @php
        $parentCount = $departments->pluck('parent_dpt_id')->filter()->unique()->count();
        $employeeCount = $departments->sum('employee_details_count');
        $emptyCount = $departments->where('employee_details_count', 0)->count();
    @endphp

    <!-- Stats Cards -->
    <div class="stats">
        <div class="stat-card">
            <div class="stat-icon stat-icon-total">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="stat-body">
                <h3>{{ $departments->count() }}</h3>
                <span>Total Sub Departments</span>
                <p class="stat-sub">Available sub departments</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-active">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-body">
                <h3>{{ $parentCount }}</h3>
                <span>Parent Departments</span>
                <p class="stat-sub">Unique parent departments</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-codes">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-body">
                <h3>{{ $employeeCount }}</h3>
                <span>Assigned Employees</span>
                <p class="stat-sub">Total employees in sub depts</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-recent">
                <i class="fas fa-user-slash"></i>
            </div>
            <div class="stat-body">
                <h3>{{ $emptyCount }}</h3>
                <span>Without Employees</span>
                <p class="stat-sub">Empty sub departments</p>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if($errors->any() || session('error') || session('success'))
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Validation Errors!</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle"></i>
            <div>
                <strong>Error!</strong> {{ session('error') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <div>
                <strong>Success!</strong> {{ session('success') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
    @endif

    <!-- Main Table Card -->
    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <div class="table-title-icon">
                    <i class="fas fa-bars"></i>
                </div>
                <div>
                    <h3>Sub Department List</h3>
                    <span class="muted">{{ $departments->count() }} sub departments available</span>
                </div>
            </div>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="departmentSearch" placeholder="Search departments..." />
            </div>
        </div>

        <!-- Bulk Actions Bar -->
        <div class="bulk-actions-bar" id="bulk-actions-bar" style="display: none;">
            <div class="bulk-actions-content">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="select-all">
                    <label class="form-check-label" for="select-all">Select All</label>
                </div>
                <span class="selected-badge" id="selected-count">0 selected</span>
                <button class="btn-clear" id="clear-selection"><i class="fas fa-times"></i> Clear</button>
                <button class="btn-bulk-delete" id="bulk-delete-btn" disabled><i class="fas fa-box-archive"></i> Archive Selected</button>
            </div>
        </div>

        <div class="table-wrapper">
            <table id="deptTable" class="department-list-table">
                <thead>
                    <tr>
                        <th width="50">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="table-select-all">
                            </div>
                        </th>
                        <th>#</th>
                        <th><i class="fas fa-code"></i> Sub Department Code</th>
                        <th><i class="fas fa-building"></i> Sub Department</th>
                        <th><i class="fas fa-arrow-right"></i> Parent Department</th>
                        <th class="text-end"><i class="fas fa-cog"></i> Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $index => $dept)
                    <tr class="department-row" data-id="{{ $dept->id }}">
                        <td>
                            <div class="form-check">
                                <input class="form-check-input select-item" type="checkbox" value="{{ $dept->id }}">
                            </div>
                        </td>
                        <td>
                            <div class="department-info">
                                <div class="mini-icon">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <div>
                                    <strong class="department-number">{{ $index + 1 }}</strong>
                                    <span class="muted">ID: {{ str_pad($dept->id, 3, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="department-info">
                                <div class="mini-icon">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <div>
                                    <strong class="department-code">{{ $dept->dpt_code }}</strong>
                                    <span class="muted"><i class="fas fa-key"></i> Unique identifier</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="department-info">
                                <div class="mini-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <strong class="department-name">{{ $dept->dpt_name }}</strong>
                                    <span class="muted"><i class="fas fa-user"></i> {{ $dept->employee_details_count }} employee(s)</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="department-info">
                                <div class="mini-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div>
                                    <strong class="parent-name">{{ $dept->parent?->dpt_name ?? 'N/A' }}</strong>
                                    <span class="muted"><i class="fas fa-folder"></i> Parent department</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="action-btn" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ \App\Support\WorkforceSettings::route('departments.show', $dept) }}">
                                            <i class="fas fa-eye"></i> View Sub Department
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ \App\Support\WorkforceSettings::route('departments.edit', $dept) }}">
                                            <i class="fas fa-pen"></i> Edit Sub Department
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ \App\Support\WorkforceSettings::route('departments.destroy', $dept) }}" style="display:inline-block; width:100%;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-warning" onclick="return confirm('Archive this sub department? It can be restored later.')">
                                                <i class="fas fa-box-archive"></i> Archive Sub Department
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-0 border-0">
                            <div class="empty-state">
                                <div class="empty-icon-box">
                                    <i class="fas fa-sitemap"></i>
                                </div>
                                <h5>No Sub Departments Found</h5>
                                <p>Get started by creating your first sub department to organize your teams.</p>
                                <a href="{{ \App\Support\WorkforceSettings::route('departments.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create Sub Department
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer with pagination info -->
        @if($departments->count() > 0)
        <div class="footer">
            <div class="footer-left">
                <div class="show-entries">
                    <span>Show</span>
                    <select id="showEntries">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                    </select>
                    <span>entries</span>
                </div>
            </div>

            <div class="pagination-info">
                <i class="fas fa-chart-simple"></i>
                Showing {{ $departments->count() }} of {{ $departments->count() }} sub departments
            </div>

            <div class="pagination-info">
                <i class="fas fa-table"></i> Client-side table paging
            </div>
        </div>
        @endif
    </div>

    <!-- Legend Card -->
    <div class="legend-card">
        <div class="legend-content">
            <span class="legend-title"><span class="legend-dot"></span> Department Info:</span>
            <span class="legend-item"><i class="fas fa-code legend-icon-code"></i> Department Code</span>
            <span class="legend-item"><i class="fas fa-users legend-icon-name"></i> Department Name</span>
            <span class="legend-item"><i class="fas fa-building legend-icon-parent"></i> Parent Department</span>
            <span class="legend-item"><i class="fas fa-user-friends legend-icon-count"></i> Employee Count</span>
        </div>
    </div>
</div>

<style>
    /* ===== CLEAN ENTERPRISE SAAS THEME FOR SUB DEPARTMENTS ===== */
    :root {
        --sd-bg: #f8fafc;
        --sd-surface: #ffffff;
        --sd-surface-subtle: #f8fafc;
        --sd-surface-hover: #f8fafc;
        --sd-border: #e2e8f0;
        --sd-border-subtle: #f1f5f9;
        --sd-text: #0f172a;
        --sd-text-body: #334155;
        --sd-text-muted: #64748b;
        --sd-text-faint: #94a3b8;
        --sd-primary: #2563eb;
        --sd-primary-hover: #1d4ed8;
        --sd-primary-soft: #eff6ff;
        --sd-primary-border: #dbeafe;
        --sd-shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
        --sd-shadow-sm: 0 4px 12px rgba(15, 23, 42, 0.06);
    }

    html:is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark),
    body:is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) {
        --sd-bg: #070b1a;
        --sd-surface: #0f1530;
        --sd-surface-subtle: #141b3d;
        --sd-surface-hover: rgba(37, 99, 235, 0.1);
        --sd-border: rgba(148, 163, 184, 0.18);
        --sd-border-subtle: rgba(148, 163, 184, 0.12);
        --sd-text: #f8fafc;
        --sd-text-body: #e2e8f0;
        --sd-text-muted: #94a3b8;
        --sd-text-faint: #64748b;
        --sd-primary: #3b82f6;
        --sd-primary-hover: #60a5fa;
        --sd-primary-soft: rgba(59, 130, 246, 0.16);
        --sd-primary-border: rgba(96, 165, 250, 0.3);
        --sd-shadow-xs: 0 4px 12px rgba(0, 0, 0, 0.25);
        --sd-shadow-sm: 0 12px 28px rgba(0, 0, 0, 0.36);
    }

    body:has(#subDepartmentPage),
    body:has(#subDepartmentPage) .layout-page,
    body:has(#subDepartmentPage) .content-wrapper {
        background: var(--sd-bg) !important;
        background-image: none !important;
    }

    #subDepartmentPage.sub-department-page {
        padding: 18px 28px 34px !important;
        min-height: 100vh;
        background: var(--sd-bg) !important;
        background-image: none !important;
        color: var(--sd-text);
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        position: relative;
    }

    #subDepartmentPage.sub-department-page::before {
        display: none !important;
        content: none !important;
    }

    /* Breadcrumb */
    #subDepartmentPage .breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        background: transparent !important;
        backdrop-filter: none !important;
        border: 0 !important;
        box-shadow: none !important;
        padding: 0 0 14px 0 !important;
        margin: 0 !important;
        font-size: 0.8rem !important;
        font-weight: 500 !important;
        color: var(--sd-text-muted) !important;
    }

    #subDepartmentPage .breadcrumb i {
        color: var(--sd-text-faint) !important;
        font-size: 0.78rem;
        margin-right: 2px;
    }

    #subDepartmentPage .breadcrumb .crumb-sep {
        color: var(--sd-text-faint);
    }

    #subDepartmentPage .breadcrumb .crumb-current {
        color: var(--sd-primary) !important;
        font-weight: 600;
    }

    /* Header Card */
    #subDepartmentPage .header-card {
        background: var(--sd-surface) !important;
        backdrop-filter: none !important;
        border: 1px solid var(--sd-border) !important;
        border-radius: 14px !important;
        padding: 1.2rem 1.45rem !important;
        margin-bottom: 1.15rem !important;
        box-shadow: var(--sd-shadow-xs) !important;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.25rem;
        flex-wrap: wrap;
        position: relative;
        z-index: 50;
        overflow: visible !important;
    }

    #subDepartmentPage .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 0;
        flex: 1 1 360px;
    }

    #subDepartmentPage .header-icon {
        width: 44px !important;
        height: 44px !important;
        background: var(--sd-primary) !important;
        background-image: none !important;
        color: #ffffff !important;
        border-radius: 11px !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem !important;
        box-shadow: none !important;
        flex-shrink: 0;
    }

    #subDepartmentPage .header-icon > i,
    #subDepartmentPage .header-icon > i::before {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    #subDepartmentPage .header-text {
        min-width: 0;
    }

    #subDepartmentPage .header-card h1 {
        font-size: 1.2rem !important;
        font-weight: 700 !important;
        line-height: 1.25 !important;
        margin: 0 0 0.2rem 0 !important;
        color: var(--sd-text) !important;
        background: none !important;
        -webkit-background-clip: initial !important;
        -webkit-text-fill-color: var(--sd-text) !important;
        letter-spacing: -0.02em;
    }

    #subDepartmentPage .header-card p {
        color: var(--sd-text-muted) !important;
        -webkit-text-fill-color: var(--sd-text-muted) !important;
        font-size: 0.82rem !important;
        font-weight: 400 !important;
        line-height: 1.45 !important;
        margin: 0 !important;
    }

    /* Header Action Buttons */
    #subDepartmentPage .header-actions.btn-group {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 0.55rem !important;
        position: relative;
        z-index: 60;
        overflow: visible;
    }

    #subDepartmentPage .btn {
        padding: 0.48rem 0.95rem !important;
        min-height: 37px;
        border-radius: 9px !important;
        font-weight: 600 !important;
        font-size: 0.82rem !important;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        white-space: nowrap;
        text-decoration: none;
        transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
    }

    #subDepartmentPage .btn i {
        font-size: 0.88rem;
    }

    #subDepartmentPage .btn-light {
        background: var(--sd-surface) !important;
        border: 1px solid var(--sd-border) !important;
        color: var(--sd-text-body) !important;
        -webkit-text-fill-color: var(--sd-text-body) !important;
        box-shadow: var(--sd-shadow-xs) !important;
    }

    #subDepartmentPage .btn-light:hover {
        background: var(--sd-surface-subtle) !important;
        border-color: #cbd5e1 !important;
        color: var(--sd-text) !important;
        -webkit-text-fill-color: var(--sd-text) !important;
        transform: none !important;
    }

    #subDepartmentPage .btn-primary {
        background: var(--sd-primary) !important;
        background-image: none !important;
        border: 1px solid var(--sd-primary) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: var(--sd-shadow-xs) !important;
    }

    #subDepartmentPage .btn-primary:hover {
        background: var(--sd-primary-hover) !important;
        border-color: var(--sd-primary-hover) !important;
        color: #ffffff !important;
        transform: none !important;
        box-shadow: var(--sd-shadow-sm) !important;
    }

    #subDepartmentPage .btn-primary i,
    #subDepartmentPage .btn-primary i::before {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    #subDepartmentPage .archive-count-badge {
        min-width: 20px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 6px;
        border-radius: 999px;
        background: #f59e0b;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-size: 0.7rem;
        font-weight: 700;
        line-height: 1;
    }

    /* ===== STATS GRID (Overrides global pms-refresh.css :first-of-type highlight) ===== */
    #subDepartmentPage .stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.15rem;
        position: relative;
        z-index: 1;
    }

    #subDepartmentPage .stat-card,
    #subDepartmentPage .stat-card:first-of-type,
    #subDepartmentPage .stat-card.is-featured {
        background: var(--sd-surface) !important;
        background-image: none !important;
        border: 1px solid var(--sd-border) !important;
        border-radius: 14px !important;
        padding: 1rem 1.2rem !important;
        box-shadow: var(--sd-shadow-xs) !important;
        color: var(--sd-text) !important;
        display: flex !important;
        align-items: center !important;
        gap: 0.9rem !important;
        position: relative;
        overflow: hidden;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    #subDepartmentPage .stat-card::after {
        display: none !important;
        content: none !important;
    }

    #subDepartmentPage .stat-card:hover {
        transform: none !important;
        border-color: #cbd5e1 !important;
        box-shadow: var(--sd-shadow-sm) !important;
    }

    #subDepartmentPage .stat-card .stat-icon,
    #subDepartmentPage .stat-card:first-of-type .stat-icon {
        width: 40px !important;
        height: 40px !important;
        border-radius: 10px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1rem !important;
        flex-shrink: 0;
        box-shadow: none !important;
        border: 0 !important;
    }

    #subDepartmentPage .stat-card .stat-icon.stat-icon-total {
        background: #eff6ff !important;
        color: #2563eb !important;
    }

    #subDepartmentPage .stat-card .stat-icon.stat-icon-active {
        background: #ecfdf5 !important;
        color: #16a34a !important;
    }

    #subDepartmentPage .stat-card .stat-icon.stat-icon-codes {
        background: #eef2ff !important;
        color: #4f46e5 !important;
    }

    #subDepartmentPage .stat-card .stat-icon.stat-icon-recent {
        background: #fffbeb !important;
        color: #d97706 !important;
    }

    #subDepartmentPage .stat-card .stat-icon > i,
    #subDepartmentPage .stat-card .stat-icon > i::before,
    #subDepartmentPage .stat-card:first-of-type .stat-icon > i,
    #subDepartmentPage .stat-card:first-of-type .stat-icon > i::before {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
        fill: currentColor !important;
        background: transparent !important;
        font-size: 1rem !important;
    }

    #subDepartmentPage .stat-card .stat-body {
        min-width: 0;
    }

    #subDepartmentPage .stat-card h3,
    #subDepartmentPage .stat-card:first-of-type h3 {
        font-size: 1.4rem !important;
        font-weight: 700 !important;
        color: var(--sd-text) !important;
        -webkit-text-fill-color: var(--sd-text) !important;
        margin: 0 0 0.2rem 0 !important;
        line-height: 1.15 !important;
        letter-spacing: -0.02em;
    }

    #subDepartmentPage .stat-card span,
    #subDepartmentPage .stat-card:first-of-type span {
        display: block;
        color: var(--sd-text-muted) !important;
        -webkit-text-fill-color: var(--sd-text-muted) !important;
        font-size: 0.68rem !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        line-height: 1.3;
    }

    #subDepartmentPage .stat-card .stat-sub,
    #subDepartmentPage .stat-card:first-of-type .stat-sub {
        font-size: 0.72rem !important;
        color: var(--sd-text-faint) !important;
        -webkit-text-fill-color: var(--sd-text-faint) !important;
        margin: 0.14rem 0 0 0 !important;
        font-weight: 400 !important;
        line-height: 1.35;
    }

    /* Alerts */
    #subDepartmentPage .alert {
        border-radius: 12px;
        border: 1px solid var(--sd-border);
        padding: 0.9rem 1.15rem;
        margin-bottom: 1.15rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        font-size: 0.85rem;
    }

    #subDepartmentPage .alert-success {
        background: #ecfdf5;
        color: #065f46;
        border-color: #a7f3d0;
    }

    #subDepartmentPage .alert-danger {
        background: #fef2f2;
        color: #991b1b;
        border-color: #fecaca;
    }

    /* ===== MAIN TABLE CARD ===== */
    #subDepartmentPage .table-card {
        background: var(--sd-surface) !important;
        border-radius: 14px !important;
        border: 1px solid var(--sd-border) !important;
        box-shadow: var(--sd-shadow-xs) !important;
        overflow: hidden;
        position: relative;
        z-index: 1;
    }

    #subDepartmentPage .table-card:has(.dropdown-menu.show),
    #subDepartmentPage .table-card:has(.pms-table-export.is-open) {
        overflow: visible !important;
    }

    #subDepartmentPage .table-header {
        padding: 1rem 1.35rem !important;
        background: var(--sd-surface) !important;
        background-image: none !important;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        border-bottom: 1px solid var(--sd-border) !important;
    }

    #subDepartmentPage .table-title {
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }

    #subDepartmentPage .table-title-icon {
        width: 36px !important;
        height: 36px !important;
        background: var(--sd-primary-soft) !important;
        background-image: none !important;
        color: var(--sd-primary) !important;
        border: 1px solid var(--sd-primary-border) !important;
        border-radius: 10px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.92rem;
        flex-shrink: 0;
    }

    #subDepartmentPage .table-title-icon i {
        color: var(--sd-primary) !important;
        -webkit-text-fill-color: var(--sd-primary) !important;
    }

    #subDepartmentPage .table-title h3 {
        font-size: 0.98rem !important;
        font-weight: 700 !important;
        color: var(--sd-text) !important;
        -webkit-text-fill-color: var(--sd-text) !important;
        margin: 0 !important;
        line-height: 1.25;
    }

    #subDepartmentPage .table-title .muted {
        display: block;
        margin-top: 2px !important;
        font-size: 0.76rem !important;
        color: var(--sd-text-muted) !important;
        -webkit-text-fill-color: var(--sd-text-muted) !important;
        font-weight: 400 !important;
    }

    /* Search Input */
    #subDepartmentPage .search-box {
        position: relative;
    }

    #subDepartmentPage .search-box input {
        width: 245px !important;
        min-height: 36px !important;
        height: 36px !important;
        padding: 0 14px 0 36px !important;
        border-radius: 9px !important;
        border: 1px solid var(--sd-border) !important;
        background: var(--sd-surface) !important;
        color: var(--sd-text) !important;
        font-size: 0.82rem !important;
        font-weight: 400 !important;
        outline: none;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    #subDepartmentPage .search-box input::placeholder {
        color: var(--sd-text-faint) !important;
    }

    #subDepartmentPage .search-box input:focus {
        border-color: var(--sd-primary) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
    }

    #subDepartmentPage .search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--sd-text-faint) !important;
        font-size: 0.82rem;
    }

    /* Bulk Actions Bar */
    #subDepartmentPage .bulk-actions-bar {
        padding: 0.68rem 1.35rem;
        background: var(--sd-primary-soft);
        border-bottom: 1px solid var(--sd-primary-border);
    }

    #subDepartmentPage .bulk-actions-content {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        flex-wrap: wrap;
    }

    #subDepartmentPage .selected-badge {
        padding: 0.25rem 0.75rem;
        background: var(--sd-surface);
        color: var(--sd-primary);
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        border: 1px solid var(--sd-primary-border);
    }

    #subDepartmentPage .btn-clear,
    #subDepartmentPage .btn-bulk-delete {
        padding: 0.35rem 0.85rem;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.18s ease;
    }

    #subDepartmentPage .btn-clear {
        background: var(--sd-surface);
        color: var(--sd-text-body);
        border: 1px solid var(--sd-border);
    }

    #subDepartmentPage .btn-bulk-delete {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    #subDepartmentPage .btn-bulk-delete:hover:not(:disabled) {
        background: #fef3c7;
        color: #92400e;
    }

    #subDepartmentPage .btn-bulk-delete:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* pms-table-tools Bar inside .table-card */
    #subDepartmentPage .table-card .pms-table-tools {
        background: var(--sd-surface) !important;
        border: 0 !important;
        border-bottom: 1px solid var(--sd-border) !important;
        border-radius: 0 !important;
        min-height: 48px !important;
        padding: 0.55rem 1.35rem !important;
        box-shadow: none !important;
    }

    #subDepartmentPage .table-card .pms-table-tools__status {
        color: var(--sd-text-muted) !important;
        font-size: 0.8rem !important;
        font-weight: 500 !important;
    }

    #subDepartmentPage .table-card .pms-table-export__toggle {
        background: var(--sd-surface) !important;
        border: 1px solid var(--sd-border) !important;
        color: var(--sd-text-body) !important;
        border-radius: 8px !important;
        font-size: 0.8rem !important;
        font-weight: 600 !important;
        min-height: 34px !important;
        padding: 0.35rem 0.78rem !important;
    }

    #subDepartmentPage .table-card .pms-table-export__toggle:hover,
    #subDepartmentPage .table-card .pms-table-export.is-open .pms-table-export__toggle {
        background: var(--sd-surface-subtle) !important;
        border-color: #cbd5e1 !important;
        color: var(--sd-text) !important;
        transform: none !important;
    }

    #subDepartmentPage .table-card .pms-table-scroll-host {
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    #subDepartmentPage .table-card .pms-table-select {
        background: transparent !important;
    }

    #subDepartmentPage .table-card .pms-table-select::after {
        display: none !important;
    }

    /* Table Wrapper & Flat Table */
    #subDepartmentPage .table-wrapper {
        overflow-x: auto;
        padding: 0 !important;
    }

    #subDepartmentPage .department-list-table {
        width: 100%;
        min-width: 920px;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        margin: 0 !important;
    }

    #subDepartmentPage .department-list-table thead th {
        text-align: left;
        padding: 0.8rem 1rem !important;
        background: var(--sd-surface-subtle) !important;
        color: var(--sd-text-muted) !important;
        -webkit-text-fill-color: var(--sd-text-muted) !important;
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid var(--sd-border) !important;
        white-space: nowrap;
    }

    #subDepartmentPage .department-list-table thead th i {
        margin-right: 6px;
        font-size: 0.72rem;
        color: var(--sd-text-faint) !important;
        -webkit-text-fill-color: var(--sd-text-faint) !important;
    }

    #subDepartmentPage .department-list-table tbody td {
        background: var(--sd-surface) !important;
        padding: 0.9rem 1rem !important;
        border-top: 0 !important;
        border-left: 0 !important;
        border-right: 0 !important;
        border-bottom: 1px solid var(--sd-border-subtle) !important;
        border-radius: 0 !important;
        font-size: 0.85rem !important;
        font-weight: 500;
        color: var(--sd-text-body) !important;
        vertical-align: middle;
        transition: background-color 0.15s ease;
    }

    #subDepartmentPage .department-list-table tbody tr:hover td,
    #subDepartmentPage .department-list-table.pms-exportable-table > tbody > tr:hover > :is(th, td) {
        background: var(--sd-surface-hover) !important;
    }

    #subDepartmentPage .department-list-table tbody tr.table-active td {
        background: var(--sd-primary-soft) !important;
    }

    #subDepartmentPage .department-info {
        display: flex;
        align-items: center;
        gap: 0.72rem;
    }

    #subDepartmentPage .department-list-table .department-info > div:last-child {
        min-width: 0;
    }

    #subDepartmentPage .department-list-table :is(.department-name, .parent-name) {
        overflow-wrap: anywhere;
    }

    #subDepartmentPage .mini-icon {
        width: 34px !important;
        height: 34px !important;
        border-radius: 9px !important;
        background: var(--sd-primary-soft) !important;
        background-image: none !important;
        border: 1px solid var(--sd-primary-border) !important;
        color: var(--sd-primary) !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.88rem !important;
        flex-shrink: 0;
    }

    #subDepartmentPage .mini-icon i {
        color: var(--sd-primary) !important;
        -webkit-text-fill-color: var(--sd-primary) !important;
    }

    #subDepartmentPage .department-number,
    #subDepartmentPage .department-name {
        font-weight: 600 !important;
        color: var(--sd-text) !important;
        -webkit-text-fill-color: var(--sd-text) !important;
        font-size: 0.86rem !important;
    }

    #subDepartmentPage .department-code,
    #subDepartmentPage .parent-name {
        font-weight: 700 !important;
        color: var(--sd-primary) !important;
        -webkit-text-fill-color: var(--sd-primary) !important;
        font-size: 0.86rem !important;
    }

    #subDepartmentPage .department-list-table .muted,
    #subDepartmentPage .department-list-table small {
        display: block;
        margin-top: 2px !important;
        color: var(--sd-text-muted) !important;
        -webkit-text-fill-color: var(--sd-text-muted) !important;
        font-size: 0.74rem !important;
        font-weight: 400 !important;
    }

    #subDepartmentPage .department-list-table .muted i {
        margin-right: 4px;
        font-size: 0.68rem;
    }

    /* Row Action Button & Dropdown */
    #subDepartmentPage .action-btn {
        width: 32px !important;
        height: 32px !important;
        border-radius: 8px !important;
        border: 1px solid var(--sd-border) !important;
        background: var(--sd-surface-subtle) !important;
        color: var(--sd-text-muted) !important;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.16s ease, color 0.16s ease, border-color 0.16s ease;
    }

    #subDepartmentPage .action-btn:hover {
        background: var(--sd-primary-soft) !important;
        border-color: var(--sd-primary-border) !important;
        color: var(--sd-primary) !important;
        transform: none !important;
    }

    #subDepartmentPage .dropdown-menu {
        background: var(--sd-surface) !important;
        border: 1px solid var(--sd-border) !important;
        border-radius: 12px !important;
        padding: 0.4rem !important;
        box-shadow: 0 14px 30px -8px rgba(15, 23, 42, 0.16) !important;
        z-index: 9999;
    }

    #subDepartmentPage .dropdown-item {
        padding: 0.5rem 0.8rem !important;
        color: var(--sd-text-body) !important;
        font-size: 0.82rem !important;
        font-weight: 500 !important;
        border-radius: 8px !important;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        cursor: pointer;
        border: none;
        background: transparent;
        width: 100%;
        text-decoration: none;
    }

    #subDepartmentPage .dropdown-item:hover {
        background: var(--sd-primary-soft) !important;
        color: var(--sd-primary) !important;
    }

    #subDepartmentPage .dropdown-item.text-warning {
        color: #d97706 !important;
    }

    #subDepartmentPage .dropdown-item.text-warning:hover {
        background: #fffbeb !important;
        color: #b45309 !important;
    }

    #subDepartmentPage .dropdown-item.text-danger:hover {
        background: #fef2f2 !important;
        color: #dc2626 !important;
    }

    /* Checkboxes */
    #subDepartmentPage .form-check {
        display: flex;
        align-items: center;
        margin: 0;
        min-height: 0;
    }

    #subDepartmentPage .department-list-table .form-check {
        justify-content: center;
        min-height: 18px;
        padding-left: 0 !important;
        margin-bottom: 0 !important;
    }

    #subDepartmentPage input.form-check-input[type="checkbox"] {
        appearance: auto !important;
        -webkit-appearance: checkbox !important;
        accent-color: var(--sd-primary);
        width: 16px !important;
        height: 16px !important;
        min-width: 16px !important;
        min-height: 16px !important;
        flex: 0 0 16px !important;
        float: none !important;
        margin: 0 !important;
        padding: 0 !important;
        background-image: none !important;
        vertical-align: middle !important;
        cursor: pointer;
    }

    #subDepartmentPage input.form-check-input[type="checkbox"]:focus-visible {
        outline: 2px solid var(--sd-primary) !important;
        outline-offset: 2px;
    }

    #subDepartmentPage .form-check-label {
        margin-left: 8px;
        font-size: 0.82rem;
        font-weight: 500;
        color: var(--sd-text-body);
        cursor: pointer;
    }

    /* Empty State */
    #subDepartmentPage .empty-state {
        padding: 3.4rem 1.5rem !important;
        text-align: center;
    }

    #subDepartmentPage .empty-icon-box {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: var(--sd-primary-soft);
        border: 1px solid var(--sd-primary-border);
        color: var(--sd-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        margin-bottom: 1rem;
    }

    #subDepartmentPage .empty-icon-box i {
        color: var(--sd-primary) !important;
        -webkit-text-fill-color: var(--sd-primary) !important;
        margin: 0 !important;
    }

    #subDepartmentPage .empty-state h5 {
        color: var(--sd-text) !important;
        -webkit-text-fill-color: var(--sd-text) !important;
        font-size: 0.98rem !important;
        font-weight: 700 !important;
        margin-bottom: 0.35rem;
    }

    #subDepartmentPage .empty-state p {
        color: var(--sd-text-muted) !important;
        -webkit-text-fill-color: var(--sd-text-muted) !important;
        font-size: 0.82rem !important;
        margin: 0 auto 1.15rem;
        max-width: 390px;
        line-height: 1.45;
    }

    /* Footer */
    #subDepartmentPage .footer {
        padding: 0.85rem 1.35rem !important;
        background: var(--sd-surface) !important;
        border-top: 1px solid var(--sd-border) !important;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.85rem;
        color: var(--sd-text-muted) !important;
        font-size: 0.8rem !important;
        font-weight: 500;
    }

    #subDepartmentPage .show-entries {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    #subDepartmentPage .show-entries select {
        background: var(--sd-surface) !important;
        border: 1px solid var(--sd-border) !important;
        color: var(--sd-text) !important;
        border-radius: 8px;
        padding: 0.28rem 0.6rem;
        height: 32px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
    }

    #subDepartmentPage .pagination-info {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.8rem !important;
        color: var(--sd-text-muted) !important;
    }

    #subDepartmentPage .pagination-info i {
        color: var(--sd-primary) !important;
    }

    /* Bottom Legend Card */
    #subDepartmentPage .legend-card {
        margin-top: 1rem !important;
        background: var(--sd-surface) !important;
        border: 1px solid var(--sd-border) !important;
        border-radius: 12px !important;
        padding: 0.78rem 1.25rem !important;
        box-shadow: var(--sd-shadow-xs) !important;
        position: relative;
        z-index: 1;
    }

    #subDepartmentPage .legend-content {
        display: flex;
        align-items: center;
        gap: 1.2rem;
        flex-wrap: wrap;
    }

    #subDepartmentPage .legend-title {
        display: inline-flex;
        align-items: center;
        gap: 0.48rem;
        font-weight: 700 !important;
        color: var(--sd-text) !important;
        -webkit-text-fill-color: var(--sd-text) !important;
        font-size: 0.78rem !important;
    }

    #subDepartmentPage .legend-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--sd-primary);
        display: inline-block;
        flex-shrink: 0;
    }

    #subDepartmentPage .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 0.38rem;
        font-size: 0.78rem !important;
        color: var(--sd-text-muted) !important;
        -webkit-text-fill-color: var(--sd-text-muted) !important;
        font-weight: 500;
    }

    #subDepartmentPage .legend-item i {
        font-size: 0.78rem !important;
        margin: 0 !important;
    }

    #subDepartmentPage .legend-icon-code { color: var(--sd-primary) !important; }
    #subDepartmentPage .legend-icon-name { color: #3b82f6 !important; }
    #subDepartmentPage .legend-icon-parent { color: #16a34a !important; }
    #subDepartmentPage .legend-icon-count { color: #d97706 !important; }

    /* Dark Mode Badge & Accent Adjustments */
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #subDepartmentPage .stat-card .stat-icon.stat-icon-total {
        background: rgba(59, 130, 246, 0.16) !important;
        color: #60a5fa !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #subDepartmentPage .stat-card .stat-icon.stat-icon-active {
        background: rgba(16, 185, 129, 0.16) !important;
        color: #34d399 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #subDepartmentPage .stat-card .stat-icon.stat-icon-codes {
        background: rgba(99, 102, 241, 0.18) !important;
        color: #a5b4fc !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #subDepartmentPage .stat-card .stat-icon.stat-icon-recent {
        background: rgba(245, 158, 11, 0.16) !important;
        color: #fbbf24 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #subDepartmentPage .btn-bulk-delete {
        background: rgba(245, 158, 11, 0.16);
        color: #fbbf24;
        border-color: rgba(245, 158, 11, 0.35);
    }

    /* Responsive */
    @media (max-width: 1200px) {
        #subDepartmentPage .stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 992px) {
        #subDepartmentPage.sub-department-page {
            padding: 16px 20px 28px !important;
        }

        #subDepartmentPage .header-card {
            flex-direction: column;
            align-items: flex-start;
        }

        #subDepartmentPage .header-actions.btn-group {
            width: 100%;
            justify-content: flex-start;
        }
    }

    @media (max-width: 768px) {
        #subDepartmentPage .stats {
            grid-template-columns: 1fr;
        }

        #subDepartmentPage .table-header {
            flex-direction: column;
            align-items: stretch;
        }

        #subDepartmentPage .search-box input {
            width: 100% !important;
        }

        #subDepartmentPage .footer {
            flex-direction: column;
            text-align: center;
        }
    }

    @media (max-width: 576px) {
        #subDepartmentPage.sub-department-page {
            padding: 14px !important;
        }

        #subDepartmentPage .header-card {
            padding: 1rem !important;
        }

        #subDepartmentPage .header-actions.btn-group .btn {
            flex: 1 1 auto;
        }
    }

    /* Print Styles */
    @media print {
        #subDepartmentPage .btn-group,
        #subDepartmentPage .search-box,
        #subDepartmentPage .bulk-actions-bar,
        #subDepartmentPage .legend-card,
        #subDepartmentPage .action-btn,
        #subDepartmentPage .dropdown,
        #subDepartmentPage .pms-table-tools {
            display: none !important;
        }

        #subDepartmentPage.sub-department-page {
            background: #ffffff !important;
            padding: 0 !important;
        }
    }
</style>

@push('js')
<script>
$(document).ready(function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Custom search
    $('#departmentSearch').on('keyup', function() {
        var searchTerm = this.value.toLowerCase();
        $('.department-row').each(function() {
            var rowText = $(this).text().toLowerCase();
            if (rowText.indexOf(searchTerm) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Show Entries dropdown
    $('#showEntries').on('change', function() {
        var perPage = $(this).val();
        var currentUrl = window.location.href;
        var url = new URL(currentUrl);
        if (perPage === 'all') {
            url.searchParams.set('per_page', 'all');
        } else {
            url.searchParams.set('per_page', perPage);
        }
        window.location.href = url.toString();
    });

    // Bulk actions functionality
    function getSelectedIds() {
        return $('.select-item:checked').map(function() {
            return $(this).val();
        }).get();
    }

    function updateUI() {
        var count = getSelectedIds().length;
        var bulkBar = $('#bulk-actions-bar');

        if (count > 0) {
            bulkBar.slideDown();
        } else {
            bulkBar.slideUp();
        }

        $('#selected-count').text(count + ' selected');
        $('#bulk-delete-btn').prop('disabled', count === 0);
    }

    $(document).on('change', '.select-item', function() {
        updateUI();
        var totalCheckboxes = $('.select-item').length;
        var checkedCheckboxes = $('.select-item:checked').length;
        $('#table-select-all').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);
        $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);

        // Highlight row
        if ($(this).is(':checked')) {
            $(this).closest('tr').addClass('table-active');
        } else {
            $(this).closest('tr').removeClass('table-active');
        }
    });

    $('#clear-selection').on('click', function() {
        $('.select-item').prop('checked', false);
        $('#table-select-all').prop('checked', false);
        $('#select-all').prop('checked', false);
        $('.department-row').removeClass('table-active');
        updateUI();
    });

    $('#table-select-all').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.select-item').prop('checked', isChecked);
        $('#select-all').prop('checked', isChecked);
        if (isChecked) {
            $('.department-row').addClass('table-active');
        } else {
            $('.department-row').removeClass('table-active');
        }
        updateUI();
    });

    $('#select-all').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.select-item').prop('checked', isChecked);
        $('#table-select-all').prop('checked', isChecked);
        if (isChecked) {
            $('.department-row').addClass('table-active');
        } else {
            $('.department-row').removeClass('table-active');
        }
        updateUI();
    });

    // Bulk archive
    $('#bulk-delete-btn').on('click', function(e) {
        e.preventDefault();

        const ids = getSelectedIds();
        if (!ids.length) {
            alert('Please select at least one sub department');
            return;
        }

        if (!confirm(`Archive ${ids.length} selected sub department(s)?\n\nThey can be restored later from the archive page.`)) {
            return;
        }

        const deleteBtn = $(this);
        const originalText = deleteBtn.html();

        deleteBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Archiving...');
        deleteBtn.prop('disabled', true);

        $.ajax({
            url: "{{ \App\Support\WorkforceSettings::route('departments.bulk-delete') }}",
            method: 'POST',
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: JSON.stringify({ bulk_ids: ids }),
            success: function(res) {
                if (res.status === 'success') {
                    if (res.deleted_ids && res.deleted_ids.length) {
                        res.deleted_ids.forEach(function(id) {
                            const checkbox = $(`.select-item[value="${id}"]`);
                            if (checkbox.length) {
                                const row = checkbox.closest('tr');
                                row.fadeOut(300, function() {
                                    $(this).remove();
                                });
                            }
                        });
                    }

                    $('#select-all, #table-select-all').prop('checked', false);
                    $('.select-item').prop('checked', false);
                    $('.department-row').removeClass('table-active');

                    alert(res.message || (res.deleted + ' item(s) archived'));
                    updateUI();

                    if ($('.department-row').length === 0) {
                        setTimeout(() => location.reload(), 1500);
                    }
                } else {
                    alert(res.message || 'Bulk archive failed');
                }
            },
            error: function(xhr) {
                let message = 'Bulk archive failed';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                alert(message);
            },
            complete: function() {
                deleteBtn.html(originalText);
                deleteBtn.prop('disabled', false);
            }
        });
    });

    // Row click for edit
    $('.department-row').on('click', function(e) {
        if (!$(e.target).closest('input, a, button, .dropdown, .form-check, .action-btn, .dropdown-menu, .btn').length) {
            var showLink = $(this).find('a.dropdown-item[href*="/departments/"]').not('[href*="edit"]').attr('href');
            if (showLink) {
                window.location.href = showLink;
            }
        }
    });
});
</script>
@endpush

@endsection
