@extends('admin.layout.app')

@section('title', 'Sub Departments')

@section('content')

<div class="sub-department-page">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <i class="fas fa-sitemap"></i> Dashboard / Sub Departments
    </div>

    <!-- Header Card -->
    <div class="header-card">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas fa-sitemap"></i>
            </div>
            <div>
                <h1>Sub Department Management</h1>
                <p>Manage teams and their parent department assignments</p>
            </div>
        </div>
        <div class="btn-group">
            <a href="{{ route('departments.archive') }}" class="btn btn-light">
                <i class="fas fa-box-archive"></i> Archived
                @if(($archivedCount ?? 0) > 0)
                    <span class="archive-count-badge">{{ $archivedCount }}</span>
                @endif
            </a>
            <a href="{{ route('departments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Add Sub Department
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
            <div class="stat-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <h3>{{ $departments->count() }}</h3>
                <span>Total Sub Departments</span>
                <p class="stat-sub">Available sub departments</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div>
                <h3>{{ $parentCount }}</h3>
                <span>Parent Departments</span>
                <p class="stat-sub">Unique parent departments</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <h3>{{ $employeeCount }}</h3>
                <span>Assigned Employees</span>
                <p class="stat-sub">Total employees in sub depts</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-slash"></i>
            </div>
            <div>
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
                    <i class="fas fa-list-ul"></i>
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
                        <th><i class="fas fa-hashtag"></i> #</th>
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
                                        <a class="dropdown-item" href="{{ route('departments.show', $dept) }}">
                                            <i class="fas fa-eye"></i> View Sub Department
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('departments.edit', $dept) }}">
                                            <i class="fas fa-pen"></i> Edit Sub Department
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('departments.destroy', $dept) }}" style="display:inline-block; width:100%;">
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
                        <td colspan="6" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-sitemap fa-4x"></i>
                                <h5>No Sub Departments Found</h5>
                                <p>Get started by creating your first sub department</p>
                                <a href="{{ route('departments.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i> Create Sub Department
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
            <span class="legend-title"><i class="fas fa-palette"></i> Department Info:</span>
            <span class="legend-item"><i class="fas fa-code text-primary"></i> Department Code</span>
            <span class="legend-item"><i class="fas fa-building text-success"></i> Department Name</span>
            <span class="legend-item"><i class="fas fa-arrow-right text-warning"></i> Parent Department</span>
            <span class="legend-item"><i class="fas fa-users text-info"></i> Employee Count</span>
        </div>
    </div>
</div>

