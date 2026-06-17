@extends('admin.layout.app')

@section('title', 'Designation Hierarchy')

@section('content')
<main class="designation-hierarchy-page">
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
                                @for($i = 0; $i <= 6; $i++)
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
                                @for($i = 0; $i <= 6; $i++)
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
                                <i class="fas fa-chart-network"></i>
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
                        <div id="chartDiv" class="chart-container"></div>

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
        color: #07130d;
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
        box-shadow: 0 18px 45px rgba(15, 116, 76, .09);
        border: 1px solid rgba(15, 116, 76, .12);
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
        background: linear-gradient(145deg, #34d399, #10b981);
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

    .stat-card {
        background: white;
        padding: 22px;
        border-radius: 22px;
        border: 1px solid rgba(15, 116, 76, .12);
        box-shadow: 0 14px 35px rgba(15, 116, 76, .06);
        display: flex;
        gap: 16px;
        align-items: center;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 40px rgba(15, 116, 76, .12);
    }

    .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        background: #d1fae5;
        color: #0f744c;
    }

    .stat-card h3 {
        font-size: 28px;
        margin-bottom: 4px;
    }

    .stat-card span {
        color: #6b7280;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .stat-sub {
        font-size: 11px;
        color: #9ca3af;
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
        color: #0f744c;
        border: 1px solid rgba(15, 116, 76, .14);
    }

    .btn-primary {
        background: linear-gradient(145deg, #34d399, #10b981);
        color: white;
        box-shadow: 0 10px 25px rgba(16, 185, 129, .25);
    }

    .btn-outline {
        background: transparent;
        border: 1px solid rgba(15, 116, 76, .2);
        color: #0f744c;
    }

    .btn-outline:hover {
        background: #edf8f2;
        border-color: #34d399;
    }

    .btn:hover {
        transform: translateY(-2px);
    }

    .btn-filter {
        background: #f0f9f4;
        border: 1px solid rgba(15, 116, 76, .15);
        padding: 10px 18px;
        border-radius: 12px;
        font-weight: 600;
        color: #0f744c;
    }

    /* Table Card */
    .table-card {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(15, 116, 76, .12);
        box-shadow: 0 18px 45px rgba(15, 116, 76, .08);
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
        border-bottom: 1px solid rgba(15, 116, 76, .1);
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
        color: #0f744c;
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
        color: #8ba198;
    }

    /* Alert Info */
    .alert-info {
        background: #ecfdf5;
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
        background: #d1fae5;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #059669;
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
        border: 1px solid rgba(15, 116, 76, .1);
        overflow: hidden;
    }

    .level-legend {
        padding: 14px 20px;
        background: #ffffff;
        border-bottom: 1px solid rgba(15, 116, 76, .1);
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .level-legend span:first-child {
        font-weight: 700;
        color: #07130d;
        font-size: 0.8rem;
    }

    .legend-badge {
        padding: 4px 10px;
        border-radius: 30px;
        font-size: 0.7rem;
        font-weight: 700;
        color: white;
    }

    .legend-badge.l0 { background: #111827; }
    .legend-badge.l1 { background: #0f744c; }
    .legend-badge.l2 { background: #10b981; }
    .legend-badge.l3 { background: #3b82f6; }
    .legend-badge.l4 { background: #f59e0b; }
    .legend-badge.l5 { background: #f97316; }
    .legend-badge.l6 { background: #ef4444; }

    .expand-all-btn {
        margin-left: auto;
        background: #f0f9f4;
        border: none;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #0f744c;
        cursor: pointer;
        transition: all 0.2s;
    }

    .expand-all-btn:hover {
        background: #d1fae5;
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
        border-left: 2px solid #d1fae5;
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
        border-color: #34d399;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);
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
        color: #0f744c;
        background: #edf8f2;
    }

    .designation-info {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
    }

    .designation-icon-small {
        width: 38px;
        height: 38px;
        background: #e7f5ee;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0f744c;
    }

    .designation-details {
        flex: 1;
    }

    .designation-name {
        font-weight: 700;
        color: #07130d;
    }

    .designation-parent {
        font-size: 0.7rem;
        color: #8ba198;
    }

    .designation-level {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        color: white;
    }

    .toggle-children {
        background: none;
        border: none;
        cursor: pointer;
        color: #8ba198;
        padding: 6px;
        border-radius: 6px;
    }

    .toggle-children:hover {
        background: #edf8f2;
        color: #0f744c;
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
        border-top: 1px solid rgba(15, 116, 76, .1);
    }

    .info-text {
        font-size: 0.75rem;
        color: #8ba198;
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
        background: #d1fae5;
        color: #059669;
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
        background: #f0f9f4;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        color: #0f744c;
        cursor: pointer;
        transition: all 0.2s;
    }

    .chart-control-btn:hover {
        background: #d1fae5;
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
        border: 1px solid rgba(15, 116, 76, .15);
        width: 36px;
        height: 36px;
        border-radius: 10px;
        color: #0f744c;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .chart-controls button:hover {
        background: #d1fae5;
        border-color: #34d399;
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
        border: 1px solid rgba(15, 116, 76, .1);
    }

    .legend-header {
        font-size: 0.7rem;
        font-weight: 700;
        color: #07130d;
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

    .legend-color.l0 { background: #111827; }
    .legend-color.l1 { background: #0f744c; }
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
        border-top-color: #0f744c;
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
        border-top: 1px solid rgba(15, 116, 76, .08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .footer-info {
        font-size: 0.7rem;
        color: #8ba198;
    }

    .footer-status {
        font-size: 0.7rem;
        color: #059669;
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
        border: 1px solid rgba(15, 116, 76, .1);
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
        color: #a7f3d0;
        margin-bottom: 20px;
    }

    .empty-state h5 {
        color: #0f744c;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #8ba198;
        margin-bottom: 20px;
    }

    /* Dropdown */
    .dropdown-menu {
        background: white;
        border: 1px solid rgba(15, 116, 76, .15);
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
        background: #ecfdf5;
        color: #059669;
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
    .l0-bg { background: #111827; }
    .l1-bg { background: #0f744c; }
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
</style>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://code.jscharting.com/latest/jscharting.js"></script>
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

    // Expand/Collapse All
    document.getElementById('expandAll')?.addEventListener('click', function() {
        const allToggles = document.querySelectorAll('.toggle-children');
        const allChildLists = document.querySelectorAll('.hierarchy-list ul');

        // Check if any are collapsed
        const hasCollapsed = Array.from(allChildLists).some(list => list.style.display === 'none');

        allChildLists.forEach(list => {
            list.style.display = hasCollapsed ? '' : 'none';
        });

        allToggles.forEach(btn => {
            const icon = btn.querySelector('i');
            if (hasCollapsed) {
                icon.className = 'fas fa-chevron-down';
            } else {
                icon.className = 'fas fa-chevron-right';
            }
        });
    });

    // Toggle children function (global for onclick)
    window.toggleChildren = function(element) {
        const parentItem = element.closest('.designation-item');
        const childList = parentItem.querySelector('ul');
        const icon = element.querySelector('i');

        if (childList) {
            if (childList.style.display === 'none') {
                childList.style.display = '';
                icon.className = 'fas fa-chevron-down';
            } else {
                childList.style.display = 'none';
                icon.className = 'fas fa-chevron-right';
            }
        }
    };

    // Save hierarchy
    const saveBtn = document.getElementById('saveHierarchy');
    saveBtn?.addEventListener('click', function() {
        const originalHtml = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        document.getElementById('chartLoading')?.classList.remove('d-none');

        const hierarchy = [];

        function traverseList(list, parentId = null) {
            Array.from(list.children).forEach((item, index) => {
                const id = item.dataset.id;
                hierarchy.push({ id, parent_id: parentId, order: index });
                const children = item.querySelector('ul');
                if (children) traverseList(children, id);
            });
        }

        traverseList(hierarchyList);

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
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'Hierarchy saved successfully',
                timer: 2000,
                showConfirmButton: false,
                position: 'top-end'
            });
            document.getElementById('lastSaved').textContent = new Date().toLocaleString();
            updateOrganizationalChart();
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'Failed to save hierarchy',
                confirmButtonColor: '#0f744c'
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
        Swal.fire({
            title: 'Reset Changes?',
            text: 'This will discard all unsaved changes.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0f744c',
            confirmButtonText: 'Yes, reset'
        }).then((result) => {
            if (result.isConfirmed && hierarchyList) {
                hierarchyList.innerHTML = originalHierarchy;
            }
        });
    });

    // Initialize chart
    let chart;
    const chartPoints = @json($chartPoints ?? []);

    let chartZoom = 1;
    let latestChartPoints = chartPoints;

    function applyChartZoom() {
        const chartDiv = document.getElementById('chartDiv');
        if (chartDiv) {
            chartDiv.style.transform = `scale(${chartZoom})`;
        }
    }

    function initOrganizationalChart(points = chartPoints) {
        if (typeof JSC === 'undefined') {
            console.warn('JScharting not loaded');
            return;
        }

        latestChartPoints = points;
        chart = JSC.chart('chartDiv', {
            type: 'organizational',
            palette: ['#0f744c', '#10b981', '#3b82f6', '#f59e0b', '#f97316', '#ef4444'],
            defaultSeries: {
                shape: {
                    outline: { width: 1, color: 'white' },
                    fill: '#0f744c'
                },
                label: {
                    style: { fontSize: 12, color: 'white', fontWeight: '600' },
                    verticalAlign: 'middle',
                    autoWrap: true,
                    maxWidth: 120
                }
            },
            title: { label: { text: 'Employee Reporting Hierarchy', style: { fontSize: 16, color: '#495057' } } },
            legend: { visible: false },
            defaultPoint: {
                tooltip: '<b>%name</b><br>Designation: %level',
                label_text: '%name'
            },
            series: [{
                name: 'Employees',
                points: latestChartPoints
            }]
        });

        applyChartZoom();
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
    });

    // Export chart
    document.getElementById('exportChartBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        chart?.export({
            format: 'png',
            width: 1920,
            height: 1080,
            download: true,
            filename: 'organizational-chart-' + new Date().toISOString().split('T')[0]
        });
    });

    // Print chart
    document.getElementById('printChartBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head><title>Employee Hierarchy</title><style>body{margin:0;padding:20px;font-family:Arial}</style></head>
                <body><h2>Employee Reporting Hierarchy</h2><p>Generated on ${new Date().toLocaleString()}</p>
                <img src="${chart?.export({ format: 'png', width: 1000, height: 700 }).dataUrl}" style="max-width:100%"></body>
            </html>
        `);
        printWindow.document.close();
        setTimeout(() => printWindow.print(), 500);
    });

    // Fullscreen chart
    document.getElementById('fullscreenChart')?.addEventListener('click', function() {
        const card = document.querySelector('.col-xl-6 .table-card');
        card.classList.toggle('fullscreen-mode');
        this.innerHTML = card.classList.contains('fullscreen-mode') ? '<i class="fas fa-compress"></i>' : '<i class="fas fa-expand"></i>';
        setTimeout(() => chart?.redraw(), 100);
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
