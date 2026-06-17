
@extends('admin.layout.app')

@section('title', 'Designations')

@section('content')
<div class="designation-page">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <i class="fas fa-building"></i> Dashboard / Designations
    </div>

    <!-- Header Card -->
    <div class="header-card">
        <div class="header-left">
            <div class="header-icon">
                <i class="fas fa-briefcase"></i>
            </div>
            <div>
                <h1>Designation Management</h1>
                <p>Manage employee roles, hierarchy levels and designation records.</p>
            </div>
        </div>
        <div class="btn-group">
            <a href="{{ route('designations.hierarchy') }}" class="btn btn-light">
                <i class="fas fa-project-diagram"></i> Hierarchy
            </a>
            <a href="{{ route('designations.archive') }}" class="btn btn-light">
                <i class="fas fa-box-archive"></i> Archived
                @if(($archivedCount ?? 0) > 0)
                    <span class="archive-count-badge">{{ $archivedCount }}</span>
                @endif
            </a>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-cloud-download-alt"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button type="button" class="dropdown-item" onclick="exportTo('copy')"><i class="fas fa-copy text-primary"></i> Copy to Clipboard</button></li>
                    <li><button type="button" class="dropdown-item" onclick="exportTo('csv')"><i class="fas fa-file-csv text-success"></i> Export as CSV</button></li>
                    <li><button type="button" class="dropdown-item" onclick="exportTo('excel')"><i class="fas fa-file-excel text-success"></i> Export as Excel</button></li>
                    <li><button type="button" class="dropdown-item" onclick="exportTo('pdf')"><i class="fas fa-file-pdf text-danger"></i> Export as PDF</button></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><button type="button" class="dropdown-item" onclick="printTable()"><i class="fas fa-print text-info"></i> Print</button></li>
                </ul>
            </div>
            <a href="{{ route('designations.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Add New
            </a>
        </div>
    </div>

    @php
        $levelsCount = $designations->groupBy('level')->count();
        $topLevelCount = $designations->where('level', '<=', 2)->count();
        $recentCount = $designations->where('updated_at', '>=', now()->subDays(7))->count();
    @endphp

    <!-- Stats Cards -->
    <div class="stats">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <h3>{{ $designations->total() }}</h3>
                <span>Total Designations</span>
                <p class="stat-sub">Available organization roles</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <h3>{{ $levelsCount }}</h3>
                <span>Active Levels</span>
                <p class="stat-sub">Levels currently configured</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-crown"></i>
            </div>
            <div>
                <h3>{{ $topLevelCount }}</h3>
                <span>Top Level (1-2)</span>
                <p class="stat-sub">Leadership designations</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-history"></i>
            </div>
            <div>
                <h3>{{ $recentCount }}</h3>
                <span>Recently Updated</span>
                <p class="stat-sub">Changed during last 7 days</p>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i>
        <strong>Success!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Error!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Main Table Card -->
    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <div class="table-title-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <h3>Designation List</h3>
                    <span class="muted">{{ $designations->total() }} designations available</span>
                </div>
            </div>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="designationSearch" placeholder="Search designations..." />
            </div>
        </div>

        <!-- Level Filters Bar -->
        <div class="level-filters-bar">
            <span class="filter-label"><i class="fas fa-filter"></i> Filter by Level:</span>
            @for($i = 0; $i <= 6; $i++)
                <button class="level-filter-btn" data-level="{{ $i }}">L{{ $i }}</button>
            @endfor
            <button class="level-filter-btn reset-btn" onclick="resetFilters()"><i class="fas fa-undo-alt"></i> All</button>
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
            <table id="designationTable" class="designation-list-table">
                <thead>
                    <tr>
                        <th width="50">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="table-select-all">
                            </div>
                        </th>
                        <th><i class="fas fa-qrcode"></i> Code</th>
                        <th><i class="fas fa-user-tag"></i> Designation Name</th>
                        <th><i class="fas fa-chart-line"></i> Level</th>
                        <th><i class="fas fa-user-plus"></i> Added By</th>
                        <th><i class="fas fa-calendar-alt"></i> Last Updated</th>
                        <th class="text-end"><i class="fas fa-cog"></i> Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($designations as $designation)
                    <tr class="designation-row" data-level="{{ $designation->level }}">
                        <td>
                            <div class="form-check">
                                <input class="form-check-input select-item" type="checkbox" value="{{ $designation->id }}">
                            </div>
                        </td>
                        <td>
                            <div class="designation-info">
                                <div class="mini-icon">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div>
                                    <strong class="designation-code">{{ $designation->unique_code ?? 'N/A' }}</strong>
                                    <span class="muted">ID: {{ str_pad($designation->id, 3, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="designation-info">
                                <div class="mini-icon">
                                    <i class="fas fa-badge"></i>
                                </div>
                                <div>
                                    <strong class="designation-name">{{ $designation->name ?? '-' }}</strong>
                                    @if($designation->parent)
                                    <span class="muted"><i class="fas fa-link"></i> {{ $designation->parent->name }}</span>
                                    @else
                                    <span class="muted"><i class="fas fa-crown"></i> Top-level designation</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($designation->level !== null)
                                <span class="level l{{ $designation->level }}">
                                    <i class="fas fa-chevron-up"></i> L{{ $designation->level }}
                                </span>
                            @else
                                <span class="level level-default"><i class="fas fa-question"></i> Not Set</span>
                            @endif
                        </td>
                        <td>
                            <div class="user-info">
                                <div class="avatar-circle">
                                    <span>{{ substr($designation->addedBy?->name ?? 'S', 0, 1) }}</span>
                                </div>
                                <div>
                                    <div class="user-name">{{ $designation->addedBy?->name ?? 'System' }}</div>
                                    <small class="text-muted">{{ $designation->created_at->format('d M Y') }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div class="update-date">{{ $designation->updated_at->format('d M Y') }}</div>
                                <small class="text-muted">{{ $designation->updated_at->format('h:i A') }}</small>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="action-btn" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('designations.show', $designation->id) }}">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('designations.edit', $designation->id) }}">
                                            <i class="fas fa-pen"></i> Edit Designation
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('designations.archive.action', $designation->id) }}" method="POST" class="d-inline delete-form" onsubmit="return confirm('Archive this designation? It will move to Archived Designations and can be restored later.')">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-warning">
                                                <i class="fas fa-box-archive"></i> Archive Designation
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-briefcase fa-4x"></i>
                                <h5>No Designations Found</h5>
                                <p>Get started by creating your first designation</p>
                                <a href="{{ route('designations.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i> Create Designation
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Show Entries -->
        @if($designations->count() > 0)
        <div class="footer">
            <div class="footer-left">
                <div class="show-entries">
                    <span>Show</span>
                    <select id="showEntries">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                        <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                        <option value="40" {{ request('per_page') == 40 ? 'selected' : '' }}>40</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span>entries</span>
                </div>
            </div>

            <div class="pagination-info">
                <i class="fas fa-chart-simple"></i>
                Showing {{ $designations->firstItem() ?? 0 }} to {{ $designations->lastItem() ?? 0 }} of {{ $designations->total() }} designations
            </div>

            <div>
                <nav aria-label="Designation pagination">
                    <ul class="pagination">
                        @if ($designations->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-btn" href="{{ $designations->previousPageUrl() . (request('per_page') ? '&per_page=' . request('per_page') : '') }}">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        @endif

                        @php
                            $current = $designations->currentPage();
                            $last = $designations->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp

                        @if($start > 1)
                            <li class="page-item"><a class="page-btn" href="{{ $designations->url(1) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}">1</a></li>
                            @if($start > 2)
                                <li class="page-item disabled"><span class="page-dots">...</span></li>
                            @endif
                        @endif

                        @for ($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                <a class="page-btn {{ $i == $current ? 'active' : '' }}" href="{{ $designations->url($i) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}">
                                    {{ $i }}
                                </a>
                            </li>
                        @endfor

                        @if($end < $last)
                            @if($end < $last - 1)
                                <li class="page-item disabled"><span class="page-dots">...</span></li>
                            @endif
                            <li class="page-item"><a class="page-btn" href="{{ $designations->url($last) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}">{{ $last }}</a></li>
                        @endif

                        @if ($designations->hasMorePages())
                            <li class="page-item">
                                <a class="page-btn" href="{{ $designations->nextPageUrl() . (request('per_page') ? '&per_page=' . request('per_page') : '') }}">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
        @endif
    </div>

    <!-- Level Legend -->
    <div class="legend-card">
        <div class="legend-content">
            <span class="legend-title"><i class="fas fa-palette"></i> Level Legend:</span>
            <span class="level l0"><i class="fas fa-seedling"></i> L0 (Intern)</span>
            <span class="level l1"><i class="fas fa-user"></i> L1 (Associate)</span>
            <span class="level l2"><i class="fas fa-user-graduate"></i> L2 (Sr. Associate)</span>
            <span class="level l3"><i class="fas fa-chalkboard-user"></i> L3 (Manager)</span>
            <span class="level l4"><i class="fas fa-trophy"></i> L4 (Sr. Manager)</span>
            <span class="level l5"><i class="fas fa-medal"></i> L5 (Associate Director)</span>
            <span class="level l6"><i class="fas fa-star-of-life"></i> L6 (Director)</span>
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

    .designation-page {
        padding: 30px 35px;
        min-height: 100vh;
        background: linear-gradient(135deg, #f0f9f4 0%, #e6f3ec 50%, #f4fbf7 100%);
        color: #0a2e1f;
        position: relative;
    }

    .designation-page::before {
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

    .btn-light {
        background: #f0f9f4;
        color: #0f744c;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .btn-light:hover {
        background: #e6f3ec;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -8px rgba(16, 185, 129, 0.25);
        border-color: #34d399;
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
        align-items: center;
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

    /* Level Filters Bar */
    .level-filters-bar {
        padding: 14px 28px;
        background: #fafefb;
        border-bottom: 1px solid rgba(16, 185, 129, 0.08);
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .filter-label {
        font-weight: 700;
        color: #0a2e1f;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-label i {
        margin-right: 6px;
        color: #059669;
    }

    .level-filter-btn {
        background: #ffffff;
        border: 1px solid rgba(16, 185, 129, 0.2);
        color: #5a6e63;
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .level-filter-btn:hover {
        background: #d1fae5;
        border-color: #34d399;
        color: #059669;
    }

    .level-filter-btn.active {
        background: linear-gradient(145deg, #34d399, #059669);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
    }

    .reset-btn {
        background: #f0f9f4;
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

    .designation-list-table {
        width: 100%;
        min-width: 1100px;
        border-collapse: separate;
        border-spacing: 0 12px;
    }

    .designation-list-table thead th {
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

    .designation-list-table thead th i {
        margin-right: 8px;
        font-size: 0.75rem;
        color: #34d399;
    }

    .designation-list-table tbody td {
        background: #ffffff;
        padding: 18px 20px;
        border-top: 1px solid rgba(16, 185, 129, 0.08);
        border-bottom: 1px solid rgba(16, 185, 129, 0.08);
        font-weight: 500;
        vertical-align: middle;
        transition: all 0.2s ease;
    }

    .designation-list-table tbody td:first-child {
        border-left: 1px solid rgba(16, 185, 129, 0.08);
        border-radius: 20px 0 0 20px;
    }

    .designation-list-table tbody td:last-child {
        border-right: 1px solid rgba(16, 185, 129, 0.08);
        border-radius: 0 20px 20px 0;
    }

    .designation-list-table tbody tr:hover td {
        background: #fafefb;
        border-color: rgba(16, 185, 129, 0.15);
    }

    /* Designation Info */
    .designation-info {
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

    .designation-row:hover .mini-icon {
        transform: scale(1.05);
    }

    .designation-code, .designation-name {
        font-weight: 700;
        color: #0a2e1f;
    }

    .designation-code {
        color: #059669;
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

    /* Level Badges */
    .level {
        padding: 6px 16px;
        border-radius: 40px;
        color: white;
        font-size: 0.75rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .level i {
        font-size: 0.7rem;
    }

    .l0 { background: linear-gradient(145deg, #1f2937, #111827); }
    .l1 { background: linear-gradient(145deg, #0f744c, #0a5a3a); }
    .l2 { background: linear-gradient(145deg, #10b981, #059669); }
    .l3 { background: linear-gradient(145deg, #3b82f6, #2563eb); }
    .l4 { background: linear-gradient(145deg, #f59e0b, #d97706); }
    .l5 { background: linear-gradient(145deg, #f97316, #ea580c); }
    .l6 { background: linear-gradient(145deg, #ef4444, #dc2626); }
    .level-default { background: linear-gradient(145deg, #e5e7eb, #d1d5db); color: #6b7280; }

    /* User Info */
    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #059669;
        font-size: 1rem;
    }

    .user-name {
        font-weight: 600;
        color: #0a2e1f;
    }

    .update-date {
        font-weight: 600;
        color: #0a2e1f;
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
    }

    .dropdown-item i {
        width: 20px;
        font-size: 1rem;
    }

    .dropdown-item:hover {
        background: #ecfdf5;
        color: #059669;
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

    .legend-content .level {
        padding: 4px 14px;
        font-size: 0.7rem;
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

    /* Responsive */
    @media (max-width: 1200px) {
        .stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 992px) {
        .designation-page {
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
    }

    @media (max-width: 576px) {
        .designation-page {
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
    }

    /* Print Styles */
    @media print {
        .btn-group, .search-box, .level-filters-bar, .bulk-actions-bar, .legend-card, .action-btn, .dropdown {
            display: none;
        }

        .designation-page {
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
    /* Larger, accessibility-focused typography for the designation index. */
    .designation-page {
        font-size: 16px;
        line-height: 1.55;
    }

    .designation-page .breadcrumb {
        font-size: 1rem;
    }

    .designation-page .header-card h1 {
        font-size: 36px;
        line-height: 1.2;
    }

    .designation-page .header-card p {
        font-size: 17px;
        line-height: 1.55;
    }

    .designation-page .btn {
        padding: 13px 24px;
        font-size: 1rem;
    }

    .designation-page .stat-card h3 {
        font-size: 36px;
    }

    .designation-page .stat-card span {
        font-size: 14px;
        line-height: 1.4;
    }

    .designation-page .stat-sub {
        font-size: 13px;
        line-height: 1.45;
    }

    .designation-page .alert {
        font-size: 1rem;
    }

    .designation-page .table-title h3 {
        font-size: 1.45rem;
    }

    .designation-page .table-title .muted {
        font-size: .9rem;
    }

    .designation-page .search-box input {
        width: 320px;
        min-height: 48px;
        font-size: 1rem;
    }

    .designation-page .filter-label {
        font-size: .95rem;
    }

    .designation-page .level-filter-btn {
        padding: 8px 18px;
        font-size: .95rem;
    }

    .designation-page .selected-badge,
    .designation-page .btn-clear,
    .designation-page .btn-bulk-delete,
    .designation-page .form-check-label {
        font-size: .95rem;
    }

    .designation-page .designation-list-table {
        min-width: 1280px;
    }

    .designation-page .designation-list-table thead th {
        padding: 17px 20px;
        font-size: .9rem;
        line-height: 1.4;
    }

    .designation-page .designation-list-table thead th i {
        font-size: .9rem;
    }

    .designation-page .designation-list-table tbody td {
        padding: 20px;
        font-size: 1rem;
        line-height: 1.45;
    }

    .designation-page .designation-code,
    .designation-page .designation-name,
    .designation-page .user-name,
    .designation-page .update-date {
        font-size: 1.05rem;
    }

    .designation-page .muted,
    .designation-page .designation-list-table small,
    .designation-page .text-muted {
        font-size: .88rem !important;
        line-height: 1.45;
    }

    .designation-page .muted i {
        font-size: .8rem;
    }

    .designation-page .level {
        padding: 8px 17px;
        font-size: .9rem;
    }

    .designation-page .level i {
        font-size: .8rem;
    }

    .designation-page .dropdown-item {
        padding: 12px 18px;
        font-size: 1rem;
    }

    .designation-page .footer,
    .designation-page .show-entries,
    .designation-page .pagination-info,
    .designation-page .show-entries select {
        font-size: 1rem;
    }

    .designation-page .page-btn {
        min-width: 42px;
        height: 42px;
        font-size: 1rem;
    }

    .designation-page .legend-title {
        font-size: 1rem;
    }

    .designation-page .legend-content .level {
        padding: 7px 15px;
        font-size: .85rem;
    }

    .designation-page .empty-state h5 {
        font-size: 1.35rem;
    }

    .designation-page .empty-state p {
        font-size: 1rem;
    }

    @media (max-width: 768px) {
        .designation-page {
            font-size: 15px;
        }

        .designation-page .header-card h1 {
            font-size: 29px;
        }

        .designation-page .header-card p {
            font-size: 16px;
        }

        .designation-page .search-box input {
            width: 100%;
        }
    }
</style>

<style>
    /* DataTables export buttons styled to match the designation UI. */
    .designation-page .dataTables_wrapper .dt-buttons {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        width: fit-content;
        margin: 14px 20px 6px;
        padding: 8px;
        border: 1px solid rgba(16, 185, 129, .14);
        border-radius: 16px;
        background: #f7fcf9;
    }

    .designation-page .dataTables_wrapper .dt-buttons .dt-button {
        min-height: 42px;
        margin: 0 !important;
        padding: 9px 16px !important;
        border: 1px solid rgba(16, 185, 129, .18) !important;
        border-radius: 12px !important;
        background: #fff !important;
        color: #0f744c !important;
        box-shadow: 0 4px 12px -10px rgba(5, 150, 105, .7);
        font-size: .95rem !important;
        font-weight: 750 !important;
        line-height: 1.2 !important;
        transition: border-color .2s ease, background .2s ease, color .2s ease, transform .2s ease, box-shadow .2s ease;
    }

    .designation-page .dataTables_wrapper .dt-buttons .dt-button span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .designation-page .dataTables_wrapper .dt-buttons .dt-button span::before {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        font-family: "Font Awesome 5 Free";
        font-size: 1rem;
        font-weight: 900;
    }

    .designation-page .dataTables_wrapper .buttons-copy span::before {
        content: "\f0c5";
    }

    .designation-page .dataTables_wrapper .buttons-csv span::before {
        content: "\f6dd";
    }

    .designation-page .dataTables_wrapper .buttons-excel span::before {
        content: "\f1c3";
    }

    .designation-page .dataTables_wrapper .buttons-pdf span::before {
        content: "\f1c1";
    }

    .designation-page .dataTables_wrapper .buttons-print span::before {
        content: "\f02f";
    }

    .designation-page .dataTables_wrapper .dt-buttons .dt-button:hover,
    .designation-page .dataTables_wrapper .dt-buttons .dt-button:focus {
        border-color: #10b981 !important;
        background: linear-gradient(145deg, #34d399, #059669) !important;
        color: #fff !important;
        box-shadow: 0 9px 20px -12px rgba(5, 150, 105, .8);
        transform: translateY(-1px);
    }

    .designation-page .dataTables_wrapper .buttons-excel {
        background: #ecfdf5 !important;
    }

    .designation-page .dataTables_wrapper .buttons-csv {
        background: #f0fdf4 !important;
    }

    .designation-page .dataTables_wrapper .buttons-pdf {
        color: #b91c1c !important;
        background: #fff7f7 !important;
        border-color: rgba(239, 68, 68, .18) !important;
    }

    .designation-page .dataTables_wrapper .buttons-print {
        color: #315f75 !important;
        background: #f4fbff !important;
        border-color: rgba(49, 95, 117, .18) !important;
    }

    html[data-pms-theme="dark"] .designation-page .dataTables_wrapper .dt-buttons {
        border-color: rgba(122, 240, 181, .16);
        background: #142a20;
    }

    html[data-pms-theme="dark"] .designation-page .dataTables_wrapper .dt-buttons .dt-button {
        border-color: rgba(122, 240, 181, .18) !important;
        background: #183026 !important;
        color: #d9f1e4 !important;
    }

    @media (max-width: 768px) {
        .designation-page .dataTables_wrapper .dt-buttons {
            width: calc(100% - 32px);
            margin-inline: 16px;
        }

        .designation-page .dataTables_wrapper .dt-buttons .dt-button {
            flex: 1 1 120px;
            justify-content: center;
        }
    }
</style>

<style>
    /* Keep the upper export menu fully visible above the page cards. */
    .designation-page .header-card {
        position: relative;
        z-index: 50;
        overflow: visible !important;
    }

    .designation-page .header-card .btn-group,
    .designation-page .header-card .dropdown {
        position: relative;
        z-index: 60;
        overflow: visible;
    }

    .designation-page .header-card .dropdown-menu {
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

    .designation-page .header-card .dropdown-menu.show {
        display: block;
        visibility: visible;
        opacity: 1;
    }

    .designation-page .header-card .dropdown-item {
        width: 100%;
        min-height: 44px;
        white-space: nowrap;
    }

    .designation-page .stats,
    .designation-page .table-card,
    .designation-page .legend-card {
        position: relative;
        z-index: 1;
    }

    html[data-pms-theme="dark"] .designation-page .header-card .dropdown-menu {
        border-color: rgba(122, 240, 181, .2);
        background: #183026;
        box-shadow: 0 24px 48px -14px rgba(0, 0, 0, .55);
    }

    @media (max-width: 576px) {
        .designation-page .header-card .dropdown-menu {
            right: auto !important;
            left: 0 !important;
            max-width: calc(100vw - 48px);
        }
    }
</style>

@push('js')
<script>
$(document).ready(function() {
    // DataTable functionality for search and filtering
    var table = $('#designationTable').DataTable({
        dom: 'lBfrtip',
        paging: false,
        searching: true,
        info: false,
        ordering: true,
        buttons: [
            {
                extend: 'copyHtml5',
                text: 'Copy',
                title: 'Designation List',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5],
                    rows: function(idx, data, node) { return $(node).is(':visible'); },
                    modifier: { search: 'applied' }
                }
            },
            {
                extend: 'csvHtml5',
                text: 'CSV',
                title: 'Designation List',
                filename: 'designation-list',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5],
                    rows: function(idx, data, node) { return $(node).is(':visible'); },
                    modifier: { search: 'applied' }
                }
            },
            {
                extend: 'excelHtml5',
                text: 'Excel',
                title: 'Designation List',
                filename: 'designation-list',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5],
                    rows: function(idx, data, node) { return $(node).is(':visible'); },
                    modifier: { search: 'applied' }
                }
            },
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                title: 'Designation List',
                filename: 'designation-list',
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
                title: 'Designation List',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5],
                    rows: function(idx, data, node) { return $(node).is(':visible'); },
                    modifier: { search: 'applied' }
                }
            }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search designations...",
            zeroRecords: "No matching records found",
        },
        initComplete: function() {
            $('.dataTables_filter').hide();
        }
    });

    // Custom search
    $('#designationSearch').on('keyup', function() {
        table.search(this.value).draw();
        updateVisibleRowsForLevelFilter();
    });

    // Level filter functionality
    function updateVisibleRowsForLevelFilter() {
        var activeLevelBtn = $('.level-filter-btn.active');
        if (activeLevelBtn.length && activeLevelBtn.data('level') !== undefined && !activeLevelBtn.hasClass('reset-btn')) {
            var level = activeLevelBtn.data('level');
            var searchTerm = $('#designationSearch').val().toLowerCase();

            $('.designation-row').each(function() {
                var rowLevel = $(this).data('level');
                var rowText = $(this).text().toLowerCase();
                var levelMatch = (rowLevel == level);
                var searchMatch = !searchTerm || rowText.indexOf(searchTerm) > -1;

                if (levelMatch && searchMatch) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    }

    $('.level-filter-btn').on('click', function() {
        if ($(this).hasClass('reset-btn')) {
            resetFilters();
            return;
        }

        $('.level-filter-btn').removeClass('active');
        $(this).addClass('active');
        var level = $(this).data('level');
        var searchTerm = $('#designationSearch').val().toLowerCase();

        $('.designation-row').each(function() {
            var rowLevel = $(this).data('level');
            var rowText = $(this).text().toLowerCase();
            var levelMatch = (rowLevel == level);
            var searchMatch = !searchTerm || rowText.indexOf(searchTerm) > -1;

            if (levelMatch && searchMatch) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Reset filters
    window.resetFilters = function() {
        $('.level-filter-btn').removeClass('active');
        $('#designationSearch').val('');
        $('.designation-row').show();
        if (table) {
            table.search('').draw();
        }
    };

    // Show Entries dropdown
    $('#showEntries').on('change', function() {
        var perPage = $(this).val();
        var currentUrl = window.location.href;
        var url = new URL(currentUrl);
        url.searchParams.set('per_page', perPage);
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
    });

    $('#clear-selection').on('click', function() {
        $('.select-item').prop('checked', false);
        $('#table-select-all').prop('checked', false);
        $('#select-all').prop('checked', false);
        updateUI();
    });

    $('#table-select-all').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.select-item').prop('checked', isChecked);
        $('#select-all').prop('checked', isChecked);
        updateUI();
    });

    $('#select-all').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.select-item').prop('checked', isChecked);
        $('#table-select-all').prop('checked', isChecked);
        updateUI();
    });

    // Bulk archive
    $('#bulk-delete-btn').on('click', function() {
        var ids = getSelectedIds();
        if (ids.length === 0) {
            if (typeof toastr !== 'undefined') {
                toastr.warning('Please select at least one designation.');
            } else {
                alert('Please select at least one designation.');
            }
            return;
        }

        if (confirm(`Archive ${ids.length} designation(s)?\n\nThey will move to Archived Designations and can be restored later.`)) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("designations.bulk-archive") }}';
            form.style.display = 'none';

            var csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            ids.forEach(function(id) {
                var idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'ids[]';
                idInput.value = id;
                form.appendChild(idInput);
            });

            document.body.appendChild(form);
            form.submit();
        }
    });

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

        if (!selector || !table.button(selector).node()) {
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
    $('.designation-row').on('click', function(e) {
        if (!$(e.target).closest('input, a, button, .dropdown, .form-check, .action-btn, .dropdown-menu, .btn, .level').length) {
            var designationId = $(this).find('.select-item').val();
            if (designationId) {
                window.location.href = "{{ route('designations.show', '') }}/" + designationId;
            }
        }
    });

    // Toastr options
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "4000",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
    }
});

@if(session('success'))
    $(document).ready(function() {
        if (typeof toastr !== 'undefined') {
            toastr.success('{{ session('success') }}');
        }
    });
@endif

@if(session('error'))
    $(document).ready(function() {
        if (typeof toastr !== 'undefined') {
            toastr.error('{{ session('error') }}');
        }
    });
@endif
</script>
@endpush

@endsection