<style>
    /* ===== PREMIUM GREEN/TEAL THEME WITH MODERN ICON STYLE ===== */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .sub-department-page {
        padding: 30px 35px;
        min-height: 100vh;
        background: linear-gradient(135deg, #f0f9f4 0%, #e6f3ec 50%, #f4fbf7 100%);
        color: #0a2e1f;
        position: relative;
    }

    .sub-department-page::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 0% 0%, rgba(16, 185, 129, 0.03) 0%, transparent 50%),
                    radial-gradient(circle at 100% 100%, rgba(52, 211, 153, 0.03) 0%, transparent 50%);
        pointer-events: none;
    }

    /* Breadcrumb */
    .breadcrumb {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        padding: 14px 24px;
        border-radius: 16px;
        border: 1px solid rgba(16, 185, 129, 0.15);
        margin-bottom: 28px;
        color: #0f744c;
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .breadcrumb i {
        margin-right: 8px;
        color: #34d399;
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
        box-shadow: 0 20px 40px -12px rgba(16, 185, 129, 0.12);
        border: 1px solid rgba(16, 185, 129, 0.12);
        margin-bottom: 32px;
        transition: all 0.3s ease;
    }

    .header-card:hover {
        box-shadow: 0 24px 48px -16px rgba(16, 185, 129, 0.18);
        border-color: rgba(16, 185, 129, 0.2);
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 22px;
    }

    .header-icon {
        width: 72px;
        height: 72px;
        background: linear-gradient(145deg, #34d399, #059669);
        color: white;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        box-shadow: 0 12px 24px -8px rgba(5, 150, 105, 0.3);
        transition: all 0.3s ease;
    }

    .header-card:hover .header-icon {
        transform: scale(1.02);
    }

    .header-card h1 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 6px;
        background: linear-gradient(135deg, #0a2e1f, #0f744c);
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

    .archive-count-badge {
        min-width: 22px;
        height: 22px;
        padding: 0 7px;
        border-radius: 999px;
        background: #f59e0b;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        font-weight: 800;
        line-height: 1;
    }

    .btn-primary {
        background: linear-gradient(145deg, #34d399, #059669);
        color: white;
        box-shadow: 0 8px 20px -6px rgba(5, 150, 105, 0.35);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px -8px rgba(5, 150, 105, 0.45);
    }

    /* Stats Cards */
    .stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 24px;
        border: 1px solid rgba(16, 185, 129, 0.1);
        display: flex;
        gap: 18px;
        align-items: flex-start;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .stat-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #34d399, #059669);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .stat-card:hover::after {
        transform: scaleX(1);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 35px -12px rgba(16, 185, 129, 0.15);
        border-color: rgba(16, 185, 129, 0.2);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        color: #059669;
    }

    .stat-card h3 {
        font-size: 32px;
        font-weight: 800;
        color: #0a2e1f;
        margin-bottom: 6px;
        line-height: 1;
    }

    .stat-card span {
        color: #6b7280;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-sub {
        font-size: 11px;
        color: #9ca3af;
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
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        color: #065f46;
        border-left: 4px solid #10b981;
    }

    .alert-danger {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        color: #991b1b;
        border-left: 4px solid #ef4444;
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
        border: 1px solid rgba(16, 185, 129, 0.1);
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }

    .table-card:hover {
        box-shadow: 0 12px 40px rgba(16, 185, 129, 0.08);
    }

    .table-header {
        padding: 22px 28px;
        background: linear-gradient(135deg, #ffffff, #fafefb);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        border-bottom: 1px solid rgba(16, 185, 129, 0.1);
    }

    .table-title {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .table-title-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        color: #059669;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }

    .table-title h3 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #0a2e1f;
        margin: 0;
    }

    .muted {
        font-size: 0.75rem;
        color: #8ba198;
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
        border: 1px solid rgba(16, 185, 129, 0.2);
        outline: none;
        font-weight: 500;
        transition: all 0.2s ease;
        background: #ffffff;
    }

    .search-box input:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.1);
    }

    .search-box i {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #34d399;
    }

    /* Bulk Actions Bar */
    .bulk-actions-bar {
        padding: 14px 28px;
        background: #ecfdf5;
        border-bottom: 1px solid rgba(16, 185, 129, 0.12);
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
        color: #059669;
        border-radius: 40px;
        font-size: 0.85rem;
        font-weight: 600;
        border: 1px solid rgba(5, 150, 105, 0.2);
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
        border-bottom: 2px solid rgba(16, 185, 129, 0.1);
    }

    .department-list-table thead th i {
        margin-right: 8px;
        font-size: 0.75rem;
        color: #34d399;
    }

    .department-list-table tbody td {
        background: #ffffff;
        padding: 18px 20px;
        border-top: 1px solid rgba(16, 185, 129, 0.08);
        border-bottom: 1px solid rgba(16, 185, 129, 0.08);
        font-weight: 500;
        vertical-align: middle;
        transition: all 0.2s ease;
    }

    .department-list-table tbody td:first-child {
        border-left: 1px solid rgba(16, 185, 129, 0.08);
        border-radius: 20px 0 0 20px;
    }

    .department-list-table tbody td:last-child {
        border-right: 1px solid rgba(16, 185, 129, 0.08);
        border-radius: 0 20px 20px 0;
    }

    .department-list-table tbody tr:hover td {
        background: #fafefb;
        border-color: rgba(16, 185, 129, 0.15);
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
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        transition: all 0.2s ease;
    }

    .department-row:hover .mini-icon {
        transform: scale(1.05);
    }

    .department-code, .department-name, .department-number, .parent-name {
        font-weight: 700;
        color: #0a2e1f;
    }

    .department-code {
        color: #059669;
    }

    .parent-name {
        color: #2563eb;
    }

    .muted {
        display: block;
        margin-top: 4px;
        color: #8ba198;
        font-size: 0.7rem;
        font-weight: 500;
    }

    .muted i {
        margin-right: 4px;
        font-size: 0.65rem;
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
        background: #d1fae5;
        color: #059669;
        transform: scale(1.05);
    }

    /* Dropdown */
    .dropdown-menu {
        background: white;
        border: 1px solid rgba(16, 185, 129, 0.15);
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
        background: #ecfdf5;
        color: #059669;
    }

    .dropdown-item.text-danger:hover {
        background: #fef2f2;
        color: #dc2626;
    }

    /* Footer */
    .footer {
        padding: 20px 28px;
        background: #fafefb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        color: #6b7280;
        font-weight: 500;
        border-top: 1px solid rgba(16, 185, 129, 0.08);
    }

    .show-entries {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .show-entries select {
        background: white;
        border: 1px solid rgba(16, 185, 129, 0.2);
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
        color: #34d399;
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
        border: 1px solid rgba(16, 185, 129, 0.2);
        color: #5a6e63;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .page-btn:hover:not(.disabled) {
        background: #d1fae5;
        border-color: #34d399;
        color: #059669;
    }

    .page-btn.active {
        background: linear-gradient(145deg, #34d399, #059669);
        border-color: transparent;
        color: white;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
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
        border: 2px solid #a7f3d0;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .form-check-input:checked {
        background-color: #059669;
        border-color: #059669;
        box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2);
    }

    .form-check-label {
        margin-left: 8px;
        font-size: 0.85rem;
        cursor: pointer;
    }

    /* Legend Card */
    .legend-card {
        margin-top: 28px;
        background: white;
        border: 1px solid rgba(16, 185, 129, 0.1);
        border-radius: 24px;
        padding: 18px 28px;
        transition: all 0.3s ease;
    }

    .legend-card:hover {
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.08);
    }

    .legend-content {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .legend-title {
        font-weight: 700;
        color: #0a2e1f;
        font-size: 0.85rem;
    }

    .legend-title i {
        margin-right: 8px;
        color: #34d399;
    }

    .legend-item {
        font-size: 0.8rem;
        color: #5a6e63;
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
        color: #a7f3d0;
        margin-bottom: 20px;
    }

    .empty-state h5 {
        color: #0f744c;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #8ba198;
        margin-bottom: 20px;
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
        .sub-department-page {
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
        .sub-department-page {
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

        .sub-department-page {
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
    .sub-department-page {
        font-size: 16px;
        line-height: 1.55;
    }

    .sub-department-page .breadcrumb {
        font-size: 1rem;
    }

    .sub-department-page .header-card h1 {
        font-size: 36px;
        line-height: 1.2;
    }

    .sub-department-page .header-card p {
        font-size: 17px;
        line-height: 1.55;
    }

    .sub-department-page .btn {
        padding: 13px 24px;
        font-size: 1rem;
    }

    .sub-department-page .stat-card h3 {
        font-size: 36px;
    }

    .sub-department-page .stat-card span {
        font-size: 14px;
        line-height: 1.4;
    }

    .sub-department-page .stat-sub {
        font-size: 13px;
        line-height: 1.45;
    }

    .sub-department-page .alert {
        font-size: 1rem;
    }

    .sub-department-page .table-title h3 {
        font-size: 1.45rem;
    }

    .sub-department-page .table-title .muted {
        font-size: .9rem;
    }

    .sub-department-page .search-box input {
        width: 320px;
        min-height: 48px;
        font-size: 1rem;
    }

    .sub-department-page .selected-badge,
    .sub-department-page .btn-clear,
    .sub-department-page .btn-bulk-delete,
    .sub-department-page .form-check-label {
        font-size: .95rem;
    }

    .sub-department-page .department-list-table {
        min-width: 1200px;
    }

    .sub-department-page .department-list-table thead th {
        padding: 17px 20px;
        font-size: .9rem;
        line-height: 1.4;
    }

    .sub-department-page .department-list-table thead th i {
        font-size: .9rem;
    }

    .sub-department-page .department-list-table tbody td {
        padding: 20px;
        font-size: 1rem;
        line-height: 1.45;
    }

    .sub-department-page .department-code,
    .sub-department-page .department-name,
    .sub-department-page .department-number,
    .sub-department-page .parent-name {
        font-size: 1.05rem;
    }

    .sub-department-page .muted,
    .sub-department-page .department-list-table small,
    .sub-department-page .text-muted {
        font-size: .88rem !important;
        line-height: 1.45;
    }

    .sub-department-page .muted i {
        font-size: .8rem;
    }

    .sub-department-page .dropdown-item {
        padding: 12px 18px;
        font-size: 1rem;
    }

    .sub-department-page .footer,
    .sub-department-page .show-entries,
    .sub-department-page .pagination-info,
    .sub-department-page .show-entries select {
        font-size: 1rem;
    }

    .sub-department-page .page-btn {
        min-width: 42px;
        height: 42px;
        font-size: 1rem;
    }

    .sub-department-page .legend-title {
        font-size: 1rem;
    }

    .sub-department-page .legend-item {
        font-size: .9rem;
    }

    .sub-department-page .empty-state h5 {
        font-size: 1.35rem;
    }

    .sub-department-page .empty-state p {
        font-size: 1rem;
    }

    @media (max-width: 768px) {
        .sub-department-page {
            font-size: 15px;
        }

        .sub-department-page .header-card h1 {
            font-size: 29px;
        }

        .sub-department-page .header-card p {
            font-size: 16px;
        }

        .sub-department-page .search-box input {
            width: 100%;
        }
    }
</style>

<style>
    /* Keep dropdown menus fully visible above the page cards */
    .sub-department-page .header-card {
        position: relative;
        z-index: 50;
        overflow: visible !important;
    }

    .sub-department-page .header-card .btn-group,
    .sub-department-page .header-card .dropdown {
        position: relative;
        z-index: 60;
        overflow: visible;
    }

    .sub-department-page .header-card .dropdown-menu {
        z-index: 9999 !important;
        min-width: 230px;
        margin-top: 10px !important;
        padding: 10px;
        overflow: visible;
        border: 1px solid rgba(16, 185, 129, .18);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 24px 48px -14px rgba(10, 46, 31, .28);
    }

    .sub-department-page .header-card .dropdown-menu.show {
        display: block;
        visibility: visible;
        opacity: 1;
    }

    .sub-department-page .header-card .dropdown-item {
        width: 100%;
        min-height: 44px;
        white-space: nowrap;
    }

    .sub-department-page .stats,
    .sub-department-page .table-card,
    .sub-department-page .legend-card {
        position: relative;
        z-index: 1;
    }

    html[data-pms-theme="dark"] .sub-department-page .header-card .dropdown-menu {
        border-color: rgba(122, 240, 181, .2);
        background: #183026;
        box-shadow: 0 24px 48px -14px rgba(0, 0, 0, .55);
    }

    @media (max-width: 576px) {
        .sub-department-page .header-card .dropdown-menu {
            right: auto !important;
            left: 0 !important;
            max-width: calc(100vw - 48px);
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
            url: "{{ route('departments.bulk-delete') }}",
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
