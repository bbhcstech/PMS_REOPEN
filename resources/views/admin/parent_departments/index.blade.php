@extends('admin.layout.app')

@section('title', 'Parent Departments')

@section('content')
@include('admin.settings.partials.workforce-navigation')
<div id="parentDepartmentPage" class="parent-department-page">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <i class="fas fa-sitemap"></i>
        <span>Admin</span>
        <span class="crumb-sep">/</span>
        <span>Settings</span>
        <span class="crumb-sep">/</span>
        <span class="crumb-current">Parent Department</span>
    </div>

    <!-- Header Card -->
    <div class="header-card">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="header-text">
                <h1>Parent Department Management</h1>
                <p>Manage organizational structure, department hierarchy and parent department records.</p>
            </div>
        </div>
        <div class="btn-group header-actions">
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-cloud-upload-alt"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button type="button" class="dropdown-item" onclick="exportTo('copy')"><i class="fas fa-copy text-primary"></i> Copy to Clipboard</button></li>
                    <li><button type="button" class="dropdown-item" onclick="exportTo('csv')"><i class="fas fa-file-csv text-success"></i> Export as CSV</button></li>
                    <li><button type="button" class="dropdown-item" onclick="exportTo('excel')"><i class="fas fa-file-excel text-success"></i> Export as Excel</button></li>
                    <li><button type="button" class="dropdown-item" onclick="exportTo('pdf')"><i class="fas fa-file-pdf text-danger"></i> Export as PDF</button></li>
                    <li><button type="button" class="dropdown-item" onclick="exportTo('print')"><i class="fas fa-print text-info"></i> Print</button></li>
                </ul>
            </div>
            <a href="{{ \App\Support\WorkforceSettings::route('parent-departments.archive') }}" class="btn btn-light">
                <i class="fas fa-box-archive"></i> Archived
                @if(($archivedCount ?? 0) > 0)
                    <span class="archive-count-badge">{{ $archivedCount }}</span>
                @endif
            </a>
            <a href="{{ \App\Support\WorkforceSettings::route('parent-departments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New
            </a>
        </div>
    </div>

    @php
        $totalDepartments = $departments->count();
        $activeDepartments = $departments->filter(function($d) { return $d->status ?? true; })->count();
        $recentCount = $departments->filter(function($d) { return $d->created_at >= now()->subDays(7); })->count();
    @endphp

    <!-- Stats Cards -->
    <div class="stats">
        <div class="stat-card">
            <div class="stat-icon stat-icon-total">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-body">
                <h3>{{ $totalDepartments }}</h3>
                <span>Total Departments</span>
                <p class="stat-sub">Available parent departments</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-active">
                <i class="far fa-check-circle"></i>
            </div>
            <div class="stat-body">
                <h3>{{ $activeDepartments }}</h3>
                <span>Active Departments</span>
                <p class="stat-sub">Currently in use</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-codes">
                <i class="fas fa-code"></i>
            </div>
            <div class="stat-body">
                <h3>{{ $departments->unique('dpt_code')->count() }}</h3>
                <span>Unique Codes</span>
                <p class="stat-sub">Department identifiers</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-recent">
                <i class="far fa-clock"></i>
            </div>
            <div class="stat-body">
                <h3>{{ $recentCount }}</h3>
                <span>Recently Added</span>
                <p class="stat-sub">Last 7 days</p>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if($errors->any() || session('error') || session('success') || session('warning'))
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
            @php
                $isEmployeeError = str_contains(session('error'), 'tagged with employees');
                $isSubDeptError = str_contains(session('error'), 'sub-departments');
                $alertClass = $isEmployeeError ? 'alert-warning' : ($isSubDeptError ? 'alert-info' : 'alert-danger');
                $icon = $isEmployeeError ? 'fa-users' : ($isSubDeptError ? 'fa-diagram-project' : 'fa-times-circle');
            @endphp
            <div class="alert {{ $alertClass }} alert-dismissible fade show" role="alert">
                <i class="fas {{ $icon }}"></i>
                <div>
                    @if($isEmployeeError)
                        <strong>Cannot Archive Department</strong>
                        <p class="mb-1">{{ session('error') }}</p>
                        <small class="text-muted"><i class="fas fa-info-circle"></i> Reassign employees before deletion</small>
                    @elseif($isSubDeptError)
                        <strong>Contains Sub-Departments</strong>
                        <p class="mb-1">{{ session('error') }}</p>
                        <small class="text-muted"><i class="fas fa-info-circle"></i> Remove or relocate sub-departments first</small>
                    @else
                        <strong>Error!</strong> {{ session('error') }}
                    @endif
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Notice!</strong> {{ session('warning') }}
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
                    <h3>Parent Department List</h3>
                    <span class="muted">{{ $departments->count() }} departments available</span>
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
            <table id="parentDepartmentTable" class="department-list-table">
                <thead>
                    <tr>
                        <th width="50">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="table-select-all">
                            </div>
                        </th>
                        <th>#</th>
                        <th><i class="fas fa-code"></i> Department Code</th>
                        <th><i class="fas fa-building"></i> Department Name</th>
                        <th><i class="far fa-calendar"></i> Created</th>
                        <th><i class="far fa-clock"></i> Last Updated</th>
                        <th class="text-end"><i class="fas fa-cog"></i> Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $index => $dpt)
                    <tr class="department-row" data-id="{{ $dpt->id }}">
                        <td>
                            <div class="form-check">
                                <input class="form-check-input select-item" type="checkbox" value="{{ $dpt->id }}">
                            </div>
                        </td>
                        <td>
                            <div class="department-info">
                                <div class="mini-icon">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <div>
                                    <strong class="department-number">{{ $index + 1 }}</strong>
                                    <span class="muted">ID: {{ str_pad($dpt->id, 3, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="department-info">
                                <div class="mini-icon">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <div>
                                    <strong class="department-code">{{ $dpt->dpt_code }}</strong>
                                    <span class="muted"><i class="fas fa-key"></i> Unique identifier</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="department-info">
                                <div class="mini-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div>
                                    <strong class="department-name">{{ $dpt->dpt_name }}</strong>
                                    <span class="muted"><i class="fas fa-folder"></i> Parent department</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div class="create-date">{{ $dpt->created_at->format('d M Y') }}</div>
                                <small class="text-muted">{{ $dpt->created_at->format('h:i A') }}</small>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div class="update-date">{{ $dpt->updated_at->format('d M Y') }}</div>
                                <small class="text-muted">{{ $dpt->updated_at->format('h:i A') }}</small>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="action-btn" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ \App\Support\WorkforceSettings::route('parent-departments.show', $dpt) }}">
                                            <i class="fas fa-eye"></i> View Department
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ \App\Support\WorkforceSettings::route('parent-departments.edit', $dpt) }}">
                                            <i class="fas fa-pen"></i> Edit Department
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button type="button" class="dropdown-item text-warning delete-btn"
                                                data-id="{{ $dpt->id }}"
                                                data-name="{{ $dpt->dpt_name }}"
                                                data-code="{{ $dpt->dpt_code }}">
                                            <i class="fas fa-box-archive"></i> Archive Department
                                        </button>
                                    </li>
                                    @if(auth()->user()?->hasModulePermission('department-parent', 'delete'))
                                    <li>
                                        {{-- Permanent delete (Archive above keeps it restorable). Refused while sub-departments or employees are linked. --}}
                                        <form method="POST" action="{{ \App\Support\WorkforceSettings::route('parent-departments.delete-permanent', $dpt->id) }}"
                                              onsubmit="return confirm('Permanently delete &quot;{{ addslashes($dpt->dpt_name) }}&quot;? This cannot be undone. Use Archive instead if you may need it again.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash-alt"></i> Delete Department
                                            </button>
                                        </form>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                            <form action="{{ \App\Support\WorkforceSettings::route('parent-departments.destroy', $dpt) }}"
                                  method="POST"
                                  class="d-none delete-form-{{ $dpt->id }}">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-0 border-0">
                            <div class="empty-state">
                                <div class="empty-icon-box">
                                    <i class="fas fa-building"></i>
                                </div>
                                <h5>No Parent Departments Found</h5>
                                <p>Get started by creating your first parent department to organize structure.</p>
                                <a href="{{ \App\Support\WorkforceSettings::route('parent-departments.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create Department
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
                Showing {{ $departments->count() }} of {{ $departments->count() }} departments
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
            <span class="legend-item"><i class="fas fa-building legend-icon-name"></i> Department Name</span>
            <span class="legend-item"><i class="fas fa-layer-group legend-icon-level"></i> Parent Level</span>
            <span class="legend-item"><i class="far fa-clock legend-icon-time"></i> Timestamps</span>
        </div>
    </div>
