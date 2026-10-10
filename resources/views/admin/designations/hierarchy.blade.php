@extends('admin.layout.app')

@section('title', 'Designation Hierarchy')

@section('content')
<main id="designation-hierarchy-page" class="designation-hierarchy-page" data-live-preserve>
    <div class="container-fluid px-4">

        <!-- Page Header -->
        <div class="header-card">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div>
                    <h1>Organization Hierarchy</h1>
                    <p>Visualize and manage your organizational structure</p>
                </div>
            </div>
            <div class="btn-group">
                <a href="{{ route('designations.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i> Options
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" id="exportChartBtn"><i class="fas fa-download"></i> Export Chart</a></li>
                        <li><a class="dropdown-item" href="#" id="printChartBtn"><i class="fas fa-print"></i> Print Chart</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('designations.index') }}"><i class="fas fa-table"></i> Switch to Table View</a></li>
                    </ul>
                </div>
                <a href="{{ route('designations.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add Designation
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
                <div>
                    <h3>{{ $designations->count() }}</h3>
                    <span>Total Designations</span>
                    <p class="stat-sub">Organizational roles</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-crown"></i></div>
                <div>
                    <h3>{{ $designations->whereNull('parent_id')->count() }}</h3>
                    <span>Top Level</span>
                    <p class="stat-sub">Executive leadership</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                <div>
                    <h3>{{ $designations->pluck('level')->unique()->count() }}</h3>
                    <span>Hierarchy Levels</span>
                    <p class="stat-sub">Max depth: {{ $maxDepth ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div>
                    <h3>{{ $designations->where('updated_at', '>=', now()->subDays(7))->count() }}</h3>
                    <span>Recently Updated</span>
                    <p class="stat-sub">Last 7 days</p>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row g-4">
            <!-- Left Panel: Drag & Drop Hierarchy -->
            <div class="col-xl-6">
                <div class="table-card">
                    <div class="table-header">
                        <div class="table-title">
                            <div class="table-title-icon">
                                <i class="fas fa-arrows-alt"></i>
                            </div>
                            <div>
                                <h3>Hierarchy Management</h3>
                                <span class="muted">Drag and drop to reorganize your structure</span>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn-filter dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-filter"></i> Filter by Level
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" data-level="all">Show All Levels</a></li>
                                <li><hr class="dropdown-divider"></li>
                                @for($i = 0; $i <= \App\Services\DesignationLevels::maximum(); $i++)
                                    <li><a class="dropdown-item" href="#" data-level="{{ $i }}">Level {{ $i }} Only</a></li>
                                @endfor
                            </ul>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- Instructions Alert -->
                        <div class="alert-info">
                            <div class="alert-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div>
                                <strong>How to reorganize:</strong>
                                <p>Drag items using the handle <i class="fas fa-grip-vertical"></i> to reorder. Drop onto other items to create parent-child relationships.</p>
                            </div>
                        </div>

                        <!-- Hierarchy Container -->
                        <div class="hierarchy-container">
                            <div class="level-legend">
                                <span><i class="fas fa-chart-line"></i> Level Legend:</span>
                                @for($i = 0; $i <= \App\Services\DesignationLevels::maximum(); $i++)
                                    <span class="legend-badge l{{ $i }}">L{{ $i }}</span>
                                @endfor
                                <button type="button" class="expand-all-btn" id="expandAll">
                                    <i class="fas fa-expand-alt"></i> Expand All
                                </button>
                            </div>

                            <div class="hierarchy-tree-wrapper">
                                @if($designations->whereNull('parent_id')->isEmpty())
                                    <div class="empty-state">
                                        <i class="fas fa-diagram-project fa-4x"></i>
                                        <h5>No Designations Found</h5>
                                        <p>Start building your organizational structure</p>
                                        <a href="{{ route('designations.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus-circle"></i> Create First Designation
                                        </a>
                                    </div>
                                @else
                                    <ul id="hierarchyList" class="hierarchy-list">
                                        @foreach($designations->whereNull('parent_id') as $designation)
                                            @include('admin.designations.partials.designation-item', ['designation' => $designation])
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-footer">
                            <div class="info-text">
                                <i class="fas fa-lightbulb"></i> Changes are saved when you click the button
                            </div>
                            <div class="action-buttons">
                                <button type="button" class="btn btn-outline" id="resetHierarchy">
                                    <i class="fas fa-undo-alt"></i> Reset Changes
                                </button>
                                <button id="saveHierarchy" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Hierarchy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Organizational Chart -->
            <div class="col-xl-6">
                <div class="table-card">
                    <div class="table-header">
                        <div class="table-title">
                            <div class="table-title-icon">
                                <i class="fas fa-sitemap"></i>
                            </div>
                            <div>
                                <h3>Employee Hierarchy</h3>
                                <span class="muted">Live reporting structure of current employees</span>
                            </div>
                        </div>
                        <div class="chart-actions">
                            <div class="status-badge">
                                <i class="fas fa-circle"></i> Live
                            </div>
                            <button type="button" class="chart-control-btn" id="fullscreenChart" title="Fullscreen">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0 position-relative">
                        <!-- Chart Controls -->
                        <div class="chart-controls">
                            <button type="button" id="zoomIn" title="Zoom In">
                                <i class="fas fa-search-plus"></i>
                            </button>
                            <button type="button" id="zoomOut" title="Zoom Out">
                                <i class="fas fa-search-minus"></i>
                            </button>
                            <button type="button" id="resetZoom" title="Reset Zoom">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>

                        <!-- Chart Legend -->
                        <div class="chart-legend">
                            <div class="legend-header">Level Legend</div>
                            <div class="legend-items">
                                <div><span class="legend-color l0"></span> L0: Executive</div>
                                <div><span class="legend-color l1"></span> L1-2: Management</div>
                                <div><span class="legend-color l3"></span> L3-4: Senior</div>
                                <div><span class="legend-color l5"></span> L5-6: Entry Level</div>
                            </div>
                        </div>

                        <!-- Chart Container -->
                        <div id="employeeOrgTree" class="chart-container pms-org-viewport" aria-label="Employee reporting hierarchy"></div>

                        <!-- Loading Overlay -->
                        <div id="chartLoading" class="chart-loading d-none">
                            <div class="spinner"></div>
                            <span>Loading chart...</span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="footer-info">
                            <i class="fas fa-info-circle"></i> Chart updates automatically when changes are saved
                        </div>
                        <div class="footer-status">
                            <i class="fas fa-check-circle"></i> Last saved: <span id="lastSaved">{{ now()->format('M d, Y h:i A') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hierarchy Status Bar -->
        <div class="status-bar">
            <div class="status-item">
                <i class="fas fa-check-circle text-success"></i> Hierarchy Status: <strong>Active</strong>
            </div>
            <div class="status-item">
                <i class="fas fa-briefcase"></i> <span id="totalPositions">{{ $designations->count() }}</span> Positions
            </div>
            <div class="status-item">
                <i class="fas fa-layer-group"></i> <span id="totalLevels">{{ $designations->pluck('level')->unique()->count() }}</span> Levels
            </div>
        </div>
    </div>
</main>

<style>
    /* ===== PREMIUM DESIGNATION HIERARCHY PAGE ===== */
    .designation-hierarchy-page {
        padding: 30px 35px;
        min-height: 100vh;
        background: linear-gradient(145deg, #f7fbf9, #eef7f2);
        color: #070B1A;
    }

    /* Header Card */
    .header-card {
        background: #fff;
        border-radius: 24px;
        padding: 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        box-shadow: 0 18px 45px rgba(47, 107, 255, .09);
        border: 1px solid rgba(47, 107, 255, .12);
        margin-bottom: 28px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .header-icon {
        width: 65px;
        height: 65px;
        background: linear-gradient(145deg, #60A5FA, #10b981);
        color: white;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

    .header-card h1 {
        font-size: 30px;
        margin-bottom: 6px;
    }

    .header-card p {
        color: #52645a;
        font-size: 15px;
    }

    /* Stats Cards */
    .stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 28px;
    }

    html:not([data-pms-theme="dark"]) .designation-hierarchy-page .stat-card,
    html:not([data-pms-theme="dark"]) .designation-hierarchy-page .stat-card:first-of-type,
    html:not([data-pms-theme="dark"]) .designation-hierarchy-page .stat-card.is-featured {
        background: #ffffff !important;
        padding: 22px;
        border-radius: 22px;
        border: 1px solid rgba(47, 107, 255, .14) !important;
        box-shadow: 0 14px 35px rgba(47, 107, 255, .06) !important;
        color: #0F172A !important;
        display: flex;
        gap: 16px;
        align-items: center;
        transition: all 0.3s ease;
    }

    .designation-hierarchy-page .stat-card:first-of-type *,
    .designation-hierarchy-page .stat-card * {
        -webkit-text-fill-color: initial;
    }

    .designation-hierarchy-page .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 40px rgba(47, 107, 255, .12) !important;
        border-color: rgba(47, 107, 255, 0.25) !important;
    }

    .designation-hierarchy-page .stat-icon,
    .designation-hierarchy-page .stat-card:first-of-type .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        background: linear-gradient(145deg, #EEF2FF, #E0E7FF) !important;
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        flex-shrink: 0;
    }

    .designation-hierarchy-page .stat-icon i,
    .designation-hierarchy-page .stat-icon .fa,
    .designation-hierarchy-page .stat-icon [class*="fa-"],
    .designation-hierarchy-page .stat-card:first-of-type .stat-icon i,
    .designation-hierarchy-page .stat-card:first-of-type .stat-icon .fa,
    .designation-hierarchy-page .stat-card:first-of-type .stat-icon [class*="fa-"] {
        color: #2F6BFF !important;
        -webkit-text-fill-color: #2F6BFF !important;
        background: transparent !important;
        background-color: transparent !important;
        display: inline-block !important;
    }

    .designation-hierarchy-page .stat-card:nth-child(2) .stat-icon {
        background: linear-gradient(145deg, #fef3c7, #fde68a) !important;
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
    }

    .designation-hierarchy-page .stat-card:nth-child(2) .stat-icon i,
    .designation-hierarchy-page .stat-card:nth-child(2) .stat-icon .fa,
    .designation-hierarchy-page .stat-card:nth-child(2) .stat-icon [class*="fa-"] {
        color: #d97706 !important;
        -webkit-text-fill-color: #d97706 !important;
        background: transparent !important;
        background-color: transparent !important;
        display: inline-block !important;
    }

    .designation-hierarchy-page .stat-card:nth-child(3) .stat-icon {
        background: linear-gradient(145deg, #e0f2fe, #bae6fd) !important;
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
    }

    .designation-hierarchy-page .stat-card:nth-child(3) .stat-icon i,
    .designation-hierarchy-page .stat-card:nth-child(3) .stat-icon .fa,
    .designation-hierarchy-page .stat-card:nth-child(3) .stat-icon [class*="fa-"] {
        color: #0284c7 !important;
        -webkit-text-fill-color: #0284c7 !important;
        background: transparent !important;
        background-color: transparent !important;
        display: inline-block !important;
    }

    .designation-hierarchy-page .stat-card:nth-child(4) .stat-icon {
        background: linear-gradient(145deg, #e0e7ff, #c7d2fe) !important;
        color: #4f46e5 !important;
        -webkit-text-fill-color: #4f46e5 !important;
    }

    .designation-hierarchy-page .stat-card:nth-child(4) .stat-icon i,
    .designation-hierarchy-page .stat-card:nth-child(4) .stat-icon .fa,
    .designation-hierarchy-page .stat-card:nth-child(4) .stat-icon [class*="fa-"] {
        color: #4f46e5 !important;
        -webkit-text-fill-color: #4f46e5 !important;
        background: transparent !important;
        background-color: transparent !important;
        display: inline-block !important;
    }

    html:not([data-pms-theme="dark"]) .designation-hierarchy-page .stat-card h3,
    html:not([data-pms-theme="dark"]) .designation-hierarchy-page .stat-card:first-of-type h3,
    html:not([data-pms-theme="dark"]) .designation-hierarchy-page .stat-card:first-of-type .stat-value {
        font-size: 28px;
        font-weight: 800;
        color: #0F172A !important;
        -webkit-text-fill-color: #0F172A !important;
        margin-bottom: 4px;
    }

    html:not([data-pms-theme="dark"]) .designation-hierarchy-page .stat-card span,
    html:not([data-pms-theme="dark"]) .designation-hierarchy-page .stat-card:first-of-type span,
    html:not([data-pms-theme="dark"]) .designation-hierarchy-page .stat-card:first-of-type .stat-title {
        color: #4b5563 !important;
        -webkit-text-fill-color: #4b5563 !important;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
    }

    html:not([data-pms-theme="dark"]) .designation-hierarchy-page .stat-sub,
    html:not([data-pms-theme="dark"]) .designation-hierarchy-page .stat-card:first-of-type .stat-sub {
        font-size: 11px;
        color: #6b7280 !important;
        -webkit-text-fill-color: #6b7280 !important;
        margin-top: 4px;
    }

    /* Buttons */
    .btn-group {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn {
        border: none;
        padding: 12px 20px;
        border-radius: 14px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: .25s;
        text-decoration: none;
    }

    .btn-light {
        background: #edf8f2;
        color: #2F6BFF;
        border: 1px solid rgba(47, 107, 255, .14);
    }

    .btn-primary {
        background: linear-gradient(145deg, #60A5FA, #10b981);
        color: white;
        box-shadow: 0 10px 25px rgba(47, 107, 255, .25);
    }

    .btn-outline {
        background: transparent;
        border: 1px solid rgba(47, 107, 255, .2);
        color: #2F6BFF;
    }

    .btn-outline:hover {
        background: #edf8f2;
        border-color: #60A5FA;
    }

    .btn:hover {
        transform: translateY(-2px);
    }

    .btn-filter {
        background: #F8FAFC;
        border: 1px solid rgba(47, 107, 255, .15);
        padding: 10px 18px;
        border-radius: 12px;
        font-weight: 600;
        color: #2F6BFF;
    }

    /* Table Card */
    .table-card {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(47, 107, 255, .12);
        box-shadow: 0 18px 45px rgba(47, 107, 255, .08);
        overflow: hidden;
        height: 100%;
    }

    .table-header {
        padding: 22px;
        background: linear-gradient(135deg, #ffffff, #f5fbf7);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        border-bottom: 1px solid rgba(47, 107, 255, .1);
    }

    .table-title {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .table-title-icon {
        width: 44px;
        height: 44px;
        background: #e7f5ee;
        color: #2F6BFF;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .table-title h3 {
        font-size: 1.2rem;
        font-weight: 700;
        margin: 0;
    }

    .muted {
        font-size: 0.75rem;
        color: #94A3B8;
    }

    /* Alert Info */
    .alert-info {
        background: #EEF2FF;
        border-left: 4px solid #10b981;
        border-radius: 14px;
        padding: 16px;
        display: flex;
        gap: 14px;
        margin-bottom: 20px;
    }

    .alert-icon {
        width: 40px;
        height: 40px;
        background: #E0E7FF;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2F6BFF;
        font-size: 1.2rem;
    }

    .alert-info strong {
        display: block;
        margin-bottom: 4px;
        color: #065f46;
    }

    .alert-info p {
        margin: 0;
        font-size: 0.8rem;
        color: #5a6e63;
    }

    /* Hierarchy Container */
    .hierarchy-container {
        background: #fafefb;
        border-radius: 18px;
        border: 1px solid rgba(47, 107, 255, .1);
        overflow: hidden;
    }

    .level-legend {
        padding: 14px 20px;
        background: #ffffff;
        border-bottom: 1px solid rgba(47, 107, 255, .1);
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .level-legend span:first-child {
        font-weight: 700;
        color: #070B1A;
        font-size: 0.8rem;
    }

    .legend-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 11px;
        min-width: 32px;
        border-radius: 30px;
        font-size: 0.72rem;
        font-weight: 700;
        line-height: 1.2;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        letter-spacing: 0.02em;
        text-align: center;
    }

    .legend-badge.l0 {
        background: #0F172A !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.22);
    }
    .legend-badge.l1 { background: #2F6BFF !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
    .legend-badge.l2 { background: #10b981 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
    .legend-badge.l3 { background: #3b82f6 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
    .legend-badge.l4 { background: #f59e0b !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
    .legend-badge.l5 { background: #f97316 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
    .legend-badge.l6 { background: #ef4444 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }

    .expand-all-btn {
        margin-left: auto;
        background: #F8FAFC;
        border: none;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #2F6BFF;
        cursor: pointer;
        transition: all 0.2s;
    }

    .expand-all-btn:hover {
        background: #E0E7FF;
    }

    .hierarchy-alert-dark.swal2-popup {
        background: #141B3D !important;
        color: #EEF1FB !important;
        border: 1px solid rgba(79, 131, 255, 0.3);
    }
    .hierarchy-alert-dark :is(.swal2-title, .swal2-html-container) {
        color: #EEF1FB !important;
        -webkit-text-fill-color: #EEF1FB !important;
    }
    .hierarchy-alert-dark .swal2-success-circular-line-left,
    .hierarchy-alert-dark .swal2-success-circular-line-right,
    .hierarchy-alert-dark .swal2-success-fix {
        background: #141B3D !important;
    }

    /* Hierarchy Tree */
    .hierarchy-tree-wrapper {
        max-height: 500px;
        overflow-y: auto;
        padding: 20px;
    }

    .hierarchy-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .hierarchy-list > li {
        margin-bottom: 10px;
    }

    .hierarchy-list ul {
        margin-left: 35px;
        margin-top: 8px;
        padding-left: 20px;
        border-left: 2px solid #E0E7FF;
        position: relative;
    }

    .designation-item {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 8px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: grab;
    }

    .designation-item:hover {
        border-color: #60A5FA;
        box-shadow: 0 4px 12px rgba(47, 107, 255, 0.1);
    }

    .designation-item.dragging {
        opacity: 0.5;
        cursor: grabbing;
    }

    .drag-handle {
        cursor: grab;
        color: #adb5bd;
        padding: 6px;
        margin: -6px 8px -6px -6px;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .drag-handle:hover {
        color: #2F6BFF;
        background: #edf8f2;
    }

    .designation-node-icon,
    .designation-icon-small {
        width: 38px;
        height: 38px;
        background: #e7f5ee;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2F6BFF;
        margin-right: 12px;
        flex-shrink: 0;
    }

    .designation-node-copy,
    .designation-details {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .designation-node-copy strong,
    .designation-name {
        font-weight: 700;
        color: #070B1A;
        font-size: 0.85rem;
    }

    .designation-node-copy span,
    .designation-parent {
        font-size: 0.7rem;
        color: #94A3B8;
    }

    .node-level,
    .designation-level {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #2F6BFF;
        padding: 4px 10px;
        min-width: 32px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 700;
        line-height: 1.2;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .node-level.level-0,
    .designation-level.level-0 {
        background: #0F172A !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.22);
    }
    .node-level.level-1,
    .designation-level.level-1 { background: #2F6BFF !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
    .node-level.level-2,
    .designation-level.level-2 { background: #10b981 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
    .node-level.level-3,
    .designation-level.level-3 { background: #3b82f6 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
    .node-level.level-4,
    .designation-level.level-4 { background: #f59e0b !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
    .node-level.level-5,
    .designation-level.level-5 { background: #f97316 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
    .node-level.level-6,
    .designation-level.level-6 { background: #ef4444 !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }

    .toggle-children {
        background: none;
        border: none;
        cursor: pointer;
        color: #94A3B8;
        padding: 6px;
        border-radius: 6px;
    }

    .toggle-children:hover {
        background: #edf8f2;
        color: #2F6BFF;
    }

    /* Action Footer */
    .action-footer {
        margin-top: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        padding-top: 16px;
        border-top: 1px solid rgba(47, 107, 255, .1);
    }

    .info-text {
        font-size: 0.75rem;
        color: #94A3B8;
    }

    .info-text i {
        color: #f59e0b;
        margin-right: 6px;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
    }

    /* Chart Controls */
    .chart-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .status-badge {
        background: #E0E7FF;
        color: #2F6BFF;
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 0.7rem;
        font-weight: 700;
    }

    .status-badge i {
        font-size: 0.6rem;
        margin-right: 4px;
    }

    .chart-control-btn {
        background: #F8FAFC;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        color: #2F6BFF;
        cursor: pointer;
        transition: all 0.2s;
    }

    .chart-control-btn:hover {
        background: #E0E7FF;
    }

    .chart-controls {
        position: absolute;
        top: 15px;
        right: 15px;
        z-index: 100;
        display: flex;
        gap: 8px;
    }

    .chart-controls button {
        background: white;
        border: 1px solid rgba(47, 107, 255, .15);
        width: 36px;
        height: 36px;
        border-radius: 10px;
        color: #2F6BFF;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .chart-controls button:hover {
        background: #E0E7FF;
        border-color: #60A5FA;
    }

    /* Chart Legend */
    .chart-legend {
        position: absolute;
        bottom: 15px;
        left: 15px;
        z-index: 100;
        background: white;
        padding: 12px 16px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(47, 107, 255, .1);
    }

    .legend-header {
        font-size: 0.7rem;
        font-weight: 700;
        color: #070B1A;
        margin-bottom: 8px;
    }

    .legend-items {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .legend-items div {
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .legend-color {
        width: 14px;
        height: 14px;
        border-radius: 4px;
    }

    .legend-color.l0 { background: #0F172A; border: 1px solid rgba(255, 255, 255, 0.25); }
    .legend-color.l1 { background: #2F6BFF; }
    .legend-color.l2 { background: #10b981; }
    .legend-color.l3 { background: #3b82f6; }
    .legend-color.l4 { background: #f59e0b; }
    .legend-color.l5 { background: #f97316; }
    .legend-color.l6 { background: #ef4444; }

    /* Chart Container */
    .chart-container {
        width: 100%;
        height: 550px;
        background: linear-gradient(135deg, #fafefb, #f5fbf7);
        border-radius: 0 0 20px 20px;
        transform-origin: center center;
        transition: transform 0.2s ease;
    }

    .chart-loading {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        border-radius: 20px;
        z-index: 200;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid #e7f5ee;
        border-top-color: #2F6BFF;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Card Footer */
    .card-footer {
        padding: 16px 22px;
        background: #fafefb;
        border-top: 1px solid rgba(47, 107, 255, .08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .footer-info {
        font-size: 0.7rem;
        color: #94A3B8;
    }

    .footer-status {
        font-size: 0.7rem;
        color: #2F6BFF;
    }

    .footer-status i {
        margin-right: 4px;
    }

    /* Status Bar */
    .status-bar {
        margin-top: 24px;
        background: white;
        border-radius: 20px;
        padding: 16px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        border: 1px solid rgba(47, 107, 255, .1);
    }

    .status-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        color: #5a6e63;
    }

    .status-item i {
        font-size: 1rem;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        color: #C7D2FE;
        margin-bottom: 20px;
    }

    .empty-state h5 {
        color: #2F6BFF;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #94A3B8;
        margin-bottom: 20px;
    }

    /* Dropdown */
    .dropdown-menu {
        background: white;
        border: 1px solid rgba(47, 107, 255, .15);
        border-radius: 14px;
        padding: 8px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
    }

    .dropdown-item {
        padding: 8px 16px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.85rem;
        transition: all 0.2s;
    }

    .dropdown-item:hover {
        background: #EEF2FF;
        color: #2F6BFF;
    }

    /* Fullscreen */
    .fullscreen-mode {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 9999 !important;
        background: white;
        margin: 0 !important;
        border-radius: 0 !important;
    }

    /* Level Colors */
    .l0-bg { background: #0F172A; }
    .l1-bg { background: #2F6BFF; }
    .l2-bg { background: #10b981; }
    .l3-bg { background: #3b82f6; }
    .l4-bg { background: #f59e0b; }
    .l5-bg { background: #f97316; }
    .l6-bg { background: #ef4444; }

    /* Responsive */
    @media (max-width: 1200px) {
        .stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 992px) {
        .designation-hierarchy-page {
            padding: 20px 25px;
        }
        .header-card {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 768px) {
        .stats {
            grid-template-columns: 1fr;
        }
        .status-bar {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    /* ==========================================================================
       DESIGNATION HIERARCHY - COMPREHENSIVE DARK MODE SYSTEM
       ========================================================================== */
    html[data-pms-theme="dark"] .designation-hierarchy-page,
    html[data-bs-theme="dark"] .designation-hierarchy-page,
    html[data-theme="dark"] .designation-hierarchy-page,
    html.dark .designation-hierarchy-page,
    body[data-pms-theme="dark"] .designation-hierarchy-page,
    body[data-bs-theme="dark"] .designation-hierarchy-page,
    body.dark .designation-hierarchy-page,
    [data-pms-theme="dark"] .designation-hierarchy-page,
    [data-theme="dark"] .designation-hierarchy-page,
    [data-bs-theme="dark"] .designation-hierarchy-page,
    .dark .designation-hierarchy-page {
        background: #070B1A !important;
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .breadcrumb,
    html[data-bs-theme="dark"] .designation-hierarchy-page .breadcrumb,
    html[data-theme="dark"] .designation-hierarchy-page .breadcrumb,
    html.dark .designation-hierarchy-page .breadcrumb,
    body[data-pms-theme="dark"] .designation-hierarchy-page .breadcrumb,
    [data-pms-theme="dark"] .designation-hierarchy-page .breadcrumb,
    .dark .designation-hierarchy-page .breadcrumb {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.2) !important;
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .header-card,
    html[data-bs-theme="dark"] .designation-hierarchy-page .header-card,
    html[data-theme="dark"] .designation-hierarchy-page .header-card,
    html.dark .designation-hierarchy-page .header-card,
    body[data-pms-theme="dark"] .designation-hierarchy-page .header-card,
    [data-pms-theme="dark"] .designation-hierarchy-page .header-card,
    .dark .designation-hierarchy-page .header-card {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.2) !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .header-card h1,
    html[data-bs-theme="dark"] .designation-hierarchy-page .header-card h1,
    html[data-theme="dark"] .designation-hierarchy-page .header-card h1,
    html.dark .designation-hierarchy-page .header-card h1,
    body[data-pms-theme="dark"] .designation-hierarchy-page .header-card h1,
    [data-pms-theme="dark"] .designation-hierarchy-page .header-card h1,
    .dark .designation-hierarchy-page .header-card h1 {
        background: linear-gradient(135deg, #ffffff 0%, #60A5FA 100%) !important;
        background-clip: text !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .header-card p,
    html[data-bs-theme="dark"] .designation-hierarchy-page .header-card p,
    html[data-theme="dark"] .designation-hierarchy-page .header-card p,
    html.dark .designation-hierarchy-page .header-card p,
    body[data-pms-theme="dark"] .designation-hierarchy-page .header-card p,
    [data-pms-theme="dark"] .designation-hierarchy-page .header-card p,
    .dark .designation-hierarchy-page .header-card p {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .btn-light,
    html[data-bs-theme="dark"] .designation-hierarchy-page .btn-light,
    html[data-theme="dark"] .designation-hierarchy-page .btn-light,
    html.dark .designation-hierarchy-page .btn-light,
    body[data-pms-theme="dark"] .designation-hierarchy-page .btn-light,
    [data-pms-theme="dark"] .designation-hierarchy-page .btn-light,
    .dark .designation-hierarchy-page .btn-light {
        background: rgba(79, 131, 255, 0.12) !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .btn-light:hover,
    html[data-bs-theme="dark"] .designation-hierarchy-page .btn-light:hover,
    html[data-theme="dark"] .designation-hierarchy-page .btn-light:hover,
    html.dark .designation-hierarchy-page .btn-light:hover,
    body[data-pms-theme="dark"] .designation-hierarchy-page .btn-light:hover,
    [data-pms-theme="dark"] .designation-hierarchy-page .btn-light:hover,
    .dark .designation-hierarchy-page .btn-light:hover {
        background: rgba(79, 131, 255, 0.25) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    /* Stat Cards & Icons in Dark Mode */
    html[data-pms-theme="dark"] .designation-hierarchy-page .stat-card,
    html[data-bs-theme="dark"] .designation-hierarchy-page .stat-card,
    html[data-theme="dark"] .designation-hierarchy-page .stat-card,
    html.dark .designation-hierarchy-page .stat-card,
    body[data-pms-theme="dark"] .designation-hierarchy-page .stat-card,
    [data-pms-theme="dark"] .designation-hierarchy-page .stat-card,
    .dark .designation-hierarchy-page .stat-card {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.25) !important;
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .stat-card h3,
    html[data-bs-theme="dark"] .designation-hierarchy-page .stat-card h3,
    html[data-theme="dark"] .designation-hierarchy-page .stat-card h3,
    html.dark .designation-hierarchy-page .stat-card h3,
    body[data-pms-theme="dark"] .designation-hierarchy-page .stat-card h3,
    [data-pms-theme="dark"] .designation-hierarchy-page .stat-card h3,
    .dark .designation-hierarchy-page .stat-card h3 {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .stat-card span,
    html[data-bs-theme="dark"] .designation-hierarchy-page .stat-card span,
    html[data-theme="dark"] .designation-hierarchy-page .stat-card span,
    html.dark .designation-hierarchy-page .stat-card span,
    body[data-pms-theme="dark"] .designation-hierarchy-page .stat-card span,
    [data-pms-theme="dark"] .designation-hierarchy-page .stat-card span,
    .dark .designation-hierarchy-page .stat-card span {
        color: #CBD5E1 !important;
        -webkit-text-fill-color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .stat-sub,
    html[data-bs-theme="dark"] .designation-hierarchy-page .stat-sub,
    html[data-theme="dark"] .designation-hierarchy-page .stat-sub,
    html.dark .designation-hierarchy-page .stat-sub,
    body[data-pms-theme="dark"] .designation-hierarchy-page .stat-sub,
    [data-pms-theme="dark"] .designation-hierarchy-page .stat-sub,
    .dark .designation-hierarchy-page .stat-sub {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    /* Stat Card Icons in Dark Mode */
    html[data-pms-theme="dark"] .designation-hierarchy-page .stat-icon,
    html[data-bs-theme="dark"] .designation-hierarchy-page .stat-icon,
    html[data-theme="dark"] .designation-hierarchy-page .stat-icon,
    html.dark .designation-hierarchy-page .stat-icon,
    body[data-pms-theme="dark"] .designation-hierarchy-page .stat-icon,
    [data-pms-theme="dark"] .designation-hierarchy-page .stat-icon,
    .dark .designation-hierarchy-page .stat-icon,
    html[data-pms-theme="dark"] .designation-hierarchy-page .stat-card:first-of-type .stat-icon,
    html[data-bs-theme="dark"] .designation-hierarchy-page .stat-card:first-of-type .stat-icon {
        background: rgba(47, 107, 255, 0.22) !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
        border: 1px solid rgba(79, 131, 255, 0.4) !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .stat-card:nth-child(2) .stat-icon,
    html[data-bs-theme="dark"] .designation-hierarchy-page .stat-card:nth-child(2) .stat-icon {
        background: rgba(245, 158, 11, 0.22) !important;
        color: #fbbf24 !important;
        -webkit-text-fill-color: #fbbf24 !important;
        border: 1px solid rgba(245, 158, 11, 0.4) !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .stat-card:nth-child(3) .stat-icon,
    html[data-bs-theme="dark"] .designation-hierarchy-page .stat-card:nth-child(3) .stat-icon {
        background: rgba(56, 189, 248, 0.22) !important;
        color: #38bdf8 !important;
        -webkit-text-fill-color: #38bdf8 !important;
        border: 1px solid rgba(56, 189, 248, 0.4) !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .stat-card:nth-child(4) .stat-icon,
    html[data-bs-theme="dark"] .designation-hierarchy-page .stat-card:nth-child(4) .stat-icon {
        background: rgba(168, 85, 247, 0.22) !important;
        color: #c084fc !important;
        -webkit-text-fill-color: #c084fc !important;
        border: 1px solid rgba(168, 85, 247, 0.4) !important;
    }

    /* Filter Button & Action Buttons */
    html[data-pms-theme="dark"] .designation-hierarchy-page .btn-filter,
    html[data-bs-theme="dark"] .designation-hierarchy-page .btn-filter,
    html[data-theme="dark"] .designation-hierarchy-page .btn-filter,
    html.dark .designation-hierarchy-page .btn-filter,
    body[data-pms-theme="dark"] .designation-hierarchy-page .btn-filter,
    [data-pms-theme="dark"] .designation-hierarchy-page .btn-filter,
    .dark .designation-hierarchy-page .btn-filter {
        background: rgba(79, 131, 255, 0.12) !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .btn-filter:hover,
    html[data-bs-theme="dark"] .designation-hierarchy-page .btn-filter:hover {
        background: rgba(79, 131, 255, 0.25) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    /* Alert Info in Dark Mode */
    html[data-pms-theme="dark"] .designation-hierarchy-page .alert-info,
    html[data-bs-theme="dark"] .designation-hierarchy-page .alert-info,
    html[data-theme="dark"] .designation-hierarchy-page .alert-info,
    html.dark .designation-hierarchy-page .alert-info,
    body[data-pms-theme="dark"] .designation-hierarchy-page .alert-info,
    [data-pms-theme="dark"] .designation-hierarchy-page .alert-info,
    .dark .designation-hierarchy-page .alert-info {
        background: rgba(47, 107, 255, 0.1) !important;
        border-left: 4px solid #2F6BFF !important;
        border: 1px solid rgba(79, 131, 255, 0.2) !important;
        border-left-width: 4px !important;
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .alert-info .alert-icon,
    html[data-bs-theme="dark"] .designation-hierarchy-page .alert-info .alert-icon {
        background: rgba(47, 107, 255, 0.25) !important;
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .alert-info strong,
    html[data-bs-theme="dark"] .designation-hierarchy-page .alert-info strong {
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .alert-info p,
    html[data-bs-theme="dark"] .designation-hierarchy-page .alert-info p {
        color: #CBD5E1 !important;
    }

    /* Hierarchy Tree Container in Dark Mode */
    html[data-pms-theme="dark"] .designation-hierarchy-page .hierarchy-container,
    html[data-bs-theme="dark"] .designation-hierarchy-page .hierarchy-container,
    html[data-theme="dark"] .designation-hierarchy-page .hierarchy-container,
    html.dark .designation-hierarchy-page .hierarchy-container,
    body[data-pms-theme="dark"] .designation-hierarchy-page .hierarchy-container,
    [data-pms-theme="dark"] .designation-hierarchy-page .hierarchy-container,
    .dark .designation-hierarchy-page .hierarchy-container {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.2) !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .level-legend,
    html[data-bs-theme="dark"] .designation-hierarchy-page .level-legend,
    html[data-theme="dark"] .designation-hierarchy-page .level-legend,
    html.dark .designation-hierarchy-page .level-legend,
    body[data-pms-theme="dark"] .designation-hierarchy-page .level-legend,
    [data-pms-theme="dark"] .designation-hierarchy-page .level-legend,
    .dark .designation-hierarchy-page .level-legend {
        background: #141B3D !important;
        border-bottom: 1px solid rgba(79, 131, 255, 0.15) !important;
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .level-legend span:first-child,
    html[data-bs-theme="dark"] .designation-hierarchy-page .level-legend span:first-child {
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .legend-badge,
    html[data-bs-theme="dark"] .designation-hierarchy-page .legend-badge,
    [data-pms-theme="dark"] .designation-hierarchy-page .legend-badge,
    html[data-pms-theme="dark"] .designation-hierarchy-page .node-level,
    html[data-bs-theme="dark"] .designation-hierarchy-page .node-level,
    [data-pms-theme="dark"] .designation-hierarchy-page .node-level {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .legend-badge.l0,
    html[data-bs-theme="dark"] .designation-hierarchy-page .legend-badge.l0,
    [data-pms-theme="dark"] .designation-hierarchy-page .legend-badge.l0,
    html[data-pms-theme="dark"] .designation-hierarchy-page .node-level.level-0,
    html[data-bs-theme="dark"] .designation-hierarchy-page .node-level.level-0,
    [data-pms-theme="dark"] .designation-hierarchy-page .node-level.level-0 {
        background: #1E293B !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        border: 1px solid rgba(148, 163, 184, 0.45) !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.35);
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .legend-color.l0,
    html[data-bs-theme="dark"] .designation-hierarchy-page .legend-color.l0,
    [data-pms-theme="dark"] .designation-hierarchy-page .legend-color.l0 {
        background: #1E293B !important;
        border: 1px solid rgba(148, 163, 184, 0.45) !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .expand-all-btn,
    html[data-bs-theme="dark"] .designation-hierarchy-page .expand-all-btn,
    html[data-theme="dark"] .designation-hierarchy-page .expand-all-btn,
    html.dark .designation-hierarchy-page .expand-all-btn,
    body[data-pms-theme="dark"] .designation-hierarchy-page .expand-all-btn,
    [data-pms-theme="dark"] .designation-hierarchy-page .expand-all-btn,
    .dark .designation-hierarchy-page .expand-all-btn {
        background: rgba(79, 131, 255, 0.12) !important;
        color: #60A5FA !important;
        border: 1px solid rgba(79, 131, 255, 0.3) !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .expand-all-btn:hover,
    html[data-bs-theme="dark"] .designation-hierarchy-page .expand-all-btn:hover {
        background: rgba(79, 131, 255, 0.25) !important;
        color: #ffffff !important;
    }

    /* Cards, Tables & Status Bar */
    html[data-pms-theme="dark"] .designation-hierarchy-page .table-card,
    html[data-bs-theme="dark"] .designation-hierarchy-page .table-card,
    html[data-theme="dark"] .designation-hierarchy-page .table-card,
    html.dark .designation-hierarchy-page .table-card,
    body[data-pms-theme="dark"] .designation-hierarchy-page .table-card,
    [data-pms-theme="dark"] .designation-hierarchy-page .table-card,
    .dark .designation-hierarchy-page .table-card,
    html[data-pms-theme="dark"] .designation-hierarchy-page .status-bar,
    html[data-bs-theme="dark"] .designation-hierarchy-page .status-bar,
    html[data-theme="dark"] .designation-hierarchy-page .status-bar,
    html.dark .designation-hierarchy-page .status-bar,
    body[data-pms-theme="dark"] .designation-hierarchy-page .status-bar,
    [data-pms-theme="dark"] .designation-hierarchy-page .status-bar,
    .dark .designation-hierarchy-page .status-bar,
    html[data-pms-theme="dark"] .designation-hierarchy-page .chart-legend,
    html[data-bs-theme="dark"] .designation-hierarchy-page .chart-legend,
    html[data-theme="dark"] .designation-hierarchy-page .chart-legend,
    html.dark .designation-hierarchy-page .chart-legend,
    body[data-pms-theme="dark"] .designation-hierarchy-page .chart-legend,
    [data-pms-theme="dark"] .designation-hierarchy-page .chart-legend,
    .dark .designation-hierarchy-page .chart-legend {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.2) !important;
        color: #EEF1FB !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .table-header,
    html[data-bs-theme="dark"] .designation-hierarchy-page .table-header,
    html[data-theme="dark"] .designation-hierarchy-page .table-header,
    html.dark .designation-hierarchy-page .table-header,
    body[data-pms-theme="dark"] .designation-hierarchy-page .table-header,
    [data-pms-theme="dark"] .designation-hierarchy-page .table-header,
    .dark .designation-hierarchy-page .table-header {
        background: #141B3D !important;
        border-bottom: 1px solid rgba(79, 131, 255, 0.15) !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .table-title-icon,
    html[data-bs-theme="dark"] .designation-hierarchy-page .table-title-icon,
    html[data-theme="dark"] .designation-hierarchy-page .table-title-icon,
    html.dark .designation-hierarchy-page .table-title-icon,
    body[data-pms-theme="dark"] .designation-hierarchy-page .table-title-icon,
    body[data-bs-theme="dark"] .designation-hierarchy-page .table-title-icon,
    body[data-theme="dark"] .designation-hierarchy-page .table-title-icon,
    body.dark .designation-hierarchy-page .table-title-icon,
    [data-pms-theme="dark"] .designation-hierarchy-page .table-title-icon,
    [data-bs-theme="dark"] .designation-hierarchy-page .table-title-icon,
    [data-theme="dark"] .designation-hierarchy-page .table-title-icon,
    .dark .designation-hierarchy-page .table-title-icon {
        background: rgba(47, 107, 255, 0.22) !important;
        color: #60A5FA !important;
        border: 1px solid rgba(79, 131, 255, 0.4) !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .table-title-icon i,
    html[data-bs-theme="dark"] .designation-hierarchy-page .table-title-icon i,
    html[data-theme="dark"] .designation-hierarchy-page .table-title-icon i,
    html.dark .designation-hierarchy-page .table-title-icon i,
    body[data-pms-theme="dark"] .designation-hierarchy-page .table-title-icon i,
    [data-pms-theme="dark"] .designation-hierarchy-page .table-title-icon i,
    .dark .designation-hierarchy-page .table-title-icon i {
        color: #60A5FA !important;
        -webkit-text-fill-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .chart-control-btn,
    html[data-bs-theme="dark"] .designation-hierarchy-page .chart-control-btn,
    html[data-pms-theme="dark"] .designation-hierarchy-page .chart-controls button,
    html[data-bs-theme="dark"] .designation-hierarchy-page .chart-controls button {
        background: rgba(79, 131, 255, 0.12) !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .chart-control-btn:hover,
    html[data-bs-theme="dark"] .designation-hierarchy-page .chart-control-btn:hover,
    html[data-pms-theme="dark"] .designation-hierarchy-page .chart-controls button:hover,
    html[data-bs-theme="dark"] .designation-hierarchy-page .chart-controls button:hover {
        background: rgba(79, 131, 255, 0.25) !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .table-title h3,
    html[data-bs-theme="dark"] .designation-hierarchy-page .table-title h3,
    html[data-theme="dark"] .designation-hierarchy-page .table-title h3,
    html.dark .designation-hierarchy-page .table-title h3 {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .designation-item,
    html[data-bs-theme="dark"] .designation-hierarchy-page .designation-item,
    html[data-theme="dark"] .designation-hierarchy-page .designation-item,
    html.dark .designation-hierarchy-page .designation-item,
    body[data-pms-theme="dark"] .designation-hierarchy-page .designation-item,
    [data-pms-theme="dark"] .designation-hierarchy-page .designation-item,
    .dark .designation-hierarchy-page .designation-item {
        background: #141B3D !important;
        border: 1px solid rgba(79, 131, 255, 0.15) !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .designation-icon-small,
    html[data-bs-theme="dark"] .designation-hierarchy-page .designation-icon-small {
        background: rgba(47, 107, 255, 0.2) !important;
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .designation-name,
    html[data-bs-theme="dark"] .designation-hierarchy-page .designation-name,
    html[data-theme="dark"] .designation-hierarchy-page .designation-name,
    html.dark .designation-hierarchy-page .designation-name {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .text-muted,
    html[data-bs-theme="dark"] .designation-hierarchy-page .text-muted,
    html[data-theme="dark"] .designation-hierarchy-page .text-muted,
    html.dark .designation-hierarchy-page .text-muted,
    html[data-pms-theme="dark"] .designation-hierarchy-page .muted,
    html[data-bs-theme="dark"] .designation-hierarchy-page .muted,
    html[data-theme="dark"] .designation-hierarchy-page .muted,
    html.dark .designation-hierarchy-page .muted {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .status-item,
    html[data-bs-theme="dark"] .designation-hierarchy-page .status-item,
    html[data-theme="dark"] .designation-hierarchy-page .status-item,
    html.dark .designation-hierarchy-page .status-item {
        color: #CBD5E1 !important;
    }

    /* JSCharting Complete Dark Mode Overrides */
    html[data-pms-theme="dark"] .designation-hierarchy-page .chart-container,
    html[data-bs-theme="dark"] .designation-hierarchy-page .chart-container,
    html[data-theme="dark"] .designation-hierarchy-page .chart-container,
    html.dark .designation-hierarchy-page .chart-container,
    body[data-pms-theme="dark"] .designation-hierarchy-page .chart-container,
    body[data-bs-theme="dark"] .designation-hierarchy-page .chart-container,
    body[data-theme="dark"] .designation-hierarchy-page .chart-container,
    body.dark .designation-hierarchy-page .chart-container,
    [data-pms-theme="dark"] .designation-hierarchy-page .chart-container,
    [data-bs-theme="dark"] .designation-hierarchy-page .chart-container,
    [data-theme="dark"] .designation-hierarchy-page .chart-container,
    .dark .designation-hierarchy-page .chart-container,
    html[data-pms-theme="dark"] #chartDiv,
    html[data-bs-theme="dark"] #chartDiv,
    html[data-theme="dark"] #chartDiv,
    html.dark #chartDiv,
    body[data-pms-theme="dark"] #chartDiv,
    body[data-bs-theme="dark"] #chartDiv,
    body[data-theme="dark"] #chartDiv,
    body.dark #chartDiv,
    [data-pms-theme="dark"] #chartDiv,
    [data-bs-theme="dark"] #chartDiv,
    [data-theme="dark"] #chartDiv,
    .dark #chartDiv {
        background: #0F1530 !important;
        background-color: #0F1530 !important;
    }

    html[data-pms-theme="dark"] #chartDiv *,
    html[data-bs-theme="dark"] #chartDiv *,
    html[data-theme="dark"] #chartDiv *,
    html.dark #chartDiv *,
    body[data-pms-theme="dark"] #chartDiv *,
    [data-pms-theme="dark"] #chartDiv *,
    .dark #chartDiv * {
        background-color: transparent !important;
    }

    html[data-pms-theme="dark"] #chartDiv svg,
    html[data-bs-theme="dark"] #chartDiv svg,
    html[data-theme="dark"] #chartDiv svg,
    html.dark #chartDiv svg,
    body[data-pms-theme="dark"] #chartDiv svg,
    [data-pms-theme="dark"] #chartDiv svg,
    .dark #chartDiv svg {
        background: transparent !important;
        background-color: transparent !important;
    }

    html[data-pms-theme="dark"] #chartDiv svg > rect,
    html[data-bs-theme="dark"] #chartDiv svg > rect,
    html[data-theme="dark"] #chartDiv svg > rect,
    html.dark #chartDiv svg > rect,
    body[data-pms-theme="dark"] #chartDiv svg > rect,
    [data-pms-theme="dark"] #chartDiv svg > rect,
    .dark #chartDiv svg > rect,
    html[data-pms-theme="dark"] #chartDiv svg rect[fill="#ffffff"],
    html[data-bs-theme="dark"] #chartDiv svg rect[fill="#ffffff"],
    html[data-pms-theme="dark"] #chartDiv svg rect[fill="white"],
    html[data-bs-theme="dark"] #chartDiv svg rect[fill="white"],
    html[data-pms-theme="dark"] #chartDiv svg rect[fill="#FAFEEB"],
    html[data-bs-theme="dark"] #chartDiv svg rect[fill="#FAFEEB"],
    html[data-pms-theme="dark"] #chartDiv svg rect[fill="#f5fbf7"],
    html[data-bs-theme="dark"] #chartDiv svg rect[fill="#f5fbf7"] {
        fill: #0F1530 !important;
    }

    html[data-pms-theme="dark"] #chartDiv text,
    html[data-bs-theme="dark"] #chartDiv text,
    html[data-theme="dark"] #chartDiv text,
    html.dark #chartDiv text,
    body[data-pms-theme="dark"] #chartDiv text,
    [data-pms-theme="dark"] #chartDiv text,
    .dark #chartDiv text {
        fill: #ffffff !important;
        color: #ffffff !important;
    }

    /* SVG Connector lines & arrows in Dark Mode */
    html[data-pms-theme="dark"] #chartDiv path[fill="none"],
    html[data-bs-theme="dark"] #chartDiv path[fill="none"],
    html[data-theme="dark"] #chartDiv path[fill="none"],
    html.dark #chartDiv path[fill="none"],
    body[data-pms-theme="dark"] #chartDiv path[fill="none"],
    [data-pms-theme="dark"] #chartDiv path[fill="none"],
    .dark #chartDiv path[fill="none"],
    html[data-pms-theme="dark"] #chartDiv path[stroke],
    html[data-bs-theme="dark"] #chartDiv path[stroke],
    html[data-theme="dark"] #chartDiv path[stroke],
    html.dark #chartDiv path[stroke],
    body[data-pms-theme="dark"] #chartDiv path[stroke],
    [data-pms-theme="dark"] #chartDiv path[stroke],
    .dark #chartDiv path[stroke] {
        stroke: #60A5FA !important;
        stroke-width: 2.5px !important;
        stroke-opacity: 1 !important;
    }

    html[data-pms-theme="dark"] #chartDiv line,
    html[data-bs-theme="dark"] #chartDiv line,
    html[data-theme="dark"] #chartDiv line,
    html.dark #chartDiv line,
    body[data-pms-theme="dark"] #chartDiv line,
    [data-pms-theme="dark"] #chartDiv line,
    .dark #chartDiv line {
        stroke: #60A5FA !important;
        stroke-width: 2.5px !important;
        stroke-opacity: 1 !important;
    }

    html[data-pms-theme="dark"] #chartDiv marker path,
    html[data-bs-theme="dark"] #chartDiv marker path,
    [data-pms-theme="dark"] #chartDiv marker path {
        fill: #60A5FA !important;
        stroke: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .legend-header,
    html[data-bs-theme="dark"] .designation-hierarchy-page .legend-header,
    html[data-theme="dark"] .designation-hierarchy-page .legend-header,
    html.dark .designation-hierarchy-page .legend-header {
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .card-footer,
    html[data-bs-theme="dark"] .designation-hierarchy-page .card-footer,
    html[data-theme="dark"] .designation-hierarchy-page .card-footer,
    html.dark .designation-hierarchy-page .card-footer {
        background: #141B3D !important;
        border-top: 1px solid rgba(79, 131, 255, 0.15) !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .dropdown-menu,
    html[data-bs-theme="dark"] .designation-hierarchy-page .dropdown-menu,
    [data-pms-theme="dark"] .designation-hierarchy-page .dropdown-menu {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.25) !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5) !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .dropdown-item,
    html[data-bs-theme="dark"] .designation-hierarchy-page .dropdown-item,
    [data-pms-theme="dark"] .designation-hierarchy-page .dropdown-item {
        color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .dropdown-item:hover,
    html[data-bs-theme="dark"] .designation-hierarchy-page .dropdown-item:hover,
    [data-pms-theme="dark"] .designation-hierarchy-page .dropdown-item:hover {
        background: rgba(79, 131, 255, 0.15) !important;
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .btn-outline,
    html[data-bs-theme="dark"] .designation-hierarchy-page .btn-outline {
        background: rgba(79, 131, 255, 0.12) !important;
        border: 1px solid rgba(79, 131, 255, 0.35) !important;
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .btn-outline:hover,
    html[data-bs-theme="dark"] .designation-hierarchy-page .btn-outline:hover {
        background: rgba(79, 131, 255, 0.25) !important;
        color: #ffffff !important;
    }

    html[data-pms-theme="dark"] .designation-hierarchy-page .status-badge,
    html[data-bs-theme="dark"] .designation-hierarchy-page .status-badge {
        background: rgba(79, 131, 255, 0.15) !important;
        color: #60A5FA !important;
        border: 1px solid rgba(79, 131, 255, 0.3) !important;
    }

    /* Permanent Watermark / Branding Removal for JSCharting */
    #brandingLogo,
    [id="brandingLogo"],
    [id*="brandingLogo"],
    [id*="branding"],
    #chartDiv #brandingLogo,
    #chartDiv [id*="branding"],
    #chartDiv g:has(#brandingLogo),
    #chartDiv svg g:has(#brandingLogo),
    #chartDiv svg [id*="brandingLogo"],
    #chartDiv svg g[id="brandingLogo"],
    #chartDiv a[href*="jscharting"],
    #chartDiv [title*="JSCharting"],
    #chartDiv g:has([title*="JSCharting"]) {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        pointer-events: none !important;
        width: 0 !important;
        height: 0 !important;
        max-width: 0 !important;
        max-height: 0 !important;
        clip: rect(0, 0, 0, 0) !important;
        clip-path: inset(100%) !important;
        transform: scale(0) !important;
    }

    #brandingLogo *,
    [id="brandingLogo"] * {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }
</style>

@push('js')
<style id="pmsOrgTreeStyles">
    .pms-org-viewport { overflow: auto; cursor: grab; user-select: none; position: relative; overscroll-behavior: contain; }
    .pms-org-viewport.is-panning { cursor: grabbing; }
    .pms-org-canvas { display: inline-block; min-width: 100%; box-sizing: border-box; padding: 64px 32px 120px; text-align: center; }
    .pms-org-tree, .pms-org-tree ul { list-style: none; margin: 0; padding: 0; display: flex; justify-content: center; align-items: flex-start; }
    .pms-org-tree { gap: 28px; }
    .pms-org-tree ul { position: relative; padding-top: 26px; }
    .pms-org-tree li { position: relative; display: flex; flex-direction: column; align-items: center; padding: 26px 10px 0; }
    .pms-org-tree > li { padding-top: 0; }
    /* Connector lines */
    .pms-org-tree ul li::before, .pms-org-tree ul li::after { content: ""; position: absolute; top: 0; right: 50%; width: 50%; height: 26px; border-top: 2px solid #93B4FF; }
    .pms-org-tree ul li::after { right: auto; left: 50%; border-left: 2px solid #93B4FF; }
    .pms-org-tree ul li:only-child::before { display: none; }
    .pms-org-tree ul li:only-child::after { border-top: 0; }
    .pms-org-tree ul li:first-child::before, .pms-org-tree ul li:last-child::after { border-top: 0; }
    .pms-org-tree ul li:last-child::before { border-right: 2px solid #93B4FF; border-radius: 0 10px 0 0; }
    .pms-org-tree ul li:first-child:not(:only-child)::after { border-radius: 10px 0 0 0; }
    .pms-org-tree ul::before { content: ""; position: absolute; top: 0; left: 50%; height: 26px; border-left: 2px solid #93B4FF; }
    /* Employee card */
    .pms-org-node { position: relative; display: flex; align-items: center; gap: 10px; min-width: 190px; max-width: 240px; padding: 10px 14px 10px 12px; background: #ffffff; border: 1px solid #DCE5FF; border-left: 5px solid #94A3B8; border-radius: 14px; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08); text-align: left; transition: transform .15s ease, box-shadow .15s ease; font-family: inherit; }
    .pms-org-node:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(47, 107, 255, 0.18); }
    .pms-org-avatar { flex: 0 0 36px; width: 36px; height: 36px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; background: #94A3B8; }
    .pms-org-text { display: flex; flex-direction: column; min-width: 0; flex: 1 1 auto; }
    .pms-org-name { font-size: 13px; font-weight: 700; color: #0F172A !important; -webkit-text-fill-color: #0F172A !important; line-height: 1.25; overflow-wrap: anywhere; }
    .pms-org-role { font-size: 11.5px; font-weight: 500; color: #64748B !important; -webkit-text-fill-color: #64748B !important; line-height: 1.3; overflow-wrap: anywhere; }
    .pms-org-badge { flex: 0 0 auto; align-self: flex-start; font-size: 10px; font-weight: 800; padding: 2px 7px; border-radius: 999px; color: #2F6BFF !important; -webkit-text-fill-color: #2F6BFF !important; background: #EEF2FF; }
    .pms-org-count { position: absolute; left: 50%; bottom: -10px; transform: translateX(-50%); min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px; font-size: 10.5px; font-weight: 800; line-height: 20px; text-align: center; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; background: #2F6BFF; border: 2px solid #ffffff; z-index: 1; }
    .pms-org-l0 { border-left-color: #0F172A; } .pms-org-l0 .pms-org-avatar { background: #0F172A; }
    .pms-org-l1 { border-left-color: #2F6BFF; } .pms-org-l1 .pms-org-avatar { background: #2F6BFF; }
    .pms-org-l2 { border-left-color: #10b981; } .pms-org-l2 .pms-org-avatar { background: #10b981; }
    .pms-org-l3 { border-left-color: #3b82f6; } .pms-org-l3 .pms-org-avatar { background: #3b82f6; }
    .pms-org-l4 { border-left-color: #f59e0b; } .pms-org-l4 .pms-org-avatar { background: #f59e0b; }
    .pms-org-l5 { border-left-color: #f97316; } .pms-org-l5 .pms-org-avatar { background: #f97316; }
    .pms-org-l6 { border-left-color: #ef4444; } .pms-org-l6 .pms-org-avatar { background: #ef4444; }
    .pms-org-empty { height: 100%; min-height: 260px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; color: #64748B; text-align: center; padding: 24px; cursor: default; }
    .pms-org-empty i { font-size: 34px; color: #93B4FF; margin-bottom: 6px; }
    .pms-org-empty strong { color: #0F172A !important; -webkit-text-fill-color: #0F172A !important; font-size: 15px; }
</style>
<style>
    .fullscreen-mode .pms-org-viewport { height: calc(100vh - 150px); }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body.dark-mode) #employeeOrgTree .pms-org-node { background: #151C3D !important; border-color: rgba(96, 165, 250, 0.28); box-shadow: 0 8px 22px rgba(0, 0, 0, 0.35); }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body.dark-mode) #employeeOrgTree .pms-org-l0 { border-left-color: #E2E8F0; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body.dark-mode) #employeeOrgTree .pms-org-l0 .pms-org-avatar { background: #334155 !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body.dark-mode) #employeeOrgTree .pms-org-name,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body.dark-mode) #employeeOrgTree .pms-org-empty strong { color: #F1F5F9 !important; -webkit-text-fill-color: #F1F5F9 !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body.dark-mode) #employeeOrgTree .pms-org-role,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body.dark-mode) #employeeOrgTree .pms-org-empty { color: #A5B4D4 !important; -webkit-text-fill-color: #A5B4D4 !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body.dark-mode) #employeeOrgTree .pms-org-avatar,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body.dark-mode) #employeeOrgTree .pms-org-count { color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body.dark-mode) #employeeOrgTree .pms-org-badge { background: rgba(96, 165, 250, 0.16) !important; color: #93C5FD !important; -webkit-text-fill-color: #93C5FD !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body.dark-mode) #employeeOrgTree .pms-org-count { border-color: #0F1530; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body.dark-mode) #employeeOrgTree .pms-org-tree :is(ul, li)::before,
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body.dark-mode) #employeeOrgTree .pms-org-tree li::after { border-color: #4F7DD9; }
</style>
<script src="{{ asset('admin/assets/vendor/libs/html2canvas/html2canvas.min.js') }}"></script>
<script>if (typeof html2canvas === 'undefined') { document.write('<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"><\/script>'); }</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Level filter functionality
    document.querySelectorAll('.dropdown-item[data-level]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const level = this.dataset.level;
            document.querySelectorAll('.designation-item').forEach(el => {
                if (level === 'all') {
                    el.style.display = '';
                } else {
                    el.style.display = el.dataset.level == level ? '' : 'none';
                }
            });
        });
    });

    // Initialize SortableJS
    const hierarchyList = document.getElementById('hierarchyList');
    let originalHierarchy = null;

    if (hierarchyList) {
        originalHierarchy = hierarchyList.cloneNode(true).innerHTML;

        new Sortable(hierarchyList, {
            group: 'nested',
            animation: 200,
            handle: '.drag-handle',
            ghostClass: 'dragging',
            onEnd: function() {
                document.querySelectorAll('.designation-item').forEach(item => {
                    item.classList.remove('dragging');
                });
            }
        });
    }

    function setBranchExpanded(node, expanded) {
        const list = node.querySelector(':scope > ul');
        const button = node.querySelector(':scope > .designation-item .toggle-children');
        if (!list) return;
        list.hidden = !expanded;
        list.style.display = expanded ? '' : 'none';
        if (button) {
            button.setAttribute('aria-expanded', String(expanded));
            const icon = button.querySelector('i');
            if (icon) icon.className = expanded ? 'fas fa-chevron-down' : 'fas fa-chevron-right';
        }
    }

    document.getElementById('expandAll')?.addEventListener('click', function() {
        hierarchyList?.querySelectorAll('.hierarchy-node').forEach(node => setBranchExpanded(node, true));
    });

    // Delegation also handles nodes restored by Reset Changes.
    hierarchyList?.addEventListener('click', function(event) {
        const button = event.target.closest('.toggle-children');
        if (!button) return;
        const node = button.closest('.hierarchy-node');
        const list = node?.querySelector(':scope > ul');
        if (list) setBranchExpanded(node, list.hidden || getComputedStyle(list).display === 'none');
    });

    function hierarchyAlert(options) {
        const dark = isDarkModeActive();
        return Swal.fire({
            ...options,
            background: dark ? '#141B3D' : '#ffffff',
            color: dark ? '#EEF1FB' : '#1e293b',
            confirmButtonColor: '#2F6BFF',
            cancelButtonColor: dark ? '#334155' : '#64748b',
            customClass: { popup: dark ? 'hierarchy-alert hierarchy-alert-dark' : 'hierarchy-alert' }
        });
    }

    // Save hierarchy
    const saveBtn = document.getElementById('saveHierarchy');
    saveBtn?.addEventListener('click', function() {
        const originalHtml = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        document.getElementById('chartLoading')?.classList.remove('d-none');

        const hierarchy = [];
        let exceedsMaxDepth = false;

        function traverseList(list, parentId = null, currentDepth = 0) {
            Array.from(list.children).forEach((item, index) => {
                const id = item.dataset.id;
                hierarchy.push({ id, parent_id: parentId, order: index });
                if (currentDepth > {{ \App\Services\DesignationLevels::maximum() }}) {
                    exceedsMaxDepth = true;
                }
                const children = item.querySelector(':scope > ul');
                if (children) traverseList(children, id, currentDepth + 1);
            });
        }

        if (hierarchyList) traverseList(hierarchyList, null, 0);

        if (exceedsMaxDepth) {
            hierarchyAlert({
                icon: 'error',
                title: 'Hierarchy Depth Limit Exceeded',
                text: 'The organizational hierarchy cannot exceed Level {{ \App\Services\DesignationLevels::maximum() }}. Please re-arrange the designations within your company limit.',
                confirmButtonColor: '#2F6BFF'
            });
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalHtml;
            document.getElementById('chartLoading')?.classList.add('d-none');
            return;
        }

        fetch('{{ route("designations.save-hierarchy") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ hierarchy })
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Failed to save');
            return data;
        })
        .then(data => {
            hierarchyAlert({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'Hierarchy saved successfully',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
            document.getElementById('lastSaved').textContent = new Date().toLocaleString();
            updateOrganizationalChart();
        })
        .catch(error => {
            hierarchyAlert({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'Failed to save hierarchy',
                confirmButtonColor: '#2F6BFF'
            });
        })
        .finally(() => {
            setTimeout(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalHtml;
                document.getElementById('chartLoading')?.classList.add('d-none');
            }, 1000);
        });
    });

    // Reset hierarchy
    document.getElementById('resetHierarchy')?.addEventListener('click', function() {
        hierarchyAlert({
            title: 'Reset Changes?',
            text: 'This will discard all unsaved changes.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2F6BFF',
            confirmButtonText: 'Yes, reset'
        }).then((result) => {
            if (result.isConfirmed && hierarchyList) {
                hierarchyList.innerHTML = originalHierarchy;
            }
        });
    });

    // Initialize chart
    const chartPoints = @json($chartPoints ?? []);

    let chartZoom = 1;
    let latestChartPoints = chartPoints;
    let renderedChartSignature = null;

    function orgTreeContainer() {
        return document.getElementById('employeeOrgTree');
    }

    function orgTreeCanvas() {
        return orgTreeContainer()?.querySelector('.pms-org-canvas') || null;
    }

    function applyChartZoom() {
        const canvas = orgTreeCanvas();
        if (canvas) canvas.style.zoom = chartZoom;
    }

    function orgEscape(value) {
        return String(value ?? '').replace(/[&<>"']/g, ch => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
    }

    function orgInitials(name) {
        const words = String(name || '').trim().split(/\s+/).filter(Boolean);
        if (!words.length) return '?';
        return (words.length > 1 ? words[0][0] + words[1][0] : words[0].slice(0, 2)).toUpperCase();
    }

    function orgLevelClass(levelNumber) {
        const level = Number(levelNumber);
        return Number.isInteger(level) && level >= 0 && level <= 6 ? 'pms-org-l' + level : 'pms-org-lnone';
    }

    /** Build the reporting tree from {id, parent, name, level, level_number} points. */
    function buildOrgTree(points) {
        const byId = new Map();
        points.forEach(point => byId.set(String(point.id), Object.assign({}, point, { children: [] })));
        const roots = [];
        byId.forEach(node => {
            const parentId = node.parent ? String(node.parent) : null;
            const parent = parentId && parentId !== String(node.id) ? byId.get(parentId) : null;
            (parent ? parent.children : roots).push(node);
        });
        // Nodes caught in a reporting loop are unreachable from any root: show them as top-level.
        const reachable = new Set();
        const walk = node => { if (reachable.has(node)) return; reachable.add(node); node.children.forEach(walk); };
        roots.forEach(walk);
        byId.forEach(node => {
            if (!reachable.has(node)) {
                const owner = [...byId.values()].find(candidate => candidate.children.includes(node));
                if (owner) owner.children = owner.children.filter(child => child !== node);
                roots.push(node);
                walk(node);
            }
        });
        return roots;
    }

    function renderOrgNode(node) {
        const children = node.children.length
            ? '<ul>' + node.children.map(renderOrgNode).join('') + '</ul>'
            : '';
        const levelText = Number.isInteger(Number(node.level_number)) && node.level_number !== null ? 'L' + node.level_number : '';
        return '<li>' +
            '<div class="pms-org-node ' + orgLevelClass(node.level_number) + '" title="' + orgEscape(node.name + ' — ' + (node.level || '')) + '">' +
                '<span class="pms-org-avatar">' + orgEscape(orgInitials(node.name)) + '</span>' +
                '<span class="pms-org-text">' +
                    '<span class="pms-org-name">' + orgEscape(node.name) + '</span>' +
                    '<span class="pms-org-role">' + orgEscape(node.level || 'No designation') + '</span>' +
                '</span>' +
                (levelText ? '<span class="pms-org-badge">' + orgEscape(levelText) + '</span>' : '') +
                (node.children.length ? '<span class="pms-org-count" title="Direct reports">' + node.children.length + '</span>' : '') +
            '</div>' +
            children +
        '</li>';
    }

    function initOrganizationalChart(points = chartPoints) {
        const container = orgTreeContainer();
        if (!container) return;
        latestChartPoints = Array.isArray(points) ? points : [];
        const signature = JSON.stringify(latestChartPoints);
        // Unchanged data keeps the current view (scroll position and zoom) instead of redrawing.
        if (signature === renderedChartSignature && orgTreeCanvas()) return;
        renderedChartSignature = signature;

        if (!latestChartPoints.length) {
            container.innerHTML = '<div class="pms-org-empty"><i class="fas fa-sitemap"></i><strong>No reporting structure yet</strong><span>Employees appear here once they are added with a reporting manager.</span></div>';
            return;
        }

        const scrollLeft = container.scrollLeft;
        const scrollTop = container.scrollTop;
        const hadCanvas = !!orgTreeCanvas();
        container.innerHTML = '<div class="pms-org-canvas"><ul class="pms-org-tree">' + buildOrgTree(latestChartPoints).map(renderOrgNode).join('') + '</ul></div>';
        applyChartZoom();
        if (hadCanvas) {
            container.scrollLeft = scrollLeft;
            container.scrollTop = scrollTop;
        } else {
            centerOrgTree();
        }
    }

    function centerOrgTree() {
        const container = orgTreeContainer();
        if (!container) return;
        container.scrollLeft = Math.max(0, (container.scrollWidth - container.clientWidth) / 2);
        container.scrollTop = 0;
    }

    function updateOrganizationalChart() {
        return fetch('{{ route("designations.chart-data") }}', {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        })
        .then(res => res.json())
        .then(data => initOrganizationalChart(data.points || []))
        .catch(() => initOrganizationalChart(latestChartPoints));
    }

    // Drag to pan the chart (mouse); touch devices scroll natively.
    (function enableOrgTreePanning() {
        const container = orgTreeContainer();
        if (!container) return;
        let drag = null;
        container.addEventListener('mousedown', event => {
            if (event.button !== 0) return;
            drag = { x: event.clientX, y: event.clientY, left: container.scrollLeft, top: container.scrollTop };
            container.classList.add('is-panning');
            event.preventDefault();
        });
        window.addEventListener('mousemove', event => {
            if (!drag) return;
            container.scrollLeft = drag.left - (event.clientX - drag.x);
            container.scrollTop = drag.top - (event.clientY - drag.y);
        });
        window.addEventListener('mouseup', () => {
            if (!drag) return;
            drag = null;
            container.classList.remove('is-panning');
        });
    })();

    // Chart controls
    document.getElementById('zoomIn')?.addEventListener('click', () => {
        chartZoom = Math.min(2, Number((chartZoom + 0.15).toFixed(2)));
        applyChartZoom();
    });
    document.getElementById('zoomOut')?.addEventListener('click', () => {
        chartZoom = Math.max(0.5, Number((chartZoom - 0.15).toFixed(2)));
        applyChartZoom();
    });
    document.getElementById('resetZoom')?.addEventListener('click', () => {
        chartZoom = 1;
        applyChartZoom();
        centerOrgTree();
    });

    /** Capture high-resolution PNG data URL from the rendered tree using html2canvas */
    async function captureOrgChartPng() {
        const canvas = orgTreeCanvas();
        if (!canvas) {
            throw new Error('No organizational chart found on the page.');
        }

        const tree = canvas.querySelector('.pms-org-tree');
        if (!tree || !tree.children.length) {
            throw new Error('No reporting structure to export yet. Please add employees with reporting managers first.');
        }

        if (typeof html2canvas !== 'function') {
            throw new Error('Chart image generator library is still loading. Please try again in a moment.');
        }

        const container = orgTreeContainer();
        const prevScrollLeft = container ? container.scrollLeft : 0;
        const prevScrollTop = container ? container.scrollTop : 0;
        const prevZoom = canvas.style.zoom;

        try {
            canvas.style.zoom = '1';
            if (container) {
                container.scrollLeft = 0;
                container.scrollTop = 0;
            }

            const isDark = isDarkModeActive();
            const backgroundColor = isDark ? '#0F1530' : '#ffffff';

            const rendered = await html2canvas(canvas, {
                scale: 2,
                backgroundColor: backgroundColor,
                useCORS: true,
                logging: false,
                allowTaint: true,
                scrollX: 0,
                scrollY: 0,
                onclone: (clonedDoc) => {
                    const clonedCanvas = clonedDoc.querySelector('.pms-org-canvas');
                    if (clonedCanvas) {
                        clonedCanvas.style.zoom = '1';
                        clonedCanvas.style.transform = 'none';
                        clonedCanvas.style.padding = '40px 32px 60px';
                    }
                }
            });

            return rendered.toDataURL('image/png');
        } finally {
            canvas.style.zoom = prevZoom;
            if (container) {
                container.scrollLeft = prevScrollLeft;
                container.scrollTop = prevScrollTop;
            }
        }
    }

    // Export chart
    document.getElementById('exportChartBtn')?.addEventListener('click', async (e) => {
        e.preventDefault();
        const exportBtn = document.getElementById('exportChartBtn');
        const origContent = exportBtn ? exportBtn.innerHTML : '';
        if (exportBtn) {
            exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
        }

        try {
            const dataUrl = await captureOrgChartPng();
            const link = document.createElement('a');
            const todayStr = new Date().toISOString().split('T')[0];
            link.href = dataUrl;
            link.download = `organization-hierarchy-${todayStr}.png`;
            document.body.appendChild(link);
            link.click();
            link.remove();

            hierarchyAlert({
                icon: 'success',
                title: 'Export Complete!',
                text: 'The organizational chart has been downloaded as a high-resolution PNG image.',
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } catch (error) {
            console.error('Export chart failed:', error);
            hierarchyAlert({
                icon: 'warning',
                title: 'Export Notice',
                text: error.message || 'Unable to export chart image at this time. Please use Print Chart instead.',
                confirmButtonColor: '#2F6BFF'
            });
        } finally {
            if (exportBtn) {
                exportBtn.innerHTML = origContent;
            }
        }
    });

    // Print chart via invisible iframe (100% bypasses browser popup blockers)
    document.getElementById('printChartBtn')?.addEventListener('click', async (e) => {
        e.preventDefault();
        const printBtn = document.getElementById('printChartBtn');
        const origContent = printBtn ? printBtn.innerHTML : '';
        if (printBtn) {
            printBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparing Print...';
        }

        try {
            const canvas = orgTreeCanvas();
            const tree = canvas ? canvas.querySelector('.pms-org-tree') : null;
            if (!tree || !tree.children.length) {
                throw new Error('No reporting structure to print yet. Please add employees with reporting managers first.');
            }

            const companyName = document.title.split('-')[0].trim() || 'Bitroxia PMS';
            const timestamp = new Date().toLocaleString();

            let dataUrl = null;
            try {
                dataUrl = await captureOrgChartPng();
            } catch (renderErr) {
                console.warn('html2canvas render skipped, falling back to pure HTML print markup:', renderErr);
            }

            let iframe = document.getElementById('pmsOrgPrintIframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'pmsOrgPrintIframe';
                iframe.style.position = 'fixed';
                iframe.style.right = '0';
                iframe.style.bottom = '0';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = '0';
                iframe.style.visibility = 'hidden';
                document.body.appendChild(iframe);
            }

            const doc = iframe.contentDocument || iframe.contentWindow.document;
            doc.open();

            if (dataUrl) {
                doc.write(`<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Organization Hierarchy - ${companyName}</title>
  <style>
    @page { size: landscape; margin: 10mm; }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 12px;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      background: #ffffff;
      color: #0f172a;
    }
    .print-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 2px solid #2F6BFF;
      padding-bottom: 12px;
      margin-bottom: 16px;
    }
    .print-title { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0; }
    .print-subtitle { font-size: 13px; color: #64748b; margin-top: 4px; }
    .print-meta { text-align: right; font-size: 12px; color: #64748b; line-height: 1.4; }
    .print-chart-wrap {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
      margin: 12px 0 20px;
    }
    .print-chart-img {
      max-width: 100%;
      height: auto;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    }
    .print-footer {
      border-top: 1px solid #e2e8f0;
      padding-top: 10px;
      display: flex;
      justify-content: space-between;
      font-size: 11px;
      color: #94a3b8;
    }
  </style>
</head>
<body>
  <div class="print-header">
    <div>
      <h1 class="print-title">Organization Hierarchy</h1>
      <div class="print-subtitle">Employee Reporting Structure &amp; Leadership Levels</div>
    </div>
    <div class="print-meta">
      <div><strong>${companyName}</strong></div>
      <div>Printed on: ${timestamp}</div>
    </div>
  </div>
  <div class="print-chart-wrap">
    <img src="${dataUrl}" class="print-chart-img" alt="Organization Chart" />
  </div>
  <div class="print-footer">
    <div>Bitroxia PMS &bull; Enterprise Organizational Directory</div>
    <div>Confidential &bull; Internal Company Records</div>
  </div>
</body>
</html>`);
            } else {
                const styles = document.getElementById('pmsOrgTreeStyles')?.textContent || '';
                doc.write(`<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Organization Hierarchy - ${companyName}</title>
  <style>
    @page { size: landscape; margin: 10mm; }
    body { margin: 0; padding: 20px; font-family: -apple-system, BlinkMacSystemFont, Arial, sans-serif; background: #ffffff; color: #0f172a; }
    .print-header { border-bottom: 2px solid #2F6BFF; padding-bottom: 10px; margin-bottom: 20px; }
    .print-title { font-size: 22px; font-weight: 800; margin: 0; }
    .print-meta { font-size: 12px; color: #64748b; margin-top: 4px; }
    ${styles}
    .pms-org-canvas { zoom: 1 !important; padding: 20px !important; }
  </style>
</head>
<body class="pms-org-export">
  <div class="print-header">
    <h1 class="print-title">Organization Hierarchy</h1>
    <div class="print-meta">${companyName} &bull; Generated on ${timestamp}</div>
  </div>
  ${canvas ? canvas.outerHTML : '<p>No reporting structure yet.</p>'}
</body>
</html>`);
            }
            doc.close();

            const img = doc.querySelector('img');
            const triggerPrint = () => {
                setTimeout(() => {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                }, 250);
            };

            if (img && !img.complete) {
                img.onload = triggerPrint;
                img.onerror = triggerPrint;
            } else {
                triggerPrint();
            }
        } catch (error) {
            console.error('Print chart failed:', error);
            hierarchyAlert({
                icon: 'warning',
                title: 'Print Notice',
                text: error.message || 'Unable to generate print layout. Please try again.',
                confirmButtonColor: '#2F6BFF'
            });
        } finally {
            if (printBtn) {
                printBtn.innerHTML = origContent;
            }
        }
    });

    // Fullscreen chart
    document.getElementById('fullscreenChart')?.addEventListener('click', function() {
        const card = document.querySelector('.col-xl-6 .table-card');
        card.classList.toggle('fullscreen-mode');
        this.innerHTML = card.classList.contains('fullscreen-mode') ? '<i class="fas fa-compress"></i>' : '<i class="fas fa-expand"></i>';
        setTimeout(centerOrgTree, 100);
    });

    // Initialize
    initOrganizationalChart();

    // Keep the employee hierarchy current when new employees are added or updated.
    window.addEventListener('focus', updateOrganizationalChart);
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) updateOrganizationalChart();
    });
    setInterval(updateOrganizationalChart, 30000);
});
</script>
@endpush
@endsection
