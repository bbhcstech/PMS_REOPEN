@extends('admin.layout.app')

@section('title', 'Parent Departments')

@section('content')
<div class="parent-department-page">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <i class="fas fa-sitemap"></i> Admin / Settings / Parent Department
    </div>

    <!-- Header Card -->
    <div class="header-card">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas fa-diagram-project"></i>
            </div>
            <div>
                <h1>Parent Department Management</h1>
                <p>Manage organizational structure, department hierarchy and parent department records.</p>
            </div>
        </div>
        <div class="btn-group">
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-cloud-download-alt"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button type="button" class="dropdown-item" onclick="exportTo('copy')"><i class="fas fa-copy text-primary"></i> Copy to Clipboard</button></li>
                    <li><button type="button" class="dropdown-item" onclick="exportTo('csv')"><i class="fas fa-file-csv text-success"></i> Export as CSV</button></li>
                    <li><button type="button" class="dropdown-item" onclick="exportTo('excel')"><i class="fas fa-file-excel text-success"></i> Export as Excel</button></li>
                    <li><button type="button" class="dropdown-item" onclick="exportTo('pdf')"><i class="fas fa-file-pdf text-danger"></i> Export as PDF</button></li>
                    <li><button type="button" class="dropdown-item" onclick="exportTo('print')"><i class="fas fa-print text-info"></i> Print</button></li>
                </ul>
            </div>
            <a href="{{ route('parent-departments.archive') }}" class="btn btn-light">
                <i class="fas fa-box-archive"></i> Archived
                @if(($archivedCount ?? 0) > 0)
                    <span class="archive-count-badge">{{ $archivedCount }}</span>
                @endif
            </a>
            <a href="{{ route('parent-departments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Add New
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
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div>
                <h3>{{ $totalDepartments }}</h3>
                <span>Total Departments</span>
                <p class="stat-sub">Available parent departments</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <h3>{{ $activeDepartments }}</h3>
                <span>Active Departments</span>
                <p class="stat-sub">Currently in use</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-code"></i>
            </div>
            <div>
                <h3>{{ $departments->unique('dpt_code')->count() }}</h3>
                <span>Unique Codes</span>
                <p class="stat-sub">Department identifiers</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div>
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
                    <i class="fas fa-list-ul"></i>
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
                        <th><i class="fas fa-hashtag"></i> #</th>
                        <th><i class="fas fa-code"></i> Department Code</th>
                        <th><i class="fas fa-building"></i> Department Name</th>
                        <th><i class="fas fa-calendar-plus"></i> Created</th>
                        <th><i class="fas fa-clock"></i> Last Updated</th>
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
                                        <a class="dropdown-item" href="{{ route('parent-departments.show', $dpt) }}">
                                            <i class="fas fa-eye"></i> View Department
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('parent-departments.edit', $dpt) }}">
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
                                </ul>
                            </div>
                            <form action="{{ route('parent-departments.destroy', $dpt) }}"
                                  method="POST"
                                  class="d-none delete-form-{{ $dpt->id }}">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-building fa-4x"></i>
                                <h5>No Parent Departments Found</h5>
                                <p>Get started by creating your first parent department</p>
                                <a href="{{ route('parent-departments.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i> Create Department
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
            <span class="legend-title"><i class="fas fa-palette"></i> Department Info:</span>
            <span class="legend-item"><i class="fas fa-code text-primary"></i> Department Code</span>
            <span class="legend-item"><i class="fas fa-building text-success"></i> Department Name</span>
            <span class="legend-item"><i class="fas fa-layer-group text-warning"></i> Parent Level</span>
            <span class="legend-item"><i class="fas fa-clock text-info"></i> Timestamps</span>
        </div>
    </div>
</div>