</div>

<style>
    /* ===== CLEAN ENTERPRISE SAAS THEME FOR PARENT DEPARTMENTS ===== */
    :root {
        --pd-bg: #f8fafc;
        --pd-surface: #ffffff;
        --pd-surface-subtle: #f8fafc;
        --pd-surface-hover: #f8fafc;
        --pd-border: #e2e8f0;
        --pd-border-subtle: #f1f5f9;
        --pd-text: #0f172a;
        --pd-text-body: #334155;
        --pd-text-muted: #64748b;
        --pd-text-faint: #94a3b8;
        --pd-primary: #2563eb;
        --pd-primary-hover: #1d4ed8;
        --pd-primary-soft: #eff6ff;
        --pd-primary-border: #dbeafe;
        --pd-shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
        --pd-shadow-sm: 0 4px 12px rgba(15, 23, 42, 0.06);
    }

    html:is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark),
    body:is([data-pms-theme="dark"], [data-bs-theme="dark"], [data-theme="dark"], .dark, .dark-mode) {
        --pd-bg: #070b1a;
        --pd-surface: #0f1530;
        --pd-surface-subtle: #141b3d;
        --pd-surface-hover: rgba(37, 99, 235, 0.1);
        --pd-border: rgba(148, 163, 184, 0.18);
        --pd-border-subtle: rgba(148, 163, 184, 0.12);
        --pd-text: #f8fafc;
        --pd-text-body: #e2e8f0;
        --pd-text-muted: #94a3b8;
        --pd-text-faint: #64748b;
        --pd-primary: #3b82f6;
        --pd-primary-hover: #60a5fa;
        --pd-primary-soft: rgba(59, 130, 246, 0.16);
        --pd-primary-border: rgba(96, 165, 250, 0.3);
        --pd-shadow-xs: 0 4px 12px rgba(0, 0, 0, 0.25);
        --pd-shadow-sm: 0 12px 28px rgba(0, 0, 0, 0.36);
    }

    body:has(#parentDepartmentPage),
    body:has(#parentDepartmentPage) .layout-page,
    body:has(#parentDepartmentPage) .content-wrapper {
        background: var(--pd-bg) !important;
        background-image: none !important;
    }

    #parentDepartmentPage.parent-department-page {
        padding: 18px 28px 34px !important;
        min-height: 100vh;
        background: var(--pd-bg) !important;
        background-image: none !important;
        color: var(--pd-text);
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        position: relative;
    }

    #parentDepartmentPage.parent-department-page::before {
        display: none !important;
        content: none !important;
    }

    /* Breadcrumb */
    #parentDepartmentPage .breadcrumb {
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
        color: var(--pd-text-muted) !important;
    }

    #parentDepartmentPage .breadcrumb i {
        color: var(--pd-text-faint) !important;
        font-size: 0.78rem;
        margin-right: 2px;
    }

    #parentDepartmentPage .breadcrumb .crumb-sep {
        color: var(--pd-text-faint);
    }

    #parentDepartmentPage .breadcrumb .crumb-current {
        color: var(--pd-primary) !important;
        font-weight: 600;
    }

    /* Header Card */
    #parentDepartmentPage .header-card {
        background: var(--pd-surface) !important;
        backdrop-filter: none !important;
        border: 1px solid var(--pd-border) !important;
        border-radius: 14px !important;
        padding: 1.2rem 1.45rem !important;
        margin-bottom: 1.15rem !important;
        box-shadow: var(--pd-shadow-xs) !important;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.25rem;
        flex-wrap: wrap;
        position: relative;
        z-index: 50;
        overflow: visible !important;
    }

    #parentDepartmentPage .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 0;
        flex: 1 1 360px;
    }

    #parentDepartmentPage .header-icon {
        width: 44px !important;
        height: 44px !important;
        background: var(--pd-primary) !important;
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

    #parentDepartmentPage .header-icon > i,
    #parentDepartmentPage .header-icon > i::before {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    #parentDepartmentPage .header-text {
        min-width: 0;
    }

    #parentDepartmentPage .header-card h1 {
        font-size: 1.2rem !important;
        font-weight: 700 !important;
        line-height: 1.25 !important;
        margin: 0 0 0.2rem 0 !important;
        color: var(--pd-text) !important;
        background: none !important;
        -webkit-background-clip: initial !important;
        -webkit-text-fill-color: var(--pd-text) !important;
        letter-spacing: -0.02em;
    }

    #parentDepartmentPage .header-card p {
        color: var(--pd-text-muted) !important;
        -webkit-text-fill-color: var(--pd-text-muted) !important;
        font-size: 0.82rem !important;
        font-weight: 400 !important;
        line-height: 1.45 !important;
        margin: 0 !important;
    }

    /* Header Action Buttons */
    #parentDepartmentPage .header-actions.btn-group {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 0.55rem !important;
        position: relative;
        z-index: 60;
        overflow: visible;
    }

    #parentDepartmentPage .header-actions .dropdown {
        position: relative;
        z-index: 60;
        overflow: visible;
    }

    #parentDepartmentPage .btn {
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

    #parentDepartmentPage .btn i {
        font-size: 0.88rem;
    }

    #parentDepartmentPage .btn-light {
        background: var(--pd-surface) !important;
        border: 1px solid var(--pd-border) !important;
        color: var(--pd-text-body) !important;
        -webkit-text-fill-color: var(--pd-text-body) !important;
        box-shadow: var(--pd-shadow-xs) !important;
    }

    #parentDepartmentPage .btn-light:hover {
        background: var(--pd-surface-subtle) !important;
        border-color: #cbd5e1 !important;
        color: var(--pd-text) !important;
        -webkit-text-fill-color: var(--pd-text) !important;
        transform: none !important;
    }

    #parentDepartmentPage .btn-primary {
        background: var(--pd-primary) !important;
        background-image: none !important;
        border: 1px solid var(--pd-primary) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: var(--pd-shadow-xs) !important;
    }

    #parentDepartmentPage .btn-primary:hover {
        background: var(--pd-primary-hover) !important;
        border-color: var(--pd-primary-hover) !important;
        color: #ffffff !important;
        transform: none !important;
        box-shadow: var(--pd-shadow-sm) !important;
    }

    #parentDepartmentPage .btn-primary i,
    #parentDepartmentPage .btn-primary i::before {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    #parentDepartmentPage .archive-count-badge {
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
    }

    #parentDepartmentPage .header-card .dropdown-menu {
        z-index: 9999 !important;
        min-width: 220px;
        margin-top: 8px !important;
        padding: 0.45rem !important;
        border: 1px solid var(--pd-border) !important;
        border-radius: 12px !important;
        background: var(--pd-surface) !important;
        box-shadow: 0 14px 30px -8px rgba(15, 23, 42, 0.16) !important;
    }

    #parentDepartmentPage .header-card .dropdown-menu.show {
        display: block;
    }

    /* ===== STATS GRID (Overrides global pms-refresh.css :first-of-type highlight) ===== */
    #parentDepartmentPage .stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.15rem;
        position: relative;
        z-index: 1;
    }

    #parentDepartmentPage .stat-card,
    #parentDepartmentPage .stat-card:first-of-type,
    #parentDepartmentPage .stat-card.is-featured {
        background: var(--pd-surface) !important;
        background-image: none !important;
        border: 1px solid var(--pd-border) !important;
        border-radius: 14px !important;
        padding: 1rem 1.2rem !important;
        box-shadow: var(--pd-shadow-xs) !important;
        color: var(--pd-text) !important;
        display: flex !important;
        align-items: center !important;
        gap: 0.9rem !important;
        position: relative;
        overflow: hidden;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    #parentDepartmentPage .stat-card::after {
        display: none !important;
        content: none !important;
    }

    #parentDepartmentPage .stat-card:hover {
        transform: none !important;
        border-color: #cbd5e1 !important;
        box-shadow: var(--pd-shadow-sm) !important;
    }

    #parentDepartmentPage .stat-card .stat-icon,
    #parentDepartmentPage .stat-card:first-of-type .stat-icon {
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

    #parentDepartmentPage .stat-card .stat-icon.stat-icon-total {
        background: #eff6ff !important;
        color: #2563eb !important;
    }

    #parentDepartmentPage .stat-card .stat-icon.stat-icon-active {
        background: #ecfdf5 !important;
        color: #16a34a !important;
    }

    #parentDepartmentPage .stat-card .stat-icon.stat-icon-codes {
        background: #eef2ff !important;
        color: #4f46e5 !important;
    }

    #parentDepartmentPage .stat-card .stat-icon.stat-icon-recent {
        background: #fffbeb !important;
        color: #d97706 !important;
    }

    #parentDepartmentPage .stat-card .stat-icon > i,
    #parentDepartmentPage .stat-card .stat-icon > i::before,
    #parentDepartmentPage .stat-card:first-of-type .stat-icon > i,
    #parentDepartmentPage .stat-card:first-of-type .stat-icon > i::before {
        color: inherit !important;
        -webkit-text-fill-color: currentColor !important;
        fill: currentColor !important;
        background: transparent !important;
        font-size: 1rem !important;
    }

    #parentDepartmentPage .stat-card .stat-body {
        min-width: 0;
    }

    #parentDepartmentPage .stat-card h3,
    #parentDepartmentPage .stat-card:first-of-type h3 {
        font-size: 1.4rem !important;
        font-weight: 700 !important;
        color: var(--pd-text) !important;
        -webkit-text-fill-color: var(--pd-text) !important;
        margin: 0 0 0.2rem 0 !important;
        line-height: 1.15 !important;
        letter-spacing: -0.02em;
    }

    #parentDepartmentPage .stat-card span,
    #parentDepartmentPage .stat-card:first-of-type span {
        display: block;
        color: var(--pd-text-muted) !important;
        -webkit-text-fill-color: var(--pd-text-muted) !important;
        font-size: 0.68rem !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        line-height: 1.3;
    }

    #parentDepartmentPage .stat-card .stat-sub,
    #parentDepartmentPage .stat-card:first-of-type .stat-sub {
        font-size: 0.72rem !important;
        color: var(--pd-text-faint) !important;
        -webkit-text-fill-color: var(--pd-text-faint) !important;
        margin: 0.14rem 0 0 0 !important;
        font-weight: 400 !important;
        line-height: 1.35;
    }

    /* Alerts */
    #parentDepartmentPage .alert {
        border-radius: 12px;
        border: 1px solid var(--pd-border);
        padding: 0.9rem 1.15rem;
        margin-bottom: 1.15rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        font-size: 0.85rem;
    }

    #parentDepartmentPage .alert-success {
        background: #ecfdf5;
        color: #065f46;
        border-color: #a7f3d0;
    }

    #parentDepartmentPage .alert-danger {
        background: #fef2f2;
        color: #991b1b;
        border-color: #fecaca;
    }

    #parentDepartmentPage .alert-warning {
        background: #fffbeb;
        color: #92400e;
        border-color: #fde68a;
    }

    #parentDepartmentPage .alert-info {
        background: #eff6ff;
        color: #1e40af;
        border-color: #bfdbfe;
    }

    /* ===== MAIN TABLE CARD ===== */
    #parentDepartmentPage .table-card {
        background: var(--pd-surface) !important;
        border-radius: 14px !important;
        border: 1px solid var(--pd-border) !important;
        box-shadow: var(--pd-shadow-xs) !important;
        overflow: hidden;
        position: relative;
        z-index: 1;
    }

    #parentDepartmentPage .table-card:has(.dropdown-menu.show),
    #parentDepartmentPage .table-card:has(.pms-table-export.is-open) {
        overflow: visible !important;
    }

    #parentDepartmentPage .table-header {
        padding: 1rem 1.35rem !important;
        background: var(--pd-surface) !important;
        background-image: none !important;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        border-bottom: 1px solid var(--pd-border) !important;
    }

    #parentDepartmentPage .table-title {
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }

    #parentDepartmentPage .table-title-icon {
        width: 36px !important;
        height: 36px !important;
        background: var(--pd-primary-soft) !important;
        background-image: none !important;
        color: var(--pd-primary) !important;
        border: 1px solid var(--pd-primary-border) !important;
        border-radius: 10px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.92rem;
        flex-shrink: 0;
    }

    #parentDepartmentPage .table-title-icon i {
        color: var(--pd-primary) !important;
        -webkit-text-fill-color: var(--pd-primary) !important;
    }

    #parentDepartmentPage .table-title h3 {
        font-size: 0.98rem !important;
        font-weight: 700 !important;
        color: var(--pd-text) !important;
        -webkit-text-fill-color: var(--pd-text) !important;
        margin: 0 !important;
        line-height: 1.25;
    }

    #parentDepartmentPage .table-title .muted {
        display: block;
        margin-top: 2px !important;
        font-size: 0.76rem !important;
        color: var(--pd-text-muted) !important;
        -webkit-text-fill-color: var(--pd-text-muted) !important;
        font-weight: 400 !important;
    }

    /* Search Input */
    #parentDepartmentPage .search-box {
        position: relative;
    }

    #parentDepartmentPage .search-box input {
        width: 245px !important;
        min-height: 36px !important;
        height: 36px !important;
        padding: 0 14px 0 36px !important;
        border-radius: 9px !important;
        border: 1px solid var(--pd-border) !important;
        background: var(--pd-surface) !important;
        color: var(--pd-text) !important;
        font-size: 0.82rem !important;
        font-weight: 400 !important;
        outline: none;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    #parentDepartmentPage .search-box input::placeholder {
        color: var(--pd-text-faint) !important;
    }

    #parentDepartmentPage .search-box input:focus {
        border-color: var(--pd-primary) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
    }

    #parentDepartmentPage .search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--pd-text-faint) !important;
        font-size: 0.82rem;
    }

    /* Bulk Actions Bar */
    #parentDepartmentPage .bulk-actions-bar {
        padding: 0.68rem 1.35rem;
        background: var(--pd-primary-soft);
        border-bottom: 1px solid var(--pd-primary-border);
    }

    #parentDepartmentPage .bulk-actions-content {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        flex-wrap: wrap;
    }

    #parentDepartmentPage .selected-badge {
        padding: 0.25rem 0.75rem;
        background: var(--pd-surface);
        color: var(--pd-primary);
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        border: 1px solid var(--pd-primary-border);
    }

    #parentDepartmentPage .btn-clear,
    #parentDepartmentPage .btn-bulk-delete {
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

    #parentDepartmentPage .btn-clear {
        background: var(--pd-surface);
        color: var(--pd-text-body);
        border: 1px solid var(--pd-border);
    }

    #parentDepartmentPage .btn-bulk-delete {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    #parentDepartmentPage .btn-bulk-delete:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* pms-table-tools Bar inside .table-card */
    #parentDepartmentPage .table-card .pms-table-tools {
        background: var(--pd-surface) !important;
        border: 0 !important;
        border-bottom: 1px solid var(--pd-border) !important;
        border-radius: 0 !important;
        min-height: 48px !important;
        padding: 0.55rem 1.35rem !important;
        box-shadow: none !important;
    }

    #parentDepartmentPage .table-card .pms-table-tools__status {
        color: var(--pd-text-muted) !important;
        font-size: 0.8rem !important;
        font-weight: 500 !important;
    }

    #parentDepartmentPage .table-card .pms-table-export__toggle {
        background: var(--pd-surface) !important;
        border: 1px solid var(--pd-border) !important;
        color: var(--pd-text-body) !important;
        border-radius: 8px !important;
        font-size: 0.8rem !important;
        font-weight: 600 !important;
        min-height: 34px !important;
        padding: 0.35rem 0.78rem !important;
    }

    #parentDepartmentPage .table-card .pms-table-export__toggle:hover,
    #parentDepartmentPage .table-card .pms-table-export.is-open .pms-table-export__toggle {
        background: var(--pd-surface-subtle) !important;
        border-color: #cbd5e1 !important;
        color: var(--pd-text) !important;
        transform: none !important;
    }

    #parentDepartmentPage .table-card .pms-table-scroll-host {
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    #parentDepartmentPage .table-card .pms-table-select {
        background: transparent !important;
    }

    #parentDepartmentPage .table-card .pms-table-select::after {
        display: none !important;
    }

    /* DataTables Export Buttons Strip (visible when rows exist) */
    #parentDepartmentPage .dataTables_wrapper .dt-buttons {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.45rem;
        width: fit-content;
        margin: 0.75rem 1.35rem;
        padding: 0.35rem;
        border: 1px solid var(--pd-border);
        border-radius: 10px;
        background: var(--pd-surface-subtle);
    }

    #parentDepartmentPage .dataTables_wrapper .dt-buttons .dt-button {
        min-height: 32px;
        margin: 0 !important;
        padding: 0.35rem 0.72rem !important;
        border: 1px solid var(--pd-border) !important;
        border-radius: 7px !important;
        background: var(--pd-surface) !important;
        color: var(--pd-text-body) !important;
        box-shadow: none !important;
        font-size: 0.78rem !important;
        font-weight: 600 !important;
        line-height: 1.2 !important;
    }

    #parentDepartmentPage .dataTables_wrapper .dt-buttons .dt-button span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    #parentDepartmentPage .dataTables_wrapper .dt-buttons .dt-button span::before {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-family: "Font Awesome 5 Free";
        font-size: 0.78rem;
        font-weight: 900;
    }

    #parentDepartmentPage .dataTables_wrapper .buttons-copy span::before { content: "\f0c5"; }
    #parentDepartmentPage .dataTables_wrapper .buttons-csv span::before { content: "\f6dd"; }
    #parentDepartmentPage .dataTables_wrapper .buttons-excel span::before { content: "\f1c3"; }
    #parentDepartmentPage .dataTables_wrapper .buttons-pdf span::before { content: "\f1c1"; }
    #parentDepartmentPage .dataTables_wrapper .buttons-print span::before { content: "\f02f"; }

    #parentDepartmentPage .dataTables_wrapper .dt-buttons .dt-button:hover,
    #parentDepartmentPage .dataTables_wrapper .dt-buttons .dt-button:focus {
        border-color: var(--pd-primary) !important;
        background: var(--pd-primary) !important;
        color: #ffffff !important;
    }

    /* Table Wrapper & Flat Table */
    #parentDepartmentPage .table-wrapper {
        overflow-x: auto;
        padding: 0 !important;
    }

    #parentDepartmentPage .department-list-table {
        width: 100%;
        min-width: 960px;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        margin: 0 !important;
    }

    #parentDepartmentPage .department-list-table thead th {
        text-align: left;
        padding: 0.8rem 1rem !important;
        background: var(--pd-surface-subtle) !important;
        color: var(--pd-text-muted) !important;
        -webkit-text-fill-color: var(--pd-text-muted) !important;
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid var(--pd-border) !important;
        white-space: nowrap;
    }

    #parentDepartmentPage .department-list-table thead th i {
        margin-right: 6px;
        font-size: 0.72rem;
        color: var(--pd-text-faint) !important;
        -webkit-text-fill-color: var(--pd-text-faint) !important;
    }

    #parentDepartmentPage .department-list-table tbody td {
        background: var(--pd-surface) !important;
        padding: 0.9rem 1rem !important;
        border-top: 0 !important;
        border-left: 0 !important;
        border-right: 0 !important;
        border-bottom: 1px solid var(--pd-border-subtle) !important;
        border-radius: 0 !important;
        font-size: 0.85rem !important;
        font-weight: 500;
        color: var(--pd-text-body) !important;
        vertical-align: middle;
        transition: background-color 0.15s ease;
    }

    #parentDepartmentPage .department-list-table tbody tr:hover td,
    #parentDepartmentPage .department-list-table.pms-exportable-table > tbody > tr:hover > :is(th, td) {
        background: var(--pd-surface-hover) !important;
    }

    #parentDepartmentPage .department-info {
        display: flex;
        align-items: center;
        gap: 0.72rem;
    }

    #parentDepartmentPage .mini-icon {
        width: 34px !important;
        height: 34px !important;
        border-radius: 9px !important;
        background: var(--pd-primary-soft) !important;
        background-image: none !important;
        border: 1px solid var(--pd-primary-border) !important;
        color: var(--pd-primary) !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.88rem !important;
        flex-shrink: 0;
    }

    #parentDepartmentPage .mini-icon i {
        color: var(--pd-primary) !important;
        -webkit-text-fill-color: var(--pd-primary) !important;
    }

    #parentDepartmentPage .department-number,
    #parentDepartmentPage .department-name,
    #parentDepartmentPage .create-date,
    #parentDepartmentPage .update-date {
        font-weight: 600 !important;
        color: var(--pd-text) !important;
        -webkit-text-fill-color: var(--pd-text) !important;
        font-size: 0.86rem !important;
    }

    #parentDepartmentPage .department-code {
        font-weight: 700 !important;
        color: var(--pd-primary) !important;
        -webkit-text-fill-color: var(--pd-primary) !important;
        font-size: 0.86rem !important;
    }

    #parentDepartmentPage .department-list-table .muted,
    #parentDepartmentPage .department-list-table small {
        display: block;
        margin-top: 2px !important;
        color: var(--pd-text-muted) !important;
        -webkit-text-fill-color: var(--pd-text-muted) !important;
        font-size: 0.74rem !important;
        font-weight: 400 !important;
    }

    /* Row Action Button & Dropdown */
    #parentDepartmentPage .action-btn {
        width: 32px !important;
        height: 32px !important;
        border-radius: 8px !important;
        border: 1px solid var(--pd-border) !important;
        background: var(--pd-surface-subtle) !important;
        color: var(--pd-text-muted) !important;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.16s ease, color 0.16s ease, border-color 0.16s ease;
    }

    #parentDepartmentPage .action-btn:hover {
        background: var(--pd-primary-soft) !important;
        border-color: var(--pd-primary-border) !important;
        color: var(--pd-primary) !important;
        transform: none !important;
    }

    #parentDepartmentPage .dropdown-menu {
        background: var(--pd-surface) !important;
        border: 1px solid var(--pd-border) !important;
        border-radius: 12px !important;
        padding: 0.4rem !important;
        box-shadow: 0 14px 30px -8px rgba(15, 23, 42, 0.16) !important;
        z-index: 9999;
    }

    #parentDepartmentPage .dropdown-item {
        padding: 0.5rem 0.8rem !important;
        color: var(--pd-text-body) !important;
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
    }

    #parentDepartmentPage .dropdown-item:hover {
        background: var(--pd-primary-soft) !important;
        color: var(--pd-primary) !important;
    }

    #parentDepartmentPage .dropdown-item.text-danger:hover {
        background: #fef2f2 !important;
        color: #dc2626 !important;
    }

    /* Form Check */
    #parentDepartmentPage .form-check {
        display: flex;
        align-items: center;
        margin: 0;
        min-height: 0;
    }

    #parentDepartmentPage .form-check-input {
        width: 16px;
        height: 16px;
        background-color: var(--pd-surface);
        border: 1.5px solid #cbd5e1;
        border-radius: 4px;
        cursor: pointer;
    }

    #parentDepartmentPage .form-check-input:checked {
        background-color: var(--pd-primary);
        border-color: var(--pd-primary);
    }

    /* Empty State */
    #parentDepartmentPage .empty-state {
        padding: 3.4rem 1.5rem !important;
        text-align: center;
    }

    #parentDepartmentPage .empty-icon-box {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: var(--pd-primary-soft);
        border: 1px solid var(--pd-primary-border);
        color: var(--pd-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        margin-bottom: 1rem;
    }

    #parentDepartmentPage .empty-icon-box i {
        color: var(--pd-primary) !important;
        -webkit-text-fill-color: var(--pd-primary) !important;
        margin: 0 !important;
    }

    #parentDepartmentPage .empty-state h5 {
        color: var(--pd-text) !important;
        -webkit-text-fill-color: var(--pd-text) !important;
        font-size: 0.98rem !important;
        font-weight: 700 !important;
        margin-bottom: 0.35rem;
    }

    #parentDepartmentPage .empty-state p {
        color: var(--pd-text-muted) !important;
        -webkit-text-fill-color: var(--pd-text-muted) !important;
        font-size: 0.82rem !important;
        margin: 0 auto 1.15rem;
        max-width: 390px;
        line-height: 1.45;
    }

    /* Footer */
    #parentDepartmentPage .footer {
        padding: 0.85rem 1.35rem !important;
        background: var(--pd-surface) !important;
        border-top: 1px solid var(--pd-border) !important;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.85rem;
        color: var(--pd-text-muted) !important;
        font-size: 0.8rem !important;
        font-weight: 500;
    }

    #parentDepartmentPage .show-entries {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    #parentDepartmentPage .show-entries select {
        background: var(--pd-surface) !important;
        border: 1px solid var(--pd-border) !important;
        color: var(--pd-text) !important;
        border-radius: 8px;
        padding: 0.28rem 0.6rem;
        height: 32px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
    }

    #parentDepartmentPage .pagination-info {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.8rem !important;
        color: var(--pd-text-muted) !important;
    }

    #parentDepartmentPage .pagination-info i {
        color: var(--pd-primary) !important;
    }

    /* Bottom Legend Card */
    #parentDepartmentPage .legend-card {
        margin-top: 1rem !important;
        background: var(--pd-surface) !important;
        border: 1px solid var(--pd-border) !important;
        border-radius: 12px !important;
        padding: 0.78rem 1.25rem !important;
        box-shadow: var(--pd-shadow-xs) !important;
        position: relative;
        z-index: 1;
    }

    #parentDepartmentPage .legend-content {
        display: flex;
        align-items: center;
        gap: 1.2rem;
        flex-wrap: wrap;
    }

    #parentDepartmentPage .legend-title {
        display: inline-flex;
        align-items: center;
        gap: 0.48rem;
        font-weight: 700 !important;
        color: var(--pd-text) !important;
        -webkit-text-fill-color: var(--pd-text) !important;
        font-size: 0.78rem !important;
    }

    #parentDepartmentPage .legend-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--pd-primary);
        display: inline-block;
        flex-shrink: 0;
    }

    #parentDepartmentPage .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 0.38rem;
        font-size: 0.78rem !important;
        color: var(--pd-text-muted) !important;
        -webkit-text-fill-color: var(--pd-text-muted) !important;
        font-weight: 500;
    }

    #parentDepartmentPage .legend-item i {
        font-size: 0.78rem !important;
        margin: 0 !important;
    }

    #parentDepartmentPage .legend-icon-code { color: var(--pd-primary) !important; }
    #parentDepartmentPage .legend-icon-name { color: #3b82f6 !important; }
    #parentDepartmentPage .legend-icon-level { color: #6366f1 !important; }
    #parentDepartmentPage .legend-icon-time { color: #d97706 !important; }

    /* Dark Mode Badge & Accent Adjustments */
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #parentDepartmentPage .stat-card .stat-icon.stat-icon-total {
        background: rgba(59, 130, 246, 0.16) !important;
        color: #60a5fa !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #parentDepartmentPage .stat-card .stat-icon.stat-icon-active {
        background: rgba(16, 185, 129, 0.16) !important;
        color: #34d399 !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #parentDepartmentPage .stat-card .stat-icon.stat-icon-codes {
        background: rgba(99, 102, 241, 0.18) !important;
        color: #a5b4fc !important;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body[data-bs-theme="dark"], body[data-theme="dark"], body.dark, body.dark-mode) #parentDepartmentPage .stat-card .stat-icon.stat-icon-recent {
        background: rgba(245, 158, 11, 0.16) !important;
        color: #fbbf24 !important;
    }

    /* Toast notifications */
    .toast-alert .alert {
        border-radius: 12px;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.14);
        min-width: 320px;
        background: var(--pd-surface) !important;
        border: 1px solid var(--pd-border) !important;
    }

    /* Archive button loading state */
    #parentDepartmentPage .delete-btn.loading {
        pointer-events: none;
        opacity: 0.6;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        #parentDepartmentPage .stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 992px) {
        #parentDepartmentPage.parent-department-page {
            padding: 16px 20px 28px !important;
        }

        #parentDepartmentPage .header-card {
            flex-direction: column;
            align-items: flex-start;
        }

        #parentDepartmentPage .header-actions.btn-group {
            width: 100%;
            justify-content: flex-start;
        }
    }

    @media (max-width: 768px) {
        #parentDepartmentPage .stats {
            grid-template-columns: 1fr;
        }

        #parentDepartmentPage .table-header {
            flex-direction: column;
            align-items: stretch;
        }

        #parentDepartmentPage .search-box input {
            width: 100% !important;
        }

        #parentDepartmentPage .footer {
            flex-direction: column;
            text-align: center;
        }
    }

    @media (max-width: 576px) {
        #parentDepartmentPage.parent-department-page {
            padding: 14px !important;
        }

        #parentDepartmentPage .header-card {
            padding: 1rem !important;
        }

        #parentDepartmentPage .header-actions.btn-group .btn,
        #parentDepartmentPage .header-actions.btn-group .dropdown {
            flex: 1 1 auto;
        }

        #parentDepartmentPage .header-actions.btn-group .dropdown .btn {
            width: 100%;
        }
    }

    @media print {
        #parentDepartmentPage .btn-group,
        #parentDepartmentPage .search-box,
        #parentDepartmentPage .bulk-actions-bar,
        #parentDepartmentPage .legend-card,
        #parentDepartmentPage .action-btn,
        #parentDepartmentPage .dropdown,
        #parentDepartmentPage .pms-table-tools {
            display: none !important;
        }

        #parentDepartmentPage.parent-department-page {
            background: #ffffff !important;
            padding: 0 !important;
        }
    }