<style>
    /* ===== BITROXIA MODERN BLUE/PURPLE THEME ===== */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .parent-department-page {
        padding: 30px 35px;
        min-height: 100vh;
        background: linear-gradient(135deg, #F8FAFC 0%, #EEF2FF 50%, #F8FAFC 100%);
        color: #0F172A;
        position: relative;
    }

    .parent-department-page::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 0% 0%, rgba(47, 107, 255, 0.03) 0%, transparent 50%),
                    radial-gradient(circle at 100% 100%, rgba(79, 131, 255, 0.03) 0%, transparent 50%);
        pointer-events: none;
    }

    /* Breadcrumb */
    .breadcrumb {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        padding: 14px 24px;
        border-radius: 16px;
        border: 1px solid rgba(47, 107, 255, 0.15);
        margin-bottom: 28px;
        color: #2F6BFF;
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .breadcrumb i {
        margin-right: 8px;
        color: #60A5FA;
    }

    /* Header Card */
    .header-card {
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(8px);
        border-radius: 28px;
        padding: 28px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        box-shadow: 0 20px 40px -12px rgba(47, 107, 255, 0.12);
        border: 1px solid rgba(47, 107, 255, 0.12);
        margin-bottom: 32px;
        transition: all 0.3s ease;
    }

    .header-card:hover {
        box-shadow: 0 24px 48px -16px rgba(47, 107, 255, 0.18);
        border-color: rgba(47, 107, 255, 0.2);
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 22px;
    }

    .header-icon {
        width: 72px;
        height: 72px;
        background: linear-gradient(145deg, #4F83FF, #2F6BFF);
        color: white;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        box-shadow: 0 12px 24px -8px rgba(47, 107, 255, 0.3);
        transition: all 0.3s ease;
    }

    .header-card:hover .header-icon {
        transform: scale(1.02);
    }

    .header-card h1 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 6px;
        background: linear-gradient(135deg, #0F172A, #1E4FCC);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .header-card p {
        color: #5a6e63;
        font-size: 15px;
        font-weight: 500;
    }

    /* Buttons */
    .btn-group {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
    }

    .btn {
        border: none;
        padding: 12px 24px;
        border-radius: 16px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        font-size: 0.9rem;
    }

    .btn i {
        font-size: 1rem;
    }

    .btn-primary {
        background: linear-gradient(145deg, #4F83FF, #2F6BFF);
        color: white;
        box-shadow: 0 8px 20px -6px rgba(47, 107, 255, 0.35);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px -8px rgba(47, 107, 255, 0.45);
    }

    .archive-count-badge {
        min-width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 7px;
        border-radius: 999px;
        background: #f59e0b;
        color: #fff;
        font-size: .78rem;
        font-weight: 850;
    }

    /* Stats Cards */
    .stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-bottom: 32px;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .parent-department-page .stat-card,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .parent-department-page .stat-card:first-of-type,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .parent-department-page .stat-card.is-featured {
        background: #ffffff !important;
        padding: 24px;
        border-radius: 24px;
        border: 1px solid rgba(47, 107, 255, 0.14) !important;
        box-shadow: 0 10px 30px -10px rgba(47, 107, 255, 0.08) !important;
        color: #0F172A !important;
        display: flex;
        gap: 18px;
        align-items: flex-start;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .parent-department-page .stat-card {
        padding: 24px;
        border-radius: 24px;
        display: flex;
        gap: 18px;
        align-items: flex-start;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .parent-department-page .stat-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4F83FF, #2F6BFF);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .parent-department-page .stat-card:hover::after {
        transform: scaleX(1);
    }

    .parent-department-page .stat-card:hover {
        transform: translateY(-4px);
    }

    .parent-department-page .stat-icon,
    .parent-department-page .stat-card:first-of-type .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF) !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        flex-shrink: 0;
    }

    .parent-department-page .stat-icon i,
    .parent-department-page .stat-icon .fa,
    .parent-department-page .stat-icon [class*="fa-"],
    .parent-department-page .stat-card:first-of-type .stat-icon i,
    .parent-department-page .stat-card:first-of-type .stat-icon .fa,
    .parent-department-page .stat-card:first-of-type .stat-icon [class*="fa-"] {
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        background: transparent !important;
        background-color: transparent !important;
        display: inline-block !important;
    }

    .parent-department-page .stat-card:nth-child(2) .stat-icon {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
    }

    .parent-department-page .stat-card:nth-child(2) .stat-icon i,
    .parent-department-page .stat-card:nth-child(2) .stat-icon .fa,
    .parent-department-page .stat-card:nth-child(2) .stat-icon [class*="fa-"] {
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
        background: transparent !important;
        background-color: transparent !important;
        display: inline-block !important;
    }

    .parent-department-page .stat-card:nth-child(3) .stat-icon {
        background: linear-gradient(145deg, #e0e7ff, #c7d2fe) !important;
        color: #4f46e5 !important;
        -webkit-text-fill-color: #4f46e5 !important;
    }

    .parent-department-page .stat-card:nth-child(3) .stat-icon i,
    .parent-department-page .stat-card:nth-child(3) .stat-icon .fa,
    .parent-department-page .stat-card:nth-child(3) .stat-icon [class*="fa-"] {
        color: #4f46e5 !important;
        -webkit-text-fill-color: #4f46e5 !important;
        background: transparent !important;
        background-color: transparent !important;
        display: inline-block !important;
    }

    .parent-department-page .stat-card:nth-child(4) .stat-icon {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
    }

    .parent-department-page .stat-card:nth-child(4) .stat-icon i,
    .parent-department-page .stat-card:nth-child(4) .stat-icon .fa,
    .parent-department-page .stat-card:nth-child(4) .stat-icon [class*="fa-"] {
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
        background: transparent !important;
        background-color: transparent !important;
        display: inline-block !important;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .parent-department-page .stat-card h3,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .parent-department-page .stat-card:first-of-type h3,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .parent-department-page .stat-card:first-of-type .stat-value {
        font-size: 32px;
        font-weight: 800;
        color: #0F172A !important;
        -webkit-text-fill-color: #0F172A !important;
        margin-bottom: 6px;
        line-height: 1;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .parent-department-page .stat-card span,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .parent-department-page .stat-card:first-of-type span,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .parent-department-page .stat-card:first-of-type .stat-title {
        color: #4b5563 !important;
        -webkit-text-fill-color: #4b5563 !important;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .parent-department-page .stat-sub,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .parent-department-page .stat-card:first-of-type .stat-sub,
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .parent-department-page .stat-card:first-of-type .stat-trend {
        font-size: 11px;
        color: #6b7280 !important;
        -webkit-text-fill-color: #6b7280 !important;
        margin-top: 6px;
        font-weight: 500;
    }

    /* Alerts */
    .alert {
        border-radius: 18px;
        border: none;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        animation: slideDown 0.4s ease;
    }

    .alert-success {
        background: linear-gradient(135deg, #EEF2FF, #E0E7FF);
        color: #065f46;
        border-left: 4px solid #10b981;
    }

    .alert-danger {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }

    .alert-warning {
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
        color: #92400e;
        border-left: 4px solid #f59e0b;
    }

    .alert-info {
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        color: #1e40af;
        border-left: 4px solid #3b82f6;
    }

    .alert i {
        font-size: 1.25rem;
        margin-top: 2px;
    }

    .alert ul {
        margin-bottom: 0;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Table Card */
    .table-card {
        background: white;
        border-radius: 28px;
        border: 1px solid rgba(47, 107, 255, 0.1);
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }

    .table-card:hover {
        box-shadow: 0 12px 40px rgba(47, 107, 255, 0.08);
    }

    .table-header {
        padding: 22px 28px;
        background: linear-gradient(135deg, #ffffff, #fafefb);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        border-bottom: 1px solid rgba(47, 107, 255, 0.1);
    }

    .table-title {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .table-title-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF);
        color: #2F6BFF;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }

    .table-title h3 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #0F172A;
        margin: 0;
    }

    .muted {
        font-size: 0.75rem;
        color: #64748B;
        font-weight: 500;
    }

    /* Search Box */
    .search-box {
        position: relative;
    }

    .search-box input {
        width: 280px;
        padding: 12px 18px 12px 44px;
        border-radius: 40px;
        border: 1px solid rgba(47, 107, 255, 0.2);
        outline: none;
        font-weight: 500;
        transition: all 0.2s ease;
        background: #ffffff;
    }

    .search-box input:focus {
        border-color: #60A5FA;
        box-shadow: 0 0 0 3px rgba(79, 131, 255, 0.1);
    }

    .search-box i {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #60A5FA;
    }

    /* Bulk Actions Bar */
    .bulk-actions-bar {
        padding: 14px 28px;
        background: #EEF2FF;
        border-bottom: 1px solid rgba(47, 107, 255, 0.12);
    }

    .bulk-actions-content {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .selected-badge {
        padding: 6px 18px;
        background: white;
        color: #2F6BFF;
        border-radius: 40px;
        font-size: 0.85rem;
        font-weight: 600;
        border: 1px solid rgba(47, 107, 255, 0.2);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    }

    .btn-clear, .btn-bulk-delete {
        padding: 8px 20px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-clear {
        background: #f3f4f6;
        color: #6b7280;
        border: 1px solid #e5e7eb;
    }

    .btn-clear:hover {
        background: #e5e7eb;
        color: #374151;
    }

    .btn-bulk-delete {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .btn-bulk-delete:hover:not(:disabled) {
        background: #fef3c7;
        color: #92400e;
    }

    .btn-bulk-delete:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Table Wrapper */
    .table-wrapper {
        overflow-x: auto;
        padding: 8px 20px 20px 20px;
    }

    .department-list-table {
        width: 100%;
        min-width: 1100px;
        border-collapse: separate;
        border-spacing: 0 12px;
    }

    .department-list-table thead th {
        text-align: left;
        padding: 16px 20px;
        color: #5a6e63;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        background: transparent;
        border-bottom: 2px solid rgba(47, 107, 255, 0.1);
    }

    .department-list-table thead th i {
        margin-right: 8px;
        font-size: 0.75rem;
        color: #60A5FA;
    }

    .department-list-table tbody td {
        background: #ffffff;
        padding: 18px 20px;
        border-top: 1px solid rgba(47, 107, 255, 0.08);
        border-bottom: 1px solid rgba(47, 107, 255, 0.08);
        font-weight: 500;
        vertical-align: middle;
        transition: all 0.2s ease;
    }

    .department-list-table tbody td:first-child {
        border-left: 1px solid rgba(47, 107, 255, 0.08);
        border-radius: 20px 0 0 20px;
    }

    .department-list-table tbody td:last-child {
        border-right: 1px solid rgba(47, 107, 255, 0.08);
        border-radius: 0 20px 20px 0;
    }

    .department-list-table tbody tr:hover td {
        background: #fafefb;
        border-color: rgba(47, 107, 255, 0.15);
    }

    /* Department Info */
    .department-info {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .mini-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF);
        color: #2F6BFF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        transition: all 0.2s ease;
    }

    .department-row:hover .mini-icon {
        transform: scale(1.05);
    }

    .department-code, .department-name, .department-number {
        font-weight: 700;
        color: #0F172A;
    }

    .department-code {
        color: #2F6BFF;
    }

    .muted {
        display: block;
        margin-top: 4px;
        color: #64748B;
        font-size: 0.7rem;
        font-weight: 500;
    }

    .muted i {
        margin-right: 4px;
        font-size: 0.65rem;
    }

    .create-date, .update-date {
        font-weight: 600;
        color: #0F172A;
    }

    /* Action Button */
    .action-btn {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        border: none;
        background: #f3f4f6;
        color: #6b7280;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .action-btn:hover {
        background: #E0E7FF;
        color: #2F6BFF;
        transform: scale(1.05);
    }

    /* Dropdown */
    .dropdown-menu {
        background: white;
        border: 1px solid rgba(47, 107, 255, 0.15);
        border-radius: 16px;
        padding: 8px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        z-index: 9999;
    }

    .dropdown-item {
        padding: 10px 18px;
        color: #374151;
        font-size: 0.85rem;
        border-radius: 12px;
        transition: all 0.2s ease;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        border: none;
        background: transparent;
        width: 100%;
    }

    .dropdown-item i {
        width: 20px;
        font-size: 1rem;
    }

    .dropdown-item:hover {
        background: #EEF2FF;
        color: #2F6BFF;
    }

    .dropdown-item.text-danger:hover {
        background: #fef2f2;
        color: #dc2626;
    }

    /* Footer */
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .parent-department-page .footer {
        padding: 20px 28px;
        background: #fafefb !important;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        color: #6b7280 !important;
        font-weight: 500;
        border-top: 1px solid rgba(47, 107, 255, 0.08) !important;
    }

    .footer {
        padding: 20px 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        font-weight: 500;
    }

    .show-entries {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .show-entries select {
        background: white;
        border: 1px solid rgba(47, 107, 255, 0.2);
        border-radius: 10px;
        padding: 8px 12px;
        width: 75px;
        font-weight: 500;
        cursor: pointer;
    }

    .pagination-info {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pagination-info i {
        color: #60A5FA;
    }

    .pagination {
        display: flex;
        gap: 6px;
        align-items: center;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .page-btn {
        min-width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: white;
        border: 1px solid rgba(47, 107, 255, 0.2);
        color: #5a6e63;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .page-btn:hover:not(.disabled) {
        background: #E0E7FF;
        border-color: #60A5FA;
        color: #2F6BFF;
    }

    .page-btn.active {
        background: linear-gradient(145deg, #4F83FF, #2F6BFF);
        border-color: transparent;
        color: white;
        box-shadow: 0 4px 12px rgba(47, 107, 255, 0.3);
    }

    .page-btn.disabled {
        background: #f3f4f6;
        color: #9ca3af;
        cursor: not-allowed;
    }

    .page-dots {
        color: #9ca3af;
        padding: 0 6px;
    }

    /* Form Check */
    .form-check {
        display: flex;
        align-items: center;
    }

    .form-check-input {
        width: 18px;
        height: 18px;
        background: white;
        border: 2px solid #C7D2FE;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .form-check-input:checked {
        background-color: #2F6BFF;
        border-color: #2F6BFF;
        box-shadow: 0 0 0 2px rgba(47, 107, 255, 0.2);
    }

    .form-check-label {
        margin-left: 8px;
        font-size: 0.85rem;
        cursor: pointer;
    }

    /* Legend Card */
    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .parent-department-page .legend-card {
        margin-top: 28px;
        background: white !important;
        border: 1px solid rgba(47, 107, 255, 0.1) !important;
        border-radius: 24px;
        padding: 18px 28px;
        transition: all 0.3s ease;
    }

    .legend-card {
        margin-top: 28px;
        border-radius: 24px;
        padding: 18px 28px;
        transition: all 0.3s ease;
    }

    .legend-card:hover {
        box-shadow: 0 8px 25px rgba(47, 107, 255, 0.08);
    }

    .legend-content {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .parent-department-page .legend-title {
        font-weight: 700;
        color: #0F172A !important;
        font-size: 0.85rem;
    }

    .legend-title {
        font-weight: 700;
        font-size: 0.85rem;
    }

    .legend-title i {
        margin-right: 8px;
        color: #60A5FA;
    }

    html:not([data-pms-theme="dark"]):not([data-bs-theme="dark"]):not([data-theme="dark"]):not(.dark) .parent-department-page .legend-item {
        font-size: 0.8rem;
        color: #5a6e63 !important;
        font-weight: 500;
    }

    .legend-item {
        font-size: 0.8rem;
        font-weight: 500;
    }

    .legend-item i {
        margin-right: 6px;
        font-size: 0.85rem;
    }

    /* Empty State */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }

    .empty-state i {
        color: #C7D2FE;
        margin-bottom: 20px;
    }

    .empty-state h5 {
        color: #2F6BFF;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #64748B;
        margin-bottom: 20px;
    }

    /* Archive button loading state */
    .delete-btn.loading {
        position: relative;
        color: transparent !important;
        pointer-events: none;
    }

    .delete-btn.loading::after {
        content: '';
        position: absolute;
        width: 18px;
        height: 18px;
        top: 50%;
        left: 50%;
        margin: -9px 0 0 -9px;
        border: 2.5px solid #dc2626;
        border-top-color: transparent;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Toast notifications */
    .toast-alert .alert {
        border-radius: 16px;
        box-shadow: 0 16px 48px rgba(0, 0, 0, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(12px);
        min-width: 340px;
        animation: slideInRight 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        background: rgba(255, 255, 255, 0.95) !important;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(40px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateX(0) scale(1);
        }
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 992px) {
        .parent-department-page {
            padding: 20px 25px;
        }

        .header-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .btn-group {
            width: 100%;
            justify-content: flex-start;
        }
    }

    @media (max-width: 768px) {
        .stats {
            grid-template-columns: 1fr;
        }

        .table-header {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box input {
            width: 100%;
        }

        .footer {
            flex-direction: column;
            text-align: center;
        }

        .pagination {
            justify-content: center;
        }

        .bulk-actions-content {
            gap: 12px;
        }

        .dropdown-menu {
            position: fixed !important;
            right: 16px !important;
            left: 16px !important;
            width: calc(100% - 32px) !important;
            max-width: 400px;
            margin: 0 auto !important;
        }
    }

    @media (max-width: 576px) {
        .parent-department-page {
            padding: 16px;
        }

        .header-card {
            padding: 20px;
        }

        .header-icon {
            width: 56px;
            height: 56px;
            font-size: 24px;
        }

        .header-card h1 {
            font-size: 24px;
        }

        .department-list-table {
            min-width: 800px;
        }

        .department-list-table tbody td {
            padding: 12px 14px;
            font-size: 0.85rem;
        }

        .mini-icon {
            width: 36px;
            height: 36px;
            font-size: 0.9rem;
        }

        .action-btn {
            width: 32px;
            height: 32px;
        }
    }

    /* Print Styles */
    @media print {
        .btn-group, .search-box, .bulk-actions-bar, .legend-card, .action-btn, .dropdown {
            display: none;
        }

        .parent-department-page {
            background: white;
            padding: 0;
        }

        .table-card {
            box-shadow: none;
            border: 1px solid #ddd;
        }
    }
</style>

<style>
    /* Larger, accessibility-focused typography */
    .parent-department-page {
        font-size: 16px;
        line-height: 1.55;
    }

    .parent-department-page .breadcrumb {
        font-size: 1rem;
    }

    .parent-department-page .header-card h1 {
        font-size: 36px;
        line-height: 1.2;
    }

    .parent-department-page .header-card p {
        font-size: 17px;
        line-height: 1.55;
    }

    .parent-department-page .btn {
        padding: 13px 24px;
        font-size: 1rem;
    }

    .parent-department-page .stat-card h3 {
        font-size: 36px;
    }

    .parent-department-page .stat-card span {
        font-size: 14px;
        line-height: 1.4;
    }

    .parent-department-page .stat-sub {
        font-size: 13px;
        line-height: 1.45;
    }

    .parent-department-page .alert {
        font-size: 1rem;
    }

    .parent-department-page .table-title h3 {
        font-size: 1.45rem;
    }

    .parent-department-page .table-title .muted {
        font-size: .9rem;
    }

    .parent-department-page .search-box input {
        width: 320px;
        min-height: 48px;
        font-size: 1rem;
    }

    .parent-department-page .selected-badge,
    .parent-department-page .btn-clear,
    .parent-department-page .btn-bulk-delete,
    .parent-department-page .form-check-label {
        font-size: .95rem;
    }

    .parent-department-page .department-list-table {
        min-width: 1200px;
    }

    .parent-department-page .department-list-table thead th {
        padding: 17px 20px;
        font-size: .9rem;
        line-height: 1.4;
    }

    .parent-department-page .department-list-table thead th i {
        font-size: .9rem;
    }

    .parent-department-page .department-list-table tbody td {
        padding: 20px;
        font-size: 1rem;
        line-height: 1.45;
    }

    .parent-department-page .department-code,
    .parent-department-page .department-name,
    .parent-department-page .department-number,
    .parent-department-page .create-date,
    .parent-department-page .update-date {
        font-size: 1.05rem;
    }

    .parent-department-page .muted,
    .parent-department-page .department-list-table small,
    .parent-department-page .text-muted {
        font-size: .88rem !important;
        line-height: 1.45;
    }

    .parent-department-page .muted i {
        font-size: .8rem;
    }

    .parent-department-page .dropdown-item {
        padding: 12px 18px;
        font-size: 1rem;
    }

    .parent-department-page .footer,
    .parent-department-page .show-entries,
    .parent-department-page .pagination-info,
    .parent-department-page .show-entries select {
        font-size: 1rem;
    }

    .parent-department-page .page-btn {
        min-width: 42px;
        height: 42px;
        font-size: 1rem;
    }

    .parent-department-page .legend-title {
        font-size: 1rem;
    }

    .parent-department-page .legend-item {
        font-size: .9rem;
    }

    .parent-department-page .empty-state h5 {
        font-size: 1.35rem;
    }

    .parent-department-page .empty-state p {
        font-size: 1rem;
    }

    @media (max-width: 768px) {
        .parent-department-page {
            font-size: 15px;
        }

        .parent-department-page .header-card h1 {
            font-size: 29px;
        }

        .parent-department-page .header-card p {
            font-size: 16px;
        }

        .parent-department-page .search-box input {
            width: 100%;
        }
    }
</style>

<style>
    /* Keep dropdown menus fully visible above the page cards */
    .parent-department-page .header-card {
        position: relative;
        z-index: 50;
        overflow: visible !important;
    }

    .parent-department-page .header-card .btn-group,
    .parent-department-page .header-card .dropdown {
        position: relative;
        z-index: 60;
        overflow: visible;
    }

    .parent-department-page .header-card .dropdown-menu {
        z-index: 9999 !important;
        min-width: 230px;
        margin-top: 10px !important;
        padding: 10px;
        overflow: visible;
        border: 1px solid rgba(47, 107, 255, .18);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 24px 48px -14px rgba(15, 23, 42, .28);
    }

    .parent-department-page .header-card .dropdown-menu.show {
        display: block;
        visibility: visible;
        opacity: 1;
    }

    .parent-department-page .header-card .dropdown-item {
        width: 100%;
        min-height: 44px;
        white-space: nowrap;
    }

    .parent-department-page .stats,
    .parent-department-page .table-card,
    .parent-department-page .legend-card {
        position: relative;
        z-index: 1;
    }

    html[data-pms-theme="dark"] .parent-department-page .header-card .dropdown-menu {
        border-color: rgba(79, 131, 255, .2);
        background: #141B3D;
        box-shadow: 0 24px 48px -14px rgba(0, 0, 0, .55);
    }

    @media (max-width: 576px) {
        .parent-department-page .header-card .dropdown-menu {
            right: auto !important;
            left: 0 !important;
            max-width: calc(100vw - 48px);
        }
    }

    /* =============================================
       DARK MODE STYLES FOR PARENT DEPARTMENT INDEX
       ============================================= */
    html[data-pms-theme="dark"] .parent-department-page,
    html[data-theme="dark"] .parent-department-page,
    html[data-bs-theme="dark"] .parent-department-page {
        background: #091510 !important;
        color: #e2e8f0 !important;
    }

    html[data-pms-theme="dark"] .parent-department-page::before,
    html[data-theme="dark"] .parent-department-page::before,
    html[data-bs-theme="dark"] .parent-department-page::before {
        background: radial-gradient(circle at 0% 0%, rgba(47, 107, 255, 0.06) 0%, transparent 50%),
                    radial-gradient(circle at 100% 100%, rgba(79, 131, 255, 0.04) 0%, transparent 50%);
    }

    html[data-pms-theme="dark"] .parent-department-page .breadcrumb,
    html[data-theme="dark"] .parent-department-page .breadcrumb,
    html[data-bs-theme="dark"] .parent-department-page .breadcrumb {
        background: rgba(15, 21, 48, 0.85) !important;
        border-color: rgba(79, 131, 255, 0.2) !important;
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .header-card,
    html[data-theme="dark"] .parent-department-page .header-card,
    html[data-bs-theme="dark"] .parent-department-page .header-card {
        background: #0F1530 !important;
        border-color: rgba(79, 131, 255, 0.2) !important;
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.3) !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .header-card h1,
    html[data-theme="dark"] .parent-department-page .header-card h1,
    html[data-bs-theme="dark"] .parent-department-page .header-card h1 {
        background: linear-gradient(135deg, #FFFFFF, #E0E7FF) !important;
        background-clip: text !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .header-card p,
    html[data-theme="dark"] .parent-department-page .header-card p,
    html[data-bs-theme="dark"] .parent-department-page .header-card p {
        color: #94a3b8 !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .btn-light,
    html[data-theme="dark"] .parent-department-page .btn-light,
    html[data-bs-theme="dark"] .parent-department-page .btn-light {
        background: #141B3D !important;
        color: #e2e8f0 !important;
        border-color: rgba(79, 131, 255, 0.2) !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .btn-light:hover,
    html[data-theme="dark"] .parent-department-page .btn-light:hover,
    html[data-bs-theme="dark"] .parent-department-page .btn-light:hover {
        background: rgba(79, 131, 255, 0.25) !important;
        color: #ffffff !important;
        border-color: #60A5FA !important;
    }

    /* Stat Cards Dark Mode */
    html[data-pms-theme="dark"] .parent-department-page .stat-card,
    html[data-theme="dark"] .parent-department-page .stat-card,
    html[data-bs-theme="dark"] .parent-department-page .stat-card,
    html[data-pms-theme="dark"] .parent-department-page .stat-card:first-of-type,
    html[data-theme="dark"] .parent-department-page .stat-card:first-of-type,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:first-of-type {
        background: #0F1530 !important;
        border-color: rgba(79, 131, 255, 0.2) !important;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.3) !important;
        color: #f8fafc !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .stat-card h3,
    html[data-theme="dark"] .parent-department-page .stat-card h3,
    html[data-bs-theme="dark"] .parent-department-page .stat-card h3,
    html[data-pms-theme="dark"] .parent-department-page .stat-card:first-of-type h3,
    html[data-theme="dark"] .parent-department-page .stat-card:first-of-type h3,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:first-of-type h3 {
        color: #f8fafc !important;
        -webkit-text-fill-color: #f8fafc !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .stat-card span,
    html[data-theme="dark"] .parent-department-page .stat-card span,
    html[data-bs-theme="dark"] .parent-department-page .stat-card span,
    html[data-pms-theme="dark"] .parent-department-page .stat-card:first-of-type span,
    html[data-theme="dark"] .parent-department-page .stat-card:first-of-type span,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:first-of-type span {
        color: #94a3b8 !important;
        -webkit-text-fill-color: #94a3b8 !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .stat-sub,
    html[data-theme="dark"] .parent-department-page .stat-sub,
    html[data-bs-theme="dark"] .parent-department-page .stat-sub,
    html[data-pms-theme="dark"] .parent-department-page .stat-card:first-of-type .stat-sub,
    html[data-theme="dark"] .parent-department-page .stat-card:first-of-type .stat-sub,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:first-of-type .stat-sub {
        color: #64748b !important;
        -webkit-text-fill-color: #64748b !important;
    }

    /* Table Card & Header Dark Mode */
    html[data-pms-theme="dark"] .parent-department-page .table-card,
    html[data-theme="dark"] .parent-department-page .table-card,
    html[data-bs-theme="dark"] .parent-department-page .table-card {
        background: #0F1530 !important;
        border-color: rgba(79, 131, 255, 0.2) !important;
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.3) !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .table-header,
    html[data-theme="dark"] .parent-department-page .table-header,
    html[data-bs-theme="dark"] .parent-department-page .table-header {
        background: #141B3D !important;
        border-bottom-color: rgba(79, 131, 255, 0.15) !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .table-title h3,
    html[data-theme="dark"] .parent-department-page .table-title h3,
    html[data-bs-theme="dark"] .parent-department-page .table-title h3 {
        color: #f8fafc !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .table-title .muted,
    html[data-theme="dark"] .parent-department-page .table-title .muted,
    html[data-bs-theme="dark"] .parent-department-page .table-title .muted {
        color: #94a3b8 !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .search-box input,
    html[data-theme="dark"] .parent-department-page .search-box input,
    html[data-bs-theme="dark"] .parent-department-page .search-box input {
        background: #141B3D !important;
        border-color: rgba(79, 131, 255, 0.2) !important;
        color: #f8fafc !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .search-box input::placeholder,
    html[data-theme="dark"] .parent-department-page .search-box input::placeholder,
    html[data-bs-theme="dark"] .parent-department-page .search-box input::placeholder {
        color: #64748b !important;
    }

    /* Table Rows & Cells Dark Mode */
    html[data-pms-theme="dark"] .parent-department-page .department-list-table thead th,
    html[data-theme="dark"] .parent-department-page .department-list-table thead th,
    html[data-bs-theme="dark"] .parent-department-page .department-list-table thead th {
        color: #94a3b8 !important;
        border-bottom-color: rgba(79, 131, 255, 0.15) !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .department-list-table tbody td,
    html[data-theme="dark"] .parent-department-page .department-list-table tbody td,
    html[data-bs-theme="dark"] .parent-department-page .department-list-table tbody td {
        background: #141B3D !important;
        border-top-color: rgba(79, 131, 255, 0.1) !important;
        border-bottom-color: rgba(79, 131, 255, 0.1) !important;
        color: #e2e8f0 !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .department-list-table tbody td:first-child,
    html[data-theme="dark"] .parent-department-page .department-list-table tbody td:first-child,
    html[data-bs-theme="dark"] .parent-department-page .department-list-table tbody td:first-child {
        border-left-color: rgba(79, 131, 255, 0.1) !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .department-list-table tbody td:last-child,
    html[data-theme="dark"] .parent-department-page .department-list-table tbody td:last-child,
    html[data-bs-theme="dark"] .parent-department-page .department-list-table tbody td:last-child {
        border-right-color: rgba(79, 131, 255, 0.1) !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .department-list-table tbody tr:hover td,
    html[data-theme="dark"] .parent-department-page .department-list-table tbody tr:hover td,
    html[data-bs-theme="dark"] .parent-department-page .department-list-table tbody tr:hover td {
        background: rgba(79, 131, 255, 0.08) !important;
        border-color: rgba(79, 131, 255, 0.25) !important;
    }

    /* Icon Containers Dark Theme Fix */
    html[data-pms-theme="dark"] .parent-department-page .mini-icon,
    html[data-theme="dark"] .parent-department-page .mini-icon,
    html[data-bs-theme="dark"] .parent-department-page .mini-icon {
        background: rgba(47, 107, 255, 0.2) !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .mini-icon i,
    html[data-theme="dark"] .parent-department-page .mini-icon i,
    html[data-bs-theme="dark"] .parent-department-page .mini-icon i,
    html[data-pms-theme="dark"] .parent-department-page .mini-icon svg,
    html[data-theme="dark"] .parent-department-page .mini-icon svg,
    html[data-bs-theme="dark"] .parent-department-page .mini-icon svg {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .header-icon,
    html[data-theme="dark"] .parent-department-page .header-icon,
    html[data-bs-theme="dark"] .parent-department-page .header-icon,
    html[data-pms-theme="dark"] .parent-department-page .table-title-icon,
    html[data-theme="dark"] .parent-department-page .table-title-icon,
    html[data-bs-theme="dark"] .parent-department-page .table-title-icon {
        background: rgba(47, 107, 255, 0.2) !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .header-icon i,
    html[data-theme="dark"] .parent-department-page .header-icon i,
    html[data-bs-theme="dark"] .parent-department-page .header-icon i,
    html[data-pms-theme="dark"] .parent-department-page .table-title-icon i,
    html[data-theme="dark"] .parent-department-page .table-title-icon i,
    html[data-bs-theme="dark"] .parent-department-page .table-title-icon i {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .stat-card:nth-child(1) .stat-icon,
    html[data-theme="dark"] .parent-department-page .stat-card:nth-child(1) .stat-icon,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:nth-child(1) .stat-icon {
        background: rgba(47, 107, 255, 0.2) !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #60A5FA !important;
    }
    html[data-pms-theme="dark"] .parent-department-page .stat-card:nth-child(1) .stat-icon i,
    html[data-theme="dark"] .parent-department-page .stat-card:nth-child(1) .stat-icon i,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:nth-child(1) .stat-icon i {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .stat-card:nth-child(2) .stat-icon,
    html[data-theme="dark"] .parent-department-page .stat-card:nth-child(2) .stat-icon,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:nth-child(2) .stat-icon {
        background: rgba(2, 132, 199, 0.2) !important;
        border: 1px solid rgba(56, 189, 248, 0.35) !important;
        color: #38bdf8 !important;
    }
    html[data-pms-theme="dark"] .parent-department-page .stat-card:nth-child(2) .stat-icon i,
    html[data-theme="dark"] .parent-department-page .stat-card:nth-child(2) .stat-icon i,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:nth-child(2) .stat-icon i {
        color: #38bdf8 !important;
        -webkit-text-fill-color: #38bdf8 !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .stat-card:nth-child(3) .stat-icon,
    html[data-theme="dark"] .parent-department-page .stat-card:nth-child(3) .stat-icon,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:nth-child(3) .stat-icon {
        background: rgba(79, 70, 229, 0.2) !important;
        border: 1px solid rgba(129, 140, 248, 0.35) !important;
        color: #818cf8 !important;
    }
    html[data-pms-theme="dark"] .parent-department-page .stat-card:nth-child(3) .stat-icon i,
    html[data-theme="dark"] .parent-department-page .stat-card:nth-child(3) .stat-icon i,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:nth-child(3) .stat-icon i {
        color: #818cf8 !important;
        -webkit-text-fill-color: #818cf8 !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .stat-card:nth-child(4) .stat-icon,
    html[data-theme="dark"] .parent-department-page .stat-card:nth-child(4) .stat-icon,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:nth-child(4) .stat-icon {
        background: rgba(217, 119, 6, 0.2) !important;
        border: 1px solid rgba(251, 191, 36, 0.35) !important;
        color: #fbbf24 !important;
    }
    html[data-pms-theme="dark"] .parent-department-page .stat-card:nth-child(4) .stat-icon i,
    html[data-theme="dark"] .parent-department-page .stat-card:nth-child(4) .stat-icon i,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:nth-child(4) .stat-icon i {
        color: #fbbf24 !important;
        -webkit-text-fill-color: #fbbf24 !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .department-code,
    html[data-theme="dark"] .parent-department-page .department-code,
    html[data-bs-theme="dark"] .parent-department-page .department-code {
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .department-name,
    html[data-theme="dark"] .parent-department-page .department-name,
    html[data-bs-theme="dark"] .parent-department-page .department-name,
    html[data-pms-theme="dark"] .parent-department-page .department-number,
    html[data-theme="dark"] .parent-department-page .department-number,
    html[data-bs-theme="dark"] .parent-department-page .department-number {
        color: #f8fafc !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .action-btn,
    html[data-theme="dark"] .parent-department-page .action-btn,
    html[data-bs-theme="dark"] .parent-department-page .action-btn {
        background: #141B3D !important;
        color: #cbd5e1 !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .action-btn:hover,
    html[data-theme="dark"] .parent-department-page .action-btn:hover,
    html[data-bs-theme="dark"] .parent-department-page .action-btn:hover {
        background: rgba(79, 131, 255, 0.25) !important;
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .dropdown-menu,
    html[data-theme="dark"] .parent-department-page .dropdown-menu,
    html[data-bs-theme="dark"] .parent-department-page .dropdown-menu {
        background: #141B3D !important;
        border-color: rgba(79, 131, 255, 0.2) !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4) !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .dropdown-item,
    html[data-theme="dark"] .parent-department-page .dropdown-item,
    html[data-bs-theme="dark"] .parent-department-page .dropdown-item {
        color: #e2e8f0 !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .dropdown-item:hover,
    html[data-theme="dark"] .parent-department-page .dropdown-item:hover,
    html[data-bs-theme="dark"] .parent-department-page .dropdown-item:hover {
        background: rgba(79, 131, 255, 0.25) !important;
        color: #60A5FA !important;
    }

    /* Pagination Footer Dark Mode */
    html[data-pms-theme="dark"] .parent-department-page .footer,
    html[data-theme="dark"] .parent-department-page .footer,
    html[data-bs-theme="dark"] .parent-department-page .footer {
        background: #141B3D !important;
        border-top-color: rgba(79, 131, 255, 0.15) !important;
        color: #94a3b8 !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .footer .show-entries select,
    html[data-theme="dark"] .parent-department-page .footer .show-entries select,
    html[data-bs-theme="dark"] .parent-department-page .footer .show-entries select {
        background: #141B3D !important;
        border-color: rgba(79, 131, 255, 0.2) !important;
        color: #f8fafc !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .footer .page-btn,
    html[data-theme="dark"] .parent-department-page .footer .page-btn,
    html[data-bs-theme="dark"] .parent-department-page .footer .page-btn {
        background: #141B3D !important;
        border-color: rgba(79, 131, 255, 0.2) !important;
        color: #cbd5e1 !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .footer .page-btn:hover:not(.disabled),
    html[data-theme="dark"] .parent-department-page .footer .page-btn:hover:not(.disabled),
    html[data-bs-theme="dark"] .parent-department-page .footer .page-btn:hover:not(.disabled) {
        background: rgba(79, 131, 255, 0.25) !important;
        color: #60A5FA !important;
        border-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .footer .page-btn.active,
    html[data-theme="dark"] .parent-department-page .footer .page-btn.active,
    html[data-bs-theme="dark"] .parent-department-page .footer .page-btn.active {
        background: linear-gradient(145deg, #4F83FF, #2F6BFF) !important;
        color: white !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .footer .page-btn.disabled,
    html[data-theme="dark"] .parent-department-page .footer .page-btn.disabled,
    html[data-bs-theme="dark"] .parent-department-page .footer .page-btn.disabled {
        background: rgba(255, 255, 255, 0.05) !important;
        color: #64748b !important;
    }

    /* Legend Card Dark Mode */
    html[data-pms-theme="dark"] .parent-department-page .legend-card,
    html[data-theme="dark"] .parent-department-page .legend-card,
    html[data-bs-theme="dark"] .parent-department-page .legend-card {
        background: #0F1530 !important;
        border-color: rgba(79, 131, 255, 0.2) !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .legend-title,
    html[data-theme="dark"] .parent-department-page .legend-title,
    html[data-bs-theme="dark"] .parent-department-page .legend-title {
        color: #f8fafc !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .legend-item,
    html[data-theme="dark"] .parent-department-page .legend-item,
    html[data-bs-theme="dark"] .parent-department-page .legend-item {
        color: #94a3b8 !important;
    }

    /* DataTables Export Buttons Dark Mode */
    html[data-pms-theme="dark"] .parent-department-page .dataTables_wrapper .dt-buttons,
    html[data-theme="dark"] .parent-department-page .dataTables_wrapper .dt-buttons,
    html[data-bs-theme="dark"] .parent-department-page .dataTables_wrapper .dt-buttons {
        background: #141B3D !important;
        border-color: rgba(79, 131, 255, 0.2) !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .dataTables_wrapper .dt-buttons .dt-button,
    html[data-theme="dark"] .parent-department-page .dataTables_wrapper .dt-buttons .dt-button,
    html[data-bs-theme="dark"] .parent-department-page .dataTables_wrapper .dt-buttons .dt-button {
        background: #141B3D !important;
        border-color: rgba(79, 131, 255, 0.2) !important;
        color: #60A5FA !important;
    }
</style>


<style>
    /* DataTables export buttons styled to match the designation UI. */
    .parent-department-page .dataTables_wrapper .dt-buttons {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        width: fit-content;
        margin: 14px 20px 6px;
        padding: 8px;
        border: 1px solid rgba(47, 107, 255, .14);
        border-radius: 16px;
        background: #f7fcf9;
    }

    .parent-department-page .dataTables_wrapper .dt-buttons .dt-button {
        min-height: 42px;
        margin: 0 !important;
        padding: 9px 16px !important;
        border: 1px solid rgba(47, 107, 255, .18) !important;
        border-radius: 12px !important;
        background: #fff !important;
        color: #2F6BFF !important;
        box-shadow: 0 4px 12px -10px rgba(47, 107, 255, .7);
        font-size: .95rem !important;
        font-weight: 750 !important;
        line-height: 1.2 !important;
        transition: border-color .2s ease, background .2s ease, color .2s ease, transform .2s ease, box-shadow .2s ease;
    }

    .parent-department-page .dataTables_wrapper .dt-buttons .dt-button span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .parent-department-page .dataTables_wrapper .dt-buttons .dt-button span::before {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        font-family: "Font Awesome 5 Free";
        font-size: 1rem;
        font-weight: 900;
    }

    .parent-department-page .dataTables_wrapper .buttons-copy span::before {
        content: "\f0c5";
    }

    .parent-department-page .dataTables_wrapper .buttons-csv span::before {
        content: "\f6dd";
    }

    .parent-department-page .dataTables_wrapper .buttons-excel span::before {
        content: "\f1c3";
    }

    .parent-department-page .dataTables_wrapper .buttons-pdf span::before {
        content: "\f1c1";
    }

    .parent-department-page .dataTables_wrapper .buttons-print span::before {
        content: "\f02f";
    }

    .parent-department-page .dataTables_wrapper .dt-buttons .dt-button:hover,
    .parent-department-page .dataTables_wrapper .dt-buttons .dt-button:focus {
        border-color: #10b981 !important;
        background: linear-gradient(145deg, #4F83FF, #2F6BFF) !important;
        color: #fff !important;
        box-shadow: 0 9px 20px -12px rgba(47, 107, 255, .8);
        transform: translateY(-1px);
    }

    .parent-department-page .dataTables_wrapper .buttons-excel {
        background: #EEF2FF !important;
    }

    .parent-department-page .dataTables_wrapper .buttons-csv {
        background: #f0fdf4 !important;
    }

    .parent-department-page .dataTables_wrapper .buttons-pdf {
        color: #b91c1c !important;
        background: #fff7f7 !important;
        border-color: rgba(239, 68, 68, .18) !important;
    }

    .parent-department-page .dataTables_wrapper .buttons-print {
        color: #315f75 !important;
        background: #f4fbff !important;
        border-color: rgba(49, 95, 117, .18) !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .dataTables_wrapper .dt-buttons {
        border-color: rgba(79, 131, 255, .16);
        background: #141B3D;
    }

    html[data-pms-theme="dark"] .parent-department-page .dataTables_wrapper .dt-buttons .dt-button {
        border-color: rgba(79, 131, 255, .18) !important;
        background: #141B3D !important;
        color: #CBD5E1 !important;
    }

    /* ===== DARK MODE SYSTEM FOR PARENT DEPARTMENTS ===== */
    html[data-pms-theme="dark"] .parent-department-page,
    html[data-bs-theme="dark"] .parent-department-page,
    html[data-theme="dark"] .parent-department-page,
    html.dark .parent-department-page,
    body[data-pms-theme="dark"] .parent-department-page,
    body[data-bs-theme="dark"] .parent-department-page,
    body.dark .parent-department-page,
    [data-pms-theme="dark"] .parent-department-page,
    [data-theme="dark"] .parent-department-page,
    [data-bs-theme="dark"] .parent-department-page,
    .dark .parent-department-page {
        background: #070B1A !important;
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .breadcrumb,
    html[data-bs-theme="dark"] .parent-department-page .breadcrumb,
    html[data-theme="dark"] .parent-department-page .breadcrumb,
    html.dark .parent-department-page .breadcrumb {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.2) !important;
        color: #60A5FA !important;
    }

    /* Header Card in Dark Mode */
    html[data-pms-theme="dark"] .parent-department-page .header-card,
    html[data-bs-theme="dark"] .parent-department-page .header-card,
    html[data-theme="dark"] .parent-department-page .header-card,
    html.dark .parent-department-page .header-card,
    body[data-pms-theme="dark"] .parent-department-page .header-card,
    [data-pms-theme="dark"] .parent-department-page .header-card,
    .dark .parent-department-page .header-card {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.2) !important;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.4) !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .header-card h1,
    html[data-bs-theme="dark"] .parent-department-page .header-card h1,
    html[data-theme="dark"] .parent-department-page .header-card h1,
    html.dark .parent-department-page .header-card h1 {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .header-card p,
    html[data-bs-theme="dark"] .parent-department-page .header-card p,
    html[data-theme="dark"] .parent-department-page .header-card p,
    html.dark .parent-department-page .header-card p {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    /* Stat Cards in Dark Mode */
    html[data-pms-theme="dark"] .parent-department-page .stat-card,
    html[data-bs-theme="dark"] .parent-department-page .stat-card,
    html[data-theme="dark"] .parent-department-page .stat-card,
    html.dark .parent-department-page .stat-card,
    body[data-pms-theme="dark"] .parent-department-page .stat-card,
    [data-pms-theme="dark"] .parent-department-page .stat-card,
    .dark .parent-department-page .stat-card,
    html[data-pms-theme="dark"] .parent-department-page .stat-card:first-of-type,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:first-of-type,
    html[data-theme="dark"] .parent-department-page .stat-card:first-of-type,
    html.dark .parent-department-page .stat-card:first-of-type,
    html[data-pms-theme="dark"] .parent-department-page .stat-card.is-featured,
    html[data-bs-theme="dark"] .parent-department-page .stat-card.is-featured,
    html[data-theme="dark"] .parent-department-page .stat-card.is-featured,
    html.dark .parent-department-page .stat-card.is-featured {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.25) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35) !important;
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .stat-card h3,
    html[data-bs-theme="dark"] .parent-department-page .stat-card h3,
    html[data-theme="dark"] .parent-department-page .stat-card h3,
    html.dark .parent-department-page .stat-card h3,
    html[data-pms-theme="dark"] .parent-department-page .stat-card:first-of-type h3,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:first-of-type h3,
    html[data-theme="dark"] .parent-department-page .stat-card:first-of-type h3,
    html.dark .parent-department-page .stat-card:first-of-type h3,
    html[data-pms-theme="dark"] .parent-department-page .stat-card:first-of-type .stat-value,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:first-of-type .stat-value {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .stat-card span,
    html[data-bs-theme="dark"] .parent-department-page .stat-card span,
    html[data-theme="dark"] .parent-department-page .stat-card span,
    html.dark .parent-department-page .stat-card span,
    html[data-pms-theme="dark"] .parent-department-page .stat-card:first-of-type span,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:first-of-type span,
    html[data-theme="dark"] .parent-department-page .stat-card:first-of-type span,
    html.dark .parent-department-page .stat-card:first-of-type span,
    html[data-pms-theme="dark"] .parent-department-page .stat-card:first-of-type .stat-title,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:first-of-type .stat-title {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    /* Subtext (highlighted section: Available parent departments) in Dark Mode */
    html[data-pms-theme="dark"] .parent-department-page .stat-sub,
    html[data-bs-theme="dark"] .parent-department-page .stat-sub,
    html[data-theme="dark"] .parent-department-page .stat-sub,
    html.dark .parent-department-page .stat-sub,
    html[data-pms-theme="dark"] .parent-department-page .stat-card:first-of-type .stat-sub,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:first-of-type .stat-sub,
    html[data-theme="dark"] .parent-department-page .stat-card:first-of-type .stat-sub,
    html.dark .parent-department-page .stat-card:first-of-type .stat-sub,
    html[data-pms-theme="dark"] .parent-department-page .stat-card:first-of-type .stat-trend,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:first-of-type .stat-trend {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
        font-weight: 600 !important;
    }

    /* Stat Icons in Dark Mode */
    html[data-pms-theme="dark"] .parent-department-page .stat-card:nth-child(1) .stat-icon,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:nth-child(1) .stat-icon,
    html[data-theme="dark"] .parent-department-page .stat-card:nth-child(1) .stat-icon,
    html.dark .parent-department-page .stat-card:nth-child(1) .stat-icon,
    html[data-pms-theme="dark"] .parent-department-page .stat-card:first-of-type .stat-icon,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:first-of-type .stat-icon {
        background: linear-gradient(135deg, rgba(47, 107, 255, 0.35) 0%, rgba(47, 107, 255, 0.55) 100%) !important;
        color: #C7D2FE !important;
        -webkit-text-fill-color: #C7D2FE !important;
        border: 1.5px solid rgba(79, 131, 255, 0.7) !important;
        box-shadow: 0 6px 20px rgba(47, 107, 255, 0.35) !important;
    }
    html[data-pms-theme="dark"] .parent-department-page .stat-card:nth-child(1) .stat-icon *,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:nth-child(1) .stat-icon *,
    html[data-theme="dark"] .parent-department-page .stat-card:nth-child(1) .stat-icon *,
    html.dark .parent-department-page .stat-card:nth-child(1) .stat-icon *,
    html[data-pms-theme="dark"] .parent-department-page .stat-card:first-of-type .stat-icon *,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:first-of-type .stat-icon * {
        background: transparent !important;
        background-image: none !important;
        color: #C7D2FE !important;
        -webkit-text-fill-color: #C7D2FE !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .stat-card:nth-child(2) .stat-icon,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:nth-child(2) .stat-icon,
    html[data-theme="dark"] .parent-department-page .stat-card:nth-child(2) .stat-icon,
    html.dark .parent-department-page .stat-card:nth-child(2) .stat-icon {
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.35) 0%, rgba(2, 132, 199, 0.55) 100%) !important;
        color: #bae6fd !important;
        -webkit-text-fill-color: #bae6fd !important;
        border: 1.5px solid rgba(56, 189, 248, 0.7) !important;
        box-shadow: 0 6px 20px rgba(56, 189, 248, 0.35) !important;
    }
    html[data-pms-theme="dark"] .parent-department-page .stat-card:nth-child(2) .stat-icon *,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:nth-child(2) .stat-icon *,
    html[data-theme="dark"] .parent-department-page .stat-card:nth-child(2) .stat-icon *,
    html.dark .parent-department-page .stat-card:nth-child(2) .stat-icon * {
        background: transparent !important;
        background-image: none !important;
        color: #bae6fd !important;
        -webkit-text-fill-color: #bae6fd !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .stat-card:nth-child(3) .stat-icon,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:nth-child(3) .stat-icon,
    html[data-theme="dark"] .parent-department-page .stat-card:nth-child(3) .stat-icon,
    html.dark .parent-department-page .stat-card:nth-child(3) .stat-icon {
        background: linear-gradient(135deg, rgba(129, 140, 248, 0.35) 0%, rgba(79, 70, 229, 0.55) 100%) !important;
        color: #c7d2fe !important;
        -webkit-text-fill-color: #c7d2fe !important;
        border: 1.5px solid rgba(129, 140, 248, 0.7) !important;
        box-shadow: 0 6px 20px rgba(129, 140, 248, 0.35) !important;
    }
    html[data-pms-theme="dark"] .parent-department-page .stat-card:nth-child(3) .stat-icon *,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:nth-child(3) .stat-icon *,
    html[data-theme="dark"] .parent-department-page .stat-card:nth-child(3) .stat-icon *,
    html.dark .parent-department-page .stat-card:nth-child(3) .stat-icon * {
        background: transparent !important;
        background-image: none !important;
        color: #c7d2fe !important;
        -webkit-text-fill-color: #c7d2fe !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .stat-card:nth-child(4) .stat-icon,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:nth-child(4) .stat-icon,
    html[data-theme="dark"] .parent-department-page .stat-card:nth-child(4) .stat-icon,
    html.dark .parent-department-page .stat-card:nth-child(4) .stat-icon {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.35) 0%, rgba(217, 119, 6, 0.55) 100%) !important;
        color: #fef08a !important;
        -webkit-text-fill-color: #fef08a !important;
        border: 1.5px solid rgba(251, 191, 36, 0.7) !important;
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.35) !important;
    }
    html[data-pms-theme="dark"] .parent-department-page .stat-card:nth-child(4) .stat-icon *,
    html[data-bs-theme="dark"] .parent-department-page .stat-card:nth-child(4) .stat-icon *,
    html[data-theme="dark"] .parent-department-page .stat-card:nth-child(4) .stat-icon *,
    html.dark .parent-department-page .stat-card:nth-child(4) .stat-icon * {
        background: transparent !important;
        background-image: none !important;
        color: #fef08a !important;
        -webkit-text-fill-color: #fef08a !important;
    }

    /* Table Card & Content in Dark Mode */
    html[data-pms-theme="dark"] .parent-department-page .table-card,
    html[data-bs-theme="dark"] .parent-department-page .table-card,
    html[data-theme="dark"] .parent-department-page .table-card,
    html.dark .parent-department-page .table-card {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.2) !important;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.4) !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .table-header,
    html[data-bs-theme="dark"] .parent-department-page .table-header,
    html[data-theme="dark"] .parent-department-page .table-header,
    html.dark .parent-department-page .table-header {
        background: #141B3D !important;
        border-bottom: 1px solid rgba(79, 131, 255, 0.15) !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .table-title h3,
    html[data-bs-theme="dark"] .parent-department-page .table-title h3,
    html[data-theme="dark"] .parent-department-page .table-title h3,
    html.dark .parent-department-page .table-title h3 {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .search-box input,
    html[data-bs-theme="dark"] .parent-department-page .search-box input,
    html[data-theme="dark"] .parent-department-page .search-box input,
    html.dark .parent-department-page .search-box input {
        background: #141B3D !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #FFFFFF !important;
        -webkit-text-fill-color: #FFFFFF !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .department-list-table thead th,
    html[data-bs-theme="dark"] .parent-department-page .department-list-table thead th,
    html[data-theme="dark"] .parent-department-page .department-list-table thead th,
    html.dark .parent-department-page .department-list-table thead th {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
        border-bottom-color: rgba(79, 131, 255, 0.2) !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .department-list-table tbody td,
    html[data-bs-theme="dark"] .parent-department-page .department-list-table tbody td,
    html[data-theme="dark"] .parent-department-page .department-list-table tbody td,
    html.dark .parent-department-page .department-list-table tbody td {
        background: #141B3D !important;
        border-color: rgba(79, 131, 255, 0.12) !important;
        color: #E2E8F0 !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .department-list-table tbody tr:hover td,
    html[data-bs-theme="dark"] .parent-department-page .department-list-table tbody tr:hover td,
    html[data-theme="dark"] .parent-department-page .department-list-table tbody tr:hover td,
    html.dark .parent-department-page .department-list-table tbody tr:hover td {
        background: #1a234a !important;
        border-color: rgba(79, 131, 255, 0.3) !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .department-code,
    html[data-bs-theme="dark"] .parent-department-page .department-code,
    html[data-theme="dark"] .parent-department-page .department-code,
    html.dark .parent-department-page .department-code {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .department-name,
    html[data-bs-theme="dark"] .parent-department-page .department-name,
    html[data-theme="dark"] .parent-department-page .department-name,
    html.dark .parent-department-page .department-name,
    html[data-pms-theme="dark"] .parent-department-page .create-date,
    html[data-bs-theme="dark"] .parent-department-page .create-date,
    html[data-theme="dark"] .parent-department-page .create-date,
    html.dark .parent-department-page .create-date,
    html[data-pms-theme="dark"] .parent-department-page .update-date,
    html[data-bs-theme="dark"] .parent-department-page .update-date,
    html[data-theme="dark"] .parent-department-page .update-date,
    html.dark .parent-department-page .update-date {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .muted,
    html[data-bs-theme="dark"] .parent-department-page .muted,
    html[data-theme="dark"] .parent-department-page .muted,
    html.dark .parent-department-page .muted,
    html[data-pms-theme="dark"] .parent-department-page .text-muted,
    html[data-bs-theme="dark"] .parent-department-page .text-muted,
    html[data-theme="dark"] .parent-department-page .text-muted,
    html.dark .parent-department-page .text-muted {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    /* Table Mini-Icons in Dark Mode */
    html[data-pms-theme="dark"] .parent-department-page .mini-icon,
    html[data-bs-theme="dark"] .parent-department-page .mini-icon,
    html[data-theme="dark"] .parent-department-page .mini-icon,
    html.dark .parent-department-page .mini-icon,
    body[data-pms-theme="dark"] .parent-department-page .mini-icon,
    body[data-bs-theme="dark"] .parent-department-page .mini-icon,
    body[data-theme="dark"] .parent-department-page .mini-icon,
    body.dark .parent-department-page .mini-icon,
    [data-pms-theme="dark"] .parent-department-page .mini-icon,
    [data-bs-theme="dark"] .parent-department-page .mini-icon,
    [data-theme="dark"] .parent-department-page .mini-icon,
    .dark .parent-department-page .mini-icon {
        background: linear-gradient(135deg, rgba(47, 107, 255, 0.25) 0%, rgba(47, 107, 255, 0.45) 100%) !important;
        border: 1.5px solid rgba(79, 131, 255, 0.6) !important;
        box-shadow: 0 4px 14px rgba(47, 107, 255, 0.25) !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .mini-icon i,
    html[data-pms-theme="dark"] .parent-department-page .mini-icon .fa,
    html[data-pms-theme="dark"] .parent-department-page .mini-icon [class*="fa-"],
    html[data-bs-theme="dark"] .parent-department-page .mini-icon i,
    html[data-bs-theme="dark"] .parent-department-page .mini-icon .fa,
    html[data-bs-theme="dark"] .parent-department-page .mini-icon [class*="fa-"],
    html[data-theme="dark"] .parent-department-page .mini-icon i,
    html[data-theme="dark"] .parent-department-page .mini-icon .fa,
    html[data-theme="dark"] .parent-department-page .mini-icon [class*="fa-"],
    html.dark .parent-department-page .mini-icon i,
    html.dark .parent-department-page .mini-icon .fa,
    html.dark .parent-department-page .mini-icon [class*="fa-"],
    body[data-pms-theme="dark"] .parent-department-page .mini-icon i,
    body[data-bs-theme="dark"] .parent-department-page .mini-icon i,
    [data-pms-theme="dark"] .parent-department-page .mini-icon i,
    .dark .parent-department-page .mini-icon i {
        background: transparent !important;
        background-image: none !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
        display: inline-block !important;
        font-size: 1.15rem !important;
    }

    /* Column 2 - Layer Group Icon */
    html[data-pms-theme="dark"] .parent-department-page .department-list-table td:nth-child(2) .mini-icon,
    html[data-bs-theme="dark"] .parent-department-page .department-list-table td:nth-child(2) .mini-icon,
    html[data-theme="dark"] .parent-department-page .department-list-table td:nth-child(2) .mini-icon,
    html.dark .parent-department-page .department-list-table td:nth-child(2) .mini-icon {
        background: linear-gradient(135deg, rgba(47, 107, 255, 0.3) 0%, rgba(47, 107, 255, 0.5) 100%) !important;
        border: 1.5px solid rgba(79, 131, 255, 0.65) !important;
        box-shadow: 0 4px 12px rgba(47, 107, 255, 0.3) !important;
    }
    html[data-pms-theme="dark"] .parent-department-page .department-list-table td:nth-child(2) .mini-icon *,
    html[data-bs-theme="dark"] .parent-department-page .department-list-table td:nth-child(2) .mini-icon *,
    html[data-theme="dark"] .parent-department-page .department-list-table td:nth-child(2) .mini-icon *,
    html.dark .parent-department-page .department-list-table td:nth-child(2) .mini-icon * {
        color: #C7D2FE !important;
        -webkit-text-fill-color: #C7D2FE !important;
    }

    /* Column 3 - Tag Icon */
    html[data-pms-theme="dark"] .parent-department-page .department-list-table td:nth-child(3) .mini-icon,
    html[data-bs-theme="dark"] .parent-department-page .department-list-table td:nth-child(3) .mini-icon,
    html[data-theme="dark"] .parent-department-page .department-list-table td:nth-child(3) .mini-icon,
    html.dark .parent-department-page .department-list-table td:nth-child(3) .mini-icon {
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.3) 0%, rgba(2, 132, 199, 0.5) 100%) !important;
        border: 1.5px solid rgba(56, 189, 248, 0.65) !important;
        box-shadow: 0 4px 12px rgba(56, 189, 248, 0.3) !important;
    }
    html[data-pms-theme="dark"] .parent-department-page .department-list-table td:nth-child(3) .mini-icon *,
    html[data-bs-theme="dark"] .parent-department-page .department-list-table td:nth-child(3) .mini-icon *,
    html[data-theme="dark"] .parent-department-page .department-list-table td:nth-child(3) .mini-icon *,
    html.dark .parent-department-page .department-list-table td:nth-child(3) .mini-icon * {
        color: #bae6fd !important;
        -webkit-text-fill-color: #bae6fd !important;
    }

    /* Column 4 - Building Icon */
    html[data-pms-theme="dark"] .parent-department-page .department-list-table td:nth-child(4) .mini-icon,
    html[data-bs-theme="dark"] .parent-department-page .department-list-table td:nth-child(4) .mini-icon,
    html[data-theme="dark"] .parent-department-page .department-list-table td:nth-child(4) .mini-icon,
    html.dark .parent-department-page .department-list-table td:nth-child(4) .mini-icon {
        background: linear-gradient(135deg, rgba(129, 140, 248, 0.3) 0%, rgba(79, 70, 229, 0.5) 100%) !important;
        border: 1.5px solid rgba(129, 140, 248, 0.65) !important;
        box-shadow: 0 4px 12px rgba(129, 140, 248, 0.3) !important;
    }
    html[data-pms-theme="dark"] .parent-department-page .department-list-table td:nth-child(4) .mini-icon *,
    html[data-bs-theme="dark"] .parent-department-page .department-list-table td:nth-child(4) .mini-icon *,
    html[data-theme="dark"] .parent-department-page .department-list-table td:nth-child(4) .mini-icon *,
    html.dark .parent-department-page .department-list-table td:nth-child(4) .mini-icon * {
        color: #c7d2fe !important;
        -webkit-text-fill-color: #c7d2fe !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .action-btn,
    html[data-bs-theme="dark"] .parent-department-page .action-btn,
    html[data-theme="dark"] .parent-department-page .action-btn,
    html.dark .parent-department-page .action-btn {
        background: rgba(79, 131, 255, 0.12) !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    /* Footer & Pagination in Dark Mode */
    html[data-pms-theme="dark"] .parent-department-page .footer,
    html[data-bs-theme="dark"] .parent-department-page .footer,
    html[data-theme="dark"] .parent-department-page .footer,
    html.dark .parent-department-page .footer,
    body[data-pms-theme="dark"] .parent-department-page .footer,
    body[data-bs-theme="dark"] .parent-department-page .footer,
    body[data-theme="dark"] .parent-department-page .footer,
    body.dark .parent-department-page .footer,
    [data-pms-theme="dark"] .parent-department-page .footer,
    [data-bs-theme="dark"] .parent-department-page .footer,
    [data-theme="dark"] .parent-department-page .footer,
    .dark .parent-department-page .footer {
        background: #141B3D !important;
        border-top: 1px solid rgba(79, 131, 255, 0.15) !important;
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .footer *,
    html[data-bs-theme="dark"] .parent-department-page .footer *,
    html[data-theme="dark"] .parent-department-page .footer *,
    html.dark .parent-department-page .footer *,
    body[data-pms-theme="dark"] .parent-department-page .footer *,
    body[data-bs-theme="dark"] .parent-department-page .footer *,
    body[data-theme="dark"] .parent-department-page .footer *,
    body.dark .parent-department-page .footer *,
    [data-pms-theme="dark"] .parent-department-page .footer *,
    [data-bs-theme="dark"] .parent-department-page .footer *,
    [data-theme="dark"] .parent-department-page .footer *,
    .dark .parent-department-page .footer * {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .pagination-info i,
    html[data-bs-theme="dark"] .parent-department-page .pagination-info i,
    html[data-theme="dark"] .parent-department-page .pagination-info i,
    html.dark .parent-department-page .pagination-info i,
    body[data-pms-theme="dark"] .parent-department-page .pagination-info i,
    [data-pms-theme="dark"] .parent-department-page .pagination-info i,
    .dark .parent-department-page .pagination-info i {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .show-entries select,
    html[data-bs-theme="dark"] .parent-department-page .show-entries select,
    html[data-theme="dark"] .parent-department-page .show-entries select,
    html.dark .parent-department-page .show-entries select,
    body[data-pms-theme="dark"] .parent-department-page .show-entries select,
    body[data-bs-theme="dark"] .parent-department-page .show-entries select,
    body[data-theme="dark"] .parent-department-page .show-entries select,
    body.dark .parent-department-page .show-entries select,
    [data-pms-theme="dark"] .parent-department-page .show-entries select,
    [data-bs-theme="dark"] .parent-department-page .show-entries select,
    [data-theme="dark"] .parent-department-page .show-entries select,
    .dark .parent-department-page .show-entries select,
    html[data-pms-theme="dark"] .parent-department-page .page-btn,
    html[data-bs-theme="dark"] .parent-department-page .page-btn,
    html[data-theme="dark"] .parent-department-page .page-btn,
    html.dark .parent-department-page .page-btn,
    body[data-pms-theme="dark"] .parent-department-page .page-btn,
    body[data-bs-theme="dark"] .parent-department-page .page-btn,
    body[data-theme="dark"] .parent-department-page .page-btn,
    body.dark .parent-department-page .page-btn,
    [data-pms-theme="dark"] .parent-department-page .page-btn,
    [data-bs-theme="dark"] .parent-department-page .page-btn,
    [data-theme="dark"] .parent-department-page .page-btn,
    .dark .parent-department-page .page-btn {
        background: rgba(79, 131, 255, 0.12) !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    /* Legend Card in Dark Mode */
    html[data-pms-theme="dark"] .parent-department-page .legend-card,
    html[data-bs-theme="dark"] .parent-department-page .legend-card,
    html[data-theme="dark"] .parent-department-page .legend-card,
    html.dark .parent-department-page .legend-card,
    body[data-pms-theme="dark"] .parent-department-page .legend-card,
    body[data-bs-theme="dark"] .parent-department-page .legend-card,
    body[data-theme="dark"] .parent-department-page .legend-card,
    body.dark .parent-department-page .legend-card,
    [data-pms-theme="dark"] .parent-department-page .legend-card,
    [data-bs-theme="dark"] .parent-department-page .legend-card,
    [data-theme="dark"] .parent-department-page .legend-card,
    .dark .parent-department-page .legend-card {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.2) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35) !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .legend-title,
    html[data-bs-theme="dark"] .parent-department-page .legend-title,
    html[data-theme="dark"] .parent-department-page .legend-title,
    html.dark .parent-department-page .legend-title,
    body[data-pms-theme="dark"] .parent-department-page .legend-title,
    body[data-bs-theme="dark"] .parent-department-page .legend-title,
    body[data-theme="dark"] .parent-department-page .legend-title,
    body.dark .parent-department-page .legend-title,
    [data-pms-theme="dark"] .parent-department-page .legend-title,
    [data-bs-theme="dark"] .parent-department-page .legend-title,
    [data-theme="dark"] .parent-department-page .legend-title,
    .dark .parent-department-page .legend-title {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .parent-department-page .legend-item,
    html[data-bs-theme="dark"] .parent-department-page .legend-item,
    html[data-theme="dark"] .parent-department-page .legend-item,
    html.dark .parent-department-page .legend-item,
    body[data-pms-theme="dark"] .parent-department-page .legend-item,
    body[data-bs-theme="dark"] .parent-department-page .legend-item,
    body[data-theme="dark"] .parent-department-page .legend-item,
    body.dark .parent-department-page .legend-item,
    [data-pms-theme="dark"] .parent-department-page .legend-item,
    [data-bs-theme="dark"] .parent-department-page .legend-item,
    [data-theme="dark"] .parent-department-page .legend-item,
    .dark .parent-department-page .legend-item {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    @media (max-width: 768px) {
        .parent-department-page .dataTables_wrapper .dt-buttons {
            width: calc(100% - 32px);
            margin-inline: 16px;
        }

        .parent-department-page .dataTables_wrapper .dt-buttons .dt-button {
            flex: 1 1 120px;
            justify-content: center;
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
        deleteBtn.html('<i class="fas fa-spinner fa-spin"></i>');
        deleteBtn.prop('disabled', true);
        deleteBtn.addClass('loading');

        $.ajax({
            url: `{{ route('parent-departments.destroy', ':id') }}`.replace(':id', departmentId),
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

        deleteBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Archiving...');
        deleteBtn.prop('disabled', true);

        $.ajax({
            url: "{{ route('parent-departments.bulk-delete') }}",
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
                window.location.href = "{{ route('parent-departments.show', '') }}/" + departmentId;
            }
        }
    });
});
</script>
@endpush

@endsection