</style>

@push('js')
<script>
$(document).ready(function() {
    var table = null;

    @if($departments->count() > 0)
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#parentDepartmentTable')) {
        table = $('#parentDepartmentTable').DataTable({
            dom: 'lBfrtip',
            paging: false,
            searching: true,
            info: false,
            ordering: true,
            buttons: [
                {
                    extend: 'copyHtml5',
                    text: 'Copy',
                    title: 'Parent Department List',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5],
                        rows: function(idx, data, node) { return $(node).is(':visible'); },
                        modifier: { search: 'applied' }
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: 'CSV',
                    title: 'Parent Department List',
                    filename: 'parent-department-list',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5],
                        rows: function(idx, data, node) { return $(node).is(':visible'); },
                        modifier: { search: 'applied' }
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    title: 'Parent Department List',
                    filename: 'parent-department-list',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5],
                        rows: function(idx, data, node) { return $(node).is(':visible'); },
                        modifier: { search: 'applied' }
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: 'PDF',
                    title: 'Parent Department List',
                    filename: 'parent-department-list',
                    pageSize: 'A4',
                    orientation: 'landscape',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5],
                        rows: function(idx, data, node) { return $(node).is(':visible'); },
                        modifier: { search: 'applied' }
                    }
                },
                {
                    extend: 'print',
                    text: 'Print',
                    title: 'Parent Department List',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5],
                        rows: function(idx, data, node) { return $(node).is(':visible'); },
                        modifier: { search: 'applied' }
                    }
                }
            ],
            language: {
                search: "",
                searchPlaceholder: "Search departments...",
                zeroRecords: "No matching records found",
            },
            initComplete: function() {
                $('.dataTables_filter').hide();
            }
        });
    }
    @endif

    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Custom search
    $('#departmentSearch').on('keyup', function() {
        if (table) {
            table.search(this.value).draw();
            return;
        }

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

    // Individual archive
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const deleteBtn = $(this);
        const departmentId = deleteBtn.data('id');
        const departmentName = deleteBtn.data('name');
        const departmentCode = deleteBtn.data('code');

        if (!confirm(`Archive department "${departmentName}" (${departmentCode})?\n\nIt will move to Archived Parent Departments and can be restored later.`)) {
            return;
        }

        const originalHtml = deleteBtn.html();
        deleteBtn.prop('disabled', true);
        deleteBtn.addClass('loading');

        $.ajax({
            url: `{{ \App\Support\WorkforceSettings::route('parent-departments.destroy', ':id') }}`.replace(':id', departmentId),
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: { _method: 'DELETE' },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const row = deleteBtn.closest('tr');
                    row.fadeOut(400, function() {
                        if (table) {
                            table.row(row).remove().draw(false);
                        } else {
                            $(this).remove();
                        }
                        showToast('success', 'Department archived successfully');
                        // Update counts
                        updateStats();
                        if ($('.department-row').length === 0) {
                            location.reload();
                        }
                    });
                } else {
                    handleDeleteError(response.message);
                }
            },
            error: function(xhr) {
                let message = 'Error deleting department';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.status === 422) {
                    message = 'Validation error: Cannot archive department.';
                }
                handleDeleteError(message);
            },
            complete: function() {
                deleteBtn.html(originalHtml);
                deleteBtn.prop('disabled', false);
                deleteBtn.removeClass('loading');
            }
        });
    });

    // Bulk archive
    $('#bulk-delete-btn').on('click', function(e) {
        e.preventDefault();

        const ids = getSelectedIds();
        if (!ids.length) {
            showToast('warning', 'Please select at least one department');
            return;
        }

        if (!confirm(`Archive ${ids.length} selected department(s)?\n\nThey will move to Archived Parent Departments and can be restored later.`)) {
            return;
        }

        const deleteBtn = $(this);
        const originalText = deleteBtn.html();

        deleteBtn.text('Archiving...');
        deleteBtn.prop('disabled', true);

        $.ajax({
            url: "{{ \App\Support\WorkforceSettings::route('parent-departments.bulk-delete') }}",
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: { bulk_ids: ids },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success' || res.status === 'warning') {
                    if (res.deleted_ids && res.deleted_ids.length) {
                        res.deleted_ids.forEach(function(id) {
                            const checkbox = $(`.select-item[value="${id}"]`);
                            if (checkbox.length) {
                                const row = checkbox.closest('tr');
                                row.fadeOut(300, function() {
                                    if (table) {
                                        table.row(row).remove().draw(false);
                                    } else {
                                        $(this).remove();
                                    }
                                });
                            }
                        });
                    }

                    $('#select-all, #table-select-all').prop('checked', false);
                    $('.select-item').prop('checked', false);
                    $('.department-row').removeClass('table-active');

                    showToast(res.status === 'warning' ? 'warning' : 'success', res.message);
                    updateUI();
                    updateStats();

                    if ($('.department-row').length === 0) {
                        setTimeout(() => location.reload(), 1500);
                    }
                } else {
                    showToast('danger', res.message || 'Error deleting departments');
                }
            },
            error: function(xhr) {
                let message = 'Error deleting departments';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.status === 422) {
                    message = 'Validation error occurred.';
                }
                showToast('danger', message);
            },
            complete: function() {
                deleteBtn.html(originalText);
                deleteBtn.prop('disabled', false);
            }
        });
    });

    // Helper function to handle archive errors
    function handleDeleteError(message) {
        let type = 'danger';
        let formattedMessage = message;

        if (message.includes('tagged with employees')) {
            formattedMessage = `<div class="mb-2"><strong>Cannot Archive Department</strong></div>
                              <div>${message}</div>
                              <div class="mt-2 small text-muted"><i class="fas fa-info-circle me-1"></i>Reassign employees to another department before deletion.</div>`;
            type = 'warning';
        } else if (message.includes('sub-departments')) {
            formattedMessage = `<div class="mb-2"><strong>Contains Sub-Departments</strong></div>
                              <div>${message}</div>
                              <div class="mt-2 small text-muted"><i class="fas fa-info-circle me-1"></i>Move sub-departments first if needed.</div>`;
            type = 'info';
        }

        showToast(type, formattedMessage);
    }

    // Toast notification function
    function showToast(type, message) {
        $('.toast-alert').remove();

        const icons = {
            'success': 'fas fa-check-circle text-success',
            'danger': 'fas fa-times-circle text-danger',
            'warning': 'fas fa-exclamation-triangle text-warning',
            'info': 'fas fa-info-circle text-info'
        };

        const colors = {
            'success': 'alert-success',
            'danger': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        };

        const toast = $(`
            <div class="toast-alert position-fixed top-0 end-0 p-3" style="z-index: 1060; max-width: 420px;">
                <div class="alert ${colors[type] || 'alert-info'} border-0 shadow-lg rounded-4 fade show d-flex align-items-start gap-3" role="alert">
                    <i class="${icons[type] || 'fas fa-info-circle'} fs-4"></i>
                    <div class="flex-grow-1">${message}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        `);

        $('body').append(toast);

        setTimeout(() => {
            toast.find('.alert').alert('close');
            setTimeout(() => toast.remove(), 300);
        }, 7000);
    }

    // Update stats function
    function updateStats() {
        var remaining = $('.department-row').length;
        $('.stat-card:first h3').text(remaining);
        // Update other stats as needed
    }

    // Export functions
    window.exportTo = function(format) {
        var buttonSelectors = {
            copy: '.buttons-copy',
            csv: '.buttons-csv',
            excel: '.buttons-excel',
            pdf: '.buttons-pdf',
            print: '.buttons-print'
        };
        var selector = buttonSelectors[format];

        if (!table || !selector || !table.button(selector).node()) {
            if (typeof toastr !== 'undefined') {
                toastr.error('The selected export option is currently unavailable.');
            } else {
                alert('The selected export option is currently unavailable.');
            }
            return;
        }

        table.button(selector).trigger();

        if (typeof toastr !== 'undefined' && format !== 'copy' && format !== 'print') {
            toastr.success(format.toUpperCase() + ' export started.');
        }
    };

    window.printTable = function() {
        exportTo('print');
    };

    // Row click for view details
    $('.department-row').on('click', function(e) {
        if (!$(e.target).closest('input, a, button, .dropdown, .form-check, .action-btn, .dropdown-menu, .btn').length) {
            var departmentId = $(this).data('id');
            if (departmentId) {
                window.location.href = "{{ \App\Support\WorkforceSettings::route('parent-departments.show', '') }}/" + departmentId;
            }
        }
    });
});
</script>
@endpush

@endsection
